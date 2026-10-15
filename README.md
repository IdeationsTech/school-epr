# School ERP

Laravel 13 + React/TypeScript + Tailwind + MySQL 8.4 foundation for Pakistani schools (500–1,000 students, 1–2 campuses).

**Current delivery:** project folders, bootable application scaffold, 83 domain tables, migrations, read-oriented models, safe reference/demo seeders, database integrity tests, a React setup page and CI.
**Not implemented yet:** login UI/authentication flows, module CRUD, financial posting services, report generation and the 46 working product screens. The images are design concepts.

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

Open http://127.0.0.1:8001. This displays the foundation page, not a finished ERP.
No API credentials or paid messaging service are needed.

Do not commit .env, database dumps, private documents, node_modules or vendor.
Do not use migrate:fresh on a database containing records you need.
Your other software keeps its own folder/database/.env; this app uses port 8001 and Vite 5174.

## Dependency locks
Initial manifests target compatible majors. package-lock.json is committed. Composer dependencies still resolve on first install until composer.lock is committed; validation CI uploads its resolved lock as an artifact. Commit the verified Composer lock before a shared production deployment.

## Development
Run Laravel in one terminal and npm run dev in another.
All domain HTTP endpoints remain unexposed until authentication, authorization and transactional services are implemented.
