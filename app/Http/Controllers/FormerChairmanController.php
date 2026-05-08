<?php

namespace App\Http\Controllers;

use App\Support\PortalCache;
use Illuminate\Support\Facades\DB;

class FormerChairmanController extends Controller
{
    public function index()
    {
        $members = collect(PortalCache::remember('former_chairman_v2', now()->addMinutes(30), function (): array {
            return DB::table('T_ORG_COMMITTEE as oc')
                ->join('CustomerMst as c', 'oc.PrvcusID', '=', 'c.PrvCusID')
                ->where('oc.is_active', 1)
                ->where(function ($q) {
                    $q->whereRaw("LOWER(oc.tx_designation) LIKE 'chairman'");
                })
                ->orderBy('oc.ct_from_year', 'desc')
                ->orderBy('oc.id_serial', 'desc')
                ->select([
                    'oc.id_org_committee_key',
                    'oc.id_serial',
                    'oc.tx_designation',
                    'oc.tx_area',
                    'oc.ct_from_year',
                    'oc.ct_to_year',
                    'c.PrvCusID',
                    'c.CusName',
                    'c.Title',
                    'c.Mobile',
                    'c.Phone',
                ])
                ->get()
                ->map(function ($m) {
                    $name = trim(($m->Title ? $m->Title . ' ' : '') . $m->CusName);
                    $phone = $m->Mobile ?: $m->Phone ?: null;
                    $initials = collect(explode(' ', $m->CusName))
                        ->map(fn ($w) => strtoupper($w[0] ?? ''))
                        ->take(2)
                        ->join('');

                    return [
                        'id' => $m->id_org_committee_key,
                        'serial' => $m->id_serial,
                        'name' => $name,
                        'initials' => $initials,
                        'member_id' => $m->PrvCusID,
                        'designation' => $m->tx_designation ?? '',
                        'area' => $m->tx_area ?? '',
                        'phone' => $phone,
                        'year_from' => $m->ct_from_year,
                        'year_to' => $m->ct_to_year,
                        'has_photo' => PortalCache::hasMemberPhoto($m->PrvCusID),
                        'photo_url' => PortalCache::memberPhotoUrl($m->PrvCusID),
                    ];
                })
                ->values()
                ->all();
        }));

        // Group by year range
        $grouped = $members
            ->groupBy(fn($m) => $m['year_from'] . '–' . $m['year_to'])
            ->map(fn($group, $label) => [
                'label'   => $label,
                'members' => $group->values(),
            ])
            ->values();

        return view('pages.former-chairman', compact('grouped'));
    }
}
