# Assinaturas / Cobrança (Asaas) — Backend

Documentação da feature de assinaturas pagas do **Meus Orçamentos**, implementada via integração com o [Asaas](https://www.asaas.com/). Leia isto antes de mexer em qualquer coisa relacionada a `Plan`, `Subscription`, `Invoice` ou pagamento.

Ver também: `../front/docs/SUBSCRIPTION.md` (contraparte no frontend).

## Regras de negócio (o que foi pedido, na ordem em que foi decidido)

1. **3 planos**: Mensal (R$ 40,00), Trimestral (R$ 109,00), Anual (R$ 400,00) — preços e ciclo 100% editáveis no banco (tabela `plan`), não hardcoded.
2. **Trial de 3 dias**, criado automaticamente no cadastro da empresa (não em algum passo separado).
3. **Escolher um plano encerra qualquer estado anterior sem pagamento confirmado** — status vira `INCOMPLETE` (bloqueado) assim que o usuário confirma a assinatura, **exceto** se já estava `ACTIVE` (trocar de plano quem já paga não derruba o acesso). Isso vale tanto pra quem estava em trial quanto pra quem estava `EXPIRED`/`CANCELED` tentando assinar de novo — a regra é "não é ACTIVE → vira INCOMPLETE ao escolher plano". O acesso só volta quando o Asaas confirmar o pagamento de verdade (webhook ou reconciliação).
4. **Inadimplência bloqueia 100% do acesso** (`402 SUBSCRIPTION_REQUIRED`) até regularizar — sem modo "somente leitura".
5. **Sem feature-gating entre planos** — a única diferença é preço/duração, não há limites de uso.
6. **Pagamento**: cartão de crédito (100% automático, tokenizado), Pix ou boleto (o Asaas gera a cobrança automaticamente a cada ciclo, mas o pagamento em si depende do cliente agir). **Boleto foi retirado do seletor no frontend** (decisão do usuário: não combina com o desbloqueio imediato — leva de 1 a 3 dias úteis pra compensar) — o backend continua aceitando `billingType: boleto` normalmente, só não é mais oferecido na tela. Ver seção "QR Code Pix embutido" abaixo pro caso do Pix.
7. **CNPJ é opcional na empresa**: o cadastro da empresa (`Company.registrationNumber`) só aceita CNPJ (14 dígitos) e continua opcional lá. Mas o Asaas **exige** CPF ou CNPJ pra criar um customer — então a tela de pagamento (frontend) coleta CPF/CNPJ **só quando a empresa não tem CNPJ cadastrado**. Ver seção "CPF/CNPJ" abaixo.

## Modelo de dados

Três entidades novas, todas em `src/Entity/Subscription/`:

- **`Plan`** — catálogo de planos. Campos principais: `code`, `name`, `priceCents` (inteiro, centavos — mesma convenção de `Transaction::$amount`), `billingCycle` (enum `PlanBillingCycle`: monthly/quarterly/yearly), `trialDays`, `active`, `sortOrder`.
  - O trial do cadastro usa o `trialDays` do **plano ativo com menor `sortOrder`** (`PlanRepository::findDefaultActive()`), não um valor fixo em `.env`.
- **`Subscription`** — 1:1 com `Company` (dono da relação, igual ao padrão `Company`↔`User`). Campos: `plan` (nullable — null até o usuário escolher), `status` (enum `SubscriptionStatus`), `billingType` (enum `SubscriptionBillingType`), `asaasCustomerId`, `asaasSubscriptionId`, `creditCardToken`/`cardLastFour`/`cardBrand`, `trialEndsAt`, `currentPeriodEnd`, `canceledAt`, **`documentNumber`** (CPF ou CNPJ efetivamente usado no Asaas — ver seção CPF/CNPJ).
- **`Invoice`** — espelho local das cobranças do Asaas (uma por pagamento gerado), alimentado por webhook. `asaasPaymentId` é **unique** (idempotência). Guardar isso localmente é proposital: o bloqueio de acesso roda em toda request e não pode depender de chamada síncrona ao Asaas.

Enums em `src/Enum/Subscription/`: `PlanBillingCycle`, `SubscriptionStatus` (`TRIALING|ACTIVE|PAST_DUE|EXPIRED|CANCELED|INCOMPLETE`, com método `blocksAccess()`), `SubscriptionBillingType`, `InvoiceStatus` (`PENDING|CONFIRMED|RECEIVED|OVERDUE|REFUNDED|FAILED|CANCELED|CHARGEBACK` — `CHARGEBACK` cobre os status de contestação do Asaas, ver seção "Estorno e chargeback" abaixo).

### Status da Subscription — quando cada um acontece

| Status | Quando | Bloqueia? |
|---|---|---|
| `TRIALING` | Empresa recém-criada, ainda não escolheu plano (ou trial dentro do prazo) | Não (a menos que `trialEndsAt` já tenha passado — ver `SubscriptionAccessSubscriber`) |
| `INCOMPLETE` | Escolheu um plano, aguardando confirmação do primeiro pagamento | **Sim** |
| `ACTIVE` | Webhook confirmou pagamento (`PAYMENT_CONFIRMED`/`RECEIVED`) | Não |
| `PAST_DUE` | Webhook de fatura vencida (`PAYMENT_OVERDUE`) | **Sim** |
| `EXPIRED` | Trial venceu e o usuário nunca escolheu plano (`app:subscription:expire-trials`) | **Sim** |
| `CANCELED` | Usuário cancelou (`POST /api/subscription/cancel`) | Não, até `currentPeriodEnd` passar (carência — ver `isBlocked()` abaixo) |

Todas as migrations correspondentes: `Version20260714024357` (plan + subscription + seed dos 3 planos), `Version20260714025914` (invoice), `Version20260714180424` (subscription.document_number).

## CPF/CNPJ (`documentNumber`)

O Asaas exige CPF ou CNPJ pra criar um customer — não tem como contornar isso, é exigência do gateway (regulação de PIX/boleto), não uma trava nossa. Como nem toda empresa cadastrada tem CNPJ (MEI/autônomo), a resolução do documento em `SubscriptionService::resolveDocument()` segue esta prioridade:

1. `Company::registrationNumber` (CNPJ, se a empresa tiver)
2. `ChoosePlanInputDTO::holderCpfCnpj` (informado na tela de pagamento nessa tentativa)
3. `Subscription::documentNumber` (reaproveita o que já foi salvo numa tentativa anterior, pra não pedir de novo)

Se nenhum dos três existir, `400 DOCUMENT_REQUIRED`. Se o tamanho não bater com CPF (11) nem CNPJ (14) depois de limpar não-dígitos, `400 INVALID_DOCUMENT`. Não fazemos validação de dígito verificador (checksum) — o Asaas valida isso do lado dele.

**Company continua exigindo só CNPJ** no próprio cadastro (`CompanyInputDTO`, `Assert\Length(min: 14, max: 14)`) — isso foi decisão explícita, não mexer.

## Integração com o Asaas

- **`Service/Gateway/AsaasClient.php`** — wrapper HTTP fino (`Symfony\Contracts\HttpClient\HttpClientInterface`, já vem com o framework). Métodos: `createCustomer`, `updateCustomer`, `tokenizeCreditCard`, `createSubscription`, `updateSubscription`, `cancelSubscription`, `cancelPayment`, `getPayment`, `listPaymentsBySubscription`. Autentica via header `access_token` (não é Bearer/OAuth).
- **`Service/Subscription/SubscriptionService.php`** — orquestração. Métodos principais: `startTrial`, `choosePlan`, `cancel`, `syncFromPaymentWebhook`, `reconcile`, `listInvoicesByCompany`.

`choosePlan()` chama `reconcile()` internamente **antes** de retornar — o Asaas já gera a primeira cobrança (com QR Code Pix / link de boleto) de forma síncrona ao criar a subscription, então buscamos ela na hora em vez de esperar o webhook chegar. Isso é o que permite o frontend mostrar o "Pagar Agora" imediatamente após escolher o plano (ver doc do front).

`cancel()` **limpa** `asaasSubscriptionId`/`creditCardToken`/`cardLastFour`/`cardBrand` depois de cancelar no Asaas. Isso foi um bug real: sem limpar, uma nova tentativa de `choosePlan` via `syncAsaasSubscription` tentava dar `updateSubscription` numa assinatura já morta no Asaas, que responde `"A assinatura [...] não pode ser atualizada."`. Se você adicionar outro fluxo que mexe em `asaasSubscriptionId`, lembre de considerar esse caso.

**`cancel()` também cancela as cobranças pendentes no Asaas** (`InvoiceRepository::findPendingBySubscription()` busca Invoices `PENDING`/`OVERDUE` da subscription, e cada uma é cancelada via `AsaasClient::cancelPayment()` antes de zerar `asaasSubscriptionId`). Esse foi outro bug real: `DELETE /subscriptions/{id}` no Asaas cancela a **assinatura**, mas não cancela cobranças **já geradas** — um Pix em aberto continuava pagável no Asaas mesmo depois do cancelamento local. Como `asaasSubscriptionId` é zerado logo em seguida, e é exatamente esse campo que `syncFromPaymentWebhook()` usa pra achar a `Subscription` (linha "localiza a `Subscription` pelo `asaasSubscriptionId`" abaixo), um pagamento confirmado depois do cancelamento não tinha como ser reconciliado — o dinheiro entrava no Asaas mas o sistema nunca ficava sabendo. Falha ao cancelar uma cobrança individual (ex.: corrida rara, pagamento confirmado um instante antes do cancelamento) só loga um warning e não impede o resto do cancelamento.

`cancel()` **não** derruba o acesso na hora — a empresa já pagou pelo período vigente, então continua liberada até `currentPeriodEnd` (carência). Isso não é feito zerando nada em `cancel()`: `status` vira `CANCELED` e `currentPeriodEnd` é preservado como estava (não é limpo), e quem decide "bloqueia ou não" em tempo real é `Subscription::isBlocked()` (ver seção "Controle de acesso" abaixo). Uma assinatura cancelada sem nunca ter tido pagamento confirmado (`currentPeriodEnd === null`) bloqueia imediatamente — não há período pago a honrar.

### Erros do Asaas repassados pro usuário

`SubscriptionController::choosePlan`/`cancel` capturam `HttpException` vindo do `AsaasClient` e devolvem `{"message": "ASAAS_ERROR", "detail": "<mensagem real do Asaas>"}` com `502` — não um código genérico. `AsaasClient::request()` já extrai `$decoded['errors'][0]['description']` da resposta do Asaas, que costuma ser uma frase pronta em português (ex: `"O CPF/CNPJ informado é inválido."`, `"O telefone informado é inválido."`) segura pra mostrar direto pro usuário. O frontend (`PaymentForm.jsx`) já trata isso — ver doc do front.

### Pegadinhas reais que já mordemos (não repetir)

- **Telefone**: mandar o celular da empresa (`Company::phone`, 11 dígitos) no campo `mobilePhone` do Asaas, **não** `phone` (que é pra fixo). Já corrigido em `ensureAsaasCustomer`.
- **Números de teste óbvios**: o Asaas rejeita celular tipo `11999999999` (dígitos repetidos) como inválido — não é bug nosso, é validação deles. Em teste manual, usar um número "normal" tipo `11987654321`.
- **CPF/CNPJ inválido não é bug**: o Asaas valida dígito verificador de verdade (nós só checamos tamanho — 11 ou 14 — em `resolveDocument`, não checksum). Um CPF "inventado" tipo `377.816.198-85` é recusado pelo Asaas com `"O CPF/CNPJ informado é inválido."`, que agora chega certinho no frontend (ver seção acima). Pra testar, usar um CPF válido de verdade, ex. `529.982.247-25`.
- **`$` na API key no `.env.local`**: a API key do Asaas começa com `$` (ex: `$aact_hmlg_...`). Como o backend usa `env_file: .env.local` no `docker-compose.yml`, o Compose interpreta `$aact_...` como interpolação de variável e zera a chave — precisa escapar como `$$aact_...` no arquivo. Se `ASAAS_API_KEY` chegar vazia dentro do container sem motivo aparente, é isso.
- **Empresa pré-existente sem `Subscription`**: qualquer `Company` criada **antes** dessa feature existir não tem `Subscription` — `/api/subscription` responde `404 SUBSCRIPTION_NOT_FOUND` até rodar `app:subscription:backfill` (ver seção Comandos). Se um usuário reclamar de 404/`SUBSCRIPTION_NOT_FOUND` do nada, é essa a causa mais provável, não bug de rota.

## Webhook

`POST /api/webhook/asaas` (`Controller/Webhook/AsaasWebhookController`). Autentica comparando o header `asaas-access-token` com o parâmetro `asaas_webhook_token` (não usa JWT — rota liberada como `PUBLIC_ACCESS` em `security.yaml`, tem que ficar **antes** da regra genérica `^/api`). A comparação usa `hash_equals()` (tempo constante), não `!==` — importante pra validação de segredo, evita vazar informação por *timing attack*.

Processamento é **síncrono**, sem fila (`symfony/messenger` não está instalado — decisão deliberada, ver Fase 6/roadmap original). `syncFromPaymentWebhook` lê `payload.event` + `payload.payment`, localiza a `Subscription` pelo `asaasSubscriptionId`, e delega pra `applyPayment()` (privado), que também é reusado pelo `reconcile()` — a diferença é que o `reconcile` busca pagamentos direto da API (`listPaymentsBySubscription`) em vez de receber via webhook. Ambos os caminhos convergem no mesmo mapeamento de status Asaas → `InvoiceStatus`/`SubscriptionStatus` (`mapAsaasStatus`).

`Invoice.asaasPaymentId` é **unique** — idempotência natural contra reentrega de webhook.

### Estorno e chargeback

`mapAsaasStatus()` mapeia `REFUNDED`/`REFUND_REQUESTED` pra `InvoiceStatus::REFUNDED` e `CHARGEBACK_REQUESTED`/`CHARGEBACK_DISPUTE`/`AWAITING_CHARGEBACK_REVERSAL` pra `InvoiceStatus::CHARGEBACK`. Em `applyPayment()`, qualquer um desses dois status força a `Subscription` pra `PAST_DUE` — reaproveita o mesmo status/bloqueio de inadimplência (`SubscriptionStatus::blocksAccess()`), em vez de criar um status novo só pra isso. Decisão deliberada: **não** cancela a assinatura no Asaas nesse caso (ao contrário do `cancel()` manual) — se a disputa for revertida a favor da empresa, a recorrência continua intacta sem precisar recriar nada. Antes disso, um estorno/chargeback não mudava o status da `Subscription` — o acesso continuava liberado mesmo com o dinheiro devolvido.

Importante conferir no painel do Asaas (Configurações → Webhooks → Eventos) se os eventos de chargeback estão marcados — a seleção padrão de "Cobranças" nem sempre inclui eles.

### Testando o webhook de verdade em ambiente local

O Asaas precisa alcançar sua máquina publicamente — `localhost` não funciona. Testado e validado com **Cloudflare Tunnel** (`cloudflared tunnel --url http://localhost:8000`, não exige conta/cadastro, gera uma URL `https://algo.trycloudflare.com` temporária). Alternativas equivalentes: `ngrok` (exige conta/authtoken) ou `npx localtunnel`.

Esse procedimento (subir o túnel, montar a URL do webhook, checklist dos pontos abaixo) está automatizado na skill do Claude Code `.claude/skills/asaas-tunnel/` (`bash .claude/skills/asaas-tunnel/scripts/start-tunnel.sh`) — os passos manuais abaixo continuam valendo como referência/fallback.

No painel sandbox do Asaas (**Configurações → Integrações → Webhooks**), ao cadastrar, preste atenção nestes 3 campos — todos já nos morderam por estarem no valor padrão errado:

1. **"Versão da API"**: escolher **v3** (o dropdown pode vir em `v2` por padrão). Nosso `AsaasClient` chama a REST API em v3 (`ASAAS_API_URL=.../v3`) e o `AsaasWebhookController` espera o formato de payload da v3 — misturar versão pode mudar o shape do JSON recebido.
2. **"Este Webhook ficará ativo?"**: precisa estar **ligado**. Fica fácil esquecer, o toggle nasce desligado.
3. **"Fila de sincronização ativada?"**: também precisa estar **ligado**. O próprio Asaas avisa no formulário: com isso desligado, eventos continuam sendo *gerados* mas **não são enviados** pro seu endpoint até você ligar.
4. **Token de autenticação**: cole o mesmo valor de `ASAAS_WEBHOOK_TOKEN` (`.env.local`) — **não** clique em "Gerar Token" no painel, isso cria um token novo do lado do Asaas que não bate com o nosso.
5. **Eventos**: marcar pelo menos os de "Cobranças" (`PAYMENT_CREATED/CONFIRMED/RECEIVED/OVERDUE/REFUNDED/DELETED/UPDATED`).

Confirmar que funcionou de ponta a ponta: gerar uma cobrança (`choosePlan`), confirmar o pagamento manualmente no sandbox ("dar baixa"), e ver nos logs (`docker logs -f backend-php-1`) uma requisição `POST` real batendo em `api_webhook_asaas` vinda do domínio do túnel — sem precisar rodar `app:subscription:reconcile` na mão.

## QR Code Pix embutido (sem redirecionar pro Asaas)

Por padrão, cada `Invoice` traz um `invoiceUrl` — a página "fatura" **hospedada pelo Asaas**, que inclui propaganda de um produto deles que concorre com o nosso SaaS. Pra pagamentos **Pix**, isso foi contornado: `AsaasClient::getPixQrCode(string $asaasPaymentId)` chama `GET /payments/{id}/pixQrCode` no Asaas e devolve os dados brutos — nada de página deles.

Resposta real do Asaas (testada contra o sandbox): `{ success, encodedImage (base64 PNG), payload (código "copia e cola" Pix), expirationDate, description }`.

Fluxo: `SubscriptionController::pixQrCode(int $id)` → `GET /api/subscription/invoices/{id}/pix-qrcode` → `SubscriptionService::getPixQrCode(Company $company, int $invoiceId)`:
- busca a `Invoice` via `InvoiceRepository::findByIdAndCompany()` (escopada pela empresa do usuário — evita IDOR, mesmo padrão de `CategoryRepository::findByIdAndCompany`)
- não encontrou → `404 INVOICE_NOT_FOUND`
- `billingType` da invoice não é `PIX` → `400 INVOICE_NOT_PIX`
- devolve o array cru do Asaas — **não persiste nada no banco**, é buscado ao vivo toda vez que a tela abre (o QR pode expirar, não vale a pena guardar)

**Boleto ficou de fora dessa melhoria** (decisão do usuário) — continua usando `invoiceUrl` normalmente, com a propaganda do Asaas. Frontend também parou de oferecer boleto como opção na hora de escolher o plano (ver doc do front), mas o backend não tem nenhuma restrição — se algum dia mandarem `billingType: boleto`, o fluxo de `choosePlan` funciona igual.

## Controle de acesso (bloqueio)

**`EventSubscriber/SubscriptionAccessSubscriber.php`**, no evento `KernelEvents::CONTROLLER`. Curto-circuita qualquer rota `/api/*` com `402 {"error":"SUBSCRIPTION_REQUIRED"}` quando a `Subscription` da empresa está bloqueada — **exceto** uma allowlist de prefixos (`ALLOWED_PATH_PREFIXES` na própria classe): auth/onboarding, `/api/me`, `/api/company`, `/api/subscription*`, `/api/plans`, `/api/webhook`, `/api/admin`.

### `Subscription::isBlocked()` — fonte única de verdade

A decisão "essa empresa pode usar o sistema?" mora **só** num lugar: `Subscription::isBlocked()`, na própria entity.

```php
public function isBlocked(): bool
{
    if ($this->status === SubscriptionStatus::TRIALING) {
        return $this->trialEndsAt !== null && $this->trialEndsAt < new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
    }

    // Cancelar não derruba o acesso na hora — a empresa já pagou pelo período
    // atual, então continua liberada até ele acabar. Sem `currentPeriodEnd`
    // (nunca teve pagamento confirmado), não há período pago a honrar.
    if ($this->status === SubscriptionStatus::CANCELED) {
        return $this->currentPeriodEnd === null || $this->currentPeriodEnd < new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
    }

    return $this->status?->blocksAccess() ?? true;
}
```

Isso não era assim originalmente — **era um bug real**: `SubscriptionAccessSubscriber` tinha essa checagem de tempo real pro `TRIALING`, mas `SubscriptionMapper::toOutputDTO()` (o campo `blocked` devolvido pro frontend em `GET /api/me`/`GET /api/subscription`) chamava direto `$subscription->getStatus()->blocksAccess()`, que não sabe de tempo — pra `TRIALING` sempre respondia `false`. Resultado: sem o cron `expire-trials` já ter rodado, o backend bloqueava as rotas de dados corretamente, mas o frontend nunca ficava sabendo (via o campo `blocked`) que devia redirecionar pra `/subscription/blocked` — o usuário via o painel carregar normalmente enquanto tudo debaixo dos panos dava 402. Corrigido extraindo a regra pra `Subscription::isBlocked()` e fazendo tanto o `SubscriptionAccessSubscriber` quanto o `SubscriptionMapper` chamarem esse método único, em vez de duplicar a lógica. **Se for adicionar um novo lugar que precisa saber se a empresa está bloqueada, chame `$subscription->isBlocked()` — nunca reimplemente a checagem.**

Importante: as checagens de `TRIALING` e `CANCELED` são **em tempo real** (comparando `trialEndsAt`/`currentPeriodEnd` com `now`), não dependem só do `status` armazenado — porque nenhum cron muda o `status` nesses casos no exato instante em que o prazo vence (o `EXPIRED` do trial só é setado quando `app:subscription:expire-trials` roda, e é diário; `CANCELED` nunca muda de status sozinho, o prazo é sempre `currentPeriodEnd`). Sem essa checagem em tempo real, teria uma janela de até 24h (trial) ou até o fim do período pago nunca expirar de fato (cancelamento) onde o acesso ficaria incorreto.

**Segundo bug real, mesma raiz (carência do cancelamento)**: o texto de confirmação do modal "Cancelar assinatura" no frontend sempre prometeu "Você perderá acesso ao sistema assim que o período atual terminar" — mas antes dessa correção, `isBlocked()` não tinha o branch de `CANCELED` acima, então caía direto em `$this->status?->blocksAccess()`, que bloqueia `CANCELED` **imediatamente** (ver `SubscriptionStatus::blocksAccess()`), sem honrar o período já pago. Corrigido com o branch de `CANCELED` acima. Testado manualmente contra o backend real: assinatura `ACTIVE` com `currentPeriodEnd` 20 dias no futuro, cancelada → `status: canceled, blocked: false` e rotas de dados continuam 200; movendo `currentPeriodEnd` pra 1 dia no passado (mesma assinatura) → `blocked: true` e rotas voltam a dar 402, sem precisar de nenhum cron. Frontend (`Index.jsx`) também foi ajustado pra diferenciar a mensagem quando `status === 'canceled'` (ver doc do front).

Todas as comparações de data/hora do domínio de assinatura (`isBlocked()`, `canChangePlan()`, `startTrial()`, `cancel()`, `applyPayment()`) usam explicitamente `new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'))`, nunca `new \DateTimeImmutable()` puro — o `now()` sem timezone explícito usa o timezone padrão do PHP configurado no servidor, que pode não ser `America/Sao_Paulo`, causando comparações erradas por algumas horas perto de meia-noite (ex.: `isBlocked()`/`canChangePlan()` decidindo errado por estar usando UTC contra um `currentPeriodEnd` pensado em horário de Brasília). Se adicionar uma comparação de data nova nesse domínio, sempre especificar o timezone explicitamente, do mesmo jeito.

### Bloqueio de troca de plano (`canChangePlan()`)

Regra de negócio: trocar de plano com uma assinatura que já tem período pago vigente derrubava esse período sem aproveitar os dias restantes — o Asaas gerava uma cobrança cheia do novo plano na hora, com vencimento amanhã (`syncAsaasSubscription()`, `nextDueDate: +1 dia`, ver abaixo o motivo desse "+1 dia"), sem nenhum crédito proporcional. A solução adotada foi **não** implementar proração (mais simples, e aqui não há feature-gating entre planos — regra de negócio #5 — então não há urgência real em trocar no meio do ciclo) e sim **bloquear a troca** enquanto o período atual não estiver perto do fim.

`Subscription::canChangePlan()`, mesmo padrão de "fonte única de verdade" do `isBlocked()`:

```php
public function canChangePlan(): bool
{
    $hasRemainingPaidAccess = match ($this->status) {
        SubscriptionStatus::ACTIVE => true,
        SubscriptionStatus::CANCELED => $this->currentPeriodEnd !== null
            && $this->currentPeriodEnd >= new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')),
        default => false,
    };

    if (!$hasRemainingPaidAccess || $this->currentPeriodEnd === null) {
        return true;
    }

    return $this->currentPeriodEnd->modify('-3 days') <= new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
}
```

- Bloqueado (retorna `false`) só quando existe período pago vigente: `ACTIVE`, ou `CANCELED` ainda dentro da carência (mesma janela que `isBlocked()` já usa — ver acima). Fora disso (trial, incompleta, atrasada, expirada, ou cancelada já fora da carência) a troca é sempre permitida — inclusive pra "reativar" depois de cancelar.
- A janela libera a troca **3 dias antes** do fim do período, não só depois de vencer — decisão deliberada pra evitar o risco de o Asaas já ter gerado/cobrado o próximo ciclo do plano antigo antes do usuário conseguir trocar.
- `SubscriptionService::choosePlan()` chama esse método logo no início e responde `400 PLAN_CHANGE_NOT_ALLOWED` (via `BadRequestHttpException`) se bloqueado. Frontend: `Index.jsx` desabilita o botão "Trocar de Plano" e mostra a data de liberação quando `subscription.canChangePlan` (exposto pelo `SubscriptionMapper`) vem `false`; `ChoosePlan.jsx` marca o card do plano atual e desabilita seu botão de seleção com a mesma flag.
- **Efeito colateral proposital**: como a checagem não diferencia "mesmo plano" de "plano diferente", ela também cobre a idempotência da escolha de plano — reenviar o mesmo plano enquanto bloqueado cai na mesma regra. Não existe (nem foi criado) um mecanismo de idempotência dedicado (lock, chave de idempotência) — é só efeito colateral dessa trava. Gap conhecido e não resolvido: enquanto a assinatura ainda está `INCOMPLETE`/`TRIALING` (antes do primeiro pagamento confirmar), `canChangePlan()` sempre libera, então nada no backend impede duas requisições `POST /api/subscription` quase simultâneas nesse estado — só o frontend (`PaymentForm.jsx`, desabilita o botão durante o envio) amortece isso, sem garantia de servidor.

**Bug real #1 (`currentPeriodEnd` errado no primeiro pagamento)**: `applyPayment()` inicialmente copiava `currentPeriodEnd` direto de `Invoice::dueDate`. Como `syncAsaasSubscription()` sempre manda `nextDueDate: +1 dia` pro Asaas — inclusive na primeira assinatura, é isso que permite mostrar "Pagar Agora" na hora em vez de esperar o ciclo (ver acima) — o `dueDate` da **primeira** cobrança de qualquer plano é sempre "amanhã", nunca reflete o ciclo real (mensal/trimestral/anual). Resultado: `currentPeriodEnd` nascia sempre "amanhã", `canChangePlan()` liberava a troca imediatamente após qualquer assinatura nova, e o bloqueio acima nunca chegava a valer no primeiro ciclo. Corrigido calculando `currentPeriodEnd` a partir do **ciclo do plano** (`Plan::billingCycle`) somado a `Invoice::paidAt`, num método dedicado `SubscriptionService::calculatePeriodEnd()`, em vez de copiar o `dueDate` do Asaas. Não mexe em `nextDueDate: +1 dia` — isso continua controlando a cobrança real no Asaas e não deve mudar. Testado contra dado real: assinatura Trimestral paga em `2026-07-15` → `currentPeriodEnd` recalculado (via `app:subscription:reconcile`) foi de `2026-07-16` (bug) pra `2026-10-15` (correto).

**Bug real #2 (cancelamento não travava a troca)**: a primeira versão de `canChangePlan()` só verificava `status === ACTIVE`. Como cancelar muda o status pra `CANCELED` (mesmo com `currentPeriodEnd` ainda no futuro, dentro da carência), a troca destravava na hora do cancelamento — e pior, escolher um plano novo nesse estado fazia `choosePlan()` setar `status = INCOMPLETE` (por não ser `ACTIVE`), e `INCOMPLETE` bloqueia acesso **imediatamente** (`SubscriptionStatus::blocksAccess()`), cortando na hora o acesso que a carência do cancelamento deveria honrar. Corrigido generalizando `canChangePlan()` pra tratar `CANCELED` com carência vigente do mesmo jeito que `ACTIVE` (código acima).

## Comandos (cron — não tem fila/scheduler instalado, de propósito)

Todos em `src/Command/Subscription/`, rodam via `bin/console`:

- **`app:subscription:expire-trials`** — marca `TRIALING` com `trialEndsAt` vencido como `EXPIRED`. Rodar diariamente.
- **`app:subscription:reconcile`** — consulta o Asaas pra cada `Subscription` com `asaasSubscriptionId` e corrige status local (rede de segurança pra webhook perdido). Rodar diariamente.
- **`app:subscription:backfill`** — cria `Subscription` em trial pra `Company` que existia **antes** dessa feature (mesma lógica do `app:setup-initial-company` que já existia pra `Company`). Rodar uma vez por ambiente/deploy caso haja empresas pré-existentes sem assinatura (dá erro `SUBSCRIPTION_NOT_FOUND` em `/api/subscription` até rodar isso).

Nenhum desses tem agendamento automático ainda — falta configurar cron real no host/plataforma de deploy (pendente, é a Fase 6 do roadmap original).

## Endpoints

| Rota | Auth | Descrição |
|---|---|---|
| `GET /api/plans` | Pública | Lista planos ativos |
| `GET /api/subscription` | JWT | Assinatura da empresa do usuário logado |
| `POST /api/subscription` | JWT | Escolhe/troca de plano (`ChoosePlanInputDTO`) |
| `POST /api/subscription/cancel` | JWT | Cancela (Asaas + local) |
| `GET /api/subscription/invoices` | JWT | Histórico de faturas |
| `GET /api/subscription/invoices/{id}/pix-qrcode` | JWT | QR Code Pix (imagem + copia-e-cola), buscado ao vivo no Asaas |
| `POST /api/webhook/asaas` | Token custom (não JWT) | Recebe eventos de pagamento |

`GET /api/me` (`UserController`) inclui `subscription` no payload ao lado de `company`.

## Variáveis de ambiente

`ASAAS_API_URL`, `ASAAS_API_KEY`, `ASAAS_WEBHOOK_TOKEN` — documentadas (comentadas) em `.env`, valores reais vão em `.env.local` (gitignored, **nunca** commitar a API key). `docker-compose.yml` carrega `.env.local` via `env_file` no serviço `php`; não duplicar essas três variáveis no bloco `environment:` do compose, senão elas sobrescrevem o que vem do `env_file` com string vazia.

## O que falta (não implementado ainda)

- CRUD admin pra planos (hoje só existe leitura pública; editar preço/trial é direto no banco).
- Cron real agendado (host/plataforma) pros 3 comandos acima — sistema ainda não está em produção, então isso ainda não foi testado nem configurado de verdade.
- Credenciais e webhook de **produção** — o fluxo completo (customer, subscription, cobrança, webhook automático) já foi validado de ponta a ponta no **sandbox**, incluindo o webhook chegando sozinho via túnel público. Falta só repetir a configuração com chave/URL de produção quando for a hora.
- Painel admin pra ver assinaturas de todas as empresas.
- Tela de cadastro de cartão pra planos de cartão de crédito não foi testada contra o Asaas de verdade ainda (só Pix foi validado ponta a ponta com pagamento real confirmado, incluindo o QR Code embutido). Adiado deliberadamente — prioridade atual é garantir Pix.
- QR Code embutido só existe pra Pix — boleto continua mandando pro `invoiceUrl` do Asaas (com a propaganda). Não é bug, foi escopo combinado — se um dia quiser reativar boleto no frontend, considerar resolver isso também.
- Idempotência de `choosePlan()` durante `INCOMPLETE`/`TRIALING` (antes do primeiro pagamento confirmar) depende só do frontend desabilitar o botão durante o envio — não há trava de servidor contra duas requisições `POST /api/subscription` quase simultâneas nesse estado. Baixo risco (gera no máximo uma cobrança extra cancelável, não corrompe estado), deixado de fora conscientemente — ver seção "Bloqueio de troca de plano" acima.
