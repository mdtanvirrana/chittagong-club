<?php

namespace App\Http\Controllers;

use App\Models\AffiliatedClub;
use App\Support\PortalCache;
use Illuminate\Support\Facades\DB;

class AffiliatedClubsController extends Controller
{
    private const UNSET_COUNTRY_LABEL = 'Country not set';

    public function index()
    {
        $version = PortalCache::contentVersion('affiliated-clubs');

        $clubs = collect(PortalCache::remember("affiliated_clubs_v5_{$version}", now()->addMinutes(30), function (): array {
            $countryNames = $this->countryNamesByStoredValue();

            return AffiliatedClub::query()
                ->where('is_active', 1)
                ->select([
                    'id_affiliated_club_key',
                    'id_serial',
                    'COMPANY',
                    'Country',
                    'BranchName',
                    'BranchAddress',
                    'HOAddress',
                    'BranchTel',
                    'HOTel',
                    'tx_mobile',
                    'tx_email',
                    'tx_url',
                    'tx_fax',
                    'CEO',
                    'Logo_Path',
                    'image_path',
                ])
                ->get()
                ->map(function (AffiliatedClub $c) use ($countryNames) {
                    $address = $c->display_address ?? '';
                    $country = $this->resolveCountryName($c->getAttribute('Country'), $countryNames);
                    $hoAddress = trim((string) $c->getAttribute('HOAddress'));
                    $firstPhone = null;

                    if ($c->BranchTel) {
                        $parts = preg_split('/[,\/;\s]+/', $c->BranchTel);
                        $firstPhone = trim($parts[0] ?? '');
                    }

                    $firstPhone = $firstPhone ?: ($c->HOTel ? trim(preg_split('/[,\/;\s]+/', $c->HOTel)[0]) : null);
                    $firstPhone = $firstPhone ?: $c->tx_mobile ?: null;

                    $allPhones = collect();
                    foreach ([$c->BranchTel, $c->HOTel, $c->tx_mobile] as $src) {
                        if ($src) {
                            foreach (preg_split('/[,\/;]+/', $src) as $num) {
                                $num = trim($num);
                                if ($num) {
                                    $allPhones->push($num);
                                }
                            }
                        }
                    }

                    $initials = collect(explode(' ', $c->display_name))
                        ->map(fn ($w) => strtoupper($w[0] ?? ''))
                        ->take(2)
                        ->join('');

                    return [
                        'id' => $c->id_affiliated_club_key,
                        'serial' => $c->id_serial,
                        'name' => $c->display_name,
                        'country' => $country,
                        'branch' => $c->BranchName ?? '',
                        'address' => $address,
                        'ho_address' => $hoAddress,
                        'initials' => $initials,
                        'first_phone' => $firstPhone,
                        'all_phones' => $allPhones->unique()->values()->all(),
                        'email' => $c->tx_email ?? '',
                        'website' => $c->tx_url ?? '',
                        'fax' => $c->tx_fax ?? '',
                        'ceo' => $c->CEO ?? '',
                        'logo_url' => $c->display_logo_url,
                        'image_url' => $c->display_image_url,
                    ];
                })
                ->sort(fn (array $a, array $b) => $this->compareClubOrder($a, $b))
                ->values()
                ->all();
        }));

        return response()
            ->view('pages.affiliated-clubs', compact('clubs'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    private function countryNamesByStoredValue(): array
    {
        try {
            return DB::table('ACountry')
                ->select(['CID', 'CName'])
                ->get()
                ->reduce(function (array $names, object $country): array {
                    $name = $this->normalizeCountryValue($country->CName ?? null);

                    if ($name === '') {
                        return $names;
                    }

                    foreach ([$country->CID ?? null, $name] as $value) {
                        $key = $this->countryLookupKey($value);

                        if ($key !== '') {
                            $names[$key] = $name;
                        }
                    }

                    return $names;
                }, []);
        } catch (\Throwable) {
            return [];
        }
    }

    private function resolveCountryName(mixed $country, array $countryNames): string
    {
        $value = $this->normalizeCountryValue($country);

        if ($value === '' || $value === '0' || $value === '?') {
            return self::UNSET_COUNTRY_LABEL;
        }

        return $countryNames[$this->countryLookupKey($value)] ?? $value;
    }

    private function normalizeCountryValue(mixed $country): string
    {
        return preg_replace('/\s+/', ' ', trim((string) $country)) ?? '';
    }

    private function countryLookupKey(mixed $country): string
    {
        return strtolower($this->normalizeCountryValue($country));
    }

    private function compareClubOrder(array $a, array $b): int
    {
        $aKey = $this->clubOrderKey($a);
        $bKey = $this->clubOrderKey($b);

        foreach ($aKey as $index => $value) {
            $comparison = $value <=> $bKey[$index];

            if ($comparison !== 0) {
                return $comparison;
            }
        }

        return 0;
    }

    private function clubOrderKey(array $club): array
    {
        $country = $this->normalizeCountryValue($club['country'] ?? '');

        return [
            $this->countrySortRank($country),
            strtolower($country),
            strtolower($this->normalizeCountryValue($club['name'] ?? '')),
            (int) ($club['id'] ?? 0),
        ];
    }

    private function countrySortRank(string $country): int
    {
        return strcasecmp($country, self::UNSET_COUNTRY_LABEL) === 0 ? 1 : 0;
    }
}
