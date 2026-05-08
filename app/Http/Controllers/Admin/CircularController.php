<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CircularRequest;
use App\Models\CircularItem;
use App\Support\PortalContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CircularController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $circulars = CircularItem::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('tx_title', 'like', "%{$search}%")
                        ->orWhere('tx_body', 'like', "%{$search}%")
                        ->orWhere('tx_tag', 'like', "%{$search}%")
                        ->orWhere('id_career_key', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('dtt_ad_start')
            ->orderByDesc('id_career_key')
            ->paginate(12)
            ->withQueryString();

        return view('admin.circulars.index', compact('circulars', 'search'));
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
            $userId = $this->resolveAuditUserId();

            $circular = CircularItem::query()->create([
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
                'tx_url' => $this->resolveMetadataField($request, 'external_url'),
                'tx_body' => PortalContent::plainTextToDelta($request->input('body')),
                'tx_hash' => $this->resolveMetadataField($request, 'hash'),
                'tx_tag' => $this->resolveMetadataField($request, 'tag'),
                'tx_career_type' => $this->resolveMetadataField($request, 'career_type'),
                'tx_address' => $this->resolveMetadataField($request, 'address'),
                'tx_phone' => $this->resolveMetadataField($request, 'phone'),
                'tx_email' => $this->resolveMetadataField($request, 'email'),
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

            $imagePath = $this->storeImage($request, (int) $circular->id_career_key);

            if ($imagePath !== null) {
                $circular->forceFill([
                    'image_path' => $imagePath,
                ])->save();
            }
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
            'tx_url' => $this->resolveMetadataField($request, 'external_url', $circularRow->tx_url),
            'tx_body' => PortalContent::plainTextToDelta($request->input('body')),
            'tx_hash' => $this->resolveMetadataField($request, 'hash', $circularRow->tx_hash),
            'tx_tag' => $this->resolveMetadataField($request, 'tag', $circularRow->tx_tag),
            'tx_career_type' => $this->resolveMetadataField($request, 'career_type', $circularRow->tx_career_type),
            'tx_address' => $this->resolveMetadataField($request, 'address', $circularRow->tx_address),
            'tx_phone' => $this->resolveMetadataField($request, 'phone', $circularRow->tx_phone),
            'tx_email' => $this->resolveMetadataField($request, 'email', $circularRow->tx_email),
            'dtt_ad_start' => $request->date('publish_at'),
            'dtt_ad_close' => $request->filled('close_at') ? $request->date('close_at') : null,
            'image_path' => $this->storeImage($request, (int) $circularRow->id_career_key, $circularRow->image_url),
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

    private function resolveMetadataField(Request $request, string $key, ?string $currentValue = null): ?string
    {
        if ($request->exists($key)) {
            return PortalContent::optionalField($request->input($key));
        }

        return $currentValue ?? '?';
    }

    private function storeImage(Request $request, int $circularId, ?string $currentUrl = null): ?string
    {
        if (! $request->hasFile('image')) {
            return $currentUrl;
        }

        $image = $request->file('image');

        if ($image === null || ! $image->isValid()) {
            return $currentUrl;
        }

        $directory = public_path('circular');
        File::ensureDirectoryExists($directory);

        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $filename = sprintf('circular-%d-%s.%s', $circularId, Str::lower(Str::random(12)), $extension);

        $image->move($directory, $filename);

        $this->deleteManagedImage($currentUrl);

        return asset('circular/'.$filename);
    }

    private function deleteManagedImage(?string $url): void
    {
        $path = parse_url((string) $url, PHP_URL_PATH);

        if (! is_string($path) || ! str_starts_with($path, '/circular/')) {
            return;
        }

        $filename = basename($path);

        if ($filename === '' || $filename === '.' || $filename === '..') {
            return;
        }

        $fullPath = public_path('circular/'.$filename);

        if (is_file($fullPath)) {
            File::delete($fullPath);
        }
    }
}
