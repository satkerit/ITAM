<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssignmentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->with('location')->withCount('assignments');

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->query('location_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest('id')->paginate(15)->withQueryString();
        $locations = Location::orderBy('name')->get();

        return view('users.index', [
            'users' => $users,
            'locations' => $locations,
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'location_id' => ['nullable', 'exists:locations,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'location_id' => $validated['location_id'] ?? null,
        ]);

        return redirect()->route('web.users.index')->with('success', 'Pengguna baru berhasil ditambahkan!');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'location_id' => ['nullable', 'exists:locations,id'],
        ]);

        if ($request->user()->is($user) && $user->role->value !== $validated['role']) {
            return redirect()->route('web.users.index')->with('error', 'Anda tidak dapat mengubah peran akun sendiri.');
        }

        if ($user->role === UserRole::Admin
            && $validated['role'] !== UserRole::Admin->value
            && $this->isLastAdmin($user)) {
            return redirect()->route('web.users.index')->with('error', 'Gagal memperbarui: minimal harus ada satu administrator aktif.');
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'location_id' => $validated['location_id'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()->route('web.users.index')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return redirect()->route('web.users.index')->with('error', 'Gagal menghapus: Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->role === UserRole::Admin && $this->isLastAdmin($user)) {
            return redirect()->route('web.users.index')->with('error', 'Gagal menghapus: minimal harus ada satu administrator aktif.');
        }

        if ($user->assignments()->where('status', AssignmentStatus::Active)->exists()) {
            return redirect()->route('web.users.index')->with('error', 'Gagal menghapus: Pengguna masih memiliki peminjaman unit aset aktif.');
        }

        $user->delete();

        return redirect()->route('web.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    private function isLastAdmin(User $user): bool
    {
        return User::query()
            ->where('role', UserRole::Admin)
            ->whereKeyNot($user->getKey())
            ->doesntExist();
    }
}
