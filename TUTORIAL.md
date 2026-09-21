# Production Deployment, Installation, and High-Availability PowerDNS Guide

**PHP-PDNSManager Enterprise Edition (v1.0.1)**
_Engineered for High-Availability Authoritative DNS Infrastructure, Cluster Replication, and Automated DNSSEC_

---

## 1. System Overview & Architecture

PHP-PDNSManager is an enterprise-grade web management suite and REST API gateway designed exclusively for **PowerDNS Authoritative Server**. The architecture focuses 100% on **Authoritative DNS**: serving authoritative records (`A`, `AAAA`, `CNAME`, `MX`, `TXT`, `NS`, `SRV`, `CAA`, `PTR`, `DNSSEC`) to the global Internet with zero recursion.

```text
       [ Global Internet DNS Clients / Resolvers ]
                           | (Port 53 UDP/TCP)
                           v
         +-----------------------------------+
         |   PowerDNS Authoritative Server   |<---+
         |        (Port 53 Public IP)        |    |
         +-----------------------------------+    |
                           |                      |
                [ MySQL / MariaDB ]               | REST API
                           ^                      | (Port 8081 Localhost)
                           |                      |
                 +-------------------+            |
                 | PHP-PDNSManager   |------------+
                 | (Nginx + PHP 8.1+)|
                 +-------------------+
```

### Core Architecture Components

1. **PowerDNS Authoritative Daemon (`pdns_server`)**:
   - Binds to public interfaces (`0.0.0.0:53` and `[::]:53`).
   - Serves authoritative zone answers without recursion.
   - Communicates with MySQL/MariaDB via the `gmysql` backend.
   - Exposes an internal REST API on `127.0.0.1:8081` secured by an API key.

2. **PHP-PDNSManager Web GUI & REST API**:
   - Interacts with PowerDNS via its internal REST API client.
   - Stores user accounts, RBAC roles, audit logs, server cluster nodes, zone templates, and webhooks in a dedicated database.
   - Provides an enterprise Web GUI and modern REST/GraphQL API for automation.

---

## 2. Server Preparation (Ubuntu 20.04/22.04/24.04 LTS, Debian 12, Rocky Linux 8/9)

### 2.1 OS Baseline Configuration

Set timezone and update packages:

```bash
# Ubuntu / Debian
sudo timedatectl set-timezone UTC
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git unzip zip software-properties-common ufw fail2ban

# Rocky Linux / CentOS 8/9 Stream / RHEL 9
sudo timedatectl set-timezone UTC
sudo dnf upgrade -y
sudo dnf install -y epel-release curl wget git unzip zip firewalld fail2ban
```

### 2.2 Time Synchronization (Chrony)

Accurate system time is mandatory for DNSSEC key validity and signature generation:

```bash
# Ubuntu / Debian
sudo apt install -y chrony
sudo systemctl enable --now chrony

# Rocky Linux / RHEL
sudo dnf install -y chrony
sudo systemctl enable --now chronyd
```

---

## 3. PowerDNS Authoritative Server Installation & Backend Setup

### 3.1 Install PowerDNS and MariaDB

```bash
# Ubuntu 22.04/24.04 LTS / Debian 12
sudo apt install -y pdns-server pdns-backend-mysql mariadb-server mariadb-client

# Rocky Linux 8/9 / RHEL 9
sudo dnf install -y mariadb-server mariadb
sudo systemctl enable --now mariadb
sudo dnf install -y pdns pdns-backend-mysql
```

### 3.2 Initialize PowerDNS Database

Secure MariaDB and create the database schemas:

```bash
sudo mysql_secure_installation
```

Log in to MySQL as root:

```bash
sudo mysql -u root -p
```

Execute database and schema creation:

