# Orçamentos (Quote) — Backend

Documentação completa da feature de Orçamentos do **Meus Orçamentos**. Leia isto antes de mexer em `Quote`, `QuoteItem`, `QuoteItemImage`, `QuoteMapper`, `QuoteService`, `QuoteController`, `QuoteItemImageController`/`QuoteItemImageService`, DTOs de Quote, ou na geração do PDF do orçamento.

**Fora de escopo deste documento**: geração de Ordem de Serviço (OS) a partir de um orçamento. `Quote::$workOrders` existe só como relação inversa (uma OS pode referenciar opcionalmente um orçamento via FK), mas toda a cópia de dados orçamento→OS acontece no **frontend** (`front/src/pages/panel/Order/Form.jsx`, função `importQuoteData`) — não há service nem endpoint de conversão no backend. Não documentado aqui por decisão explícita (escopo restrito a Orçamentos).

## Modelo de dados

### `Quote` (`src/Entity/Quote/Quote.php`)

Campos: `id`, `code` (gerado automaticamente, ver abaixo), `customer` (ManyToOne `Customer`), `status` (enum `QuoteStatus`), `date`/`due_date` (`DateTimeImmutable`, nomes de propriedade inconsistentes — a coluna interna é `due_date` com underscore, mas o getter/setter é `getDueDate()`/`setDueDate()`, camelCase, como o resto do código), `subtotal` (int, centavos), `discountType` (enum `DiscountType`), `discountValue`/`shippingValue` (`?int`, centavos), `totalAmount` (int, centavos), `description`/`notes`/`internalNotes` (`?string`, `TEXT`), `company` (ManyToOne, obrigatório), `asset` (ManyToOne `CustomerAsset`, opcional), `quoteItems` (`OneToMany` → `QuoteItem`, `cascade: ['persist','remove']`, `orphanRemoval: true`), `receipts` (`OneToMany` → `Receipt`, sem cascade — um Receipt pode referenciar opcionalmente um Quote), `workOrders` (`OneToMany` → `WorkOrder`, sem cascade — ver aviso de escopo acima).

Geração do `code` (`#[ORM\PrePersist]`):
```php
public function setInitialValues(): void
{
    if ($this->code === null) {
        $year = (new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')))->format('dmY');
        $uniquePart = strtoupper(substr(uniqid(), -4));
        $this->code = sprintf('ORC-%s-%s', $year, $uniquePart);
    }
}
```
Formato `ORC-DDMMYYYY-XXXX` — os 4 últimos caracteres de `uniqid()` em uppercase, não é sequencial. Risco teórico (não observado na prática) de colisão em criações concorrentes muito próximas.

`recalculateTotals()` — chamado por `QuoteService::create/update` depois de montar os itens:
```php
public function recalculateTotals(): void
{
    $subtotal = 0;
    foreach ($this->quoteItems as $item) {
        $item->calculateTotal();
        $subtotal += $item->getTotalPrice();
    }
    $this->subtotal = $subtotal;

    $discount = $this->discountValue ?? 0;
    if ($this->discountType === DiscountType::PERCENTAGE) {
        $value = $this->discountValue ?? 0;
        $discount = (int) round($subtotal * ($value / 100));
    }

    $this->totalAmount = $subtotal - $discount + ($this->shippingValue ?? 0);
}
```
Quando `discountType` é `FIXED`, o desconto é `discountValue` direto (já em centavos); quando `PERCENTAGE`, recalcula sobre o subtotal. O frontend replica essa mesma fórmula em JS (`QuoteSummary.jsx`) só para exibir o total em tempo real antes de salvar — o valor que vale de verdade é sempre o recalculado aqui no backend.

### `QuoteItem` (`src/Entity/Quote/QuoteItem.php`)

Campos: `id`, `quote` (ManyToOne, obrigatório), `description`, `quantity` (`DECIMAL(10,2)` como string), `unitPrice` (int, centavos), `totalPrice` (int, calculado, ver abaixo), `product`/`labor` (ManyToOne opcionais, mutuamente exclusivos por convenção do frontend — nunca ambos preenchidos), `images` (`OneToMany` → `QuoteItemImage`, ver seção própria abaixo).

```php
#[ORM\PrePersist]
#[ORM\PreUpdate]
public function calculateTotal(): void
{
    $this->totalPrice = (int) round($this->unitPrice * (float) $this->quantity);
}
```

