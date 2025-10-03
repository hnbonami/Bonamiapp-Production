#!/usr/bin/env bash
set -euo pipefail

# Simple backup script: create a timestamped branch from the current branch and push it to origin
# Usage: ./scripts/push-backup.sh

cd "$(dirname "$0")/.."

CURRENT=$(git rev-parse --abbrev-ref HEAD)
TIMESTAMP=$(date -u +"%F_%H%M")
BACKUP="backup/${CURRENT}_${TIMESTAMP}"

echo "Creating backup branch: $BACKUP (from $CURRENT)"
# Force-create a branch ref to the current commit
git branch -f "$BACKUP" "$CURRENT"

echo "Pushing $BACKUP to origin..."
git push origin "$BACKUP"

echo "Backup pushed: $BACKUP"
