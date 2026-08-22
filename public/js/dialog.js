/**
 * ============================================================================
 * JobLink UI - Thư viện Dialog & Toast Notification Chuẩn Dùng Chung
 * Xây dựng theo đặc tả OneNote:
 * - 11 Loại Dialog tương tác (Alert, Confirm, Form, Detail, Loading, Search, Filter, Select, Upload, Reject Reason, Status)
 * - 6 Loại Notification Toast (Success, Error, Warning, Info, Loading, Progress)
 * - Tiêu chuẩn màu sắc & nút bấm: Primary, Success, Warning, Danger, Secondary
 * ============================================================================
 */

(function (window, document) {
    'use strict';

    // ------------------------------------------------------------------------
    // 1. TOAST NOTIFICATION MODULE
    // ------------------------------------------------------------------------
    const Toast = {
        _getContainer() {
            let container = document.getElementById('joblink-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'joblink-toast-container';
                document.body.appendChild(container);
            }
            return container;
        },

        _getIcon(type) {
            switch (type) {
                case 'success':
                    return `<svg class="joblink-toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
                case 'error':
                    return `<svg class="joblink-toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>`;
                case 'warning':
                    return `<svg class="joblink-toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
                case 'loading':
                    return `<div class="joblink-spinner" style="width:18px;height:18px;border-width:2px;border-color:rgba(255,255,255,0.3);border-top-color:#ffffff;"></div>`;
                case 'info':
                default:
                    return `<svg class="joblink-toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4m0-4h.01"/></svg>`;
            }
        },

        /**
         * Hiển thị thông báo Toast chung
         */
        show(message, type = 'info', duration = 4000) {
            const container = this._getContainer();
            const toast = document.createElement('div');
            toast.className = `joblink-toast joblink-toast-${type}`;

            toast.innerHTML = `
                ${this._getIcon(type)}
                <div class="joblink-toast-body">${message}</div>
                <button class="joblink-toast-close" title="Đóng">&times;</button>
            `;

            const closeBtn = toast.querySelector('.joblink-toast-close');
            let timeoutId = null;

            const removeToast = () => {
                if (timeoutId) clearTimeout(timeoutId);
                toast.classList.remove('show');
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 300);
            };

            closeBtn.addEventListener('click', removeToast);

            // Đưa thông báo mới nhất lên TRÊN CÙNG (các thông báo cũ tự động bị đẩy xuống)
            container.prepend(toast);
            requestAnimationFrame(() => toast.classList.add('show'));

            if (duration > 0) {
                timeoutId = setTimeout(removeToast, duration);
            }

            return {
                close: removeToast,
                element: toast
            };
        },

        success(message, duration = 4000) {
            return this.show(message, 'success', duration);
        },

        error(message, duration = 5000) {
            return this.show(message, 'error', duration);
        },

        warning(message, duration = 4500) {
            return this.show(message, 'warning', duration);
        },

        info(message, duration = 4000) {
            return this.show(message, 'info', duration);
        },

        loading(message = 'Đang xử lý...') {
            const toastObj = this.show(message, 'loading', 0);
            return {
                update: (newMessage, newType = 'success', autoCloseDuration = 3500) => {
                    toastObj.element.className = `joblink-toast joblink-toast-${newType} show`;
                    toastObj.element.querySelector('.joblink-toast-body').innerHTML = newMessage;
                    const oldIcon = toastObj.element.querySelector('.joblink-toast-icon, .joblink-spinner');
                    if (oldIcon) {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = Toast._getIcon(newType);
                        oldIcon.replaceWith(tempDiv.firstElementChild);
                    }
                    if (autoCloseDuration > 0) {
                        setTimeout(toastObj.close, autoCloseDuration);
                    }
                },
                close: toastObj.close
            };
        },

        progress(message = 'Đang tải lên...', initialPercent = 0) {
            const container = this._getContainer();
            const toast = document.createElement('div');
            toast.className = 'joblink-toast joblink-toast-loading';
            toast.innerHTML = `
                ${this._getIcon('info')}
                <div class="joblink-toast-body">
                    <div style="margin-bottom: 4px;">${message}</div>
                    <div style="font-size: 0.8rem; opacity: 0.8;" class="joblink-progress-text">${initialPercent}%</div>
                </div>
                <div class="joblink-toast-progress-bar" style="width: ${initialPercent}%"></div>
                <button class="joblink-toast-close">&times;</button>
            `;

            const progressBar = toast.querySelector('.joblink-toast-progress-bar');
            const progressText = toast.querySelector('.joblink-progress-text');
            const closeBtn = toast.querySelector('.joblink-toast-close');

            const closeToast = () => {
                toast.classList.remove('show');
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 300);
            };
            closeBtn.addEventListener('click', closeToast);

            // Đưa thông báo mới lên trên cùng
            container.prepend(toast);
            requestAnimationFrame(() => toast.classList.add('show'));

            return {
                setProgress: (percent, newText = null) => {
                    progressBar.style.width = percent + '%';
                    progressText.innerText = newText ? `${newText} (${percent}%)` : `${percent}%`;
                    if (percent >= 100) {
                        toast.className = 'joblink-toast joblink-toast-success show';
                        setTimeout(closeToast, 2500);
                    }
                },
                close: closeToast
            };
        }
    };

    // ------------------------------------------------------------------------
    // 2. DIALOG (MODAL) MODULE
    // ------------------------------------------------------------------------
    const Dialog = {
        _activeBackdrops: [],

        _createBase({ title = 'Thông báo', size = '', showClose = true }) {
            const backdrop = document.createElement('div');
            backdrop.className = 'joblink-dialog-backdrop';

            const modal = document.createElement('div');
            modal.className = `joblink-dialog-modal ${size ? 'modal-' + size : ''}`;

            let closeBtnHtml = showClose ? `<button type="button" class="joblink-dialog-close-btn" title="Đóng">&times;</button>` : '';

            modal.innerHTML = `
                <div class="joblink-dialog-header">
                    <h3 class="joblink-dialog-title">${title}</h3>
                    ${closeBtnHtml}
                </div>
                <div class="joblink-dialog-body"></div>
                <div class="joblink-dialog-footer"></div>
            `;

            backdrop.appendChild(modal);
            document.body.appendChild(backdrop);

            const close = () => {
                backdrop.classList.remove('show');
                setTimeout(() => {
                    backdrop.remove();
                    Dialog._activeBackdrops = Dialog._activeBackdrops.filter(b => b !== backdrop);
                }, 250);
            };

            if (showClose) {
                modal.querySelector('.joblink-dialog-close-btn').addEventListener('click', close);
                backdrop.addEventListener('click', (e) => {
                    if (e.target === backdrop) close();
                });
            }

            requestAnimationFrame(() => backdrop.classList.add('show'));
            Dialog._activeBackdrops.push(backdrop);

            return {
                backdrop,
                modal,
                body: modal.querySelector('.joblink-dialog-body'),
                footer: modal.querySelector('.joblink-dialog-footer'),
                close
            };
        },

        /**
         * 1. Alert Dialog: Thông báo cần người dùng xác nhận đã đọc
         */
        alert(options) {
            const opts = typeof options === 'string' ? { content: options } : options;
            const dialog = this._createBase({
                title: opts.title || 'Thông Báo',
                size: 'sm'
            });

            dialog.body.innerHTML = opts.content || '';
            const btn = document.createElement('button');
            btn.className = 'joblink-btn joblink-btn-secondary';
            btn.innerText = opts.buttonText || 'Đóng';
            btn.addEventListener('click', () => {
                dialog.close();
                if (typeof opts.onConfirm === 'function') opts.onConfirm();
            });

            dialog.footer.appendChild(btn);
            return dialog;
        },

        /**
         * 2. Confirm Dialog: Xác nhận một hành động (Primary / Danger / Warning / Success)
         */
        confirm(options) {
            const opts = typeof options === 'string' ? { content: options } : options;
            const dialog = this._createBase({
                title: opts.title || 'Xác Nhận Hành Động',
                size: 'sm'
            });

            dialog.body.innerHTML = opts.content || 'Bạn có chắc chắn muốn thực hiện hành động này không?';

            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'joblink-btn joblink-btn-secondary';
            cancelBtn.innerText = opts.cancelText || 'Hủy';
            cancelBtn.addEventListener('click', () => {
                dialog.close();
                if (typeof opts.onCancel === 'function') opts.onCancel();
            });

            const confirmBtn = document.createElement('button');
            const type = opts.confirmType || 'primary'; // 'primary' | 'danger' | 'warning' | 'success'
            confirmBtn.className = `joblink-btn joblink-btn-${type}`;
            confirmBtn.innerText = opts.confirmText || 'Đồng ý';
            confirmBtn.addEventListener('click', () => {
                dialog.close();
                if (typeof opts.onConfirm === 'function') opts.onConfirm();
            });

            dialog.footer.appendChild(cancelBtn);
            dialog.footer.appendChild(confirmBtn);
            return dialog;
        },

        /**
         * 3. Form Dialog: Thêm mới hoặc chỉnh sửa dữ liệu
         */
        form(options) {
            const dialog = this._createBase({
                title: options.title || 'Nhập Thông Tin',
                size: options.size || ''
            });

            let formHtml = `<form id="joblink-dialog-form" style="display:flex; flex-direction:column; gap:1rem;">`;
            (options.fields || []).forEach(f => {
                formHtml += `<div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-weight:700; margin-bottom:0.35rem; display:block;">${f.label || ''} ${f.required ? '<span style="color:var(--danger)">*</span>' : ''}</label>`;
                
                if (f.type === 'textarea') {
                    formHtml += `<textarea name="${f.name}" class="form-control" rows="${f.rows || 3}" placeholder="${f.placeholder || ''}" ${f.required ? 'required' : ''}>${f.value || ''}</textarea>`;
                } else if (f.type === 'select') {
                    formHtml += `<select name="${f.name}" class="form-control" ${f.required ? 'required' : ''}>`;
                    (f.options || []).forEach(opt => {
                        const isSelected = (opt.value == f.value) ? 'selected' : '';
                        formHtml += `<option value="${opt.value}" ${isSelected}>${opt.label}</option>`;
                    });
                    formHtml += `</select>`;
                } else {
                    formHtml += `<input type="${f.type || 'text'}" name="${f.name}" value="${f.value || ''}" class="form-control" placeholder="${f.placeholder || ''}" ${f.required ? 'required' : ''}>`;
                }
                formHtml += `</div>`;
            });
            formHtml += `</form>`;
            dialog.body.innerHTML = formHtml;

            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'joblink-btn joblink-btn-secondary';
            cancelBtn.innerText = options.cancelText || 'Hủy';
            cancelBtn.addEventListener('click', dialog.close);

            const submitBtn = document.createElement('button');
            submitBtn.className = 'joblink-btn joblink-btn-primary';
            submitBtn.innerText = options.submitText || 'Lưu Dữ Liệu';
            submitBtn.addEventListener('click', () => {
                const form = dialog.modal.querySelector('#joblink-dialog-form');
                if (form.reportValidity()) {
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());
                    if (typeof options.onSubmit === 'function') {
                        options.onSubmit(data, dialog);
                    } else {
                        dialog.close();
                    }
                }
            });

            dialog.footer.appendChild(cancelBtn);
            dialog.footer.appendChild(submitBtn);
            return dialog;
        },

        /**
         * 4. Detail Dialog: Xem chi tiết dữ liệu (dạng label-value)
         */
        detail(options) {
            const dialog = this._createBase({
                title: options.title || 'Chi Tiết Dữ Liệu',
                size: options.size || ''
            });

            let gridHtml = `<div class="joblink-detail-grid">`;
            (options.items || []).forEach(item => {
                gridHtml += `
                    <div class="joblink-detail-label">${item.label}:</div>
                    <div class="joblink-detail-value">${item.value ?? '—'}</div>
                `;
            });
            gridHtml += `</div>`;
            dialog.body.innerHTML = gridHtml;

            const closeBtn = document.createElement('button');
            closeBtn.className = 'joblink-btn joblink-btn-secondary';
            closeBtn.innerText = options.buttonText || 'Đóng';
            closeBtn.addEventListener('click', dialog.close);

            dialog.footer.appendChild(closeBtn);
            return dialog;
        },

        /**
         * 5. Loading Dialog: Hiển thị trạng thái đang xử lý (không có nút, khóa màn hình)
         */
        loading(options) {
            const opts = typeof options === 'string' ? { message: options } : (options || {});
            const dialog = this._createBase({
                title: opts.title || 'Hệ Thống Đang Xử Lý',
                size: 'sm',
                showClose: false
            });

            dialog.body.innerHTML = `
                <div class="joblink-spinner-wrapper">
                    <div class="joblink-spinner"></div>
                    <div style="font-weight:700; color:#0f172a; font-size:1rem;">${opts.message || 'Vui lòng chờ trong giây lát...'}</div>
                </div>
            `;
            dialog.footer.style.display = 'none';
            return dialog;
        },

        /**
         * 6. Search Dialog: Tìm kiếm nâng cao
         */
        search(options) {
            return this.form({
                title: options.title || 'Tìm Kiếm Nâng Cao',
                fields: options.fields || [],
                submitText: options.searchBtnText || 'Tìm Kiếm',
                cancelText: 'Hủy',
                onSubmit: options.onSearch
            });
        },

        /**
         * 7. Filter Dialog: Bộ lọc dữ liệu
         */
        filter(options) {
            const dialog = this.form({
                title: options.title || 'Bộ Lọc Dữ Liệu',
                fields: options.filters || [],
                submitText: options.applyBtnText || 'Áp Dụng',
                cancelText: 'Hủy',
                onSubmit: options.onApply
            });

            const resetBtn = document.createElement('button');
            resetBtn.className = 'joblink-btn joblink-btn-warning';
            resetBtn.style.marginRight = 'auto';
            resetBtn.innerText = options.resetBtnText || 'Đặt Lại';
            resetBtn.addEventListener('click', () => {
                const form = dialog.modal.querySelector('#joblink-dialog-form');
                if (form) form.reset();
                if (typeof options.onReset === 'function') options.onReset(dialog);
            });

            dialog.footer.prepend(resetBtn);
            return dialog;
        },

        /**
         * 8. Select Dialog: Chọn dữ liệu từ danh sách
         */
        select(options) {
            const dialog = this._createBase({
                title: options.title || 'Chọn Dữ Liệu',
                size: options.size || ''
            });

            let listHtml = `<div style="display:flex; flex-direction:column; gap:0.5rem; max-height:320px; overflow-y:auto;">`;
            (options.options || []).forEach(opt => {
                listHtml += `
                    <label style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer; transition:all 0.15s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='#e2e8f0'">
                        <input type="radio" name="joblink_dialog_selected_opt" value="${opt.value}" style="width:18px; height:18px; accent-color:var(--primary);">
                        <div>
                            <strong style="color:#0f172a; display:block;">${opt.label}</strong>
                            ${opt.desc ? `<span style="font-size:0.82rem; color:#64748b;">${opt.desc}</span>` : ''}
                        </div>
                    </label>
                `;
            });
            listHtml += `</div>`;
            dialog.body.innerHTML = listHtml;

            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'joblink-btn joblink-btn-secondary';
            cancelBtn.innerText = 'Hủy';
            cancelBtn.addEventListener('click', dialog.close);

            const selectBtn = document.createElement('button');
            selectBtn.className = 'joblink-btn joblink-btn-primary';
            selectBtn.innerText = options.selectBtnText || 'Chọn';
            selectBtn.addEventListener('click', () => {
                const checked = dialog.modal.querySelector('input[name="joblink_dialog_selected_opt"]:checked');
                if (!checked) {
                    Toast.warning('Vui lòng chọn 1 mục!');
                    return;
                }
                dialog.close();
                if (typeof options.onSelect === 'function') options.onSelect(checked.value);
            });

            dialog.footer.appendChild(cancelBtn);
            dialog.footer.appendChild(selectBtn);
            return dialog;
        },

        /**
         * 9. Upload Dialog: Tải tệp lên hệ thống
         */
        upload(options) {
            const dialog = this._createBase({
                title: options.title || 'Tải Tệp Lên Hệ Thống',
                size: 'sm'
            });

            dialog.body.innerHTML = `
                <div class="joblink-upload-dropzone" id="joblink-dropzone">
                    <svg style="width:40px; height:40px; color:var(--primary); margin-bottom:0.5rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <div style="font-weight:700; color:#0f172a; margin-bottom:0.25rem;">Kéo thả tệp vào đây hoặc bấm để chọn</div>
                    <div style="font-size:0.82rem; color:#64748b;" id="joblink-file-name">Hỗ trợ các định dạng: ${options.accept || 'PDF, DOCX, JPG, PNG'}</div>
                    <input type="file" id="joblink-file-input" accept="${options.accept || '*'}" style="display:none;">
                </div>
            `;

            const dropzone = dialog.modal.querySelector('#joblink-dropzone');
            const fileInput = dialog.modal.querySelector('#joblink-file-input');
            const fileNameDisplay = dialog.modal.querySelector('#joblink-file-name');
            let selectedFile = null;

            dropzone.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    selectedFile = e.target.files[0];
                    fileNameDisplay.innerHTML = `Đã chọn: <strong style="color:var(--primary)">${selectedFile.name}</strong> (${(selectedFile.size/1024).toFixed(1)} KB)`;
                }
            });

            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'joblink-btn joblink-btn-secondary';
            cancelBtn.innerText = 'Hủy';
            cancelBtn.addEventListener('click', dialog.close);

            const uploadBtn = document.createElement('button');
            uploadBtn.className = 'joblink-btn joblink-btn-primary';
            uploadBtn.innerText = options.uploadBtnText || 'Tải Lên';
            uploadBtn.addEventListener('click', () => {
                if (!selectedFile) {
                    Toast.warning('Vui lòng chọn tệp trước khi tải lên!');
                    return;
                }
                if (typeof options.onUpload === 'function') {
                    options.onUpload(selectedFile, dialog);
                } else {
                    dialog.close();
                }
            });

            dialog.footer.appendChild(cancelBtn);
            dialog.footer.appendChild(uploadBtn);
            return dialog;
        },

        /**
         * 10. Reject Reason Dialog: Nhập lý do từ chối
         */
        rejectReason(options) {
            const opts = options || {};
            const dialog = this._createBase({
                title: opts.title || 'Nhập Lý Do Từ Chối',
                size: 'sm'
            });

            dialog.body.innerHTML = `
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-weight:700; margin-bottom:0.35rem; display:block;">${opts.label || 'Vui lòng cung cấp lý do từ chối để thông báo đến người dùng'}:</label>
                    <textarea id="joblink-reject-reason-input" class="form-control" rows="4" placeholder="${opts.placeholder || 'Nhập chi tiết lý do từ chối...'}"></textarea>
                </div>
            `;

            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'joblink-btn joblink-btn-secondary';
            cancelBtn.innerText = 'Hủy';
            cancelBtn.addEventListener('click', dialog.close);

            const confirmBtn = document.createElement('button');
            confirmBtn.className = 'joblink-btn joblink-btn-danger';
            confirmBtn.innerText = opts.confirmText || 'Xác Nhận Từ Chối';
            confirmBtn.addEventListener('click', () => {
                const reason = dialog.modal.querySelector('#joblink-reject-reason-input').value.trim();
                if (!reason && opts.required !== false) {
                    Toast.warning('Vui lòng nhập lý do từ chối!');
                    return;
                }
                dialog.close();
                if (typeof opts.onConfirm === 'function') opts.onConfirm(reason);
            });

            dialog.footer.appendChild(cancelBtn);
            dialog.footer.appendChild(confirmBtn);
            return dialog;
        },

        /**
         * 11. Status Dialog: Cập nhật trạng thái dữ liệu
         */
        status(options) {
            const dialog = this._createBase({
                title: options.title || 'Cập Nhật Trạng Thái',
                size: 'sm'
            });

            let selectHtml = `
                <div class="form-group">
                    <label class="form-label" style="font-weight:700; margin-bottom:0.35rem; display:block;">Trạng thái mới *</label>
                    <select id="joblink-status-select" class="form-control">
            `;
            (options.statuses || []).forEach(st => {
                const isSelected = (st.value == options.currentStatus) ? 'selected' : '';
                selectHtml += `<option value="${st.value}" ${isSelected}>${st.label}</option>`;
            });
            selectHtml += `
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-weight:700; margin-bottom:0.35rem; display:block;">Ghi chú kèm theo</label>
                    <textarea id="joblink-status-note" class="form-control" rows="3" placeholder="Ghi chú nội bộ hoặc gửi kèm thông báo..."></textarea>
                </div>
            `;
            dialog.body.innerHTML = selectHtml;

            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'joblink-btn joblink-btn-secondary';
            cancelBtn.innerText = 'Hủy';
            cancelBtn.addEventListener('click', dialog.close);

            const updateBtn = document.createElement('button');
            updateBtn.className = 'joblink-btn joblink-btn-primary';
            updateBtn.innerText = options.updateBtnText || 'Cập Nhật';
            updateBtn.addEventListener('click', () => {
                const newStatus = dialog.modal.querySelector('#joblink-status-select').value;
                const note = dialog.modal.querySelector('#joblink-status-note').value.trim();
                dialog.close();
                if (typeof options.onUpdate === 'function') options.onUpdate(newStatus, note);
            });

            dialog.footer.appendChild(cancelBtn);
            dialog.footer.appendChild(updateBtn);
            return dialog;
        },

        closeAll() {
            this._activeBackdrops.forEach(b => b.remove());
            this._activeBackdrops = [];
        }
    };

    // Tự động lắng nghe các nút có `data-confirm` hoặc `data-reject-prompt`
    document.addEventListener('click', (e) => {
        // 1. Xử lý nút Từ Chối yêu cầu nhập Lý Do (data-reject-prompt)
        const rejectBtn = e.target.closest('[data-reject-prompt]');
        if (rejectBtn) {
            e.preventDefault();
            const promptTitle = rejectBtn.getAttribute('data-reject-prompt') || 'Nhập Lý Do Từ Chối';
            const form = rejectBtn.closest('form');

            Dialog.rejectReason({
                title: promptTitle,
                placeholder: 'Vui lòng cung cấp lý do chi tiết...',
                confirmText: 'Xác Nhận Từ Chối',
                onConfirm: (reason) => {
                    if (form) {
                        let reasonInput = form.querySelector('input[name="rejection_reason"], input[name="reason"]');
                        if (!reasonInput) {
                            reasonInput = document.createElement('input');
                            reasonInput.type = 'hidden';
                            reasonInput.name = 'rejection_reason';
                            form.appendChild(reasonInput);
                        }
                        reasonInput.value = reason;
                        form.submit();
                    }
                }
            });
            return;
        }

        // 2. Xử lý nút Xác nhận hành động (data-confirm)
        const confirmBtn = e.target.closest('[data-confirm]');
        if (confirmBtn) {
            e.preventDefault();
            const message = confirmBtn.getAttribute('data-confirm');
            const isDanger = confirmBtn.classList.contains('btn-danger') || confirmBtn.classList.contains('joblink-btn-danger');
            const form = confirmBtn.closest('form');

            Dialog.confirm({
                content: message,
                confirmType: isDanger ? 'danger' : 'primary',
                confirmText: isDanger ? 'Đồng Ý Xóa' : 'Đồng Ý',
                onConfirm: () => {
                    if (confirmBtn.tagName === 'FORM') confirmBtn.submit();
                    else if (form) form.submit();
                    else if (confirmBtn.href) window.location.href = confirmBtn.href;
                }
            });
        }
    });

    // Expose to window for global access across all JS files and views
    window.Toast = Toast;
    window.Dialog = Dialog;
    window.JobLinkUI = { Toast, Dialog };

})(window, document);
