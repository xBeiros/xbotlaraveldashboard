# Debug: Bot findet keine Rollen/Kategorien

## Problem
Der Bot findet weder Kategorien noch Rollen.

## Mögliche Ursachen

### 1. Bot-Token nicht gesetzt
Der `DISCORD_BOT_TOKEN` ist nicht in der `.env` Datei gesetzt.

**Lösung:**
1. Öffne die `.env` Datei im `xdashboard` Ordner
2. Füge folgende Zeile hinzu:
   ```env
   DISCORD_BOT_TOKEN=dein_bot_token_hier
   ```
3. Cache leeren:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### 2. Bot-Token ist ungültig
Der Bot-Token ist falsch oder abgelaufen.

**Lösung:**
1. Gehe zu https://discord.com/developers/applications
2. Wähle deine Application aus
3. Gehe zu "Bot" im linken Menü
4. Klicke auf "Reset Token" und kopiere den neuen Token
5. Aktualisiere die `.env` Datei

### 3. Bot ist nicht auf dem Server
Der Bot ist nicht auf dem Discord-Server, auf dem du die Rollen/Kategorien abrufen möchtest.

**Lösung:**
1. Lade den Bot auf den Server ein
2. Stelle sicher, dass der Bot die nötigen Berechtigungen hat

### 4. Bot hat keine Berechtigung
Der Bot hat keine Berechtigung, Rollen/Kategorien abzurufen.

**Lösung:**
1. Prüfe die Bot-Berechtigungen auf dem Server
2. Der Bot benötigt mindestens "View Channels" und "Manage Roles" Berechtigung

## Logs prüfen

Nach dem Neuladen der Seite, prüfe die Logs:

```bash
cd xdashboard
tail -f storage/logs/laravel.log | grep -i "role\|channel\|bot token"
```

Die Logs zeigen:
- Ob der Bot-Token gefunden wurde
- Ob die API-Anfragen erfolgreich sind
- Welche Fehler auftreten (401, 403, 404, etc.)

## Test

Um zu testen, ob der Bot-Token funktioniert:

```bash
cd xdashboard
php artisan tinker
```

Dann:
```php
$token = config('services.discord.bot_token');
echo $token ? "Token gefunden: " . substr($token, 0, 10) . "..." : "Token nicht gefunden!";
```

## Häufige Fehler

### 401 Unauthorized
- Bot-Token ist ungültig oder abgelaufen
- Token wurde falsch kopiert (Leerzeichen, etc.)

### 403 Forbidden
- Bot hat keine Berechtigung
- Bot ist nicht auf dem Server

### 404 Not Found
- Guild-ID ist falsch
- Bot ist nicht auf dem Server


