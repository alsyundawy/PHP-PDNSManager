<?php
declare(strict_types=1);

/**
 * PHP-PDNSManager Enterprise Edition — Master Admin Layout
 *
 * @var string $title
 * @var string $content
 * @var string|null $csrfToken
 * @var string|null $cspNonce
 * @var \App\Models\User|null $user
 */
$title = $title ?? 'PHP-PDNSManager Enterprise';
$content = $content ?? '';
$csrfToken = $csrfToken ?? (function_exists('csrf_token') ? csrf_token() : '');
$cspNonce = $cspNonce ?? null;
$user = $user ?? null;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= htmlspecialchars($title ?? 'PHP-PDNSManager Enterprise', ENT_QUOTES, 'UTF-8') ?></title>

    <!-- SEO & Metadata -->
    <meta name="description" content="PHP-PDNSManager Enterprise Edition — High Performance, Secure PowerDNS Web Administration">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PHP-PDNS">

    <!-- CSRF Token Meta -->
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <!-- Offline Vendor & Custom Stylesheets (Zero CDN Dependency) -->
    <link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/vendor/fontawesome/all.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body>
    <!-- Skip to Content for Screen Readers & Keyboard Navigation (WCAG 2.4.1) -->
    <a href="#main-content" class="skip-to-content">Skip to main content</a>

    <!-- Mobile Drawer Dimmed Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>

    <div class="container-fluid p-0">
        <div class="row g-0 flex-nowrap min-vh-100">
            <!-- Sidebar Navigation -->
            <nav class="sidebar col-md-3 col-lg-2 p-3" id="sidebar" aria-label="Main Navigation">
                <div class="sidebar-brand position-relative">
                    <div>
                        <h4 class="text-white mb-0">PHP-PDNS</h4>
                        <small class="text-muted">Enterprise Edition</small>
                    </div>
                    <!-- Mobile Close Button (Touch accessible) -->
                    <button type="button"
                            class="btn-close btn-close-white d-md-none position-absolute top-0 end-0 mt-1 me-1"
                            id="sidebarClose"
                            aria-label="Close navigation sidebar"></button>
                </div>

                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= request()->getPath() === '/dashboard' ? 'active' : '' ?>"
                           href="/dashboard"
                           <?= request()->getPath() === '/dashboard' ? 'aria-current="page"' : '' ?>>
                            <i class="fas fa-tachometer-alt me-2" aria-hidden="true"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= request()->getPath() === '/zones' || (str_starts_with(request()->getPath(), '/zones/') && !str_starts_with(request()->getPath(), '/zones/bulk-records')) ? 'active' : '' ?>"
                           href="/zones"
                           <?= request()->getPath() === '/zones' ? 'aria-current="page"' : '' ?>>
                            <i class="fas fa-globe me-2" aria-hidden="true"></i> Zones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= request()->getPath() === '/zones/bulk-records' ? 'active' : '' ?>"
                           href="/zones/bulk-records">
                            <i class="fas fa-layer-group me-2" aria-hidden="true"></i> Bulk Records
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(request()->getPath(), '/templates') ? 'active' : '' ?>"
                           href="/templates">
                            <i class="fas fa-file-invoice me-2" aria-hidden="true"></i> Zone Templates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(request()->getPath(), '/servers') ? 'active' : '' ?>"
                           href="/servers">
                            <i class="fas fa-server me-2" aria-hidden="true"></i> Server Clusters
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(request()->getPath(), '/audit-logs') ? 'active' : '' ?>"
                           href="/audit-logs">
                            <i class="fas fa-history me-2" aria-hidden="true"></i> Audit Trail
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(request()->getPath(), '/health') ? 'active' : '' ?>"
                           href="/health">
                            <i class="fas fa-heartbeat me-2" aria-hidden="true"></i> Health Check
                        </a>
                    </li>
                </ul>

                <hr class="border-secondary my-3">

                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/logout">
                            <i class="fas fa-sign-out-alt me-2" aria-hidden="true"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content Container -->
            <main class="col-md-9 col-lg-10 p-3 p-md-4" id="main-content" role="main">
                <!-- Top Header Bar -->
                <header class="navbar top-navbar navbar-light rounded-3 shadow-sm mb-4">
                    <div class="container-fluid px-2">
                        <!-- Mobile Hamburger Button -->
                        <button class="navbar-toggler d-md-none me-2"
                                id="sidebarToggle"
                                type="button"
                                aria-controls="sidebar"
                                aria-expanded="false"
                                aria-label="Toggle navigation menu">
                            <span class="navbar-toggler-icon" aria-hidden="true"></span>
                        </button>

                        <div class="d-none d-sm-flex align-items-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle me-2">
                                <i class="fas fa-circle fa-xs me-1" aria-hidden="true"></i> Connected
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="fas fa-server fa-xs me-1" aria-hidden="true"></i> Authoritative DNS
                            </span>
                        </div>

                        <!-- Right Actions: Theme Toggle & User Profile -->
                        <div class="navbar-text ms-auto d-flex align-items-center">
                            <!-- Theme Mode Switcher (Dark / Light) -->
                            <button class="theme-toggle-btn me-3"
                                    id="themeToggleBtn"
                                    type="button"
                                    aria-label="Toggle dark/light theme"
                                    title="Toggle theme">
                                <i class="fas fa-moon" id="themeIcon" aria-hidden="true"></i>
                            </button>

                            <i class="fas fa-user-circle fa-lg me-2 text-primary" aria-hidden="true"></i>
                            <span class="fw-semibold text-reset"><?= htmlspecialchars($user->username ?? 'Administrator', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                </header>

                <!-- Page Injected Content -->
                <div class="content-wrapper">
                    <?= $content ?>
                </div>
            </main>
        </div>
    </div>

    <?php $nonceAttr = !empty($cspNonce) ? 'nonce="' . htmlspecialchars($cspNonce, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>
    <!-- 100% Offline JavaScript Libraries (Zero CDN Dependency) -->
    <script src="<?= asset('assets/vendor/jquery/jquery.min.js') ?>" <?= $nonceAttr ?>></script>
    <script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>" <?= $nonceAttr ?>></script>
    <script src="<?= asset('assets/vendor/chartjs/chart.umd.min.js') ?>" <?= $nonceAttr ?>></script>
    <script src="<?= asset('assets/js/app.js') ?>" <?= $nonceAttr ?>></script>
</body>
</html>
