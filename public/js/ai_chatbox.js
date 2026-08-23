/**
 * public/js/ai_chatbox.js - JobLink AI Assistant Chat Widget Client
 */

(function () {
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
            .replace(/^\s*\d+\.\s+(.*$)/gim, '<li>$1</li>');

        // Bọc các thẻ li liên tiếp vào ul
        html = html.replace(/(<li>.*?<\/li>)+/gis, function (match) {
            return '<ul style="margin:6px 0;padding-left:20px;">' + match + '</ul>';
        });

        // Thay đổi xuống dòng đơn lẻ thành <br> nếu không thuộc danh sách
        return html.replace(/\n\n/g, '<br><br>').replace(/\n/g, '<br>');
    }

    function initChatWidget() {
        if (document.getElementById('joblink-ai-launcher')) return;

        // 1. Tạo nút Floating Launcher
        const launcher = document.createElement('div');
        launcher.id = 'joblink-ai-launcher';
        launcher.className = 'ai-chat-launcher';
        launcher.innerHTML = `
            <div class="ai-chat-badge" id="ai-chat-badge-toggle">
                <span>✨ Joby AI</span>
            </div>
            <button class="ai-chat-btn" id="ai-chat-btn-toggle" title="Trò chuyện cùng Joby AI">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="12" x="3" y="6" rx="2"></rect>
                    <circle cx="9" cy="12" r="1" fill="currentColor"></circle>
                    <circle cx="15" cy="12" r="1" fill="currentColor"></circle>
                    <path d="M12 2v4"></path><path d="M2 12h1"></path><path d="M21 12h1"></path>
                </svg>
            </button>
        `;
        document.body.appendChild(launcher);

        const defaultGreeting = (typeof window.JOBY_GREETING !== 'undefined' && window.JOBY_GREETING)
            ? window.JOBY_GREETING
            : "Dạ chào bạn, em là Joby! Em có thể giúp gì cho bạn hôm nay ạ? ✨";

        // 1.5. Tạo Bong bóng Thông báo Chủ động (Proactive Speech Bubble)
        const bubbleDiv = document.createElement('div');
        bubbleDiv.id = 'joblink-ai-proactive-bubble';
        bubbleDiv.className = 'ai-proactive-bubble';
        bubbleDiv.innerHTML = `
            <div class="ai-bubble-header">
                <div class="ai-bubble-title">
                    <span class="ai-bubble-dot"></span>
                    <strong>✨ Joby AI Thông Báo</strong>
                </div>
                <button type="button" class="ai-bubble-close" id="ai-bubble-close-btn" title="Đóng">&times;</button>
            </div>
            <div class="ai-bubble-body">
                ${defaultGreeting}
            </div>
            <div class="ai-bubble-actions">
                <button type="button" class="ai-bubble-btn" id="ai-bubble-chat-now">Trò chuyện ngay &rarr;</button>
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
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="12" x="3" y="6" rx="2"></rect>
                            <circle cx="9" cy="12" r="1" fill="currentColor"></circle>
                            <circle cx="15" cy="12" r="1" fill="currentColor"></circle>
                            <path d="M12 2v4"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="ai-header-title">Joby AI</h4>
                        <div class="ai-header-status">
                            <span class="ai-status-dot"></span>
                            <span>Trực tuyến 24/7 &bull; Trợ lý thông minh (Bấm thu gọn ▾)</span>
                        </div>
                    </div>
                </div>
                <button class="ai-close-btn" id="ai-chat-close" title="Thu gọn">&times;</button>
            </div>

            <div class="ai-chat-messages" id="ai-messages-container">
                <div class="ai-msg ai-msg-bot">
                    ${defaultGreeting}
                    <div class="ai-suggestions" style="margin-top: 10px;">
                        <span class="ai-chip" data-query="Tìm việc làm mới nhất">🔍 Tìm việc làm</span>
                        <span class="ai-chip" data-query="Xác thực chữ ký số DN là gì?">🔐 Chữ Ký Số DN</span>
                        <span class="ai-chip" data-query="Quy định điểm uy tín người lao động">🛡️ Điểm Uy Tín</span>
                    </div>
                </div>
                <div class="ai-typing" id="ai-typing-indicator">
                    <span class="ai-typing-dot"></span>
                    <span class="ai-typing-dot"></span>
                    <span class="ai-typing-dot"></span>
                </div>
            </div>

            <div class="ai-chat-footer">
                <input type="text" id="ai-chat-input" class="ai-chat-input" placeholder="Nhập câu hỏi..." autocomplete="off">
                <button id="ai-chat-send" class="ai-send-btn" title="Gửi">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        `;
        document.body.appendChild(windowDiv);

        // 3. Event Listeners
        const btnToggle = document.getElementById('ai-chat-btn-toggle');
        const badgeToggle = document.getElementById('ai-chat-badge-toggle');
        const chatHeader = windowDiv.querySelector('.ai-chat-header');
        const inputField = document.getElementById('ai-chat-input');
        const btnSend = document.getElementById('ai-chat-send');
        const bubbleClose = document.getElementById('ai-bubble-close-btn');
        const bubbleChatNow = document.getElementById('ai-bubble-chat-now');

        function dismissBubble() {
            bubbleDiv.classList.remove('visible');
            sessionStorage.setItem('joby_bubble_dismissed', '1');
        }

        function toggleWindow() {
            isOpen = !isOpen;
            if (isOpen) {
                windowDiv.classList.add('open');
                bubbleDiv.classList.remove('visible');
                inputField.focus();
            } else {
                windowDiv.classList.remove('open');
            }
        }

        // Tự động hiển thị bong bóng thông báo chào đón sau 1s nếu chưa tắt
        setTimeout(() => {
            if (!isOpen && !sessionStorage.getItem('joby_bubble_dismissed')) {
                bubbleDiv.classList.add('visible');
            }
        }, 1000);

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
            if (e.target !== bubbleClose && !e.target.closest('#ai-bubble-close-btn')) {
                dismissBubble();
                if (!isOpen) toggleWindow();
            }
        });

        btnToggle.addEventListener('click', toggleWindow);
        badgeToggle.addEventListener('click', toggleWindow);
        // Bấm vào bất kỳ đâu trên Header (hoặc tiêu đề) là tự thu gọn/ẩn chatbox
        chatHeader.addEventListener('click', toggleWindow);

        btnSend.addEventListener('click', handleSendMessage);
        inputField.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSendMessage();
            }
        });

        // Handle suggestion chips
        windowDiv.addEventListener('click', function (e) {
            if (e.target.classList.contains('ai-chip')) {
                const query = e.target.getAttribute('data-query');
                if (query) {
                    inputField.value = query;
                    handleSendMessage();
                }
            }
        });
    }

    function handleSendMessage() {
        const input = document.getElementById('ai-chat-input');
        const container = document.getElementById('ai-messages-container');
        const indicator = document.getElementById('ai-typing-indicator');
        const text = input.value.trim();

        if (!text) return;

        // Append User Message
        const userDiv = document.createElement('div');
        userDiv.className = 'ai-msg ai-msg-user';
        userDiv.textContent = text;
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
                history: chatHistory
            })
        })
            .then(res => res.json())
            .then(data => {
                indicator.style.display = 'none';

                const botDiv = document.createElement('div');
                botDiv.className = 'ai-msg ai-msg-bot';

                let replyHtml = parseMarkdown(data.reply || 'Cảm ơn bạn đã liên hệ. Em có thể hỗ trợ gì thêm không ạ?');

                // Thêm các gợi ý tiếp theo nếu có
                if (data.suggestions && data.suggestions.length > 0) {
                    replyHtml += '<div class="ai-suggestions" style="margin-top: 10px;">';
                    data.suggestions.forEach(s => {
                        replyHtml += `<span class="ai-chip" data-query="${s}">${s}</span>`;
                    });
                    replyHtml += '</div>';
                }

                botDiv.innerHTML = replyHtml;
                container.insertBefore(botDiv, indicator);

                // 2. Khi AI trả lời: Cuộn để nội dung câu hỏi của người dùng nằm ngay sát đỉnh trên cùng (không bị trôi xuống cuối câu trả lời)
                setTimeout(() => {
                    const targetBotScroll = userDiv.offsetTop - 12;
                    container.scrollTo({
                        top: Math.max(0, targetBotScroll),
                        behavior: 'smooth'
                    });
                }, 40);

                chatHistory.push({ role: 'assistant', content: data.reply });
            })
            .catch(err => {
                indicator.style.display = 'none';
                const botDiv = document.createElement('div');
                botDiv.className = 'ai-msg ai-msg-bot';
                botDiv.innerHTML = 'Hệ thống đang xử lý câu hỏi. Vui lòng bấm vào các gợi ý bên dưới hoặc thử lại sau giây lát: <div class="ai-suggestions" style="margin-top:8px;"><span class="ai-chip" data-query="Tìm việc làm mới nhất">Tìm việc làm</span><span class="ai-chip" data-query="Xác thực chữ ký số DN là gì?">Xác thực Chữ ký số</span></div>';
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
