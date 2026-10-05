/**
 * public/js/messages/messages.js
 * Engine xử lý Khung Chat Trao Đổi 2 Chiều Trực Tiếp (JobLink Messenger)
 */

document.addEventListener('DOMContentLoaded', function () {
    const config = window.CHAT_CONFIG || {};
    const chatStream = document.getElementById('chatStreamContainer');
    const messagesStream = document.getElementById('chatMessagesStream');
    const chatInput = document.getElementById('chatInputText');
    const chatForm = document.getElementById('chatMessageForm');
    const contactSearch = document.getElementById('contactSearchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const filterTabs = document.querySelectorAll('.filter-tab');
    const btnToggleAiSlots = document.getElementById('btnToggleAiSlots');
    const aiSlotsAccordion = document.getElementById('aiSlotsAccordion');
    const aiSlotsContainer = document.getElementById('aiSlotsContainer');
    const aiSlotLoading = document.getElementById('aiSlotLoading');

    let currentPartnerId = config.activePartnerId || 0;
    let myAccountId = config.myAccountId || 0;
    let isPolling = true;

    // 1. Tự động cuộn xuống cuối khung chat
    function scrollToBottom(smooth = true) {
        if (chatStream) {
            chatStream.scrollTo({
                top: chatStream.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }
    }
    scrollToBottom(false);

    // Ẩn triệt để Joby AI lơ lửng ở góc khi vào trang tin nhắn
    function hideFloatingJobyLauncher() {
        ['joblink-ai-launcher', 'joblink-ai-chatbox', 'joblink-ai-bubble'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.style.setProperty('display', 'none', 'important');
                el.style.setProperty('visibility', 'hidden', 'important');
                el.style.setProperty('opacity', '0', 'important');
                el.style.setProperty('pointer-events', 'none', 'important');
            }
        });
    }
    hideFloatingJobyLauncher();
    setInterval(hideFloatingJobyLauncher, 800);

    // 2. Tự động co giãn textarea khi gõ & Phím Enter / Shift+Enter
    if (chatInput) {
        chatInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Bấm Enter: Gửi ngay lập tức. Muốn xuống dòng: Shift + Enter
        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                if (e.shiftKey) {
                    // Cho phép xuống dòng bình thường
                    return;
                }
                e.preventDefault();
                sendChatMessage();
            }
        });
    }

    // 3. Hàm gửi tin nhắn qua AJAX (Không tải lại trang)
    function sendChatMessage() {
        if (!chatInput) return;
        let content = (chatInput.value || '').trim();
        const receiverId = document.getElementById('activeReceiverId')?.value || currentPartnerId;

        if (!content || !receiverId) return;

        // Nếu đang trong trạng thái trả lời tin nhắn, kèm trích dẫn
        let quotedPrefix = '';
        if (quotedReplyText) {
            quotedPrefix = `[Trả lời ${quotedReplyTo || 'tin nhắn'}: "${quotedReplyText.substring(0, 60)}${quotedReplyText.length > 60 ? '...' : ''}"]\n`;
            cancelReply();
        }

        const fullContent = quotedPrefix ? quotedPrefix + content : content;
        const sendBtn = document.getElementById('chatSendButton');
        if (sendBtn) sendBtn.disabled = true;

        const formData = new FormData();
        formData.append('receiver_account_id', receiverId);
        formData.append('message_content', fullContent);

        fetch(config.routes.sendAjax, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (sendBtn) sendBtn.disabled = false;
            if (data.success && data.data) {
                // Reset input
                chatInput.value = '';
                chatInput.style.height = 'auto';

                // Thêm bong bóng chat tức thì
                appendOutgoingMessage(data.data.message_content, data.data.time_formatted || 'Vừa xong', data.data.message_id, null, '', data.data.sent_at || data.data.created_at);

                // Cập nhật sidebar snippet
                updateSidebarLastMessage(receiverId, content, 'Vừa xong');
                scrollToBottom(true);
            } else {
                alert(data.message || 'Không thể gửi tin nhắn.');
            }
        })
        .catch(err => {
            if (sendBtn) sendBtn.disabled = false;
            console.error('[Chat Send Error]', err);
        });
    }

    if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            sendChatMessage();
        });
    }

    // =========================================================================
    // SMART TIME HELPERS (TOOLTIP & DIVIDER)
    // =========================================================================
    const WEEKDAY_MAP = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

    function parseMsgDate(sentAt) {
        if (!sentAt) return new Date();
        if (sentAt instanceof Date) return sentAt;
        if (typeof sentAt === 'string') {
            const parts = sentAt.trim().split(/[\sT]/);
            if (parts.length >= 2) {
                const dateParts = parts[0].split('-');
                const timeParts = parts[1].split(':');
                if (dateParts.length === 3 && timeParts.length >= 2) {
                    return new Date(
                        parseInt(dateParts[0], 10),
                        parseInt(dateParts[1], 10) - 1,
                        parseInt(dateParts[2], 10),
                        parseInt(timeParts[0], 10),
                        parseInt(timeParts[1], 10),
                        parseInt(timeParts[2] || 0, 10)
                    );
                }
            }
            const d = new Date(sentAt);
            if (!isNaN(d.getTime())) return d;
        }
        return new Date();
    }

    function getSmartTimeTooltip(sentAt) {
        const d = parseMsgDate(sentAt);
        const now = new Date();
        const pad = (n) => (n < 10 ? '0' + n : '' + n);
        const timeStr = `${pad(d.getHours())}:${pad(d.getMinutes())}`;
        const weekday = WEEKDAY_MAP[d.getDay()];

        const msgYear = d.getFullYear();
        const nowYear = now.getFullYear();
        const msgMonth = d.getMonth() + 1;
        const nowMonth = now.getMonth() + 1;
        const msgDay = d.getDate();
        const nowDay = now.getDate();

        const diffDays = Math.floor((now.getTime() - d.getTime()) / (1000 * 60 * 60 * 24));

        // 1. Trong ngày hôm đó thì chỉ cần hiện giờ
        if (msgYear === nowYear && msgMonth === nowMonth && msgDay === nowDay) {
            return timeStr;
        }
        // 2. Trong tuần thì chỉ cần hiển thị giờ và thứ
        if (diffDays >= 0 && diffDays < 7) {
            return `${timeStr} ${weekday}`;
        }
        // 3. Trong tháng đó thì chỉ cần hiện giờ ngày
        if (msgYear === nowYear && msgMonth === nowMonth) {
            return `${timeStr} ngày ${msgDay}`;
        }
        // 4. Trong năm đó thì chỉ cần hiển thị giờ ngày tháng
        if (msgYear === nowYear) {
            return `${timeStr} ${msgDay} Tháng ${msgMonth}`;
        }
        // 5. Khác năm
        return `${timeStr} ${msgDay} Tháng ${msgMonth}, ${msgYear}`;
    }

    function getSmartTimeDivider(sentAt) {
        const d = parseMsgDate(sentAt);
        const now = new Date();
        const pad = (n) => (n < 10 ? '0' + n : '' + n);
        const timeStr = `${pad(d.getHours())}:${pad(d.getMinutes())}`;
        const weekday = WEEKDAY_MAP[d.getDay()];

        const msgYear = d.getFullYear();
        const nowYear = now.getFullYear();
        const msgMonth = d.getMonth() + 1;
        const nowMonth = now.getMonth() + 1;
        const msgDay = d.getDate();

        const diffDays = Math.floor((now.getTime() - d.getTime()) / (1000 * 60 * 60 * 24));

        if (diffDays >= 0 && diffDays < 7) {
            return `${timeStr} ${weekday}`;
        }
        if (msgYear === nowYear && msgMonth === nowMonth) {
            return `${timeStr} ngày ${msgDay}`;
        }
        if (msgYear === nowYear) {
            return `${timeStr} ${msgDay} Tháng ${msgMonth}`;
        }
        return `${timeStr} ${msgDay} Tháng ${msgMonth}, ${msgYear}`;
    }

    function maybeAppendTimeDivider(sentAt) {
        if (!messagesStream) return;
        const msgRows = messagesStream.querySelectorAll('.chat-message-row');
        const curDate = parseMsgDate(sentAt);
        let shouldAdd = false;

        if (msgRows.length === 0) {
            shouldAdd = true;
        } else {
            const lastRow = msgRows[msgRows.length - 1];
            const lastSentAt = lastRow.getAttribute('data-sent-at');
            if (lastSentAt) {
                const lastDate = parseMsgDate(lastSentAt);
                const diffSec = Math.floor((curDate.getTime() - lastDate.getTime()) / 1000);
                if (diffSec >= 900) { // 15 phút
                    shouldAdd = true;
                }
            } else {
                shouldAdd = true;
            }
        }

        if (shouldAdd) {
            const div = document.createElement('div');
            div.className = 'chat-time-divider';
            div.innerHTML = `<span>${getSmartTimeDivider(curDate)}</span>`;
            messagesStream.appendChild(div);
        }
    }

    // Hàm tạo bubble tin nhắn gửi đi (Của tôi) - Messenger Style
    function appendOutgoingMessage(content, timeStr, msgId = null, title = null, msgType = '', sentAt = null) {
        if (!messagesStream) return;

        const starter = messagesStream.querySelector('.chat-thread-starter');
        if (starter) starter.remove();

        const dateObj = sentAt ? parseMsgDate(sentAt) : new Date();
        const pad = (n) => (n < 10 ? '0' + n : '' + n);
        const isoDateStr = sentAt || `${dateObj.getFullYear()}-${pad(dateObj.getMonth() + 1)}-${pad(dateObj.getDate())} ${pad(dateObj.getHours())}:${pad(dateObj.getMinutes())}:${pad(dateObj.getSeconds())}`;
        const smartTooltip = getSmartTimeTooltip(dateObj);

        maybeAppendTimeDivider(dateObj);

        const row = document.createElement('div');
        row.className = 'chat-message-row outgoing-row';
        if (msgId) row.setAttribute('data-msg-id', msgId);
        row.setAttribute('data-is-mine', '1');
        row.setAttribute('data-sender-name', 'Bạn');
        row.setAttribute('data-sent-at', isoDateStr);
        row.setAttribute('oncontextmenu', 'handleMsgContextMenu(event, this)');

        const escaped = escapeHtml(content).replace(/\n/g, '<br>');
        const isInvite = (msgType === 'invitation' || msgType === 'meeting_invite');
        const isOnboarding = (msgType === 'onboarding');
        const safeMsgId = msgId || ('temp-' + Date.now());

        row.innerHTML = `
            <!-- Dark pill time tooltip: hiện khi để chuột lâu hơn (outgoing) -->
            <div class="msg-time-tooltip">${smartTooltip}</div>

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

            <div class="chat-bubble-wrap">
                <div class="chat-bubble bubble-outgoing ${isInvite ? 'bubble-invitation' : ''} ${isOnboarding ? 'bubble-onboarding' : ''}">
                    ${title ? `<div class="bubble-title" style="font-weight: 800; font-size: 0.84rem; margin-bottom: 0.35rem; padding-bottom: 0.25rem; border-bottom: 1px solid rgba(255,255,255,0.2);">${escapeHtml(title)}</div>` : ''}
                    <div class="bubble-content">${escaped}</div>
                </div>

                <!-- Floating reaction bar -->
                <div class="msg-reaction-bar" id="reactionBar-${safeMsgId}" style="display:none;">
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '👍')">👍</button>
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '❤️')">❤️</button>
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '😂')">😂</button>
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '😮')">😮</button>
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '😢')">😢</button>
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '😡')">😡</button>
                </div>
            </div>
        `;
        messagesStream.appendChild(row);
    }

    // Hàm tạo bubble tin nhắn nhận về (Đối phương) - Messenger Style
    function appendIncomingMessage(content, timeStr, avatarUrl, msgId = null, title = null, msgType = '', senderName = 'Đối phương', sentAt = null) {
        if (!messagesStream) return;

        const starter = messagesStream.querySelector('.chat-thread-starter');
        if (starter) starter.remove();

        const dateObj = sentAt ? parseMsgDate(sentAt) : new Date();
        const pad = (n) => (n < 10 ? '0' + n : '' + n);
        const isoDateStr = sentAt || `${dateObj.getFullYear()}-${pad(dateObj.getMonth() + 1)}-${pad(dateObj.getDate())} ${pad(dateObj.getHours())}:${pad(dateObj.getMinutes())}:${pad(dateObj.getSeconds())}`;
        const smartTooltip = getSmartTimeTooltip(dateObj);

        maybeAppendTimeDivider(dateObj);

        const row = document.createElement('div');
        row.className = 'chat-message-row incoming-row';
        if (msgId) row.setAttribute('data-msg-id', msgId);
        row.setAttribute('data-is-mine', '0');
        row.setAttribute('data-sender-name', senderName);
        row.setAttribute('data-sent-at', isoDateStr);
        row.setAttribute('oncontextmenu', 'handleMsgContextMenu(event, this)');

        const escaped = escapeHtml(content).replace(/\n/g, '<br>');
        const isInvite = (msgType === 'invitation' || msgType === 'meeting_invite');
        const isOnboarding = (msgType === 'onboarding');
        const safeMsgId = msgId || ('temp-' + Date.now());
        let titleHtml = '';
        if (title) {
            titleHtml = `<div class="bubble-title">${escapeHtml(title)}</div>`;
        }

        row.innerHTML = `
            <img src="${avatarUrl || '/images/logo.png'}" alt="avatar" class="bubble-sender-avatar" onerror="this.src='/images/logo.png'">
            <div class="chat-bubble-wrap">
                <div class="chat-bubble bubble-incoming ${isInvite ? 'bubble-invitation' : ''} ${isOnboarding ? 'bubble-onboarding' : ''}">
                    ${titleHtml}
                    <div class="bubble-content">${escaped}</div>
                </div>

                <!-- Floating reaction bar -->
                <div class="msg-reaction-bar" id="reactionBar-${safeMsgId}" style="display:none;">
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '👍')">👍</button>
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '❤️')">❤️</button>
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '😂')">😂</button>
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '😮')">😮</button>
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '😢')">😢</button>
                    <button type="button" class="react-emoji-btn" onclick="addMsgReaction('${safeMsgId}', '😡')">😡</button>
                </div>
            </div>

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
            <div class="msg-time-tooltip">${smartTooltip}</div>
        `;
        messagesStream.appendChild(row);
    }

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"']/g, function (m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    // 4. Cập nhật xem trước tin nhắn trên Sidebar
    function updateSidebarLastMessage(partnerId, content, timeStr) {
        const item = document.querySelector(`.conversation-item[data-partner-id="${partnerId}"]`);
        if (item) {
            const snippet = item.querySelector('.conv-snippet');
            const time = item.querySelector('.conv-time');
            if (snippet) snippet.textContent = content;
            if (time && timeStr) time.textContent = timeStr;

            // Đưa cuộc trò chuyện này lên đầu danh sách
            const parent = item.parentElement;
            if (parent && parent.firstChild !== item) {
                parent.insertBefore(item, parent.firstChild);
            }
        }
    }

    // 5. Chuyển đổi cuộc trò chuyện (Switch Conversation)
    window.switchConversation = function (partnerId) {
        if (partnerId === 'ai_joby' || partnerId === 0) {
            openAiJobyChat();
            return;
        }

        if (parseInt(partnerId) === parseInt(currentPartnerId)) {
            // Nếu đang ở màn Joby AI mà bấm lại vào đối tác này, chuyển lại giao diện đối tác
            if (aiJobyView && aiJobyView.style.display !== 'none' && userChatView) {
                aiJobyView.style.display = 'none';
                userChatView.style.display = 'flex';
                document.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
                const curItem = document.getElementById('conv-item-' + partnerId);
                if (curItem) curItem.classList.add('active');
                scrollToBottom(false);
                return;
            }
            return;
        }

        // Cập nhật class active trên sidebar
        document.querySelectorAll('.conversation-item').forEach(el => {
            el.classList.remove('active');
            if (parseInt(el.getAttribute('data-partner-id')) === parseInt(partnerId)) {
                el.classList.add('active');
                // Xóa unread badge
                const badge = el.querySelector('.conv-unread-badge');
                if (badge) badge.remove();
                const snippet = el.querySelector('.conv-snippet');
                if (snippet) snippet.classList.remove('unread-snippet');
            }
        });

        // Chuyển trang hoặc cập nhật URL để đồng bộ
        const url = new URL(window.location);
        url.searchParams.set('partner_id', partnerId);
        window.location.href = url.toString();
    };

    // 6. Polling thời gian thực kiểm tra tin nhắn mới
    function pollNewMessages() {
        if (!isPolling || !currentPartnerId || !config.routes.getThread) return;

        fetch(`${config.routes.getThread}?partner_id=${currentPartnerId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.messages && data.messages.length > 0) {
                let hasNew = false;
                data.messages.forEach(msg => {
                    const msgId = msg.message_id;
                    const existing = document.querySelector(`.chat-message-row[data-msg-id="${msgId}"]`);
                    if (!existing) {
                        const isMine = (parseInt(msg.sender_account_id) === myAccountId);
                        const timeStr = msg.sent_at ? msg.sent_at.substring(11, 16) : 'Vừa xong';
                        if (isMine) {
                            appendOutgoingMessage(msg.message_content, timeStr, msgId, msg.message_title, msg.message_type, msg.sent_at);
                        } else {
                            appendIncomingMessage(msg.message_content, timeStr, msg.sender_avatar, msgId, msg.message_title, msg.message_type, 'Đối phương', msg.sent_at);
                        }
                        hasNew = true;
                    }
                });

                if (hasNew) {
                    scrollToBottom(true);
                }
            }
        })
        .catch(err => {
            // im lặng khi lỗi mạng nền
        });
    }

    // Bật Polling mỗi 3.5 giây
    if (currentPartnerId > 0) {
        setInterval(pollNewMessages, 3500);
    }

    // 7. Gợi ý trả lời nhanh (AI Quick Reply Chips)
    document.querySelectorAll('.ai-reply-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            const text = this.getAttribute('data-text');
            if (chatInput && text) {
                chatInput.value = text;
                chatInput.focus();
                chatInput.style.height = 'auto';
                chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
            }
        });
    });

    // 8. Đối chiếu khung giờ rảnh AI (AI Slot Suggester)
    if (btnToggleAiSlots && aiSlotsAccordion) {
        btnToggleAiSlots.addEventListener('click', function () {
            if (aiSlotsAccordion.style.display === 'none') {
                aiSlotsAccordion.style.display = 'block';
                loadAiSlots();
            } else {
                aiSlotsAccordion.style.display = 'none';
            }
        });
    }

    function loadAiSlots() {
        if (!config.activeSeekerId || !config.routes.suggestSlots) return;

        if (aiSlotLoading) aiSlotLoading.style.display = 'inline';
        if (aiSlotsContainer) aiSlotsContainer.innerHTML = '<span style="font-size:0.8rem; color:#64748b;">Đang quét lịch...</span>';

        fetch(`${config.routes.suggestSlots}?seeker_id=${config.activeSeekerId}`)
        .then(res => res.json())
        .then(data => {
            if (aiSlotLoading) aiSlotLoading.style.display = 'none';
            if (aiSlotsContainer) {
                aiSlotsContainer.innerHTML = '';
                if (data.slots && data.slots.length > 0) {
                    data.slots.forEach(slot => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'ai-slot-btn';
                        btn.innerHTML = `<strong>${escapeHtml(slot.label)}</strong> <span style="color:#059669; font-size:0.72rem;">(${escapeHtml(slot.ai_reason || 'Rảnh cả 2 bên')})</span>`;
                        btn.addEventListener('click', function () {
                            if (chatInput) {
                                chatInput.value = `Chào bạn,\n\nCông ty trân trọng mời bạn tham gia phỏng vấn vào khung giờ sau:\n- Thời gian: ${slot.label}\n- Địa điểm: Văn phòng Công ty\n\nBạn vui lòng phản hồi xem khung giờ này có thuận tiện với bạn không nhé!`;
                                chatInput.focus();
                                chatInput.style.height = 'auto';
                                chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
                            }
                        });
                        aiSlotsContainer.appendChild(btn);
                    });
                } else {
                    aiSlotsContainer.innerHTML = '<span style="font-size:0.8rem; color:#64748b;">Chưa tìm thấy khung giờ rảnh đối chiếu. Bạn có thể tự thỏa thuận thời gian với người lao động.</span>';
                }
            }
        })
        .catch(err => {
            if (aiSlotLoading) aiSlotLoading.style.display = 'none';
            if (aiSlotsContainer) aiSlotsContainer.innerHTML = '<span style="font-size:0.8rem; color:#ef4444;">Không thể tải gợi ý lúc này.</span>';
        });
    }

    // 9. Lọc & Tìm kiếm liên hệ Messenger đa tiêu chí (Tất cả, Chưa đọc, Nhà tuyển dụng, Người lao động, JobLink)
    let currentFilterType = 'all';

    function applyConversationFilters() {
        const query = contactSearch ? contactSearch.value.trim().toLowerCase() : '';
        if (clearSearchBtn) {
            clearSearchBtn.style.display = query ? 'block' : 'none';
        }

        let visibleCount = 0;
        const convItems = document.querySelectorAll('.conversation-item');

        convItems.forEach(item => {
            const isHidden = item.classList.contains('is-hidden');
            const unread = parseInt(item.getAttribute('data-unread') || '0', 10);
            const category = item.getAttribute('data-category') || 'other';
            const name = (item.getAttribute('data-partner-name') || '').toLowerCase();
            const displayName = (item.getAttribute('data-partner-display') || '').toLowerCase();
            const snippet = (item.querySelector('.conv-snippet')?.textContent || '').toLowerCase();

            // 1. Kiểm tra tiêu chí lọc theo tab
            let matchesTab = false;
            if (currentFilterType === 'all') {
                matchesTab = true;
            } else if (currentFilterType === 'unread') {
                matchesTab = (unread > 0);
            } else if (currentFilterType === 'employer') {
                matchesTab = (category === 'employer');
            } else if (currentFilterType === 'seeker') {
                matchesTab = (category === 'seeker');
            } else if (currentFilterType === 'joblink') {
                matchesTab = (category === 'joblink');
            } else {
                matchesTab = true;
            }

            // 2. Kiểm tra từ khóa tìm kiếm (nếu có)
            let matchesSearch = true;
            if (query) {
                matchesSearch = name.includes(query) || displayName.includes(query) || snippet.includes(query);
            }

            // 3. Ẩn/hiện cuộc trò chuyện
            if (!isHidden && matchesTab && matchesSearch) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Xử lý thông báo khi không có kết quả
        const emptyFilterNotice = document.getElementById('filterEmptyNotice');
        const emptyConvState = document.getElementById('emptyConvStateNotice');
        if (emptyFilterNotice) {
            if (visibleCount === 0) {
                emptyFilterNotice.style.display = 'block';
                if (emptyConvState) emptyConvState.style.display = 'none';
            } else {
                emptyFilterNotice.style.display = 'none';
            }
        }
    }

    if (contactSearch) {
        contactSearch.addEventListener('input', applyConversationFilters);
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function () {
                contactSearch.value = '';
                applyConversationFilters();
                contactSearch.focus();
            });
        }
    }

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilterType = this.getAttribute('data-filter') || 'all';
            applyConversationFilters();
        });
    });

    // Sidebar Header Actions: Menu tùy chọn & Soạn tin nhắn mới
    const sidebarMenuBtn = document.getElementById('sidebarMenuBtn');
    const sidebarOptionsMenu = document.getElementById('sidebarOptionsMenu');
    if (sidebarMenuBtn && sidebarOptionsMenu) {
        sidebarMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = sidebarOptionsMenu.style.display === 'block';
            sidebarOptionsMenu.style.display = isOpen ? 'none' : 'block';
        });
        document.addEventListener('click', function (e) {
            if (!sidebarOptionsMenu.contains(e.target) && e.target !== sidebarMenuBtn) {
                sidebarOptionsMenu.style.display = 'none';
            }
        });
    }

    window.markAllConversationsRead = function () {
        document.querySelectorAll('.conv-unread-badge').forEach(el => el.remove());
        document.querySelectorAll('.unread-snippet').forEach(el => el.classList.remove('unread-snippet'));
        document.querySelectorAll('.conversation-item').forEach(el => el.setAttribute('data-unread', '0'));
        if (sidebarOptionsMenu) sidebarOptionsMenu.style.display = 'none';
        if (window.Toast) Toast.success('Đã đánh dấu tất cả đoạn chat là đã đọc!');
        applyConversationFilters();
    };

    window.focusSearchOrNew = function () {
        if (contactSearch) {
            contactSearch.focus();
            contactSearch.select();
        }
    };

    // Nút cuộn xuống tin nhắn mới nhất nổi giữa đáy chat (Scroll to bottom)
    const chatScrollBottomBtn = document.getElementById('chatScrollBottomBtn');
    if (chatStream && chatScrollBottomBtn) {
        chatStream.addEventListener('scroll', function () {
            const distanceFromBottom = chatStream.scrollHeight - chatStream.scrollTop - chatStream.clientHeight;
            if (distanceFromBottom > 150) {
                chatScrollBottomBtn.style.display = 'flex';
            } else {
                chatScrollBottomBtn.style.display = 'none';
            }
        });
    }

    window.scrollChatToBottom = function (smooth = true) {
        scrollToBottom(smooth);
        if (chatScrollBottomBtn) chatScrollBottomBtn.style.display = 'none';
    };

    // Các tính năng tương tác Messenger Bar: Ghi âm, Tệp, Emoji, Phím gửi
    window.triggerVoiceNote = function () {
        if (window.Toast) Toast.info('Tính năng ghi âm giọng nói đang sẵn sàng kết nối.');
    };

    window.handleChatFileUpload = function (input) {
        if (!input.files || input.files.length === 0) return;
        const file = input.files[0];
        if (chatInput) {
            chatInput.value = (chatInput.value ? chatInput.value + ' ' : '') + `[Đính kèm: ${file.name}]`;
            chatInput.focus();
            chatInput.dispatchEvent(new Event('input'));
        }
        if (window.Toast) Toast.info(`Đã đính kèm tệp: ${file.name}`);
        input.value = '';
    };

    window.insertQuickEmoji = function (emoji) {
        if (chatInput) {
            chatInput.value = (chatInput.value ? chatInput.value + ' ' : '') + emoji;
            chatInput.focus();
            chatInput.dispatchEvent(new Event('input'));
        }
    };

    window.toggleEmojiPicker = function (btn) {
        const emojis = ['👍', '❤️', '😊', '🎉', '👋', '💼', '⭐', '🔥'];
        const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)];
        insertQuickEmoji(randomEmoji);
    };

    // =========================================================================
    // 10. DIALOG THIẾT LẬP LỊCH HẸN PHỎNG VẤN TRỰC TIẾP TỪ CHAT (EMPLOYER)
    // =========================================================================
    const meetingModal = document.getElementById('chatMeetingModal');
    const btnOpenMeetingModal = document.getElementById('btnOpenMeetingModal');
    const closeMeetingModalBtn = document.getElementById('closeMeetingModalBtn');
    const cancelMeetingModalBtn = document.getElementById('cancelMeetingModalBtn');
    const meetingForm = document.getElementById('chatMeetingForm');
    const meetingJobSelect = document.getElementById('chatMeetingJobSelect');
    const meetingLocation = document.getElementById('chatMeetingLocation');
    const meetingFormat = document.getElementById('chatMeetingFormat');
    const aiChipsBox = document.getElementById('chatAiSlotsChips');
    const chatMeetingAiSlotLoading = document.getElementById('chatAiSlotLoading');

    function openMeetingDialog() {
        if (!meetingModal) return;
        meetingModal.style.display = 'flex';
        loadChatMeetingAiSlots();
    }

    function closeMeetingDialog() {
        if (!meetingModal) return;
        meetingModal.style.display = 'none';
    }

    if (btnOpenMeetingModal) {
        btnOpenMeetingModal.addEventListener('click', openMeetingDialog);
    }
    if (closeMeetingModalBtn) {
        closeMeetingModalBtn.addEventListener('click', closeMeetingDialog);
    }
    if (cancelMeetingModalBtn) {
        cancelMeetingModalBtn.addEventListener('click', closeMeetingDialog);
    }
    if (meetingModal) {
        meetingModal.addEventListener('click', function (e) {
            if (e.target === meetingModal) closeMeetingDialog();
        });
    }

    // Khi chọn tin tuyển dụng khác, tự động điền địa chỉ làm việc
    if (meetingJobSelect && meetingLocation) {
        meetingJobSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && meetingFormat && meetingFormat.value === 'offline') {
                const workplace = opt.getAttribute('data-workplace');
                if (workplace) meetingLocation.value = workplace;
            }
            loadChatMeetingAiSlots();
        });
    }

    // Khi đổi hình thức online/offline
    if (meetingFormat && meetingLocation) {
        meetingFormat.addEventListener('change', function () {
            if (this.value === 'online') {
                if (!meetingLocation.value.startsWith('http')) {
                    meetingLocation.value = 'https://meet.google.com/new';
                }
            } else {
                if (meetingJobSelect) {
                    const opt = meetingJobSelect.options[meetingJobSelect.selectedIndex];
                    meetingLocation.value = opt ? (opt.getAttribute('data-workplace') || 'Văn phòng Công ty') : 'Văn phòng Công ty';
                }
            }
        });
    }

    // Tải danh sách khung giờ AI gợi ý vào Dialog (Thanh ngang timeline)
    function loadChatMeetingAiSlots() {
        if (!aiChipsBox || !config.activeSeekerId || !config.routes.suggestSlots) return;

        const timelineWrap = document.getElementById('chatTimelineWrap');
        const timelineNodes = document.getElementById('chatTimelineNodes');
        const bgBar = document.getElementById('chatTimelineBgBar');
        const activeBar = document.getElementById('chatTimelineActiveBar');
        const noticeText = document.getElementById('chatActiveSlotText');

        if (chatMeetingAiSlotLoading) chatMeetingAiSlotLoading.style.display = 'inline';
        if (timelineWrap) timelineWrap.style.display = 'none';
        aiChipsBox.style.display = 'block';
        aiChipsBox.innerHTML = '<span style="font-size:0.78rem; color:#94a3b8;">Đang quét lịch rảnh tối ưu...</span>';

        const reqId = document.getElementById('chatMeetingRequestId')?.value || config.selectedRequestId || 0;
        fetch(`${config.routes.suggestSlots}?seeker_id=${config.activeSeekerId}&request_id=${reqId}`)
            .then(res => res.json())
            .then(data => {
                if (chatMeetingAiSlotLoading) chatMeetingAiSlotLoading.style.display = 'none';

                if (data.slots && data.slots.length > 0 && timelineWrap && timelineNodes) {
                    aiChipsBox.style.display = 'none';
                    timelineWrap.style.display = 'block';
                    timelineNodes.innerHTML = '';

                    const total = data.slots.length;
                    const halfCol = (100 / total) / 2;
                    const trackWidth = 100 - (halfCol * 2);

                    if (bgBar) {
                        bgBar.style.left = halfCol + '%';
                        bgBar.style.width = trackWidth + '%';
                    }
                    if (activeBar) {
                        activeBar.style.left = halfCol + '%';
                    }

                    let activeIdx = 0;

                    function selectChatMilestone(idx) {
                        activeIdx = idx;
                        const curSlot = data.slots[idx];

                        if (activeBar) {
                            const w = total > 1 ? (idx / (total - 1)) * trackWidth : 0;
                            activeBar.style.width = w + '%';
                        }

                        if (noticeText) {
                            noticeText.textContent = `Mốc ${idx + 1}: ${curSlot.label} (${curSlot.ai_reason || 'Rảnh'})`;
                        }

                        // Tự động điền form
                        const dateInput = document.getElementById('chatMeetingDate');
                        const startInput = document.getElementById('chatMeetingStartTime');
                        const endInput = document.getElementById('chatMeetingEndTime');
                        const rangeInput = document.getElementById('chatInterviewRange');

                        if (dateInput && curSlot.date) dateInput.value = curSlot.date;
                        if (startInput && curSlot.start_time) startInput.value = curSlot.start_time;
                        if (endInput && curSlot.end_time) endInput.value = curSlot.end_time;
                        if (rangeInput && curSlot.date) {
                            rangeInput.value = `Khoảng ngày ${curSlot.date} (Theo khung giờ AI gợi ý)`;
                        }

                        // Cập nhật styles cho các node
                        timelineNodes.querySelectorAll('.chat-milestone-item').forEach((item, i) => {
                            const isSel = (i === idx);
                            const circle = item.querySelector('.chat-milestone-circle');
                            const timeLabel = item.querySelector('.chat-milestone-time');
                            if (isSel) {
                                circle.style.background = '#0d9488';
                                circle.style.borderColor = '#0d9488';
                                circle.style.color = '#ffffff';
                                circle.style.boxShadow = '0 0 0 3px rgba(13, 148, 136, 0.25)';
                                timeLabel.style.color = '#0d9488';
                                timeLabel.style.fontWeight = '800';
                            } else {
                                circle.style.background = '#ffffff';
                                circle.style.borderColor = '#cbd5e1';
                                circle.style.color = '#475569';
                                circle.style.boxShadow = 'none';
                                timeLabel.style.color = '#334155';
                                timeLabel.style.fontWeight = '600';
                            }
                        });
                    }

                    data.slots.forEach((slot, index) => {
                        const item = document.createElement('div');
                        item.className = 'chat-milestone-item';
                        item.style.cssText = 'flex: 1; display: flex; flex-direction: column; align-items: center; cursor: pointer; text-align: center; user-select: none; padding: 0 2px;';

                        let dayConcise = slot.day_title || '';
                        if (dayConcise.includes('Ngày mai')) {
                            dayConcise = slot.session + ' mai';
                        } else {
                            const m = dayConcise.match(/^(Thứ\s+[^\s(]+)/i);
                            dayConcise = slot.session + (m ? ' · ' + m[1] : '');
                        }

                        item.innerHTML = `
                            <div class="chat-milestone-time" style="font-size: 0.76rem; font-weight: 600; color: #334155; margin-bottom: 4px; white-space: nowrap;">
                                ${slot.start_time} - ${slot.end_time}
                            </div>
                            <div class="chat-milestone-circle" style="width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; border: 2px solid #cbd5e1; background: #ffffff; color: #475569; z-index: 4; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); margin-bottom: 4px;">
                                ${index + 1}
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b; line-height: 1.1; white-space: nowrap;">
                                ${dayConcise}
                            </div>
                        `;

                        item.addEventListener('click', () => selectChatMilestone(index));
                        timelineNodes.appendChild(item);
                    });

                    // Chọn mốc 1 mặc định
                    selectChatMilestone(0);
                } else if (data.slots && data.slots.length > 0) {
                    aiChipsBox.innerHTML = '';
                    data.slots.forEach(slot => {
                        const chip = document.createElement('button');
                        chip.type = 'button';
                        chip.style.cssText = 'background: #ffffff; border: 1px solid #99f6e4; color: #0f766e; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.78rem; cursor: pointer; text-align: left; transition: all 0.2s;';
                        chip.innerHTML = `<strong>${escapeHtml(slot.label)}</strong> <span style="font-size:0.7rem; color:#059669;">(${escapeHtml(slot.ai_reason || 'Rảnh')})</span>`;

                        chip.addEventListener('click', function () {
                            aiChipsBox.querySelectorAll('button').forEach(b => {
                                b.style.background = '#ffffff';
                                b.style.borderColor = '#99f6e4';
                            });
                            chip.style.background = '#ccfbf1';
                            chip.style.borderColor = '#0d9488';

                            const dateInput = document.getElementById('chatMeetingDate');
                            const startInput = document.getElementById('chatMeetingStartTime');
                            const endInput = document.getElementById('chatMeetingEndTime');
                            const rangeInput = document.getElementById('chatInterviewRange');

                            if (dateInput && slot.date) dateInput.value = slot.date;
                            if (startInput && slot.start_time) startInput.value = slot.start_time;
                            if (endInput && slot.end_time) endInput.value = slot.end_time;
                            if (rangeInput && slot.date) {
                                rangeInput.value = `Khoảng ngày ${slot.date} (Theo khung giờ AI gợi ý)`;
                            }
                        });
                        aiChipsBox.appendChild(chip);
                    });
                } else {
                    aiChipsBox.innerHTML = '<span style="font-size:0.78rem; color:#64748b;">Không có khung giờ rảnh đối chiếu tự động. Bạn có thể tự chọn ngày giờ ở bên dưới.</span>';
                }
            })
            .catch(() => {
                if (aiSlotLoading) aiSlotLoading.style.display = 'none';
                aiChipsBox.innerHTML = '<span style="font-size:0.78rem; color:#64748b;">Bạn có thể tùy chỉnh ngày giờ trực tiếp ở bên dưới.</span>';
            });
    }

    // Submit form hẹn lịch qua AJAX
    if (meetingForm) {
        meetingForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitMeetingModalBtn');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Đang thiết lập lịch...</span>';
            }

            const formData = new FormData(meetingForm);
            const scheduleUrl = config.routes.scheduleMeeting || meetingForm.action;

            fetch(scheduleUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }

                if (data.success && data.data) {
                    // Đóng modal
                    closeMeetingDialog();

                    // Thêm tin nhắn mời phỏng vấn vào luồng chat tức thì
                    appendOutgoingMessage(
                        data.data.message_content,
                        data.data.time_formatted || 'Vừa xong',
                        data.data.message_id,
                        data.data.message_title,
                        'meeting_invite'
                    );

                    // Cập nhật last message trên sidebar
                    updateSidebarLastMessage(
                        formData.get('partner_id'),
                        data.data.message_title || 'Lời mời phỏng vấn mới',
                        'Vừa xong'
                    );

                    scrollToBottom(true);
                    alert(data.message || 'Đã lên lịch phỏng vấn và gửi thư mời thành công!');
                } else {
                    alert(data.message || 'Không thể thiết lập lịch phỏng vấn. Vui lòng thử lại!');
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                console.error('[Schedule Meeting Error]', err);
                alert('Có lỗi xảy ra khi kết nối máy chủ. Vui lòng thử lại!');
            });
        });
    }

    // =========================================================================
    // 11. DIALOG DUYỆT NHẬN VIỆC & THIẾT LẬP ONBOARDING TRỰC TIẾP TỪ CHAT (EMPLOYER)
    // =========================================================================
    const acceptModal = document.getElementById('chatAcceptOnboardingModal');
    const btnOpenChatAcceptModal = document.getElementById('btnOpenChatAcceptModal');
    const closeChatAcceptModalBtn = document.getElementById('closeChatAcceptModalBtn');
    const cancelChatAcceptModalBtn = document.getElementById('cancelChatAcceptModalBtn');
    const acceptForm = document.getElementById('chatAcceptOnboardingForm');
    const acceptJobSelect = document.getElementById('chatAcceptJobSelect');
    const acceptWorkplace = document.getElementById('chatAcceptWorkplace');
    const acceptRequestId = document.getElementById('chatAcceptRequestId');

    function openChatAcceptDialog() {
        if (!acceptModal) return;
        acceptModal.style.display = 'flex';
    }

    function closeChatAcceptDialog() {
        if (!acceptModal) return;
        acceptModal.style.display = 'none';
    }

    if (btnOpenChatAcceptModal) {
        btnOpenChatAcceptModal.addEventListener('click', openChatAcceptDialog);
    }
    if (closeChatAcceptModalBtn) {
        closeChatAcceptModalBtn.addEventListener('click', closeChatAcceptDialog);
    }
    if (cancelChatAcceptModalBtn) {
        cancelChatAcceptModalBtn.addEventListener('click', closeChatAcceptDialog);
    }
    if (acceptModal) {
        acceptModal.addEventListener('click', function (e) {
            if (e.target === acceptModal) closeChatAcceptDialog();
        });
    }

    // Khi đổi vị trí tuyển dụng trong form duyệt nhận việc, cập nhật request_id và workplace
    if (acceptJobSelect) {
        acceptJobSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt) {
                const reqId = opt.getAttribute('data-request-id');
                const wp = opt.getAttribute('data-workplace');
                if (acceptRequestId && reqId !== null) {
                    acceptRequestId.value = reqId;
                }
                if (acceptWorkplace && wp) {
                    acceptWorkplace.value = wp;
                }
            }
        });
    }

    // Submit form duyệt nhận việc qua AJAX
    if (acceptForm) {
        acceptForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitChatAcceptModalBtn');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Đang xác nhận & gửi thông báo...</span>';
            }

            const formData = new FormData(acceptForm);
            const acceptUrl = config.routes.acceptOnboarding || acceptForm.action;

            fetch(acceptUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }

                if (data.success && data.data) {
                    // Đóng modal
                    closeChatAcceptDialog();

                    // Thêm tin nhắn chúc mừng nhận việc vào luồng chat tức thì
                    appendOutgoingMessage(
                        data.data.message_content,
                        data.data.time_formatted || 'Vừa xong',
                        data.data.message_id,
                        data.data.message_title,
                        'onboarding'
                    );

                    // Cập nhật last message trên sidebar
                    updateSidebarLastMessage(
                        formData.get('partner_id'),
                        data.data.message_title || 'Thông báo nhận việc & ngày đầu',
                        'Vừa xong'
                    );

                    scrollToBottom(true);
                    alert(data.message || 'Đã duyệt nhận người lao động và gửi cẩm nang ngày đầu đi làm thành công!');
                } else {
                    alert(data.message || 'Không thể duyệt nhận việc lúc này. Vui lòng thử lại!');
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                console.error('[Accept Onboarding Error]', err);
                alert('Có lỗi xảy ra khi kết nối máy chủ. Vui lòng thử lại!');
            });
        });
    }

    // Tự động mở dialog nếu được chỉ định (URL param open_meeting_modal=1 hoặc autoOpenMeetingModal)
    const urlParams = new URLSearchParams(window.location.search);
    if (config.autoOpenMeetingModal || urlParams.get('open_meeting_modal') === '1') {
        setTimeout(function () {
            openMeetingDialog();
            // Dọn query param khỏi thanh địa chỉ để không bị mở lặp khi F5
            if (window.history && window.history.replaceState) {
                urlParams.delete('open_meeting_modal');
                const cleanQuery = urlParams.toString();
                const newUrl = window.location.pathname + (cleanQuery ? '?' + cleanQuery : '');
                window.history.replaceState({}, document.title, newUrl);
            }
        }, 300);
    } else if (urlParams.get('open_accept_modal') === '1') {
        setTimeout(function () {
            openChatAcceptDialog();
            if (window.history && window.history.replaceState) {
                urlParams.delete('open_accept_modal');
                const cleanQuery = urlParams.toString();
                const newUrl = window.location.pathname + (cleanQuery ? '?' + cleanQuery : '');
                window.history.replaceState({}, document.title, newUrl);
            }
        }, 300);
    }
    // =========================================================================
    // 12. JOBY AI ENGINE (TRỢ LÝ THÔNG MINH TRONG MESSENGER)
    // =========================================================================
    const aiJobyView = document.getElementById('aiJobyMainView');
    const userChatView = document.getElementById('userChatMainView');
    const aiJobyStream = document.getElementById('aiJobyStream');
    const aiJobyContainer = document.getElementById('aiJobyMessagesContainer');
    const aiJobyInput = document.getElementById('aiJobyInputText');
    const aiJobyForm = document.getElementById('aiJobyForm');
    let jobyHistoryLoaded = false;
    let jobyLocalHistory = [];

    function scrollAiStreamToBottom(smooth = true) {
        if (aiJobyStream) {
            aiJobyStream.scrollTo({
                top: aiJobyStream.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }
    }

    // Mở khung chat Joby AI
    window.openAiJobyChat = function() {
        document.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
        const jobyItem = document.getElementById('conv-ai-joby');
        if (jobyItem) jobyItem.classList.add('active');

        if (userChatView) userChatView.style.display = 'none';
        if (aiJobyView) {
            aiJobyView.style.display = 'flex';
            if (!jobyHistoryLoaded) {
                loadJobyHistory();
            } else {
                scrollAiStreamToBottom(false);
            }
        }

        // Cập nhật URL
        if (window.history && window.history.pushState) {
            const url = new URL(window.location);
            url.searchParams.set('partner_id', 'ai_joby');
            window.history.pushState({}, '', url.toString());
        }

        if (aiJobyInput) aiJobyInput.focus();
    };

    // Tải lịch sử trò chuyện cùng Joby AI từ máy chủ
    function loadJobyHistory() {
        if (!config.routes.aiChatHistory || !aiJobyContainer) return;
        jobyHistoryLoaded = true;

        fetch(config.routes.aiChatHistory)
        .then(res => res.json())
        .then(data => {
            if (data.success && Array.isArray(data.history) && data.history.length > 0) {
                data.history.forEach(item => {
                    const userMsg = item.user_message || item.user || '';
                    const aiReply = item.ai_reply || item.reply || '';
                    const timeStr = item.created_at ? item.created_at.substring(11, 16) : 'Vừa xong';
                    if (userMsg) {
                        appendJobyUserBubble(userMsg, timeStr);
                        jobyLocalHistory.push({ role: 'user', content: userMsg });
                    }
                    if (aiReply) {
                        appendJobyBotBubble(aiReply, timeStr, item.suggestions);
                        jobyLocalHistory.push({ role: 'assistant', content: aiReply });
                    }
                });
                scrollAiStreamToBottom(false);
            }
        })
        .catch(err => {
            console.error('[Load Joby History Error]', err);
        });
    }

    // Thêm bong bóng tin nhắn của người dùng trong chat Joby AI
    function appendJobyUserBubble(text, timeStr = 'Vừa xong') {
        if (!aiJobyContainer) return;
        const row = document.createElement('div');
        row.className = 'ai-joby-bubble-row user-row';
        row.innerHTML = `
            <div class="ai-user-bubble">
                ${escapeHtml(text).replace(/\n/g, '<br>')}
                <div style="font-size:0.65rem; opacity:0.8; text-align:right; margin-top:2px;">${timeStr}</div>
            </div>
        `;
        aiJobyContainer.appendChild(row);
        scrollAiStreamToBottom(true);
    }

    // Thêm bong bóng phản hồi của Joby AI
    function appendJobyBotBubble(markdownText, timeStr = 'Vừa xong', suggestions = []) {
        if (!aiJobyContainer) return;
        const row = document.createElement('div');
        row.className = 'ai-joby-bubble-row bot-row';
        let chipsHtml = '';
        if (Array.isArray(suggestions) && suggestions.length > 0) {
            chipsHtml = '<div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:8px;">' +
                suggestions.map(s => `<button type="button" class="ai-reply-chip" onclick="askJobyFromChip(this)">${escapeHtml(s)}</button>`).join('') +
                '</div>';
        }
        row.innerHTML = `
            <div class="ai-joby-avatar-wrap" style="width:30px; height:30px; margin-top:2px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <rect width="18" height="12" x="3" y="6" rx="2"></rect>
                    <circle cx="9" cy="12" r="1" fill="currentColor"></circle>
                    <circle cx="15" cy="12" r="1" fill="currentColor"></circle>
                    <path d="M12 2v4"></path><path d="M2 12h1"></path><path d="M21 12h1"></path>
                </svg>
            </div>
            <div class="ai-msg-bubble">
                <div class="ai-bot-content">${parseMarkdownSimple(markdownText)}</div>
                ${chipsHtml}
                <div style="font-size:0.65rem; color:#94a3b8; margin-top:4px;">Joby AI &bull; ${timeStr}</div>
            </div>
        `;
        aiJobyContainer.appendChild(row);
        scrollAiStreamToBottom(true);
    }

    // Gửi tin nhắn đến Joby AI
    function sendJobyMessage(customText = null) {
        if (!aiJobyInput && !customText) return;
        const text = (customText || aiJobyInput.value || '').trim();
        if (!text) return;

        if (aiJobyInput) {
            aiJobyInput.value = '';
            aiJobyInput.style.height = 'auto';
        }

        appendJobyUserBubble(text, 'Vừa xong');
        jobyLocalHistory.push({ role: 'user', content: text });

        // Hiển thị indicator đang soạn
        const typingEl = document.createElement('div');
        typingEl.id = 'jobyTypingIndicator';
        typingEl.className = 'ai-typing-indicator';
        typingEl.style.margin = '4px 0 4px 38px';
        typingEl.innerHTML = `
            <div class="ai-typing-dot"></div>
            <div class="ai-typing-dot"></div>
            <div class="ai-typing-dot"></div>
            <span>Joby đang soạn câu trả lời...</span>
        `;
        aiJobyContainer.appendChild(typingEl);
        scrollAiStreamToBottom(true);

        const sendBtn = document.getElementById('aiJobySendBtn');
        if (sendBtn) sendBtn.disabled = true;

        fetch(config.routes.aiChat, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                message: text,
                history: jobyLocalHistory
            })
        })
        .then(res => res.json())
        .then(data => {
            if (sendBtn) sendBtn.disabled = false;
            typingEl.remove();

            const reply = data.reply || data.response || 'Joby đã tiếp nhận thông tin của bạn.';
            const suggestions = data.suggestions || [];
            jobyLocalHistory.push({ role: 'assistant', content: reply });
            appendJobyBotBubble(reply, 'Vừa xong', suggestions);

            // Cập nhật snippet ở sidebar
            const snippet = document.getElementById('jobySidebarSnippet');
            if (snippet) {
                snippet.textContent = reply.replace(/[*_#`]/g, '').substring(0, 45) + '...';
            }
        })
        .catch(err => {
            if (sendBtn) sendBtn.disabled = false;
            typingEl.remove();
            appendJobyBotBubble('Rất tiếc, đã có sự cố kết nối tới máy chủ trí tuệ nhân tạo. Bạn vui lòng thử lại sau giây lát!');
            console.error('[Joby Chat Error]', err);
        });
    }

    if (aiJobyForm) {
        aiJobyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            sendJobyMessage();
        });
    }

    if (aiJobyInput) {
        aiJobyInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Bấm Enter gửi câu hỏi cho Joby, Shift+Enter xuống dòng
        aiJobyInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                if (e.shiftKey) {
                    return;
                }
                e.preventDefault();
                sendJobyMessage();
            }
        });
    }

    window.askJobyFromChip = function(btn) {
        const text = btn.textContent.trim();
        if (text) sendJobyMessage(text);
    };

    window.clearJobyHistory = function() {
        if (!confirm('Bạn có chắc chắn muốn xoá toàn bộ lịch sử trò chuyện cùng Joby AI không?')) return;
        fetch(config.routes.aiChatClear, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(() => {
            if (aiJobyContainer) aiJobyContainer.innerHTML = '';
            jobyLocalHistory = [];
            if (window.Toast) {
                Toast.success('Đã xoá lịch sử trò chuyện cùng Joby AI.');
            } else {
                alert('Đã xoá lịch sử trò chuyện cùng Joby AI.');
            }
        })
        .catch(err => console.error(err));
    };

    function parseMarkdownSimple(text) {
        if (!text) return '';
        let html = text
            .replace(/^### (.*$)/gim, '<h4 style="margin:4px 0; color:#0f172a;">$1</h4>')
            .replace(/^## (.*$)/gim, '<h4 style="margin:4px 0; color:#0f172a;">$1</h4>')
            .replace(/^# (.*$)/gim, '<h4 style="margin:4px 0; color:#0f172a;">$1</h4>')
            .replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/gim, '<em>$1</em>')
            .replace(/`([^`]+)`/gim, '<code style="background:#f1f5f9;padding:2px 4px;border-radius:4px;font-size:0.85em;">$1</code>')
            .replace(/\[(.*?)\]\((.*?)\)/gim, '<a href="$2" target="_blank" style="color:#0d9488;font-weight:700;text-decoration:underline;">$1</a>')
            .replace(/^\s*-\s+(.*$)/gim, '<li>$1</li>')
            .replace(/^\s*•\s+(.*$)/gim, '<li>$1</li>')
            .replace(/^\s*\d+\.\s+(.*$)/gim, '<li>$1</li>');

        html = html.replace(/(<li>.*?<\/li>)+/gis, function (m) {
            return '<ul style="margin:4px 0;padding-left:18px;">' + m + '</ul>';
        });
        html = html.replace(/\n\n/g, '<br><br>').replace(/\n/g, '<br>');
        return html;
    }

    // =========================================================================
    // 13. TÍNH NĂNG TIN NHẮN: TRẢ LỜI, SAO CHÉP, CHUYỂN TIẾP, XOÁ, THU HỒI
    // =========================================================================
    let currentActiveMsgRow = null;
    let quotedReplyText = null;
    let quotedReplyTo = null;

    window.handleMsgContextMenu = function(e, row) {
        e.preventDefault();
        e.stopPropagation();
        currentActiveMsgRow = row;

        const isMine = (row.getAttribute('data-is-mine') === '1');
        const recallBtn = document.getElementById('ctxRecallBtn');
        if (recallBtn) recallBtn.style.display = isMine ? 'flex' : 'none';

        const menu = document.getElementById('messageContextMenu');
        const convMenu = document.getElementById('conversationContextMenu');
        if (convMenu) convMenu.style.display = 'none';

        if (menu) {
            menu.style.display = 'block';
            let x = e.clientX;
            let y = e.clientY;
            const menuWidth = 185;
            const menuHeight = 200;
            if (x + menuWidth > window.innerWidth) x = window.innerWidth - menuWidth - 10;
            if (y + menuHeight > window.innerHeight) y = window.innerHeight - menuHeight - 10;
            menu.style.left = x + 'px';
            menu.style.top = y + 'px';
        }
    };

    window.openMsgActionsMenu = function(e, btn) {
        e.preventDefault();
        e.stopPropagation();
        const row = btn.closest ? btn.closest('.chat-message-row') : null;
        if (!row) return;
        currentActiveMsgRow = row;

        const isMine = (row.getAttribute('data-is-mine') === '1');
        const recallBtn = document.getElementById('ctxRecallBtn');
        if (recallBtn) recallBtn.style.display = isMine ? 'flex' : 'none';

        const menu = document.getElementById('messageContextMenu');
        const convMenu = document.getElementById('conversationContextMenu');
        if (convMenu) convMenu.style.display = 'none';

        if (menu) {
            menu.style.display = 'block';
            const rect = btn.getBoundingClientRect();
            let x = rect.left;
            let y = rect.bottom + 4;
            const menuWidth = 185;
            const menuHeight = 200;
            if (x + menuWidth > window.innerWidth) x = window.innerWidth - menuWidth - 10;
            if (y + menuHeight > window.innerHeight) y = rect.top - menuHeight - 4;
            menu.style.left = x + 'px';
            menu.style.top = y + 'px';
        }
    };

    window.toggleMsgReactions = function(e, btn) {
        e.preventDefault();
        e.stopPropagation();
        const row = btn.closest ? btn.closest('.chat-message-row') : null;
        if (!row) return;
        const bar = row.querySelector('.msg-reaction-bar');
        if (!bar) return;

        const wasOpen = (bar.style.display === 'flex');
        document.querySelectorAll('.msg-reaction-bar').forEach(b => b.style.display = 'none');
        bar.style.display = wasOpen ? 'none' : 'flex';
    };

    window.addMsgReaction = function(msgId, emoji) {
        const row = document.querySelector(`.chat-message-row[data-msg-id="${msgId}"]`);
        if (!row) return;

        const bar = row.querySelector('.msg-reaction-bar');
        if (bar) bar.style.display = 'none';

        const bubbleWrap = row.querySelector('.chat-bubble-wrap');
        if (!bubbleWrap) return;

        let badge = bubbleWrap.querySelector('.bubble-reaction-badge');
        if (badge) {
            if (badge.textContent === emoji) {
                badge.remove();
                return;
            }
            badge.textContent = emoji;
        } else {
            badge = document.createElement('span');
            badge.className = 'bubble-reaction-badge';
            badge.textContent = emoji;
            badge.title = 'Bày tỏ cảm xúc: ' + emoji;
            badge.onclick = function(e) {
                e.stopPropagation();
                this.remove();
            };
            bubbleWrap.appendChild(badge);
        }
    };

    window.execMsgCtxAction = function(action) {
        const menu = document.getElementById('messageContextMenu');
        if (menu) menu.style.display = 'none';
        if (!currentActiveMsgRow) return;

        switch (action) {
            case 'reply':
                actionReplyMsg(currentActiveMsgRow);
                break;
            case 'copy':
                actionCopyMsg(currentActiveMsgRow);
                break;
            case 'forward':
                actionForwardMsg(currentActiveMsgRow);
                break;
            case 'delete':
                actionDeleteMsg(currentActiveMsgRow);
                break;
            case 'recall':
                actionRecallMsg(currentActiveMsgRow);
                break;
        }
    };

    window.actionReplyMsg = function(btnOrRow) {
        const row = btnOrRow.closest ? btnOrRow.closest('.chat-message-row') : btnOrRow;
        if (!row) return;

        const contentEl = row.querySelector('.bubble-content');
        const text = contentEl ? contentEl.innerText.trim() : '';
        const sender = row.getAttribute('data-sender-name') || 'Người dùng';

        quotedReplyText = text;
        quotedReplyTo = sender;

        const replyBar = document.getElementById('chatReplyPreviewBar');
        const replyToEl = document.getElementById('replyPreviewTo');
        const replyTextEl = document.getElementById('replyPreviewText');

        if (replyBar && replyToEl && replyTextEl) {
            replyToEl.textContent = 'Đang trả lời ' + sender + ':';
            replyTextEl.textContent = text.substring(0, 100) + (text.length > 100 ? '...' : '');
            replyBar.style.display = 'flex';
        }

        if (chatInput) chatInput.focus();
    };

    window.cancelReply = function() {
        quotedReplyText = null;
        quotedReplyTo = null;
        const replyBar = document.getElementById('chatReplyPreviewBar');
        if (replyBar) replyBar.style.display = 'none';
    };

    window.actionCopyMsg = function(btnOrRow) {
        const row = btnOrRow.closest ? btnOrRow.closest('.chat-message-row') : btnOrRow;
        if (!row) return;

        const contentEl = row.querySelector('.bubble-content');
        const text = contentEl ? contentEl.innerText.trim() : '';
        if (text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    if (window.Toast) Toast.success('Đã sao chép nội dung tin nhắn!');
                }).catch(() => {
                    alert('Đã sao chép: ' + text);
                });
            } else {
                alert('Đã sao chép: ' + text);
            }
        }
    };

    window.actionForwardMsg = function(btnOrRow) {
        const row = btnOrRow.closest ? btnOrRow.closest('.chat-message-row') : btnOrRow;
        if (!row) return;

        const contentEl = row.querySelector('.bubble-content');
        const text = contentEl ? contentEl.innerText.trim() : '';
        if (!text) return;

        const previewEl = document.getElementById('forwardMsgPreview');
        if (previewEl) previewEl.textContent = text;

        const contactListEl = document.getElementById('forwardContactList');
        if (contactListEl) {
            contactListEl.innerHTML = '';
            const convItems = document.querySelectorAll('.conversation-item:not(.conv-ai-pinned)');
            let count = 0;
            convItems.forEach(item => {
                const partnerId = parseInt(item.getAttribute('data-partner-id'));
                if (partnerId && partnerId !== currentPartnerId) {
                    count++;
                    const name = item.getAttribute('data-partner-display') || 'Thành viên';
                    const avatar = item.getAttribute('data-partner-avatar') || '/images/logo.png';
                    const div = document.createElement('div');
                    div.className = 'forward-contact-item';
                    div.innerHTML = `
                        <div class="forward-contact-left">
                            <img src="${avatar}" alt="avatar" class="forward-contact-avatar" onerror="this.src='/images/logo.png'">
                            <span class="forward-contact-name">${escapeHtml(name)}</span>
                        </div>
                        <button type="button" class="btn-forward-send" onclick="sendForwardToPartner(${partnerId}, ${JSON.stringify(text)})">Gửi</button>
                    `;
                    contactListEl.appendChild(div);
                }
            });
            if (count === 0) {
                contactListEl.innerHTML = '<span style="font-size:0.8rem; color:#94a3b8; padding:0.5rem 0;">Chưa có liên hệ khác để chuyển tiếp.</span>';
            }
        }

        const modal = document.getElementById('forwardMsgModal');
        if (modal) modal.style.display = 'flex';
    };

    window.closeForwardModal = function() {
        const modal = document.getElementById('forwardMsgModal');
        if (modal) modal.style.display = 'none';
    };

    window.sendForwardToPartner = function(targetPartnerId, text) {
        const formData = new FormData();
        formData.append('receiver_account_id', targetPartnerId);
        formData.append('message_content', text);

        fetch(config.routes.sendAjax, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            closeForwardModal();
            if (data.success) {
                if (window.Toast) Toast.success('Đã chuyển tiếp tin nhắn thành công!');
                updateSidebarLastMessage(targetPartnerId, text, 'Vừa xong');
            } else {
                alert(data.message || 'Không thể chuyển tiếp tin nhắn lúc này.');
            }
        })
        .catch(err => {
            console.error(err);
            closeForwardModal();
        });
    };

    window.actionDeleteMsg = function(btnOrRow) {
        const row = btnOrRow.closest ? btnOrRow.closest('.chat-message-row') : btnOrRow;
        if (!row) return;

        row.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
        row.style.opacity = '0';
        row.style.transform = 'scale(0.95)';
        setTimeout(() => {
            row.remove();
            if (window.Toast) Toast.info('Đã xoá tin nhắn khỏi thiết bị của bạn.');
        }, 200);
    };

    window.actionRecallMsg = function(btnOrRow) {
        const row = btnOrRow.closest ? btnOrRow.closest('.chat-message-row') : btnOrRow;
        if (!row) return;

        if (row.getAttribute('data-is-mine') !== '1') {
            if (window.Toast) Toast.warning('Chỉ người gửi mới có quyền thu hồi tin nhắn!');
            return;
        }

        const bubble = row.querySelector('.chat-bubble');
        if (bubble) {
            bubble.classList.add('bubble-recalled');
            bubble.innerHTML = '<div class="bubble-content" style="font-style:italic; color:#94a3b8;">Tin nhắn đã được thu hồi</div>';
        }
        const hoverActs = row.querySelector('.msg-hover-tools');
        if (hoverActs) hoverActs.remove();
        const rxBar = row.querySelector('.msg-reaction-bar');
        if (rxBar) rxBar.remove();
        const rxBadge = row.querySelector('.bubble-reaction-badge');
        if (rxBadge) rxBadge.remove();

        if (window.Toast) Toast.success('Đã thu hồi tin nhắn thành công.');
    };

    // =========================================================================
    // 14. TÍNH NĂNG NGƯỜI DÙNG: GHIM, ẨN, XOÁ CUỘC TRÒ CHUYỆN
    // =========================================================================
    let currentActivePartnerId = null;
    let currentActivePartnerName = '';

    function getPinnedPartners() {
        try {
            return JSON.parse(localStorage.getItem('joblink_pinned_partners') || '[]');
        } catch (e) {
            return [];
        }
    }

    function setPinnedPartners(arr) {
        localStorage.setItem('joblink_pinned_partners', JSON.stringify(arr));
    }

    function getHiddenPartners() {
        try {
            return JSON.parse(localStorage.getItem('joblink_hidden_partners') || '[]');
        } catch (e) {
            return [];
        }
    }

    function setHiddenPartners(arr) {
        localStorage.setItem('joblink_hidden_partners', JSON.stringify(arr));
    }

    window.handlePartnerContextMenu = function(e, partnerId, partnerName) {
        e.preventDefault();
        e.stopPropagation();
        currentActivePartnerId = partnerId;
        currentActivePartnerName = partnerName;

        const isPinned = getPinnedPartners().includes(partnerId);
        const pinLabel = document.getElementById('ctxPinLabel');
        if (pinLabel) {
            pinLabel.textContent = isPinned ? 'Bỏ ghim cuộc trò chuyện' : 'Ghim cuộc trò chuyện';
        }

        const msgMenu = document.getElementById('messageContextMenu');
        if (msgMenu) msgMenu.style.display = 'none';

        const convMenu = document.getElementById('conversationContextMenu');
        if (convMenu) {
            convMenu.style.display = 'block';
            let x = e.clientX;
            let y = e.clientY;
            const menuWidth = 185;
            const menuHeight = 150;
            if (x + menuWidth > window.innerWidth) x = window.innerWidth - menuWidth - 10;
            if (y + menuHeight > window.innerHeight) y = window.innerHeight - menuHeight - 10;
            convMenu.style.left = x + 'px';
            convMenu.style.top = y + 'px';
        }
    };

    window.execConvCtxAction = function(action) {
        const convMenu = document.getElementById('conversationContextMenu');
        if (convMenu) convMenu.style.display = 'none';
        if (!currentActivePartnerId) return;

        const partnerId = currentActivePartnerId;
        const partnerName = currentActivePartnerName;
        const convItem = document.getElementById('conv-item-' + partnerId);

        if (action === 'pin') {
            let pinned = getPinnedPartners();
            const badge = document.getElementById('pin-badge-' + partnerId);
            if (pinned.includes(partnerId)) {
                pinned = pinned.filter(id => id !== partnerId);
                setPinnedPartners(pinned);
                if (badge) badge.style.display = 'none';
                if (convItem) convItem.classList.remove('is-pinned');
                if (window.Toast) Toast.info(`Đã bỏ ghim cuộc trò chuyện với ${partnerName}.`);
            } else {
                pinned.push(partnerId);
                setPinnedPartners(pinned);
                if (badge) badge.style.display = 'inline-block';
                if (convItem) convItem.classList.add('is-pinned');
                if (window.Toast) Toast.success(`Đã ghim cuộc trò chuyện với ${partnerName} lên đầu.`);
            }
            applyPinnedAndHiddenOrder();
        } else if (action === 'hide') {
            let hidden = getHiddenPartners();
            if (!hidden.includes(partnerId)) {
                hidden.push(partnerId);
                setHiddenPartners(hidden);
            }
            if (convItem) convItem.style.display = 'none';
            if (window.Toast) Toast.info(`Đã ẩn cuộc trò chuyện với ${partnerName}.`);
        } else if (action === 'delete') {
            const confirmMsg = `Bạn có chắc chắn muốn xoá cuộc trò chuyện với "${partnerName}" không? Toàn bộ tin nhắn trao đổi trên thiết bị sẽ bị dọn dẹp.`;
            if (window.Dialog && window.Dialog.confirm) {
                Dialog.confirm({
                    title: 'Xoá Cuộc Trò Chuyện',
                    content: confirmMsg,
                    confirmType: 'danger',
                    confirmText: 'Đồng Ý Xoá',
                    onConfirm: () => doDeleteConv(partnerId, partnerName, convItem)
                });
            } else if (confirm(confirmMsg)) {
                doDeleteConv(partnerId, partnerName, convItem);
            }
        }
    };

    function doDeleteConv(partnerId, partnerName, convItem) {
        if (convItem) convItem.remove();
        let hidden = getHiddenPartners();
        if (!hidden.includes(partnerId)) {
            hidden.push(partnerId);
            setHiddenPartners(hidden);
        }
        if (window.Toast) Toast.success(`Đã xoá cuộc trò chuyện với ${partnerName}.`);

        // Nếu đang mở cuộc trò chuyện này, chuyển về Joby AI
        if (parseInt(currentPartnerId) === parseInt(partnerId)) {
            openAiJobyChat();
        }
    }

    function applyPinnedAndHiddenOrder() {
        const list = document.getElementById('conversationList');
        const jobyItem = document.getElementById('conv-ai-joby');
        if (!list) return;

        const pinnedIds = getPinnedPartners();
        const hiddenIds = getHiddenPartners();

        // Ẩn các cuộc trò chuyện bị ẩn
        hiddenIds.forEach(id => {
            const item = document.getElementById('conv-item-' + id);
            if (item && parseInt(id) !== parseInt(currentPartnerId)) {
                item.style.display = 'none';
            }
        });

        // Di chuyển các cuộc trò chuyện đã ghim lên ngay sau Joby AI
        pinnedIds.slice().reverse().forEach(id => {
            const item = document.getElementById('conv-item-' + id);
            const badge = document.getElementById('pin-badge-' + id);
            if (item) {
                item.classList.add('is-pinned');
                if (badge) badge.style.display = 'inline-block';
                if (jobyItem && jobyItem.nextSibling) {
                    list.insertBefore(item, jobyItem.nextSibling);
                }
            }
        });
    }
    applyPinnedAndHiddenOrder();

    // 15. Đóng context menu & reaction bar khi click ra ngoài
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#messageContextMenu')) {
            const menu = document.getElementById('messageContextMenu');
            if (menu) menu.style.display = 'none';
        }
        if (!e.target.closest('#conversationContextMenu')) {
            const menu = document.getElementById('conversationContextMenu');
            if (menu) menu.style.display = 'none';
        }
        if (!e.target.closest('.msg-reaction-bar') && !e.target.closest('.btn-react-trigger')) {
            document.querySelectorAll('.msg-reaction-bar').forEach(b => b.style.display = 'none');
        }
    });

    // Nếu cấu hình ban đầu là Joby AI thì kích hoạt luôn
    if (config.isAiActive) {
        openAiJobyChat();
    }
});

// Modal Admin Broadcast
window.openBroadcastModal = function () {
    const modal = document.getElementById('broadcastModal');
    if (modal) modal.style.display = 'flex';
};

window.closeBroadcastModal = function () {
    const modal = document.getElementById('broadcastModal');
    if (modal) modal.style.display = 'none';
};
