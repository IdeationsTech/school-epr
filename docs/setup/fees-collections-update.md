# Phase 4: school student fees and collections

This is the school's student billing workspace, separate from provider subscription/service invoices.

After Pull origin, from the existing project folder run:

```powershell
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=FeeAccessSeeder
php artisan database:verify
npm run build
```

Refresh with Ctrl+F5. Existing student data, passwords, invoice/payment rows and .env remain. No dependency or key reset is required. One nullable invoice-line billing-frequency snapshot is added; schema/DBML remain 100 tables, 258 foreign keys and 72 enforced CHECK constraints. Existing legacy financial records are not rewritten.

## Workflow and permissions

1. **Fee setup:** add fee heads and collection accounts; create class/session/campus fee plans with monthly, annual or once-per-student fee lines. An assigned plan is locked: copy it into a new plan for revised rates. Shared setup requires access to all campuses, while campus-specific plans/accounts require that campus.
2. **Fee assignments:** choose an enrollment or an entire section, plan, effective date and reason. The old assignment closes before a new one begins. Existing billed periods are protected. A student moving class/campus needs an assignment for the new enrollment.
3. **Concessions:** record a fixed or percentage discount, fee head, dates and reason; approve it before generating invoices. Approved overlapping concessions for the same enrollment/head are blocked. The approval changes only future invoice calculations, never existing invoice snapshots. Sibling discounts are explicit concessions, not automatic deductions based only on a shared guardian.
4. **Generate fee invoices:** select month/campus/section and review every row before confirmation. Confirmation recalculates inside a school transaction. Any invalid row aborts the whole batch. Existing monthly invoices are skipped across all of a student's enrollments, including transfers. The earliest overlapping enrollment in the selected scope supplies the plan; resolve missing historical assignments before billing.
5. **Opening dues:** enter a reviewed old balance before the student's first non-void invoice. These balances are explicit invoices, not a mutable student balance, and cannot be entered again on top of existing billing.
6. **Fee collections:** search invoice/student, record partial or full payment and print/save the invoice or receipt as PDF through the browser. Cash has no bank account; bank transfer/JazzCash/Easypaisa require a collection account and transaction reference. Overpayments are rejected. One payment request allocates to one invoice; duplicate request keys and duplicate non-cash transaction references are checked.
7. **Corrections/refunds:** request an amount and reason, then an authorized reviewer approves or rejects it. Pending requests do not change balances. Correction removes a wrongly recorded collection; refund records money actually returned, with refund method/reference/account as appropriate. Approval restores the corresponding outstanding amount. This application records the action; it does not initiate a bank/wallet transfer. Receipts retain the original amount and show current net/reversal history. Legacy payments allocated to multiple invoices support full reversal only.
8. **Reports:** date/campus collection totals, method totals, approved corrections/refunds, outstanding/defaulter register and a student's invoice ledger with charged/net-paid/balance columns. Collections use Pakistan dates. Outstanding balances are current approved balances for invoices issued through the selected end date, not a historical closing balance. Gross methods are explicitly labeled gross. Negative net collection days are supported when refund/correction activity exceeds that day's receipts.

Default grants: owner/principal manage setup and concessions; owner/accountant collect; owner/principal/accountant may request reversals; only owner has default reversal approval. Existing role overrides remain effective. Provider admins gain no school role. Every endpoint checks fee-module entitlement, school membership, permissions and campus scope. New permissions are additive through FeeAccessSeeder; seed it once after updating an existing installation.

## Billing rules

All arithmetic uses integer paisa, with percentage discounts rounded half-up to the nearest paisa. A fixed concession is capped at the fee line amount. Invoice lines snapshot charge, discount, net and frequency. Annual heads bill once per academic session; once-only heads bill once across the student's history; non-void historical fee-head charges conservatively suppress duplicate annual/once charges even where a legacy line has no frequency snapshot. Monthly tuition is a full month; proration, installment schedules, late penalties, advance/unallocated credits, and automatic discount carry-over after transfer are not silently assumed.

Void retains an invoice's number, lines and reason. It requires zero net allocated payment. Reissue uses a new invoice/number and retains the void original. A later plan cannot alter an issued bill. Payment/reversal idempotency keys are tied to their exact business details; failed-network retries reuse the original key. School-row locks serialize fee mutation, numbers and balance checks. Audit rows retain actor, amount/date/reason and source IDs; issued documents and financial history are not deleted through this workspace.

The regular GitHub validation workflow covers MySQL migrations/rollback/schema contract, fee feature tests and the browser flow from fee setup to concession, invoice, partial collection, refund approval, print preview and mobile reports.
