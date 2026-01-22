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

// Authenticated fetch function (no token refresh needed since tokens don't expire)
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

document.addEventListener("DOMContentLoaded", function () {
  // Initialize PPMP functionality
  initializePPMP();
});

let currentPPMPId = null;
let currentPPMPNumber = '';
let selectedItems = new Set(); // Track selected item IDs to prevent duplicates
let modalSelectedItems = new Set(); // Track items selected in modal
let currentPage = 1;
let itemsList = [];
const rowsPerPage = 10;
const ITEMS_DISPLAY_LIMIT = 100; // Limit items shown to prevent lag, but search works on all

function initializePPMP() {
  // Load items from API
  loadItems();

  // Load categories for filter
  loadCategories();

  // Initialize pagination
  showPage(1);

  // Make PPMP number readonly for new PPMPs (auto-generated)
  const ppmpNumberField = document.getElementById('ppmp_number');
  ppmpNumberField.readOnly = true;
  if (!ppmpNumberField.value) {
    ppmpNumberField.placeholder = 'Auto-generated on save';
  }

  // Set default plan year
  if (!document.getElementById('plan_year').value) {
    document.getElementById('plan_year').value = new Date().getFullYear();
  }

  // Add window resize listener for height adjustments
  window.addEventListener('resize', () => {
    setTimeout(() => {
      adjustAllRowHeights();
    }, 100);
  });

  // Add keyboard shortcut for item selection (F3)
  document.addEventListener('keydown', function(e) {
    // Check for F3 key (both modern and legacy keyCode)
    if (e.key === 'F3' || e.keyCode === 114) {
      e.preventDefault();
      e.stopPropagation();
      console.log('F3 key detected - opening item selection modal');

      // Check if the F3 button exists and is not disabled
      const f3Btn = document.getElementById('f3Btn');
      if (f3Btn && !f3Btn.disabled) {
        openItemSelectionModal();
      } else {
        console.log('F3 button is disabled or not found');
      }
      return false;
    }
  });

  // Initialize modal functionality
  initializeItemSelectionModal();
}

function loadItems() {
    // Fetch all items initially to ensure imported items are available
    return authenticatedFetch(`${API_BASE_URL}/get_items.php`)
      .then(response => {
        // Check if response is OK
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text(); // Get text first to check if empty
      })
      .then(text => {
        if (!text || text.trim() === '') {
          console.warn('Empty response from API');
          itemsList = [];
          return [];
        }
        try {
          const data = JSON.parse(text);
          itemsList = data.items || [];
          console.log('DEBUG: Loaded', itemsList.length, 'items from API (all items)');
          // Adjust heights for all existing rows after items are loaded
          setTimeout(() => {
            adjustAllRowHeights();
          }, 100);
          return itemsList; // Return the items for promise chaining
        } catch (e) {
          console.error('Error parsing JSON:', e, 'Response text:', text);
          itemsList = [];
          return [];
        }
      })
      .catch(error => {
        console.error('Error loading items:', error);
        itemsList = [];
        return []; // Return empty array on error
      });
  }

  // Load categories for filter from API
  function loadCategories() {
    authenticatedFetch(`${API_BASE_URL}/get_categories.php`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const categoryFilter = document.getElementById('itemCategoryFilter');
          if (categoryFilter) {
            categoryFilter.innerHTML = '<option value="">All Categories</option>';
            
            data.categories.forEach(category => {
              categoryFilter.innerHTML += `<option value="${category}">${category}</option>`;
            });
          }
        }
      })
      .catch(error => {
        console.error('Error loading categories:', error);
      });
  }

  // Search items via API (searches ALL items in database)
  function searchItemsAPI(searchTerm) {
    if (!searchTerm || searchTerm.length < 2) {
      return Promise.resolve([]);
    }
    
    // Add limit=10000 to get all matching items (but search is done on server side)
    return authenticatedFetch(`${API_BASE_URL}/get_items.php?search=${encodeURIComponent(searchTerm)}&limit=10000`)
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
      })
      .then(text => {
        if (!text || text.trim() === '') {
          return [];
        }
        try {
          const data = JSON.parse(text);
          return data.items || [];
        } catch (e) {
          console.error('Error parsing search results:', e);
          return [];
        }
      })
      .catch(error => {
        console.error('Error searching items:', error);
        return [];
      });
  }

function adjustAllRowHeights() {
  const rows = document.querySelectorAll('#itemsTableBody tr');
  rows.forEach(row => {
    adjustRowHeight(row);
  });
}

function addRow() {
    const table = document.getElementById("itemsTableBody");
    const row = table.insertRow();

    // Filter out already selected items
    const availableItems = itemsList.filter(item => !selectedItems.has(parseInt(item.ID)));
    // Limit to 100 items for initial display (search will still work on all items)
    const displayItems = availableItems.slice(0, ITEMS_DISPLAY_LIMIT);

    row.innerHTML = `
        <td>
            <div class="searchable-dropdown">
                <div class="dropdown-display" tabindex="0">
                    <span class="selected-text">Select Item</span>
                </div>
                <div class="dropdown-menu">
                    <input type="text" class="search-input" placeholder="Search items...">
                    <div class="dropdown-items">
                        ${displayItems.map(item => `<div class="dropdown-item" data-value="${item.ID}" data-description="${item.Items_Description}" data-unit="${item.Unit}" data-cost="${item.Unit_Cost}" data-code="${item.Item_Code}" data-name="${item.Item_Name}" data-category="${item.Category}">${item.Items_Description}</div>`).join('')}
                    </div>
                </div>
            </div>
        </td>
       <td><textarea class="form-control item-description" rows="1" style="resize: none; overflow: hidden;"></textarea></td>
       <td><input type="text" class="form-control item-unit"></td>
       <td><input type="number" class="form-control jan" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="number" class="form-control feb" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="number" class="form-control mar" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="text" class="form-control q1total" value="0" readonly></td>
       <td><input type="number" class="form-control apr" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="number" class="form-control may" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="number" class="form-control jun" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="text" class="form-control q2total" value="0" readonly></td>
       <td><input type="number" class="form-control jul" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="number" class="form-control aug" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="number" class="form-control sep" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="text" class="form-control q3total" value="0" readonly></td>
       <td><input type="number" class="form-control oct" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="number" class="form-control nov" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="number" class="form-control dec" value="0" min="0" oninput="recalculateRow(this)"></td>
       <td><input type="text" class="form-control q4total" value="0" readonly></td>
       <td><input type="number" class="form-control unit_cost"></td>
       <td><input type="text" class="form-control total_qty" readonly></td>
       <td><input type="text" class="form-control total_cost" readonly></td>
       <td><input type="text" class="form-control item-category" readonly></td>
       <td><button class="btn btn-danger btn-sm" onclick="deleteRow(this)">Delete</button></td>
    `;

  // Initialize searchable dropdown
  initializeSearchableDropdown(row);

  updateItemCount();
  updatePagination();
  showPage(currentPage);
}

