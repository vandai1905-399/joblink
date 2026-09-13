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
            <h2 class="sidebar-heading-title" style="display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('chat', '', 18) ?> <span>Tin Nhắn Trao Đổi</span>
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
                    $isActive = ($pId === (int)($partnerId ?? ($activePartner['account_id'] ?? 0)));
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
                        <a href="<?= url('/employer/candidates/' . $activePartner['seeker_id']) ?>" class="btn-chat-tool" title="Xem chi tiết hồ sơ người lao động" target="_blank">
                            <?= icon('user', '', 15) ?> <span>Hồ sơ</span>
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
                                    <div class="chat-bubble <?= $isMine ? 'bubble-outgoing' : 'bubble-incoming' ?> <?= in_array(($msg['message_type'] ?? ''), ['invitation', 'meeting_invite']) ? 'bubble-invitation' : '' ?> <?= (($msg['message_type'] ?? '') === 'onboarding') ? 'bubble-onboarding' : '' ?>">
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
        routes: {
            sendAjax: '<?= url('/api/messages/send-ajax') ?>',
            getThread: '<?= url('/api/messages/thread') ?>',
            getConversations: '<?= url('/api/messages/conversations') ?>',
            suggestSlots: '<?= url('/api/ai/suggest-meeting-slots') ?>',
            scheduleMeeting: '<?= url('/employer/meetings/schedule-from-chat') ?>',
            acceptOnboarding: '<?= url('/employer/applications/accept-onboarding') ?>'
        }
    };
</script>

<?php 
$content = ob_get_clean(); 
require_once $layoutFile;
?>
