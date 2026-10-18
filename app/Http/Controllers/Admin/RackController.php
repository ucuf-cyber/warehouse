<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stok\RackRequest;
use App\Http\Requests\Stok\SubRackRequest;
use App\Models\Rack;
use App\Models\SubRack;
use Illuminate\Http\JsonResponse;

class RackController extends Controller
{
    public function data(): JsonResponse
    {
        $racks = Rack::with(['subRacks' => fn ($q) => $q->withCount('products')])->orderBy('code')->get();

        return response()->json(['success' => true, 'data' => $racks, 'message' => '']);
    }

    public function store(RackRequest $request): JsonResponse
    {
        $rack = Rack::create($request->validated());

        return response()->json(['success' => true, 'data' => $rack, 'message' => 'Rak berhasil ditambahkan.'], 201);
    }

    public function show(Rack $rak): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $rak, 'message' => '']);
    }

    public function update(RackRequest $request, Rack $rak): JsonResponse
    {
        $rak->update($request->validated());

        return response()->json(['success' => true, 'data' => $rak, 'message' => 'Rak berhasil diperbarui.']);
    }

    public function destroy(Rack $rak): JsonResponse
    {
        if ($rak->subRacks()->exists()) {
            return response()->json([
                'success' => false, 'data' => null,
                'message' => 'Rak tidak dapat dihapus karena masih memiliki sub rak.',
            ], 422);
        }

        $rak->delete();

        return response()->json(['success' => true, 'data' => null, 'message' => 'Rak berhasil dihapus.']);
    }

    public function subRackStore(SubRackRequest $request): JsonResponse
    {
        $subRack = SubRack::create($request->validated());

        return response()->json(['success' => true, 'data' => $subRack, 'message' => 'Sub rak berhasil ditambahkan.'], 201);
    }

    public function subRackShow(SubRack $subRak): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $subRak, 'message' => '']);
    }

    public function subRackUpdate(SubRackRequest $request, SubRack $subRak): JsonResponse
    {
        $subRak->update($request->validated());

        return response()->json(['success' => true, 'data' => $subRak, 'message' => 'Sub rak berhasil diperbarui.']);
    }

    public function subRackDestroy(SubRack $subRak): JsonResponse
    {
        if ($subRak->products()->exists()) {
            return response()->json([
                'success' => false, 'data' => null,
                'message' => 'Sub rak tidak dapat dihapus karena masih ditempati produk.',
            ], 422);
        }

        $subRak->delete();

        return response()->json(['success' => true, 'data' => null, 'message' => 'Sub rak berhasil dihapus.']);
    }
}
