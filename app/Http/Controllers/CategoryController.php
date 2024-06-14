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
        return view('admin.data-master.cashcategory.category', $data);
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
    public function update(Request $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
