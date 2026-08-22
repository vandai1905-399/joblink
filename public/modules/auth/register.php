<?php 
$extraCss = 'css/auth/register.css';
$extraJs = 'js/auth/auth.js';
ob_start(); 
?>

<div class="container" style="max-width: 520px; padding-top: 2.5rem; padding-bottom: 3.5rem;">
    <div class="card" style="padding: 2.25rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Tạo Tài Khoản Mới</h1>
            <p style="font-size: 0.9rem; color: var(--slate-500);">Tham gia nền tảng tuyển dụng & tìm việc làm uy tín</p>
        </div>



        <form action="<?= url('/auth/register') ?>" method="POST">
            <div class="form-group">
                <label class="form-label">Bạn tham gia với vai trò nào?</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <label style="border: 2px solid var(--primary); padding: 0.75rem; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 0.5rem; cursor: pointer; background: var(--primary-light);">
                        <input type="radio" name="role_type" value="JOB_SEEKER" checked>
                        <span style="font-weight: 700; font-size: 0.9rem; color: var(--primary);">Người Lao Động</span>
                    </label>
                    <label style="border: 2px solid var(--slate-200); padding: 0.75rem; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="role_type" value="EMPLOYER_ADMIN">
                        <span style="font-weight: 700; font-size: 0.9rem; color: var(--dark);">Nhà Tuyển Dụng</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Tên tài khoản (Username / Số điện thoại)</label>
                <input type="text" name="username" class="form-control" required placeholder="VD: nguyenhoa hoặc 0901234567">
            </div>

            <div class="form-group">
                <label class="form-label">Địa chỉ Email</label>
                <input type="email" name="email" class="form-control" required placeholder="name@example.com">
            </div>

            <div class="form-group">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="password" class="form-control" required placeholder="Tối thiểu 6 ký tự">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem; margin-top: 0.5rem;">
                Đăng Ký Tài Khoản
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--slate-600);">
            Đã có tài khoản? <a href="<?= url('/auth/login') ?>" style="color: var(--primary); font-weight: 700;">Đăng nhập ngay</a>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/main_layout.php';
?>
