# Database audit and corrections

Audited repository migrations/schema: original 95 tables, 238 physical foreign keys, 59 CHECK constraints. Counts matched the contract, but licensing and identity requirements were incomplete.

New additive migration: `2026_10_16_000003_complete_platform_contract.php`.

| Gap | Change |
| --- | --- |
| Guardian/student identity missing | Guardian CNIC; student CNIC/B-Form type + number, nullable for existing records, school-specific uniqueness and database format checks |
| No permanent school license | One stable school_licenses row per school, issued number and access status |
| Mutable template used as entitlement | Subscription snapshots of package name, student/campus limits and modules |
| No trial/grace contract | Period kind and bounded grace_days |
| Annual price/availability buried in JSON | Normalized plan fields, with backward-compatible feature metadata |
| No financial correction ledger | Full payment reversal and invoice amount adjustment records; original records retained |
| No provider contact profile | provider_settings JSON record for business/contact/receipt information |
| Support assignment/priority absent | Assigned active provider admin, priority, resolution timestamp |

Result: **99 tables / 247 foreign keys / 65 CHECK constraints**. All deployed alterations use a new migration; no historic migration is rewritten. Support workflow statuses, subscription overlap, invoice allocation limits and active-admin assignment are transactional service rules. Foreign keys alone cannot guarantee those cross-row rules.

The audit does not claim direct access to the user's laptop database. `database:verify` performs a read-only check when run on that installation. MySQL integration tests cover fresh install, upgrade backfill, rollback/remigration, identity uniqueness, access isolation and billing corrections.
