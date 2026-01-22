// Helper functions for authentication
function isLoggedIn() {
  const token = localStorage.getItem('access_token');
  // Since tokens don't expire, just check if token exists
  return !!token;
}

function getUserData() {
  const userData = localStorage.getItem('user_data');
  return userData ? JSON.parse(userData) : null;
}

function getAccessToken() {
  return localStorage.getItem('access_token');
}

// Authenticated fetch function
function authenticatedFetch(url, options = {}) {
  const token = getAccessToken();
  if (!token) {
    return Promise.reject(new Error('Authentication required'));
  }

  const defaultOptions = {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      ...options.headers
    },
    ...options
  };

  return fetch(url, defaultOptions);
}

let allItems = [];
let allSummary = {};
let allPPMPList = [];

document.addEventListener("DOMContentLoaded", function () {
  // Wait for all elements to be loaded
  setTimeout(() => {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
      searchInput.addEventListener('input', filterItems);
    }
    loadAvailableYears();
    loadCategories();
    loadConsolidatedItems();
  }, 500);
});

function loadAvailableYears() {
  authenticatedFetch(`${API_BASE_URL}/api_get_available_years.php`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const yearFilter = document.getElementById('yearFilter');
        if (yearFilter) {
          yearFilter.innerHTML = '<option value="">All Years</option>';

          data.years.forEach(year => {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            yearFilter.appendChild(option);
          });
        }
      }
    })
    .catch(err => {
      console.error("Error loading available years:", err);
    });
}

function loadCategories() {
  authenticatedFetch(`${API_BASE_URL}/get_categories.php`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const categoryFilter = document.getElementById('categoryFilter');
        if (categoryFilter) {
          categoryFilter.innerHTML = '<option value="">Select Category</option>';

          data.categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categoryFilter.appendChild(option);
          });
        }
      }
    })
    .catch(err => {
      console.error("Error loading categories:", err);
    });
}

function loadConsolidatedItems() {
  const tableBody = document.getElementById("consolidatedTableBody");
  if (!tableBody) {
    console.error("consolidatedTableBody element not found - retrying in 1 second");
    setTimeout(() => loadConsolidatedItems(), 1000);
    return;
  }

  const yearFilter = document.getElementById('yearFilter');
  const selectedYear = yearFilter ? yearFilter.value : '';

  // Show loading
  tableBody.innerHTML = `
    <tr>
      <td colspan="9" class="text-center py-4">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <p class="mt-2">Loading consolidated items...</p>
      </td>
    </tr>
  `;

  const url = selectedYear
    ? `${API_BASE_URL}/api_get_consolidated_items.php?year=${selectedYear}`
    : `${API_BASE_URL}/api_get_consolidated_items.php`;

  authenticatedFetch(url)
    .then(res => res.json())
    .then(data => {
      console.log('API Response:', data); // Debug log
      if (data.success) {
        allItems = data.consolidated_items || [];
        allSummary = data.summary || {};
        allPPMPList = data.approved_ppmp_list || [];
        console.log('Items to display:', allItems); // Debug log
        console.log('Items length:', allItems.length); // Debug log
        displayConsolidatedItems(allItems);
        displaySummary(allSummary);
        displayApprovedPPMPs(allPPMPList);
      } else {
        showError("Failed to load consolidated items: " + (data.message || 'Unknown error'));
      }
    })
    .catch(err => {
      console.error("Error loading consolidated items:", err);
      showError("Network error. Please try again.");
    });
}

