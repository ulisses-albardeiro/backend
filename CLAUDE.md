# CLAUDE.md

Guia de arquitetura para trabalhar neste repositório. Leia isto antes de explorar o código do zero.

## Visão geral

Este é o **backend** do **Meus Orçamentos**, um SaaS de gestão para pequenas empresas: orçamentos, ordens de serviço, CRM de clientes, controle de estoque, financeiro e assinatura paga. API em Symfony/PHP, consumida pelo front web e pelo app mobile.

Faz parte de um monorepo maior:
- `../front` — front-end web (React/Vite). Tem seu próprio `CLAUDE.md`.
- `../mobile` — app Expo/React Native. Tem sua própria documentação (`mobile/docs/features/`).

## Stack e comandos

- **PHP 8.4+**, **Symfony 8**, **Doctrine ORM 3**, **MySQL 8**, autenticação **JWT** (`LexikJWTAuthenticationBundle`), PDF via **dompdf**.
- Ambiente local via Docker: `docker compose up -d --build` (roda `symfony serve` dentro do container `php`, não é PHP-FPM/Nginx).
- Migrations: dentro do container —
  ```bash
  docker compose exec php php bin/console doctrine:migrations:diff     # gera a partir do diff entidade↔schema
  docker compose exec php php bin/console doctrine:migrations:migrate  # aplica
  ```
- Testes: `docker compose exec php php bin/phpunit` (PHPUnit, só unitários hoje — ver seção "Testes" abaixo).

## Geração de arquivos via `bin/console make:*` — obrigatório

Sempre gerar o esqueleto via `bin/console make:*` (dentro do container) em vez de escrever esses arquivos à mão do zero. O projeto tem `symfony/maker-bundle` instalado — rodar `bin/console list make` pra ver a lista completa. Os mais relevantes aqui:

- **Entity**: `make:entity` — cria entidade nova ou adiciona campo a uma existente. Depois de qualquer mudança de entidade, gerar a migration correspondente (ver abaixo) — nunca só editar a entidade e deixar o schema do banco dessincronizado.
- **Migration**: `doctrine:migrations:diff` + `doctrine:migrations:migrate` (ver comandos acima). Revisar o SQL gerado antes de aplicar; não escrever migration à mão.
- **Controller**: `make:controller`
- **Command**: `make:command` (comandos de console ficam em `src/Command/`, ex.: os crons de assinatura em `src/Command/Subscription/`)
- **Fixtures**: `make:fixtures` (o projeto usa Zenstruck Foundry — `src/Factory/` + `src/Story/` trabalham junto com as fixtures)
- **Validator customizado**: `make:validator`
- **Voter de segurança**: `make:voter`
- **Subscriber/Listener de evento**: `make:subscriber`
- **Teste**: `make:test` / `make:unit-test` / `make:functional-test`

DTOs (`src/DTO/`), Mappers (`src/Mapper/`), Services (`src/Service/`) e Enums (`src/Enum/`) **não têm gerador no maker-bundle** — seguem o padrão manual já estabelecido no projeto (ver "Padrões de arquitetura" abaixo); ao criar um novo, copiar a estrutura de um exemplo já existente do mesmo tipo em vez de inventar um formato novo.

## Estrutura de pastas

```
src/
├── Controller/       # uma classe por recurso — recebe DTO via #[MapRequestPayload], chama Service, devolve JSON
├── Service/          # orquestração e regra de negócio; subpastas quando o domínio tem mais de um arquivo (Subscription/, Product/, Order/)
├── Mapper/           # DTO ⇄ Entity (toEntity()/toOutputDTO()) — única camada que constrói/atualiza Entity a partir de DTO
├── DTO/
│   ├── Request/      # input dos endpoints, validado via Assert\* (Symfony Validator), mensagens em pt-BR
│   └── Response/     # output, sempre readonly com construtor promovido
├── Entity/           # entidades Doctrine, subpasta por domínio quando há mais de uma entidade relacionada (Quote/, Product/, Order/, Subscription/, Customer/, Labor/)
├── Repository/        # queries customizadas (ex.: findByIdAndCompany) — resto vem de ServiceEntityRepository puro
├── Enum/             # enums string-backed, quase todos com getLabel() (pt-BR) e getColor() quando aparecem como badge no front
├── EventSubscriber/  # ex.: SubscriptionAccessSubscriber, roda em KernelEvents::CONTROLLER
├── Command/          # comandos de console (crons — sem scheduler/fila configurado, ver docs/SUBSCRIPTION.md)
├── Factory/, Story/, DataFixtures/  # Zenstruck Foundry — dados de teste/dev
└── Validator/        # constraints customizadas (ex.: validação de CPF/CNPJ)
```

