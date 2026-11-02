# GC Chat – handoff (read with CLAUDE.md)

GC Chat is the team chat for GURUCADD. It is NOT a separate system: it is a second front end on the TaskFlow backend. Same server, same database, same logins, same repo (/home/claude/taskflow, GitHub ssmebd/taskflow). Live: https://taskflow.gurucadd.com/chat.php

## Standing rules (same as TaskFlow)
- Run `./bump.sh` before every commit; it sets BUILD in api.php, index.php AND chat.php (one shared build number).
- Commit trailers: `Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>` and `Claude-Session: https://claude.ai/code/session_0158Fftx2yiYDSvStLzRsbkD`. Report the build after every push.
- Never show/store plain passwords. The agent cannot reach the live site; verify with `php -l`, node parse of inline scripts, local Playwright. The user deploys and hard-refreshes.
- Keep replies short, yes/no unless details are requested (user wants to save usage).

## How GC Chat connects to TaskFlow
- Login/session: no separate login. chat.php uses the TaskFlow session cookie (same-origin). Users = TaskFlow users; the team list and photos come from TaskFlow.
- API: chat.php (about 500 lines, inline CSS/JS) calls `api.php?a=<action>` with POST JSON via its own `api()` helper (`X-Requested-With: fetch`, same-origin credentials). The chat actions live in the same `switch($a)` in api.php as TaskFlow's:
  - `msg_users` (conversation/team list + current build, used by `chkBuild` for auto-reload), `msg_list` (params: peer, after, noread), `msg_send` (peer, body, optional html, files), `msg_edit`, `msg_del`, `msg_react`, `msg_file` (download; files are deleted after 48 hours, HTTP 410), `chat_me`, `chat_tasks` (search TaskFlow tasks by title/order no. so a chat message can reference a task; limited to the user's workspaces `$WS`), `push_reg` / `push_test` (phone notification tokens).
- Database tables (created inline in api.php, `CREATE TABLE IF NOT EXISTS`): `messages` (from_id, to_id, body, created_at, edited_at, deleted), `msg_files`, `msg_reacts`, `msg_reads` (per user/peer last_id), `msg_dlv` (delivered marker), `push_tokens` (token, user_id, platform, app).
- Notifications: TaskFlow's push system (FCM) also delivers chat messages. `push_tokens.app` separates the TaskFlow app from the chat app. Server key `taskflow-fcm.json` sits in the hosting home folder (not public_html) or `FCM_KEY_FILE` in config.php.
- Unread/read: opening a conversation marks it read via `noread` logic; chat only marks read when the window is focused (`seenNow()` on focus). Chat opens on the newest conversation / last message.
- Build coupling: because the BUILD constant is shared, any TaskFlow commit bumps GC Chat's build too; `chkBuild` makes open chat pages reload when it changes.

## App packaging (all load https://taskflow.gurucadd.com/chat.php)
- Web/PWA: `chat.webmanifest` (name/short name "GC Chat", id /chat.php, scope ./chat.php, theme #0f2a5c) + icons `chat-192.png`, `chat-512.png`, `chat-maskable-512.png`. Logo works on light and dark themes.
- Windows: `apps/chat-desktop/` (Electron: main.js incl. spellcheck right-click suggestions, preload.js, badge-1..9/plus.png for the taskbar unread count, icon.ico/png).
- Android: `apps/chat-android/` (Capacitor, package `com.gurucadd.taskflow.chat`, google-services.json must list both Android apps and be committed to BOTH apps/android and apps/chat-android).
- Build via GitHub Actions: Actions tab > "Build chat apps" (.github/workflows/chat-apps.yml) > Run workflow, or push a tag `chat-v*` (tag pushes from the agent fail; use the Actions button). Outputs: `GC-Chat-Setup.exe`, `GC-Chat.apk` (Artifacts or Releases).

## Done recently
- Renamed to GC Chat with new logo; bright (not dark) round team-chat bubble; taskbar unread count kept until the app opens; always opens on last message; word correction (browser spellcheck in web, Electron context menu on desktop).

## Open items
- IMPORTANT: the .exe and .apk have not been rebuilt since the rename/icon/spellcheck changes. User must run Actions > "Build chat apps", then reinstall.
- Check the Android push still works after reinstall (Firebase config above).
- Ideas not started: tie chat to TaskFlow more (task links in messages are possible via `chat_tasks`; group chats are not built, chats are 1-to-1 / team).

## Verify before assuming
- Run `git log --oneline | head -20` to see what was built after Build 286 (another session made Build 287 on branch status-report-fullscreen).
