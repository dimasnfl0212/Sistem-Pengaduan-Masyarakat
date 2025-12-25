// File: C:\xampp\htdocs\lapor-system\public\assets\js\custom.js

// Custom JavaScript for Lapor! System

// Form validation feedback
document.addEventListener('DOMContentLoaded', function() {
    // Add Bootstrap validation styles to all forms
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Image preview for file inputs
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewId = e.target.dataset.preview || 'preview-' + e.target.id;
            const previewElement = document.getElementById(previewId);
            
            if (previewElement && file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewElement.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail mt-2" style="max-height: 200px;">';
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Character counter for textareas
    const textareas = document.querySelectorAll('textarea[data-maxlength]');
    textareas.forEach(function(textarea) {
        const maxLength = textarea.dataset.maxlength;
        const counterId = 'counter-' + textarea.id;
        let counterElement = document.getElementById(counterId);
        
        if (!counterElement) {
            counterElement = document.createElement('small');
            counterElement.id = counterId;
            counterElement.className = 'form-text text-muted d-block';
            textarea.parentNode.appendChild(counterElement);
        }
        
        function updateCounter() {
            const length = textarea.value.length;
            counterElement.textContent = length + ' / ' + maxLength + ' karakter';
            
            if (length > maxLength * 0.9) {
                counterElement.style.color = '#dc3545';
            } else if (length > maxLength * 0.7) {
                counterElement.style.color = '#ffc107';
            } else {
                counterElement.style.color = '#6c757d';
            }
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter(); // Initial update
    });
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus? Tindakan ini tidak dapat dibatalkan.')) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-submit forms on select change (for filters)
    const autoSubmitSelects = document.querySelectorAll('select[data-auto-submit]');
    autoSubmitSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
    
    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Popover initialization
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    const popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// AJAX helper functions
const LaporAjax = {
    // Mark notification as read
    markNotificationAsRead: function(notificationId, callback) {
        fetch('mark-read.php?id=' + notificationId)
            .then(response => response.json())
            .then(data => {
                if (callback) callback(data);
            })
            .catch(error => console.error('Error:', error));
    },
    
    // Update report status
    updateReportStatus: function(reportId, status, callback) {
        const formData = new FormData();
        formData.append('id', reportId);
        formData.append('status', status);
        
        fetch('admin/update-status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (callback) callback(data);
        })
        .catch(error => console.error('Error:', error));
    },
    
    // Search reports
    searchReports: function(query, callback) {
        fetch('api/search.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (callback) callback(data);
            })
            .catch(error => console.error('Error:', error));
    },
    
    // Show toast notification
    showToast: function(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        document.getElementById('toast-container').insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();
        
        // Remove element after hide
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }
};

// Dark mode toggle (using cookies)
const DarkMode = {
    init: function() {
        const toggleButton = document.getElementById('darkModeToggle');
        if (toggleButton) {
            toggleButton.addEventListener('click', this.toggle);
            this.updateIcon();
        }
    },
    
    toggle: function() {
        const isDark = document.body.classList.toggle('dark-mode');
        document.cookie = 'dark_mode=' + (isDark ? '1' : '0') + '; path=/; max-age=31536000';
        DarkMode.updateIcon();
    },
    
    updateIcon: function() {
        const icon = document.getElementById('darkModeIcon');
        if (icon) {
            if (document.body.classList.contains('dark-mode')) {
                icon.classList.remove('bi-moon');
                icon.classList.add('bi-sun');
            } else {
                icon.classList.remove('bi-sun');
                icon.classList.add('bi-moon');
            }
        }
    },
    
    load: function() {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'dark_mode' && value === '1') {
                document.body.classList.add('dark-mode');
                break;
            }
        }
    }
};

// Initialize dark mode on page load
document.addEventListener('DOMContentLoaded', function() {
    DarkMode.load();
    DarkMode.init();
});

// Export functions to global scope
window.LaporAjax = LaporAjax;
window.DarkMode = DarkMode;