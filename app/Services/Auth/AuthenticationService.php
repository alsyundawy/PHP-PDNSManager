<?php
declare(strict_types=1);
namespace App\Services\Auth;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Logger;
use App\Core\SessionManager;

class AuthenticationService
{
    private UserRepositoryInterface $userRepo;
    private Logger $logger;
    private SessionManager $session;
    public function __construct(
        UserRepositoryInterface $userRepo,
        Logger $logger,
        SessionManager $session
    ) {
        $this->userRepo = $userRepo;
        $this->logger = $logger;
        $this->session = $session;
    }
    public function login(string $username, string $password, string $ip): ?User
    {
        $user = $this->userRepo->findByUsername($username) ?: $this->userRepo->findByEmail($username);
        if (!$user) {
            $this->logger->channel('security')->warning('Login failed: user not found', ['username' => $username, 'ip' => $ip]);
            return null;
        }
        if (!password_verify($password, $user->password_hash)) {
            $this->logger->channel('security')->warning('Login failed: invalid password', ['username' => $username, 'ip' => $ip]);
            return null;
        }
        $this->session->regenerate();
        $this->session->set('user_id', $user->id);
        $this->userRepo->recordLogin($user->id);
        $this->logger->channel('security')->info('User logged in', ['user_id' => $user->id, 'ip' => $ip]);
        return $user;
    }
    public function logout(): void
    {
        $userId = $this->session->get('user_id');
        $this->session->destroy();
        if ($userId) {
            $this->logger->channel('security')->info('User logged out', ['user_id' => $userId]);
        }
    }
    public function check(): ?User
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            return null;
        }
        return $this->userRepo->find((int) $userId);
    }
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
}
