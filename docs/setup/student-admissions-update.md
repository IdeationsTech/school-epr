# Phase 3: Admissions, students and guardians

Pull `main`, then from the project directory run:

```powershell
composer install
php artisan optimize:clear
php artisan migrate
php artisan database:verify
npm ci
npm run build
```

Restart `php artisan serve --port=8001` if necessary and refresh with Ctrl+F5. Existing users, passwords and records remain. Do not recreate the database. No new third-party runtime package is required. The migration adds nullable student address/previous-school and guardian WhatsApp columns; existing rows can be completed through profile editing. The schema contract and full DBML are updated (100 tables, 258 foreign keys, 72 CHECK constraints).

## School workflow

School sidebar: Students, New admission, Guardians & families, Import students. Finish academic sessions/classes/sections first. Admission creates the student, primary guardian relationship and enrollment in one transaction. Admission numbers and CNIC/B-Form numbers are unique per school. CNIC/B-Form is optional where unavailable, but supplied numbers must have 13 digits; this is format validation, not NADRA verification. Date of birth, gender, name, admission date, address, section and primary guardian contact are required.

Reuse an existing guardian for siblings rather than creating duplicate contacts. Family tabs show linked siblings and relationship-specific primary/reports/pickup permissions. Updating the directory contact updates every linked student. A student always retains a primary guardian; make another primary before removing an old link. The directory is manager-only. Shared-profile edits require access to all campuses in that record's history. Read-only users cannot see identity numbers or private documents.

Student profile tabs contain personal details, family, dated enrollment history and private documents. Change section, withdraw or readmit with an effective date and reason. Existing enrollments remain; they are never overwritten with a new class. Backdated transitions with existing fee/attendance/result records are blocked. Future class moves/withdrawals are not scheduled by this release. Readmission may start in a future active session. Students with old enrollment history keep their past placement. Withdrawal retains data rather than deleting it.

Photo, birth certificate, B-Form, previous-school certificate and other documents accept PDF/JPEG/PNG up to 5 MB; photos accept images only. Files live under `storage/app/private`, retrieved through authorized routes. No `storage:link` is needed. Maintain database and private-storage backups together. School student limits count active registrations, including future starts, at admission/readmission; access additionally checks subscription/module entitlement and existing school/campus permissions.

## Import

Download the CSV template from Import students. CSV and XLSX first-sheet values are supported (500 rows, 5 MB maximum). PHP `zip` is needed for XLSX; CSV works without it. Enable `extension=zip` in the same PHP installation running Artisan if absent. No `.xls`, formulas, macros, external resources or automatic Excel numeric-date conversion: store dates as YYYY-MM-DD text and IDs/phones as text. The screen lists permitted section IDs. Header order must match the template exactly.

Preview shows row errors without saving students and lasts 30 minutes in the authenticated session. Commit uses that server-held preview, checks authorization, uniqueness, current capacity and license again, and rolls back the entire file on any error. Existing guardians are reused by CNIC only when name and phone match; blank CNIC creates a separate guardian for each row. A successful preview does not reserve seats. Guardian contact changes happen in the directory, not implicitly during an import.

Admissions/application inquiry pipelines, issued ID cards/certificates, attendance, and fee assignment/collections remain separate later workflows. This release is direct student admission, not an inquiry/application conversion module.

Validation: feature coverage includes tenant/campus access, duplicate identities, required fields, capacities, license entitlements, sibling reuse, identity privacy, dated transitions, private files, CSV atomicity and bounded XLSX parsing. Browser flow covers admission, siblings, documents, shared contact editing, import confirmation and mobile width.
