/**
 * public/js/app.js - Logic tổng quát và tiện ích phía Client (Pure JavaScript)
 * Lưu ý: Các module chuyên biệt (Auth, Jobs, Applications, Meetings, Reviews, Messages)
 * được xử lý độc lập tại public/js/<module>/<module>.js
 */

// ============================================================================
// 1. TOAST NOTIFICATION ALIAS (Kế thừa từ dialog.js)
// ============================================================================
const Toast = window.Toast || {
    show(message, type = 'success', duration = 3500) {
        if (window.Toast && window.Toast.show) {
            return window.Toast.show(message, type, duration);
        }
    }
};

// ============================================================================
// 2. TIỆN ÍCH DÙNG CHUNG TOÀN ỨNG DỤNG
// ============================================================================
const AppCommon = {
    init() {
        // Tự ẩn các alert tĩnh cũ (nếu có)
        document.querySelectorAll('.alert').forEach(a => {
            setTimeout(() => {
                a.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                a.style.opacity = '0';
                a.style.transform = 'translateY(-10px)';
                setTimeout(() => a.remove(), 400);
            }, 4000);
        });

        // Xử lý Dropdown người dùng & Dropdown icon Thông báo / Tin nhắn
        const allDropdownContainers = document.querySelectorAll('.user-dropdown-container, .header-icon-container');

        allDropdownContainers.forEach(container => {
            const trigger = container.querySelector('.user-dropdown-trigger, .header-icon-btn');
            if (!trigger) return;

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = container.classList.contains('is-open');
                // Đóng tất cả các dropdown khác đang mở
                allDropdownContainers.forEach(c => {
                    if (c !== container) c.classList.remove('is-open');
                });
                if (!isOpen) {
                    container.classList.add('is-open');
                } else {
                    container.classList.remove('is-open');
                }
            });
        });

        // Đóng dropdown khi click ra ngoài
        document.addEventListener('click', (e) => {
            allDropdownContainers.forEach(container => {
                if (!container.contains(e.target)) {
                    container.classList.remove('is-open');
                }
            });
        });

        // Đóng dropdown khi nhấn phím ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                allDropdownContainers.forEach(container => {
                    container.classList.remove('is-open');
                });
            }
        });

        // Xử lý nút Lưu việc làm toàn cục (.btn-save-job)
        document.addEventListener('click', async (e) => {
            const saveBtn = e.target.closest('.btn-save-job');
            if (!saveBtn) return;
            e.preventDefault();
            e.stopPropagation();

            const jobId = saveBtn.dataset.jobId || saveBtn.getAttribute('data-job-id');
            if (!jobId) return;

            saveBtn.disabled = true;
            const baseUrl = (window.APP_URL || '').replace(/\/+$/, '');
            const saveUrl = baseUrl + '/seeker/toggle-save/' + jobId;

            try {
                const res = await fetch(saveUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (res.status === 401) {
                    window.location.href = baseUrl + '/auth/login';
                    return;
                }

                const data = await res.json();
                saveBtn.disabled = false;

                if (data.success === false) {
                    if (window.Toast && window.Toast.show) {
                        Toast.show(data.message || 'Không thể lưu việc làm!', 'warning');
                    } else {
                        alert(data.message);
                    }
                    return;
                }

                if (data.saved) {
                    saveBtn.classList.add('btn-primary');
                    saveBtn.classList.remove('btn-outline');
                    saveBtn.title = 'Bỏ lưu việc làm';
                    const spanText = saveBtn.querySelector('span');
                    if (spanText) spanText.textContent = 'Đã Lưu';
                    if (window.Toast && window.Toast.show) {
                        Toast.show(data.message || 'Đã lưu việc làm vào danh sách quan tâm!', 'success');
                    }
                } else {
                    saveBtn.classList.remove('btn-primary');
                    saveBtn.classList.add('btn-outline');
                    saveBtn.title = 'Lưu việc làm';
                    const spanText = saveBtn.querySelector('span');
                    if (spanText) spanText.textContent = 'Lưu Việc Làm';
                    if (window.Toast && window.Toast.show) {
                        Toast.show(data.message || 'Đã bỏ lưu việc làm!', 'info');
                    }
                }
            } catch (err) {
                saveBtn.disabled = false;
                console.error('Error saving job:', err);
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    AppCommon.init();
});
