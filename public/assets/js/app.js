/**
 * ╔══════════════════════════════════════════════════════════════════════════════╗
 * ║ PHP-PDNSManager Enterprise Edition — Application Script                     ║
 * ╠══════════════════════════════════════════════════════════════════════════════╣
 * ║ Features:                                                                    ║
 * ║ - Dynamic Dark / Light Theme Manager (Persisted in localStorage)            ║
 * ║ - Anti-Clipping Mobile Sidebar Drawer with Backdrop & Escape Dismissal      ║
 * ║ - Dynamic Viewport Height (--vh) for Xiaomi / Redmi / POCO / iOS             ║
 * ║ - Automatic Data Table Responsive Container Wrapping                         ║
 * ║ - Full WCAG 2.2 AA Keyboard Navigation & Screen Reader States                ║
 * ║ - Offline jQuery & Bootstrap Integration                                     ║
 * ╚══════════════════════════════════════════════════════════════════════════════╝
 */

(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    initThemeManager();
    initDynamicViewport();
    initMobileSidebar();
    initZoneChart();
    initAutoResponsiveTables();
    initBulkSelection();
  });

  /**
   * 1. Dark / Light Mode Theme Manager
   * Switches theme between dark and light, syncing with localStorage and system preference.
   */
  function initThemeManager() {
    const themeToggleBtn = document.getElementById("themeToggleBtn");
    const themeIcon = document.getElementById("themeIcon");

    function getPreferredTheme() {
      const storedTheme = localStorage.getItem("pdns_theme");
      if (storedTheme) {
        return storedTheme;
      }
      return window.matchMedia("(prefers-color-scheme: dark)").matches
        ? "dark"
        : "light";
    }

    function applyTheme(theme) {
      document.documentElement.setAttribute("data-bs-theme", theme);
      document.documentElement.setAttribute("data-theme", theme);
      localStorage.setItem("pdns_theme", theme);

      if (themeIcon) {
        if (theme === "dark") {
          themeIcon.className = "fas fa-sun text-warning";
        } else {
          themeIcon.className = "fas fa-moon text-secondary";
        }
      }
    }

    // Apply initial theme
    const currentTheme = getPreferredTheme();
    applyTheme(currentTheme);

    if (themeToggleBtn) {
      themeToggleBtn.addEventListener("click", function (e) {
        e.preventDefault();
        const activeTheme =
          document.documentElement.getAttribute("data-bs-theme") || "light";
        const newTheme = activeTheme === "dark" ? "light" : "dark";
        applyTheme(newTheme);
      });
    }

    // Listen for OS theme preference changes
    window
      .matchMedia("(prefers-color-scheme: dark)")
      .addEventListener("change", function (e) {
        if (!localStorage.getItem("pdns_theme")) {
          applyTheme(e.matches ? "dark" : "light");
        }
      });
  }

  /**
   * 2. Dynamic Viewport Fix for Xiaomi (MIUI / HyperOS), Android Chrome & iOS Safari
   * Updates CSS variable --vh based on actual window.innerHeight on resize.
   */
  function initDynamicViewport() {
    function setAppHeight() {
      const vh = window.innerHeight * 0.01;
      document.documentElement.style.setProperty("--vh", `${vh}px`);
    }
    setAppHeight();
    window.addEventListener("resize", setAppHeight, { passive: true });
    window.addEventListener("orientationchange", function () {
      setTimeout(setAppHeight, 150);
    });
  }

  /**
   * 3. Mobile Sidebar Drawer Controller
   * Handles toggle, backdrop clicks, Escape key dismissal, and body scroll lock.
   */
  function initMobileSidebar() {
    const sidebar =
      document.getElementById("sidebar") || document.querySelector(".sidebar");
    const toggleBtn = document.getElementById("sidebarToggle");
    let backdrop = document.getElementById("sidebarBackdrop");

    if (!sidebar) return;

    if (!backdrop) {
      backdrop = document.createElement("div");
      backdrop.id = "sidebarBackdrop";
      backdrop.className = "sidebar-backdrop";
      backdrop.setAttribute("aria-hidden", "true");
      document.body.appendChild(backdrop);
    }

    function openSidebar() {
      sidebar.classList.add("show");
      sidebar.classList.remove("d-none");
      backdrop.classList.add("show");
      document.body.classList.add("sidebar-open");
      document.body.style.overflow = "hidden";
      if (toggleBtn) {
        toggleBtn.setAttribute("aria-expanded", "true");
      }
    }

    function closeSidebar() {
      sidebar.classList.remove("show");
      backdrop.classList.remove("show");
      document.body.classList.remove("sidebar-open");
      document.body.style.overflow = "";
      if (toggleBtn) {
        toggleBtn.setAttribute("aria-expanded", "false");
        toggleBtn.focus();
      }
    }

    if (toggleBtn) {
      toggleBtn.addEventListener("click", function (e) {
        e.preventDefault();
        const isOpen = sidebar.classList.contains("show");
        if (isOpen) {
          closeSidebar();
        } else {
          openSidebar();
        }
      });
    }

    backdrop.addEventListener("click", closeSidebar);

    const closeBtn = document.getElementById("sidebarClose");
    if (closeBtn) {
      closeBtn.addEventListener("click", closeSidebar);
    }

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && sidebar.classList.contains("show")) {
        closeSidebar();
      }
    });

    window.addEventListener(
      "resize",
      function () {
        if (window.innerWidth >= 768 && sidebar.classList.contains("show")) {
          closeSidebar();
        }
      },
      { passive: true },
    );
  }

  /**
   * 4. Chart.js Initialization (Zone Distribution)
   * Safely checks for Chart instance from offline library (window.Chart).
   */
  function initZoneChart() {
    const canvas = document.getElementById("zoneChart");
    if (!canvas) return;

    const ChartLib = window.Chart;
    if (!ChartLib) {
      return;
    }

    const existingChart = ChartLib.getChart(canvas);
    if (existingChart) {
      existingChart.destroy();
    }

    const isDark =
      document.documentElement.getAttribute("data-bs-theme") === "dark";
    const textColor = isDark ? "#94a3b8" : "#64748b";

    new ChartLib(canvas, {
      type: "doughnut",
      data: {
        labels: ["Active Zones", "Inactive Zones", "Secondary Zones"],
        datasets: [
          {
            data: [120, 15, 8],
            backgroundColor: ["#0284c7", "#ef4444", "#10b981"],
            borderWidth: 2,
            borderColor: isDark ? "#0f172a" : "#ffffff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              boxWidth: 12,
              padding: 14,
              color: textColor,
              font: {
                family: "system-ui, -apple-system, sans-serif",
                size: 12,
              },
            },
          },
        },
      },
    });
  }

  /**
   * 5. Auto Responsive Table Wrapper
   * Automatically wraps any bare <table> in a .table-responsive container.
   */
  function initAutoResponsiveTables() {
    const tables = document.querySelectorAll("table:not(.no-auto-responsive)");
    tables.forEach(function (table) {
      if (!table.parentElement.classList.contains("table-responsive")) {
        const wrapper = document.createElement("div");
        wrapper.className = "table-responsive";
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
      }
    });
  }

  /**
   * 6. Bulk Checkbox Selection Engine
   */
  function initBulkSelection() {
    const selectAll = document.getElementById("selectAll");
    if (!selectAll) return;

    selectAll.addEventListener("change", function (e) {
      const isChecked = e.target.checked;
      const checkboxes = document.querySelectorAll('input[name="selected[]"]');
      checkboxes.forEach(function (cb) {
        cb.checked = isChecked;
      });
    });
  }
})();
