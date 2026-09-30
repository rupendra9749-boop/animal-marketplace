# Pipeline (GitHub Actions)

| Workflow | When | What |
|---|---|---|
| `Test and deploy` (`.github/workflows/pipeline.yml`) | push to `animalmandi`, pull requests, manual | runs the tests; on a push (or manual run) of `animalmandi` it then deploys to https://animalmandi.wuaze.com and tests the live site |
| `Live site health check` (`health.yml`) | every 6 hours, manual | checks the public pages and that private files stay private; a failure emails the repo owner |

## One-time setup: two secrets

GitHub repo → **Settings → Secrets and variables → Actions → New repository secret**

| Name | Value |
|---|---|
| `FTP_USER` | the InfinityFree FTP username (`if0_...`) |
| `FTP_PASSWORD` | the InfinityFree FTP password |

Until they exist the tests still run and the deploy step is skipped with a yellow warning.

Optional: **Settings → Environments → production → Required reviewers** makes every deploy wait for a click.

## How a deploy works

The free host has no SSH, so `deploy.sh` does it in steps: zip the code, upload the zips plus two helper scripts with a
random one-time token over FTP, call the helpers over HTTP (they unpack the zips and run `php artisan migrate`), delete the
helpers, then test the live site. `hostfetch.mjs` passes the host's "enable JavaScript" browser check.

- The server's `.env`, uploaded photos and database are never replaced. `install.php` has no wipe option.
- Libraries (`vendor`, about 6,400 files) are only re-uploaded when `composer.lock` changes.
- Files deleted from the repo are not deleted on the server (old files stay, harmless).
- Old hashed style/script files in `build/assets` are removed after each deploy.

Run the same checks by hand: `node deploy/ci/hostfetch.mjs https://animalmandi.wuaze.com/up --expect-status 200`
