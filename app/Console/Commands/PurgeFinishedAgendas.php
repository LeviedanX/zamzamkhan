<?php

namespace App\Console\Commands;

use App\Support\AgendaPurger;
use Illuminate\Console\Command;

class PurgeFinishedAgendas extends Command
{
    protected $signature = 'agendas:purge';

    protected $description = 'Hapus agenda yang waktu selesainya sudah lewat, beserta gambarnya.';

    public function handle(): int
    {
        $deleted = AgendaPurger::purgeFinished();

        $this->info($deleted > 0
            ? "{$deleted} agenda selesai telah dihapus."
            : 'Tidak ada agenda selesai yang perlu dihapus.');

        return self::SUCCESS;
    }
}
