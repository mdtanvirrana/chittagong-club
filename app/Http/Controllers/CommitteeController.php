<?php

namespace App\Http\Controllers;

use App\Support\PortalCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommitteeController extends Controller
{
    public function index()
    {
        $currentYear  = (int) Carbon::now()->format('Y');
        $previousYear = $currentYear - 1;
        $version = PortalCache::contentVersion('committee');

        $members = collect(PortalCache::remember(
            "committee_members_{$currentYear}_{$previousYear}_v3_{$version}",
            now()->addMinutes(30),
            function () use ($currentYear, $previousYear): array {
                return DB::table('T_ORG_COMMITTEE as oc')
                    ->join('CustomerMst as c', 'oc.PrvcusID', '=', 'c.PrvCusID')
                    ->where('oc.is_active', 1)
                    ->where(function ($q) use ($currentYear, $previousYear) {
                        $q->where('oc.ct_from_year', $currentYear)
                            ->orWhere('oc.ct_from_year', $previousYear)
                            ->orWhere('oc.ct_to_year', $currentYear)
                            ->orWhere('oc.ct_to_year', $previousYear);
                    })
                    ->orderBy('oc.ct_from_year', 'desc')
                    ->orderBy('oc.id_serial', 'asc')
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
                        $name = trim((trim((string) $m->Title) !== '' ? trim((string) $m->Title) . ' ' : '') . $m->CusName);
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
                            'designation' => trim((string) ($m->tx_designation ?? '')),
                            'area' => trim((string) ($m->tx_area ?? '')),
                            'phone' => $phone,
                            'year_from' => $m->ct_from_year,
                            'year_to' => $m->ct_to_year,
                            'has_photo' => PortalCache::hasMemberPhoto($m->PrvCusID),
                            'photo_url' => PortalCache::memberPhotoUrl($m->PrvCusID),
                        ];
                    })
                    ->values()
                    ->all();
            }
        ));

        // Group by year range for display
        $grouped = $members->groupBy(fn($m) => $m['year_from'] . '–' . $m['year_to'])
            ->map(fn($group, $label) => [
                'label'   => $label,
                'members' => $group->values(),
            ])
            ->values();

        return view('pages.executive-committee', compact('grouped', 'currentYear', 'previousYear'));
    }
}