function addRowForItem(item) {
    const table = document.getElementById("itemsTableBody");
    const row = table.insertRow();

    // Add item to selected items set
    selectedItems.add(parseInt(item.ID));

    row.innerHTML = `
        <td>
            <div class="searchable-dropdown" data-selected-item="${item.ID}" data-code="${item.Item_Code || ''}" data-name="${item.Item_Name || ''}" data-category="${item.Category || ''}">
                <div class="dropdown-display" tabindex="0">
                    <span class="selected-text">${item.Items_Description}</span>
                </div>
                <div class="dropdown-menu">
                    <input type="text" class="search-input" placeholder="Search items...">
                    <div class="dropdown-items">
                        <!-- Items will be loaded if dropdown is opened -->
                    </div>
                </div>
            </div>
        </td>
        <td><textarea class="form-control item-description" rows="1" style="resize: none; overflow: hidden;" title="${item.Items_Description || ''}">${item.Items_Description || ''}</textarea></td>
        <td><input type="text" class="form-control item-unit" value="${item.Unit || ''}"></td>
        <td><input type="number" class="form-control jan" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="number" class="form-control feb" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="number" class="form-control mar" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="text" class="form-control q1total" value="0" readonly></td>
        <td><input type="number" class="form-control apr" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="number" class="form-control may" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="number" class="form-control jun" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="text" class="form-control q2total" value="0" readonly></td>
        <td><input type="number" class="form-control jul" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="number" class="form-control aug" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="number" class="form-control sep" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="text" class="form-control q3total" value="0" readonly></td>
        <td><input type="number" class="form-control oct" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="number" class="form-control nov" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="number" class="form-control dec" value="0" min="0" oninput="recalculateRow(this)"></td>
        <td><input type="text" class="form-control q4total" value="0" readonly></td>
        <td><input type="number" class="form-control unit_cost" value="${item.Unit_Cost || 0}" title="${item.Unit_Cost || 0}"></td>
        <td><input type="text" class="form-control total_qty" readonly value="0"></td>
        <td><input type="text" class="form-control total_cost" readonly value="0.00"></td>
        <td><input type="text" class="form-control item-category" readonly value="${item.Category || ''}" title="${item.Category || ''}"></td>
        <td><button class="btn btn-danger btn-sm" onclick="deleteRow(this)">Delete</button></td>
    `;

    // Set the selected text to show item code if available
    const selectedText = row.querySelector('.selected-text');
    const displayText = item.Item_Code ? '[' + item.Item_Code + '] ' + item.Items_Description : item.Items_Description;
    selectedText.textContent = displayText;
    selectedText.title = displayText;

  // Initialize searchable dropdown
  initializeSearchableDropdown(row);

  // Adjust row height after adding
  setTimeout(() => {
    adjustRowHeight(row);
  }, 100);
}

function deleteRow(btn) {
    const row = btn.closest('tr');

    // Remove item from selectedItems set
    const dropdown = row.querySelector('.searchable-dropdown');
    const itemId = dropdown.getAttribute('data-selected-item');
    if (itemId) {
        selectedItems.delete(parseInt(itemId));
    }

   row.remove();
   // Add input validation for quantity fields
   const quantityInputs = row.querySelectorAll('input[type="number"]');
   quantityInputs.forEach(input => {
     input.addEventListener('input', function() {
       if (parseFloat(this.value) < 0) {
         this.value = 0;
       }
     });

     input.addEventListener('keydown', function(e) {
       // Prevent minus sign
       if (e.key === '-' || e.key === 'e' || e.key === 'E') {
         e.preventDefault();
       }
     });
   });

   updateItemCount();
   updatePagination();
   showPage(currentPage);
   recalculateTotals();
}

