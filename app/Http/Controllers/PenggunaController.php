<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function index()
    {
        Gate::authorize('super-admin-only');
        $pengguna = User::latest()->paginate(10);
        return view('pengguna.index', compact('pengguna'));
    }

    public function create()
    {
        Gate::authorize('super-admin-only');
        return view('pengguna.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('super-admin-only');
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user,super_admin',   // ← tambah super_admin
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Pengguna berhasil ditambahkan!');
    }

    public function show(User $pengguna)
    {
        Gate::authorize('super-admin-only');
        return view('pengguna.show', compact('pengguna'));
    }

    public function edit(User $pengguna)
    {
        Gate::authorize('super-admin-only');
        return view('pengguna.edit', compact('pengguna'));
    }

    public function update(Request $request, User $pengguna)
    {
        Gate::authorize('super-admin-only');
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($pengguna->id),
            ],
            'role' => 'required|in:admin,user,super_admin',   // ← tambah super_admin
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pengguna->update($data);

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Pengguna berhasil diupdate!');
    }

    public function destroy(User $pengguna)
    {
        Gate::authorize('super-admin-only');

        // Cegah menghapus diri sendiri
        if ($pengguna->id === auth()->id()) {
            return redirect()
                ->route('pengguna.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri!');
        }

        $pengguna->delete();

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus!');
    }
    public function toggleActive(User $pengguna)
    {
        Gate::authorize('super-admin-only');

        if ($pengguna->id === auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menonaktifkan akun sendiri.'
                ], 403);
            }
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $pengguna->is_active = !$pengguna->is_active;
        $pengguna->save();

        $status = $pengguna->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $message = "Pengguna {$pengguna->name} berhasil {$status}.";

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return back()->with('success', $message);
    }
}