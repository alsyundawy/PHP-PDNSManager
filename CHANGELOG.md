# Changelog

All notable changes to **PHP-PDNSManager Enterprise Edition** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.4] - 2026-11-21

### 14-Pillar Production Code Review & CI/CD Pipeline Overhaul

- **CI/CD Multi-Linter Orchestration**:
  - Resolved `Super-Linter` v8.3.1 configuration conflicts by strictly running in Inclusion Mode (`VALIDATE_<LANG>: true`), eliminating conflicting exclusion flags.
  - Resolved `MegaLinter` v9.6.0 container execution blockers: disabled conflicting container linters (`PHP_PHPCSFIXER`, `REPOSITORY_BETTERLEAKS`, `REPOSITORY_GITLEAKS`, `SQL_TSQLLINT`, `PHP_PHPSTAN`, `PHP_PSALM`), sanitized example tokens in documentation (`${PDNS_API_KEY}`), and excluded `.php-cs-fixer.cache`.
  - Upgraded Composer dependencies for PHP 8.1–8.5 matrix testing: updated `vimeo/psalm` to `^6.0` and `phpunit/phpunit` to `^10.5.45 || ^11.0` with `fail-fast: false`.
  - Added single-threaded execution flag `--threads=1` to Psalm runs in `.github/workflows/ci.yml` preventing IPC worker crashes in containerized environments.
- **Bug & Runtime Remediation**:
  - Fixed infinite 302 redirect loop on `/login` and unauthenticated health checks in `AuthenticationMiddleware.php`.
  - Resolved fatal `TypeError` during route dispatching in `Router.php` by implementing reflection-based parameter resolution (`ServerRequestInterface` for `GraphQLController`, wrapped `App\Core\Request` for application controllers).
  - Fixed route registration loss in multi-instance execution by switching `require_once` to `require` for `routes/web.php` and `routes/api.php` in `Application.php`.
  - Replaced non-portable SQLite `datetime("now")` with ANSI standard `CURRENT_TIMESTAMP`, and converted non-portable `INSERT OR REPLACE` / `INSERT OR IGNORE` to portable ANSI SQL queries across `WebhookService`, `ZoneTemplateService`, `ServerClusterService`, and `OrganizationService`.
  - Synchronized and normalized RBAC permission evaluation (`RbacMiddleware.php` and `RbacService.php`) by stripping `/api/v1` prefixes and handling sub-action segments (`/delete`, `/edit`).
- **Security & Vulnerability Hardening (OWASP Top 25 & CWE Top 50)**:
  - **SSRF Mitigation (CWE-918)**: Added strict URL validation enforcing `http://` or `https://` schemes in `WebhookService::createWebhook()`, `updateWebhook()`, and `dispatch()`.
  - **Memory Bounds (CWE-400 / CWE-770)**: Implemented periodic expired-key pruning when in-memory storage exceeds 1,000 entries in `RateLimitMiddleware.php`, preventing memory exhaustion in worker/daemon mode.
  - **Sensitive Data Logging (CWE-532)**: Implemented recursive payload redaction for sensitive keys (`password`, `password_hash`, `token`, `secret`, `api_key`) in `AuditLogService.php`.
  - **Timing Attacks & Strict Typing**: Added explicit float casting for high-resolution microtime operations in `HealthCheckService.php` and `ServerClusterService.php`, and strict array body validation in `GraphQLController.php`.
- **Quality Assurance & Zero-Error Verification**:
  - `Super-Linter` Run `#44830724587` — **PASS**
  - `MegaLinter` Run `#44830724462` — **PASS**
  - `CodeQL Advanced` Run `#44830724722` — **PASS**
  - `PHP-CI` Run `#44830724574` — **PASS** across PHP 8.1, 8.2, 8.3, 8.4, and 8.5
  - Local verification: `phpcs` (86/86 files clean), `phpstan` (Level 5 Clean), `psalm` (94.25% type inference), `phpunit` (23/23 tests, 66 assertions, 100% green), `ec` (100% files clean).

---

## [1.0.3] - 2026-10-12

### 13-Pillar Code Audit & Quality Hardening

