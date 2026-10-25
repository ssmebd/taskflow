# TaskFlow apps

Both apps are thin shells around the live site at https://gurucadd.com/taskflow/ . The server on cPanel is unchanged.

- `desktop/` Windows app (Electron). Built to a `TaskFlow Setup.exe` installer.
- `android/` Android app (Capacitor). Built to an installable `TaskFlow.apk`.

They are built on GitHub (Actions tab > "Build apps" > Run workflow), then downloaded from the run page (Artifacts) or from Releases when you push a tag like `app-v1.0.0`.

To change the address, edit `TASKFLOW_URL` default in `desktop/main.js` and `server.url` in `android/capacitor.config.json`.
