# Local Development Setup

These instructions target Windows PowerShell and Docker Desktop.

## 1. Prerequisites

Install and verify PHP 8.4+, Composer 2, Symfony CLI, Git, and Docker Desktop with Docker Compose.

Enable the PHP extensions required by the project, especially `pdo_pgsql`, `ctype`, `iconv`, `intl`, `mbstring`, `openssl`, and `xml`.

```powershell
php -v
php -m
composer --version
symfony version
docker --version
docker compose version
symfony check:requirements
```

Symfony 8 requires PHP 8.4 or newer. If your terminal reports PHP 8.2, update PHP and PATH before continuing.

## 2. Clone the repository

```powershell
git clone https://github.com/almiaaziz/maak_symfony.git
cd maak_symfony
git checkout feature/symfony-foundation
```

## 3. Install PHP dependencies

```powershell
composer install
```

Commit the generated `composer.lock` after dependency resolution. Do not commit `vendor/`.

## 4. Start PostgreSQL

Make sure Docker Desktop is running:

```powershell
docker compose up -d database
docker compose ps
```

Local database settings: database `maak`, username `maak`, password `maak_dev_password`, host `127.0.0.1:5432`. These credentials are for local development only.

## 5. Initialize Tailwind

```powershell
php bin/console tailwind:init
php bin/console tailwind:build
```

For active development, use a separate terminal:

```powershell
php bin/console tailwind:build --watch
```

## 6. Install Shadcn UI Toolkit recipes

The Toolkit copies editable Twig components and Stimulus controllers into this repository. Symfony currently marks the Shadcn kit as work in progress, so test each recipe against this app and keep generated code under version control.

```powershell
php bin/console ux:install button --kit=shadcn
php bin/console ux:install card --kit=shadcn
php bin/console ux:install input --kit=shadcn
php bin/console ux:install label --kit=shadcn
```

If the installer prints additional Composer or import-map commands, run those too. Review generated files before committing.

## 7. Run the application

```powershell
symfony server:start
```

Open the local URL printed by Symfony CLI. The first page should display the MAAK foundation landing page.

## 8. Database migrations

No business entities or migrations are included yet. Once Identity and Family entities are implemented:

```powershell
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## Troubleshooting

- If Composer reports a PHP platform error, run `where.exe php` to see which PHP executable is first in PATH.
- If PostgreSQL does not start, inspect `docker compose logs database`.
- If `pdo_pgsql` is missing, enable it in the active `php.ini`, then reopen PowerShell.
- If Tailwind cannot download its binary, check the network and rerun `php bin/console tailwind:init`.