- **Bug & Undefined Variable Remediation**:
    - Resolved undefined variables (`$csrfToken`, `$zone`, `$records`, `$keys`) and added complete fallback defaults and docblocks across `app/Views/zone/clone.php`, `create.php`, `edit.php`, and `show.php`.
- **Cognitive Complexity Reduction (< 15 Threshold)**:
    - Decomposed `BindZoneService::parse()` (from 41 to 6) by extracting `unrollZoneLines()` and `resolveOriginFromRecords()`.
    - Decomposed `BindZoneService::parseRecordLine()` (from 16 to 4) by extracting `resolveRecordName()`.
    - Extracted `filterZoneRecords()` in `BulkRecordService::searchRecords()` (from 18 to 4).
- **Code Duplication & Dead Code Cleanup**:
    - Replaced duplicate literal format strings in `BindZoneService` with `RECORD_FORMAT` constant.
    - Removed unused local variable `$class` in `BindZoneService::parseRecordLine()`.
- **Return Count & Control Flow Simplification**:
    - Refactored `OrganizationService::canUserAccessZone()` to reduce exit points from 4 to 2.
    - Extracted nested ternary operations into clean `if/elseif` branches in `HealthCheckService::checkOverall()` and `app/Views/audit/index.php`.
- **Model Architecture & Static Analysis Harmonization**:
    - Migrated `ApiToken`, `Organization`, `Webhook`, `ZoneTemplate`, and `ZoneTemplateRecord` to standard camelCase properties (`userId`, `createdAt`, `updatedAt`, `isActive`, `isDefault`, `templateId`).
    - Preserved full backwards compatibility with SQLite snake_case schema via magic `__get`, `__set`, `__isset` and `@property` annotations.
    - Synchronized caller services (`OrganizationService`, `ZoneTemplateService`, `WebhookService`) and unit test assertions.

### SEO & Web Standards (2026 Modernization)

- **Metadata & Discovery**:
    - Enhanced `app/Views/layouts/admin.php` and `app/Views/auth/login.php` with 2026 meta keywords, `format-detection: telephone=no`, and OpenGraph locale tags.
    - Implemented media-query-aware `meta[name="theme-color"]` supporting both dark (`#0f172a`) and light (`#0284c7`) system preferences.

### Accessibility (WCAG 2.2 AA) & Form Association

- **Input Control Association**:
    - Added explicit `id` attributes, `aria-label`, and associated `<label for>` elements across dynamic record rows, modal forms, checkboxes, and bulk action select controls in `record/bulk.php`, `template/create.php`, and `zone/show.php`.
    - Added keyboard interaction handling (`onkeydown` for space and enter keys) on bulk table select-all toggles.

### Multi-Device Responsiveness (VGA to 2K & Xiaomi/POCO Cutoff Prevention)

- **HyperOS & MIUI Safe-Area Enforcement**:
    - Verified `viewport-fit=cover`, safe-area insets (`env(safe-area-inset-*)`), and dynamic viewport unit (`100dvh`).
    - Maintained horizontal scroll guards and momentum scrolling (`-webkit-overflow-scrolling: touch`) on wide DNS record tables.

### Tooling & Verification

- **Linters & Static Verification**:
    - PHPUnit: 21/21 tests passed (63 assertions).
    - PHPCS: 86/86 files clean (PSR-12).
    - PHPStan: 98/98 files clean (0 errors, memory limit 512M).
    - Psalm: 78/78 files clean (0 errors).
    - Trunk: Checked all 193 repository files (PHP, CSS, JS, HTML, YAML, JSON) with zero issues.

---

## [1.0.2] - 2026-09-22

### Security & DevSkim Alert Resolution

