<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCommitteeRecords;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FormerChairmanRequest;
use App\Models\CommitteeMember;
use App\Support\PortalCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormerChairmanController extends Controller
{
    use ManagesCommitteeRecords;

    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $query = $this->committeeRecordsQuery();
        $this->applyFormerChairmanFilter($query);

        $records = $query
            ->when($search !== '', fn ($query) => $this->applyCommitteeSearch($query, $search))
            ->orderByDesc('committee.ct_from_year')
            ->orderByDesc('committee.id_serial')
            ->orderByDesc('committee.id_org_committee_key')
            ->paginate(15)
            ->withQueryString();

        return view('admin.committee-records.index', [
            'records' => $records,
            'search' => $search,
            'section' => $this->section(),
        ]);
    }

    public function create()
    {
        $currentYear = (int) now()->format('Y');

        return view('admin.committee-records.form', $this->formViewData(new CommitteeMember([
            'id_serial' => 1,
            'tx_designation' => 'Chairman',
            'ct_from_year' => $currentYear - 1,
            'ct_to_year' => $currentYear,
            'is_active' => true,
        ]), false, $this->section()));
    }

    public function store(FormerChairmanRequest $request)
    {
        DB::transaction(function () use ($request): void {
            $recordId = $this->nextCommitteePrimaryKey();

            CommitteeMember::query()->create(array_merge(
                $this->committeeCreationMetadata($recordId),
                $this->committeeRecordPayload($request),
            ));
        });

        PortalCache::clearOrgCommitteeCaches();

        return redirect()
            ->route('admin.former-chairmen.index')
            ->with('status', 'Former chairman created successfully.');
    }

    public function edit(int $chairman)
    {
        return view('admin.committee-records.form', $this->formViewData(
            $this->findFormerChairmanRecord($chairman),
            true,
            $this->section()
        ));
    }

    public function update(FormerChairmanRequest $request, int $chairman)
    {
        $record = $this->findFormerChairmanRecord($chairman);

        $record->fill(array_merge($this->committeeRecordPayload($request), [
            'id_org_committee_ver' => max(1, (int) $record->id_org_committee_ver) + 1,
            'dtt_mod' => now(),
            'id_user_mod' => $this->resolveCommitteeAuditUserId(),
        ]))->save();

        PortalCache::clearOrgCommitteeCaches();

        return redirect()
            ->route('admin.former-chairmen.index')
            ->with('status', 'Former chairman updated successfully.');
    }

    public function destroy(int $chairman)
    {
        $this->findFormerChairmanRecord($chairman)->delete();

        PortalCache::clearOrgCommitteeCaches();

        return redirect()
            ->route('admin.former-chairmen.index')
            ->with('status', 'Former chairman deleted successfully.');
    }

    private function section(): array
    {
        return [
            'title' => 'Former Chairman',
            'pageTitleCreate' => 'Create Former Chairman',
            'pageTitleEdit' => 'Edit Former Chairman',
            'eyebrow' => 'Leadership',
            'managerEyebrow' => 'Former Chairman Manager',
            'managerTitle' => 'Manage Former Chairmen',
            'description' => 'Create, edit, and remove chairman records shown on the former chairmen page.',
            'listTitle' => 'Former Chairman List',
            'routePrefix' => 'admin.former-chairmen',
            'addLabel' => 'Add Chairman',
            'backLabel' => 'Back to former chairmen',
            'saveLabel' => 'Save Chairman',
            'updateLabel' => 'Update Chairman',
            'deleteConfirm' => 'Delete this former chairman?',
            'emptyLabel' => 'No former chairmen found.',
            'emptySearchLabel' => 'No former chairmen matched the current search.',
            'lockDesignation' => true,
        ];
    }
}