function recalculateRow(input) {
  const row = input.closest('tr');

  // Ensure input value is not negative
  if (parseFloat(input.value) < 0) {
    input.value = 0;
  }

  const jan = Math.max(0, parseFloat(row.querySelector('.jan').value) || 0);
  const feb = Math.max(0, parseFloat(row.querySelector('.feb').value) || 0);
  const mar = Math.max(0, parseFloat(row.querySelector('.mar').value) || 0);
  const apr = Math.max(0, parseFloat(row.querySelector('.apr').value) || 0);
  const may = Math.max(0, parseFloat(row.querySelector('.may').value) || 0);
  const jun = Math.max(0, parseFloat(row.querySelector('.jun').value) || 0);
  const jul = Math.max(0, parseFloat(row.querySelector('.jul').value) || 0);
  const aug = Math.max(0, parseFloat(row.querySelector('.aug').value) || 0);
  const sep = Math.max(0, parseFloat(row.querySelector('.sep').value) || 0);
  const oct = Math.max(0, parseFloat(row.querySelector('.oct').value) || 0);
  const nov = Math.max(0, parseFloat(row.querySelector('.nov').value) || 0);
  const dec = Math.max(0, parseFloat(row.querySelector('.dec').value) || 0);

  const q1 = jan + feb + mar;
  const q2 = apr + may + jun;
  const q3 = jul + aug + sep;
  const q4 = oct + nov + dec;

  row.querySelector('.q1total').value = q1;
  row.querySelector('.q2total').value = q2;
  row.querySelector('.q3total').value = q3;
  row.querySelector('.q4total').value = q4;

  const totalQty = q1 + q2 + q3 + q4;
  const unitCost = parseFloat(row.querySelector('.unit_cost').value) || 0;
  const totalCostValue = (totalQty * unitCost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  row.querySelector('.total_qty').value = totalQty;
  const totalCostInput = row.querySelector('.total_cost');
  totalCostInput.value = totalCostValue;
  totalCostInput.title = totalCostValue;

  recalculateTotals();
}

function recalculateTotals() {
  let totalCost = 0;
  const costs = document.querySelectorAll('.total_cost');
  costs.forEach(c => totalCost += parseFloat(c.value.replace(/,/g, '')) || 0);
  document.getElementById('grand_total').textContent = totalCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateItemCount() {
  const count = document.querySelectorAll('#itemsTableBody tr').length;
  // Update both total_items elements
  const paginationCount = document.getElementById('total_items_pagination');
  const summaryCount = document.getElementById('total_items_summary');
  if (paginationCount) paginationCount.textContent = count;
  if (summaryCount) summaryCount.textContent = count;
}

function updatePagination() {
  const totalRows = document.querySelectorAll('#itemsTableBody tr').length;
  const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
  document.getElementById('totalPages').textContent = totalPages;
  document.getElementById('currentPage').textContent = currentPage;
  document.getElementById('prevPage').disabled = currentPage === 1;
  document.getElementById('nextPage').disabled = currentPage === totalPages;
}

function showPage(page) {
  const rows = document.querySelectorAll('#itemsTableBody tr');
  const totalPages = Math.ceil(rows.length / rowsPerPage) || 1;
  if (page < 1) page = 1;
  if (page > totalPages) page = totalPages;
  currentPage = page;
  rows.forEach((row, index) => {
      row.style.display = (index >= (page - 1) * rowsPerPage && index < page * rowsPerPage) ? '' : 'none';
  });
  updatePagination();
  updateItemCount(); // Update count when page changes
}

function changePage(direction) {
  showPage(currentPage + direction);
}

// PPMP Management Functions
function savePPMP(status = 'draft', buttonId = null) {
    return new Promise((resolve, reject) => {
  const ppmpNumber = document.getElementById('ppmp_number').value.trim();
  const planYear = document.getElementById('plan_year').value;
  const classification = 'ANNUAL'; // Default
  const position = 'ADMINISTRATIVE AIDE'; // Default
  // Get department - handle single select combobox
  const departmentSelect = document.getElementById('department');
  let department = '';
  if (departmentSelect && departmentSelect.tagName === 'SELECT') {
    department = departmentSelect.value.trim();
  } else if (departmentSelect) {
    department = departmentSelect.value.trim();
  }
  const contactPerson = document.getElementById('contact_person').value.trim();
  const address = 'MALAYBALAY CITY'; // Default

  // Validate required fields
  if (!department) {
    alert('Department is required. Please log in with a user account that has a department assigned.');
    return;
  }

  if (!contactPerson) {
    alert('Contact Person is required. Please log in with a valid user account.');
    return;
  }

  // Allow empty PPMP number - API will generate if empty

  // Collect all table rows
  const rows = document.querySelectorAll('#itemsTableBody tr');
  if (rows.length === 0) {
      alert('Please add at least one item to the PPMP before saving.');
      return;
  }

  const entries = [];
  let hasIncompleteRow = false;
  rows.forEach((row, index) => {
      const dropdown = row.querySelector('.searchable-dropdown');
      const itemId = dropdown.getAttribute('data-selected-item');

      if (!itemId) {
          alert(`Please select an item for row ${index + 1}.`);
          hasIncompleteRow = true;
          return;
      }

      // Check if at least one quantity is entered
      const quantities = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
      const hasQuantity = quantities.some(month => {
          const qty = parseInt(row.querySelector(`.${month}`).value) || 0;
          return qty > 0;
      });

      if (!hasQuantity) {
          alert(`Please enter at least one quantity for row ${index + 1} (${dropdown.querySelector('.selected-text').textContent}).`);
          hasIncompleteRow = true;
          return;
      }

      // If validation passed, add the entry
      const entry = {
          item_id: parseInt(itemId),
          item_code: dropdown.getAttribute('data-code') || '',
          item_name: dropdown.getAttribute('data-name') || '',
          item_description: row.querySelector('.item-description').value,
          unit: row.querySelector('.item-unit').value,
          unit_cost: parseFloat(row.querySelector('.unit_cost').value) || 0,
          category: row.querySelector('.item-category').value || '',
          jan_qty: parseInt(row.querySelector('.jan').value) || 0,
          feb_qty: parseInt(row.querySelector('.feb').value) || 0,
          mar_qty: parseInt(row.querySelector('.mar').value) || 0,
          apr_qty: parseInt(row.querySelector('.apr').value) || 0,
          may_qty: parseInt(row.querySelector('.may').value) || 0,
          jun_qty: parseInt(row.querySelector('.jun').value) || 0,
          jul_qty: parseInt(row.querySelector('.jul').value) || 0,
          aug_qty: parseInt(row.querySelector('.aug').value) || 0,
          sep_qty: parseInt(row.querySelector('.sep').value) || 0,
          oct_qty: parseInt(row.querySelector('.oct').value) || 0,
          nov_qty: parseInt(row.querySelector('.nov').value) || 0,
          dec_qty: parseInt(row.querySelector('.dec').value) || 0,
          total_qty: parseInt(row.querySelector('.total_qty').value) || 0,
          total_cost: parseFloat(row.querySelector('.total_cost').value.replace(/,/g, '')) || 0
      };
      entries.push(entry);
  });

  // Check if any row had validation errors
  if (hasIncompleteRow) {
      return;
  }

  const ppmpData = {
      ppmp_number: ppmpNumber,
      plan_year: planYear,
      classification: classification,
      position: position,
      department: department,
      contact_person: contactPerson,
      address: address,
      status: status,
      entries: entries
  };

  if (currentPPMPId) {
      ppmpData.id = currentPPMPId;
  }

  // Show loading state
  const buttonIdToUse = buttonId || (status === 'submitted' ? 'submitBtn' : 'saveDraftBtn');
  const saveBtn = document.getElementById(buttonIdToUse);
  const originalText = saveBtn.innerHTML;
  const loadingText = status === 'submitted' ? '<i class="fas fa-spinner fa-spin"></i> Submitting...' : '<i class="fas fa-spinner fa-spin"></i> Saving...';
  saveBtn.innerHTML = loadingText;
  saveBtn.disabled = true;

  // Debug: Check if API_BASE_URL is defined
  console.log('API_BASE_URL:', typeof API_BASE_URL !== 'undefined' ? API_BASE_URL : 'NOT DEFINED');

  // Fallback if API_BASE_URL is not defined
  if (typeof API_BASE_URL === 'undefined') {
      console.warn('API_BASE_URL not defined, using fallback');
      API_BASE_URL = 'http://localhost/SystemsMISPYO/PPMP/apiPPMP';
  }

  console.log('Full URL:', `${API_BASE_URL}/api_save_ppmp.php`);

  authenticatedFetch(`${API_BASE_URL}/api_save_ppmp.php`, {
      method: 'POST',
      body: JSON.stringify(ppmpData)
  })
  .then(response => {
      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers);
      if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
  })
  .then(data => {
      console.log('Response data:', data);
      if (data.success) {
          currentPPMPId = data.ppmp_id;
          currentPPMPNumber = data.ppmp_number;

          // Update the PPMP number field with the generated/assigned number
          document.getElementById('ppmp_number').value = data.ppmp_number;

          const actionText = status === 'submitted' ? 'submitted' : 'saved as draft';
          alert(`PPMP ${actionText} successfully! PPMP Number: ${data.ppmp_number}`);
          resolve(data);
          window.location.href = 'ppmp_list.php';
      } else {
          alert('Error: ' + data.message);
          reject(new Error(data.message));
      }
  })
  .catch(error => {
      console.error('Save error:', error);
      alert('Network error: ' + error.message + '. Please check the console for details.');
      reject(error);
  })
  .finally(() => {
      saveBtn.innerHTML = originalText;
      saveBtn.disabled = false;
  });
   });
}

function loadPPMP() {
  const ppmpNumber = prompt('Enter PPMP Number to load:');
  if (!ppmpNumber) return;

  // Show loading
  const loadBtn = document.getElementById('loadBtn');
  const originalText = loadBtn.innerHTML;
  loadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
  loadBtn.disabled = true;

  // First, find PPMP by number
  authenticatedFetch(`${API_BASE_URL}/api_get_ppmp_list.php`)
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          const ppmp = data.ppmp_list.find(p => (p.ppmp_number || p.PPMP_Number) === ppmpNumber);
          if (ppmp) {
              // Load the specific PPMP
              return authenticatedFetch(`${API_BASE_URL}/api_load_ppmp.php?id=${ppmp.id || ppmp.ID}`);
          } else {
              throw new Error('PPMP not found');
          }
      } else {
          throw new Error(data.message);
      }
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          populatePPMPForm(data.ppmp);
          currentPPMPId = data.ppmp.header.ID || data.ppmp.header.id;
          currentPPMPNumber = data.ppmp.header.PPMP_Number || data.ppmp.header.ppmp_number;
          alert('PPMP loaded successfully!');
      } else {
          alert('Error: ' + data.message);
      }
  })
  .catch(error => {
      console.error('Load error:', error);
      alert('Error loading PPMP: ' + error.message);
  })
  .finally(() => {
      loadBtn.innerHTML = originalText;
      loadBtn.disabled = false;
  });
}

