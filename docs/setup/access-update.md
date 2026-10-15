# Update 2 — Login, roles and school/campus setup

## Pull using GitHub Desktop
Select school-epr → Fetch origin → Pull origin. Use the existing cloned folder. Do not clone again or copy files over your old software.
If .env appears in Changes, do not commit it. It should remain ignored.

Stop this project's existing Laravel/Vite process, then open Repository → Open in terminal (PowerShell).

```powershell
composer install
npm ci
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=AccessSetupSeeder
npm run build
```

The new migration adds school_role_permissions. Your existing records remain. Total tables become 93 (84 domain + 8 framework + migrations).
Never run migrate:fresh, drop tables, overwrite .env or regenerate APP_KEY as part of this update.

## Create your first real school and owner (once)

```powershell
php artisan school:setup
```

Answer: school name, a unique school code, owner name, owner email, password and password confirmation. Passwords need 12+ characters and input is hidden. Choose your own password; do not send it in chat.
This creates a school, Main Campus and owner membership. It does not reset an existing account or overwrite school data.
If you already deliberately created the demo school, its owner@demo.example account uses the password you chose for DEMO_ADMIN_PASSWORD; no hardcoded default password exists. For a new real school use a different unused email, or use that existing demo login only for testing.

```powershell
php artisan serve --host=127.0.0.1 --port=8001
```

Open http://127.0.0.1:8001 and sign in with the credentials you chose.

## Working screens
- Login, show/hide password, remember-device option and logout.
- Overview with actual accessible campuses, active user count for owners and role.
- School profile name, contact phone/email and fixed Pakistan timezone/currency.
- Campus creation/editing, status and last-active-campus protection.
- Owner user management: create user, assign roles/campuses, activate/deactivate membership.
- School-specific permission matrix; owner permissions and access administration remain protected.
- School switcher appears only for identities with multiple active school memberships.

All school data and write actions are validated server-side. School and campus IDs from other schools are rejected. Revoked memberships lose access on the next protected request. Management changes are audited without passwords.
Existing user emails cannot be reused through the Add user screen: this deliberately avoids changing another school's global account. Controlled cross-school identity linking will be added with SaaS administration.
Roles are school-level action grants; class/subject scopes for later teaching workflows must still be implemented before those endpoints are exposed.

Not yet implemented: student/fee/diary/exam module screens, self-service password reset, logo uploads, or full SaaS onboarding/billing. There is no automatic WhatsApp/email sending.
