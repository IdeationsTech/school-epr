# Provider release: licenses, billing controls, support and identity contract

In GitHub Desktop, Pull origin. Run in the existing project directory:

```powershell
composer install
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=ServicePackageSeeder
npm ci
npm run build
php artisan database:verify
```

Keep `.env`, `APP_KEY`, user accounts and existing data. Do not run `migrate:fresh` on an installation with data.
The database now has **99 tables, 247 foreign keys and 65 CHECK constraints**. The new migration preserves old school, subscription, invoice/payment and guardian/student rows. Existing schools receive stable `LIC-00000001` style license numbers. Existing subscriptions copy the package name, modules and student/campus limits into historical entitlement snapshots. Existing identity fields start NULL; no fake CNIC/B-Form is inserted.

## Provider workflow

1. **Provider profile:** enter your business name, phone, email, address and receipt footer. School owners can see provider contact details.
2. **Packages:** Starter/Standard are editable templates. Monthly/yearly prices, availability and modules are explicit fields. Updating a template does not change existing subscription snapshots. Existing zero-price reference packages are not sold by default.
3. **Schools → Add school:** create school, main campus, separate owner login, permanent school license, first period and invoice atomically. Trial/paid, 0–30 grace days and approved limit/module overrides can be set under the additional period options.
4. **School licenses:** search/filter by access status or date state. One license number belongs permanently to one school. Active/suspended/closed is distinct from valid/grace/expired/upcoming.
5. **Renewals:** see expiring within 30 days, expired, grace or upcoming periods. Add a non-overlapping renewal period to the same license. Dates use Asia/Karachi and are inclusive.
6. **Package changes:** in school details choose the active period to replace, effective start date, replacement dates/limits and total agreed replacement-period charge. The previous period is shortened or marked replaced; before-values and reason remain audited. The new period gets a new invoice. There is **no automatic proration**: any agreed unused-period credit must be recorded explicitly against the old invoice. A downgrade below current usage is rejected.
7. **Service billing:** searchable invoice queue, partial/full payments, balances, print/save invoice and receipt. Financial amounts use DECIMAL and integer-paisa balance checks. A unique payment reference prevents duplicate entry.
8. **Corrections:** select invoice + corrected total + reason; history records before/after. A corrected invoice cannot go below net paid. Reverse a wrong payment in full with a reason before replacing it; its original record stays visible. A reversal is not a bank refund and does not transfer money. Cancelled periods do not erase invoices or payments.
9. **Support:** open requests against a school; assign an active platform admin, priority, status and replies. School owners can open and reply from My License & Billing. Closed tickets cannot receive owner replies.
10. **Audit log:** searchable, paginated platform activity, actor, target, timestamp and change details. User passwords and CNIC values are not logged.

## School access

- An active period and active license allow normal school work. Grace keeps access through `ends_on + grace_days`. Closed/suspended license blocks operations.
- A school owner can still authenticate and open **My License & Billing** and support while suspended/expired; this does not authorize campus, student or fee APIs. Staff do not receive this owner exception.
- The owner sees the license number, agreed package/limits/modules, dates/type, usage, invoices/net paid and provider contact.
- Existing schools without any subscription retain explicitly flagged legacy access until assigned their first period. Cancelling that first period does not restore legacy access.
- Student usage means active students with an active enrollment covering the current date; withdrawn/historical records do not consume quota. Campus usage counts active campuses. Campus limits are already enforced in campus creation/reactivation; admissions enforcement will be connected when admissions is implemented.
- Entitled operational module names describe the contract. Their full admissions/fees/attendance/exam/payroll screens remain subsequent releases; this release does not claim they are implemented.

## CNIC / B-Form contract

- `guardians.cnic`: nullable, normalized 13 digits, unique per school.
- `students.identity_number` + `identity_type`: nullable together, type `cnic` or `b_form`, normalized 13 digits, unique per school.
- Reuse one guardian row for siblings. The same guardian may exist independently in different schools.
- Laravel rule and model normalization accept display separators such as `35202-1234567-1`; MySQL stores canonical digits and rejects malformed records/duplicates. Format validation is not NADRA identity verification.
- Identity attributes are hidden from default model serialization. Dedicated authorized admission forms/exports must opt in; those forms come with admissions. Do not put CNIC numbers in URLs, logs or WhatsApp templates.

## Read-only verification

`php artisan database:verify` checks the declared tables/columns and physical FK count. It prints no student/guardian values and changes no data. Updated full ERD: `docs/database/school-erp-full.dbml`.

## Current scope boundary

This release completes provider modules at the manual service-management level. Online charging, automatic renewal messages, partial cash refunds, offline licensing and provider impersonation are not included. Backups/restore and production deployment remain explicit operational work; no backup service is silently provisioned.
