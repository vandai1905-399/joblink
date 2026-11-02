<?php 
// public/modules/messages/chat_messenger.php - Giao diện Khung Chat Trao Đổi 2 Chiều Trực Tiếp
$extraCss = ['css/messages/messages.css'];
$extraJs = ['js/messages/messages.js'];
ob_start(); 

$currentRole = $user['role_code'] ?? '';
$myId = $user['account_id'] ?? 0;

if (!function_exists('formatSmartTimeTooltip')) {
    function formatSmartTimeTooltip($sentAt) {
        if (empty($sentAt)) return '';
        $ts = is_numeric($sentAt) ? (int)$sentAt : strtotime($sentAt);
        $now = time();
        $msgYear = (int)date('Y', $ts);
        $nowYear = (int)date('Y', $now);
        $msgMonth = (int)date('n', $ts);
        $nowMonth = (int)date('n', $now);
        $msgDay = (int)date('j', $ts);
        $nowDay = (int)date('j', $now);
        $timeStr = date('H:i', $ts);
        
        $wMap = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
        $weekday = $wMap[(int)date('w', $ts)];

        $diffDays = floor(($now - $ts) / 86400);

        // 1. Trong ngày hôm đó thì chỉ cần hiện giờ
        if ($msgYear === $nowYear && $msgMonth === $nowMonth && $msgDay === $nowDay) {
            return $timeStr;
        }
        // 2. Trong tuần thì chỉ cần hiển thị giờ và thứ
        if ($diffDays < 7) {
            return $timeStr . ' ' . $weekday;
        }
        // 3. Trong tháng đó thì chỉ cần hiện giờ ngày
        if ($msgYear === $nowYear && $msgMonth === $nowMonth) {
            return $timeStr . ' ngày ' . $msgDay;
        }
        // 4. Trong năm đó thì chỉ cần hiển thị giờ ngày tháng
        if ($msgYear === $nowYear) {
            return $timeStr . ' ' . $msgDay . ' Tháng ' . $msgMonth;
        }
        // 5. Khác năm thì hiển thị giờ ngày tháng năm
        return $timeStr . ' ' . $msgDay . ' Tháng ' . $msgMonth . ', ' . $msgYear;
    }
}

if (!function_exists('formatSmartTimeDivider')) {
    function formatSmartTimeDivider($sentAt) {
        if (empty($sentAt)) return '';
        $ts = is_numeric($sentAt) ? (int)$sentAt : strtotime($sentAt);
        $now = time();
        $msgYear = (int)date('Y', $ts);
        $nowYear = (int)date('Y', $now);
        $msgMonth = (int)date('n', $ts);
        $nowMonth = (int)date('n', $now);
        $msgDay = (int)date('j', $ts);
        $nowDay = (int)date('j', $now);
        $timeStr = date('H:i', $ts);
        
        $wMap = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
        $weekday = $wMap[(int)date('w', $ts)];

        $diffDays = floor(($now - $ts) / 86400);

        if ($diffDays < 7) {
            return $timeStr . ' ' . $weekday;
        }
        if ($msgYear === $nowYear && $msgMonth === $nowMonth) {
            return $timeStr . ' ngày ' . $msgDay;
        }
        if ($msgYear === $nowYear) {
            return $timeStr . ' ' . $msgDay . ' Tháng ' . $msgMonth;
        }
        return $timeStr . ' ' . $msgDay . ' Tháng ' . $msgMonth . ', ' . $msgYear;
    }
}
?>