function populatePPMPForm(ppmpData) {
   const header = ppmpData.header;
   const entries = ppmpData.entries;

   // Clear selected items set
   selectedItems.clear();

   // Populate header fields (handle case-insensitive column names)
   document.getElementById('ppmp_number').value = header.PPMP_Number || header.ppmp_number || '';
   document.getElementById('plan_year').value = header.Plan_Year || header.plan_year || '';

   // Clear existing table
   const tableBody = document.getElementById('itemsTableBody');
   tableBody.innerHTML = '';

   // Populate entries
   entries.forEach(entry => {
       addRow();

       // Get the last added row
       const rows = tableBody.querySelectorAll('tr');
       const lastRow = rows[rows.length - 1];

       // Set item selection
       const dropdown = lastRow.querySelector('.searchable-dropdown');
       const selectedText = dropdown.querySelector('.selected-text');
       const itemId = entry.Item_ID || entry.item_id;

       // Use entry data directly instead of looking up in itemsList
       const itemCode = entry.Item_Code || entry.item_code || '';
       const itemName = entry.Item_Name || entry.item_name || '';
       const itemDescription = entry.Item_Description || entry.item_description || '';
       const unit = entry.Unit || entry.unit || '';
       const unitCost = entry.Unit_Cost || entry.unit_cost || 0;
       const category = entry.Category || entry.category || '';

       // Set the selected text to show item code and description
       const displayText = itemCode ? '[' + itemCode + '] ' + itemDescription : itemDescription;
       selectedText.textContent = displayText;
       selectedText.title = displayText;

       // Add to selected items set to prevent duplicates
       if (itemId) {
           selectedItems.add(parseInt(itemId));
           dropdown.setAttribute('data-selected-item', itemId);
           dropdown.setAttribute('data-code', itemCode);
           dropdown.setAttribute('data-name', itemName);
           dropdown.setAttribute('data-category', category);
       }

       // Set the description, unit, cost, and category fields
       const descTextarea = lastRow.querySelector('.item-description');
       descTextarea.value = itemDescription;
       descTextarea.title = itemDescription;
       lastRow.querySelector('.item-unit').value = unit;
       const costInput = lastRow.querySelector('.unit_cost');
       costInput.value = unitCost;
       costInput.title = unitCost;
       const categoryInput = lastRow.querySelector('.item-category');
       categoryInput.value = category;
       categoryInput.title = category;

       // Set quantities (ensure non-negative) - handle case-insensitive column names
       lastRow.querySelector('.jan').value = Math.max(0, entry.Jan_Qty || entry.jan_qty || 0);
       lastRow.querySelector('.feb').value = Math.max(0, entry.Feb_Qty || entry.feb_qty || 0);
       lastRow.querySelector('.mar').value = Math.max(0, entry.Mar_Qty || entry.mar_qty || 0);
       lastRow.querySelector('.apr').value = Math.max(0, entry.Apr_Qty || entry.apr_qty || 0);
       lastRow.querySelector('.may').value = Math.max(0, entry.May_Qty || entry.may_qty || 0);
       lastRow.querySelector('.jun').value = Math.max(0, entry.Jun_Qty || entry.jun_qty || 0);
       lastRow.querySelector('.jul').value = Math.max(0, entry.Jul_Qty || entry.jul_qty || 0);
       lastRow.querySelector('.aug').value = Math.max(0, entry.Aug_Qty || entry.aug_qty || 0);
       lastRow.querySelector('.sep').value = Math.max(0, entry.Sep_Qty || entry.sep_qty || 0);
       lastRow.querySelector('.oct').value = Math.max(0, entry.Oct_Qty || entry.oct_qty || 0);
       lastRow.querySelector('.nov').value = Math.max(0, entry.Nov_Qty || entry.nov_qty || 0);
       lastRow.querySelector('.dec').value = Math.max(0, entry.Dec_Qty || entry.dec_qty || 0);

       // Recalculate totals
       recalculateRow(lastRow.querySelector('.jan'));

       // Adjust row height after loading
       setTimeout(() => {
         adjustRowHeight(lastRow);
       }, 50);
   });

   updateItemCount();
   updatePagination();
   showPage(1);
}

