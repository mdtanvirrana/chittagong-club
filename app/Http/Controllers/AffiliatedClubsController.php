<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AffiliatedClubsController extends Controller
{
    public function index()
    {
        $clubs = DB::table('T_AFFILIATED_CLUBS')
            ->where('is_active', 1)
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
            ])
            ->get()
            ->map(function ($c) {
                $address = $c->HOAddress ?? '';

                // BranchTel is comma-separated — take first number
                $firstPhone = null;
                if ($c->BranchTel) {
                    $parts      = preg_split('/[,\/;\s]+/', $c->BranchTel);
                    $firstPhone = trim($parts[0] ?? '');
                }
                $firstPhone = $firstPhone ?: ($c->HOTel ? trim(preg_split('/[,\/;\s]+/', $c->HOTel)[0]) : null);
                $firstPhone = $firstPhone ?: $c->tx_mobile ?: null;

                // All phone numbers for detail modal
                $allPhones = collect();
                foreach ([$c->BranchTel, $c->HOTel, $c->tx_mobile] as $src) {
                    if ($src) {
                        foreach (preg_split('/[,\/;]+/', $src) as $num) {
                            $num = trim($num);
                            if ($num) $allPhones->push($num);
                        }
                    }
                }

                $initials = collect(explode(' ', $c->COMPANY ?? ''))
                    ->map(fn($w) => strtoupper($w[0] ?? ''))
                    ->take(2)->join('');

                return [
                    'id'         => $c->id_affiliated_club_key,
                    'serial'     => $c->id_serial,
                    'name'       => $c->COMPANY ?? $c->BranchName ?? '—',
                    'branch'     => $c->BranchName ?? '',
                    'address'    => $address,
                    'initials'   => $initials,
                    'first_phone'=> $firstPhone,
                    'all_phones' => $allPhones->unique()->values()->all(),
                    'email'      => $c->tx_email ?? '',
                    'website'    => $c->tx_url ?? '',
                    'fax'        => $c->tx_fax ?? '',
                    'ceo'        => $c->CEO ?? '',
                ];
            });

        return view('pages.affiliated-clubs', compact('clubs'));
    }
}