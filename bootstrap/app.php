<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Register Employee Module Routes
            Route::middleware(['web', 'auth'])
                ->prefix('dashboard/employees')
                ->name('employee.')
                ->group(base_path('routes/employee.php'));

            //Register Users Moudule Routes
            Route::middleware(['web', 'auth'])
                ->prefix('dashboard/users')
                ->name('user.')
                ->group(base_path('routes/user.php'));

            //Register expense module routes
            Route::middleware(['web', 'auth'])
                ->prefix('dashboard/expense-claims')
                ->name('expense-claim.')
                ->group(base_path('routes/expense_claim.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
        // Redirect unauthenticated guests to your management login
        $middleware->redirectTo(guests: '/management/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();