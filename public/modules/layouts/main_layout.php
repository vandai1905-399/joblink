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
    <script>window.APP_URL = '<?= url('') ?>';</script>
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
                    <?php 
                        $uDisplayName = $_SESSION['user']['display_name'] ?? $_SESSION['user']['username'];
                        $uAvatar = getAvatarUrl($_SESSION['user']['avatar_url'] ?? '', $userRole);
                        $roleNameText = ($userRole === 'EMPLOYER_ADMIN') ? 'Nhà Tuyển Dụng' : (($userRole === 'INTERMEDIARY') ? 'Người Trung Gian' : (($userRole === 'JOB_SEEKER') ? 'Người Lao Động' : 'Quản Trị Viên'));
                    ?>
                    <!-- Cụm Icon Tin Nhắn & Thông Báo -->
                    <?= renderHeaderIcons($_SESSION['user'] ?? null) ?>

                    <!-- Nút Avatar Tròn Phong Cách Facebook -->
                    <div class="user-dropdown-container">
                        <button type="button" class="user-dropdown-trigger fb-avatar-trigger" title="<?= htmlspecialchars($uDisplayName) ?>">
                            <img src="<?= htmlspecialchars($uAvatar) ?>" alt="avatar" class="fb-nav-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                            <span class="fb-chevron-badge"><?= icon('chevron_down', '', 9) ?></span>
                        </button>

                        <div class="user-dropdown-menu fb-dropdown-menu">
                            <!-- 1. HỘP PROFILE TRANG CÁ NHÂN / BẢNG ĐIỀU KHIỂN (FB STYLE) -->
                            <a href="<?= $dashUrl ?>" class="fb-profile-card-box">
                                <div class="fb-card-user">
                                    <img src="<?= htmlspecialchars($uAvatar) ?>" alt="avatar" class="fb-card-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                                    <div class="fb-card-meta">
                                        <div class="fb-card-name"><?= htmlspecialchars($uDisplayName) ?></div>
                                        <div class="fb-card-role"><?= htmlspecialchars($roleNameText) ?> • @<?= htmlspecialchars($_SESSION['user']['username']) ?></div>
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
                                <?php if ($userRole === 'JOB_SEEKER'): ?>
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
                                <?php elseif ($userRole === 'EMPLOYER_ADMIN'): ?>
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
                                <?php elseif ($userRole === 'INTERMEDIARY'): ?>
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
                                <?php elseif (in_array($userRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])): ?>
                                    <a href="<?= url('/admin/settings') ?>" class="fb-menu-item">
                                        <div class="fb-item-icon"><?= icon('settings', '', 17) ?></div>
                                        <div class="fb-item-content">
                                            <div class="fb-item-title">Cài đặt hệ thống & Root CA</div>
                                            <div class="fb-item-desc">Cấu hình máy chủ, Gmail & Chữ ký số</div>
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
