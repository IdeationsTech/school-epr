# Step 2 — school theme and academic setup

Pull origin, then run in the existing project folder:

```powershell
composer install
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=AcademicSetupSeeder
npm run build
php artisan database:verify
```

No tables or existing financial records are changed in this phase. Expected schema remains **100 tables, 258 foreign keys, 72 CHECK constraints, 0 mismatches**. The additive seeder grants academic view to owner/principal/coordinator/teacher and manage to owner/principal/coordinator; it preserves school role overrides and does not create users or reset passwords.

## Setup order

1. Academic sessions: add dated, non-overlapping sessions; explicitly select an active current session. The current session is a school setup context, not a subscription/access decision; upcoming sessions can be prepared intentionally. Select another current session before closing the old one.
2. Academic terms: optional term names, order and non-overlapping dates contained in their session.
3. Classes: school-wide class catalogue and display order.
4. Sections: campus + session + class + section name, optional capacity.
5. Subjects: name/code catalogue. Section subjects determine actual offered subjects and whether each needs a diary entry.
6. Users & access: create the teacher account, teacher role and correct campus access.
7. Teaching profiles: employee number, designation, joining date and profile status for an eligible existing teacher account. This is the minimum teaching identity, not the later payroll/leave module.
8. Teacher assignments: subject teachers and at most one class teacher per section. Subject must be offered and teacher must have active account/profile/role and target campus access.

Every screen has list/search, create/edit and confirmed delete for unused records. Current session, date overlap, duplicate offering/assignment, capacity and linked-record rules are server validated. Referenced records cannot be deleted; linked sections cannot move campus/class/session. Linked session dates cannot be rewritten. Reassign active teaching duties before disabling a teacher account/profile or removing the required role/campus. Archived session histories remain visible.

School-wide sessions/classes/subjects are shared across campuses. Campus-specific sections, offerings, teacher candidates and assignments are restricted to the user's assigned active campuses. Academic read-only access never permits writes; existing per-school overrides apply. Super Admin gets no automatic school membership.

## School palette

The public Schola screenshots informed the light-gray background, white cards, navy text, pastel pink selection/actions and pale cyan surfaces. This is original application code using the reference palette direction; no template source/assets were purchased or bundled.

All school theme values are centralized in `resources/css/school-theme-tokens.css`. Radius, shadows, 240ms transitions, card lift, button feedback, keyboard focus and reduced-motion behavior follow the admin interactions, with separate school colors. Admin palette remains scoped separately.

```powershell
npm run theme:school -- --accent "#ffc8f7" --teal "#194b70" --background "#f7f8fa"
npm run build
```

Theme commands update CSS tokens only. Review contrast after custom changes. Font uses the existing local system/Inter stack; no external font service is required.

## Boundary

This completes the agreed step-2 setup foundation. Student admission/enrollment workflows, promotion, fee collection, attendance, diary publishing, result entry and full staff/payroll remain their following modules. Setup counts are actual stored data, not fabricated student/finance charts.
