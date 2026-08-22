<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'JobLink Admin - Quản Trị Hệ Thống' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/dialog.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ai_chatbox.css') ?>">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ((array)$extraCss as $cssFile): ?>
            <link rel="stylesheet" href="<?= asset($cssFile) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="navbar navbar-admin">
        <div class="nav-container">
            <a href="<?= url('/admin/dashboard') ?>" class="logo" style="color: #ffffff !important;">
                <span>JobLink Admin</span>
                <span class="logo-badge" style="background: var(--primary); color: #ffffff; font-size: 0.8rem;"><?= htmlspecialchars($_SESSION['user']['role_name'] ?? 'Admin') ?></span>
            </a>
            <div class="nav-actions">
                <!-- Dropdown Thông Tin Quản Trị Viên (Profile Card chi tiết khi click) -->
                <div class="user-dropdown-container">
                    <div class="user-dropdown-trigger">
                        <span><?= icon('user', '', 16) ?> Xin chào, <strong><?= htmlspecialchars($_SESSION['user']['display_name'] ?? $_SESSION['user']['username']) ?></strong></span>
                        <span style="margin-left: 2px;"><?= icon('chevron_down', '', 13) ?></span>
                    </div>

                    <div class="user-dropdown-menu user-profile-card-dropdown">
                        <!-- 1. HEADER CARD: AVATAR & TÊN ADMIN -->
                        <div class="user-card-header">
                            <div class="user-card-avatar">
                                <?php $initial = mb_substr($_SESSION['user']['display_name'] ?? $_SESSION['user']['username'], 0, 1); ?>
                                <div class="avatar-circle" style="background: #0f766e;"><?= htmlspecialchars(mb_strtoupper($initial)) ?></div>
                            </div>
                            <div class="user-card-meta">
                                <h4 class="user-card-name"><?= htmlspecialchars($_SESSION['user']['display_name'] ?? $_SESSION['user']['username']) ?></h4>
                                <div class="user-card-username">@<?= htmlspecialchars($_SESSION['user']['username']) ?></div>
                                <span class="user-card-badge super_admin"><?= htmlspecialchars($_SESSION['user']['role_name'] ?? 'Ban Quản Trị') ?></span>
                            </div>
                        </div>

                        <!-- 2. BODY CARD: THÔNG TIN QUẢN TRỊ -->
                        <div class="user-card-body">
                            <div class="user-card-row">
                                <span class="row-label">🛡️ Quyền hạn:</span>
                                <span class="row-value"><strong style="color: #0d9488;">Toàn quyền hệ thống</strong></span>
                            </div>
                            <div class="user-card-row">
                                <span class="row-label">✉️ Email BQT:</span>
                                <span class="row-value"><?= htmlspecialchars($_SESSION['user']['email'] ?? 'admin.dai@joblink.io.vn') ?></span>
                            </div>
                            <div class="user-card-actions">
                                <a href="<?= url('/admin/settings') ?>" class="btn-profile-primary">
                                    <?= icon('settings', '', 14) ?> <span>Thiết Lập Hệ Thống & Mail</span>
                                </a>
                            </div>
                        </div>

                        <!-- 3. FOOTER CARD: ĐỔI/QUÊN MẬT KHẨU & ĐĂNG XUẤT -->
                        <div class="user-card-footer">
                            <a href="<?= url('/auth/change-password') ?>" class="dropdown-item">
                                <?= icon('lock', '', 15) ?> <span>Đổi Mật Khẩu</span>
                            </a>
                            <a href="<?= url('/auth/forgot-password') ?>" class="dropdown-item">
                                <?= icon('key', '', 15) ?> <span>Quên Mật Khẩu</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= url('/auth/logout') ?>" class="dropdown-item dropdown-item-danger">
                                <?= icon('logout', '', 15) ?> <span>Đăng Xuất</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <?php $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'; ?>
            
            <div class="sidebar-heading">Báo Cáo & Thống Kê</div>
            <ul class="sidebar-menu">
                <li><a href="<?= url('/admin/dashboard') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/dashboard') !== false ? 'active' : '' ?>">
                    <?= icon('dashboard', '', 17) ?> <span>Bảng Điều Khiển</span>
                </a></li>
            </ul>

            <div class="sidebar-heading">Chữ Ký Số & Pháp Lý DN</div>
            <ul class="sidebar-menu">
                <li><a href="<?= url('/admin/contracts') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/contracts') !== false ? 'active' : '' ?>">
                    <?= icon('file_signature', '', 17) ?> <span>Hợp Đồng Điện Tử Sàn</span>
                </a></li>
                <li><a href="<?= url('/admin/authorizations') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/authorizations') !== false ? 'active' : '' ?>">
                    <?= icon('handshake', '', 17) ?> <span>Thẩm Định Ủy Quyền (TG)</span>
                </a></li>
                <li><a href="<?= url('/admin/employers') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/employers') !== false ? 'active' : '' ?>">
                    <?= icon('employers', '', 17) ?> <span>Xác Minh Doanh Nghiệp</span>
                </a></li>
            </ul>

            <div class="sidebar-heading">Kiểm Duyệt & Vận Hành</div>
            <ul class="sidebar-menu">
                <li><a href="<?= url('/admin/job-posts') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/job-posts') !== false ? 'active' : '' ?>">
                    <?= icon('posts', '', 17) ?> <span>Duyệt Tin Tuyển Dụng</span>
                </a></li>
                <li><a href="<?= url('/admin/review-criteria') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/review-criteria') !== false ? 'active' : '' ?>">
                    <?= icon('criteria', '', 17) ?> <span>Tiêu Chí Đánh Giá 2 Chiều</span>
                </a></li>
                <li><a href="<?= url('/admin/reviews') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/reviews') !== false ? 'active' : '' ?>">
                    <?= icon('reviews', '', 17) ?> <span>Kiểm Duyệt Đánh Giá</span>
                </a></li>
            </ul>

            <div class="sidebar-heading">Quản Trị Người Dùng</div>
            <ul class="sidebar-menu">
                <li><a href="<?= url('/admin/accounts') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/accounts') !== false ? 'active' : '' ?>">
                    <?= icon('accounts', '', 17) ?> <span>Tài Khoản Hệ Thống</span>
                </a></li>
                <li><a href="<?= url('/admin/staffs') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/staffs') !== false ? 'active' : '' ?>">
                    <?= icon('staffs', '', 17) ?> <span>Nhân Viên Nội Bộ</span>
                </a></li>
            </ul>

            <div class="sidebar-heading">CSKH & Thông Báo</div>
            <ul class="sidebar-menu">
                <li><a href="<?= url('/admin/messages') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/messages') !== false ? 'active' : '' ?>">
                    <?= icon('messages', '', 17) ?> <span>Gửi Thông Báo Hệ Thống</span>
                </a></li>
                <li><a href="<?= url('/admin/support') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/support') !== false ? 'active' : '' ?>">
                    <?= icon('support', '', 17) ?> <span>CSKH & Khiếu Nại</span>
                </a></li>
            </ul>

            <div class="sidebar-heading">Cấu Hình & Tích Hợp</div>
            <ul class="sidebar-menu">
                <li><a href="<?= url('/admin/settings') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/settings') !== false ? 'active' : '' ?>">
                    <?= icon('sliders', '', 17) ?> <span>Thiết Lập Hệ Thống & Mail</span>
                </a></li>
                <li><a href="<?= url('/admin/ai-settings') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/ai-settings') !== false ? 'active' : '' ?>">
                    <?= icon('zap', '', 17) ?> <span>Cấu Hình AI (Gemini)</span>
                </a></li>
            </ul>
        </aside>

        <main class="main-content">
            <?= $content ?? '' ?>
        </main>
    </div>
    <script src="<?= asset('js/dialog.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/ai_chatbox.js') ?>"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ((array)$extraJs as $jsFile): ?>
            <script src="<?= asset($jsFile) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <?= class_exists('DialogHelper') ? DialogHelper::renderFlashScript() : '' ?>
</body>
</html>
