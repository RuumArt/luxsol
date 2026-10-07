#!/usr/bin/python3
"""One-time administrator migration of literal credentials; never print their values."""
from pathlib import Path
import os
import re
import shutil
import subprocess
import time

ROOT = Path('/var/www/html/bx-site/local/php_interface')
TARGET = ROOT / 'room/settings.local.php'
FIELDS = {
    'room/settings.php': ['B24_LEAD_HOOK', 'SMS_API_KEY', 'RECAPTCHA_SECRET',
                          'PACKETA_API_PASSWORD', 'TELEGRAM_BOT_TOKEN', 'TELEGRAM_CHAT_ID'],
    'init.php': ['RE_SITE_KEY', 'RE_SEC_KEY'],
    'room/classes/Tools/Order.php': ['DPD_CLIENT_KEY', 'DPD_EMAIL', 'DPD_DELIS_ID', 'DPD_PICKUP_ADDRESS_ID'],
}

if TARGET.exists():
    print('Server local settings already exist; preserved.')
    raise SystemExit(0)

literal = r"('(?:\\.|[^'\\])*'|\"(?:\\.|[^\"\\])*\"|[0-9]+)"
values = {}
for relative, names in FIELDS.items():
    source = (ROOT / relative).read_text()
    for name in names:
        if relative.endswith('Order.php'):
            pattern = r'\bconst\s+' + name + r'\s*=\s*' + literal + r'\s*;'
        else:
            pattern = r'define\(\s*[\x27\x22]' + name + r'[\x27\x22]\s*,\s*' + literal + r'\s*\);'
        match = re.search(pattern, source)
        if match is None:
            raise RuntimeError('Cannot safely migrate literal setting: ' + name)
        values[name] = match.group(1)

backup = Path('/root/deploy-backups') / ('luxsol_settings_migration_' + str(time.time_ns()) + '.tar.gz')
backup.parent.mkdir(mode=0o700, exist_ok=True)
os.umask(0o077)
subprocess.run(['tar', '-czpf', str(backup), '-C', str(ROOT), '--', *FIELDS.keys()], check=True)
content = '<?php\n\n// Server integration credentials. Excluded from Git and deployment.\n'
content += ''.join("define('" + name + "', " + value + ");\n" for name, value in values.items())
with TARGET.open('x') as target:
    target.write(content)
shutil.chown(TARGET, user='www-data', group='www-data')
TARGET.chmod(0o600)
print('Migrated ' + str(len(values)) + ' server settings; original files backed up; values not displayed.')
