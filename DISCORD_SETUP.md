# Discord OAuth Setup

## 1. Discord Application erstellen

1. Gehe zu https://discord.com/developers/applications
2. Klicke auf "New Application"
3. Gib einen Namen ein (z.B. "XBot Dashboard")
4. Klicke auf "Create"

## 2. OAuth2 Credentials konfigurieren (WICHTIG!)

1. Gehe zu "OAuth2" → "General"
2. Kopiere die **Client ID** und **Client Secret**
3. **WICHTIG - Redirect URIs hinzufügen:**
   - Klicke auf "Add Redirect"
   - Füge folgende Redirect URI hinzu:
     ```
     http://xdashboard.test/auth/discord/callback
     ```
   - (Für Production: `https://deine-domain.com/auth/discord/callback`)
   - **Speichere die Änderungen!**

4. **WICHTIG - OAuth2 URL Generator:**
   - Gehe zu "OAuth2" → "URL Generator"
   - Wähle Scopes:
     - ✅ `bot` (für Bot-Einladung)
     - ✅ `applications.commands` (für Slash Commands)
   - Wähle Berechtigungen (z.B. Administrator oder spezifische Berechtigungen)
   - Die generierte URL zeigt dir, wie die Bot-Einladung funktioniert

## 3. Bot erstellen

1. Gehe zu "Bot" im linken Menü
2. Klicke auf "Add Bot" (falls noch nicht erstellt)
3. Kopiere den **Bot Token** (wird später für den Bot benötigt)
4. Aktiviere folgende Privileged Gateway Intents:
   - ✅ MESSAGE CONTENT INTENT
   - ✅ SERVER MEMBERS INTENT
5. **WICHTIG - Public Bot:**
   - Stelle sicher, dass "Public Bot" aktiviert ist (wenn der Bot öffentlich sein soll)
   - Oder deaktiviere es, wenn der Bot nur für bestimmte Server ist

## 4. Bot-Einladung konfigurieren

**WICHTIG:** Für Bot-Einladungen benötigst du KEINE redirect_uri in der OAuth2 URL!

Die Bot-Einladungs-URL sollte so aussehen:
```
https://discord.com/api/oauth2/authorize?client_id=DEINE_CLIENT_ID&permissions=8&scope=bot%20applications.commands
```

**Hinweis:** Der Fehler "Integration requires code grant" tritt auf, wenn:
- Die OAuth2 Redirect URIs nicht korrekt konfiguriert sind
- Die Application nicht richtig eingerichtet ist
- Die Scopes nicht korrekt sind

## 5. .env Datei konfigurieren

Füge folgende Zeilen zu deiner `.env` Datei hinzu:

```env
# Discord OAuth (für User Login)
DISCORD_CLIENT_ID=deine_client_id_hier
DISCORD_CLIENT_SECRET=dein_client_secret_hier
DISCORD_REDIRECT_URI=http://xdashboard.test/auth/discord/callback

# Discord Bot (für Bot-Einladung)
DISCORD_BOT_CLIENT_ID=deine_bot_client_id_hier
DISCORD_BOT_TOKEN=dein_bot_token_hier
```

**Wichtig:** 
- `DISCORD_CLIENT_ID` und `DISCORD_CLIENT_SECRET` sind für den User-Login
- `DISCORD_BOT_CLIENT_ID` ist die gleiche Client ID wie oben (eine Application = ein Bot)
- `DISCORD_BOT_TOKEN` ist der Bot Token aus dem Bot-Tab

## 6. Fertig!

Nach der Konfiguration kannst du:
- Mit Discord einloggen: `/auth/discord`
- Bot auf Server einladen: `/bot/invite`

