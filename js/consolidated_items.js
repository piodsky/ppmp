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
      if (data.success) {
        allItems = data.consolidated_items;
        allSummary = data.summary;
        allPPMPList = data.approved_ppmp_list;
        displayConsolidatedItems(allItems);
        displaySummary(allSummary);
        displayApprovedPPMPs(allPPMPList);
      } else {
        showError("Failed to load consolidated items: " + data.message);
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

  if (items.length === 0) {
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
   window.open('generate_app_report.php?preview=1', '_blank');
}

function downloadAPPReport() {
   // Directly download the APP report PDF
   window.location.href = 'generate_app_report.php';
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
  const url = selectedYear && selectedYear !== ""
    ? `${API_BASE_URL}/api_get_department_report.php?year=${selectedYear}`
    : `${API_BASE_URL}/api_get_department_report.php`;

  // Get current data
  authenticatedFetch(url)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // Create Excel-compatible CSV content with BOM for proper encoding
        const BOM = '\uFEFF'; // Byte Order Mark for UTF-8
        let csvContent = BOM;

        // Add report title and metadata as separate rows
        const reportYear = selectedYear && selectedYear !== "" ? selectedYear : new Date().getFullYear();
        csvContent += `"Department Consolidated Report",,,,,,,,,,,\n`;
        csvContent += `"Year: ${reportYear}",,,,,,,,,,,\n`;
        csvContent += `"Generated: ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}",,,,,,,,,,,\n`;
        csvContent += ',,,,,,,,,,,\n'; // Empty row

        // Create header row with proper comma separation
        csvContent += '"Item Code","Item Name & Specifications","Unit"';

        // Add department columns
        data.departments.forEach(dept => {
          csvContent += `,"${dept}"`;
        });
        csvContent += ',"Total Quantity","Unit Cost","Total Cost"\n';

        // Add data rows
        data.export_data.forEach(item => {
          // Escape quotes and format data for Excel
          const itemCode = (item.item_code || 'N/A').replace(/"/g, '""');
          const itemName = (item.item_name || 'N/A').replace(/"/g, '""');
          const description = (item.description || 'N/A').replace(/"/g, '""');
          const unit = (item.unit || 'N/A').replace(/"/g, '""');

          csvContent += `"${itemCode}","${itemName} - ${description}","${unit}"`;

          // Add department quantities
          data.departments.forEach(dept => {
            const qty = item[dept] || 0;
            csvContent += `,${qty}`;
          });

          // Format numbers for Excel
          const totalQty = parseInt(item.total_quantity) || 0;
          const unitCost = parseFloat(item.unit_cost) || 0;
          const totalCost = parseFloat(item.total_cost) || 0;

          csvContent += `,${totalQty},"₱${unitCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}","₱${totalCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}"\n`;
        });

        // Add summary section
        csvContent += ',,,,,,,,,,,\n'; // Empty row
        csvContent += '"SUMMARY",,,,,,,,,,,\n';

        const totalItems = data.export_data.length;
        const grandTotalCost = data.export_data.reduce((sum, item) => sum + (parseFloat(item.total_cost) || 0), 0);

        csvContent += `"Total Unique Items:",${totalItems},,,,,,,,,,\n`;
        csvContent += `"Grand Total Cost:","₱${grandTotalCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}",,,,,,,,,,\n`;

        // Create and download file as proper CSV
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        const blobUrl = URL.createObjectURL(blob);
        const yearSuffix = selectedYear && selectedYear !== "" ? `_${selectedYear}` : '';
        link.setAttribute("href", blobUrl);
        link.setAttribute("download", `Department_Consolidated_Report${yearSuffix}_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Clean up
        URL.revokeObjectURL(blobUrl);
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(err => {
      console.error('Export error:', err);
      alert('Network error. Please try again.');
    });
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