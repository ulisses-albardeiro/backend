# Ordem de Serviço (WorkOrder/OS) — Backend

Documentação da feature de Ordem de Serviço (OS) do **Meus Orçamentos**. Leia isto antes de mexer em `WorkOrder`, `WorkOrderItem`, `WorkOrderItemImage`, `WorkOrderMapper`, `WorkOrderService`, `WorkOrderController`, `WorkOrderItemImageController`/`WorkOrderItemImageService`, DTOs de OS, ou na geração do PDF da OS.

**Conversão de orçamento → OS**: a cópia dos dados (cliente, itens, bem associado etc.) acontece no **frontend** (`front/src/pages/panel/Order/Form.jsx`, função `importQuoteData`), disparada por `QuoteSelectionModal`. O backend não tem endpoint de "converter orçamento em OS" — é um `POST /api/work-order` normal, só que com `quoteId` preenchido e (desde a feature de fotos) `sourceQuoteItemId` por item para viabilizar a cópia de imagens (ver seção própria abaixo).

## Modelo de dados

### `WorkOrder` (`src/Entity/Order/WorkOrder.php`)

Campos: `id`, `code` (gerado automaticamente, formato `OS-DDMMYYYY-XXXX`, mesmo esquema do `Quote::$code`), `customer`/`company` (ManyToOne, obrigatórios), `quote` (ManyToOne opcional — referência à origem quando a OS foi criada a partir de um orçamento), `transaction` (OneToOne obrigatório, `cascade: ['persist','remove']` — toda OS gera automaticamente uma `Transaction` de receita, ver seção própria), `status` (enum `WorkOrderStatus`), `startDate`/`endDate` (`?DateTimeImmutable`), `createdAt`/`updatedAt`, `title`, `problemDescription`/`technicalReport` (`TEXT`), `equipment` (`?string`), `asset` (ManyToOne `CustomerAsset`, opcional), `workOrderItems` (`OneToMany` → `WorkOrderItem`, `cascade: ['persist','remove']`, `orphanRemoval: true`), `totalAmount` (int, centavos).

### `WorkOrderItem` (`src/Entity/Order/WorkOrderItem.php`)

Campos: `id`, `workOrder` (ManyToOne, obrigatório), `product`/`labor` (ManyToOne opcionais, mutuamente exclusivos por convenção do frontend), `description`, `quantity` (`DECIMAL(10,2)` como string), `unitPrice`/`totalPrice` (int, centavos — `totalPrice` calculado no mapper, não tem `#[PrePersist]` próprio como `QuoteItem`), `images` (`OneToMany` → `WorkOrderItemImage`, ver seção própria).

### Enum `WorkOrderStatus` (`src/Enum/Order/WorkOrderStatus.php`)

String enum, 7 casos: `PENDING`/`DRAFT`/`OPEN`/`IN_PROGRESS`/`COMPLETED`/`CANCELED`/`WAITING_PARTES` (sic — nome do case tem esse typo, o valor string é `waiting_parts` correto). `getLabel()` em pt-BR, sem `getColor()` (diferente de `QuoteStatus`).

### `WorkOrderRepository`

`findByIdAndCompany()` — mesmo padrão de `QuoteRepository`: escopo por empresa via `customer.company`, não via `workOrder.company` diretamente (mesma ressalva do doc de Quote: se `WorkOrder` e seu `Customer` puderem um dia pertencer a empresas diferentes, esse filtro passaria a estar errado).

## `WorkOrderMapper::toEntity()` — diff de itens por `id` (corrigido)

**Bug histórico corrigido**: até a feature de fotos, `toEntity()` no update removia **todos** os `WorkOrderItem` existentes e recriava do zero a cada `PUT`, mesmo editando um campo da OS sem relação com itens (ex.: só o status). Era inofensivo até então porque não havia nada pendurado no item além de referências a produto/labor — mas quebraria silenciosamente qualquer foto anexada, via `orphanRemoval: true` em cascata. Corrigido replicando o mesmo padrão de `QuoteMapper::toEntity()`:

