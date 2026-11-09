/**
 * public/js/ai_chatbox.js - JobLink AI Assistant Chat Widget Client
 */

(function () {
    if (window.__JOBLINK_AI_CHATBOX_INITIALIZED__) return;
    window.__JOBLINK_AI_CHATBOX_INITIALIZED__ = true;

    let isOpen = false;
    let chatHistory = [];

    // Helper: Parse markdown đơn giản sang HTML
    function parseMarkdown(text) {
        if (!text) return '';
        let html = text
            .replace(/^### (.*$)/gim, '<h3>$1</h3>')
            .replace(/^## (.*$)/gim, '<h3>$1</h3>')
            .replace(/^# (.*$)/gim, '<h3>$1</h3>')
            .replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/gim, '<em>$1</em>')
            .replace(/`([^`]+)`/gim, '<code>$1</code>')
            .replace(/\[(.*?)\]\((.*?)\)/gim, '<a href="$2" style="color:#0d9488;font-weight:700;text-decoration:underline;">$1</a>')
            .replace(/^\s*-\s+(.*$)/gim, '<li>$1</li>')
            .replace(/^\s*•\s+(.*$)/gim, '<li>$1</li>')
            .replace(/^\s*\d+\.\s+(.*$)/gim, '<li>$1</li>');

        // Bọc các thẻ li liên tiếp vào ul
        html = html.replace(/(<li>.*?<\/li>)+/gis, function (match) {
            return '<ul style="margin:6px 0;padding-left:20px;">' + match + '</ul>';
        });

        // Thay đổi xuống dòng đơn lẻ thành <br> nếu không thuộc danh sách
        html = html.replace(/\n\n/g, '<br><br>').replace(/\n/g, '<br>');
        // Loại bỏ triệt để mọi thẻ <br> dư thừa nằm sát trước, sau, hoặc bên trong các khối HTML như div, ul
        html = html.replace(/(?:<br\s*[\/]?>\s*)+(<\/?(?:div|span|ul|li|p|h3|button|strong|em))/gi, '$1');
        html = html.replace(/(<\/(?:div|span|ul|li|p|h3|button)>)(?:\s*<br\s*[\/]?>)+/gi, '$1');
        html = html.replace(/(<div[^>]*>)(?:\s*<br\s*[\/]?>)+/gi, '$1');
        html = html.replace(/(?:\s*<br\s*[\/]?>)+(<\/div>)/gi, '$1');
        return html;
    }

    // Helper: Render danh sách gợi ý hành động (Action Chips)
    function renderSuggestionChips(suggestions) {
        if (!suggestions || !Array.isArray(suggestions) || suggestions.length === 0) {
            return '';
        }

        let html = '<div class="ai-suggestions">';
        suggestions.forEach(s => {
            const trimmed = String(s).trim();
            if (!trimmed) return;

            if (trimmed.includes('Đăng nhập') || trimmed.includes('dang nhap')) {
                html += `<span class="ai-chip ai-chip-action ai-chip-login" data-action="login">${trimmed}</span>`;
            } else if (trimmed.includes('Đăng ký') || trimmed.includes('dang ky')) {
                html += `<span class="ai-chip ai-chip-action ai-chip-register" data-action="register">${trimmed}</span>`;
            } else if (trimmed.startsWith('action:')) {
                const parts = trimmed.split(':');
                const actionType = parts[1] || 'query';
                const actionLabel = parts[2] || parts[1];
                html += `<span class="ai-chip ai-chip-action" data-action="${actionType}">${actionLabel}</span>`;
            } else {
                html += `<span class="ai-chip" data-query="${trimmed}">${trimmed}</span>`;
            }
        });
        html += '</div>';
        return html;
    }

    // Helper: Chống XSS an toàn
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Helper: Định dạng thời gian cho từng tin nhắn
    function formatMessageTime(input) {
        if (!input) {
            const now = new Date();
            return String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        }
        if (typeof input === 'string' && /^\d{1,2}:\d{2}$/.test(input.trim())) {
            return input.trim();
        }
        const d = new Date(input);
        if (isNaN(d.getTime())) {
            const now = new Date();
            return String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        }
        const now = new Date();
        const isToday = (d.getDate() === now.getDate() && d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear());
        const h = String(d.getHours()).padStart(2, '0');
        const m = String(d.getMinutes()).padStart(2, '0');
        if (isToday) {
            return `${h}:${m}`;
        }
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        return `${h}:${m} • ${day}/${month}`;
    }

    function initChatWidget() {
        // Tuyệt đối không khởi tạo Joby nếu trang đang nằm trong iframe con (ví dụ tab MasterWorkspace) để tránh 2 Joby
        if (window.self !== window.top) return;
        // Không khởi tạo nếu trang đang chạy ở chế độ nhúng embedded=1
        if (window.location.search.includes('embedded=1')) return;
        if (document.getElementById('joblink-ai-launcher')) return;

        let baseUrl = (typeof window.APP_URL !== 'undefined' ? window.APP_URL : '');
        if (!baseUrl) {
            const pathname = window.location.pathname;
            const segments = pathname.split('/').filter(Boolean);
            if (segments.length > 0 && segments[0].toLowerCase() === 'joblink') {
                baseUrl = '/' + segments[0];
            }
        }
        const jobyAvatarUrl = (typeof window.JOBY_AVATAR_URL !== 'undefined' && window.JOBY_AVATAR_URL)
            ? window.JOBY_AVATAR_URL
            : (baseUrl ? (baseUrl.replace(/\/+$/, '') + '/uploads/avatars/joby.png') : '/uploads/avatars/joby.png');
        const defaultLogoUrl = baseUrl ? (baseUrl.replace(/\/+$/, '') + '/uploads/avatars/logo.png') : '/uploads/avatars/logo.png';

        // 1. Tạo nút Floating Launcher
        const launcher = document.createElement('div');
        launcher.id = 'joblink-ai-launcher';
        launcher.className = 'ai-chat-launcher';
        launcher.innerHTML = `
            <div class="ai-chat-badge" id="ai-chat-badge-toggle" title="Joby AI">
                <span>Joby</span>
            </div>
            <button class="ai-chat-btn" id="ai-chat-btn-toggle" title="Trò chuyện cùng Joby AI">
                <img src="${jobyAvatarUrl}" alt="Joby AI" class="ai-chat-btn-avatar" onerror="this.onerror=null;this.src='${defaultLogoUrl}';">
            </button>
        `;
        document.body.appendChild(launcher);

        // Ẩn nút Joby lơ lửng nếu đang ở trang Tin Nhắn (nơi đã có sẵn khung chat Joby AI tương tác trực tiếp)
        if (window.location.pathname.includes('/messages') || window.location.href.includes('/messages')) {
            launcher.style.display = 'none';
        }

        // Cung cấp hàm toàn cục để các workspace/tab có thể ẩn/hiện Joby lơ lửng khi chuyển tab
        window.setJobyLauncherVisible = function (visible) {
            const lEl = document.getElementById('joblink-ai-launcher');
            if (lEl) {
                lEl.style.display = visible ? '' : 'none';
            }
            if (!visible && isOpen) {
                const chatbox = document.getElementById('joblink-ai-chatbox');
                if (chatbox) chatbox.classList.remove('active');
                isOpen = false;
            }
        };

        const defaultGreeting = (typeof window.JOBY_GREETING !== 'undefined' && window.JOBY_GREETING)
            ? window.JOBY_GREETING
            : "Chào bạn! Mình là Joby, người bạn trợ lý của bạn. Hôm nay mình có thể hỗ trợ gì cho bạn nào?";

        const defaultSuggestions = (typeof window.JOBY_DEFAULT_SUGGESTIONS !== 'undefined' && Array.isArray(window.JOBY_DEFAULT_SUGGESTIONS))
            ? window.JOBY_DEFAULT_SUGGESTIONS
            : ['Đăng nhập ngay', 'Đăng ký tài khoản', 'Tìm việc làm nhanh'];

        const bubbleData = (typeof window.JOBY_BUBBLE_DATA !== 'undefined' && window.JOBY_BUBBLE_DATA)
            ? window.JOBY_BUBBLE_DATA
            : null;

        let bubbleBodyContent = '';
        let bubbleTagText = 'Cập nhật nhanh';
        let bubbleBtnText = 'Trò chuyện ngay';

        if (bubbleData && Array.isArray(bubbleData.cards) && bubbleData.cards.length > 0) {
            bubbleTagText = bubbleData.tag || bubbleTagText;
            bubbleBtnText = bubbleData.btn_text || bubbleBtnText;

            bubbleBodyContent = '<div class="ai-bubble-grid">';
            bubbleData.cards.forEach(card => {
                const cardType = card.type || 'neutral';
                const cardIcon = card.icon || '';
                const cardNum = (card.num !== undefined && card.num !== null) ? card.num : '';
                const cardLabel = card.label || '';
                const cardQuery = card.query || '';
                bubbleBodyContent += `
                    <div class="ai-stat-card ai-stat-${cardType}" data-query="${cardQuery}" title="Bấm để xử lý ngay với Joby">
                        <div class="ai-stat-header">
                            <span class="ai-stat-icon">${cardIcon}</span>
                            <span class="ai-stat-num">${cardNum}</span>
                        </div>
                        <div class="ai-stat-label">${cardLabel}</div>
                    </div>
                `;
            });
            bubbleBodyContent += '</div>';
        } else {
            bubbleBodyContent = `<div class="ai-bubble-plain">${defaultGreeting}</div>`;
        }

        // 1.5. Tạo Bong bóng Thông báo Chủ động (Proactive Speech Bubble)
        const bubbleDiv = document.createElement('div');
        bubbleDiv.id = 'joblink-ai-proactive-bubble';
        bubbleDiv.className = 'ai-proactive-bubble';
        bubbleDiv.innerHTML = `
            <div class="ai-bubble-header">
                <div class="ai-bubble-title">
                    <img src="${jobyAvatarUrl}" alt="Joby" class="ai-bubble-avatar-mini" onerror="this.onerror=null;this.src='${defaultLogoUrl}';">
                    <span class="ai-bubble-dot"></span>
                    <strong>Joby</strong>
                    <span class="ai-bubble-tag">${bubbleTagText}</span>
                </div>
                <button type="button" class="ai-bubble-close" id="ai-bubble-close-btn" title="Đóng thông báo">&times;</button>
            </div>
            <div class="ai-bubble-body">
                ${bubbleBodyContent}
            </div>
            <div class="ai-bubble-actions">
                <span class="ai-bubble-countdown-wrap">Tự đóng sau <b id="ai-bubble-countdown-sec">6</b>s</span>
                <button type="button" class="ai-bubble-chat-now">${bubbleBtnText}</button>
            </div>
            <div class="ai-bubble-progress" id="ai-bubble-progress">
                <div class="ai-bubble-progress-bar" id="ai-bubble-progress-bar"></div>
            </div>
        `;
        document.body.appendChild(bubbleDiv);

        // 2. Tạo Chat Window
        const windowDiv = document.createElement('div');
        windowDiv.id = 'joblink-ai-window';
        windowDiv.className = 'ai-chat-window';
        windowDiv.innerHTML = `
            <div class="ai-chat-header" id="ai-chat-header" style="cursor: pointer; user-select: none;" title="Bấm vào tiêu đề để thu gọn">
                <div class="ai-header-info">
                    <div class="ai-avatar">
                        <img src="${jobyAvatarUrl}" alt="Joby AI" class="ai-avatar-img" onerror="this.onerror=null;this.src='${defaultLogoUrl}';">
                    </div>
                    <div>
                        <h4 class="ai-header-title">Joby</h4>
                    </div>
                </div>
                <div class="ai-header-actions">
                    <button type="button" class="ai-header-btn" id="ai-chat-history-btn" title="Xem lịch sử trò chuyện"><svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>Lịch sử</button>
                    <button type="button" class="ai-header-btn" id="ai-chat-new-btn" title="Bắt đầu cuộc trò chuyện mới"><svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>Mới</button>
                    <button type="button" class="ai-close-btn" id="ai-chat-close" title="Thu gọn">&times;</button>
                </div>
            </div>

            <!-- Khung Lịch Sử Trò Chuyện (History Panel) -->
            <div class="ai-history-panel" id="ai-history-panel">
                <div class="ai-hist-topbar">
                    <button type="button" class="ai-hist-back-btn" id="ai-hist-back-btn">Quay lại</button>
                    <span class="ai-hist-title">Lịch sử trò chuyện</span>
                    <button type="button" class="ai-hist-restore-all" id="ai-hist-restore-all" title="Nạp toàn bộ lịch sử vào màn hình chat">Khôi phục tất cả</button>
                </div>
                <div class="ai-history-list" id="ai-history-list">
                    <div class="ai-hist-empty">Đang tải lịch sử...</div>
                </div>
            </div>

            <div class="ai-chat-messages" id="ai-messages-container">
                <div class="ai-msg ai-msg-bot">
                    <div class="ai-msg-content">${defaultGreeting}</div>
                    <span class="ai-msg-time">${formatMessageTime(new Date())}</span>
                    ${renderSuggestionChips(defaultSuggestions)}
                </div>
                <div class="ai-typing" id="ai-typing-indicator">
                    <span class="ai-typing-dot"></span>
                    <span class="ai-typing-dot"></span>
                    <span class="ai-typing-dot"></span>
                </div>
            </div>

            <div class="ai-chat-footer">
                <input type="text" id="ai-chat-input" class="ai-chat-input" placeholder="Nhập câu hỏi để tiếp tục trò chuyện..." autocomplete="off">
                <button id="ai-chat-send" class="ai-send-btn" title="Gửi">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        `;
        document.body.appendChild(windowDiv);

        // QUẢN LÝ LỊCH SỬ HỘI THOẠI (PERSISTENT CHAT HISTORY)
        const storageKey = 'joblink_ai_history_' + (window.JOBY_USER_ID ? window.JOBY_USER_ID : 'guest');

        function getStoredHistory() {
            try {
                const raw = localStorage.getItem(storageKey);
                return raw ? JSON.parse(raw) : [];
            } catch (e) {
                return [];
            }
        }

        function setStoredHistory(list) {
            try {
                if (list.length > 20) list = list.slice(-20);
                localStorage.setItem(storageKey, JSON.stringify(list));
            } catch (e) {}
        }

        let serverHistoryCache = [];

        function renderFullConversation(items) {
            const container = document.getElementById('ai-messages-container');
            const indicator = document.getElementById('ai-typing-indicator');
            if (!container || !indicator) return;

            // Xóa tin nhắn cũ trong DOM
            const oldMsgs = container.querySelectorAll('.ai-msg, .ai-history-divider');
            oldMsgs.forEach(m => m.remove());

            chatHistory = [];

            if (!items || items.length === 0) {
                // Hiển thị lời chào ban đầu
                const botDiv = document.createElement('div');
                botDiv.className = 'ai-msg ai-msg-bot';
                const nowTime = formatMessageTime(new Date());
                botDiv.innerHTML = `<div class="ai-msg-content">${defaultGreeting}</div><span class="ai-msg-time">${nowTime}</span>` + renderSuggestionChips(defaultSuggestions);
                container.insertBefore(botDiv, indicator);
                return;
            }

            // Có lịch sử hội thoại trước đó: Thêm thanh ngăn cách trực quan
            const divDivider = document.createElement('div');
            divDivider.className = 'ai-history-divider';
            divDivider.innerHTML = '<span><svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> Lịch sử trò chuyện</span>';
            container.insertBefore(divDivider, indicator);

            items.forEach((item, idx) => {
                const isLast = (idx === items.length - 1);
                const userText = item.user_message || item.user || '';
                const botText = item.ai_reply || item.reply || '';
                const suggestions = item.suggestions || [];
                const timeStr = formatMessageTime(item.created_at || item.time || new Date());

                if (userText) {
                    const uDiv = document.createElement('div');
                    uDiv.className = 'ai-msg ai-msg-user';
                    uDiv.innerHTML = `<div class="ai-msg-content">${escapeHtml(userText)}</div><span class="ai-msg-time">${timeStr}</span>`;
                    container.insertBefore(uDiv, indicator);
                    chatHistory.push({ role: 'user', content: userText });
                }

                if (botText) {
                    const bDiv = document.createElement('div');
                    bDiv.className = 'ai-msg ai-msg-bot';
                    let html = `<div class="ai-msg-content">${parseMarkdown(botText)}</div><span class="ai-msg-time">${timeStr}</span>`;
                    if (isLast && Array.isArray(suggestions) && suggestions.length > 0) {
                        html += renderSuggestionChips(suggestions);
                    }
                    bDiv.innerHTML = html;
                    if (bDiv.querySelector('.ai-card-frame')) {
                        bDiv.classList.add('ai-msg-has-frame');
                    }
                    container.insertBefore(bDiv, indicator);
                    chatHistory.push({ role: 'assistant', content: botText });
                }
            });

            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 60);
        }

        function initHistory() {
            const localItems = getStoredHistory();
            if (localItems && localItems.length > 0) {
                serverHistoryCache = localItems;
                renderFullConversation(localItems);
                return;
            }

            let baseUrl = (typeof window.APP_URL !== 'undefined' ? window.APP_URL : '');
            if (!baseUrl) {
                const segments = window.location.pathname.split('/').filter(Boolean);
                if (segments.length > 0 && segments[0].toLowerCase() === 'joblink') {
                    baseUrl = '/' + segments[0];
                }
            }
            const historyUrl = baseUrl ? (baseUrl.replace(/\/+$/, '') + '/api/ai/chat/history') : '/api/ai/chat/history';

            fetch(historyUrl)
                .then(res => res.json())
                .then(data => {
                    if (data && data.success && Array.isArray(data.history) && data.history.length > 0) {
                        serverHistoryCache = data.history;
                        setStoredHistory(data.history);
                        renderFullConversation(data.history);
                    } else {
                        serverHistoryCache = [];
                        renderFullConversation([]);
                    }
                })
                .catch(() => {
                    serverHistoryCache = [];
                    renderFullConversation([]);
                });
        }

        // Tải lại lịch sử hội thoại khi widget khởi chạy
        initHistory();

        // 3. Event Listeners & Auto-dismiss Timer
        const btnToggle = document.getElementById('ai-chat-btn-toggle');
        const badgeToggle = document.getElementById('ai-chat-badge-toggle');
        const chatHeader = windowDiv.querySelector('.ai-chat-header');
        const inputField = document.getElementById('ai-chat-input');
        const btnSend = document.getElementById('ai-chat-send');
        const btnNewChat = document.getElementById('ai-chat-new-btn');
        const btnHistory = document.getElementById('ai-chat-history-btn');
        const historyPanel = document.getElementById('ai-history-panel');
        const btnHistBack = document.getElementById('ai-hist-back-btn');
        const btnHistRestoreAll = document.getElementById('ai-hist-restore-all');
        const historyListEl = document.getElementById('ai-history-list');
        const bubbleClose = document.getElementById('ai-bubble-close-btn');
        const bubbleChatNow = document.getElementById('ai-bubble-chat-now');
        const btnClose = document.getElementById('ai-chat-close');

        // Logic Mở / Đóng Khung Lịch Sử Trò Chuyện
        function toggleHistoryPanel(show) {
            if (!historyPanel) return;
            const willOpen = (typeof show === 'boolean') ? show : !historyPanel.classList.contains('open');
            if (willOpen) {
                historyPanel.classList.add('open');
                loadHistoryData();
            } else {
                historyPanel.classList.remove('open');
            }
        }

        function loadHistoryData() {
            if (!historyListEl) return;
            historyListEl.innerHTML = '<div class="ai-hist-empty">⏳ Đang tải lịch sử trò chuyện...</div>';

            let baseUrl = (typeof window.APP_URL !== 'undefined' ? window.APP_URL : '');
            if (!baseUrl) {
                const segments = window.location.pathname.split('/').filter(Boolean);
                if (segments.length > 0 && segments[0].toLowerCase() === 'joblink') {
                    baseUrl = '/' + segments[0];
                }
            }
            const historyUrl = baseUrl ? (baseUrl.replace(/\/+$/, '') + '/api/ai/chat/history') : '/api/ai/chat/history';

            fetch(historyUrl)
                .then(res => res.json())
                .then(data => {
                    let items = [];
                    if (data && data.success && Array.isArray(data.history) && data.history.length > 0) {
                        items = data.history;
                    } else {
                        items = getStoredHistory();
                    }
                    serverHistoryCache = items;
                    renderHistoryList(items);
                })
                .catch(() => {
                    const localItems = getStoredHistory();
                    serverHistoryCache = localItems;
                    renderHistoryList(localItems);
                });
        }

        function renderHistoryList(items) {
            if (!historyListEl) return;
            if (!items || items.length === 0) {
                historyListEl.innerHTML = `
                    <div class="ai-hist-empty">
                        <div style="font-size:2.2rem;margin-bottom:8px;color:#0d9488;"><svg class="icon-svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg></div>
                        Chưa có lịch sử trò chuyện nào.<br>
                        Gửi câu hỏi để bắt đầu trò chuyện cùng Joby nhé!
                    </div>
                `;
                return;
            }

            let html = '';
            // Hiển thị tin mới nhất ở trên cùng
            const reversed = items.slice().reverse();
            reversed.forEach((item, idx) => {
                const realIdx = items.length - 1 - idx;
                const userMsg = item.user_message || item.user || '';
                let rawReply = (item.ai_reply || item.reply || '').replace(/<[^>]+>/g, '').trim();
                if (rawReply.length > 90) rawReply = rawReply.substring(0, 90) + '...';
                const timeStr = item.formatted_time || formatMessageTime(item.created_at || item.time);

                html += `
                    <div class="ai-history-item" data-index="${realIdx}" title="Bấm để mở cuộc trò chuyện này">
                        <div class="ai-hist-item-user">
                            <span><svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg></span>
                            <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(userMsg)}</div>
                        </div>
                        <div class="ai-hist-item-reply">${escapeHtml(rawReply || 'Đã phản hồi')}</div>
                        <div class="ai-hist-item-footer">
                            <span><svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>${timeStr}</span>
                            <span class="ai-hist-item-action">Xem tin nhắn</span>
                        </div>
                    </div>
                `;
            });
            historyListEl.innerHTML = html;
        }

        if (btnHistory) {
            btnHistory.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleHistoryPanel();
            });
        }

        if (btnHistBack) {
            btnHistBack.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleHistoryPanel(false);
            });
        }

        if (btnHistRestoreAll) {
            btnHistRestoreAll.addEventListener('click', (e) => {
                e.stopPropagation();
                if (serverHistoryCache && serverHistoryCache.length > 0) {
                    setStoredHistory(serverHistoryCache);
                    renderFullConversation(serverHistoryCache);
                } else {
                    initHistory();
                }
                toggleHistoryPanel(false);
            });
        }

        if (historyListEl) {
            historyListEl.addEventListener('click', (e) => {
                const itemEl = e.target.closest('.ai-history-item');
                if (!itemEl) return;
                const idx = parseInt(itemEl.getAttribute('data-index'), 10);
                if (!isNaN(idx) && serverHistoryCache && serverHistoryCache.length > 0) {
                    setStoredHistory(serverHistoryCache);
                    renderFullConversation(serverHistoryCache);
                    toggleHistoryPanel(false);
                }
            });
        }

        let dismissSecondsLeft = 6;
        let dismissInterval = null;
        let isHovered = false;

        function startAutoDismiss() {
            stopAutoDismiss();
            dismissSecondsLeft = 6;
            updateCountdownUi();

            const pBar = document.getElementById('ai-bubble-progress-bar');
            if (pBar) {
                pBar.style.animation = 'none';
                void pBar.offsetWidth; // trigger reflow
                pBar.style.animation = 'ai-progress-shrink 6s linear forwards';
            }

            dismissInterval = setInterval(() => {
                if (!isHovered) {
                    dismissSecondsLeft--;
                    updateCountdownUi();
                    if (dismissSecondsLeft <= 0) {
                        stopAutoDismiss();
                        dismissBubble();
                    }
                }
            }, 1000);
        }

        function stopAutoDismiss() {
            if (dismissInterval) {
                clearInterval(dismissInterval);
                dismissInterval = null;
            }
        }

        function updateCountdownUi() {
            const el = document.getElementById('ai-bubble-countdown-sec');
            if (el) {
                el.textContent = Math.max(0, dismissSecondsLeft);
            }
        }

        function dismissBubble() {
            stopAutoDismiss();
            bubbleDiv.classList.remove('visible');
            sessionStorage.setItem('joby_bubble_dismissed', '1');
        }

        // QUẢN LÝ VỊ TRÍ & KÉO THẢ LAUNCHER (DRAGGABLE VERTICAL LAUNCHER)
        let isDragging = false;
        let dragStartY = 0;
        let initialTop = 0;
        let hasMoved = false;
        let suppressClickUntil = 0;

        function updateBubblePosition(launcherTop) {
            if (!bubbleDiv) return;
            const bubbleHeight = bubbleDiv.offsetHeight || 135;
            const launcherH = launcher.offsetHeight || 60;
            // Nếu launcher ở sát đỉnh màn hình (< bubbleHeight + 20), hiển thị bubble ở DƯỚI launcher
            if (launcherTop < bubbleHeight + 20) {
                bubbleDiv.style.top = (launcherTop + launcherH + 12) + 'px';
                bubbleDiv.style.bottom = 'auto';
                bubbleDiv.classList.add('ai-bubble-below');
            } else {
                // Mặc định hiển thị bubble ở TRÊN launcher
                bubbleDiv.style.top = (launcherTop - bubbleHeight - 12) + 'px';
                bubbleDiv.style.bottom = 'auto';
                bubbleDiv.classList.remove('ai-bubble-below');
            }
        }

        function updateWindowPosition() {
            if (!windowDiv || !isOpen) return;
            const rect = launcher.getBoundingClientRect();
            const launcherTop = rect.top;
            const launcherH = rect.height || 60;
            const vh = window.innerHeight;
            const windowH = windowDiv.offsetHeight || 540;

            if (launcherTop >= windowH + 16) {
                // Đủ khoảng trống: Đặt phía trên launcher
                windowDiv.style.top = (launcherTop - windowH - 12) + 'px';
                windowDiv.style.bottom = 'auto';
            } else if (vh - (launcherTop + launcherH) >= windowH + 16) {
                // Đặt phía dưới launcher
                windowDiv.style.top = (launcherTop + launcherH + 12) + 'px';
                windowDiv.style.bottom = 'auto';
            } else {
                // Đặt ở vị trí vừa vặn màn hình, không tràn lên đỉnh hay xuống đáy
                const maxTop = Math.max(16, vh - windowH - 16);
                const idealTop = Math.max(16, Math.min(maxTop, launcherTop - (windowH / 2)));
                windowDiv.style.top = idealTop + 'px';
                windowDiv.style.bottom = 'auto';
            }
        }

        function applyLauncherTop(topPx, save = true) {
            const isMobile = (window.innerWidth <= 900);
            const launcherH = launcher.offsetHeight || (isMobile ? 50 : 58);
            const minTop = isMobile ? 60 : 16;
            const bottomMargin = isMobile ? 76 : 24;
            const maxTop = Math.max(minTop, window.innerHeight - launcherH - bottomMargin);
            const clamped = Math.max(minTop, Math.min(maxTop, topPx));

            launcher.style.setProperty('top', clamped + 'px', 'important');
            launcher.style.setProperty('bottom', 'auto', 'important');
            updateBubblePosition(clamped);
            if (isOpen) {
                updateWindowPosition();
            }

            if (save) {
                const totalRange = window.innerHeight - launcherH - minTop - bottomMargin;
                const ratio = totalRange > 0 ? ((clamped - minTop) / totalRange) : 0.85;
                try {
                    localStorage.setItem('joby_launcher_ratio', ratio.toFixed(4));
                } catch (e) {}
            }
        }

        function restoreLauncherPosition() {
            const isMobile = (window.innerWidth <= 900);
            const launcherH = launcher.offsetHeight || (isMobile ? 50 : 58);
            const minTop = isMobile ? 60 : 16;
            const bottomMargin = isMobile ? 76 : 24;
            try {
                const savedRatio = localStorage.getItem('joby_launcher_ratio');
                if (savedRatio !== null) {
                    const ratio = parseFloat(savedRatio);
                    if (!isNaN(ratio) && ratio >= 0 && ratio <= 1) {
                        const totalRange = window.innerHeight - launcherH - minTop - bottomMargin;
                        const targetTop = minTop + (ratio * totalRange);
                        applyLauncherTop(targetTop, false);
                        return;
                    }
                }
            } catch (e) {}
            const defaultTop = window.innerHeight - launcherH - bottomMargin;
            applyLauncherTop(defaultTop, false);
        }

        setTimeout(restoreLauncherPosition, 50);

        window.addEventListener('resize', () => {
            restoreLauncherPosition();
            if (isOpen) updateWindowPosition();
        });

        // Xử lý kéo thả dọc trực tiếp bằng Avatar (Mouse & Touch)
        function onDragStart(clientY) {
            isDragging = true;
            hasMoved = false;
            dragStartY = clientY;
            const rect = launcher.getBoundingClientRect();
            initialTop = rect.top;
        }

        function onDragMove(clientY, e) {
            if (!isDragging) return;
            const deltaY = clientY - dragStartY;
            if (Math.abs(deltaY) > 5) {
                hasMoved = true;
                launcher.classList.add('is-dragging');
                if (e && e.cancelable) e.preventDefault();
                applyLauncherTop(initialTop + deltaY, false);
            }
        }

        function onDragEnd() {
            if (!isDragging) return;
            isDragging = false;
            launcher.classList.remove('is-dragging');
            if (hasMoved) {
                suppressClickUntil = Date.now() + 400;
                const rect = launcher.getBoundingClientRect();
                applyLauncherTop(rect.top, true);
            }
        }

        launcher.addEventListener('mousedown', function (e) {
            if (e.button !== 0) return;
            onDragStart(e.clientY);

            function handleMouseMove(ev) {
                onDragMove(ev.clientY, ev);
            }

            function handleMouseUp() {
                onDragEnd();
                window.removeEventListener('mousemove', handleMouseMove);
                window.removeEventListener('mouseup', handleMouseUp);
            }

            window.addEventListener('mousemove', handleMouseMove);
            window.addEventListener('mouseup', handleMouseUp);
        });

        launcher.addEventListener('touchstart', function (e) {
            if (e.touches && e.touches.length > 0) {
                onDragStart(e.touches[0].clientY);
            }
        }, { passive: true });

        launcher.addEventListener('touchmove', function (e) {
            if (e.touches && e.touches.length > 0) {
                onDragMove(e.touches[0].clientY, e);
            }
        }, { passive: false });

        launcher.addEventListener('touchend', function () {
            onDragEnd();
        });

        launcher.addEventListener('touchcancel', function () {
            onDragEnd();
        });

        function toggleWindow(show) {
            if (typeof show === 'boolean') {
                isOpen = show;
            } else {
                isOpen = !isOpen;
            }
            if (isOpen) {
                windowDiv.classList.add('open');
                dismissBubble();
                updateWindowPosition();
                const container = document.getElementById('ai-messages-container');
                if (container) {
                    setTimeout(() => { container.scrollTop = container.scrollHeight; }, 60);
                }
                inputField.focus();
            } else {
                windowDiv.classList.remove('open');
                toggleHistoryPanel(false);
                if (inputField) {
                    inputField.blur();
                }
            }
        }

        if (btnNewChat) {
            btnNewChat.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleHistoryPanel(false);
                localStorage.removeItem(storageKey);
                chatHistory = [];
                renderFullConversation([]);
                inputField.placeholder = "Nhập câu hỏi để bắt đầu cuộc trò chuyện mới...";
                inputField.focus();

                let baseUrl = (typeof window.APP_URL !== 'undefined' ? window.APP_URL : '');
                if (!baseUrl) {
                    const segments = window.location.pathname.split('/').filter(Boolean);
                    if (segments.length > 0 && segments[0].toLowerCase() === 'joblink') {
                        baseUrl = '/' + segments[0];
                    }
                }
                const clearUrl = baseUrl ? (baseUrl.replace(/\/+$/, '') + '/api/ai/chat/clear') : '/api/ai/chat/clear';
                fetch(clearUrl, { method: 'POST' }).catch(() => {});
            });
        }

        // Tự động hiển thị bong bóng thông báo chào đón sau 1s nếu chưa tắt
        setTimeout(() => {
            if (!isOpen && !sessionStorage.getItem('joby_bubble_dismissed')) {
                const rect = launcher.getBoundingClientRect();
                updateBubblePosition(rect.top);
                bubbleDiv.classList.add('visible');
                startAutoDismiss();
            }
        }, 1000);

        bubbleDiv.addEventListener('mouseenter', () => {
            isHovered = true;
            const pBar = document.getElementById('ai-bubble-progress-bar');
            if (pBar) pBar.style.animationPlayState = 'paused';
        });

        bubbleDiv.addEventListener('mouseleave', () => {
            isHovered = false;
            const pBar = document.getElementById('ai-bubble-progress-bar');
            if (pBar) pBar.style.animationPlayState = 'running';
            if (dismissSecondsLeft < 3) {
                dismissSecondsLeft = 3;
                updateCountdownUi();
            }
        });

        if (bubbleClose) {
            bubbleClose.addEventListener('click', (e) => {
                e.stopPropagation();
                dismissBubble();
            });
        }

        if (bubbleChatNow) {
            bubbleChatNow.addEventListener('click', (e) => {
                e.stopPropagation();
                dismissBubble();
                if (!isOpen) toggleWindow();
            });
        }

        bubbleDiv.addEventListener('click', (e) => {
            const statCard = e.target.closest('.ai-stat-card');
            if (statCard) {
                e.stopPropagation();
                const q = statCard.getAttribute('data-query');
                dismissBubble();
                if (!isOpen) toggleWindow();
                if (q) {
                    inputField.value = q;
                    handleSendMessage();
                }
                return;
            }

            if (e.target !== bubbleClose && !e.target.closest('#ai-bubble-close-btn')) {
                e.stopPropagation();
                dismissBubble();
                if (!isOpen) toggleWindow();
            }
        });

        function handleToggleClick(e) {
            if (Date.now() < suppressClickUntil) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                return;
            }
            toggleWindow();
        }

        btnToggle.addEventListener('click', handleToggleClick);
        badgeToggle.addEventListener('click', handleToggleClick);

        if (btnClose) {
            btnClose.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleWindow(false);
            });
        }

        chatHeader.addEventListener('click', (e) => {
            if (e.target.closest('#ai-chat-history-btn, #ai-chat-new-btn, #ai-chat-close')) {
                return;
            }
            toggleWindow();
        });

        // Tự động thu nhỏ chat box Joby khi thao tác bấm ra bên ngoài
        let isPointerDownInside = false;

        document.addEventListener('pointerdown', function (e) {
            if (!isOpen) return;
            const path = (typeof e.composedPath === 'function') ? e.composedPath() : null;
            isPointerDownInside = path
                ? (path.includes(windowDiv) || path.includes(launcher) || (bubbleDiv && path.includes(bubbleDiv)))
                : (windowDiv.contains(e.target) || launcher.contains(e.target) || (bubbleDiv && bubbleDiv.contains(e.target)));
        }, true);

        document.addEventListener('click', function (e) {
            if (!isOpen) return;
            if (Date.now() < suppressClickUntil) return;

            const path = (typeof e.composedPath === 'function') ? e.composedPath() : null;
            const isClickInside = path
                ? (path.includes(windowDiv) || path.includes(launcher) || (bubbleDiv && path.includes(bubbleDiv)))
                : (windowDiv.contains(e.target) || launcher.contains(e.target) || (bubbleDiv && bubbleDiv.contains(e.target)));

            const wasStartedInside = isPointerDownInside;
            isPointerDownInside = false;

            // Nếu click hoặc thao tác bắt đầu từ bên trong chat box / launcher / bubble thì bỏ qua
            if (wasStartedInside || isClickInside) {
                return;
            }

            // Thao tác bên ngoài -> Tự động thu nhỏ về launcher
            toggleWindow(false);
        }, true);

        document.addEventListener('pointercancel', function () {
            isPointerDownInside = false;
        }, true);

        // Thu nhỏ chat box khi người dùng nhấn phím ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) {
                toggleWindow(false);
            }
        });

        btnSend.addEventListener('click', handleSendMessage);
        inputField.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSendMessage();
            }
        });

        // Handle suggestion chips, Quick Actions & Visual Cards
        windowDiv.addEventListener('click', function (e) {
            // 1. Xử lý click vào thẻ trực quan trong khung (Cả thẻ vuông .ai-grid-card và thẻ ngang .ai-list-card)
            const card = e.target.closest('.ai-grid-card, .ai-list-card');
            if (card) {
                const actionBtn = e.target.closest('.ai-card-action-btn, .ai-card-action-pill, .ai-card-arrow-btn');
                const jobId = card.getAttribute('data-job-id');
                const empId = card.getAttribute('data-employer-id');
                const actionUrl = card.getAttribute('data-action-url');
                const query = card.getAttribute('data-query');

                let baseUrl = (typeof window.APP_URL !== 'undefined' ? window.APP_URL : '');
                if (!baseUrl) {
                    const segments = window.location.pathname.split('/').filter(Boolean);
                    if (segments.length > 0 && segments[0].toLowerCase() === 'joblink') {
                        baseUrl = '/' + segments[0];
                    }
                }

                // Nếu click vào nút action hoặc có actionUrl/jobId/empId thì chuyển trang
                if (actionBtn && actionUrl) {
                    window.location.href = baseUrl + actionUrl;
                    return;
                }
                if (actionBtn && jobId && parseInt(jobId, 10) > 0) {
                    window.open(baseUrl + '/jobs/' + jobId, '_blank');
                    return;
                }
                if (actionBtn && empId && parseInt(empId, 10) > 0) {
                    window.open(baseUrl + '/employers/' + empId, '_blank');
                    return;
                }

                // Nếu click vào thân thẻ -> tự động hỏi Joby AI
                if (query) {
                    inputField.value = query;
                    handleSendMessage();
                    return;
                }
            }

            // 2. Xử lý click vào gợi ý (Chips)
            const chip = e.target.closest('.ai-chip');
            if (!chip) return;

            const action = chip.getAttribute('data-action');
            const query = chip.getAttribute('data-query');

            if (action === 'login') {
                window.location.href = window.JOBY_LOGIN_URL || '/auth/login';
                return;
            }
            if (action === 'register') {
                window.location.href = window.JOBY_REGISTER_URL || '/auth/register';
                return;
            }
            if (action === 'jobs') {
                window.location.href = window.JOBY_JOBS_URL || '/jobs';
                return;
            }

            if (query) {
                inputField.value = query;
                handleSendMessage();
            }
        });
    }

    /**
     * Tự động đọc và trích xuất ngữ cảnh tab và màn hình đang mở trong Dashboard (hỗ trợ cả Tab mẹ Root và các Tab con MasterWorkspace)
     */
    function getScreenContext() {
        try {
            // 0. Kiểm tra xem người dùng đang đứng ở Tab con nào trong MasterWorkspace Dashboard
            let isSubTab = false;
            let activeTabKey = 'root';
            let activeTabTitle = '';
            let activeTabUrl = '';
            let targetDoc = document;

            // Kiểm tra MasterWorkspace hoặc active chrome tab element
            if (window.MasterWorkspace) {
                activeTabKey = window.MasterWorkspace.currentActiveTab || 'root';
                if (activeTabKey !== 'root') {
                    isSubTab = true;
                    const tabData = window.MasterWorkspace.openedTabs ? window.MasterWorkspace.openedTabs.get(activeTabKey) : null;
                    if (tabData) {
                        activeTabTitle = tabData.title || '';
                        activeTabUrl = tabData.url || '';
                        if (tabData.iframeEl) {
                            try {
                                const iframeDoc = tabData.iframeEl.contentDocument || tabData.iframeEl.contentWindow?.document;
                                if (iframeDoc && iframeDoc.body && iframeDoc.body.innerHTML.trim().length > 50) {
                                    targetDoc = iframeDoc;
                                }
                            } catch (e) {
                                // cross-origin fallback nếu có
                            }
                        }
                    }
                }
            }

            // Nếu không có tabData qua object, kiểm tra trực tiếp qua DOM của Chrome Tab Strip
            if (!isSubTab) {
                const activeChromeTab = document.querySelector('#masterChromeTabsStrip .chrome-tab.active:not(#mtab-root)');
                if (activeChromeTab) {
                    isSubTab = true;
                    activeTabTitle = activeChromeTab.querySelector('.chrome-tab-title')?.innerText?.trim() || activeChromeTab.getAttribute('data-title') || '';
                    const activePanel = document.querySelector('.master-tab-panel.active');
                    if (activePanel) {
                        const iframe = activePanel.querySelector('iframe');
                        if (iframe) {
                            try {
                                activeTabUrl = iframe.src || '';
                                const iframeDoc = iframe.contentDocument || iframe.contentWindow?.document;
                                if (iframeDoc && iframeDoc.body && iframeDoc.body.innerHTML.trim().length > 50) {
                                    targetDoc = iframeDoc;
                                }
                            } catch (e) {}
                        }
                    }
                }
            }

            // Lấy URL và Title tương ứng
            let resolvedUrl = activeTabUrl || window.location.pathname;
            let resolvedTitle = activeTabTitle || (targetDoc.title || document.title || '').replace(/\s*[-–|].*$/, '').trim();

            // Nếu là trang gốc root dashboard
            if (!isSubTab) {
                resolvedTitle = 'Bảng Điều Khiển / Trang Chủ';
                const rootPanel = document.getElementById('mpanel-root');
                if (rootPanel) targetDoc = rootPanel;
            }

            // 1. Tiêu đề các khối chính trên trang/tab (h1, h2, h3, class tiêu đề)
            const headings = [];
            targetDoc.querySelectorAll('h1, h2, h3, .dashboard-title, .page-header-title, .section-title, .card-title, .page-title').forEach(el => {
                if (el.closest('#ai-chat-window, #ai-chat-launcher')) return;
                const txt = el.innerText.trim();
                if (txt && !headings.includes(txt) && txt.length < 100) {
                    headings.push(txt);
                }
            });

            // 2. Các chỉ số thống kê trên màn hình
            const stats = [];
            targetDoc.querySelectorAll('.stat-card, .metric-card, .stat-badge, [class*="stat-"], [class*="metric-"]').forEach(el => {
                if (el.closest('#ai-chat-window, #ai-chat-launcher')) return;
                const txt = el.innerText.replace(/\s+/g, ' ').trim();
                if (txt && txt.length < 80 && !stats.includes(txt)) {
                    stats.push(txt);
                }
            });

            // 3. Khối thông báo nổi bật (Banner trúng tuyển, Cảnh báo chưa lưu, AI Matching Alert, Empty State...)
            const highlights = [];
            targetDoc.querySelectorAll('.alert, .banner, .announcement, .congrats-card, [class*="congrat"], [class*="notification"], [class*="unsaved"], [class*="empty"]').forEach(el => {
                if (el.closest('#ai-chat-window, #ai-chat-launcher')) return;
                const txt = el.innerText.replace(/\s+/g, ' ').trim();
                if (txt && txt.length < 200 && !highlights.includes(txt)) {
                    highlights.push(txt);
                }
            });

            // 4. Các bộ lọc hoặc ô nhập liệu (inputs, selects, placeholder)
            const formFilters = [];
            targetDoc.querySelectorAll('input, select, textarea').forEach(el => {
                if (el.closest('#ai-chat-window, #ai-chat-launcher')) return;
                if (el.type === 'hidden') return;
                const label = el.closest('.form-group, .filter-item, div')?.querySelector('label')?.innerText?.trim() || el.getAttribute('placeholder') || el.name || '';
                const val = el.value?.trim() || '';
                const desc = label + (val ? ': ' + val : '');
                if (desc && desc.length < 60 && !formFilters.includes(desc)) {
                    formFilters.push(desc);
                }
            });

            // 5. Các nút thao tác chính trên màn hình (để hướng dẫn người dùng bấm vào đâu)
            const actionButtons = [];
            targetDoc.querySelectorAll('button, a.btn, a[class*="btn"], .action-btn, input[type="submit"]').forEach(el => {
                if (el.closest('#ai-chat-window, #ai-chat-launcher')) return;
                const txt = (el.innerText || el.value || '').trim();
                if (txt && txt.length < 40 && !actionButtons.includes(txt)) {
                    actionButtons.push(txt);
                }
            });

            // 6. Thẻ hoặc danh sách đang hiển thị
            const visibleItems = [];
            targetDoc.querySelectorAll('.job-card, .applicant-card, .application-item, [class*="card-name"]').forEach(el => {
                if (el.closest('#ai-chat-window, #ai-chat-launcher')) return;
                const txt = el.innerText.replace(/\s+/g, ' ').trim();
                if (txt && txt.length < 100 && !visibleItems.includes(txt)) {
                    visibleItems.push(txt);
                }
            });

            return {
                is_sub_tab: isSubTab,
                active_tab_key: activeTabKey,
                active_tab_title: activeTabTitle,
                url: resolvedUrl,
                pathname: resolvedUrl.split('?')[0].replace(window.location.origin, ''),
                title: resolvedTitle,
                headings: headings.slice(0, 6),
                stats: stats.slice(0, 8),
                highlights: highlights.slice(0, 4),
                formFilters: formFilters.slice(0, 6),
                actionButtons: actionButtons.slice(0, 10),
                visibleItems: visibleItems.slice(0, 6)
            };
        } catch (e) {
            return {
                url: window.location.pathname,
                title: document.title
            };
        }
    }

    function handleSendMessage() {
        const input = document.getElementById('ai-chat-input');
        const container = document.getElementById('ai-messages-container');
        const indicator = document.getElementById('ai-typing-indicator');
        const text = input.value.trim();

        if (!text) return;

        // Append User Message
        const nowTime = formatMessageTime(new Date());
        const userDiv = document.createElement('div');
        userDiv.className = 'ai-msg ai-msg-user';
        userDiv.innerHTML = `<div class="ai-msg-content">${escapeHtml(text)}</div><span class="ai-msg-time">${nowTime}</span>`;
        container.insertBefore(userDiv, indicator);

        input.value = '';
        indicator.style.display = 'flex';

        // 1. Khi người dùng gửi câu hỏi: Nếu câu hỏi dài thì cuộn vừa đủ hiện ra dòng cuối & indicator
        setTimeout(() => {
            const targetUserScroll = (userDiv.offsetTop + userDiv.offsetHeight + indicator.offsetHeight + 16) - container.clientHeight;
            container.scrollTo({
                top: Math.max(0, targetUserScroll),
                behavior: 'smooth'
            });
        }, 20);

        chatHistory.push({ role: 'user', content: text });

        // Thu thập ngữ cảnh màn hình đang mở
        const screenContext = getScreenContext();

        // Gửi tới API backend
        let baseUrl = (typeof window.APP_URL !== 'undefined' ? window.APP_URL : '');
        if (!baseUrl) {
            const pathname = window.location.pathname;
            const segments = pathname.split('/').filter(Boolean);
            if (segments.length > 0 && segments[0].toLowerCase() === 'joblink') {
                baseUrl = '/' + segments[0];
            }
        }
        const apiUrl = baseUrl ? (baseUrl.replace(/\/+$/, '') + '/api/ai/chat') : '/api/ai/chat';

        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                message: text,
                history: chatHistory,
                page_context: screenContext
            })
        })
            .then(res => res.json())
            .then(data => {
                indicator.style.display = 'none';

                const botDiv = document.createElement('div');
                botDiv.className = 'ai-msg ai-msg-bot';

                const botTime = formatMessageTime(new Date());
                let replyHtml = `<div class="ai-msg-content">${parseMarkdown(data.reply || 'Mình có thể hỗ trợ gì thêm cho bạn không?')}</div><span class="ai-msg-time">${botTime}</span>`;

                // Thêm các gợi ý hành động tiếp theo
                if (data.suggestions && Array.isArray(data.suggestions) && data.suggestions.length > 0) {
                    replyHtml += renderSuggestionChips(data.suggestions);
                }

                botDiv.innerHTML = replyHtml;
                if (botDiv.querySelector('.ai-card-frame')) {
                    botDiv.classList.add('ai-msg-has-frame');
                }
                container.insertBefore(botDiv, indicator);

                // 2. Khi AI trả lời: Cuộn để nội dung câu hỏi của người dùng nằm ngay sát đỉnh trên cùng
                setTimeout(() => {
                    const targetBotScroll = userDiv.offsetTop - 12;
                    container.scrollTo({
                        top: Math.max(0, targetBotScroll),
                        behavior: 'smooth'
                    });
                }, 40);

                chatHistory.push({ role: 'assistant', content: data.reply });

                // Lưu vào lịch sử cục bộ để khi mở lại Joby vẫn còn nội dung cũ
                try {
                    const stored = getStoredHistory();
                    stored.push({
                        user_message: text,
                        ai_reply: data.reply,
                        suggestions: data.suggestions || [],
                        created_at: new Date().toISOString(),
                        time: botTime
                    });
                    setStoredHistory(stored);
                } catch (e) {}
            })
            .catch(err => {
                indicator.style.display = 'none';
                const botDiv = document.createElement('div');
                botDiv.className = 'ai-msg ai-msg-bot';
                const errTime = formatMessageTime(new Date());
                botDiv.innerHTML = `<div class="ai-msg-content">Hệ thống đang xử lý câu hỏi. Bạn vui lòng bấm vào các gợi ý bên dưới hoặc thử lại sau giây lát nhé:</div><span class="ai-msg-time">${errTime}</span>` +
                    renderSuggestionChips(['Tìm việc làm mới nhất', 'Chữ ký số doanh nghiệp là gì?', 'Đăng nhập ngay']);
                container.insertBefore(botDiv, indicator);

                setTimeout(() => {
                    const targetBotScroll = userDiv.offsetTop - 12;
                    container.scrollTo({
                        top: Math.max(0, targetBotScroll),
                        behavior: 'smooth'
                    });
                }, 40);
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChatWidget);
    } else {
        initChatWidget();
    }
})();
