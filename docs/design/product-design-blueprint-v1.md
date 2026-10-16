# School ERP — Product & Design Blueprint v1.3
Date: 15 October 2026
Repository: IdeationsTech/school-epr
Audience: Pakistani private schools, 500–1,000 students, 1–2 campuses.
Stack: Laravel/PHP, React/TypeScript, Tailwind CSS, MySQL. Dependency versions to be verified against supplied runtimes before implementation.
Status: design specification; no application or migrations implemented yet.

> Database table inventories in the early design sections are historical estimates. The current scope/storage map and implementation boundaries are documented in [the 16 October audit](../database/scope-coverage-audit.md); the executable schema contract and additive migrations govern actual database structure.

## 1. Agreed scope
Cloud web app; desktop admin/accountant workflows and responsive teacher workflows.
Daily class diary manually shared to WhatsApp groups. Individual test report manually shared to the registered guardian chat. Explicit staff confirmation records sharing; opening WhatsApp NEVER proves delivery.
No email, paid WhatsApp automation or native parent app in initial release. Design permits future additions.
Working UI name: School ERP; final brand name undecided. Demo records are fictional.
Interpretation of “no tables with entities”: number of database tables, their entities and relationships.

## 2. Product modules
| Module | Planned capability |
|---|---|
| SaaS administration | Schools, onboarding checklist, plans, subscriptions, enabled modules, usage, support tickets |
| School setup | Branding, campuses, academic sessions, classes/sections, holidays, school timezone, print templates |
| Access | Owner, principal, accountant, coordinator, teacher; campus and class scope; revoke sessions; audit sensitive changes |
| Admissions | Inquiry follow-up, application, document checklist, admission conversion; configurable required fields |
| Student/family | Guardian links, siblings, contacts, enrollment timeline, active/withdrawn/graduated status |
| Session transition | Promotion preview, exception list, repeated students, campus transfer, prior-session history |
| Fees | Fee heads/plans, opening balance, monthly invoices, sibling concessions, date-bounded discounts, partial/advance payment, receipts |
| Financial control | Payment allocation, refunds/reversals, cancellation approval, daily cashier closing, expenses, export |
| Attendance | Class roster, present/absent/late/leave, draft save, submit, permissioned correction and history |
| Daily diary | Subject entries, required-subject completeness, coordinator review, publish, text/PDF preview, revision history, class-group sharing log |
| Tests/exams | Quick class test, marks grid, absent/exempt handling, grading snapshot, approval, individual PDF/text, performance history |
| Sharing desk | Pending/shared filters, guardian number validation, open WhatsApp, explicit mark shared, next pending, resend reason |
| Academics | Teacher allocations, manual timetable and overlap checks, lesson/topic tracking, calendar |
| Staff | Staff profiles, teacher assignments, leave requests, manual staff attendance and basic payroll; see section 13 |
| Documents | Student attachments, ID cards, leaving/character certificates, configurable signature blocks |
| Operations | PTM scheduling, follow-up notes, notices and acknowledged staff tasks |
| Reports | Dues aging, daily/monthly collection, attendance trends, test analysis, campus comparison; figures drill down to underlying records |
| Data utilities | Excel import preview, per-row errors, duplicate checking, CSV export, backup/restore procedures |

Later modules: full double-entry accounts, advanced payroll, transport, library, inventory, LMS, parent app and optional official messaging. Basic payroll and expanded term exams are now in release scope; see section 13.
Release 1 is a coherent production scope, not all later modules at once.

## 3. Role boundaries
Super admin manages subscriptions and school status. Student/finance access is not automatically granted to support; any support access is time-limited, authorized and audited.
School owner sees all authorized campuses; principal sees academics and permitted summaries.
Accountant manages fee workflows but cannot alter published test marks.
Coordinator reviews diaries and results.
Teacher sees assigned rosters/subjects only. Reporting permission does not imply export permission.
Roles are templates; permission grants and campus scope are explicit.

