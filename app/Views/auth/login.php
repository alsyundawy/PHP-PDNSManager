<?php
declare(strict_types=1);

/**
 * PHP-PDNSManager Enterprise DNS Administration Console
 * Login View - VisualSubnetCalc Dark/Light Theme with 100% Offline Assets
 *
 * @var string|null $error
 * @var string|null $csrfToken
 * @var string|null $cspNonce
 */
$viewVars = get_defined_vars();
$error = $viewVars['error'] ?? null;
$csrfToken = $viewVars['csrfToken'] ?? (function_exists('csrf_token') ? csrf_token() : '');
$cspNonce = $viewVars['cspNonce'] ?? null;
$token = $csrfToken;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login — PHP-PDNSManager Enterprise</title>

    <!-- SEO & Identity -->
    <meta name="description" content="Secure authentication console for PHP-PDNSManager Enterprise DNS Suite">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- 100% Offline Local Assets (Strict Zero CDN) -->
    <link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/vendor/fontawesome/all.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">

    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme');
                const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const activeTheme = savedTheme || (systemDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', activeTheme);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body class="login-body">
    <main class="login-wrapper" role="main">
        <div class="card card-glass login-card animate-fadeIn">
            <div class="card-header border-0 bg-transparent text-center pt-4 pb-2">
                <div class="login-brand mb-3">
                    <span class="brand-icon me-0" style="width: 48px; height: 48px; font-size: 1.5rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; background: linear-gradient(135deg, var(--primary-accent) 0%, var(--primary-accent-hover) 100%); color: #ffffff; box-shadow: 0 4px 15px var(--glow-primary);">
                        <i class="fas fa-shield-halved" aria-hidden="true"></i>
                    </span>
                </div>
                <h1 class="h4 fw-bold mb-1 text-contrast">PHP-PDNSManager</h1>
                <p class="text-secondary small mb-0">Enterprise Authoritative DNS Control Center</p>
            </div>

            <div class="card-body p-4 pt-2">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 px-3 mb-3 d-flex align-items-center" role="alert">
                        <i class="fas fa-triangle-exclamation me-2 flex-shrink-0" aria-hidden="true"></i>
                        <div class="small fw-medium"><?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/login" novalidate autocomplete="on">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)$token, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label small fw-semibold text-contrast">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="fas fa-user text-secondary" aria-hidden="true"></i>
                            </span>
                            <input type="text"
                                   class="form-control border-start-0 ps-0"
                                   id="username"
                                   name="username"
                                   autocomplete="username"
                                   required
                                   autofocus
                                   placeholder="admin"
                                   spellcheck="false">
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label small fw-semibold text-contrast mb-0">Password</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="fas fa-lock text-secondary" aria-hidden="true"></i>
                            </span>
                            <input type="password"
                                   class="form-control border-start-0 ps-0"
                                   id="password"
                                   name="password"
                                   autocomplete="current-password"
                                   required
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
                        <i class="fas fa-arrow-right-to-bracket me-2" aria-hidden="true"></i> Sign In to Console
                    </button>
                </form>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary border-opacity-25 small">
                    <span class="text-secondary">v1.0.1 Enterprise</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-decoration-none" id="themeToggleBtn" aria-label="Toggle dark/light mode">
                        <i class="fas fa-moon theme-icon-dark d-none" aria-hidden="true"></i>
                        <i class="fas fa-sun theme-icon-light d-none" aria-hidden="true"></i>
                        <span class="ms-1 d-none d-sm-inline" id="themeLabel">Theme</span>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- 100% Offline Local JavaScript -->
    <script src="<?= asset('assets/vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
