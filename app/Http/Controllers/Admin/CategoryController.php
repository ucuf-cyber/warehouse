<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produk\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function data(): JsonResponse
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => '',
        ]);
    }

    public function show(Category $kategori): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $kategori,
            'message' => '',
        ]);
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Kategori berhasil ditambahkan.',
        ], 201);
    }

    public function update(CategoryRequest $request, Category $kategori): JsonResponse
    {
        $kategori->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $kategori,
            'message' => 'Kategori berhasil diperbarui.',
        ]);
    }

    public function destroy(Category $kategori): JsonResponse
    {
        if ($kategori->products()->exists()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki produk.',
            ], 422);
        }

        $kategori->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
