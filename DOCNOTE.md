# Technical Documentation Note (DOCNOTE)

**PHP-PDNSManager Enterprise Edition (v1.0.6)**
_Technical Architecture & Production Engineering Note: PowerDNS Native Control Operations (AXFR/NOTIFY/Rectify), TSIG Management (RFC 2845/8945), Zone Metadata Directives, Auto-PTR Engine, DNS Security & Deliverability Toolbox (SPF/DKIM/DMARC/RBL), AES-256-GCM Encrypted Backups, Global Command Palette (Ctrl+K), CLI Runner (bin/pdns), & 100% Green CI/CD Matrix across PHP 8.1–8.5_
_Date: 2026-11-21_

---

## 1. Executive Summary & Release Evolution

This technical documentation note records the architectural evolution, security hardening, multi-linter CI/CD orchestration, and production engineering across **PHP-PDNSManager Enterprise Edition v1.0.5**.

From initial scaffolding to enterprise-grade stability, PHP-PDNSManager provides a security-hardened Web GUI, versioned REST API gateway (`/api/v1`), and zero-dependency GraphQL query engine (`/graphql`) specifically engineered for PowerDNS Authoritative Server. All implementations strictly adhere to PSR standards (PSR-7, PSR-11, PSR-14, PSR-15, PSR-17, PSR-18), zero external CDN dependencies, strict typing (`declare(strict_types=1);`), and 100% green multi-linter verification across PHP 8.1, 8.2, 8.3, 8.4, and 8.5.

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

