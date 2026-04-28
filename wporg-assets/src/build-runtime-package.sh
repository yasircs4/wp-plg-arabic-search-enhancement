#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
DEST="${1:-$(mktemp -d "${TMPDIR:-/tmp}/ase-runtime.XXXXXX")}"

if [ -e "$DEST" ] && [ "$(find "$DEST" -mindepth 1 -maxdepth 1 2>/dev/null | wc -l | tr -d ' ')" != "0" ]; then
	echo "Destination exists and is not empty: $DEST" >&2
	exit 1
fi

mkdir -p "$DEST"

rsync -a \
	--exclude '.DS_Store' \
	--exclude 'README.md' \
	"$REPO_ROOT/arabic-search-enhancement.php" \
	"$REPO_ROOT/readme.txt" \
	"$REPO_ROOT/src" \
	"$REPO_ROOT/assets" \
	"$REPO_ROOT/languages" \
	"$DEST/"

find "$DEST" -type d -empty -delete

printf '%s\n' "$DEST"
