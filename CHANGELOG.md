# Changelog

All notable changes to **PHP-PDNSManager Enterprise Edition** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
    - Setup guide for simple local recursive resolvers (Unbound / PowerDNS Recursor) strictly without Response Policy Zones (RPZ) for mail server DNSBL lookups.
    - Master Zimbra Mail Server integration guide covering authoritative DNS records (`A`, `AAAA`, `MX`, `SPF`, `DKIM`, `DMARC`, `PTR` in `in-addr.arpa`, Autodiscover).
    - Zimbra cross-OS migration runbook (CentOS to Rocky / Ubuntu) synthesizing official and Indonesian community best practices (Vavai/Excellent & Imanudin).
    - Zimbra CVE timeline & mitigations (CVE-2018-6882 through CVE-2024-45519 Postjournal RCE).
    - Production UFW, firewalld, iptables, Fail2ban, and Linux kernel sysctl hardening scripts.

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