<div class="messenger-container" id="messengerContainer">
    <!-- ==================== CỘT 1: DANH SÁCH CUỘC TRÒ CHUYỆN ==================== -->
    <div class="messenger-sidebar">
        <!-- Sidebar Top Header Messenger Style -->
        <div class="sidebar-top-header">
            <h2 class="sidebar-heading-title">Đoạn chat</h2>
            <div class="sidebar-top-actions">
                <?php if (in_array($currentRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])): ?>
                    <button type="button" class="sidebar-circle-btn" onclick="openBroadcastModal()" title="Phát thông báo hệ thống">
                        <?= icon('posts', '', 16) ?>
                    </button>
                <?php endif; ?>
                <div class="sidebar-menu-dropdown-wrap">
                    <button type="button" class="sidebar-circle-btn" id="sidebarMenuBtn" title="Tùy chọn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="12" r="2.2"></circle>
                            <circle cx="5" cy="12" r="2.2"></circle>
                            <circle cx="19" cy="12" r="2.2"></circle>
                        </svg>
                    </button>
                    <div class="sidebar-options-menu" id="sidebarOptionsMenu" style="display:none;">
                        <button type="button" class="opt-menu-item" onclick="markAllConversationsRead()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Đánh dấu tất cả đã đọc</span>
                        </button>
                        <button type="button" class="opt-menu-item" onclick="location.reload()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"></path><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                            <span>Làm mới danh sách</span>
                        </button>
                    </div>
                </div>
                <button type="button" class="sidebar-circle-btn" id="sidebarComposeBtn" title="Tin nhắn mới" onclick="focusSearchOrNew()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Search & Filter Messenger Pill -->
        <div class="sidebar-search-box">
            <div class="search-input-wrapper">
                <span class="search-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#65676b" stroke-width="2.2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input type="text" id="contactSearchInput" placeholder="Tìm kiếm trên Messenger" autocomplete="off">
                <button type="button" id="clearSearchBtn" class="search-clear-btn" style="display:none;">&times;</button>
            </div>
        </div>

        <!-- Filter Tabs / Pills Theo Vai Trò Người Dùng -->
        <div class="sidebar-filter-tabs" id="sidebarFilterTabs">
            <?php if ($currentRole === 'JOB_SEEKER'): ?>
                <button type="button" class="filter-tab active" data-filter="all">Tất cả</button>
                <button type="button" class="filter-tab" data-filter="unread">Chưa đọc</button>
                <button type="button" class="filter-tab" data-filter="employer">Nhà tuyển dụng</button>
                <button type="button" class="filter-tab" data-filter="joblink">JobLink</button>
            <?php elseif (in_array($currentRole, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])): ?>
                <button type="button" class="filter-tab active" data-filter="all">Tất cả</button>
                <button type="button" class="filter-tab" data-filter="unread">Chưa đọc</button>
                <button type="button" class="filter-tab" data-filter="seeker">Người lao động</button>
                <button type="button" class="filter-tab" data-filter="joblink">JobLink</button>
            <?php else: ?>
                <button type="button" class="filter-tab active" data-filter="all">Tất cả</button>
                <button type="button" class="filter-tab" data-filter="unread">Chưa đọc</button>
                <button type="button" class="filter-tab" data-filter="employer">Nhà tuyển dụng</button>
                <button type="button" class="filter-tab" data-filter="seeker">Người lao động</button>
                <button type="button" class="filter-tab" data-filter="joblink">JobLink</button>
            <?php endif; ?>
        </div>

        <!-- Contacts List -->
        <div class="conversation-list" id="conversationList">
            <!-- 1. JOBY AI ĐƯỢC TỰ ĐỘNG GHIM ĐẦU DANH SÁCH -->
            <div class="conversation-item conv-ai-pinned <?= (!empty($isAiActive) || ($partnerId === 'ai_joby')) ? 'active' : '' ?>" 
                 id="conv-ai-joby"
                 data-partner-id="ai_joby"
                 data-partner-name="trợ lý joby ai thông minh joblink"
                 data-partner-display="Trợ Lý Joby AI"
                 data-partner-avatar="<?= getJobyAvatar() ?>"
                 data-category="joblink"
                 data-unread="0"
                 onclick="openAiJobyChat()">
                <div class="conv-avatar-box">
                    <div class="conv-ai-avatar">
                        <img src="<?= getJobyAvatar() ?>" alt="Joby AI" class="conv-avatar conv-ai-avatar-img" onerror="this.src='<?= getSystemLogo() ?>'">
                    </div>
                    <span class="conv-ai-sparkle">✨</span>
                </div>
                <div class="conv-meta">
                    <div class="conv-meta-header">
                        <strong class="conv-name" style="color: #0f766e; display: flex; align-items: center; gap: 4px;">
                            <span>Trợ Lý Joby AI</span>
                            <span class="conv-pinned-badge" title="Được ghim đầu danh sách">📌</span>
                        </strong>
                        <span class="conv-time" style="color: #0d9488; font-weight: 700; font-size: 0.68rem;">24/7</span>
                    </div>
                    <div class="conv-meta-role">
                        <span class="conv-role-badge role-joby-ai">AI Trợ Lý Thông Minh</span>
                    </div>
                    <div class="conv-meta-body">
                        <span class="conv-snippet" id="jobySidebarSnippet">Hỏi tôi về việc làm, người lao động, cẩm nang...</span>
                    </div>
                </div>
            </div>

            <!-- Empty Notice khi lọc danh mục không có kết quả -->
            <div class="filter-empty-state" id="filterEmptyNotice" style="display:none;">
                <div class="filter-empty-icon">💬</div>
                <p>Không có đoạn chat nào</p>
                <span>Không tìm thấy tin nhắn phù hợp trong mục này.</span>
            </div>

            <!-- 2. DANH SÁCH HỘI THOẠI VỚI THÀNH VIÊN KHÁC -->
            <?php if (empty($conversations)): ?>
                <div class="conversation-empty-state" id="emptyConvStateNotice">
                    <div class="empty-icon"><?= icon('chat', '', 28) ?></div>
                    <p style="font-size: 0.8rem; margin: 0.2rem 0;">Chưa có trao đổi cá nhân nào</p>
                    <span class="empty-hint">Bạn có thể trò chuyện cùng Joby AI ở trên hoặc trao đổi khi có người liên hệ.</span>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $c): 
                    $pId = (int)$c['partner_id'];
                    $isActive = (!$isAiActive && $pId === (int)($partnerId ?? ($activePartner['account_id'] ?? 0)));
                    $pName = htmlspecialchars($c['partner_name'] ?? 'Thành viên');
                    $pAvatar = !empty($c['partner_avatar']) ? asset($c['partner_avatar']) : getSystemLogo();
                    $pRole = htmlspecialchars($c['partner_role_name'] ?? 'Thành viên');
                    $lastMsg = htmlspecialchars($c['last_message'] ?? 'Bắt đầu cuộc trò chuyện...');
                    $timeStr = !empty($c['last_message_time']) ? date('H:i - d/m', strtotime($c['last_message_time'])) : '';
                    $unread = (int)($c['unread_count'] ?? 0);

                    // Phân loại danh mục lọc: seeker | employer | joblink
                    $pRoleCode = $c['partner_role_code'] ?? '';
                    $pCategory = 'joblink';
                    if ($pRoleCode === 'JOB_SEEKER' || (!empty($c['seeker_id']) && empty($c['employer_id']))) {
                        $pCategory = 'seeker';
                    } elseif (in_array($pRoleCode, ['EMPLOYER_ADMIN', 'INTERMEDIARY']) || !empty($c['employer_id'])) {
                        $pCategory = 'employer';
                    } elseif (in_array($pRoleCode, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
                        $pCategory = 'joblink';
                    }

                    $isLastMine = (!empty($c['last_sender_id']) && (int)$c['last_sender_id'] === (int)$myId);
                    $snippetPrefix = $isLastMine ? 'Bạn: ' : '';
                ?>
                    <div class="conversation-item <?= $isActive ? 'active' : '' ?>" 
                         id="conv-item-<?= $pId ?>"
                         data-partner-id="<?= $pId ?>" 
                         data-partner-name="<?= strtolower($pName) ?>"
                         data-partner-display="<?= $pName ?>"
                         data-partner-avatar="<?= $pAvatar ?>"
                         data-category="<?= $pCategory ?>"
                         data-unread="<?= $unread ?>"
                         onclick="switchConversation(<?= $pId ?>)"
                         oncontextmenu="handlePartnerContextMenu(event, <?= $pId ?>, '<?= addslashes($pName) ?>')">
                        <div class="conv-avatar-box">
                            <img src="<?= $pAvatar ?>" alt="avatar" class="conv-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                            <span class="conv-online-dot"></span>
                        </div>
                        <div class="conv-meta">
                            <div class="conv-meta-header">
                                <strong class="conv-name" title="<?= $pName ?>">
                                    <span class="conv-partner-display-title"><?= $pName ?></span>
                                    <span class="conv-pinned-badge conv-user-pin" id="pin-badge-<?= $pId ?>" style="display:none;" title="Đã ghim">📌</span>
                                </strong>
                                <span class="conv-time"><?= $timeStr ?></span>
                            </div>
                            <div class="conv-meta-role">
                                <span class="conv-role-badge role-<?= strtolower($c['partner_role_code'] ?? 'user') ?>"><?= $pRole ?></span>
                            </div>
                            <div class="conv-meta-body">
                                <span class="conv-snippet <?= $unread > 0 ? 'unread-snippet' : '' ?>"><?= $snippetPrefix . $lastMsg ?></span>
                                <?php if ($unread > 0): ?>
                                    <span class="conv-unread-badge" id="unread-badge-<?= $pId ?>"><?= $unread ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ==================== CỘT 2: KHUNG CHAT TRỰC TIẾP ==================== -->
    <div class="messenger-main" id="messengerMain">
        <!-- 2A. KHUNG TRÒ CHUYỆN CÙNG JOBY AI (GHIM ĐẦU BÊN TRÁI) -->
        <div class="ai-joby-view" id="aiJobyMainView" style="display: <?= ($isAiActive || !$activePartner) ? 'flex' : 'none' ?>;">
            <!-- AI HEADER -->
            <div class="ai-joby-header">
                <div class="ai-joby-info">
                    <div class="ai-joby-avatar-wrap">
                        <img src="<?= getJobyAvatar() ?>" alt="Joby AI" class="ai-joby-avatar-img" onerror="this.src='<?= getSystemLogo() ?>'">
                        <span class="ai-joby-dot"></span>
                    </div>
                    <div>
                        <div class="ai-joby-title-row">
                            <h3 class="ai-joby-title">Trợ Lý Thông Minh Joby AI</h3>
                            <span class="ai-joby-badge">Hỗ trợ 24/7</span>
                        </div>
                        <div class="ai-joby-status">
                            <span>Hệ thống AI JobLink &bull; Luôn sẵn sàng giải đáp thắc mắc</span>
                        </div>
                    </div>
                </div>
                <div class="chat-header-actions">
                    <button type="button" class="btn-chat-tool" onclick="clearJobyHistory()" title="Xoá toàn bộ lịch sử trò chuyện cùng Joby AI">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        <span>Xoá lịch sử</span>
                    </button>
                </div>
            </div>

            <!-- AI STREAM -->
            <div class="ai-joby-stream" id="aiJobyStream">
                <div class="ai-hero-card">
                    <div class="ai-hero-icon">
                        <img src="<?= getJobyAvatar() ?>" alt="Joby AI" class="ai-hero-avatar-img" onerror="this.src='<?= getSystemLogo() ?>'">
                    </div>
                    <div class="ai-hero-content">
                        <h4>Xin chào! Mình là Joby AI</h4>
                        <p>Mình có thể hỗ trợ bạn tìm kiếm cơ hội việc làm, gợi ý phỏng vấn, tư vấn tuyển dụng hoặc giải đáp các thắc mắc về ca làm việc trên JobLink. Hãy chọn gợi ý nhanh bên dưới hoặc nhập câu hỏi trực tiếp nhé!</p>
                    </div>
                </div>
                <div id="aiJobyMessagesContainer" style="display: flex; flex-direction: column; gap: 0.65rem;"></div>
            </div>

            <!-- AI QUICK REPLIES BAR -->
            <div class="ai-quick-replies-bar">
                <span class="ai-chip-label" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                    <?= icon('sparkle', '', 12) ?> Gợi ý nhanh:
                </span>
                <div class="ai-chips-scroll" id="aiJobyChipsContainer">
                    <?php if (in_array($currentRole, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])): ?>
                        <button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">Mẹo đăng tin tuyển dụng thu hút</button>
                        <button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">Cách đối chiếu khung giờ rảnh phỏng vấn</button>
                        <button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">Gợi ý câu hỏi phỏng vấn theo ca</button>
                        <button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">Quy trình nhận việc và gửi cẩm nang</button>
                    <?php elseif ($currentRole === 'JOB_SEEKER'): ?>
                        <button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">Gợi ý việc làm theo ca phù hợp với tôi</button>
                        <button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">Cách viết hồ sơ ấn tượng</button>
                        <button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">Quy trình ký số ca nhận việc</button>
                        <button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">Bí quyết phỏng vấn thành công</button>
                    <?php else: ?>
                        <button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">Thống kê người dùng và tin tuyển dụng</button>
                        <button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">Tiêu chuẩn kiểm duyệt tin tuyển dụng</button>
                        <button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">Hướng dẫn giải quyết khiếu nại</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AI INPUT FOOTER -->
            <div class="chat-input-footer">
                <form id="aiJobyForm" class="chat-form-inline">
                    <div class="chat-input-box">
                        <textarea id="aiJobyInputText" 
                                  rows="1" 
                                  placeholder="Hỏi Joby bất cứ điều gì... (Enter để gửi, Shift+Enter để xuống dòng)" 
                                  autocomplete="off" 
                                  required></textarea>
                    </div>
                    <button type="submit" id="aiJobySendBtn" class="btn-chat-send" title="Gửi câu hỏi cho Joby (Enter)">
                        <?= icon('sparkle', '', 16) ?>
                        <span>Hỏi AI</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- 2B. KHUNG CHAT TRAO ĐỔI VỚI THÀNH VIÊN KHÁC -->
        <?php if ($activePartner): 
            $actId = (int)$activePartner['account_id'];
            $actName = htmlspecialchars($activePartner['display_name'] ?? $activePartner['username']);
            $actAvatar = !empty($activePartner['avatar_url']) ? asset($activePartner['avatar_url']) : getSystemLogo();
            $actRoleCode = $activePartner['role_code'] ?? '';
            $actRoleName = htmlspecialchars($activePartner['role_name'] ?? 'Thành viên');
            $isPartnerSeeker = ($actRoleCode === 'JOB_SEEKER');
            $isPartnerEmployer = in_array($actRoleCode, ['EMPLOYER_ADMIN', 'INTERMEDIARY']);
        ?>
            <div id="userChatMainView" class="messenger-user-chat" style="display: <?= (!$isAiActive) ? 'flex' : 'none' ?>; flex-direction: column; height: 100%; width: 100%; min-height: 0;">
                <!-- ACTIVE CHAT HEADER -->
                <div class="chat-main-header">
                    <div class="chat-partner-info">
                        <div class="chat-partner-avatar-wrap">
                            <img src="<?= $actAvatar ?>" alt="avatar" class="chat-partner-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                            <span class="status-indicator-dot online"></span>
                        </div>
                        <div>
                            <div class="chat-partner-name-row">
                                <h3 class="chat-partner-title"><?= $actName ?></h3>
                                <span class="chat-role-tag"><?= $actRoleName ?></span>
                            </div>
                            <div class="chat-partner-status">
                                <span>Đang trực tuyến &bull; Phản hồi nhanh</span>
                                <?php if (!empty($activePartner['phone_number'])): ?>
                                    <span>&bull; SĐT: <?= htmlspecialchars($activePartner['phone_number']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- QUICK ACTIONS & MESSENGER ICONS -->
                    <div class="chat-header-actions">
                        <?php if ($isPartnerSeeker && !empty($activePartner['seeker_id'])): ?>
                            <a href="<?= url('/employer/candidates/' . $activePartner['seeker_id']) ?>" class="btn-chat-tool" title="Xem chi tiết hồ sơ người lao động" target="_blank">
                                <?= icon('user', '', 14) ?> <span>Hồ sơ</span>
                            </a>
                            <?php if (in_array($currentRole, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])): ?>
                                <button type="button" class="btn-chat-tool btn-accept-tool" id="btnOpenChatAcceptModal" title="Duyệt nhận việc & gửi cẩm nang ngày đầu" style="background: #059669; color: #ffffff; border-color: #059669; font-weight: 700;">
                                    <?= icon('check_circle', '', 14) ?> <span>Duyệt Nhận Việc</span>
                                </button>
                                <button type="button" class="btn-chat-tool btn-schedule-tool" id="btnOpenMeetingModal" title="Lên lịch phỏng vấn và gửi cẩm nang cho người lao động" style="background: #0d9488; color: #ffffff; border-color: #0d9488; font-weight: 700;">
                                    <?= icon('calendar', '', 14) ?> <span>Hẹn Lịch Phỏng Vấn</span>
                                </button>
                                <button type="button" class="btn-chat-tool btn-ai-tool" id="btnToggleAiSlots" title="AI đối chiếu lịch rảnh">
                                    <?= icon('sparkle', '', 14) ?> <span>AI Gợi ý lịch</span>
                                </button>
                            <?php endif; ?>
                        <?php elseif ($isPartnerEmployer && !empty($activePartner['employer_id'])): ?>
                            <a href="<?= url('/employers/' . $activePartner['employer_id']) ?>" class="btn-chat-tool" title="Xem trang doanh nghiệp" target="_blank">
                                <?= icon('building', '', 14) ?> <span>Doanh nghiệp</span>
                            </a>
                        <?php endif; ?>

                        <!-- Standard Messenger Action Buttons -->
                        <button type="button" class="btn-messenger-action" title="Bắt đầu cuộc gọi thoại" onclick="alert('Tính năng gọi thoại bảo mật qua JobLink đang kết nối với <?= addslashes($actName) ?>...')">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 0 0-1.01.24l-2.2 2.2a15.05 15.05 0 0 1-6.59-6.59l2.2-2.21a.96.96 0 0 0 .25-1.01A11.36 11.36 0 0 1 8.57 3.9c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1 0 9.39 7.61 17 17 17 .55 0 1-.45 1-1v-3.52c0-.55-.45-1-.99-1z"/>
                            </svg>
                        </button>
                        <button type="button" class="btn-messenger-action" title="Bắt đầu cuộc gọi video" onclick="alert('Tính năng gọi video bảo mật qua JobLink đang kết nối với <?= addslashes($actName) ?>...')">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
                            </svg>
                        </button>
                        <button type="button" class="btn-messenger-action" title="Thông tin về cuộc trò chuyện" onclick="alert('Đoạn chat với: <?= addslashes($actName) ?> (<?= addslashes($actRoleName) ?>)')">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- AI SCHEDULE SUGGESTION BOX (COLLAPSIBLE) -->
                <?php if ($isPartnerSeeker && in_array($currentRole, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])): ?>
                    <div class="ai-slots-accordion" id="aiSlotsAccordion" style="display:none;">
                        <div class="ai-slots-box">
                            <div class="ai-slots-header">
                                <strong style="display: inline-flex; align-items: center; gap: 0.3rem;"><?= icon('sparkle', '', 14) ?> Khung giờ rảnh AI gợi ý cho 2 bên:</strong>
                                <span id="aiSlotLoading" style="display:none; font-size:0.75rem; color:#0f766e;">Đang quét lịch...</span>
                            </div>
                            <div id="aiSlotsContainer" class="ai-slots-container">
                                <span style="font-size:0.8rem; color:#64748b;">Đang tải danh sách khung giờ tối ưu...</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- MESSAGE STREAM (BODY) -->
                <div class="chat-stream-container" id="chatStreamContainer">
                    <div class="chat-messages-stream" id="chatMessagesStream">
                        <?php if (empty($thread)): ?>
                            <div class="chat-thread-starter">
                                <div class="starter-avatar">
                                    <img src="<?= $actAvatar ?>" alt="avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                                </div>
                                <h4>Bắt đầu trò chuyện cùng <?= $actName ?></h4>
                                <p>Hãy gửi lời chào hoặc trao đổi về công việc để kết nối ngay nhé!</p>
                            </div>
                        <?php else: ?>
                            <?php 
                            $prevTime = 0;
                            foreach ($thread as $msg): 
                                $isMine = ((int)$msg['sender_account_id'] === (int)$myId);
                                $curTime = strtotime($msg['sent_at']);
                                $senderAvatar = !empty($msg['sender_avatar']) ? asset($msg['sender_avatar']) : getSystemLogo();
                                $msgId = (int)$msg['message_id'];
                                $rawContent = $msg['message_content'];

                                // Mốc thời gian giữa các lần nhắn cách nhau từ 15 phút trở lên hoặc tin nhắn đầu
                                if ($prevTime === 0 || ($curTime - $prevTime) >= 900): 
                            ?>
                                    <div class="chat-time-divider">
                                        <span><?= formatSmartTimeDivider($msg['sent_at']) ?></span>
                                    </div>
                            <?php 
                                endif; 
                                $prevTime = $curTime;
                            ?>

                                <div class="chat-message-row <?= $isMine ? 'outgoing-row' : 'incoming-row' ?>" 
                                     data-msg-id="<?= $msgId ?>"
                                     data-is-mine="<?= $isMine ? '1' : '0' ?>"
                                     data-sender-name="<?= $isMine ? 'Bạn' : $actName ?>"
                                     data-sent-at="<?= $msg['sent_at'] ?>"
                                     oncontextmenu="handleMsgContextMenu(event, this)">
                                    
                                    <?php if (!$isMine): ?>
                                        <img src="<?= $senderAvatar ?>" alt="avatar" class="bubble-sender-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                                    <?php else: ?>
                                        <!-- Dark pill time tooltip: hiện khi để chuột lâu hơn (outgoing) -->
                                        <div class="msg-time-tooltip"><?= formatSmartTimeTooltip($msg['sent_at']) ?></div>

                                        <!-- 3 nút tròn thao tác nhanh khi rê chuột: ⋮ ↩ 😊 (outgoing) -->
                                        <div class="msg-hover-tools">
                                            <button type="button" class="btn-msg-round" title="Xem thêm" onclick="openMsgActionsMenu(event, this)">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                                    <circle cx="12" cy="5" r="2.2"></circle>
                                                    <circle cx="12" cy="12" r="2.2"></circle>
                                                    <circle cx="12" cy="19" r="2.2"></circle>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn-msg-round" title="Trả lời tin nhắn" onclick="actionReplyMsg(this)">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="9 14 4 9 9 4"></polyline>
                                                    <path d="M20 20v-7a4 4 0 0 0-4-4H4"></path>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn-msg-round btn-react-trigger" title="Bày tỏ cảm xúc" onclick="toggleMsgReactions(event, this)">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                                                    <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                                    <line x1="15" y1="9" x2="15.01" y2="9"></line>
                                                </svg>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <div class="chat-bubble-wrap">
                                        <div class="chat-bubble <?= $isMine ? 'bubble-outgoing' : 'bubble-incoming' ?> <?= in_array(($msg['message_type'] ?? ''), ['invitation', 'meeting_invite']) ? 'bubble-invitation' : '' ?> <?= (($msg['message_type'] ?? '') === 'onboarding') ? 'bubble-onboarding' : '' ?>">
                                            <?php if (!empty($msg['message_title'])): ?>
                                                <div class="bubble-title"><?= htmlspecialchars($msg['message_title']) ?></div>
                                            <?php endif; ?>
                                            <div class="bubble-content"><?= nl2br(htmlspecialchars($rawContent)) ?></div>
                                        </div>

                                        <!-- Floating reaction bar -->
                                        <div class="msg-reaction-bar" id="reactionBar-<?= $msgId ?>" style="display:none;">
                                            <button type="button" class="react-emoji-btn" onclick="addMsgReaction(<?= $msgId ?>, '👍')">👍</button>
                                            <button type="button" class="react-emoji-btn" onclick="addMsgReaction(<?= $msgId ?>, '❤️')">❤️</button>
                                            <button type="button" class="react-emoji-btn" onclick="addMsgReaction(<?= $msgId ?>, '😂')">😂</button>
                                            <button type="button" class="react-emoji-btn" onclick="addMsgReaction(<?= $msgId ?>, '😮')">😮</button>
                                            <button type="button" class="react-emoji-btn" onclick="addMsgReaction(<?= $msgId ?>, '😢')">😢</button>
                                            <button type="button" class="react-emoji-btn" onclick="addMsgReaction(<?= $msgId ?>, '😡')">😡</button>
                                        </div>
                                    </div>

                                    <?php if (!$isMine): ?>
                                        <!-- 3 nút tròn thao tác nhanh khi rê chuột: 😊 ↩ ⋮ (incoming) -->
                                        <div class="msg-hover-tools">
                                            <button type="button" class="btn-msg-round btn-react-trigger" title="Bày tỏ cảm xúc" onclick="toggleMsgReactions(event, this)">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                                                    <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                                    <line x1="15" y1="9" x2="15.01" y2="9"></line>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn-msg-round" title="Trả lời tin nhắn" onclick="actionReplyMsg(this)">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="9 14 4 9 9 4"></polyline>
                                                    <path d="M20 20v-7a4 4 0 0 0-4-4H4"></path>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn-msg-round" title="Xem thêm" onclick="openMsgActionsMenu(event, this)">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                                    <circle cx="12" cy="5" r="2.2"></circle>
                                                    <circle cx="12" cy="12" r="2.2"></circle>
                                                    <circle cx="12" cy="19" r="2.2"></circle>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Dark pill time tooltip: hiện khi để chuột lâu hơn (incoming) -->
                                        <div class="msg-time-tooltip"><?= formatSmartTimeTooltip($msg['sent_at']) ?></div>
                                    <?php endif; ?>

                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- Nút cuộn xuống dưới cùng nổi giữa đáy chat giống Messenger -->
                    <button type="button" class="chat-scroll-bottom-btn" id="chatScrollBottomBtn" title="Cuộn xuống tin nhắn mới nhất" style="display:none;" onclick="scrollChatToBottom(true)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <polyline points="19 12 12 19 5 12"></polyline>
                        </svg>
                    </button>
                </div>

                <!-- AI QUICK REPLY CHIPS -->
                <div class="ai-quick-replies-bar" id="aiQuickRepliesBar">
                    <span class="ai-chip-label" style="display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('sparkle', '', 12) ?> Gợi ý nhanh:</span>
                    <div class="ai-chips-scroll">
                        <?php if (in_array($currentRole, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])): ?>
                            <button type="button" class="ai-reply-chip" data-text="Chào bạn! Doanh nghiệp đã xem qua hồ sơ và rất ấn tượng với kinh nghiệm của bạn.">Chào mừng người lao động</button>
                            <button type="button" class="ai-reply-chip" data-text="Bạn có thể tham gia phỏng vấn trao đổi trực tiếp vào ngày mai được không ạ?">Hẹn phỏng vấn</button>
                            <button type="button" class="ai-reply-chip" data-text="Mức thu nhập và chế độ đãi ngộ cho vị trí này đã được cập nhật chi tiết trong tin tuyển dụng nhé bạn.">Trao đổi mức lương</button>
                            <button type="button" class="ai-reply-chip" data-text="Cảm ơn bạn đã phản hồi! Công ty sẽ gửi thông báo kết quả sớm nhất.">Cảm ơn phản hồi</button>
                        <?php elseif ($currentRole === 'JOB_SEEKER'): ?>
                            <button type="button" class="ai-reply-chip" data-text="Dạ em chào anh/chị tuyển dụng ạ! Em rất quan tâm đến vị trí này.">Chào nhà tuyển dụng</button>
                            <button type="button" class="ai-reply-chip" data-text="Dạ em sẵn sàng tham gia phỏng vấn theo lịch hẹn của công ty ạ.">Đồng ý lịch hẹn</button>
                            <button type="button" class="ai-reply-chip" data-text="Cho em xin hỏi thêm về thời gian làm việc cụ thể của ca này với ạ?">Hỏi ca làm việc</button>
                            <button type="button" class="ai-reply-chip" data-text="Dạ em đã chuẩn bị đầy đủ hồ sơ và sẽ đến đúng giờ hẹn ạ.">Xác nhận đến hẹn</button>
                        <?php else: ?>
                            <button type="button" class="ai-reply-chip" data-text="Chào bạn, Ban Quản Trị JobLink có thể hỗ trợ gì cho bạn?">Chào người dùng</button>
                            <button type="button" class="ai-reply-chip" data-text="Yêu cầu của bạn đã được tiếp nhận và xử lý thành công.">Xác nhận xử lý</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- CHAT INPUT FOOTER CHUẨN MESSENGER -->
                <div class="chat-input-footer messenger-footer-style">
                    <!-- THANH PREVIEW TRẢ LỜI TIN NHẮN -->
                    <div class="chat-reply-preview-bar" id="chatReplyPreviewBar" style="display:none;">
                        <div class="reply-preview-content">
                            <div class="reply-preview-to" id="replyPreviewTo">Đang trả lời: ...</div>
                            <div class="reply-preview-text" id="replyPreviewText">...</div>
                        </div>
                        <button type="button" class="reply-cancel-btn" id="cancelReplyBtn" onclick="cancelReply()" title="Hủy trả lời">&times;</button>
                    </div>

                    <!-- Hidden file picker -->
                    <input type="file" id="chatFileInput" accept="image/*,.pdf,.doc,.docx" style="display:none;" onchange="handleChatFileUpload(this)">

                    <form id="chatMessageForm" class="chat-form-inline">
                        <input type="hidden" id="activeReceiverId" value="<?= $actId ?>">
                        
                        <!-- Messenger toolbar on left -->
                        <div class="chat-tools-left">
                            <button type="button" class="btn-chat-icon" title="Gửi ghi âm giọng nói" onclick="triggerVoiceNote()">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                                </svg>
                            </button>
                            <button type="button" class="btn-chat-icon" title="Đính kèm hình ảnh hoặc file" onclick="document.getElementById('chatFileInput').click()">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                </svg>
                            </button>
                            <button type="button" class="btn-chat-icon" title="Chọn nhãn dán" onclick="insertQuickEmoji('👍')">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                                </svg>
                            </button>
                            <button type="button" class="btn-chat-icon" title="Chọn file GIF" onclick="insertQuickEmoji('🎉')">
                                <span class="gif-icon-pill">GIF</span>
                            </button>
                        </div>

                        <!-- Pill Input Box -->
                        <div class="chat-input-box messenger-pill-input">
                            <textarea id="chatInputText" 
                                      rows="1" 
                                      placeholder="Aa" 
                                      autocomplete="off" 
                                      required></textarea>
                            <button type="button" class="btn-input-emoji" title="Chọn biểu tượng cảm xúc" onclick="toggleEmojiPicker(this)">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Right Send / Like button -->
                        <button type="submit" id="chatSendButton" class="btn-messenger-send" title="Gửi tin nhắn (Enter)">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" id="chatSendSvg">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ==================== CÁC CONTEXT MENU & MODAL BỔ TRỢ ==================== -->
