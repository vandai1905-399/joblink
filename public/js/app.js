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

        // Xử lý Dropdown người dùng (Chỉ mở khi Click)
        document.querySelectorAll('.user-dropdown-container').forEach(container => {
            const trigger = container.querySelector('.user-dropdown-trigger');
            if (!trigger) return;

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = container.classList.contains('is-open');
                // Đóng các dropdown khác (nếu có)
                document.querySelectorAll('.user-dropdown-container.is-open').forEach(c => c.classList.remove('is-open'));
                if (!isOpen) {
                    container.classList.add('is-open');
                }
            });
        });

        // Đóng dropdown khi click ra ngoài
        document.addEventListener('click', (e) => {
            document.querySelectorAll('.user-dropdown-container.is-open').forEach(container => {
                if (!container.contains(e.target)) {
                    container.classList.remove('is-open');
                }
            });
        });

        // Đóng dropdown khi nhấn phím ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.user-dropdown-container.is-open').forEach(container => {
                    container.classList.remove('is-open');
                });
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    AppCommon.init();
});
