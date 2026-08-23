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

    // 2. Tự động co giãn textarea khi gõ
    if (chatInput) {
        chatInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Gửi tin khi bấm Enter (Shift+Enter để xuống dòng)
        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (chatForm) {
                    chatForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
            }
        });
    }

    // 3. Gửi tin nhắn qua AJAX (Không tải lại trang)
    if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const content = (chatInput.value || '').trim();
            const receiverId = document.getElementById('activeReceiverId')?.value || currentPartnerId;

            if (!content || !receiverId) return;

            const sendBtn = document.getElementById('chatSendButton');
            if (sendBtn) sendBtn.disabled = true;

            const formData = new FormData();
            formData.append('receiver_account_id', receiverId);
            formData.append('message_content', content);

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
                    appendOutgoingMessage(data.data.message_content, data.data.time_formatted || 'Vừa xong', data.data.message_id);

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
        });
    }

    // Hàm tạo bubble tin nhắn gửi đi (Của tôi)
    function appendOutgoingMessage(content, timeStr, msgId = null) {
        if (!messagesStream) return;

        // Xóa starter nếu có
        const starter = messagesStream.querySelector('.chat-thread-starter');
        if (starter) starter.remove();

        const row = document.createElement('div');
        row.className = 'chat-message-row outgoing-row';
        if (msgId) row.setAttribute('data-msg-id', msgId);

        const escaped = escapeHtml(content).replace(/\n/g, '<br>');

        row.innerHTML = `
            <div class="chat-bubble-wrap">
                <div class="chat-bubble bubble-outgoing">
                    <div class="bubble-content">${escaped}</div>
                </div>
                <div class="bubble-meta">
                    <span class="bubble-time">${timeStr}</span>
                    <span class="bubble-check">✓</span>
                </div>
            </div>
        `;
        messagesStream.appendChild(row);
    }

    // Hàm tạo bubble tin nhắn nhận về (Đối phương)
    function appendIncomingMessage(content, timeStr, avatarUrl, msgId = null, title = null) {
        if (!messagesStream) return;

        const starter = messagesStream.querySelector('.chat-thread-starter');
        if (starter) starter.remove();

        const row = document.createElement('div');
        row.className = 'chat-message-row incoming-row';
        if (msgId) row.setAttribute('data-msg-id', msgId);

        const escaped = escapeHtml(content).replace(/\n/g, '<br>');
        let titleHtml = '';
        if (title) {
            titleHtml = `<div class="bubble-title">${escapeHtml(title)}</div>`;
        }

        row.innerHTML = `
            <img src="${avatarUrl || '/images/logo.png'}" alt="avatar" class="bubble-sender-avatar" onerror="this.src='/images/logo.png'">
            <div class="chat-bubble-wrap">
                <div class="chat-bubble bubble-incoming">
                    ${titleHtml}
                    <div class="bubble-content">${escaped}</div>
                </div>
                <div class="bubble-meta">
                    <span class="bubble-time">${timeStr}</span>
                </div>
            </div>
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
        if (partnerId === currentPartnerId) return;

        // Cập nhật class active trên sidebar
        document.querySelectorAll('.conversation-item').forEach(el => {
            el.classList.remove('active');
            if (parseInt(el.getAttribute('data-partner-id')) === partnerId) {
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
                            appendOutgoingMessage(msg.message_content, timeStr, msgId);
                        } else {
                            appendIncomingMessage(msg.message_content, timeStr, msg.sender_avatar, msgId, msg.message_title);
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
                                chatInput.value = `Chào bạn,\n\nCông ty trân trọng mời bạn tham gia phỏng vấn vào khung giờ sau:\n📅 Thời gian: ${slot.label}\n📍 Địa điểm: Văn phòng Công ty\n\nBạn vui lòng phản hồi xem khung giờ này có thuận tiện với bạn không nhé!`;
                                chatInput.focus();
                                chatInput.style.height = 'auto';
                                chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
                            }
                        });
                        aiSlotsContainer.appendChild(btn);
                    });
                } else {
                    aiSlotsContainer.innerHTML = '<span style="font-size:0.8rem; color:#64748b;">Chưa tìm thấy khung giờ rảnh đối chiếu. Bạn có thể tự thỏa thuận thời gian với ứng viên.</span>';
                }
            }
        })
        .catch(err => {
            if (aiSlotLoading) aiSlotLoading.style.display = 'none';
            if (aiSlotsContainer) aiSlotsContainer.innerHTML = '<span style="font-size:0.8rem; color:#ef4444;">Không thể tải gợi ý lúc này.</span>';
        });
    }

    // 9. Tìm kiếm & Lọc liên hệ trên Sidebar
    if (contactSearch) {
        contactSearch.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            if (clearSearchBtn) {
                clearSearchBtn.style.display = query ? 'block' : 'none';
            }

            document.querySelectorAll('.conversation-item').forEach(item => {
                const name = item.getAttribute('data-partner-name') || '';
                const snippet = item.querySelector('.conv-snippet')?.textContent.toLowerCase() || '';
                if (name.includes(query) || snippet.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function () {
                contactSearch.value = '';
                contactSearch.dispatchEvent(new Event('input'));
                contactSearch.focus();
            });
        }
    }

    // Filter tabs
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');
            document.querySelectorAll('.conversation-item').forEach(item => {
                const unread = parseInt(item.getAttribute('data-unread') || '0');
                if (filter === 'unread') {
                    item.style.display = (unread > 0) ? 'flex' : 'none';
                } else {
                    item.style.display = 'flex';
                }
            });
        });
    });
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
