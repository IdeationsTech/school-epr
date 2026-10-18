# Phase 7: tests, exams, results and individual guardian sharing

After GitHub Desktop **Pull origin**, run:

```powershell
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=ExamAccessSeeder
php artisan database:verify
npm run build
```

Then Ctrl+F5. No new dependencies or .env changes. Existing data/passwords stay retained. The migration adds default-zero test/cycle version counters, default-zero passing marks and nullable correction reason; no existing publication is invented. Schema/DBML: 100 tables, 258 foreign keys, 72 CHECK constraints.

## Setup and workflow

School sidebar: **Tests & exam setup**, **Marks & review**, **Results & report cards**.

1. Offer section subjects and assign active subject teachers with campus access. Coordinators with tests.publish oversee accessible campuses. A class-teacher assignment allows reading class papers/consolidated cards, but writing needs actual subject assignment and tests.enter_marks. Optional tests.view grants read-only access within assignments; its seeder changes no default grants or existing overrides. Provider administrators receive no implicit school access. License module tests_results is required, with manual_whatsapp_sharing also required for sharing controls.
2. Coordinator creates immutable grading versions: unique minimum-percent thresholds including zero, subject passing percentage, two-decimal or whole-percent rounding (half up), competition/dense/no-position tie policy. New versions do not change earlier reports. Unsupported legacy schemes/publication snapshots require review; data is retained.
3. Create a standalone test or a term/exam cycle with papers. Cycle dates must fit the academic session and optional term; papers must match cycle scheme and dates. Individual test maximum/passing marks support two decimals. Section, subject, date, assessment type, grading version and cycle are fixed after creation; draft title, maximum/passing marks and paper schedule can be corrected. To change fixed links, create a different assessment. Future tests can be scheduled; marks cannot be saved before their Pakistan date.
4. Optional paper time/room produces a manual date sheet. Overlapping section/room papers in the same campus produce warnings (not automatic scheduling). Register candidates to generate stable school/cycle-unique E<enrollment-ID> rolls and individual printable roll slips. No automatic seating/invigilation solver is included.
5. Marks start Pending. Present requires a mark (zero allowed), while absent/exempt/pending require no marks. Optional remarks are bounded. Bulk absent/clear affects only visible filtered students. Dated enrollments determine the roster; withdrawn students stay eligible for their historical dates. Conflicting/overlapping enrollment history is flagged; saved rows are not silently removed.
6. Download the marks CSV template, fill it in Excel or use CSV/XLSX import preview: admission_no,status,marks,remarks. Up to 500 rows, 2 MB, values only; formulas/external XML entities are rejected. Unknown/duplicate admissions or bad marks reject the whole preview. Preview does not write to the database. Review, then Save marks draft. Partial file imports preserve other **saved** roster values, replacing the displayed preview; save unsaved work before importing.
7. Save the full roster, submit for review, then coordinator returns with feedback or publishes immutable per-student snapshots. Published names, grades, percentages and marks do not change when references change later. Review/correction reasons and before/after marks remain in paginated audit. Revision checks reject stale tabs. No mandatory second-person approval rule: a coordinator can publish their own reviewed work.
8. Corrections require a coordinator reason and reopen a draft. Published snapshots remain archived; sharing is disabled until a new revision is published. Corrected reports do not inherit sharing confirmation.

## Calculation policies

Individual assessments: percent = marks / maximum, rounded under the chosen grading policy; grades use that rounded percentage. Pass/fail uses the exact original passing marks. Present students receive positions by exact marks, including failing present students. Absent means Fail with no marks/percentage/position; exempt means Exempt with no marks/percentage/position.

Consolidated cards: every offered subject requires papers for each positive configured assessment group. Default is Exam 100%. Configure per-cycle/class/subject weightages totaling **exactly 100%**. Multiple papers in one group combine marks/max before applying its weight. Exempt or not-enrolled components do not add marks/max. Entirely exempt/ineligible groups are excluded and remaining weights renormalized. Subjects contribute equally to overall percentage; each non-exempt subject must pass the scheme passing percentage. Overall percentages use the same rounding policy. All-exempt candidates have no percentage/position. Positions include passing candidates only. Raw marks/max are not a substitute for weighted percentages.

All section/cycle papers must be published before consolidation; missing component papers or conflicting historical rosters block publication. Coordinator reviews the class/date sheet/weightages, confirms consolidated publication, and supplies a correction reason on repeat publication. Published weightages are frozen; use a new cycle for policy changes. Paper corrections invalidate old consolidated cards until republished. Every section gets its own candidate snapshots even though cycle publication counters are shared.

## Reports, print and sharing

Open a student's published report from the marks register or consolidated list. Latest rows show by default; archived versions remain selectable. Class gazette uses published snapshots and labels outdated cards. CSV export protects formula-like cells. Browser Print / Save PDF supports individual reports, consolidated cards, class gazette, date sheet and candidate roll slips; attachments to WhatsApp are manual.

Only linked guardians with can_receive_reports and a valid registered Pakistan mobile/WhatsApp number are offered as recipients. The software opens that specific chat with encoded report text; staff must send manually. Opening/copying/printing does not mark it shared. Return and explicitly confirm the exact guardian, phone and snapshot revision. The server rechecks consent, phone, license, section scope and current publication. Number changes require reload/review before confirmation. Duplicate confirmations are idempotent; another deliberate sharing record requires a resend reason. Staff-confirmed is never delivered/read status.

The same student's corrected report needs a fresh sharing confirmation. Old snapshot/guardian/recipient-phone/staff/time records remain archived. Current guardian details are revalidated rather than allowing arbitrary phone entry in the sharing dialog.

Archived campus/sessions cannot edit or publish; permitted staff may preview/share existing current reports. Unsaved marks prompt on ordinary sidebar navigation, logout, school switching and browser close. Save before browser back/forward navigation. Draft metadata forms are separate from marks; save one before editing the other.
