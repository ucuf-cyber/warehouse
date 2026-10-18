<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produk\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    public function data(): JsonResponse
    {
        $suppliers = Supplier::withCount('products')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $suppliers,
            'message' => '',
        ]);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $supplier,
            'message' => '',
        ]);
    }

    public function store(SupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $supplier,
            'message' => 'Supplier berhasil ditambahkan.',
        ], 201);
    }

    public function update(SupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $supplier,
            'message' => 'Supplier berhasil diperbarui.',
        ]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        if ($supplier->products()->exists()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Supplier tidak dapat dihapus karena masih memiliki produk.',
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Supplier berhasil dihapus.',
        ]);
    }
}
