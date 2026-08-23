<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'JobLink - Quản Trị Người Dùng' ?></title>
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
        $userSession = $_SESSION['user'] ?? [];
        $uRole = $userSession['role_code'] ?? '';
        $userDisplayName = $userSession['display_name'] ?? ($userSession['username'] ?? 'Người dùng');
        $userUsername = $userSession['username'] ?? 'user';
        $userEmail = $userSession['email'] ?? '';

        $dashUrl = url('/seeker/dashboard');
        $roleBadge = 'Người Lao Động';
        if ($uRole === 'EMPLOYER_ADMIN') {
            $dashUrl = url('/employer/dashboard');
            $roleBadge = 'Nhà Tuyển Dụng';
        } elseif ($uRole === 'INTERMEDIARY') {
            $dashUrl = url('/intermediary/dashboard');
            $roleBadge = 'Người Trung Gian';
        }

        // Lấy thông tin hồ sơ chi tiết để hiển thị trên Profile Card Dropdown
        $accId = $userSession['account_id'] ?? 0;
        $profile = $userSession['profile'] ?? null;
        if (!$profile && $accId > 0) {
            if ($uRole === 'JOB_SEEKER' && class_exists('UserSeeker')) {
                $profile = UserSeeker::findByAccountId($accId);
                $_SESSION['user']['profile'] = $profile;
            } elseif ($uRole === 'EMPLOYER_ADMIN' && class_exists('UserEmployer')) {
                $profile = UserEmployer::findByAccountId($accId);
                $_SESSION['user']['profile'] = $profile;
            }
        }
    ?>

    <header class="navbar navbar-light">
        <div class="nav-container">
            <a href="<?= $dashUrl ?>" class="logo">
                <span>JobLink</span>
                <span class="logo-badge"><?= $roleBadge ?></span>
            </a>
            <div class="nav-actions">
                <?php
                    $userAvatar = getAvatarUrl($profile['avatar_url'] ?? ($userSession['avatar_url'] ?? ''), $uRole, $profile['gender'] ?? '');
                ?>
                <!-- Nút Avatar Tròn Phong Cách Facebook -->
                <div class="user-dropdown-container">
                    <button type="button" class="user-dropdown-trigger fb-avatar-trigger" title="<?= htmlspecialchars($userDisplayName) ?>">
                        <img src="<?= htmlspecialchars($userAvatar) ?>" alt="avatar" class="fb-nav-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                        <span class="fb-chevron-badge"><?= icon('chevron_down', '', 9) ?></span>
                    </button>

                    <div class="user-dropdown-menu fb-dropdown-menu">
                        <!-- 1. HỘP PROFILE TRANG CÁ NHÂN / BẢNG ĐIỀU KHIỂN (FB STYLE) -->
                        <a href="<?= $dashUrl ?>" class="fb-profile-card-box">
                            <div class="fb-card-user">
                                <img src="<?= htmlspecialchars($userAvatar) ?>" alt="avatar" class="fb-card-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                                <div class="fb-card-meta">
                                    <div class="fb-card-name"><?= htmlspecialchars($userDisplayName) ?></div>
                                    <div class="fb-card-role"><?= htmlspecialchars($roleBadge) ?> • @<?= htmlspecialchars($userUsername) ?></div>
                                </div>
                            </div>
                            <div class="fb-view-profile-btn">
                                <?= icon('user', '', 15) ?>
                                <span>Xem Bảng Điều Khiển & Trang Cá Nhân</span>
                            </div>
                        </a>

                        <div class="fb-divider"></div>

                        <!-- 2. DANH SÁCH MENU ĐIỀU HƯỚNG FB STYLE -->
                        <div class="fb-menu-list">
                            <?php if ($uRole === 'JOB_SEEKER'): ?>
                                <a href="<?= url('/seeker/profile') ?>" class="fb-menu-item">
                                    <div class="fb-item-icon"><?= icon('user', '', 17) ?></div>
                                    <div class="fb-item-content">
                                        <div class="fb-item-title">Hồ sơ năng lực & Điểm uy tín</div>
                                        <div class="fb-item-desc">Kỹ năng, vị trí & Trust Score</div>
                                    </div>
                                    <div class="fb-item-arrow"><?= icon('chevron_right', '', 14) ?></div>
                                </a>
                                <a href="<?= url('/seeker/settings') ?>" class="fb-menu-item">
                                    <div class="fb-item-icon"><?= icon('settings', '', 17) ?></div>
                                    <div class="fb-item-content">
                                        <div class="fb-item-title">Cài đặt & Cam kết việc làm</div>
                                        <div class="fb-item-desc">Ký cam kết không bùng ca & Gmail</div>
                                    </div>
                                    <div class="fb-item-arrow"><?= icon('chevron_right', '', 14) ?></div>
                                </a>
                            <?php elseif ($uRole === 'EMPLOYER_ADMIN'): ?>
                                <a href="<?= url('/employer/company-profile') ?>" class="fb-menu-item">
                                    <div class="fb-item-icon"><?= icon('building', '', 17) ?></div>
                                    <div class="fb-item-content">
                                        <div class="fb-item-title">Hồ sơ doanh nghiệp & GPKD</div>
                                        <div class="fb-item-desc">Thông tin công ty và người đại diện</div>
                                    </div>
                                    <div class="fb-item-arrow"><?= icon('chevron_right', '', 14) ?></div>
                                </a>
                                <a href="<?= url('/employer/settings?tab=cert') ?>" class="fb-menu-item">
                                    <div class="fb-item-icon"><?= icon('certificate', '', 17) ?></div>
                                    <div class="fb-item-content">
                                        <div class="fb-item-title">Chữ ký số & Hợp đồng</div>
                                        <div class="fb-item-desc">Xác thực CA và hợp đồng điện tử</div>
                                    </div>
                                    <div class="fb-item-arrow"><?= icon('chevron_right', '', 14) ?></div>
                                </a>
                                <a href="<?= url('/employer/settings?tab=auth') ?>" class="fb-menu-item">
                                    <div class="fb-item-icon"><?= icon('handshake', '', 17) ?></div>
                                    <div class="fb-item-content">
                                        <div class="fb-item-title">Ủy quyền Người trung gian</div>
                                        <div class="fb-item-desc">Văn bản ủy quyền tuyển dụng pháp lý</div>
                                    </div>
                                    <div class="fb-item-arrow"><?= icon('chevron_right', '', 14) ?></div>
                                </a>
                                <a href="<?= url('/employer/settings?tab=mail') ?>" class="fb-menu-item">
                                    <div class="fb-item-icon"><?= icon('mail', '', 17) ?></div>
                                    <div class="fb-item-content">
                                        <div class="fb-item-title">Cài đặt & Thông báo Gmail</div>
                                        <div class="fb-item-desc">Đồng bộ email nhận hồ sơ ứng viên</div>
                                    </div>
                                    <div class="fb-item-arrow"><?= icon('chevron_right', '', 14) ?></div>
                                </a>
                            <?php elseif ($uRole === 'INTERMEDIARY'): ?>
                                <a href="<?= url('/intermediary/settings?tab=contracts') ?>" class="fb-menu-item">
                                    <div class="fb-item-icon"><?= icon('handshake', '', 17) ?></div>
                                    <div class="fb-item-content">
                                        <div class="fb-item-title">Hợp đồng & Văn bản ủy quyền</div>
                                        <div class="fb-item-desc">Quản lý giấy ủy quyền tuyển dụng</div>
                                    </div>
                                    <div class="fb-item-arrow"><?= icon('chevron_right', '', 14) ?></div>
                                </a>
                                <a href="<?= url('/intermediary/settings?tab=mail') ?>" class="fb-menu-item">
                                    <div class="fb-item-icon"><?= icon('mail', '', 17) ?></div>
                                    <div class="fb-item-content">
                                        <div class="fb-item-title">Cài đặt & Thông báo Gmail</div>
                                        <div class="fb-item-desc">Cấu hình nhận email ứng tuyển</div>
                                    </div>
                                    <div class="fb-item-arrow"><?= icon('chevron_right', '', 14) ?></div>
                                </a>
                            <?php endif; ?>

                            <a href="<?= url('/auth/change-password') ?>" class="fb-menu-item">
                                <div class="fb-item-icon"><?= icon('lock', '', 17) ?></div>
                                <div class="fb-item-content">
                                    <div class="fb-item-title">Đổi mật khẩu & Bảo mật</div>
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
                            Quyền riêng tư · Điều khoản · JobLink © <?= date('Y') ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </header>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <?php $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'; ?>
            
            <?php if ($uRole === 'JOB_SEEKER'): ?>

                <!-- ========================================================= -->
                <!-- SIDEBAR NGƯỜI LAO ĐỘNG (KHÔNG TRÙNG VỚI MENU AVATAR)    -->
                <!-- ========================================================= -->
                
                <div class="sidebar-heading">Khám Phá Việc Làm</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/seeker/dashboard') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/dashboard') !== false ? 'active' : '' ?>">
                        <?= icon('dashboard', '', 17) ?> <span>Bảng Điều Khiển</span>
                    </a></li>
                    <li><a href="<?= url('/jobs') ?>" class="sidebar-link <?= (strpos($currentUri, '/jobs') !== false && strpos($currentUri, 'ai_recommend') === false) ? 'active' : '' ?>">
                        <?= icon('search', '', 17) ?> <span>Tìm Kiếm Việc Làm</span>
                    </a></li>
                    <li><a href="<?= url('/jobs?ai_recommend=1') ?>" class="sidebar-link <?= (isset($_GET['ai_recommend']) && $_GET['ai_recommend'] == '1') ? 'active' : '' ?>">
                        <?= icon('zap', '', 17) ?> <span>Việc Làm Gợi Ý AI</span>
                    </a></li>
                    <li><a href="<?= url('/seeker/saved-jobs') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/saved-jobs') !== false ? 'active' : '' ?>">
                        <?= icon('saved', '', 17) ?> <span>Việc Làm Đã Lưu</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading">Tiến Độ Tuyển Dụng</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/seeker/applied-jobs') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/applied-jobs') !== false ? 'active' : '' ?>">
                        <?= icon('applied', '', 17) ?> <span>Hồ Sơ Đã Ứng Tuyển</span>
                    </a></li>
                    <li><a href="<?= url('/seeker/meetings') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/meetings') !== false ? 'active' : '' ?>">
                        <?= icon('meetings', '', 17) ?> <span>Lịch Hẹn & Phỏng Vấn</span>
                    </a></li>
                    <li><a href="<?= url('/seeker/messages') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/messages') !== false ? 'active' : '' ?>">
                        <?= icon('chat', '', 17) ?> <span>Tin Nhắn Trao Đổi</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading">Đánh Giá & Phản Hồi</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/seeker/reviews') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/reviews') !== false ? 'active' : '' ?>">
                        <?= icon('reviews', '', 17) ?> <span>Đánh Giá Nơi Làm Việc</span>
                    </a></li>
                </ul>

            <?php elseif ($uRole === 'INTERMEDIARY'): ?>

                <!-- ========================================================= -->
                <!-- SIDEBAR NGƯỜI TRUNG GIAN                                  -->
                <!-- ========================================================= -->
                
                <div class="sidebar-heading">Tổng Quan</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/intermediary/dashboard') ?>" class="sidebar-link <?= strpos($currentUri, '/intermediary/dashboard') !== false ? 'active' : '' ?>">
                        <?= icon('dashboard', '', 17) ?> <span>Bảng Điều Khiển</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading">Việc Làm & Khách Hàng</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/jobs') ?>" class="sidebar-link <?= strpos($currentUri, '/jobs') !== false ? 'active' : '' ?>">
                        <?= icon('search', '', 17) ?> <span>Tra Cứu Việc Làm Sàn</span>
                    </a></li>
                    <li><a href="<?= url('/seeker/messages') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/messages') !== false ? 'active' : '' ?>">
                        <?= icon('chat', '', 17) ?> <span>Tin Nhắn Trao Đổi</span>
                    </a></li>
                </ul>

            <?php else: ?>
                <!-- ========================================================= -->
                <!-- SIDEBAR NHÀ TUYỂN DỤNG (KHÔNG TRÙNG VỚI MENU AVATAR)      -->
                <!-- ========================================================= -->
                
                <div class="sidebar-heading">Tổng Quan</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/employer/dashboard') ?>" class="sidebar-link <?= strpos($currentUri, '/employer/dashboard') !== false ? 'active' : '' ?>">
                        <?= icon('dashboard', '', 17) ?> <span>Tổng Quan Tuyển Dụng</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading">Quản Lý Tin Tuyển Dụng</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/employer/jobs/create') ?>" class="sidebar-link <?= strpos($currentUri, '/employer/jobs/create') !== false ? 'active' : '' ?>" style="color: #0d9488; font-weight: 700;">
                        <?= icon('posts', '', 17) ?> <span>+ Đăng Tin Mới</span>
                    </a></li>
                    <li><a href="<?= url('/employer/jobs') ?>" class="sidebar-link <?= (strpos($currentUri, '/employer/jobs') !== false && strpos($currentUri, '/create') === false) ? 'active' : '' ?>">
                        <?= icon('list', '', 17) ?> <span>Danh Sách Tin Đăng</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading">Tiếp Nhận & Phỏng Vấn</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/employer/applications') ?>" class="sidebar-link <?= strpos($currentUri, '/employer/applications') !== false ? 'active' : '' ?>">
                        <?= icon('applications', '', 17) ?> <span>Hồ Sơ Người Lao Động</span>
                    </a></li>
                    <li><a href="<?= url('/employer/meetings') ?>" class="sidebar-link <?= strpos($currentUri, '/employer/meetings') !== false ? 'active' : '' ?>">
                        <?= icon('meetings', '', 17) ?> <span>Lịch Hẹn & Phỏng Vấn</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading">Trao Đổi & Đánh Giá 2 Chiều</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/employer/messages') ?>" class="sidebar-link <?= strpos($currentUri, '/employer/messages') !== false ? 'active' : '' ?>">
                        <?= icon('chat', '', 17) ?> <span>Tin Nhắn Trao Đổi</span>
                    </a></li>
                    <li><a href="<?= url('/employer/candidate-reviews') ?>" class="sidebar-link <?= strpos($currentUri, '/employer/candidate-reviews') !== false ? 'active' : '' ?>">
                        <?= icon('reviews', '', 17) ?> <span>Đánh Giá Người Lao Động</span>
                    </a></li>
                </ul>
            <?php endif; ?>
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
