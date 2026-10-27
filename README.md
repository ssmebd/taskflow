# TaskFlow
PHP + MySQL task manager (workspaces, groups, statuses, Gantt, messages).
- `config.php` is private and not in git. Copy `config.sample.php` to `config.php` and fill in DB details.
- Pushing to `main` triggers `deploy.php` via a GitHub webhook (see deploy.php header).

## Hours reminders (cron)
In the app: user menu > Backups > "Hours reminders" shows a ready-to-paste command (no paths to edit) and test buttons.
In cPanel > Cron Jobs: schedule "Every 30 minutes", paste the command. It only sends at ~8am and ~3pm Dhaka time, once per day, Mon-Fri.
