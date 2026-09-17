<?php

use App\Http\Controllers\ExpenseClaimController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ExpenseClaimController::class, 'index'])->name('index');
Route::get('/create', [ExpenseClaimController::class, 'create'])->name('create');
Route::post('/', [ExpenseClaimController::class, 'store'])->name('store');
Route::get('/{expenseclaim}/show', [ExpenseClaimController::class, 'show'])->name('show');
Route::get('/{expenseclaim}/edit', [ExpenseClaimController::class, 'edit'])->name('edit');
Route::put('/{expenseclaim}', [ExpenseClaimController::class, 'update'])->name('update');
Route::post('/{expenseclaim}/verify', [ExpenseClaimController::class, 'verify'])->name('verify');
Route::post('/{expenseclaim}/approve', [ExpenseClaimController::class, 'approve'])->name('approve');
Route::post('/{expenseclaim}/financeapprove', [ExpenseClaimController::class, 'financeapprove'])->name('finance-approve');
Route::post('/{expenseclaim}/book', [ExpenseClaimController::class, 'book'])->name('book');
Route::post('/{expenseclaim}/post', [ExpenseClaimController::class, 'post'])->name('post');


Route::get('/api/exchange-rate', [ExpenseClaimController::class, 'getExchangeRate'])->name('exchange-rate');
Route::get('/items/{item}/attachment', [ExpenseClaimController::class, 'downloadAttachment'])
    ->name('items.attachment');
