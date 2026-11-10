# MAAK — Symfony

MAAK is an educational marketplace connecting university students who teach with school students and their parents.


## Architecture

- Symfony 8.1, PHP 8.4+
- Twig server-side rendering
- Symfony UX: Turbo, Stimulus, Twig Components, Live Components
- Symfony UX Toolkit (Shadcn kit) for locally owned UI components
- Doctrine ORM and PostgreSQL 16
- Symfony Security with server-side sessions
- Simulated payment for the MVP


## Product roles

- **Parent:** owns the account, manages child profiles, purchases courses, and monitors learning.
- **Teacher:** a university student who creates and manages courses.
- **Student:** a child profile managed by a parent; no independent login in the MVP.
- **Administrator:** manages platform operations, users, courses, and moderation.

## MVP

Public recorded courses, course discovery, teacher course creation, parent/child management, simulated purchases, course access, learning progress, and administration.

Private tutoring, live video meetings, chat, subscriptions, native mobile apps, AI tutoring, and advanced recommendation/social features are outside the initial MVP.


## Local development

1. Install PHP 8.4+, Composer 2, Symfony CLI, Git and PostgreSQL 16.*.
2. Follow [the setup guide](docs/development-setup.md).
3. Start PostgreSQL with `docker compose up -d database`.
4. Install dependencies with `composer install`.
5. Run the app with `symfony server:start`.


