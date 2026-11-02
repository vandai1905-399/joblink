<?php
/**
 * public/modules/layouts/mobile_nav.php
 * Unified Mobile App Navigation Shell for JobLink (All Roles & Guests)
 * Cung cấp Header, Bottom Navigation, Side Drawer và Sub-bar mượt mà trên di động
 */

$isAuth = isset($_SESSION['user']) && !empty($_SESSION['user']['account_id']);
$mUser = $isAuth ? $_SESSION['user'] : null;
$mRole = $mUser['role_code'] ?? 'GUEST';
$mDisplayName = $mUser['display_name'] ?? ($mUser['username'] ?? 'Khách');
$mUsername = $mUser['username'] ?? 'guest';
$mEmail = $mUser['email'] ?? '';

// Cấu hình URL Dashboard & Vai trò tương ứng
$mDashUrl = url('/');
$mRoleBadge = 'Khách';
$mRoleFullName = 'Khách truy cập';
$mAvatar = '';

if ($isAuth) {
    if ($mRole === 'JOB_SEEKER') {
        $mDashUrl = url('/seeker/dashboard');
        $mRoleBadge = 'NLĐ';
        $mRoleFullName = 'Người Lao Động';
        $mProfile = $mUser['profile'] ?? null;
        if (!$mProfile && class_exists('UserSeeker')) {
            $mProfile = UserSeeker::findByAccountId($mUser['account_id']);
            $_SESSION['user']['profile'] = $mProfile;
        }
        $mAvatar = getAvatarUrl($mProfile['avatar_url'] ?? ($mUser['avatar_url'] ?? ''), 'JOB_SEEKER', $mProfile['gender'] ?? '');
    } elseif ($mRole === 'EMPLOYER_ADMIN') {
        $mDashUrl = url('/employer/dashboard');
        $mRoleBadge = 'NTD';
        $mRoleFullName = 'Nhà Tuyển Dụng';
        $mProfile = $mUser['profile'] ?? null;
        if (!$mProfile && class_exists('UserEmployer')) {
            $mProfile = UserEmployer::findByAccountId($mUser['account_id']);
            $_SESSION['user']['profile'] = $mProfile;
        }
        $mAvatar = getAvatarUrl($mProfile['avatar_url'] ?? ($mUser['avatar_url'] ?? ''), 'EMPLOYER_ADMIN');
    } elseif ($mRole === 'INTERMEDIARY') {
        $mDashUrl = url('/intermediary/dashboard');
        $mRoleBadge = 'Trung Gian';
        $mRoleFullName = 'Người Trung Gian';
        $mAvatar = getAvatarUrl($mUser['avatar_url'] ?? '', 'INTERMEDIARY');
    } elseif (in_array($mRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
        $mDashUrl = url('/admin/dashboard');
        $mRoleBadge = 'Admin';
        $mRoleFullName = ($mRole === 'CSKH_STAFF') ? 'Nhân Viên CSKH' : 'Quản Trị Viên';
        $mAvatar = getAvatarUrl($mUser['avatar_url'] ?? '', $mRole);
    }
} else {
    $mAvatar = getSystemLogo();
}

$mHeaderData = ($isAuth && function_exists('getHeaderNotificationsData')) ? getHeaderNotificationsData($mUser) : [];
$mUnreadMsgs = (int)($mHeaderData['unread_messages_count'] ?? 0);
$mUnreadNotis = (int)($mHeaderData['unread_notifications_count'] ?? 0);
?>

<!-- ========================================================================= -->
<!-- 1. TOP MOBILE APP BAR (HEADER CỐ ĐỊNH TRÊN CÙNG MÀN HÌNH DI ĐỘNG)          -->
<!-- ========================================================================= -->
<header class="joblink-mobile-header" id="joblinkMobileHeader">
    <div class="jmh-left">
        <button type="button" class="jmh-menu-btn" id="joblinkDrawerOpenBtn" aria-label="Mở Menu điều hướng">
            <?= icon('menu', '', 20) ?>
        </button>
        <a href="<?= $mDashUrl ?>" class="jmh-brand workspace-nav-link" data-title="Bảng Điều Khiển">
            <img src="<?= getSystemLogo() ?>" alt="JobLink Logo" class="jmh-logo">
            <span class="jmh-brand-text">JobLink</span>
            <span class="jmh-badge"><?= htmlspecialchars($mRoleBadge) ?></span>
        </a>
    </div>

    <div class="jmh-right">
        <?php if ($isAuth): ?>
            <!-- Nút Hộp Thư / Tin Nhắn -->
            <?php 
                $msgUrl = ($mRole === 'EMPLOYER_ADMIN') ? url('/employer/messages') : url('/seeker/messages');
            ?>
            <a href="<?= $msgUrl ?>" class="jmh-icon-btn workspace-nav-link" data-title="Tin Nhắn Trao Đổi" title="Tin nhắn">
                <?= icon('chat', '', 19) ?>
                <?php if ($mUnreadMsgs > 0): ?>
                    <span class="jmh-badge-count"><?= $mUnreadMsgs > 9 ? '9+' : $mUnreadMsgs ?></span>
                <?php endif; ?>
            </a>

            <!-- Nút Chuông Thông Báo -->
            <a href="<?= url('/notifications') ?>" class="jmh-icon-btn workspace-nav-link" data-title="Thông Báo Hệ Thống" title="Thông báo">
                <?= icon('bell', '', 19) ?>
                <?php if ($mUnreadNotis > 0): ?>
                    <span class="jmh-badge-count jmh-badge-danger"><?= $mUnreadNotis > 9 ? '9+' : $mUnreadNotis ?></span>
                <?php endif; ?>
            </a>

            <!-- Avatar Người Dùng (Bấm mở Side Drawer) -->
            <button type="button" class="jmh-avatar-btn" id="jmhAvatarTrigger" title="<?= htmlspecialchars($mDisplayName) ?>">
                <img src="<?= htmlspecialchars($mAvatar) ?>" alt="avatar" class="jmh-avatar-img" onerror="this.src='<?= getSystemLogo() ?>'">
            </button>
        <?php else: ?>
            <a href="<?= url('/jobs') ?>" class="jmh-icon-btn" title="Tìm việc nhanh">
                <?= icon('search', '', 18) ?>
            </a>
            <a href="<?= url('/auth/login') ?>" class="jmh-login-btn">
                <span>Đăng Nhập</span>
            </a>
        <?php endif; ?>
    </div>
</header>

<!-- ========================================================================= -->
<!-- 2. THANH SUB-BAR (HIỆN KHI ĐANG MỞ TAB CON TRÊN WORKSPACE DI ĐỘNG)        -->
<!-- ========================================================================= -->
<div class="joblink-mobile-subbar" id="joblinkMobileSubbar">
    <button type="button" class="jms-back-btn" id="jmsBackBtn">
        <?= icon('chevron_left', '', 14) ?> <span>Bảng điều khiển</span>
    </button>
    <span class="jms-title" id="jmsTitle">Nội dung chi tiết</span>
</div>

<!-- ========================================================================= -->
<!-- 3. MOBILE BOTTOM NAVIGATION (THANH ĐIỀU HƯỚNG CỐ ĐỊNH CHÂN MÀN HÌNH)      -->
<!-- ========================================================================= -->
<nav class="joblink-mobile-bottom-nav" id="joblinkBottomNav">
    <?php if (!$isAuth): ?>
        <a href="<?= url('/') ?>" class="jbn-item active" data-tab="home" title="Trang Chủ">
            <div class="jbn-icon"><?= icon('home', '', 20) ?></div>
            <span class="jbn-label">Trang chủ</span>
        </a>
        <a href="<?= url('/jobs') ?>" class="jbn-item" data-tab="jobs" title="Tìm Kiếm Việc Làm">
            <div class="jbn-icon"><?= icon('search', '', 20) ?></div>
            <span class="jbn-label">Tìm việc</span>
        </a>
        <a href="<?= url('/employers') ?>" class="jbn-item" data-tab="employers" title="Nhà Tuyển Dụng">
            <div class="jbn-icon"><?= icon('building', '', 20) ?></div>
            <span class="jbn-label">Công ty</span>
        </a>
        <a href="<?= url('/about') ?>" class="jbn-item" data-tab="about" title="Giới Thiệu">
            <div class="jbn-icon"><?= icon('certificate', '', 20) ?></div>
            <span class="jbn-label">Giới thiệu</span>
        </a>
        <a href="<?= url('/auth/login') ?>" class="jbn-item" data-tab="auth" title="Đăng Nhập">
            <div class="jbn-icon"><?= icon('user', '', 20) ?></div>
            <span class="jbn-label">Tài khoản</span>
        </a>

    <?php elseif ($mRole === 'JOB_SEEKER'): ?>
        <a href="<?= url('/seeker/dashboard') ?>" class="jbn-item workspace-nav-link active" data-tab="dashboard" data-title="Bảng Điều Khiển">
            <div class="jbn-icon"><?= icon('dashboard', '', 20) ?></div>
            <span class="jbn-label">Tổng quan</span>
        </a>
        <a href="<?= url('/jobs') ?>" class="jbn-item workspace-nav-link" data-tab="jobs" data-title="Tìm Kiếm Việc Làm">
            <div class="jbn-icon"><?= icon('search', '', 20) ?></div>
            <span class="jbn-label">Tìm việc</span>
        </a>
        <a href="<?= url('/seeker/applied-jobs') ?>" class="jbn-item workspace-nav-link" data-tab="applied" data-title="Hồ Sơ Đã Ứng Tuyển">
            <div class="jbn-icon"><?= icon('applied', '', 20) ?></div>
            <span class="jbn-label">Ứng tuyển</span>
        </a>
        <a href="<?= url('/seeker/meetings') ?>" class="jbn-item workspace-nav-link" data-tab="meetings" data-title="Lịch Hẹn & Phỏng Vấn">
            <div class="jbn-icon"><?= icon('meetings', '', 20) ?></div>
            <span class="jbn-label">Lịch hẹn</span>
        </a>
        <a href="<?= url('/seeker/profile') ?>" class="jbn-item workspace-nav-link" data-tab="profile" data-title="Hồ Sơ Năng Lực">
            <div class="jbn-icon"><?= icon('user', '', 20) ?></div>
            <span class="jbn-label">Hồ sơ</span>
        </a>

    <?php elseif ($mRole === 'EMPLOYER_ADMIN'): ?>
        <a href="<?= url('/employer/dashboard') ?>" class="jbn-item workspace-nav-link active" data-tab="dashboard" data-title="Tổng Quan Tuyển Dụng">
            <div class="jbn-icon"><?= icon('dashboard', '', 20) ?></div>
            <span class="jbn-label">Tổng quan</span>
        </a>
        <a href="<?= url('/employer/jobs') ?>" class="jbn-item workspace-nav-link" data-tab="jobs" data-title="Danh Sách Tin Đăng">
            <div class="jbn-icon"><?= icon('list', '', 20) ?></div>
            <span class="jbn-label">Tin đăng</span>
        </a>
        <!-- NÚT NỔI BẬT ĐĂNG TIN MỚI -->
        <a href="<?= url('/employer/jobs/create') ?>" class="jbn-item jbn-item-action workspace-nav-link" data-tab="create-job" data-title="Đăng Tin Tuyển Dụng">
            <div class="jbn-action-btn">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </div>
            <span class="jbn-label">Đăng tin</span>
        </a>
        <a href="<?= url('/employer/applications') ?>" class="jbn-item workspace-nav-link" data-tab="applications" data-title="Hồ Sơ Người Lao Động">
            <div class="jbn-icon"><?= icon('applications', '', 20) ?></div>
            <span class="jbn-label">Ứng viên</span>
        </a>
        <a href="<?= url('/employer/company-profile') ?>" class="jbn-item workspace-nav-link" data-tab="profile" data-title="Thông Tin Doanh Nghiệp">
            <div class="jbn-icon"><?= icon('employers', '', 20) ?></div>
            <span class="jbn-label">Công ty</span>
        </a>

    <?php elseif ($mRole === 'INTERMEDIARY'): ?>
        <a href="<?= url('/intermediary/dashboard') ?>" class="jbn-item workspace-nav-link active" data-tab="dashboard" data-title="Bảng Điều Khiển">
            <div class="jbn-icon"><?= icon('dashboard', '', 20) ?></div>
            <span class="jbn-label">Tổng quan</span>
        </a>
        <a href="<?= url('/jobs') ?>" class="jbn-item workspace-nav-link" data-tab="jobs" data-title="Tra Cứu Việc Làm Sàn">
            <div class="jbn-icon"><?= icon('search', '', 20) ?></div>
            <span class="jbn-label">Tìm việc</span>
        </a>
        <a href="<?= url('/seeker/messages') ?>" class="jbn-item workspace-nav-link" data-tab="messages" data-title="Tin Nhắn Trao Đổi">
            <div class="jbn-icon">
                <?= icon('chat', '', 20) ?>
                <?php if ($mUnreadMsgs > 0): ?><span class="jbn-dot-badge"></span><?php endif; ?>
            </div>
            <span class="jbn-label">Tin nhắn</span>
        </a>
        <a href="<?= url('/intermediary/settings?tab=contracts') ?>" class="jbn-item workspace-nav-link" data-tab="contracts" data-title="Hợp Đồng Ủy Quyền">
            <div class="jbn-icon"><?= icon('handshake', '', 20) ?></div>
            <span class="jbn-label">Hợp đồng</span>
        </a>
        <a href="<?= url('/auth/change-password') ?>" class="jbn-item workspace-nav-link" data-tab="profile" data-title="Đổi Mật Khẩu">
            <div class="jbn-icon"><?= icon('lock', '', 20) ?></div>
            <span class="jbn-label">Bảo mật</span>
        </a>

    <?php elseif (in_array($mRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])): ?>
        <a href="<?= url('/admin/dashboard') ?>" class="jbn-item workspace-nav-link active" data-tab="dashboard" data-title="Bảng Điều Khiển Quản Trị">
            <div class="jbn-icon"><?= icon('dashboard', '', 20) ?></div>
            <span class="jbn-label">Tổng quan</span>
        </a>
        <a href="<?= url('/admin/accounts') ?>" class="jbn-item workspace-nav-link" data-tab="accounts" data-title="Quản Lý Tài Khoản">
            <div class="jbn-icon"><?= icon('users', '', 20) ?></div>
            <span class="jbn-label">Tài khoản</span>
        </a>
        <a href="<?= url('/admin/employers') ?>" class="jbn-item workspace-nav-link" data-tab="employers" data-title="Xác Minh Doanh Nghiệp">
            <div class="jbn-icon"><?= icon('building', '', 20) ?></div>
            <span class="jbn-label">Doanh nghiệp</span>
        </a>
        <a href="<?= url('/admin/job-posts') ?>" class="jbn-item workspace-nav-link" data-tab="jobs" data-title="Kiểm Duyệt Tin Tuyển Dụng">
            <div class="jbn-icon"><?= icon('list', '', 20) ?></div>
            <span class="jbn-label">Duyệt tin</span>
        </a>
        <a href="<?= url('/admin/ai-settings') ?>" class="jbn-item workspace-nav-link" data-tab="ai" data-title="Cài Đặt AI">
            <div class="jbn-icon"><?= icon('ai', '', 20) ?></div>
            <span class="jbn-label">Cài đặt AI</span>
        </a>
    <?php endif; ?>