function displayConsolidatedItems(items) {
  const tableBody = document.getElementById("consolidatedTableBody");

  if (!tableBody) {
    console.error("consolidatedTableBody element not found");
    return;
  }

  if (!items || items.length === 0) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="9" class="text-center py-4">
          <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No approved PPMP items found</h5>
          <p class="text-muted">Items will appear here once PPMPs are approved.</p>
        </td>
      </tr>
    `;
    return;
  }

  tableBody.innerHTML = "";

  items.forEach((item) => {
    tableBody.innerHTML += `
      <tr>
        <td>${item.id}</td>
        <td>
          <code>${item.item_code}</code>
        </td>
        <td>${item.item_name}</td>
        <td>${item.description}</td>
        <td>${item.unit}</td>
        <td>₱${item.unit_cost.toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
        <td>
          <span class="badge bg-primary">${item.total_quantity.toLocaleString()}</span>
        </td>
        <td>
          <strong>₱${item.total_cost.toLocaleString('en-US', { minimumFractionDigits: 2 })}</strong>
        </td>
        <td>
          <span class="consolidated-badge">${item.ppmp_count}</span>
        </td>
      </tr>
    `;
  });
}

function displaySummary(summary) {
  const totalItemsEl = document.getElementById('totalItems');
  const totalCostEl = document.getElementById('totalCost');
  const approvedPPMPsEl = document.getElementById('approvedPPMPs');

  if (totalItemsEl) totalItemsEl.textContent = summary.total_items.toLocaleString();
  if (totalCostEl) totalCostEl.textContent = summary.total_cost.toLocaleString('en-US', { minimumFractionDigits: 2 });
  if (approvedPPMPsEl) approvedPPMPsEl.textContent = summary.approved_ppmp_count;
}

function displayApprovedPPMPs(ppmpList) {
   const container = document.getElementById('approvedPPMPList');

   if (!container) {
     console.error("approvedPPMPList element not found");
     return;
   }

   if (ppmpList.length === 0) {
     container.innerHTML = '<p class="text-muted">No approved PPMPs found.</p>';
     return;
   }

   let html = '<div class="row">';
   ppmpList.forEach((ppmp, index) => {
     const approvedDate = new Date(ppmp.Approved_At).toLocaleDateString('en-US', {
       year: 'numeric',
       month: 'short',
       day: 'numeric'
     });

     html += `
       <div class="col-md-6 mb-2">
         <div class="d-flex justify-content-between align-items-center">
           <div>
             <strong>${ppmp.PPMP_Number}</strong>
             <br><small class="text-muted">${ppmp.Department}</small>
           </div>
           <small class="text-muted">${approvedDate}</small>
         </div>
       </div>
     `;

     // Add row break every 2 items
     if ((index + 1) % 2 === 0) {
       html += '</div><div class="row">';
     }
   });
   html += '</div>';

   container.innerHTML = html;
}


function previewAPPReport() {
   // Open APP report in new window for preview
   window.open(`${API_BASE_URL}/generate_app_report.php?preview=1`, '_blank');
}

function downloadAPPReport() {
   // Directly download the APP report PDF
   window.location.href = `${API_BASE_URL}/generate_app_report.php`;
}

function filterItems() {
  const searchInput = document.getElementById('searchInput');
  if (!searchInput) {
    console.error("searchInput element not found");
    return;
  }

  const searchTerm = searchInput.value.toLowerCase();
  const filteredItems = allItems.filter(item =>
    item.item_name.toLowerCase().includes(searchTerm) ||
    item.description.toLowerCase().includes(searchTerm)
  );
  displayConsolidatedItems(filteredItems);

  // Update summary for filtered items
  const filteredSummary = {
    total_items: filteredItems.length,
    total_cost: filteredItems.reduce((sum, item) => sum + item.total_cost, 0),
    approved_ppmp_count: allSummary.approved_ppmp_count
  };
  displaySummary(filteredSummary);

  // Keep all PPMP list
  displayApprovedPPMPs(allPPMPList);
}

function exportConsolidatedItems() {
  // Get current year filter
  const yearFilter = document.getElementById('yearFilter');
  const selectedYear = yearFilter ? yearFilter.value : '';
  const token = getAccessToken();
  const url = selectedYear && selectedYear !== ""
    ? `${API_BASE_URL}/generate_excel_report.php?type=consolidated&year=${selectedYear}&token=${encodeURIComponent(token)}`
    : `${API_BASE_URL}/generate_excel_report.php?type=consolidated&token=${encodeURIComponent(token)}`;

  // Trigger download by setting location
  window.location.href = url;
}

function exportAPPReport() {
  // Get current year filter
  const yearFilter = document.getElementById('yearFilter');
  const selectedYear = yearFilter ? yearFilter.value : '';
  const token = getAccessToken();
  const url = selectedYear && selectedYear !== ""
    ? `${API_BASE_URL}/generate_excel_report.php?type=app&year=${selectedYear}&token=${encodeURIComponent(token)}`
    : `${API_BASE_URL}/generate_excel_report.php?type=app&token=${encodeURIComponent(token)}`;

  // Trigger download by setting location
  window.location.href = url;
}

function exportAllCategoriesReport() {
  // Get current year filter
  const yearFilter = document.getElementById('yearFilter');
  const selectedYear = yearFilter ? yearFilter.value : '';
  const token = getAccessToken();
  const url = selectedYear && selectedYear !== ""
    ? `${API_BASE_URL}/generate_excel_report.php?type=category&year=${selectedYear}&token=${encodeURIComponent(token)}`
    : `${API_BASE_URL}/generate_excel_report.php?type=category&token=${encodeURIComponent(token)}`;

  // Trigger download by setting location
  window.location.href = url;
}

function exportSummaryReport() {
  // Get current year filter
  const yearFilter = document.getElementById('yearFilter');
  const selectedYear = yearFilter ? yearFilter.value : '';
  const token = getAccessToken();
  const url = selectedYear && selectedYear !== ""
    ? `${API_BASE_URL}/generate_excel_report.php?type=summary&year=${selectedYear}&token=${encodeURIComponent(token)}`
    : `${API_BASE_URL}/generate_excel_report.php?type=summary&token=${encodeURIComponent(token)}`;

  // Trigger download by setting location
  window.location.href = url;
}

function exportConsolidated() {
  // Get current data
  authenticatedFetch(`${API_BASE_URL}/api_get_consolidated_items.php`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // Create CSV content
        let csvContent = "Item Code,Item Name,Description,Unit,Unit Cost,Total Quantity,Total Cost,PPMP Count\n";

        data.consolidated_items.forEach(item => {
          csvContent += `"${item.item_code}","${item.item_name}","${item.description}","${item.unit}",${item.unit_cost},${item.total_quantity},${item.total_cost},${item.ppmp_count}\n`;
        });

        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", `consolidated_ppmp_items_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(err => {
      console.error('Export error:', err);
      alert('Network error. Please try again.');
    });
}

function exportDepartmentReport() {
  // Get current year filter
  const yearFilter = document.getElementById('yearFilter');
  const selectedYear = yearFilter ? yearFilter.value : '';
  const token = getAccessToken();
  const url = selectedYear && selectedYear !== ""
    ? `${API_BASE_URL}/generate_excel_report.php?type=department&year=${selectedYear}&token=${encodeURIComponent(token)}`
    : `${API_BASE_URL}/generate_excel_report.php?type=department&token=${encodeURIComponent(token)}`;

  // Trigger download by setting location
  window.location.href = url;
}

function loadCategoryReport() {
  const categoryFilter = document.getElementById('categoryFilter');
  const selectedCategory = categoryFilter ? categoryFilter.value : '';

  if (!selectedCategory) {
    alert('Please select a category first.');
    return;
  }

  const tableBody = document.getElementById('categoryReportTableBody');
  if (!tableBody) {
    console.error('categoryReportTableBody element not found');
    return;
  }

  // Show loading
  tableBody.innerHTML = `
    <tr>
      <td colspan="16" class="text-center">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <p class="mt-2">Loading category report...</p>
      </td>
    </tr>
  `;

  authenticatedFetch(`${API_BASE_URL}/api_category_report_data.php?category=${encodeURIComponent(selectedCategory)}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        displayCategoryReport(data);
      } else {
        showError('Failed to load category report: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(err => {
      console.error('Error loading category report:', err);
      showError('Network error. Please try again.');
    });
}

function displayCategoryReport(data) {
  const tableHead = document.getElementById('categoryReportTableHead');
  const tableBody = document.getElementById('categoryReportTableBody');

  if (!tableHead || !tableBody) {
    console.error('Category report table elements not found');
    return;
  }

  // Update table headers with departments
  const departments = data.departments || [];
  let headerHtml = `
    <tr>
      <th>Item Code</th>
      <th>Item Name & Specifications</th>
      <th>Unit</th>
      <th colspan="${departments.length}">Departments</th>
      <th>Total Qty</th>
      <th>Unit Cost</th>
      <th>Total Cost</th>
    </tr>
    <tr class="bg-light">
      <th colspan="3"></th>
  `;

  departments.forEach(dept => {
    headerHtml += `<th>${dept}</th>`;
  });

  headerHtml += `
      <th colspan="3"></th>
    </tr>
  `;

  tableHead.innerHTML = headerHtml;

  // Display data
  if (!data.data || data.data.length === 0) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="${6 + departments.length}" class="text-center py-4">
          <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No items found for this category</h5>
        </td>
      </tr>
    `;
    return;
  }

  tableBody.innerHTML = '';

  data.data.forEach(item => {
    let rowHtml = `
      <tr>
        <td>${item.item_code || ''}</td>
        <td>${item.item_name}</td>
        <td>${item.unit || ''}</td>
    `;

    item.departments.forEach(qty => {
      rowHtml += `<td>${qty}</td>`;
    });

    rowHtml += `
        <td>${item.total_quantity}</td>
        <td>${item.unit_cost}</td>
        <td>${item.total_cost}</td>
      </tr>
    `;

    tableBody.innerHTML += rowHtml;
  });
}

function exportCategoryReport() {
  const categoryFilter = document.getElementById('categoryFilter');
  const selectedCategory = categoryFilter ? categoryFilter.value : '';

  if (!selectedCategory) {
    alert('Please select a category first.');
    return;
  }

  // Get current year filter
  const yearFilter = document.getElementById('yearFilter');
  const selectedYear = yearFilter ? yearFilter.value : '';
  const token = getAccessToken();
  const url = selectedYear && selectedYear !== ""
    ? `${API_BASE_URL}/generate_excel_report.php?type=category&category=${encodeURIComponent(selectedCategory)}&year=${selectedYear}&token=${encodeURIComponent(token)}`
    : `${API_BASE_URL}/generate_excel_report.php?type=category&category=${encodeURIComponent(selectedCategory)}&token=${encodeURIComponent(token)}`;

  // Trigger download by setting location
  window.location.href = url;
}

function downloadCategoryReport() {
  // For now, same as export (Excel)
  exportCategoryReport();
}

function showError(message) {
  const tableBody = document.getElementById("consolidatedTableBody");
  if (tableBody) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="9" class="text-center py-4">
          <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
          <h5 class="text-danger">Error</h5>
          <p class="text-muted">${message}</p>
          <button class="btn btn-primary" onclick="loadConsolidatedItems()">
            <i class="fas fa-sync"></i> Try Again
          </button>
        </td>
      </tr>
    `;
  }
}

// Auto-refresh every 60 seconds
setInterval(() => {
  if (document.hasFocus()) {
    loadConsolidatedItems();
  }
}, 60000);