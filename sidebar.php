<!-- Dark Theme PPMP Sidebar -->
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 fixed-start bg-gradient-dark" id="sidenav-main" style="background: linear-gradient(180deg, #1f2937 0%, #374151 100%) !important;">
  <div class="sidenav-header text-center py-4" style="background: rgba(255,255,255,0.05); border-radius: 0 0 15px 15px; border-bottom: 1px solid rgba(255,255,255,0.1); position: relative;">
    <button id="sidebarToggle" class="btn btn-sm btn-outline-light position-absolute top-50 end-0 me-3" title="Collapse Sidebar" style="z-index: 10; transform: translateY(-50%); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3);">
      <i class="fas fa-chevron-left" id="sidebarToggleIcon" style="font-size: 1.2rem; transition: all 0.3s ease;"></i>
    </button>
    <a class="navbar-brand text-white fw-bold d-block" href="dashboard.php" style="text-decoration: none;">
      <div class="text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 80px;">
        <div class="logo-container mb-3" style="width: 60px; height: 60px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.2);">
          <img src="assets/logo.svg" alt="PPMP Logo" style="width: 85%; height: 85%; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
        </div>
        <div class="brand-text text-center">
          <div class="h6 mb-1 fw-bold" style="font-size: 1.1rem; letter-spacing: 0.5px; color: var(--sidebar-text, #1e3a8a);" id="brandTitle">PPMP System</div>
          <small style="font-size: 0.7rem; opacity: 0.8; color: var(--sidebar-text-muted, #6b7280);" id="brandSubtitle">Procurement Management</small>
        </div>
      </div>
    </a>
  </div>

  <hr class="horizontal light mt-0 mb-3">

  <div class="collapse navbar-collapse w-auto">
    <ul class="navbar-nav">

      <!-- Dashboard -->
      <li class="nav-item mb-2">
        <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php" style="background: <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'rgba(255,255,255,0.1)' : 'transparent' ?>; border-radius: 8px; transition: all 0.3s ease;">
          <div class="d-flex align-items-center">
            <div class="icon-shape" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: 1px solid rgba(59, 130, 246, 0.3);" class="rounded me-3">
              <i class="fas fa-gauge" style="color:white;"></i>
            </div>
            <span class="nav-link-text fw-bold">Dashboard</span>
          </div>
        </a>
      </li>

      <!-- PPMP List -->
      <li class="nav-item mb-2">
        <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'ppmp_list.php' ? 'active' : '' ?>" href="ppmp_list.php" style="background: <?= basename($_SERVER['PHP_SELF']) == 'ppmp_list.php' ? 'rgba(255,255,255,0.1)' : 'transparent' ?>; border-radius: 8px; transition: all 0.3s ease;">
          <div class="d-flex align-items-center">
            <div class="icon-shape" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: 1px solid rgba(59, 130, 246, 0.3);" class="rounded me-3">
              <i class="fas fa-list" style="color:white;"></i>
            </div>
            <span class="nav-link-text fw-bold">PPMP List</span>
          </div>
        </a>
      </li>

      <!-- Consolidated Items -->
      <li class="nav-item mb-2">
        <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'consolidated_items.php' ? 'active' : '' ?>" href="consolidated_items.php" style="background: <?= basename($_SERVER['PHP_SELF']) == 'consolidated_items.php' ? 'rgba(255,255,255,0.1)' : 'transparent' ?>; border-radius: 8px; transition: all 0.3s ease;">
          <div class="d-flex align-items-center">
            <div class="icon-shape" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: 1px solid rgba(59, 130, 246, 0.3);" class="rounded me-3">
              <i class="fas fa-chart-bar" style="color:white;"></i>
            </div>
            <span class="nav-link-text fw-bold">Consolidated Items</span>
          </div>
        </a>
      </li>

      <!-- PPMP -->
      <li class="nav-item mb-2">
        <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'ppmp.php' ? 'active' : '' ?>" href="ppmp.php" style="background: <?= basename($_SERVER['PHP_SELF']) == 'ppmp.php' ? 'rgba(255,255,255,0.1)' : 'transparent' ?>; border-radius: 8px; transition: all 0.3s ease;">
          <div class="d-flex align-items-center">
            <div class="icon-shape" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: 1px solid rgba(59, 130, 246, 0.3);" class="rounded me-3">
              <i class="fas fa-file-lines" style="color:white;"></i>
            </div>
            <span class="nav-link-text fw-bold">PPMP</span>
          </div>
        </a>
      </li>

      <!-- Items Management (Unified) -->
      <li class="nav-item mb-2">
        <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'items_management.php' ? 'active' : '' ?>" href="items_management.php" style="background: <?= basename($_SERVER['PHP_SELF']) == 'items_management.php' ? 'rgba(255,255,255,0.1)' : 'transparent' ?>; border-radius: 8px; transition: all 0.3s ease;">
          <div class="d-flex align-items-center">
            <div class="icon-shape" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: 1px solid rgba(16, 185, 129, 0.3);" class="rounded me-3">
              <i class="fas fa-cogs" style="color:white;"></i>
            </div>
            <span class="nav-link-text fw-bold">Items Management</span>
          </div>
        </a>
      </li>

      <!-- View Database Items -->
      <li class="nav-item mb-2">
        <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'view_database_items.php' ? 'active' : '' ?>" href="view_database_items.php" style="background: <?= basename($_SERVER['PHP_SELF']) == 'view_database_items.php' ? 'rgba(255,255,255,0.1)' : 'transparent' ?>; border-radius: 8px; transition: all 0.3s ease;">
          <div class="d-flex align-items-center">
            <div class="icon-shape" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border: 1px solid rgba(139, 92, 246, 0.3);" class="rounded me-3">
              <i class="fas fa-database" style="color:white;"></i>
            </div>
            <span class="nav-link-text fw-bold">View Database Items</span>
          </div>
        </a>
      </li>

      <!-- Management Section (shown for admin users) -->
      <div id="managementSection" style="display: none;">
      <hr class="horizontal light my-4">
      <small class="text-gray-400 mb-2" style="font-weight: bold; padding-left: 1rem;">Management</small>

      <!-- User Management -->
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="user_management.php" style="border-radius: 8px; transition: all 0.3s ease;">
          <div class="d-flex align-items-center">
            <div class="icon-shape" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border: 1px solid rgba(139, 92, 246, 0.3);" class="rounded me-3">
              <i class="fas fa-users" style="color:white;"></i>
            </div>
            <span class="nav-link-text fw-bold">User Management</span>
          </div>
        </a>
      </li>

      <!-- Settings -->
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="settings.php" style="border-radius: 8px; transition: all 0.3s ease;">
          <div class="d-flex align-items-center">
            <div class="icon-shape" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); border: 1px solid rgba(107, 114, 128, 0.3);" class="rounded me-3">
              <i class="fas fa-cog" style="color:white;"></i>
            </div>
            <span class="nav-link-text fw-bold">Settings</span>
          </div>
        </a>
      </li>
      </div>

      <!-- Logout -->
      <li class="nav-item">
        <a class="nav-link text-white" href="logout.php" onclick="return confirm('Are you sure you want to logout?');" style="border-radius: 8px; transition: all 0.3s ease;">
          <div class="d-flex align-items-center">
            <div class="icon-shape" style="background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%); border: 1px solid rgba(255,255,255,0.2);" class="rounded me-3">
              <i class="fas fa-right-from-bracket" style="color:white;"></i>
            </div>
            <span class="nav-link-text fw-bold">Logout</span>
          </div>
        </a>
      </li>
    </ul>
  </div>

  <!-- Theme Toggle & Footer -->
  <div class="sidenav-footer mt-auto p-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <small class="text-gray-400 mb-0">Theme</small>
      <button id="themeToggle" class="btn btn-sm btn-outline-light border-0 p-1" title="Toggle Theme">
        <i class="fas fa-moon text-warning" id="themeIcon"></i>
      </button>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-2">
      <small class="text-gray-400 mb-0">Status</small>
      <div class="d-flex align-items-center">
        <i class="fas fa-wifi text-success me-1" id="connectivityIcon" title="Online"></i>
        <small class="text-gray-400 mb-0" id="pingTime">--ms</small>
      </div>
    </div>
    <div class="text-center">
      <small style="color: var(--sidebar-text-muted, #6b7280);" id="footerText">© PPMP System by MIS Pyo</small>
    </div>
  </div>
