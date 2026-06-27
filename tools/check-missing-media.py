#!/usr/bin/env python3
"""Compare image filenames in the SQL dump vs local public/media folder."""
import os
import re
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SQL_FILE = os.path.join(ROOT, 'thedureshawar.sql')
MEDIA_DIR = os.path.join(ROOT, 'public', 'media')

IMG_RE = re.compile(
    r"'((?:\d{8,})-(?:\d{6,}-)?[^'\s]*\.(?:jpg|jpeg|png|gif|webp|ico|svg))'",
    re.IGNORECASE,
)


def main():
    if not os.path.isfile(SQL_FILE):
        print(f'SQL file not found: {SQL_FILE}')
        sys.exit(1)
    if not os.path.isdir(MEDIA_DIR):
        print(f'Media folder not found: {MEDIA_DIR}')
        sys.exit(1)

    with open(SQL_FILE, encoding='utf-8', errors='ignore') as f:
        sql = f.read()

    referenced = set(IMG_RE.findall(sql))
    local = set(os.listdir(MEDIA_DIR))
    missing = sorted(f for f in referenced if f not in local)
    extra = sorted(f for f in local if f not in referenced)

    print(f'SQL dump: {SQL_FILE}')
    print(f'Media dir: {MEDIA_DIR}')
    print(f'Referenced in SQL: {len(referenced)}')
    print(f'Local files:       {len(local)}')
    print(f'Missing locally:   {len(missing)}')
    print(f'Extra local only:  {len(extra)}')
    print()
    if missing:
        print('Missing files (first 30):')
        for name in missing[:30]:
            print(f'  {name}')
        if len(missing) > 30:
            print(f'  ... and {len(missing) - 30} more')
        out = os.path.join(ROOT, 'tools', 'missing-media-files.txt')
        with open(out, 'w', encoding='utf-8') as fh:
            fh.write('\n'.join(missing))
        print(f'\nFull list written to: {out}')


if __name__ == '__main__':
    main()