```sql
-- 1. PowerDNS Authoritative Database
CREATE DATABASE pdns CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pdns_user'@'localhost' IDENTIFIED BY 'SuperSecretPdnsDbPassword2026!';
GRANT ALL PRIVILEGES ON pdns.* TO 'pdns_user'@'localhost';

-- 2. PHP-PDNSManager Application Database
CREATE DATABASE pdns_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pdns_mgr'@'localhost' IDENTIFIED BY 'SuperSecretManagerPassword2026!';
GRANT ALL PRIVILEGES ON pdns_manager.* TO 'pdns_mgr'@'localhost';
FLUSH PRIVILEGES;

USE pdns;

-- PowerDNS Standard 4.x MySQL Schema
CREATE TABLE IF NOT EXISTS domains (
  id                    INT AUTO_INCREMENT,
  name                  VARCHAR(255) NOT NULL,
  master                VARCHAR(128) DEFAULT NULL,
  last_check            INT DEFAULT NULL,
  type                  VARCHAR(8) NOT NULL,
  notified_serial       INT UNSIGNED DEFAULT NULL,
  account               VARCHAR(40) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY name_index (name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS records (
  id                    BIGINT AUTO_INCREMENT,
  domain_id             INT DEFAULT NULL,
  name                  VARCHAR(255) DEFAULT NULL,
  type                  VARCHAR(10) DEFAULT NULL,
  content               VARCHAR(65535) DEFAULT NULL,
  ttl                   INT DEFAULT NULL,
  prio                  INT DEFAULT NULL,
  disabled              TINYINT(1) DEFAULT 0,
  ordername             VARCHAR(255) BINARY DEFAULT NULL,
  auth                  TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id),
  KEY nametype_index (name,type),
  KEY domain_id (domain_id),
  KEY ordername (ordername)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS supermasters (
  ip                    VARCHAR(64) NOT NULL,
  nameserver            VARCHAR(255) NOT NULL,
  account               VARCHAR(40) NOT NULL,
  PRIMARY KEY (ip, nameserver)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comments (
  id                    INT AUTO_INCREMENT,
  domain_id             INT NOT NULL,
  name                  VARCHAR(255) NOT NULL,
  type                  VARCHAR(10) NOT NULL,
  modified_at           INT NOT NULL,
  account               VARCHAR(40) DEFAULT NULL,
  comment               TEXT NOT NULL,
  PRIMARY KEY (id),
  KEY comments_name_type_idx (name,type),
  KEY comments_order_idx (domain_id, modified_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS domainmetadata (
  id                    INT AUTO_INCREMENT,
  domain_id             INT NOT NULL,
  kind                  VARCHAR(32),
  content               TEXT,
  PRIMARY KEY (id),
  KEY domainmetadata_idx (domain_id, kind)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cryptokeys (
  id                    INT AUTO_INCREMENT,
  domain_id             INT NOT NULL,
  flags                 INT NOT NULL,
  active                BOOL,
  published             BOOL DEFAULT 1,
  content               TEXT,
  PRIMARY KEY (id),
  KEY domainidindex (domain_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tsigkeys (
  id                    INT AUTO_INCREMENT,
  name                  VARCHAR(255),
  algorithm             VARCHAR(50),
  secret                VARCHAR(255),
  PRIMARY KEY (id),
  UNIQUE KEY namealgoindex (name, algorithm)
) ENGINE=InnoDB;
```

### 3.3 Configure PowerDNS Authoritative Server (`pdns.conf`)

Edit `/etc/powerdns/pdns.conf` (or `/etc/pdns/pdns.conf` on Rocky Linux):

```ini
# /etc/powerdns/pdns.conf

# Authoritative Network Binding
local-address=0.0.0.0, ::
local-port=53

# Backend Configuration (gmysql)
launch=gmysql
gmysql-host=127.0.0.1
gmysql-port=3306
gmysql-user=pdns_user
gmysql-password=SuperSecretPdnsDbPassword2026!
gmysql-dbname=pdns
gmysql-dnssec=yes

# Internal REST API (Secured to Localhost)
webserver=yes
webserver-address=127.0.0.1
webserver-port=8081
webserver-allow-from=127.0.0.1, ::1
api=yes
api-key=StrongPowerDnsApiKeyGenerated2026!

# Performance & SOA Management
distributor-threads=3
receiver-threads=2
cache-ttl=60
negquery-cache-ttl=60
query-cache-ttl=20
default-soa-edit=INCEPTION-INCREMENT

# Cluster Replication Settings
master=yes
slave=yes
```

