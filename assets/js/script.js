/**
 * Recipe Management System - JavaScript Functions
 * Handles interactive features for the recipe management system
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // 1. FORM VALIDATION
    // ============================================
    
    // Add validation for recipe forms
    const recipeForms = document.querySelectorAll('form#recipeForm');
    
    recipeForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const title = this.querySelector('#recipe_title');
            if (title && title.value.trim() === '') {
                e.preventDefault();
                showAlert('Recipe title is required!', 'error');
                title.focus();
                return false;
            }
            
            // Validate prep time and cooking time are not negative
            const prepTime = this.querySelector('#prep_time');
            const cookTime = this.querySelector('#cooking_time');
            
            if (prepTime && parseInt(prepTime.value) < 0) {
                e.preventDefault();
                showAlert('Prep time cannot be negative!', 'error');
                prepTime.focus();
                return false;
            }
            
            if (cookTime && parseInt(cookTime.value) < 0) {
                e.preventDefault();
                showAlert('Cooking time cannot be negative!', 'error');
                cookTime.focus();
                return false;
            }
            
            // Validate servings
            const servings = this.querySelector('#servings');
            if (servings && parseInt(servings.value) < 1) {
                e.preventDefault();
                showAlert('Servings must be at least 1!', 'error');
                servings.focus();
                return false;
            }
            
            return true;
        });
    });
    
    // ============================================
    // 2. INGREDIENT MANAGEMENT (Dynamic Rows)
    // ============================================
    
    // Make addIngredient and removeIngredient globally accessible
    window.addIngredient = function() {
        const container = document.getElementById('ingredients-container');
        if (!container) return;
        
        const row = document.createElement('div');
        row.className = 'ingredient-row';
        
        // Get the current select options from the first row
        const firstSelect = container.querySelector('.ingredient-select');
        let selectHTML = '<option value="">Select ingredient</option>';
        
        if (firstSelect) {
            // Copy options from existing select
            const options = firstSelect.querySelectorAll('option');
            options.forEach(opt => {
                selectHTML += `<option value="${opt.value}">${opt.textContent}</option>`;
            });
        } else {
            // Default options if no select exists
            selectHTML = `
                <option value="">Select ingredient</option>
                <option value="1">Flour</option>
                <option value="2">Sugar</option>
                <option value="3">Eggs</option>
                <option value="4">Butter</option>
                <option value="5">Milk</option>
            `;
        }
        
        row.innerHTML = `
            <div class="form-group">
                <label>Ingredient</label>
                <select name="ingredient_id[]" class="ingredient-select">
                    ${selectHTML}
                </select>
            </div>
            <div class="form-group">
                <label>Quantity</label>
                <input type="text" name="ingredient_quantity[]" placeholder="e.g., 2 cups, 500g" class="ingredient-quantity">
            </div>
            <button type="button" onclick="removeIngredient(this)" class="btn-small btn-small-danger" style="margin-top: 6px;">
                <i class="fas fa-trash"></i> Remove
            </button>
        `;
        
        container.appendChild(row);
        
        // Add animation effect
        row.style.opacity = '0';
        row.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, 10);
        
        // Focus on the new quantity input
        const newInput = row.querySelector('.ingredient-quantity');
        if (newInput) {
            setTimeout(() => newInput.focus(), 200);
        }
    };
    
    window.removeIngredient = function(button) {
        const container = document.getElementById('ingredients-container');
        if (!container) return;
        
        // Don't remove if only one row left
        if (container.children.length <= 1) {
            showAlert('You need at least one ingredient row.', 'warning');
            return;
        }
        
        // Add fade out animation before removing
        const row = button.parentElement;
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(20px)';
        
        setTimeout(() => {
            row.remove();
        }, 300);
    };
    
    // ============================================
    // 3. SEARCH FUNCTIONALITY
    // ============================================
    
    // Live search with debounce
    const searchInput = document.querySelector('.search-bar input[type="text"]');
    const searchForm = document.querySelector('.search-bar');
    
    if (searchInput && searchForm) {
        let debounceTimer;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const keyword = this.value.trim();
            
            // Only search if keyword length >= 3 or empty
            if (keyword.length >= 3 || keyword.length === 0) {
                debounceTimer = setTimeout(() => {
                    if (keyword.length === 0) {
                        // Redirect to home if search is cleared
                        if (window.location.pathname.includes('search.php')) {
                            window.location.href = 'index.php';
                        }
                    } else {
                        searchForm.submit();
                    }
                }, 500);
            }
        });
        
        // Add keyboard shortcut (Ctrl+K to focus search)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });
    }
    
    // ============================================
    // 4. TABLE SORTING
    // ============================================
    
    const tables = document.querySelectorAll('.recipe-table');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('th');
        let currentSort = { column: null, ascending: true };
        
        headers.forEach((header, index) => {
            // Skip actions column (usually last)
            if (header.textContent.trim().toLowerCase() === 'actions') return;
            
            header.style.cursor = 'pointer';
            header.title = 'Click to sort';
            
            header.addEventListener('click', function() {
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                // Determine sort direction
                if (currentSort.column === index) {
                    currentSort.ascending = !currentSort.ascending;
                } else {
                    currentSort.column = index;
                    currentSort.ascending = true;
                }
                
                // Clear previous sort indicators
                headers.forEach(h => {
                    h.textContent = h.textContent.replace(/ [▲▼]/, '');
                });
                
                // Add sort indicator
                const sortIcon = currentSort.ascending ? ' ▲' : ' ▼';
                this.textContent += sortIcon;
                
                // Sort rows
                rows.sort((a, b) => {
                    const aValue = a.children[index]?.textContent.trim() || '';
                    const bValue = b.children[index]?.textContent.trim() || '';
                    
                    // Try numeric comparison
                    const aNum = parseFloat(aValue);
                    const bNum = parseFloat(bValue);
                    
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return currentSort.ascending ? aNum - bNum : bNum - aNum;
                    }
                    
                    // String comparison
                    return currentSort.ascending 
                        ? aValue.localeCompare(bValue) 
                        : bValue.localeCompare(aValue);
                });
                
                // Re-append sorted rows
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
    
    // ============================================
    // 5. MODAL CONFIRMATION DIALOGS
    // ============================================

    // Replace browser confirm() popups with a reusable modal.
    function ensureModalStyles() {
        // CSS is loaded from style.css; this function only creates the modal markup.
    }

    function createModal() {
        let modal = document.getElementById('appModal');
        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = 'appModal';
        modal.className = 'modal-overlay';
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = `
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
                <div class="modal-header">
                    <div class="modal-icon" id="modalIcon">!</div>
                    <button type="button" class="modal-close" id="modalClose" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <h3 id="modalTitle">Confirmation</h3>
                    <p id="modalMessage"></p>
                </div>
                <div class="modal-footer" id="modalFooter">
                    <button type="button" class="btn btn-secondary" id="modalCancel">Cancel</button>
                    <button type="button" class="btn btn-primary" id="modalConfirm">Confirm</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        return modal;
    }

    window.showModal = function(options = {}) {
        const modal = createModal();
        const title = options.title || 'Confirmation';
        const message = options.message || '';
        const confirmText = options.confirmText || 'Confirm';
        const cancelText = options.cancelText || 'Cancel';
        const type = options.type || 'info';
        const onConfirm = typeof options.onConfirm === 'function' ? options.onConfirm : function(){};

        modal.querySelector('#modalTitle').textContent = title;
        modal.querySelector('#modalMessage').textContent = message;
        modal.querySelector('#modalConfirm').textContent = confirmText;
        modal.querySelector('#modalCancel').textContent = cancelText;
        modal.querySelector('#modalIcon').textContent = type === 'danger' ? '!' : 'i';
        modal.querySelector('#modalIcon').className = 'modal-icon modal-icon-' + type;
        modal.querySelector('#modalConfirm').className = 'btn ' + (type === 'danger' ? 'btn-danger' : 'btn-primary');

        const close = function() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        };

        // Clone buttons to remove old handlers cleanly.
        const confirmBtn = modal.querySelector('#modalConfirm');
        const cancelBtn = modal.querySelector('#modalCancel');
        const closeBtn = modal.querySelector('#modalClose');

        confirmBtn.onclick = function() {
            close();
            onConfirm();
        };
        cancelBtn.onclick = close;
        closeBtn.onclick = close;

        modal.onclick = function(e) {
            if (e.target === modal) close();
        };

        modal._close = close;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');

        setTimeout(() => confirmBtn.focus(), 50);
    };

    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('appModal');
        if (modal && modal.classList.contains('is-open') && e.key === 'Escape') {
            if (typeof modal._close === 'function') modal._close();
        }
    });

    const deleteLinks = document.querySelectorAll('a[href*="delete.php"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const url = this.href;
            const title = this.closest('.recipe-card')?.querySelector('.recipe-card-title')?.textContent ||
                          this.closest('tr')?.querySelector('td:nth-child(2)')?.textContent ||
                          'this recipe';

            window.showModal({
                title: 'Delete Recipe',
                message: `Are you sure you want to delete "${title.trim()}"? This action cannot be undone.`,
                confirmText: 'Yes, Delete',
                cancelText: 'Cancel',
                type: 'danger',
                onConfirm: function() {
                    window.location.href = url;
                }
            });
        });
    });

    // ============================================
    // 6. MODAL ALERT SYSTEM
    // ============================================

    function showAlert(message, type = 'info') {
        const titles = {
            success: 'Success',
            error: 'Error',
            warning: 'Warning',
            info: 'Information'
        };

        window.showModal({
            title: titles[type] || titles.info,
            message: message,
            confirmText: 'OK',
            cancelText: '',
            type: type,
            onConfirm: function() {}
        });

        const modal = document.getElementById('appModal');
        if (modal) {
            const cancelBtn = modal.querySelector('#modalCancel');
            if (cancelBtn) cancelBtn.style.display = 'none';
        }
    }

    window.showAlert = showAlert;

    // ============================================
    // 7. RECIPE CARD HOVER EFFECTS
    // ============================================
    
    const recipeCards = document.querySelectorAll('.recipe-card');
    
    recipeCards.forEach(card => {
        // Add click effect for recipe details (expand)
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on a button or link
            if (e.target.closest('a') || e.target.closest('button')) return;
            
            this.classList.toggle('expanded');
        });
    });
    
    // ============================================
    // 8. IMAGE PREVIEW (for URL input)
    // ============================================
    
    const imageUrlInput = document.querySelector('#image_url');
    if (imageUrlInput) {
        const previewContainer = document.querySelector('.image-preview');
        const previewImg = document.querySelector('#previewImage');
        
        if (previewContainer && previewImg) {
            imageUrlInput.addEventListener('input', function() {
                const url = this.value.trim();
                if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
                    previewImg.src = url;
                    previewContainer.style.display = 'block';
                    previewImg.onerror = function() {
                        previewContainer.style.display = 'none';
                    };
                } else {
                    previewContainer.style.display = 'none';
                }
            });
        }
    }
    
    // ============================================
    // 9. KEYBOARD SHORTCUTS
    // ============================================
    
    document.addEventListener('keydown', function(e) {
        // Ctrl+N or Cmd+N to add new recipe
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            const addLink = document.querySelector('a[href*="add.php"]');
            if (addLink && !e.target.closest('input, textarea, select')) {
                e.preventDefault();
                window.location.href = addLink.href;
            }
        }
        
        // Escape key to go back
        if (e.key === 'Escape') {
            const backLink = document.querySelector('a[href*="view.php"], a[href*="index.php"]');
            if (backLink && !e.target.closest('input, textarea, select')) {
                e.preventDefault();
                window.location.href = backLink.href;
            }
        }
    });
    
    // ============================================
    // 10. RESPONSIVE TABLE HANDLING
    // ============================================
    
    function handleResponsiveTables() {
        const tables = document.querySelectorAll('.table-wrapper');
        
        tables.forEach(wrapper => {
            // For small screens, make table scrollable
            if (window.innerWidth < 768) {
                wrapper.style.overflowX = 'auto';
                wrapper.style.webkitOverflowScrolling = 'touch';
            } else {
                wrapper.style.overflowX = '';
            }
        });
    }
    
    // Run on load and resize
    window.addEventListener('resize', handleResponsiveTables);
    handleResponsiveTables();
    
    // ============================================
    // 11. STATS ANIMATION
    // ============================================
    
    function animateStats() {
        const statNumbers = document.querySelectorAll('.stat-number');
        statNumbers.forEach(stat => {
            const text = stat.textContent;
            const number = parseInt(text);
            if (!isNaN(number)) {
                let current = 0;
                const increment = Math.ceil(number / 30);
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= number) {
                        stat.textContent = text;
                        clearInterval(timer);
                    } else {
                        stat.textContent = current + (text.includes('min') ? ' min' : '');
                    }
                }, 50);
            }
        });
    }
    
    // Run stats animation if stats exist
    if (document.querySelector('.stat-number')) {
        setTimeout(animateStats, 300);
    }
});

// ============================================
// 12. Additional Utility Functions
// ============================================

// Print current view
function printPage() {
    window.print();
}

// Export table to CSV
function exportToCSV(tableId, filename = 'recipes.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const rowData = [];
        const cells = row.querySelectorAll('td, th');
        cells.forEach(cell => {
            // Clean cell text and remove action buttons
            let text = cell.textContent.trim();
            if (cell.querySelector('.btn-small')) {
                text = ''; // Skip action buttons
            }
            // Remove sort indicators
            text = text.replace(/[▲▼]/g, '').trim();
            rowData.push(text);
        });
        csv.push(rowData.join(','));
    });
    
    // Create download link
    const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Auto-save form data (for forms)
function autoSaveForm(formId, storageKey) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    
    // Save on input change
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                if (data[key]) {
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value;
                }
            });
            localStorage.setItem(storageKey, JSON.stringify(data));
        });
    });
    
    // Load saved data
    const savedData = localStorage.getItem(storageKey);
    if (savedData) {
        const data = JSON.parse(savedData);
        Object.keys(data).forEach(key => {
            const input = form.querySelector(`[name="${key}"]`);
            if (input) {
                if (Array.isArray(data[key])) {
                    // Handle multiple values (like ingredients)
                    // This is a simplified version
                } else {
                    input.value = data[key];
                }
            }
        });
    }
}