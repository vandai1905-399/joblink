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
</head>
<body>
    <?php 
        $uRole = $_SESSION['user']['role_code'] ?? '';
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
        $accId = $_SESSION['user']['account_id'] ?? 0;
        $profile = $_SESSION['user']['profile'] ?? null;
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
                <!-- Dropdown Thông Tin Cá Nhân (Bấm vào hiển thị Profile Card chi tiết) -->
                <div class="user-dropdown-container">
                    <div class="user-dropdown-trigger">
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
                                <span class="user-card-badge <?= strtolower($uRole) ?>"><?= $roleBadge ?></span>
                            </div>
                        </div>

                        <!-- 2. BODY CARD: THÔNG TIN CÁ NHÂN CHI TIẾT TỪNG ROLE -->
                        <div class="user-card-body">
                            <?php if ($uRole === 'JOB_SEEKER'): ?>
                                <div class="user-card-row">
                                    <span class="row-label">📞 Số điện thoại:</span>
                                    <span class="row-value"><?= htmlspecialchars($profile['phone_number'] ?? 'Chưa cập nhật') ?></span>
                                </div>
                                <div class="user-card-row">
                                    <span class="row-label">✉️ Email:</span>
                                    <span class="row-value"><?= htmlspecialchars($profile['email'] ?? $_SESSION['user']['email'] ?? 'Chưa cập nhật') ?></span>
                                </div>
                                <div class="user-card-row">
                                    <span class="row-label">🛡️ Điểm uy tín:</span>
                                    <span class="row-value">
                                        <strong><?= $profile['trust_score'] ?? 100 ?>/100</strong>
                                        <?php if (($profile['agreement_signed'] ?? 0) == 1): ?>
                                            <span class="badge-mini-success">✓ Đã ký cam kết</span>
                                        <?php else: ?>
                                            <span class="badge-mini-warning">Chưa ký</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if (!empty($profile['desired_position'])): ?>
                                    <div class="user-card-row">
                                        <span class="row-label">🎯 Vị trí:</span>
                                        <span class="row-value"><?= htmlspecialchars($profile['desired_position']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="user-card-actions">
                                    <a href="<?= url('/seeker/profile') ?>" class="btn-profile-primary">
                                        <?= icon('user', '', 14) ?> <span>Xem & Chỉnh Sửa Hồ Sơ</span>
                                    </a>
                                </div>

                            <?php elseif ($uRole === 'EMPLOYER_ADMIN'): ?>
                                <div class="user-card-row">
                                    <span class="row-label">🏢 Doanh nghiệp:</span>
                                    <span class="row-value"><?= htmlspecialchars($profile['employer_name'] ?? $_SESSION['user']['display_name']) ?></span>
                                </div>
                                <div class="user-card-row">
                                    <span class="row-label">📑 Mã số thuế:</span>
                                    <span class="row-value"><?= htmlspecialchars($profile['tax_code'] ?? 'Chưa cập nhật') ?></span>
                                </div>
                                <div class="user-card-row">
                                    <span class="row-label">🔐 Chữ ký số:</span>
                                    <span class="row-value">
                                        <?php if (($profile['digital_signature_status'] ?? '') === 'verified'): ?>
                                            <span class="badge-mini-success">✓ Đã Ký Số CA</span>
                                        <?php else: ?>
                                            <span class="badge-mini-warning">Chưa xác thực</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="user-card-actions">
                                    <a href="<?= url('/employer/company-profile') ?>" class="btn-profile-primary">
                                        <?= icon('employers', '', 14) ?> <span>Hồ Sơ Doanh Nghiệp / GPKD</span>
                                    </a>
                                </div>

                            <?php elseif ($uRole === 'INTERMEDIARY'): ?>
                                <div class="user-card-row">
                                    <span class="row-label">🤝 Loại hình:</span>
                                    <span class="row-value">Đại lý trung gian tuyển dụng</span>
                                </div>
                                <div class="user-card-row">
                                    <span class="row-label">✉️ Email:</span>
                                    <span class="row-value"><?= htmlspecialchars($_SESSION['user']['email'] ?? 'Chưa cập nhật') ?></span>
                                </div>
                                <div class="user-card-actions">
                                    <a href="<?= url('/intermediary/settings') ?>" class="btn-profile-primary">
                                        <?= icon('handshake', '', 14) ?> <span>Hồ Sơ & Thiết Lập Đại Lý</span>
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
            </div>
        </div>
    </header>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <?php $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'; ?>
            
            <?php if ($_SESSION['user']['role_code'] === 'JOB_SEEKER'): ?>
                <!-- ========================================================= -->
                <!-- SIDEBAR NGƯỜI LAO ĐỘNG (ĐÃ BỎ MỤC HỒ SƠ - XEM TẠI HEADER) -->
                <!-- ========================================================= -->
                
                <div class="sidebar-heading">Tổng Quan & Tìm Việc</div>
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
                </ul>

                <div class="sidebar-heading">Uy Tín & Cam Kết</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/seeker/settings?tab=trust') ?>" class="sidebar-link <?= (strpos($currentUri, '/seeker/settings') !== false && ($_GET['tab'] ?? '') === 'trust') ? 'active' : '' ?>">
                        <?= icon('award', '', 17) ?> <span>Điểm Uy Tín & Cam Kết</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading">Quản Lý Ứng Tuyển</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/seeker/applied-jobs') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/applied-jobs') !== false ? 'active' : '' ?>">
                        <?= icon('applied', '', 17) ?> <span>Hồ Sơ Đã Ứng Tuyển</span>
                    </a></li>
                    <li><a href="<?= url('/seeker/saved-jobs') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/saved-jobs') !== false ? 'active' : '' ?>">
                        <?= icon('saved', '', 17) ?> <span>Việc Làm Đã Lưu</span>
                    </a></li>
                    <li><a href="<?= url('/seeker/meetings') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/meetings') !== false ? 'active' : '' ?>">
                        <?= icon('meetings', '', 17) ?> <span>Lịch Hẹn & Phỏng Vấn</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading">Trao Đổi & Đánh Giá</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/seeker/messages') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/messages') !== false ? 'active' : '' ?>">
                        <?= icon('chat', '', 17) ?> <span>Tin Nhắn Trao Đổi</span>
                    </a></li>
                    <li><a href="<?= url('/seeker/reviews') ?>" class="sidebar-link <?= strpos($currentUri, '/seeker/reviews') !== false ? 'active' : '' ?>">
                        <?= icon('reviews', '', 17) ?> <span>Đánh Giá Nơi Làm Việc</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading">Thiết Lập</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/seeker/settings') ?>" class="sidebar-link <?= (strpos($currentUri, '/seeker/settings') !== false && empty($_GET['tab'])) ? 'active' : '' ?>">
                        <?= icon('settings', '', 17) ?> <span>Cài Đặt & Thông Báo Gmail</span>
                    </a></li>
                </ul>

            <?php elseif ($_SESSION['user']['role_code'] === 'INTERMEDIARY'): ?>
                <!-- ========================================================= -->
                <!-- SIDEBAR NGƯỜI TRUNG GIAN                                  -->
                <!-- ========================================================= -->
                
                <div class="sidebar-heading">Tổng Quan</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/intermediary/dashboard') ?>" class="sidebar-link <?= strpos($currentUri, '/intermediary/dashboard') !== false ? 'active' : '' ?>">
                        <?= icon('dashboard', '', 17) ?> <span>Bảng Điều Khiển</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading">Ủy Quyền Pháp Lý</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/intermediary/settings?tab=contracts') ?>" class="sidebar-link <?= (strpos($currentUri, '/intermediary/settings') !== false && ($_GET['tab'] ?? '') === 'contracts') ? 'active' : '' ?>">
                        <?= icon('handshake', '', 17) ?> <span>Hợp Đồng & Văn Bản Ủy Quyền</span>
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

                <div class="sidebar-heading">Thiết Lập</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/intermediary/settings?tab=mail') ?>" class="sidebar-link <?= (strpos($currentUri, '/intermediary/settings') !== false && ($_GET['tab'] ?? '') === 'mail') ? 'active' : '' ?>">
                        <?= icon('settings', '', 17) ?> <span>Cài Đặt & Thông Báo Gmail</span>
                    </a></li>
                </ul>

            <?php else: ?>
                <!-- ========================================================= -->
                <!-- SIDEBAR NHÀ TUYỂN DỤNG (ĐÃ BỎ MỤC HỒ SƠ - XEM TẠI HEADER) -->
                <!-- ========================================================= -->
                
                <div class="sidebar-heading">Tổng Quan & Pháp Lý</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/employer/dashboard') ?>" class="sidebar-link <?= strpos($currentUri, '/employer/dashboard') !== false ? 'active' : '' ?>">
                        <?= icon('dashboard', '', 17) ?> <span>Tổng Quan Tuyển Dụng</span>
                    </a></li>
                    <li><a href="<?= url('/employer/settings?tab=cert') ?>" class="sidebar-link <?= (strpos($currentUri, '/employer/settings') !== false && ($_GET['tab'] ?? '') === 'cert') ? 'active' : '' ?>">
                        <?= icon('certificate', '', 17) ?> <span>Xác Thực Chữ Ký Số (CA)</span>
                    </a></li>
                    <li><a href="<?= url('/employer/settings?tab=auth') ?>" class="sidebar-link <?= (strpos($currentUri, '/employer/settings') !== false && ($_GET['tab'] ?? '') === 'auth') ? 'active' : '' ?>">
                        <?= icon('handshake', '', 17) ?> <span>Ủy Quyền Người Trung Gian</span>
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

                <div class="sidebar-heading">Thiết Lập</div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/employer/settings') ?>" class="sidebar-link <?= (strpos($currentUri, '/employer/settings') !== false && empty($_GET['tab'])) ? 'active' : '' ?>">
                        <?= icon('settings', '', 17) ?> <span>Cài Đặt & Thông Báo Gmail</span>
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
    <script src="<?= asset('js/ai_chatbox.js') ?>"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ((array)$extraJs as $jsFile): ?>
            <script src="<?= asset($jsFile) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <?= class_exists('DialogHelper') ? DialogHelper::renderFlashScript() : '' ?>
</body>
</html>
