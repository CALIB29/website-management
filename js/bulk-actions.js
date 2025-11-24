document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const checkboxes = document.querySelectorAll('.website-checkbox-input');
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const bulkActions = document.querySelector('.bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    const bulkActionSelect = document.getElementById('bulk-action');
    const applyBulkAction = document.getElementById('apply-bulk-action');
    const cancelBulkActions = document.getElementById('cancel-bulk-actions');
    const websitesForm = document.getElementById('websites-form');

    // Toggle bulk actions bar
    function toggleBulkActions() {
        const checkedBoxes = document.querySelectorAll('.website-checkbox-input:checked');
        if (checkedBoxes.length > 0) {
            bulkActions.style.display = 'flex';
            selectedCount.textContent = `${checkedBoxes.length} selected`;
        } else {
            bulkActions.style.display = 'none';
        }
    }

    // Update select all checkbox
    function updateSelectAll() {
        const allChecked = checkboxes.length === document.querySelectorAll('.website-checkbox-input:checked').length;
        selectAllCheckbox.checked = allChecked && checkboxes.length > 0;
    }

    // Toggle all checkboxes
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            toggleBulkActions();
        });
    }

    // Toggle individual checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            toggleBulkActions();
            updateSelectAll();
        });
    });

    // Apply bulk action
    if (applyBulkAction) {
        applyBulkAction.addEventListener('click', function() {
            const action = bulkActionSelect.value;
            if (!action) {
                alert('Please select an action');
                return;
            }

            // Confirm before delete
            if (action === 'delete') {
                if (!confirm('Are you sure you want to delete the selected websites? This action cannot be undone.')) {
                    return;
                }
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'bulk_actions.php';

            // Add CSRF token if available
            const token = document.querySelector('input[name="csrf_token"]');
            if (token) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = token.value;
                form.appendChild(csrfInput);
            }

            // Add action
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);

            // Add selected website IDs
            const checkedBoxes = document.querySelectorAll('.website-checkbox-input:checked');
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'website_ids[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });
    }

    // Cancel bulk actions
    if (cancelBulkActions) {
        cancelBulkActions.addEventListener('click', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            bulkActions.style.display = 'none';
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
        });
    }

    // Handle click on card (but not on links or buttons)
    document.querySelectorAll('.website-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on links, buttons, or checkboxes
            if (e.target.tagName === 'A' || 
                e.target.tagName === 'BUTTON' || 
                e.target.closest('a') || 
                e.target.closest('button') ||
                e.target.classList.contains('website-checkbox') ||
                e.target.closest('.website-checkbox')) {
                return;
            }
            
            // Toggle checkbox
            const checkbox = this.querySelector('.website-checkbox-input');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                const event = new Event('change');
                checkbox.dispatchEvent(event);
            }
        });
    });
});