function adjustRowHeight(row) {
  // Force the browser to recalculate the row height
  const cells = row.querySelectorAll('td');
  cells.forEach(cell => {
    // Temporarily remove height constraints
    cell.style.height = 'auto';
    cell.style.minHeight = 'auto';

    // Adjust textarea height to fit content
    const textarea = cell.querySelector('textarea');
    if (textarea) {
      textarea.style.height = 'auto';
      textarea.style.height = textarea.scrollHeight + 'px';
    }
  });

  // Force reflow
  row.offsetHeight;

  // Reset to auto height
  row.style.height = 'auto';

  // Find the maximum height among all cells in this row
  let maxHeight = 0;
  cells.forEach(cell => {
    const cellHeight = cell.offsetHeight;
    if (cellHeight > maxHeight) {
      maxHeight = cellHeight;
    }
  });

  // Set all cells to the same height
  if (maxHeight > 0) {
    cells.forEach(cell => {
      cell.style.height = maxHeight + 'px';
      cell.style.minHeight = maxHeight + 'px';
    });
    row.style.height = maxHeight + 'px';
  }
}

function newPPMP() {
    if (confirm('Are you sure you want to start a new PPMP? Any unsaved changes will be lost.')) {
        // Clear selected items set
        selectedItems.clear();

        // Clear form
        document.getElementById('ppmp_number').value = '';
        document.getElementById('plan_year').value = new Date().getFullYear();

        // Clear table
        document.getElementById('itemsTableBody').innerHTML = '';

        // Reset variables
        currentPPMPId = null;
        currentPPMPNumber = '';

        // Reset button text
        document.getElementById('saveDraftBtn').innerHTML = '<i class="fas fa-save"></i> Save Draft';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-paper-plane"></i> Submit';

        // Reset PPMP number to readonly for new PPMP (auto-generated)
        const ppmpNumberField = document.getElementById('ppmp_number');
        ppmpNumberField.readOnly = true;
        ppmpNumberField.value = '';
        ppmpNumberField.placeholder = 'Auto-generated on save';

        // Reset pagination
        currentPage = 1;
        updateItemCount();
        updatePagination();
        showPage(1);

        // Recalculate totals
        document.getElementById('total_items').textContent = '0';
        document.getElementById('grand_total').textContent = '0.00';
    }
}

