MANUSACARE PATCH v3 — CONDITIONAL LOCATION INPUTS

BASE VERSION
- Apply this patch on top of: manusacare-aurora-frosted-glass-v2.zip
- This patch contains only files changed between v2 and v3, plus this README.

FILES TO REPLACE
1. public/css/ui.css
2. public/js/app.js
3. resources/views/reporter/reports/create.blade.php
4. CHANGELOG-MANUSACARE.txt

HOW TO APPLY
1. Make a backup of your current MANUSACARE project.
2. Extract this ZIP into the MANUSACARE project root.
3. Allow the existing files to be overwritten while preserving folder structure.
4. Refresh the browser. If Laravel serves cached views, run: php artisan view:clear

WHAT CHANGED
- Gedung, lantai, and ruangan custom inputs appear only when the corresponding dropdown is set to “Lainnya”.
- The custom input becomes required only while “Lainnya” is selected.
- Selecting a normal option hides and disables the custom input.
- Existing server-side validation and report storage format are preserved.

IMPORTANT
- Do not apply this patch to an older version or to an unrelated project snapshot.
- No database migration is included or required for this UI behavior.
