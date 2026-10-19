# Phase 10 — session promotions and dated transfers

GitHub Desktop: select `school-epr`, branch `main`, then **Pull origin**.

```powershell
cd "C:\Users\b\Documents\GitHub\school-epr"
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=TransitionAccessSeeder
php artisan database:verify
npm run build
```

Refresh the browser with Ctrl+F5. No dependency change or database reset is required. Expected schema: **109 tables, 302 foreign keys, 90 enforced CHECK constraints**, zero contract mismatches. The additive migration creates three transition tables; existing passwords and student/finance records are retained.

## School workflow

1. In Academic sessions create the later, non-overlapping session. Set up its sections, or use **Session promotions → Prepare next-session sections** to preview and copy missing section structures and optional subject offerings. Existing target sections stay unchanged. Teacher assignments, academic terms, fee plans, concessions and students are not copied.
2. In **Session promotions**, select a source section, load the roster and explicitly select students. Create a named draft for the destination session and effective date. Every student initially remains **Hold**.
3. Choose **Promote**, **Repeat**, **Graduate**, **Withdraw**, or **Hold** individually or for selected rows. Promotion requires a higher class position; repeat requires the same class in the next session. Enter destination sections, optional new rolls and reasons. Source rolls are copied only by explicit bulk choice.
4. Save decisions, then check the preview. Resolve duplicate rolls, overlapping enrollments, capacity and historical attendance/result exceptions, or hold affected students. Future drafts can only be finalized on or after their effective date; there is no automatic scheduler.
5. Review both confirmation checkboxes and finalize. The entire batch applies atomically, or nothing changes. Repeated confirmation of the same finalized digest does not duplicate enrollments. Concurrent changes require a fresh preview/version.
6. In **Student transfers**, use the same flow for a dated move to a different section/campus within the source session, or withdrawal. Existing enrollment starts must precede the effective date. Transfers do not rewrite previous enrollments.
7. **Transition history** stores drafts, cancellations and finalized snapshots. Applied events and their saved snapshot cannot be edited. Export CSV or print the report. Use a further dated transfer or existing reasoned readmission workflow for subsequent changes; no destructive undo is provided.

## History and billing rules

The old enrollment closes at the earliest of its existing end, source-session end and the day before the effective date. It is never extended over a gap between sessions. Attendance and result records after that proposed closing day block application. Past attendance, results, exam candidates, report snapshots and invoices retain their original enrollment IDs. Published report snapshots are not republished or re-shared automatically.

New target enrollments run from the effective date through the destination-session end. Fees and concessions are **not** copied. Existing dues and future-dated invoices remain on the original enrollment; review them and the new fee assignment separately. The academic current-session selection is not changed automatically. Hold keeps the student's existing lifecycle and enrollment unchanged. Graduated students have a distinct status and may use the audited readmission workflow.

## Access and storage

`students.transitions` permits managing transitions (owner and principal defaults). `students.transitions_view` is an optional read-only grant with no default role grants. Section copying additionally requires `academics.manage`. Student admissions must be included in the school license. Source and every saved destination campus must be in scope, including for history, previews and export. Provider admins do not acquire school membership or transition access through this module.

New tables: `enrollment_transition_batches`, `enrollment_transition_items`, `enrollment_transition_events`. Tenant composite foreign keys prevent cross-school references; one finalized event per source enrollment prevents double processing. Audit logs record draft creation/edits, cancellation, finalization and section copying. The full ERD remains in `docs/database/school-erp-full.dbml`.
