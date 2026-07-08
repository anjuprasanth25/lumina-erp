<?php
namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse implements Responsable
{
    public function toResponse($request): Response
    {


        // Get the previous URL page path (e.g., "/admin/login")
        $refererPath = parse_url(url()->previous(), PHP_URL_PATH);
        if (str_starts_with($refererPath, '/admin')) {

             return redirect()->to('/admin/login');
        }

        return redirect()->to('/management/login');
    }
}