1. Indexa os itens já persistidos (`$workOrder->getWorkOrderItems()`) por `id`.
2. Para cada item do DTO: se `itemDto->id` bate com um item existente, reaproveita a entidade; senão cria um `WorkOrderItem` novo.
3. Só remove os itens existentes que não vieram no DTO.

`WorkOrderItemInputDTO::$id` é o que permite esse casamento — mesmo aviso do doc de Quote: se o `id` vier errado ou ausente para um item que já existia, o mapper recria e perde o vínculo com as fotos antigas dele.

**Outro bug corrigido junto**: `WorkOrderService::update()` chamava `$this->mapper->toEntity($dto, $company, $workOrder, $customerAsset)` — os dois últimos argumentos posicionais estavam trocados em relação à assinatura real (`..., ?CustomerAsset $customerAsset, ?WorkOrder $workOrder = null`). Isso fazia o mapper nunca receber a entidade `WorkOrder` existente corretamente (ou estourar `TypeError`, dependendo dos valores). Corrigido para `toEntity($dto, $company, $customerAsset, $workOrder, $itemImageFiles)`. Sem essa correção, o diff-por-id do item 1 não teria efeito nenhum em updates reais.

## `WorkOrderService`

- `create()`/`update()` — chamam `WorkOrderMapper::toEntity()`, calculam `totalAmount` no próprio mapper (diferente de Quote, que tem `recalculateTotals()` na entidade). Ambos aceitam `array $itemImageFiles = []` (arquivos de foto por item) e repassam pro mapper — mesmo contrato de `QuoteService`.
- `create()` também gera automaticamente uma `Transaction` de receita (categoria "Serviços", criada sob demanda se não existir) via `createAutomaticTransaction()`; `update()` atualiza o valor/descrição dessa transação a partir do `WorkOrder` recalculado.
- `getOrderDocument()` — monta o `OrderDocument` usado pela geração de PDF, incluindo `photosByItemId` (mesmo padrão de `QuoteService::getQuoteDocument()`).

## `WorkOrderController` — Endpoints

| Rota | Método | Descrição |
|---|---|---|
| `GET /api/work-order` | GET | Lista as OS da empresa do usuário logado |
| `GET /api/work-order/{id}` | GET | Detalhe de uma OS |
| `POST /api/work-order` | POST | Cria; aceita JSON puro ou multipart com fotos por item (`items[i][images][]`) |
| `PUT /api/work-order/{id}` | PUT (ou `POST` + `_method=PUT`) | Atualiza; mesmo suporte a foto por item |
| `DELETE /api/work-order/{id}` | DELETE | Exclui (hard delete, cascata) |
| `GET /api/work-order/{id}/pdf` | GET | Gera e devolve o PDF |
| `DELETE /api/work-order-item-image/{id}` | DELETE | Remove uma foto específica de um item (controller próprio, `WorkOrderItemImageController`) |

Mesmo padrão de autenticação/escopo de `QuoteController`: `IS_AUTHENTICATED_FULLY` + `$user->getCompany()`.

## Fotos por item (`WorkOrderItemImage`)

Réplica de `docs/QUOTE.md` → "Fotos por item (`QuoteItemImage`)", com uma diferença central: **o diretório físico das fotos é compartilhado com o de orçamentos**, para viabilizar a cópia de foto ao criar OS a partir de um orçamento sem duplicar o arquivo.

### Modelo de dados

- **`WorkOrderItemImage`** (`src/Entity/Order/WorkOrderItemImage.php`) — espelha `QuoteItemImage`: `id`, `workOrderItem` (ManyToOne, `nullable: false`), `isMain` (bool), `sortOrder` (int), `path` (string 255 — só o nome do arquivo).
- `WorkOrderItem::$images` — `OneToMany`, `cascade: ['persist']`, `orphanRemoval: true` — mesmo motivo do `QuoteItem::$images`: criar OS + item novo + foto acontece tudo no mesmo request, sem `flush()` intermediário.
- Migration: `migrations/Version20260721032846.php` — tabela `work_order_item_image`, mesmo padrão de `quote_item_image`.

### Onde o arquivo fica salvo — diretório compartilhado com Quote

