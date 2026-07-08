<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BiayaHarianController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Storage;


Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::middleware(['throttle:10,1'])->group(function () {
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Shared routes: Admin & SuperAdmin
    Route::middleware(['role:admin,superadmin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
        Route::get('/dashboard/recent-services', [DashboardController::class, 'recentServices'])->name('dashboard.recent-services');

        Route::resource('customers', CustomerController::class);
        Route::post('/customers/quick', [CustomerController::class, 'quickStore'])->name('customers.quick-store');
        Route::get('/services/queue', [ServiceController::class, 'queue'])->name('services.queue');
        Route::resource('services', ServiceController::class);
        Route::get('/services/{service}/print-pdf', [ServiceController::class, 'printPdf'])->name('services.print-pdf');
        Route::put('/services/{service}/status', [ServiceController::class, 'updateStatus'])->name('services.update-status');
        Route::put('/services/{service}/quick-update', [ServiceController::class, 'quickUpdate'])->name('services.quick-update');
        Route::put('/services/{service}/garansi', [ServiceController::class, 'updateGaransi'])->name('services.garansi');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
        Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
        Route::get('/reports/preview-pdf', [ReportController::class, 'previewPdf'])->name('reports.preview-pdf');
        Route::get('/reports/import', [ReportController::class, 'importForm'])->name('reports.import');
        Route::post('/reports/import', [ReportController::class, 'import']);
        Route::get('/reports/import/template', [ReportController::class, 'importTemplate'])->name('reports.import.template');

        Route::get('/data', [DataController::class, 'index'])->name('data.index');
        Route::post('/data/backup', [DataController::class, 'backup'])->name('data.backup');
        Route::post('/data/restore', [DataController::class, 'restore'])->name('data.restore');
    });

    // Shared biaya harian
    Route::middleware(['role:admin,superadmin'])->group(function () {
        Route::get('/biaya-harian', [BiayaHarianController::class, 'index'])->name('biaya-harian.index');
        Route::post('/biaya-harian', [BiayaHarianController::class, 'update'])->name('biaya-harian.update');
        Route::delete('/biaya-harian/{date}', [BiayaHarianController::class, 'destroy'])->name('biaya-harian.destroy');
    });

    // Admin & SuperAdmin routes
    Route::middleware(['role:admin,superadmin'])->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/damage-categories', [SettingController::class, 'storeDamageCategory'])->name('settings.damage-categories.store');
        Route::post('/settings/damage-categories/quick', [SettingController::class, 'quickStoreDamageCategory'])->name('settings.damage-categories.quick');
        Route::put('/settings/damage-categories/{damageCategory}', [SettingController::class, 'updateDamageCategory'])->name('settings.damage-categories.update');
        Route::delete('/settings/damage-categories/{damageCategory}', [SettingController::class, 'destroyDamageCategory'])->name('settings.damage-categories.destroy');

        Route::post('/settings/type-hp/quick', [SettingController::class, 'quickStoreTypeHp'])->name('settings.type-hp.quick');
        Route::delete('/settings/type-hp/{typeHpOption}', [SettingController::class, 'destroyTypeHp'])->name('settings.type-hp.destroy');
    });

    // SuperAdmin-only routes
    Route::middleware(['role:superadmin'])->group(function () {
        Route::resource('admin', AdminController::class)->except(['show']);
    });
});



Route::get('storage-files/{path}', function (string $path) {
    $fullPath = storage_path('app/public/' . $path);
    abort_unless(file_exists($fullPath), 404);
    return response()->file($fullPath, [
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('path', '.*')->name('storage.file');
