<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactRequest;
use App\Models\ClubContact;
use App\Support\ContactDirectory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $search = ContactDirectory::clean($request->input('q'));
        $department = ContactDirectory::clean($request->input('department'));
        $subDepartment = ContactDirectory::clean($request->input('sub_department'));
        $hierarchy = ContactDirectory::departmentHierarchy();
        $departments = array_keys($hierarchy);
        $availableSubDepartments = ContactDirectory::subDepartmentOptions($department);

        $contacts = ClubContact::query()
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';

                $query->where(function ($builder) use ($like) {
                    $builder
                        ->whereRaw('LTRIM(RTRIM(Contact_Dept)) LIKE ?', [$like])
                        ->orWhereRaw('LTRIM(RTRIM(Contact_Sub_Dept)) LIKE ?', [$like])
                        ->orWhereRaw('LTRIM(RTRIM(Phone)) LIKE ?', [$like])
                        ->orWhereRaw('LTRIM(RTRIM(Email)) LIKE ?', [$like]);
                });
            })
            ->when($department !== '', fn ($query) => $query->whereRaw('LTRIM(RTRIM(Contact_Dept)) = ?', [$department]))
            ->when($subDepartment !== '', fn ($query) => $query->whereRaw('LTRIM(RTRIM(Contact_Sub_Dept)) = ?', [$subDepartment]))
            ->orderBy('Sl')
            ->paginate(15)
            ->withQueryString();

        return view('admin.contacts.index', compact(
            'contacts',
            'search',
            'department',
            'subDepartment',
            'departments',
            'availableSubDepartments',
            'hierarchy'
        ));
    }

    public function create()
    {
        return view('admin.contacts.form', $this->formViewData(new ClubContact(), false));
    }

    public function store(ContactRequest $request)
    {
        DB::transaction(function () use ($request): void {
            $contacts = ClubContact::query()
                ->lockForUpdate()
                ->get(['Sl', 'Contact_ID', 'Contact_Dept']);

            $department = ContactDirectory::clean($request->input('department'));

            ClubContact::query()->create([
                'Sl' => ((int) $contacts->max(fn (ClubContact $contact): int => (int) $contact->Sl)) + 1,
                'Contact_ID' => (string) $this->resolveGroupId($contacts, $department),
                'Contact_Dept' => $department,
                'Contact_Sub_Dept' => $this->nullableInput($request->input('sub_department')),
                'Phone' => $this->nullableInput($request->input('phone')),
                'Email' => $this->nullableInput($request->input('email')),
            ]);
        });

        ContactDirectory::clearCaches();

        return redirect()
            ->route('admin.contacts.index')
            ->with('status', 'Contact created successfully.');
    }

    public function edit(int $contact)
    {
        return view('admin.contacts.form', $this->formViewData($this->findContact($contact), true));
    }

    public function update(ContactRequest $request, int $contact)
    {
        $contactRow = $this->findContact($contact);

        DB::transaction(function () use ($request, $contactRow): void {
            $contacts = ClubContact::query()
                ->lockForUpdate()
                ->get(['Sl', 'Contact_ID', 'Contact_Dept']);

            $department = ContactDirectory::clean($request->input('department'));

            $contactRow->fill([
                'Contact_ID' => (string) $this->resolveGroupId($contacts, $department, $contactRow),
                'Contact_Dept' => $department,
                'Contact_Sub_Dept' => $this->nullableInput($request->input('sub_department')),
                'Phone' => $this->nullableInput($request->input('phone')),
                'Email' => $this->nullableInput($request->input('email')),
            ])->save();
        });

        ContactDirectory::clearCaches();

        return redirect()
            ->route('admin.contacts.index')
            ->with('status', 'Contact updated successfully.');
    }

    public function destroy(int $contact)
    {
        $this->findContact($contact)->delete();

        ContactDirectory::clearCaches();

        return redirect()
            ->route('admin.contacts.index')
            ->with('status', 'Contact deleted successfully.');
    }

    private function findContact(int $contact): ClubContact
    {
        return ClubContact::query()->findOrFail($contact);
    }

    private function formViewData(ClubContact $contact, bool $isEditing): array
    {
        $hierarchy = ContactDirectory::departmentHierarchy();
        $selectedDepartment = old('department', $contact->department_name);

        return [
            'contact' => $contact,
            'isEditing' => $isEditing,
            'departments' => array_keys($hierarchy),
            'hierarchy' => $hierarchy,
            'selectedDepartment' => $selectedDepartment,
            'availableSubDepartments' => ContactDirectory::subDepartmentOptions($selectedDepartment),
        ];
    }

    private function resolveGroupId(Collection $contacts, string $department, ?ClubContact $currentContact = null): int
    {
        $existingGroupId = $contacts
            ->filter(function (ClubContact $contact) use ($department, $currentContact): bool {
                if ($currentContact !== null && (int) $contact->Sl === (int) $currentContact->Sl) {
                    return false;
                }

                return ContactDirectory::clean($contact->Contact_Dept) === $department && $contact->group_id !== null;
            })
            ->map(fn (ClubContact $contact): ?int => $contact->group_id)
            ->filter()
            ->sort()
            ->first();

        if ($existingGroupId !== null) {
            return $existingGroupId;
        }

        if (
            $currentContact !== null
            && ContactDirectory::clean($currentContact->Contact_Dept) === $department
            && $currentContact->group_id !== null
        ) {
            return $currentContact->group_id;
        }

        return ((int) $contacts
            ->map(fn (ClubContact $contact): ?int => $contact->group_id)
            ->filter()
            ->max()) + 1;
    }

    private function nullableInput(mixed $value): ?string
    {
        $value = ContactDirectory::clean($value);

        return $value !== '' ? $value : null;
    }
}
