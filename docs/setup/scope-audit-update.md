# Scope-audit database update

Pull origin, then run from the existing project folder:

```powershell
composer install
php artisan optimize:clear
php artisan migrate
php artisan database:verify
```

No frontend rebuild or new admin account is required for this database-only update. Keep the existing database and `.env`/`APP_KEY`.

Expected after successful migration: **100 tables, 257 foreign keys, 72 enforced CHECK constraints, 0 contract mismatches**.

The new migration adds student fee-plan assignment history and its invoice source link, completes missing subscription license/snapshots, strengthens section-subject and arithmetic constraints, and adds actual student-fee refund metadata. It does not start charging fees, assign arbitrary fee plans, send messages, or alter existing invoice amounts.

If a preflight error names an existing table/record ID, stop and review that record. The migration intentionally refuses to rewrite existing financial values. Do not use `migrate:fresh` to bypass it.

The verification command is read-only: it checks table/column types and nullability, unique keys, FK column pairs/targets and enforced CHECK definitions without printing student/guardian identity numbers.

Detailed scope map: [database audit](../database/scope-coverage-audit.md). Updated diagram: [full ERD](../database/school-erp-full.dbml).
