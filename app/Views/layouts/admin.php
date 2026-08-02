<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'PHP-PDNSManager') ?></title>
    <link rel="stylesheet" href="<?= asset('assets/css/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/vendor/fontawesome.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
    <meta name="csrf-token" content="<?= $csrfToken ?? '' ?>">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <nav class="sidebar col-md-3 col-lg-2 d-md-block d-none p-3 min-vh-100">
                <div class="text-center mb-4"><h4 class="text-white">PHP-PDNS</h4><small class="text-muted">Enterprise Edition</small></div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link <?= request()->getPath() === '/dashboard' ? 'active' : '' ?>" href="/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?= str_starts_with(request()->getPath(), '/zones') ? 'active' : '' ?>" href="/zones"><i class="fas fa-globe me-2"></i> Zones</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-server me-2"></i> Records</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-key me-2"></i> DNSSEC</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-cogs me-2"></i> System</a></li>
                    <li class="nav-item mt-5"><a class="nav-link text-danger" href="/logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </nav>
            <main class="col-md-9 col-lg-10 p-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded-3 shadow-sm mb-4">
                    <div class="container-fluid">
                        <button class="navbar-toggler" id="sidebarToggle" type="button"><span class="navbar-toggler-icon"></span></button>
                        <span class="navbar-text ms-auto"><i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($user->username ?? 'Guest') ?></span>
                    </div>
                </nav>
                <?= $content ?>
            </main>
        </div>
    </div>
    <script src="<?= asset('assets/js/vendor/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('assets/js/vendor/chart.min.js') ?>"></script>
    <script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
