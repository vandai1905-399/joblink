<?php 
// public/modules/messages/chat_messenger.php - Giao diện Khung Chat Trao Đổi 2 Chiều Trực Tiếp
$extraCss = ['css/messages/messages.css'];
$extraJs = ['js/messages/messages.js'];
ob_start(); 

$currentRole = $user['role_code'] ?? '';
$myId = $user['account_id'] ?? 0;
?>

<div class="messenger-container" id="messengerContainer">
    <!-- ==================== CỘT 1: DANH SÁCH CUỘC TRÒ CHUYỆN ==================== -->
    <div class="messenger-sidebar">
        <!-- Sidebar Top Header -->
        <div class="sidebar-top-header">
            <h2 class="sidebar-heading-title">
                <span>💬</span> <span>Tin Nhắn Trao Đổi</span>
            </h2>
            <?php if (in_array($currentRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])): ?>
                <button type="button" class="btn-broadcast-sm" onclick="openBroadcastModal()" title="Phát thông báo hệ thống">
                    <?= icon('posts', '', 14) ?>
                </button>
            <?php endif; ?>
        </div>

        <!-- Search & Filter -->
        <div class="sidebar-search-box">
            <div class="search-input-wrapper">
                <span class="search-icon"><?= icon('search', '', 16) ?></span>
                <input type="text" id="contactSearchInput" placeholder="Tìm kiếm liên hệ, công ty..." autocomplete="off">
                <button type="button" id="clearSearchBtn" class="search-clear-btn" style="display:none;">&times;</button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="sidebar-filter-tabs">
            <button type="button" class="filter-tab active" data-filter="all">Tất cả</button>
            <button type="button" class="filter-tab" data-filter="unread">Chưa đọc</button>
            <button type="button" class="filter-tab" data-filter="recent">Gần đây</button>
        </div>

        <!-- Contacts List -->
        <div class="conversation-list" id="conversationList">
            <?php if (empty($conversations)): ?>
                <div class="conversation-empty-state">
                    <div class="empty-icon"><?= icon('chat', '', 32) ?></div>
                    <p>Chưa có cuộc trò chuyện nào</p>
                    <span class="empty-hint">Khi có người ứng tuyển hoặc liên hệ, danh sách sẽ xuất hiện tại đây.</span>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $c): 
                    $pId = (int)$c['partner_id'];
                    $isActive = ($pId === (int)$partnerId);
                    $pName = htmlspecialchars($c['partner_name'] ?? 'Thành viên');
                    $pAvatar = !empty($c['partner_avatar']) ? asset($c['partner_avatar']) : getSystemLogo();
                    $pRole = htmlspecialchars($c['partner_role_name'] ?? 'Thành viên');
                    $lastMsg = htmlspecialchars($c['last_message'] ?? 'Bắt đầu cuộc trò chuyện...');
                    $timeStr = !empty($c['last_message_time']) ? date('H:i - d/m', strtotime($c['last_message_time'])) : '';
                    $unread = (int)($c['unread_count'] ?? 0);
                ?>
                    <div class="conversation-item <?= $isActive ? 'active' : '' ?>" 
                         data-partner-id="<?= $pId ?>" 
                         data-partner-name="<?= strtolower($pName) ?>"
                         data-unread="<?= $unread ?>"
                         onclick="switchConversation(<?= $pId ?>)">
                        <div class="conv-avatar-box">
                            <img src="<?= $pAvatar ?>" alt="avatar" class="conv-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                            <span class="conv-online-dot"></span>
                        </div>
                        <div class="conv-meta">
                            <div class="conv-meta-header">
                                <strong class="conv-name" title="<?= $pName ?>"><?= $pName ?></strong>
                                <span class="conv-time"><?= $timeStr ?></span>
                            </div>
                            <div class="conv-meta-role">
                                <span class="conv-role-badge role-<?= strtolower($c['partner_role_code'] ?? 'user') ?>"><?= $pRole ?></span>
                            </div>
                            <div class="conv-meta-body">
                                <span class="conv-snippet <?= $unread > 0 ? 'unread-snippet' : '' ?>"><?= $lastMsg ?></span>
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
        <?php if (!$activePartner): ?>
            <!-- EMPTY CHAT STATE -->
            <div class="chat-placeholder">
                <div class="placeholder-content">
                    <div class="placeholder-icon-circle">
                        <?= icon('chat', '', 48) ?>
                    </div>
                    <h3>Chọn một người để bắt đầu trò chuyện</h3>
                    <p>Trao đổi thông tin công việc, giải đáp thắc mắc hoặc đề xuất lịch phỏng vấn nhanh chóng</p>
                </div>
            </div>
        <?php else: 
            $actId = (int)$activePartner['account_id'];
            $actName = htmlspecialchars($activePartner['display_name'] ?? $activePartner['username']);
            $actAvatar = !empty($activePartner['avatar_url']) ? asset($activePartner['avatar_url']) : getSystemLogo();
            $actRoleCode = $activePartner['role_code'] ?? '';
            $actRoleName = htmlspecialchars($activePartner['role_name'] ?? 'Thành viên');
            $isPartnerSeeker = ($actRoleCode === 'JOB_SEEKER');
            $isPartnerEmployer = in_array($actRoleCode, ['EMPLOYER_ADMIN', 'INTERMEDIARY']);
        ?>
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

                <!-- QUICK ACTIONS -->
                <div class="chat-header-actions">
                    <?php if ($isPartnerSeeker && !empty($activePartner['seeker_id'])): ?>
                        <a href="<?= url('/employer/candidates/' . $activePartner['seeker_id']) ?>" class="btn-chat-tool" title="Xem chi tiết hồ sơ ứng viên" target="_blank">
                            <?= icon('user', '', 15) ?> <span>Hồ sơ</span>
                        </a>
                        <?php if (in_array($currentRole, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])): ?>
                            <button type="button" class="btn-chat-tool btn-ai-tool" id="btnToggleAiSlots" title="AI đối chiếu lịch rảnh">
                                <span>✨</span> <span>AI Gợi ý lịch</span>
                            </button>
                        <?php endif; ?>
                    <?php elseif ($isPartnerEmployer && !empty($activePartner['employer_id'])): ?>
                        <a href="<?= url('/employers/' . $activePartner['employer_id']) ?>" class="btn-chat-tool" title="Xem trang doanh nghiệp" target="_blank">
                            <?= icon('building', '', 15) ?> <span>Doanh nghiệp</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AI SCHEDULE SUGGESTION BOX (COLLAPSIBLE) -->
            <?php if ($isPartnerSeeker && in_array($currentRole, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])): ?>
                <div class="ai-slots-accordion" id="aiSlotsAccordion" style="display:none;">
                    <div class="ai-slots-box">
                        <div class="ai-slots-header">
                            <strong>✨ Khung giờ rảnh AI gợi ý cho 2 bên:</strong>
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
                        $lastDate = '';
                        foreach ($thread as $msg): 
                            $isMine = ((int)$msg['sender_account_id'] === (int)$myId);
                            $msgDate = date('d/m/Y', strtotime($msg['sent_at']));
                            $msgTime = date('H:i', strtotime($msg['sent_at']));
                            $senderAvatar = !empty($msg['sender_avatar']) ? asset($msg['sender_avatar']) : getSystemLogo();
                        ?>
                            <?php if ($msgDate !== $lastDate): 
                                $lastDate = $msgDate;
                                $displayDate = ($msgDate === date('d/m/Y')) ? 'Hôm nay' : (($msgDate === date('d/m/Y', strtotime('-1 day'))) ? 'Hôm qua' : $msgDate);
                            ?>
                                <div class="chat-date-divider">
                                    <span><?= $displayDate ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="chat-message-row <?= $isMine ? 'outgoing-row' : 'incoming-row' ?>" data-msg-id="<?= $msg['message_id'] ?>">
                                <?php if (!$isMine): ?>
                                    <img src="<?= $senderAvatar ?>" alt="avatar" class="bubble-sender-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                                <?php endif; ?>
                                <div class="chat-bubble-wrap">
                                    <div class="chat-bubble <?= $isMine ? 'bubble-outgoing' : 'bubble-incoming' ?> <?= ($msg['message_type'] ?? '') === 'invitation' ? 'bubble-invitation' : '' ?>">
                                        <?php if (!empty($msg['message_title'])): ?>
                                            <div class="bubble-title"><?= htmlspecialchars($msg['message_title']) ?></div>
                                        <?php endif; ?>
                                        <div class="bubble-content"><?= nl2br(htmlspecialchars($msg['message_content'])) ?></div>
                                    </div>
                                    <div class="bubble-meta">
                                        <span class="bubble-time"><?= $msgTime ?></span>
                                        <?php if ($isMine): ?>
                                            <span class="bubble-check"><?= icon('check', '', 12) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AI QUICK REPLY CHIPS -->
            <div class="ai-quick-replies-bar" id="aiQuickRepliesBar">
                <span class="ai-chip-label">✨ Gợi ý nhanh:</span>
                <div class="ai-chips-scroll">
                    <?php if (in_array($currentRole, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])): ?>
                        <button type="button" class="ai-reply-chip" data-text="Chào bạn! Doanh nghiệp đã xem qua hồ sơ và rất ấn tượng với kinh nghiệm của bạn.">👋 Chào mừng ứng viên</button>
                        <button type="button" class="ai-reply-chip" data-text="Bạn có thể tham gia phỏng vấn trao đổi trực tiếp vào ngày mai được không ạ?">📅 Hẹn phỏng vấn</button>
                        <button type="button" class="ai-reply-chip" data-text="Mức thu nhập và chế độ đãi ngộ cho vị trí này đã được cập nhật chi tiết trong tin tuyển dụng nhé bạn.">💼 Trao đổi mức lương</button>
                        <button type="button" class="ai-reply-chip" data-text="Cảm ơn bạn đã phản hồi! Công ty sẽ gửi thông báo kết quả sớm nhất.">✨ Cảm ơn phản hồi</button>
                    <?php elseif ($currentRole === 'JOB_SEEKER'): ?>
                        <button type="button" class="ai-reply-chip" data-text="Dạ em chào anh/chị tuyển dụng ạ! Em rất quan tâm đến vị trí này.">👋 Chào nhà tuyển dụng</button>
                        <button type="button" class="ai-reply-chip" data-text="Dạ em sẵn sàng tham gia phỏng vấn theo lịch hẹn của công ty ạ.">✅ Đồng ý lịch hẹn</button>
                        <button type="button" class="ai-reply-chip" data-text="Cho em xin hỏi thêm về thời gian làm việc cụ thể của ca này với ạ?">⏰ Hỏi ca làm việc</button>
                        <button type="button" class="ai-reply-chip" data-text="Dạ em đã chuẩn bị đầy đủ hồ sơ và sẽ đến đúng giờ hẹn ạ.">📍 Xác nhận đến hẹn</button>
                    <?php else: ?>
                        <button type="button" class="ai-reply-chip" data-text="Chào bạn, Ban Quản Trị JobLink có thể hỗ trợ gì cho bạn?">👋 Chào người dùng</button>
                        <button type="button" class="ai-reply-chip" data-text="Yêu cầu của bạn đã được tiếp nhận và xử lý thành công.">✅ Xác nhận xử lý</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CHAT INPUT FOOTER -->
            <div class="chat-input-footer">
                <form id="chatMessageForm" class="chat-form-inline">
                    <input type="hidden" id="activeReceiverId" value="<?= $actId ?>">
                    <div class="chat-input-box">
                        <textarea id="chatInputText" 
                                  rows="1" 
                                  placeholder="Nhập tin nhắn... (Nhấn Enter để gửi, Shift+Enter để xuống dòng)" 
                                  autocomplete="off" 
                                  required></textarea>
                    </div>
                    <button type="submit" id="chatSendButton" class="btn-chat-send" title="Gửi tin nhắn (Enter)">
                        <?= icon('rocket', '', 18) ?>
                        <span>Gửi</span>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL GỬI THÔNG BÁO HỆ THỐNG (Dành riêng cho ADMIN) -->
<?php if (in_array($currentRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])): ?>
    <div id="broadcastModal" class="chat-modal-overlay" style="display:none;">
        <div class="chat-modal-box">
            <div class="chat-modal-header">
                <h3>📢 Phát Thông Báo Hệ Thống</h3>
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

<script>
    window.CHAT_CONFIG = {
        myAccountId: <?= (int)$myId ?>,
        activePartnerId: <?= (int)($activePartner['account_id'] ?? 0) ?>,
        activeSeekerId: <?= (int)($activePartner['seeker_id'] ?? 0) ?>,
        routes: {
            sendAjax: '<?= url('/api/messages/send-ajax') ?>',
            getThread: '<?= url('/api/messages/thread') ?>',
            getConversations: '<?= url('/api/messages/conversations') ?>',
            suggestSlots: '<?= url('/api/ai/suggest-meeting-slots') ?>'
        }
    };
</script>

<?php 
$content = ob_get_clean(); 
require_once $layoutFile;
?>
