# TaskFlow – handoff notes (read first)

Owner: Shihab (ssmebd@gmail.com, timezone Asia/Dhaka). Live: https://taskflow.gurucadd.com
Repo: this folder (/home/claude/taskflow). Sister repo: ssmebd/gurucadd-site (/home/claude/gurucadd-site) for www.gurucadd.com.

## Standing rules
- Run `./bump.sh` before EVERY commit (sets `const BUILD=N` in api.php, index.php, chat.php; prints N).
- Commit messages end with:
  `Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>`
  `Claude-Session: https://claude.ai/code/session_0158Fftx2yiYDSvStLzRsbkD`
- After every push, report the Build number to the user.
- Never show or store plain passwords. Keep real client info out of public pages (Allshelter and DMC / Dynamic Modular Constructions logos are explicitly allowed).
- The agent CANNOT reach the live sites or login. Verify with `php -l`, node parsing of inline scripts, local Playwright screenshots, PHP snippet tests. The user deploys (GitHub webhook / cron / cPanel) and hard-refreshes.
- Tag pushes to GitHub fail ("remote end hung up"); branch pushes work.
- Be brief in replies; user is busy and non-technical about code.

## Architecture
- PHP/MySQL on cPanel. `api.php` = JSON API (`switch($a)` dispatcher, `$in` JSON body, `$uid`, `$me`, `$WS`, `$W` workspace from `?w=`). `index.php` = main app, one big file with inline CSS/JS (`api()` helper, `$`, `esc`, `ltoday`). `chat.php` = GC Chat web app. `sw.js`, manifests, `deploy.php`, `apps/chat-desktop` (Electron, main.js has spellcheck context menu), Capacitor Android (capacitor.config.json).
- Schema migrations are inline, guarded by `SHOW COLUMNS` / `schema_vNN` flags (latest photo seed flag `schema_v41`).

## Status report feature (most recent work, Builds 270-285)
- Functions in api.php: `sr_ddl, sr_off, sr_ahead, sr_order, sr_types, sr_data($db,$ws,$exg,$ext,$ahead,$order,$rf,$rt,$mode), sr_html, sr_send, sr_run`; actions `sr_list, sr_preview, sr_save, sr_send, sr_del`.
- status_reports columns: ahead, grp_order ("done|going|soon"), rng_from, rng_to (day offsets from send day, clamped -28..+14; both NULL = legacy this-week Mon-Sun), rng_mode ('' | 'nweek' = next Mon-Sun | 'twn' = this Monday to next Sunday).
- UI in index.php `#srdg`: one dropdown `#srpm` (This week, This week to next week, Next week, Last 7 days, Last 2 weeks, Last 4 weeks, Next 2 weeks, 4 back to 2 ahead, Choose dates) + date inputs `#srf1/#srf2`; JS `srPm, srRng, srForm, srPrev, srOrdDraw`. Sections can be reordered with up/down arrows.
- Known gap: guest overview and "email me my report" still use default 3 days / this week.

## Other recent work (done and pushed)
- GC Chat: renamed, new logo for light/dark, unread badge kept until app opens, opens on newest conversation / last message, spellcheck context menu (Electron), bright team-chat bubble.
- "Tasks For Me" single button (uses header colours).
- Team photos (Raiyan, Hasib) seeded via `photo_seed2/3`.
- Morning welcome card idea (agreed design: centred, welcoming; shows missing hours for last day, tasks due last day needing status update, today's assignments; persists until acknowledged; all users incl. admin) – check git log whether it was built before assuming.

## Open items / not done
- GC Chat desktop .exe and Android .apk need a GitHub Actions "Build chat apps" run to pick up name/icon/spellcheck.
- Unanswered earlier: meaning of "TaskFlow Force or Go"; admin recent-logins and reset-password; guest Tools visibility; Imogen group-hide re-save; Allshelter / Exchange policy items.
- Website: Free Standing card wording, footer service list, Agribin label permission, hero photo resolution.
- Discussed (not started): what it takes to sell TaskFlow to clients as a product (multi-tenant, onboarding, pricing, notifications/follow-up as the selling point; clients use Smartsheet).

## Website repo (gurucadd-site) quick facts
- Files in `site/`; `pages.py` generates other pages from index.html; its `bump.sh` also sets `hero-site.webp?v=N`. Hero is blue (white version kept in `backup/`); hero image has scroll motion. Last version Ver 0.67.0. Same commit trailer rules apply.