</aside>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="assets/font-awesome.min.css">

<style>
.icon-shape {
  width: 35px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Modern Toggle Button Styles */
#sidebarToggle {
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

#sidebarToggle:hover {
  background: rgba(255,255,255,0.2) !important;
  border-color: rgba(255,255,255,0.5) !important;
  transform: translateY(-50%) scale(1.1) !important;
  box-shadow: 0 6px 20px rgba(0,0,0,0.25);
}

#sidebarToggle:active {
  transform: translateY(-50%) scale(0.95) !important;
}

#sidebarToggle i {
  filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));
}

.sidenav .nav-link,
.sidenav .nav-link:hover,
.sidenav .nav-link:focus,
.sidenav .nav-link:active,
.sidenav .nav-link:visited {
  color: white !important;
}

/* Light theme specific - ensure all nav links are dark */
[data-theme="light"] .sidenav .nav-link,
[data-theme="light"] .sidenav .nav-link:hover,
[data-theme="light"] .sidenav .nav-link:focus,
[data-theme="light"] .sidenav .nav-link:active,
[data-theme="light"] .sidenav .nav-link:visited {
  color: #1e3a8a !important;
}

/* More specific rules for light theme */
[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link {
  color: #1e3a8a !important;
}

[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link:hover {
  color: #1e3a8a !important;
  background: rgba(30, 58, 138, 0.1) !important;
}

[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link.active {
  color: #1e3a8a !important;
  background: rgba(30, 58, 138, 0.15) !important;
}

/* Ultimate override for light theme nav links */
[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link,
[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link:hover,
[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link:focus,
[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link:active,
[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link:visited,
[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link.active,
[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link.active:hover,
[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link.active:focus,
[data-theme="light"] .sidenav .navbar-nav .nav-item .nav-link.active:active {
  color: #1e3a8a !important;
}

.sidenav .nav-link.active,
.sidenav .nav-link.active:hover,
.sidenav .nav-link.active:focus,
.sidenav .nav-link.active:active,
.sidenav .nav-link[href*="ppmp.php"].active,
.sidenav .nav-link[href*="ppmp.php"].active:hover,
.sidenav .nav-link[href*="ppmp.php"].active:focus,
.sidenav .nav-link[href*="ppmp.php"].active:active,
/* Target PPMP link specifically */
.sidenav .navbar-nav .nav-item:nth-child(4) .nav-link.active,
.sidenav .navbar-nav .nav-item:nth-child(4) .nav-link.active:hover,
.sidenav .navbar-nav .nav-item:nth-child(4) .nav-link.active:focus,
.sidenav .navbar-nav .nav-item:nth-child(4) .nav-link.active:active,
/* Extra specific rule for PPMP */
a[href="ppmp.php"].active,
a[href="ppmp.php"].active:hover,
a[href="ppmp.php"].active:focus,
a[href="ppmp.php"].active:active,
/* Light theme specific override */
[data-theme="light"] .sidenav .nav-link.active,
[data-theme="light"] .sidenav .nav-link[href*="ppmp.php"].active,
[data-theme="light"] .sidenav .navbar-nav .nav-item:nth-child(4) .nav-link.active,
[data-theme="light"] a[href="ppmp.php"].active {
  color: #1e3a8a !important;
  background: rgba(30, 58, 138, 0.15) !important;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  transform: translateX(5px);
}

.sidenav-footer {
  border-top: 1px solid rgba(255,255,255,0.1);
}

/* Theme Toggle Styles */
.theme-toggle {
  transition: all 0.3s ease;
}

.theme-toggle:hover {
  transform: scale(1.1);
}

/* Sidebar Toggle Styles */
.sidebar-collapsed .sidebar-title,
.sidebar-collapsed .sidebar-subtitle,
.sidebar-collapsed .nav-link-text {
  display: none;
}

.sidebar-collapsed {
  width: 80px !important;
  transition: width 0.3s ease;
}

.sidebar-collapsed .navbar-nav .nav-item {
  text-align: center;
}

.sidebar-collapsed .navbar-nav .nav-link {
  justify-content: center;
  padding: 1rem 0.5rem;
}

.sidebar-collapsed .sidenav-header {
  padding: 1rem 0.5rem;
  text-align: center;
}

.sidebar-collapsed .logo-container {
  width: 40px !important;
  height: 40px !important;
  margin: 0 auto 0.5rem auto;
}

.sidebar-collapsed .logo-container img {
  width: 100% !important;
  height: 100% !important;
}

.sidebar-collapsed .brand-text {
  display: none;
}

.sidebar-collapsed #sidebarToggle {
  right: 50% !important;
  transform: translate(50%, -50%) !important;
  top: 50% !important;
}

.sidebar-collapsed .sidenav-footer {
  text-align: center;
}

.sidebar-collapsed .sidenav-footer .d-flex {
  justify-content: center;
}

.sidebar-collapsed .sidenav-footer small {
  display: none;
}

.main-content-collapsed {
  margin-left: 80px !important;
  transition: margin-left 0.3s ease;
}

/* Connectivity Status Styles */
[data-theme="light"] #connectivityIcon {
  color: #10b981; /* green for online in light theme */
}

#connectivityIcon.offline {
  color: #ef4444; /* red for offline */
}

#pingTime {
  font-size: 0.75rem;
  font-weight: 500;
  min-width: 35px;
  text-align: right;
}

[data-theme="light"] #pingTime {
  color: #6b7280;
}

</style>

<script>
// Theme Management
class ThemeManager {
  constructor() {
    this.currentTheme = localStorage.getItem('ppmp-theme') || 'dark';
    this.init();
  }

  init() {
    // Apply theme immediately
    this.applyTheme(this.currentTheme);
    this.setupToggle();
    this.updateToggleIcon();

    // Force a re-application after a short delay to ensure CSS is applied
    setTimeout(() => {
      this.applyTheme(this.currentTheme);
    }, 100);
  }

  setupToggle() {
    const toggleBtn = document.getElementById('themeToggle');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        this.toggleTheme();
      });
    }
  }

  toggleTheme() {
    this.currentTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
    this.applyTheme(this.currentTheme);
    this.saveTheme();
    this.updateToggleIcon();

    // Update PPMP link color immediately
    setTimeout(() => {
      const ppmpLink = document.querySelector('a[href="ppmp.php"]');
      if (ppmpLink) {
        if (this.currentTheme === 'light') {
          ppmpLink.style.color = '#1e3a8a !important';
          ppmpLink.style.setProperty('color', '#1e3a8a', 'important');
        } else {
          ppmpLink.style.color = 'white !important';
          ppmpLink.style.setProperty('color', 'white', 'important');
        }
      }
    }, 50);
  }

  applyTheme(theme) {
      // Set theme attribute on document element for CSS rules
      document.documentElement.setAttribute('data-theme', theme);

      const root = document.documentElement;

      if (theme === 'dark') {
          // Dark theme variables - Charcoal Gray + White + Blue
          root.style.setProperty('--bg-primary', 'linear-gradient(135deg, #374151 0%, #1f2937 100%)');
          root.style.setProperty('--bg-secondary', 'linear-gradient(135deg, #1f2937 0%, #374151 100%)');
          root.style.setProperty('--bg-accent', 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)');
          root.style.setProperty('--text-primary', '#ffffff');
          root.style.setProperty('--text-muted', '#9ca3af');
          root.style.setProperty('--border-light', 'rgba(255,255,255,0.1)');
          root.style.setProperty('--shadow-color', 'rgba(0,0,0,0.4)');
          root.style.setProperty('--sidebar-bg', 'linear-gradient(180deg, #1f2937 0%, #374151 100%)');
      } else {
          // Light theme variables - White + Gray + Navy
          root.style.setProperty('--bg-primary', 'linear-gradient(135deg, #f8f9fa 0%, #e2e8f0 100%)');
          root.style.setProperty('--bg-secondary', '#ffffff');
          root.style.setProperty('--bg-accent', 'linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%)');
          root.style.setProperty('--text-primary', '#1e3a8a');
          root.style.setProperty('--text-muted', '#6b7280');
          root.style.setProperty('--border-light', '#e2e8f0');
          root.style.setProperty('--shadow-color', 'rgba(0,0,0,0.1)');
          root.style.setProperty('--sidebar-bg', 'linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%)');
          root.style.setProperty('--sidebar-text', '#1e3a8a');
          root.style.setProperty('--sidebar-text-muted', '#6b7280');
      }

    document.body.style.background = `var(--bg-primary)`;
    document.body.style.color = `var(--text-primary)`;
    this.updateElements(theme);
  }

  updateElements(theme) {
      const sidebar = document.getElementById('sidenav-main');
      if (sidebar) {
          sidebar.style.background = theme === 'dark'
              ? 'linear-gradient(180deg, #1f2937 0%, #374151 100%)'
              : 'linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%)';
      }

      // Update sidebar text colors
      const brandTitle = document.getElementById('brandTitle');
      const brandSubtitle = document.getElementById('brandSubtitle');
      if (brandTitle) {
          brandTitle.style.color = theme === 'dark' ? '#ffffff' : '#1e3a8a';
      }
      if (brandSubtitle) {
          brandSubtitle.style.color = theme === 'dark' ? '#9ca3af' : '#6b7280';
      }

      const footerText = document.getElementById('footerText');
      if (footerText) {
          footerText.style.color = theme === 'dark' ? '#9ca3af' : '#6b7280';
      }

    const cards = document.querySelectorAll('.card, .form-section, .chart-container, .recent-activity');
    cards.forEach(card => {
      card.style.background = theme === 'dark'
        ? 'linear-gradient(135deg, #1f2937 0%, #374151 100%)'
        : '#ffffff';
      card.style.border = theme === 'dark'
        ? '1px solid rgba(59, 130, 246, 0.3)'
        : '1px solid #e2e8f0';
      card.style.color = `var(--text-primary)`;
    });

    const tables = document.querySelectorAll('.table');
    tables.forEach(table => {
      const headers = table.querySelectorAll('thead th');
      headers.forEach(header => {
        header.style.background = theme === 'dark'
          ? 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)'
          : 'linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%)';
        header.style.color = '#ffffff';
      });

      const rows = table.querySelectorAll('tbody tr');
      rows.forEach(row => {
        row.style.color = `var(--text-primary)`;
      });
    });

    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
      input.style.background = theme === 'dark'
        ? 'rgba(255,255,255,0.08)'
        : 'rgba(255,255,255,0.9)';
      input.style.border = theme === 'dark'
        ? '1px solid rgba(59, 130, 246, 0.3)'
        : '1px solid rgba(30, 58, 138, 0.2)';
      input.style.color = `var(--text-primary)`;
    });

    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
      if (theme === 'dark') {
        if (btn.classList.contains('btn-primary')) {
          btn.style.background = 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)';
        } else if (btn.classList.contains('btn-success')) {
          btn.style.background = 'linear-gradient(135deg, #38a169 0%, #2f855a 100%)';
        } else if (btn.classList.contains('btn-info')) {
          btn.style.background = 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)';
        } else if (btn.classList.contains('btn-warning')) {
          btn.style.background = 'linear-gradient(135deg, #d69e2e 0%, #b7791f 100%)';
        } else if (btn.classList.contains('btn-danger')) {
          btn.style.background = 'linear-gradient(135deg, #e53e3e 0%, #c53030 100%)';
        }
        btn.style.border = '1px solid rgba(59, 130, 246, 0.3)';
        btn.style.color = '#ffffff';
      } else {
        if (btn.classList.contains('btn-primary')) {
          btn.style.background = 'linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%)';
          btn.style.border = '1px solid #1e40af';
          btn.style.color = '#ffffff';
        } else {
          btn.style.removeProperty('background');
          btn.style.removeProperty('border');
          btn.style.removeProperty('color');
        }
      }
    });
  }

  updateToggleIcon() {
    const icon = document.getElementById('themeIcon');
    if (icon) {
      if (this.currentTheme === 'dark') {
        icon.className = 'fas fa-sun text-warning';
        icon.parentElement.title = 'Switch to Light Theme';
      } else {
        icon.className = 'fas fa-moon text-primary';
        icon.parentElement.title = 'Switch to Dark Theme';
      }
    }
  }

  saveTheme() {
    localStorage.setItem('ppmp-theme', this.currentTheme);
  }
}

