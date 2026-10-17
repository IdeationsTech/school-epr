# Phase 5: student attendance

After GitHub Desktop **Pull origin**, use the existing project folder:

```powershell
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=AttendanceAccessSeeder
php artisan database:verify
npm run build
```

Refresh with Ctrl+F5. No dependency installation, .env, database recreation or password change is needed. The nullable submission timestamp/day note and default-zero revision are added to existing attendance sessions; legacy marks remain. Schema and DBML still contain 100 tables, 258 foreign keys and 72 enforced CHECK constraints.

## School workflow

The school sidebar contains Attendance overview, Mark attendance and Attendance reports.

- Choose section and Pakistan date. The roster is based on dated enrollments, including past withdrawn/completed enrollments where the student was eligible on that date; future admissions are excluded. Future attendance and dates outside the academic session are rejected.
- No mark is preselected. Choose Present, Absent, Late or Leave for each student; remarks require a mark. Bulk status applies only to the visible filtered students and shows the affected count. Clear mark also clears that row's note.
- Save a partial draft or submit a fully marked register. Draft counts are unofficial. A submitted register is locked against ordinary teacher edits. Authorized corrections require a reason of at least five characters and are submitted as a new audited revision. Corrections keep stable attendance record IDs where the same enrollment remains marked.
- No-class/holiday is an explicit section/date action requiring correction permission, a reason and confirmation. It removes that day's marks from official totals; prior values remain in audit. Replacing it with normal attendance is another reasoned correction. No weekend or holiday is automatically assumed.
- Concurrent tabs use the saved revision. A stale save is rejected rather than overwriting a newer register; reload and review first. Confirmations precede submission/no-class conversion. Unsaved changes prompt on ordinary sidebar navigation, logout, school switching or browser close; save a draft before leaving a register.
- Revision history shows actor, Pakistan timestamp, reason and saved before/after marks. Existing legacy records do not acquire invented earlier audit history. Enrollment-history mismatches are flagged and existing marks retained; correct the enrollment issue before attempting another save. A student cannot have submitted attendance in two sections on the same school/date.

## Permissions and scope

Existing attendance.manage permits ordinary marking. Additive AttendanceAccessSeeder supplies attendance.view, attendance.oversee and attendance.correct defaults. Existing school overrides remain effective.

Owner/principal/coordinator have default oversight/correction within accessible campuses. A teacher requires an active staff profile and **class-teacher** assignment (subject teaching alone does not grant daily class-register access), plus campus access. Explicit oversight can be granted to another role. Oversight selects accessible sections but is not by itself a read/write permission. View-only roles cannot save; a correction-only role must supply a correction reason. Historical backfill into a closed session requires correction permission and reason; ordinary active-session marking cannot bypass this. Owners can review inactive-campus history; other staff remain within their active assigned campuses. Provider admins receive no school operational access. Every endpoint checks the attendance license module and school membership.

## Monthly report

Report days stop at today and session boundaries. The matrix distinguishes P present, A absent, L late, E leave, D draft, U unsubmitted, O no class, and '-' not enrolled. A day without any enrolled students and without a register is identified separately in the section summary and does not fabricate absences.

Attendance percentage is **(Present + Late) / submitted marked days × 100**; leave counts as a marked day. Draft, unsubmitted, no-class and not-enrolled days do not enter the percentage denominator. A student with no official marked days has no percentage. This explicit initial policy can be changed later if a school needs leave excluded or different late weighting.

Search students, scroll the monthly matrix and click a date to open the daily register. Export visible rows to a UTF-8 CSV usable in Excel; formula-like names/values are escaped. Export includes the actual loaded month/section, daily codes, counts and percentage. If enrollment history no longer matches saved marks, a warning is shown rather than silently rewriting historical attendance.

This release covers student attendance. Staff attendance/payroll, biometric devices, automatic absent messages, and daily diary/WhatsApp sharing remain separate modules.

Validation covers tenant/campus/class scope, teacher permissions, enrollment dates, future dates, drafts, complete submission, stale revisions, corrections/audit, no-class conversion, record ID stability, historical roster conflicts, module entitlement, monthly percentages, CSV download and desktop/mobile browser flows.
