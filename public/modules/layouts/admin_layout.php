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
    <title><?= $pageTitle ?? 'JobLink Admin' ?></title>
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
    <link rel="stylesheet" href="<?= asset('css/admin/admin.css?v=' . filemtime(__DIR__ . '/../../css/admin/admin.css')) ?>">
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
$reqUri = $_SERVER['REQUEST_URI'] ?? '/';
$reqPath = parse_url($reqUri, PHP_URL_PATH) ?? '/';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$cleanPath = str_replace('\\', '/', $scriptDir);
if ($cleanPath !== '/' && $cleanPath !== '' && strpos($reqPath, $cleanPath) === 0) {
    $reqPath = substr($reqPath, strlen($cleanPath));
}
$reqPath = preg_replace('#^/(Joblink|public|Joblink/public)#i', '', $reqPath);
$cleanReq = '/' . trim($reqPath, '/');

$shouldRedirectAdmin = (
    strpos($cleanReq, '/admin') === 0 || 
    strpos($cleanReq, '/auth/change-password') === 0 ||
    strpos($cleanReq, '/employers') === 0 ||
    strpos($cleanReq, '/jobs') === 0
);
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $shouldRedirectAdmin && $cleanReq !== '/admin/dashboard' && $cleanReq !== '/admin') {
    header('Location: ' . url('/admin/dashboard') . '?open=' . urlencode($reqUri));
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'JobLink Admin - Quản Trị Hệ Thống' ?></title>
    <link rel="icon" type="image/png" href="<?= getSystemLogo() ?>">
    <link rel="shortcut icon" type="image/png" href="<?= getSystemLogo() ?>">
    <link rel="apple-touch-icon" href="<?= getSystemLogo() ?>">
    <script>
        // Tự động nhận diện nếu chạy trong iframe để tránh lồng layout 2 lần
        if (window.self !== window.top && !window.location.search.includes('embedded=1')) {
            var sep = window.location.href.indexOf('?') !== -1 ? '&' : '?';
            window.location.replace(window.location.href + sep + 'embedded=1');
        }
        // Khởi tạo sớm trạng thái thu gọn sidebar để tránh giật giao diện (FOUC)
        try {
            if (localStorage.getItem('admin_sidebar_collapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css?v=' . filemtime(__DIR__ . '/../../css/style.css')) ?>">
    <link rel="stylesheet" href="<?= asset('css/dialog.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ai_chatbox.css?v=' . (file_exists(__DIR__ . '/../../css/ai_chatbox.css') ? filemtime(__DIR__ . '/../../css/ai_chatbox.css') : time())) ?>">
    <link rel="stylesheet" href="<?= asset('css/mobile.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin/admin.css?v=' . filemtime(__DIR__ . '/../../css/admin/admin.css')) ?>">
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
<body class="role-admin">
    <?php require_once __DIR__ . '/mobile_nav.php'; ?>
    <?php
        $adminUser = $_SESSION['user'] ?? [];
        $adminDisplayName = $adminUser['display_name'] ?? ($adminUser['username'] ?? 'Admin');
        $adminUsername = $adminUser['username'] ?? 'admin';
        $adminRoleCode = $adminUser['role_code'] ?? 'SUPER_ADMIN';
        $adminRoleName = $adminUser['role_name'] ?? 'Admin';
        $adminRoleBadge = getSidebarRoleBadge($adminRoleCode, $adminRoleName);
        $adminEmail = $adminUser['email'] ?? 'admin@joblink.vn';
        $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    ?>
    <div class="dashboard-layout">
        <!-- THANH DỌC BÊN TRÁI CHỨA MENU CHÍNH & TIỆN ÍCH -->
        <aside class="sidebar sidebar-admin" id="adminSidebar">
            <?php
                $adminAvatar = getAvatarUrl($adminUser['avatar_url'] ?? '', $adminUser['role_code'] ?? 'SUPER_ADMIN');
            ?>
            <!-- 1. ĐẦU THANH DỌC: LOGO + NÚT THU GỌN VÀ CỤM CHUÔNG, CHAT, AVATAR AD -->
            <div class="sidebar-header">
                <div class="sidebar-brand-row">
                    <a href="<?= url('/admin/dashboard') ?>" class="sidebar-brand-box" title="JobLink - <?= htmlspecialchars($adminRoleName) ?>">
                        <span class="sidebar-brand-title">
                            <span class="sidebar-brand-icon">
                                <img src="<?= getSystemLogo() ?>" alt="JobLink" class="sidebar-brand-logo-img">
                            </span>
                            <span class="sidebar-brand-text">JobLink</span>
                        </span>
                        <span class="sidebar-brand-badge" title="<?= htmlspecialchars($adminRoleName) ?>"><?= htmlspecialchars($adminRoleBadge) ?></span>
                    </a>
                    <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Thu gọn menu (Ctrl+B)" aria-label="Thu gọn hoặc mở rộng thanh menu">
                        <span class="collapse-icon-left"><?= icon('sidebar_toggle_collapse', '', 18) ?></span>
                        <span class="collapse-icon-right"><?= icon('sidebar_toggle_expand', '', 18) ?></span>
                    </button>
                </div>

                <!-- CỤM THÔNG TIN AD: CHUÔNG + CHAT + AVATAR AD (KHÔNG MỞ GÌ VÌ ADMIN KHÔNG CÓ TRANG HỒ SƠ CÁ NHÂN) -->
                <div class="sidebar-user-cluster">
                    <div class="sidebar-cluster-row">
                        <!-- Thông tin tóm tắt người dùng -->
                        <div class="sidebar-user-meta" title="<?= htmlspecialchars($adminDisplayName) ?> (@<?= htmlspecialchars($adminUsername) ?> - <?= htmlspecialchars($adminRoleName) ?>)" style="cursor: default;">
                            <span class="sidebar-user-name" title="<?= htmlspecialchars($adminDisplayName) ?>"><?= htmlspecialchars($adminDisplayName) ?></span>
                            <span class="sidebar-user-sub">@<?= htmlspecialchars($adminUsername) ?></span>
                        </div>

                        <!-- Cụm 3 nút icon -->
                        <div class="sidebar-cluster-icons">
                            <!-- 1 & 2. Nút Chuông (kèm badge đỏ 3) & Nút Chat -->
                            <div class="sidebar-aux-icons">
                                <?= renderHeaderIcons($adminUser) ?>
                            </div>

                            <!-- 3. Avatar tròn hiển thị danh tính AD -->
                            <div class="sidebar-avatar-item" title="<?= htmlspecialchars($adminDisplayName) ?> (@<?= htmlspecialchars($adminUsername) ?> - <?= htmlspecialchars($adminRoleName) ?>)" style="cursor: default;">
                                <img src="<?= htmlspecialchars($adminAvatar) ?>" alt="avatar" class="fb-nav-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. THÂN THANH DỌC: MENU CHỨC NĂNG (CUỘN ĐỘC LẬP) -->
            <div class="sidebar-nav-scroll">
                <div class="sidebar-heading" title="Báo Cáo & Thống Kê"><span>Báo Cáo & Thống Kê</span></div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/admin/dashboard') ?>" class="sidebar-link workspace-nav-link" data-title="Bảng Điều Khiển">
                        <span class="nav-avatar-box nav-avatar-dashboard">
                            <?= icon('nav_dashboard', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Bảng Điều Khiển</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading" title="Chữ Ký Số & Pháp Lý DN"><span>Chữ Ký Số & Pháp Lý DN</span></div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/admin/contracts') ?>" class="sidebar-link workspace-nav-link" data-title="Hợp Đồng Điện Tử Sàn">
                        <span class="nav-avatar-box nav-avatar-contracts">
                            <?= icon('nav_contract', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Hợp Đồng Điện Tử Sàn</span>
                    </a></li>
                    <li><a href="<?= url('/admin/authorizations') ?>" class="sidebar-link workspace-nav-link" data-title="Thẩm Định Ủy Quyền (TG)">
                        <span class="nav-avatar-box nav-avatar-authorizations">
                            <?= icon('nav_authorization', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Thẩm Định Ủy Quyền (TG)</span>
                    </a></li>
                    <li><a href="<?= url('/admin/employers') ?>" class="sidebar-link workspace-nav-link" data-title="Xác Minh Doanh Nghiệp">
                        <span class="nav-avatar-box nav-avatar-employers">
                            <?= icon('nav_employer', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Xác Minh Doanh Nghiệp</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading" title="Kiểm Duyệt & Vận Hành"><span>Kiểm Duyệt & Vận Hành</span></div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/admin/job-posts') ?>" class="sidebar-link workspace-nav-link" data-title="Duyệt Tin Tuyển Dụng">
                        <span class="nav-avatar-box nav-avatar-job-posts">
                            <?= icon('nav_job_post', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Duyệt Tin Tuyển Dụng</span>
                    </a></li>
                    <li><a href="<?= url('/admin/review-criteria') ?>" class="sidebar-link workspace-nav-link" data-title="Tiêu Chí Đánh Giá 2 Chiều">
                        <span class="nav-avatar-box nav-avatar-criteria">
                            <?= icon('nav_scale_criteria', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Tiêu Chí Đánh Giá 2 Chiều</span>
                    </a></li>
                    <li><a href="<?= url('/admin/reviews') ?>" class="sidebar-link workspace-nav-link" data-title="Kiểm Duyệt Đánh Giá">
                        <span class="nav-avatar-box nav-avatar-reviews">
                            <?= icon('nav_review_bubble', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Kiểm Duyệt Đánh Giá</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading" title="Quản Trị Người Dùng"><span>Quản Trị Người Dùng</span></div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/admin/accounts') ?>" class="sidebar-link workspace-nav-link" data-title="Tài Khoản Hệ Thống">
                        <span class="nav-avatar-box nav-avatar-accounts">
                            <?= icon('nav_accounts_id', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Tài Khoản Hệ Thống</span>
                    </a></li>
                    <li><a href="<?= url('/admin/profile-history') ?>" class="sidebar-link workspace-nav-link" data-title="Lịch Sử Thay Đổi Hồ Sơ">
                        <span class="nav-avatar-box" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff;">
                            <?= icon('clock', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Lịch Sử Thay Đổi Hồ Sơ</span>
                    </a></li>
                    <li><a href="<?= url('/admin/staffs') ?>" class="sidebar-link workspace-nav-link" data-title="Nhân Viên Nội Bộ">
                        <span class="nav-avatar-box nav-avatar-staffs">
                            <?= icon('nav_staff_badge', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Nhân Viên Nội Bộ</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading" title="CSKH & Thông Báo"><span>CSKH & Thông Báo</span></div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/admin/messages') ?>" class="sidebar-link workspace-nav-link" data-title="Gửi Thông Báo Hệ Thống">
                        <span class="nav-avatar-box nav-avatar-messages">
                            <?= icon('nav_broadcast', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Gửi Thông Báo Hệ Thống</span>
                    </a></li>
                    <li><a href="<?= url('/admin/support') ?>" class="sidebar-link workspace-nav-link" data-title="CSKH & Khiếu Nại">
                        <span class="nav-avatar-box nav-avatar-support">
                            <?= icon('nav_support_headset', '', 18) ?>
                        </span>
                        <span class="nav-link-text">CSKH & Khiếu Nại</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading" title="Trí Tuệ Nhân Tạo (AI)"><span>Trí Tuệ Nhân Tạo (AI)</span></div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/admin/ai-analytics') ?>" class="sidebar-link workspace-nav-link" data-title="Báo Cáo & Huấn Luyện AI">
                        <span class="nav-avatar-box nav-avatar-ai-analytics">
                            <?= icon('nav_ai_brain', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Báo Cáo & Huấn Luyện AI</span>
                    </a></li>
                    <li><a href="<?= url('/admin/ai-settings') ?>" class="sidebar-link workspace-nav-link" data-title="Cấu Hình Model Gemini">
                        <span class="nav-avatar-box nav-avatar-ai-settings">
                            <?= icon('nav_gemini_star', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Cấu Hình Model Gemini</span>
                    </a></li>
                </ul>

                <div class="sidebar-heading" title="Tài Khoản & Bảo Mật"><span>Tài Khoản & Bảo Mật</span></div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/auth/change-password') ?>" class="sidebar-link workspace-nav-link" data-title="Đổi Mật Khẩu Admin">
                        <span class="nav-avatar-box nav-avatar-password">
                            <?= icon('nav_security_key', '', 18) ?>
                        </span>
                        <span class="nav-link-text">Đổi Mật Khẩu Admin</span>
                    </a></li>
                </ul>
            </div>

            <!-- 3. ĐÁY THANH DỌC: PHIÊN BẢN & ĐĂNG XUẤT NHANH -->
            <div class="sidebar-footer">
                <span class="sidebar-version-text">JobLink Admin v2.0</span>
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

                // Phục hồi các tab con đã mở trước đó nếu vừa reload tab mẹ trong cùng phiên tài khoản này
                try {
                    const currentUserId = <?= json_encode($_SESSION['user']['account_id'] ?? null) ?>;
                    const saved = sessionStorage.getItem('admin_workspace_persistent_tabs') || sessionStorage.getItem('master_restore_tabs');
                    if (saved) {
                        const parsed = JSON.parse(saved);
                        // Nếu userId không khớp (đã đăng xuất hoặc chuyển sang tài khoản khác) -> Xóa sạch, không mở lại tab cũ!
                        if (parsed && parsed.userId && parsed.userId !== currentUserId) {
                            sessionStorage.removeItem('admin_workspace_persistent_tabs');
                            sessionStorage.removeItem('master_restore_tabs');
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

                // Lắng nghe F5 / Ctrl+R để chỉ reload tab hiện hành thay vì reload toàn bộ mẹ
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'F5' || (e.ctrlKey && e.key.toLowerCase() === 'r')) {
                        if (this.currentActiveTab && this.currentActiveTab !== 'root') {
                            e.preventDefault();
                            this.reloadTab(this.currentActiveTab);
                        }
                    }
                });

                // 1. Lắng nghe click toàn cục để mở tab tức thì (không khựng, không chớp, không chuyển hướng 302)
                document.addEventListener('click', (e) => {
                    const link = e.target.closest('a');
                    if (!link) return;

                    const rawHref = link.getAttribute('href');
                    if (!rawHref || rawHref.startsWith('javascript:') || rawHref.startsWith('#')) return;

                    // Nếu click Đăng xuất -> Xóa sạch toàn bộ tab con của workspace
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

                        const isModuleLink = cleanPath.startsWith('/admin')
                                          || cleanPath.startsWith('/employer')
                                          || cleanPath.startsWith('/employers')
                                          || cleanPath.startsWith('/jobs')
                                          || cleanPath.startsWith('/auth/change-password');
                        const isRootDashboard = (cleanPath === '/admin/dashboard' || cleanPath === '/admin' || cleanPath === '/' || cleanPath === '');

                        if (isModuleLink && !isRootDashboard) {
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

                // 2. Lắng nghe thông điệp từ các iframe con (khi người dùng bấm link trong iframe con)
                window.addEventListener('message', (e) => {
                    if (e.data && e.data.action === 'openTab' && e.data.url) {
                        MasterWorkspace.openTab(e.data.url, e.data.title || 'Chi Tiết', e.data.icon || '');
                    }
                });

                // 3. Kiểm tra tham số ?open= trên URL để tự động mở tab con ban đầu
                const urlParams = new URLSearchParams(window.location.search);
                const initialOpenUrl = urlParams.get('open');
                if (initialOpenUrl) {
                    // Xóa param ?open= ngay lập tức để giữ URL luôn sạch là /admin/dashboard
                    const cleanUrl = window.location.pathname;
                    window.history.replaceState(null, '', cleanUrl);

                    // Tìm sidebar tương ứng để lấy tiêu đề & icon
                    let matchedTitle = 'Chức Năng';
                    let matchedIcon = '';

                    if (initialOpenUrl.includes('/admin/notifications')) {
                        matchedTitle = 'Tất Cả Thông Báo';
                        matchedIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>';
                    } else if (initialOpenUrl.includes('/admin/messages')) {
                        matchedTitle = 'Tin Nhắn & Hỗ Trợ';
                        matchedIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
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
                                matchedTitle = l.getAttribute('data-title') || l.querySelector('.nav-link-text')?.innerText?.trim() || matchedTitle;
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
                    'Duyệt Tin Tuyển Dụng': 'Duyệt Tin',
                    'Xác Minh Doanh Nghiệp': 'Xác Minh DN',
                    'Thẩm Định Doanh Nghiệp Ủy Quyền': 'Ủy Quyền TG',
                    'Thẩm Định Ủy Quyền (TG)': 'Ủy Quyền TG',
                    'Thẩm Định Ủy Quyền': 'Ủy Quyền TG',
                    'Hợp Đồng Điện Tử Sàn': 'Hợp Đồng',
                    'Hợp Đồng Điện Tử': 'Hợp Đồng',
                    'Tiêu Chí Đánh Giá 2 Chiều': 'Tiêu Chí ĐG',
                    'Kiểm Duyệt Đánh Giá': 'Duyệt ĐG',
                    'Đánh Giá & Khiếu Nại': 'Đánh Giá',
                    'Tài Khoản Hệ Thống': 'Tài Khoản',
                    'Quản Trị Tài Khoản': 'Tài Khoản',
                    'Tổng Tài Khoản': 'Tài Khoản',
                    'Nhân Viên Nội Bộ': 'Nhân Viên',
                    'Quản Lý Nhân Viên': 'Nhân Viên',
                    'Gửi Thông Báo Hệ Thống': 'Gửi TB',
                    'CSKH & Khiếu Nại': 'CSKH',
                    'Trung Tâm CSKH': 'CSKH',
                    'Báo Cáo & Huấn Luyện AI': 'Huấn Luyện AI',
                    'Cấu Hình Model Gemini': 'Model Gemini',
                    'Đổi Mật Khẩu Admin': 'Đổi Mật Khẩu',
                    'Đổi Mật Khẩu': 'Đổi Mật Khẩu',
                    'Hồ Sơ Ứng Tuyển': 'Ứng Tuyển',
                    'Lịch Phỏng Vấn': 'Lịch Hẹn'
                };

                if (map[t]) return map[t];

                if (t.includes('Duyệt Tin')) return 'Duyệt Tin';
                if (t.includes('Xác Minh')) return 'Xác Minh DN';
                if (t.includes('Hợp Đồng')) return 'Hợp Đồng';
                if (t.includes('Ủy Quyền')) return 'Ủy Quyền TG';
                if (t.includes('Thông Báo')) return 'Thông Báo';
                if (t.includes('Tin Nhắn')) return 'Tin Nhắn';
                if (t.includes('Đánh Giá')) return 'Đánh Giá';
                if (t.includes('Tài Khoản')) return 'Tài Khoản';
                if (t.includes('Nhân Viên')) return 'Nhân Viên';
                if (t.includes('CSKH') || t.includes('Khiếu Nại')) return 'CSKH';
                if (t.includes('Gemini')) return 'Model Gemini';
                if (t.includes('Huấn Luyện')) return 'Huấn Luyện AI';
                if (t.includes('Ứng Tuyển')) return 'Ứng Tuyển';
                if (t.includes('Hồ Sơ')) return 'Hồ Sơ';

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
                const rootUrls = ['/admin/dashboard', '/admin', '/', ''];
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
                    if (url.includes('/admin/messages')) {
                        currentIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
                    } else if (url.includes('/admin/notifications')) {
                        currentIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>';
                    } else if (url.includes('/auth/change-password')) {
                        currentIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>';
                    } else if (url.includes('/candidates')) {
                        currentIcon = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
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
                tabEl.title = title; // Hiển thị đầy đủ tên khi rê chuột
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

                // 2. Tạo Panel chứa Iframe độc lập (có skeleton placeholder chống chớp khựng)
                const panelEl = document.createElement('div');
                panelEl.className = 'master-tab-panel';
                panelEl.id = 'mpanel-' + tabKey;

                // Placeholder tinh tế ngăn ngừa chớp trắng / giật giao diện
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
                    sessionStorage.setItem('admin_workspace_persistent_tabs', JSON.stringify({
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

                    // Lưu các tab con để phục hồi sau reload tab mẹ
                    try {
                        const tabsToRestore = [];
                        this.openedTabs.forEach((data, key) => {
                            tabsToRestore.push({ url: data.url, title: data.title, icon: data.icon });
                        });
                        sessionStorage.setItem('master_restore_tabs', JSON.stringify({
                            activeTab: this.currentActiveTab,
                            tabs: tabsToRestore
                        }));
                    } catch(e) {}

                    window.location.reload();
                    return;
                }

                const tabData = this.openedTabs.get(tabKey);
                if (tabData && tabData.iframeEl) {
                    const tabReloadIcon = tabData.tabEl ? tabData.tabEl.querySelector('.chrome-tab-reload .icon-svg') : null;
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
        // QUẢN LÝ THU GỌN / MỞ RỘNG THANH MENU ADMIN SIDEBAR (~50px)
        // ====================================================================
        const AdminSidebarManager = {
            init() {
                const toggleBtn = document.getElementById('sidebarCollapseBtn');
                const isStoredCollapsed = localStorage.getItem('joblink_sidebar_collapsed') === '1'
                                       || localStorage.getItem('admin_sidebar_collapsed') === '1';
                
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
        window.MasterWorkspace = MasterWorkspace;

        document.addEventListener('DOMContentLoaded', () => {
            MasterWorkspace.init();
            AdminSidebarManager.init();
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
