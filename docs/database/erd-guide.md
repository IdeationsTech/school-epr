# Full database ERD for dbdiagram.io

Open [school-erp-full.dbml](school-erp-full.dbml), download the raw file, or copy its entire contents.
At https://dbdiagram.io/ create a diagram, replace the sample DBML with this file's contents, and save.
If the editor provides Import DBML, the same file can be imported directly.

## Coverage
- 100 tables: 91 domain, 8 Laravel infrastructure, 1 migration repository.
- 236 physical foreign keys, including school-scoped composite relationships.
- Primary keys, nullability, money precision, declared unique keys and database checks.
- Module groups; future-only front office/complaint/observation storage is identified separately.

Validated with @dbml/core's current DBML v2 parser: 100 tables and 257 references.
No database data, credentials or student records are embedded.

Laravel's sessions.user_id is indexed but has no physical FK in the existing migration, so no invented line is drawn for it. Other framework cache/job tables can likewise appear disconnected. That matches the installed structure.
Relation arrows generally show many-to-one foreign keys; uniqueness on a child FK can further constrain cardinality.

This file is a diagram, not the installation script. Continue creating/updating MySQL through Laravel migrations.
Database updates must be reflected in database/schema/schema.json and the generator, then run:

```bash
python scripts/generate_erd.py
```

Framework definitions in the generator mirror the committed framework migrations. Update them if those migrations change.
