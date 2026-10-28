# E-Voting System

A school-based online election system. This repository is the shared Laravel application for the project.

## Official working folder

Use only `C:\xampp\htdocs\e-voting` for development. Do not use the similarly named OneDrive folder as a project copy.

## Technology baseline

- Laravel 13 and PHP 8.3
- MySQL/MariaDB through XAMPP
- Bootstrap 5, JavaScript, Vite, and Blade
- GitHub repository: `roninjacob/e-voting`

## First-time setup

1. Clone the repository into `C:\xampp\htdocs\e-voting`.
2. Run `composer install`.
3. Run `npm install`.
4. Copy `.env.example` to `.env`.
5. Create a local database named `e_voting`.
6. Review the local database credentials in `.env`; never commit this file.
7. Run `php artisan key:generate`.
8. Run `php artisan migrate`.
9. Start Vite with `npm run dev` and Laravel with `php artisan serve`.

## Daily checks

Run these before opening a pull request:

```powershell
php artisan test
npm run build
```

## Branches and ownership

- `main` is the stable integration branch. Do not commit directly to it.
- Use the assigned group branch only:
  - Group 1: `feature/group-1-auth-verification`
  - Group 2: `feature/group-2-cics-sc-election`
  - Group 3: `feature/group-3-block-election`
  - Group 4: `feature/group-4-voting-records-results`
- Pull the latest `main` before starting work. Open a pull request for review before merging back to `main`.
- Coordinate first before editing shared files: `app/Models/User.php`, `routes/web.php`, `database/migrations`, shared layouts, `package.json`, and project configuration.

## Security rules

- Never commit `.env`, passwords, API keys, database exports, or real voter data.
- Do not log or expose an individual voter's candidate selections.
- Treat voting rules, eligibility checks, and vote finalization as security-sensitive changes that require review.
- Local `APP_DEBUG=true` is for development only. Production must use `APP_DEBUG=false`, HTTPS, secure credentials, and a unique `APP_KEY`.

## Current scope

The project now has a Bootstrap 5 frontend baseline and only the default Laravel database migrations. Authentication, election management, candidates, voting, audit logs, and published results must be implemented in their assigned modules.
