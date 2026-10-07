#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/../.."

composer install --working-dir=www/local --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-plugins
count=0
while IFS= read -r -d '' file; do
    php -d short_open_tag=1 -d display_errors=0 -l "$file" >/dev/null
    count=$((count + 1))
done < <(git ls-files -z -- '*.php')
echo "PHP syntax checked: $count files"
php -d short_open_tag=1 -d display_errors=0 scripts/tests/gpwebpay-response.php
python3 -m py_compile scripts/ci/server-release.py scripts/ci/ssh-entrypoint.py
python3 scripts/tests/test_ci_release.py
bash -n scripts/deploy.sh scripts/ci/deploy.sh scripts/ci/validate.sh
