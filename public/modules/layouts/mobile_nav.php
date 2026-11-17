<?php
/**
 * public/modules/layouts/mobile_nav.php
 * Unified mobile app navigation shell for JobLink (All roles & Guests)
 * Cung cấp Header, Bottom navigation, Side drawer và Sub-bar mượt mà trên di động
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
        $mRoleFullName = 'Người lao động';
        $mProfile = $mUser['profile'] ?? null;
        if (!$mProfile && class_exists('UserSeeker')) {
            $mProfile = UserSeeker::findByAccountId($mUser['account_id']);
            $_SESSION['user']['profile'] = $mProfile;
        }
        $mAvatar = getAvatarUrl($mProfile['avatar_url'] ?? ($mUser['avatar_url'] ?? ''), 'JOB_SEEKER', $mProfile['gender'] ?? '');
    } elseif ($mRole === 'EMPLOYER_ADMIN') {
        $mDashUrl = url('/employer/dashboard');
        $mRoleBadge = 'NTD';
        $mRoleFullName = 'Nhà tuyển dụng';
        $mProfile = $mUser['profile'] ?? null;
        if (!$mProfile && class_exists('UserEmployer')) {
            $mProfile = UserEmployer::findByAccountId($mUser['account_id']);
            $_SESSION['user']['profile'] = $mProfile;
        }
        $mAvatar = getAvatarUrl($mProfile['avatar_url'] ?? ($mUser['avatar_url'] ?? ''), 'EMPLOYER_ADMIN');
    } elseif ($mRole === 'INTERMEDIARY') {
        $mDashUrl = url('/intermediary/dashboard');
        $mRoleBadge = 'Trung gian';
        $mRoleFullName = 'Người trung gian';
        $mAvatar = getAvatarUrl($mUser['avatar_url'] ?? '', 'INTERMEDIARY');
    } elseif (in_array($mRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
        $mDashUrl = url('/admin/dashboard');
        $mRoleBadge = 'Admin';
        $mRoleFullName = ($mRole === 'CSKH_STAFF') ? 'nhân viên CSKH' : 'Quản trị viên';
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
        <button type="button" class="jmh-menu-btn" id="joblinkDrawerOpenBtn" aria-label="Mở menu điều hướng">
            <?= icon('menu', '', 20) ?>
        </button>
        <a href="<?= $mDashUrl ?>" class="jmh-brand workspace-nav-link" data-title="Bảng điều khiển">
            <img src="<?= getSystemLogo() ?>" alt="JobLink Logo" class="jmh-logo">
            <span class="jmh-brand-text">JobLink</span>
            <span class="jmh-badge"><?= htmlspecialchars($mRoleBadge) ?></span>
        </a>
    </div>

    <div class="jmh-right">
        <?php if ($isAuth): ?>
            <!-- Nút hộp thư / Tin nhắn -->
            <?php
                $msgUrl = ($mRole === 'EMPLOYER_ADMIN') ? url('/employer/messages') : url('/seeker/messages');
            ?>
            <a href="<?= $msgUrl ?>" class="jmh-icon-btn workspace-nav-link" data-title="Tin nhắn trao đổi" title="Tin nhắn">
                <?= icon('chat', '', 19) ?>
                <?php if ($mUnreadMsgs > 0): ?>
                    <span class="jmh-badge-count"><?= $mUnreadMsgs > 9 ? '9+' : $mUnreadMsgs ?></span>
                <?php endif; ?>
            </a>

            <!-- Nút chuông thông báo -->
            <a href="<?= url('/notifications') ?>" class="jmh-icon-btn workspace-nav-link" data-title="Thông báo hệ thống" title="Thông báo">
                <?= icon('bell', '', 19) ?>
                <?php if ($mUnreadNotis > 0): ?>
                    <span class="jmh-badge-count jmh-badge-danger"><?= $mUnreadNotis > 9 ? '9+' : $mUnreadNotis ?></span>
                <?php endif; ?>
            </a>

            <!-- Avatar người dùng (Bấm mở Side drawer) -->
            <button type="button" class="jmh-avatar-btn" id="jmhAvatarTrigger" title="<?= htmlspecialchars($mDisplayName) ?>">
                <img src="<?= htmlspecialchars($mAvatar) ?>" alt="avatar" class="jmh-avatar-img" onerror="this.src='<?= getSystemLogo() ?>'">
            </button>
        <?php else: ?>
            <a href="<?= url('/jobs') ?>" class="jmh-icon-btn" title="Tìm việc nhanh">
                <?= icon('search', '', 18) ?>
            </a>
            <a href="<?= url('/auth/login') ?>" class="jmh-login-btn">
                <span>Đăng nhập</span>
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
        <a href="<?= url('/') ?>" class="jbn-item active" data-tab="home" title="Trang chủ">
            <div class="jbn-icon"><?= icon('home', '', 20) ?></div>
            <span class="jbn-label">Trang chủ</span>
        </a>
        <a href="<?= url('/jobs') ?>" class="jbn-item" data-tab="jobs" title="Tìm kiếm việc làm">
            <div class="jbn-icon"><?= icon('search', '', 20) ?></div>
            <span class="jbn-label">Tìm việc</span>
        </a>
        <a href="<?= url('/employers') ?>" class="jbn-item" data-tab="employers" title="Nhà tuyển dụng">
            <div class="jbn-icon"><?= icon('building', '', 20) ?></div>
            <span class="jbn-label">Công ty</span>
        </a>
        <a href="<?= url('/about') ?>" class="jbn-item" data-tab="about" title="Giới thiệu">
            <div class="jbn-icon"><?= icon('certificate', '', 20) ?></div>
            <span class="jbn-label">Giới thiệu</span>
        </a>
        <a href="<?= url('/auth/login') ?>" class="jbn-item" data-tab="auth" title="Đăng nhập">
            <div class="jbn-icon"><?= icon('user', '', 20) ?></div>
            <span class="jbn-label">Tài khoản</span>
        </a>

    <?php elseif ($mRole === 'JOB_SEEKER'): ?>
        <a href="<?= url('/seeker/dashboard') ?>" class="jbn-item workspace-nav-link active" data-tab="dashboard" data-title="Bảng điều khiển">
            <div class="jbn-icon"><?= icon('dashboard', '', 20) ?></div>
            <span class="jbn-label">Tổng quan</span>
        </a>
        <a href="<?= url('/jobs') ?>" class="jbn-item workspace-nav-link" data-tab="jobs" data-title="Tìm kiếm việc làm">
            <div class="jbn-icon"><?= icon('search', '', 20) ?></div>
            <span class="jbn-label">Tìm việc</span>
        </a>
        <a href="<?= url('/seeker/applied-jobs') ?>" class="jbn-item workspace-nav-link" data-tab="applied" data-title="Hồ sơ đã ứng tuyển">
            <div class="jbn-icon"><?= icon('applied', '', 20) ?></div>
            <span class="jbn-label">Ứng tuyển</span>
        </a>
        <a href="<?= url('/seeker/meetings') ?>" class="jbn-item workspace-nav-link" data-tab="meetings" data-title="Lịch hẹn & phỏng vấn">
            <div class="jbn-icon"><?= icon('meetings', '', 20) ?></div>
            <span class="jbn-label">Lịch hẹn</span>
        </a>
        <a href="<?= url('/seeker/profile') ?>" class="jbn-item workspace-nav-link" data-tab="profile" data-title="Hồ sơ năng lực">
            <div class="jbn-icon"><?= icon('user', '', 20) ?></div>
            <span class="jbn-label">Hồ sơ</span>
        </a>

    <?php elseif ($mRole === 'EMPLOYER_ADMIN'): ?>
        <a href="<?= url('/employer/dashboard') ?>" class="jbn-item workspace-nav-link active" data-tab="dashboard" data-title="Tổng quan tuyển dụng">
            <div class="jbn-icon"><?= icon('dashboard', '', 20) ?></div>
            <span class="jbn-label">Tổng quan</span>
        </a>
        <a href="<?= url('/employer/jobs') ?>" class="jbn-item workspace-nav-link" data-tab="jobs" data-title="Danh sách tin đăng">
            <div class="jbn-icon"><?= icon('list', '', 20) ?></div>
            <span class="jbn-label">Tin đăng</span>
        </a>
        <!-- NÚT NỔI BẬT ĐĂNG TIN MỚI -->
        <a href="<?= url('/employer/jobs/create') ?>" class="jbn-item jbn-item-action workspace-nav-link" data-tab="create-job" data-title="Đăng tin tuyển dụng">
            <div class="jbn-action-btn">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </div>
            <span class="jbn-label">Đăng tin</span>
        </a>
        <a href="<?= url('/employer/applications') ?>" class="jbn-item workspace-nav-link" data-tab="applications" data-title="Hồ sơ người lao động">
            <div class="jbn-icon"><?= icon('applications', '', 20) ?></div>
            <span class="jbn-label">Ứng viên</span>
        </a>
        <a href="<?= url('/employer/company-profile') ?>" class="jbn-item workspace-nav-link" data-tab="profile" data-title="Thông tin doanh nghiệp">
            <div class="jbn-icon"><?= icon('employers', '', 20) ?></div>
            <span class="jbn-label">Công ty</span>
        </a>

    <?php elseif ($mRole === 'INTERMEDIARY'): ?>
        <a href="<?= url('/intermediary/dashboard') ?>" class="jbn-item workspace-nav-link active" data-tab="dashboard" data-title="Bảng điều khiển">
            <div class="jbn-icon"><?= icon('dashboard', '', 20) ?></div>
            <span class="jbn-label">Tổng quan</span>
        </a>
        <a href="<?= url('/jobs') ?>" class="jbn-item workspace-nav-link" data-tab="jobs" data-title="Tra cứu việc làm sàn">
            <div class="jbn-icon"><?= icon('search', '', 20) ?></div>
            <span class="jbn-label">Tìm việc</span>
        </a>
        <a href="<?= url('/seeker/messages') ?>" class="jbn-item workspace-nav-link" data-tab="messages" data-title="Tin nhắn trao đổi">
            <div class="jbn-icon">
                <?= icon('chat', '', 20) ?>
                <?php if ($mUnreadMsgs > 0): ?><span class="jbn-dot-badge"></span><?php endif; ?>
            </div>
            <span class="jbn-label">Tin nhắn</span>
        </a>
        <a href="<?= url('/intermediary/settings?tab=contracts') ?>" class="jbn-item workspace-nav-link" data-tab="contracts" data-title="Hợp đồng ủy quyền">
            <div class="jbn-icon"><?= icon('handshake', '', 20) ?></div>
            <span class="jbn-label">Hợp đồng</span>
        </a>
        <a href="<?= url('/auth/change-password') ?>" class="jbn-item workspace-nav-link" data-tab="profile" data-title="Đổi mật khẩu">
            <div class="jbn-icon"><?= icon('lock', '', 20) ?></div>
            <span class="jbn-label">Bảo mật</span>
        </a>

    <?php elseif (in_array($mRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])): ?>
        <a href="<?= url('/admin/dashboard') ?>" class="jbn-item workspace-nav-link active" data-tab="dashboard" data-title="Bảng điều khiển quản trị">
            <div class="jbn-icon"><?= icon('dashboard', '', 20) ?></div>
            <span class="jbn-label">Tổng quan</span>
        </a>
        <a href="<?= url('/admin/accounts') ?>" class="jbn-item workspace-nav-link" data-tab="accounts" data-title="Quản lý tài khoản">
            <div class="jbn-icon"><?= icon('users', '', 20) ?></div>
            <span class="jbn-label">Tài khoản</span>
        </a>
        <a href="<?= url('/admin/employers') ?>" class="jbn-item workspace-nav-link" data-tab="employers" data-title="Xác minh doanh nghiệp">
            <div class="jbn-icon"><?= icon('building', '', 20) ?></div>
            <span class="jbn-label">Doanh nghiệp</span>
        </a>
        <a href="<?= url('/admin/job-posts') ?>" class="jbn-item workspace-nav-link" data-tab="jobs" data-title="Kiểm duyệt tin tuyển dụng">
            <div class="jbn-icon"><?= icon('list', '', 20) ?></div>
            <span class="jbn-label">Duyệt tin</span>
        </a>
        <a href="<?= url('/admin/ai-settings') ?>" class="jbn-item workspace-nav-link" data-tab="ai" data-title="Cài đặt AI">
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
                <?php
                    $mProfileUrl = url('/seeker/profile');
                    $mProfileTitle = 'Hồ sơ năng lực';
                    $hasProfileLink = true;
                    if ($mRole === 'EMPLOYER_ADMIN') {
                        $mProfileUrl = url('/employer/company-profile');
                        $mProfileTitle = 'Thông tin doanh nghiệp';
                    } elseif (in_array($mRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
                        $hasProfileLink = false;
                        $mProfileUrl = '';
                        $mProfileTitle = '';
                    }
                ?>
                <?php if ($hasProfileLink): ?>
                    <a href="<?= $mProfileUrl ?>" class="jmd-user-card workspace-nav-link" data-title="<?= htmlspecialchars($mProfileTitle) ?>" title="Xem <?= htmlspecialchars($mProfileTitle) ?>" style="text-decoration:none;">
                <?php else: ?>
                    <div class="jmd-user-card" title="<?= htmlspecialchars($mDisplayName) ?> (@<?= htmlspecialchars($mUsername) ?> - <?= htmlspecialchars($mRoleFullName) ?>)" style="text-decoration:none; cursor:default;">
                <?php endif; ?>
                    <img src="<?= htmlspecialchars($mAvatar) ?>" alt="avatar" class="jmd-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                    <div class="jmd-user-info">
                        <h4 class="jmd-user-name" title="<?= htmlspecialchars($mDisplayName) ?>"><?= htmlspecialchars($mDisplayName) ?></h4>
                        <span class="jmd-user-sub"><?= htmlspecialchars($mEmail ?: '@' . $mUsername) ?></span>
                        <span class="jmd-role-pill">✨ <?= htmlspecialchars($mRoleFullName) ?></span>
                    </div>
                <?php if ($hasProfileLink): ?>
                    </a>
                <?php else: ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="jmd-guest-card">
                    <img src="<?= getSystemLogo() ?>" alt="JobLink Logo" class="jmd-guest-logo">
                    <div>
                        <h4 class="jmd-guest-title">Chào mừng bạn đến với JobLink</h4>
                        <p class="jmd-guest-sub">Cổng kết nối việc làm minh bạch & đánh giá 2 chiều</p>
                    </div>
                    <div class="jmd-guest-actions">
                        <a href="<?= url('/auth/login') ?>" class="btn btn-primary btn-sm" style="flex:1; justify-content:center; text-align:center;">Đăng nhập</a>
                        <a href="<?= url('/auth/register') ?>" class="btn btn-outline btn-sm" style="flex:1; justify-content:center; text-align:center; background:#475569; color:#ffffff; border-color:#475569;">Đăng ký</a>
                    </div>
                </div>
            <?php endif; ?>
            <button type="button" class="jmd-close-btn" id="joblinkDrawerCloseBtn" aria-label="Đóng menu">&times;</button>
        </div>

        <!-- DANH SÁCH MENU CUỘN -->
        <div class="jmd-nav-scroll">
            <?php if (!$isAuth): ?>
                <div class="jmd-group-title">Khám phá sàn việc làm</div>
                <a href="<?= url('/') ?>" class="jmd-link active">
                    <?= icon('home', '', 18) ?> <span>Trang chủ</span>
                </a>
                <a href="<?= url('/jobs') ?>" class="jmd-link">
                    <?= icon('search', '', 18) ?> <span>Tìm kiếm việc làm</span>
                </a>
                <a href="<?= url('/employers') ?>" class="jmd-link">
                    <?= icon('building', '', 18) ?> <span>Danh sách doanh nghiệp</span>
                </a>
                <a href="<?= url('/about') ?>" class="jmd-link">
                    <?= icon('certificate', '', 18) ?> <span>Về chúng tôi & GPKD</span>
                </a>

                <div class="jmd-group-title">Dành cho người dùng mới</div>
                <a href="<?= url('/auth/login') ?>" class="jmd-link">
                    <?= icon('user', '', 18) ?> <span>Đăng nhập tài khoản</span>
                </a>
                <a href="<?= url('/auth/register') ?>" class="jmd-link">
                    <?= icon('rocket', '', 18) ?> <span>Đăng ký tài khoản mới</span>
                </a>

            <?php elseif ($mRole === 'JOB_SEEKER'): ?>
                <div class="jmd-group-title">Khám phá việc làm</div>
                <a href="<?= url('/seeker/dashboard') ?>" class="jmd-link workspace-nav-link active" data-title="Bảng điều khiển">
                    <?= icon('dashboard', '', 18) ?> <span>Bảng điều khiển</span>
                </a>
                <a href="<?= url('/jobs') ?>" class="jmd-link workspace-nav-link" data-title="Tìm việc & gợi ý AI">
                    <?= icon('search', '', 18) ?> <span>Tìm việc & gợi ý AI</span>
                    <span class="jmd-badge-new">AI Match</span>
                </a>
                <a href="<?= url('/seeker/saved-jobs') ?>" class="jmd-link workspace-nav-link" data-title="Việc làm đã lưu">
                    <?= icon('saved', '', 18) ?> <span>Việc làm đã lưu</span>
                </a>

                <div class="jmd-group-title">Tiến độ tuyển dụng</div>
                <a href="<?= url('/seeker/applied-jobs') ?>" class="jmd-link workspace-nav-link" data-title="Hồ sơ đã ứng tuyển">
                    <?= icon('applied', '', 18) ?> <span>Hồ sơ đã ứng tuyển</span>
                </a>
                <a href="<?= url('/seeker/meetings') ?>" class="jmd-link workspace-nav-link" data-title="Lịch hẹn & phỏng vấn">
                    <?= icon('meetings', '', 18) ?> <span>Lịch hẹn & phỏng vấn</span>
                </a>
                <a href="<?= url('/seeker/messages') ?>" class="jmd-link workspace-nav-link" data-title="Tin nhắn trao đổi">
                    <?= icon('chat', '', 18) ?> <span>Tin nhắn trao đổi</span>
                    <?php if ($mUnreadMsgs > 0): ?>
                        <span class="jmd-badge-new"><?= $mUnreadMsgs ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= url('/seeker/reviews') ?>" class="jmd-link workspace-nav-link" data-title="Đánh giá ca làm (Part-time)">
                    <?= icon('reviews', '', 18) ?> <span>Đánh giá ca làm</span>
                    <span style="font-size: 0.65rem; font-weight: 700; background: #e0f2fe; color: #0284c7; padding: 0.1rem 0.35rem; border-radius: 4px; margin-left: auto;">Part-time</span>
                </a>

                <div class="jmd-group-title">Hồ sơ & cài đặt</div>
                <a href="<?= url('/seeker/profile') ?>" class="jmd-link workspace-nav-link" data-title="Hồ sơ năng lực">
                    <?= icon('user', '', 18) ?> <span>Hồ sơ năng lực</span>
                </a>
                <a href="<?= url('/seeker/settings') ?>" class="jmd-link workspace-nav-link" data-title="Cài đặt & cam kết">
                    <?= icon('settings', '', 18) ?> <span>Cài đặt & cam kết</span>
                </a>
                <a href="<?= url('/auth/change-password') ?>" class="jmd-link workspace-nav-link" data-title="Đổi mật khẩu">
                    <?= icon('lock', '', 18) ?> <span>Đổi mật khẩu</span>
                </a>

            <?php elseif ($mRole === 'EMPLOYER_ADMIN'): ?>
                <div class="jmd-group-title">Quản lý tuyển dụng</div>
                <a href="<?= url('/employer/dashboard') ?>" class="jmd-link workspace-nav-link active" data-title="Tổng quan tuyển dụng">
                    <?= icon('dashboard', '', 18) ?> <span>Tổng quan tuyển dụng</span>
                </a>
                <a href="<?= url('/employer/jobs/create') ?>" class="jmd-link workspace-nav-link" data-title="Đăng tin mới" style="color: #0d9488; font-weight: 700;">
                    <?= icon('posts', '', 18) ?> <span>+ Đăng tin mới</span>
                </a>
                <a href="<?= url('/employer/jobs') ?>" class="jmd-link workspace-nav-link" data-title="Danh sách tin đăng">
                    <?= icon('list', '', 18) ?> <span>Danh sách tin đăng</span>
                </a>

                <div class="jmd-group-title">Tiếp nhận & phỏng vấn</div>
                <a href="<?= url('/employer/applications') ?>" class="jmd-link workspace-nav-link" data-title="Hồ sơ người lao động">
                    <?= icon('applications', '', 18) ?> <span>Hồ sơ người lao động</span>
                </a>
                <a href="<?= url('/employer/candidates/recommended') ?>" class="jmd-link workspace-nav-link" data-title="Quản lý NLĐ đề xuất">
                    <?= icon('zap', '', 18) ?> <span>Ứng viên gợi ý AI</span>
                    <span class="jmd-badge-new">AI</span>
                </a>
                <a href="<?= url('/employer/meetings') ?>" class="jmd-link workspace-nav-link" data-title="Lịch hẹn & phỏng vấn">
                    <?= icon('meetings', '', 18) ?> <span>Lịch hẹn & phỏng vấn</span>
                </a>

                <div class="jmd-group-title">Trao đổi & đánh giá</div>
                <a href="<?= url('/employer/messages') ?>" class="jmd-link workspace-nav-link" data-title="Tin nhắn trao đổi">
                    <?= icon('chat', '', 18) ?> <span>Tin nhắn trao đổi</span>
                    <?php if ($mUnreadMsgs > 0): ?>
                        <span class="jmd-badge-new"><?= $mUnreadMsgs ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= url('/employer/candidate-reviews') ?>" class="jmd-link workspace-nav-link" data-title="Đánh giá sau ca (Part-time)">
                    <?= icon('reviews', '', 18) ?> <span>Đánh giá sau ca</span>
                    <span style="font-size: 0.65rem; font-weight: 700; background: #e0f2fe; color: #0284c7; padding: 0.1rem 0.35rem; border-radius: 4px; margin-left: auto;">Part-time</span>
                </a>

                <div class="jmd-group-title">Doanh nghiệp & cài đặt</div>
                <a href="<?= url('/employer/company-profile') ?>" class="jmd-link workspace-nav-link" data-title="Thông tin doanh nghiệp">
                    <?= icon('employers', '', 18) ?> <span>Thông tin doanh nghiệp</span>
                </a>
                <a href="<?= url('/employer/settings?tab=cert') ?>" class="jmd-link workspace-nav-link" data-title="Chữ ký số & hợp đồng">
                    <?= icon('certificate', '', 18) ?> <span>Chữ ký số & hợp đồng</span>
                </a>
                <a href="<?= url('/employer/settings?tab=auth') ?>" class="jmd-link workspace-nav-link" data-title="Ủy quyền tuyển dụng">
                    <?= icon('handshake', '', 18) ?> <span>Giấy ủy quyền tuyển dụng</span>
                </a>
                <a href="<?= url('/employer/settings?tab=mail') ?>" class="jmd-link workspace-nav-link" data-title="Cài đặt Gmail">
                    <?= icon('mail', '', 18) ?> <span>Thông báo Gmail</span>
                </a>
                <a href="<?= url('/auth/change-password') ?>" class="jmd-link workspace-nav-link" data-title="Đổi mật khẩu">
                    <?= icon('lock', '', 18) ?> <span>Đổi mật khẩu</span>
                </a>

            <?php elseif ($mRole === 'INTERMEDIARY'): ?>
                <div class="jmd-group-title">Tổng quan</div>
                <a href="<?= url('/intermediary/dashboard') ?>" class="jmd-link workspace-nav-link active" data-title="Bảng điều khiển">
                    <?= icon('dashboard', '', 18) ?> <span>Bảng điều khiển</span>
                </a>
                <a href="<?= url('/jobs') ?>" class="jmd-link workspace-nav-link" data-title="Tra cứu việc làm sàn">
                    <?= icon('search', '', 18) ?> <span>Tra cứu việc làm sàn</span>
                </a>
                <a href="<?= url('/seeker/messages') ?>" class="jmd-link workspace-nav-link" data-title="Tin nhắn trao đổi">
                    <?= icon('chat', '', 18) ?> <span>Tin nhắn trao đổi</span>
                </a>
                <a href="<?= url('/intermediary/settings?tab=contracts') ?>" class="jmd-link workspace-nav-link" data-title="Hợp đồng ủy quyền">
                    <?= icon('handshake', '', 18) ?> <span>Hợp đồng & giấy ủy quyền</span>
                </a>
                <a href="<?= url('/auth/change-password') ?>" class="jmd-link workspace-nav-link" data-title="Đổi mật khẩu">
                    <?= icon('lock', '', 18) ?> <span>Đổi mật khẩu</span>
                </a>

            <?php elseif (in_array($mRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])): ?>
                <div class="jmd-group-title">Hệ thống & báo cáo</div>
                <a href="<?= url('/admin/dashboard') ?>" class="jmd-link workspace-nav-link active" data-title="Bảng điều khiển">
                    <?= icon('dashboard', '', 18) ?> <span>Bảng điều khiển</span>
                </a>
                <a href="<?= url('/admin/ai-analytics') ?>" class="jmd-link workspace-nav-link" data-title="Phân tích AI">
                    <?= icon('reports', '', 18) ?> <span>Phân tích & thống kê AI</span>
                </a>
                <a href="<?= url('/admin/profile-history') ?>" class="jmd-link workspace-nav-link" data-title="Lịch sử hồ sơ">
                    <?= icon('history', '', 18) ?> <span>Lịch sử thay đổi hồ sơ</span>
                </a>

                <div class="jmd-group-title">Quản trị người dùng & sàn</div>
                <a href="<?= url('/admin/accounts') ?>" class="jmd-link workspace-nav-link" data-title="Quản lý tài khoản">
                    <?= icon('users', '', 18) ?> <span>Quản lý tài khoản</span>
                </a>
                <a href="<?= url('/admin/employers') ?>" class="jmd-link workspace-nav-link" data-title="Xác minh doanh nghiệp">
                    <?= icon('building', '', 18) ?> <span>Xác thực GPKD doanh nghiệp</span>
                </a>
                <a href="<?= url('/admin/job-posts') ?>" class="jmd-link workspace-nav-link" data-title="Kiểm duyệt tin tuyển dụng">
                    <?= icon('list', '', 18) ?> <span>Kiểm duyệt tin tuyển dụng</span>
                </a>
                <a href="<?= url('/admin/staffs') ?>" class="jmd-link workspace-nav-link" data-title="Nhân viên hỗ trợ">
                    <?= icon('staffs', '', 18) ?> <span>Quản lý nhân viên CSKH</span>
                </a>

                <div class="jmd-group-title">Cấu hình & kỹ thuật</div>
                <a href="<?= url('/admin/ai-settings') ?>" class="jmd-link workspace-nav-link" data-title="Cấu hình AI">
                    <?= icon('ai', '', 18) ?> <span>Cấu hình mô hình AI</span>
                </a>
                <a href="<?= url('/admin/settings') ?>" class="jmd-link workspace-nav-link" data-title="Cài đặt hệ thống">
                    <?= icon('settings', '', 18) ?> <span>Cài đặt máy chủ & root CA</span>
                </a>
                <a href="<?= url('/auth/change-password') ?>" class="jmd-link workspace-nav-link" data-title="Đổi mật khẩu">
                    <?= icon('lock', '', 18) ?> <span>Đổi mật khẩu</span>
                </a>
            <?php endif; ?>

            <!-- Nút gọi trợ lý Ảo Joby AI -->
            <button type="button" class="jmd-link jmd-link-bot" onclick="window.joblinkOpenChat && window.joblinkOpenChat(); document.getElementById('joblinkMobileDrawer').classList.remove('open');">
                <img src="<?= getJobyAvatar() ?>" alt="Joby" style="width:22px;height:22px;border-radius:50%;object-fit:cover;vertical-align:middle;">
                <span>Trợ lý thông minh Joby AI</span>
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