## 4. Design system — “Calm Campus”
| Token | Value | Purpose |
|---|---|---|
| Navy | #14263D | Sidebar, titles |
| Teal | #0F766E | Primary actions, selected navigation |
| Background | #F5F7FB | Workspace |
| Surface | #FFFFFF | Cards/forms |
| Main text | #172B4D | Content |
| Secondary | #52637A | Supporting labels |
| Border | #DCE3EC | Separation |
| Success | #166534 | Confirmed completion |
| Warning | #92400E | Pending/review |
| Danger | #B91C1C | Errors, overdue |

Light theme first. Optional dark mode later. Contrast must be checked on implemented combinations.
Typography: Inter or system sans; Urdu content Noto Sans Arabic with local fallback. RTL for Urdu content blocks, stable LTR numeric/currency fields.
Base body 14–16px, page heading 24–28px, line height ~1.5; mobile editable inputs at least 16px.
8px spacing rhythm, 12px card radius, restrained shadows, consistent outline icons.
Always labels + status icons/text; color alone does not identify state.
Amounts consistently formatted PKR with tabular numerals. Financial cards distinguish issued, collected, refunded and outstanding.

## 5. Screen inventory — 40 principal screens
| # | Screen | Principal interaction |
|---|---|---|
| 1 | Login / school selection | Account validation; school selector only for users with multiple memberships |
| 2 | Password recovery | Secure reset, no account-disclosure messages |
| 3 | SaaS overview | Schools, subscriptions, renewal tasks |
| 4 | School directory/detail | Onboarding, plan, campuses |
| 5 | SaaS billing | Service invoices, payments, renewal history |
| 6 | School onboarding wizard | Branding → academic setup → import → staff access |
| 7 | Owner dashboard | Collection/dues, attendance, unapproved diaries, follow-ups |
| 8 | Teacher home | Today's assigned classes and tasks |
| 9 | Admission inquiry board | Follow-up dates and conversion |
| 10 | Admission form | Guided sections, save draft |
| 11 | Student directory | Search admission number/name/guardian; filters |
| 12 | Student profile | Overview, family, enrollment, fees, attendance, results, documents |
| 13 | Guardian/family view | Siblings and authorized contacts |
| 14 | Import wizard | Mapping, validation preview, errors, confirmed import |
| 15 | Enrollment/promotion | Preview destination and exceptions before commit |
| 16 | Fee plans/concessions | Effective dates and approval |
| 17 | Monthly fee generation | Preview totals, duplicates prevented |
| 18 | Invoice directory/detail | Open/partial/paid/void status |
| 19 | Fee collection workspace | Search student → select invoices → amount/method → receipt |
| 20 | Receipt preview/history | Print, authorized reversal |
| 21 | Dues dashboard | Aging, class/campus filters, export |
| 22 | Cashier closing | Cash/bank breakdown, variance and approval |
| 23 | Expenses | Category, attachment, approval |
| 24 | Attendance roster | Large status controls, save draft/submit |
| 25 | Attendance report | Daily/monthly, corrections |
| 26 | Diary editor | Subject cards, completeness, draft state |
| 27 | Diary review queue | Changes, required entries, approve/publish |
| 28 | Diary preview/archive | Text/PDF, group share, revision number |
| 29 | Test setup | Subject/date/section/total marks |
| 30 | Marks grid | Keyboard entry, absent/exempt, validation |
| 31 | Result preview/approval | Grade snapshot and correction review |
| 32 | Report sharing desk | Pending/shared, exact recipient, mark shared |
| 33 | Student progress | Test history, subject trends |
| 34 | Timetable | Week grid, teacher clashes |
| 35 | Staff/assignments | Profiles and class/subject scope |
| 36 | Calendar/PTM | Events, appointments and follow-up |
| 37 | Document center | ID/certificate templates and issuance |
| 38 | Reports hub | Saved filters, exports and drill-down |
| 39 | Settings/roles | School settings, permissions, print branding |
| 40 | Audit/support center | Changes, incidents and tickets |