Restart and verify PowerDNS:

```bash
sudo systemctl restart pdns
sudo systemctl enable pdns
curl -H 'X-API-Key: StrongPowerDnsApiKeyGenerated2026!' http://127.0.0.1:8081/api/v1/servers/localhost
```

---

## 4. High-Availability PowerDNS Cluster Setup

PowerDNS Authoritative Server supports two primary methods for high-availability clustering:

### 4.1 Native Database Replication (Recommended for Multi-Node Clusters)

In a Native setup, all PowerDNS nodes share or replicate the MySQL/MariaDB database (e.g., MariaDB Galera Cluster or Master-Slave replication):

1. Configure MariaDB replication between Node 1 (Primary) and Node 2 (Secondary).
2. On both nodes, set zone type to `Native`.
3. In PHP-PDNSManager, navigate to **Servers** (`/servers`) and add Node 2 with its REST API endpoint.
4. Changes made through PHP-PDNSManager are immediately reflected across all cluster members via database synchronization.

### 4.2 Master/Slave (AXFR/IXFR) with Supermasters

For geographically distributed clusters where database replication is not viable:

1. **On Primary Node (`pdns.conf`)**:
   ```ini
   master=yes
   slave=no
   ```
2. **On Secondary Node (`pdns.conf`)**:
   ```ini
   slave=yes
   master=no
   autosecondary=yes
   ```
3. **Register Supermaster**:
   On the secondary node, register the primary node's IP in the `supermasters` table:
   ```sql
   INSERT INTO supermasters (ip, nameserver, account) VALUES ('198.51.100.10', 'ns1.example.com', 'admin');
   ```
4. When a new zone is created on the primary node with an `NS` record pointing to `ns1.example.com`, the secondary automatically provisions the slave zone via AXFR.

---

## 5. PHP-PDNSManager Enterprise Deployment

### 5.1 Install Nginx & PHP 8.1+

```bash
# Ubuntu 22.04/24.04 LTS
sudo apt install -y nginx php8.1-fpm php8.1-cli php8.1-mysql php8.1-curl php8.1-mbstring php8.1-xml php8.1-zip composer

# Rocky Linux 9
sudo dnf install -y nginx php-fpm php-cli php-mysqlnd php-curl php-mbstring php-xml php-zip composer
sudo systemctl enable --now php-fpm nginx
```

### 5.2 Clone Repository & Configure Application

```bash
cd /var/www
sudo git clone https://github.com/alsyundawy/PHP-PDNSManager.git php-pdnsmanager
cd php-pdnsmanager

# Install production dependencies without dev packages
composer install --no-dev --optimize-autoloader

# Create environment configuration
cp .env.example .env
```

Edit `.env`:

```ini
APP_NAME=PHP-PDNSManager
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dns.example.com
APP_TIMEZONE=UTC
APP_SECRET=Generate64CharRandomHexSecretHere1234567890abcdef!

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pdns_manager
DB_USERNAME=pdns_mgr
DB_PASSWORD=SuperSecretManagerPassword2026!

PDNS_API_URL=http://127.0.0.1:8081
PDNS_API_KEY=StrongPowerDnsApiKeyGenerated2026!
PDNS_SERVER_ID=localhost

SESSION_SECURE=true
SESSION_HTTPONLY=true
SESSION_SAMESITE=Strict
```

Run database migrations and initial seed:

```bash
php bin/console migrate
php bin/console seed
```

### 5.3 Offline Vendor Assets Verification (Strict Zero CDN)

Verify all local vendor assets are present:

