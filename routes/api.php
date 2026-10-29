<?php

use App\Http\Controllers\Api\AssetAssignmentController;
use App\Http\Controllers\Api\AssetCategoryController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AssetDisposalController;
use App\Http\Controllers\Api\AssetMutationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MaintenanceLogController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('asset-categories', AssetCategoryController::class);

    Route::get('assets/scan', [AssetController::class, 'scan'])->name('assets.scan');
    Route::get('assets/{asset}/qr', [AssetController::class, 'qrImage'])->name('assets.qr');
    Route::apiResource('assets', AssetController::class);

    Route::get('assignments', [AssetAssignmentController::class, 'index'])->name('assignments.index');
    Route::post('assignments/assign', [AssetAssignmentController::class, 'assign'])->name('assignments.assign');
    Route::post('assignments/{assignment}/return', [AssetAssignmentController::class, 'return'])->name('assignments.return');

    Route::get('mutations', [AssetMutationController::class, 'index'])->name('mutations.index');
    Route::post('mutations', [AssetMutationController::class, 'store'])->name('mutations.store');

    Route::get('maintenances', [MaintenanceLogController::class, 'index'])->name('maintenances.index');
    Route::post('maintenances', [MaintenanceLogController::class, 'store'])->name('maintenances.store');
    Route::post('maintenances/{maintenance}/complete', [MaintenanceLogController::class, 'complete'])->name('maintenances.complete');

    Route::get('disposals', [AssetDisposalController::class, 'index'])->name('disposals.index');
    Route::post('disposals', [AssetDisposalController::class, 'store'])->name('disposals.store');

    Route::get('dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
    Route::get('reports/stock-by-location', [ReportController::class, 'stockByLocation'])->name('reports.stock');
    Route::get('reports/assets/{asset}/history', [ReportController::class, 'assetHistory'])->name('reports.history');
    Route::get('reports/disposals', [ReportController::class, 'disposals'])->name('reports.disposals');
});
