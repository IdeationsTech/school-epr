"""Generate the DBML diagram from the committed schema contract.

Run from any directory: python scripts/generate_erd.py
Only diagram files are written; this script never connects to MySQL.
"""
from pathlib import Path
import json

ROOT = Path(__file__).resolve().parents[1]
domain = json.loads((ROOT / 'database/schema/schema.json').read_text())
lookup = {t['name']: t for t in domain}

def q(value):
    return "'" + value.replace('\\', '\\\\').replace("'", "\\'") + "'"

out = [
    '// School ERP — implemented database ERD',
    f'// {len(domain)} domain + 8 Laravel infrastructure + 1 migration tracking = {len(domain)+9} tables.',
    '// Paste this entire file into the DBML editor at https://dbdiagram.io/.',
    '// Diagram only: Laravel migrations remain the source for database creation.',
    '// Composite references preserve school isolation. No invented logical FKs.',
    '',
    "Project school_erp {",
    "  database_type: 'MySQL'",
    f"  Note: 'MySQL 8.4 school ERP. {len(domain)+9} implemented tables. Future-only tables are grouped separately. Status values are strings unless a check explicitly restricts them. Cross-row business rules require application services.'",
    '}',
    '',
]

# These 9 tables mirror Laravel's three framework migrations and migration repository.
framework = {
    'users': [
        'id "bigint unsigned" [pk, increment, not null]',
        'name varchar(255) [not null]', 'email varchar(255) [not null, unique]',
        'email_verified_at timestamp [null]', 'password varchar(255) [not null]',
        'remember_token varchar(100) [null]', 'created_at timestamp [null]', 'updated_at timestamp [null]',
    ],
    'password_reset_tokens': [
        'email varchar(255) [pk, not null]', 'token varchar(255) [not null]', 'created_at timestamp [null]',
    ],
    'sessions': [
        'id varchar(255) [pk, not null]',
        "user_id \"bigint unsigned\" [null, note: 'Indexed Laravel identity reference; no physical foreign key exists in the migration.']",
        'ip_address varchar(45) [null]', 'user_agent text [null]', 'payload longtext [not null]',
        'last_activity int [not null]',
    ],
    'cache': ['key varchar(255) [pk, not null]', 'value mediumtext [not null]', 'expiration bigint [not null]'],
    'cache_locks': ['key varchar(255) [pk, not null]', 'owner varchar(255) [not null]', 'expiration bigint [not null]'],
    'jobs': [
        'id "bigint unsigned" [pk, increment, not null]', 'queue varchar(255) [not null]', 'payload longtext [not null]',
        'attempts "smallint unsigned" [not null]', 'reserved_at "int unsigned" [null]',
        'available_at "int unsigned" [not null]', 'created_at "int unsigned" [not null]',
    ],
    'job_batches': [
        'id varchar(255) [pk, not null]', 'name varchar(255) [not null]', 'total_jobs int [not null]',
        'pending_jobs int [not null]', 'failed_jobs int [not null]', 'failed_job_ids longtext [not null]',
        'options mediumtext [null]', 'cancelled_at int [null]', 'created_at int [not null]', 'finished_at int [null]',
    ],
    'failed_jobs': [
        'id "bigint unsigned" [pk, increment, not null]', 'uuid varchar(255) [not null, unique]',
        'connection varchar(255) [not null]', 'queue varchar(255) [not null]', 'payload longtext [not null]',
        'exception longtext [not null]', 'failed_at timestamp [not null, default: `CURRENT_TIMESTAMP`]',
    ],
    'migrations': ['id "int unsigned" [pk, increment, not null]', 'migration varchar(255) [not null]', 'batch int [not null]'],
}
framework_indexes = {
    'sessions': ['user_id', 'last_activity'],
    'cache': ['expiration'], 'cache_locks': ['expiration'], 'jobs': ['queue'],
    'failed_jobs': ['(connection, queue, failed_at)'],
}
for name, columns in framework.items():
    out.append('Table ' + name + ' {')
    out += ['  ' + c for c in columns]
    if name in framework_indexes:
        out += ['  indexes {'] + ['    ' + c for c in framework_indexes[name]] + ['  }']
    out += ['}', '']

types = {'id': '"bigint unsigned"', 'uint': '"int unsigned"', 'string': 'varchar(160)',
         'text': 'text', 'json': 'json', 'date': 'date', 'datetime': 'datetime',
         'time': 'time', 'bool': 'boolean', 'money': 'decimal(12,2)', 'decimal': 'decimal(12,2)'}

for idx, t in enumerate(domain, 10):
    out.append('Table ' + t['name'] + ' {')
    if t['group'] == 'future':
        out.append("  Note: 'Future-only storage. No active screens, routes or default permission grants.'")
    out.append('  id "bigint unsigned" [pk, increment, not null]')
    if t['tenant']:
        out.append('  school_id "bigint unsigned" [not null]')
    for c in t['columns']:
        settings = ['null' if c['nullable'] else 'not null']
        if 'default' in c:
            value=c['default']
            settings.append('default: '+(str(value).lower() if isinstance(value,(bool,int,float)) else q(value)))
        elif c['type'] == 'bool':
            settings.append('default: false')
        out.append('  ' + c['name'] + ' ' + ('varchar('+str(c['length'])+')' if c.get('length') else types[c['type']]) + ' [' + ', '.join(settings) + ']')
    out += ['  created_at timestamp [null]', '  updated_at timestamp [null]']
    if t['tenant'] or t['unique']:
        out.append('  indexes {')
        if t['tenant']:
            out.append(f"    (school_id, id) [unique, name: 't{idx}_school_id']")
        for j, cols in enumerate(t['unique']):
            expression = cols[0] if len(cols) == 1 else '(' + ', '.join(cols) + ')'
            out.append(f"    {expression} [unique, name: 't{idx}_u{j}']")
        out.append('  }')
    if t['checks']:
        out.append('  checks {')
        for j, expression in enumerate(t['checks']):
            out.append(f"    `{expression}` [name: 't{idx}_check{j}']")
        out.append('  }')
    out += ['}', '']

refs = []
for idx, t in enumerate(domain, 10):
    relationships = [('school_id', 'schools', False)] if t['tenant'] else []
    relationships += [(c, target, t['tenant'] and target in lookup and lookup[target]['tenant'])
                      for c, target in t['refs'].items()]
    for j, (column, target, composite) in enumerate(relationships):
        source = '(school_id, ' + column + ')' if composite else column
        parent = '(school_id, id)' if composite else 'id'
        refs.append(f"Ref t{idx}_fk{j}: {t['name']}.{source} > {target}.{parent} [delete: restrict, update: no action]")
for idx,t in enumerate(domain,10):
    for j,r in enumerate(t.get('composite_refs',[])):
        child='('+', '.join(r['columns'])+')'
        parent='('+', '.join(r['references'])+')'
        refs.append(f"Ref t{idx}_extra_fk{j}: {t['name']}.{child} > {r['table']}.{parent} [delete: restrict, update: no action]")
out += ['// Physical foreign keys from all committed migrations.', *refs, '']

groups = {'laravel_infrastructure': list(framework)}
for t in domain:
    groups.setdefault(t['group'], []).append(t['name'])
for group, tables in groups.items():
    out += ['TableGroup ' + group + ' {', *['  ' + t for t in tables], '}', '']

destination = ROOT / 'docs/database/school-erp-full.dbml'
destination.write_text('\n'.join(out))
assert len(framework) == 9
print(json.dumps({'tables': len(framework)+len(domain), 'foreign_keys': len(refs), 'file': str(destination)}))
