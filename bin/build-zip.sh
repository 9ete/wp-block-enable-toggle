#!/usr/bin/env bash
#
# Build a WordPress.org-ready distribution zip for Block Enable Toggle.
#
# The zip contains a single top-level folder matching the plugin slug
# (block-enable-toggle/), with everything in .distignore stripped out. A
# versioned copy is also written to zips/ for archival.
#
# Usage: ./bin/build-zip.sh [-q]
#   -q   quiet (used by the composer plugin-check script)

set -euo pipefail

QUIET=0
[ "${1:-}" = "-q" ] && QUIET=1

SLUG="block-enable-toggle"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MAIN_FILE="$ROOT/$SLUG.php"

log() { [ "$QUIET" -eq 1 ] || printf '%s\n' "$*"; }

if [ ! -f "$MAIN_FILE" ]; then
	echo "Main plugin file not found: $MAIN_FILE" >&2
	exit 1
fi

VERSION="$(grep -iE '^[[:space:]]*\*[[:space:]]*Version:' "$MAIN_FILE" | head -1 | sed -E 's/.*Version:[[:space:]]*//' | tr -d '[:space:]\r')"
if [ -z "$VERSION" ]; then
	echo "Could not read Version from $MAIN_FILE" >&2
	exit 1
fi

BUILD_DIR="$ROOT/zips/.build"
STAGE="$BUILD_DIR/$SLUG"
ZIP_ROOT="$ROOT/$SLUG.zip"
ZIP_VERSIONED="$ROOT/zips/$SLUG-$VERSION.zip"

log "Building $SLUG $VERSION ..."

rm -rf "$BUILD_DIR"
mkdir -p "$STAGE" "$ROOT/zips"

rsync -a --exclude-from="$ROOT/.distignore" "$ROOT/." "$STAGE/"

rm -f "$ZIP_ROOT" "$ZIP_VERSIONED"
( cd "$BUILD_DIR" && zip -rqX "$ZIP_ROOT" "$SLUG" )
cp "$ZIP_ROOT" "$ZIP_VERSIONED"

unzip -tq "$ZIP_ROOT" >/dev/null

rm -rf "$BUILD_DIR"

log "Created: $ZIP_ROOT"
log "Created: $ZIP_VERSIONED"
if [ "$QUIET" -ne 1 ]; then
	log "Contents:"
	unzip -l "$ZIP_ROOT" | sed 's/^/  /'
fi
