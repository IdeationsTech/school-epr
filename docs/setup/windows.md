# Windows: clone, install and create the database

## 1. Local project folder with GitHub Desktop
1. Open GitHub Desktop and sign in to the account with repository access.
2. File → Clone repository → URL.
3. Repository URL: https://github.com/IdeationsTech/school-epr.git
4. Local path: C:\\Projects\\school-epr (or another NEW empty destination).
5. Click Clone. GitHub Desktop creates/populates the folder; do not copy files manually.
6. If already cloned, select the existing school-epr repository, Fetch origin → Pull origin.
7. Repository → Open in terminal. Your prompt should end with school-epr.
Never clone over the folder/database of your old software.

## 2. Install PHP dependencies and initialize local configuration
These are PowerShell commands inside the cloned project:
```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```
Only copy .env.example on FIRST setup. Do not overwrite an existing configured .env.
Composer must report PHP extensions including pdo_mysql, mbstring, openssl, fileinfo, tokenizer, ctype, dom/xml and curl as required by dependencies. Enable missing extensions in the existing PHP installation if Composer identifies them; do not reinstall blindly.

## 3. Create the empty database
In Workbench connect to your existing local MySQL Server and run:
```sql
CREATE DATABASE IF NOT EXISTS school_erp
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Or open mysql shell with mysql -u root -p and run the same SQL.
Use a fresh school_erp database, not the database used by your existing app.
This SQL creates the container; the next step creates its tables.

## 4. Set this project's .env
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_erp
DB_USERNAME=root
DB_PASSWORD="your local MySQL password"
```
Set the real username/password locally. Never send passwords in chat or commit .env.
If your MySQL port is different, use that actual port.
Keep APP_ENV=local and APP_URL=http://127.0.0.1:8001.
Generate APP_KEY once; do not regenerate on routine updates.

## 5. Build tables and initial reference data
In PowerShell (NOT Workbench):
```powershell
php artisan config:clear
php artisan migrate --seed
php artisan migrate:status
php artisan school:schema-status
```
Migrations create tables, foreign keys, indexes and checks. Seeder adds roles/permission templates and a placeholder plan; no school/student/payment records or default password.
Refresh Workbench Schemas → school_erp → Tables. Records persist in MySQL after Laravel closes.
The migrations table and framework tables are in addition to the 83 domain tables.

## 6. Optional explicit demo data
Not needed for the database installation.
Set a locally chosen DEMO_ADMIN_PASSWORD of at least 12 characters in .env, then:
```powershell
php artisan config:clear
php artisan db:seed --class=DemoSchoolSeeder
```
Creates a fictional demo school, main campus and owner@demo.example user record. No login screen exists in this foundation phase. Seeder reruns do not reset the password. Do not run demo seeding against real school databases.

## 7. Run the foundation page
```powershell
npm ci
npm run build
php artisan serve --host=127.0.0.1 --port=8001
```
Open http://127.0.0.1:8001.
For active frontend development use a second terminal with npm run dev.
Laravel port 8001 and Vite port 5174 avoid the common defaults used by your other project.

## 8. Later updates
GitHub Desktop → Fetch origin → Pull origin.
```powershell
composer install
npm install
php artisan migrate
npm run build
```
Run seeders again only when a release explicitly requires it.
Back up real data before schema updates. Never use migrate:fresh as an update command.
No full offline operation is implemented.

## Troubleshooting
- could not find driver: enable pdo_mysql for C:\\php\\php.ini used by the CLI.
- Access denied: check .env username/password, then php artisan config:clear.
- Unknown database: complete step 3 on the same MySQL host/port.
- Vite manifest missing: run npm install then npm run build.
- Port busy: stop only the relevant old process or use a different APP_URL/serve port.
- Foreign key/check migration failure: preserve the error and migration name; do not disable foreign keys to bypass it.
- Database tests use school_erp_test only. Create that empty database separately; do not point test commands at school_erp or old software data.
