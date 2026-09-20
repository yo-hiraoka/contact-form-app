<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])
    ->name('contact.confirm');
Route::post('/contacts', [ContactController::class, 'store'])
    ->name('contact.store');

Route::get('/thanks', function () {
    return view('contact.thanks');
})->name('contact.thanks');
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('auth')
    ->name('admin.index');
