<?php

use Livewire\Volt\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Support\Auditable;
use App\Models\User;
use App\Actions\Users\ToggleUserStatus;
use Livewire\WithPagination;
use App\Actions\Users\CreateUser;
use App\Actions\Users\UpdateUser;
use App\Actions\Users\DeleteUser;
use App\Actions\Users\ChangeUserRole;
use App\Notifications\UserActionNotification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

new class extends Component {
    use Auditable;
    use AuthorizesRequests;
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    /* =====================
       Lifecycle
    ===================== */

    public function mount(): void
    {
        // Authorization via Policy (super-admin bypass handled globally)
        $this->authorize('viewAny', User::class);
    }

    /* =====================
       State
    ===================== */

    public string $search = '';
    public bool $showCreateModal = false;
    public string $searchInput = '';


    public string $name = '';
    public string $email = '';
    public string $role = 'admin';
    public ?string $note = null;

    public bool $showEditModal = false;
    public ?User $editingUser = null;

    public string $editName = '';
    public string $editEmail = '';
    public ?string $editNote = null;
    public string $editRole = 'admin';

    /* =====================
       Computed
    ===================== */

    public function users()
    {
        return User::query()
            ->with('roles')
            ->when($this->search, function ($query) {
                $query->where(
                    fn($q) =>
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                );
            })
            ->latest()
            ->paginate(10);
    }

    public function applySearch(): void
    {
        $this->search = $this->searchInput;
        $this->resetPage(); // مهم مع pagination
    }


    public function editUser(User $user): void
    {
        $this->authorize('update', $user);

        $this->editingUser = $user;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editNote = $user->note;
        $this->editRole = $user->roles->first()?->name ?? 'admin';

        $this->showEditModal = true;

    }

    public function stats(): array
    {
        return [
            'total' => User::count(),
            'admins' => User::role('admin')->count(),
            'superAdmins' => User::role('super-admin')->count(),
        ];
    }

    /* =====================
       Actions
    ===================== */

    public function createUser(): void
    {
        $this->authorize('create', User::class);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:admin,super-admin'],
        ]);

        $user = app(CreateUser::class)->execute([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'note' => $this->note,
        ]);

        // 🔐 Send reset password link
        Password::sendResetLink([
            'email' => $user->email,
        ]);

        // 🔹 Audit Log
        $this->audit('user.created', $user, [
            'role' => $this->role,
        ]);

        // 🔹 Push Notification
        auth()->user()->notify(
            new UserActionNotification(
                'user.created',
                [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]
            )
        );

        $this->resetForm();
        $this->resetPage();

        $this->js("
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: '" . __('User created and password reset link sent successfully') . "'
                    }
                })
            );
        ");
    }

    public function updateUser(): void
    {
        $this->authorize('update', $this->editingUser);

        $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editEmail' => [
                'required',
                'email',
                'unique:users,email,' . $this->editingUser->id,
            ],
            'editRole' => ['required', 'in:admin,super-admin'],
        ]);

        app(UpdateUser::class)->execute($this->editingUser, [
            'name'  => $this->editName,
            'email' => $this->editEmail,
            'note'  => $this->editNote,
        ]);

        if ($this->editingUser->roles->first()?->name !== $this->editRole) {
            app(ChangeUserRole::class)->execute(
                $this->editingUser,
                $this->editRole
            );
        }

        // 🔹 Audit Log
        $this->audit('user.updated', $this->editingUser, [
            'fields' => ['name', 'email', 'note', 'role'],
        ]);

        // 🔹 Push Notification
        auth()->user()->notify(
            new UserActionNotification(
                'user.updated',
                [
                    'user_id' => $this->editingUser->id,
                    'email' => $this->editingUser->email,
                ]
            )
        );

        $this->resetEditForm();
        $this->resetPage();

        $this->js("
        window.dispatchEvent(
            new CustomEvent('toast', {
                detail: {
                    type: 'success',
                    message: '" . __('User updated successfully') . "'
                }
            })
        );
    ");
    }

    public function deleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->authorize('delete', $user);

        app(DeleteUser::class)->execute($user);

        // 🔹 Audit Log
        $this->audit('user.deleted', $user, [
            'email' => $user->email,
        ]);

        // 🔔 Notification
        auth()->user()->notify(
            new UserActionNotification(
                'user.deleted',
                [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]
            )
        );

        $this->resetPage();

        $this->js("
        window.dispatchEvent(
            new CustomEvent('toast', {
                detail: {
                    type: 'success',
                    message: '" . __('User deleted successfully') . "'
                }
            })
        );
    ");
    }

    public function toggleUserStatus(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->authorize('toggleActive', $user);

        app(ToggleUserStatus::class)->execute($user);

        $this->audit('user.toggled', $user, [
            'is_active' => $user->is_active,
        ]);

        // 🔹 Push Notification
        auth()->user()->notify(
            new UserActionNotification(
                'user.status_changed',
                [
                    'user_id' => $user->id,
                    'is_active' => $user->is_active,
                ]
            )
        );

        $this->js("
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: '" . ($user->is_active
            ? __('The user has been activated successfully')
            : __('The user has been successfully disabled')) . "'
                    }
                })
            );
        ");
    }

    public function canActOn(User $user): bool
    {
        return auth()->user()->id !== $user->id;
    }

    public function cannotActReason(User $user): ?string
    {
        if (auth()->user()->id === $user->id) {
            return __('You cannot perform this action on your own account');
        }

        return null;
    }


    public function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'role',
            'note',
            'showCreateModal',
        ]);
    }

    public function resetEditForm(): void
    {
        $this->reset([
            'showEditModal',
            'editingUser',
            'editName',
            'editEmail',
            'editNote',
            'editRole',
        ]);
    }

};
?>


