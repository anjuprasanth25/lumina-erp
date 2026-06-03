<?php

use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {

        $user = auth()->user();

        //to get the list of modules the user has access
        $modules = $user->companyRoleAssignments()
            ->with('role.modules.parentModule')
            ->get()
            ->pluck('role.modules')// get only the modules - nested array
            ->flatten()
            ->unique('id'); // remove the duplicates

        $groupedModules = $modules->groupBy(function ($module) {
            return $module->parentModule ? $module->parentModule->name : 'General';
        })->map(function ($items, $parentname) {
            return [
                'parent_name' => $parentname,
                'items' => $items->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'name' => $module->name,
                        'slug' => $module->slug
                    ];
                })->values(),
            ];

        })->values();


        return Inertia::render(
            'Dashboard',
            [
                'auth' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'initials' => collect(preg_split('/[\s,]+/', $user->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join(''),

                    ],
                    'menuStructure' => $groupedModules,
                ]
            ],

        );
    })->name('dashboard');

    Route::get('/leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');


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


