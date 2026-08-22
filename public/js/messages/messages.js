/**
 * controllers/MessageController.js - Xử lý gửi tin nhắn trực tiếp & thông báo hệ thống Client
 */

const MessageController = {
    /**
     * Gửi tin nhắn qua AJAX không reload trang
     */
    async sendMessage(event) {
        if (event) event.preventDefault();
        const form = event.target;
        const receiverId = form.querySelector('select[name="receiver_account_id"], input[name="receiver_account_id"]')?.value;
        const content = form.querySelector('textarea[name="message_content"]')?.value.trim();
        const submitBtn = form.querySelector('button[type="submit"]');

        if (!receiverId || !content) {
            Toast.show('Vui lòng chọn người nhận và nhập nội dung tin nhắn!', 'error');
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Đang gửi...';
        }

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action || '/employer/messages/send', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            Toast.show(data.message || 'Đã gửi tin nhắn thành công!', 'success');
            form.reset();
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Gửi Tin Nhắn';
            }
            setTimeout(() => window.location.reload(), 800);
        } catch (err) {
            form.submit();
        }
    },

    init() {
        const messageForms = document.querySelectorAll('form[action*="/messages/send"], form[action*="/messages/broadcast"]');
        messageForms.forEach(form => {
            form.addEventListener('submit', (e) => this.sendMessage(e));
        });
    }
};

document.addEventListener('DOMContentLoaded', () => MessageController.init());
