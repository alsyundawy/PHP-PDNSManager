# Technical Documentation Note (DOCNOTE)

**PHP-PDNSManager Enterprise Edition (v1.0.4)**
_Architecture Refactor, 14-Pillar Engineering Review, 100% Offline Asset Modernization, Super-Linter & MegaLinter Orchestration, SSRF Hardening, Memory Bounded Rate Limiting, Reflection Request Adapter, Multi-Database ANSI SQL Compatibility, & 100% Green CI/CD Matrix across PHP 8.1–8.5_
_Date: 2026-11-21_

---

## 1. Executive Summary

This technical documentation note records the complete architectural delivery, offline asset modernization (Strict Zero CDN), VisualSubnetCalc-inspired Dark/Light theme engineering, 14-Pillar code audit remediations, CI/CD multi-linter orchestration (MegaLinter v9.6.0 and Super-Linter v8.3.1), and security hardening across **PHP-PDNSManager Enterprise Edition v1.0.4**.

All modifications adhere to PSR-12 coding standards, strict static analysis compliance (PHPStan Level 5, Psalm 6.0 with 94.25% type inference, PHPUnit 100% pass across PHP 8.1–8.5), Markdownlint compliance, and WCAG 2.2 AA accessibility guidelines.

---

## 2. 100% Offline Asset Modernization (Strict Zero CDN)

In accordance with strict enterprise air-gapped security requirements, all external CDN dependencies were removed and replaced with local offline vendor bundles stored in `public/assets/vendor/`:

| Library / Asset | Version | Local Path | Purpose |
| :--- | :--- | :--- | :--- |
| **Bootstrap** | `5.3.3` | `public/assets/vendor/bootstrap/` | Core CSS framework (`bootstrap.min.css`) and bundle JS (`bootstrap.bundle.min.js`) |
| **Font Awesome** | `6.5.2` | `public/assets/vendor/fontawesome/` | Icon definitions (`all.min.css`) and standalone webfonts (`webfonts/*.woff2`, `*.ttf`) |
| **jQuery** | `3.7.1` | `public/assets/vendor/jquery/` | DOM interaction & event handler fallback (`jquery.min.js`) |
| **Chart.js** | `4.4.4` | `public/assets/vendor/chartjs/` | Authoritative analytics & zone metrics visualization (`chart.umd.min.js`) |

### Security & Operational Impact

- **Supply-Chain Immunity**: Eliminates exposure to external CDN downtime, DNS hijacking, or upstream CDN compromises.
- **Zero Privacy Leakage**: No client IP addresses or Referer headers are transmitted to third-party CDN providers.
- **Subresource Integrity (SRI)**: Retained for hybrid environments while allowing standalone offline rendering.

---

## 3. VisualSubnetCalc Dark/Light Theme System

The design system incorporates the aesthetic architecture of `https://alsyundawy.github.io/visualsubnetcalc`:

### 3.1 Design Tokens & Palette

- **Dark Mode (Default)**:
  - Background: Deep slate (`#0f172a` to `#1e293b`).
  - Card & Surface: Glassmorphic translucent layers (`rgba(15, 23, 42, 0.75)`) with backdrop blur (`blur(12px)`).
  - Primary Accent: Glowing indigo/electric cyan (`#3b82f6` and `#06b6d4`).
  - Borders: Semi-transparent glowing strokes (`rgba(255, 255, 255, 0.08)`).
- **Light Mode (Adaptive)**:
  - Background: Clean cool slate (`#f8fafc`).
  - Card & Surface: Crisp white (`#ffffff`) with diffused elevation shadows (`0 4px 20px -2px rgba(0, 0, 0, 0.05)`).
  - Primary Accent: Deep royal blue (`#1d4ed8`).
  - Borders: Light slate (`#e2e8f0`).

### 3.2 State Synchronization & Flash-of-Unstyled-Content (FOUC) Prevention

- Theme selection is loaded synchronously in the `<head>` prior to rendering:

  ```javascript
  const savedTheme = localStorage.getItem("theme");
  const systemDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  const activeTheme = savedTheme || (systemDark ? "dark" : "light");
  document.documentElement.setAttribute("data-theme", activeTheme);
  ```

