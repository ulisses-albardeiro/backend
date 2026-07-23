# Assinatura Digital (Technician/Signature) — Backend

Documentação da feature de assinatura digital do **Meus Orçamentos**: cadastro de uma assinatura desenhada à mão (touch) e sua integração como carimbo opcional nos PDFs de Recibo, Ordem de Serviço e Orçamento. Leia isto antes de mexer em `Technician`, `Signature`, `TechnicianService`/`SignatureService`, `TechnicianController`/`SignatureController`, ou no campo `includeSignature` de `Receipt`/`WorkOrder`/`Quote`.

## Modelo de dados

### `Technician` (`src/Entity/Technician.php`)

Campos: `id`, `name` (obrigatório), `company` (`ManyToOne`, obrigatório), `createdAt`/`updatedAt` (manuais + `#[ORM\PrePersist]`/`#[ORM\PreUpdate]`, mesmo padrão de `Customer`), `signature` (`OneToOne`, `mappedBy: 'technician'`, inverso — conveniência de acesso).

**Decisão de design**: tratado como módulo de domínio completo (entidade própria, CRUD completo) mesmo que hoje o frontend só use **um único** técnico por empresa — antecipa um futuro módulo de gestão de múltiplos técnicos sem exigir redesenho. Na prática, toda a resolução de "a assinatura da empresa" (ver seção de integração abaixo) assume `findOneBy(['company' => $company])`, ou seja, **o primeiro/único técnico da empresa** — não há seleção de qual técnico assina cada documento.

### `Signature` (`src/Entity/Signature.php`)

Campos: `id`, `fileName` (`?string`, nome do arquivo em disco), `technician` (`OneToOne`, `inversedBy: 'signature'`, `#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]`), `company` (`ManyToOne`, obrigatório — redundante com `technician->getCompany()`, mas permite filtrar/verificar posse diretamente sem precisar sempre passar pelo técnico), `createdAt`/`updatedAt`.

**Cascade só em um sentido**: excluir um `Technician` remove a `Signature` vinculada automaticamente **no banco** (`ON DELETE CASCADE` na FK) — confirmado no SQL da migration (`ALTER TABLE signature ADD CONSTRAINT ... FOREIGN KEY (technician_id) REFERENCES technician (id) ON DELETE CASCADE`, mais um índice único em `technician_id` garantindo uma assinatura por técnico). Excluir uma `Signature` nunca afeta o `Technician`. O arquivo físico **não** é removido pela cascade do banco — `TechnicianService::delete()` busca a `Signature` antes de remover o técnico e chama `FileService::remove()` explicitamente.

### Armazenamento do arquivo

Mesmo padrão do logo da empresa: `FileService` salva em disco, só o nome do arquivo vai pro banco. Subdiretório: `company_[hash]/signature/<nome-gerado>`, via `Company::getSubDir('/signature')` (reaproveita o método já usado pelo logo, só troca o sufixo).

## Endpoints

Dois controllers separados — salvar o nome (técnico) e salvar a imagem (assinatura) são **requisições diferentes**, decisão deliberada do usuário:

| Rota | Método | Descrição |
|---|---|---|
| `GET /api/technician` | GET | Lista os técnicos da empresa (hoje sempre 0 ou 1 na prática) |
| `GET /api/technician/{id}` | GET | Detalhe de um técnico |
| `POST /api/technician` | POST | Cria (só o nome) |
| `PUT /api/technician/{id}` | PUT | Atualiza o nome |
| `DELETE /api/technician/{id}` | DELETE | Exclui o técnico — cascata remove a assinatura (banco) + arquivo (service) |
| `POST`/`PUT /api/signature` | POST/PUT | Upsert da imagem — multipart, `technicianId` + arquivo `signature` (`$request->files->get('signature')`, mesmo motivo do logo: não dá pra usar `#[MapRequestPayload]` puro com arquivo) |
| `DELETE /api/signature/{technicianId}` | DELETE | Remove só a assinatura, mantém o técnico |

Toda operação verifica que o `Technician`/`Signature` resolvido pertence à `company` do usuário autenticado (`findOneBy(['id' => ..., 'company' => $company])`) — nunca confia no `id` vindo do request, mesmo padrão de `Customer`/`Category`.

`TechnicianOutputDTO` expõe `signatureUrl` (nullable, via `FileService::getPublicUrl()`) — evita uma segunda requisição só pra saber se já existe assinatura cadastrada.

## Frontend — cadastro (`front/src/pages/panel/Signature/Index.jsx`)

Tela única em Documentos → "Assinatura Digital" (nome escolhido pra não colidir com o item "Assinatura" já existente, que é o plano pago/`/subscription`). O conceito de "técnico" não aparece — só um campo de nome + canvas de assinatura.

Captura via **`react-signature-canvas`** (touch). **Achado real**: `getTrimmedCanvas()` (que corta a borda em branco do desenho) quebra em dev com `TypeError: (0, import_trim_canvas.default) is not a function` — problema de interop do Vite com a dependência `trim-canvas` na pré-otimização de deps (`esbuild`). Usar `getCanvas().toDataURL('image/png')` em vez disso (sem corte automático de borda, mas funciona).

Salvar dispara duas requisições em sequência: 1) nome → `POST/PUT /api/technician`, obtém o `id`; 2) se o canvas tiver desenho novo, `FormData` (`technicianId` + arquivo) → `POST/PUT /api/signature`.

## Integração nos documentos (Recibo, Ordem de Serviço, Orçamento)

Cada um dos três domínios (`Receipt`, `Order/WorkOrder`, `Quote/Quote`) ganhou um campo **próprio**, persistido:

