# Phase 9: school expenses and cash-flow control

After GitHub Desktop **Pull origin**, run in the existing project:

```powershell
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=FinanceAccessSeeder
php artisan database:verify
npm run build
```

Ctrl+F5 in the browser. No dependency or .env changes. Existing accounts, passwords and data are retained. Current schema: **106 tables, 287 foreign keys, 86 enforced CHECK constraints**. Do not use migrate:fresh on your existing database.

## School workflow

1. **Expense settings**: create school-wide expense categories and optional campus bank/wallet accounts. Existing fee/payroll accounts are reused. Cash is separate for each campus. Creating an account does not move money.
2. **School expenses**: request an expense with date, payee, category, amount and proposed payment details. An approver records a review reason. Approval alone is not a payment. The approved unpaid expense can be voided with a reason; recreate incorrect requests rather than silently changing approved details.
3. Record actual expense payment separately, using the approved account and payment date. The full payment gets a frozen printable receipt. Create retries and payment retries do not create duplicate entries. Expense payments are full payments; partial school fee and salary payments remain in their own modules.
4. A **refund** means actual returned money; it can be partial and may use another valid campus/school-wide account. A **correction** reverses the full original bookkeeping entry on its original account. Both need a request, reason, review and approval. Approved plus pending reversals cannot exceed the original expense. Original receipts remain available and list approved refunds/corrections.
5. **Cash-flow reports** combine fee receipts/refunds/corrections, actual expense payments/refunds/corrections, salary payments/corrections, and disbursed/voided staff advances. Invoices, unpaid approved expenses, payroll approval and advance recoveries are not new cash movements. Salary payments are counted once even when allocated across multiple payslips.
6. Report dates use Pakistan time. Actual inflow/outflow, actual net movement, signed book corrections and book net movement are separate. Campus/account/date filters, CSV, source-record details and printable reports are available. These are recorded movements, **not profit, complete bank statements or double-entry accounts**.
7. **Daily cash & bank closing**: select campus, cash or a campus-specific account, and date. Supply a verified opening balance. If yesterday has an approved closing, its counted balance is carried automatically. Enter counted cash or the bank statement closing balance and explain the opening verification/variance. A variance does not create an automatic balancing transaction. A separate approver reviews the frozen snapshot (owners can perform both steps).
8. Backdated payments, refunds/corrections or changes to the prior approved closing flag an existing snapshot as changed. Reject changed pending snapshots; submit a fresh revision. Approved snapshots are preserved, and the latest approved revision supersedes earlier revisions. A changed earlier daily closing must be reconciled before carrying its balance forward.

## Boundaries and access

- Default finance view/manage: owner and accountant. Default approve: owner. Optional grants and existing explicit school-role denials are preserved by the seeder. Teachers and provider administrators receive no implicit financial access. Active fees license scope and school/campus authorization apply on every endpoint.
- Finance view intentionally includes combined salary-payment/advance cash movements; it does not grant payroll editing or access to salary-structure APIs.
- School-wide bank/wallet accounts are included in filtered cash-flow reports. Daily statement reconciliation currently supports campus-specific accounts only, avoiding a partial campus balance being labelled as a full shared-account balance.
- Opening floats and bank statement balances are verified manually. Deposits, withdrawals, transfers or historical balances outside this application are not inferred or silently invented. No bank transfer, automated refund, email or WhatsApp message is sent.
- Unsupported legacy paid expenses, missing payment account/date, or voided advances without a correction date require explicit review. No legacy history is fabricated. The old reserved cashier_closings table is retained; versioned operational closing snapshots use finance_reconciliations.

Routes: `/finance/settings`, `/finance/expenses`, `/finance/cash-flow`, `/finance/closing`.
