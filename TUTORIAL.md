# Production Deployment, Installation, and Zimbra Mail Server Integration Guide

**PHP-PDNSManager Enterprise Edition (v1.0.1)**
_Engineered for High-Availability Authoritative DNS, Simple Recursive Resolvers, and Hardened Mail Infrastructure_

---

## 1. System Overview & Architecture

PHP-PDNSManager is an enterprise-grade web management suite for **PowerDNS Authoritative Server**. In high-performance mail and enterprise hosting environments (such as Zimbra Collaboration Suite), DNS architecture must be divided into two distinct tiers:

1. **Authoritative DNS Tier (Primary Focus)**:
    - Hosted by **PowerDNS Authoritative Server** (`pdns`) backed by MySQL/MariaDB.
    - Binds to the public network interfaces (`0.0.0.0:53` and `[::]:53`).
    - Serves authoritative records (`A`, `AAAA`, `MX`, `TXT`, `PTR`, `SRV`, `CAA`, `CNAME`, `DNSSEC`) to the world without recursion.
    - Controlled via the PowerDNS REST API (`127.0.0.1:8081`) managed by PHP-PDNSManager.

2. **Simple Recursive Resolver Tier (Local Resolution Only - Strictly No RPZ)**:
    - Hosted by **PowerDNS Recursor** or **Unbound** / **BIND9** configured strictly as a lightweight caching/forwarding resolver without Response Policy Zones (RPZ).
    - Binds exclusively to the loopback interface (`127.0.0.1:53` or `127.0.0.53:53`) or trusted internal subnets.
    - Essential for Zimbra Mail Server to perform DNSBL/RBL spam lookups (Spamhaus, Spamcop, Barracuda) without exceeding public DNS rate limits.

```text
       [ Public Internet Clients / MTAs ]
                       | (Port 53 UDP/TCP)
                       v
     +-----------------------------------+
     |   PowerDNS Authoritative Server   |<---+
     |        (Port 53 Public IP)        |    |
     +-----------------------------------+    |
                       |                      |
            [ MySQL / MariaDB Cluster ]       | REST API
                       ^                      | (Port 8081 Localhost)
                       |                      |
             +-------------------+            |
             | PHP-PDNSManager   |------------+
             | (Nginx + PHP 8.1+)|
             +-------------------+

     +-----------------------------------+
     |   Simple Recursive Resolver       | <--- Queried by Zimbra MTA / Postscreen
     |  (127.0.0.1:53 - Standard Cache)  |      (Strictly No RPZ)
     +-----------------------------------+
```

---

## 2. Server Preparation (Ubuntu 20.04/22.04 LTS, Debian 12, Rocky Linux 8/9)

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

Accurate time is mandatory for DNSSEC key rotation and mail TLS handshakes:

```bash
# Ubuntu / Debian
sudo apt install -y chrony
sudo systemctl enable --now chrony

# Rocky Linux / CentOS
sudo dnf install -y chrony
sudo systemctl enable --now chronyd
```

---

## 3. PowerDNS Authoritative Server Installation & Backend Setup

### 3.1 Install PowerDNS and MariaDB

```bash
# Ubuntu 22.04 LTS / Debian 12
sudo apt install -y pdns-server pdns-backend-mysql mariadb-server mariadb-client

# Rocky Linux 8/9 / CentOS Stream
sudo dnf install -y mariadb-server mariadb
sudo systemctl enable --now mariadb
sudo dnf install -y pdns pdns-backend-mysql
```

### 3.2 Initialize PowerDNS Database

Secure MariaDB and create the PowerDNS schema:

```bash
sudo mysql_secure_installation
```

Log in to MySQL as root:

```bash
sudo mysql -u root -p
```

Execute SQL schema setup:

