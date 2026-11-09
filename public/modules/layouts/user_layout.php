<?php
// 1. KIỂM TRA CHẾ ĐỘ NHÚNG TAB (EMBEDDED VIEWPORT)
$isEmbedded = (isset($_GET['embedded']) && $_GET['embedded'] == '1')
    || (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'iframe');
if ($isEmbedded):
?>
<!DOCTYPE html>
<html lang="vi" style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'JobLink' ?></title>
    <link rel="icon" type="image/png" href="<?= getSystemLogo() ?>">
    <link rel="shortcut icon" type="image/png" href="<?= getSystemLogo() ?>">
    <link rel="apple-touch-icon" href="<?= getSystemLogo() ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css?v=' . filemtime(__DIR__ . '/../../css/style.css')) ?>">
    <link rel="stylesheet" href="<?= asset('css/dialog.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ai_chatbox.css?v=' . (file_exists(__DIR__ . '/../../css/ai_chatbox.css') ? filemtime(__DIR__ . '/../../css/ai_chatbox.css') : time())) ?>">
    <link rel="stylesheet" href="<?= asset('css/mobile.css') ?>">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ((array)$extraCss as $cssFile): ?>
            <?php 
                $cssFsPath = __DIR__ . '/../../' . ltrim($cssFile, '/');
                $cssVer = file_exists($cssFsPath) ? filemtime($cssFsPath) : time();
            ?>
            <link rel="stylesheet" href="<?= asset($cssFile . '?v=' . $cssVer) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <script>window.APP_URL = '<?= url('') ?>';</script>
</head>
<body class="embedded-body">
    <div class="embedded-content-wrapper">
        <?= $content ?? '' ?>
    </div>
    <script>
        // Tự động nhận diện cấu trúc trang để kích hoạt cuộn mượt mà
        (function() {
            function detectScrollLayout() {
                var hasZone = !!document.querySelector('.workspace-zone-body');
                var hasMsg = !!document.querySelector('.messenger-container');
                var wrapper = document.querySelector('.embedded-content-wrapper');
                if (hasZone) {
                    document.body.classList.add('has-workspace-zone');
                    document.body.classList.remove('scrollable-page');
                    if (wrapper) {
                        wrapper.classList.add('has-workspace-zone');
                        wrapper.classList.remove('scrollable-page');
                    }
                } else if (hasMsg) {
                    document.body.classList.add('has-messenger');
                    document.body.classList.remove('scrollable-page');
                    if (wrapper) {
                        wrapper.classList.add('has-messenger');
                        wrapper.classList.remove('scrollable-page');
                    }
                } else {
                    document.body.classList.add('scrollable-page');
                    document.body.classList.remove('has-workspace-zone', 'has-messenger');
                    if (wrapper) {
                        wrapper.classList.add('scrollable-page');
                        wrapper.classList.remove('has-workspace-zone', 'has-messenger');
                    }
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', detectScrollLayout);
            } else {
                detectScrollLayout();
            }
        })();
    </script>
    <script src="<?= asset('js/dialog.js') ?>"></script>
    <script src="<?= asset('js/app.js?v=' . (file_exists(__DIR__ . '/../../js/app.js') ? filemtime(__DIR__ . '/../../js/app.js') : time())) ?>"></script>
    <script src="<?= asset('js/unsaved_changes_bar.js?v=' . (file_exists(__DIR__ . '/../../js/unsaved_changes_bar.js') ? filemtime(__DIR__ . '/../../js/unsaved_changes_bar.js') : time())) ?>"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ((array)$extraJs as $jsFile): ?>
            <script src="<?= asset($jsFile) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <?= class_exists('DialogHelper') ? DialogHelper::renderFlashScript() : '' ?>
    <script>
        // 1. Luôn giữ mọi liên kết mở trực tiếp trong trang trắng, bỏ target="_blank"
        document.addEventListener('click', function(e) {
            var link = e.target.closest('a');
            if (link) {
                var href = link.getAttribute('href');
                if (!href || href.startsWith('javascript:') || href.startsWith('#')) return;

                // Nếu click Đăng xuất -> Xóa sạch toàn bộ tab con của workspace
                if (href.includes('logout')) {
                    try {
                        sessionStorage.removeItem('user_workspace_persistent_tabs');
                        sessionStorage.removeItem('user_workspace_restore_tabs');
                        sessionStorage.removeItem('admin_workspace_persistent_tabs');
                        sessionStorage.removeItem('master_restore_tabs');
                        localStorage.removeItem('user_workspace_persistent_tabs');
                        localStorage.removeItem('admin_workspace_persistent_tabs');
                        if (window.parent && window.parent !== window && window.parent.MasterWorkspace) {
                            window.parent.MasterWorkspace.clearAllWorkspaceTabs();
                        }
                    } catch(err) {}
                    return;
                }

                if (link.hasAttribute('download')) return;

                // Nếu đang nằm trong iframe con của MasterWorkspace, mở như một tab con mới trên thanh tab mẹ
                if (window.parent && window.parent !== window) {
                    try {
                        var targetUrl = new URL(link.href, window.location.origin);
                        if (targetUrl.origin === window.location.origin) {
                            e.preventDefault();
                            link.removeAttribute('target');
                            var title = link.getAttribute('data-title') || link.innerText.trim() || 'Chi Tiết';
                            title = title.replace(/[→←&rarr;&larr;]/g, '').trim();
                            if (title.length > 25) title = title.substring(0, 22) + '...';

                            if (window.parent.MasterWorkspace && typeof window.parent.MasterWorkspace.openTab === 'function') {
                                window.parent.MasterWorkspace.openTab(link.href, title);
                            } else {
                                window.parent.postMessage({
                                    action: 'openTab',
                                    url: link.href,
                                    title: title
                                }, '*');
                            }
                        }
                    } catch(err) {}
                }
            }
        });

        // 2. Tự động kèm cờ embedded=1 khi submit form để luôn giữ nguyên trong trang trắng
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (form && !form.querySelector('input[name="embedded"]')) {
                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'embedded';
                hidden.value = '1';
                form.appendChild(hidden);
            }
        });
        // 3. Bắt F5 / Ctrl+R bên trong iframe để chỉ tải lại riêng iframe này
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F5' || (e.ctrlKey && e.key.toLowerCase() === 'r')) {
                e.preventDefault();
                window.location.reload();
            }
        });
    </script>
</body>
</html>
<?php
    exit;
endif;

// 2. TỰ ĐỘNG CHUYỂN HƯỚNG CÁC TRANG CON VỀ TRANG GỐC DASHBOARD ĐỂ MỞ DẠNG TAB CON
$userSession = $_SESSION['user'] ?? [];
$uRole = $userSession['role_code'] ?? '';
$targetDashUrl = url('/seeker/dashboard');
$targetDashPath = '/seeker/dashboard';
if ($uRole === 'EMPLOYER_ADMIN') {
    $targetDashUrl = url('/employer/dashboard');
    $targetDashPath = '/employer/dashboard';
} elseif ($uRole === 'INTERMEDIARY') {
    $targetDashUrl = url('/intermediary/dashboard');
    $targetDashPath = '/intermediary/dashboard';
}

$reqUri = $_SERVER['REQUEST_URI'] ?? '/';
$reqPath = parse_url($reqUri, PHP_URL_PATH) ?? '/';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$cleanPath = str_replace('\\', '/', $scriptDir);
if ($cleanPath !== '/' && $cleanPath !== '' && strpos($reqPath, $cleanPath) === 0) {
    $reqPath = substr($reqPath, strlen($cleanPath));
}
$reqPath = preg_replace('#^/(Joblink|public|Joblink/public)#i', '', $reqPath);
$cleanReq = '/' . trim($reqPath, '/');

