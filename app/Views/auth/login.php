<?php

declare(strict_types=1);

/**
 * FILE: app/Views/auth/login.php
 * DESCRIPTION: High-Performance Enterprise Login Console with dual-dark high-contrast themes,
 *              faithfully modeled after ns1.orion.net.id (PowerDNS-Admin by ALSYUNDAWY IT SOLUTION).
 * AUTHOR: ALSYUNDAWY IT SOLUTION / PHP-PDNSManager Team
 *
 * @var string|null $error
 * @var string|null $csrfToken
 */

$viewVars = get_defined_vars();
$rawError = $viewVars['error'] ?? null;
$errorMessage = '';
if (!empty($rawError)) {
    if (is_array($rawError)) {
        $errorMessage = (string) ($rawError['message'] ?? implode(', ', $rawError));
    } else {
        $errorMessage = (string) $rawError;
    }
}

$csrf = $viewVars['csrfToken'] ?? (function_exists('csrf_token') ? csrf_token() : '');
$currentLang = function_exists('current_locale') ? current_locale() : 'en';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>" data-theme="dark">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
  <title><?= htmlspecialchars(__('login_title'), ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="Sign in to PHP-PDNSManager Enterprise to manage PowerDNS authoritative zones and DNS configurations." />
  <meta name="robots" content="noindex, nofollow"/>
  <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)"/>
  <meta name="theme-color" content="#0284c7" media="(prefers-color-scheme: light)"/>
  <meta name="mobile-web-app-capable" content="yes"/>
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>

  <!-- Offline Local Assets (Strict Zero CDN) -->
  <link rel="stylesheet" href="<?= asset('assets/vendor/fontawesome/all.min.css') ?>"/>

  <script>
    (function(){
      var t = 'dark';
      try { t = localStorage.getItem('site-theme') || 'dark'; } catch(e){}
      document.documentElement.setAttribute('data-theme', t);
    })();
  </script>

  <style>
/* Custom Properties & Eye-Friendly Dual-Dark Themes (Inspired by ns1.orion.net.id) */
:root {
  --bg-1: #0f172a;
  --bg-2: #020617;
  --text-primary: #f8fafc;
  --text-muted: #94a3b8;
  --accent-from: #38bdf8;
  --accent-to: #0ea5e9;
  --glass-border: rgba(56, 189, 248, 0.22);
  --card-bg: rgba(15, 23, 42, 0.65);
  --card-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(56, 189, 248, 0.15);
  --input-bg: rgba(2, 6, 23, 0.55);
  --input-border: rgba(56, 189, 248, 0.3);
  --radius: 14px;
  color-scheme: dark;
}

[data-theme="dark"] {
  --bg-1: #0a0518;
  --bg-2: #02010a;
  --text-primary: #ffffff;
  --text-muted: #a5b4fc;
  --accent-from: #818cf8;
  --accent-to: #6366f1;
  --glass-border: rgba(129, 140, 248, 0.22);
  --card-bg: rgba(10, 5, 24, 0.68);
  --card-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(129, 140, 248, 0.15);
  --input-bg: rgba(2, 1, 10, 0.6);
  --input-border: rgba(129, 140, 248, 0.3);
  --radius: 14px;
  color-scheme: dark;
}

[data-theme="light"] {
  --bg-1: #f8fafc;
  --bg-2: #e2e8f0;
  --text-primary: #0f172a;
  --text-muted: #475569;
  --accent-from: #0284c7;
  --accent-to: #0369a1;
  --glass-border: rgba(2, 132, 199, 0.25);
  --card-bg: rgba(255, 255, 255, 0.88);
  --card-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(2, 132, 199, 0.18);
  --input-bg: rgba(255, 255, 255, 0.95);
  --input-border: rgba(148, 163, 184, 0.45);
  --radius: 14px;
  color-scheme: light;
}

/* Core Page Setup */
* {
  box-sizing: border-box;
}

html, body {
  height: 100%;
  margin: 0;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-rendering: optimizeLegibility;
  background: radial-gradient(circle at 50% -20%, rgba(56, 189, 248, 0.15), transparent 50%), linear-gradient(180deg, var(--bg-1), var(--bg-2));
  color: var(--text-primary);
  transition: background-color 0.3s ease;
}

[data-theme="dark"] html, [data-theme="dark"] body {
  background: radial-gradient(circle at 50% -20%, rgba(129, 140, 248, 0.15), transparent 50%), linear-gradient(180deg, var(--bg-1), var(--bg-2));
}

[data-theme="light"] html, [data-theme="light"] body {
  background: radial-gradient(circle at 50% -20%, rgba(2, 132, 199, 0.12), transparent 50%), linear-gradient(180deg, var(--bg-1), var(--bg-2));
}

