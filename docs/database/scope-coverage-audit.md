# Agreed-scope database audit — 16 October 2026

## Conclusion and evidence boundary

The **latest agreed product scope** was compared with every committed domain table, reference, unique key and CHECK constraint, plus the existing provider/access services. After the additive scope-audit migration the contract has **100 tables (91 domain + 9 Laravel infrastructure), 257 foreign keys and 72 CHECK constraints**.

This is a schema and implemented-service audit, not a claim that all school modules already work. Provider/access flows are implemented. Admissions, school fees, attendance, diary, exams, payroll and the remaining operational screens still need their application services and UI. A complete database cannot by itself enforce every cross-row or permission rule, and cannot guarantee a future implementation has no bugs.

The repository and MySQL 8.4 test database are inspectable here. The user's Windows database is verified by running the read-only `php artisan database:verify` after migration. No direct inspection of their laptop is claimed.

## Scope-to-storage map

| Agreed capability | Existing / added storage | Required application rule or implementation boundary |
| --- | --- | --- |
| Separate provider identity | users, platform_admins | CLI creates a new account; school roles never grant platform access. Implemented and tested. |
| School onboarding and contacts | schools, campuses, school_settings, users, school_memberships | School, owner, license, first subscription and invoice are transactional. Implemented. Branding/configurable forms can use versioned settings; settings are not arbitrary unauthenticated writes. |
| Package catalogue | plans | Explicit monthly/yearly price, limits, availability and modules. Template edits cannot rewrite an agreed period. Implemented. |
| Permanent school license | school_licenses | One stable number per school; active/suspended/closed is separate from subscription dates. Implemented. |
| Subscription trial, grace, renewal/change | subscriptions | License + agreed package name/limits/modules now required. No overlaps; dated replacement with audit; manual old-invoice credit. Implemented. |
| Provider billing and corrections | subscription_invoices, subscription_payments, subscription_payment_reversals, subscription_invoice_adjustments | Lock rows, prevent duplicate/overpayment, retain corrections, derive net balances. Implemented. A ledger reversal is not an automatic bank refund. |
| Renewal queue/dashboard | same license/subscription/billing tables | Date states calculated in Asia/Karachi, not stale cached status fields. Implemented. |
| Support and provider contact | support_tickets, support_replies, provider_settings | Admin assignment/replies, owner-only school support even when school operations are blocked. Implemented. |
| Audit | platform_audit_logs, audit_logs | Record actor, reason and before/after where needed; redact identity/password values. Provider flows implemented; future school modules must use school audit. |
| School permissions/campus scope | roles, permissions, role_permissions, school_role_permissions, school_memberships, membership_roles, membership_campuses | Tenant membership first, then role + campus + teacher assignment. Setup/provider guards implemented; new school endpoints need individual policies. |
| Academic sessions/classes/sections/subjects | academic_sessions, academic_terms, classes, sections, subjects, section_subjects, teacher_assignments | Terms stay inside sessions; capacity, overlapping sessions and class-teacher policy need transactional validation. Offered-subject FKs now reject invalid teacher/test/timetable subject links. |
| Admissions and guardian/family links | admission_inquiries, admission_applications, students, guardians, student_guardians, student_documents, files | Application conversion, duplicate-student detection, private document access and consent/guardian eligibility remain admissions work. Siblings reuse guardian links; no extra family table is required for that relationship. |
| CNIC/B-Form | guardians.cnic; students.identity_number + identity_type | Canonical 13 digits, nullable for legacy records, unique per school; student type and number must be provided together. Hidden from default model serialization. Format check is not NADRA verification. |
| Promotion/transfer/repeat/withdrawal | students + dated enrollments + audit_logs | Close old enrollment, create new one, retain old results/fees; prevent conflicting active enrollments. These transitions need a service; a mutable current-section field would lose history. |
| Student-specific fee-plan assignment | **student_fee_assignments**, fee_plans, fee_plan_items, fee_heads, invoices.fee_assignment_id | Added explicit effective-dated assignment and billing-source link. Same-school FK and matching invoice enrollment are enforced. Prevent date overlap and ensure plan matches enrollment campus/class/session in the fees service. No arbitrary existing assignments were guessed. |
| Monthly charges/concessions/arrears | invoices, invoice_items, student_concessions, enrollments | Unique period/kind/installment is available. Student-level duplicate checking across transfers, fee frequency, opening-dues invoice kind and bulk idempotency require the fees service. Arrears derive from unpaid balances rather than a mutable student balance. |
| Partial payments/advances/receipts | payments, payment_allocations | Explicit allocations allow partial payment; unallocated money is advance credit. Allocation student ownership and total ceilings require locked transactions. Student-fee payment endpoints are not implemented yet. |
| School receipt corrections vs actual refund | payment_reversals, reversal_allocations, **kind/refund_method/refund_reference/refund_bank_account_id/reversed_by** | Added explicit correction/refund distinction and refund payout metadata. Historical NULL kind is unclassified, not silently labelled as returned money. Refund kind requires a method; amount ceiling, refund approval/actor/timestamp and allocation restoration belong in the future fees service. |
| Cashier closing/expenses | cashier_closings, expenses, expense_categories, bank_accounts | Cashier variance arithmetic now enforced. Expected balance must be derived from posted cash transactions and approved refunds/expenses. No full double-entry accounting is implied. |
| Student attendance | attendance_sessions, attendance_records, calendar_events | Draft/submit/correction audit, roster section and enrollment/date eligibility remain attendance service rules. Daily uniqueness exists. |
| Daily class diary | diaries, diary_items, diary_shares, section_subjects | Immutable published revisions, required-subject completeness and explicit no-homework. WhatsApp group sharing remains manual and staff-confirmed. |
| Tests/term/annual results | grading_schemes, exam_cycles, tests, test_results, report_snapshots, exam_papers, assessment_weightages, exam_candidates, consolidated_result_snapshots | Validate marks against test max, same section/session, term dates and weight sum; publish immutable snapshots and grade policy versions. Absent/exempt is NULL, not zero. |
| Individual parent report sharing | report_shares + report/result snapshots + student_guardians | Confirm guardian is linked/authorized to that student before opening/sharing. Opening WhatsApp never implies sent/delivered/read. Corrected report revision starts its own sharing state. |
| Staff/attendance/leave | staff_profiles, leave_requests, staff_attendance_records, school memberships/campus links | Staff movement, leave/date eligibility and attendance correction require service rules. |
| Basic monthly payroll | salary_structures, salary_structure_items, staff_advances, payroll_runs, payroll_items, payroll_item_components, salary_payments, salary_payment_allocations, advance_recoveries | Normalize monthly period, prevent duplicate pay across campuses/revisions, overlapping salary structures and over-recovery/overpayment. Net pay arithmetic is already enforced. |
| Timetable/holidays/events/PTM | timetable_slots, calendar_events, ptm_appointments | Offered-subject FK, time interval checks exist; teacher/room overlap, guardian link and holiday policy need services. |
| Notices/certificates/ID documents | notices, certificate_templates, certificate_issues, files, school_settings | Template versions/signature branding and issued payload snapshots support printable records. ID cards may be generated from student data; a table per print layout is unnecessary. |
| Reports/search/exports/import preview | source records above, report snapshots, files; Laravel jobs/job_batches/failed_jobs if asynchronous | Most reports are derived queries. Import preview can validate before writing; commit must revalidate duplicates/permissions. Exports and private files need scoped authorization. |
| Backups/restore and deployment | operational infrastructure | Not a database table. Backups, retention, restore drills, TLS and hosting must be provisioned/tested separately; they have not been silently deployed. |
| Deferred modules | visitor_logs, student_gate_passes, parent_complaints, complaint_updates, student_observations | Storage only. No current screens/routes/default grants. Parent mobile app, paid automatic messaging, library/inventory/transport/full accounting/offline sync remain outside the agreed first release. |

