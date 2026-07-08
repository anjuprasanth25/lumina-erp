<?php

use App\Http\Controllers\EmployeeOnboardingController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/management');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render(
            'Dashboard'
        );
    })->name('dashboard');

    Route::get(
        '/dashboard/employees',
        [EmployeeOnboardingController::class, 'index']
    )->name('employee.index');


    Route::get(
        '/dashboard/employees/create',
        [EmployeeOnboardingController::class, 'create']
    )->name('employee.create');


    Route::post(
        '/dashboard/employees',
        [EmployeeOnboardingController::class, 'store']
    )->name('employee.store');

    Route::get(
        '/dashboard/employees/{employee}/edit',
        [EmployeeOnboardingController::class, 'edit']
    )->name('employee.edit');

    Route::put(
        '/dashboard/employees/{employee}',
        [EmployeeOnboardingController::class, 'update']
    )->name('employee.update');

    Route::get(
        '/dashboard/employee/{employee}/show',
        [EmployeeOnboardingController::class, 'view']

    )->name('employee.show');

    Route::delete(
        '/dashboard/employees/{employee}',
        [EmployeeOnboardingController::class, 'destroy']
    )->name('employee.delete');


    //Route::get('/dashboard/leave-application', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
    //Route::post('dashboard/leave-application', [LeaveRequestController::class, 'store'])->name('leave-requests.store');




});

Route::post('/logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return Inertia::location('/management/login');
})->name('logout');





Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