### Enums

`src/Enum/QuoteStatus.php` — string enum, 7 casos: `DRAFT`/`PENDING`/`SENT`/`EXPIRED`/`ACCEPTED`/`REJECTED`/`CANCELED`. `getLabel()` (Rascunho/Pendente/Enviado/Expirado/Aprovado/Recusado/Cancelado) e `getColor()` (gray/amber/blue/orange/green/red/slate) — usados direto no badge de status da listagem no frontend, sem mapeamento próprio lá.

`src/Enum/DiscountType.php` — string enum, 3 casos: `NONE`/`FIXED`/`PERCENTAGE`. `getLabel()` ("Sem Desconto"/"Valor Fixo (R$)"/"Percentual (%)") e `getSymbol()` (``/`R$`/`%`).

**Pegadinha real (frontend web desalinhado, ainda não corrigida)**: o `<Select>` de status em `QuoteSummary.jsx` só oferece `draft`, `sent`, `accepted`, `declined`, `expired` — falta `pending` e `canceled`, e usa `declined`, que **não existe** no enum real (`REJECTED`). Se um dia isso for corrigido, o valor certo do enum é `rejected`, não `declined`.

### `QuoteItemRepository` / `QuoteRepository`

`QuoteRepository::findByIdAndCompany()` é o único método customizado:
```php
public function findByIdAndCompany(int $id, Company $company): ?Quote
{
    return $this->createQueryBuilder('q')
        ->join('q.customer', 'c')
        ->where('q.id = :id')
        ->andWhere('c.company = :company')
        ->setParameter('id', $id)
        ->setParameter('company', $company)
        ->getQuery()
        ->getOneOrNullResult();
}
```
**Atenção**: o escopo por empresa é feito via `customer.company`, não via `quote.company` diretamente (embora `Quote` também tenha campo `company` próprio, não-nullable, redundante com o do customer). Se um dia um `Quote` e seu `Customer` puderem pertencer a empresas diferentes (não deveria ser possível hoje), esse método passaria a filtrar errado.

## DTOs

`QuoteInputDTO` (`src/DTO/Request/Quote/QuoteInputDTO.php`): `customerId` (int, obrigatório), `date`/`dueDate` (`DateTimeImmutable`, obrigatórios), `discountType` (string, default `'none'`), `discountValue`/`shippingValue` (int, default `0`), `description`/`internalNotes`/`notes` (`?string`), `assetId` (`?int`, default `null` — precisa do default explícito, ver seção de Fotos abaixo sobre por quê), `status` (`QuoteStatus`, obrigatório), `items` (`QuoteItemInputDTO[]`, `#[Assert\Count(min: 1)]` — orçamento sempre precisa de ao menos 1 item).

`QuoteItemInputDTO` (`src/DTO/Request/Quote/QuoteItemInputDTO.php`): `id` (`?int`, default `null` — usado para casar item existente no update, ver seção do Mapper), `description` (obrigatório), `quantity` (string, obrigatório), `unitPrice` (int, obrigatório), `laborId`/`productId` (`?int`, default `null`).

`QuoteOutputDTO`/`QuoteItemOutputDTO` (`src/DTO/Response/Quote/`) — readonly, espelham os campos acima mais os derivados (`statusLabel`, `statusColor`, `customerName`, `assetName`, `laborName`/`laborUnit`, `productName`/`productUnit`). `QuoteItemOutputDTO.images` é a lista de fotos do item (ver seção própria).

## `QuoteMapper` (`src/Mapper/Quote/QuoteMapper.php`)

`toEntity()` monta/atualiza a entidade a partir do `QuoteInputDTO`. Ponto mais importante do mapper — **diff de itens por `id` em vez de recriar tudo a cada update**:

1. Indexa os itens já persistidos (`$quote->getQuoteItems()`) por `id`.
2. Para cada item do DTO: se `itemDto->id` bate com um item existente, **reaproveita a entidade** (atualiza campos in-place); senão cria um `QuoteItem` novo.
3. Só remove (`$quote->removeQuoteItem()` — hard delete, `orphanRemoval: true`) os itens existentes que **não vieram** no DTO, ou seja, que o usuário excluiu da lista antes de salvar.

