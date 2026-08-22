<?php 
$extraCss = 'css/auth/login.css';
$extraJs = 'js/auth/auth.js';
ob_start(); 
?>

<div class="container" style="max-width: 480px; padding-top: 3rem; padding-bottom: 4rem;">
    <div class="card" style="padding: 2.25rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="margin-bottom: 0.75rem; color: var(--primary);">
                <?= icon('key', '', 40) ?>
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Quên Mật Khẩu</h1>
            <p style="color: var(--slate-500); font-size: 0.92rem;">Nhập tài khoản / email để đặt lại mật khẩu mới cho tài khoản</p>
        </div>



        <form action="<?= url('/auth/forgot-password') ?>" method="POST" id="form-forgot-password">
            <div class="form-group">
                <label class="form-label" for="identity">Tên đăng nhập hoặc Email đăng ký <span style="color: var(--danger);">*</span></label>
                <input type="text" id="identity" name="identity" class="form-control" placeholder="admin hoặc email@domain.com" required value="<?= htmlspecialchars($_POST['identity'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="new_password">Mật khẩu mới muốn đặt <span style="color: var(--danger);">*</span></label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Tối thiểu 6 ký tự" minlength="6" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Xác nhận lại mật khẩu mới <span style="color: var(--danger);">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" minlength="6" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; padding: 0.8rem; font-weight: 700;">
                Đặt Lại Mật Khẩu
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; border-top: 1px solid var(--slate-100); padding-top: 1.25rem;">
            <p style="font-size: 0.9rem; color: var(--slate-500);">
                Bạn đã nhớ mật khẩu? <a href="<?= url('/auth/login') ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">Đăng nhập ngay</a>
            </p>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/main_layout.php';
?>
