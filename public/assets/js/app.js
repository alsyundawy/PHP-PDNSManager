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
    initCommandPalette();
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

  /**
   * 7. Command Palette (Ctrl+K / Cmd+K) Global Search & Navigation
   */
  function initCommandPalette() {
    const modalEl = document.getElementById("commandPaletteModal");
    const inputEl = document.getElementById("commandPaletteInput");
    const resultsEl = document.getElementById("commandPaletteResults");
    const triggerBtn = document.getElementById("commandPaletteTrigger");

    if (!modalEl || !inputEl || !resultsEl) return;

    let debounceTimer = null;
    let selectedIndex = -1;

    function getModalInstance() {
      if (typeof window.bootstrap !== "undefined" && window.bootstrap.Modal) {
        return window.bootstrap.Modal.getOrCreateInstance(modalEl);
      }
      return null;
    }

    function openModal() {
      const modal = getModalInstance();
      if (modal) {
        modal.show();
      } else {
        modalEl.classList.add("show");
        modalEl.style.display = "block";
      }
    }

    modalEl.addEventListener("shown.bs.modal", function () {
      inputEl.focus();
      inputEl.select();
    });

    if (triggerBtn) {
      triggerBtn.addEventListener("click", function (e) {
        e.preventDefault();
        openModal();
      });
    }

    document.addEventListener("keydown", function (e) {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "k") {
        e.preventDefault();
        openModal();
      }
    });

    function escapeHtml(str) {
      return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    function renderLoading() {
      resultsEl.innerHTML =
        '<div class="p-4 text-center text-muted small"><i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Searching PowerDNS records, zones, and actions...</div>';
      selectedIndex = -1;
    }

    function renderEmpty(query) {
      resultsEl.innerHTML = `<div class="p-4 text-center text-muted small">No matches found for "<strong>${escapeHtml(query)}</strong>"</div>`;
      selectedIndex = -1;
    }

    function renderPrompt() {
      resultsEl.innerHTML =
        '<div class="p-4 text-muted text-center small">Type at least 2 characters to search across PowerDNS zones, records, and navigation...</div>';
      selectedIndex = -1;
    }

    function getTypeBadgeClass(type) {
      switch (type.toUpperCase()) {
        case "ZONE":
          return "bg-primary";
        case "RECORD":
          return "bg-info text-dark";
        case "ACTION":
          return "bg-success";
        case "COMMENT":
          return "bg-secondary";
        default:
          return "bg-dark";
      }
    }

    function renderResults(items) {
      if (!items || items.length === 0) {
        renderEmpty(inputEl.value.trim());
        return;
      }

      let html = "";
      items.forEach(function (item, idx) {
        const badgeClass = getTypeBadgeClass(item.type || "RECORD");
        html += `
          <a href="${escapeHtml(item.url || "#")}" class="list-group-item list-group-item-action command-palette-item d-flex justify-content-between align-items-center py-2 px-3 border-0" data-index="${idx}">
            <div class="me-2 text-truncate">
              <div class="fw-semibold text-reset text-truncate">${escapeHtml(item.title || "")}</div>
              <small class="text-muted text-truncate d-block">${escapeHtml(item.subtitle || "")}</small>
            </div>
            <span class="badge ${badgeClass} text-uppercase font-monospace px-2 py-1" style="font-size:0.75rem;">${escapeHtml(item.type || "")}</span>
          </a>
        `;
      });
      resultsEl.innerHTML = html;
      selectedIndex = -1;
    }

    function executeSearch(query) {
      if (query.length < 2) {
        renderPrompt();
        return;
      }

      renderLoading();
      fetch(`/api/search?q=${encodeURIComponent(query)}`, {
        headers: { Accept: "application/json" },
      })
        .then(function (res) {
          if (!res.ok) throw new Error("Search network error");
          return res.json();
        })
        .then(function (data) {
          renderResults(data.results || []);
        })
        .catch(function () {
          resultsEl.innerHTML =
            '<div class="p-3 text-center text-danger small"><i class="fas fa-exclamation-triangle me-1"></i> Search service unavailable.</div>';
        });
    }

    inputEl.addEventListener("input", function () {
      clearTimeout(debounceTimer);
      const query = this.value.trim();
      debounceTimer = setTimeout(function () {
        executeSearch(query);
      }, 220);
    });

    inputEl.addEventListener("keydown", function (e) {
      const items = resultsEl.querySelectorAll(".command-palette-item");
      if (!items.length) return;

      if (e.key === "ArrowDown") {
        e.preventDefault();
        selectedIndex = (selectedIndex + 1) % items.length;
        updateSelection(items);
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        selectedIndex = (selectedIndex - 1 + items.length) % items.length;
        updateSelection(items);
      } else if (e.key === "Enter") {
        e.preventDefault();
        if (selectedIndex >= 0 && items[selectedIndex]) {
          items[selectedIndex].click();
        } else if (items[0]) {
          items[0].click();
        }
      }
    });

    function updateSelection(items) {
      items.forEach(function (el, idx) {
        if (idx === selectedIndex) {
          el.classList.add("active");
          el.scrollIntoView({ block: "nearest" });
        } else {
          el.classList.remove("active");
        }
      });
    }
  }
})();
