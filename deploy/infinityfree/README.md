# Deploying to InfinityFree (animalmandi.wuaze.com)

The free host has no SSH, Composer, Artisan or Node, so the site is built on your computer and uploaded.

## Layout on the host

```
htdocs/
  _app/          the Laravel app (app, config, vendor, .env ...) - blocked from the web by .htaccess
  index.php      patched front controller (points at _app, public path = htdocs)
  build/ images/ favicon.svg robots.txt .htaccess     the public files
  storage/       uploaded photos (real folder - symlinks are switched off); PUBLIC_DISK_ROOT in .env
  unzip.php  install.php     one-time helpers, delete after use
```

## Steps

1. `npm run build`, then copy the app into a staging folder: `app bootstrap config database (no factories) resources (no js/css) routes storage skeleton artisan composer.*` -> `_app/`, and `public/` -> the site root (`index.php`, `root.htaccess` as `.htaccess`, `app.htaccess` as `_app/.htaccess` come from this folder).
2. Write `_app/.env` (see the keys below), then `composer install --no-dev --optimize-autoloader --no-scripts` inside `_app` and `php artisan package:discover`.
3. Zip `_app/vendor`, the rest of `_app`, and the public files (keep each zip under ~10 MB), upload with FTP (`ftpupload.net`, passive mode) together with `unzip.php` and `install.php` (replace `@@TOKEN@@` with a random secret first).
4. Open `unzip.php?t=TOKEN&zip=vendor.zip&to=_app/vendor`, then `zip=app.zip&to=_app`, then `zip=public.zip&to=` .
5. Upload `storage.htaccess` as `htdocs/storage/.htaccess`.
6. Open `install.php?t=TOKEN` (add `&fresh=1` only the very first time: it wipes the tables). It migrates and runs `ProductionSeeder` (animal types + one admin from `ADMIN_EMAIL` / `ADMIN_PASSWORD`).
7. Delete `unzip.php`, `install.php`, and take `ADMIN_EMAIL` / `ADMIN_PASSWORD` out of `.env`.

The host puts a JavaScript check in front of every page: browsers pass it automatically, `curl` does not.

## .env keys that matter

```
APP_ENV=production  APP_DEBUG=false  APP_URL=https://animalmandi.wuaze.com  APP_KEY=...
DB_HOST=sql112.infinityfree.com  DB_DATABASE=...  DB_USERNAME=...  DB_PASSWORD=...
DB_ENGINE=InnoDB            # the server default is MyISAM: no foreign keys, 1000-byte index limit
SESSION_DRIVER=database  CACHE_STORE=database  QUEUE_CONNECTION=sync
PUBLIC_DISK_ROOT=/home/<vol>/infinityfree.com/<account>/htdocs/storage
MAIL_MAILER=phpmail         # PHP mail(): proc_open (needed by sendmail) is disabled
```

## Later updates (code changes only)

Zip only what changed (`app`, `config`, `lang`, `resources/views`, `routes`, `database/migrations`, `bootstrap/app.php`; and `public/build` if the front-end changed), upload it with `unzip.php` + `install.php` (fresh token), unpack into `_app` (and `build/` into the site root), run `install.php?t=TOKEN` (no `fresh`) to apply new migrations, remove the old hashed files from `build/assets`, then delete both helpers again.

## Languages

English and Hindi. Everything shown to people goes through `__('English text')`; the Hindi text is in `lang/hi.json`. `php artisan test --filter=LanguageTest` fails when a string has no Hindi entry.
