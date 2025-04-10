<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhoneLookupController;

/* Route::get('/', function () {
    return view('welcome');
}); */

Route::get('/', [PhoneLookupController::class, 'index']);
Route::post('/lookup', [PhoneLookupController::class, 'lookup'])->name('lookup');