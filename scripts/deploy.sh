#!/usr/bin/env bash
set -euo pipefail

: "${DEPLOY_HOST:?Missing DEPLOY_HOST}"
: "${DEPLOY_PORT:?Missing DEPLOY_PORT}"
: "${DEPLOY_USER:?Missing DEPLOY_USER}"
: "${DEPLOY_TARGET:?Missing DEPLOY_TARGET}"
: "${DEPLOY_SSH_KEY:?Missing DEPLOY_SSH_KEY}"

key_file=$(mktemp)
trap 'rm -f "$key_file"' EXIT
printf '%s\n' "$DEPLOY_SSH_KEY" > "$key_file"
chmod 600 "$key_file"

ssh_args=(
  -i "$key_file"
  -p "$DEPLOY_PORT"
  -o BatchMode=yes
  -o ConnectTimeout=15
  -o ServerAliveInterval=15
  -o ServerAliveCountMax=3
  -o UserKnownHostsFile=/dev/null
  -o StrictHostKeyChecking=no
)

for attempt in 1 2 3; do
  echo "Deployment attempt $attempt/3: testing SSH port"
  if nc -vz -w 10 "$DEPLOY_HOST" "$DEPLOY_PORT" && \
    ssh -v "${ssh_args[@]}" "$DEPLOY_USER@$DEPLOY_HOST" 'echo SSH connection successful'; then
    echo 'Uploading files with rsync'
    if rsync -avz --delay-updates \
      --exclude-from=.gitignore --exclude=/.git/ --exclude=/.github/ \
      -e "ssh -i $key_file -p $DEPLOY_PORT -o BatchMode=yes -o ConnectTimeout=15 -o ServerAliveInterval=15 -o ServerAliveCountMax=3 -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" \
      ./ "$DEPLOY_USER@$DEPLOY_HOST:$DEPLOY_TARGET"; then
      echo 'Deployment complete'
      exit 0
    fi
  fi
  if (( attempt < 3 )); then
    echo "Deployment attempt $attempt failed; retrying"
    sleep $((attempt * 10))
  fi
done

echo 'Deployment failed after 3 attempts' >&2
exit 1
