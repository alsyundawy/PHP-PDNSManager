<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Request;
use App\Core\Response;

class DashboardController
{
    public function index(Request $request): Response
    {
        $user = $request->getAttribute('user');
        $html = view('dashboard.index', ['user' => $user, 'csrfToken' => csrf_token()]);
        return (new Response())->html($html);
    }
}
