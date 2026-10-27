<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\AssetCategoryWebController;
use App\Http\Controllers\Web\AssetWebController;
use App\Http\Controllers\Web\CompanyProfileWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LocationWebController;
use App\Http\Controllers\Web\OperationWebController;
use App\Http\Controllers\Web\ReportWebController;
use App\Http\Controllers\Web\UserWebController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Redirect root & shortcut
Route::redirect('/', '/app/dashboard');
Route::redirect('/dashboard', '/app/dashboard');

// Protected Application Routes
Route::prefix('app')->name('web.')->middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Aset (Katalog, Registrasi, QR Code, Scan)
    Route::get('/assets/scan', [AssetWebController::class, 'scan'])->name('assets.scan');
    Route::get('/assets/import/template', [AssetWebController::class, 'downloadTemplate'])->name('assets.template');
    Route::post('/assets/import', [AssetWebController::class, 'import'])->name('assets.import');
    Route::get('/assets', [AssetWebController::class, 'index'])->name('assets.index');
    Route::get('/assets/create', [AssetWebController::class, 'create'])->name('assets.create');
    Route::post('/assets', [AssetWebController::class, 'store'])->name('assets.store');
    Route::get('/assets/{asset}', [AssetWebController::class, 'show'])->name('assets.show');

    // Operasional: Penugasan (Check-out & Check-in)
    Route::get('/assignments', [OperationWebController::class, 'assignmentsIndex'])->name('assignments.index');
    Route::post('/assignments', [OperationWebController::class, 'assignmentsStore'])->name('assignments.store');
    Route::post('/assignments/{assignment}/checkin', [OperationWebController::class, 'assignmentsCheckin'])->name('assignments.checkin');

    // Operasional: Mutasi Antar Lokasi
    Route::get('/mutations', [OperationWebController::class, 'mutationsIndex'])->name('mutations.index');
    Route::post('/mutations', [OperationWebController::class, 'mutationsStore'])->name('mutations.store');

    // Operasional: Servis & Maintenance
    Route::get('/maintenances', [OperationWebController::class, 'maintenancesIndex'])->name('maintenances.index');
    Route::post('/maintenances', [OperationWebController::class, 'maintenancesStore'])->name('maintenances.store');
    Route::post('/maintenances/{maintenance}/complete', [OperationWebController::class, 'maintenancesComplete'])->name('maintenances.complete');

    // Operasional: Disposal (Sale & Scrap)
    Route::get('/disposals', [OperationWebController::class, 'disposalsIndex'])->name('disposals.index');
    Route::post('/disposals', [OperationWebController::class, 'disposalsStore'])->name('disposals.store');

    // Laporan & Audit
    Route::get('/reports', [ReportWebController::class, 'index'])->name('reports.index');

    // Master Data & RBAC
    // Manajemen Lokasi Cabang
    Route::get('/locations', [LocationWebController::class, 'index'])->name('locations.index');
    Route::post('/locations', [LocationWebController::class, 'store'])->name('locations.store');
    Route::put('/locations/{location}', [LocationWebController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{location}', [LocationWebController::class, 'destroy'])->name('locations.destroy');

    // Manajemen Kategori Aset
    Route::get('/categories', [AssetCategoryWebController::class, 'index'])->name('categories.index');
    Route::get('/categories/import/template', [AssetCategoryWebController::class, 'downloadTemplate'])->name('categories.template');
    Route::post('/categories/import', [AssetCategoryWebController::class, 'import'])->name('categories.import');
    Route::post('/categories', [AssetCategoryWebController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AssetCategoryWebController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AssetCategoryWebController::class, 'destroy'])->name('categories.destroy');

    // Manajemen User & Role (RBAC)
    Route::get('/users', [UserWebController::class, 'index'])->name('users.index');
    Route::post('/users', [UserWebController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserWebController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserWebController::class, 'destroy'])->name('users.destroy');

    // Profil Perusahaan (Identitas Korporasi, Logo & Kantor)
    Route::get('/company-profile', [CompanyProfileWebController::class, 'edit'])->name('company.edit');
    Route::put('/company-profile', [CompanyProfileWebController::class, 'update'])->name('company.update');
});