// Sidebar Toggle Management
class SidebarManager {
  constructor() {
    this.isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    this.init();
  }

  init() {
    this.setupToggle();
    this.applyState();
  }

  setupToggle() {
    const toggleBtn = document.getElementById('sidebarToggle');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        this.toggleSidebar();
      });
    }
  }

  toggleSidebar() {
    this.isCollapsed = !this.isCollapsed;
    this.applyState();
    this.saveState();
  }

  applyState() {
    const sidebar = document.getElementById('sidenav-main');
    const mainContent = document.querySelector('.main-content');
    const toggleIcon = document.getElementById('sidebarToggleIcon');
    const toggleBtn = document.getElementById('sidebarToggle');

    if (this.isCollapsed) {
      sidebar.classList.add('sidebar-collapsed');
      if (mainContent) {
        mainContent.classList.add('main-content-collapsed');
      }
      if (toggleIcon) {
        toggleIcon.className = 'fas fa-chevron-right';
      }
      if (toggleBtn) {
        toggleBtn.title = 'Expand Sidebar';
      }
    } else {
      sidebar.classList.remove('sidebar-collapsed');
      if (mainContent) {
        mainContent.classList.remove('main-content-collapsed');
      }
      if (toggleIcon) {
        toggleIcon.className = 'fas fa-chevron-left';
      }
      if (toggleBtn) {
        toggleBtn.title = 'Collapse Sidebar';
      }
    }
  }

  saveState() {
    localStorage.setItem('sidebar-collapsed', this.isCollapsed);
  }
}

