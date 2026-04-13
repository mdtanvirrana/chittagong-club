<?php

namespace App\Http\Controllers;

use App\Support\PortalCache;
use Illuminate\Support\Facades\DB;

class EmployeeDirectoryController extends Controller
{
    public function index()
    {
        $employees = collect(PortalCache::remember('employee_directory_v1', now()->addMinutes(30), function (): array {
            return DB::table('EmployeesDetails')
                ->where('is_active', '1')
                ->where('PreStatus', 'Y')
                ->whereNotNull('EmpName')
                ->where('EmpName', '!=', '')
                ->orderBy('Branch')
                ->orderBy('EmpName')
                ->select([
                    'EmpID',
                    'EmpName',
                    'Title',
                    'Branch',
                    'Sec',
                    'Desig',
                    'Mobile',
                    'BloodGroup',
                    'Sex',
                    'DateJoin',
                ])
                ->get()
                ->map(function ($e) {
                    $name = trim(($e->Title && $e->Title !== '0' ? $e->Title . ' ' : '') . $e->EmpName);

                    $initials = collect(explode(' ', $e->EmpName))
                        ->filter(fn ($w) => strlen($w) > 0)
                        ->map(fn ($w) => strtoupper($w[0]))
                        ->take(2)
                        ->join('');

                    $phone = ($e->Mobile && $e->Mobile !== '0') ? $e->Mobile : null;
                    $joinYear = $e->DateJoin
                        ? \Carbon\Carbon::parse($e->DateJoin)->format('Y')
                        : null;

                    return [
                        'id' => $e->EmpID,
                        'name' => $name,
                        'initials' => $initials,
                        'branch' => $e->Branch ?? '',
                        'section' => ($e->Sec && $e->Sec !== $e->Branch) ? $e->Sec : '',
                        'desig' => $e->Desig ?? '',
                        'phone' => $phone,
                        'blood' => ($e->BloodGroup && $e->BloodGroup !== '0') ? $e->BloodGroup : '',
                        'sex' => $e->Sex ?? '',
                        'join_year' => $joinYear,
                    ];
                })
                ->values()
                ->all();
        }));

        // Group by Branch
        $grouped = $employees
            ->groupBy('branch')
            ->map(fn($group, $branch) => [
                'branch'  => $branch ?: 'General',
                'members' => $group->values(),
            ])
            ->values();

        return view('pages.employee-directory', compact('grouped', 'employees'));
    }
}
