<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\CompanyProfile;
use App\Support\PortalCache;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{
    public function show(): JsonResponse
    {
        $payload = PortalCache::rememberGlobal('app_config', now()->addSeconds(PortalCache::ttl('global')), function (): array {
            $profile = CompanyProfile::current();

            return [
                'company' => [
                    'name' => $profile['companyName'],
                    'short_name' => $profile['shortName'],
                    'address' => $profile['companyAddressText'],
                    'phones' => $profile['phones'],
                    'email' => $profile['email'],
                    'website_url' => $profile['websiteUrl'],
                ],
                'assets' => [
                    'logo_url' => $profile['logoUrl'],
                    'logo_thumb_url' => $profile['logoThumbUrl'],
                    'club_photo_url' => $profile['clubPhotoUrl'],
                    'club_photo_thumb_url' => $profile['clubPhotoThumbUrl'],
                    'canterbury_font_url' => asset('assets/canterbury/Canterbury.ttf'),
                ],
                'theme' => config('theme.member'),
                'payment' => [
                    'return_url' => config('services.mobile_app.payment_return_url'),
                    'sandbox' => (bool) config('services.sslcommerz.sandbox'),
                    'currency' => config('services.sslcommerz.currency', 'BDT'),
                ],
            ];
        });

        return response()->json($payload);
    }
}
