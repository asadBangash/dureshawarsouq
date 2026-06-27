#!/usr/bin/env python3
import json
import os
import re
import subprocess

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MEDIA_DIR = os.path.join(ROOT, 'public', 'media')
DB_LIST = os.path.join(ROOT, 'tools', 'db-media-files.txt')
MYSQL = r'D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe'
DB = 'dureshahwarsouq'

IMG_RE = re.compile(r'([0-9]{8,}-[^"\s\\]+\.(?:jpg|jpeg|png|gif|webp|ico|svg))', re.I)


def load_db_filenames():
    names = set()
    if os.path.isfile(DB_LIST):
        with open(DB_LIST, encoding='utf-16-le', errors='ignore') as f:
            for line in f:
                line = line.strip()
                if line:
                    names.add(line)
    # tp_options JSON may contain extra image refs
    try:
        out = subprocess.check_output(
            [MYSQL, '-u', 'root', DB, '-N', '-e', 'SELECT option_value FROM tp_options'],
            text=True,
            encoding='utf-8',
            errors='ignore',
        )
        for match in IMG_RE.findall(out):
            names.add(match)
    except Exception as exc:
        print('Could not scan tp_options:', exc)
    return names


def main():
    db_files = load_db_filenames()
    local_files = set(os.listdir(MEDIA_DIR)) if os.path.isdir(MEDIA_DIR) else set()
    missing = sorted(f for f in db_files if f not in local_files)

    print(f'DB-referenced filenames: {len(db_files)}')
    print(f'Local media files:       {len(local_files)}')
    print(f'Missing on disk:         {len(missing)}')
    if missing:
        print('\nFirst 25 missing files:')
        for name in missing[:25]:
            print(' ', name)
        out = os.path.join(ROOT, 'tools', 'missing-media-files.txt')
        with open(out, 'w', encoding='utf-8') as fh:
            fh.write('\n'.join(missing))
        print(f'\nFull missing list: {out}')


if __name__ == '__main__':
    main()
