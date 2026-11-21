<?php

declare(strict_types=1);

/**
 * PHP-PDNSManager Enterprise Edition — Kamus Bahasa Indonesia
 */
return [
    // Brand & Application
    'app_name' => 'PHP-PDNSManager Enterprise',
    'app_subtitle' => 'Panel Kontrol Authoritative PowerDNS Berkeamanan Tinggi',
    'authoritative_dns' => 'Authoritative DNS',
    'connected' => 'Terhubung',
    'online' => 'Aktif',
    'offline' => 'Nonaktif',
    'degraded' => 'Terdegradasi',

    // Navigation Items
    'nav_dashboard' => 'Dasbor',
    'nav_zones' => 'Zona DNS',
    'nav_bulk_records' => 'Operasi Massal',
    'nav_reverse_wizard' => 'Wisaya Reverse DNS',
    'nav_dns_toolbox' => 'Kotak Alat DNS',
    'nav_tsig_keys' => 'Kunci TSIG',
    'nav_cluster_backups' => 'Cadangan Klaster',
    'nav_zone_templates' => 'Templat Zona',
    'nav_server_clusters' => 'Klaster Server',
    'nav_audit_trail' => 'Jejak Audit',
    'nav_health_check' => 'Uji Kesehatan',
    'nav_logout' => 'Keluar',
    'nav_quick_search' => 'Pencarian Cepat',

    // Theme Switcher & Accessibility
    'theme' => 'Tema Tampilan',
    'theme_dark' => 'Slate Gelap',
    'theme_light' => 'Terang Bersih',
    'theme_midnight' => 'Biru Tengah Malam',
    'theme_ocean' => 'Sian Samudera',
    'theme_forest' => 'Hijau Zamrud',
    'theme_high_contrast' => 'Kontras Tinggi (AAA)',
    'toggle_theme' => 'Ganti Tema',
    'language' => 'Bahasa',
    'lang_en' => 'English',
    'lang_id' => 'Bahasa Indonesia',

    // Common Actions & Labels
    'actions' => 'Tindakan',
    'add' => 'Tambah',
    'edit' => 'Ubah',
    'delete' => 'Hapus',
    'save' => 'Simpan',
    'cancel' => 'Batal',
    'search' => 'Cari',
    'filter' => 'Saring',
    'refresh' => 'Segarkan',
    'confirm' => 'Konfirmasi',
    'back' => 'Kembali',
    'view' => 'Lihat',
    'details' => 'Detail',
    'status' => 'Status',
    'name' => 'Nama',
    'type' => 'Tipe',
    'content' => 'Konten RDATA',
    'ttl' => 'TTL',
    'priority' => 'Prioritas',
    'serial' => 'Nomor Seri',
    'created_at' => 'Waktu Dibuat',
    'updated_at' => 'Waktu Diperbarui',
    'options' => 'Opsi',
    'enabled' => 'Diaktifkan',
    'disabled' => 'Dinonaktifkan',
    'close' => 'Tutup',
    'copied' => 'Tersalin!',
    'copy' => 'Salin',

    // Dashboard
    'dashboard_overview' => 'Ringkasan Sistem & Telemetri',
    'total_zones' => 'Total Zona DNS',
    'active_servers' => 'Node PowerDNS Aktif',
    'total_records' => 'Total Catatan DNS',
    'cluster_health' => 'Status Klaster',
    'quick_actions' => 'Operasi Cepat',
    'create_new_zone' => 'Buat Zona Baru',
    'import_bind_zone' => 'Impor Berkas Zona BIND',
    'system_telemetry' => 'Status Telemetri Sistem',
    'pdns_api_latency' => 'Latensi API PowerDNS',
    'database_engine' => 'Mesin Basis Data',
    'php_runtime' => 'Runtime PHP',
    'recent_audit_events' => 'Peristiwa Audit Keamanan Terkini',

    // Zones
    'zones_title' => 'Zona DNS Authoritative',
    'zone_name' => 'Nama Zona',
    'zone_kind' => 'Jenis Zona',
    'zone_native' => 'Native',
    'zone_master' => 'Master',
    'zone_slave' => 'Slave',
    'zone_producer' => 'Producer (RFC 9432 Catalog)',
    'zone_consumer' => 'Consumer (RFC 9432 Catalog)',
    'add_zone' => 'Tambah Zona',
    'import_bind' => 'Impor Berkas Zona BIND',
    'export_bind' => 'Ekspor Format BIND',
    'export_json' => 'Ekspor JSON',
    'clone_zone' => 'Kloning Zona',
    'check_zone' => 'Validasi Zona',
    'notify_slaves' => 'Kirim DNS NOTIFY',
    'axfr_retrieve' => 'Tarik Transfer AXFR',
    'rectify_zone' => 'Rektifikasi Zona',
    'dnssec_management' => 'Otomasi DNSSEC',
    'dnssec_enabled' => 'DNSSEC Aktif',
    'dnssec_disabled' => 'DNSSEC Nonaktif',
    'ds_records' => 'Catatan Delegation Signer (DS)',

    // Records
    'records_title' => 'Catatan Sumber Daya (RR)',
    'add_record' => 'Tambah Catatan',
    'edit_record' => 'Ubah Catatan',
    'delete_record' => 'Hapus Catatan',
    'record_name' => 'Nama Host / Catatan',
    'record_type' => 'Tipe Catatan',
    'record_content' => 'RDATA / Target',
    'auto_ptr' => 'Sinkronisasi Auto-PTR',
    'bulk_update' => 'Pembaruan Catatan Massal',

    // Reverse Wizard
    'reverse_wizard_title' => 'Wisaya Zona Reverse DNS IPv4 / IPv6',
    'reverse_wizard_desc' => 'Hasilkan zona in-addr.arpa dan ip6.arpa sesuai RFC 1035 & RFC 3596 secara otomatis dari prefiks subnet CIDR.',
    'cidr_subnet' => 'Prefiks Subnet CIDR',
    'generate_reverse_zone' => 'Buat Zona Reverse',

    // DNS Toolbox
    'dns_toolbox_title' => 'Kotak Alat Keamanan & Diagnostik DNS',
    'spf_validator' => 'Pemeriksa & Pembuat SPF RFC 7208',
    'dkim_inspector' => 'Inspeksi Selektor Kunci Publik DKIM',
    'dmarc_builder' => 'Pembuat Kebijakan DMARC RFC 7489',
    'rbl_checker' => 'Pengecekan Daftar Hitam Multi-Penyedia RBL/DNSBL',
    'lookup_domain' => 'Nama Domain untuk Diperiksa',
    'lookup_button' => 'Jalankan Diagnostik',

    // TSIG Keys
    'tsig_keys_title' => 'Kunci TSIG RFC 2845 / RFC 8945',
    'tsig_algorithm' => 'Algoritma HMAC',
    'tsig_secret' => 'Rahasia Bersama (Base64)',
    'generate_tsig_key' => 'Hasilkan Kunci TSIG',

    // Backups
    'backups_title' => 'Snapshot Klaster & Cadangan Terenkripsi',
    'create_backup' => 'Buat Snapshot Klaster',
    'encrypt_snapshot' => 'Enkripsi AES-256-GCM',
    'backup_passphrase' => 'Frasa Sandi Enkripsi',
    'restore_preview' => 'Pratinjau Uji Pulih (Dry-Run)',
    'restore_confirm' => 'Konfirmasi Pemulihan Snapshot',

    // Server Clusters
    'servers_title' => 'Node Server PowerDNS',
    'server_name' => 'Nama Server',
    'server_api_url' => 'URL API REST Internal',
    'server_api_key' => 'Kunci API PowerDNS',
    'test_connection' => 'Uji Koneksi',
    'set_default_server' => 'Jadikan Node Default Klaster',

    // Zone Templates
    'templates_title' => 'Templat & Cetak Biru Zona DNS',
    'create_template' => 'Buat Templat Zona',
    'template_name' => 'Nama Templat',
    'apply_template' => 'Terapkan Templat ke Zona',

    // Audit Trail
    'audit_title' => 'Jejak Audit Keamanan',
    'audit_user' => 'Pengguna',
    'audit_action' => 'Aktivitas',
    'audit_resource' => 'Sumber Daya',
    'audit_ip' => 'Alamat IP',
    'audit_status' => 'Kode Status',
    'export_csv' => 'Ekspor CSV Audit',

    // Health Telemetry
    'health_title' => 'Telemetri Daemon & Klaster PowerDNS',
    'health_overall' => 'Status Operasional Keseluruhan',
    'health_database' => 'Basis Data Persistensi',
    'health_storage' => 'Izin Direktori Penyimpanan',
    'health_pdns_daemon' => 'Layanan Authoritative PowerDNS',

    // Auth & Login
    'login_title' => 'Masuk — PHP-PDNSManager Enterprise',
    'login_header' => 'Panel Kontrol DNS Authoritative',
    'username' => 'Nama Pengguna',
    'password' => 'Kata Sandi',
    'sign_in' => 'Masuk Sistem',
    'welcome_back' => 'Portal Akses Korporat',
    'invalid_credentials' => 'Nama pengguna atau kata sandi tidak valid',
];
