# Super Admin completion update

Pull origin in GitHub Desktop, then run from the existing project directory:

```powershell
composer install
php artisan optimize:clear
php artisan migrate
npm run build
php artisan database:verify
```

Expected schema: **100 tables, 258 foreign keys, 72 enforced CHECK constraints, 0 contract mismatches**. Migration 000005 adds only a nullable school reference/index to platform audit, and backfills known historical targets without inventing missing school attribution. Keep the existing database and `.env`; no reset or new admin is needed.

## Completed provider scope

- Overview: schools, valid licenses, expiring within 30 days, expired periods, grace, suspended access, net collections, invoice balances and open support. Valid/grace/expiring exclude disabled access; expiring is a subset of valid, and date-state and suspension are separate dimensions, not additive totals.
- Schools: existing registration/contact/campus/first-owner flow plus automatic setup checks and an explicit owner handover confirmation. `ready` means setup complete, not permission to bypass license expiry/suspension. Readiness is recomputed, so missing owners/campuses or an expired unrenewed period can make a previously ready school incomplete.
- Packages: availability for new sales, rates, approved modules and limits; existing subscription snapshots remain agreed values.
- School licenses: stable license number, usage/limits, access changes with reasons, issue record and paginated school-specific license/subscription timeline. Historical issue dates do not imply a known issuing actor; older unrecorded events are not reconstructed.
- Subscriptions: `/admin/subscriptions` lists current/grace/upcoming/expired/cancelled/replaced trial/paid periods with school/type/status/search filters. Renew, cancel and schedule changes through the existing audited school controls.
- Service billing: invoices, partial/full payments, receipts, corrections and retained history use the existing tested flows.
- Support: school requests/replies, priority, assigned admin and resolution use existing tested flows.
- Audit: explicit school links, school search/filter, administrator and Pakistan-date filters, actor/time/change details. Global package/provider changes stay global; historical events with no surviving target stay unlinked.

Admin accounts remain separate. These modules never grant a provider school-owner membership or add an impersonation shortcut. Optional time-limited support access is not enabled.

## Review locally

Open `/admin`, then Schools → Manage → School onboarding and License & subscription history. Confirm a real handover only after checking owner sign-in. Use School audit to see that school's changes. The Subscriptions navigation item opens the consolidated list. Existing dark theme tokens, hover effects and mobile drawer are retained.