<!-- 1. CONTEXT MENU CHO TIN NHẮN (TRẢ LỜI, SAO CHÉP, CHUYỂN TIẾP, XOÁ, THU HỒI) -->
<div class="chat-context-menu" id="messageContextMenu" style="display:none;">
    <button type="button" class="ctx-item" data-action="reply" onclick="execMsgCtxAction('reply')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 14 4 9 9 4"></polyline><path d="M20 20v-7a4 4 0 0 0-4-4H4"></path></svg>
        <span>Trả lời</span>
    </button>
    <button type="button" class="ctx-item" data-action="copy" onclick="execMsgCtxAction('copy')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
        <span>Sao chép</span>
    </button>
    <button type="button" class="ctx-item" data-action="forward" onclick="execMsgCtxAction('forward')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 14 20 9 15 4"></polyline><path d="M4 20v-7a4 4 0 0 1 4-4h12"></path></svg>
        <span>Chuyển tiếp</span>
    </button>
    <div class="ctx-divider"></div>
    <button type="button" class="ctx-item ctx-danger" data-action="delete" onclick="execMsgCtxAction('delete')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
        <span>Xoá tin nhắn</span>
    </button>
    <button type="button" class="ctx-item ctx-danger" data-action="recall" id="ctxRecallBtn" style="display:none;" onclick="execMsgCtxAction('recall')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
        <span>Thu hồi</span>
    </button>
