/**
 * controllers/AuthController.js - Xử lý chức năng & thực thi lệnh Đăng nhập, Đăng ký, Phân quyền Client
 */

const AuthController = {
    /**
     * Xử lý Đăng Nhập qua AJAX
     */
    async login(event) {
        if (event) event.preventDefault();
        const form = document.querySelector('form[action*="/auth/login"]');
        if (!form) return;

        const submitBtn = form.querySelector('button[type="submit"]');
        const username = form.querySelector('input[name="username"]')?.value.trim();
        const password = form.querySelector('input[name="password"]')?.value;

        if (!username || !password) {
            Toast.show('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!', 'error');
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Đang xác thực...';
        }

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action || '/auth/login', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (res.ok && data.success) {
                Toast.show(data.message || 'Đăng nhập thành công!', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect || '/';
                }, 500);
            } else {
                Toast.show(data.message || 'Đăng nhập thất bại!', 'error');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Đăng Nhập Ngay';
                }
            }
        } catch (err) {
            Toast.show('Đã xảy ra lỗi kết nối, vui lòng thử lại!', 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Đăng Nhập Ngay';
            }
        }
    },

    /**
     * Chuyển đổi hiển thị Bước trong Wizard
     */
    goToStep(stepNumber) {
        // Cập nhật Panels
        document.querySelectorAll('.wizard-step-panel').forEach(p => p.classList.remove('active'));
        const targetPanel = document.getElementById(`wizard-panel-${stepNumber}`);
        if (targetPanel) targetPanel.classList.add('active');

        // Cập nhật Progress Fill
        const fillBar = document.getElementById('wizard-progress-fill');
        if (fillBar) {
            if (stepNumber === 1) fillBar.style.width = '0%';
            else if (stepNumber === 2) fillBar.style.width = '50%';
            else if (stepNumber === 3) fillBar.style.width = '100%';
        }

        // Cập nhật Indicators
        for (let i = 1; i <= 3; i++) {
            const ind = document.getElementById(`wizard-indicator-${i}`);
            if (!ind) continue;
            ind.classList.remove('active', 'completed');
            if (i < stepNumber) {
                ind.classList.add('completed');
                ind.querySelector('.wizard-step-circle').innerHTML = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            } else if (i === stepNumber) {
                ind.classList.add('active');
                ind.querySelector('.wizard-step-circle').innerHTML = i;
            } else {
                ind.querySelector('.wizard-step-circle').innerHTML = i;
            }
        }

        // Cuộn lên đầu card mượt mà
        document.querySelector('.register-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    /**
     * Xử lý Đăng Ký Bước 1 (Tài Khoản) qua AJAX
     */
    async registerStep1(event) {
        if (event) event.preventDefault();
        const form = document.getElementById('form-register-step-1') || document.querySelector('form[action*="/auth/register"]');
        if (!form) return;

        const username = form.querySelector('input[name="username"]')?.value.trim();
        const email = form.querySelector('input[name="email"]')?.value.trim();
        const password = form.querySelector('input[name="password"]')?.value;
        const confirmPassword = form.querySelector('input[name="confirm_password"]')?.value;
        const roleType = form.querySelector('input[name="role_type"]')?.value || 'JOB_SEEKER';

        // Kiểm tra định dạng username theo từng vai trò
        if (roleType === 'JOB_SEEKER') {
            const phoneRegex = /^(0[3|5|7|8|9])[0-9]{8}$/;
            if (!phoneRegex.test(username)) {
                Toast.show('Người lao động phải nhập Tên tài khoản là Số điện thoại Việt Nam hợp lệ (10 chữ số, bắt đầu bằng 03, 05, 07, 08, 09)!', 'error');
                return;
            }
        } else {
            if (/^[0-9]+$/.test(username)) {
                Toast.show('Tên tài khoản Nhà tuyển dụng phải ở dạng chữ (Ví dụ: becamex_idc, vinamilk), không được đặt toàn bộ bằng số điện thoại!', 'error');
                return;
            }
            const usernameRegex = /^[a-zA-Z0-9_]{3,30}$/;
            if (!usernameRegex.test(username)) {
                Toast.show('Tên tài khoản Nhà tuyển dụng chỉ gồm chữ cái, số và dấu gạch dưới (từ 3 đến 30 ký tự)!', 'error');
                return;
            }
        }

        if (!email || !email.includes('@')) {
            Toast.show('Vui lòng nhập địa chỉ email hợp lệ!', 'error');
            return;
        }

        if (!password || password.length < 6) {
            Toast.show('Mật khẩu tối thiểu phải từ 6 ký tự trở lên!', 'error');
            return;
        }

        if (confirmPassword !== undefined && password !== confirmPassword) {
            Toast.show('Mật khẩu xác nhận không trùng khớp!', 'error');
            return;
        }

        const submitBtn = document.getElementById('btn-submit-step-1') || form.querySelector('button[type="submit"]');
        const origBtnText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Đang khởi tạo tài khoản...';
        }

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action || '/auth/register', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (res.ok && data.success) {
                Toast.show(data.message || 'Khởi tạo tài khoản thành công!', 'success');
                
                // Điền sẵn dữ liệu sang form Bước 2 nếu có
                if (data.data) {
                    const d = data.data;
                    const nameField = document.getElementById('step2_full_name');
                    if (nameField && !nameField.value) {
                        nameField.value = d.display_name || d.username || '';
                    }
                    const phoneField = document.getElementById('step2_phone_number');
                    if (phoneField && !phoneField.value && roleType === 'JOB_SEEKER') {
                        phoneField.value = username;
                    }
                    const empNameField = document.getElementById('step2_employer_name');
                    if (empNameField && !empNameField.value) {
                        empNameField.value = d.display_name || d.username || '';
                    }
                }

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origBtnText;
                }

                // Cập nhật URL trình duyệt sang step=2 không tải lại trang
                try {
                    window.history.pushState({ step: 2 }, '', window.location.pathname + '?step=2');
                } catch (e) {}

                // Chuyển sang Bước 2 ngay lập tức
                AuthController.goToStep(2);
            } else {
                Toast.show(data.message || 'Đăng ký không thành công!', 'error');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origBtnText;
                }
            }

        } catch (err) {
            Toast.show('Lỗi kết nối máy chủ, vui lòng thử lại!', 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = origBtnText;
            }
        }
    },

    /**
     * Xử lý Đăng Ký Bước 2 (Hồ Sơ Chi Tiết) qua AJAX
     */
    async registerStep2(event) {
        if (event) event.preventDefault();
        const form = document.getElementById('form-register-step-2');
        if (!form) return;

        const submitBtn = document.getElementById('btn-submit-step-2');
        const origBtnText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Đang lưu thông tin hồ sơ...';
        }

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action || '/auth/register-step2', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (res.ok && data.success) {
                Toast.show(data.message || 'Lưu thông tin hồ sơ thành công!', 'success');

                // Cập nhật màn hình Bước 3 (Hoàn tất)
                if (data.data) {
                    const d = data.data;
                    const resName = document.getElementById('res-display-name');
                    if (resName) resName.innerText = d.display_name;

                    const resId = document.getElementById('res-account-id');
                    if (resId && d.formatted_id) resId.innerText = d.formatted_id;

                    const btnPrimary = document.getElementById('btn-action-primary');
                    if (btnPrimary && d.ai_url) {
                        btnPrimary.href = d.ai_url;
                        btnPrimary.innerHTML = (d.role === 'JOB_SEEKER') 
                            ? '<svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path></svg>Khám Phá Việc Làm Phù Hợp Gợi Ý Từ AI' 
                            : '<svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Đăng Tin Tuyển Dụng Đầu Tiên Ngay';
                    }

                    const btnSecondary = document.getElementById('btn-action-secondary');
                    if (btnSecondary && d.dash_url) btnSecondary.href = d.dash_url;
                }

                // Chuyển sang Bước 3
                setTimeout(() => {
                    AuthController.goToStep(3);
                }, 400);
            } else {
                Toast.show(data.message || 'Không thể lưu hồ sơ!', 'error');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origBtnText;
                }
            }
        } catch (err) {
            Toast.show('Lỗi kết nối máy chủ, vui lòng thử lại!', 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = origBtnText;
            }
        }
    },

    /**
     * Bỏ qua Bước 2
     */
    skipStep2() {
        AuthController.goToStep(3);
    },


    /**
     * Xử lý Quên Mật Khẩu qua AJAX
     */
    async forgotPassword(event) {
        if (event) event.preventDefault();
        const form = document.querySelector('#form-forgot-password') || document.querySelector('form[action*="/auth/forgot-password"]');
        if (!form) return;

        const newPass = form.querySelector('input[name="new_password"]')?.value;
        const confirmPass = form.querySelector('input[name="confirm_password"]')?.value;

        if (newPass !== confirmPass) {
            Toast.show('Mật khẩu xác nhận không khớp!', 'error');
            return;
        }

        if (newPass.length < 6) {
            Toast.show('Mật khẩu mới phải có tối thiểu 6 ký tự!', 'error');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Đang xử lý...';
        }

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action || '/auth/forgot-password', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (res.ok && data.success) {
                Toast.show(data.message || 'Đặt lại mật khẩu thành công!', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect || '/auth/login';
                }, 800);
            } else {
                Toast.show(data.message || 'Có lỗi xảy ra!', 'error');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Đặt Lại Mật Khẩu';
                }
            }
        } catch (err) {
            form.submit();
        }
    },

    /**
     * Xử lý Đổi Mật Khẩu qua AJAX
     */
    async changePassword(event) {
        if (event) event.preventDefault();
        const form = document.querySelector('#form-change-password') || document.querySelector('form[action*="/auth/change-password"]');
        if (!form) return;

        const newPass = form.querySelector('input[name="new_password"]')?.value;
        const confirmPass = form.querySelector('input[name="confirm_password"]')?.value;

        if (newPass !== confirmPass) {
            Toast.show('Mật khẩu xác nhận không khớp!', 'error');
            return;
        }

        if (newPass.length < 6) {
            Toast.show('Mật khẩu mới phải có tối thiểu 6 ký tự!', 'error');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Đang cập nhật...';
        }

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action || '/auth/change-password', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (res.ok && data.success) {
                Toast.show(data.message || 'Đổi mật khẩu thành công!', 'success');
                form.reset();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Cập Nhật Mật Khẩu';
                }
            } else {
                Toast.show(data.message || 'Đổi mật khẩu không thành công!', 'error');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Cập Nhật Mật Khẩu';
                }
            }
        } catch (err) {
            form.submit();
        }
    },

    /**
     * Khởi tạo lắng nghe sự kiện
     */
    init() {
        const loginForm = document.querySelector('form[action*="/auth/login"]');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.login(e));
        }

        const step1Form = document.getElementById('form-register-step-1') || document.querySelector('form[action*="/auth/register"]');
        if (step1Form) {
            step1Form.addEventListener('submit', (e) => this.registerStep1(e));
        }

        const step2Form = document.getElementById('form-register-step-2') || document.querySelector('form[action*="/auth/register-step2"]');
        if (step2Form) {
            step2Form.addEventListener('submit', (e) => this.registerStep2(e));
        }

        const skipBtn = document.getElementById('btn-skip-step-2');
        if (skipBtn) {
            skipBtn.addEventListener('click', () => this.skipStep2());
        }

        const forgotForm = document.querySelector('#form-forgot-password') || document.querySelector('form[action*="/auth/forgot-password"]');
        if (forgotForm) {
            forgotForm.addEventListener('submit', (e) => this.forgotPassword(e));
        }

        const changePwdForm = document.querySelector('#form-change-password') || document.querySelector('form[action*="/auth/change-password"]');
        if (changePwdForm) {
            changePwdForm.addEventListener('submit', (e) => this.changePassword(e));
        }
    }
};

window.AuthController = AuthController;
document.addEventListener('DOMContentLoaded', () => AuthController.init());


