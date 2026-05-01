<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Bookings\Index as BookingsIndex;
use App\Livewire\Bookings\Form as BookingsForm;
use App\Livewire\Bookings\Detail as BookingsDetail;

Route::view('/', 'welcome')->name('home');

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