```sql
CREATE DATABASE pdns CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pdns_user'@'localhost' IDENTIFIED BY 'SuperSecretPdnsDbPassword2026!';
GRANT ALL PRIVILEGES ON pdns.* TO 'pdns_user'@'localhost';

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

# Network Binding (Authoritative Server)
local-address=0.0.0.0, ::
local-port=53

# Backend Configuration
launch=gmysql
gmysql-host=127.0.0.1
gmysql-port=3306
gmysql-user=pdns_user
gmysql-password=SuperSecretPdnsDbPassword2026!
gmysql-dbname=pdns
gmysql-dnssec=yes

# Internal Webserver & REST API (Secured to Localhost)
webserver=yes
webserver-address=127.0.0.1
webserver-port=8081
webserver-allow-from=127.0.0.1, ::1
api=yes
api-key=StrongPowerDnsApiKeyGenerated2026!

# Performance Tuning
distributor-threads=3
receiver-threads=2
cache-ttl=60
negquery-cache-ttl=60
query-cache-ttl=20
default-soa-edit=INCEPTION-INCREMENT
```

Restart and verify PowerDNS:

```bash
sudo systemctl restart pdns
sudo systemctl enable pdns
curl -H 'X-API-Key: StrongPowerDnsApiKeyGenerated2026!' http://127.0.0.1:8081/api/v1/servers/localhost
```

---

## 4. Simple Recursive Resolver Setup (Strictly No RPZ)

Zimbra Mail Server processes hundreds of DNS queries per minute for SPF, DKIM, and Real-Time Blacklists (RBL). Public DNS servers (e.g., `8.8.8.8`, `1.1.1.1`) block RBL queries from Spamhaus (`zen.spamhaus.org`). Therefore, a local caching recursive resolver without Response Policy Zones (RPZ) is required.

### 4.1 Option A: Simple PowerDNS Recursor

Install and configure PowerDNS Recursor on `127.0.0.1:5300` or secondary loopback `127.0.0.53:53`:

```bash
# Ubuntu / Debian
sudo apt install -y pdns-recursor

# Edit /etc/powerdns/recursor.conf
local-address=127.0.0.53
local-port=53
allow-from=127.0.0.0/8, ::1/128
threads=4
max-cache-entries=1000000
# Strictly no RPZ configuration
```

### 4.2 Option B: Simple Unbound Recursive Resolver (No RPZ)

```bash
sudo apt install -y unbound

# Create /etc/unbound/unbound.conf.d/simple-recursive.conf
server:
    interface: 127.0.0.1
    port: 5353
    access-control: 127.0.0.0/8 allow
    do-ip4: yes
    do-ip6: yes
    do-udp: yes
    do-tcp: yes
    hide-identity: yes
    hide-version: yes
    harden-glue: yes
    harden-dnssec-stripped: yes
    use-caps-for-id: no
    cache-min-ttl: 60
    cache-max-ttl: 86400
    prefetch: yes
    num-threads: 2
    # STRICT REQUIREMENT: No RPZ (response-policy) blocks
```

Start the service:

```bash
sudo systemctl restart unbound
sudo systemctl enable unbound
```

---

## 5. PHP-PDNSManager Enterprise Deployment

### 5.1 Install Nginx & PHP 8.1+