```bash
ls -lh public/assets/vendor/bootstrap/bootstrap.min.css
ls -lh public/assets/vendor/fontawesome/all.min.css
ls -lh public/assets/vendor/jquery/jquery.min.js
ls -lh public/assets/vendor/chartjs/chart.umd.min.js
```

Set secure directory permissions:

```bash
sudo chown -R www-data:www-data /var/www/php-pdnsmanager
sudo find /var/www/php-pdnsmanager -type f -exec chmod 644 {} \;
sudo find /var/www/php-pdnsmanager -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/php-pdnsmanager/storage
```

### 5.4 Hardened Nginx Virtual Host

Create `/etc/nginx/sites-available/php-pdnsmanager.conf`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name dns.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name dns.example.com;

    root /var/www/php-pdnsmanager/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/dns.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/dns.example.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Enterprise Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
    }

    location ~* \.(css|js|woff2|woff|ttf|png|jpg|ico|svg)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        access_log off;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site and reload Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/php-pdnsmanager.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

---

## 6. Authoritative DNS Zone & Record Management

In PHP-PDNSManager, navigate to **Zones** (`/zones`) to manage your authoritative zones:

### 6.1 Supported Record Types

| Record Type | Description & Usage | Example Content |
| :--- | :--- | :--- |
| **A** | Host IPv4 address | `198.51.100.10` |
| **AAAA** | Host IPv6 address | `2001:db8::10` |
| **CNAME** | Canonical name alias (FQDN) | `host.example.com.` |
| **MX** | Mail exchange server with priority | `10 mail.example.com.` |
| **TXT** | Arbitrary text (SPF, DKIM, DMARC, verification) | `"v=spf1 mx ~all"` |
| **NS** | Authoritative nameserver for the zone/delegation | `ns1.example.com.` |
| **SRV** | Service locator with priority, weight, port, target | `0 5 5060 sip.example.com.` |
| **CAA** | Certificate Authority Authorization | `0 issue "letsencrypt.org"` |
| **PTR** | Reverse DNS pointer (in `in-addr.arpa` or `ip6.arpa`) | `host.example.com.` |
| **NAPTR** | Naming Authority Pointer | `100 10 "s" "SIP+D2T" "" _sip._tcp.example.com.` |

### 6.2 Reverse DNS Management (`in-addr.arpa` & `ip6.arpa`)

1. **IPv4 Reverse Zone**:
   - For subnet `198.51.100.0/24`, create zone `100.51.198.in-addr.arpa`.
   - Add `PTR` record: Name `10.100.51.198.in-addr.arpa.`, Content `ns1.example.com.`.
2. **IPv6 Reverse Zone**:
   - For prefix `2001:db8::/32`, create zone `0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa`.
   - Add `PTR` record with nibble format for the target IPv6 address.

---

## 7. DNSSEC Cryptographic Automation

PHP-PDNSManager integrates directly with PowerDNS's native DNSSEC engine:

1. **Enabling DNSSEC for a Zone**:
   - Open zone details at `/zones/{id}`.
   - Click **Enable DNSSEC** or view **DNSSEC Keys**.
   - PHP-PDNSManager instructs PowerDNS to generate a Key Signing Key (KSK) and Zone Signing Key (ZSK) using modern cryptographic algorithms (e.g., Algorithm 13 - ECDSAP256SHA256).
2. **Delegation Signer (DS) Record**:
   - PHP-PDNSManager extracts the DS record generated by PowerDNS.
   - Copy the DS record (Key Tag, Algorithm, Digest Type, Digest) and submit it to your domain registrar to establish the cryptographic chain of trust.
3. **Verifying DNSSEC**:
   ```bash
   dig @127.0.0.1 example.com DNSKEY +dnssec
   delv @127.0.0.1 example.com
   ```

---

## 8. RFC 1035 BIND Zone File Migration & Bulk Operations

### 8.1 BIND Zone File Import & Export

