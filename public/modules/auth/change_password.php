<?php 
$extraCss = 'css/auth/login.css';
$extraJs = 'js/auth/auth.js';
ob_start(); 
?>

<div class="container" style="max-width: 500px; padding-top: 2.5rem; padding-bottom: 3.5rem;">
    <div class="card" style="padding: 2.25rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="margin-bottom: 0.75rem; color: var(--primary);">
                <?= icon('lock', '', 40) ?>
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Đổi Mật Khẩu</h1>
            <p style="color: var(--slate-500); font-size: 0.92rem;">Cập nhật mật khẩu bảo vệ tài khoản JobLink của bạn</p>
        </div>



        <form action="<?= url('/auth/change-password') ?>" method="POST" id="form-change-password">
            <div class="form-group">
                <label class="form-label" for="current_password">Mật khẩu hiện tại <span style="color: var(--danger);">*</span></label>
                <input type="password" id="current_password" name="current_password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="new_password">Mật khẩu mới <span style="color: var(--danger);">*</span></label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Tối thiểu 6 ký tự" minlength="6" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Xác nhận mật khẩu mới <span style="color: var(--danger);">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" minlength="6" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; padding: 0.8rem; font-weight: 700;">
                Cập Nhật Mật Khẩu
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; border-top: 1px solid var(--slate-100); padding-top: 1.25rem;">
            <a href="<?= url('/') ?>" style="color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                ← Quay lại Bảng điều khiển
            </a>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
$role = $_SESSION['user']['role_code'] ?? '';
if (in_array($role, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
    require_once __DIR__ . '/../layouts/admin_layout.php';
} elseif (in_array($role, ['EMPLOYER_ADMIN', 'JOB_SEEKER'])) {
    require_once __DIR__ . '/../layouts/user_layout.php';
} else {
    require_once __DIR__ . '/../layouts/main_layout.php';
}
?>
