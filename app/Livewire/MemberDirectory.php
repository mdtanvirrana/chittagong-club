<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
#[Title('Member Directory — Chittagong Club Ltd.')]
class MemberDirectory extends Component
{
    public string $search       = '';
    public string $activeFilter = 'All';
    public int    $perPage      = 20;
    public bool   $hasMore      = false;

    public function updatingSearch(): void
    {
        $this->perPage = 20;
    }

    public function updatingActiveFilter(): void
    {
        $this->perPage = 20; // reset BEFORE render
    }

    public function loadMore(): void
    {
        $this->perPage += 20;
    }

    private function baseQuery()
    {
        return DB::table('CustomerMst as c')
            ->leftJoin('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.Cardid')
            ->select([
                'c.PrvCusID',
                'c.CusName',
                'c.Mobile',
                'c.DOE',
                'cc.Remarks as MemberCategory',
            ])
            ->where('c.MemExpTypeID', 100)
            ->whereIn('c.Cardid', [101]);
    }

    public function render()
    {
        $query = $this->baseQuery();

        if (trim($this->search) !== '') {
            $s = '%' . $this->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('c.CusName',    'like', $s)
                    ->orWhere('c.PrvCusID', 'like', $s);
            });
        }

        if ($this->activeFilter !== 'All') {
            $query->where('cc.Remarks', $this->activeFilter);
        }

        $total   = (clone $query)->count();
        $members = (clone $query)
            ->orderBy('c.CusName')
            ->limit($this->perPage)
            ->get();

        $this->hasMore = $total > $this->perPage;

        $categories = DB::table('CusCardCatagory')
            ->whereNotNull('Remarks')
            ->orderBy('Remarks')
            ->pluck('Remarks')
            ->values();

        return view('livewire.member-directory', [
            'members'    => $members,
            'categories' => $categories,
            'total'      => $total,
        ]);
    }
}
