# School ERP

Laravel 13 + React/TypeScript + Tailwind + MySQL 8.4 foundation for Pakistani schools (500–1,000 students, 1–2 campuses).

**Current delivery:** session login/logout, responsive owner workspace, school/campus settings, users/roles/campus access, school-specific permission overrides, first-owner CLI setup and 86 domain tables.
**Next modules:** admissions/students, fees, diary, attendance and exams. Those domain tables exist, but the application workflows are not implemented yet.

Existing installation? Follow [Update 2](docs/setup/access-update.md) to pull, migrate and create your first owner account. Full schema now has 95 tables.

Start here: [Windows setup](docs/setup/windows.md).
Database: [table inventory](docs/database/schema.md) · [schema contract](docs/database/integrity.md).
Design: [product blueprint](docs/design/product-design-blueprint-v1.md).

## Quick local setup (PowerShell)
Clone this repository with GitHub Desktop first. Then open a terminal in its folder.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Create a NEW database named school_erp using database/sql/create-local-database.sql.
Edit only this project's .env with your local MySQL credentials.

```powershell
php artisan config:clear
php artisan migrate --seed
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8001
```

Open http://127.0.0.1:8001. Run php artisan school:setup once to create your school/owner, then sign in.
No API credentials or paid messaging service are needed.

Do not commit .env, database dumps, private documents, node_modules or vendor.
Do not use migrate:fresh on a database containing records you need.
Your other software keeps its own folder/database/.env; this app uses port 8001 and Vite 5174.

## Dependency locks
Initial manifests target compatible majors. composer.lock and package-lock.json are committed. The Composer lock comes from the successful MySQL/React validation run. Use composer install and npm ci to reproduce tested versions.

## Development
Run Laravel in one terminal and npm run dev in another.
Setup/access endpoints use session authentication, CSRF protection and tenant authorization. Student/finance/teaching endpoints remain unexposed until their business services and scopes are implemented.

## Provider / Super Admin workspace

School onboarding, packages, subscription invoices, partial payments, renewal and suspension are available. Follow [Update 3](docs/setup/platform-update.md) for migration and one-time platform access. Platform privileges are separate from school roles.
