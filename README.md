# TaskFlow
PHP + MySQL task manager (workspaces, groups, statuses, Gantt, messages).
- `config.php` is private and not in git. Copy `config.sample.php` to `config.php` and fill in DB details.
- Pushing to `main` triggers `deploy.php` via a GitHub webhook (see deploy.php header).

## Hours reminders (cron)
In the app: user menu > Backups > "Hours reminders" shows a ready-to-paste command (no paths to edit) and test buttons.
In cPanel > Cron Jobs: schedule "Every 30 minutes", paste the command. It only sends at ~8am and ~3pm Dhaka time, once per day, Mon-Fri.

## Sending email through Microsoft 365 (status reports)
Create `taskflow-mail.json` in the server home folder (not in the web root, never in GitHub):
`{"tenant_id":"...","client_id":"...","client_secret":"...","from":"taskflow@gurucadd.com"}`
The app registration needs Microsoft Graph **Application** permission `Mail.Send` with admin consent. Limit it to the taskflow mailbox with an Exchange application access policy. If the file is missing, or Microsoft refuses, TaskFlow falls back to the web server's mail.

## Email to task (forward an email, get a popup)
Anyone can forward an email to taskflow@gurucadd.com. Within about a minute the person who forwarded it (matched by the email in their user details) gets a popup to turn it into a task (title, company, assignee, dates, notes pre-filled). Admins also see forwards from unknown senders.
Needs one extra permission on the same app registration: Microsoft Graph **Application** permission `Mail.Read` with admin consent (limit it to the taskflow mailbox with the same Exchange application access policy). Until it is granted, admins see the reason in the browser console ("Email to task: ...").
Only the last 3 days of the inbox are read. Tables `mail_inbox` and `mail_state` are created automatically.

## Read-only Wiki API for other programs (e.g. CadMan)
Admin: user menu > API keys > create a key (shown once; only its hash is stored). Revoke any time.
Send the key with every request: `Authorization: Bearer tfk_...` (or `X-API-Key: tfk_...` if the host strips the Authorization header).
- Search: `GET https://taskflow.gurucadd.com/api.php?a=ext_wiki_search&q=TEXT&limit=10` (limit 1-25, default 10; all words must match in title, tags or text; title matches rank first) -> `{"query","count","results":[{"id","title","category","snippet","updated_at"}]}`
- One page: `GET https://taskflow.gurucadd.com/api.php?a=ext_wiki_page&id=12` or `...&title=Exact title` -> `{"id","title","category","tags","updated_at","text"}` (plain text, images removed)
- Errors are JSON `{"error":"..."}` with HTTP 400 (bad request), 401 (missing/invalid/revoked key), 404 (page not found). Keys can only read the Wiki.

## Sign-in check for other programs (`ext_login`)
`POST https://taskflow.gurucadd.com/api.php?a=ext_login` with the same API key header as the Wiki API and a JSON body `{"user_id":"...","password":"..."}` (user_id is the username or email).
- Right ID and password: `{"ok":true,"user":{"id":"..","name":"..","role":"..","token":".."}}` (the token is valid 30 days; only its hash is stored).
- Wrong: HTTP 401 `{"ok":false,"error":"..."}`. Bad or missing API key: 401. Not HTTPS: 403. Not POST: 405.
- A user is never locked out for wrong tries. Only a flood from one IP (20 wrong tries) is paused for 15 minutes (HTTP 429, `Retry-After: 900`).
- The password is never stored or logged. Failed and successful sign-ins are listed in the audit log (without the password).
