<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CircularRequest;
use App\Models\CircularItem;
use App\Support\PortalContent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CircularController extends Controller
{
    public function index()
    {
        $circulars = CircularItem::query()
            ->orderByDesc('dtt_ad_start')
            ->orderByDesc('id_career_key')
            ->paginate(12)
            ->withQueryString();

        return view('admin.circulars.index', compact('circulars'));
    }

    public function create()
    {
        return view('admin.circulars.form', [
            'circular' => new CircularItem([
                'dtt_ad_start' => now(),
                'dtt_ad_close' => now()->copy()->addDays(30),
                'is_active' => true,
                'is_online' => true,
            ]),
            'isEditing' => false,
        ]);
    }

    public function store(CircularRequest $request)
    {
        DB::transaction(function () use ($request) {
            $now = now();
            $nextId = ((int) (CircularItem::query()->lockForUpdate()->max('id_career_key') ?? 10000)) + 1;
            $userId = $this->resolveAuditUserId();

            CircularItem::query()->create([
                'id_career_key' => $nextId,
                'id_career_ver' => 1,
                'is_active' => $request->boolean('is_active'),
                'dtt_mod' => $now,
                'id_user_mod' => $userId,
                'id_env_key' => 100000,
                'id_event_key' => 1,
                'id_state_key' => 100000,
                'id_action_key' => 100000,
                'dtt_added' => $now,
                'is_online' => $request->boolean('is_online'),
                'tx_title' => trim((string) $request->input('title')),
                'tx_url' => PortalContent::optionalField($request->input('external_url')),
                'tx_body' => PortalContent::plainTextToDelta($request->input('body')),
                'tx_hash' => PortalContent::optionalField($request->input('hash')),
                'tx_tag' => PortalContent::optionalField($request->input('tag')),
                'tx_career_type' => PortalContent::optionalField($request->input('career_type')),
                'tx_address' => PortalContent::optionalField($request->input('address')),
                'tx_phone' => PortalContent::optionalField($request->input('phone')),
                'tx_email' => PortalContent::optionalField($request->input('email')),
                'flt_cost' => 0,
                'flt_cpc' => 0,
                'flt_cpc_max' => 0,
                'ct_click' => 0,
                'flt_duration' => 0,
                'dtt_ad_start' => $request->date('publish_at'),
                'dtt_ad_close' => $request->filled('close_at') ? $request->date('close_at') : null,
                'dtt_ad_exp' => '1900-01-01 00:00:00',
                'flt_min_salary' => 0,
                'flt_max_salary' => 0,
                'ct_seen' => 0,
                'ct_interval' => 0,
            ]);
        });

        PortalContent::clearCircularCaches();

        return redirect()
            ->route('admin.circulars.index')
            ->with('status', 'Circular created successfully.');
    }

    public function edit(int $circular)
    {
        return view('admin.circulars.form', [
            'circular' => $this->findCircular($circular),
            'isEditing' => true,
        ]);
    }

    public function update(CircularRequest $request, int $circular)
    {
        $circularRow = $this->findCircular($circular);

        $circularRow->fill([
            'id_career_ver' => max(1, (int) $circularRow->id_career_ver) + 1,
            'is_active' => $request->boolean('is_active'),
            'dtt_mod' => now(),
            'id_user_mod' => $this->resolveAuditUserId(),
            'is_online' => $request->boolean('is_online'),
            'tx_title' => trim((string) $request->input('title')),
            'tx_url' => PortalContent::optionalField($request->input('external_url')),
            'tx_body' => PortalContent::plainTextToDelta($request->input('body')),
            'tx_hash' => PortalContent::optionalField($request->input('hash')),
            'tx_tag' => PortalContent::optionalField($request->input('tag')),
            'tx_career_type' => PortalContent::optionalField($request->input('career_type')),
            'tx_address' => PortalContent::optionalField($request->input('address')),
            'tx_phone' => PortalContent::optionalField($request->input('phone')),
            'tx_email' => PortalContent::optionalField($request->input('email')),
            'dtt_ad_start' => $request->date('publish_at'),
            'dtt_ad_close' => $request->filled('close_at') ? $request->date('close_at') : null,
        ])->save();

        PortalContent::clearCircularCaches();

        return redirect()
            ->route('admin.circulars.index')
            ->with('status', 'Circular updated successfully.');
    }

    public function toggleOnline(int $circular)
    {
        $circularRow = $this->findCircular($circular);
        $circularRow->fill([
            'is_online' => ! (bool) $circularRow->is_online,
            'id_career_ver' => max(1, (int) $circularRow->id_career_ver) + 1,
            'dtt_mod' => now(),
            'id_user_mod' => $this->resolveAuditUserId(),
        ])->save();

        PortalContent::clearCircularCaches();

        return back()->with('status', 'Circular visibility updated.');
    }

    public function toggleActive(int $circular)
    {
        $circularRow = $this->findCircular($circular);
        $circularRow->fill([
            'is_active' => ! (bool) $circularRow->is_active,
            'id_career_ver' => max(1, (int) $circularRow->id_career_ver) + 1,
            'dtt_mod' => now(),
            'id_user_mod' => $this->resolveAuditUserId(),
        ])->save();

        PortalContent::clearCircularCaches();

        return back()->with('status', 'Circular status updated.');
    }

    private function findCircular(int $circularId): CircularItem
    {
        return CircularItem::query()->findOrFail($circularId);
    }

    private function resolveAuditUserId(): int
    {
        $identifier = Auth::guard('admin')->user()?->userid;

        return is_numeric($identifier) ? (int) $identifier : 0;
    }
}
