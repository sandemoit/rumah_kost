<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'pageTitle' => 'Kategori',
        ];
        $data['pengeluaran'] = Category::where('type', 'pengeluaran')->get();
        $data['pemasukan'] = Category::where('type', 'pemasukan')->get();
        return view('admin.pengaturan.cashcategory.category', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_pemasukan' => 'nullable|string|max:255',
            'category_pengeluaran' => 'nullable|string|max:255',
        ]);

        if ($request->filled('category_pemasukan')) {
            Category::create([
                'category_name' => $request->category_pemasukan,
                'type' => 'pemasukan'
            ]);
        }

        if ($request->filled('category_pengeluaran')) {
            Category::create([
                'category_name' => $request->category_pengeluaran,
                'type' => 'pengeluaran'
            ]);
        }

        return redirect()->back()->with('success', 'Category berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validasi data
        $validatedData = $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        // Temukan kategori berdasarkan ID
        $category = Category::findOrFail($id);

        // Update kategori dengan data yang divalidasi
        $category->category_name = $validatedData['category_name'];

        try {
            // Simpan perubahan
            $category->save();
            // Return response success
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            // Return response error
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui kategori: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category, $id)
    {
        try {
            Category::where('id', $id)->delete();
            return response()->json(['success' => 'User berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['failed' => 'Terjadi kesalahan saat menghapus kategori: ' . $e->getMessage()], 500);
        }
    }
}
