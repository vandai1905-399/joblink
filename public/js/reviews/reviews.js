/**
 * controllers/ReviewController.js - Xử lý đánh giá 2 chiều & tiêu chí Client
 */

const ReviewController = {
    /**
     * Khởi tạo chọn số sao tương tác (1 - 5 sao)
     */
    initStarRating() {
        document.querySelectorAll('.rating-star-selector').forEach(container => {
            const stars = container.querySelectorAll('.star-item');
            const input = container.querySelector('input[name="overall_rating"]');

            stars.forEach((star, index) => {
                star.style.cursor = 'pointer';
                star.addEventListener('click', () => {
                    if (input) input.value = index + 1;
                    stars.forEach((s, i) => {
                        s.innerHTML = `<svg class="icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="${i <= index ? '#eab308' : 'none'}" stroke="${i <= index ? '#eab308' : '#cbd5e1'}" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`;
                    });
                });
            });
        });
    },

    /**
     * Admin duyệt hoặc ẩn bài đánh giá qua Fetch API
     */
    async moderateReview(reviewId, status) {
        try {
            const formData = new FormData();
            formData.append('status', status);

            const res = await fetch(`/admin/reviews/moderate/${reviewId}`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            Toast.show(data.message || 'Đã cập nhật trạng thái đánh giá!', 'success');
            setTimeout(() => window.location.reload(), 600);
        } catch (err) {
            window.location.reload();
        }
    },

    init() {
        this.initStarRating();
    }
};

document.addEventListener('DOMContentLoaded', () => ReviewController.init());