## Padrões de arquitetura

- **Fluxo padrão de um endpoint**: `Controller` (`#[MapRequestPayload] XInputDTO`, chama `XService`) → `Service` (busca entidades relacionadas, chama `XMapper::toEntity()`, persiste/flush, chama `XMapper::toOutputDTO()`) → `Mapper` (única camada que toca a Entity a partir do DTO) → `Repository` (só quando a query precisa de mais que `find`/`findBy`).
- **Escopo por empresa (multi-tenant)**: toda entidade de domínio pertence a uma `Company`. Nunca buscar só por `id` — sempre um `findByIdAndCompany(id, company)` (ou equivalente) no repository. `$user->getCompany()` no controller é a fonte da empresa autenticada.
- **Dinheiro em centavos**: todo valor monetário é `int` em centavos — nunca `float`. Divisão por 100 é sempre na camada de exibição (frontend/PDF), nunca no banco/DTO.
- **Enums string-backed**: quase todo enum de domínio tem `getLabel()` (pt-BR) e às vezes `getColor()` (badge). DTOs de output expõem o valor do enum **e** o label já traduzido — o frontend nunca precisa mapear.
- **Upload de arquivo**: `FileService` (`src/Service/FileService.php`) é o único ponto de escrita/leitura em disco (`upload`, `remove`, `getPublicUrl`, `getBase64`). Pasta por empresa segue `company_[md5(company->getCreatedAt()->format('U'))]/<recurso>` — ver `docs/QUOTE.md` para um caso documentado, incluindo um bug de instabilidade nesse hash ainda não resolvido.
- **Mensagens de erro em pt-BR** nas constraints (`Assert\NotBlank(message: "...")`) — mesma convenção do frontend.
- **Migrations**: sempre via `doctrine:migrations:diff`, nunca escritas à mão.

## Autenticação e multi-tenant

- JWT via `LexikJWTAuthenticationBundle`; login em `POST /api/login_check` (campo `username`, não `email`).
- Toda rota autenticada usa `#[IsGranted('IS_AUTHENTICATED_FULLY')]` no controller.
- Bloqueio de acesso por assinatura vencida/inadimplente é um `EventSubscriber` global (`SubscriptionAccessSubscriber`) — ver `docs/SUBSCRIPTION.md`.

## Testes

PHPUnit, só testes unitários hoje (`tests/Unit/Service/`, mocks via `createMock`), sem testes funcionais/de integração. Rodar: `docker compose exec php php bin/phpunit`.

**Cobertura não está em dia**: `QuoteServiceTest.php` está quebrado agora (`ArgumentCountError` nos 5 testes — mocks desatualizados em relação ao construtor atual de `QuoteService`, que ganhou dependências novas ao longo do tempo sem o teste ser atualizado). Se for tocar em `QuoteService`, atualizar esse teste também.

## Documentação por módulo (`docs/`)

Cada feature relevante ganha um `.md` próprio em `docs/`, escrito para outra sessão/agente conseguir trabalhar nela sem reler todo o código — inclui bugs reais encontrados/corrigidos e decisões de design (com o porquê), não só o estado atual. Módulos documentados até agora:

- [`docs/SUBSCRIPTION.md`](docs/SUBSCRIPTION.md) — assinaturas pagas via Asaas (planos, trial, webhook, bloqueio de acesso).
- [`docs/QUOTE.md`](docs/QUOTE.md) — orçamentos (modelo de dados, PDF, fotos por item).

Nem todo módulo tem doc ainda (ex.: Product/Stock, Labor, Transaction/Category, Customer, Order/WorkOrder) — sendo adicionados aos poucos. Ao fazer uma mudança grande num módulo sem doc, considere criar um.
