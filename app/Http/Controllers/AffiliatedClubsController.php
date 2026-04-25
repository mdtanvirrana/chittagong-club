<?php

namespace App\Http\Controllers;

use App\Models\AffiliatedClub;
use App\Support\PortalCache;

class AffiliatedClubsController extends Controller
{
    public function index()
    {
        $clubs = collect(PortalCache::remember('affiliated_clubs_v2', now()->addMinutes(30), function (): array {
            return AffiliatedClub::query()
                ->where('is_active', 1)
                ->orderByRaw('CASE WHEN id_serial IS NULL THEN 1 ELSE 0 END')
                ->orderBy('id_serial')
                ->orderBy('COMPANY')
                ->select([
                    'id_affiliated_club_key',
                    'id_serial',
                    'COMPANY',
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
                    'image_path',
                ])
                ->get()
                ->map(function (AffiliatedClub $c) {
                    $address = $c->display_address ?? '';
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
                        'branch' => $c->BranchName ?? '',
                        'address' => $address,
                        'initials' => $initials,
                        'first_phone' => $firstPhone,
                        'all_phones' => $allPhones->unique()->values()->all(),
                        'email' => $c->tx_email ?? '',
                        'website' => $c->tx_url ?? '',
                        'fax' => $c->tx_fax ?? '',
                        'ceo' => $c->CEO ?? '',
                        'image_url' => $c->display_image_url,
                    ];
                })
                ->values()
                ->all();
        }));

        return view('pages.affiliated-clubs', compact('clubs'));
    }
}
