<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produk\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    public function data(): JsonResponse
    {
        $brands = Brand::withCount('products')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $brands,
            'message' => '',
        ]);
    }

    public function show(Brand $brand): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $brand,
            'message' => '',
        ]);
    }

    public function store(BrandRequest $request): JsonResponse
    {
        $brand = Brand::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $brand,
            'message' => 'Brand berhasil ditambahkan.',
        ], 201);
    }

    public function update(BrandRequest $request, Brand $brand): JsonResponse
    {
        $brand->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $brand,
            'message' => 'Brand berhasil diperbarui.',
        ]);
    }

    public function destroy(Brand $brand): JsonResponse
    {
        if ($brand->products()->exists()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Brand tidak dapat dihapus karena masih memiliki produk.',
            ], 422);
        }

        $brand->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Brand berhasil dihapus.',
        ]);
    }
}