// Item Selection Modal Functions
function initializeItemSelectionModal() {
    console.log('Initializing item selection modal...');

    // Check if modal exists
    const modal = document.getElementById('itemSelectionModal');
    if (!modal) {
        console.error('Item selection modal not found in DOM');
        return;
    }

    // Modal event listeners
    const searchInput = document.getElementById('itemSearchInput');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const clearAllBtn = document.getElementById('clearAllBtn');
    const addSelectedBtn = document.getElementById('addSelectedItemsBtn');

    if (searchInput) {
        let searchTimeout = null;
        searchInput.addEventListener('input', function() {
            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // Debounce the search - wait 300ms after user stops typing
            searchTimeout = setTimeout(() => {
                loadItemsIntoModal();
            }, 300);
        });
    }
    if (selectAllBtn) selectAllBtn.addEventListener('click', selectAllItems);
    if (clearAllBtn) clearAllBtn.addEventListener('click', clearAllItems);
    if (addSelectedBtn) addSelectedBtn.addEventListener('click', addSelectedItems);
    
    // Add category filter event listener
    const categoryFilter = document.getElementById('itemCategoryFilter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterItems);
    }

    // Fallback function to close modal manually
    function closeModalManually() {
        const modal = document.getElementById('itemSelectionModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            // Clear selections
            modalSelectedItems.clear();
            updateSelectedCount();
            console.log('Modal closed manually');
        }
    }

    // Add manual cancel button handler
    const cancelBtn = document.querySelector('#itemSelectionModal .btn-secondary[data-dismiss="modal"]');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            console.log('Cancel button clicked');
            // Try jQuery first, then fallback
            if (typeof $ !== 'undefined') {
                try {
                    $('#itemSelectionModal').modal('hide');
                } catch (error) {
                    console.warn('jQuery modal hide failed, using fallback');
                    closeModalManually();
                }
            } else {
                closeModalManually();
            }
        });
    }

    // Add manual close button handler (X button)
    const closeBtn = document.querySelector('#itemSelectionModal .close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            console.log('Close button clicked');
            // Try jQuery first, then fallback
            if (typeof $ !== 'undefined') {
                try {
                    $('#itemSelectionModal').modal('hide');
                } catch (error) {
                    console.warn('jQuery modal hide failed, using fallback');
                    closeModalManually();
                }
            } else {
                closeModalManually();
            }
        });
    }

    // Modal show event
    $('#itemSelectionModal').on('show.bs.modal', function() {
        console.log('Modal show event triggered');
        loadItemsIntoModal();
    });

    // Modal hide event
    $('#itemSelectionModal').on('hide.bs.modal', function() {
        console.log('Modal hide event triggered');
        modalSelectedItems.clear();
        updateSelectedCount();
    });

    console.log('Item selection modal initialized successfully');
}

function openItemSelectionModal() {
    console.log('Opening item selection modal...');
    if (typeof $ !== 'undefined' && $('#itemSelectionModal').length > 0) {
        try {
            $('#itemSelectionModal').modal('show');
            console.log('Modal opened successfully with jQuery');
        } catch (error) {
            console.warn('jQuery modal failed, using fallback:', error);
            openModalManually();
        }
    } else {
        console.error('jQuery or modal not found, using fallback');
        openModalManually();
    }
}

// Fallback function to open modal manually
function openModalManually() {
    const modal = document.getElementById('itemSelectionModal');
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');

        // Create backdrop if it doesn't exist
        let backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop show';
            document.body.appendChild(backdrop);
        }

        // Load items into modal
        loadItemsIntoModal();
        console.log('Modal opened successfully with fallback');
    } else {
        alert('Modal not found. Please refresh the page.');
    }
}

function loadItemsIntoModal() {
    const container = document.getElementById('itemsListContainer');
    const searchInput = document.getElementById('itemSearchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const categoryFilter = document.getElementById('itemCategoryFilter') ? document.getElementById('itemCategoryFilter').value : '';
    
    // Function to display items in the modal
    const displayItemsInModal = (items) => {
        // Filter out already selected items
        let filteredItems = items.filter(item => !selectedItems.has(parseInt(item.ID)));
        
        // Apply category filter (if not already applied via API)
        if (categoryFilter && !searchTerm) {
            filteredItems = filteredItems.filter(item => item.Category === categoryFilter);
        }
        
        // Limit to 100 items for display performance
        const displayItems = filteredItems.slice(0, ITEMS_DISPLAY_LIMIT);
        const hasMoreItems = filteredItems.length > ITEMS_DISPLAY_LIMIT;

        if (displayItems.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-4" style="color: var(--text-muted);">' + 
                (searchTerm || categoryFilter ? 'No items found matching your filters' : 'All items have been added to the PPMP') + '</div>';
            return;
        }

        container.innerHTML = displayItems.map(item => `
            <div class="item-checkbox-container mb-2 p-2 border rounded">
                <div class="form-check">
                    <input class="form-check-input item-checkbox" type="checkbox" value="${item.ID}" id="item_${item.ID}">
                    <label class="form-check-label" for="item_${item.ID}" style="cursor: pointer; width: 100%;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="font-weight-bold" style="color: #3498db;">
                                    ${item.Item_Name || 'N/A'}
                                </div>
                                <div class="small" style="color: var(--text-primary); font-weight: 500;">
                                    ${item.Items_Description || 'No description'}
                                </div>
                                <div class="small">
                                    <span class="badge badge-secondary" style="background: #27ae60;">${item.Category || 'Uncategorized'}</span> | 
                                    <span style="color: #ff6600;">Unit: ${item.Unit || 'N/A'}</span> | 
                                    <span style="color: #ff6600; font-weight: 500;">Cost: ₱${parseFloat(item.Unit_Cost || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        `).join('') + (hasMoreItems ? `<div class="text-center text-muted py-2" style="color: var(--text-muted); font-size: 0.85rem;">Showing first ${ITEMS_DISPLAY_LIMIT} of ${filteredItems.length} matching items. Refine filters to see more.</div>` : '');

        // Add event listeners to checkboxes
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    modalSelectedItems.add(parseInt(this.value));
                } else {
                    modalSelectedItems.delete(parseInt(this.value));
                }
                updateSelectedCount();
            });
        });

        updateSelectedCount();

        // Force theme application to modal content
        applyThemeToModal();
    };
    
    // If there's a search term, search the API for all items
    if (searchTerm && searchTerm.length >= 2) {
        // Show loading indicator
        container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
        
        searchItemsAPI(searchTerm).then(searchResults => {
            displayItemsInModal(searchResults);
        }).catch(error => {
            console.error('Search error:', error);
            container.innerHTML = '<div class="text-center text-muted py-4">Error searching items. Please try again.</div>';
        });
    } else if (categoryFilter) {
        // Category filter selected - fetch ALL items for this category from API
        container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Loading items by category...</div>';
        
        // Fetch all items for the selected category (limit=10000 to get all)
        authenticatedFetch(`${API_BASE_URL}/get_items.php?category=${encodeURIComponent(categoryFilter)}&limit=10000`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                if (!text || text.trim() === '') {
                    displayItemsInModal([]);
                    return;
                }
                try {
                    const data = JSON.parse(text);
                    const categoryItems = data.items || [];
                    console.log(`DEBUG: Loaded ${categoryItems.length} items for category: ${categoryFilter}`);
                    displayItemsInModal(categoryItems);
                } catch (e) {
                    console.error('Error parsing category items:', e);
                    container.innerHTML = '<div class="text-center text-muted py-4">Error loading category items. Please try again.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading category items:', error);
                container.innerHTML = '<div class="text-center text-muted py-4">Error loading category items. Please try again.</div>';
            });
    } else {
        // No search term and no category filter - show items from the loaded itemsList
        const availableItems = itemsList.filter(item => !selectedItems.has(parseInt(item.ID)));
        displayItemsInModal(availableItems);
    }
}

