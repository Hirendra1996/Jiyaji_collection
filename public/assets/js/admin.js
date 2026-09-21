/**
 * JIYAJI LX — Advanced Admin Portal Scripts
 * - Modern Toast Notification System
 * - Interactive Chart & Time Range Filtering
 * - Real-time Table Search & Tab Filtering
 * - Notification Center & Dropdowns
 */

// Global Toast Engine
window.showToast = function(type = 'info', title = '', message = '', duration = 4500) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    // Icon based on type
    let iconSvg = '';
    switch (type) {
        case 'success':
            iconSvg = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`;
            break;
        case 'error':
            iconSvg = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;
            break;
        case 'warning':
            iconSvg = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
            break;
        default:
            iconSvg = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`;
    }

    toast.innerHTML = `
        <div class="toast-icon">${iconSvg}</div>
        <div class="toast-body">
            ${title ? `<div class="toast-title">${escapeHtml(title)}</div>` : ''}
            <div class="toast-message">${escapeHtml(message)}</div>
        </div>
        <button type="button" class="toast-close" aria-label="Close notification">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <div class="toast-progress" style="animation-duration: ${duration}ms;"></div>
    `;

    container.appendChild(toast);

    function removeToast() {
        toast.classList.add('toast-hiding');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    const timer = setTimeout(removeToast, duration);

    const closeBtn = toast.querySelector('.toast-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            clearTimeout(timer);
            removeToast();
        });
    }
};

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
    // 1. Flush Server-Side Toasts
    if (window.serverToasts && Array.isArray(window.serverToasts)) {
        window.serverToasts.forEach((t, index) => {
            setTimeout(() => {
                window.showToast(t.type || 'info', t.title || '', t.message || '', 5000);
            }, index * 250);
        });
    }

    // 2. Password Visibility Toggle
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

            togglePasswordBtn.innerHTML = isPassword
                ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`
                : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        });
    }

    // 3. Demo Credentials Auto-Fill
    const demoFillBtn = document.getElementById('demoFillBtn');
    const emailInput = document.getElementById('email');

    if (demoFillBtn && emailInput && passwordInput) {
        demoFillBtn.addEventListener('click', () => {
            emailInput.value = 'admin@jiyaji.com';
            passwordInput.value = 'Admin@123';
            window.showToast('info', 'Credentials Loaded', 'Default admin credentials populated into fields.', 3000);
        });
    }

    // 4. Mobile Sidebar Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const adminSidebar = document.getElementById('adminSidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    function toggleSidebar() {
        if (adminSidebar && sidebarBackdrop) {
            adminSidebar.classList.toggle('open');
            sidebarBackdrop.classList.toggle('open');
        }
    }

    function closeSidebar() {
        if (adminSidebar && sidebarBackdrop) {
            adminSidebar.classList.remove('open');
            sidebarBackdrop.classList.remove('open');
        }
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleSidebar);
    }
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }

    // Auto-close sidebar on mobile when navigating
    if (adminSidebar) {
        const sidebarLinks = adminSidebar.querySelectorAll('a.nav-item');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 1024) {
                    closeSidebar();
                }
            });
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidebar();
            if (profileMenu) profileMenu.classList.remove('show');
            if (notifMenu) notifMenu.classList.remove('show');
        }
    });

    // 5. Profile Dropdown Toggle
    const profilePill = document.getElementById('profilePill');
    const profileMenu = document.getElementById('profileMenu');

    if (profilePill && profileMenu) {
        profilePill.addEventListener('click', (e) => {
            e.stopPropagation();
            if (notifMenu) notifMenu.classList.remove('show');
            profileMenu.classList.toggle('show');
        });
    }

    // 6. Notification Center Bell Toggle
    const notifBtn = document.getElementById('notifBtn');
    const notifMenu = document.getElementById('notifMenu');
    const notifBadge = document.getElementById('notifBadge');
    const clearNotifBtn = document.getElementById('clearNotifBtn');

    if (notifBtn && notifMenu) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (profileMenu) profileMenu.classList.remove('show');
            notifMenu.classList.toggle('show');
        });

        if (clearNotifBtn) {
            clearNotifBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (notifBadge) notifBadge.style.display = 'none';
                const notifItems = notifMenu.querySelectorAll('.notif-item');
                notifItems.forEach(item => item.style.opacity = '0.5');
                window.showToast('success', 'Notifications Cleared', 'All pending notifications marked as read.');
            });
        }
    }

    // Close dropdowns on outside click
    document.addEventListener('click', (e) => {
        if (profileMenu && !profileMenu.contains(e.target) && !profilePill.contains(e.target)) {
            profileMenu.classList.remove('show');
        }
        if (notifMenu && !notifMenu.contains(e.target) && !notifBtn.contains(e.target)) {
            notifMenu.classList.remove('show');
        }
    });

    // 7. Time Range Switcher
    const timeFilterBtns = document.querySelectorAll('.time-filter-btn');
    timeFilterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            timeFilterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const range = btn.getAttribute('data-range') || '30D';
            window.showToast('info', 'Filter Applied', `Displaying analytics for ${range}.`, 2800);
        });
    });

    // 8. Quick Actions Bar Feedback
    const quickActionBtns = document.querySelectorAll('.quick-action-btn');
    quickActionBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.getAttribute('data-action') || 'Action';
            window.showToast('info', action, `${action} will be available when product & order modules are initialized.`, 3500);
        });
    });

    // 9. Orders Table Status Tabs Filtering
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tableRows = document.querySelectorAll('#ordersTableBody tr');

    tabBtns.forEach(tab => {
        tab.addEventListener('click', () => {
            tabBtns.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const statusFilter = tab.getAttribute('data-status') || 'all';

            tableRows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                if (statusFilter === 'all' || rowStatus === statusFilter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // 10. Table Live Search
    const tableSearchInput = document.getElementById('tableSearchInput');
    if (tableSearchInput) {
        tableSearchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // 11. Export Report Button
    const btnExport = document.getElementById('btnExport');
    if (btnExport) {
        btnExport.addEventListener('click', () => {
            window.showToast('success', 'Export Started', 'Preparing comprehensive PDF/Excel sales report for download.', 3500);
        });
    }
});