</div>

<!-- 2. CONTEXT MENU CHO NGƯỜI DÙNG / CUỘC TRÒ CHUYỆN (GHIM, ẨN, XOÁ) -->
<div class="chat-context-menu" id="conversationContextMenu" style="display:none;">
    <button type="button" class="ctx-item" data-action="pin" onclick="execConvCtxAction('pin')">
        <span style="font-size:0.95rem;">📌</span>
        <span id="ctxPinLabel">Ghim cuộc trò chuyện</span>
    </button>
    <button type="button" class="ctx-item" data-action="hide" onclick="execConvCtxAction('hide')">
        <span style="font-size:0.95rem;">👁️‍🗨️</span>
        <span>Ẩn tin nhắn</span>
    </button>
    <div class="ctx-divider"></div>
    <button type="button" class="ctx-item ctx-danger" data-action="delete" onclick="execConvCtxAction('delete')">
        <span style="font-size:0.95rem;">🗑️</span>
        <span>Xoá tin nhắn</span>
    </button>
</div>

<!-- 3. MODAL CHUYỂN TIẾP TIN NHẮN (FORWARD) -->
<div id="forwardMsgModal" class="chat-modal-overlay" style="display:none;">
    <div class="chat-modal-box" style="width: 440px;">
        <div class="chat-modal-header">
            <h3 style="display: flex; align-items: center; gap: 0.4rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 14 20 9 15 4"></polyline><path d="M4 20v-7a4 4 0 0 1 4-4h12"></path></svg>
                <span>Chuyển Tiếp Tin Nhắn</span>
            </h3>
            <button type="button" class="modal-close-btn" onclick="closeForwardModal()">&times;</button>
        </div>
        <div class="chat-modal-body" style="padding: 1rem 1.25rem;">
            <div class="forward-preview-box">
                <span class="forward-preview-label">Nội dung chuyển tiếp:</span>
                <div class="forward-preview-content" id="forwardMsgPreview">...</div>
            </div>
            <div class="forward-contact-title">Chọn cuộc trò chuyện để gửi:</div>
            <div class="forward-contact-list" id="forwardContactList">
                <!-- Danh sách người nhận được sinh động bởi JS -->
            </div>
        </div>
    </div>