The design system incorporates the aesthetic architecture of [Visual Subnet Calculator](https://alsyundawy.github.io/visualsubnetcalc):

### 3.1 Design Tokens & Palette

- **Dark Mode (Default)**:
  - Background: Deep obsidian slate (`#0f172a` to `#1e293b`).
  - Card & Surface: Glassmorphic translucent layers (`rgba(15, 23, 42, 0.75)`) with backdrop blur (`blur(12px)`).
  - Primary Accent: Glowing electric cyan and royal blue (`#3b82f6` and `#06b6d4`).
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

### 4.1 Resolution of Mobile Display Clipping

Testing on mobile browsers (MIUI/HyperOS Mi Browser, Chromium, WebKit iOS) identified root causes for UI truncation and clipping, resolved as follows:

- **1. Dynamic Viewport Shrinkage (`100vh` Bug)**:
  - Dynamic address bars and gesture bars caused the bottom 56px–80px of views (such as form submission buttons and pagination) to be clipped.
  - **Resolution**: Implemented CSS `100dvh` (Dynamic Viewport Height) with `100svh` fallbacks and dynamic `--vh` JavaScript calculations.
- **2. Safe Area Insets (Notch & Gesture Bar Padding)**:
  - Full-screen displays truncated sticky headers and action bars underneath camera cutouts.
  - **Resolution**: Enforced `viewport-fit=cover` and applied CSS padding:
    `padding-top: env(safe-area-inset-top, 0px); padding-bottom: env(safe-area-inset-bottom, 0px);`.
- **3. Broad Table Horizontal Anti-Clipping**:
  - Wide DNS records (such as DKIM 2048-bit TXT strings, RFC 9460 HTTPS parameters, and DNSSEC keys) broke table boundaries on mobile screens.
  - **Resolution**: Wrapped table containers in `-webkit-overflow-scrolling: touch` with rounded boundary wrappers and responsive font scaling.

---

## 5. Strict Authoritative DNS Invariant (No Cache-Poisoning Vectors)

A core invariant of PHP-PDNSManager is that it manages **Authoritative DNS only**. It does not perform recursive DNS resolution, caching resolver lookups, or forwarder resolution:

- **PowerDNS Authoritative Only**: Communicates exclusively with the authoritative daemon (`pdns_server`), which authoritative-answers for configured zones via backend databases (`gmysql`, `gpgsql`, `gsqlite3`).
- **Elimination of Cache Poisoning (CVE-2008-1447, Kaminsky Attack)**: Because authoritative servers only serve authoritative data and never cache recursive query results, cache-poisoning vulnerabilities do not apply to this system.
- **DNS Amplification Protection**: Directs operators to configure response rate limiting (`response-rate-limit`) in `pdns.conf` and disallows any open-resolver configurations.

---

## 6. Multi-Server PowerDNS Routing & Failover Architecture

### 6.1 Cluster Abstraction (`ServerClusterService`)

- Manages multiple PowerDNS nodes defined in the `pdns_servers` database table.
- Each cluster node maintains:
  - `name`: Human-readable identifier.
  - `api_url`: Internal REST API endpoint (e.g., `http://127.0.0.1:8081`).
  - `api_key`: Shared secret passed via `X-API-Key` header.
  - `server_id`: Target PowerDNS server ID (default: `localhost`).
  - `is_active` & `is_default`: Routing status flags.
- **Latency Testing & Health Probe**:
  - Measures API round-trip latency using high-resolution microsecond timers:
    `$latency = round(((float) microtime(true) - (float) $start) * 1000.0, 2);`.
  - Automatically classifies daemon status into `Optimal` (<50ms), `Degraded` (50–200ms), `Slow` (>200ms), or `Offline`.

---

## 7. RFC 9460 Modern DNS Records Architecture (`HTTPS` & `SVCB`)

### 7.1 Protocol Overview

PowerDNS Authoritative Server supports modern Service Binding Resource Records defined by the IETF in RFC 9460:

- **`HTTPS` (RR 65)**: Optimized binding record for HTTP and HTTPS origins, supporting Apex Aliasing (replacing CNAME at zone root) and parameter negotiation (ALPN `h2,h3`, port 443, IPv4/IPv6 hints).
- **`SVCB` (RR 64)**: General-purpose service binding for other application protocols (e.g., DNS-over-HTTPS, DNS-over-TLS on port 853).

### 7.2 Implementation in `BindZoneService`

- Expanded `$knownTokens` in `resolveRecordName()` to include:
  `'HTTPS'`, `'SVCB'`, `'DS'`, `'DNSKEY'`, `'TLSA'`, `'SSHFP'`, `'CAA'`, `'NAPTR'`, `'LOC'`, `'URI'`, `'DNAME'`, `'ALIAS'`, `'SPF'`, `'RP'`, and `'HINFO'`.
- Supports parsing standard presentation format:
  `@ IN HTTPS 1 . alpn="h2,h3" port=443`
  `@ IN HTTPS 0 cdn.example.net.` (AliasMode apex redirection)
- Web UI in `app/Views/zone/show.php` features direct creation dropdowns for all modern RR types.

---

## 8. RFC 9432 DNS Catalog Zones Management Architecture

### 8.1 Protocol Overview

DNS Catalog Zones (RFC 9432) automate the provisioning and synchronization of member zones across primary and secondary nameservers using standard DNS zone transfers (AXFR/IXFR):

- **Producer (`kind: "Producer"`)**: Resides on primary nameserver; automatically generates internal records representing member zones.
- **Consumer (`kind: "Consumer"`)**: Resides on secondary nameserver; pulls the catalog zone and provisions member zones automatically.

### 8.2 Implementation in `ZoneController` and Views

- **`ZoneController::create()` & `edit()`**: Extracts and persists the `catalog` association string.
- **`app/Views/zone/create.php` & `app/Views/zone/edit.php`**: Exposes `Producer` and `Consumer` kinds and explicit `catalog` domain input field.
- Seamlessly passes `"catalog": "catalog.example.com."` to the PowerDNS HTTP API v1 payload.

---

## 9. Real-Time Cluster Telemetry & Health Monitoring

- Injected `ServerClusterService` and `ZoneService` into `DashboardController` using reflection autowiring.
- Real-time calculations:
  - `$totalZones`: Live count of authoritative zones.
  - `$activeServers`: Count of active PowerDNS nodes.
  - `$serverHealth`: Ping connectivity badge (`OK`, `Degraded`, `Offline`) with graceful offline fallback.
- Dynamic visual widgets rendered in `app/Views/dashboard/index.php`.

---

## 10. The 14 Engineering Review Pillars Matrix

| Pillar | Focus Dimension | Implementation & Status |
| :--- | :--- | :--- |
| **1. Bug Review** | Critical Logic Faults | Fixed `/login` redirect loop, controller route dispatch `TypeError`, and missing `catalog` extraction in `ZoneController`. |
| **2. Syntax Review** | PHP 8.1–8.5 Standards | 100% files syntax clean (`php -l`); strict typing declared; binary microtime operand typing enforced. |
| **3. Runtime Review** | Database & Protocol | Standardized on ANSI SQL `CURRENT_TIMESTAMP`; portable upserts across MySQL and SQLite. Verified reflection autowiring. |
| **4. Logic Review** | RBAC & GraphQL | Corrected route action inference and implemented strict array body parsing. BIND token resolver handles apex and relative names. |
| **5. Memory Review** | Resource Bounding | Implemented periodic key sweep in rate limiter (>1000 items) to prevent daemon memory leaks. |
| **6. Dead Code Review** | Unused Variables | Pruned unneeded local variables and unused imports across controllers and views. |
| **7. Duplicate Code Review** | CSS & Format Strings | Synchronized `app.css` design tokens and centralized `RECORD_FORMAT` in BIND parser. |
| **8. Circular Dependency Review** | DI Architecture | Verified clean unidirectional container graph: `Container -> Core -> Repositories -> Services -> Controllers`. |
| **9. Performance Review** | High-Resolution Timing | Added strict float casting for microsecond latency tracking; bounded DB queries and API timeouts (5.0s / 15.0s). |
| **10. Security Review** | OWASP Top 25 & CWE | SSRF validation on webhooks (CWE-918), recursive payload redaction (CWE-532), nonce-based CSP, and CSRF protection. |
| **11. Maintainability Review** | CISQ/ISO 5055 | PSR-12 standard verified (86/86 files); camelCase model properties with backward-compatible array accessors. |
| **12. Scalability Review** | Multi-Server & Mobile | Multi-server PowerDNS routing; mobile dynamic viewport `100dvh` and safe-area insets. |
| **13. Readability Review** | Google Practices | Clear descriptive naming, typed PHP 8.1+ properties, and inline RFC protocol citations. |
| **14. Linter & Testability Review** | Verification Gates | 100% green: `phpcs` (86/86 clean), `phpstan` (Level 5 Clean), `psalm` (94.33% inferred), `phpunit` (41/41 pass, 125 assertions). |

---

## 10. Version 1.0.6 Architecture & Enterprise Enhancements

The v1.0.6 release brings major operational capabilities designed for high-throughput, enterprise-scale PowerDNS deployments:

### 10.1 PowerDNS Native Operations Control Plane
- **AXFR Retrieval** (`PUT /zones/{id}/axfr-retrieve`): Forces immediate asynchronous zone transfer pull from primary nameservers on secondary/slave zones.
- **DNS NOTIFY Broadcast** (`PUT /zones/{id}/notify`): Sends RFC 1996 NOTIFY packets to all configured secondary nameservers and `ALSO-NOTIFY` targets.
- **DNSSEC Rectification** (`PUT /zones/{id}/rectify`): Rectifies zone database ordering (`ordername`) and DNSSEC signing metadata across backends.

### 10.2 RFC 2845 / RFC 8945 TSIG Keys Management
- Direct CRUD integration with PowerDNS REST API (`/tsigkeys`).
- Supported HMAC algorithms: `hmac-sha256`, `hmac-sha512`, `hmac-sha384`, `hmac-sha1`, `hmac-md5`.
- Built-in 256-bit cryptographically secure random secret generator for zero-effort key generation.

### 10.3 PowerDNS Zone Metadata Directives
- Granular per-zone native configuration for 9 metadata directives:
  - `ALLOW-AXFR-FROM`: Subnet/IP access-control lists for zone transfers.
  - `TSIG-ALLOW-AXFR`: Enforces specific TSIG key authentication for transfers.
  - `AXFR-SOURCE`: Specifies source IP address for outbound zone transfers.
  - `SOA-EDIT`: Configures automatic serial number incrementation (`INCEPTION-INCREMENT`, `DEFAULT`).
  - `SOA-EDIT-API`: API-specific serial increment rule.
  - `ALSO-NOTIFY`: Explicit list of remote IP addresses to notify upon zone updates.
  - `NOTIFY-DNSUPDATE`: Triggers NOTIFY broadcasts upon RFC 2136 dynamic updates.

### 10.4 Automated Reverse DNS (Auto-PTR) & Wizard
- RFC-compliant `DomainName` and `IpAddress` value objects handling canonicalization, FQDN validation, and reverse pointer generation (`in-addr.arpa`, `ip6.arpa`).
- Automated PTR record synchronization when adding or modifying `A` or `AAAA` records.
- Dedicated Reverse Zone Creation Wizard (`/zones/reverse-wizard`) supporting `/24`, `/16`, `/8` IPv4 subnets and `/64`, `/48`, `/32` IPv6 prefixes.

### 10.5 DNS Security & Email Deliverability Toolbox
- **SPF Parser & Validator**: RFC 7208 syntax validation, mechanism breakdown, and strict 10-lookup count ceiling enforcement.
- **SPF Builder**: Interactive visual builder for mechanisms (`a`, `mx`, `ip4`, `ip6`, `include`, `all`) and qualifiers (`+`, `-`, `~`, `?`).
- **DKIM Inspector**: Live selector query against authoritative nameservers to inspect public keys and key types (`rsa`, `ed25519`).
- **DMARC Builder & Validator**: RFC 7489 alignment policy generator (`p=none|quarantine|reject`, `sp`, `pct`, `rua`, `ruf`).
- **Multi-Provider RBL/DNSBL Checker**: Checks IP/domain reputation across major blacklist providers (Spamhaus ZEN, Barracuda, Spamcop, SORBS, Composite Blocking List).

### 10.6 Encrypted Cluster Backup & Restore Engine
- AES-256-GCM symmetric encryption with PBKDF2 key derivation (SHA-256, 10,000 iterations), 96-bit IV, and 128-bit authentication tag.
- SHA-256 cryptographic checksums for backup archive integrity verification.
- Dry-run restore preview validating zone schemas and record counts prior to mutation.

### 10.7 Global Command Palette (Ctrl+K / Cmd+K)
- Fast, accessible modal dialog with debounced async queries against `/api/search`.
- Real-time search across PowerDNS zones, resource records, comments, and application quick-actions.
- Keyboard navigation with ArrowUp/ArrowDown selection and Enter execution.

### 10.8 CLI Runner (`bin/pdns`) & Console Kernel
- Standalone CLI executable `bin/pdns` for headless server administration and cron automation:
  - `php bin/pdns backup:run [--encrypt] [--passphrase=...]`
  - `php bin/pdns cluster:probe`
  - `php bin/pdns zone:notify <zone_id>`
  - `php bin/pdns zone:rectify <zone_id>`
  - `php bin/pdns migrate`

---

## 11. CI/CD Multi-Linter Orchestration & Verification Gates

All remote GitHub Actions workflows execute with 100% passing status:

| Workflow | Version / Runner | Verification Scope | Status |
| :--- | :--- | :--- | :--- |
| **Super-Linter** | `v8.3.1` (Inclusion Mode) | PHP builtin, JavaScript, Markdown, YAML, JSON, Shell | **PASS (0 errors)** |
| **MegaLinter** | `v9.6.0` (Automated Multi-Scan) | 50+ Linters across all repository languages and assets | **PASS (0 errors)** |
| **CodeQL Advanced** | GitHub Advanced Security | Semantic AST security analysis for PHP and JavaScript | **PASS (0 alerts)** |
| **PHP-CI** | Matrix PHP 8.1, 8.2, 8.3, 8.4, 8.5 | Composer validation, PHPCS, PHPStan, Psalm, PHPUnit | **PASS (0 errors)** |

---

## 12. Credits & Maintainer Recognition

**PHP-PDNSManager Enterprise Edition** is architected, developed, and maintained by **HARRY DERTIN SUTISNA ALSYUNDAWY** with foundational contributions and infrastructure from the global open-source community. See the full [CREDITS.md](CREDITS.md) document for comprehensive details:

- **Founder, Lead Architect & Maintainer**:
  - **[Harry Dertin Sutisna Alsyundawy (@alsyundawy)](https://github.com/alsyundawy)** — Founder & Principal Enterprise Architect at **[ALSYUNDAWY IT SOLUTION](https://alsyundawy.com)**.
  - Creator of the Visual Subnet Calculator theming engine, authoritative DNS invariants, multi-server clustering architecture, and zero-CDN air-gapped asset design.
- **PowerDNS Authoritative Core**:
  - **[PowerDNS.COM BV](https://www.powerdns.com/)** and the PowerDNS engineering team for the high-performance PowerDNS Authoritative Server daemon (`pdns_server`), RFC 9460 & RFC 9432 implementations, and internal HTTP API v1.
- **Domain References & Research**:
  - **[Charles A. Barnes (`charlesabarnes/SPFtoolbox`)](https://github.com/charlesabarnes/SPFtoolbox)** for RFC 7208 SPF mechanism parsing, 10-lookup limit validation, and email deliverability diagnostic patterns.
  - **[PowerDNS-Admin Community](https://github.com/PowerDNS-Admin/PowerDNS-Admin)** for Auto-PTR reverse synchronization patterns and zone history concepts.
  - **[PHP-BindManager (`alsyundawy/PHP-BindManager`)](https://github.com/alsyundawy/PHP-BindManager)** for BIND9 zone parsing and DNS diagnostic utilities.
- **PHP Foundation & Language Contributors**:
  - **The PHP Group** and global core developers for modern PHP (PHP 8.1, 8.2, 8.3, 8.4, and 8.5) delivering strict typing, fibers, JIT, and modern runtime performance.
- **Enterprise Testing & Static Analysis Toolchains**:
  - **[PHPUnit](https://phpunit.de/)** by Sebastian Bergmann for unit and integration test runners.
  - **[PHPStan](https://phpstan.org/)** by Ondřej Mirtes for Level 5 strict static analysis.
  - **[Psalm](https://psalm.dev/)** by Vimeo for advanced semantic type inference and type safety.
  - **[PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer)** by Greg Sherwood and PHPCSStandards for PSR-12 standard verification.
  - **[MegaLinter](https://megalinter.io/)** (Ox Security) & **[Super-Linter](https://github.com/super-linter/super-linter)** (GitHub) for 50+ language CI/CD orchestration.
  - **[GitHub Advanced Security CodeQL](https://codeql.github.com/)** for semantic AST security scanning and CWE/OWASP vulnerability checks.
- **Offline UI, Theming & Front-End Bundles**:
  - **[Bootstrap 5.3.3](https://getbootstrap.com/)** by Mark Otto and Jacob Thornton.
  - **[Font Awesome 6.5.2](https://fontawesome.com/)** by Dave Gandy and Fonticons.
  - **[Chart.js 4.4.4](https://www.chartjs.org/)** for client-side zone analytics and distribution rendering.
  - **[Visual Subnet Calculator](https://alsyundawy.github.io/visualsubnetcalc)** design tokens, glassmorphism headers, and mobile anti-clipping dynamic viewport CSS.
