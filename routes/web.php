<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
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
Route::post('/admin/tags', [TagController::class, 'store'])
    ->middleware('auth')
    ->name('admin.tags.store');
Route::get('/admin/tags/{tag}/edit', [TagController::class, 'edit'])
    ->middleware('auth')
    ->name('admin.tags.edit');
Route::put('/admin/tags/{tag}', [TagController::class, 'update'])
    ->middleware('auth')
    ->name('admin.tags.update');
Route::delete('/admin/tags/{tag}', [TagController::class, 'destroy'])
    ->middleware('auth')
    ->name('admin.tags.destroy');
