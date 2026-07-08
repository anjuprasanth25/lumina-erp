<?php
namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->to('/management');
        }

        // Get the previous URL page path (e.g., "/admin/login")
        $refererPath = parse_url(url()->previous(), PHP_URL_PATH);

        if ($user->isSystemAdmin() && str_starts_with($refererPath, '/admin')) {

             return redirect()->to('/admin');
        }

        return redirect()->to('/dashboard');




        // $userRoles = $user->roles()->pluck('name')->toArray();

        // $adminRoles = ['HR Admin', 'Finance Admin', 'Super Admin'];

        // if (!empty(array_intersect($userRoles, $adminRoles))) {
        //     return redirect()->to('/management');
        // }




    }
}
