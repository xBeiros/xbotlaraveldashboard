# Bot Token Setup - WICHTIG!

## Problem
Der Bot-Status wird nicht erkannt, weil der `DISCORD_BOT_TOKEN` in der Dashboard `.env` Datei fehlt!

## Lösung

1. **Öffne die `.env` Datei im `xdashboard` Ordner**

2. **Füge folgende Zeile hinzu:**
   ```env
   DISCORD_BOT_TOKEN=dein_bot_token_hier
   ```

3. **Wo findest du den Bot Token?**
   - Gehe zu https://discord.com/developers/applications
   - Wähle deine Application aus
   - Gehe zu "Bot" im linken Menü
   - Klicke auf "Reset Token" oder kopiere den vorhandenen Token
   - **WICHTIG:** Der Token sollte so aussehen: `MTQ0NzY1NTgxOTY3NDkxNTAxOA.MUAwkf6fGzRI2wtBPWLmf9HTGRX8R5`

4. **Füge den Token in die `.env` Datei ein:**
   ```env
   DISCORD_BOT_CLIENT_ID=1447655819674915018
   DISCORD_BOT_TOKEN=MTQ0NzY1NTgxOTY3NDkxNTAxOA.MUAwkf6fGzRI2wtBPWLmf9HTGRX8R5
   ```

5. **Cache leeren:**
   ```bash
   cd xdashboard
   php artisan config:clear
   php artisan cache:clear
   ```

6. **Bot neu starten (falls er läuft):**
   - Der Bot sollte beim Start automatisch alle Server synchronisieren
   - Oder warte, bis der Bot das nächste Mal startet

## Nach dem Setup

1. Öffne das Dashboard
2. Klicke auf "Aktualisieren"
3. Der Bot-Status sollte jetzt korrekt erkannt werden!

## Alternative: Bot manuell synchronisieren

Falls der Bot bereits läuft und du nicht warten möchtest:

1. Starte den Bot neu (im `xbot` Ordner)
2. Beim Start wird automatisch ein `ready` Event ausgelöst
3. Dieses Event synchronisiert alle Server, auf denen der Bot ist

