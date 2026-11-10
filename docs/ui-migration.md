# MAAK UI migration

The `maak_front` React/TanStack repository is used as the visual reference only. The Symfony app now renders its UI with Twig.

## Shared design
- Plus Jakarta Sans typography
- Indigo/violet primary color, teal secondary accents, pale neutral surfaces
- 8px card radius, subtle borders, compact typography
- Responsive page wrapper, course grid, catalogue filters, auth cards and dashboard shell

## Template map
- `templates/base.html.twig`: global document shell
- `templates/components/`: public navigation, footer and course card
- `templates/home/`: landing page
- `templates/catalogue/`: course list and details
- `templates/teachers/`: teacher profile
- `templates/auth/`, `templates/security/`, `templates/registration/`, `templates/password_reset/`: authentication
- `templates/dashboard/`: parent, teacher and admin workspaces
- `templates/pages/`: checkout, payment status and course player

## Data and backend boundary
`src/Service/MarketplaceCatalog.php` contains the initial demonstration catalogue and teacher profiles matching the React reference data. Replace this with Doctrine repositories as Course and Teacher domain entities are introduced. Dashboard sample cards and progress values are presentation placeholders until Family, Enrollment, Learning, Checkout and Admin domain services are wired into the controllers.

## Notes
The original React images are retained in `public/images/maak/`. No React runtime or separate frontend server is required for the Symfony-rendered pages.