Each screen needs loading, empty, error, permission-denied and populated states. Forms also need dirty/unsaved, validation and save-failed states.

## 6. Desktop and mobile behavior
Desktop >=1280px: 240px sidebar, topbar with campus/session selectors, main workspace max ~1600px.
Tablet 768–1279px: collapsible rail/drawer, two-column cards.
Phone <768px: single column; role-specific bottom navigation (Home, Attendance, Diary, Tests, More).
Breakpoints are design proposals, not external standards.
Mobile student lists become cards. Large marks grids use a one-student editor with Previous/Next; optional grid remains horizontally scrollable.
Fee collection: stacked invoice selection and sticky review/payment action. Never hide totals beneath keyboard.
Sharing desk: student card, guardian last digits, Preview, Open WhatsApp, then explicit Mark shared.
Tap controls target 44–48px as a project choice; keyboard focus visible; status announcements accessible.
Test at widths 360, 390, 768, 1024 and 1440px, plus browser zoom 200%.
Urdu may wrap; names are not truncated without a way to read full text.

## 7. Motion specification
| Interaction | Motion |
|---|---|
| Hover/press | 120–160ms color/opacity |
| Drawer | 180–220ms slide/fade |
| Tab/content change | 150–180ms subtle fade |
| Confirmation toast | 160ms enter; dismissible |
| Mark shared | Status icon transition after successful server save |
| Loading | Skeletons for lists; localized button busy state |

Respect prefers-reduced-motion: remove movement and shimmer. No confetti, looping decorative animations or artificial delays. Monetary totals update immediately; success never appears before server confirmation. Prevent double submit.

## 8. Critical flows
### Fees
Select student/family → unpaid invoice lines → enter amount/payment method → review → transactional save → numbered receipt.
No floating point amounts; DECIMAL(12,2). Store payments independently of invoices with explicit allocations.
Advance amounts remain identifiable credits; refunds/voids use linked reversal records.
Daily closing is cashier/campus-specific. This fee subledger is not a full accounting system.

### Diary
Teacher drafts own subject → completeness review → coordinator publishes immutable revision → staff previews/copies → manually chooses WhatsApp class group → confirms shared.
Missing subject entry is not “no homework”; teacher explicitly sets no-homework when applicable.
Published revisions remain archived; correction requires new revision and separate sharing confirmation.

### Individual test report
Create test → marks draft → validate (0..maximum, absent vs zero) → approve/publish revision → generate each student's report snapshot.
Open registered guardian WhatsApp chat with encoded text; staff sends manually → return → confirm shared.
Track recipient + report revision + channel + staff + timestamp. “Shared” means staff-confirmed, never delivered/read.
Browser sharing of PDFs depends on device support: download PDF and attach manually when native file sharing is unavailable.
Changed marks create a new report revision; previous sharing never counts as sharing the corrected report.

