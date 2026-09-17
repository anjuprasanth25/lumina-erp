<?php

use App\Http\Controllers\EmployeeOnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EmployeeOnboardingController::class, 'index'])->name('index');
Route::get('/create', [EmployeeOnboardingController::class, 'create'])->name('create');
Route::post('/', [EmployeeOnboardingController::class, 'store'])->name('store');
Route::get('/{employee}/edit', [EmployeeOnboardingController::class, 'edit'])->name('edit');
Route::put('/{employee}', [EmployeeOnboardingController::class, 'update'])->name('update');
Route::get('/{employee}/show', [EmployeeOnboardingController::class, 'view'])->name('show');
Route::delete('/{employee}', [EmployeeOnboardingController::class, 'destroy'])->name('delete');


