<?php
declare(strict_types=1);
namespace App\Services\Auth;
use App\Core\SessionManager;

class CsrfService
{
    private SessionManager $session;
    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        return $token;
    }
    public function validate(string $token): bool
    {
        $stored = $this->session->get('csrf_token');
        return $stored && hash_equals($stored, $token);
    }
}
