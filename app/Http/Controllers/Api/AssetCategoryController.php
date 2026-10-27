<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCategory\StoreCategoryRequest;
use App\Http\Requests\AssetCategory\UpdateCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Http\JsonResponse;

class AssetCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = AssetCategory::query()->withCount(['assets', 'softwareLicenses'])->paginate(15);

        return response()->json($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = AssetCategory::create($request->validated());

        return response()->json([
            'message' => 'Asset category created successfully',
            'data' => $category,
        ], 201);
    }

    public function show(AssetCategory $assetCategory): JsonResponse
    {
        $assetCategory->loadCount(['assets', 'softwareLicenses']);

        return response()->json([
            'data' => $assetCategory,
        ]);
    }

    public function update(UpdateCategoryRequest $request, AssetCategory $assetCategory): JsonResponse
    {
        $assetCategory->update($request->validated());

        return response()->json([
            'message' => 'Asset category updated successfully',
            'data' => $assetCategory,
        ]);
    }

    public function destroy(AssetCategory $assetCategory): JsonResponse
    {
        if ($assetCategory->assets()->exists()) {
            return response()->json([
                'message' => 'Cannot delete asset category that still has linked assets',
            ], 422);
        }

        $assetCategory->delete();

        return response()->json([
            'message' => 'Asset category deleted successfully',
        ]);
    }
}