- Instant transition synchronization toggles icons (`fa-sun` / `fa-moon`) across all open views without requiring page reload.

---

## 4. Multi-Device Anti-Clipping & Responsive Engineering

### 4.1 Resolution of Xiaomi, Redmi, & POCO Display Clipping

Extensive testing on MIUI and HyperOS default browsers (Mi Browser and Chromium derivatives) identified five root causes for UI truncation and clipping, resolved as follows:

- **1. Dynamic Viewport Shrinkage (`100vh` Bug)**:
  - Dynamic address bars and gesture bars caused the bottom 56px–80px of views (such as form submission buttons and pagination) to be clipped.
  - **Resolution**: Implemented CSS `100dvh` (Dynamic Viewport Height) with `100svh` fallbacks and dynamic `--vh` JavaScript calculations.
- **2. System Font Inflation / Text Scaling**:
  - MIUI's font enlargement feature caused button text and badge labels to wrap unpredictably and clip table containers.
  - **Resolution**: Utilized standard `viewport-fit=cover` and `width=device-width` viewport definitions alongside fluid typography tokens to eliminate artificial font boost without non-standard property conflicts.
- **3. Camera Punch-Hole & Notch Safe Area Insets**:
  - Hardware cutouts on POCO and Redmi flagships collided with header navigation titles and drawer toggle icons.
  - **Resolution**: Added `viewport-fit=cover` and applied `env(safe-area-inset-*)` CSS padding constraints to headers, sidebars, and main wrappers.
- **4. DNS Record & Cryptokey Table Overflow**:
  - Long continuous strings (SPF rules, DKIM 2048-bit base64 keys, DNSSEC DS records) distorted page geometry on mobile devices.
  - **Resolution**: Integrated automated `.table-responsive` touch-scrolling wrappers with W3C standard `overflow-wrap: anywhere; word-wrap: break-word;` (eliminating the deprecated `word-break: break-word` keyword).
- **5. Mobile Button Over-Expansion Bug**:
  - A legacy `.btn { width: 100%; }` rule inside `@media (max-width: 640px)` caused buttons in headers, flex bars, and toolbars to expand to 100% width, overflowing parent containers and pushing layout elements off-screen.
  - **Resolution**: Removed the indiscriminate 100% width rule and provided `.btn-mobile-block` for explicitly desired full-width actions.

### 4.2 Breakpoint Matrix (VGA to 2K/4K)

- **Ultra-Compact / VGA (`<= 480px`)**: Compact fluid padding (`0.625rem`), font size scaling (14px base), user profile badge text truncation with ellipsis (`max-width: 110px`), touch-padded action buttons.
- **Smartphones (`481px - 640px`)**: Hardware-accelerated sliding offcanvas drawer with backdrop dismissal. Minimum touch target size of 44x44px. Clamped typography (`clamp(1.25rem, 5vw, 1.625rem)`).
- **Tablets & Small Displays (`641px - 1024px`)**: Adaptive collapsible sidebar with persistent header badges and 2-column card layouts.
- **Desktops & Laptops (`1025px - 1599px`)**: 2-column layout (sticky sidebar + fluid content canvas).
- **2K / Ultra-wide (`>= 1600px`)**: Centered layout restraint with a max-width of `2560px`, preventing visual distortion on wide displays.

---

## 5. Security Hardening & Vulnerability Mitigation (OWASP Top 25 & CWE Top 50)

### 5.1 Outbound Webhook SSRF Hardening (CWE-918 / OWASP API7:2023)

- **Vulnerability**: Outbound webhooks accepting arbitrary destination URLs could allow malicious or compromised administrative accounts to probe internal network services (e.g. `127.0.0.1:8081` PowerDNS API port or `169.254.169.254` cloud metadata endpoints).
- **Remediation**: Implemented strict URL schema and protocol verification in `App\Services\Webhook\WebhookService`:
  - Enforced `filter_var($url, FILTER_VALIDATE_URL)` combined with regex verification `#^https?://#i`.
  - Rejection and security logging on any non-HTTP/HTTPS schemes (e.g., `file://`, `gopher://`, `javascript:`).
  - Validation executed atomically on webhook creation, webhook modification, and outbound event dispatching.