## 9. Database inventory — 50 proposed domain tables
Counts are planning estimates, not a requirement to maximize table count.
| Group | Tables (key fields / relationship intent) |
|---|---|
| Platform — 8 | schools(id,name,status); campuses(school_id); plans; subscriptions(school_id,plan_id); subscription_invoices(subscription_id); subscription_payments(subscription_invoice_id); school_settings(school_id,key,value); support_tickets(school_id,opened_by) |
| Access/staff — 9 | users; school_memberships(user_id,school_id); roles(school_id nullable for platform roles); permissions; role_permissions(role_id,permission_id); membership_roles(membership_id,role_id); membership_campuses(membership_id,campus_id); staff_profiles(membership_id); leave_requests(staff_profile_id) |
| Academic — 5 | academic_sessions(school_id); classes(school_id); sections(campus_id,session_id,class_id); subjects(school_id); teacher_assignments(staff_profile_id,section_id,subject_id nullable for class teacher) |
| Student/admission — 7 | admission_inquiries(school_id,campus_id); admission_applications(inquiry_id nullable); students(school_id,admission_no); guardians(school_id,phone); student_guardians(student_id,guardian_id,relationship,primary_contact); enrollments(student_id,section_id,start_date,end_date,status); student_documents(student_id,file_id) |
| Fees — 10 | fee_heads(school_id); fee_plans(school_id,session_id,class_id); fee_plan_items(plan_id,head_id,amount); student_concessions(enrollment_id,head_id,effective_dates); invoices(enrollment_id,period,status); invoice_items(invoice_id,head_id,amount_snapshot); payments(school_id,campus_id,student_id,receipt_no); payment_allocations(payment_id,invoice_id,amount); payment_reversals(payment_id,amount,approval); cashier_closings(campus_id,cashier_membership_id,date) |
| Attendance/diary — 5 | attendance_sessions(section_id,date); attendance_records(attendance_session_id,enrollment_id,status); diaries(section_id,date,revision,status); diary_items(diary_id,subject_id,author_membership_id); diary_shares(diary_id,revision,shared_by,confirmed_at) |
| Tests/sharing — 6 | tests(section_id,subject_id,date,max_marks); test_results(test_id,enrollment_id,marks,status,revision); grading_schemes(school_id,version,rules_json); report_snapshots(test_result_id,revision,grading_scheme_id,payload); report_shares(report_snapshot_id,guardian_id,channel,status,confirmed_by,confirmed_at); audit_logs(school_id,actor_membership_id,action,target,before_after) |

Total: 8+9+5+7+10+5+6 = 50.
Framework infrastructure tables (sessions, password resets, jobs, failed jobs, tokens) are additional as required.
Extended operational tables (expenses/categories, files, timetable slots, calendar/PTM, certificate templates/issuance, notices, support replies) are added with their modules; budget about 12–18 additional domain tables after design refinement. This is not an exact final migration count.

## 10. Relationships and integrity
School 1:N campuses, sessions, students, guardians, subscriptions.
User M:N schools through school_memberships. Membership M:N campuses and roles.
Campus/session/class 1:N sections. Section 1:N enrollments, diaries, tests and attendance sessions.
Student 1:N enrollments; preserve history on promotion/transfer.
Student M:N guardians through student_guardians; guardian contact does not automatically create login access.
Teacher M:N section/subject via teacher_assignments.
Fee plan 1:N plan items; enrollment 1:N invoices; invoice 1:N invoice items.
Payment M:N invoices via allocations. Reversal references its original payment.
Test 1:N results; enrollment 1:N results. Result 1:N report revisions; report 1:N sharing attempts/confirmations.
Published diary 1:N subject items and revision-specific sharing records.

School-owned rows carry school_id (including denormalized tenant keys on child tables when needed for constraints); composite foreign keys enforce same-school relationships. Campus references must match school.
Tenant context derives from authenticated membership, never trusted request school_id.
Indexes: (school_id,admission_no) unique; (section_id,date) attendance-session unique; (attendance_session_id,enrollment_id) unique; (test_id,enrollment_id) unique; invoice period uniqueness includes enrollment and invoice kind, allowing controlled supplements.
Diary revision uniqueness (school_id,section_id,date,revision); stable group id for revisions.
Amounts/grade/fee descriptions are historical snapshots; later plan changes do not rewrite past invoices.
Use transactions and idempotency for payment posting and bulk fee generation. Backend validates permissions, guardian links and same-tenant access.
UTC storage for timestamps; Asia/Karachi display. Dates for attendance/fees remain school-local calendar dates.
Financial/published records cannot be casually hard-deleted. Retention and correction policies defined before production.
Allocation totals cannot exceed available payment; invoice balance derives from valid charges, allocations and reversals.
Sensitive exports and guardian contact changes are audited.

