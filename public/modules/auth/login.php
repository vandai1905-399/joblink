<?php 
$extraCss = 'css/auth/login.css';
$extraJs = 'js/auth/auth.js';
ob_start(); 
?>

<div class="container" style="max-width: 480px; padding-top: 3rem; padding-bottom: 4rem;">
    <div class="card" style="padding: 2.25rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Đăng Nhập JobLink</h1>
            <p style="font-size: 0.9rem; color: var(--slate-500);">Truy cập bảng điều khiển việc làm & tuyển dụng</p>
        </div>

        <form action="<?= url('/auth/login') ?>" method="POST">
            <div class="form-group">
                <label class="form-label">Tên đăng nhập hoặc Email</label>
                <input type="text" name="username" class="form-control" required placeholder="Nhập tên đăng nhập hoặc số điện thoại" tabindex="1" autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" style="margin-bottom: 0.4rem;">Mật khẩu</label>
                <input type="password" name="password" class="form-control" required placeholder="Nhập mật khẩu" tabindex="2">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem; margin-top: 0.5rem;" tabindex="3">
                Đăng Nhập Ngay
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--slate-600); line-height: 1.7;">
            <div>Nếu chưa có tài khoản?</div>
            <div style="margin-top: 0.25rem;">
                <a href="<?= url('/auth/register') ?>" style="color: var(--primary); font-weight: 700;" tabindex="4">Đăng ký tại đây</a> hoặc <a href="<?= url('/auth/forgot-password') ?>" style="color: var(--primary); font-weight: 700;" tabindex="5">Quên mật khẩu</a>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/main_layout.php';
?>