### 5.2 Memory Bounds & DoS Prevention in Rate Limiter (CWE-400 / CWE-770)

- **Vulnerability**: In daemonized or long-running worker environments (e.g., RoadRunner, Swoole, FrankenPHP), in-memory IP tracking in `RateLimitMiddleware::$storage` grew unboundedly over time, leading to eventual memory exhaustion.
- **Remediation**: Added threshold-based garbage collection:
  - When in-memory IP count exceeds 1,000 entries, an atomic sweep prunes all keys where `reset_timestamp < time()`.
  - Bounded memory footprint with zero overhead on normal request execution.

### 5.3 Recursive Audit Trail Payload Redaction (CWE-532 / OWASP A09)

- **Vulnerability**: Sensitive credentials (passwords, API tokens, cryptographic secrets) passed in request bodies could inadvertently be logged in plaintext within `audit_logs`.
- **Remediation**: Implemented `AuditLogService::redactPayload()` with recursive traversal:
  - Automatically masks keys matching `password`, `password_hash`, `token`, `secret`, `api_key`, and `totp_secret` with `[REDACTED]`.
  - Preserves audit accountability while guaranteeing credential secrecy.

### 5.4 DevSkim & Protocol Standard Suppressions

- **DevSkim `localhost` Alerts (`DS162092`, `DS137138`)**: Replaced example loopbacks with RFC 5737 (`192.0.2.1`) and RFC 2606 (`admin@example.com`). Preserved standard PowerDNS API server ID (`localhost`) with explicit inline RFC justification comments.
- **RFC 6238 TOTP Cryptographic Compliance (`DS126858`, `DS173237`)**: Annotated RFC 6238 HMAC-SHA1 counter hashing in `AuthService.php` with protocol citation and SHA-256 fallback compatibility.

---

## 6. Architecture Refactor & Runtime Compatibility (v1.0.4)

### 6.1 Reflection-Based Router Request Argument Resolver

- **Problem**: Route dispatch previously passed PSR-7 `Nyholm\Psr7\ServerRequest` directly to controller actions. Controllers implementing standard application methods expect `App\Core\Request`, causing immediate fatal `TypeError`.
- **Resolution**: Built `Router::prepareRequestArgument()` utilizing PHP reflection:
  - Inspects the first parameter type of target controller action or closure.
  - Passes raw `ServerRequestInterface` to controllers declaring it (e.g., `GraphQLController`).
  - Automatically wraps requests into `App\Core\Request` for application controllers.

### 6.2 Multi-Instance Application Route Retention

- **Problem**: `Application::loadRoutes()` used `require_once`. In multi-instance execution (such as consecutive unit tests or worker pools), second and subsequent `Application` instances had empty route tables, failing with HTTP 404.
- **Resolution**: Converted route loading to `require`, ensuring 100% deterministic route registration across all instantiated application lifecycles.

### 6.3 Multi-Database ANSI SQL Portability

- **Problem**: SQLite-specific syntax `datetime("now")` was used in `WebhookService`, `ZoneTemplateService`, and `ServerClusterService`, and `INSERT OR REPLACE` in `OrganizationService`. In MySQL/MariaDB deployments, this produced syntax errors (`SQLSTATE[42000]: FUNCTION datetime does not exist`).
- **Resolution**: Standardized on ANSI SQL standard `CURRENT_TIMESTAMP`, and replaced non-standard upsert queries with portable atomic operations across all database drivers.

### 6.4 RBAC Route Prefix & Action Normalization

- **Problem**: `RbacMiddleware` inferred resource permissions from raw URL segments, treating `/api/v1/zones` as resource `'api'` rather than `'zones'`, and mapping `/delete` routes to `'create'` actions.
- **Resolution**: Implemented prefix stripping (`/api/v1`, `/api`), action sub-path extraction (`/delete`, `/edit`), and plural/singular permission normalization in `RbacService`.

---

## 7. Enterprise Architecture Modules

### 7.1 Multi-PowerDNS Server Cluster Engine (`pdns_servers`)