## 11. Validation before implementation
Tenant isolation tests (including search, exports, file access and cross-school ID substitution).
Fees: partial, advance, duplicate submit, refund, cancellation and closing reconciliation.
Enrollment: promotion, transfer, withdrawal and repeat.
Marks: absent vs zero; max boundary; revision forces re-share.
Sharing: canceled WhatsApp opening remains pending; confirmation save failure remains pending.
Mobile: keyboard, Urdu, long names, slow network, print/PDF.
Backups: restore into isolated test environment; document recovery process.
Demo data must never be mixed with school production data.

## 12. Research sources and interpretation
EduSuite official product page — admissions/fees/exams/attendance/timetable and extended modules:
https://www.edusuite.pk/
Frappe Education official docs — enrollment/session/fees and grouped assessment concepts:
https://docs.frappe.io/education/program-enrollment
https://docs.frappe.io/education/student-group
https://docs.frappe.io/education/education-settings
Google Material guidance — compact/expanded navigation patterns:
https://developer.android.com/design/ui/mobile/guides/layout-and-content/layout-and-nav-patterns
W3C WCAG target size:
https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum
W3C accessibility guidance including interaction animation:
https://www.w3.org/TR/WCAG22/

These sources inform the specification; they do not validate our proposed implementation. No competitor internals audited.
Visual mockups are illustrative raster concepts. Exact strings, counts, financial calculations and accessibility are governed by this specification and must be verified in the real UI.

## 13. Module gap review — 15 October 2026
Reviewed official EduSuite module directory and linked front-office, payroll and exam descriptions against this specification.
This section supersedes earlier references to all payroll being deferred. Initial 40 screen concepts remain references; new screens below are specifications, not generated mockups.

### Decisions for our target schools
Already covered: admissions, students/families, fees, student attendance, multi-campus, teacher allocations, timetable, certificates, manual diary/test sharing, expenses, access and audit.
Scope amendment: only items 1–2 below remain in the first production release. Items 3–5 are deferred per owner decision; retain basic database structure only, with no current UI, navigation, permissions grants or workflows.

Module detail:
1. Staff attendance and basic payroll: daily present/absent/late/leave; salary effective dates, fixed allowances/deductions, approved advances/recoveries, monthly calculation preview, approval, payslips, partial salary payment and outstanding balance. Attendance affects pay only through an explicitly configured school policy and approved review. Draft payroll recalculation never duplicates payment; finalized payroll is immutable and corrected via adjustment. No statutory compliance claim; statutory components require separately verified rules before implementation.
2. Term/annual exams beyond class tests: exam terms, per-subject papers, manual datesheet with conflict warnings, roll slips, subject totals/pass rules, configurable assessment weightages, consolidated term/annual cards and class gazette. Absent/exempt handling, rounding and tie/position policy explicit. Reuse existing tests/results and snapshot sharing, do not duplicate marks storage. Auto timetable solving, seating optimization, invigilator scheduling and question bank remain later.
3. Front office: minimal visitor entry/exit, student early-leave gate passes with authorized pickup contact, reason, approver and departure time. Inquiry follow-ups reuse admissions. Do not require CNIC copies or excessive visitor identifiers by default.
4. Parent complaint/follow-up register: school-owned complaints separate from SaaS technical support, assigned staff, due date, status, resolution notes and limited visibility.
5. Student behaviour/achievement notes: dated observations, positive achievements or incidents, action taken and follow-up; restricted access, objective descriptions. No automatic financial penalty or unreviewed parent sharing.

Strengthen existing financial reporting: daily cash/bank movements, expenses, fee collection, net cash movement, reconciliation and reversals. Do not label cash flow as profit or pretend this is full double-entry accounting.
Optional per-school later: transport, library, inventory, full LMS, hostel, biometrics and advanced AI. Feature flags are school-level and do not weaken tenant isolation.
Retain agreed communications: manual WhatsApp group diary and individual report sharing with staff confirmation; no automatic WhatsApp or email introduced.

