# MAAK — Initial Architecture

## 1. Architectural style

MAAK is implemented as a **modular monolith** with Symfony 8. The application owns its routing, business logic, persistence, authentication, and server-side rendering in one deployable codebase.

The primary request flow is:

`Browser → Symfony route/controller → application/domain services → Doctrine repositories → PostgreSQL`

Twig renders the pages. Symfony UX adds focused interactivity without turning the project into a separate React single-page application.

### Technology decisions

| Concern | Decision |
|---|---|
| Framework | Symfony 8 |
| Server-rendered UI | Twig |
| Interactivity | Symfony UX: Turbo, Stimulus, Twig Components, Live Components when justified |
| UI component starting point | Symfony UX Toolkit, Shadcn kit |
| ORM | Doctrine ORM |
| Database | PostgreSQL |
| Authentication | Symfony Security, server-side sessions |
| MVP payment | Simulated checkout |

The React/TanStack frontend is a reference for layout, navigation, and user experience. Rebuild the relevant screens in Twig; do not embed or deploy it as a separate frontend for this architecture.

## 2. Suggested code organization

Organize code by business capability rather than creating one large collection of generic controllers and services.

```text
src/
  Identity/
    Entity/
    Repository/
    Service/
    Controller/
  Family/
  Teacher/
  Course/
  Catalogue/
  Checkout/
  Enrollment/
  Learning/
  Admin/
  Notification/
  Shared/

templates/
  layouts/
  components/
  public/
  auth/
  parent/
  teacher/
  student/
  admin/

assets/
  controllers/
  styles/

migrations/
tests/
```

The folders shown inside each module are a suggested starting convention, not a requirement to create every folder immediately. Keep module dependencies explicit and avoid circular dependencies.

## 3. Main domain concepts

### Identity

Represents authenticated platform accounts and their credentials, account status, and role/permissions.

### Family

Represents the parent-child relationship and child profiles managed by a parent.

### Teacher

Represents teacher-specific profile data, university information, and verification status.

### Course

Owns course authoring and course structure, including sections and lessons, plus publication/moderation state.

### Catalogue

Provides public course discovery, browsing, and course detail pages.

### Checkout

Handles the simulated purchase flow and its outcome. Do not integrate a real payment provider in the MVP unless the product requirements change.

### Enrollment

Connects a successful purchase to the account and the children who are allowed to access the purchased course.

### Learning

Tracks each child's course access and learning progress.

### Admin

Provides administrative workflows such as user/course management and moderation.

### Notification

Centralizes in-app and email notification behavior as it is introduced.

### Shared

Contains only genuinely cross-cutting primitives. Avoid turning it into a dumping ground for business logic.

## 4. Important business rules

1. A parent owns the authenticated account used to manage their family.
2. A parent can create and manage child/student profiles.
3. A student is a child profile, not an independently authenticated user in the MVP.
4. Course purchases are made by the parent account.
5. Access to a purchased course can be assigned to the parent's eligible children.
6. Learning progress is tracked per child, not merely per purchasing parent.
7. Teachers create and manage courses; publication may require moderation.
8. Checkout is simulated in the first version.

Enforce these rules in application/domain services and database constraints where appropriate. Do not rely only on hiding buttons in Twig.

## 5. Identity and profile persistence

Keep the distinction between an authenticated account and role-specific profile data explicit.

A practical relational starting point is:

- `users`: credentials, email, account status, and authentication identity.
- `parent_profiles`: parent-specific fields, with a one-to-one foreign key to `users`.
- `teacher_profiles`: teacher-specific fields and verification data, with a one-to-one foreign key to `users`.
- `admin_profiles`: only if administrators need profile fields beyond roles/permissions.
- `student_profiles`: child information and a foreign key to the owning parent account; no login credentials in the MVP.

Use Symfony Security roles/authorization for access control. Do not introduce JWTs, refresh-token tables, or API-token authentication for the server-rendered MVP unless a separately approved requirement needs them.

The profile-table approach is a persistence recommendation. Decide exact inheritance mapping (if any) only after the fields and lifecycle of each account type are defined. Do not force every domain concept into ORM inheritance.

## 6. Course and learning relationships

A useful conceptual relationship model is:

- A teacher authors many courses.
- A course contains ordered sections.
- A section contains ordered lessons.
- A parent account completes a checkout for one or more courses.
- A successful purchase creates an entitlement/enrollment record.
- A parent may grant course access to one or more child profiles.
- Each child has independent progress records for lessons/courses.

Keep purchase records, access entitlements, and learning progress separate. A payment/purchase is a financial event; an enrollment/entitlement represents access; progress records represent learning activity.

## 7. Request and UI conventions

- Use Symfony controllers for HTTP orchestration, not as containers for all business rules.
- Use application/domain services for workflows such as registration, course publication, checkout, and child access assignment.
- Use Doctrine repositories for persistence queries.
- Use Twig for page composition and reusable Twig Components for repeated UI.
- Use Stimulus/Turbo/Live Components only where they improve a concrete interaction.
- Keep authorization checks on the server for every protected action.
- Validate input on the server even when the UI also validates it.

## 8. Initial implementation milestones

1. Create the Symfony 8 web application and configure environment variables.
2. Configure PostgreSQL and Doctrine migrations.
3. Implement Identity: registration, login, logout, account status, and role-based authorization.
4. Implement parent and teacher profiles, followed by child profiles.
5. Implement course authoring: courses, sections, lessons, and publication states.
6. Implement public catalogue and course detail pages.
7. Implement simulated checkout, purchases, and child entitlements.
8. Implement course player and per-child learning progress.
9. Implement administration and moderation.
10. Add automated tests for domain rules, authorization, and critical workflows.

Build incrementally. Do not generate every entity and controller before the domain rules are validated.

## 9. Explicit non-goals for the first version

Unless requirements are deliberately revised, do not add:

- A separate React SPA or API-first architecture.
- JWT/refresh-token authentication.
- Real payment-provider integration.
- Private tutoring, live video sessions, or messaging/chat.
- Subscription billing.
- AI tutoring or advanced recommendation systems.
- Native mobile applications or social-network features.

These boundaries keep the first release aligned with the intended Symfony/Twig monolith.
