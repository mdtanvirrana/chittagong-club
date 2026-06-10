<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCommitteeRecords;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommitteeMemberRequest;
use App\Models\CommitteeMember;
use App\Support\PortalCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommitteeController extends Controller
{
    use ManagesCommitteeRecords;

    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $records = $this->committeeRecordsQuery()
            ->when($search !== '', fn ($query) => $this->applyCommitteeSearch($query, $search))
            ->orderByDesc('committee.ct_from_year')
            ->orderBy('committee.id_serial')
            ->orderBy('committee.id_org_committee_key')
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
            'id_serial' => $this->nextCommitteeSerial($currentYear),
            'ct_from_year' => $currentYear,
            'ct_to_year' => $currentYear + 1,
            'is_active' => true,
        ]), false, $this->section()));
    }

    public function store(CommitteeMemberRequest $request)
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
            ->route('admin.committees.index')
            ->with('status', 'Committee member created successfully.');
    }

    public function edit(int $committee)
    {
        return view('admin.committee-records.form', $this->formViewData(
            $this->findCommitteeRecord($committee),
            true,
            $this->section()
        ));
    }

    public function update(CommitteeMemberRequest $request, int $committee)
    {
        $record = $this->findCommitteeRecord($committee);

        $record->fill(array_merge($this->committeeRecordPayload($request), [
            'id_org_committee_ver' => max(1, (int) $record->id_org_committee_ver) + 1,
            'dtt_mod' => now(),
            'id_user_mod' => $this->resolveCommitteeAuditUserId(),
        ]))->save();

        PortalCache::clearOrgCommitteeCaches();

        return redirect()
            ->route('admin.committees.index')
            ->with('status', 'Committee member updated successfully.');
    }

    public function destroy(int $committee)
    {
        $this->findCommitteeRecord($committee)->delete();

        PortalCache::clearOrgCommitteeCaches();

        return redirect()
            ->route('admin.committees.index')
            ->with('status', 'Committee member deleted successfully.');
    }

    private function section(): array
    {
        return [
            'title' => 'Committee',
            'pageTitleCreate' => 'Create Committee Member',
            'pageTitleEdit' => 'Edit Committee Member',
            'eyebrow' => 'Leadership',
            'managerEyebrow' => 'Committee Manager',
            'managerTitle' => 'Manage Committee Members',
            'description' => 'Create, edit, and remove records shown on the executive committee page.',
            'listTitle' => 'Committee List',
            'routePrefix' => 'admin.committees',
            'addLabel' => 'Add Member',
            'backLabel' => 'Back to committee',
            'saveLabel' => 'Save Member',
            'updateLabel' => 'Update Member',
            'deleteConfirm' => 'Delete this committee member?',
            'emptyLabel' => 'No committee members found.',
            'emptySearchLabel' => 'No committee members matched the current search.',
            'lockDesignation' => false,
        ];
    }
}
