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
    function appendOutgoingMessage(content, timeStr, msgId = null, title = null, msgType = '') {
        if (!messagesStream) return;

        // Xóa starter nếu có
        const starter = messagesStream.querySelector('.chat-thread-starter');
        if (starter) starter.remove();

        const row = document.createElement('div');
        row.className = 'chat-message-row outgoing-row';
        if (msgId) row.setAttribute('data-msg-id', msgId);

        const escaped = escapeHtml(content).replace(/\n/g, '<br>');
        const isInvite = (msgType === 'invitation' || msgType === 'meeting_invite');
        const isOnboarding = (msgType === 'onboarding');

        row.innerHTML = `
            <div class="chat-bubble-wrap">
                <div class="chat-bubble bubble-outgoing ${isInvite ? 'bubble-invitation' : ''} ${isOnboarding ? 'bubble-onboarding' : ''}">
                    ${title ? `<div class="bubble-title" style="font-weight: 800; font-size: 0.84rem; margin-bottom: 0.35rem; padding-bottom: 0.25rem; border-bottom: 1px solid rgba(255,255,255,0.2);">${escapeHtml(title)}</div>` : ''}
                    <div class="bubble-content">${escaped}</div>
                </div>
                <div class="bubble-meta">
                    <span class="bubble-time">${timeStr}</span>
                    <span class="bubble-check"><svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                </div>
            </div>
        `;
        messagesStream.appendChild(row);
    }

    // Hàm tạo bubble tin nhắn nhận về (Đối phương)
    function appendIncomingMessage(content, timeStr, avatarUrl, msgId = null, title = null, msgType = '') {
        if (!messagesStream) return;

        const starter = messagesStream.querySelector('.chat-thread-starter');
        if (starter) starter.remove();

        const row = document.createElement('div');
        row.className = 'chat-message-row incoming-row';
        if (msgId) row.setAttribute('data-msg-id', msgId);

        const escaped = escapeHtml(content).replace(/\n/g, '<br>');
        const isInvite = (msgType === 'invitation' || msgType === 'meeting_invite');
        const isOnboarding = (msgType === 'onboarding');
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
                            appendOutgoingMessage(msg.message_content, timeStr, msgId, msg.message_title, msg.message_type);
                        } else {
                            appendIncomingMessage(msg.message_content, timeStr, msg.sender_avatar, msgId, msg.message_title, msg.message_type);
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
    const aiSlotLoading = document.getElementById('chatAiSlotLoading');

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

        if (aiSlotLoading) aiSlotLoading.style.display = 'inline';
        if (timelineWrap) timelineWrap.style.display = 'none';
        aiChipsBox.style.display = 'block';
        aiChipsBox.innerHTML = '<span style="font-size:0.78rem; color:#94a3b8;">Đang quét lịch rảnh tối ưu...</span>';

        const reqId = document.getElementById('chatMeetingRequestId')?.value || config.selectedRequestId || 0;
        fetch(`${config.routes.suggestSlots}?seeker_id=${config.activeSeekerId}&request_id=${reqId}`)
            .then(res => res.json())
            .then(data => {
                if (aiSlotLoading) aiSlotLoading.style.display = 'none';

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
