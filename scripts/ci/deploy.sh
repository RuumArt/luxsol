#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/../.."

: "${DEPLOY_HOST:?DEPLOY_HOST is required}"
: "${DEPLOY_SSH_KEY_FILE:?DEPLOY_SSH_KEY_FILE is required}"
: "${DEPLOY_KNOWN_HOSTS_FILE:?DEPLOY_KNOWN_HOSTS_FILE is required}"
commit=$(git rev-parse HEAD)
ssh_options=(-i "$DEPLOY_SSH_KEY_FILE" -p "${DEPLOY_PORT:-22}" -o BatchMode=yes -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes -o "UserKnownHostsFile=$DEPLOY_KNOWN_HOSTS_FILE" -o ConnectTimeout=15)
target="${DEPLOY_USER:-luxsol-deploy}@$DEPLOY_HOST"
remote() { ssh "${ssh_options[@]}" "$target" "$1 $commit"; }

# Archive only tracked site files: no local credentials, dumps or uploads.
stage=$(mktemp -d)
trap 'rm -rf "$stage"' EXIT
git archive HEAD www | tar -x -C "$stage"
cp -R www/local/vendor "$stage/www/local/vendor"
# Dependency test fixtures include private test keys and are not needed at runtime.
find "$stage/www/local/vendor" -type d \( -name tests -o -name .git \) -prune -exec rm -rf {} +

remote prepare
# rrsync restricts the receiving account to its staging directory.
printf -v transport '%q ' ssh "${ssh_options[@]}"
rsync -rlt --checksum --itemize-changes -e "$transport" "$stage/www/" "$target:./"
remote check

if [[ ${DEPLOY_DRY_RUN:-false} == true ]]; then
    echo "Preview passed. Production files were not changed."
    exit 0
fi

remote release
health_ok=true
for path in / /catalog/ /kontakt/ /cart/ /cart/order/; do
    if ! status=$(curl --silent --show-error --location --max-time 45 --retry 2 --retry-delay 3 --output /dev/null --write-out '%{http_code}' "https://luxsol.sk$path"); then
        health_ok=false
        break
    fi
    printf '%-16s %s\n' "$path" "$status"
    if [[ $status != 200 ]]; then
        health_ok=false
        break
    fi
done
if [[ $health_ok != true ]]; then
    echo "Health check failed; restoring the deployment backup."
    remote rollback
    exit 1
fi
echo "Deployment succeeded: $commit"
