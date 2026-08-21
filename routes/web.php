<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SalesOrderController;
use App\Http\Controllers\Admin\UploadSoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to accessible home route or login
Route::get('/', function () {
    return Auth::check() ? redirect(Auth::user()->getHomeRoute()) : redirect()->route('login');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated Routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Protected Admin Group
    Route::prefix('admin')->name('admin.')->group(function () {

        // User Self-Service Profile Password Update
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware('permission:dashboard.view')
            ->name('dashboard');

        // Activity Logs
        Route::middleware('permission:activity-logs.view')->group(function () {
            Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        });

        // User Management
        Route::middleware('permission:users.view')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
        });

        Route::middleware('permission:users.create')->group(function () {
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
        });

        Route::middleware('permission:users.edit')->group(function () {
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        });

        Route::middleware('permission:users.delete')->group(function () {
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        // Role Management
        Route::middleware('permission:roles.view')->group(function () {
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        });

        Route::middleware('permission:roles.create')->group(function () {
            Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        });

        Route::middleware('permission:roles.edit')->group(function () {
            Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });

        Route::middleware('permission:roles.delete')->group(function () {
            Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        });

        // Permission Management
        Route::middleware('permission:permissions.view')->group(function () {
            Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        });

        Route::middleware('permission:permissions.create')->group(function () {
            Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
        });

        Route::middleware('permission:permissions.delete')->group(function () {
            Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
        });

        // Sales Orders Management (Original Sales Orders Module)
        Route::middleware('permission:sales-orders.view')->group(function () {
            Route::get('/sales-orders', [SalesOrderController::class, 'index'])->name('sales-orders.index');
        });

        Route::middleware('permission:sales-orders.create')->group(function () {
            Route::get('/sales-orders/create', [SalesOrderController::class, 'create'])->name('sales-orders.create');
            Route::post('/sales-orders', [SalesOrderController::class, 'store'])->name('sales-orders.store');
        });

        // Sales Orders Bulk Upload (Dedicated Permission Level: sales-orders.bulk-upload)
        Route::middleware('permission:sales-orders.bulk-upload')->group(function () {
            Route::get('/sales-orders/bulk-upload', [SalesOrderController::class, 'bulkUploadForm'])->name('sales-orders.bulk-upload');
            Route::post('/sales-orders/bulk-upload', [SalesOrderController::class, 'processBulkUpload'])->name('sales-orders.bulk-upload.store');
            Route::get('/sales-orders/bulk-upload/sample', [SalesOrderController::class, 'downloadSample'])->name('sales-orders.bulk-upload.sample');
        });

        Route::middleware('permission:sales-orders.view')->group(function () {
            Route::get('/sales-orders/{salesOrder}', [SalesOrderController::class, 'show'])->name('sales-orders.show');
        });

        Route::middleware('permission:sales-orders.edit')->group(function () {
            Route::get('/sales-orders/{salesOrder}/edit', [SalesOrderController::class, 'edit'])->name('sales-orders.edit');
            Route::put('/sales-orders/{salesOrder}', [SalesOrderController::class, 'update'])->name('sales-orders.update');
            Route::post('/sales-orders/items/{item}/return', [SalesOrderController::class, 'processReturn'])->name('sales-orders.items.return');
        });

        Route::middleware('permission:sales-orders.delete')->group(function () {
            Route::delete('/sales-orders/{salesOrder}', [SalesOrderController::class, 'destroy'])->name('sales-orders.destroy');
        });

        // Bills Management (Dedicated Permission Level: bills.*)
        Route::middleware('permission:bills.view')->group(function () {
            Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
        });

        Route::middleware('permission:bills.create')->group(function () {
            Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
            Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
        });

        Route::middleware('permission:bills.view')->group(function () {
            Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
        });

        Route::middleware('permission:bills.edit')->group(function () {
            Route::get('/bills/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit');
            Route::put('/bills/{bill}', [BillController::class, 'update'])->name('bills.update');
        });

        Route::middleware('permission:bills.delete')->group(function () {
            Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
        });

        // Upload SO Management (Dedicated Permission Level: upload-sos.*)
        Route::middleware('permission:upload-sos.view')->group(function () {
            Route::get('/upload-sos', [UploadSoController::class, 'index'])->name('upload-sos.index');
        });

        Route::middleware('permission:upload-sos.create')->group(function () {
            Route::get('/upload-sos/create', [UploadSoController::class, 'create'])->name('upload-sos.create');
            Route::post('/upload-sos', [UploadSoController::class, 'store'])->name('upload-sos.store');
        });

        Route::middleware('permission:upload-sos.view')->group(function () {
            Route::get('/upload-sos/{uploadSo}', [UploadSoController::class, 'show'])->name('upload-sos.show');
        });

        Route::middleware('permission:upload-sos.edit')->group(function () {
            Route::get('/upload-sos/{uploadSo}/edit', [UploadSoController::class, 'edit'])->name('upload-sos.edit');
            Route::put('/upload-sos/{uploadSo}', [UploadSoController::class, 'update'])->name('upload-sos.update');
        });

        Route::middleware('permission:upload-sos.delete')->group(function () {
            Route::delete('/upload-sos/{uploadSo}', [UploadSoController::class, 'destroy'])->name('upload-sos.destroy');
        });
    });
});
