/**
 * public/js/unsaved_changes_bar.js
 * Thanh thông báo nhắc nhở thay đổi chưa lưu nổi ở đáy màn hình chuẩn phong cách Discord
 * "Hãy cẩn thận – bạn chưa lưu các thay đổi! [Đặt lại] [Lưu Thay Đổi]"
 */

(function() {
    'use strict';

    class DiscordUnsavedNotice {
        constructor(form) {
            this.form = form;
            this.initialData = new Map();
            this.initialContainersHtml = new Map();
            this.initialCardCounts = new Map();
            this.initialAvatarSrc = null;
            this.barEl = null;
            this.isDirty = false;
            this.isSubmitting = false;
            this.isResetting = false;

            this.init();
        }

        init() {
            // Lưu ảnh avatar ban đầu nếu có preview
            const avatarImg = this.form.querySelector('#employer_avatar_img, #seeker_avatar_img, #admin_avatar_preview, #inter_avatar_img');
            if (avatarImg) {
                this.initialAvatarSrc = avatarImg.src;
            }

            // Lưu trạng thái ban đầu của các container động (cơ sở, ca làm, kỹ năng...)
            const dynamicContainers = this.form.querySelectorAll('#branchesContainer, #shiftsContainer, #skillProofsContainer, #workHistoryContainer');
            dynamicContainers.forEach(c => {
                this.initialContainersHtml.set(c, c.innerHTML);
                this.initialCardCounts.set(c, c.children.length);
            });

            // Chụp ảnh snapshot toàn bộ input ban đầu
            this.captureSnapshot();

            // Tạo thanh thông báo Discord nổi
            this.createBar();

            // Lắng nghe sự kiện thay đổi dữ liệu
            this.bindEvents();
        }

        captureSnapshot() {
            this.initialData.clear();
            const elements = this.getTrackableElements();
            elements.forEach(el => {
                this.initialData.set(el, this.getElementValue(el));
            });
        }

        getTrackableElements() {
            return Array.from(this.form.querySelectorAll('input, select, textarea')).filter(el => {
                if (el.type === 'hidden') {
                    // Bỏ qua các hidden field bảo mật hệ thống
                    if (['embedded', 'csrf_token', '_token'].includes(el.name)) return false;
                }
                return true;
            });
        }

        getElementValue(el) {
            if (el.type === 'checkbox' || el.type === 'radio') {
                return el.checked;
            }
            if (el.type === 'file') {
                return (el.files && el.files.length) ? el.files[0].name + '_' + el.files[0].size : '';
            }
            return el.value;
        }

        createBar() {
            // Kiểm tra xem đã có bar chưa
            let bar = document.getElementById('discord-unsaved-notice-bar');
            if (!bar) {
                bar = document.createElement('div');
                bar.id = 'discord-unsaved-notice-bar';
                bar.className = 'discord-unsaved-bar';
                bar.setAttribute('role', 'alert');
                bar.setAttribute('aria-live', 'polite');
                bar.innerHTML = `
                    <div class="discord-unsaved-bar-text">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <span>Hãy cẩn thận – bạn chưa lưu các thay đổi!</span>
                    </div>
                    <div class="discord-unsaved-bar-actions">
                        <button type="button" class="discord-unsaved-btn-reset">Đặt lại</button>
                        <button type="button" class="discord-unsaved-btn-save">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            <span>Lưu Thay Đổi</span>
                        </button>
                    </div>
                `;
                document.body.appendChild(bar);
            }
            this.barEl = bar;

            // Bắt sự kiện click nút Đặt lại
            const resetBtn = this.barEl.querySelector('.discord-unsaved-btn-reset');
            if (resetBtn) {
                resetBtn.onclick = (e) => {
                    e.preventDefault();
                    this.resetForm();
                };
            }

            // Bắt sự kiện click nút Lưu Thay Đổi
            const saveBtn = this.barEl.querySelector('.discord-unsaved-btn-save');
            if (saveBtn) {
                saveBtn.onclick = (e) => {
                    e.preventDefault();
                    this.saveForm();
                };
            }
        }

        bindEvents() {
            // Lắng nghe input, change, keyup, paste
            const checkFn = () => {
                if (this.isResetting) return;
                setTimeout(() => this.checkDirtyState(), 20);
            };

            this.form.addEventListener('input', checkFn);
            this.form.addEventListener('change', checkFn);
            this.form.addEventListener('keyup', checkFn);
            this.form.addEventListener('paste', checkFn);

            // Phím tắt Ctrl+S hoặc Cmd+S để lưu nhanh
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                    if (this.isDirty) {
                        e.preventDefault();
                        this.saveForm();
                    }
                }
            });

            // Lắng nghe thêm/xóa phần tử động (MutationObserver)
            const dynamicContainers = this.form.querySelectorAll('#branchesContainer, #shiftsContainer, #skillProofsContainer, #workHistoryContainer');
            if (window.MutationObserver) {
                const observer = new MutationObserver(() => {
                    if (!this.isResetting) {
                        checkFn();
                    }
                });
                dynamicContainers.forEach(c => {
                    observer.observe(c, { childList: true, subtree: true });
                });
            }

            // Khi submit form thành công -> ẩn bar
            this.form.addEventListener('submit', () => {
                this.isSubmitting = true;
                this.hideBar();
            });

            // Cảnh báo người dùng khi cố đóng tab / reload trình duyệt nếu chưa lưu
            window.addEventListener('beforeunload', (e) => {
                if (this.isDirty && !this.isSubmitting) {
                    e.preventDefault();
                    e.returnValue = 'Bạn có thay đổi chưa lưu!';
                    return 'Bạn có thay đổi chưa lưu!';
                }
            });
        }

        checkDirtyState() {
            if (this.isSubmitting || this.isResetting) return;

            let dirty = false;

            // 1. So khớp giá trị các trường input
            const elements = this.getTrackableElements();
            for (let el of elements) {
                const currentVal = this.getElementValue(el);
                const initialVal = this.initialData.get(el);

                // Trường mới được thêm động sau này
                if (initialVal === undefined) {
                    if (currentVal !== '' && currentVal !== false) {
                        dirty = true;
                        break;
                    }
                } else if (currentVal !== initialVal) {
                    dirty = true;
                    break;
                }
            }

            // 2. So khớp số lượng phần tử động (ví dụ cơ sở, ca làm...)
            if (!dirty) {
                const dynamicContainers = this.form.querySelectorAll('#branchesContainer, #shiftsContainer, #skillProofsContainer, #workHistoryContainer');
                for (let c of dynamicContainers) {
                    const initCount = this.initialCardCounts.get(c) || 0;
                    if (c.children.length !== initCount) {
                        dirty = true;
                        break;
                    }
                }
            }

            this.isDirty = dirty;
            if (this.isDirty) {
                this.showBar();
            } else {
                this.hideBar();
            }
        }

        showBar() {
            if (!this.barEl) return;
            this.barEl.classList.add('is-visible');
            document.body.classList.add('has-unsaved-bar');
        }

        hideBar() {
            if (!this.barEl) return;
            this.barEl.classList.remove('is-visible');
            this.barEl.classList.remove('shake');
            document.body.classList.remove('has-unsaved-bar');
        }

        shakeBar() {
            if (!this.barEl) return;
            this.barEl.classList.remove('shake');
            void this.barEl.offsetWidth; // trigger reflow
            this.barEl.classList.add('shake');
        }

        resetForm() {
            this.isResetting = true;

            // 1. Phục hồi lại giá trị gốc cho tất cả input
            this.initialData.forEach((val, el) => {
                if (!document.body.contains(el)) return;
                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = val;
                } else if (el.type === 'file') {
                    el.value = '';
                } else {
                    el.value = val;
                }
            });

            // 2. Phục hồi ảnh đại diện preview
            if (this.initialAvatarSrc) {
                const avatarImg = this.form.querySelector('#employer_avatar_img, #seeker_avatar_img, #admin_avatar_preview, #inter_avatar_img');
                if (avatarImg) {
                    avatarImg.src = this.initialAvatarSrc;
                }
            }

            // 3. Phục hồi các container động (nếu có xóa hoặc thêm)
            this.initialContainersHtml.forEach((html, container) => {
                if (document.body.contains(container)) {
                    container.innerHTML = html;
                }
            });

            this.isDirty = false;
            this.hideBar();

            setTimeout(() => {
                this.isResetting = false;
            }, 80);

            // Thông báo ngắn cho người dùng
            if (window.Toast && typeof window.Toast.show === 'function') {
                window.Toast.show('Đã khôi phục các thông tin về trạng thái ban đầu!', 'info', 2500);
            }
        }

        saveForm() {
            const saveBtn = this.barEl ? this.barEl.querySelector('.discord-unsaved-btn-save') : null;

            // Kiểm tra tính hợp lệ của Form (HTML5 Validation)
            if (typeof this.form.checkValidity === 'function' && !this.form.checkValidity()) {
                if (typeof this.form.reportValidity === 'function') {
                    this.form.reportValidity();
                }
                this.shakeBar();
                return;
            }

            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '⏳ <span>Đang lưu...</span>';
            }

            this.isSubmitting = true;

            // Ưu tiên click nút submit gốc của form nếu có
            const nativeSubmitBtn = this.form.querySelector('button[type="submit"], input[type="submit"]');
            if (nativeSubmitBtn) {
                nativeSubmitBtn.click();
            } else if (typeof this.form.requestSubmit === 'function') {
                this.form.requestSubmit();
            } else {
                this.form.submit();
            }
        }
    }

    // Tự động khởi tạo cho các trang hồ sơ & thiết lập
    function initUnsavedNotices() {
        const candidateForms = document.querySelectorAll(
            '#employerProfileForm, #seekerProfileForm, form[data-unsaved-bar="true"], form[action*="profile/update"], form[action*="company-profile/update"], form[action*="intermediary/settings"]'
        );

        candidateForms.forEach(form => {
            if (!form.__discordNoticeInstance) {
                form.__discordNoticeInstance = new DiscordUnsavedNotice(form);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUnsavedNotices);
    } else {
        initUnsavedNotices();
    }

    window.DiscordUnsavedNotice = DiscordUnsavedNotice;
})();
