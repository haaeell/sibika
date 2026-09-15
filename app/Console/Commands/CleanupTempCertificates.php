<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupTempCertificates extends Command
{
    protected $signature = 'app:cleanup-temp-certificates {--hours=24 : Hapus file temp lebih lama dari jumlah jam ini}';

    protected $description = 'Hapus file sertifikat sementara yang tidak jadi dilampirkan';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $threshold = now()->subHours((int) $this->option('hours'));
        $deleted = 0;

        foreach ($disk->allFiles('tmp/certificates') as $path) {
            if ($disk->lastModified($path) < $threshold->getTimestamp()) {
                $disk->delete($path);
                $deleted++;
            }
        }

        $this->info("Berhasil menghapus {$deleted} file sertifikat sementara.");

        return self::SUCCESS;
    }
}