Earlier concept drawings/table-count estimates are historical planning material. This report uses the later explicit scope and actual migration contract. Small exploratory ideas in older mockups are not evidence of implemented modules.

## Verified structural corrections in this audit

1. Effective-dated student fee assignments and a tenant/enrollment-safe invoice link.
2. Required subscription license, package name, limits and modules after missing-value backfill. Existing negotiated values stay unchanged.
3. Teacher assignments, tests and timetable rows must reference an offered section subject; nullable subject is still allowed for a class teacher.
4. Invoice line arithmetic, cashier variance and birth/admission date ordering are database-enforced.
5. Actual student-fee refund metadata is distinct from a receipt correction. Provider billing's reversal model remains intentionally separate.
6. Schema verification checks real types, lengths/nullability, full unique keys, FK target/column ordering and restricted deletion, and normalized enforced CHECK definitions—not just counts.

## Safe upgrade policy

The new migration checks existing arithmetic/date/subject conflicts **before DDL**. If it finds a conflict it reports table + record IDs without printing CNIC values or rewriting financial amounts. Review those records and apply an audited correction before retrying; do not delete them simply to pass a migration.

No fee plan is automatically assigned to historical enrollments because the agreed choice cannot be inferred safely. Existing invoices keep NULL source assignment and retain their amounts. Their source can be reconciled during the fees module without fabricating history. New fee assignments and invoices must use the validated service flow.

Legacy subscription snapshots with missing fields are filled from the associated package/license; non-NULL agreed values are retained. Previous historical package terms cannot be reconstructed if they were never stored; backfill does not invent them.

## Remaining rules that must be tested with their module

Cross-row rules are explicitly listed above. In particular, enrollment overlap, parent-report authorization, allocation/refund limits, invoice totals, payroll duplication, mark maxima, grade weights and schedule conflicts are **not guaranteed merely because tables exist**. Each module must implement and test them before its endpoints/screens are considered complete.