$shouldRedirectToDash = (
    strpos($cleanReq, '/employer') === 0 || 
    strpos($cleanReq, '/seeker') === 0 || 
    strpos($cleanReq, '/intermediary') === 0 ||
    strpos($cleanReq, '/auth/change-password') === 0 ||
    strpos($cleanReq, '/employers') === 0 ||
    strpos($cleanReq, '/jobs') === 0 ||
    strpos($cleanReq, '/messages') === 0 ||
    strpos($cleanReq, '/notifications') === 0 ||
    strpos($cleanReq, '/reviews') === 0
);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $cleanReq !== $targetDashPath && $shouldRedirectToDash) {
    header('Location: ' . $targetDashUrl . '?open=' . urlencode($reqUri));
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'JobLink - Quản Trị Người Dùng' ?></title>
    <link rel="icon" type="image/png" href="<?= getSystemLogo() ?>">
    <link rel="shortcut icon" type="image/png" href="<?= getSystemLogo() ?>">
    <link rel="apple-touch-icon" href="<?= getSystemLogo() ?>">
    <script>
        // Tự động nhận diện nếu chạy trong iframe để tránh lồng layout 2 lần
        if (window.self !== window.top && !window.location.search.includes('embedded=1')) {
            var sep = window.location.href.indexOf('?') !== -1 ? '&' : '?';
            window.location.replace(window.location.href + sep + 'embedded=1');
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css?v=' . filemtime(__DIR__ . '/../../css/style.css')) ?>">
    <link rel="stylesheet" href="<?= asset('css/dialog.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ai_chatbox.css?v=' . (file_exists(__DIR__ . '/../../css/ai_chatbox.css') ? filemtime(__DIR__ . '/../../css/ai_chatbox.css') : time())) ?>">
    <link rel="stylesheet" href="<?= asset('css/mobile.css') ?>">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ((array)$extraCss as $cssFile): ?>
            <?php 
                $cssFsPath = __DIR__ . '/../../' . ltrim($cssFile, '/');
                $cssVer = file_exists($cssFsPath) ? filemtime($cssFsPath) : time();
            ?>
            <link rel="stylesheet" href="<?= asset($cssFile . '?v=' . $cssVer) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <script>window.APP_URL = '<?= url('') ?>';</script>
</head>
<body class="<?= ($uRole === 'JOB_SEEKER') ? 'role-job-seeker' : '' ?>">
    <?php require_once __DIR__ . '/mobile_nav.php'; ?>
    <?php 
        $userDisplayName = $userSession['display_name'] ?? ($userSession['username'] ?? 'Người dùng');
        $userUsername = $userSession['username'] ?? 'user';
        $userEmail = $userSession['email'] ?? '';

        $dashUrl = url('/seeker/dashboard');
        $roleFullName = 'Người Lao Động';
        if ($uRole === 'EMPLOYER_ADMIN') {
            $dashUrl = url('/employer/dashboard');
            $roleFullName = 'Nhà Tuyển Dụng';
        } elseif ($uRole === 'INTERMEDIARY') {
            $dashUrl = url('/intermediary/dashboard');
            $roleFullName = 'Người Trung Gian';
        }
        $roleBadge = getSidebarRoleBadge($uRole, $roleFullName);

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
        $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    ?>

    <div class="dashboard-layout">
        <!-- THANH DỌC BÊN TRÁI CHỨA MENU CHÍNH & TIỆN ÍCH -->
        <aside class="sidebar">
            <?php
                $userAvatar = getAvatarUrl($profile['avatar_url'] ?? ($userSession['avatar_url'] ?? ''), $uRole, $profile['gender'] ?? '');
                
                $userProfileUrl = url('/seeker/profile');
                $userProfileTitle = 'Hồ Sơ Năng Lực';
                if ($uRole === 'EMPLOYER_ADMIN') {
                    $userProfileUrl = url('/employer/company-profile');
                    $userProfileTitle = 'Thông Tin Doanh Nghiệp';
                } elseif ($uRole === 'INTERMEDIARY') {
                    $userProfileUrl = url('/seeker/profile');
                    $userProfileTitle = 'Hồ Sơ Năng Lực';
                }
            ?>
            <!-- 1. ĐẦU THANH DỌC: LOGO VÀ CỤM CHUÔNG, CHAT, AVATAR -->
            <div class="sidebar-header">
                <div class="sidebar-brand-row">
                    <a href="<?= $dashUrl ?>" class="sidebar-brand-box">
                        <span class="sidebar-brand-title">
                            <span class="sidebar-brand-icon">
                                <img src="<?= getSystemLogo() ?>" alt="JobLink" class="sidebar-brand-logo-img">
                            </span>
                            <span class="sidebar-brand-text">JobLink</span>
                        </span>
                        <span class="sidebar-brand-badge" title="<?= htmlspecialchars($roleFullName) ?>"><?= htmlspecialchars($roleBadge) ?></span>
                    </a>
                    <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Thu gọn menu (Ctrl+B)" aria-label="Thu gọn hoặc mở rộng thanh menu">
                        <span class="collapse-icon-left"><?= icon('sidebar_toggle_collapse', '', 18) ?></span>
                        <span class="collapse-icon-right"><?= icon('sidebar_toggle_expand', '', 18) ?></span>
                    </button>
                </div>

                <!-- CỤM NHƯ HÌNH: CHUÔNG 3 + CHAT + AVATAR KÈM DROPDOWN -->
                <div class="sidebar-user-cluster">
                    <div class="sidebar-cluster-row">
                        <!-- Thông tin tóm tắt người dùng - Bấm vào tự mở Hồ Sơ Năng Lực trong tab dashboard -->
                        <a href="<?= $userProfileUrl ?>" class="sidebar-user-meta workspace-nav-link" data-title="<?= htmlspecialchars($userProfileTitle) ?>" title="Xem <?= htmlspecialchars($userProfileTitle) ?>" style="text-decoration: none; cursor: pointer;">
                            <span class="sidebar-user-name" title="<?= htmlspecialchars($userDisplayName) ?>"><?= htmlspecialchars($userDisplayName) ?></span>
                            <span class="sidebar-user-sub"><?= htmlspecialchars($roleFullName) ?> • @<?= htmlspecialchars($userUsername) ?></span>
                        </a>

                        <!-- Cụm 3 nút icon -->
                        <div class="sidebar-cluster-icons">
                            <!-- 1 & 2. Nút Chuông & Nút Chat -->
                            <div class="sidebar-aux-icons">
                                <?= renderHeaderIcons($userSession) ?>
                            </div>

                            <!-- 3. Avatar tròn hiển thị danh tính - Bấm vào tự mở Hồ Sơ Năng Lực trong tab dashboard -->
                            <a href="<?= $userProfileUrl ?>" class="sidebar-avatar-item workspace-nav-link" data-title="<?= htmlspecialchars($userProfileTitle) ?>" title="Xem <?= htmlspecialchars($userProfileTitle) ?>" style="text-decoration: none; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                <img src="<?= htmlspecialchars($userAvatar) ?>" alt="avatar" class="fb-nav-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. THÂN THANH DỌC: MENU CHỨC NĂNG (CUỘN ĐỘC LẬP) -->
            <div class="sidebar-nav-scroll">
                <?php if ($uRole === 'JOB_SEEKER'): ?>
                    <div class="sidebar-heading" title="Khám Phá Việc Làm"><span>Khám Phá Việc Làm</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/seeker/dashboard') ?>" class="sidebar-link workspace-nav-link" data-title="Bảng Điều Khiển">
                            <span class="nav-avatar-box nav-avatar-dashboard">
                                <?= icon('dashboard', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Bảng Điều Khiển</span>
                        </a></li>
                        <li><a href="<?= url('/jobs') ?>" class="sidebar-link workspace-nav-link" data-title="Tìm Việc & Gợi Ý AI">
                            <span class="nav-avatar-box nav-avatar-ai-analytics">
                                <?= icon('search', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Tìm Việc & Gợi Ý AI</span>
                            <span class="nav-sub-badge" style="font-size: 0.65rem; font-weight: 800; background: linear-gradient(135deg, #0d9488, #0284c7); color: #ffffff; padding: 0.1rem 0.4rem; border-radius: 4px; margin-left: auto;">AI</span>
                        </a></li>
                        <li><a href="<?= url('/seeker/saved-jobs') ?>" class="sidebar-link workspace-nav-link" data-title="Việc Làm Đã Lưu">
                            <span class="nav-avatar-box nav-avatar-contracts">
                                <?= icon('saved', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Việc Làm Đã Lưu</span>
                        </a></li>
                    </ul>

                    <div class="sidebar-heading" title="Tiến Độ Tuyển Dụng"><span>Tiến Độ Tuyển Dụng</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/seeker/applied-jobs') ?>" class="sidebar-link workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển">
                            <span class="nav-avatar-box nav-avatar-employers">
                                <?= icon('applied', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Hồ Sơ Đã Ứng Tuyển</span>
                        </a></li>
                        <li><a href="<?= url('/seeker/meetings') ?>" class="sidebar-link workspace-nav-link" data-title="Lịch Hẹn & Phỏng Vấn">
                            <span class="nav-avatar-box nav-avatar-authorizations">
                                <?= icon('meetings', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Lịch Hẹn & Phỏng Vấn</span>
                        </a></li>
                        <li><a href="<?= url('/seeker/messages') ?>" class="sidebar-link workspace-nav-link" data-title="Tin Nhắn Trao Đổi">
                            <span class="nav-avatar-box nav-avatar-messages">
                                <?= icon('chat', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Tin Nhắn Trao Đổi</span>
                        </a></li>
                    </ul>

                    <div class="sidebar-heading" title="Đánh Giá & Phản Hồi"><span>Đánh Giá & Phản Hồi</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/seeker/reviews') ?>" class="sidebar-link workspace-nav-link" data-title="Đánh Giá Ca Làm (Part-time)">
                            <span class="nav-avatar-box nav-avatar-reviews">
                                <?= icon('reviews', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Đánh Giá Ca Làm</span>
                            <span class="nav-sub-badge" style="font-size: 0.65rem; font-weight: 700; background: #e0f2fe; color: #0284c7; padding: 0.1rem 0.35rem; border-radius: 4px; margin-left: auto;">Part-time</span>
                        </a></li>
                    </ul>

                    <div class="sidebar-heading" title="Tài Khoản & Cá Nhân"><span>Tài Khoản & Cá Nhân</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/seeker/profile') ?>" class="sidebar-link workspace-nav-link" data-title="Hồ Sơ Năng Lực">
                            <span class="nav-avatar-box nav-avatar-accounts">
                                <?= icon('user', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Hồ Sơ Năng Lực</span>
                        </a></li>
                        <li><a href="<?= url('/auth/change-password') ?>" class="sidebar-link workspace-nav-link" data-title="Đổi Mật Khẩu">
                            <span class="nav-avatar-box nav-avatar-password">
                                <?= icon('lock', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Đổi Mật Khẩu</span>
                        </a></li>
                    </ul>

                <?php elseif ($uRole === 'INTERMEDIARY'): ?>
                    <div class="sidebar-heading" title="Tổng Quan"><span>Tổng Quan</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/intermediary/dashboard') ?>" class="sidebar-link workspace-nav-link" data-title="Bảng Điều Khiển">
                            <span class="nav-avatar-box nav-avatar-dashboard">
                                <?= icon('dashboard', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Bảng Điều Khiển</span>
                        </a></li>
                    </ul>

                    <div class="sidebar-heading" title="Việc Làm & Khách Hàng"><span>Việc Làm & Khách Hàng</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/jobs') ?>" class="sidebar-link workspace-nav-link" data-title="Tra Cứu Việc Làm Sàn">
                            <span class="nav-avatar-box nav-avatar-contracts">
                                <?= icon('search', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Tra Cứu Việc Làm Sàn</span>
                        </a></li>
                        <li><a href="<?= url('/seeker/messages') ?>" class="sidebar-link workspace-nav-link" data-title="Tin Nhắn Trao Đổi">
                            <span class="nav-avatar-box nav-avatar-messages">
                                <?= icon('chat', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Tin Nhắn Trao Đổi</span>
                        </a></li>
                    </ul>

                    <div class="sidebar-heading" title="Tài Khoản & Bảo Mật"><span>Tài Khoản & Bảo Mật</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/auth/change-password') ?>" class="sidebar-link workspace-nav-link" data-title="Đổi Mật Khẩu">
                            <span class="nav-avatar-box nav-avatar-password">
                                <?= icon('lock', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Đổi Mật Khẩu</span>
                        </a></li>
                    </ul>

                <?php else: ?>
                    <div class="sidebar-heading" title="Tổng Quan"><span>Tổng Quan</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/employer/dashboard') ?>" class="sidebar-link workspace-nav-link" data-title="Tổng Quan Tuyển Dụng">
                            <span class="nav-avatar-box nav-avatar-dashboard">
                                <?= icon('dashboard', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Tổng Quan Tuyển Dụng</span>
                        </a></li>
                    </ul>

                    <div class="sidebar-heading" title="Quản Lý Tin Tuyển Dụng"><span>Quản Lý Tin Tuyển Dụng</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/employer/jobs/create') ?>" class="sidebar-link workspace-nav-link" data-title="Đăng Tin Mới">
                            <span class="nav-avatar-box nav-avatar-job-posts">
                                <?= icon('posts', '', 17) ?>
                            </span>
                            <span class="nav-link-text" style="color: #0d9488; font-weight: 700;">+ Đăng Tin Mới</span>
                        </a></li>
                        <li><a href="<?= url('/employer/jobs') ?>" class="sidebar-link workspace-nav-link" data-title="Danh Sách Tin Đăng">
                            <span class="nav-avatar-box nav-avatar-contracts">
                                <?= icon('list', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Danh Sách Tin Đăng</span>
                        </a></li>
                    </ul>

                    <div class="sidebar-heading" title="Tiếp Nhận & Phỏng Vấn"><span>Tiếp Nhận & Phỏng Vấn</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/employer/applications') ?>" class="sidebar-link workspace-nav-link" data-title="Hồ Sơ Người Lao Động">
                            <span class="nav-avatar-box nav-avatar-employers">
                                <?= icon('applications', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Hồ Sơ Người Lao Động</span>
                        </a></li>
                        <li><a href="<?= url('/employer/candidates/recommended') ?>" class="sidebar-link workspace-nav-link" data-title="Quản Lý NLĐ Đề Xuất">
                            <span class="nav-avatar-box nav-avatar-ai-analytics">
                                <?= icon('zap', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Quản Lý NLĐ Đề Xuất</span>
                        </a></li>
                        <li><a href="<?= url('/employer/meetings') ?>" class="sidebar-link workspace-nav-link" data-title="Lịch Hẹn & Phỏng Vấn">
                            <span class="nav-avatar-box nav-avatar-authorizations">
                                <?= icon('meetings', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Lịch Hẹn & Phỏng Vấn</span>
                        </a></li>
                    </ul>

                    <div class="sidebar-heading" title="Trao Đổi & Đánh Giá 2 Chiều"><span>Trao Đổi & Đánh Giá 2 Chiều</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/employer/messages') ?>" class="sidebar-link workspace-nav-link" data-title="Tin Nhắn Trao Đổi">
                            <span class="nav-avatar-box nav-avatar-messages">
                                <?= icon('chat', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Tin Nhắn Trao Đổi</span>
                        </a></li>
                        <li><a href="<?= url('/employer/candidate-reviews') ?>" class="sidebar-link workspace-nav-link" data-title="Đánh Giá Sau Ca (Part-time)">
                            <span class="nav-avatar-box nav-avatar-reviews">
                                <?= icon('reviews', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Đánh Giá Sau Ca</span>
                            <span class="nav-sub-badge" style="font-size: 0.65rem; font-weight: 700; background: #e0f2fe; color: #0284c7; padding: 0.1rem 0.35rem; border-radius: 4px; margin-left: auto;">Part-time</span>
                        </a></li>
                    </ul>

                    <div class="sidebar-heading" title="Tài Khoản & Doanh Nghiệp"><span>Tài Khoản & Doanh Nghiệp</span></div>
                    <ul class="sidebar-menu">
                        <li><a href="<?= url('/employer/company-profile') ?>" class="sidebar-link workspace-nav-link" data-title="Thông Tin Doanh Nghiệp">
                            <span class="nav-avatar-box nav-avatar-accounts">
                                <?= icon('employers', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Thông Tin Doanh Nghiệp</span>
                        </a></li>
                        <li><a href="<?= url('/auth/change-password') ?>" class="sidebar-link workspace-nav-link" data-title="Đổi Mật Khẩu">
                            <span class="nav-avatar-box nav-avatar-password">
                                <?= icon('lock', '', 17) ?>
                            </span>
                            <span class="nav-link-text">Đổi Mật Khẩu</span>
                        </a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- 3. ĐÁY THANH DỌC: PHIÊN BẢN & ĐĂNG XUẤT NHANH -->
            <div class="sidebar-footer">
                <span class="sidebar-version-text">JobLink Portal</span>
                <a href="<?= url('/auth/logout') ?>" class="sidebar-logout-btn" title="Đăng xuất" data-title="Đăng xuất">
                    <span class="nav-avatar-box nav-avatar-logout">
                        <?= icon('logout', '', 15) ?>
                    </span>
                    <span class="sidebar-logout-text">Đăng xuất</span>
                </a>
            </div>
        </aside>

        <!-- VÙNG LÀM VIỆC CHÍNH (MASTER WORKSPACE CANVAS WITH CHROME TABS) -->
        <main class="workspace-main-area">
            <!-- Thanh chỉ báo tải trang siêu mỏng -->
            <div class="workspace-loading-bar" id="workspaceLoadingBar"></div>

            <!-- 1. THANH GOOGLE CHROME TABS STRIP TRÊN CÙNG CỦA TRANG GỐC -->
            <div class="chrome-workspace-header" id="masterChromeHeader">
                <div class="chrome-tabs-strip-container">
                    <div class="chrome-tabs-strip" id="masterChromeTabsStrip">
                        <!-- TAB MẸ (ROOT PARENT TAB): TRANG CHỦ - CHỈ HIỂN THỊ LOGO WEBSITE CỐ ĐỊNH NHỎ GỌN -->
                        <div class="chrome-tab active chrome-tab-root" id="mtab-root" onclick="MasterWorkspace.switchTab('root')" title="Trang Chủ">
                            <span class="chrome-tab-icon">
                                <img src="<?= getSystemLogo() ?>" alt="Trang Chủ" class="chrome-root-logo">
                            </span>
                            <span class="chrome-tab-reload" onclick="event.stopPropagation(); MasterWorkspace.reloadTab('root');" title="Tải lại Trang Chủ">
                                <?= icon('refresh', '', 11) ?>
                            </span>
                        </div>
                        <!-- CÁC TAB CON (CHILD TABS) SẼ ĐƯỢC TẠO ĐỘNG TẠI ĐÂY -->
                    </div>
                    <div class="chrome-tab-actions">
                        <button type="button" class="chrome-action-btn danger" id="masterCloseAllBtn" onclick="MasterWorkspace.closeAllChildTabs()" style="display:none;" title="Đóng tất cả các tab con đang mở">
                            <svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            <span>Đóng hết tab con</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. VÙNG CHỨA CÁC NỘI DUNG PANEL (GIỮ NGUYÊN TRẠNG THÁI CỦA TỪNG TAB) -->
            <div class="master-panels-container" id="masterPanelsContainer">
                <!-- PANEL MẸ: HIỂN THỊ NỘI DUNG BẢNG ĐIỀU KHIỂN GỐC -->
                <div class="master-tab-panel-root" id="mpanel-root">
                    <div class="embedded-content-wrapper">
                        <?= $content ?? '' ?>
                    </div>
                </div>

                <!-- CÁC PANEL CON CHỨA IFRAME ĐỘC LẬP SẼ ĐƯỢC CHÈN ĐỘNG VÀO ĐÂY -->
            </div>
        </main>
    </div>

    <script src="<?= asset('js/dialog.js') ?>"></script>
    <script src="<?= asset('js/app.js?v=' . (file_exists(__DIR__ . '/../../js/app.js') ? filemtime(__DIR__ . '/../../js/app.js') : time())) ?>"></script>
    <script src="<?= asset('js/unsaved_changes_bar.js?v=' . (file_exists(__DIR__ . '/../../js/unsaved_changes_bar.js') ? filemtime(__DIR__ . '/../../js/unsaved_changes_bar.js') : time())) ?>"></script>
    <script>
        window.APP_URL = '<?= url('') ?>';
        window.JOBY_GREETING = <?= json_encode(getAiGreeting(), JSON_UNESCAPED_UNICODE) ?>;
        window.JOBY_BUBBLE_DATA = <?= json_encode(getAiProactiveSummary(), JSON_UNESCAPED_UNICODE) ?>;
        window.JOBY_USER_ROLE = <?= json_encode($_SESSION['user']['role_code'] ?? 'GUEST') ?>;
        window.JOBY_USER_ID = <?= json_encode($_SESSION['user']['account_id'] ?? null) ?>;
        window.JOBY_IS_GUEST = <?= empty($_SESSION['user']) ? 'true' : 'false' ?>;
        window.JOBY_DEFAULT_SUGGESTIONS = <?= json_encode(AIService::generateSuggestionsByContext('', $_SESSION['user']['role_code'] ?? 'GUEST'), JSON_UNESCAPED_UNICODE) ?>;
        window.JOBY_AVATAR_URL = '<?= getJobyAvatar() ?>';
        window.JOBY_LOGIN_URL = '<?= url('/auth/login') ?>';
        window.JOBY_REGISTER_URL = '<?= url('/auth/register') ?>';
        window.JOBY_JOBS_URL = '<?= url('/jobs') ?>';

        // ====================================================================
        // GOOGLE CHROME MULTI-TAB WORKSPACE HUB (MÔ HÌNH TAB MẸ & TAB CON)
        // ====================================================================
        const MasterWorkspace = window.MasterWorkspace = {
            rootTabKey: 'root',
            currentActiveTab: 'root',
            openedTabs: new Map(), // tabKey -> { url, title, icon, tabEl, panelEl, iframeEl }
            loadingBar: null,

            init() {
                this.loadingBar = document.getElementById('workspaceLoadingBar');

                // Tự động nhận diện cấu trúc root panel để đảm bảo cuộn mượt
                const rootPanel = document.getElementById('mpanel-root');
                if (rootPanel) {
                    const hasZone = !!rootPanel.querySelector('.workspace-zone-body');
                    const hasMsg = !!rootPanel.querySelector('.messenger-container');
                    const wrapper = rootPanel.querySelector('.embedded-content-wrapper');
                    if (hasZone) {
                        rootPanel.classList.add('has-workspace-zone');
                        if (wrapper) wrapper.classList.add('has-workspace-zone');
                    } else if (hasMsg) {
                        rootPanel.classList.add('has-messenger');
                        if (wrapper) wrapper.classList.add('has-messenger');
                    } else {
                        rootPanel.classList.add('scrollable-page');
                        if (wrapper) wrapper.classList.add('scrollable-page');
                    }
                }

                // 1. Phục hồi các tab đang mở nếu trang mẹ vừa được reload (F5) trong cùng phiên của tài khoản này
                try {
                    const currentUserId = <?= json_encode($_SESSION['user']['account_id'] ?? null) ?>;
                    const savedState = sessionStorage.getItem('user_workspace_persistent_tabs') || sessionStorage.getItem('user_workspace_restore_tabs');
                    if (savedState) {
                        const parsed = JSON.parse(savedState);
                        // Nếu userId không khớp (đã đăng xuất hoặc chuyển sang tài khoản khác) -> Xóa sạch, không mở lại tab cũ!
                        if (parsed && parsed.userId && parsed.userId !== currentUserId) {
                            sessionStorage.removeItem('user_workspace_persistent_tabs');
                            sessionStorage.removeItem('user_workspace_restore_tabs');
                        } else if (parsed && Array.isArray(parsed.tabs)) {
                            parsed.tabs.forEach(t => {
                                this.openTab(t.url, t.title, t.icon);
                            });
                            if (parsed.activeTab && (parsed.activeTab === 'root' || this.openedTabs.has(parsed.activeTab))) {
                                this.switchTab(parsed.activeTab);
                            }
                        }
                    }
                } catch(e) {}

                // 2. Global Link Delegation: Bắt toàn bộ click thẻ <a> nội bộ để mở tab con tức thì, không bị khựng / reload trang mẹ
                document.addEventListener('click', (e) => {
                    const link = e.target.closest('a');
                    if (!link) return;

                    const rawHref = link.getAttribute('href');
                    if (!rawHref || rawHref.startsWith('javascript:') || rawHref.startsWith('#')) return;

                    // Nếu click Đăng xuất -> Xóa sạch toàn bộ tab con ngay lập tức
                    if (rawHref.includes('logout')) {
                        MasterWorkspace.clearAllWorkspaceTabs();
                        return;
                    }
                    if (link.getAttribute('download') !== null) return;

                    try {
                        const targetUrl = new URL(link.href, window.location.origin);
                        if (targetUrl.origin !== window.location.origin) return;

                        let cleanPath = targetUrl.pathname || '';
                        while (true) {
                            let prev = cleanPath;
                            cleanPath = cleanPath.replace(/^\/(Joblink|public)/i, '');
                            if (cleanPath === prev) break;
                        }
                        if (!cleanPath.startsWith('/')) cleanPath = '/' + cleanPath;

                        const roleDashboards = ['/employer/dashboard', '/seeker/dashboard', '/intermediary/dashboard', '/admin/dashboard', '/', ''];
                        const isRootDashboard = (cleanPath === '<?= $targetDashPath ?>' || roleDashboards.includes(cleanPath));

                        // Kiểm tra xem có phải link thuộc nghiệp vụ hệ thống không
                        const isInternalModule = cleanPath.startsWith('/seeker') 
                                              || cleanPath.startsWith('/employer') 
                                              || cleanPath.startsWith('/employers') 
                                              || cleanPath.startsWith('/intermediary') 
                                              || cleanPath.startsWith('/messages') 
                                              || cleanPath.startsWith('/notifications')
                                              || cleanPath.startsWith('/jobs')
                                              || cleanPath.startsWith('/contracts')
                                              || cleanPath.startsWith('/evaluations')
                                              || cleanPath.startsWith('/reviews')
                                              || cleanPath.startsWith('/auth/change-password');

                        if (isInternalModule && !isRootDashboard) {
                            e.preventDefault();

                            // Tự động đóng dropdown nếu có
                            document.querySelectorAll('.header-icon-container.is-open, .user-dropdown-container.is-open').forEach(c => c.classList.remove('is-open'));

                            let rawTitle = link.getAttribute('data-title')
                                        || link.querySelector('.kpi-title')?.innerText?.trim()
                                        || link.querySelector('.nav-link-text')?.innerText?.trim()
                                        || link.querySelector('span')?.innerText?.trim()
                                        || link.innerText?.trim()
                                        || 'Chức Năng';

                            rawTitle = rawTitle.replace(/[→←&rarr;&larr;]/g, '').trim();

                            let iconSvg = link.querySelector('.icon-svg')?.outerHTML || '';
                            MasterWorkspace.openTab(link.href, rawTitle, iconSvg, link);
                        } else if (isRootDashboard) {
                            e.preventDefault();
                            MasterWorkspace.switchTab('root');
                        }
                    } catch(err) {}
                });

                // 3. Lắng nghe thông điệp từ các iframe con (khi người dùng bấm link trong iframe con)
                window.addEventListener('message', (e) => {
                    if (e.data && e.data.action === 'openTab' && e.data.url) {
                        MasterWorkspace.openTab(e.data.url, e.data.title || 'Chi Tiết', e.data.icon || '');
                    }
                });

                // 4. Kiểm tra tham số ?open= trên URL để tự động mở tab con ban đầu
                const urlParams = new URLSearchParams(window.location.search);
                const initialOpenUrl = urlParams.get('open');
                if (initialOpenUrl) {
                    // Xóa param ?open= ngay lập tức để giữ URL luôn sạch là URL dashboard gốc
                    const cleanUrl = window.location.pathname;
                    window.history.replaceState(null, '', cleanUrl);

                    // Tìm sidebar tương ứng để lấy tiêu đề & icon
                    let matchedTitle = 'Chức Năng';
                    let matchedIcon = '';

                    if (initialOpenUrl.includes('/messages')) {
                        matchedTitle = 'Tin Nhắn & Hỗ Trợ';
                        matchedIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
                    } else if (initialOpenUrl.includes('/notifications')) {
                        matchedTitle = 'Tất Cả Thông Báo';
                        matchedIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>';
                    } else if (initialOpenUrl.includes('/auth/change-password')) {
                        matchedTitle = 'Đổi Mật Khẩu';
                        matchedIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>';
                    } else if (initialOpenUrl.includes('/employers/')) {
                        matchedTitle = 'Hồ Sơ Doanh Nghiệp';
                        matchedIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
                    } else if (initialOpenUrl.includes('/jobs/')) {
                        matchedTitle = 'Chi Tiết Việc Làm';
                        matchedIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
                    } else {
                        document.querySelectorAll('.sidebar .sidebar-link').forEach(l => {
                            const h = l.getAttribute('href');
                            if (h && initialOpenUrl.includes(h.replace(/[\?&]embedded=1/g, ''))) {
                                matchedTitle = l.getAttribute('data-title') || l.querySelector('span')?.innerText?.trim() || matchedTitle;
                                matchedIcon = l.querySelector('.icon-svg')?.outerHTML || '';
                            }
                        });
                    }

                    // Mở tab con
                    this.openTab(initialOpenUrl, matchedTitle, matchedIcon);
                }

                this.updateJobyLauncher(window.location.href);
            },

            formatTabTitle(rawTitle) {
                if (!rawTitle) return 'Tab';
                let t = rawTitle.trim();
                t = t.replace(/\s*-\s*JobLink.*$/i, '').replace(/\s*\|\s*JobLink.*$/i, '').trim();

                const map = {
                    'Bảng Điều Khiển': 'Trang Chủ',
                    'Bảng Điều Khiển Quản Trị': 'Trang Chủ',
                    'Tổng Quan': 'Trang Chủ',
                    'Trang Chủ': 'Trang Chủ',
                    'Tất Cả Thông Báo': 'Thông Báo',
                    'Thông Báo Hệ Thống': 'Thông Báo',
                    'Tin Nhắn & Hỗ Trợ': 'Tin Nhắn',
                    'Tin Nhắn Trao Đổi': 'Tin Nhắn',
                    'Tin Nhắn & Phỏng Vấn': 'Tin Nhắn',
                    'Đăng Tin Tuyển Dụng': 'Đăng Tin',
                    'Quản Lý Tin Tuyển Dụng': 'Quản Lý Tin',
                    'Tin Tuyển Dụng': 'Quản Lý Tin',
                    'Hồ Sơ Ứng Tuyển': 'Ứng Tuyển',
                    'Hồ Sơ Đã Ứng Tuyển': 'Ứng Tuyển',
                    'Danh Sách Ứng Tuyển': 'Ứng Tuyển',
                    'Danh Sách Người Lao Động': 'Người Lao Động',
                    'Tìm Kiếm Người Lao Động': 'Tìm Lao Động',
                    'Hồ Sơ Cá Nhân': 'Hồ Sơ',
                    'Quản Lý Hồ Sơ': 'Hồ Sơ',
                    'Hồ Sơ Năng Lực': 'Hồ Sơ',
                    'Hồ Sơ': 'Hồ Sơ',
                    'Tạo Hồ Sơ Mới': 'Tạo Hồ Sơ',
                    'Việc Làm Đã Lưu': 'Việc Đã Lưu',
                    'Tìm Việc Làm': 'Tìm Việc',
                    'Tìm Việc & Gợi Ý AI': 'Tìm Việc AI',
                    'Tra Cứu Việc Làm Sàn': 'Tìm Việc',
                    'Đã Ứng Tuyển': 'Ứng Tuyển',
                    'Lịch Phỏng Vấn': 'Lịch Hẹn',
                    'Lịch Hẹn & Phỏng Vấn': 'Lịch Hẹn',
                    'Lịch Hẹn Phỏng Vấn': 'Lịch Hẹn',
                    'Hợp Đồng Điện Tử': 'Hợp Đồng',
                    'Hợp Đồng Của Tôi': 'Hợp Đồng',
                    'Quản Lý Hợp Đồng': 'Hợp Đồng',
                    'Hợp Đồng Điện Tử Sàn': 'Hợp Đồng',
                    'Xác Minh Doanh Nghiệp': 'Xác Minh DN',
                    'Ủy Quyền Tuyển Dụng': 'Ủy Quyền TG',
                    'Doanh Nghiệp Ủy Quyền': 'DN Ủy Quyền',
                    'Thông Tin Doanh Nghiệp': 'Hồ Sơ Cty',
                    'Thông Tin Công Ty': 'Hồ Sơ Cty',
                    'Đánh Giá Của Tôi': 'Đánh Giá',
                    'Đánh Giá 2 Chiều': 'Đánh Giá',
                    'Kiểm Duyệt Đánh Giá': 'Duyệt ĐG',
                    'Đổi Mật Khẩu': 'Đổi Mật Khẩu',
                    'Cài Đặt Tài Khoản': 'Cài Đặt',
                    'Hỗ Trợ & CSKH': 'CSKH'
                };

                if (map[t]) return map[t];

                if (t.includes('Đăng Tin')) return 'Đăng Tin';
                if (t.includes('Tin Tuyển Dụng')) return 'Quản Lý Tin';
                if (t.includes('Ứng Tuyển')) return 'Ứng Tuyển';
                if (t.includes('Người Lao Động')) return 'Người Lao Động';
                if (t.includes('Hồ Sơ')) return 'Hồ Sơ';
                if (t.includes('Tìm Việc')) return 'Tìm Việc AI';
                if (t.includes('Việc Làm')) return 'Tìm Việc';
                if (t.includes('Phỏng Vấn') || t.includes('Lịch Hẹn')) return 'Lịch Hẹn';
                if (t.includes('Hợp Đồng')) return 'Hợp Đồng';
                if (t.includes('Xác Minh')) return 'Xác Minh DN';
                if (t.includes('Ủy Quyền')) return 'Ủy Quyền TG';
                if (t.includes('Thông Báo')) return 'Thông Báo';
                if (t.includes('Tin Nhắn')) return 'Tin Nhắn';
                if (t.includes('Đánh Giá')) return 'Đánh Giá';
                if (t.includes('Công Ty') || t.includes('Doanh Nghiệp')) return 'Hồ Sơ Cty';
                if (t.includes('Cài Đặt')) return 'Cài Đặt';

                if (t.length > 13) {
                    return t.substring(0, 12) + '…';
                }
                return t;
            },

            getTabKeyFromUrl(url) {
                try {
                    const u = new URL(url, window.location.origin);
                    let p = u.pathname || '';
                    while (true) {
                        let prev = p;
                        p = p.replace(/^\/(Joblink|public)/i, '');
                        if (p === prev) break;
                    }
                    p = p.replace(/[^a-zA-Z0-9_-]/g, '_').replace(/^_+|_+$/g, '').toLowerCase();
                    if (u.search && u.search.length > 1) {
                        let q = u.search.replace(/[\?&]embedded=1/g, '').replace(/[^a-zA-Z0-9_-]/g, '_').replace(/^_+|_+$/g, '');
                        if (q) p += '_' + q;
                    }
                    return p || 'page';
                } catch(e) {
                    return 'tab_' + Math.random().toString(36).substr(2, 6);
                }
            },

            openTab(url, title, iconSvg = '', linkElement = null) {
                if (!url) return;

                // Nếu bấm về chính trang Dashboard / Trang Gốc -> chuyển về tab mẹ
                const rootUrls = ['<?= $targetDashPath ?>', '/employer/dashboard', '/seeker/dashboard', '/intermediary/dashboard', '/', ''];
                let cleanPath = url.replace(window.location.origin, '').split('?')[0];
                while (true) {
                    let prev = cleanPath;
                    cleanPath = cleanPath.replace(/^\/(Joblink|public)/i, '');
                    if (cleanPath === prev) break;
                }
                if (!cleanPath.startsWith('/')) cleanPath = '/' + cleanPath;

                if (rootUrls.includes(cleanPath)) {
                    this.switchTab('root');
                    return;
                }

                const tabKey = this.getTabKeyFromUrl(url);

                // Nếu tab con này đã mở rồi -> chỉ cần chuyển tab, không tải lại
                if (this.openedTabs.has(tabKey)) {
                    const existing = this.openedTabs.get(tabKey);
                    if (url && existing && existing.url !== url && url.includes('?')) {
                        existing.url = url;
                        const sep = url.includes('?') ? '&' : '?';
                        if (existing.iframeEl) {
                            existing.iframeEl.src = url.replace(/[\?&]embedded=1/g, '') + sep + 'embedded=1';
                        }
                    }
                    this.switchTab(tabKey);
                    return;
                }

                this.startLoading();

                // Chuẩn bị URL nhúng (kèm cờ embedded=1)
                const cleanUrl = url.replace(/[\?&]embedded=1/g, '');
                const sep = cleanUrl.includes('?') ? '&' : '?';
                const embeddedUrl = cleanUrl + sep + 'embedded=1';

                let currentIcon = iconSvg;
                if (!currentIcon) {
                    if (url.includes('/messages')) {
                        currentIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
                    } else if (url.includes('/notifications')) {
                        currentIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>';
                    } else if (url.includes('/auth/change-password')) {
                        currentIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>';
                    } else if (url.includes('/employers')) {
                        currentIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
                    } else if (url.includes('/jobs')) {
                        currentIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
                    } else {
                        document.querySelectorAll('.sidebar .sidebar-link').forEach(l => {
                            const h = l.getAttribute('href');
                            if (h && url.includes(h.replace(/[\?&]embedded=1/g, ''))) {
                                currentIcon = l.querySelector('.icon-svg')?.outerHTML || '';
                            }
                        });
                    }
                }

                const shortTitle = this.formatTabTitle(title);

                // 1. Tạo thẻ Chrome Tab con trên thanh tab
                const tabEl = document.createElement('div');
                tabEl.className = 'chrome-tab';
                tabEl.id = 'mtab-' + tabKey;
                tabEl.title = title; // Hiển thị đầy đủ tiêu đề khi hover chuột
                tabEl.innerHTML = `
                    <span class="chrome-tab-icon">${currentIcon || '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>'}</span>
                    <span class="chrome-tab-title">${shortTitle}</span>
                    <span class="chrome-tab-reload" title="Tải lại tab này">
                        <svg class="icon-svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    </span>
                    <span class="chrome-tab-close" title="Đóng tab này">✕</span>
                `;

                // Bắt sự kiện chuyển tab khi click
                tabEl.addEventListener('click', (e) => {
                    if (e.target.classList.contains('chrome-tab-close') || e.target.closest('.chrome-tab-reload')) return;
                    MasterWorkspace.switchTab(tabKey);
                });

                // Bắt sự kiện click nút tải lại riêng tab này
                const tabReloadBtn = tabEl.querySelector('.chrome-tab-reload');
                if (tabReloadBtn) {
                    tabReloadBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        MasterWorkspace.reloadTab(tabKey);
                    });
                }

                // Bắt sự kiện click nút đóng tab ✕
                const closeBtn = tabEl.querySelector('.chrome-tab-close');
                closeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    MasterWorkspace.closeTab(tabKey);
                });

                document.getElementById('masterChromeTabsStrip').appendChild(tabEl);

                // 2. Tạo Panel chứa Iframe độc lập (kèm skeleton placeholder chống chớp trắng / khựng)
                const panelEl = document.createElement('div');
                panelEl.className = 'master-tab-panel';
                panelEl.id = 'mpanel-' + tabKey;

                // Skeleton placeholder nhẹ nhàng hiển thị ngay lập tức
                const skeletonEl = document.createElement('div');
                skeletonEl.className = 'tab-panel-skeleton';
                skeletonEl.innerHTML = `
                    <div class="tab-skeleton-spinner"></div>
                    <span class="tab-skeleton-label">Đang mở ${shortTitle}...</span>
                `;
                panelEl.appendChild(skeletonEl);

                const iframeEl = document.createElement('iframe');
                iframeEl.className = 'workspace-tab-iframe';
                iframeEl.id = 'mframe-' + tabKey;
                iframeEl.style.opacity = '0';
                iframeEl.style.transition = 'opacity 0.15s ease';
                iframeEl.src = embeddedUrl;

                const onTabIframeLoad = () => {
                    skeletonEl.style.opacity = '0';
                    setTimeout(() => skeletonEl.remove(), 160);
                    iframeEl.style.opacity = '1';
                    MasterWorkspace.stopLoading();
                    iframeEl.removeEventListener('load', onTabIframeLoad);
                };
                iframeEl.addEventListener('load', onTabIframeLoad);

                panelEl.appendChild(iframeEl);
                document.getElementById('masterPanelsContainer').appendChild(panelEl);

                // Lưu vào danh bạ tab đang mở
                this.openedTabs.set(tabKey, {
                    url: url,
                    title: title,
                    icon: currentIcon,
                    tabEl: tabEl,
                    panelEl: panelEl,
                    iframeEl: iframeEl
                });

                this.updateCloseAllBtn();
                this.switchTab(tabKey);
                this.saveTabsState();
            },

            saveTabsState() {
                try {
                    const tabsToSave = [];
                    this.openedTabs.forEach((data, key) => {
                        tabsToSave.push({
                            key: key,
                            url: data.url,
                            title: data.title,
                            icon: data.icon
                        });
                    });
                    sessionStorage.setItem('user_workspace_persistent_tabs', JSON.stringify({
                        userId: <?= json_encode($_SESSION['user']['account_id'] ?? null) ?>,
                        activeTab: this.currentActiveTab,
                        tabs: tabsToSave
                    }));
                } catch(e) {}
            },

            clearAllWorkspaceTabs() {
                try {
                    sessionStorage.removeItem('user_workspace_persistent_tabs');
                    sessionStorage.removeItem('user_workspace_restore_tabs');
                    sessionStorage.removeItem('admin_workspace_persistent_tabs');
                    sessionStorage.removeItem('master_restore_tabs');
                    localStorage.removeItem('user_workspace_persistent_tabs');
                    localStorage.removeItem('admin_workspace_persistent_tabs');
                } catch(e) {}

                if (typeof this.closeAllChildTabs === 'function') {
                    this.closeAllChildTabs();
                }
            },

            switchTab(tabKey) {
                this.currentActiveTab = tabKey;
                this.saveTabsState();

                // Bỏ active tất cả các tab trên thanh Chrome
                document.querySelectorAll('#masterChromeTabsStrip .chrome-tab').forEach(t => t.classList.remove('active'));

                // Ẩn tất cả các panel
                const rootPanel = document.getElementById('mpanel-root');
                if (rootPanel) {
                    rootPanel.style.display = 'none';
                    rootPanel.classList.add('hidden');
                    rootPanel.classList.remove('active');
                }
                document.querySelectorAll('.master-tab-panel').forEach(p => p.classList.remove('active'));

                let currentUrl = '';
                if (tabKey === 'root') {
                    document.getElementById('mtab-root').classList.add('active');
                    if (rootPanel) {
                        rootPanel.style.display = 'flex';
                        rootPanel.classList.remove('hidden');
                        rootPanel.classList.add('active');
                    }

                    // Đồng bộ highlight menu Dashboard trên sidebar
                    document.querySelectorAll('.sidebar .sidebar-link').forEach(l => l.classList.remove('active'));
                    const dashLink = document.querySelector('.sidebar .sidebar-link[href*="dashboard"]');
                    if (dashLink) dashLink.classList.add('active');
                    currentUrl = window.location.href;
                } else {
                    const tabData = this.openedTabs.get(tabKey);
                    if (tabData) {
                        tabData.tabEl.classList.add('active');
                        tabData.panelEl.classList.add('active');

                        // Đồng bộ highlight menu tương ứng trên sidebar
                        document.querySelectorAll('.sidebar .sidebar-link').forEach(l => {
                            const h = l.getAttribute('href');
                            if (h && tabData.url.includes(h.replace(/[\?&]embedded=1/g, ''))) {
                                l.classList.add('active');
                            } else {
                                l.classList.remove('active');
                            }
                        });
                        currentUrl = tabData.url;
                    }
                }

                // Ẩn nút Joby lơ lửng nếu tab hiện tại là trang Tin Nhắn (nơi đã có sẵn khung chat Joby AI trực tiếp)
                // và tự động hiện lại khi chuyển sang các tab khác hoặc về Dashboard
                this.updateJobyLauncher(currentUrl);
            },

            updateJobyLauncher(url) {
                const isMessages = (url && url.includes('/messages'))
                    || window.location.pathname.includes('/messages')
                    || window.location.href.includes('/messages');
                if (typeof window.setJobyLauncherVisible === 'function') {
                    window.setJobyLauncherVisible(!isMessages);
                } else {
                    const launcher = document.getElementById('joblink-ai-launcher');
                    if (launcher) launcher.style.display = isMessages ? 'none' : '';
                }
            },

            reloadTab(tabKey) {
                if (!tabKey) tabKey = this.currentActiveTab;
                this.startLoading();

                if (tabKey === 'root') {
                    const rootReloadIcon = document.querySelector('#mtab-root .chrome-tab-reload .icon-svg');
                    if (rootReloadIcon) rootReloadIcon.classList.add('tab-spin-anim');

                    // Lưu danh sách các tab đang mở vào sessionStorage để phục hồi lại sau khi root reload
                    try {
                        const tabsToSave = [];
                        this.openedTabs.forEach((data, key) => {
                            tabsToSave.push({
                                url: data.url,
                                title: data.title,
                                icon: data.icon
                            });
                        });
                        sessionStorage.setItem('user_workspace_restore_tabs', JSON.stringify({
                            activeTab: this.currentActiveTab,
                            tabs: tabsToSave
                        }));
                    } catch(e) {}

                    window.location.reload();
                    return;
                }

                const tabData = this.openedTabs.get(tabKey);
                if (tabData && tabData.iframeEl) {
                    const tabReloadIcon = tabData.tabEl.querySelector('.chrome-tab-reload .icon-svg');
                    if (tabReloadIcon) {
                        tabReloadIcon.classList.add('tab-spin-anim');
                    }

                    const onReloadFinished = () => {
                        this.stopLoading();
                        if (tabReloadIcon) tabReloadIcon.classList.remove('tab-spin-anim');
                        tabData.iframeEl.removeEventListener('load', onReloadFinished);
                    };
                    tabData.iframeEl.addEventListener('load', onReloadFinished);

                    try {
                        tabData.iframeEl.contentWindow.location.reload();
                    } catch(e) {
                        tabData.iframeEl.src = tabData.iframeEl.src;
                    }
                } else {
                    this.stopLoading();
                }
            },

            reloadActiveTab() {
                this.reloadTab(this.currentActiveTab);
            },

            closeTab(tabKey) {
                const tabData = this.openedTabs.get(tabKey);
                if (!tabData) return;

                tabData.tabEl.remove();
                tabData.panelEl.remove();
                this.openedTabs.delete(tabKey);

                this.updateCloseAllBtn();
                this.saveTabsState();

                // Nếu tab vừa đóng đang hiển thị, tự động chuyển về tab con gần nhất hoặc về Trang Mẹ
                if (this.currentActiveTab === tabKey) {
                    if (this.openedTabs.size > 0) {
                        const lastKey = Array.from(this.openedTabs.keys())[this.openedTabs.size - 1];
                        this.switchTab(lastKey);
                    } else {
                        this.switchTab('root');
                    }
                }
            },

            closeAllChildTabs() {
                this.openedTabs.forEach((tabData) => {
                    tabData.tabEl.remove();
                    tabData.panelEl.remove();
                });
                this.openedTabs.clear();
                this.updateCloseAllBtn();
                this.saveTabsState();
                this.switchTab('root');
            },

            updateCloseAllBtn() {
                const btn = document.getElementById('masterCloseAllBtn');
                if (btn) {
                    btn.style.display = (this.openedTabs.size >= 2) ? 'inline-flex' : 'none';
                }
            },

            startLoading() {
                if (this.loadingBar) {
                    this.loadingBar.style.width = '35%';
                    this.loadingBar.style.opacity = '1';
                }
            },

            stopLoading() {
                if (this.loadingBar) {
                    this.loadingBar.style.width = '100%';
                    setTimeout(() => {
                        this.loadingBar.style.opacity = '0';
                        this.loadingBar.style.width = '0%';
                    }, 200);
                }
            }
        };

        // ====================================================================
        // QUẢN LÝ THU GỌN / MỞ RỘNG THANH MENU USER / DOANH NGHIỆP SIDEBAR (~56px)
        // ====================================================================
        const UserSidebarManager = {
            init() {
                const toggleBtn = document.getElementById('sidebarCollapseBtn');
                const isStoredCollapsed = localStorage.getItem('joblink_sidebar_collapsed') === '1';
                
                if (isStoredCollapsed) {
                    this.setCollapsed(true);
                }

                if (toggleBtn) {
                    toggleBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        const isCollapsed = document.body.classList.contains('sidebar-collapsed') || document.documentElement.classList.contains('sidebar-collapsed');
                        this.setCollapsed(!isCollapsed);
                    });
                }

                // Phím tắt Ctrl + B
                document.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
                        e.preventDefault();
                        const isCollapsed = document.body.classList.contains('sidebar-collapsed') || document.documentElement.classList.contains('sidebar-collapsed');
                        this.setCollapsed(!isCollapsed);
                    }
                });
            },

            setCollapsed(collapsed) {
                const toggleBtn = document.getElementById('sidebarCollapseBtn');
                if (collapsed) {
                    document.documentElement.classList.add('sidebar-collapsed');
                    document.body.classList.add('sidebar-collapsed');
                    localStorage.setItem('joblink_sidebar_collapsed', '1');
                    if (toggleBtn) toggleBtn.setAttribute('title', 'Mở rộng menu (Ctrl+B)');
                } else {
                    document.documentElement.classList.remove('sidebar-collapsed');
                    document.body.classList.remove('sidebar-collapsed');
                    localStorage.setItem('joblink_sidebar_collapsed', '0');
                    if (toggleBtn) toggleBtn.setAttribute('title', 'Thu gọn menu (Ctrl+B)');
                }
            }
        };

        // Lắng nghe phím F5 hoặc Ctrl+R toàn cục để chỉ tải lại tab hiện hành
        window.addEventListener('keydown', (e) => {
            if (e.key === 'F5' || (e.ctrlKey && e.key.toLowerCase() === 'r')) {
                e.preventDefault();
                MasterWorkspace.reloadActiveTab();
            }
        });

        window.MasterWorkspace = MasterWorkspace;

        document.addEventListener('DOMContentLoaded', () => {
            MasterWorkspace.init();
            UserSidebarManager.init();
        });
    </script>
    <script src="<?= asset('js/ai_chatbox.js?v=' . (file_exists(__DIR__ . '/../../js/ai_chatbox.js') ? filemtime(__DIR__ . '/../../js/ai_chatbox.js') : time())) ?>"></script>
    <script src="<?= asset('js/mobile.js') ?>"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ((array)$extraJs as $jsFile): ?>
            <script src="<?= asset($jsFile) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <?= class_exists('DialogHelper') ? DialogHelper::renderFlashScript() : '' ?>
</body>
</html>