**Isso corrigiu um bug real**: antes, `toEntity()` removia **todos** os itens existentes e recriava do zero em todo `PUT`, mesmo editando um campo do orçamento que não tinha nada a ver com itens (ex: data de vencimento). Inofensivo antes de existirem fotos por item, mas depois de `QuoteItem→QuoteItemImage` (também `orphanRemoval: true`), isso apagaria as fotos de **todos** os itens em qualquer edição do orçamento. `QuoteItemInputDTO::$id` é o que permite esse casamento — é o único jeito de o backend saber "esse item já existe"; se o `id` vier errado ou ausente para um item que já existia, o mapper trata como item novo e recria, perdendo o vínculo com as fotos antigas dele.

Tratamento de `laborId`/`productId`: usa `$em->getReference()` (proxy sem carregar do banco) em vez de buscar a entidade completa — mais barato, mas significa que um `laborId`/`productId` inválido só falha na hora do `flush()` (constraint de FK), não antes.

`toOutputDTO()` monta o DTO de resposta, incluindo `images` de cada item via `QuoteItemImageService::formatImages()` (ver seção de Fotos).

## `QuoteService` (`src/Service/QuoteService.php`)

Orquestração simples, sem lógica de negócio própria além de delegar para mapper/repository:
- `listAllByCompany()` — lista ordenada por `date DESC`.
- `getByIdAndCompany()` — `404 QUOTE_NOT_FOUND` se não achar.
- `create()`/`update()` — chamam `QuoteMapper::toEntity()`, depois `$quote->recalculateTotals()`, depois `flush()`. Ambos aceitam `array $itemImageFiles = []` (arquivos de foto por item, ver seção de Fotos) e repassam pro mapper.
- `delete()` — hard delete direto (`$em->remove($quote)`), cascata via `orphanRemoval` cuida de itens e fotos.
- `getQuoteDocument()` — monta o `QuoteDocument` usado pela geração de PDF (ver seção própria).

## `QuoteController` (`src/Controller/QuoteController.php`) — Endpoints

| Rota | Método | Descrição |
|---|---|---|
| `GET /api/quote` | GET | Lista os orçamentos da empresa do usuário logado |
| `GET /api/quote/{id}` | GET | Detalhe de um orçamento |
| `POST /api/quote` | POST | Cria; aceita JSON puro ou multipart com fotos por item (`items[i][images][]`) |
| `PUT /api/quote/{id}` | PUT (ou `POST` + `_method=PUT`) | Atualiza; mesmo suporte a foto por item |
| `DELETE /api/quote/{id}` | DELETE | Exclui (hard delete, cascata) |
| `GET /api/quote/{id}/pdf` | GET | Gera e devolve o PDF |
| `DELETE /api/quote-item-image/{id}` | DELETE | Remove uma foto específica de um item (controller próprio, `QuoteItemImageController`) |

Não existe rota de duplicar orçamento, mudar status isoladamente, nem converter em OS — tudo isso, se existir, é feito editando o orçamento inteiro via `PUT` normal (mudar `status` é só mais um campo do mesmo payload).

Todos exigem `IS_AUTHENTICATED_FULLY`; escopo por empresa é sempre via `$user->getCompany()` passado para o service/repository — nunca confie em `id` da URL sem esse filtro.

## Geração de PDF

`GET /api/quote/{id}/pdf` → `QuoteController::downloadPdf()` → `QuoteService::getQuoteDocument()` monta um `QuoteDocument` (`src/Service/Pdf/Documents/QuoteDocument.php`) com o `QuoteOutputDTO`, `CompanyOutputDTO` (logo em base64 via `FileService::getBase64()`) e `CustomerOutputDTO` → `PdfGeneratorService::generate()` renderiza `templates/pdf/quote.html.twig` via Twig e converte pra PDF com **dompdf**.

Seções do template: cabeçalho (logo + dados da empresa + status), dados do cliente/endereço, bem associado (se houver `assetName`), descrição/objetivo (se houver), tabela de itens, seção de fotos (ver abaixo), totais (subtotal/desconto/frete/total), observações, rodapé com validade.

## Fotos por item (`QuoteItemImage`)

Feature que permite anexar múltiplas fotos a um item de orçamento (documentar trabalho realizado, peça, defeito, etc.).

### Modelo de dados

