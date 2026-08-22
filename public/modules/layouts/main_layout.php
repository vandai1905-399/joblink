<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'JobLink - Cổng Kết Nối Việc Làm') ?></title>
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
    <header class="navbar">
        <div class="nav-container">
            <?php 
                $userRole = $_SESSION['user']['role_code'] ?? '';
                $dashUrl = url('/');
                if ($userRole === 'JOB_SEEKER') $dashUrl = url('/seeker/dashboard');
                elseif ($userRole === 'EMPLOYER_ADMIN') $dashUrl = url('/employer/dashboard');
                elseif ($userRole === 'INTERMEDIARY') $dashUrl = url('/intermediary/dashboard');
                elseif (in_array($userRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) $dashUrl = url('/admin/dashboard');
            ?>
            <a href="<?= isset($_SESSION['user']) ? $dashUrl : url('/') ?>" class="logo">
                <span>JobLink</span>
                <span class="logo-badge">Kết Nối Việc Làm</span>
            </a>
            <ul class="nav-links">
                <?php if (!isset($_SESSION['user'])): ?>
                    <li><a href="<?= url('/') ?>" class="nav-link">Trang Chủ</a></li>
                <?php endif; ?>
                <li><a href="<?= url('/jobs') ?>" class="nav-link">Tìm Việc Làm</a></li>
                <li><a href="<?= url('/employers') ?>" class="nav-link">Nhà Tuyển Dụng</a></li>
                <li><a href="<?= url('/about') ?>" class="nav-link">Giới Thiệu</a></li>
            </ul>
            <div class="nav-actions">
                <?php if (isset($_SESSION['user'])): ?>
                    <!-- Dropdown Thông Tin Cá Nhân (Bấm vào hiển thị Profile Card chi tiết) -->
                    <div class="user-dropdown-container">
                        <div class="user-dropdown-trigger" style="background:var(--slate-100); border-color:var(--slate-200); color:var(--slate-800);">
                            <span><?= icon('user', '', 16) ?> Xin chào, <strong><?= htmlspecialchars($_SESSION['user']['display_name'] ?? $_SESSION['user']['username']) ?></strong></span>
                            <span style="margin-left: 2px;"><?= icon('chevron_down', '', 13) ?></span>
                        </div>

                        <div class="user-dropdown-menu user-profile-card-dropdown">
                            <!-- 1. HEADER CARD: AVATAR, TÊN, USERNAME & ROLE -->
                            <div class="user-card-header">
                                <div class="user-card-avatar">
                                    <?php 
                                        $avatarImg = $profile['avatar_url'] ?? '';
                                        $initial = mb_substr($_SESSION['user']['display_name'] ?? $_SESSION['user']['username'], 0, 1);
                                    ?>
                                    <?php if (!empty($avatarImg)): ?>
                                        <img src="<?= htmlspecialchars($avatarImg) ?>" alt="avatar">
                                    <?php else: ?>
                                        <div class="avatar-circle"><?= htmlspecialchars(mb_strtoupper($initial)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="user-card-meta">
                                    <h4 class="user-card-name"><?= htmlspecialchars($_SESSION['user']['display_name'] ?? $_SESSION['user']['username']) ?></h4>
                                    <div class="user-card-username">@<?= htmlspecialchars($_SESSION['user']['username']) ?></div>
                                    <span class="user-card-badge <?= strtolower($userRole) ?>">
                                        <?= ($userRole === 'EMPLOYER_ADMIN') ? 'Nhà Tuyển Dụng' : (($userRole === 'INTERMEDIARY') ? 'Người Trung Gian' : (($userRole === 'JOB_SEEKER') ? 'Người Lao Động' : 'Ban Quản Trị')) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- 2. BODY CARD: THÔNG TIN CHI TIẾT -->
                            <div class="user-card-body">
                                <div class="user-card-actions" style="margin-top: 0;">
                                    <a href="<?= $dashUrl ?>" class="btn-profile-primary" style="margin-bottom: 0.5rem;">
                                        <?= icon('dashboard', '', 14) ?> <span>Truy Cập Bảng Điều Khiển</span>
                                    </a>
                                </div>
                                <?php if ($userRole === 'JOB_SEEKER'): ?>
                                    <div class="user-card-actions">
                                        <a href="<?= url('/seeker/profile') ?>" class="btn-profile-primary">
                                            <?= icon('user', '', 14) ?> <span>Hồ Sơ Năng Lực Cá Nhân</span>
                                        </a>
                                    </div>
                                <?php elseif ($userRole === 'EMPLOYER_ADMIN'): ?>
                                    <div class="user-card-actions">
                                        <a href="<?= url('/employer/company-profile') ?>" class="btn-profile-primary">
                                            <?= icon('employers', '', 14) ?> <span>Hồ Sơ Doanh Nghiệp / GPKD</span>
                                        </a>
                                    </div>
                                <?php elseif ($userRole === 'INTERMEDIARY'): ?>
                                    <div class="user-card-actions">
                                        <a href="<?= url('/intermediary/settings') ?>" class="btn-profile-primary">
                                            <?= icon('handshake', '', 14) ?> <span>Hồ Sơ & Thiết Lập Đại Lý</span>
                                        </a>
                                    </div>
                                <?php elseif (in_array($userRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])): ?>
                                    <div class="user-card-actions">
                                        <a href="<?= url('/admin/settings') ?>" class="btn-profile-primary">
                                            <?= icon('settings', '', 14) ?> <span>Thiết Lập Hệ Thống & Mail</span>
                                        </a>
                                    </div>
                                <?php endif; ?>
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
                <?php else: ?>
                    <a href="<?= url('/auth/login') ?>" class="btn btn-outline btn-sm">Đăng Nhập</a>
                    <a href="<?= url('/auth/register') ?>" class="btn btn-primary btn-sm">Đăng Ký</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <?= $content ?? '' ?>
    </main>

    <footer class="footer">
        <div class="footer-grid">
            <div>
                <a href="<?= url('/') ?>" class="logo" style="color:#fff; margin-bottom: 1rem; display: inline-flex;">
                    JobLink
                </a>
                <p style="font-size: 0.9rem; line-height: 1.6;">
                    Nền tảng kết nối người lao động và nhà tuyển dụng trực tiếp, minh bạch với hệ thống đánh giá ca làm việc 2 chiều đầu tiên tại Bình Dương & toàn quốc.
                </p>
            </div>
            <div>
                <h4 class="footer-title">Dành Cho Người Lao Động</h4>
                <ul class="footer-links">
                    <li><a href="<?= url('/jobs') ?>">Tìm việc theo ca</a></li>
                    <li><a href="<?= url('/jobs?employment_type=part_time') ?>">Việc làm Part-time</a></li>
                    <li><a href="<?= url('/jobs?employment_type=full_time') ?>">Việc làm Full-time</a></li>
                    <li><a href="<?= url('/employers') ?>">Tra cứu Doanh nghiệp</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-title">Nhà Tuyển Dụng</h4>
                <ul class="footer-links">
                    <li><a href="<?= url('/employer/jobs/create') ?>">Đăng tin tuyển dụng</a></li>
                    <li><a href="<?= url('/employer/dashboard') ?>">Quản lý người lao động</a></li>
                    <li><a href="<?= url('/employer/meetings') ?>">Lên lịch phỏng vấn</a></li>
                    <li><a href="<?= url('/about') ?>">Quy trình xác minh GPKD</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-title">Hệ Thống</h4>
                <ul class="footer-links">
                    <li><a href="<?= url('/about') ?>">Về chúng tôi</a></li>
                    <li><a href="<?= url('/admin/dashboard') ?>">Cổng Quản Trị</a></li>
                    <li><a href="mailto:admin.dai@joblink.io.vn">Liên hệ: admin.dai@joblink.io.vn</a></li>
                    <li><a href="tel:0793497434">Hotline: 0793 497 434</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> JobLink.io.vn<p>
        </div>
    </footer>
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