</nav>

<!-- ========================================================================= -->
<!-- 4. SIDE DRAWER MENU (MENU TRƯỢT NATIVE APP TỪ BÊN TRÁI)                   -->
<!-- ========================================================================= -->
<div class="joblink-mobile-drawer" id="joblinkMobileDrawer">
    <div class="jmd-backdrop" id="joblinkDrawerBackdrop"></div>
    <div class="jmd-panel">
        <!-- HEADER CỦA DRAWER -->
        <div class="jmd-header">
            <?php if ($isAuth): ?>
                <div class="jmd-user-card">
                    <img src="<?= htmlspecialchars($mAvatar) ?>" alt="avatar" class="jmd-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                    <div class="jmd-user-info">
                        <h4 class="jmd-user-name" title="<?= htmlspecialchars($mDisplayName) ?>"><?= htmlspecialchars($mDisplayName) ?></h4>
                        <span class="jmd-user-sub"><?= htmlspecialchars($mEmail ?: '@' . $mUsername) ?></span>
                        <span class="jmd-role-pill">✨ <?= htmlspecialchars($mRoleFullName) ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="jmd-guest-card">
                    <img src="<?= getSystemLogo() ?>" alt="JobLink Logo" class="jmd-guest-logo">
                    <div>
                        <h4 class="jmd-guest-title">Chào mừng bạn đến với JobLink</h4>
                        <p class="jmd-guest-sub">Cổng kết nối việc làm minh bạch & đánh giá 2 chiều</p>
                    </div>
                    <div class="jmd-guest-actions">
                        <a href="<?= url('/auth/login') ?>" class="btn btn-primary btn-sm" style="flex:1; justify-content:center; text-align:center;">Đăng Nhập</a>
                        <a href="<?= url('/auth/register') ?>" class="btn btn-outline btn-sm" style="flex:1; justify-content:center; text-align:center; background:#fff; color:#0f766e;">Đăng Ký</a>
                    </div>
                </div>
            <?php endif; ?>
            <button type="button" class="jmd-close-btn" id="joblinkDrawerCloseBtn" aria-label="Đóng menu">&times;</button>
        </div>

        <!-- DANH SÁCH MENU CUỘN -->
        <div class="jmd-nav-scroll">
            <?php if (!$isAuth): ?>
                <div class="jmd-group-title">Khám Phá Sàn Việc Làm</div>
                <a href="<?= url('/') ?>" class="jmd-link active">
                    <?= icon('home', '', 18) ?> <span>Trang Chủ</span>
                </a>
                <a href="<?= url('/jobs') ?>" class="jmd-link">
                    <?= icon('search', '', 18) ?> <span>Tìm Kiếm Việc Làm</span>
                </a>
                <a href="<?= url('/employers') ?>" class="jmd-link">
                    <?= icon('building', '', 18) ?> <span>Danh Sách Doanh Nghiệp</span>
                </a>
                <a href="<?= url('/about') ?>" class="jmd-link">
                    <?= icon('certificate', '', 18) ?> <span>Về Chúng Tôi & GPKD</span>
                </a>

                <div class="jmd-group-title">Dành Cho Người Dùng Mới</div>
                <a href="<?= url('/auth/login') ?>" class="jmd-link">
                    <?= icon('user', '', 18) ?> <span>Đăng Nhập Tài Khoản</span>
                </a>
                <a href="<?= url('/auth/register') ?>" class="jmd-link">
                    <?= icon('rocket', '', 18) ?> <span>Đăng Ký Tài Khoản Mới</span>
                </a>

            <?php elseif ($mRole === 'JOB_SEEKER'): ?>
                <div class="jmd-group-title">Khám Phá Việc Làm</div>
                <a href="<?= url('/seeker/dashboard') ?>" class="jmd-link workspace-nav-link active" data-title="Bảng Điều Khiển">
                    <?= icon('dashboard', '', 18) ?> <span>Bảng Điều Khiển</span>
                </a>
                <a href="<?= url('/jobs') ?>" class="jmd-link workspace-nav-link" data-title="Tìm Kiếm Việc Làm">
                    <?= icon('search', '', 18) ?> <span>Tìm Kiếm Việc Làm</span>
                </a>
                <a href="<?= url('/jobs?ai_recommend=1') ?>" class="jmd-link workspace-nav-link" data-title="Việc Làm Gợi Ý AI">
                    <?= icon('zap', '', 18) ?> <span>Việc Làm Gợi Ý AI</span>
                    <span class="jmd-badge-new">AI</span>
                </a>
                <a href="<?= url('/seeker/saved-jobs') ?>" class="jmd-link workspace-nav-link" data-title="Việc Làm Đã Lưu">
                    <?= icon('saved', '', 18) ?> <span>Việc Làm Đã Lưu</span>
                </a>

                <div class="jmd-group-title">Tiến Độ Tuyển Dụng</div>
                <a href="<?= url('/seeker/applied-jobs') ?>" class="jmd-link workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển">
                    <?= icon('applied', '', 18) ?> <span>Hồ Sơ Đã Ứng Tuyển</span>
                </a>
                <a href="<?= url('/seeker/meetings') ?>" class="jmd-link workspace-nav-link" data-title="Lịch Hẹn & Phỏng Vấn">
                    <?= icon('meetings', '', 18) ?> <span>Lịch Hẹn & Phỏng Vấn</span>
                </a>
                <a href="<?= url('/seeker/messages') ?>" class="jmd-link workspace-nav-link" data-title="Tin Nhắn Trao Đổi">
                    <?= icon('chat', '', 18) ?> <span>Tin Nhắn Trao Đổi</span>
                    <?php if ($mUnreadMsgs > 0): ?>
                        <span class="jmd-badge-new"><?= $mUnreadMsgs ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= url('/seeker/reviews') ?>" class="jmd-link workspace-nav-link" data-title="Đánh Giá Nơi Làm Việc">
                    <?= icon('reviews', '', 18) ?> <span>Đánh Giá Nơi Làm Việc</span>
                </a>

                <div class="jmd-group-title">Hồ Sơ & Cài Đặt</div>
                <a href="<?= url('/seeker/profile') ?>" class="jmd-link workspace-nav-link" data-title="Hồ Sơ Năng Lực">
                    <?= icon('user', '', 18) ?> <span>Hồ Sơ Năng Lực (CV)</span>
                </a>
                <a href="<?= url('/seeker/settings') ?>" class="jmd-link workspace-nav-link" data-title="Cài Đặt & Cam Kết">
                    <?= icon('settings', '', 18) ?> <span>Cài Đặt & Cam Kết</span>
                </a>
                <a href="<?= url('/auth/change-password') ?>" class="jmd-link workspace-nav-link" data-title="Đổi Mật Khẩu">
                    <?= icon('lock', '', 18) ?> <span>Đổi Mật Khẩu</span>
                </a>

            <?php elseif ($mRole === 'EMPLOYER_ADMIN'): ?>
                <div class="jmd-group-title">Quản Lý Tuyển Dụng</div>
                <a href="<?= url('/employer/dashboard') ?>" class="jmd-link workspace-nav-link active" data-title="Tổng Quan Tuyển Dụng">
                    <?= icon('dashboard', '', 18) ?> <span>Tổng Quan Tuyển Dụng</span>
                </a>
                <a href="<?= url('/employer/jobs/create') ?>" class="jmd-link workspace-nav-link" data-title="Đăng Tin Mới" style="color: #0d9488; font-weight: 700;">
                    <?= icon('posts', '', 18) ?> <span>+ Đăng Tin Mới</span>
                </a>
                <a href="<?= url('/employer/jobs') ?>" class="jmd-link workspace-nav-link" data-title="Danh Sách Tin Đăng">
                    <?= icon('list', '', 18) ?> <span>Danh Sách Tin Đăng</span>
                </a>

                <div class="jmd-group-title">Tiếp Nhận & Phỏng Vấn</div>
                <a href="<?= url('/employer/applications') ?>" class="jmd-link workspace-nav-link" data-title="Hồ Sơ Người Lao Động">
                    <?= icon('applications', '', 18) ?> <span>Hồ Sơ Người Lao Động</span>
                </a>
                <a href="<?= url('/employer/candidates/recommended') ?>" class="jmd-link workspace-nav-link" data-title="Quản Lý NLĐ Đề Xuất">
                    <?= icon('zap', '', 18) ?> <span>Ứng Viên Gợi Ý AI</span>
                    <span class="jmd-badge-new">AI</span>
                </a>
                <a href="<?= url('/employer/meetings') ?>" class="jmd-link workspace-nav-link" data-title="Lịch Hẹn & Phỏng Vấn">
                    <?= icon('meetings', '', 18) ?> <span>Lịch Hẹn & Phỏng Vấn</span>
                </a>

                <div class="jmd-group-title">Trao Đổi & Đánh Giá</div>
                <a href="<?= url('/employer/messages') ?>" class="jmd-link workspace-nav-link" data-title="Tin Nhắn Trao Đổi">
                    <?= icon('chat', '', 18) ?> <span>Tin Nhắn Trao Đổi</span>
                    <?php if ($mUnreadMsgs > 0): ?>
                        <span class="jmd-badge-new"><?= $mUnreadMsgs ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= url('/employer/candidate-reviews') ?>" class="jmd-link workspace-nav-link" data-title="Đánh Giá Người Lao Động">
                    <?= icon('reviews', '', 18) ?> <span>Đánh Giá Người Lao Động</span>
                </a>

                <div class="jmd-group-title">Doanh Nghiệp & Cài Đặt</div>
                <a href="<?= url('/employer/company-profile') ?>" class="jmd-link workspace-nav-link" data-title="Thông Tin Doanh Nghiệp">
                    <?= icon('employers', '', 18) ?> <span>Thông Tin Doanh Nghiệp</span>
                </a>
                <a href="<?= url('/employer/settings?tab=cert') ?>" class="jmd-link workspace-nav-link" data-title="Chữ Ký Số & Hợp Đồng">
                    <?= icon('certificate', '', 18) ?> <span>Chữ Ký Số & Hợp Đồng</span>
                </a>
                <a href="<?= url('/employer/settings?tab=auth') ?>" class="jmd-link workspace-nav-link" data-title="Ủy Quyền Tuyển Dụng">
                    <?= icon('handshake', '', 18) ?> <span>Giấy Ủy Quyền Tuyển Dụng</span>
                </a>
                <a href="<?= url('/employer/settings?tab=mail') ?>" class="jmd-link workspace-nav-link" data-title="Cài Đặt Gmail">
                    <?= icon('mail', '', 18) ?> <span>Thông Báo Gmail</span>
                </a>
                <a href="<?= url('/auth/change-password') ?>" class="jmd-link workspace-nav-link" data-title="Đổi Mật Khẩu">
                    <?= icon('lock', '', 18) ?> <span>Đổi Mật Khẩu</span>
                </a>

            <?php elseif ($mRole === 'INTERMEDIARY'): ?>
                <div class="jmd-group-title">Tổng Quan</div>
                <a href="<?= url('/intermediary/dashboard') ?>" class="jmd-link workspace-nav-link active" data-title="Bảng Điều Khiển">
                    <?= icon('dashboard', '', 18) ?> <span>Bảng Điều Khiển</span>
                </a>
                <a href="<?= url('/jobs') ?>" class="jmd-link workspace-nav-link" data-title="Tra Cứu Việc Làm Sàn">
                    <?= icon('search', '', 18) ?> <span>Tra Cứu Việc Làm Sàn</span>
                </a>
                <a href="<?= url('/seeker/messages') ?>" class="jmd-link workspace-nav-link" data-title="Tin Nhắn Trao Đổi">
                    <?= icon('chat', '', 18) ?> <span>Tin Nhắn Trao Đổi</span>
                </a>
                <a href="<?= url('/intermediary/settings?tab=contracts') ?>" class="jmd-link workspace-nav-link" data-title="Hợp Đồng Ủy Quyền">
                    <?= icon('handshake', '', 18) ?> <span>Hợp Đồng & Giấy Ủy Quyền</span>
                </a>
                <a href="<?= url('/auth/change-password') ?>" class="jmd-link workspace-nav-link" data-title="Đổi Mật Khẩu">
                    <?= icon('lock', '', 18) ?> <span>Đổi Mật Khẩu</span>
                </a>

            <?php elseif (in_array($mRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])): ?>
                <div class="jmd-group-title">Hệ Thống & Báo Cáo</div>
                <a href="<?= url('/admin/dashboard') ?>" class="jmd-link workspace-nav-link active" data-title="Bảng Điều Khiển">
                    <?= icon('dashboard', '', 18) ?> <span>Bảng Điều Khiển</span>
                </a>
                <a href="<?= url('/admin/ai-analytics') ?>" class="jmd-link workspace-nav-link" data-title="Phân Tích AI">
                    <?= icon('reports', '', 18) ?> <span>Phân Tích & Thống Kê AI</span>
                </a>
                <a href="<?= url('/admin/profile-history') ?>" class="jmd-link workspace-nav-link" data-title="Lịch Sử Hồ Sơ">
                    <?= icon('history', '', 18) ?> <span>Lịch Sử Thay Đổi Hồ Sơ</span>
                </a>

                <div class="jmd-group-title">Quản Trị Người Dùng & Sàn</div>
                <a href="<?= url('/admin/accounts') ?>" class="jmd-link workspace-nav-link" data-title="Quản Lý Tài Khoản">
                    <?= icon('users', '', 18) ?> <span>Quản Lý Tài Khoản</span>
                </a>
                <a href="<?= url('/admin/employers') ?>" class="jmd-link workspace-nav-link" data-title="Xác Minh Doanh Nghiệp">
                    <?= icon('building', '', 18) ?> <span>Xác Thực GPKD Doanh Nghiệp</span>
                </a>
                <a href="<?= url('/admin/job-posts') ?>" class="jmd-link workspace-nav-link" data-title="Kiểm Duyệt Tin Tuyển Dụng">
                    <?= icon('list', '', 18) ?> <span>Kiểm Duyệt Tin Tuyển Dụng</span>
                </a>
                <a href="<?= url('/admin/staffs') ?>" class="jmd-link workspace-nav-link" data-title="Nhân Viên Hỗ Trợ">
                    <?= icon('staffs', '', 18) ?> <span>Quản Lý Nhân Viên CSKH</span>
                </a>

                <div class="jmd-group-title">Cấu Hình & Kỹ Thuật</div>
                <a href="<?= url('/admin/ai-settings') ?>" class="jmd-link workspace-nav-link" data-title="Cấu Hình AI">
                    <?= icon('ai', '', 18) ?> <span>Cấu Hình Mô Hình AI</span>
                </a>
                <a href="<?= url('/admin/settings') ?>" class="jmd-link workspace-nav-link" data-title="Cài Đặt Hệ Thống">
                    <?= icon('settings', '', 18) ?> <span>Cài Đặt Máy Chủ & Root CA</span>
                </a>
                <a href="<?= url('/auth/change-password') ?>" class="jmd-link workspace-nav-link" data-title="Đổi Mật Khẩu">
                    <?= icon('lock', '', 18) ?> <span>Đổi Mật Khẩu</span>
                </a>
            <?php endif; ?>

            <!-- Nút Gọi Trợ Lý Ảo Joby AI -->
            <button type="button" class="jmd-link jmd-link-bot" onclick="window.joblinkOpenChat && window.joblinkOpenChat(); document.getElementById('joblinkMobileDrawer').classList.remove('open');">
                <img src="<?= getJobyAvatar() ?>" alt="Joby" style="width:22px;height:22px;border-radius:50%;object-fit:cover;vertical-align:middle;">
                <span>Trợ Lý Thông Minh Joby AI</span>
                <span class="jmd-badge-bot">24/7</span>
            </button>
        </div>

        <!-- FOOTER CỦA DRAWER -->
        <div class="jmd-footer">
            <?php if ($isAuth): ?>
                <a href="<?= url('/auth/logout') ?>" class="jmd-logout-btn">
                    <?= icon('logout', '', 16) ?> <span>Đăng xuất</span>
                </a>
            <?php else: ?>
                <a href="tel:0793497434" class="jmd-hotline-btn">
                    <?= icon('phone', '', 15) ?> <span>Hotline: 0793 497 434</span>
                </a>
            <?php endif; ?>
            <span class="jmd-version">JobLink App • v2.6</span>
        </div>
    </div>
</div>
