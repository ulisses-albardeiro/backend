# My Budgets — Backend

## Description
- Backend API for managing freelancers and small businesses.

## Technical stack
- PHP 8+, Symfony 8+, MySQL

## Requirements
- PHP 8.4+ with extensions: `pdo`, `pdo_mysql`, `fileinfo`
- Composer
- MySQL 8 (adjust `DATABASE_URL`)
- (Optional) Docker / Docker Compose for local environment

## Installation (local)
```bash
composer install
cp .env .env.local
# adjust DATABASE_URL in .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

```

## Test
```bash
php bin/phpunit

```
