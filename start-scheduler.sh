#!/bin/bash

# Laravel Scheduler Start Script
# Dieses Script startet den Laravel Scheduler als Daemon-Prozess

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Prüfe ob PHP verfügbar ist
if ! command -v php &> /dev/null; then
    echo "Fehler: PHP ist nicht installiert oder nicht im PATH"
    exit 1
fi

# Prüfe ob artisan existiert
if [ ! -f "artisan" ]; then
    echo "Fehler: artisan Datei nicht gefunden. Bitte im Projekt-Root ausführen."
    exit 1
fi

echo "Starte Laravel Scheduler..."
echo "Drücke Ctrl+C zum Beenden"

# Starte den Scheduler (läuft kontinuierlich)
php artisan schedule:work