<!-- =====================
     UI
===================== -->

<div class="space-y-6">

    <!-- Dashboard Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg p-4 shadow">
            <p class="text-sm text-gray-500">{{ __('Total users') }}</p>
            <p class="text-2xl font-bold">{{ $this->stats()['total'] }}</p>
        </div>

        <div class="bg-white rounded-lg p-4 shadow">
            <p class="text-sm text-gray-500">{{ __('Admins') }}</p>
            <p class="text-2xl font-bold">{{ $this->stats()['admins'] }}</p>
        </div>

        <div class="bg-white rounded-lg p-4 shadow">
            <p class="text-sm text-gray-500">{{ __('Super Admins') }}</p>
            <p class="text-2xl font-bold">{{ $this->stats()['superAdmins'] }}</p>
        </div>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center">
        <div class="flex gap-2">
            <input type="text" wire:model.defer="searchInput" placeholder="{{ __('Search user...') }}"
                class="border rounded px-3 py-2 w-64" />

            <button wire:click="applySearch" class="bg-gray-800 text-white px-4 py-2 rounded">
                {{ __('Search') }}
            </button>
        </div>


        @can('create', App\Models\User::class)
            <button wire:click="$set('showCreateModal', true)" class="bg-black text-white px-4 py-2 rounded">
                + {{ __('Create user') }}
            </button>
        @endcan
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">{{ __('Name') }}</th>
                    <th class="p-3 text-left">{{ __('Email') }}</th>
                    <th class="p-3 text-left">{{ __('Role') }}</th>
                    <th class="p-3 text-left">{{ __('Status') }}</th>
                    <th class="p-3 text-left">{{ __('Note') }}</th>
                    <th class="p-3 text-left">{{ __('Actions') }}</th>
                    <th class="p-3 text-left">{{ __('Created') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->users() as $user)
                    <tr class="border-t">
                        <td class="p-3">{{ $user->name }}</td>
                        <td class="p-3">{{ $user->email }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs rounded bg-gray-200">
                                {{ $user->roles->first()?->name }}
                            </span>
                        </td>
                        <td class="p-3">
                            @if ($user->is_active)
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                    {{ __('Active') }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                    {{ __('Disabled') }}
                                </span>
                            @endif
                        </td>

                        <td class="p-3">
                            <span class="px-2 py-1 text-xs rounded bg-gray-200">
                                @if ($user->note)
                                    {{ $user->note }}
                                @else
                                    N/N
                                @endif
                            </span>
                        </td>
                        <td class="p-3 space-x-1">
                            <button
                                wire:click="toggleUserStatus({{ $user->id }})"
                                @disabled(! $this->canActOn($user))
                                class="text-xs px-3 py-1 rounded text-white
                                    {{ $user->is_active ? 'bg-red-600' : 'bg-green-600' }}
                                    disabled:opacity-40 disabled:cursor-not-allowed"
                                title="{{ $this->cannotActReason($user) }}"
                            >
                                {{ $user->is_active ? __('Disable') : __('Enable') }}
                            </button>
                            <button
                                wire:click="editUser({{ $user->id }})"
                                @disabled(! $this->canActOn($user))
                                class="text-xs px-3 py-1 rounded bg-blue-600 text-white
                                    disabled:opacity-40 disabled:cursor-not-allowed"
                                title="{{ $this->cannotActReason($user) }}"
                            >
                                {{ __('Edit') }}
                            </button>
                            <button
                                wire:click="deleteUser({{ $user->id }})"
                                wire:confirm="{{ __('Are you sure you want to delete this user?') }}"
                                @disabled(! $this->canActOn($user))
                                class="text-xs px-3 py-1 rounded bg-red-600 text-white
                                    disabled:opacity-40 disabled:cursor-not-allowed"
                                title="{{ $this->cannotActReason($user) }}"
                            >
                                {{ __('Delete') }}
                            </button>
                        </td>
                        <td class="p-3">
                            {{ $user->created_at->format('Y-m-d') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">
            {{ $this->users()->links() }}
        </div>

    </div>

    <!-- Create User Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div class="bg-white w-96 p-6 rounded-lg space-y-3">
                <h2 class="font-bold text-lg">{{ __('Create User') }}</h2>

                <input wire:model="name" placeholder="Name" class="border w-full p-2 rounded">
                <input wire:model="email" placeholder="Email" class="border w-full p-2 rounded">
                <textarea wire:model="note" placeholder="Note (private)" class="border w-full p-2 rounded"></textarea>

                <select wire:model="role" class="border w-full p-2 rounded">
                    <option value="admin">{{ __('Admin') }}</option>
                    <option value="super-admin">{{ __('Super Admin') }}</option>
                </select>

                <div class="flex justify-end gap-2">
                    <button wire:click="resetForm">{{ __('Cancel') }}</button>
                    <button wire:click="createUser" class="bg-black text-white px-4 py-2 rounded">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showEditModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div class="bg-white w-96 p-6 rounded-lg space-y-3">
                <h2 class="font-bold text-lg">{{ __('Edit User') }}</h2>

                <input wire:model="editName" class="border w-full p-2 rounded" placeholder="{{ __('Name') }}">
                <input wire:model="editEmail" class="border w-full p-2 rounded" placeholder="{{ __('Email') }}">
                <textarea wire:model="editNote" class="border w-full p-2 rounded" placeholder="{{ __('Note') }}"></textarea>

                <select wire:model="editRole" class="border w-full p-2 rounded">
                    <option value="admin">{{ __('Admin') }}</option>
                    <option value="super-admin">{{ __('Super Admin') }}</option>
                </select>

                <div class="flex justify-end gap-2">
                    <button wire:click="resetEditForm">{{ __('Cancel') }}</button>
                    <button wire:click="updateUser" class="bg-black text-white px-4 py-2 rounded">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>