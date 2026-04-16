<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $appUrlScheme = parse_url((string) config('app.url'), PHP_URL_SCHEME);

        if ($this->app->isProduction() || $appUrlScheme === 'https') {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view): void {
            static $companyProfile;

            if ($companyProfile === null) {
                $companyProfile = [
                    'companyName' => 'Chittagong Club Ltd.',
                    'companyAddress' => '',
                    'companyAddressLines' => [],
                    'companyAddressMapQuery' => 'Chittagong Club Ltd.',
                ];

                try {
                    $profile = DB::table('CPROFILE')
                        ->select(['COMPANY', 'HOAddress'])
                        ->first();

                    $companyName = trim((string) ($profile?->COMPANY ?? '')) ?: $companyProfile['companyName'];
                    $companyAddress = trim((string) ($profile?->HOAddress ?? ''));

                    $companyProfile = [
                        'companyName' => $companyName,
                        'companyAddress' => $companyAddress,
                        'companyAddressLines' => $companyAddress !== ''
                            ? preg_split('/\r\n|\r|\n/', $companyAddress)
                            : [],
                        'companyAddressMapQuery' => trim($companyName . ' ' . $companyAddress),
                    ];
                } catch (Throwable) {
                    //
                }
            }

            $view->with($companyProfile);
        });
    }
}
