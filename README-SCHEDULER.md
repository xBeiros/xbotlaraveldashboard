# Laravel Scheduler - Automatische Ausführung

Der Laravel Scheduler muss kontinuierlich laufen, damit geplante Tasks (wie das automatische Löschen von Transcripts) ausgeführt werden.

## Option 1: Supervisor (Empfohlen für Production)

1. Installiere Supervisor (falls nicht vorhanden):
```bash
sudo apt-get install supervisor  # Ubuntu/Debian
# oder
sudo yum install supervisor       # CentOS/RHEL
```

2. Kopiere die Supervisor-Config:
```bash
sudo cp supervisor-laravel-scheduler.conf /etc/supervisor/conf.d/laravel-scheduler.conf
```

3. Passe den Pfad in der Config an:
   - `command=php /home/xbot_bot/xdashboard/artisan schedule:work` → Dein Projekt-Pfad
   - `user=xbot_bot` → Dein Benutzer
   - `stdout_logfile=/home/xbot_bot/xdashboard/storage/logs/scheduler.log` → Dein Log-Pfad

4. Lade Supervisor neu und starte den Service:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-scheduler:*
```

5. Status prüfen:
```bash
sudo supervisorctl status laravel-scheduler:*
```

## Option 2: systemd Service (Alternative)

1. Kopiere die Service-Datei:
```bash
sudo cp systemd-laravel-scheduler.service /etc/systemd/system/laravel-scheduler.service
```

2. Passe den Pfad an:
   - `WorkingDirectory=/home/xbot_bot/xdashboard` → Dein Projekt-Pfad
   - `User=xbot_bot` → Dein Benutzer
   - `ExecStart=/usr/bin/php /home/xbot_bot/xdashboard/artisan schedule:work` → Dein PHP-Pfad

3. Aktiviere und starte den Service:
```bash
sudo systemctl daemon-reload
sudo systemctl enable laravel-scheduler
sudo systemctl start laravel-scheduler
```

4. Status prüfen:
```bash
sudo systemctl status laravel-scheduler
```

## Option 3: Cron-Job (Einfachste Lösung)

Füge folgenden Cron-Job hinzu (läuft jede Minute):

```bash
crontab -e
```

Füge diese Zeile hinzu:
```
* * * * * cd /home/xbot_bot/xdashboard && php artisan schedule:run >> /dev/null 2>&1
```

**Wichtig:** Passe den Pfad `/home/xbot_bot/xdashboard` an dein Projekt-Verzeichnis an.

## Option 4: Manuelles Script (Für Testing)

1. Mache das Script ausführbar:
```bash
chmod +x start-scheduler.sh
```

2. Starte den Scheduler:
```bash
./start-scheduler.sh
```

**Hinweis:** Dieses Script läuft im Vordergrund. Für Production sollte Option 1 oder 2 verwendet werden.

## Verifizierung

Nach dem Einrichten kannst du prüfen, ob der Scheduler läuft:

```bash
# Prüfe geplante Tasks
php artisan schedule:list

# Prüfe Logs
tail -f storage/logs/scheduler.log
# oder
tail -f storage/logs/laravel.log | grep CleanupOldTicketTranscripts
```

## Troubleshooting

- **Scheduler läuft nicht:** Prüfe die Logs und stelle sicher, dass PHP und der Projekt-Pfad korrekt sind
- **Tasks werden nicht ausgeführt:** Prüfe ob `schedule:work` oder der Cron-Job läuft
- **Berechtigungen:** Stelle sicher, dass der Benutzer Schreibrechte auf `storage/logs/` hat