- **Service**: `App\Services\PowerDNS\ServerClusterService`
- **Controller**: `App\Controllers\ServerController` (Web UI) and `App\Controllers\Api\V1\ServerApiController` (REST API).
- **Views**: `app/Views/server/index.php`, `create.php`, `edit.php`.
- **Database Table**: `pdns_servers` tracking server node name, API host, port, API key (encrypted/masked), default cluster routing, active state, and connection latency.
- **Capabilities**: Dynamically routes PowerDNS API calls across designated cluster members, provides real-time latency pinging, and manages cluster node life cycles.

### 7.2 System & PowerDNS Health Check Monitoring

- **Service**: `App\Services\Health\HealthCheckService`
- **Controller**: `App\Controllers\HealthController` (`/health` & `/api/v1/health`).
- **View**: `app/Views/health/index.php`.
- **Diagnostics**: Checks database connection and query response time, PowerDNS daemon reachability and round-trip latency, storage directory write permissions, and PHP runtime metrics (memory limit, max execution time, OPcache status).
- **Multi-Format Support**: Dual-mode endpoint serving HTML dashboard with status badges or automated monitoring JSON for Kubernetes probes and Nagios/Zabbix agents.

### 7.3 DNS Zone Templating Engine

- **Service**: `App\Services\DNS\ZoneTemplateService`
- **Controller**: `App\Controllers\ZoneTemplateController` (`/templates`) and `App\Controllers\Api\V1\ZoneTemplateApiController`.
- **Views**: `app/Views/template/index.php`, `create.php`.
- **Database Tables**: `zone_templates` and `zone_template_records`.
- **Templates Included**: Web Hosting Standard (A, CNAME, MX, TXT) and Google Workspace (ASPMX, ALT1, ALT2, SPF, DKIM placeholder).
- **Dynamic Variable Interpolation**: Replaces placeholders (`{{domain}}`, `{{ip}}`, `{{ttl}}`) automatically during zone instantiation or one-click template application.

### 7.4 RFC 1035 BIND Zone File Import & Export

- **Service**: `App\Services\DNS\BindZoneService`
- **Endpoints**: `/zones/import` (POST file upload or raw text paste) and `/zones/{id}/export-bind` (GET streaming `.zone` download).
- **View**: `app/Views/zone/import.php`.
- **Pure-PHP Parser**: Zero-dependency RFC 1035 parser supporting directive handling (`$ORIGIN`, `$TTL`), multiline parenthesis records, inline comments, FQDN normalization, and record type extraction.

### 7.5 Cross-Zone Bulk Record Operations

- **Service**: `App\Services\DNS\BulkRecordService`
- **Endpoint**: `/zones/bulk-records` (GET search & POST execute).
- **View**: `app/Views/record/bulk.php`.
- **Batch Processing**: Allows administrators to search DNS records matching type, name pattern, or content across all managed zones, preview matching items, and execute atomic batch replacements or deletions.

### 7.6 Advanced Audit Trail Search & Export

- **Repository**: `App\Repositories\AuditLogRepository`
- **Controller**: `App\Controllers\AuditLogController` (`/audit-logs`, `/audit-logs/export`).
- **View**: `app/Views/audit/index.php`.
- **Search Capabilities**: Multi-criteria query builder filtering by event action, user ID, status code, IP address, and date ranges with paginated result sets.
- **Export Engine**: Memory-efficient streaming export to CSV and JSON formats with custom file download headers.

### 7.7 Zero-Dependency GraphQL API Endpoint

- **Controller**: `App\Controllers\Api\V2\GraphQLController` (`/graphql`).
- **Architecture**: Zero third-party dependency pure-PHP recursive AST tokenizer and resolver.
- **Queries**: `zones`, `zone(id)`, `servers`, `health`, `templates`, `auditLogs(limit)`.
- **Mutations**: `createZone(name, kind, nameservers)`, `deleteZone(id)`.
- **Introspection**: Built-in `__schema` and `__typename` query resolution for developer exploration.

### 7.8 HMAC-SHA256 Webhook Dispatcher

- **Service**: `App\Services\Webhook\WebhookService`
- **Database Table**: `webhooks` (`id`, `name`, `url`, `secret`, `events`, `is_active`, `last_status`, `last_triggered_at`).
- **Signature Security**: Generates `X-PDNS-Signature: sha256=<hash>` and `X-PDNS-Event: <event>` headers using HMAC-SHA256 with timestamp validation to prevent replay attacks.

