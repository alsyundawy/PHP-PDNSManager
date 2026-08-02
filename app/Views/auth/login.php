<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Login</title>
<link rel="stylesheet" href="<?= asset('assets/css/vendor/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/vendor/fontawesome.min.css') ?>">
<style>body{background:linear-gradient(135deg,#1e3a8a,#3b82f6);min-height:100vh;display:flex;align-items:center;justify-content:center}.login-card{max-width:400px;width:100%;background:white;border-radius:1rem;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,0.3)}</style>
</head>
<body>
<div class="login-card">
    <div class="logo text-center"><h1>PHP-PDNS</h1><p class="text-muted">Enterprise DNS Management</p></div>
    <?php if (isset($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST" action="/login">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="mb-3"><label class="form-label">Username or Email</label><input type="text" class="form-control" name="username" required autofocus></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <div class="mt-3 text-center"><small class="text-muted">PHP-PDNSManager Enterprise v1.0</small></div>
</div>
</body>
</html>
