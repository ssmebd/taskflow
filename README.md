# TaskFlow
PHP + MySQL task manager (workspaces, groups, statuses, Gantt, messages).
- `config.php` is private and not in git. Copy `config.sample.php` to `config.php` and fill in DB details.
- Pushing to `main` triggers `deploy.php` via a GitHub webhook (see deploy.php header).

## Hours reminders (cron)
Add one cPanel cron job, every 30 minutes (it only sends at ~8am and ~3pm Dhaka time, once per day, Mon-Fri):

    */30 * * * * /usr/local/bin/php /home/CPANELUSER/taskflow.gurucadd.com/api.php remind

Test now: `php api.php remind am force` (or `pm force`).