```php
#[ORM\Column(options: ['default' => false])]
private bool $includeSignature = false;
```

**Decisão deliberada do usuário**: esse campo fica no banco (não é um parâmetro passado só na hora de baixar o PDF), porque o download do PDF é um `GET .../{id}/pdf` sem corpo — a única forma de "lembrar" a decisão entre a criação/edição do documento e o download posterior é persistir. Chegou a ser cogitado não persistir (mandar a flag só na hora do download, via query string ou mudando o endpoint pra `POST`), mas foi descartado a favor de manter no banco, igual ao resto do payload do documento.

Default do DTO de request é **`true`** (`ReceiptInputDTO`/`WorkOrderInputDTO`/`QuoteInputDTO::$includeSignature = true`) — o requisito é "ligado por padrão quando há assinatura cadastrada"; quem decide não mandar `true` quando não há assinatura é o frontend (switch desabilitado, ver abaixo), e o backend reforça de qualquer forma (ver próximo parágrafo).

### Resolver "a assinatura da empresa" — `TechnicianService::getCompanySignatureBase64()`

Método reutilizado pelos três `Service`s (`ReceiptService`, `WorkOrderService`, `QuoteService`, todos com `TechnicianService` injetado):

```php
public function getCompanySignatureBase64(Company $company): ?string
{
    $technician = $this->repository->findOneBy(['company' => $company]);
    $signature = $technician?->getSignature();

    if (!$signature || !$signature->getFileName()) {
        return null;
    }

    return $this->fileService->getBase64($this->getSignatureSubDir($company), $signature->getFileName());
}
```

Cada `Service::get{Receipt,Order,Quote}Document()` chama isso condicionalmente:
```php
$signatureBase64 = $entity->isIncludeSignature()
    ? $this->technicianService->getCompanySignatureBase64($company)
    : null;
```
Ou seja: mesmo com `includeSignature = true`, se a empresa não tiver técnico/assinatura cadastrada (ou o arquivo tiver sido removido depois), o método retorna `null` e o template simplesmente não desenha nada — dupla proteção, não depende só do frontend desabilitar o switch.

`*Document` (`ReceiptDocument`/`OrderDocument`/`QuoteDocument`) ganharam um parâmetro `?string $signatureBase64` e uma chave `'signature'` no array de `getData()`.

### Templates Twig

- `templates/pdf/receipt.html.twig` — já tinha `.signature-area`/`.signature-line` (sem imagem); ganhou `{% if signature %}<img src="{{ signature }}" ...>{% endif %}` dentro do bloco existente.
- `templates/pdf/order.html.twig` — já tinha a linha "Assinatura do Técnico"/"Assinatura do Cliente" (só texto); a imagem entra condicionalmente só no lado do Técnico, a assinatura do Cliente continua sem imagem (fora de escopo).
- `templates/pdf/quote.html.twig` — **não tinha nenhum bloco de assinatura** antes desta feature. Adicionado do zero, mesmo CSS/layout do Recibo (`.signature-area`/`.signature-line` copiados pro `<style>` deste template, já que cada PDF Twig é independente, sem CSS compartilhado), posicionado antes do `.footer`.

### Frontend — switch "Incluir assinatura"

Novo componente `front/src/components/ui/Switch.jsx` (não existia nenhum switch/toggle no projeto antes). Detalhe de implementação: o botão **não usa o atributo HTML `disabled`** quando desabilitado — usa `aria-disabled` + estilo visual + guarda no `onClick` — porque elementos com `disabled` de verdade não disparam `:hover` em todos os browsers, o que quebraria o tooltip explicativo. O tooltip reaproveita a receita Tailwind `group`/`group-hover:opacity-100` já usada (isolada, não exportada) em `Sidebar.jsx`.

Cada uma das três telas (`Receipts/Form.jsx`, `Order/Form.jsx`, `Quotes/Form.jsx`) busca `getTechnicians()` no mount e deriva `hasSignature = Boolean(data?.[0]?.signatureUrl)` — mesma checagem já usada em `Signature/Index.jsx`. O switch fica desabilitado (com tooltip "Cadastre uma assinatura em Documentos → Assinatura Digital para habilitar esta opção.") quando `hasSignature` é `false`; `INITIAL_DATA.includeSignature` começa `true`, mas o valor efetivo exibido/enviado é sempre `hasSignature && formData.includeSignature`.

**Achado técnico verificado em runtime**: `Order/Form.jsx` e `Quotes/Form.jsx` às vezes mandam o payload como `FormData` (quando há foto nova de item), e `FormData.append` converteria um booleano JS pra string `"true"`/`"false"` por padrão. Testado explicitamente mandando `'1'`/`'0'` (em vez do JS boolean bruto) — a denormalização do Symfony pro `bool $includeSignature` do DTO funciona corretamente com essas strings. `buildFormData` em ambos os arquivos trata qualquer valor booleano do payload como caso especial: `fd.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : value)`.

## O que falta / observações

- Não existe seleção de **qual** técnico assina um documento — sempre "o único técnico da empresa" (`findOneBy(['company' => $company])`, primeiro resultado). Se um dia existir gestão de múltiplos técnicos, essa resolução precisa mudar (provavelmente um `technicianId` por documento).
- Assinatura do Cliente (rótulo já existe no PDF da Ordem de Serviço) continua sem imagem — fora de escopo desta entrega.
- Mesma observação de outros módulos: arquivo físico da assinatura antiga (ao trocar/redesenhar) é removido corretamente (`SignatureService::upsert()`), mas segue o padrão geral do projeto de não ter testes de integração cobrindo isso — só testado manualmente.
