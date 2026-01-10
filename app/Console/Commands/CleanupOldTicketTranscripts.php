<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupOldTicketTranscripts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:cleanup-transcripts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Löscht Ticket-Transcripts, die älter als 7 Tage sind';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subDays(7);
        
        // Log für Debugging
        \Log::info("CleanupOldTicketTranscripts: Starte Cleanup für Transcripts älter als {$cutoffDate}");
        
        $tickets = Ticket::where('status', 'closed')
            ->whereNotNull('transcript')
            ->whereNotNull('closed_at')
            ->get();
        
        $countBefore = $tickets->count();
        \Log::info("CleanupOldTicketTranscripts: Gefunden {$countBefore} Tickets mit Transcripts");
        
        $deleted = Ticket::where('status', 'closed')
            ->whereNotNull('transcript')
            ->whereNotNull('closed_at')
            ->where('closed_at', '<', $cutoffDate)
            ->update(['transcript' => null]);

        $this->info("{$deleted} Transcripts wurden gelöscht (älter als 7 Tage).");
        \Log::info("CleanupOldTicketTranscripts: {$deleted} Transcripts gelöscht");
        
        return Command::SUCCESS;
    }
}