- **`QuoteItemImage`** (`src/Entity/Quote/QuoteItemImage.php`) — espelha `ProductImage`/`Product`. Campos: `id`, `quoteItem` (ManyToOne, `nullable: false`), `isMain` (bool), `sortOrder` (int), `path` (string 255 — só o nome do arquivo, não o caminho completo).
- `QuoteItem::$images` — `OneToMany`, `cascade: ['persist']` (diferente de `Product→ProductImage`, que não cascera persist — aqui precisa, porque criar orçamento + item novo + foto acontece tudo no mesmo request, sem `flush()` intermediário) + `orphanRemoval: true`.
- Migration: `migrations/Version20260720185421.php` — tabela `quote_item_image`, mesmo padrão de `product_image`.

### Onde o arquivo fica salvo

`company_[md5(company->getCreatedAt()->format('U'))]/quote_items/<nome-gerado>` — mesmo padrão já usado por `Company::getSubDir()` (logo) e `ProductService::getSubDir()` (produtos), só troca o sufixo (`QuoteItemImageService::getSubDir()`). **Decisão explícita do usuário: não trocar esse padrão por algo baseado em ID da empresa** mesmo sendo mais simples — o hash já está em produção, trocar exigiria migrar arquivos existentes.

**Achado, não resolvido de propósito**: esse hash se mostrou instável em pelo menos um teste manual nessa sessão — `format('U')` do mesmo `createdAt` retornou dois valores diferentes (offset de exatamente 3h, o de America/Sao_Paulo) em momentos diferentes do mesmo processo PHP de longa duração (`symfony serve`). Causa raiz não isolada. Efeito prático se acontecer de novo: a imagem existe no disco mas o app procura na pasta errada — aparece vazia, **sem nenhum erro** (`FileService::getBase64()`/`getPublicUrl()` retornam `''` silenciosamente para arquivo não encontrado). Se voltar a acontecer, comece verificando `date_default_timezone_get()` ao longo do request antes de suspeitar de código novo.

### Upload no mesmo create/update (não é endpoint separado)

Decisão deliberada do usuário: `POST /api/quote` e `PUT /api/quote/{id}` aceitam multipart com `items[{indice}][images][]` — o **mesmo índice** usado pelo objeto do item (`items[{indice}][description]`, etc.). `QuoteController::store/update` recebem `Request $request` só para extrair `$request->files->get('items')` e repassar para `QuoteService`→`QuoteMapper`.

**Achado técnico que evitou reescrever o parsing do payload**: o `RequestPayloadValueResolver` do Symfony (por trás do `#[MapRequestPayload]`) já lê `$request->request->all()` (que o PHP popula sozinho a partir de campos `items[0][description]=...` em notação de colchetes) quando o content-type não é JSON — não precisou de parsing manual, o mesmo `QuoteInputDTO $dto` funciona igual para JSON puro e multipart. Validado com `curl` misturando os dois formatos no mesmo endpoint.

**Retrocompatibilidade confirmada**: o app mobile continua mandando `POST`/`PUT` com `Content-Type: application/json` puro, sem campo de imagem — funciona sem nenhuma mudança. (Aliás, à parte dessa feature: o formulário de orçamento do mobile hoje manda campos que não correspondem ao `QuoteInputDTO` real — `customer`/`validUntil` em vez de `customerId`/`dueDate`, sem `date`/`status`/`discountType`, com desconto por item em vez de no orçamento. Não investigado a fundo nem corrigido aqui, escopo era só a feature de fotos — mas é um sinal de que o cliente mobile pode estar dessincronizado do contrato atual da API.)

### PHP não popula `$_FILES` em PUT multipart

Limitação do **PHP em si**: `$_FILES`/`$_POST` só são populados a partir do corpo multipart quando o método real da requisição é `POST` — confirmado com teste isolado (`curl -X PUT ... -F arquivo=@foto.jpg` contra um script PHP puro: `$_FILES` chegou vazio; o mesmo `-X POST` chegou populado). Resolvido em `public/index.php`: `Request::enableHttpMethodParameterOverride();` — só muda comportamento para requisições que mandam explicitamente um campo `_method` (o frontend envia `POST` com `_method=PUT` quando o corpo é `FormData`). Requisições sem esse campo continuam se comportando exatamente como antes — mudança aditiva.

