# TaskFlow: brand, design and deployment notes

Written so a new Claude session (for example the GURUCADD website rebuild) can understand what was built for TaskFlow and reuse it. No secrets are in this file. Never put passwords, API keys, the mail secret or the Firebase key in GitHub or chat.

## What TaskFlow is
A multi-user task manager for a drawing/drafting team (the "Allshelter" companies), built by GURUCADD. PHP 7.4+/8, MySQL, cPanel shared hosting. One-page vanilla JS UI in `index.php`, JSON API in `api.php`, sign-in in `login.php`. Live at https://taskflow.gurucadd.com. Repo: github.com/ssmebd/taskflow (main). Features: List / Board / Gantt views, company tabs with logos, hours logging with automatic overtime (8 h regular Mon-Fri), "My hours check", admin hours report, chat and notifications, wiki, weekly client status report by email, worklog and invoice view, backups every 3 hours, roles and approvals, usage log, Windows desktop app (Electron) and Android app (Capacitor) built by GitHub Actions, phone push via Firebase.

## Brand and look (reuse this for the GURUCADD website)
- Colours: navy `#0f2a5c` (main), deep navy `#081a3d`, sky blue `#38bdf8`, green `#22c55e`, ink `#0f172a`, muted `#64748b`, line `#e2e8f0`, page background `#f6f8fc`. Status colours: green `#16a34a` done/ongoing, blue `#2563eb`, amber `#d97706`.
- Gradient used on dark sections: `linear-gradient(160deg,#0f2a5c,#081a3d)` with soft radial glows of sky blue and green.
- Type: system UI stack (`"Segoe UI", system-ui, -apple-system, Roboto, Arial`), very bold headings (weight 800-900) with tight line-height, small uppercase tracked eyebrow labels in sky blue.
- Shapes: large rounded corners (14-24 px), soft shadows, cards that lift on hover, scroll-reveal fade-up, pill badges, initials avatars.
- Logo: the "Gantt cascade" mark: a white bar with a navy tick, a sky-blue bar, and a green bar with an arrow tip, on a navy gradient rounded tile. Files in the repo root: `logo.svg`, `icon-192.png`, `icon-512.png`, `icon-maskable-512.png`. `login.php?logo=1` serves the SVG. The word mark is "TaskFlow" with "POWERED BY GURUCADD" in small tracked caps underneath.
- Do not draw an arrow flowing out of a "T"; an earlier logo was rejected for looking inappropriate. Keep the Gantt cascade.

## Best style references already in the repo
- **Discover TaskFlow page** (a public marketing page, full dark hero, feature cards, report mock, CTA): it is embedded in `login.php` inside the block `if(isset($_GET['discover'])){ ... TFDISCOVER ... }`. Live: https://taskflow.gurucadd.com/login.php?discover=1. All names and numbers on it are samples.
- **Weekly status email design**: function `sr_html()` in `api.php` (table-based HTML with inline styles for email clients).
- The app UI itself: `index.php`.

## Contact
Website contact email is info@gurucadd.com. Sales enquiries from the TaskFlow Discover page go to shihab.shahriar@gurucadd.com.

## Hosting and deployment
- Server: cPanel, home folder `/home/guru`, document root `public_html`. TaskFlow lives in `public_html/taskflow/` and on the subdomain `taskflow.gurucadd.com`. Never touch that folder from the website deploy.
- TaskFlow deploy: a cron job every 5 minutes runs `cd $HOME/repositories/taskflow && git pull -q origin main && cp <listed files> $HOME/public_html/taskflow/`. The repo is cloned on the server with cPanel Git Version Control at `repositories/taskflow`. Pushing to `main` therefore deploys within about 5 minutes. Any new file must be added to that `cp` list.
- Planned website deploy (same pattern): repo `gurucadd-site`, cloned at `repositories/gurucadd-site`, cron copies the `site/` folder first to `public_html/preview/`, and later to `public_html/` using `rsync -a --exclude=taskflow --exclude=.htaccess --exclude=cgi-bin site/ $HOME/public_html/`. Never use `--delete`. Back up the old site first.
- Each TaskFlow commit runs `./bump.sh` (build number) and ends with the Co-Authored-By and Claude-Session lines. Report the build number after every push.

## Email
Mail goes out through Microsoft 365 using the Graph API, sending as `taskflow@gurucadd.com` (a licensed mailbox). Code: `ms_cfg`, `ms_token`, `ms_send` in `api.php`. It reads `taskflow-mail.json` from the server home folder, shaped `{"tenant_id","client_id","client_secret","from"}`; the secret lives only on the server. The Entra app is "TaskFlow Mail" with Graph application permission `Mail.Send`. Still recommended: restrict it to the one mailbox with an Exchange application access policy. The secret expires about October 2028. If Graph fails, TaskFlow falls back to PHP `mail()`. A website contact form can reuse the same approach, with its own mailbox and app registration if preferred.
- Email lessons learned: use base64 bodies, attach logos inline with `cid:` (data URIs are blocked), put logos on the navy header as white silhouettes so they survive dark mode, keep the sender consistent.

## Working rules the owner (Shihab) has set
- Do not put secrets in GitHub or chat; keys stay on the server.
- Ask before sending messages on his behalf, publishing, or doing anything destructive.
- Show screenshots of design work before it goes live.
- He is in Bangladesh (Asia/Dhaka). The business serves Australian and US customers.