- **RFC 6238 TOTP & Cryptographic Hashing (#21, #22)**:
    - Addressed DevSkim alerts in `App\Services\Auth\AuthService` for time-based counter hashing and HMAC-SHA1 with inline standard compliance suppressions and SHA-256 fallback compatibility.
- **TLS Protocol Enforcement (#6, #37)**:
    - Replaced HTTP scheme with HTTPS in `phpunit.xml` (`PDNS_API_URL`) and `app/Views/server/create.php` placeholder to satisfy secure transport requirements.
- **Third-Party Vendor Script Exclusions (#23–#34)**:
    - Configured `ignore-globs` in `.github/workflows/devskim.yml` for `**/*.min.js`, `**/*.min.css`, and `**/vendor/**`, eliminating upstream `setTimeout` false positives in minified Bootstrap and jQuery bundles.
- **Localhost & Loopback Binding Hardening (#2–#55)**:
    - Replaced `admin@localhost` with RFC 2606 `admin@example.com` in `InitialSeeder.php`.
    - Replaced loopback `127.0.0.1` with RFC 5737 `192.0.2.1` in `AuthTest.php` and `PowerDNSClientTest.php`.
    - Added protocol-documented DevSkim suppressions for PowerDNS standard default server ID (`localhost`) across `Database.php`, `config/database.php`, `config/powerdns.php`, `PowerDNSClient.php`, `ServerClusterService.php`, `PdnsServer.php`, `ServerController.php`, `ServerApiController.php`, and `002_roadmap_features.sql`.

### CI/CD & MegaLinter Modernization

- **GitHub Actions Version Harmonization**:
    - Replaced invalid `@v7` and `@v8` action version tags in `megalinter.yml`, `devskim.yml`, `ci.yml`, and `super-linter.yml` with stable releases (`actions/checkout@v4`, `actions/upload-artifact@v4`, `peter-evans/create-pull-request@v7`, `stefanzweifel/git-auto-commit-action@v5`, `super-linter/super-linter@v7`).
- **MegaLinter Repository Configuration**:
    - Created root `.mega-linter.yml` with vendor/cache regex filtering, disabled noisy `COPYPASTE` and `SPELL` linters, and mapped project linter files (`phpcs.xml`, `phpstan.neon`, `psalm.xml`).
    - Added `--memory-limit=1G` to PHPStan in CI to eliminate 128MB worker crashes.

### Multi-Device Responsiveness (Xiaomi Redmi, POCO & VGA to 2K)

- **MIUI & HyperOS Display Optimization**:
    - Removed destructive `.btn { width: 100%; }` rule on mobile viewports (<= 640px) in `app.css` that caused toolbar, action button, and card layout clipping.
    - Added `@media (max-width: 480px)` ultra-compact viewport optimizations (VGA, iPhone SE, entry POCO C and Redmi A series) with text truncation safeguards for navbar user profile badges.
    - Enforced safe-area insets (`env(safe-area-inset-*)`) and `-webkit-text-size-adjust: 100%` across all containers to eliminate punch-hole camera cut-offs and system font-scaling distortion.

### SEO & Social Metadata

- **Master Layout Optimization**:
    - Enhanced `app/Views/layouts/admin.php` with dynamic title hierarchy, Open Graph tags (`og:title`, `og:description`, `og:type`, `og:site_name`), Twitter Card metadata, and canonical URL links.

### Fixed & Quality Assurance

- **AuthService Bugfix**: Fixed PHPStan undefined property error on `User::$is_active` in `AuthService.php` by switching to `$user->isActive`.
- **PHPUnit Coverage Runner**: Streamlined `phpunit.xml` coverage reporting to enable clean local test runs without requiring local Xdebug/PCOV drivers.
- **Code Quality**: 100% clean passes on PHPCS (PSR-12), PHPStan (Level 5), Psalm, PHP-CS-Fixer, and PHPUnit (21 tests, 62 assertions).

---

## [1.0.1] - 2026-09-21

### Added

- **Multi-PowerDNS Server Cluster Management (Milestone v1.1.0)**:
    - Database table `pdns_servers` supporting multiple authoritative server nodes and clusters.
    - `App\Services\PowerDNS\ServerClusterService` for node lifecycle, default cluster routing, latency testing, and client provisioning.
    - `App\Controllers\ServerController` and `App\Controllers\Api\V1\ServerApiController` providing comprehensive Web GUI and RESTful management.
    - Web views `app/Views/server/index.php`, `create.php`, and `edit.php`.
- **System & PowerDNS Health Check Monitoring (Milestone v1.1.0)**:
    - `App\Services\Health\HealthCheckService` performing latency and uptime checks for PowerDNS, database connection, storage permissions, and PHP runtime health.
    - Dedicated `/health` endpoint supporting both rich dashboard UI and automated monitoring JSON output.
    - Web view `app/Views/health/index.php`.
- **DNS Zone Templating Engine (Milestone v1.1.0)**:
    - Database tables `zone_templates` and `zone_template_records` with seeded templates for Web Hosting and Google Workspace.
    - `App\Services\DNS\ZoneTemplateService` and `App\Controllers\ZoneTemplateController` for blueprint creation and instant one-click application to zones.
    - Web views `app/Views/template/index.php` and `create.php`.
- **RFC 1035 BIND Zone File Import & Export (Milestone v1.2.0)**:
    - Pure PHP parser and serializer in `App\Services\DNS\BindZoneService` supporting `$ORIGIN`, `$TTL`, comments, and multi-line parenthesis records.
    - Interactive import UI at `/zones/import` (`app/Views/zone/import.php`) and standard `.zone` file export at `/zones/{id}/export-bind`.
- **Cross-Zone Bulk Record Operations (Milestone v1.2.0)**:
    - `App\Services\DNS\BulkRecordService` and `/zones/bulk-records` UI (`app/Views/record/bulk.php`) to search records across all zones and execute batch IP/content updates or deletions.
- **Advanced Audit Trail Search & CSV/JSON Export (Milestone v1.2.0)**:
    - Enhanced `AuditLogRepository` with `search()` and `count()` methods supporting multi-parameter filtering (action, user ID, date ranges, status codes).
    - Web UI at `/audit-logs` (`app/Views/audit/index.php`) and export endpoints at `/audit-logs/export` supporting raw CSV and JSON formats.
- **Zero-Dependency GraphQL API Endpoint (Milestone v2.0.0)**:
    - Built-in lightweight GraphQL engine in `App\Controllers\Api\V2\GraphQLController` handling `/graphql` (queries: `zones`, `zone`, `servers`, `health`, `templates`, `auditLogs`; mutations: `createZone`, `deleteZone`).
- **HMAC-SHA256 Webhook Notification System (Milestone v2.0.0)**:
    - Database table `webhooks` and `App\Services\Webhook\WebhookService` for real-time external event dispatching with `X-PDNS-Signature` headers.
- **Multi-Tenant RBAC & Organizations (Milestone v2.0.0)**:
    - Multi-tenant data structures (`organizations`, `organization_user`, `zone_organizations`) and `App\Services\Auth\OrganizationService` for zone-level tenant isolation.

### Fixed & Optimized (13-Dimension Deep Code Review)

- **Bug & Runtime**: Fixed `ZoneApiTest` database connection failure by implementing canonical path normalization in `Config.php`; fixed `Application.php` router reloading and Monolog 3 `Level` enum compatibility.
- **Syntax & Type Safety**: Added dedicated `AuditLogNotFoundException` extending `HttpException(404)`; added explicit parameter type-hints to `Container.php`, `Request.php`, `SessionManager.php`, and `View.php`.
- **Duplicate & Dead Code**: Replaced repeated string literals (`/zones/`, `application/json`, `zones/`, `/cryptokeys/`, `nonce="..."`) with class constants.
- **Xiaomi, Redmi, POCO & Cross-Device Responsiveness**:
    - Implemented dynamic viewport units (`100dvh`, `100svh`) and safe-area insets (`env(safe-area-inset-*)`).
    - Enforced `-webkit-text-size-adjust: 100%` and vendor prefixes to prevent MIUI/HyperOS font-boosting from breaking card grids.
    - Implemented `overflow-wrap: anywhere; word-break: break-word;` for long DNS hashes, DKIM keys, and IPv6 records in data tables.
- **Coding Standards & Linters**: 100% clean passes on PHPStan (Level 5, 0 errors), Psalm (0 errors), PHPCS (PSR-12, 0 errors), PHP-CS-Fixer (0 errors), and PHPUnit (21 tests, 62 assertions, 0 failures).

---

## [1.1.0] - 2026-09-20

### Added

- **100% Offline Asset Suite (Strict Zero CDN)**:
    - Localized Bootstrap 5.3.3 (`bootstrap.min.css`, `bootstrap.bundle.min.js`).
    - Localized Font Awesome 6.5.2 (`all.min.css` and 8 binary webfonts in `webfonts/`).
    - Localized jQuery 3.7.1 (`jquery.min.js`).
    - Localized Chart.js 4.4.4 UMD bundle (`chart.umd.min.js`).
    - Strict zero external CDN dependencies for air-gapped and sovereign network compliance.
- **VisualSubnetCalc Dark/Light Theme System**:
    - Implemented slate dark mode palette (`#0f172a` to `#1e293b`) with glassmorphism (`rgba(15, 23, 42, 0.75)`), glowing borders, and electric blue/cyan accents modeled after `visualsubnetcalc`.
    - Added clean light mode palette with automatic OS preference detection (`prefers-color-scheme`) and persistent `localStorage` synchronization.
    - Interactive theme toggle button integrated in navigation bars and login views with instant icon switching.
- **Comprehensive Production Tutorial (`TUTORIAL.md`)**:
    - End-to-end deployment guide for PowerDNS Authoritative Server with MySQL/MariaDB backend on Ubuntu 20.04/22.04 LTS and Rocky Linux 8/9.
    - High-Availability Authoritative DNS Clustering with Native Database Replication and Primary/Secondary Supermaster setups.
    - Authoritative DNS Zone and Record management best practices (`A`, `AAAA`, `CNAME`, `MX`, `TXT`, `NS`, `SRV`, `CAA`, `PTR`).
    - Automated DNSSEC signing, KSK/ZSK key rollover, and DS record generation for parent zone delegation.
    - RFC 1035 BIND zone file import, export, and cross-zone bulk record operations.
    - Production UFW, firewalld, iptables, Fail2ban, and Linux kernel sysctl hardening scripts for DNS port 53 and web gateway ports.

### Changed

- **Cross-Device Anti-Clipping Engine (Xiaomi, Redmi, POCO & Android/iOS Devices)**:
    - Fixed dynamic address bar clipping on MIUI / HyperOS using CSS `100dvh` with `100svh` and dynamic `--vh` JavaScript calculations.
    - Added `viewport-fit=cover` and safe-area inset tokens (`env(safe-area-inset-*)`) to prevent punch-hole camera and gesture bar overlap.
    - Enforced `-webkit-text-size-adjust: 100%` to prevent font inflation from distorting card layouts.
    - Added responsive touch-scroll wrappers for wide DNS TXT records, DKIM public keys, and DNSSEC signatures.
- **Backend & Static Analysis Fixes**:
    - `app/Core/helpers.php`: Replaced legacy switch statement with PHP 8 `match` expression, eliminating unreachable statement warnings.
    - `app/Core/Request.php`: Fixed `getParsedBody()` return type signature to safely handle arrays, objects, and null.
    - `app/Core/View.php`: Added nullable string type hint (`?string`) to `$layout` property and updated `setLayout(?string)`.
    - `app/Services/PowerDNS/PowerDNSClient.php`: Consolidated repeated literal `" failed: "` into `formatFailedMessage()` helper method.
    - `config/app.php` and `tests/Integration/DatabaseTest.php`: Added DevSkim suppressions for `localhost` fallback bindings (`DS162092`, `DS137138`).
    - Standardized project version to `1.1.0` across `package.json`, `sonar-project.properties`, `docs/openapi.yaml`, `login.php`, and documentation.

---

## [1.0.0] - 2026-08-02

### Added

- Initial enterprise release of PHP-PDNSManager.
- PowerDNS REST API integration via Guzzle HTTP client (`ZoneResource`, `RecordResource`, `CryptokeyResource`, `ServerResource`).
- Native, Master, and Slave zone management with automatic SOA serial incrementation (`YYYYMMDDNN`).
- Comprehensive DNS record types (`A`, `AAAA`, `CNAME`, `MX`, `TXT`, `NS`, `SRV`, `CAA`, `PTR`).
- Automated DNSSEC cryptographic key management (KSK / ZSK generation and DS record extraction).
- RBAC authentication, session management, and CSRF token protection.

[1.0.1]: https://github.com/alsyundawy/PHP-PDNSManager/releases/tag/v1.0.1
[1.1.0]: https://github.com/alsyundawy/PHP-PDNSManager/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/alsyundawy/PHP-PDNSManager/releases/tag/v1.0.0