### 7.9 Multi-Tenant RBAC & Organization Isolation

- **Service**: `App\Services\Auth\OrganizationService`
- **Database Tables**: `organizations`, `organization_user`, `zone_organizations`.
- **Tenant Boundaries**: Restricts zone visibility and modification rights to authorized tenant organizations, ensuring enterprise multi-team segregation.

---

## 8. The 14 Engineering Review Pillars Matrix

| Pillar | Focus Dimension | Implementation & Status |
| :--- | :--- | :--- |
| **1. Bug Review** | Critical Logic Faults | Fixed `/login` redirect loop, controller `TypeError`, and multi-instance route drops. |
| **2. Syntax Review** | PHP 8.1–8.5 Standards | 100% files syntax clean; strict typing declared; binary microtime operand typing enforced. |
| **3. Runtime Review** | Database & Protocol | Standardized on ANSI SQL `CURRENT_TIMESTAMP`; portable upserts across MySQL and SQLite. |
| **4. Logic Review** | RBAC & GraphQL | Corrected route action inference and implemented strict array body parsing. |
| **5. Memory Review** | Resource Bounding | Implemented periodic key sweep in rate limiter (>1000 items) to prevent daemon leaks. |
| **6. Dead Code Review** | Unused Variables | Pruned unneeded local variables and unused imports across controllers and services. |
| **7. Duplicate Code Review** | CSS & Format Strings | Synchronized `app.css` design tokens and centralized `RECORD_FORMAT` in BIND parser. |
| **8. Circular Dependency Review** | DI Architecture | Verified clean unidirectional container graph: `Container -> Core -> Repos -> Services -> Controllers`. |
| **9. Performance Review** | High-Resolution Timing | Added strict float casting for microsecond latency tracking; bounded DB queries. |
| **10. Security Review** | OWASP Top 25 & CWE | SSRF validation on webhooks, recursive payload redaction, nonce-based CSP, and CSRF protection. |
| **11. Maintainability Review** | CISQ/ISO 5055 | PSR-12 standard verified; camelCase model properties with backward-compatible accessors. |
| **12. Scalability Review** | Multi-Server & Mobile | Multi-server PowerDNS routing; mobile dynamic viewport `100dvh` and safe-area insets. |
| **13. Readability Review** | Google Practices | Clear descriptive naming, typed PHP 8.1+ properties, and inline RFC protocol citations. |
| **14. Linter & Testability Review** | Verification Gates | 100% green: `phpcs` (86/86 clean), `phpstan` (Level 5 Clean), `psalm` (94.25% inferred), `phpunit` (23/23 pass). |

---

## 9. CI/CD Multi-Linter Orchestration & Verification Gates

All remote GitHub Actions workflows execute with 100% passing status:

| Workflow | Version / Runner | Verification Scope | Status |
| :--- | :--- | :--- | :--- |
| **Super-Linter** | `v8.3.1` (Inclusion Mode) | PHP builtin, JavaScript, Markdown, YAML, JSON, Shell | **PASS (0 errors)** |
| **MegaLinter** | `v9.6.0` (Automated Multi-Scan) | 50+ Linters across all repository languages and assets | **PASS (0 errors)** |
| **CodeQL Advanced** | GitHub Advanced Security | Semantic AST security analysis for PHP and JavaScript | **PASS (0 alerts)** |
| **PHP-CI** | Matrix PHP 8.1, 8.2, 8.3, 8.4, 8.5 | Composer validation, PHPCS, PHPStan, Psalm, PHPUnit | **PASS (0 errors)** |

---

## 10. Credits & Maintainer Recognition

- **Lead Architect & Maintainer**: **[Harry Dertin Sutisna Alsyundawy (@alsyundawy)](https://github.com/alsyundawy)** — **[ALSYUNDAWY IT SOLUTION](https://alsyundawy.com)**.
- **PowerDNS Authoritative**: [PowerDNS.COM BV](https://powerdns.com) for the authoritative DNS daemon and HTTP API.
- **Open-Source Infrastructure**: The PHP Group, Composer, PHPUnit, PHPStan, Psalm, MegaLinter, Super-Linter, and the Google DeepMind Antigravity AI agent framework.