### Additional screen specifications — 41 to 50
| # | Screen | Main workflow |
|---|---|---|
| 41 | Staff attendance | Daily roster, leave linkage, correction history |
| 42 | Salary structures and advances | Effective salary components; approved advances and recoveries |
| 43 | Monthly payroll and payslips | Preview, approve, pay, outstanding salary, print |
| 44 | Front-office visitor desk | Check-in/out and purpose with minimal personal data |
| 45 | Student gate passes | Verify pickup authority, approve, issue and record departure |
| 46 | Parent complaints and follow-ups | Assign, due date, resolution and restricted notes |
| 47 | Exam terms and weightages | School-defined terms, pass/rounding/tie policies |
| 48 | Exam datesheet and roll slips | Manual paper scheduling, conflicts, print |
| 49 | Consolidated results and gazette | Subject results, term/annual cards, publish revision |
| 50 | Student observations | Behaviour/achievement entries and follow-ups |

Active screen count is now 46: original 40 concepts plus screens 41–43 and 47–49. Screens 44–46 and 50 are future-only specifications and are not part of the current build. Existing identifiers are preserved to avoid renumbering references. New images have NOT been generated in this review.

### Database planning impact
Original 50 core tables are a baseline, not final schema. All feature commitments must be represented before migrations are authored.
Payroll candidates: staff_attendance_records, salary_structures, salary_structure_items, staff_advances, payroll_runs, payroll_items, payroll_item_components, salary_payments, salary_payment_allocations, advance_recoveries.
Front-office candidates: visitor_logs, student_gate_passes.
Complaints: parent_complaints, complaint_updates.
Observations: student_observations (follow-up fields initially; separate follow-up table if multiple actions needed).
Exams: academic_terms, exam_cycles, exam_papers, assessment_weightages, exam_candidates, consolidated_result_snapshots.
These 21 candidate tables suggest 71 domain tables when added to the original core baseline, BEFORE the earlier operational modules' tables and framework tables. This is a provisional inventory, not a finalized migration count. Consolidation and naming will be resolved during schema design.

Relations:
- Staff profile 1:N attendance records, effective salary structures, advances and payroll items.
- Payroll run belongs to school/campus/period; 1:N payroll items, each linked to staff and salary component snapshots.
- Salary payments M:N payroll items through allocations; advance recoveries link advances to payroll items, with outstanding balances protected transactionally.
- Student gate pass belongs to current enrollment and an authorized pickup contact or separately approved exception; approver and departure recorder are audited.
- Complaint belongs to school/campus, optional student/guardian and assigned membership; 1:N updates.
- Observation belongs to enrollment and authorized author; optional follow-up date/status.
- Session 1:N academic terms; exam cycle belongs to session/term; papers reference existing tests as assessments; exam candidates link cycle/enrollment.
- Weightages have explicit assessment scope, sum validation and effective version; report snapshots preserve policies and marks used for published consolidated results.
- Every new school-owned relationship follows same-school composite constraints and tenant-scoped authorization.
- Salary, advance and cash movements use decimal money, idempotency and immutable finalized records.

### Sources for this review
https://www.edusuite.pk/module/
https://www.edusuite.pk/module/payroll-management-system/
https://www.edusuite.pk/module/exam-management-system/
Front Office linked directly from module directory (visitor, gate-pass and complaint features reviewed).
Reference product descriptions inform coverage; our module priority, schema and controls are independent design recommendations.

## 14. Owner scope decision — 15 October 2026
Front office (including visitors and student gate passes), parent complaints, and behaviour/achievements are deferred. Preserve minimal future database entities/relationships for visitor_logs, student_gate_passes, parent_complaints, complaint_updates and student_observations; do not expose these modules in the current product. Their schema remains provisional until database design. No tables or migrations have been created yet.
Basic payroll/staff attendance and consolidated term/annual exams remain current-release additions. Current build has 46 principal screens, retaining original screen IDs. Sections 13's future module descriptions describe future capability only where overridden here.
