<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analyticsService) {}

    public function stockOut(): JsonResponse
    {
        $data = $this->analyticsService->stockOutPrediction()
            ->take(30)
            ->map(fn ($row) => [
                'kode_barang' => $row['product']->kode_barang,
                'name' => $row['product']->name,
                'current_stock' => $row['product']->current_stock,
                'avg_daily_qty' => $row['avg_daily_qty'],
                'days_left' => is_infinite($row['days_left']) ? null : $row['days_left'],
            ]);

        return response()->json(['success' => true, 'data' => $data, 'message' => '']);
    }

    public function restock(): JsonResponse
    {
        $data = $this->analyticsService->restockRecommendations()
            ->map(fn ($row) => [
                'kode_barang' => $row['product']->kode_barang,
                'name' => $row['product']->name,
                'current_stock' => $row['product']->current_stock,
                'days_left' => $row['days_left'],
                'lead_time_days' => $row['lead_time_days'],
                'suggested_qty' => $row['suggested_qty'],
                'supplier' => $row['product']->supplier?->name,
            ]);

        return response()->json(['success' => true, 'data' => $data, 'message' => '']);
    }

    public function slowMoving(Request $request): JsonResponse
    {
        $days = $request->integer('days') ?: 30;

        $data = $this->analyticsService->slowMovingProducts($days)
            ->map(fn ($product) => [
                'kode_barang' => $product->kode_barang,
                'name' => $product->name,
                'current_stock' => $product->current_stock,
                'category' => $product->category?->name,
            ]);

        return response()->json(['success' => true, 'data' => $data, 'message' => '']);
    }
}