// Function to apply theme to modal content
function applyThemeToModal() {
    const modal = document.getElementById('itemSelectionModal');
    if (!modal) return;

    const currentTheme = document.body.getAttribute('data-theme') || 'light';

    // Force repaint of modal elements to apply CSS variables
    const modalElements = modal.querySelectorAll('*');
    modalElements.forEach(element => {
        // Force style recalculation
        const computedStyle = getComputedStyle(element);
        const color = computedStyle.color;
        const background = computedStyle.backgroundColor;

        // Reapply styles to ensure CSS variables are applied
        element.style.color = color;
        element.style.backgroundColor = background;
    });

    console.log('Theme applied to modal for theme:', currentTheme);
}

function filterItems() {
    // Re-load items with the current filters (loadItemsIntoModal handles both search and category)
    loadItemsIntoModal();
}

function selectAllItems() {
    const visibleCheckboxes = document.querySelectorAll('.item-checkbox-container[style*="block"] .item-checkbox, .item-checkbox-container:not([style*="none"]) .item-checkbox');
    visibleCheckboxes.forEach(checkbox => {
        if (!checkbox.checked) {
            checkbox.checked = true;
            modalSelectedItems.add(parseInt(checkbox.value));
        }
    });
    updateSelectedCount();
}

function clearAllItems() {
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        modalSelectedItems.delete(parseInt(checkbox.value));
    });
    updateSelectedCount();
    
    // Clear search input and reload original 100 items
    const searchInput = document.getElementById('itemSearchInput');
    if (searchInput) {
        searchInput.value = '';
    }
    loadItemsIntoModal();
}

function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = modalSelectedItems.size;
}

function addSelectedItems() {
    if (modalSelectedItems.size === 0) {
        alert('Please select at least one item.');
        return;
    }

    // Add rows for each selected item
    modalSelectedItems.forEach(itemId => {
        const item = itemsList.find(item => parseInt(item.ID) === itemId);
        if (item) {
            addRowForItem(item);
        }
    });

    // Close modal
    $('#itemSelectionModal').modal('hide');

    // Update counts and pagination
    updateItemCount();
    updatePagination();
    showPage(currentPage);
}

