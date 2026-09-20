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
Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])
    ->middleware('auth')
    ->name('admin.contacts.show');
Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])
    ->middleware('auth')
    ->name('admin.contacts.destroy');
