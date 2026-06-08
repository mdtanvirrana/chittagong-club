<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use App\Support\MemberAccess;
use App\Support\NotifyOutbox;
use App\Support\GalleryAlbums;
use App\Support\GalleryImageOptimizer;
use App\Support\ImageVariants;
use App\Support\PortalCache;
use App\Support\PortalImageDirectory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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

Artisan::command('images:warm-variants {--gallery : Warm gallery thumbnails and previews} {--people : Warm member and employee thumbnails and previews} {--content : Warm circular, affiliated club, company, and page images}', function () {
    $warmGallery = (bool) $this->option('gallery');
    $warmPeople = (bool) $this->option('people');
    $warmContent = (bool) $this->option('content');

    if (! $warmGallery && ! $warmPeople && ! $warmContent) {
        $warmGallery = true;
        $warmPeople = true;
        $warmContent = true;
    }

    $files = collect();

    if ($warmGallery) {
        GalleryAlbums::ensureBaseDirectory();
        $files = $files->merge(
            collect(File::allFiles(GalleryAlbums::baseDirectory()))
                ->map(fn ($file): array => [
                    'path' => $file->getPathname(),
                    'relative' => ltrim(Str::after(str_replace('\\', '/', $file->getPathname()), str_replace('\\', '/', public_path()).'/'), '/'),
                    'variants' => ImageVariants::galleryVariants(),
                ])
        );
    }

    if ($warmPeople) {
        foreach ([PortalImageDirectory::MEMBER_DIRECTORY, PortalImageDirectory::EMPLOYEE_DIRECTORY] as $folder) {
            $directory = PortalImageDirectory::absoluteDirectory($folder);

            if (! is_dir($directory)) {
                continue;
            }

            $files = $files->merge(
                collect(File::files($directory))
                    ->map(fn ($file): array => [
                        'path' => $file->getPathname(),
                        'relative' => PortalImageDirectory::relativePath($folder, $file->getFilename()),
                        'variants' => ImageVariants::memberVariants(),
                    ])
            );
        }
    }

    if ($warmContent) {
        $contentDirectories = [
            public_path('circular') => [[640, 900, 72, 'contain']],
            public_path('affiliated_clubs') => [[640, 360, 72, 'cover'], [160, 160, 72, 'contain']],
            public_path('company_profile') => [[640, 360, 72, 'contain'], [160, 160, 72, 'contain']],
        ];

        foreach ([PortalImageDirectory::FACILITIES_DIRECTORY, PortalImageDirectory::DRESS_CODE_DIRECTORY, PortalImageDirectory::GENERAL_RULES_DIRECTORY] as $folder) {
            $contentDirectories[PortalImageDirectory::absoluteDirectory($folder)] = [[640, 900, 72, 'contain'], [1080, 1080, 74, 'contain']];
        }

        foreach ($contentDirectories as $directory => $variants) {
            if (! is_dir($directory)) {
                continue;
            }

            $files = $files->merge(
                collect(File::allFiles($directory))
                    ->map(fn ($file): array => [
                        'path' => $file->getPathname(),
                        'relative' => ltrim(Str::after(str_replace('\\', '/', $file->getPathname()), str_replace('\\', '/', public_path()).'/'), '/'),
                        'variants' => $variants,
                    ])
            );
        }
    }

    $files = $files
        ->filter(fn (array $item): bool => in_array(strtolower(pathinfo($item['path'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true))
        ->values();

    $warmed = 0;

    foreach ($files as $item) {
        $warmed += ImageVariants::warm($item['relative'], $item['variants']);
    }

    PortalCache::clearPhotoRelatedCaches();
    $this->info("Warmed {$warmed} image variant(s) for {$files->count()} source image(s).");

    return 0;
})->purpose('Pre-generate static WebP variants used by the mobile app.');

Artisan::command('api:warm-mobile-cache', function () {
    $tasks = [
        'app-config' => fn () => app(\App\Http\Controllers\Api\V1\AppConfigController::class)->show(),
        'gallery' => fn () => app(\App\Http\Controllers\Api\V1\ClubContentController::class)->gallery(),
        'employees' => fn () => app(\App\Http\Controllers\Api\V1\ClubContentController::class)->employees(\Illuminate\Http\Request::create('/api/v1/employees', 'GET', ['page' => 1, 'per_page' => 20])),
        'committee' => fn () => app(\App\Http\Controllers\Api\V1\ClubContentController::class)->committee(\Illuminate\Http\Request::create('/api/v1/committee', 'GET', ['page' => 1, 'per_page' => 20])),
        'former-chairmen' => fn () => app(\App\Http\Controllers\Api\V1\ClubContentController::class)->formerChairmen(\Illuminate\Http\Request::create('/api/v1/former-chairmen', 'GET', ['page' => 1, 'per_page' => 20])),
        'member-directory' => fn () => app(\App\Http\Controllers\Api\V1\MemberDirectoryController::class)->index(\Illuminate\Http\Request::create('/api/v1/directory', 'GET', ['page' => 1, 'per_page' => 20])),
    ];

    foreach ($tasks as $name => $task) {
        $startedAt = microtime(true);

        try {
            $task();
            $this->info($name.' warmed in '.number_format((microtime(true) - $startedAt) * 1000, 1).' ms');
        } catch (Throwable $exception) {
            $this->error($name.' failed: '.$exception->getMessage());
        }
    }

    return 0;
})->purpose('Warm Redis payload caches for mobile app endpoints.');
