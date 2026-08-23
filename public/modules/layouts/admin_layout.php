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
    <script>window.APP_URL = '<?= url('') ?>';</script>
</head>
<body>
    <?php
        $adminUser = $_SESSION['user'] ?? [];
        $adminDisplayName = $adminUser['display_name'] ?? ($adminUser['username'] ?? 'Admin');
        $adminUsername = $adminUser['username'] ?? 'admin';
        $adminRoleName = $adminUser['role_name'] ?? 'Ban Quản Trị';
        $adminEmail = $adminUser['email'] ?? 'admin@joblink.vn';
    ?>
    <header class="navbar navbar-admin">
        <div class="nav-container">
            <a href="<?= url('/admin/dashboard') ?>" class="logo" style="color: #ffffff !important;">
                <span>JobLink Admin</span>
                <span class="logo-badge" style="background: var(--primary); color: #ffffff; font-size: 0.8rem;"><?= htmlspecialchars($adminRoleName) ?></span>
            </a>
            <div class="nav-actions">
                <?php
                    $adminAvatar = getAvatarUrl($adminUser['avatar_url'] ?? '', $adminUser['role_code'] ?? 'SUPER_ADMIN');
                ?>
                <!-- Cụm Icon Tin Nhắn & Thông Báo -->
                <?= renderHeaderIcons($adminUser) ?>

                <!-- Nút Avatar Tròn Phong Cách Facebook -->
                <div class="user-dropdown-container">
                    <button type="button" class="user-dropdown-trigger fb-avatar-trigger" title="<?= htmlspecialchars($adminDisplayName) ?>">
                        <img src="<?= htmlspecialchars($adminAvatar) ?>" alt="avatar" class="fb-nav-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                        <span class="fb-chevron-badge"><?= icon('chevron_down', '', 9) ?></span>
                    </button>

                    <div class="user-dropdown-menu fb-dropdown-menu">
                        <!-- 1. HỘP PROFILE TRANG CÁ NHÂN / BẢNG ĐIỀU KHIỂN -->
                        <a href="<?= url('/admin/dashboard') ?>" class="fb-profile-card-box">
                            <div class="fb-card-user">
                                <img src="<?= htmlspecialchars($adminAvatar) ?>" alt="avatar" class="fb-card-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                                <div class="fb-card-meta">
                                    <div class="fb-card-name"><?= htmlspecialchars($adminDisplayName) ?></div>
                                    <div class="fb-card-role"><?= htmlspecialchars($adminRoleName) ?> • @<?= htmlspecialchars($adminUsername) ?></div>
                                </div>
                            </div>
                            <div class="fb-view-profile-btn">
                                <?= icon('dashboard', '', 15) ?>
                                <span>Xem Bảng Điều Khiển Quản Trị</span>
                            </div>
                        </a>

                                         <!-- 2. DANH SÁCH MENU ĐIỀU HƯỚNG FB STYLE -->
                        <div class="fb-menu-list">
                            <a href="<?= url('/admin/settings') ?>" class="fb-menu-item">
                                <div class="fb-item-icon"><?= icon('settings', '', 17) ?></div>
                                <div class="fb-item-content">
                                    <div class="fb-item-title">Cài đặt hệ thống & Root CA</div>
                                    <div class="fb-item-desc">Cấu hình máy chủ, Gmail & Chữ ký số</div>
                                </div>
                                <div class="fb-item-arrow"><?= icon('chevron_right', '', 14) ?></div>
                            </a>

                            <a href="<?= url('/auth/change-password') ?>" class="fb-menu-item">
                                <div class="fb-item-icon"><?= icon('lock', '', 17) ?></div>
                                <div class="fb-item-content">
                                    <div class="fb-item-title">Đổi mật khẩu Admin</div>
                                </div>
                                <div class="fb-item-arrow"><?= icon('chevron_right', '', 14) ?></div>
                            </a>

                            <a href="<?= url('/auth/logout') ?>" class="fb-menu-item fb-item-danger">
                                <div class="fb-item-icon"><?= icon('logout', '', 17) ?></div>
                                <div class="fb-item-content">
                                    <div class="fb-item-title">Đăng xuất</div>
                                </div>
                                <div class="fb-item-arrow"><?= icon('chevron_right', '', 14) ?></div>
                            </a>
                        </div>

                        <!-- 3. FOOTER -->
                        <div class="fb-dropdown-footer">
                            Quyền riêng tư · Điều khoản · JobLink Admin © <?= date('Y') ?>
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

            <div class="sidebar-heading">Trí Tuệ Nhân Tạo (AI)</div>
            <ul class="sidebar-menu">
                <li><a href="<?= url('/admin/ai-analytics') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/ai-analytics') !== false ? 'active' : '' ?>">
                    <?= icon('reports', '', 17) ?> <span>Báo Cáo & Huấn Luyện AI</span>
                </a></li>
                <li><a href="<?= url('/admin/ai-settings') ?>" class="sidebar-link <?= strpos($currentUri, '/admin/ai-settings') !== false ? 'active' : '' ?>">
                    <?= icon('zap', '', 17) ?> <span>Cấu Hình Model Gemini</span>
                </a></li>
            </ul>
        </aside>

        <main class="main-content">
            <?= $content ?? '' ?>
        </main>
    </div>
    <script src="<?= asset('js/dialog.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script>
        window.APP_URL = '<?= url('') ?>';
        window.JOBY_GREETING = <?= json_encode(getAiGreeting(), JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="<?= asset('js/ai_chatbox.js') ?>"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ((array)$extraJs as $jsFile): ?>
            <script src="<?= asset($jsFile) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <?= class_exists('DialogHelper') ? DialogHelper::renderFlashScript() : '' ?>
</body>
</html>
