<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/regions/{resource}/{code?}', RegionController::class)->middleware('auth')->name('regions.index');
Route::view('/dashboard', 'pages.dashboard')->middleware('auth')->name('dashboard');

require __DIR__.'/admin.php';
require __DIR__.'/bk.php';
require __DIR__.'/guru.php';
require __DIR__.'/wali_kelas.php';
require __DIR__.'/siswa.php';
