<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RegionController;
use App\Models\LoginSetting;
use App\Support\DashboardRedirector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn () => view('auth.login', ['loginSetting' => LoginSetting::current()]))->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/academic-year/select', function (Request $request) {
    $validated = $request->validate(['academic_year' => ['required', 'string', 'exists:academic_years,name']]);
    session(['academic_year' => $validated['academic_year']]);

    return response()->json(['academic_year' => $validated['academic_year']]);
})->middleware('auth')->name('academic-year.select');
Route::get('/regions/{resource}/{code?}', RegionController::class)->middleware('auth')->name('regions.index');
Route::middleware('auth')->group(function (): void {
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});
Route::get('/dashboard', function () {
    $target = DashboardRedirector::for(auth()->user());

    // Pengaman: user tanpa role tetap melihat placeholder, bukan redirect loop.
    if ($target === route('dashboard')) {
        return view('pages.dashboard');
    }

    return redirect($target);
})->middleware('auth')->name('dashboard');

require __DIR__.'/admin.php';
require __DIR__.'/bk.php';
require __DIR__.'/guru.php';
require __DIR__.'/wali_kelas.php';
require __DIR__.'/siswa.php';
