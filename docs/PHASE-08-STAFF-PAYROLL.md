# Phase 8: staff attendance and basic payroll

## Install Phase 7 and 8 together on an existing database

In GitHub Desktop select `school-epr`, branch `main`, and **Pull origin**. In PowerShell:

```powershell
cd "C:\Users\b\Documents\GitHub\school-epr"
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=ExamAccessSeeder
php artisan db:seed --class=PayrollAccessSeeder
php artisan database:verify
npm run build
```

Refresh the browser with Ctrl+F5. Existing data, accounts and passwords remain. No dependency or .env changes. Never use migrate:fresh on a database you want to retain. Schema contract after both updates: 104 tables, 274 foreign keys, 78 enforced checks.

## Working order

1. Users & access: create the employee's existing school account and campus assignment.
2. Staff & appointments: link a unique employee number to that account, then add a dated campus appointment. No appointment or salary is inferred from older data.
3. Salary & payroll policy: add effective salary components; exactly one positive basic salary is required. New versions can explicitly end the previous open version. Set a campus payroll policy from the first day of a month. Paying bank accounts can be created here by an approver.
4. Staff attendance: load the dated roster. No marks are preselected. Approved leave must be marked as leave with its linked request. Saved corrections require a reason and current version.
5. Staff leave: staff request their own paid/unpaid leave in My staff portal. Approvers review with a reason. Cancellations retain their history and require linked attendance to be corrected before a future calculation.
6. Staff advances: request, approve, then separately record disbursement. Approved loans alone do not create a debt recovery. Recovery starts the month after disbursement, oldest first, and is capped by the loan balance and available net salary.
7. Monthly payroll: select a **completed month**, calculate and review, generate/recalculate the draft, then approve. The source digest rejects approval after salary, attendance, policy or loan inputs change. Process months chronologically.
8. Open an approved payslip to record partial/full salary payments, print receipts, request adjustments, or review reversals. Staff can view/print only their own approved payslips and request leave.

## Calculation and correction rules

- Monetary arithmetic uses integer cents and half-up rounding. Salary components are prorated for employed calendar days divided by calendar days in the month, including effective-date changes.
- Fixed-pay policy does not deduct for attendance. Attendance-linked policy requires all eligible working days to be marked. Only explicit school rules apply absence/unpaid-leave/late deductions. Unmarked days never become absence automatically.
- Preview/draft generation creates neither salary payments nor advance recoveries. Approval books recoveries once and freezes original payslip snapshots.
- Credits, debits and recovery refunds require separate approval. Debits cannot reduce entitlement below net recorded payments. Refunds cannot exceed their original recovery, and restore loan debt as well as salary entitlement.
- Payments use idempotency keys and cannot overpay allocated approved payslips. Full payment reversals retain original receipts and restore outstanding salary. Payment/advance entries are bookkeeping; no bank transfer or email is sent.
- Campus/school scope, license and explicit role overrides apply on the server. Payroll manage, approve and view are separate permissions. Self portal does not grant company payroll access. Provider administrators do not become school owners.
- This phase does not calculate statutory tax, provident fund or jurisdiction-specific deductions. Fixed school allowances/deductions can be configured explicitly.
- Campus transfers must begin in a separate payroll month. Reconcile ambiguous legacy salary/loan/payroll records rather than inventing historical metadata.

Routes: `/staff`, `/staff/attendance`, `/staff/leave`, `/staff/me`, `/payroll/setup`, `/payroll/advances`, `/payroll/runs`.