**Esse mesmo problema já existe hoje em outros 3 fluxos do sistema, não corrigidos**: `updateCompany` (logo), `updateProduct` (imagens), `updateBrand` (logo) — todos mandam `PUT` com `FormData` quando o usuário troca a imagem numa edição, sem `_method`. Provavelmente nunca notado. Se for corrigir, o padrão é o mesmo usado aqui.

### GD — necessário para PNG no PDF

O dompdf só embute **PNG** através da extensão GD — JPEG embute direto (`Cpdf::addJpegFromFile`, sem GD). Sem GD, gerar o PDF de um orçamento com foto PNG quebra com `"The PHP GD extension is required, but is not installed."`. Adicionado ao `Dockerfile`: `libjpeg-turbo-dev`/`freetype-dev` + `docker-php-ext-configure gd --with-freetype --with-jpeg` + `gd` no `docker-php-ext-install`. **Só vale se a produção builda a partir desse Dockerfile** — se o PHP de produção roda fora do Docker, reinstalar GD manualmente no servidor real.

### Validação e limites de upload

`FileService::upload()` agora checa `$file->isValid()` antes de processar — sem isso, um upload rejeitado pelo PHP (maior que `upload_max_filesize`) chegava com `tmp_name` vazio, e chamar `guessExtension()` nesse estado quebrava com um erro de mime type sem sentido, capturado como 500 genérico. Agora lança `\InvalidArgumentException` com a mensagem real do PHP, e `QuoteController` devolve `400` legível. Vale para **qualquer** consumidor de `FileService::upload()`, não só orçamento.

Limites subidos em `docker/php/php.ini`: `upload_max_filesize` 2M→10M, `post_max_size` 8M→40M (soma de todos os arquivos + campos de um mesmo request). Mesma ressalva do GD: só vale se a produção usa esse Dockerfile.

### Seção de fotos no PDF

`QuoteService::getQuoteDocument()` monta `photosByItemId` (array `[quoteItemId => string[]]`, base64 via `FileService::getBase64()`), **separado** do DTO da API (`QuoteItemOutputDTO.images[].url` continua só a URL pública, leve — base64 só é montado na hora do PDF). Template: cada foto numa caixa de tamanho **fixo** (`.item-photo-box`, 150×80 — igual ao logo), imagem centralizada sem distorcer. Número do item é calculado pela posição na lista (`loop.index`), não é campo persistido — a relação real imagem↔item já é garantida pela FK.

**Bug de layout corrigido**: o título do item era `<strong>...</strong><br>` (inline) — o dompdf calculava a altura da linha de forma ambígua e a foto de baixo sobrepunha o texto do item seguinte. Corrigido trocando por um `<div>` (bloco próprio) e envolvendo cada foto numa caixa de tamanho fixo com `overflow: hidden`.

### Remoção de foto

`DELETE /api/quote-item-image/{id}` → `QuoteItemImageService::removeImage()` — valida que a imagem pertence a um item de um orçamento da empresa do usuário (404 se não), remove **só a linha do banco**. O arquivo físico no disco **não é apagado** (decisão deliberada — ver "O que falta").

## Soft delete / versionamento de item — avaliado e descartado por ora

Foi discutida a ideia de soft delete em `QuoteItem` para dar suporte a um futuro versionamento. Decisão: não implementar agora — soft delete resolveria "o que aconteceu com um item removido", não o bug do Mapper acima (que era sobre itens **mantidos**); "versionamento" de verdade normalmente implica snapshot do orçamento inteiro a cada edição, não só evitar apagar a linha do item. Revisitar só se houver requisito concreto de histórico.

## O que falta / observações

- Limpeza de arquivo físico órfão quando uma imagem é removida (linha do banco some, arquivo continua no disco).
- Investigar a causa raiz do drift de `md5(createdAt->format('U'))` mencionado acima.
- Corrigir o bug de PUT-multipart-perde-arquivo em `updateCompany`/`updateProduct`/`updateBrand`.
- Alinhar o `<Select>` de status do frontend web com os valores reais de `QuoteStatus` (falta `pending`/`canceled`, usa `declined` em vez de `rejected`).
- Verificar/corrigir o contrato de `QuoteInputDTO` consumido pelo app mobile (campos e nomes divergentes, ver seção de Fotos acima).
- Soft delete / versionamento de item — ver seção própria acima.