`company_[hash]/docs_images/<nome-gerado>`, servido por `FileService`. **Esse subdiretório é compartilhado com `QuoteItemImageService::getSubDir()`** (que também aponta para `docs_images`, renomeado de `quote_items` nesta mesma mudança). Decisão deliberada do usuário: como o mesmo arquivo físico pode pertencer a uma foto de orçamento **e** a uma foto de OS (ver seção de cópia abaixo), as duas entidades precisam resolver pro mesmo diretório — do contrário copiar a referência sem re-upload não funcionaria. Não houve migração de arquivos existentes ao renomear `quote_items` → `docs_images` porque, no momento da mudança, não havia dado real em produção nesse caminho (feature de fotos em orçamento é de 2026-07-20).

### Upload no mesmo create/update (não é endpoint separado)

Mesmo padrão de Quote: `POST /api/work-order` e `PUT /api/work-order/{id}` aceitam multipart com `items[{indice}][images][]`. `WorkOrderController::store/update` extraem `$request->files->get('items')` e repassam pra `WorkOrderService`→`WorkOrderMapper`. Mesma ressalva de PHP não popular `$_FILES` em PUT multipart real — resolvida globalmente em `public/index.php` (`enableHttpMethodParameterOverride()`), front manda `_method=PUT` via `FormData` quando há arquivo novo (`front/src/api/order/workOrder.js#updateOrder`).

### Cópia de fotos ao importar orçamento → OS (`sourceQuoteItemId`)

Quando o usuário usa "Importar dados de um orçamento" no front (`Order/Form.jsx#importQuoteData`), cada item copiado carrega `sourceQuoteItemId` = id do `QuoteItem` de origem, além das `images` já existentes (só para exibição imediata no form — o backend não confia nelas). No `WorkOrderMapper::toEntity()`, para cada item com `sourceQuoteItemId` preenchido:

1. Busca o `QuoteItem` de origem via `$em->find(QuoteItem::class, $id)`.
2. Valida escopo por empresa: `$sourceItem->getQuote()->getCompany()->getId() === $company->getId()` — nunca confia no id vindo do client sem essa checagem.
3. Chama `WorkOrderItemImageService::copyFromQuoteItem()`, que **não faz nenhum I/O de arquivo** — só cria uma nova linha `WorkOrderItemImage` por `QuoteItemImage` de origem, copiando `path`/`isMain`/`sortOrder`. Funciona sem re-upload porque `path` (só o nome do arquivo) resolve pro mesmo arquivo físico nos dois casos, já que o subdiretório é compartilhado (ver seção acima).

O item ainda pode receber upload de foto nova no mesmo request (`itemImageFiles[$index]['images']`), tratado depois da cópia, igual ao fluxo de Quote.

### Remoção de foto

`DELETE /api/work-order-item-image/{id}` → `WorkOrderItemImageService::removeImage()` — remove **só a linha do banco**. O arquivo físico nunca é apagado — mesma decisão deliberada de `QuoteItemImageService::removeImage()`. Como o mesmo arquivo pode ter uma linha em `quote_item_image` e outra em `work_order_item_image` (cópia), isso também significa que excluir a foto do lado da OS nunca afeta a exibição da mesma foto no orçamento de origem, e vice-versa — as duas linhas são independentes, só compartilham o `path`.

### Seção de fotos no PDF

`WorkOrderService::getOrderDocument()` monta `photosByItemId` (base64 via `FileService::getBase64()`, usando `WorkOrderItemImageService::getSubDir()`), passado pro `OrderDocument` e renderizado em `templates/pdf/order.html.twig` — mesma estrutura (`.item-photo-box`, caixa fixa 150×80) copiada de `quote.html.twig`.

## O que falta / observações

- Mesma limpeza de arquivo físico órfão pendente do lado de Quote (linha do banco removida, arquivo permanece — decisão deliberada, não é bug).
- `WorkOrderStatus::WAITING_PARTES` tem esse nome de case com erro de digitação (valor string `waiting_parts` está correto) — não corrigido aqui por não fazer parte do escopo da feature de fotos.
- Assim como o Quote, não investigado o contrato do app mobile para criação/edição de OS.
