<?php

namespace App\Console\Commands;

use App\Models\FactionWarn;
use Illuminate\Console\Command;

class CleanupExpiredFactionWarns extends Command
{
    protected $signature = 'faction-warns:cleanup';

    protected $description = 'Entfernt abgelaufene Fraktion-Warns täglich (expires_at < jetzt). Nach Ablauf zählt die Warn nicht mehr (2→1, 1→0).';

    public function handle(): int
    {
        $deleted = FactionWarn::where('expires_at', '<', now())->delete();
        if ($deleted > 0) {
            $this->info("Abgelaufene Warns entfernt: {$deleted}");
        }
        return self::SUCCESS;
    }
}
