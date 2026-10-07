"""Exercise actual backup/rsync/rollback in isolated temporary directories."""
import importlib.util
import json
from pathlib import Path
import tempfile
import unittest

spec = importlib.util.spec_from_file_location('release', Path(__file__).parents[1] / 'ci/server-release.py')
release = importlib.util.module_from_spec(spec)
spec.loader.exec_module(release)
COMMIT = 'a' * 40


class ReleaseTest(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        root = Path(self.temp.name)
        release.SITE = root / 'site'
        release.STAGE = root / 'stage'
        release.STATE = root / 'state.json'
        release.BACKUPS = root / 'backups'
        release.SITE.mkdir()
        release.STAGE.mkdir()
        self.write(release.SITE, str(release.PRIVATE), '<?php // local credentials')
        self.write(release.SITE, 'local/php_interface/init.php', '<?php // old version')
        self.write(release.STAGE, 'local/php_interface/init.php', '<?php // new version')
        release.STATE.write_text(json.dumps({'commit': COMMIT, 'released': False}))

    @staticmethod
    def write(root, name, data):
        path = root / name
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(data)

    def test_release_and_rollback_preserve_runtime_data(self):
        self.write(release.SITE, 'local/runtime-only.txt', 'preserve me')
        self.write(release.STAGE, 'catalog/new.php', '<?php // newly added file')
        release.main('release', COMMIT)
        self.assertIn('new version', (release.SITE / 'local/php_interface/init.php').read_text())
        self.assertTrue((release.SITE / 'catalog/new.php').exists())
        self.assertTrue((release.SITE / release.PRIVATE).exists())
        self.assertEqual((release.SITE / 'local/runtime-only.txt').read_text(), 'preserve me')
        release.main('rollback', COMMIT)
        self.assertIn('old version', (release.SITE / 'local/php_interface/init.php').read_text())
        self.assertFalse((release.SITE / 'catalog/new.php').exists())
        self.assertEqual((release.SITE / release.PRIVATE).read_text(), '<?php // local credentials')

    def test_preview_does_not_change_site(self):
        before = (release.SITE / 'local/php_interface/init.php').read_text()
        release.main('check', COMMIT)
        self.assertEqual((release.SITE / 'local/php_interface/init.php').read_text(), before)
        self.assertFalse(release.BACKUPS.exists())

    def test_protected_paths_cannot_be_deployed(self):
        for path in ['bitrix/a.php', 'upload/a.php', str(release.PRIVATE),
                     'local/php_interface/include/sale_payment/gpweb/keys/private.pem']:
            with self.subTest(path=path):
                self.write(release.STAGE, path, 'forbidden')
                with self.assertRaises(RuntimeError):
                    release.files()
                (release.STAGE / path).unlink()
                # Protected empty directories also must not remain in staging.
                root = release.STAGE / path.split('/')[0]
                if path.startswith(('bitrix/', 'upload/')):
                    root.rmdir()
                elif path.endswith('private.pem'):
                    (release.STAGE / path).parent.rmdir()

    def test_source_symlinks_are_rejected(self):
        (release.STAGE / 'outside').symlink_to(release.SITE, target_is_directory=True)
        with self.assertRaises(RuntimeError):
            release.files()

    def test_destination_symlinks_are_rejected(self):
        (release.SITE / 'catalog').symlink_to(self.temp.name, target_is_directory=True)
        self.write(release.STAGE, 'catalog/index.php', '<?php')
        with self.assertRaises(RuntimeError):
            release.files()

    def test_wrong_commit_cannot_release_or_roll_back(self):
        for action in ['check', 'release', 'rollback']:
            with self.subTest(action=action), self.assertRaises(RuntimeError):
                release.main(action, 'b' * 40)


if __name__ == '__main__':
    unittest.main()
