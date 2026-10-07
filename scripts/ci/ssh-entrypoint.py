#!/usr/bin/python3
"""Forced SSH command for the luxsol-deploy account. Installed by an administrator."""
import os
import re
import sys

command = os.environ.get('SSH_ORIGINAL_COMMAND', '')
if re.fullmatch(r'(prepare|check|release|rollback) [0-9a-f]{40}', command):
    action, commit = command.split()
    os.execv('/usr/bin/sudo', ['sudo', '-n', '/usr/local/sbin/luxsol-ci-release', action, commit])
elif command.startswith('rsync --server '):
    # The distribution's rrsync validates all flags and confines writes to staging.
    os.execv('/usr/bin/rrsync', ['rrsync', '-wo', '-no-del', '/var/lib/luxsol-deploy/incoming'])
else:
    sys.exit('Only deployment commands and restricted rsync are permitted.')
