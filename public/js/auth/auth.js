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
     * Xử lý Đăng Ký qua AJAX
     */
    async register(event) {
        if (event) event.preventDefault();
        const form = document.querySelector('form[action*="/auth/register"]');
        if (!form) return;

        const password = form.querySelector('input[name="password"]')?.value;
        if (password && password.length < 6) {
            Toast.show('Mật khẩu tối thiểu phải từ 6 ký tự trở lên!', 'error');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Đang đăng ký...';
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
                Toast.show(data.message || 'Đăng ký thành công!', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect || '/';
                }, 500);
            } else {
                Toast.show(data.message || 'Đăng ký không thành công!', 'error');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Đăng Ký Tài Khoản';
                }
            }
        } catch (err) {
            form.submit();
        }
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

        const registerForm = document.querySelector('form[action*="/auth/register"]');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => this.register(e));
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

document.addEventListener('DOMContentLoaded', () => AuthController.init());
