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
if (!defined('APP_NAME')) {
    define('APP_NAME', 'PHP-PDNSManager Enterprise');
}
if (!defined('DASHBOARD_URI')) {
    define('DASHBOARD_URI', '/dashboard');
}

$viewVars = get_defined_vars();
$title = $viewVars['title'] ?? APP_NAME;
$content = $viewVars['content'] ?? '';
$csrfToken = $viewVars['csrfToken'] ?? null;
if ($csrfToken === null && function_exists('csrf_token')) {
    $csrfToken = (string) csrf_token();
}
$csrfToken = (string) ($csrfToken ?? '');
$cspNonce = $viewVars['cspNonce'] ?? null;
$user = $viewVars['user'] ?? null;
$robots = $viewVars['robots'] ?? 'noindex, nofollow';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= htmlspecialchars(($title ? $title . ' — ' : '') . APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- SEO & Metadata (Standard 2026) -->
    <meta name="description" content="PHP-PDNSManager Enterprise Edition — High Performance, Secure PowerDNS Authoritative Web Administration Control Plane.">
    <meta name="keywords" content="PowerDNS, DNS Management, Authoritative DNS, DNSSEC, BIND9, Enterprise DNS, Debian, Nginx, PHP, REST API">
    <meta name="robots" content="<?= htmlspecialchars($robots, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="PHP-PDNSManager Team">
    <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#0284c7" media="(prefers-color-scheme: light)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PHP-PDNS">
    <meta name="format-detection" content="telephone=no">

    <!-- Open Graph / Social SEO -->
    <meta property="og:title" content="<?= htmlspecialchars(($title ? $title . ' — ' : '') . APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="High Performance, Secure PowerDNS Authoritative Web Administration Control Plane.">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:locale" content="en_US">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= htmlspecialchars(($title ? $title . ' — ' : '') . APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="High Performance, Secure PowerDNS Authoritative Web Administration Control Plane.">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?= htmlspecialchars(function_exists('request') ? request()->getPath() : DASHBOARD_URI, ENT_QUOTES, 'UTF-8') ?>">

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
                        <a class="nav-link <?= request()->getPath() === DASHBOARD_URI ? 'active' : '' ?>"
                            href="<?= DASHBOARD_URI ?>"
                            <?= request()->getPath() === DASHBOARD_URI ? 'aria-current="page"' : '' ?>>
                            <i class="fas fa-tachometer-alt me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_dashboard'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= request()->getPath() === '/zones' || (str_starts_with(request()->getPath(), '/zones/') && !str_starts_with(request()->getPath(), '/zones/bulk-records')) ? 'active' : '' ?>"
                            href="/zones"
                            <?= request()->getPath() === '/zones' ? 'aria-current="page"' : '' ?>>
                            <i class="fas fa-globe me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_zones'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= request()->getPath() === '/zones/bulk-records' ? 'active' : '' ?>"
                            href="/zones/bulk-records">
                            <i class="fas fa-layer-group me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_bulk_records'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= request()->getPath() === '/zones/reverse-wizard' ? 'active' : '' ?>"
                            href="/zones/reverse-wizard">
                            <i class="fas fa-undo-alt me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_reverse_wizard'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(request()->getPath(), '/toolbox') ? 'active' : '' ?>"
                            href="/toolbox">
                            <i class="fas fa-toolbox me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_dns_toolbox'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(request()->getPath(), '/tsig-keys') ? 'active' : '' ?>"
                            href="/tsig-keys">
                            <i class="fas fa-key me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_tsig_keys'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(request()->getPath(), '/backups') ? 'active' : '' ?>"
                            href="/backups">
                            <i class="fas fa-shield-alt me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_cluster_backups'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(request()->getPath(), '/templates') ? 'active' : '' ?>"
                            href="/templates">
                            <i class="fas fa-file-invoice me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_zone_templates'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(request()->getPath(), '/servers') ? 'active' : '' ?>"
                            href="/servers">
                            <i class="fas fa-server me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_server_clusters'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(request()->getPath(), '/audit-logs') ? 'active' : '' ?>"
                            href="/audit-logs">
                            <i class="fas fa-history me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_audit_trail'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(request()->getPath(), '/health') ? 'active' : '' ?>"
                            href="/health">
                            <i class="fas fa-heartbeat me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_health_check'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                </ul>

                <hr class="border-secondary my-3">

                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/logout">
                            <i class="fas fa-sign-out-alt me-2" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_logout'), ENT_QUOTES, 'UTF-8') ?>
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
                                <i class="fas fa-circle fa-xs me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('connected'), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="fas fa-server fa-xs me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('authoritative_dns'), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <!-- Right Actions: Quick Search, Language, Theme & User Profile -->
                        <div class="navbar-text ms-auto d-flex align-items-center">
                            <!-- Quick Search Command Palette Trigger -->
                            <button class="btn btn-sm btn-outline-secondary me-2 d-none d-md-inline-flex align-items-center"
                                    id="commandPaletteTrigger"
                                    type="button"
                                    aria-label="Quick Search (Ctrl+K)"
                                    title="Quick Search (Ctrl+K)">
                                <i class="fas fa-search me-1" aria-hidden="true"></i> <?= htmlspecialchars(__('nav_quick_search'), ENT_QUOTES, 'UTF-8') ?> <kbd class="ms-2 px-1 py-0 text-muted border rounded" style="font-size:0.75rem;">Ctrl K</kbd>
                            </button>

                            <!-- Language Switcher Dropdown (EN / ID) -->
                            <div class="dropdown me-2">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center"
                                        type="button"
                                        id="languageDropdown"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        title="<?= htmlspecialchars(__('language'), ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fas fa-globe me-1" aria-hidden="true"></i>
                                    <span class="text-uppercase fw-semibold"><?= htmlspecialchars(current_locale(), ENT_QUOTES, 'UTF-8') ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="languageDropdown">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center justify-content-between <?= current_locale() === 'en' ? 'active fw-bold' : '' ?>" href="/locale/en">
                                            <span>English (US)</span>
                                            <?php if (current_locale() === 'en'): ?><i class="fas fa-check fa-xs ms-2"></i><?php endif; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center justify-content-between <?= current_locale() === 'id' ? 'active fw-bold' : '' ?>" href="/locale/id">
                                            <span>Bahasa Indonesia</span>
                                            <?php if (current_locale() === 'id'): ?><i class="fas fa-check fa-xs ms-2"></i><?php endif; ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <!-- Theme Mode Switcher Dropdown (SparkNotePad Presets) -->
                            <div class="dropdown me-3">
                                <button class="theme-toggle-btn dropdown-toggle d-flex align-items-center"
                                        id="themeToggleBtn"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        title="<?= htmlspecialchars(__('toggle_theme'), ENT_QUOTES, 'UTF-8') ?>"
                                        aria-label="<?= htmlspecialchars(__('toggle_theme'), ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fas fa-palette me-1" id="themeIcon" aria-hidden="true"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" id="themeDropdownMenu" aria-labelledby="themeToggleBtn">
                                    <li><h6 class="dropdown-header text-uppercase fs-7"><i class="fas fa-paint-brush me-1"></i> <?= htmlspecialchars(__('theme'), ENT_QUOTES, 'UTF-8') ?></h6></li>
                                    <li><a class="dropdown-item theme-select-item" href="#" data-theme="dark"><i class="fas fa-moon text-info me-2"></i> <?= htmlspecialchars(__('theme_dark'), ENT_QUOTES, 'UTF-8') ?></a></li>
                                    <li><a class="dropdown-item theme-select-item" href="#" data-theme="light"><i class="fas fa-sun text-warning me-2"></i> <?= htmlspecialchars(__('theme_light'), ENT_QUOTES, 'UTF-8') ?></a></li>
                                    <li><a class="dropdown-item theme-select-item" href="#" data-theme="midnight"><i class="fas fa-star text-primary me-2"></i> <?= htmlspecialchars(__('theme_midnight'), ENT_QUOTES, 'UTF-8') ?></a></li>
                                    <li><a class="dropdown-item theme-select-item" href="#" data-theme="ocean"><i class="fas fa-water text-cyan me-2"></i> <?= htmlspecialchars(__('theme_ocean'), ENT_QUOTES, 'UTF-8') ?></a></li>
                                    <li><a class="dropdown-item theme-select-item" href="#" data-theme="forest"><i class="fas fa-tree text-success me-2"></i> <?= htmlspecialchars(__('theme_forest'), ENT_QUOTES, 'UTF-8') ?></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item theme-select-item" href="#" data-theme="high-contrast"><i class="fas fa-adjust text-white bg-dark p-1 rounded me-2"></i> <?= htmlspecialchars(__('theme_high_contrast'), ENT_QUOTES, 'UTF-8') ?></a></li>
                                </ul>
                            </div>

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

    <!-- Command Palette Modal (Ctrl+K) -->
    <div class="modal fade" id="commandPaletteModal" tabindex="-1" aria-labelledby="commandPaletteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom p-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search" aria-hidden="true"></i></span>
                        <input type="text" id="commandPaletteInput" class="form-control bg-transparent border-0 fs-5 text-reset" placeholder="Type a domain, record, IP, or command..." autocomplete="off">
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="max-height: 380px; overflow-y: auto;">
                    <div id="commandPaletteResults" class="list-group list-group-flush">
                        <div class="p-4 text-muted text-center small">Type at least 2 characters to search across PowerDNS zones, records, and navigation...</div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3 small text-muted justify-content-between">
                    <span><kbd class="px-1 border rounded">↑</kbd> <kbd class="px-1 border rounded">↓</kbd> Navigate</span>
                    <span><kbd class="px-1 border rounded">↵</kbd> Select</span>
                    <span><kbd class="px-1 border rounded">ESC</kbd> Close</span>
                </div>
            </div>
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
