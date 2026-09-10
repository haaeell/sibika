<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::view('/login', 'auth.login')->middleware('guest')->name('login');
Route::view('/dashboard', 'pages.dashboard')->name('dashboard');

require __DIR__.'/admin.php';
require __DIR__.'/bk.php';
require __DIR__.'/guru.php';
require __DIR__.'/wali_kelas.php';
require __DIR__.'/siswa.php';
