<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use App\Support\MemberAccess;
use App\Support\NotifyOutbox;
use App\Support\GalleryAlbums;
use App\Support\GalleryImageOptimizer;
use App\Support\PortalCache;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:due-reminders', function () {
    $dueSubquery = DB::table('Customer_ledger')
        ->where('InvMRN', '<>', '0')
        ->select('PrvCusId')
        ->selectRaw('COALESCE(SUM(COALESCE(DrAmt, 0) - COALESCE(CrAmt, 0)), 0) as Due')
        ->groupBy('PrvCusId');

    $count = 0;

    MemberAccess::activeMemberQuery('c', 'cc')
        ->joinSub($dueSubquery, 'ledger_due', 'ledger_due.PrvCusId', '=', 'c.PrvCusID')
        ->where('ledger_due.Due', '>=', 40000)
        ->select('c.PrvCusID', 'c.CreditAmt', 'ledger_due.Due')
        ->orderBy('c.PrvCusID')
        ->chunk(200, function ($members) use (&$count): void {
            foreach ($members as $member) {
                NotifyOutbox::dueReminder(
                    (string) $member->PrvCusID,
                    (float) $member->Due,
                    (float) ($member->CreditAmt ?? 0)
                );
                $count++;
            }
        });

    $this->info("Due reminder scan completed for {$count} members.");
})->purpose('Create member due reminder notifications for due thresholds.');

Schedule::command('notifications:due-reminders')->hourly()->withoutOverlapping();

Artisan::command('gallery:optimize {--dry-run : Count optimizable files without changing them}', function () {
    GalleryAlbums::ensureBaseDirectory();

    $files = collect(File::allFiles(GalleryAlbums::baseDirectory()))
        ->filter(fn ($file): bool => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true));

    if ($this->option('dry-run')) {
        $this->info($files->count().' gallery image(s) can be checked for optimization.');

        return 0;
    }

    $optimized = 0;

    foreach ($files as $file) {
        if (GalleryImageOptimizer::optimizePath($file->getPathname())) {
            $optimized++;
        }
    }

    if ($optimized > 0) {
        PortalCache::clearPhotoRelatedCaches();
    }

    $this->info("Optimized {$optimized} of {$files->count()} gallery image(s).");

    return 0;
})->purpose('Resize and compress existing gallery images.');