</div>

<!-- MODAL GỬI THÔNG BÁO HỆ THỐNG (Dành riêng cho ADMIN) -->
<?php if (in_array($currentRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])): ?>
    <div id="broadcastModal" class="chat-modal-overlay" style="display:none;">
        <div class="chat-modal-box">
            <div class="chat-modal-header">
                <h3 style="display: flex; align-items: center; gap: 0.4rem;"><?= icon('bell', '', 16) ?> Phát Thông Báo Hệ Thống</h3>
                <button type="button" class="modal-close-btn" onclick="closeBroadcastModal()">&times;</button>
            </div>
            <form action="<?= url('/admin/messages/broadcast') ?>" method="POST" class="chat-modal-body">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" style="font-weight: 700;">Gửi Đến Tài Khoản *</label>
                    <select name="receiver_account_id" class="form-control" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                        <?php foreach ($accounts ?? [] as $acc): ?>
                            <option value="<?= $acc['account_id'] ?>">
                                [<?= formatId('account', $acc['account_id']) ?>] <?= htmlspecialchars($acc['username']) ?> (<?= htmlspecialchars($acc['email']) ?> - <?= $acc['role_name'] ?? $acc['role_code'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" style="font-weight: 700;">Tiêu Đề Thông Báo *</label>
                    <input type="text" name="message_title" class="form-control" required placeholder="VD: Duyệt tin tuyển dụng / Nhắc nhở quy định..." style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 700;">Nội Dung Thông Báo *</label>
                    <textarea name="message_content" class="form-control" rows="4" required placeholder="Nội dung thông báo hệ thống..." style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;"></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" class="btn btn-outline" onclick="closeBroadcastModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Gửi Thông Báo</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- MODAL THIẾT LẬP LỊCH HẸN PHỎNG VẤN TRỰC TIẾP TỪ CHAT (EMPLOYER)            -->
<!-- ========================================================================= -->
<?php if (in_array($currentRole, ['EMPLOYER_ADMIN', 'INTERMEDIARY']) && $activePartner && $isPartnerSeeker): ?>
<div id="chatMeetingModal" class="chat-modal-overlay" style="display: <?= !empty($openMeetingModal) ? 'flex' : 'none' ?>;">
    <div class="chat-modal-box" style="width: 640px; max-width: 95vw; max-height: 92vh; display: flex; flex-direction: column;">
        <div class="chat-modal-header" style="background: #f0fdfa; border-bottom: 1px solid #ccfbf1; padding: 1rem 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #0d9488; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <?= icon('calendar', '', 20) ?>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.08rem; font-weight: 800; color: #0f172a;">Thiết Lập Lịch Phỏng Vấn & Gửi Lời Mời</h3>
                    <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: #64748b;">
                        Người lao động: <strong style="color: #0d9488;"><?= $actName ?></strong> · Thông tin sẽ gửi qua chat & email
                    </p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="closeMeetingModalBtn">&times;</button>
        </div>

        <form id="chatMeetingForm" action="<?= url('/employer/meetings/schedule-from-chat') ?>" method="POST" style="overflow-y: auto; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.95rem;">
            <input type="hidden" name="partner_id" value="<?= $actId ?>">
            <input type="hidden" name="seeker_id" value="<?= (int)($activePartner['seeker_id'] ?? ($seekerId ?? 0)) ?>">
            <input type="hidden" name="request_id" id="chatMeetingRequestId" value="<?= (int)($selectedRequestId ?? ($requestId ?? 0)) ?>">
            <input type="hidden" name="is_ajax" value="1">

            <!-- 1. VỊ TRÍ TUYỂN DỤNG -->
            <div>
                <label style="font-size: 0.84rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">
                    Vị trí tuyển dụng đang mở: *
                </label>
                <select name="job_post_id" id="chatMeetingJobSelect" class="form-control" required style="width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.9rem;">
                    <?php if (empty($activeJobs)): ?>
                        <option value="">-- Doanh nghiệp chưa có tin tuyển dụng nào đang mở --</option>
                    <?php else: ?>
                        <?php foreach ($activeJobs as $job): 
                            $isJobSel = ((int)$job['job_post_id'] === (int)$selectedJobId);
                        ?>
                            <option value="<?= $job['job_post_id'] ?>" 
                                    data-workplace="<?= htmlspecialchars($job['workplace_address'] ?? 'Văn phòng Công ty') ?>"
                                    <?= $isJobSel ? 'selected' : '' ?>>
                                <?= htmlspecialchars($job['job_title']) ?> (<?= htmlspecialchars($job['salary_display'] ?? 'Lương thỏa thuận') ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- 2. AI SMART SLOTS (GỢI Ý KHUNG GIỜ NHANH - THANH NGANG TIMELINE) -->
            <div id="chatAiSlotsSection" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.75rem 0.85rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: #0f766e; display: flex; align-items: center; gap: 0.3rem;">
                        <?= icon('sparkle', '', 14) ?> <span>Khung giờ AI gợi ý trống lịch (Trục thời gian tương tác):</span>
                    </span>
                    <span id="chatAiSlotLoading" style="display:none; font-size: 0.75rem; color: #64748b;">Đang quét...</span>
                </div>
                
                <div id="chatTimelineWrap" style="display: none; position: relative; padding: 0.25rem 0.25rem 0.5rem;">
                    <div id="chatTimelineBgBar" style="position: absolute; height: 3px; background: #cbd5e1; top: 34px; z-index: 1; border-radius: 2px;"></div>
                    <div id="chatTimelineActiveBar" style="position: absolute; height: 3px; background: #0d9488; top: 34px; z-index: 2; border-radius: 2px; transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                    <div id="chatTimelineNodes" style="position: relative; z-index: 3; display: flex; justify-content: space-between; align-items: flex-start;"></div>
                    
                    <div id="chatActiveSlotNotice" style="margin-top: 0.6rem; padding: 0.45rem 0.75rem; background: #ffffff; border: 1px solid #ccfbf1; border-left: 3px solid #0d9488; border-radius: 6px; font-size: 0.78rem; color: #0f172a; display: flex; justify-content: space-between; align-items: center;">
                        <span id="chatActiveSlotText" style="font-weight: 600; color: #0f766e;"></span>
                        <span style="font-size: 0.7rem; color: #047857; background: #ecfdf5; padding: 1px 6px; border-radius: 4px;">Đã tự điền form</span>
                    </div>
                </div>

                <div id="chatAiSlotsChips" style="color: #94a3b8; font-size: 0.78rem;">
                    Đang tải gợi ý lịch rảnh...
                </div>
            </div>

            <!-- 3. THỜI GIAN PHỎNG VẤN -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 0.75rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Ngày phỏng vấn: *</label>
                    <input type="date" name="meeting_date" id="chatMeetingDate" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d') ?>" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Bắt đầu: *</label>
                    <input type="time" name="start_time" id="chatMeetingStartTime" class="form-control" required value="09:00" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Kết thúc: *</label>
                    <input type="time" name="end_time" id="chatMeetingEndTime" class="form-control" required value="09:45" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
            </div>

            <!-- 4. KHOẢNG NGÀY PHỎNG VẤN CỦA ĐỢT -->
            <div>
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">
                    Khoảng ngày phỏng vấn dự kiến (Để người lao động chuẩn bị thời gian & xin dời nếu bận):
                </label>
                <input type="text" name="interview_range" id="chatInterviewRange" class="form-control" placeholder="Ví dụ: Từ ngày <?= date('d/m', strtotime('+1 day')) ?> đến <?= date('d/m/Y', strtotime('+4 days')) ?> (Trong giờ hành chính)" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
            </div>

            <!-- 5. HÌNH THỨC & ĐỊA ĐIỂM -->
            <div style="display: grid; grid-template-columns: 140px 1fr; gap: 0.75rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Hình thức: *</label>
                    <select name="meeting_format" id="chatMeetingFormat" class="form-control" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                        <option value="offline">Trực tiếp</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Địa chỉ cơ sở hoặc Link phòng họp Online: *</label>
                    <input type="text" name="location_or_meeting_link" id="chatMeetingLocation" class="form-control" required value="<?= htmlspecialchars($activeJobs[0]['workplace_address'] ?? 'Văn phòng Công ty') ?>" placeholder="Địa chỉ cơ sở hoặc link Google Meet / Zoom..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
            </div>

            <!-- 6. CẨM NANG CHUẨN BỊ -->
            <div>
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">
                    Cẩm nang hướng dẫn người lao động chuẩn bị trước buổi phỏng vấn:
                </label>
                <textarea name="prep_guide" id="chatMeetingPrepGuide" rows="2" class="form-control" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">Tìm hiểu thông tin công ty và vị trí công việc. Chuẩn bị phần giới thiệu kinh nghiệm bản thân và mang theo CCCD/bản in CV khi đến tham gia phỏng vấn.</textarea>
            </div>

            <!-- 7. GHI CHÚ THÊM -->
            <div>
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">
                    Lời nhắn / Ghi chú dặn dò thêm:
                </label>
                <textarea name="meeting_note" id="chatMeetingNote" rows="2" class="form-control" placeholder="Trang phục lịch sự, liên hệ bảo vệ để gửi xe ở tầng hầm..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;"></textarea>
            </div>

            <!-- FOOTER BUTTONS -->
            <div style="display: flex; justify-content: flex-end; gap: 0.6rem; border-top: 1px solid #e2e8f0; padding-top: 0.85rem; margin-top: 0.25rem;">
                <button type="button" class="btn btn-outline btn-sm" id="cancelMeetingModalBtn">Hủy Bỏ</button>
                <button type="submit" class="btn btn-primary btn-sm" id="submitMeetingModalBtn" style="font-weight: 700; padding: 0.5rem 1.25rem; display: inline-flex; align-items: center; gap: 0.35rem; background: #0d9488; border-color: #0d9488;">
                    <?= icon('calendar', '', 15) ?>
                    <span>Xác Nhận & Gửi Thư Mời (Chat + Email)</span>
                </button>
            </div>
        </form>
    </div>
</div>
<!-- ==================== MODAL DUYỆT NHẬN VIỆC & THIẾT LẬP ONBOARDING TRỰC TIẾP TỪ CHAT ==================== -->
<div id="chatAcceptOnboardingModal" class="chat-modal-overlay" style="display: none;">
    <div class="chat-modal-box" style="width: 640px; max-width: 95vw; max-height: 92vh; display: flex; flex-direction: column;">
        <div class="chat-modal-header" style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0; padding: 1rem 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #059669; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <?= icon('check_circle', '', 20) ?>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.08rem; font-weight: 800; color: #0f172a;">Duyệt Nhận Việc & Cẩm Nang Ngày Đầu Đi Làm</h3>
                    <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: #64748b;">
                        Người lao động: <strong style="color: #059669;"><?= $actName ?></strong> · Thông báo trúng tuyển & hướng dẫn gửi qua Chat
                    </p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="closeChatAcceptModalBtn">&times;</button>
        </div>

        <form id="chatAcceptOnboardingForm" action="<?= url('/employer/applications/accept-onboarding') ?>" method="POST" style="overflow-y: auto; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.95rem;">
            <input type="hidden" name="partner_id" value="<?= $actId ?>">
            <input type="hidden" name="seeker_id" value="<?= (int)($activePartner['seeker_id'] ?? ($seekerId ?? 0)) ?>">
            <input type="hidden" name="request_id" id="chatAcceptRequestId" value="<?= (int)($selectedRequestId ?? ($seekerApplications[0]['request_id'] ?? 0)) ?>">
            <input type="hidden" name="is_ajax" value="1">

            <!-- 1. VỊ TRÍ TUYỂN DỤNG ĐƯỢC NHẬN -->
            <div>
                <label style="font-size: 0.84rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">
                    Vị trí công việc duyệt nhận: *
                </label>
                <select name="job_post_id" id="chatAcceptJobSelect" class="form-control" required style="width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.9rem;">
                    <?php if (!empty($seekerApplications)): ?>
                        <optgroup label="Vị trí người lao động đã nộp hồ sơ">
                            <?php foreach ($seekerApplications as $app): 
                                $isSelApp = ((int)($selectedRequestId ?? 0) === (int)$app['request_id']);
                            ?>
                                <option value="<?= $app['job_post_id'] ?>" 
                                        data-request-id="<?= $app['request_id'] ?>" 
                                        data-workplace="<?= htmlspecialchars($app['workplace_address'] ?? 'Văn phòng Công ty') ?>"
                                        <?= $isSelApp ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($app['job_title']) ?> (<?= htmlspecialchars($app['salary_display'] ?? 'Lương thỏa thuận') ?>) - [Đơn #<?= $app['request_id'] ?>]
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>

                    <?php if (!empty($activeJobs)): ?>
                        <optgroup label="Tất cả vị trí đang mở của doanh nghiệp">
                            <?php foreach ($activeJobs as $job): 
                                $isJobSelected = (empty($seekerApplications) && (int)$job['job_post_id'] === (int)$selectedJobId);
                            ?>
                                <option value="<?= $job['job_post_id'] ?>" 
                                        data-request-id="0" 
                                        data-workplace="<?= htmlspecialchars($job['workplace_address'] ?? 'Văn phòng Công ty') ?>"
                                        <?= $isJobSelected ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($job['job_title']) ?> (<?= htmlspecialchars($job['salary_display'] ?? 'Lương thỏa thuận') ?>)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>

            <!-- 2. THỜI GIAN NGÀY ĐẦU TIÊN -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Ngày bắt đầu làm việc: *</label>
                    <input type="date" name="start_date" id="chatAcceptStartDate" class="form-control" required value="<?= date('Y-m-d', strtotime('+3 days')) ?>" min="<?= date('Y-m-d') ?>" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Giờ có mặt buổi đầu: *</label>
                    <input type="time" name="start_time" id="chatAcceptStartTime" class="form-control" required value="08:30" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
            </div>

            <!-- 3. ĐỊA ĐIỂM & NGƯỜI ĐÓN TIẾP -->
            <div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 0.75rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Địa điểm làm việc: *</label>
                    <input type="text" name="workplace" id="chatAcceptWorkplace" class="form-control" required value="<?= htmlspecialchars($seekerApplications[0]['workplace_address'] ?? ($activeJobs[0]['workplace_address'] ?? 'Văn phòng Công ty')) ?>" placeholder="Địa chỉ nơi làm việc..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Người đón tiếp / Quản lý: *</label>
                    <input type="text" name="contact_person" id="chatAcceptContactPerson" class="form-control" required value="Phòng Nhân sự / Quản lý trực tiếp" placeholder="Họ tên & SĐT liên hệ..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
            </div>

            <!-- 4. CHECKLIST NHỮNG THỨ CẦN CHUẨN BỊ -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.9rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #0f172a; display: block; margin-bottom: 0.4rem;">
                    Danh mục người lao động cần chuẩn bị mang theo ngày đầu:
                </label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; font-size: 0.82rem; color: #334155;">
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Căn cước công dân (bản gốc hoặc photo)" checked>
                        <span>Căn cước công dân</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Bằng cấp / Chứng chỉ chuyên môn liên quan" checked>
                        <span>Bằng cấp / Chứng chỉ</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Trang phục lịch sự / Đồng phục theo yêu cầu" checked>
                        <span>Trang phục theo quy định</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Thông tin tài khoản ngân hàng nhận lương" checked>
                        <span>Số tài khoản ngân hàng</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Sổ ghi chép cá nhân hoặc laptop làm việc" checked>
                        <span>Sổ tay / Bút / Laptop</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Sổ bảo hiểm xã hội (nếu có)">
                        <span>Sổ bảo hiểm xã hội</span>
                    </label>
                </div>
            </div>

            <!-- 5. LỜI CHÚC & DẶN DÒ -->
            <div>
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">
                    Lời nhắn & Hướng dẫn dặn dò thêm:
                </label>
                <textarea name="notes" id="chatAcceptNotes" rows="2" class="form-control" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">Chúc mừng bạn đã trúng tuyển! Hãy đến đúng giờ và liên hệ người đón tiếp tại sảnh lễ tân khi đến công ty nhé.</textarea>
            </div>

            <!-- FOOTER BUTTONS -->
            <div style="display: flex; justify-content: flex-end; gap: 0.6rem; border-top: 1px solid #e2e8f0; padding-top: 0.85rem; margin-top: 0.25rem;">
                <button type="button" class="btn btn-outline btn-sm" id="cancelChatAcceptModalBtn">Hủy Bỏ</button>
                <button type="submit" class="btn btn-primary btn-sm" id="submitChatAcceptModalBtn" style="font-weight: 700; padding: 0.5rem 1.25rem; display: inline-flex; align-items: center; gap: 0.35rem; background: #059669; border-color: #059669;">
                    <?= icon('check_circle', '', 15) ?>
                    <span>Xác Nhận Nhận Việc & Gửi Thông Báo</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
    window.CHAT_CONFIG = {
        myAccountId: <?= (int)$myId ?>,
        activePartnerId: <?= (int)($activePartner['account_id'] ?? 0) ?>,
        activeSeekerId: <?= (int)($activePartner['seeker_id'] ?? ($seekerId ?? 0)) ?>,
        selectedJobId: <?= (int)($selectedJobId ?? 0) ?>,
        selectedRequestId: <?= (int)($selectedRequestId ?? ($requestId ?? 0)) ?>,
        autoOpenMeetingModal: <?= !empty($openMeetingModal) ? 'true' : 'false' ?>,
        isAiActive: <?= !empty($isAiActive) ? 'true' : 'false' ?>,
        jobyAvatar: '<?= getJobyAvatar() ?>',
        routes: {
            sendAjax: '<?= url('/api/messages/send-ajax') ?>',
            getThread: '<?= url('/api/messages/thread') ?>',
            getConversations: '<?= url('/api/messages/conversations') ?>',
            suggestSlots: '<?= url('/api/ai/suggest-meeting-slots') ?>',
            scheduleMeeting: '<?= url('/employer/meetings/schedule-from-chat') ?>',
            acceptOnboarding: '<?= url('/employer/applications/accept-onboarding') ?>',
            aiChat: '<?= url('/api/ai/chat') ?>',
            aiChatHistory: '<?= url('/api/ai/chat/history') ?>',
            aiChatClear: '<?= url('/api/ai/chat/clear') ?>'
        }
    };
</script>

<?php 
$content = ob_get_clean(); 
require_once $layoutFile;
?>
