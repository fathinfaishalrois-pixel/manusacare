<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Reporter\{DashboardController as ReporterDashboard, ReportController, ReviewController};
use App\Http\Controllers\Officer\DashboardController as OfficerDashboard;
use App\Http\Controllers\Admin\{DashboardController as AdminDashboard, ReportManagementController, UserManagementController, CategoryController};

Route::view('/', 'public.home')->name('home');
Route::view('/tentang', 'public.about')->name('about');
Route::view('/faq', 'public.faq')->name('faq');
Route::view('/kontak', 'public.contact')->name('contact');

// Authentication controllers handle already-authenticated and legacy sessions
// explicitly so one guard cannot block the other tab's login form.
Route::get('/daftar', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/daftar', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');

// Admin login is intentionally independent from the reporter guard.
Route::get('/admin/login', [AuthenticatedSessionController::class, 'adminCreate'])->name('admin.login');
Route::post('/admin/login', [AuthenticatedSessionController::class, 'adminStore'])->middleware('throttle:5,1')->name('admin.login.store');

Route::get('/media/{media}', [\App\Http\Controllers\MediaController::class, 'show'])->name('media.show');

Route::post('/logout', fn (\Illuminate\Http\Request $request) => app(AuthenticatedSessionController::class)->destroy($request, 'web'))
    ->middleware('auth:web')->name('logout');
Route::post('/admin/logout', fn (\Illuminate\Http\Request $request) => app(AuthenticatedSessionController::class)->destroy($request, 'admin'))
    ->middleware('auth:admin')->name('admin.logout');

Route::middleware(['auth:web', 'role:reporter'])->group(function () {
    Route::get('/dashboard', [ReporterDashboard::class, 'index'])->name('reporter.dashboard');
    Route::resource('/laporan', ReportController::class)->only(['index', 'create', 'store', 'show'])
        ->parameters(['laporan' => 'report'])->names('reports');
    Route::post('/laporan/{report}/ulasan', [ReviewController::class, 'store'])->name('reports.review');
});

Route::middleware(['auth:web', 'role:officer'])->prefix('petugas')->name('officer.')->group(function () {
    Route::get('/dashboard', [OfficerDashboard::class, 'index'])->name('dashboard');
    Route::get('/laporan/{report}', [OfficerDashboard::class, 'show'])->name('reports.show');
    Route::post('/laporan/{report}/progres', [OfficerDashboard::class, 'update'])->name('reports.update');
});

Route::prefix('admin')->name('admin.')->middleware(['auth:admin', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/laporan', [ReportManagementController::class, 'index'])->name('reports.index');
    Route::get('/laporan/{report}', [ReportManagementController::class, 'show'])->name('reports.show');
    Route::post('/laporan/{report}/verifikasi', [ReportManagementController::class, 'verify'])->name('reports.verify');
    Route::post('/laporan/{report}/penugasan', [ReportManagementController::class, 'assign'])->name('reports.assign');
    Route::post('/laporan/{report}/proses-perbaikan', [ReportManagementController::class, 'updateRepairProgress'])->name('reports.repair-progress');
    Route::get('/petugas/rekomendasi', [ReportManagementController::class, 'recommendations'])->name('officers.recommendations');
    Route::delete('/laporan/{report}', [ReportManagementController::class, 'destroy'])->name('reports.destroy');
    Route::resource('/pengguna', UserManagementController::class)->parameters(['pengguna' => 'user'])->except(['show'])->names('users');
    Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update');
});
