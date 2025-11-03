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

document.addEventListener("DOMContentLoaded", function () {
  // Initialize user data from localStorage (simplified approach)
  initializeUserData();
  loadPPMPList();
});

function initializeUserData() {
  // Get user data from localStorage
  const userData = getUserData();

  if (userData) {
    currentUser = {
      username: userData.username || '',
      role: userData.role || 'user'
    };
    userRole = userData.role || 'user';
  }
}

function loadPPMPList() {
  const tableBody = document.getElementById("ppmpTableBody");

  // Show loading
  tableBody.innerHTML = `
    <tr>
      <td colspan="10" class="text-center py-4">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <p class="mt-2">Loading PPMP documents...</p>
      </td>
    </tr>
  `;

  authenticatedFetch(`${API_BASE_URL}/api_get_ppmp_list.php`)
    .then(res => {
      if (!res.ok) {
        return res.json().then(err => {
          throw new Error(err.message || 'API request failed');
        });
      }
      return res.json();
    })
    .then(data => {
      if (data.success) {
        displayPPMPList(data.ppmp_list);
      } else {
        showError("Failed to load PPMP list: " + data.message);
      }
    })
    .catch(err => {
      showError("Error loading PPMP: " + err.message);
    });
}

function displayPPMPList(ppmpList) {
  const tableBody = document.getElementById("ppmpTableBody");

  if (ppmpList.length === 0) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="10" class="text-center py-4">
          <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No PPMP documents found</h5>
          <p class="text-muted">Create your first PPMP document to get started.</p>
          <button class="btn btn-primary" onclick="window.location.href='ppmp.php'">
            <i class="fas fa-plus"></i> Create PPMP
          </button>
        </td>
      </tr>
    `;
    return;
  }

  tableBody.innerHTML = "";

  ppmpList.forEach((ppmp, index) => {
    const statusClass = getStatusClass(ppmp.status);
    const formattedDate = new Date(ppmp.date_created).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });

    tableBody.innerHTML += `
      <tr>
        <td>${index + 1}</td>
        <td>
          <strong>${ppmp.ppmp_number}</strong>
        </td>
        <td>${ppmp.plan_year}</td>
        <td>
          <span class="status-badge ${statusClass}">${ppmp.status.toUpperCase()}</span>
        </td>
        <td>${ppmp.department}</td>
        <td>
          <span class="badge bg-primary">${ppmp.total_items}</span>
        </td>
        <td>
          <strong>₱${ppmp.total_cost.toLocaleString('en-US', { minimumFractionDigits: 2 })}</strong>
        </td>
        <td>${ppmp.created_by}</td>
        <td>
          <small class="text-muted">${formattedDate}</small>
        </td>
        <td>
          <div class="btn-group" role="group">
            <button class="btn btn-sm btn-info" onclick="viewPPMP(${ppmp.id})" title="View PPMP">
              <i class="fas fa-eye"></i>
            </button>
            ${ppmp.status !== 'rejected' ? `
            <button class="btn btn-sm btn-warning" onclick="editPPMP(${ppmp.id})" title="Edit PPMP">
              <i class="fas fa-edit"></i>
            </button>
            ` : ''}
            ${ppmp.status === 'submitted' && userRole === 'admin' ? `
            <button class="btn btn-sm btn-success" onclick="approvePPMP(${ppmp.id}, '${ppmp.ppmp_number}', this)" title="Approve PPMP">
              <i class="fas fa-check"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="rejectPPMP(${ppmp.id}, '${ppmp.ppmp_number}', this)" title="Reject PPMP">
              <i class="fas fa-times"></i>
            </button>
            ` : ''}
            ${ppmp.status === 'rejected' ? `
            <button class="btn btn-sm btn-primary" onclick="resubmitPPMP(${ppmp.id}, '${ppmp.ppmp_number}', this)" title="Resubmit PPMP">
              <i class="fas fa-redo"></i>
            </button>
            ` : ''}
            ${(ppmp.status !== 'approved' || userRole === 'admin') ? `
            <button class="btn btn-sm btn-danger" onclick="deletePPMP(${ppmp.id}, '${ppmp.ppmp_number}', this)" title="Delete PPMP">
              <i class="fas fa-trash"></i>
            </button>
            ` : ''}
          </div>
        </td>
      </tr>
    `;
  });
}

function getStatusClass(status) {
  switch (status.toLowerCase()) {
    case 'draft':
      return 'status-draft';
    case 'saved':
      return 'status-saved';
    case 'submitted':
      return 'status-submitted';
    case 'approved':
      return 'status-approved';
    case 'rejected':
      return 'status-rejected';
    default:
      return 'status-draft';
  }
}

function viewPPMP(ppmpId) {
  // Open PPMP in view mode (same page)
  window.location.href = `ppmp.php?view=${ppmpId}`;
}

function editPPMP(ppmpId) {
  // First, fetch PPMP details to check permissions
  authenticatedFetch(`${API_BASE_URL}/api_load_ppmp.php?id=${ppmpId}`)
    .then(response => {
      if (!response.ok) {
        return response.json().then(err => {
          throw new Error(err.message || 'API request failed');
        });
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        const ppmp = data.ppmp;
        const header = ppmp.header;

        // Check if current user is the creator
        if (!currentUser || !currentUser.username) {
          showAlert('User session not found. Please log in again.', 'danger');
          return;
        }

        if (header.Created_By !== currentUser.username) {
          showAlert('You can only edit PPMP documents that you created.', 'warning');
          return;
        }

        // Check status and apply password requirement
        if (header.Status === 'submitted') {
          // For submitted PPMPs, require password
          const password = prompt('This PPMP has been submitted. Enter password to edit:');
          if (password !== 'pyopyo') {
            showAlert('Incorrect password. Edit cancelled.', 'danger');
            return;
          }
        }
        // For draft status, no password required

        // If all checks pass, redirect to edit page
        window.location.href = `ppmp.php?edit=${ppmpId}`;
      } else {
        showAlert('Error loading PPMP details: ' + data.message, 'danger');
      }
    })
    .catch(error => {
      console.error('Error checking PPMP permissions:', error);
      showAlert('Error checking permissions. Please try again.', 'danger');
    });
}

async function deletePPMP(ppmpId, ppmpNumber, button) {
  // Check if token is still valid
  const loggedIn = isLoggedIn();
  if (!loggedIn) {
    showAlert('Your session has expired. Please log in again.', 'danger');
    setTimeout(() => {
      window.location.href = 'login.php';
    }, 2000);
    return;
  }

  // First, fetch PPMP details to check ownership
  authenticatedFetch(`${API_BASE_URL}/api_load_ppmp.php?id=${ppmpId}`)
    .then(response => {
      if (!response.ok) {
        return response.json().then(err => {
          throw new Error(err.message || 'API request failed');
        });
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        const ppmp = data.ppmp;
        const header = ppmp.header;

        // Check if current user is the creator or admin
        if (!currentUser || !currentUser.username) {
          showAlert('User session not found. Please log in again.', 'danger');
          return;
        }

        const isCreator = header.Created_By === currentUser.username;
        const isAdmin = currentUser.role === 'admin';

        if (!isCreator && !isAdmin) {
          showAlert('You can delete only PPMP documents that you created.', 'warning');
          return;
        }

        // If user is creator or admin, proceed with confirmation
        if (confirm(`Are you sure you want to delete PPMP "${ppmpNumber}"?\n\nThis action cannot be undone.`)) {
          // Ask for password
          const password = prompt('Enter password to confirm deletion:');
          if (password !== 'pyopyo') {
            showAlert('Incorrect password. Deletion cancelled.', 'danger');
            return;
          }

          // Show loading
          const deleteBtn = button;
          const originalHTML = deleteBtn.innerHTML;
          deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
          deleteBtn.disabled = true;

          // Call delete API
          authenticatedFetch(`${API_BASE_URL}/api_delete_ppmp.php`, {
            method: 'POST',
            body: JSON.stringify({ ppmp_id: ppmpId })
          })
          .then(response => {
            if (!response.ok) {
              return response.json().then(err => {
                throw new Error(err.message || 'API request failed');
              });
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              showAlert('PPMP deleted successfully!', 'success');
              loadPPMPList(); // Refresh the list
            } else {
              showAlert('Error: ' + data.message, 'danger');
            }
          })
          .catch(error => {
            console.error('Delete error:', error);
            showAlert('Network error. Please try again.', 'danger');
          })
          .finally(() => {
            deleteBtn.innerHTML = originalHTML;
            deleteBtn.disabled = false;
          });
        }
      } else {
        showAlert('Error loading PPMP details: ' + data.message, 'danger');
      }
    })
    .catch(error => {
      console.error('Error checking PPMP ownership:', error);
      showAlert('Error checking permissions. Please try again.', 'danger');
    });
}

function approvePPMP(ppmpId, ppmpNumber, button) {
  if (confirm(`Are you sure you want to approve PPMP "${ppmpNumber}"?\n\nThis will make the PPMP items available for consolidation.`)) {
    // Show loading
    const approveBtn = button;
    const originalHTML = approveBtn.innerHTML;
    approveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    approveBtn.disabled = true;

    // Call approve API
    authenticatedFetch(`${API_BASE_URL}/api_approve_ppmp.php`, {
      method: 'POST',
      body: JSON.stringify({ ppmp_id: ppmpId, action: 'approve' })
    })
    .then(response => {
      if (!response.ok) {
        return response.json().then(err => {
          throw new Error(err.message || 'API request failed');
        });
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        showAlert('PPMP approved successfully!', 'success');
        loadPPMPList(); // Refresh the list
      } else {
        showAlert('Error: ' + data.message, 'danger');
      }
    })
    .catch(error => {
      console.error('Approval error:', error);
      showAlert('Network error. Please try again.', 'danger');
    })
    .finally(() => {
      approveBtn.innerHTML = originalHTML;
      approveBtn.disabled = false;
    });
  }
}

function rejectPPMP(ppmpId, ppmpNumber, button) {
  const rejectionReason = prompt(`Please provide a reason for rejecting PPMP "${ppmpNumber}":`);
  if (rejectionReason === null) return; // User cancelled

  if (rejectionReason.trim() === '') {
    showAlert('Rejection reason is required.', 'warning');
    return;
  }

  // Show loading
  const rejectBtn = button;
  const originalHTML = rejectBtn.innerHTML;
  rejectBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  rejectBtn.disabled = true;

  // Call reject API
  authenticatedFetch(`${API_BASE_URL}/api_approve_ppmp.php`, {
    method: 'POST',
    body: JSON.stringify({
      ppmp_id: ppmpId,
      action: 'reject',
      rejection_reason: rejectionReason.trim()
    })
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(err => {
        throw new Error(err.message || 'API request failed');
      });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showAlert('PPMP rejected successfully!', 'success');
      loadPPMPList(); // Refresh the list
    } else {
      showAlert('Error: ' + data.message, 'danger');
    }
  })
  .catch(error => {
    console.error('Rejection error:', error);
    showAlert('Network error. Please try again.', 'danger');
  })
  .finally(() => {
    rejectBtn.innerHTML = originalHTML;
    rejectBtn.disabled = false;
  });
}

function resubmitPPMP(ppmpId, ppmpNumber, button) {
  if (confirm(`Are you sure you want to resubmit PPMP "${ppmpNumber}"?\n\nThis will change the status back to 'submitted' for re-approval.`)) {
    // Show loading
    const resubmitBtn = button;
    const originalHTML = resubmitBtn.innerHTML;
    resubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    resubmitBtn.disabled = true;

    // Call resubmit API
    authenticatedFetch(`${API_BASE_URL}/api_approve_ppmp.php`, {
      method: 'POST',
      body: JSON.stringify({
        ppmp_id: ppmpId,
        action: 'resubmit'
      })
    })
    .then(response => {
      if (!response.ok) {
        return response.json().then(err => {
          throw new Error(err.message || 'API request failed');
        });
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        showAlert('PPMP resubmitted successfully!', 'success');
        loadPPMPList(); // Refresh the list
      } else {
        showAlert('Error: ' + data.message, 'danger');
      }
    })
    .catch(error => {
      console.error('Resubmit error:', error);
      showAlert('Network error. Please try again.', 'danger');
    })
    .finally(() => {
      resubmitBtn.innerHTML = originalHTML;
      resubmitBtn.disabled = false;
    });
  }
}

function showError(message) {
  const tableBody = document.getElementById("ppmpTableBody");
  tableBody.innerHTML = `
    <tr>
      <td colspan="10" class="text-center py-4">
        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
        <h5 class="text-danger">Error</h5>
        <p class="text-muted">${message}</p>
        <button class="btn btn-primary" onclick="loadPPMPList()">
          <i class="fas fa-sync"></i> Try Again
        </button>
      </td>
    </tr>
  `;
}

function showAlert(message, type = 'info') {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
  alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
  alertDiv.innerHTML = `
    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  document.body.appendChild(alertDiv);

  setTimeout(() => {
    alertDiv.remove();
  }, 5000);
}

// Auto-refresh every 30 seconds
setInterval(() => {
  if (document.hasFocus()) {
    loadPPMPList();
  }
}, 30000);