# PHP-PDNSManager — Enterprise PowerDNS Authoritative Control Plane

[![Release](https://img.shields.io/badge/Release-v1.0.1-007ACC?style=for-the-badge&logo=github)](https://github.com/alsyundawy/PHP-PDNSManager/releases)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%20%7C%208.3%20%7C%208.4%20%7C%208.5-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![PowerDNS](https://img.shields.io/badge/PowerDNS-Authoritative%20Only-00599C?style=for-the-badge&logo=powerdns&logoColor=white)](https://powerdns.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)
[![Quality Gates](https://img.shields.io/badge/Quality%20Gates-PHPStan%20L5%20%7C%20Psalm%20L4%20%7C%20PSR--12-brightgreen?style=for-the-badge&logo=checkmarx)](https://github.com/alsyundawy/PHP-PDNSManager/actions)
[![Zero CDN](https://img.shields.io/badge/Assets-100%25%20Offline%20Local-blue?style=for-the-badge&logo=bootstrap)](https://getbootstrap.com/)
[![Multi-Server](https://img.shields.io/badge/Multi--Server-Cluster%20%26%20Failover-orange?style=for-the-badge&logo=serverfault)](https://powerdns.com/)
[![Database](https://img.shields.io/badge/Database-MySQL%20%7C%20MariaDB%20%7C%20SQLite-003B57?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Donate PayPal](https://img.shields.io/badge/Donate-PayPal-00457C?style=for-the-badge&logo=paypal&logoColor=white)](https://www.paypal.me/alsyundawy)

> **Enterprise-grade, security-hardened Web GUI, REST API Gateway, and GraphQL engine for PowerDNS Authoritative Server.
> Engineered with modern PHP 8.2+, strict PSR standards (PSR-7, PSR-11, PSR-14, PSR-15, PSR-17, PSR-18), zero external CDN
> dependencies, dual dark/light theming inspired by Visual Subnet Calculator, multi-server PowerDNS cluster routing with
> latency monitoring, RFC 1035 BIND zone import/export, DNS zone templates, and HMAC-SHA256 signed webhooks.**
>
> Designed, engineered, and maintained by
> **[`HARRY DERTIN SUTISNA ALSYUNDAWY (@alsyundawy)`](https://github.com/alsyundawy)** —
> Built for mission-critical authoritative DNS operations.
>
> 📦 **[`GitHub Releases`](https://github.com/alsyundawy/PHP-PDNSManager/releases)** &nbsp;|&nbsp;
> 📖 **[`Installation Guide`](docs/INSTALL.md)** &nbsp;|&nbsp;
> 🛠️ **[`Production Deployment Tutorial`](TUTORIAL.md)** &nbsp;|&nbsp;
> 🏛️ **[`Architecture & Notes`](DOCNOTE.md)** &nbsp;|&nbsp;
> 📜 **[`Full Changelog`](CHANGELOG.md)** &nbsp;|&nbsp;
> 💖 **[`Support via PayPal`](https://www.paypal.me/alsyundawy)** &nbsp;|&nbsp;
> 🇮🇩 **[`QRIS Donation`](#-support--donation)**

---

## 🧭 Navigation

- [Overview](#-overview)
- [Why This Modernized Edition?](#-why-this-modernized-edition)
- [Key Features](#-key-features)
- [Architecture & Request Pipeline](#️-architecture--request-pipeline)
- [DNS Record Types & Authoritative Engine](#-dns-record-types--authoritative-engine)
- [Visual Subnet Calculator Design & Mobile Responsive System](#-visual-subnet-calculator-design--mobile-responsive-system)
- [Cross-OS Production Deployment & Clustering](#-cross-os-production-deployment--clustering)
- [Installation & Setup Guide](#-installation--setup-guide)
- [Configuration Reference](#️-configuration-reference)
- [REST & GraphQL API Automation Layer](#-rest--graphql-api-automation-layer)
- [Quality Assurance & Verification Gates](#-quality-assurance--verification-gates)
- [Engineering Standards & Invariants](#-engineering-standards--invariants)
- [Security & Content Safety](#-security--content-safety)
- [Project Directory Structure](#-project-directory-structure)
- [Contributing](#-contributing)
- [Maintainer & Contact](#-maintainer--contact)
- [Support & Donation](#-support--donation)
- [License](#-license)

---

## 🌟 Overview

**PHP-PDNSManager** is a high-performance, web-based authoritative DNS control plane and automation gateway tailored
for system administrators, network engineers, hosting providers, enterprise teams, and DevOps practitioners.

Managing PowerDNS Authoritative Server manually through command-line utilities (`pdnsutil`), raw SQL backend edits, or
unstructured shell scripts is error-prone, risks syntax inconsistencies, and creates dangerous bottlenecks during
production incident response. **PHP-PDNSManager** bridges this gap by providing an intuitive, accessible Web GUI,
a comprehensive REST API V1, and a zero-dependency GraphQL endpoint, all while ensuring 100% compliance with RFC DNS
standards and zero service interruption.

Whether running on Ubuntu LTS, Debian, Rocky Linux, or AlmaLinux, PHP-PDNSManager delivers sub-millisecond local
configuration rendering, multi-server cluster failover, real-time node health telemetry, and complete decoupling
from internet-dependent third-party CDNs.

---

## 🚀 Why This Modernized Edition?

This edition (**v1.0.1**) represents a clean-slate architectural, security, accessibility, and visual overhaul of
modern PowerDNS administration:

### 🛡️ 1. Zero-CDN Offline Architecture & Content Security

- **100% Local Distribution**: Ships with production bundles of **Bootstrap 5.3.3**, **jQuery 3.7.1**, **Font Awesome
  6.5.2** (with 8 binary webfonts), and **Chart.js 4.4.4 UMD** located in `public/assets/vendor/`.
- **Air-Gapped & Sovereign Network Ready**: Runs reliably in isolated data center enclaves, air-gapped server racks,
  and restricted private intranets without external CDN latency, outages, or tracking.
- **Strict Content Security Policy (CSP)**: HTTP security headers enforce `default-src 'self'` and
  `style-src 'self' 'unsafe-inline'` with zero external origins permitted.

### ⚡ 2. Strict Authoritative DNS Invariant (No Cache-Poisoning Vectors)

- **Dedicated Primary & Secondary Authority**: Explicitly engineered for Authoritative Forward and Reverse DNS zones.
- **Elimination of Recursive Bloat**: Recursive caching and Response Policy Zones (RPZ) are deliberately excluded.
  Keeping the Authoritative nameserver completely separated from recursive resolvers eliminates DNS cache-poisoning
  vectors, recursive query amplification hazards, and memory leaks.

### 🖥️ 3. Multi-PowerDNS Cluster Management & Health Telemetry

- **Multi-Node Cluster Engine (`pdns_servers`)**: Manage multiple PowerDNS authoritative nodes from a single pane of
  glass, complete with dynamic server switching, active status toggling, and default cluster failover.
- **Real-Time Health & Latency Telemetry (`/health`)**: Comprehensive telemetry inspecting PowerDNS daemon API latency,
  database connectivity, storage directory permissions, and PHP runtime health status with dedicated JSON endpoints.

### 🎨 4. Visual Subnet Calculator Theming & Mobile Anti-Clipping Engine

- **Curated Slate Palette**: Inspired by the modern dark/light design system of
  [Visual Subnet Calculator](https://alsyundawy.github.io/visualsubnetcalc), featuring deep slate tones (`#0f172a` to
  `#1e293b`), glassmorphism overlays (`rgba(15, 23, 42, 0.75)`), and electric cyan/blue accents.
- **Dual Theme Synchronization**: Instant reactivity syncing both `data-theme` and `data-bs-theme` attributes across
  `dark`, `light`, and automatic OS preferences with persistent `localStorage` retention.
- **Mobile Viewport Hardening**: Engineered for compact viewports and dynamic mobile browser chrome (Xiaomi HyperOS,
  MIUI, POCO, Redmi, iPhone dynamic island) using CSS `100svh`/`100dvh`, safe-area-inset padding, and `min-width: 0`
  flex/grid constraints to eliminate clipping and horizontal scrolling.

### 🔒 5. Enterprise Security & Defense-in-Depth

- **Two-Factor Authentication (2FA)**: RFC 6238 TOTP engine with secure QR provisioning and backup recovery codes.
- **Cryptographic Webhook Dispatcher**: Real-time HTTP webhook notifications on zone and record mutations signed with
  HMAC-SHA256 (`X-PDNS-Signature`) for automated external orchestration.
- **Granular RBAC & Multi-Tenant Partitioning**: Role-Based Access Control (`admin`, `operator`, `viewer`) combined
  with multi-tenant organization boundaries to isolate zones between enterprise clients.
- **Brute-Force Rate Limiting**: IP-based rate limiting on login and API endpoints with automatic cooldowns.
- **SonarLint & PSR-12 Compliant**: Clean static analysis, camelCase model properties, zero unreachable code, and
  tamper-evident audit logging.

### 🔄 6. RFC 1035 BIND Zone Import/Export & Templating

- **RFC 1035 BIND Zone Parser**: Pure PHP BIND zone file parser and serializer supporting `$ORIGIN`, `$TTL`, comments,
  and multi-line records for zero-downtime migration from legacy BIND9 deployments.
- **Zone Templating Engine**: Reusable DNS blueprints (e.g., Enterprise Web Hosting, Google Workspace, Custom Mail)
  enabling one-click zone creation with complete record sets.
- **Cross-Zone Bulk Record Operations**: Powerful search across all authoritative zones with bulk IP replacement and
  mass deletion capabilities.

---

## 🎯 Key Features

| Capability Area             | Highlights & Implementations                                                                                                                              |
|:----------------------------|:----------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Multi-Server Clustering** | Manage multiple PowerDNS nodes (`pdns_servers`), cluster failover, connection latency testing, and dedicated REST routes (`/servers`).                    |
| **Zone Management**         | Native, Master, and Slave zones; auto-increment SOA serial (`YYYYMMDDNN`); instant search, filtering, and RFC 1035 BIND import/export.                   |
| **Authoritative Records**   | Full CRUD for `A`, `AAAA`, `CNAME`, `MX`, `TXT`, `NS`, `SRV`, `CAA`, `PTR`, `NAPTR`, and `SOA` with real-time FQDN syntax validation.                    |
| **DNSSEC Automation**       | Cryptographic key management (KSK / ZSK generation), automatic zone signing, key rollover, and DS record generation for parent delegation.                |
| **Zone Templating Engine**  | Preconfigured DNS profiles for instant 1-click zone provisioning and record deployment (`/templates`).                                                    |
| **Bulk Record Operations**  | Cross-zone search and replace for batch IP/target migrations and mass record deletion (`/zones/bulk-records`).                                            |
| **Identity & RBAC**         | Role-Based Access Control (`admin`, `operator`, `viewer`), Multi-Tenant Organizations, TOTP Two-Factor Authentication, and scoped API tokens.             |
| **REST API Gateway**        | Versioned REST API (`/api/v1`) with JSON-Schema validation and Bearer token authentication for Terraform, Ansible, and CI/CD pipelines.                  |
| **GraphQL Endpoint**        | Zero-dependency GraphQL execution engine (`/graphql`) supporting flexible queries (`zones`, `servers`, `health`) and mutations.                           |
| **Signed Webhooks**         | HMAC-SHA256 payload signing with `X-PDNS-Signature` headers for event-driven integration upon zone and record modifications.                             |
| **Audit Logging & Trail**   | Granular audit logs tracking user ID, IP address, exact action, target zone, HTTP status, and timestamp with CSV/JSON export (`/audit-logs`).             |
| **System Telemetry**        | Health telemetry dashboard (`/health`) monitoring PowerDNS latency, database connectivity, storage permissions, and PHP runtime metrics.                 |
| **Offline UI & Theming**    | Visual Subnet Calculator dark/light palette, 100% offline local vendor assets, glassmorphism navigation, and mobile anti-clipping viewport engine.         |

---

## 🏗️ Architecture & Request Pipeline

PHP-PDNSManager is built on a clean, decoupled MVC architecture with PSR-15 middleware and a Service-Repository pattern:

```mermaid
flowchart TB
    subgraph Client["Web Browser & Automation Clients"]
        User["Sysadmin / Web Browser"]
        APIClient["Terraform / Ansible / CI-CD / cURL"]
    end

    subgraph WebServer["Web Server (Nginx / Apache)"]
        Nginx["TLS Termination / Reverse Proxy<br/>(HSTS, CSP, Security Headers)"]
        Static["Local Static Assets (Zero CDN)<br/>(Bootstrap 5.3.3, jQuery 3.7.1, FontAwesome 6.5.2, Chart.js)"]
    end

    subgraph AppKernel["PHP-PDNSManager Runtime (PHP 8.2+)"]
        FrontController["public/index.php"]
        Middleware["PSR-15 Middleware Pipeline<br/>(Auth, CSRF, Rate Limiting, Security Headers)"]
        Router["Application Router<br/>(Web, API v1, GraphQL)"]
        Controllers["Controllers Layer<br/>(Zone, Record, Server, Template, Health, API)"]
        Services["Domain Service Layer<br/>(PowerDNSClient, BindZoneService, DNSSEC, WebhookService)"]
        Repositories["Repository Layer<br/>(ZoneRepo, ServerRepo, UserRepo, AuditRepo)"]
    end

    subgraph Persistence["Persistence & DNS Infrastructure"]
        Database[("Relational Database<br/>(MySQL 8.0+ / MariaDB 10.5+ / SQLite)")]
        PDNSDaemon["PowerDNS Authoritative Server<br/>(pdns_server daemon, gmysql backend)"]
        PDNSAPI["PowerDNS Internal REST API<br/>(http://127.0.0.1:8081)"]
    end

    User -->|"HTTPS"| Nginx
    APIClient -->|"HTTPS Bearer API / GraphQL"| Nginx
    Nginx -->|"Static Files"| Static
    Nginx -->|"FastCGI (PHP-FPM)"| FrontController
    FrontController --> Middleware
    Middleware --> Router
    Router --> Controllers
    Controllers --> Services
    Services --> Repositories
    Repositories --> Database
    Services -->|"Internal REST API (JSON)"| PDNSAPI
    PDNSAPI --> PDNSDaemon
    PDNSDaemon -->|"Direct DB Queries"| Database
```

---

## 📊 DNS Record Types & Authoritative Engine

PHP-PDNSManager validates, formats, and provisions all standard DNS Resource Records:

| Record Type | Description                           | RFC Standard       | Syntax Validation & RDATA Schema                               |
|:------------|:--------------------------------------|:-------------------|:---------------------------------------------------------------|
| **`A`**     | IPv4 Host Address                     | RFC 1035           | Dotted-decimal `0.0.0.0` – `255.255.255.255`                   |
| **`AAAA`**  | IPv6 Host Address                     | RFC 3596           | Standard compressed or uncompressed RFC 4291 IPv6              |
| **`CNAME`** | Canonical Name (Alias)                | RFC 1035           | Fully Qualified Domain Name (FQDN)                             |
| **`MX`**    | Mail Exchange Server                  | RFC 1035, RFC 7505 | Priority integer (`0–65535`) + mail exchanger FQDN             |
| **`NS`**    | Authoritative Name Server             | RFC 1035           | Authoritative nameserver FQDN                                  |
| **`TXT`**   | Text Annotations (SPF, DKIM, DMARC)   | RFC 1464, RFC 7208 | Character-string (supports multi-string chunks & quotation)    |
| **`PTR`**   | Pointer Record (Reverse DNS)          | RFC 1035           | Target host FQDN in `in-addr.arpa` or `ip6.arpa`               |
| **`SRV`**   | Service Location Record               | RFC 2782           | Priority, weight, port (`1–65535`), target hostname            |
| **`CAA`**   | Certification Authority Authorization | RFC 6844, RFC 8659 | Flag byte, tag (`issue`, `issuewild`, `iodef`), CA domain      |
| **`NAPTR`** | Naming Authority Pointer              | RFC 2915, RFC 3403 | Order, preference, flags, service, regexp, replacement         |
| **`SOA`**   | Start of Authority                    | RFC 1035, RFC 2181 | Primary NS, contact email, serial, refresh, retry, expire, TTL |
| **`DNSSEC`**| DS & DNSKEY Records                   | RFC 4034, RFC 4035 | Key tag, algorithm, digest type, cryptographic digest          |

---

## 🎨 Visual Subnet Calculator Design & Mobile Responsive System

The user interface has been designed following the aesthetic of
[Visual Subnet Calculator](https://alsyundawy.github.io/visualsubnetcalc):

- **Curated Slate Dark/Light Palette**: Deep obsidian slate background (`#0f172a` to `#1e293b`), crisp borders
  (`#334155`), and electric cyan/blue accents (`#0284c7` to `#38bdf8` with glow effects).
- **Glassmorphism Navigation Header**: Semi-transparent sticky navigation bar with `backdrop-filter: blur(12px)` and
  subtle border illumination.
- **Notch, Cutout & Safe Area Insets**: Integrated with `viewport-fit=cover` and CSS safe-area padding
  (`padding-top: env(safe-area-inset-top, 0px); padding-bottom: env(safe-area-inset-bottom, 0px);`).
- **Dynamic Viewport Height**: Replaces rigid `100vh` with adaptive `100dvh` and `100svh` to prevent UI controls from
  being clipped beneath mobile browser dynamic navigation bars.
- **Anti-Clipping & Touch Scrolling**: Table containers implement `-webkit-overflow-scrolling: touch` with rounded
  boundary wrappers, ensuring wide TXT records, DKIM public keys, and DNSSEC signatures are easily inspectable.

---

## 🌐 Cross-OS Production Deployment & Clustering

PHP-PDNSManager is verified across enterprise Linux operating systems. When deploying PowerDNS Authoritative Server,
the package names and service configurations vary slightly:

### Distribution Paths & Configuration Mapping

| Component / Setting      | Ubuntu 20.04 / 22.04 / 24.04 & Debian 11 / 12 | Rocky Linux 8 / 9 & AlmaLinux 8 / 9       |
|:-------------------------|:----------------------------------------------|:------------------------------------------|
| **PowerDNS Packages**    | `pdns-server`, `pdns-backend-mysql`           | `pdns`, `pdns-backend-mysql` (via EPEL)   |
| **Systemd Service**      | `pdns.service`                                | `pdns.service`                            |
| **Main Config File**     | `/etc/powerdns/pdns.conf`                     | `/etc/pdns/pdns.conf`                     |
| **Schema Definition**    | `/usr/share/doc/pdns-backend-mysql/schema.sql`| `/usr/share/doc/pdns-backend-mysql/schema.sql` |
| **PHP Runtime**          | `php8.2-fpm`, `php8.3-fpm`, `php8.4-fpm`     | `php-fpm` (Remi repository)               |
| **Web Server**           | `nginx`                                       | `nginx`                                   |
| **Firewall System**      | `ufw` (Uncomplicated Firewall)                | `firewalld` or `nftables` / `iptables`    |

### Hardened PowerDNS Authoritative Configuration (`pdns.conf`)

```ini
# /etc/powerdns/pdns.conf (Strictly Authoritative Only)
launch=gmysql

# MySQL / MariaDB Backend Credentials
gmysql-host=127.0.0.1
gmysql-port=3306
gmysql-user=pdns_user
gmysql-password=StrongAuthoritativePasswordHere
gmysql-dbname=pdns_db
gmysql-dnssec=yes

# Network & Listening
local-address=0.0.0.0, ::
local-port=53

# Security & Reconnaissance Prevention
version-string=anonymous
security-poll-suffix=

# Internal REST API (Bound to loopback only)
api=yes
api-key=YourSecureGeneratedPdnsApiKeyHere
webserver=yes
webserver-address=127.0.0.1
webserver-port=8081
webserver-allow-from=127.0.0.1, ::1

# Master / Slave DNS Clustering
master=yes
slave=yes
```

### Production Firewall Configuration

#### Ubuntu / Debian (UFW)

```bash
# Allow SSH & Web Management GUI traffic
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow Authoritative DNS traffic (Both UDP & TCP are mandatory)
sudo ufw allow 53/tcp
sudo ufw allow 53/udp

# Enable firewall
sudo ufw enable
```

#### Rocky Linux / AlmaLinux (Firewalld)

```bash
# Open Web and DNS services
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --permanent --add-service=dns
sudo firewall-cmd --reload
```

---

## 📦 Installation & Setup Guide

### 1. Prerequisites

Ensure your system meets the requirements:

- **PHP**: `8.2`, `8.3`, `8.4`, or `8.5` with extensions: `pdo`, `pdo_mysql` (or `pdo_sqlite`), `mbstring`, `json`,
  `openssl`, `curl`, `sodium`.
- **Database**: MySQL 8.0+, MariaDB 10.5+, or SQLite 3 (WAL mode).
- **PowerDNS Authoritative Server**: `>= 4.x` with REST API enabled.
- **Web Server**: Nginx (recommended) or Apache with PHP-FPM.
- **Composer**: `>= 2.2`.

### 2. Clone & Install Dependencies

```bash
# 1. Clone repository
git clone https://github.com/alsyundawy/PHP-PDNSManager.git /var/www/php-pdnsmanager
cd /var/www/php-pdnsmanager

# 2. Copy production environment configuration
cp .env.example .env

# 3. Install composer dependencies (optimized autoloader)
composer install --no-dev --optimize-autoloader
```

### 3. Initialize Database & Seed Administrator

```bash
# Run database migrations (creates schema, servers, templates, organizations)
php bin/migrate.php

# Seed initial roles and default administrator account
php bin/seed.php
```

> **Default Admin Credentials**:
>
> - **Username**: `admin`
> - **Password**: `ChangeMe@2026!`
> - *(Important: You will be prompted to change this password immediately upon first login).*

### 4. File Permissions

```bash
# Ensure web server user can write to runtime directories
sudo chown -R www-data:www-data /var/www/php-pdnsmanager/storage
sudo chmod -R 775 /var/www/php-pdnsmanager/storage
```

### 5. Nginx Production Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name pdns.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name pdns.example.com;

    ssl_certificate /etc/ssl/certs/pdns.example.com.crt;
    ssl_certificate_key /etc/ssl/private/pdns.example.com.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    root /var/www/php-pdnsmanager/public;
    index index.php;

    # Security Headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;
    add_header Content-Security-Policy "default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; font-src 'self'; img-src 'self' data:;" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## ⚙️ Configuration Reference

Key configuration settings available in your `.env` file:

| Setting Key                 | Default Value                  | Description                                                    |
|:----------------------------|:-------------------------------|:---------------------------------------------------------------|
| `APP_NAME`                  | `"PHP-PDNSManager"`            | Application title displayed across headers and metadata.       |
| `APP_ENV`                   | `"production"`                 | Environment profile (`production`, `local`, `testing`).        |
| `APP_DEBUG`                 | `false`                        | Enable detailed error traces (Must be `false` in production).  |
| `APP_URL`                   | `"https://pdns.example.com"`   | Canonical base URL of the control plane.                       |
| `DB_CONNECTION`             | `"mysql"`                      | Database engine (`mysql`, `sqlite`).                           |
| `DB_HOST`                   | `"127.0.0.1"`                  | Database hostname or IP address.                               |
| `DB_PORT`                   | `3306`                         | Database port.                                                 |
| `DB_DATABASE`               | `"pdns_manager"`               | Database name for application state and metadata.              |
| `DB_USERNAME`               | `"pdns_user"`                  | Database username.                                             |
| `DB_PASSWORD`               | `"secret"`                     | Database password.                                             |
| `PDNS_API_URL`              | `"http://127.0.0.1:8081"`       | PowerDNS daemon internal REST API URL.                         |
| `PDNS_API_KEY`              | `""`                           | PowerDNS daemon `api-key` secret configured in `pdns.conf`.    |
| `PDNS_SERVER_ID`            | `"localhost"`                  | PowerDNS server identifier (`localhost`).                      |
| `SESSION_SECURE`            | `true`                         | Enforces HTTPS-only session cookies.                           |
| `SESSION_LIFETIME`          | `7200`                         | Idle session expiration in seconds (2 hours).                  |
| `SESSION_SAMESITE`          | `"Strict"`                     | Cross-site cookie isolation policy (`Strict`, `Lax`).          |
| `SECURITY_RATE_LIMIT_LOGIN` | `5`                            | Maximum failed login attempts before temporary IP lock.        |

---

## 🌐 REST & GraphQL API Automation Layer

PHP-PDNSManager provides both a versioned REST API V1 and a zero-dependency GraphQL endpoint for automation:

### REST API Authentication

All REST API requests require a Bearer token in the HTTP Authorization header:

```http
Authorization: Bearer pdns_sec_your_generated_api_token_here
```

### Core REST Endpoints

| Method | Endpoint                        | Required Scope   | Description                                       |
|:-------|:--------------------------------|:-----------------|:--------------------------------------------------|
| `GET`  | `/api/v1/zones`                 | `zones:read`     | List all managed authoritative DNS zones.         |
| `POST` | `/api/v1/zones`                 | `zones:write`    | Create a new Native, Master, or Slave DNS zone.   |
| `GET`  | `/api/v1/zones/{id}/records`    | `records:read`   | Fetch all resource records for a given zone.      |
| `POST` | `/api/v1/zones/{id}/records`    | `records:write`  | Add or update a resource record.                  |
| `DELETE`| `/api/v1/zones/{id}/records/{rId}`| `records:write`| Delete a resource record.                         |
| `GET`  | `/api/v1/servers`               | `servers:read`   | List configured PowerDNS server nodes.            |
| `POST` | `/api/v1/servers/{id}/test`     | `servers:read`   | Test connection and measure node latency.         |
| `GET`  | `/api/v1/templates`             | `templates:read` | List reusable DNS zone blueprints.                |
| `GET`  | `/health`                       | None / Public    | System and PowerDNS daemon health telemetry.      |

### Example REST cURL Request

```bash
curl -X POST https://pdns.example.com/api/v1/zones/example.com/records \
  -H "Authorization: Bearer pdns_sec_8f92b41c0e" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "api.example.com.",
    "type": "A",
    "content": "192.0.2.53",
    "ttl": 3600
  }'
```

### Zero-Dependency GraphQL Endpoint (`/graphql`)

```graphql
query GetSystemOverview {
  health {
    status
    database
    pdnsLatencyMs
  }
  zones {
    id
    name
    kind
    serial
  }
  servers {
    id
    name
    apiUrl
    isActive
    isDefault
  }
}
```

---

## 📊 Quality Assurance & Verification Gates

Every commit of PHP-PDNSManager is validated against comprehensive automated quality gates:

| Quality Gate             | Verification Engine                                               | Target / Standard                   | Pass Criteria               |       Status        |
|:-------------------------|:------------------------------------------------------------------|:------------------------------------|:----------------------------|:-------------------:|
| **Unit & Service Tests** | [`PHPUnit 10.5`](https://phpunit.de)                              | Core models, services, repositories | 100% assertions pass        |  **✔ 21/21 PASS**   |
| **Static Analysis**      | [`PHPStan`](https://phpstan.org)                                  | Strict Level 5 analysis             | 0 errors                    | **✔ LEVEL 5 CLEAN** |
| **Type Inference**       | [`Psalm`](https://psalm.dev)                                      | Level 4 strict type safety          | 0 errors                    |     **✔ CLEAN**     |
| **Coding Standards**     | [`PHP_CodeSniffer`](https://github.com/squizlabs/PHP_CodeSniffer) | PSR-12 strict compliance            | 0 errors, 0 warnings        |  **✔ PSR-12 PASS**  |
| **Code Formatting**      | [`PHP-CS-Fixer`](https://cs.symfony.com)                          | Strict rule set                     | 0 fixable files remaining   |     **✔ CLEAN**     |
| **Security Scanning**    | GitHub Code Scanning & SonarLint                                  | OWASP Top 10, CWE checks            | 0 security vulnerabilities  |   **✔ 0 ISSUES**    |

---

## 📋 Engineering Standards & Invariants

To ensure long-term maintainability, high performance, and security, the following invariants are enforced:

- **Strict Typing Mandatory**: Every PHP source file declares `declare(strict_types=1);` at line 3.
- **Strict Line Length Bound**: All controllers, services, repositories, HTML/PHP view templates, and unit tests
  strictly adhere to $\le 120$ characters per line.
- **Zero Third-Party CDN Dependency**: No runtime asset requests may query external hosts. All vendor CSS, JS, and
  fonts must reside locally in `public/assets/vendor/`.
- **Prepared Statements Exclusive**: Raw SQL query concatenations are strictly forbidden. All database operations
  must utilize PDO prepared statements with explicit parameter binding.
- **Fail-Safe Session Cookies**: Session cookies must always have `secure: true`, `httponly: true`, and
  `SameSite: Strict` configured.

---

## 🔒 Security & Content Safety

- **OWASP Top 10 Hardened**: Validated against SQL Injection, Cross-Site Scripting (XSS), Cross-Site Request Forgery
  (CSRF), Insecure Direct Object References (IDOR), and Broken Access Control.
- **Argon2id & Sodium Password Hashes**: Passwords are saved using secure Argon2id/Ed25519 hashing with hardened
  memory and time cost factors.
- **Signed Webhook Dispatch**: All outbound webhook payloads are cryptographically signed with HMAC-SHA256.
- **Tamper-Evident Audit Trails**: Every administrative mutation (zone edits, record creation, server changes, role
  updates) is persisted in the `audit_logs` table with IP addresses, user IDs, and timestamps.

---

## 📂 Project Directory Structure

```text
PHP-PDNSManager/
├── app/                        # Application Source Code
│   ├── Controllers/            # Web GUI & REST/GraphQL API Controllers
│   │   ├── Api/                # REST API V1 Controllers (Zones, Records, Servers)
│   │   ├── Auth/               # Authentication, 2FA & Session Controllers
│   │   ├── HealthController.php# Health Telemetry & Latency Monitoring
│   │   ├── ServerController.php# Multi-Server Cluster Management
│   │   ├── TemplateController.php# DNS Zone Templating Controller
│   │   └── ZoneController.php  # Zone CRUD, BIND Import/Export, Bulk Records
│   ├── Core/                   # Middleware Pipeline, Request/Response, Helpers
│   ├── Middleware/             # Security Middleware (Auth, CSRF, Rate Limit)
│   ├── Models/                 # Domain Entity Models (Zone, PdnsServer, User)
│   ├── Repositories/           # PDO Database Repositories
│   └── Services/               # Domain Business Logic Layer
│       ├── Auth/               # Authentication & TOTP 2FA Services
│       ├── DNS/                # BIND Zone Import/Export & Templating
│       └── PowerDNS/           # PowerDNS REST API Client & Cluster Services
├── bin/                        # CLI Commands (migrate.php, seed.php)
├── config/                     # Modular Application Configurations
│   ├── app.php                 # Core Application Settings
│   ├── database.php            # MySQL / MariaDB / SQLite Connection Settings
│   └── powerdns.php            # PowerDNS Daemon API Settings
├── database/                   # Schema Migrations & Seeders
├── docs/                       # Comprehensive Architecture Guides & OpenAPI Spec
├── public/                     # Web Document Root
│   ├── index.php               # Front Controller
│   └── assets/                 # Local Assets (Strict Zero CDN)
│       ├── css/app.css         # Visual Subnet Calculator Slate Theme
│       ├── js/app.js           # UI & Theme Controller JavaScript
│       └── vendor/             # Local Vendor Distributions
│           ├── bootstrap/      # Bootstrap 5.3.3 (CSS & JS Bundle)
│           ├── chartjs/        # Chart.js 4.4.4 UMD Bundle
│           ├── fontawesome/    # Font Awesome 6.5.2 (Webfonts & CSS)
│           └── jquery/         # jQuery 3.7.1 Minified
├── resources/                  # Server-Side View Templates
│   └── Views/                  # PHP HTML Views (Auth, Zones, Servers, Templates)
├── routes/                     # Route Definitions (web.php, api.php)
├── storage/                    # Runtime Storage (Logs, Cache)
├── tests/                      # Automated PHPUnit Test Suite
│   ├── Feature/                # Feature & Integration Tests
│   └── Unit/                   # Unit Tests (Cluster, BIND, Webhooks)
├── CHANGELOG.md                # Full Semantic Versioning Changelog
├── DOCNOTE.md                  # Engineering Architecture Notes
├── LICENSE                     # MIT Open Source License
├── phpstan.neon                # PHPStan Static Analysis Configuration
├── psalm.xml                   # Psalm Strict Configuration
├── phpcs.xml                   # PHP_CodeSniffer PSR-12 Configuration
└── TUTORIAL.md                 # Complete PowerDNS Production Deployment Tutorial
```

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository and create your feature branch: `git checkout -b feature/amazing-feature`.
2. Ensure all changes adhere strictly to PSR-12 and max 120-character line lengths.
3. Verify that all quality gates pass: `./vendor/bin/phpunit --no-coverage`, `./vendor/bin/phpstan analyse`, and
   `./vendor/bin/phpcs`.
4. Commit your changes with conventional commit messages: `git commit -m 'feat: add DNSSEC automated rollover'`.
5. Push to your branch and open a Pull Request.

---

## 📬 Maintainer & Contact

For technical inquiries, enterprise deployments, security consultations, or collaboration:

- **Lead Maintainer & Engineering**: **HARRY DERTIN SUTISNA ALSYUNDAWY** — [`ALSYUNDAWY IT SOLUTION`](https://alsyundawy.com)
- **Official Website**: [`https://alsyundawy.com`](https://alsyundawy.com)
- **GitHub Profile**: [`@alsyundawy`](https://github.com/alsyundawy)
- **Email**: [`alsyundawy@gmail.com`](mailto:alsyundawy@gmail.com)
- **Phone / WhatsApp / Telegram**: [`+62 856-8515-212`](tel:+628568515212)
- **Repository**: [`https://github.com/alsyundawy/PHP-PDNSManager`](https://github.com/alsyundawy/PHP-PDNSManager)

---

## 💖 Support & Donation

If **PHP-PDNSManager** has saved you time, enhanced your DNS operations, or provided value in your enterprise
infrastructure, consider supporting its continuous maintenance, security audits, and open-source development:

### 💳 International Support: PayPal

[![Donate with PayPal](https://img.shields.io/badge/Donate-PayPal-00457C?style=for-the-badge&logo=paypal&logoColor=white)](https://www.paypal.me/alsyundawy)

- **PayPal Link**: [`https://www.paypal.me/alsyundawy`](https://www.paypal.me/alsyundawy)

### 🇮🇩 Indonesian & Regional Support: QRIS (Quick Response Code Indonesian Standard)

Scan the QRIS barcode below using any Indonesian mobile banking app (BCA, Mandiri, BRI, BNI, BSI, CIMB Niaga, Permata)
or e-wallet (GoPay, OVO, DANA, LinkAja, ShopeePay):

![QRIS Donation Barcode - ALSYUNDAWY](https://github.com/user-attachments/assets/a0126f28-6dde-43da-ba14-d7c9a27de0df)

- **Merchant / Account Name**: **ALSYUNDAWY IT SOLUTION**
- **NMID**: **`ID1020021153676`**
- **Direct Barcode Asset Link**: [`https://github.com/user-attachments/assets/a0126f28-6dde-43da-ba14-d7c9a27de0df`](https://github.com/user-attachments/assets/a0126f28-6dde-43da-ba14-d7c9a27de0df)
- **WhatsApp Confirmation**: [`+62 856-8515-212`](https://wa.me/628568515212)

Your support directly powers open-source DNS infrastructure tooling, security enhancements, and continuous community improvements.

---

## 📄 License

PHP-PDNSManager is open-source software licensed under the [`MIT License`](LICENSE) © 2024–2026 Harry DS Alsyundawy.

Feel free to use, modify, and distribute it for personal, commercial, and enterprise infrastructure deployments.