- **Import (`/zones/import`)**: Paste raw RFC 1035 zone file content or upload a `.zone` file. The built-in parser (`BindZoneService`) automatically processes `$ORIGIN`, `$TTL`, SOA, and all resource records.
- **Export (`/zones/{id}/export-bind`)**: Download a standardized RFC 1035 `.zone` file for backups, audits, or migration to other systems.

### 8.2 Cross-Zone Bulk Record Operations (`/zones/bulk-records`)

- **Search**: Query records matching specific names, types, or content across all zones.
- **Batch Update**: Replace old IP addresses or target hostnames across dozens of zones in a single atomic operation.
- **Batch Delete**: Remove obsolete records in bulk across multiple zones.

---

## 9. Firewall & Operating System Hardening for Authoritative DNS

### 9.1 UFW Rules for Authoritative DNS (Ubuntu / Debian)

```bash
# Default policies
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Authoritative DNS (Public UDP and TCP)
sudo ufw allow 53/tcp
sudo ufw allow 53/udp

# Management Web GUI & SSH
sudo ufw allow from 192.168.1.0/24 to any port 22 proto tcp
sudo ufw allow from 192.168.1.0/24 to any port 443 proto tcp

# Enable Firewall
sudo ufw enable
```

### 9.2 Firewalld Rules (Rocky Linux / RHEL)

```bash
sudo firewall-cmd --set-default-zone=drop
sudo firewall-cmd --zone=drop --add-service=dns --permanent
sudo firewall-cmd --zone=drop --add-service=https --permanent
sudo firewall-cmd --zone=drop --add-rich-rule='rule family="ipv4" source address="192.168.1.0/24" port port="22" protocol="tcp" accept' --permanent
sudo firewall-cmd --reload
```

### 9.3 Linux Kernel UDP & Network Stack Hardening for DNS

Create `/etc/sysctl.d/99-dns-hardening.conf`:

```ini
# Increase socket receive and send buffers for high-volume DNS UDP traffic
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
net.core.rmem_default = 262144
net.core.wmem_default = 262144

# Increase netdev backlog for peak query bursts
net.core.netdev_max_backlog = 10000

# TCP SYN Flood Protection
net.ipv4.tcp_syncookies = 1
net.ipv4.tcp_max_syn_backlog = 8192
net.ipv4.tcp_fin_timeout = 15

# IP Spoofing & Route Hardening
net.ipv4.conf.all.rp_filter = 1
net.ipv4.conf.default.rp_filter = 1
net.ipv4.conf.all.accept_source_route = 0
net.ipv4.conf.default.accept_source_route = 0

# Ignore ICMP Broadcast Requests
net.ipv4.icmp_echo_ignore_broadcasts = 1
net.ipv4.icmp_ignore_bogus_error_responses = 1

# File Descriptors
fs.file-max = 2097152
```

Apply immediately:

```bash
sudo sysctl --system
```

---

## 10. Verification & Diagnostics Checklist

Execute these commands to confirm complete operational readiness:

1. **Verify PowerDNS Authoritative Query Response**:
   ```bash
   dig @127.0.0.1 example.com SOA +short
   dig @127.0.0.1 example.com NS +short
   dig @127.0.0.1 example.com A +short
   ```

2. **Verify PowerDNS DNSSEC Keys**:
   ```bash
   dig @127.0.0.1 example.com DNSKEY +dnssec
   ```

3. **Verify PowerDNS Authoritative Zone Integrity**:
   ```bash
   pdnsutil check-all-zones
   ```

4. **Verify PowerDNS REST API**:
   ```bash
   curl -s -H 'X-API-Key: StrongPowerDnsApiKeyGenerated2026!' http://127.0.0.1:8081/api/v1/servers/localhost | jq .
   ```

5. **Verify PHP-PDNSManager Web Application**:
   ```bash
   curl -I https://dns.example.com/login
   # Expected HTTP/2 200 OK with strict Content-Security-Policy and security headers
   ```
