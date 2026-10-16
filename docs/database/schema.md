# Implemented database inventory

91 domain tables; framework tables are additional. Schema is initial scaffolding, not implemented business workflows.

| Table | Group | References |
|---|---|---|
| schools | platform |  |
| plans | platform |  |
| permissions | access |  |
| roles | access |  |
| role_permissions | access | role_id → roles, permission_id → permissions |
| campuses | platform |  |
| subscriptions | platform | plan_id → plans |
| subscription_invoices | platform | subscription_id → subscriptions |
| subscription_payments | platform | subscription_invoice_id → subscription_invoices |
| school_settings | platform |  |
| school_memberships | access | user_id → users |
| membership_roles | access | membership_id → school_memberships, role_id → roles |
| membership_campuses | access | membership_id → school_memberships, campus_id → campuses |
| files | operations | campus_id → campuses, uploaded_by → school_memberships |
| staff_profiles | staff | membership_id → school_memberships |
| leave_requests | staff | staff_profile_id → staff_profiles, approved_by → school_memberships |
| academic_sessions | academics |  |
| academic_terms | academics | academic_session_id → academic_sessions |
| classes | academics |  |
| sections | academics | campus_id → campuses, academic_session_id → academic_sessions, class_id → classes |
| subjects | academics |  |
| section_subjects | academics | section_id → sections, subject_id → subjects |
| teacher_assignments | academics | staff_profile_id → staff_profiles, section_id → sections, subject_id → subjects |
| admission_inquiries | students | campus_id → campuses |
| admission_applications | students | inquiry_id → admission_inquiries, campus_id → campuses, class_id → classes, academic_session_id → academic_sessions |
| students | students | application_id → admission_applications, photo_file_id → files |
| guardians | students |  |
| student_guardians | students | student_id → students, guardian_id → guardians |
| enrollments | students | student_id → students, section_id → sections |
| student_documents | students | student_id → students, file_id → files |
| fee_heads | fees |  |
| fee_plans | fees | academic_session_id → academic_sessions, class_id → classes, campus_id → campuses |
| fee_plan_items | fees | fee_plan_id → fee_plans, fee_head_id → fee_heads |
| student_concessions | fees | enrollment_id → enrollments, fee_head_id → fee_heads, approved_by → school_memberships |
| invoices | fees | enrollment_id → enrollments |
| invoice_items | fees | invoice_id → invoices, fee_head_id → fee_heads |
| bank_accounts | finance | campus_id → campuses |
| payments | fees | campus_id → campuses, student_id → students, bank_account_id → bank_accounts, received_by → school_memberships |
| payment_allocations | fees | payment_id → payments, invoice_id → invoices |
| payment_reversals | fees | payment_id → payments, approved_by → school_memberships |
| reversal_allocations | fees | payment_reversal_id → payment_reversals, payment_allocation_id → payment_allocations |
| cashier_closings | finance | campus_id → campuses, cashier_membership_id → school_memberships, approved_by → school_memberships |
| expense_categories | finance |  |
| expenses | finance | campus_id → campuses, category_id → expense_categories, bank_account_id → bank_accounts, approved_by → school_memberships, file_id → files |
| attendance_sessions | attendance | section_id → sections, submitted_by → school_memberships |
| attendance_records | attendance | attendance_session_id → attendance_sessions, enrollment_id → enrollments |
| diaries | diary | section_id → sections, published_by → school_memberships, supersedes_id → diaries |
| diary_items | diary | diary_id → diaries, subject_id → subjects, author_membership_id → school_memberships |
| diary_shares | diary | diary_id → diaries, confirmed_by → school_memberships |
| grading_schemes | exams |  |
| exam_cycles | exams | academic_session_id → academic_sessions, academic_term_id → academic_terms, grading_scheme_id → grading_schemes |
| tests | exams | section_id → sections, subject_id → subjects, exam_cycle_id → exam_cycles, grading_scheme_id → grading_schemes |
| test_results | exams | test_id → tests, enrollment_id → enrollments |
| report_snapshots | exams | test_result_id → test_results, grading_scheme_id → grading_schemes, published_by → school_memberships |
| exam_papers | exams | test_id → tests |
| assessment_weightages | exams | exam_cycle_id → exam_cycles, class_id → classes, subject_id → subjects |
| exam_candidates | exams | exam_cycle_id → exam_cycles, enrollment_id → enrollments |
| consolidated_result_snapshots | exams | exam_cycle_id → exam_cycles, enrollment_id → enrollments, grading_scheme_id → grading_schemes, published_by → school_memberships |
| report_shares | sharing | report_snapshot_id → report_snapshots, consolidated_snapshot_id → consolidated_result_snapshots, guardian_id → guardians, confirmed_by → school_memberships |
| staff_attendance_records | payroll | staff_profile_id → staff_profiles, campus_id → campuses, leave_request_id → leave_requests, recorded_by → school_memberships |
| salary_structures | payroll | staff_profile_id → staff_profiles |
| salary_structure_items | payroll | salary_structure_id → salary_structures |
| staff_advances | payroll | staff_profile_id → staff_profiles, approved_by → school_memberships |
| payroll_runs | payroll | campus_id → campuses, approved_by → school_memberships |
| payroll_items | payroll | payroll_run_id → payroll_runs, staff_profile_id → staff_profiles, salary_structure_id → salary_structures |
| payroll_item_components | payroll | payroll_item_id → payroll_items |
| salary_payments | payroll | campus_id → campuses, staff_profile_id → staff_profiles, bank_account_id → bank_accounts, recorded_by → school_memberships |
| salary_payment_allocations | payroll | salary_payment_id → salary_payments, payroll_item_id → payroll_items |
| advance_recoveries | payroll | staff_advance_id → staff_advances, payroll_item_id → payroll_items |
| timetable_slots | operations | section_id → sections, subject_id → subjects, staff_profile_id → staff_profiles |
| calendar_events | operations | campus_id → campuses |
| ptm_appointments | operations | enrollment_id → enrollments, guardian_id → guardians, staff_profile_id → staff_profiles |
| certificate_templates | operations |  |
| certificate_issues | operations | student_id → students, template_id → certificate_templates, issued_by → school_memberships |
| notices | operations | campus_id → campuses, published_by → school_memberships |
| support_tickets | platform | opened_by → school_memberships |
| support_replies | platform | support_ticket_id → support_tickets, author_user_id → users |
| audit_logs | operations | actor_membership_id → school_memberships |
| visitor_logs | future | campus_id → campuses |
| student_gate_passes | future | enrollment_id → enrollments, pickup_guardian_id → guardians, approved_by → school_memberships |
| parent_complaints | future | campus_id → campuses, student_id → students, guardian_id → guardians, assigned_to → school_memberships |
| complaint_updates | future | parent_complaint_id → parent_complaints, author_membership_id → school_memberships |
| student_observations | future | enrollment_id → enrollments, author_membership_id → school_memberships |

All tenant rows use school_id; foreign keys to tenant parents include school_id. Money uses DECIMAL(12,2). Deletion is restricted.
Cross-record business invariants (allocation balances, student matching, class membership on the date, marks ≤ test maximum, weight totals and immutable publication) require transactional services; schema checks alone cannot enforce them.
Future-only tables: visitor_logs, student_gate_passes, parent_complaints, complaint_updates, student_observations. No UI/API for them.

| school_role_permissions | access | role_id → roles, permission_id → permissions |
