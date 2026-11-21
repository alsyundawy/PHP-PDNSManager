<?php

declare(strict_types=1);

/**
 * PHP-PDNSManager Enterprise Edition — English Language Dictionary
 */
return [
    // Brand & Application
    'app_name' => 'PHP-PDNSManager Enterprise',
    'app_subtitle' => 'High Performance, Secure PowerDNS Authoritative Control Plane',
    'authoritative_dns' => 'Authoritative DNS',
    'connected' => 'Connected',
    'online' => 'Online',
    'offline' => 'Offline',
    'degraded' => 'Degraded',

    // Navigation Items
    'nav_dashboard' => 'Dashboard',
    'nav_zones' => 'Zones',
    'nav_bulk_records' => 'Bulk Records',
    'nav_reverse_wizard' => 'Reverse Wizard',
    'nav_dns_toolbox' => 'DNS Toolbox',
    'nav_tsig_keys' => 'TSIG Keys',
    'nav_cluster_backups' => 'Cluster Backups',
    'nav_zone_templates' => 'Zone Templates',
    'nav_server_clusters' => 'Server Clusters',
    'nav_audit_trail' => 'Audit Trail',
    'nav_health_check' => 'Health Check',
    'nav_logout' => 'Logout',
    'nav_quick_search' => 'Quick Search',

    // Theme Switcher & Accessibility
    'theme' => 'Theme',
    'theme_dark' => 'Slate Dark',
    'theme_light' => 'Clean Light',
    'theme_midnight' => 'Midnight Navy',
    'theme_ocean' => 'Ocean Teal',
    'theme_forest' => 'Emerald Forest',
    'theme_high_contrast' => 'High Contrast (AAA)',
    'toggle_theme' => 'Toggle Theme',
    'language' => 'Language',
    'lang_en' => 'English',
    'lang_id' => 'Bahasa Indonesia',

    // Common Actions & Labels
    'actions' => 'Actions',
    'add' => 'Add',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'search' => 'Search',
    'filter' => 'Filter',
    'refresh' => 'Refresh',
    'confirm' => 'Confirm',
    'back' => 'Back',
    'view' => 'View',
    'details' => 'Details',
    'status' => 'Status',
    'name' => 'Name',
    'type' => 'Type',
    'content' => 'Content',
    'ttl' => 'TTL',
    'priority' => 'Priority',
    'serial' => 'Serial',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'options' => 'Options',
    'enabled' => 'Enabled',
    'disabled' => 'Disabled',
    'close' => 'Close',
    'copied' => 'Copied!',
    'copy' => 'Copy',

    // Dashboard
    'dashboard_overview' => 'System Overview & Telemetry',
    'total_zones' => 'Total Zones',
    'active_servers' => 'Active PowerDNS Nodes',
    'total_records' => 'Total Resource Records',
    'cluster_health' => 'Cluster Status',
    'quick_actions' => 'Quick Operations',
    'create_new_zone' => 'Create New Zone',
    'import_bind_zone' => 'Import BIND Zone',
    'system_telemetry' => 'System Telemetry & Telemetry Status',
    'pdns_api_latency' => 'PowerDNS API Latency',
    'database_engine' => 'Database Engine',
    'php_runtime' => 'PHP Runtime',
    'recent_audit_events' => 'Recent Security Audit Events',

    // Zones
    'zones_title' => 'Authoritative DNS Zones',
    'zone_name' => 'Zone Name',
    'zone_kind' => 'Zone Kind',
    'zone_native' => 'Native',
    'zone_master' => 'Master',
    'zone_slave' => 'Slave',
    'zone_producer' => 'Producer (RFC 9432 Catalog)',
    'zone_consumer' => 'Consumer (RFC 9432 Catalog)',
    'add_zone' => 'Add Zone',
    'import_bind' => 'Import BIND Zone File',
    'export_bind' => 'Export BIND Zone',
    'export_json' => 'Export JSON',
    'clone_zone' => 'Clone Zone',
    'check_zone' => 'Check Zone Validity',
    'notify_slaves' => 'DNS NOTIFY',
    'axfr_retrieve' => 'AXFR Retrieve',
    'rectify_zone' => 'Rectify Zone',
    'dnssec_management' => 'DNSSEC Automation',
    'dnssec_enabled' => 'DNSSEC Active',
    'dnssec_disabled' => 'DNSSEC Inactive',
    'ds_records' => 'Delegation Signer (DS) Records',

    // Records
    'records_title' => 'Resource Records',
    'add_record' => 'Add Record',
    'edit_record' => 'Edit Record',
    'delete_record' => 'Delete Record',
    'record_name' => 'Record Name',
    'record_type' => 'Record Type',
    'record_content' => 'RDATA / Target',
    'auto_ptr' => 'Auto-PTR Sync',
    'bulk_update' => 'Bulk Record Update',

    // Reverse Wizard
    'reverse_wizard_title' => 'IPv4 / IPv6 Reverse DNS Zone Wizard',
    'reverse_wizard_desc' => 'Generate RFC 1035 and RFC 3596 compliant in-addr.arpa and ip6.arpa zones from CIDR subnet prefixes.',
    'cidr_subnet' => 'CIDR Subnet Prefix',
    'generate_reverse_zone' => 'Generate Reverse Zone',

    // DNS Toolbox
    'dns_toolbox_title' => 'DNS Security & Diagnostics Toolbox',
    'spf_validator' => 'RFC 7208 SPF Validator & Builder',
    'dkim_inspector' => 'DKIM Public Key Selector Inspector',
    'dmarc_builder' => 'RFC 7489 DMARC Policy Builder',
    'rbl_checker' => 'Multi-Provider RBL / DNSBL Blacklist Checker',
    'lookup_domain' => 'Domain to Inspect',
    'lookup_button' => 'Run Diagnostic Lookup',

    // TSIG Keys
    'tsig_keys_title' => 'RFC 2845 / RFC 8945 TSIG Keys',
    'tsig_algorithm' => 'HMAC Algorithm',
    'tsig_secret' => 'Shared Secret (Base64)',
    'generate_tsig_key' => 'Generate TSIG Key',

    // Backups
    'backups_title' => 'Cluster Snapshots & Encrypted Backups',
    'create_backup' => 'Create Cluster Snapshot',
    'encrypt_snapshot' => 'AES-256-GCM Encryption',
    'backup_passphrase' => 'Encryption Passphrase',
    'restore_preview' => 'Dry-Run Restore Preview',
    'restore_confirm' => 'Confirm Restore Snapshot',

    // Server Clusters
    'servers_title' => 'PowerDNS Server Nodes',
    'server_name' => 'Server Name',
    'server_api_url' => 'Internal REST API URL',
    'server_api_key' => 'PowerDNS API Key',
    'test_connection' => 'Test Connection',
    'set_default_server' => 'Set as Default Cluster Node',

    // Zone Templates
    'templates_title' => 'DNS Zone Templates & Blueprints',
    'create_template' => 'Create Zone Template',
    'template_name' => 'Template Name',
    'apply_template' => 'Apply Template to Zone',

    // Audit Trail
    'audit_title' => 'Security Audit Trail',
    'audit_user' => 'User',
    'audit_action' => 'Action',
    'audit_resource' => 'Resource',
    'audit_ip' => 'IP Address',
    'audit_status' => 'Status Code',
    'export_csv' => 'Export Audit CSV',

    // Health Telemetry
    'health_title' => 'PowerDNS Daemon & Cluster Telemetry',
    'health_overall' => 'Overall Operational Status',
    'health_database' => 'Persistence Database',
    'health_storage' => 'Storage Directory Permissions',
    'health_pdns_daemon' => 'PowerDNS Authoritative Service',

    // Auth & Login
    'login_title' => 'Sign In — PHP-PDNSManager Enterprise',
    'login_header' => 'Authoritative DNS Control Plane',
    'username' => 'Username',
    'password' => 'Password',
    'remember_me' => 'Remember me',
    'sign_in' => 'Sign In',
    'welcome_back' => 'Enterprise Access Portal',
    'invalid_credentials' => 'Invalid username or password',
];
