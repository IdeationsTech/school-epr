# Phase 6: daily diary and manual WhatsApp group sharing

After GitHub Desktop **Pull origin**, run in the existing project folder:

```powershell
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=DiaryAccessSeeder
php artisan database:verify
npm run build
```

Then Ctrl+F5. No package installation or .env change is required. Existing data/passwords remain. The migration adds a default-zero `lock_version` and nullable JSON publication `snapshot` to diaries. Existing 100 tables, 258 foreign keys and 72 CHECK constraints remain; DBML is updated. Legacy publications without snapshots stay retained and cannot be automatically shared or rewritten as invented historical publications.

## Setup and roles

Offer subjects to each section in Academic setup and mark the required diary subjects. Assign each teacher to their actual subject; assigning a class teacher alone does not permit writing every subject. Staff need an active profile, campus access and section assignment. Existing `diary.write` permits assigned-subject entry; `diary.publish` permits coordinator oversight of accessible campuses and publication. Owners have school-wide access. Class teachers with diary.write can submit a complete class diary for review. Subject-only teachers cannot lock the whole class for review. Publication permission does not by itself grant writing when diary.write is explicitly denied.

The optional new `diary.view` permission grants read-only access within assigned sections. Its seeder registers the permission without new default grants or changing any existing role overrides. Existing diary.write / diary.publish already permit reading. Sharing also requires `reports.confirm_share`. Every diary endpoint checks school membership and the `daily_diary` license module; sharing controls additionally require `manual_whatsapp_sharing`. Platform administrators receive no implicit school access.

## Workflow

1. Open Diary overview or Daily diary, select class/section and a date within the academic session, through the current Pakistan date.
2. Teachers save only their changed assigned subjects. Missing homework is not no-homework: explicitly check **No homework** when applicable. Clearing all fields removes that draft subject entry. Classwork-only drafts can be saved but cannot be submitted. Entered optional subjects must also be complete before submission.
3. Class teacher/coordinator submits the saved complete diary. Entries are locked during review. Coordinator either returns it with feedback (five or more characters) or confirms publication. There is no separate mandatory second-person approval; the coordinator can review and publish their own entries.
4. Publication archives school/class/subject names and diary text in an immutable snapshot. Later reference name changes do not change its preview, WhatsApp text or PDF. Review activity records actor, timestamp and before/after content. Concurrent tabs must reload after a conflict; no silent last-save overwrite.
5. Preview the published diary, copy its text or open WhatsApp with the text. Choose the correct class group manually and send there. The software does not select a group, send automatically, or claim delivery/read confirmation. Long WhatsApp drafts may exceed browser/device URL limits; use Copy diary text and paste in the group, or print/save PDF and attach manually.
6. Return and explicitly confirm sharing, optionally naming the class group. Opening WhatsApp/copying/printing does not create a sharing record. Repeated confirmation by the same staff member for the same revision and identical note is idempotent. Sharing confirmations show staff name, Pakistan time and note.
7. Corrections require a coordinator reason and create a new draft revision. Submit/review/publish it again. Old published revisions and their sharing logs stay archived. New revisions do not inherit sharing confirmation. Once a newer draft exists, the older publication cannot be confirmed as the current diary.

## Screens and output

- Diary overview: selected-date class cards, missing required subjects, latest workflow status and revision-specific sharing counts.
- Daily diary: subject cards, explicit no-homework, review and publication controls, correction revisions, paginated before/after activity and sharing history.
- Published preview / browser **Print / Save PDF**: archived content, A4 layout with multi-page support. It uses the browser's PDF destination, not a server PDF package. PDF attachment to WhatsApp is manual.
- Mobile: stacked fields/cards, readable published diary and the same review/sharing controls. Existing school colors and motion tokens are retained, with reduced-motion support.

Unsaved entries prompt on ordinary sidebar navigation, school switching, logout and browser close. Save drafts before browser back/forward navigation. Archived campus/sessions cannot edit, review or publish diary content; authorized staff can still preview and manually share an existing published copy. Removed subject offerings that conflict with saved draft entries are flagged; restore/review academic setup before submission rather than silently dropping saved work.

This phase adds class diary group sharing only. Individual test result sharing, parent app, automatic WhatsApp messaging and delivery receipts are separate work.
