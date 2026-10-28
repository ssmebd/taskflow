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
