# MAAK — Symfony

MAAK is an educational marketplace connecting university students who teach with school students and their parents.

This repository contains the **new Symfony implementation**. It is separate from the original `MAAK` repository, which remains preserved as the historical Laravel implementation.

## Architecture

- Symfony 8.1, PHP 8.4+
- Twig server-side rendering
- Symfony UX: Turbo, Stimulus, Twig Components, Live Components
- Symfony UX Toolkit (Shadcn kit) for locally owned UI components
- Doctrine ORM and PostgreSQL 16
- Symfony Security with server-side sessions
- Simulated payment for the MVP

The React/TanStack project in [maak_front](https://github.com/almiaaziz/maak_front) is a visual and functional reference, not a separate frontend for this application.

## Product roles

- **Parent:** owns the account, manages child profiles, purchases courses, and monitors learning.
- **Teacher:** a university student who creates and manages courses.
- **Student:** a child profile managed by a parent; no independent login in the MVP.
- **Administrator:** manages platform operations, users, courses, and moderation.

## MVP

Public recorded courses, course discovery, teacher course creation, parent/child management, simulated purchases, course access, learning progress, and administration.

Private tutoring, live video meetings, chat, subscriptions, native mobile apps, AI tutoring, and advanced recommendation/social features are outside the initial MVP.

## Repository structure

Business code is organized by domain under `src/`:

`Identity`, `Family`, `Teacher`, `Course`, `Catalogue`, `Checkout`, `Enrollment`, `Learning`, `Admin`, `Notification`, and `Shared`.

See [docs/architecture.md](docs/architecture.md) and [docs/development-setup.md](docs/development-setup.md).

## Local development

1. Install PHP 8.4+, Composer 2, Symfony CLI, Git, and Docker Desktop.
2. Follow [the setup guide](docs/development-setup.md).
3. Start PostgreSQL with `docker compose up -d database`.
4. Install dependencies with `composer install`.
5. Run the app with `symfony server:start`.

## Repository boundaries

- This repository: new Symfony implementation.
- [MAAK](https://github.com/almiaaziz/MAAK): original project; do not use it as the destination for Symfony work.
- [maak_front](https://github.com/almiaaziz/maak_front): UI/UX reference only.

## UI migration status (10 November 2026)

The first Twig migration pass is now in place. The React application remains the visual reference and is not loaded by Symfony.

Migrated server-rendered views:
- Public landing page, catalogue, course details, teacher profiles and About page.
- Login, account-type selection, parent/teacher registration and password-reset views.
- Parent, teacher and administrator dashboard shells, plus course creation, checkout, payment status and learning-player views.
- Shared navigation, footer, course cards, responsive layout and MAAK visual tokens.
- Original hero and course thumbnail assets copied into `public/images/maak/`.

The catalogue content is currently seeded as presentation data in `MarketplaceCatalog`; it is not yet persisted in Doctrine. Parent/teacher/admin dashboard areas are UI scaffolds where their domain modules are not implemented yet. Registration, login, email verification and password reset continue to use the existing Symfony Security and token services.

The visual layer uses Twig templates and a locally owned component style system inspired by the React shadcn/ui design. Symfony UX Turbo, Stimulus and Twig Components remain available in the application. The Tailwind bundle is configured; the current CSS is kept browser-ready so the interface can be previewed even when the Tailwind binary cannot be downloaded in a restricted environment.

### Verify the migrated UI

```bash
php bin/console lint:twig templates
php bin/console lint:container
php bin/console debug:router
symfony server:start
```

To build Tailwind assets in an environment where the binary download is available:

```bash
php bin/console tailwind:build --minify
```

