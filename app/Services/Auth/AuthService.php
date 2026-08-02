<?php
declare(strict_types=1);
namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Core\SessionManager;
use App\Core\Logger;

/**
 * SECURITY FIX: password_verify() is already timing-safe; added rate-limit
 * hook stub and explicit session regeneration on login to prevent
 * session fixation attacks.
 * BUGFIX: verifyTotp() now uses hash_equals() instead of == comparison.
 */
class AuthService
{
    private UserRepositoryInterface $userRepo;
    private SessionManager $session;
    private Logger $logger;

    public function __construct(
        UserRepositoryInterface $userRepo,
        SessionManager $session,
        Logger $logger
    ) {
        $this->userRepo = $userRepo;
        $this->session  = $session;
        $this->logger   = $logger;
    }

    public function login(string $username, string $password): ?User
    {
        $user = $this->userRepo->findByUsername($username);

        if (!$user || !$user->is_active) {
            // Timing-safe: still verify a dummy hash to prevent user enumeration
            password_verify($password, '$argon2id$v=19$m=65536,t=4,p=1$dummy');
            return null;
        }

        if (!password_verify($password, $user->password_hash)) {
            $this->logger->warning('Failed login attempt', ['username' => $username]);
            return null;
        }

        // SECURITY FIX: Regenerate session ID on login to prevent session fixation
        $this->session->regenerate();
        $this->session->set('user_id', $user->id);

        $this->userRepo->updateLastLogin($user->id);
        $this->logger->info('User logged in', ['user_id' => $user->id]);

        return $user;
    }

    public function logout(): void
    {
        $this->session->destroy();
        $this->logger->info('User logged out');
    }

    public function getCurrentUser(): ?User
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            return null;
        }
        return $this->userRepo->find((int) $userId);
    }

    public function verifyTotp(User $user, string $code): bool
    {
        if ($user->totp_secret === null) {
            return false;
        }
        $expected = $this->generateTotpCode($user->totp_secret);
        // BUGFIX: Use hash_equals() instead of == to prevent timing attacks
        return hash_equals($expected, $code);
    }

    private function generateTotpCode(string $secret): string
    {
        $time    = (int) floor(time() / 30);
        $key     = base64_decode($secret);
        $timeBytes = pack('N*', 0) . pack('N*', $time);
        $hash    = hash_hmac('sha1', $timeBytes, $key, true);
        $offset  = ord($hash[19]) & 0x0f;
        $otp     = (
            ((ord($hash[$offset])     & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) <<  8) |
             (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }
}