// Connectivity Status Manager
class ConnectivityManager {
  constructor() {
    this.pingInterval = null;
    this.init();
  }

  init() {
    this.updateStatus();
    window.addEventListener('online', () => this.updateStatus());
    window.addEventListener('offline', () => this.updateStatus());

    // Start ping measurement
    this.startPingMeasurement();
  }

  async measurePing() {
    try {
      const start = performance.now();
      // Ping a small resource from the same domain to avoid CORS
      const response = await fetch('../favicon.ico', {
        method: 'HEAD',
        cache: 'no-cache'
      });
      const end = performance.now();
      const ping = Math.round(end - start);
      return ping;
    } catch (error) {
      console.warn('Ping measurement failed:', error);
      return null;
    }
  }

  async updatePingDisplay() {
    const pingElement = document.getElementById('pingTime');
    if (!navigator.onLine) {
      if (pingElement) pingElement.textContent = '--ms';
      return;
    }

    const ping = await this.measurePing();
    if (pingElement) {
      if (ping !== null) {
        pingElement.textContent = `${ping}ms`;
        // Update icon color based on ping quality
        const icon = document.getElementById('connectivityIcon');
        if (icon) {
          if (ping < 100) {
            icon.className = 'fas fa-wifi text-success';
          } else if (ping < 500) {
            icon.className = 'fas fa-wifi text-warning';
          } else {
            icon.className = 'fas fa-wifi text-danger';
          }
        }
      } else {
        pingElement.textContent = '--ms';
      }
    }
  }