```bash
# Ubuntu 22.04 LTS
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

Run database migrations and initial seeder:

```bash
php bin/console migrate
php bin/console seed
```

### 5.3 Offline Vendor Assets Verification

Ensure all local vendor assets are present and ready (Strict Zero CDN):

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

## 6. Authoritative DNS Records Setup for Zimbra Mail Server

In PHP-PDNSManager, create the authoritative zone for your domain (e.g., `example.com`) and add the following records:

| Record Type | Hostname / Name                  | Priority | Content / Value                                                   | Purpose                             |
| :---------- | :------------------------------- | :------- | :---------------------------------------------------------------- | :---------------------------------- |
| **A**       | `mail.example.com`               | -        | `198.51.100.25`                                                   | Primary Zimbra MTA IPv4             |
| **AAAA**    | `mail.example.com`               | -        | `2001:db8::25`                                                    | Primary Zimbra MTA IPv6 (if active) |
| **MX**      | `example.com`                    | `10`     | `mail.example.com.`                                               | Mail Exchanger routing              |
| **TXT**     | `example.com`                    | -        | `"v=spf1 mx ip4:198.51.100.25 ~all"`                              | SPF record for authorized sending   |
| **TXT**     | `01A2B3._domainkey.example.com`  | -        | `"v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BA...IDAQAB"`              | DKIM public key signature           |
| **TXT**     | `_dmarc.example.com`             | -        | `"v=DMARC1; p=quarantine; rua=mailto:dmarc@example.com; pct=100"` | DMARC domain alignment policy       |
| **CNAME**   | `autodiscover.example.com`       | -        | `mail.example.com.`                                               | Outlook autodiscover service        |
| **CNAME**   | `autoconfig.example.com`         | -        | `mail.example.com.`                                               | Thunderbird autoconfig service      |
| **SRV**     | `_autodiscover._tcp.example.com` | `0 443`  | `mail.example.com.`                                               | ActiveSync Autodiscover pointer     |

### 6.1 Reverse DNS (`PTR` Record in `in-addr.arpa`)

Zimbra's Postfix server checks reverse DNS during sender evaluation. The MTA banner (`myhostname` / `zmhostname`) must match the PTR record.

In PHP-PDNSManager:

1. Create zone `100.51.198.in-addr.arpa`.
2. Add `PTR` record:
    - Name: `25.100.51.198.in-addr.arpa.`
    - Content: `mail.example.com.`

---

## 7. Zimbra Cross-OS Migration Master Guide (CentOS to Rocky / Ubuntu)

_Synthesized from official Zimbra Collaboration docs and proven practices from the Indonesian Zimbra Community (PT. Excellent Infotama Kreasindo by Vavai / M. Rifai / Imanudin)._

### 7.1 Why Direct `/opt/zimbra` Copy Fails Cross-OS

A direct `rsync` of `/opt/zimbra` is **only valid between identical OS versions and patch levels** (e.g., Ubuntu 20.04 to Ubuntu 20.04). Copying `/opt/zimbra` from CentOS 7 to Ubuntu 20.04/22.04 or Rocky Linux 9 fails catastrophically due to:

1. **Dynamic Linker & glibc differences**: Compiled binaries (OpenLDAP `slapd`, Postfix, Amavis, MySQL/MariaDB engines) link against different versions of glibc and libssl.
2. **Perl and Python Path Discrepancies**: Library paths (`/usr/lib64/perl5` vs `/usr/share/perl5`) break core scripts (`zmcontrol`, `zmlocalconfig`).
3. **OpenLDAP Database Format (MDB)**: Differences in 32-bit vs 64-bit page sizing and OpenLDAP version dependencies.

### 7.2 Method 1: Open-Source Edition (FOSS) Scripted REST API Migration

This method works across any operating systems and Zimbra versions.

#### Step 1: Export Accounts, Passwords, and Distribution Lists on Source (CentOS)

Run as user `zimbra`:

```bash
su - zimbra
mkdir -p /tmp/zimbra-export && cd /tmp/zimbra-export

# 1. Export domains
zmprov gad > domains.txt

# 2. Export accounts
zmprov -l gaa > accounts.txt

# 3. Export passwords, user details, and identities
for acc in $(cat accounts.txt); do
    zmprov -l ga $acc userPassword | grep userPassword: | awk '{print $2}' > pass_$acc.txt
    zmprov -l ga $acc cn displayName givenName sn > details_$acc.txt
done

# 4. Export Distribution Lists and Members
zmprov gadl > dl.txt
for list in $(cat dl.txt); do
    zmprov gdl $list > members_$list.txt
done

# 5. Export User Mailbox Data (Mails, Contacts, Calendar, Briefcase) via REST API
for acc in $(cat accounts.txt); do
    echo "Exporting mailbox $acc..."
    zmmailbox -z -m $acc getRestURL -u "//?fmt=tgz" > /tmp/zimbra-export/$acc.tgz