// Searchable Dropdown Functionality
function initializeSearchableDropdown(row) {
    const dropdown = row.querySelector('.searchable-dropdown');
    const display = dropdown.querySelector('.dropdown-display');
    const menu = dropdown.querySelector('.dropdown-menu');
    const searchInput = dropdown.querySelector('.search-input');
    const items = dropdown.querySelectorAll('.dropdown-item');
    const selectedText = dropdown.querySelector('.selected-text');

    let selectedItemId = null;
    let currentFocus = -1;

    // Toggle dropdown
    display.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleDropdown();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            closeDropdown();
        }
    });

    // Search functionality - searches ALL items via API
    let searchTimeout = null;
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        if (searchTerm.length === 0) {
            // Reset to show only first 100 items (non-selected items)
            const dropdownItems = dropdown.querySelector('.dropdown-items');
            dropdownItems.innerHTML = '';
            
            // Rebuild with first 100 non-selected items from itemsList
            let displayCount = 0;
            itemsList.forEach(item => {
                if (displayCount >= ITEMS_DISPLAY_LIMIT) return;
                if (selectedItems.has(parseInt(item.ID))) return;
                
                const div = document.createElement('div');
                div.className = 'dropdown-item';
                div.setAttribute('data-value', item.ID);
                div.setAttribute('data-description', item.Items_Description || '');
                div.setAttribute('data-unit', item.Unit || '');
                div.setAttribute('data-cost', item.Unit_Cost || '0');
                div.setAttribute('data-code', item.Item_Code || '');
                div.setAttribute('data-name', item.Item_Name || '');
                div.setAttribute('data-category', item.Category || '');
                div.textContent = item.Items_Description;
                div.addEventListener('click', function() {
                    selectItem(this);
                });
                dropdownItems.appendChild(div);
                displayCount++;
            });
            
            const noResults = dropdown.querySelector('.no-results');
            if (noResults) noResults.style.display = 'none';
            currentFocus = -1;
            return;
        }
        
        // Debounce search - wait 300ms after user stops typing
        searchTimeout = setTimeout(async () => {
            // Show loading indicator
            const loadingIndicator = dropdown.querySelector('.search-loading');
            if (loadingIndicator) loadingIndicator.style.display = 'block';
            
            // Search ALL items via API
            const searchResults = await searchItemsAPI(searchTerm);
            
            // Hide loading indicator
            if (loadingIndicator) loadingIndicator.style.display = 'none';
            
            // Filter out already selected items
            const availableItems = searchResults.filter(item => !selectedItems.has(parseInt(item.ID)));
            
            // Rebuild dropdown with search results (up to 100 items)
            const dropdownItems = dropdown.querySelector('.dropdown-items');
            dropdownItems.innerHTML = '';
            
            let displayCount = 0;
            availableItems.forEach(item => {
                if (displayCount >= ITEMS_DISPLAY_LIMIT) return;
                
                const div = document.createElement('div');
                div.className = 'dropdown-item';
                div.setAttribute('data-value', item.ID);
                div.setAttribute('data-description', item.Items_Description || '');
                div.setAttribute('data-unit', item.Unit || '');
                div.setAttribute('data-cost', item.Unit_Cost || '0');
                div.setAttribute('data-code', item.Item_Code || '');
                div.setAttribute('data-name', item.Item_Name || '');
                div.setAttribute('data-category', item.Category || '');
                div.textContent = item.Items_Description;
                div.addEventListener('click', function() {
                    selectItem(this);
                });
                dropdownItems.appendChild(div);
                displayCount++;
            });
            
            // Show no results message
            const noResults = dropdown.querySelector('.no-results') || document.createElement('div');
            noResults.className = 'no-results';
            noResults.textContent = 'No items found';
            
            if (availableItems.length === 0) {
                noResults.style.display = 'block';
                dropdownItems.appendChild(noResults);
            } else {
                noResults.style.display = 'none';
            }
            
            // Update items reference for keyboard navigation
            const newItems = dropdown.querySelectorAll('.dropdown-item');
            newItems.forEach((item, index) => {
                item.addEventListener('click', function() {
                    selectItem(this);
                });
            });
            
            currentFocus = -1;
        }, 300); // 300ms debounce
    });

    // Keyboard navigation - use dynamic items from DOM
    searchInput.addEventListener('keydown', function(e) {
        // Get current visible items from DOM (dynamic, after search)
        const dropdownItems = dropdown.querySelector('.dropdown-items');
        const visibleItems = Array.from(dropdownItems.querySelectorAll('.dropdown-item')).filter(item => item.style.display !== 'none');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus = Math.min(currentFocus + 1, visibleItems.length - 1);
            updateFocus(visibleItems);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus = Math.max(currentFocus - 1, -1);
            updateFocus(visibleItems);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocus >= 0 && visibleItems[currentFocus]) {
                selectItem(visibleItems[currentFocus]);
            }
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    // Item selection
    items.forEach(item => {
        item.addEventListener('click', function() {
            selectItem(this);
        });
    });

    function toggleDropdown() {
        const isOpen = dropdown.classList.contains('open');
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    }

    function openDropdown() {
        dropdown.classList.add('open');
        searchInput.focus();
        searchInput.value = '';
        // Reset all items to visible (first 100)
        items.forEach((item, index) => {
            item.style.display = index < ITEMS_DISPLAY_LIMIT ? 'block' : 'none';
        });
        const noResults = dropdown.querySelector('.no-results');
        if (noResults) noResults.style.display = 'none';
    }

    function closeDropdown() {
        dropdown.classList.remove('open');
        currentFocus = -1;
        items.forEach(item => item.classList.remove('highlighted'));
    }

    function selectItem(item) {
        const itemId = item.getAttribute('data-value');

        // Remove previously selected item from set (if any)
        if (selectedItemId) {
            selectedItems.delete(parseInt(selectedItemId));
        }

        // Check for duplicate selection
        if (itemId && selectedItems.has(parseInt(itemId))) {
            alert('This item has already been selected in another row. Please choose a different item.');
            return;
        }

        // Add new selection to set (if not empty)
        if (itemId) {
            selectedItems.add(parseInt(itemId));
            selectedItemId = itemId;
            dropdown.setAttribute('data-selected-item', itemId);
        } else {
            selectedItemId = null;
            dropdown.removeAttribute('data-selected-item');
        }

        // Update display text
        selectedText.textContent = itemId ? item.textContent : 'Select Item';

        // Store additional data for reference
        const description = item.getAttribute('data-description') || '';
        const unit = item.getAttribute('data-unit') || '';
        const cost = item.getAttribute('data-cost') || '0';
        const code = item.getAttribute('data-code') || '';
        const name = item.getAttribute('data-name') || '';
        const category = item.getAttribute('data-category') || '';

        dropdown.setAttribute('data-code', code);
        dropdown.setAttribute('data-name', name);
        dropdown.setAttribute('data-category', category);

        row.querySelector('.item-description').value = description;
        row.querySelector('.item-unit').value = unit;
        row.querySelector('.item-category').value = category;
        row.querySelector('.unit_cost').value = cost;

        // Force height recalculation after content change
        setTimeout(() => {
            adjustRowHeight(row);
        }, 10);

        recalculateRow(row.querySelector('.jan')); // Trigger recalculation
        closeDropdown();
    }

    function updateFocus(visibleItems) {
        // Remove highlighted from all items in dropdown-items
        const dropdownItems = dropdown.querySelector('.dropdown-items');
        dropdownItems.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('highlighted'));

        if (currentFocus >= 0 && visibleItems[currentFocus]) {
            visibleItems[currentFocus].classList.add('highlighted');
            visibleItems[currentFocus].scrollIntoView({ block: 'nearest' });
        }
    }
}
