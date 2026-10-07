#!/usr/bin/python3
"""Fixed privileged deployment operations. Never execute PHP application code as root."""
import fcntl
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import sys
import time

SITE = Path('/var/www/html/bx-site')
STATE_DIR = Path('/var/lib/luxsol-deploy')
STAGE = STATE_DIR / 'incoming'
STATE = STATE_DIR / 'state.json'
BACKUPS = Path('/root/deploy-backups')
PRIVATE = Path('local/php_interface/room/settings.local.php')
PROTECTED = ['bitrix', 'upload', 'tmp', 'local/php_interface/include/sale_payment/gpweb/keys',
             str(PRIVATE)]


def run(*args):
    subprocess.run(args, check=True)


def files():
    result = []
    for root, dirs, names in os.walk(STAGE):
        for name in dirs + names:
            path = Path(root) / name
            rel = path.relative_to(STAGE)
            if path.is_symlink() or (not path.is_dir() and not path.is_file()):
                raise RuntimeError('Symlinks and special files are not allowed in deployment.')
            if any(str(rel) == p or str(rel).startswith(p + '/') for p in PROTECTED):
                raise RuntimeError('Protected deployment path: ' + str(rel))
            if name.startswith('.env') or name.endswith(('.pem', '.key', '.p12', '.pfx', '.sql', '.log', '.prod-backup')):
                raise RuntimeError('Private or runtime file in deployment: ' + str(rel))
            if str(rel).startswith('local/tools/psc/') and path.suffix == '.xlsx':
                raise RuntimeError('Server postal-code data must not be overwritten.')
            # A destination symlink could cause privileged writes outside the site.
            for parent in [SITE / rel, *(SITE / rel).parents]:
                if parent == SITE.parent:
                    break
                if parent.is_symlink():
                    raise RuntimeError('Symlink at deployment destination: ' + str(rel))
            if path.is_file():
                result.append(str(rel))
    if 'local/php_interface/init.php' not in result:
        raise RuntimeError('Incomplete deployment payload.')
    return sorted(result)


def clear_cache():
    for name in ['cache', 'managed_cache', 'stack_cache']:
        path = SITE / 'bitrix' / name
        if path.is_symlink():
            raise RuntimeError('Refusing to clear a symlink cache directory.')
        if path.is_dir():
            for item in path.iterdir():
                if item.is_dir() and not item.is_symlink():
                    shutil.rmtree(item)
                else:
                    item.unlink()


def rollback(state):
    # Only remove files introduced by this run; the archive restores all previous files.
    for name in state.get('new_files', []):
        path = SITE / name
        if path.is_file() or path.is_symlink():
            path.unlink()
    run('tar', '-xzpf', state['backup'], '-C', str(SITE))
    clear_cache()
    state['released'] = False
    STATE.write_text(json.dumps(state))
    print('Backup restored: ' + state['backup'])


def main(action, commit):
    if action == 'prepare':
        if not (SITE / PRIVATE).is_file():
            raise RuntimeError('Server settings.local.php must be provisioned before deploying.')
        if STAGE.exists():
            shutil.rmtree(STAGE)
        STAGE.mkdir(mode=0o700)
        shutil.chown(STAGE, user='luxsol-deploy', group='luxsol-deploy')
        STATE.write_text(json.dumps({'commit': commit, 'released': False}))
        STATE.chmod(0o600)
        print('Staging prepared for ' + commit)
        return

    state = json.loads(STATE.read_text())
    if state['commit'] != commit:
        raise RuntimeError('Deployment commit does not match the prepared staging area.')
    if action == 'rollback':
        if not state.get('released'):
            raise RuntimeError('No deployment available to roll back.')
        rollback(state)
        return

    payload = files()
    # Syntax only; this never runs the deployed PHP files or touches the database.
    for name in payload:
        if name.endswith('.php'):
            subprocess.run(['php', '-d', 'short_open_tag=1', '-d', 'display_errors=0', '-l', str(STAGE / name)],
                           check=True, stdout=subprocess.DEVNULL)
    if action == 'check':
        run('rsync', '-rltcn', '--itemize-changes', str(STAGE) + '/', str(SITE) + '/')
        print('Server preflight passed: ' + str(len(payload)) + ' files.')
        return

    if state.get('released'):
        raise RuntimeError('This deployment has already been released.')
    BACKUPS.mkdir(mode=0o700, exist_ok=True)
    backup = BACKUPS / ('luxsol_ci_' + commit + '_' + str(time.time_ns()) + '.tar.gz')
    # Back up touched top-level paths, as in the manual deployment script.
    roots = sorted({name.split('/')[0] for name in payload})
    existing = [name for name in roots if (SITE / name).exists()]
    run('tar', '-czpf', str(backup), '-C', str(SITE), '--', *existing)
    backup.chmod(0o600)
    state.update(backup=str(backup), new_files=[name for name in payload if not (SITE / name).exists()], released=True)
    STATE.write_text(json.dumps(state))
    try:
        run('rsync', '-rltpcog', '--chown=www-data:www-data', '--chmod=D755,F644',
            '--itemize-changes', str(STAGE) + '/', str(SITE) + '/')
        clear_cache()
    except BaseException:
        rollback(state)
        raise
    print('Released ' + commit + '; backup: ' + str(backup))


if __name__ == '__main__':
    if len(sys.argv) != 3 or sys.argv[1] not in ['prepare', 'check', 'release', 'rollback'] or not re.fullmatch('[0-9a-f]{40}', sys.argv[2]):
        sys.exit('Usage: luxsol-ci-release prepare|check|release|rollback COMMIT')
    os.umask(0o077)
    with open(STATE_DIR / 'lock', 'a') as lock:
        fcntl.flock(lock, fcntl.LOCK_EX)
        main(sys.argv[1], sys.argv[2])
