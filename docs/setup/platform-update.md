# Update 3: provider / Super Admin workspace

Pull origin in GitHub Desktop, then run in the existing project folder:

```powershell
composer install
php artisan optimize:clear
php artisan migrate
npm ci
npm run build
```

Keep the existing `.env`, database and `APP_KEY`. Do not run `migrate:fresh`.
Two new tables bring the database to **95 tables**. No existing records are replaced.

## One-time provider access

```powershell
php artisan platform:admin
```

Enter your **platform administrator name, a new email address, a new password and its confirmation**. Use at least 12 characters for the password; it stays hidden while typing. The email must not already belong to a school owner or any other account.

This creates a separate user with platform access and **no school membership**. Existing school owners and their passwords remain unchanged. Initial provider creation uses the trusted server terminal. After that, new schools and owners are created through the panel.

Start the app if it is not already running:

```powershell
php artisan serve --host=127.0.0.1 --port=8001
```

Open http://127.0.0.1:8001/admin and sign in with the **new platform email/password**. If already signed in, refresh using Ctrl+F5. Use the separate school owner credentials when testing the school workspace. If you previously promoted an existing school owner with the older version of this command, that earlier grant is not automatically removed.

## First school from the panel

1. Open **Packages**. Create a package with your actual monthly/yearly rates and student/campus limits. The pre-existing zero-price reference plan is a placeholder, not a pricing recommendation.
2. Open **Schools → Add school**. Enter school contact/address, unique code, owner credentials, package, exact subscription dates and **total agreed period price**.
3. Submit once. School, main campus, owner, membership, subscription and invoice are created together. Share the credentials yourself; this release sends no email or WhatsApp messages.
4. Open the school's details to record partial/full payments using a unique bank transaction or cash receipt reference. Overpayments and duplicate references are rejected. Money is stored as DECIMAL; balance checks use integer paisa.
5. Renew by adding a period that begins after the previous period ends. Overlapping periods are rejected. Existing invoices and subscription prices remain historical records even if package rates change.
6. Suspend/activate with a reason. Every platform write records the actor and target in a separate platform audit log. No passwords are logged.

## Access and limits

- School owner roles cannot grant platform access. Every platform API request checks the separate active admin grant.
- Suspension blocks school users on their next API request, including existing sessions. Subscription access is valid inclusively from start through end date in Asia/Karachi.
- Schools that already existed **without any subscription** retain access. Assign a subscription when ready; expiry checks then apply. A future period does not fill a gap in access.
- Invoice payment and subscription validity are independent: recording payment does not silently extend dates; an unpaid invoice does not automatically suspend a valid subscription.
- Active-campus limits are enforced in campus creation/reactivation. Assigning/editing packages below a school's current campus/student count is rejected. Student admission enforcement will be added with the admissions workflow.
- No live payment gateway, automated recurring charge, refunds or financial reversal UI is included in this release. Payment records are not editable/deletable from the UI.

## Routes

`/admin`, `/admin/schools`, `/admin/schools/new`, `/admin/schools/{id}`, `/admin/packages`.

The ERD at `docs/database/school-erp-full.dbml` includes the two platform tables.