  startPingMeasurement() {
    // Update ping immediately
    this.updatePingDisplay();

    // Update ping every 30 seconds
    this.pingInterval = setInterval(() => {
      this.updatePingDisplay();
    }, 30000);
  }

  updateStatus() {
    const icon = document.getElementById('connectivityIcon');
    const pingElement = document.getElementById('pingTime');

    if (icon) {
      if (navigator.onLine) {
        icon.title = 'Online';
        icon.classList.remove('offline');
        // Don't change className here as it's handled by ping measurement
      } else {
        icon.className = 'fas fa-wifi-slash text-danger offline';
        icon.title = 'Offline';
        if (pingElement) pingElement.textContent = '--ms';
      }
    }

    // Update ping when status changes
    if (navigator.onLine) {
      this.updatePingDisplay();
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
   new ThemeManager();
   new SidebarManager();
   new ConnectivityManager();

   // Check user role and show/hide management section
   const checkUserRole = () => {
       try {
           const userData = JSON.parse(localStorage.getItem('user_data'));
           const managementSection = document.getElementById('managementSection');

           if (userData && userData.role === 'admin' && managementSection) {
               managementSection.style.display = 'block';
           } else if (managementSection) {
               managementSection.style.display = 'none';
           }
       } catch (error) {
           console.error('Error checking user role:', error);
           // Hide management section if there's an error
           const managementSection = document.getElementById('managementSection');
           if (managementSection) {
               managementSection.style.display = 'none';
           }
       }
   };

   // Authentication removed - no token checking needed

   // Check role immediately
   checkUserRole();

   // Also check when localStorage changes (in case user logs in/out)
   window.addEventListener('storage', checkUserRole);

   // Listen for authentication completion event
   window.addEventListener('authenticationComplete', checkUserRole);

  // Force PPMP link to have correct color based on theme
  const forcePPMPCorrectColor = () => {
    const ppmpLink = document.querySelector('a[href="ppmp.php"]');
    if (ppmpLink) {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
      if (currentTheme === 'light') {
        ppmpLink.style.color = '#1e3a8a !important';
        ppmpLink.style.setProperty('color', '#1e3a8a', 'important');
      } else {
        ppmpLink.style.color = 'white !important';
        ppmpLink.style.setProperty('color', 'white', 'important');
      }
    }
  };

  // Run immediately and on any DOM changes
  forcePPMPCorrectColor();

  // Use MutationObserver to watch for changes
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
        forcePPMPCorrectColor();
      }
    });
  });

  // Observe the sidebar for changes
  const sidebar = document.getElementById('sidenav-main');
  if (sidebar) {
    observer.observe(sidebar, {
      attributes: true,
      subtree: true,
      attributeFilter: ['class']
    });
  }
});
</script>