done
```

#### Step 2: Install Clean Zimbra on Target Server (Ubuntu 22.04 / Rocky 9)

1. Install an identical or newer release of Zimbra on the target OS.
2. Ensure hostname and MX record are set properly.

#### Step 3: Import Accounts & Data on Target Server

Transfer `/tmp/zimbra-export` to the new server and execute:

```bash
su - zimbra
cd /tmp/zimbra-export

# 1. Recreate domains
for dom in $(cat domains.txt); do
    zmprov cd $dom
done

# 2. Recreate accounts with original password hash
for acc in $(cat accounts.txt); do
    PASS=$(cat pass_$acc.txt)
    zmprov ca $acc TemporaryPass123! userPassword "$PASS"
done

# 3. Recreate Distribution Lists
for list in $(cat dl.txt); do
    zmprov cdl $list
    for member in $(grep -i members members_$list.txt | awk '{print $2}'); do
        zmprov adlm $list $member
    done
done

# 4. Restore Mailbox Content via REST API
for acc in $(cat accounts.txt); do
    if [ -f "/tmp/zimbra-export/$acc.tgz" ]; then
        echo "Restoring mailbox $acc..."
        zmmailbox -z -m $acc postRestURL -u "//?fmt=tgz&resolve=skip" /tmp/zimbra-export/$acc.tgz
    fi
done
```

### 7.3 Method 2: Zextras Suite / Network Edition Backup & Restore

If running Zimbra Network Edition or Zextras Suite:

1. On source server:
    ```bash
    zxsuite backup doBackupCluster
    ```
2. Mount the backup directory on the new server.
3. On target server:
    ```bash
    zxsuite backup doRestoreOnNewServer /opt/zimbra/backup/zextras
    ```

---

## 8. Zimbra Security, CVE Remediation, Malware & Anti-Spam

### 8.1 Critical Zimbra CVE Timeline & Mitigations (2018–Present)

| CVE Identifier     | Vulnerability Summary                           | Remediation / Hardening Action                                                                      |
| :----------------- | :---------------------------------------------- | :-------------------------------------------------------------------------------------------------- |
| **CVE-2019-9670**  | XXE in MailboxService (Admin/Autodiscover)      | Update ZCS to 8.8.15 Patch 1+. Restrict port 7071 to internal management network.                   |
| **CVE-2022-27925** | mboximport Zip Slip Arbitrary File Upload (RCE) | Patch immediately. Disable public mboximport endpoint via Nginx rewrite rules.                      |
| **CVE-2022-30333** | UnRAR Path Traversal in Amavisd                 | Replace `unrar` with 7-Zip or install unrar `>= 6.1.7`. On Debian/Ubuntu: `apt install unrar-free`. |
| **CVE-2022-41352** | cpio Command Injection in Amavisd               | Install `pax` utility so Amavis uses pax instead of cpio: `apt install pax` or `dnf install pax`.   |
| **CVE-2024-45519** | Postjournal Remote Code Execution (0-Day)       | Patch `zmconfigd` and postfix immediately. Disable postjournal if unused.                           |

### 8.2 Postfix Postscreen & DNSBL Integration

Configure Postscreen to reject spam connections at the TCP handshake before Amavis/ClamAV processing:

```bash
su - zimbra
# Enable Postscreen in Zimbra Postfix
zmprov mcf zimbraPostscreenEnable true
zmprov mcf zimbraPostscreenGreetAction enforce
zmprov mcf zimbraPostscreenDnsblAction enforce
zmprov mcf zimbraPostscreenDnsblSites "zen.spamhaus.org*3" "bl.spamcop.net*2" "b.barracudacentral.org*2"
zmprov mcf zimbraPostscreenDnsblThreshold 3
zmmtactl restart
```

### 8.3 Block Dangerous Executables in Amavis

Edit `/opt/zimbra/conf/amavisd.conf.in` inside the `$banned_filename_re` block to reject ransomware vectors:

```perl
qr'.\.(exe|vbs|pif|scr|bat|cmd|com|cpl|dll|hta|iso|img|jar|js|vbe|wsf)$'i,
```

Apply configuration:

```bash
zmamavisdctl restart
```

---

## 9. Firewall (UFW & iptables) and Linux OS Hardening

### 9.1 UFW Rules for Zimbra Mail Server (Ubuntu)

```bash
# Default policies
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Management: SSH & Zimbra Admin Console (REPLICATE TO TRUSTED IP ONLY)
sudo ufw allow from 192.168.1.0/24 to any port 22 proto tcp
sudo ufw allow from 192.168.1.0/24 to any port 7071 proto tcp

