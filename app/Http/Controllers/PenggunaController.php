<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $pengguna = User::latest()->paginate(10);
        return view('pengguna.index', compact('pengguna'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('pengguna.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Pengguna berhasil ditambahkan!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $pengguna)
    {
        return view('pengguna.show', compact('pengguna'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $pengguna)
    {
        return view('pengguna.edit', compact('pengguna'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $pengguna)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($pengguna->id),
            ],
            'role' => 'required|in:admin,user',
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

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $pengguna)
    {
        // Cegah menghapus diri sendiri
        if ($pengguna->id == auth()->id()) {  // ✅ Akses sebagai properti (tanpa kurung)
            return redirect()
                ->route('pengguna.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri!');
        }

        $pengguna->delete();

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus!');
    }
}
