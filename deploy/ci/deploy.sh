#!/usr/bin/env bash
# Deploys the current checkout to the InfinityFree host (no SSH there: FTP for files, HTTP helpers for the rest).
#
# Expects, from the workflow: FTP_HOST, FTP_USER, FTP_PASSWORD, SITE_URL, and that these were already run:
#   composer install --no-dev --optimize-autoloader --no-scripts     (-> vendor/)
#   npm ci && npm run build                                          (-> public/build/)
#
# What it does: zip the code -> upload -> unpack on the server -> run migrations -> unpack the public files ->
# delete the one-time helper files -> test the live site. The server's .env, uploaded photos and database are never
# replaced. Nothing here can wipe data.
set -euo pipefail

: "${FTP_HOST:?}" "${FTP_USER:?}" "${FTP_PASSWORD:?}" "${SITE_URL:?}"
cd "$(dirname "$0")/../.."

PY=$(command -v python3 || command -v python)
TOKEN=$(openssl rand -hex 16)
WORK=$(mktemp -d)
SHA=${GITHUB_SHA:-$(git rev-parse HEAD 2>/dev/null || echo local)}

ftp_put()  { curl -sS --fail --retry 3 --retry-delay 3 --ftp-pasv --ftp-create-dirs -u "$FTP_USER:$FTP_PASSWORD" -T "$1" "ftp://$FTP_HOST/htdocs/$2"; }
ftp_get()  { curl -sS --ftp-pasv -u "$FTP_USER:$FTP_PASSWORD" "ftp://$FTP_HOST/htdocs/$1" 2>/dev/null || true; }
ftp_del()  { curl -sS --ftp-pasv -u "$FTP_USER:$FTP_PASSWORD" -Q "DELE htdocs/$1" "ftp://$FTP_HOST/htdocs/" -l -o /dev/null 2>/dev/null || true; }
zipdir()   { (cd "$1" && "$PY" -m zipfile -c "$2" .); }
say()      { echo; echo "== $*"; }

cleanup() {
    for f in install.php unzip.php app.zip vendor.zip public.zip; do ftp_del "$f"; done
    rm -rf "$WORK"
}
trap cleanup EXIT

# Runs a helper on the server through the browser-check solver; fails on any error text.
helper() {
    local out
    out=$(node deploy/ci/hostfetch.mjs "$SITE_URL/$1") || { echo "$out"; echo "helper call failed: $1"; exit 1; }
    echo "$out" | sed 's/t=[0-9a-f]\{32\}/t=***/'
    if ! echo "$out" | grep -q '^HTTP 200' || echo "$out" | grep -q 'FAILED'; then
        echo "DEPLOY FAILED in: $1"; exit 1
    fi
}

check() { # path, expected status, [expected text]
    local args=(--expect-status "$2"); [ -n "${3:-}" ] && args+=(--expect-text "$3")
    if node deploy/ci/hostfetch.mjs "$SITE_URL$1" "${args[@]}" >/dev/null 2>"$WORK/err"; then echo "  ok  $1 -> $2"; else echo "  FAIL $1 (wanted $2 ${3:-})"; cat "$WORK/err"; SMOKE_FAILED=1; fi
}

say "Packaging ${SHA:0:7}"
APP="$WORK/app-stage"; PUB="$WORK/public-stage"
mkdir -p "$APP" "$PUB"
cp -r --parents app config lang resources/views resources/data routes database/migrations database/seeders bootstrap/app.php bootstrap/providers.php "$APP/"
(cd public && cp -r --parents build images favicon.svg manifest.webmanifest sw.js robots.txt "$PUB/")
cp deploy/infinityfree/root.htaccess "$PUB/.htaccess"
cp deploy/infinityfree/index.php "$PUB/index.php"
zipdir "$APP" "$WORK/app.zip"
zipdir "$PUB" "$WORK/public.zip"

VENDOR_HASH=$(sha256sum composer.lock | cut -d' ' -f1)
NEED_VENDOR=0
if [ "$(ftp_get _app/.vendor-hash)" != "$VENDOR_HASH" ]; then
    NEED_VENDOR=1
    zipdir vendor "$WORK/vendor.zip"
fi
ls -la "$WORK"/*.zip | awk '{print "  " $5 " bytes  " $9}'
echo "  libraries changed: $([ $NEED_VENDOR = 1 ] && echo yes || echo no)"

sed "s/@@TOKEN@@/$TOKEN/" deploy/infinityfree/unzip.php > "$WORK/unzip.php"
sed "s/@@TOKEN@@/$TOKEN/" deploy/infinityfree/install.php > "$WORK/install.php"

say "Uploading"
ftp_put "$WORK/app.zip" app.zip
ftp_put "$WORK/public.zip" public.zip
[ $NEED_VENDOR = 1 ] && ftp_put "$WORK/vendor.zip" vendor.zip
ftp_put "$WORK/unzip.php" unzip.php
ftp_put "$WORK/install.php" install.php

say "Unpacking on the server"
[ $NEED_VENDOR = 1 ] && helper "unzip.php?t=$TOKEN&zip=vendor.zip&to=_app/vendor"
helper "unzip.php?t=$TOKEN&zip=app.zip&to=_app"

say "Database update (migrations only - never a wipe)"
helper "install.php?t=$TOKEN"

say "Public files"
helper "unzip.php?t=$TOKEN&zip=public.zip&to=&prune=build/assets"

if [ $NEED_VENDOR = 1 ]; then printf '%s' "$VENDOR_HASH" > "$WORK/vendor-hash"; ftp_put "$WORK/vendor-hash" _app/.vendor-hash; fi
printf '%s %s\n' "$SHA" "$(date -u +%FT%TZ)" > "$WORK/deployed"; ftp_put "$WORK/deployed" _app/.deployed

say "Removing the one-time helper files"
for f in install.php unzip.php app.zip vendor.zip public.zip; do ftp_del "$f"; done

say "Testing the live site"
SMOKE_FAILED=0
check /up 200 "Application up"
check /home 200 "AnimalMandi"
check /login 200 "AnimalMandi"
check /register 200 "AnimalMandi"
check /vets 200 "AnimalMandi"
check /promo 200 "AnimalMandi"
check /privacy 200 "Privacy"
check /language/hi 200 "AnimalMandi"
check /_app/vendor/autoload.php 403
check /install.php 404
check /unzip.php 404
check /no-such-page 404
if [ "$SMOKE_FAILED" = 1 ]; then echo; echo "DEPLOYED, BUT THE LIVE SITE FAILED ITS CHECKS - look at the lines above."; exit 1; fi

echo; echo "Deployed ${SHA:0:7} to $SITE_URL - all checks passed."
