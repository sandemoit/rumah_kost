<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManajemenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->input('search');

        $users = User::when($keyword, function ($query, $keyword) {
            return $query->where(function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%$keyword%")
                    ->orWhere('email', 'LIKE', "%$keyword%");
            });
        })->paginate(10);

        $data = [
            'users' => $users,
            'pageTitle' => 'Manajemen User',
        ];

        return view('admin.data-master.user.manajemen', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'pageTitle' => 'Tambah User',
        ];
        return view('admin.data-master.user.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
            'role' => 'required|string|in:admin,karyawan',
        ]);

        // $imageFile = $request->file('image');
        // $imageName = time() . '.' . $request->image->extension();
        // $path = $imageFile->storeAs('profile', $imageName, 'public');

        try {
            // Buat user baru
            $userData = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'],
            ];

            User::create($userData);

            // Redirect dengan pesan sukses
            return redirect()->route('usermanajemen')->with('success', 'User berhasil dibuat.');
        } catch (\Exception $e) {
            // Tangani kesalahan
            return redirect()->back()->with(['error' => 'Terjadi kesalahan saat membuat user: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user, $id)
    {
        $data = [
            'user' => User::findOrFail($id),
            'pageTitle' => 'Edit User',
        ];
        return view('admin.data-master.user.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|in:admin,karyawan',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->role = $request->role;

        // Cek apakah password diubah
        if (!empty($request->new_password)) {
            $user->password = Hash::make($request->new_password);
        }

        try {
            $user->save();
            return redirect()->route('usermanajemen')->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('failed', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, $id)
    {
        try {
            User::where('id', $id)->delete();
            return response()->json(['success' => 'User berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['failed' => 'Terjadi kesalahan saat menghapus user: ' . $e->getMessage()], 500);
        }
    }
}
