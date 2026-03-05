<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

#[Layout('layouts.app')]
#[Title('Member Directory — Chittagong Club Ltd.')]
class MemberDirectory extends Component
{
    public string $search       = '';
    public string $activeFilter = 'All';
    public int    $perPage      = 20;
    public bool   $hasMore      = true;

    public function updatedSearch(): void
    {
        $this->perPage  = 20;
        $this->hasMore  = true;
    }

    public function updatedActiveFilter(): void
    {
        $this->perPage  = 20;
        $this->hasMore  = true;
    }

    public function loadMore(): void
    {
        $this->perPage += 20;
    }

    public function render()
    {
        $query = DB::table('CustomerMst as c')
            ->leftJoin('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.Cardid')
            ->select([
                'c.PrvCusID',
                'c.CusName',
                'c.Mobile',
                'c.DOE',
                'cc.Remarks as MemberCategory',
            ])
            //            ->where('MemExpTypeID',100)
            ->orderBy('PrvCusID')
//            ->whereNotIn('c.Cardid', [137])
            ->where('c.is_active', 1);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('c.CusName',   'like', '%' . $this->search . '%')
                    ->orWhere('c.PrvCusID','like', '%' . $this->search . '%');
            });
        }

        if ($this->activeFilter !== 'All') {
            $query->where('cc.Remarks', $this->activeFilter);
        }

        $total   = (clone $query)->count();
        $members = $query->orderBy('c.CusName')->limit($this->perPage)->get();

        $this->hasMore = $members->count() < $total;

        $categories = DB::table('CusCardCatagory')
            ->orderBy('Remarks')
            ->pluck('Remarks')
            ->filter()
            ->values();

        return view('livewire.member-directory', [
            'members'    => $members,
            'categories' => $categories,
            'total'      => DB::table('CustomerMst')->where('is_active', 1)->count(),
        ]);
    }
}
