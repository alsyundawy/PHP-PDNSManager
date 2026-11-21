# Credits & Acknowledgements

**PHP-PDNSManager Enterprise Edition** is architected, developed, and maintained by **HARRY DERTIN SUTISNA ALSYUNDAWY** with foundational contributions and infrastructure from the global open-source community.

---

## 👤 Founder, Lead Architect & Maintainer

- **[Harry Dertin Sutisna Alsyundawy (@alsyundawy)](https://github.com/alsyundawy)**
  - Founder & Principal Enterprise Architect at **[ALSYUNDAWY IT SOLUTION](https://alsyundawy.com)**.
  - Creator of the Visual Subnet Calculator theming engine, authoritative DNS invariants, multi-server clustering architecture, zero-CDN air-gapped asset design, and enterprise automation toolchain.
  - GitHub: [`@alsyundawy`](https://github.com/alsyundawy) | Telegram: [`@alsyundawy`](https://t.me/alsyundawy) | Email: [`alsyundawy@gmail.com`](mailto:alsyundawy@gmail.com)

---

## 🏛️ PowerDNS Authoritative Core & APIs

- **[PowerDNS.COM BV](https://www.powerdns.com/)**
  - High-performance PowerDNS Authoritative Server daemon (`pdns_server`), RFC 9460 & RFC 9432 implementations, and internal HTTP REST API v1.
  - Special appreciation for the native replication control plane (`notify`, `axfr-retrieve`, `rectify`) and domain metadata subsystem.

---

## 🔬 Domain References & Community Architecture

- **[Charles A. Barnes (`charlesabarnes/SPFtoolbox`)](https://github.com/charlesabarnes/SPFtoolbox)**
  - Domain research, RFC 7208 SPF mechanism parsing, 10-lookup ceiling validation, and DMARC/DKIM email deliverability diagnostics.
- **[PowerDNS-Admin Community (`PowerDNS-Admin/PowerDNS-Admin`)](https://github.com/PowerDNS-Admin/PowerDNS-Admin)**
  - Automated Reverse DNS (`Auto-PTR`) synchronization patterns and zone history concepts.
- **[Poweradmin Project (`poweradmin/poweradmin`)](https://github.com/poweradmin/poweradmin)**
  - PowerDNS database backend integration and permission structure references.
- **[PHP-BindManager (`alsyundawy/PHP-BindManager`)](https://github.com/alsyundawy/PHP-BindManager)**
  - Legacy BIND9 zone file parser, zone templating engine, and DNS diagnostic utilities.

---

## 🐘 PHP Foundation & Runtime Ecosystem

- **[The PHP Group](https://www.php.net/)** and global core developers for modern PHP (PHP 8.1, 8.2, 8.3, 8.4, 8.5, and 8.6) delivering strict typing (`declare(strict_types=1);`), JIT, fibers, and memory-efficient runtime execution.
- **[PHP-FIG (Framework Interoperability Group)](https://www.php-fig.org/)** for standard recommendations:
  - PSR-7 (HTTP Message Interface)
  - PSR-11 (Container Interface)
  - PSR-12 & PER Coding Style (Coding Style Standard)
  - PSR-14 (Event Dispatcher)
  - PSR-15 (HTTP Server Handlers & Middleware)
  - PSR-17 (HTTP Factories)
  - PSR-18 (HTTP Client)

---

## 🛡️ Enterprise Testing & Static Analysis Toolchains

- **[PHPUnit](https://phpunit.de/)** by Sebastian Bergmann for enterprise test execution.
- **[PHPStan](https://phpstan.org/)** by Ondřej Mirtes for strict Level 5 static analysis.
- **[Psalm](https://psalm.dev/)** by Vimeo for advanced semantic type inference and type safety.
- **[PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer)** by Greg Sherwood and PHPCSStandards for PSR-12 code style compliance.
- **[MegaLinter](https://megalinter.io/)** (Ox Security) & **[Super-Linter](https://github.com/super-linter/super-linter)** (GitHub) for multi-language CI/CD orchestration.
- **[GitHub Advanced Security CodeQL](https://codeql.github.com/)** for semantic AST security scanning (CWE Top 50 & OWASP Top 25).

---

## 🎨 Offline Front-End Bundles & Design System

- **[Visual Subnet Calculator](https://alsyundawy.github.io/visualsubnetcalc)** design tokens, glassmorphism headers, dark/light jewel themes, and dynamic viewport mobile anti-clipping engine.
- **[Bootstrap 5.3.3](https://getbootstrap.com/)** by Mark Otto and Jacob Thornton for responsive UI foundation.
- **[Font Awesome 6.5.2](https://fontawesome.com/)** by Dave Gandy and Fonticons for iconography.
- **[Chart.js 4.4.4](https://www.chartjs.org/)** for client-side zone metrics and telemetry visualization.
- **[Tailwind CSS](https://tailwindcss.com/)** by Tailwind Labs for utility-first styling compilation.

---

## 📄 License & Intellectual Property

PHP-PDNSManager Enterprise Edition is open-source software licensed under the **[MIT License](LICENSE)** © 2024–2026 Harry Dertin Sutisna Alsyundawy.
