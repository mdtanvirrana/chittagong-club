<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserRequest;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q'));

        $admins = AdminUser::query()
            ->where('is_admin', 1)
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereRaw('LTRIM(RTRIM(PrvcusID)) LIKE ?', ['%' . $search . '%']);
            })
            ->orderBy('PrvcusID')
            ->paginate(15)
            ->withQueryString();

        return view('admin.admin-users.index', compact('admins', 'search'));
    }

    public function create()
    {
        return view('admin.admin-users.form', [
            'admin' => new AdminUser(['is_admin' => true]),
            'isEditing' => false,
        ]);
    }

    public function store(AdminUserRequest $request)
    {
        $userId = $this->cleanUserId($request->input('user_id'));
        $this->ensureUserIdIsAvailable($userId);

        $timestamp = now();

        AdminUser::query()->create([
            'PrvcusID' => $userId,
            'Password' => AdminUser::hashPassword((string) $request->input('password')),
            'is_admin' => $request->boolean('is_admin'),
            'LastUpdateDate' => $timestamp,
            'LastUpdateTime' => $timestamp->format('H:i:s'),
        ]);

        return redirect()
            ->route('admin.admin-users.index')
            ->with('status', 'Admin account created successfully.');
    }

    public function edit(string $adminUser)
    {
        return view('admin.admin-users.form', [
            'admin' => $this->findAdminUser($adminUser),
            'isEditing' => true,
        ]);
    }

    public function update(AdminUserRequest $request, string $adminUser)
    {
        $admin = $this->findAdminUser($adminUser);
        $originalUserId = $admin->userid;
        $newUserId = $this->cleanUserId($request->input('user_id'));
        $isAdmin = $request->boolean('is_admin');

        $this->ensureUserIdIsAvailable($newUserId, $originalUserId);

        if (! $isAdmin) {
            $this->ensureCanRemoveAdminAccess($admin);
        }

        $timestamp = now();

        $admin->PrvcusID = $newUserId;
        $admin->is_admin = $isAdmin;
        $admin->LastUpdateDate = $timestamp;
        $admin->LastUpdateTime = $timestamp->format('H:i:s');

        if (filled($request->input('password'))) {
            $admin->Password = AdminUser::hashPassword((string) $request->input('password'));
        }

        $admin->save();

        return redirect()
            ->route('admin.admin-users.index')
            ->with('status', 'Admin account updated successfully.');
    }

    public function destroy(string $adminUser)
    {
        $admin = $this->findAdminUser($adminUser);

        $this->ensureCanRemoveAdminAccess($admin);

        $admin->delete();

        return redirect()
            ->route('admin.admin-users.index')
            ->with('status', 'Admin account deleted successfully.');
    }

    private function findAdminUser(string $adminUser): AdminUser
    {
        return AdminUser::query()
            ->where('PrvcusID', $adminUser)
            ->firstOrFail();
    }

    private function ensureUserIdIsAvailable(string $userId, ?string $currentUserId = null): void
    {
        $exists = AdminUser::query()
            ->where('PrvcusID', $userId)
            ->when($currentUserId !== null, fn ($query) => $query->where('PrvcusID', '!=', $currentUserId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'user_id' => 'This user ID is already used.',
            ]);
        }
    }

    private function ensureCanRemoveAdminAccess(AdminUser $admin): void
    {
        $currentAdminId = Auth::guard('admin')->user()?->userid;

        if ($admin->userid === $currentAdminId) {
            throw ValidationException::withMessages([
                'is_admin' => 'You cannot remove your own admin access.',
            ]);
        }

        $anotherAdminExists = AdminUser::query()
            ->where('is_admin', 1)
            ->where('PrvcusID', '!=', $admin->userid)
            ->exists();

        if (! $anotherAdminExists) {
            throw ValidationException::withMessages([
                'is_admin' => 'At least one active admin account is required.',
            ]);
        }
    }

    private function cleanUserId(mixed $value): string
    {
        return trim((string) $value);
    }
}