# Public Mail Services
sudo ufw allow 25/tcp    # SMTP Inbound
sudo ufw allow 465/tcp   # SMTPS Submissions
sudo ufw allow 587/tcp   # Submission
sudo ufw allow 993/tcp   # IMAPS
sudo ufw allow 995/tcp   # POP3S
sudo ufw allow 80/tcp    # HTTP (Let's Encrypt / Webmail Redirect)
sudo ufw allow 443/tcp   # HTTPS Webmail

# Enable Firewall
sudo ufw enable
```

### 9.2 iptables / firewalld Rules (Rocky Linux / CentOS)

```bash
sudo firewall-cmd --set-default-zone=drop
sudo firewall-cmd --zone=drop --add-service=smtp --permanent
sudo firewall-cmd --zone=drop --add-service=smtps --permanent
sudo firewall-cmd --zone=drop --add-service=smtp-submission --permanent
sudo firewall-cmd --zone=drop --add-service=imaps --permanent
sudo firewall-cmd --zone=drop --add-service=pop3s --permanent
sudo firewall-cmd --zone=drop --add-service=http --permanent
sudo firewall-cmd --zone=drop --add-service=https --permanent

# Restrict Admin and SSH to Management Network
sudo firewall-cmd --zone=drop --add-rich-rule='rule family="ipv4" source address="192.168.1.0/24" port port="7071" protocol="tcp" accept' --permanent
sudo firewall-cmd --zone=drop --add-rich-rule='rule family="ipv4" source address="192.168.1.0/24" port port="22" protocol="tcp" accept' --permanent
sudo firewall-cmd --reload
```

### 9.3 Linux Kernel TCP & Network Stack Hardening

Create `/etc/sysctl.d/99-network-hardening.conf`:

```ini
# SYN Flood Protection
net.ipv4.tcp_syncookies = 1
net.ipv4.tcp_max_syn_backlog = 4096
net.ipv4.tcp_synack_retries = 2

# IP Spoofing & Route Hardening
net.ipv4.conf.all.rp_filter = 1
net.ipv4.conf.default.rp_filter = 1
net.ipv4.conf.all.accept_source_route = 0
net.ipv4.conf.default.accept_source_route = 0

# Ignore ICMP Broadcast Requests
net.ipv4.icmp_echo_ignore_broadcasts = 1
net.ipv4.icmp_ignore_bogus_error_responses = 1

# File Descriptor & Socket Allocation for High-Throughput DNS / Mail
fs.file-max = 2097152
```

Apply immediately:

```bash
sudo sysctl --system
```

---

## 10. Verification & Diagnostics Checklist

Execute these commands to confirm complete operational readiness:

1. **Verify PowerDNS Authoritative Server**:

    ```bash
    dig @127.0.0.1 mail.example.com A +short
    dig @127.0.0.1 example.com MX +short
    dig @127.0.0.1 example.com TXT +short
    ```

2. **Verify Local Recursive Resolver (No RPZ)**:

    ```bash
    dig @127.0.0.1 -p 5353 2.0.0.127.zen.spamhaus.org TXT +short
    # Expected response: "127.0.0.2" (Testing Spamhaus RBL connectivity)
    ```

3. **Verify Zimbra Mail Server Status**:

    ```bash
    su - zimbra -c "zmcontrol status"
    ```

4. **Verify PHP-PDNSManager Web Application**:
    ```bash
    curl -I https://dns.example.com/login
    # Expected HTTP/2 200 OK with strict Content-Security-Policy & anti-clipping meta headers
    ```
