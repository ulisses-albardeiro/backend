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

## Installation (Docker)
```bash
# Copy and adjust environment variables (optional)
cp .env .env.local

# Build and start containers
docker compose up -d --build

# Run migrations
docker compose exec php php bin/console doctrine:migrations:migrate

# Generate JWT keys (first time only)
docker compose exec php php bin/console lexik:jwt:generate-keypair

```

The API will be available at `http://localhost:8000`.

To connect to MySQL from a GUI tool use host `127.0.0.1`, port `3306`, user `root`, password `root`.

### Useful commands
```bash
# Run a Symfony console command
docker compose exec php php bin/console <command>

# Access the PHP container shell
docker compose exec php sh

# View logs
docker compose logs -f

# Stop containers
docker compose down

# Stop containers and remove database volume
docker compose down -v
```

## Test
```bash
php bin/phpunit

```
