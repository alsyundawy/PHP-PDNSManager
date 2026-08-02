<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Request;
use App\Core\Response;
use App\Core\Exceptions\ValidationException;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\CsrfService;

class AuthController
{
    private AuthenticationService $auth;
    private CsrfService $csrf;
    public function __construct(AuthenticationService $auth, CsrfService $csrf)
    {
        $this->auth = $auth;
        $this->csrf = $csrf;
    }
    public function showLogin(Request $request): Response
    {
        $csrfToken = csrf_token();
        $html = view('auth.login', ['csrfToken' => $csrfToken]);
        return (new Response())->html($html);
    }
    public function login(Request $request): Response
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $ip = $request->getServerRequest()->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
        if (empty($username) || empty($password)) {
            throw new ValidationException(['message' => 'Username and password required']);
        }
        $user = $this->auth->login($username, $password, $ip);
        if (!$user) {
            throw new ValidationException(['message' => 'Invalid credentials']);
        }
        return (new Response())->redirect('/dashboard');
    }
    public function logout(Request $request): Response
    {
        $this->auth->logout();
        return (new Response())->redirect('/login');
    }
}
