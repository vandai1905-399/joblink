<?php 
$extraCss = 'css/auth/login.css';
$extraJs = 'js/auth/auth.js';
$rememberedUsername = $rememberedUsername ?? ($_COOKIE['joblink_remember_username'] ?? '');
$isRemembered = isset($isRemembered) ? $isRemembered : (!empty($rememberedUsername));
ob_start(); 
?>

<div class="container" style="max-width: 480px; padding-top: 3rem; padding-bottom: 4rem;">
    <div class="card auth-card" style="padding: 2.25rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Đăng Nhập JobLink</h1>
            <p style="font-size: 0.9rem; color: var(--slate-500);">Truy cập bảng điều khiển việc làm & tuyển dụng</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.25rem; font-size: 0.9rem; border-radius: 8px; padding: 0.75rem 1rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form id="form-login" action="<?= url('/auth/login') ?>" method="POST" autocomplete="on">
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="username" class="form-label" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.4rem; color: #334155;">
                    Tên đăng nhập hoặc Email
                </label>
                <div class="input-with-icon">
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           required 
                           placeholder="Nhập tên đăng nhập hoặc số điện thoại" 
                           tabindex="1" 
                           autofocus
                           autocomplete="username"
                           autocapitalize="none"
                           spellcheck="false"
                           value="<?= htmlspecialchars($rememberedUsername) ?>"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label for="password" class="form-label" style="font-weight: 600; font-size: 0.9rem; margin-bottom: 0; color: #334155;">
                        Mật khẩu
                    </label>
                    <a href="<?= url('/auth/forgot-password') ?>" style="font-size: 0.85rem; color: var(--primary, #0d9488); font-weight: 600; text-decoration: none;" tabindex="5">
                        Quên mật khẩu?
                    </a>
                </div>
                <div class="password-input-wrapper" style="position: relative; display: flex; align-items: center;">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           required 
                           placeholder="Nhập mật khẩu" 
                           tabindex="2"
                           autocomplete="current-password"
                           style="width: 100%; padding: 0.75rem 2.85rem 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
                    <button type="button" 
                            id="toggle-password-btn" 
                            class="btn-toggle-password" 
                            title="Hiện hoặc ẩn mật khẩu" 
                            aria-label="Hiện hoặc ẩn mật khẩu"
                            tabindex="-1">
                        <svg class="eye-icon eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-icon eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" y1="2" x2="22" y2="22"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- TÙY CHỌN LƯU MẬT KHẨU TRÊN TRÌNH DUYỆT / GHI NHỚ ĐĂNG NHẬP -->
            <div class="remember-group">
                <label class="custom-checkbox-label" for="remember_me">
                    <input type="checkbox" name="remember_me" id="remember_me" value="1" <?= $isRemembered ? 'checked' : '' ?>>
                    <span class="custom-checkbox-box">
                        <svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </span>
                    <span class="custom-checkbox-text">
                        <span class="checkbox-title">Lưu mật khẩu trên trình duyệt</span>
                        <span class="checkbox-subtitle">Tự động điền thông tin đăng nhập cho các lần truy cập sau</span>
                    </span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem; margin-top: 0.25rem; font-weight: 700; border-radius: 8px;" tabindex="3">
                Đăng Nhập Ngay
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--slate-600); line-height: 1.7;">
            <div>Nếu chưa có tài khoản?</div>
            <div style="margin-top: 0.25rem;">
                <a href="<?= url('/auth/register') ?>" style="color: var(--primary, #0d9488); font-weight: 700;" tabindex="4">Đăng ký tại đây</a>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/main_layout.php';
?>