.page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1.2rem;
  gap: 1.25rem;
}

/* Header & Logo */
.site-header {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 0;
  animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.brand-shield-wrap {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 68px;
  height: 68px;
  border-radius: 20px;
  background: linear-gradient(135deg, var(--accent-from), var(--accent-to));
  color: #0b1329;
  box-shadow: 0 10px 25px -4px rgba(56, 189, 248, 0.45);
  transition: transform 0.3s ease;
}

.brand-shield-wrap:hover {
  transform: scale(1.05) rotate(3deg);
}

/* Theme Toggle Button */
.theme-toggle {
  position: fixed;
  right: 18px;
  top: 16px;
  z-index: 60;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  border: 1px solid var(--glass-border);
  background: var(--card-bg);
  box-shadow: var(--card-shadow);
  cursor: pointer;
  color: var(--text-primary);
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.theme-toggle:focus {
  outline: 3px solid rgba(59, 130, 246, 0.3);
}
.theme-toggle:hover {
  transform: scale(1.08) rotate(8deg);
  border-color: var(--accent-to);
  color: var(--accent-to);
}
.theme-toggle:active {
  transform: scale(0.95);
}

/* Glassmorphism Card Container */
.card {
  width: 100%;
  max-width: 460px;
  padding: 36px 32px;
  border-radius: 22px;
  background: var(--card-bg);
  border: 1px solid var(--glass-border);
  box-shadow: var(--card-shadow);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  display: flex;
  flex-direction: column;
  gap: 18px;
  align-items: stretch;
  position: relative;
  overflow: hidden;
  min-width: 280px;
  animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

/* Branding Headers */
.brand {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  margin-bottom: 2px;
  text-align: center;
}
.brand .title {
  font-weight: 700;
  font-size: 1.35rem;
  color: var(--text-primary);
  letter-spacing: 0.2px;
  margin: 0;
}
.brand .subtitle {
  font-size: 0.9375rem;
  color: var(--text-muted);
  margin: 0;
}

/* Forms & Inputs */
form {
  display: block;
}
.field {
  margin-bottom: 1rem;
}
.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.input-icon {
  position: absolute;
  left: 14px;
  color: var(--text-muted);
  font-size: 0.95rem;
  pointer-events: none;
  transition: color 0.2s ease;
}
.input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 2.6rem;
  border-radius: 12px;
  border: 1px solid var(--input-border);
  background: var(--input-bg);
  color: var(--text-primary);
  font-size: 0.9375rem;
  outline: none;
  box-sizing: border-box;
  transition: all 0.2s ease;
}
.input::placeholder {
  color: var(--text-muted);
  opacity: 0.65;
}
.input:focus {
  box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25);
  border-color: var(--accent-to);
  background: var(--bg-1);
}
.input:focus + .input-icon,
.input-wrap:focus-within .input-icon {
  color: var(--accent-from);
}

/* Flex Row Layout */
.row {
  display: flex;
  gap: 12px;
  align-items: center;
  justify-content: space-between;
  margin-top: 6px;
}
.row .left {
  flex: 0 0 auto;
}
.row .right {
  flex: 1 1 auto;
  display: flex;
  justify-content: flex-end;
}

/* Remember Me Switch */
.remember {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  color: var(--text-muted);
  font-weight: 500;
  font-size: 0.9rem;
  user-select: none;
  cursor: pointer;
}
.remember input {
  width: 18px;
  height: 18px;
  appearance: none;
  -webkit-appearance: none;
  border-radius: 6px;
  border: 1px solid var(--input-border);
  background: var(--input-bg);
  display: inline-block;
  outline: none;
  cursor: pointer;
  position: relative;
  transition: all 0.2s ease;
  margin: 0;
}
.remember input:hover {
  border-color: var(--accent-to);
  box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
}
.remember input:checked {
  background: linear-gradient(135deg, var(--accent-from), var(--accent-to));
  border-color: transparent;
}
.remember input:checked::after {
  content: '';
  position: absolute;
  left: 5px;
  top: 2px;
  width: 5px;
  height: 9px;
  border: solid #0b1329;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

/* Submit Button */
.btn {
  padding: 0.85rem 1.4rem;
  border-radius: 12px;
  border: 0;
  font-weight: 700;
  cursor: pointer;
  font-size: 0.9375rem;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
.btn-primary {
  background: linear-gradient(135deg, var(--accent-from), var(--accent-to));
  color: #0b1329;
  box-shadow: 0 8px 20px -4px rgba(56, 189, 248, 0.4);
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px -4px rgba(56, 189, 248, 0.55);
  filter: brightness(1.05);
}
.btn-primary:active {
  transform: translateY(0);
  filter: brightness(0.95);
}
.btn-primary:focus {
  outline: 3px solid rgba(56, 189, 248, 0.35);
}
.cta {
  min-width: 170px;
}

/* Alert / Errors */
.alert {
  padding: 12px 14px;
  border-radius: 12px;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.28);
  color: #f87171;
  font-weight: 600;
  font-size: 0.875rem;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  animation: shake 0.4s ease;
}

/* Language Switcher Bar */
.lang-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-top: 4px;
  padding-top: 14px;
  border-top: 1px solid var(--glass-border);
  font-size: 0.85rem;
}
.lang-link {
  color: var(--text-muted);
  text-decoration: none;
  font-weight: 500;
  padding: 4px 8px;
  border-radius: 8px;
  transition: all 0.2s ease;
}
.lang-link:hover {
  color: var(--text-primary);
  background: rgba(255, 255, 255, 0.06);
}
.lang-link.active {
  color: var(--accent-from);
  font-weight: 700;
  background: rgba(56, 189, 248, 0.12);
}
.lang-sep {
  opacity: 0.4;
  color: var(--text-muted);
}

/* Footer */
.footer {
  text-align: center;
  color: var(--text-muted);
  font-size: 0.8125rem;
  margin-top: 4px;
  line-height: 1.5;
}
.footer a {
  color: var(--text-muted);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
}
.footer a:hover {
  color: var(--accent-to);
}

/* Accessibility & Honeypot */
.hp-field {
  position: absolute !important;
  left: -9999px !important;
  top: auto !important;
  width: 1px !important;
  height: 1px !important;
  overflow: hidden !important;
}
.sr-only {
  position: absolute;
  left: -10000px;
  top: auto;
  width: 1px;
  height: 1px;
  overflow: hidden;
}

/* Keyframes */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(18px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

/* Mobile & Viewport Tuning (Xiaomi / Redmi / POCO Safe) */
@media (max-width: 520px) {
  .page {
    padding: 1.5rem 1rem;
    gap: 1rem;
  }
  .card {
    padding: 24px 20px;
    border-radius: 18px;
  }
  .brand-shield-wrap {
    width: 56px;
    height: 56px;
    border-radius: 16px;
  }
  .row {
    flex-direction: column;
    align-items: stretch;
    gap: 14px;
  }
  .row .right {
    justify-content: stretch;
  }
  .cta {
    width: 100%;
  }
}
  </style>
</head>
<body>
  <div class="page" id="pageRoot">
    <!-- Brand Header -->
    <header class="site-header" role="banner">
      <a href="/login" aria-label="Home" title="<?= htmlspecialchars(__('app_name'), ENT_QUOTES, 'UTF-8') ?>">
        <div class="brand-shield-wrap">
          <i class="fas fa-shield-halved" style="font-size: 1.85rem;" aria-hidden="true"></i>
        </div>
      </a>
    </header>

    <!-- Floating Theme Toggle (Dual-Dark & Light Presets) -->
    <button id="themeToggle" class="theme-toggle" aria-label="Toggle color theme" title="Toggle color theme" type="button">
      <svg id="themeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
        <path d="M12 3v2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
        <path d="M12 19v2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
        <path d="M4.22 4.22l1.42 1.42" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
        <path d="M18.36 18.36l1.42 1.42" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
        <path d="M1 12h2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
        <path d="M21 12h2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
        <path d="M4.22 19.78l1.42-1.42" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
        <path d="M18.36 5.64l1.42-1.42" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
        <circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
      </svg>
    </button>

    <!-- Glassmorphic Login Card -->
    <main class="card" role="main" aria-labelledby="login-title">
      <div class="brand">
        <h1 id="login-title" class="title"><?= htmlspecialchars(__('app_name'), ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="subtitle"><?= htmlspecialchars(__('login_header'), ENT_QUOTES, 'UTF-8') ?></div>
      </div>

      <?php if (!empty($errorMessage)): ?>
        <div class="alert" role="alert">
          <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
          <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      <?php endif; ?>

      <form id="loginForm" action="/login" method="post" autocomplete="on" accept-charset="UTF-8">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">

        <!-- Bot Honeypot Protection -->
        <div class="hp-field" aria-hidden="true">
          <label for="hp_name">Do not fill</label>
          <input id="hp_name" name="hp_name" type="text" autocomplete="off" tabindex="-1">
        </div>

        <div class="field">
          <label for="username" class="sr-only"><?= htmlspecialchars(__('username'), ENT_QUOTES, 'UTF-8') ?></label>
          <div class="input-wrap">
            <i class="fas fa-user input-icon" aria-hidden="true"></i>
            <input id="username" name="username" type="text" class="input" placeholder="<?= htmlspecialchars(__('username'), ENT_QUOTES, 'UTF-8') ?>" required autofocus maxlength="64" autocomplete="username" aria-label="Username">
          </div>
        </div>

        <div class="field">
          <label for="password" class="sr-only"><?= htmlspecialchars(__('password'), ENT_QUOTES, 'UTF-8') ?></label>
          <div class="input-wrap">
            <i class="fas fa-lock input-icon" aria-hidden="true"></i>
            <input id="password" name="password" type="password" class="input" placeholder="<?= htmlspecialchars(__('password'), ENT_QUOTES, 'UTF-8') ?>" required maxlength="128" autocomplete="current-password" aria-label="Password">
          </div>
        </div>

        <input type="hidden" name="auth_method" value="LOCAL">

        <div class="row">
          <div class="left">
            <label class="remember">
              <input type="checkbox" name="remember" id="remember">
              <span><?= htmlspecialchars(__('remember_me'), ENT_QUOTES, 'UTF-8') ?></span>
            </label>
          </div>

          <div class="right">
            <button type="submit" class="btn btn-primary cta" aria-label="<?= htmlspecialchars(__('sign_in'), ENT_QUOTES, 'UTF-8') ?>">
              <i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i>
              <span><?= htmlspecialchars(__('sign_in'), ENT_QUOTES, 'UTF-8') ?></span>
            </button>
          </div>
        </div>

        <!-- Bilingual i18n Switcher -->
        <div class="lang-bar">
          <a href="/locale/en" class="lang-link <?= $currentLang === 'en' ? 'active' : '' ?>">English (EN)</a>
          <span class="lang-sep">|</span>
          <a href="/locale/id" class="lang-link <?= $currentLang === 'id' ? 'active' : '' ?>">Bahasa Indonesia (ID)</a>
        </div>
      </form>

      <!-- Provenance Footer -->
      <div class="footer" role="contentinfo">
        Powered by <a href="https://github.com/alsyundawy/PowerDNS-Admin" target="_blank" rel="noopener noreferrer">PowerDNS-Admin</a>
        <span aria-hidden="true" style="opacity:0.6;margin:0 8px">|</span>
        Modified by <a href="https://alsyundawy.com" target="_blank" rel="noopener noreferrer">ALSYUNDAWY IT SOLUTION</a>
      </div>
    </main>
  </div>

  <script>
    (function(){
      var d = document, ls = window.localStorage || null, root = d.documentElement,
          toggle = d.getElementById('themeToggle'), icon = d.getElementById('themeIcon');

      function applyTheme(t){
        if (!t) t = 'dark';
        root.setAttribute('data-theme', t);
        try { if (ls) ls.setItem('site-theme', t); } catch(e){}
      }

      function getStoredOrDefault(){
        try { if (ls) { var s = ls.getItem('site-theme'); if (s) return s; } } catch(e){}
        return 'dark';
      }

      function updateIcon(t){
        if (!icon) return;
        var isDark = (t === 'dark');
        if (isDark) {
          icon.innerHTML = '<path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" fill="currentColor"/>';
        } else {
          icon.innerHTML = '<g stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"><path d="M12 3v2"/><path d="M12 19v2"/><path d="M4.22 4.22l1.42 1.42"/><path d="M18.36 18.36l1.42 1.42"/><path d="M1 12h2"/><path d="M21 12h2"/><path d="M4.22 19.78l1.42-1.42"/><path d="M18.36 5.64l1.42-1.42"/><circle cx="12" cy="12" r="3.5"/></g>';
        }
      }

      function toggleTheme(){
        var cur = root.getAttribute('data-theme') || 'dark';
        var next = (cur === 'dark' ? 'light' : 'dark');
        applyTheme(next);
        updateIcon(next);
      }

      if (toggle) {
        toggle.addEventListener('click', function(e){ e.preventDefault(); toggleTheme(); }, false);
        toggle.addEventListener('keydown', function(e){ if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleTheme(); } });
      }

      var initial = getStoredOrDefault();
      applyTheme(initial);
      updateIcon(initial);

      var form = d.getElementById('loginForm');
      if (form) {
        form.addEventListener('submit', function(e){
          var hp = d.getElementById('hp_name');
          if (hp && hp.value && hp.value.trim() !== '') {
            e.preventDefault();
            return;
          }
          var u = d.getElementById('username'), p = d.getElementById('password');
          if (u) u.value = u.value.trim().slice(0, 64);
          if (p) p.value = p.value.slice(0, 128);
        }, { passive: false });
      }

      try {
        var u0 = d.getElementById('username');
        if (u0) u0.focus();
      } catch(e){}
    })();
  </script>
</body>
</html>
