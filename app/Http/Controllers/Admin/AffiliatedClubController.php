<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AffiliatedClubRequest;
use App\Models\AffiliatedClub;
use App\Support\PortalCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AffiliatedClubController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $clubs = AffiliatedClub::query()
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';

                $query->where(function ($builder) use ($like) {
                    $builder
                        ->whereRaw('LTRIM(RTRIM(COMPANY)) LIKE ?', [$like])
                        ->orWhereRaw('LTRIM(RTRIM(BranchName)) LIKE ?', [$like])
                        ->orWhereRaw('LTRIM(RTRIM(BranchAddress)) LIKE ?', [$like])
                        ->orWhereRaw('LTRIM(RTRIM(HOAddress)) LIKE ?', [$like])
                        ->orWhereRaw('LTRIM(RTRIM(BranchTel)) LIKE ?', [$like])
                        ->orWhereRaw('LTRIM(RTRIM(HOTel)) LIKE ?', [$like])
                        ->orWhereRaw('LTRIM(RTRIM(tx_mobile)) LIKE ?', [$like])
                        ->orWhereRaw('LTRIM(RTRIM(tx_email)) LIKE ?', [$like])
                        ->orWhereRaw('LTRIM(RTRIM(CEO)) LIKE ?', [$like]);
                });
            })
            ->orderByRaw('CASE WHEN id_serial IS NULL THEN 1 ELSE 0 END')
            ->orderBy('id_serial')
            ->orderBy('COMPANY')
            ->paginate(15)
            ->withQueryString();

        return view('admin.affiliated-clubs.index', compact('clubs', 'search'));
    }

    public function create()
    {
        return view('admin.affiliated-clubs.form', [
            'club' => new AffiliatedClub([
                'id_serial' => ((int) (AffiliatedClub::query()->max('id_serial') ?? 0)) + 1,
                'is_active' => true,
            ]),
            'isEditing' => false,
        ]);
    }

    public function store(AffiliatedClubRequest $request)
    {
        DB::transaction(function () use ($request): void {
            $now = now();
            $nextId = ((int) (AffiliatedClub::query()->lockForUpdate()->max('id_affiliated_club_key') ?? 0)) + 1;

            AffiliatedClub::query()->create([
                'id_affiliated_club_key' => $nextId,
                'id_affiliated_club_ver' => 1,
                'is_active' => $request->boolean('is_active'),
                'id_ds_env' => 100000,
                'dtt_mod' => $now,
                'id_user_mod' => $this->resolveAuditUserId(),
                'id_env_key' => 100000,
                'id_event_key' => 1,
                'id_state_key' => 100000,
                'id_action_key' => 100000,
                'dtt_added' => $now,
                'Edate' => $now->toDateString(),
                'Etime' => $now->format('H:i:s'),
                'id_serial' => (int) $request->input('serial'),
                'COMPANY' => $this->nullableInput($request->input('company')),
                'BranchName' => $this->nullableInput($request->input('branch_name')),
                'BranchAddress' => $this->nullableInput($request->input('branch_address')),
                'HOAddress' => $this->nullableInput($request->input('ho_address')),
                'BranchTel' => $this->nullableInput($request->input('branch_tel')),
                'HOTel' => $this->nullableInput($request->input('ho_tel')),
                'tx_mobile' => $this->nullableInput($request->input('mobile')),
                'tx_email' => $this->nullableInput($request->input('email')),
                'tx_url' => $this->nullableInput($request->input('website')),
                'tx_fax' => $this->nullableInput($request->input('fax')),
                'CEO' => $this->nullableInput($request->input('ceo')),
                'VATREGISTRATION' => $this->nullableInput($request->input('vat_registration')),
                'Shopid' => $this->nullableInput($request->input('shop_id')),
                'Logo_Path' => $this->storeImage(
                    $request,
                    $nextId,
                    null,
                    'logo',
                    'remove_logo',
                    'affiliated-club-logo',
                ),
                'image_path' => $this->storeImage(
                    $request,
                    $nextId,
                    null,
                    'image',
                    'remove_image',
                    'affiliated-club-featured',
                ),
            ]);
        });

        PortalCache::clearAffiliatedClubCaches();

        return redirect()
            ->route('admin.affiliated-clubs.index')
            ->with('status', 'Affiliated club created successfully.');
    }

    public function edit(int $club)
    {
        return view('admin.affiliated-clubs.form', [
            'club' => $this->findClub($club),
            'isEditing' => true,
        ]);
    }

    public function update(AffiliatedClubRequest $request, int $club)
    {
        $clubRow = $this->findClub($club);

        $clubRow->fill([
            'id_affiliated_club_ver' => max(1, (int) $clubRow->id_affiliated_club_ver) + 1,
            'is_active' => $request->boolean('is_active'),
            'dtt_mod' => now(),
            'id_user_mod' => $this->resolveAuditUserId(),
            'id_serial' => (int) $request->input('serial'),
            'COMPANY' => $this->nullableInput($request->input('company')),
            'BranchName' => $this->nullableInput($request->input('branch_name')),
            'BranchAddress' => $this->nullableInput($request->input('branch_address')),
            'HOAddress' => $this->nullableInput($request->input('ho_address')),
            'BranchTel' => $this->nullableInput($request->input('branch_tel')),
            'HOTel' => $this->nullableInput($request->input('ho_tel')),
            'tx_mobile' => $this->nullableInput($request->input('mobile')),
            'tx_email' => $this->nullableInput($request->input('email')),
            'tx_url' => $this->nullableInput($request->input('website')),
            'tx_fax' => $this->nullableInput($request->input('fax')),
            'CEO' => $this->nullableInput($request->input('ceo')),
            'VATREGISTRATION' => $this->nullableInput($request->input('vat_registration')),
            'Shopid' => $this->nullableInput($request->input('shop_id')),
            'Logo_Path' => $this->storeImage(
                $request,
                (int) $clubRow->id_affiliated_club_key,
                $clubRow->getAttribute('Logo_Path'),
                'logo',
                'remove_logo',
                'affiliated-club-logo',
            ),
            'image_path' => $this->storeImage(
                $request,
                (int) $clubRow->id_affiliated_club_key,
                $clubRow->getAttribute('image_path'),
                'image',
                'remove_image',
                'affiliated-club-featured',
            ),
        ])->save();

        PortalCache::clearAffiliatedClubCaches();

        return redirect()
            ->route('admin.affiliated-clubs.index')
            ->with('status', 'Affiliated club updated successfully.');
    }

    public function destroy(int $club)
    {
        $clubRow = $this->findClub($club);

        $this->deleteManagedImage($clubRow->getAttribute('Logo_Path'));
        $this->deleteManagedImage($clubRow->getAttribute('image_path'));
        $clubRow->delete();

        PortalCache::clearAffiliatedClubCaches();

        return redirect()
            ->route('admin.affiliated-clubs.index')
            ->with('status', 'Affiliated club deleted successfully.');
    }

    private function findClub(int $clubId): AffiliatedClub
    {
        return AffiliatedClub::query()->findOrFail($clubId);
    }

    private function resolveAuditUserId(): int
    {
        $identifier = Auth::guard('admin')->user()?->userid;

        return is_numeric($identifier) ? (int) $identifier : 0;
    }

    private function nullableInput(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function storeImage(
        Request $request,
        int $clubId,
        ?string $currentPath = null,
        string $fileKey = 'image',
        string $removeKey = 'remove_image',
        string $filenamePrefix = 'affiliated-club',
    ): ?string
    {
        if ($request->boolean($removeKey) && ! $request->hasFile($fileKey)) {
            $this->deleteManagedImage($currentPath);

            return null;
        }

        if (! $request->hasFile($fileKey)) {
            return $currentPath;
        }

        $image = $request->file($fileKey);

        if ($image === null || ! $image->isValid()) {
            return $currentPath;
        }

        $directory = public_path('affiliated_clubs');
        File::ensureDirectoryExists($directory);

        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $filename = sprintf('%s-%d-%s.%s', $filenamePrefix, $clubId, Str::lower(Str::random(12)), $extension);

        $image->move($directory, $filename);

        $this->deleteManagedImage($currentPath);

        return 'affiliated_clubs/' . $filename;
    }

    private function deleteManagedImage(?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '' || $path === '?') {
            return;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $urlPath = parse_url($path, PHP_URL_PATH);
            $path = is_string($urlPath) ? ltrim($urlPath, '/') : '';
        } else {
            $path = ltrim($path, '/');
        }

        if ($path === '' || ! str_starts_with($path, 'affiliated_clubs/')) {
            return;
        }

        $fullPath = public_path($path);

        if (is_file($fullPath)) {
            File::delete($fullPath);
        }
    }
}
