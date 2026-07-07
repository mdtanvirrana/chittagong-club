<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Http\Requests\Admin\CommitteeMemberRequest;
use App\Models\CommitteeMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesCommitteeRecords
{
    protected function committeeRecordsQuery(): Builder
    {
        return CommitteeMember::query()
            ->from('T_ORG_COMMITTEE as committee')
            ->leftJoin('CustomerMst as customers', 'committee.PrvcusID', '=', 'customers.PrvCusID')
            ->select([
                'committee.*',
                'customers.Title',
                'customers.CusName',
                'customers.Mobile',
                'customers.Phone',
            ]);
    }

    protected function applyCommitteeSearch(Builder $query, string $search): void
    {
        $like = '%'.$search.'%';

        $query->where(function (Builder $builder) use ($like, $search): void {
            $builder
                ->whereRaw('LTRIM(RTRIM(committee.PrvcusID)) LIKE ?', [$like])
                ->orWhereRaw('LTRIM(RTRIM(committee.tx_designation)) LIKE ?', [$like])
                ->orWhereRaw('LTRIM(RTRIM(committee.tx_area)) LIKE ?', [$like])
                ->orWhereRaw('LTRIM(RTRIM(customers.CusName)) LIKE ?', [$like])
                ->orWhereRaw('LTRIM(RTRIM(customers.Mobile)) LIKE ?', [$like])
                ->orWhereRaw('LTRIM(RTRIM(customers.Phone)) LIKE ?', [$like]);

            if (ctype_digit($search)) {
                $builder
                    ->orWhere('committee.id_org_committee_key', (int) $search)
                    ->orWhere('committee.id_serial', (int) $search)
                    ->orWhere('committee.ct_from_year', (int) $search)
                    ->orWhere('committee.ct_to_year', (int) $search);
            }
        });
    }

    protected function applyFormerChairmanFilter(Builder $query): void
    {
        $query->whereRaw('LOWER(LTRIM(RTRIM(committee.tx_designation))) = ?', ['chairman']);
    }

    protected function findCommitteeRecord(int $recordId): CommitteeMember
    {
        return CommitteeMember::query()->findOrFail($recordId);
    }

    protected function findFormerChairmanRecord(int $recordId): CommitteeMember
    {
        return CommitteeMember::query()
            ->whereKey($recordId)
            ->whereRaw('LOWER(LTRIM(RTRIM(tx_designation))) = ?', ['chairman'])
            ->firstOrFail();
    }

    protected function committeeRecordPayload(CommitteeMemberRequest $request): array
    {
        return [
            'is_active' => $request->boolean('is_active'),
            'id_serial' => (int) $request->input('serial'),
            'PrvcusID' => $this->nullableCommitteeInput($request->input('member_id')),
            'tx_designation' => $this->nullableCommitteeInput($request->input('designation')),
            'ct_from_year' => (int) $request->input('from_year'),
            'ct_to_year' => (int) $request->input('to_year'),
            'tx_area' => $this->nullableCommitteeInput($request->input('area')),
            'UserSI' => 0,
        ];
    }

    protected function nextCommitteePrimaryKey(): int
    {
        return ((int) (CommitteeMember::query()
            ->lockForUpdate()
            ->max('id_org_committee_key') ?? 0)) + 1;
    }

    protected function nextCommitteeSerial(?int $fromYear = null): int
    {
        $query = CommitteeMember::query();

        if ($fromYear !== null) {
            $query->where('ct_from_year', $fromYear);
        }

        return ((int) ($query->max('id_serial') ?? 0)) + 1;
    }

    protected function committeeCreationMetadata(int $recordId): array
    {
        $now = now();

        return [
            'id_org_committee_key' => $recordId,
            'id_org_committee_ver' => 1,
            'id_ds_env' => 100000,
            'dtt_mod' => $now,
            'id_user_mod' => $this->resolveCommitteeAuditUserId(),
            'id_env_key' => 100000,
            'id_event_key' => 1,
            'id_state_key' => 100000,
            'id_action_key' => 100000,
            'dtt_added' => $now,
            'Edate' => $now->toDateString(),
            'Etime' => $now->format('H:i:s'),
        ];
    }

    protected function formViewData(CommitteeMember $record, bool $isEditing, array $section): array
    {
        $selectedMemberId = old('member_id', $record->member_id);

        return [
            'record' => $record,
            'section' => $section,
            'isEditing' => $isEditing,
            'memberOptions' => $this->memberOptions($selectedMemberId),
            'selectedMember' => $this->memberSummary($selectedMemberId),
        ];
    }

    protected function memberOptions(?string $selectedMemberId = null): array
    {
        $selectedMemberId = trim((string) $selectedMemberId);

        $members = DB::table('CustomerMst')
            ->whereNotNull('PrvCusID')
            ->whereRaw("LTRIM(RTRIM(PrvCusID)) <> ''")
            ->whereNotNull('CusName')
            ->whereRaw("LTRIM(RTRIM(CusName)) <> ''")
            ->orderBy('PrvCusID')
            ->limit(500)
            ->get(['PrvCusID', 'Title', 'CusName', 'Mobile', 'Phone'])
            ->map(fn (object $member): array => $this->formatMemberSummary($member))
            ->values();

        if ($selectedMemberId !== '' && ! $members->contains('id', $selectedMemberId)) {
            $selected = $this->memberSummary($selectedMemberId);

            if ($selected !== null) {
                $members->prepend($selected);
            }
        }

        return $members->all();
    }

    protected function memberSummary(?string $memberId): ?array
    {
        $memberId = trim((string) $memberId);

        if ($memberId === '') {
            return null;
        }

        $member = DB::table('CustomerMst')
            ->where('PrvCusID', $memberId)
            ->first(['PrvCusID', 'Title', 'CusName', 'Mobile', 'Phone']);

        return $member ? $this->formatMemberSummary($member) : null;
    }

    protected function formatMemberSummary(object $member): array
    {
        $name = trim((string) $member->CusName);
        $title = trim((string) $member->Title);
        $phone = trim((string) ($member->Mobile ?: $member->Phone));

        return [
            'id' => trim((string) $member->PrvCusID),
            'name' => trim(($title !== '' ? $title.' ' : '').$name),
            'phone' => $phone !== '' ? $phone : null,
        ];
    }

    protected function resolveCommitteeAuditUserId(): int
    {
        $identifier = Auth::guard('admin')->user()?->userid;

        return is_numeric($identifier) ? (int) $identifier : 0;
    }

    protected function nullableCommitteeInput(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
