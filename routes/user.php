<?php

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'index'])->name('index');
Route::get('/create', [UserController::class, 'create'])->name('create');
Route::post('/', [UserController::class, 'store'])->name('store');
Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
Route::put('/{user}', [UserController::class, 'update'])->name('update');
Route::get('/{user}/show', [UserController::class, 'show'])->name('show');
Route::delete('/{user}', [UserController::class, 'destroy'])->name('delete');

Route::post('/{user}/resend-link', [UserController::class, 'resendLink'])->name('resend-link');