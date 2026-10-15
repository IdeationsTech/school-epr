# Database contract and implementation boundary

## Implemented structure
83 domain tables are enumerated in schema.md and database/schema/schema.json.
3 framework migrations create users/password resets/sessions, cache/locks, jobs/batches/failed jobs.
Domain migrations create tables first; the last migration installs foreign keys and MySQL checks.
The MySQL minimum for this scaffold is 8.4, matching the supplied 8.4.11 server.
Tenant-parent foreign keys use (school_id,parent_id) → (school_id,id).
Parent composite keys are explicitly unique for MySQL 8.4.
Financial relationships use restrict-on-delete, with linked reversal records rather than cascaded deletion.
A full initial-batch rollback drops constraints before domain tables. Future deployed schema changes must use NEW migrations.

## Access model
users are global identities; school_memberships link users to schools.
roles and permissions are global templates; membership_roles and membership_campuses are school-scoped assignments.
SchoolModel queries return no rows without SchoolContext. Model instance writes verify tenant context.
This is defense in depth, NOT complete authorization. Query-builder writes and bulk updates bypass model instance hooks. No domain endpoints are published yet.
Authenticated middleware must establish context from an ACTIVE authorized membership and clear it between jobs/requests; role, campus, class/subject and export policies must be implemented before exposing records.
Never call withoutGlobalScopes in user-facing flows.
Protected models block mass assignment until dedicated request/service logic is implemented.

## Required transactional business rules for later implementation
- Allocation/reversal must match the same student's original payments/invoices, and cannot exceed balance.
- Lock affected invoice/payment rows; use idempotency keys; retain unused advance credit.
- Daily cashier totals depend on valid posted transactions, not editable cached numbers.
- Enrollment/section/academic term/session consistency and date membership must be checked.
- Subject assignment, diary completeness and attendance roster scope must be checked.
- Present marks must not exceed that test's maximum; absent/exempt marks are NULL, not zero.
- Published diary/result revisions and financial documents must be immutable; corrections create revisions/reversals.
- Snapshot sharing must validate that guardian is linked/authorized to the reported student.
- “Shared” is staff-confirmed only; no delivered/read receipts are inferred.
- Salary/advance allocations must refer to the same staff member and not exceed outstanding amounts.
- Salary structures must not overlap; employee payroll cannot double-pay across campuses.
- Weight totals and rounding/absent/pass policies must be validated before publishing.
Cross-row rules cannot be claimed as guaranteed solely by this schema. They are deliberately not implemented as fake placeholder services.

## Future-only
Front office/gate passes, parent complaints and observations have storage tables only, without screens/routes/default permission grants.
Some earlier mockup details were illustrative. Explicit requirements and this schema contract govern implementation.

## Validation
GitHub Actions provisions MySQL 8.4, lints PHP, executes tests, tests rollback/remigration and builds React.
Local agent environment has no PHP/MySQL runtime; CI results must be checked before telling the user migration execution passed.
