<?php
declare(strict_types=1);
namespace App\Services\Auth;

use App\Core\SessionManager;

/**
 * SECURITY FIX: Added rotate() to regenerate CSRF token after each
 * successful form submission, preventing CSRF token reuse attacks.
 * validate() remains constant-time via hash_equals().
 */
class CsrfService
{
    private SessionManager $session;

    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }

    public function generateToken(): string
    {
        $token = $this->session->get('csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->set('csrf_token', $token);
        }
        return $token;
    }

    public function validate(string $token): bool
    {
        $stored = $this->session->get('csrf_token');
        return is_string($stored) && $stored !== '' && hash_equals($stored, $token);
    }

    /**
     * SECURITY FIX: Rotate token after use to prevent replay.
     */
    public function rotate(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        return $token;
    }
}
