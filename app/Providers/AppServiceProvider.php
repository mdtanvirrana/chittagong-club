<?php

namespace App\Providers;

use App\Support\CompanyProfile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
                $companyProfile = CompanyProfile::current();
            }

            $view->with($companyProfile + [
                'companyProfile' => $companyProfile,
                'companyLogoUrl' => $companyProfile['logoUrl'] ?? asset('logo.png'),
                'companyFaviconUrl' => $companyProfile['logoUrl'] ?? asset('favicon.ico'),
            ]);
        });
    }
}
