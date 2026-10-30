# TaskFlow apps

Both apps are thin shells around the live site at https://taskflow.gurucadd.com/ . The server on cPanel is unchanged.

- `desktop/` Windows app (Electron). Built to a `TaskFlow Setup.exe` installer.
- `android/` Android app (Capacitor). Built to an installable `TaskFlow.apk`.

They are built on GitHub (Actions tab > "Build apps" > Run workflow), then downloaded from the run page (Artifacts) or from Releases when you push a tag like `app-v1.0.0`.

To change the address, edit `TASKFLOW_URL` default in `desktop/main.js` and `server.url` in `android/capacitor.config.json`.

## Phone notifications (Android)
1. Firebase console: create a project, add an Android app with package `com.gurucadd.taskflow`, download `google-services.json` and commit it to `apps/android/google-services.json`.
2. Firebase > Project settings > Service accounts > Generate new private key. Upload that file to your hosting home folder (NOT public_html) as `taskflow-fcm.json` (or set `FCM_KEY_FILE` in config.php).
3. Run Actions > Build apps, install the new APK, open it once and allow notifications.
4. In TaskFlow: user menu > Backups > "Send test phone notification".
Messages, task assignments, comments/instructions and the 8am/3pm hours reminders are pushed.

## GC Chat (separate chat apps)
- `chat-desktop/` Windows chat app (Electron), `chat-android/` Android chat app (Capacitor). Both open https://taskflow.gurucadd.com/chat.php .
- Build: Actions tab > "Build chat apps" > Run workflow. Download `GC-Chat-Setup.exe` and `GC-Chat.apk` from the run's Artifacts (or from Releases when you push a tag like `chat-v1.0.0`).
- Phone alerts for chat: in Firebase add a second Android app with package `com.gurucadd.taskflow.chat`, download the new `google-services.json` (it lists both apps) and commit it to BOTH `apps/android/google-services.json` and `apps/chat-android/google-services.json`. The server key (`taskflow-fcm.json`) stays the same.
- The chat page itself is also installable from a browser (Install app) on any device.
