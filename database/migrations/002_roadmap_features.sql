CREATE TABLE IF NOT EXISTS pdns_servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL UNIQUE,
    api_url VARCHAR(255) NOT NULL,
    api_key VARCHAR(255) NOT NULL,
    server_id VARCHAR(64) NOT NULL DEFAULT 'localhost',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_server_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS zone_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_template_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS zone_template_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(16) NOT NULL,
    content TEXT NOT NULL,
    ttl INT NOT NULL DEFAULT 3600,
    priority INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_template_id (template_id),
    FOREIGN KEY (template_id) REFERENCES zone_templates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS webhooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    url VARCHAR(255) NOT NULL,
    secret VARCHAR(255) NULL,
    events TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_triggered_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_webhook_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS organizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL UNIQUE,
    slug VARCHAR(128) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_org_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS organization_user (
    organization_id INT NOT NULL,
    user_id INT NOT NULL,
    role VARCHAR(64) NOT NULL DEFAULT 'member',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (organization_id, user_id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS zone_organizations (
    zone_id VARCHAR(255) NOT NULL,
    organization_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (zone_id, organization_id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default Zone Templates
INSERT IGNORE INTO zone_templates (id, name, description, is_default) VALUES
(1, 'Standard Web Hosting', 'Basic website setup with Web, Mail, SPF records', 1),
(2, 'Google Workspace', 'Mail routing & SPF configured for Google Workspace', 0);

INSERT IGNORE INTO zone_template_records (template_id, name, type, content, ttl, priority) VALUES
(1, '@', 'A', '192.0.2.1', 3600, NULL),
(1, 'www', 'CNAME', '@', 3600, NULL),
(1, '@', 'MX', 'mail.@', 3600, 10),
(1, '@', 'TXT', '"v=spf1 a mx ~all"', 3600, NULL),
(2, '@', 'MX', 'aspmx.l.google.com.', 3600, 1),
(2, '@', 'MX', 'alt1.aspmx.l.google.com.', 3600, 5),
(2, '@', 'MX', 'alt2.aspmx.l.google.com.', 3600, 5),
(2, '@', 'MX', 'alt3.aspmx.l.google.com.', 3600, 10),
(2, '@', 'MX', 'alt4.aspmx.l.google.com.', 3600, 10),
(2, '@', 'TXT', '"v=spf1 include:_spf.google.com ~all"', 3600, NULL);
