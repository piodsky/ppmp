document.addEventListener("DOMContentLoaded", function () {
  const itemForm = document.getElementById("itemForm");
  const itemTableBody = document.querySelector("#itemsTable tbody");
  const itemCodeInput = document.getElementById("itemCode");
  const itemNameInput = document.getElementById("itemName");
  const itemDescriptionInput = document.getElementById("itemDescription");
  const itemCodeSuggestions = document.getElementById("itemCodeSuggestions");

  // New elements for pagination and search
  const searchInput = document.getElementById("searchInput");
  const categoryFilter = document.getElementById("categoryFilter");
  const clearFiltersBtn = document.getElementById("clearFilters");
  const itemsPerPageSelect = document.getElementById("itemsPerPage");
  const paginationControls = document.getElementById("paginationControls");
  const paginationInfo = document.getElementById("paginationInfo");
  const totalItemsBadge = document.getElementById("totalItems");

  let allItems = []; // Store all items for suggestions
  let currentPage = parseInt(localStorage.getItem('ppmp_current_page')) || 1;
  let itemsPerPage = parseInt(localStorage.getItem('ppmp_items_per_page')) || 50;
  let currentSearch = localStorage.getItem('ppmp_current_search') || '';
  let currentCategory = localStorage.getItem('ppmp_current_category') || '';
  let isEditMode = false;
  let editingItemId = null;

  // Save current state to localStorage
  function saveState() {
    localStorage.setItem('ppmp_current_page', currentPage);
    localStorage.setItem('ppmp_items_per_page', itemsPerPage);
    localStorage.setItem('ppmp_current_search', currentSearch);
    localStorage.setItem('ppmp_current_category', currentCategory);
  }

  // Restore UI state from saved values
  function restoreUIState() {
    searchInput.value = currentSearch;
    categoryFilter.value = currentCategory;
    itemsPerPageSelect.value = itemsPerPage;
  }

  // 🔄 Load items with pagination and search
  function loadItems(page = currentPage) {
    currentPage = page; // Update current page
    const params = new URLSearchParams({
      page: page,
      limit: itemsPerPage,
      search: currentSearch,
      category: currentCategory
    });

    return fetch(`${API_BASE_URL}/get_items.php?${params}`)
      .then(res => res.json())
      .then(data => {
        if (data.error) {
          throw new Error(data.error);
        }

        const { items, pagination } = data;
        allItems = items; // Store items for suggestions

        // Update UI
        renderTable(items, pagination);
        renderPagination(pagination);
        updatePaginationInfo(pagination);

        // Update suggestions
        updateItemCodeSuggestions(items);

        return data;
      })
      .catch(err => {
        alert("⚠️ Error loading items:\n" + err.message);
        throw err;
      });
  }

  // 📊 Render table with items
  function renderTable(items, pagination) {
    itemTableBody.innerHTML = "";

    if (items.length === 0) {
      itemTableBody.innerHTML = `
        <tr>
          <td colspan="8" class="text-center text-muted py-4">
            <i class="fas fa-inbox fa-2x mb-2"></i><br>
            No items found
          </td>
        </tr>
      `;
      return;
    }

    const startIndex = (pagination.current_page - 1) * pagination.items_per_page;

    items.forEach((item, index) => {
      const rowNumber = startIndex + index + 1;
      itemTableBody.innerHTML += `
        <tr>
          <td>${rowNumber}</td>
          <td>${item.Item_Code || ''}</td>
          <td>${item.Item_Name || ''}</td>
          <td>${item.Items_Description || ''}</td>
          <td>${item.Unit || ''}</td>
          <td>₱${parseFloat(item.Unit_Cost || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
          <td>${item.Category || ''}</td>
          <td>
            <button class="btn btn-sm btn-warning edit-btn me-1" data-id="${item.ID}" data-code="${item.Item_Code}" data-name="${item.Item_Name}" data-description="${item.Items_Description}" data-unit="${item.Unit}" data-cost="${item.Unit_Cost}" data-category="${item.Category}" title="Edit Item">
              ✏️ Edit
            </button>
            <button class="btn btn-sm btn-danger delete-btn" data-id="${item.ID}" data-code="${item.Item_Code}" title="Delete Item">
              🗑️ Delete
            </button>
          </td>
        </tr>
      `;
    });
  }

  // 📄 Render pagination controls
  function renderPagination(pagination) {
    paginationControls.innerHTML = "";

    if (pagination.total_pages <= 1) return;

    const { current_page, total_pages, has_prev, has_next } = pagination;

    // Previous button
    const prevBtn = document.createElement("li");
    prevBtn.className = `page-item ${!has_prev ? 'disabled' : ''}`;
    prevBtn.innerHTML = `<a class="page-link" href="#" ${has_prev ? '' : 'tabindex="-1" aria-disabled="true"'}>Previous</a>`;
    if (has_prev) {
      prevBtn.addEventListener("click", (e) => {
        e.preventDefault();
        currentPage = current_page - 1;
        saveState();
        loadItems(currentPage);
      });
    }
    paginationControls.appendChild(prevBtn);

    // Page numbers
    const startPage = Math.max(1, current_page - 2);
    const endPage = Math.min(total_pages, current_page + 2);

    if (startPage > 1) {
      const firstBtn = document.createElement("li");
      firstBtn.className = "page-item";
      firstBtn.innerHTML = `<a class="page-link" href="#">1</a>`;
      firstBtn.addEventListener("click", (e) => {
        e.preventDefault();
        currentPage = 1;
        saveState();
        loadItems(1);
      });
      paginationControls.appendChild(firstBtn);

      if (startPage > 2) {
        const ellipsis = document.createElement("li");
        ellipsis.className = "page-item disabled";
        ellipsis.innerHTML = `<span class="page-link">...</span>`;
        paginationControls.appendChild(ellipsis);
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      const pageBtn = document.createElement("li");
      pageBtn.className = `page-item ${i === current_page ? 'active' : ''}`;
      pageBtn.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      pageBtn.addEventListener("click", (e) => {
        e.preventDefault();
        currentPage = i;
        saveState();
        loadItems(i);
      });
      paginationControls.appendChild(pageBtn);
    }

    if (endPage < total_pages) {
      if (endPage < total_pages - 1) {
        const ellipsis = document.createElement("li");
        ellipsis.className = "page-item disabled";
        ellipsis.innerHTML = `<span class="page-link">...</span>`;
        paginationControls.appendChild(ellipsis);
      }

      const lastBtn = document.createElement("li");
      lastBtn.className = "page-item";
      lastBtn.innerHTML = `<a class="page-link" href="#">${total_pages}</a>`;
      lastBtn.addEventListener("click", (e) => {
        e.preventDefault();
        currentPage = total_pages;
        saveState();
        loadItems(total_pages);
      });
      paginationControls.appendChild(lastBtn);
    }

    // Next button
    const nextBtn = document.createElement("li");
    nextBtn.className = `page-item ${!has_next ? 'disabled' : ''}`;
    nextBtn.innerHTML = `<a class="page-link" href="#" ${has_next ? '' : 'tabindex="-1" aria-disabled="true"'}>Next</a>`;
    if (has_next) {
      nextBtn.addEventListener("click", (e) => {
        e.preventDefault();
        currentPage = current_page + 1;
        saveState();
        loadItems(currentPage);
      });
    }
    paginationControls.appendChild(nextBtn);
  }

  // ℹ️ Update pagination info
  function updatePaginationInfo(pagination) {
    const { current_page, total_pages, total_items, items_per_page } = pagination;
    const startItem = (current_page - 1) * items_per_page + 1;
    const endItem = Math.min(current_page * items_per_page, total_items);

    if (total_items === 0) {
      paginationInfo.textContent = "No items found";
    } else {
      paginationInfo.textContent = `Showing ${startItem}-${endItem} of ${total_items} items`;
    }

    totalItemsBadge.textContent = total_items;
  }

  // 🔍 Update item code suggestions
  function updateItemCodeSuggestions(items) {
    itemCodeSuggestions.innerHTML = "";
    items.forEach(item => {
      if (item.Item_Code) {
        const option = document.createElement("option");
        option.value = item.Item_Code;
        option.setAttribute("data-name", item.Item_Name || "");
        option.setAttribute("data-description", item.Items_Description || "");
        itemCodeSuggestions.appendChild(option);
      }
    });
  }

  // 🎯 Auto-fill form when item code is selected
  itemCodeInput.addEventListener("input", function() {
    const selectedCode = this.value;
    const selectedOption = Array.from(itemCodeSuggestions.options).find(option => option.value === selectedCode);

    if (selectedOption) {
      itemNameInput.value = selectedOption.getAttribute("data-name") || "";
      itemDescriptionInput.value = selectedOption.getAttribute("data-description") || "";
    }
  });

  // 🔍 Smart suggestions based on item name and description
  function updateSuggestions() {
    const nameValue = itemNameInput.value.toLowerCase().trim();
    const descValue = itemDescriptionInput.value.toLowerCase().trim();

    // Clear current suggestions
    itemCodeSuggestions.innerHTML = "";

    // If no items loaded yet, don't show suggestions
    if (allItems.length === 0) {
      return;
    }

    // If input is too short, show all suggestions
    if (nameValue.length < 2 && descValue.length < 2) {
      allItems.forEach(item => {
        if (item.Item_Code) {
          const option = document.createElement("option");
          option.value = item.Item_Code;
          option.setAttribute("data-name", item.Item_Name || "");
          option.setAttribute("data-description", item.Items_Description || "");
          itemCodeSuggestions.appendChild(option);
        }
      });
      return;
    }

    // Filter and show matching suggestions
    allItems.forEach(item => {
      const itemName = (item.Item_Name || "").toLowerCase();
      const itemDesc = (item.Items_Description || "").toLowerCase();

      // Check if name or description matches the input
      if ((nameValue && itemName.includes(nameValue)) ||
          (descValue && itemDesc.includes(descValue))) {
        const option = document.createElement("option");
        option.value = item.Item_Code;
        option.setAttribute("data-name", item.Item_Name || "");
        option.setAttribute("data-description", item.Items_Description || "");
        itemCodeSuggestions.appendChild(option);
      }
    });

    console.log(`Suggestions updated: ${itemCodeSuggestions.children.length} items found for "${nameValue}" or "${descValue}"`);
  }

  // Add event listeners for smart suggestions
  itemNameInput.addEventListener("input", function() {
    setTimeout(updateSuggestions, 100); // Small delay to ensure value is updated
  });

  itemDescriptionInput.addEventListener("input", function() {
    setTimeout(updateSuggestions, 100); // Small delay to ensure value is updated
  });

  // 🎯 Real-time validation feedback
  function validateField(field, validator) {
    const value = field.value.trim();
    const isValid = validator(value);
    field.classList.toggle('is-valid', isValid && value !== '');
    field.classList.toggle('is-invalid', !isValid && value !== '');

    // Hide/show validation message
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
      feedback.style.display = (!isValid && value !== '') ? 'block' : 'none';
    }
  }

  // Add real-time validation to required fields
  itemCodeInput.addEventListener('input', function() {
    validateField(this, value => value.length > 0);
  });

  itemNameInput.addEventListener('input', function() {
    validateField(this, value => value.length > 0);
  });

  itemDescriptionInput.addEventListener('input', function() {
    validateField(this, value => value.length > 0);
  });

  document.getElementById('unit').addEventListener('change', function() {
    validateField(this, value => value !== '');
  });

  document.getElementById('unitCost').addEventListener('input', function() {
    const value = parseFloat(this.value);
    validateField(this, val => !isNaN(value) && value > 0);
  });

  document.getElementById('category').addEventListener('change', function() {
    validateField(this, value => value !== '');
  });

  // Clear validation on modal close
  document.getElementById('addItemModal').addEventListener('hidden.bs.modal', function() {
    itemForm.classList.remove('was-validated');
    itemForm.reset();

    // Clear validation classes
    const fields = itemForm.querySelectorAll('.form-control');
    fields.forEach(field => {
      field.classList.remove('is-valid', 'is-invalid');
      const feedback = field.parentNode.querySelector('.invalid-feedback');
      if (feedback) {
        feedback.style.display = 'none';
      }
    });

    // Reset to add mode
    isEditMode = false;
    editingItemId = null;
    document.querySelector("#addItemModalLabel").textContent = "➕ Add Item";
    document.querySelector("#itemForm button[type='submit']").innerHTML = "💾 Save";
  });

  // 💾 Save item with enhanced validation
  itemForm.addEventListener("submit", function (e) {
    e.preventDefault();

    // Clear previous validation states
    itemForm.classList.remove('was-validated');

    // Get form values
    const itemCode = document.getElementById("itemCode").value.trim();
    const itemName = document.getElementById("itemName").value.trim();
    const itemDescription = document.getElementById("itemDescription").value.trim();
    const unit = document.getElementById("unit").value.trim();
    const unitCost = parseFloat(document.getElementById("unitCost").value);
    const category = document.getElementById("category").value.trim();

    // Enhanced validation
    let isValid = true;
    let errorMessages = [];

    // Check required fields
    if (!itemCode) {
      errorMessages.push("Item Code is required");
      isValid = false;
    }

    if (!itemName) {
      errorMessages.push("Item Name is required");
      isValid = false;
    }

    if (!itemDescription) {
      errorMessages.push("Item Description is required");
      isValid = false;
    }

    if (!unit) {
      errorMessages.push("Unit selection is required");
      isValid = false;
    }

    if (!unitCost || unitCost <= 0) {
      errorMessages.push("Unit Cost must be greater than 0");
      isValid = false;
    }

    if (!category) {
      errorMessages.push("Category selection is required");
      isValid = false;
    }

    // Check for duplicate item codes
    if (itemCode && allItems.some(item => item.Item_Code === itemCode)) {
      errorMessages.push("Item Code already exists. Please use a unique code.");
      isValid = false;
    }

    if (!isValid) {
      // Show validation errors
      itemForm.classList.add('was-validated');
      alert("❌ Please correct the following errors:\n\n" + errorMessages.join("\n"));
      return;
    }

    // Show loading state
    const submitBtn = itemForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '⏳ Saving...';
    submitBtn.disabled = true;

    const payload = {
      item_code: itemCode,
      item_name: itemName,
      item_description: itemDescription,
      unit: unit,
      unit_cost: unitCost,
      category: category
    };

    // Determine API endpoint and method based on mode
    const apiUrl = isEditMode
      ? `${API_BASE_URL}/api_update_ppmp_item.php`
      : `${API_BASE_URL}/api_save_ppmp_item.php`;

    if (isEditMode) {
      payload.id = editingItemId;
    }

    fetch(apiUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("✅ " + data.message);
          itemForm.reset();
          itemForm.classList.remove('was-validated');
          bootstrap.Modal.getInstance(document.getElementById("addItemModal")).hide();

          // Reload the same page to maintain pagination position
          loadItems(currentPage);

          // Reset edit mode
          isEditMode = false;
          editingItemId = null;
        } else {
          alert("❌ " + data.message);
        }
      })
      .catch(err => {
        alert("❌ Failed to save item:\n" + err.message);
      })
      .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
  });

  // 🎯 Event Listeners for Search and Filter
  searchInput.addEventListener("input", function() {
    currentSearch = this.value.trim();
    currentPage = 1; // Reset to first page
    saveState();
    loadItems(1);
  });

  categoryFilter.addEventListener("change", function() {
    currentCategory = this.value;
    currentPage = 1; // Reset to first page
    saveState();
    loadItems(1);
  });

  clearFiltersBtn.addEventListener("click", function() {
    searchInput.value = "";
    categoryFilter.value = "";
    currentSearch = "";
    currentCategory = "";
    currentPage = 1;
    saveState();
    loadItems(1);
  });

  itemsPerPageSelect.addEventListener("change", function() {
    itemsPerPage = parseInt(this.value);
    currentPage = 1; // Reset to first page
    saveState();
    loadItems(1);
  });

  // ✏️ Edit functionality
  document.addEventListener("click", function(e) {
    if (e.target.classList.contains("edit-btn")) {
      const button = e.target;
      const itemId = button.getAttribute("data-id");

      // Populate form with item data
      document.getElementById("itemCode").value = button.getAttribute("data-code") || "";
      document.getElementById("itemName").value = button.getAttribute("data-name") || "";
      document.getElementById("itemDescription").value = button.getAttribute("data-description") || "";
      document.getElementById("unit").value = button.getAttribute("data-unit") || "";
      document.getElementById("unitCost").value = button.getAttribute("data-cost") || "";
      document.getElementById("category").value = button.getAttribute("data-category") || "";

      // Set edit mode
      isEditMode = true;
      editingItemId = itemId;

      // Update modal title and button
      document.querySelector("#addItemModalLabel").textContent = "✏️ Edit Item";
      document.querySelector("#itemForm button[type='submit']").innerHTML = "💾 Update";

      // Show modal
      const modal = new bootstrap.Modal(document.getElementById("addItemModal"));
      modal.show();
    }
  });

  // 🗑️ Delete functionality
  document.addEventListener("click", function(e) {
    if (e.target.classList.contains("delete-btn")) {
      const button = e.target;
      const itemId = button.getAttribute("data-id");
      const itemCode = button.getAttribute("data-code");

      if (confirm(`Are you sure you want to delete item "${itemCode}"?\n\nThis action cannot be undone.`)) {
        deleteItem(itemId, itemCode);
      }
    }
  });

  // 🗑️ Delete item function
  function deleteItem(itemId, itemCode) {
    fetch(`${API_BASE_URL}/api_delete_ppmp_item.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: itemId })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("✅ " + data.message);

          // Check if we need to adjust the page after deletion
          const itemsOnCurrentPage = document.querySelectorAll("#itemsTable tbody tr").length;

          // If this was the only item on the page and we're not on page 1, go to previous page
          if (itemsOnCurrentPage === 1 && currentPage > 1) {
            loadItems(currentPage - 1);
          } else {
            // Otherwise, reload the same page
            loadItems(currentPage);
          }
        } else {
          alert("❌ " + data.message);
        }
      })
      .catch(err => {
        alert("❌ Failed to delete item:\n" + err.message);
      });
  }

  // ⏳ Initial load
  loadItems(currentPage).then(() => {
    // Restore UI state after items are loaded
    restoreUIState();
    // Initialize suggestions after items are loaded
    updateSuggestions();
  });
});