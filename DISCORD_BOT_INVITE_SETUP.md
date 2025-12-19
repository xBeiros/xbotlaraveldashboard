# Discord Bot-Einladung Setup - Fehlerbehebung

## Problem: "Integration requires code grant"

Dieser Fehler tritt auf, wenn die Discord Application nicht richtig für Bot-Einladungen konfiguriert ist.

## Lösung: Discord Developer Portal konfigurieren

### Schritt 1: OAuth2 Redirect URIs prüfen

1. Gehe zu https://discord.com/developers/applications
2. Wähle deine Application aus
3. Gehe zu **"OAuth2"** → **"General"**
4. Stelle sicher, dass folgende Redirect URI hinzugefügt ist:
   ```
   http://xdashboard.test/auth/discord/callback
   ```
5. **WICHTIG:** Klicke auf **"Save Changes"** am Ende der Seite!

### Schritt 2: Bot-Einladung ohne redirect_uri

Die Bot-Einladungs-URL sollte **KEINE** `redirect_uri` enthalten. 

**Korrekte Bot-Einladungs-URL:**
```
https://discord.com/api/oauth2/authorize?client_id=DEINE_CLIENT_ID&permissions=8&scope=bot%20applications.commands&guild_id=SERVER_ID
```

**Falsche Bot-Einladungs-URL (mit redirect_uri):**
```
https://discord.com/api/oauth2/authorize?client_id=...&redirect_uri=...&...
```

### Schritt 3: Bot-Einstellungen prüfen

1. Gehe zu **"Bot"** im linken Menü
2. Stelle sicher, dass:
   - ✅ Bot ist erstellt
   - ✅ Bot Token ist vorhanden
   - ✅ "Public Bot" ist aktiviert (wenn der Bot öffentlich sein soll)
   - ✅ "Requires OAuth2 Code Grant" ist **DEAKTIVIERT** (wichtig!)

### Schritt 4: OAuth2 URL Generator testen

1. Gehe zu **"OAuth2"** → **"URL Generator"**
2. Wähle Scopes:
   - ✅ `bot`
   - ✅ `applications.commands`
3. Wähle Berechtigungen (z.B. Administrator)
4. Die generierte URL sollte so aussehen:
   ```
   https://discord.com/api/oauth2/authorize?client_id=...&permissions=...&scope=bot%20applications.commands
   ```
5. **WICHTIG:** Diese URL sollte **KEINE** `redirect_uri` enthalten!

### Schritt 5: Application neu laden

Nach Änderungen im Discord Developer Portal:
1. Warte 1-2 Minuten (Änderungen brauchen Zeit zum Propagieren)
2. Versuche die Bot-Einladung erneut

## Häufige Fehler

### ❌ Falsch: "Requires OAuth2 Code Grant" aktiviert
- **Problem:** Discord denkt, dass die Application eine Code Grant benötigt
- **Lösung:** Deaktiviere diese Option im Bot-Tab

### ❌ Falsch: Redirect URI fehlt für User-Login
- **Problem:** User-Login funktioniert nicht
- **Lösung:** Füge `http://xdashboard.test/auth/discord/callback` zu OAuth2 Redirect URIs hinzu

### ❌ Falsch: Bot-Einladungs-URL enthält redirect_uri
- **Problem:** Discord erwartet keine redirect_uri für Bot-Einladungen
- **Lösung:** Entferne `redirect_uri` aus der Bot-Einladungs-URL

## Checkliste

- [ ] OAuth2 Redirect URI für User-Login hinzugefügt: `http://xdashboard.test/auth/discord/callback`
- [ ] "Save Changes" im OAuth2 Tab geklickt
- [ ] Bot ist erstellt und Token vorhanden
- [ ] "Requires OAuth2 Code Grant" ist **DEAKTIVIERT**
- [ ] Bot-Einladungs-URL enthält **KEINE** `redirect_uri`
- [ ] 1-2 Minuten gewartet nach Änderungen

## Testen

1. Öffne das Dashboard: `http://xdashboard.test/dashboard`
2. Klicke auf "Bot einladen" bei einem Server
3. Discord sollte die Bot-Einladungsseite öffnen (ohne Fehler)
4. Bestätige die Einladung
5. Der Bot sollte dem Server beitreten

