<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Bookings\Index as BookingsIndex;
use App\Livewire\Bookings\Form as BookingsForm;
use App\Livewire\Bookings\Detail as BookingsDetail;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Users\Form as UsersForm;
use App\Livewire\Users\Detail as UsersDetail;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';


// Bookings Routes (Livewire)
Route::prefix('bookings')->name('bookings.')->group(function () {
    Route::get('/', BookingsIndex::class)->name('index');
    Route::get('/form/{id?}', BookingsForm::class)->name('form');
    Route::get('/detail/{id}', BookingsDetail::class)->name('detail');
});


// Users Routes (Livewire)
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', UsersIndex::class)->name('index');
    Route::get('/form/{id?}', UsersForm::class)->name('form');
    Route::get('/detail/{id}', UsersDetail::class)->name('detail');
});