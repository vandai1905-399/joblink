/**
 * controllers/JobPostController.js - Xử lý tìm kiếm, duyệt tin, xóa tin tuyển dụng Client
 */

const JobPostController = {
    /**
     * Xóa tin tuyển dụng với xác nhận
     */
    async deleteJob(jobId, deleteUrl) {
        if (!confirm('Bạn có chắc chắn muốn xóa tin tuyển dụng này? Thao tác này không thể hoàn tác!')) {
            return;
        }
        window.location.href = deleteUrl || `/employer/jobs/delete/${jobId}`;
    },

    /**
     * Admin Duyệt / Từ chối tin tuyển dụng qua Fetch API
     */
    async moderateJob(jobId, status) {
        try {
            const formData = new FormData();
            formData.append('status', status);

            const res = await fetch(`/admin/job-posts/${status === 'approved' ? 'approve' : 'reject'}/${jobId}`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            Toast.show(`Đã cập nhật trạng thái tin thành: ${status}`, 'success');
            setTimeout(() => window.location.reload(), 600);
        } catch (err) {
            window.location.reload();
        }
    },

    /**
     * Kiểm tra hợp lệ form đăng tin tuyển dụng
     */
    validateForm(event) {
        const form = event.target;
        const title = form.querySelector('input[name="job_title"]')?.value.trim();
        const positions = form.querySelector('input[name="number_of_positions"]')?.value;

        if (!title) {
            event.preventDefault();
            Toast.show('Vui lòng nhập tiêu đề công việc!', 'error');
            return false;
        }

        if (positions && parseInt(positions) <= 0) {
            event.preventDefault();
            Toast.show('Số lượng tuyển phải lớn hơn 0!', 'error');
            return false;
        }
        return true;
    },

    init() {
        const jobForm = document.querySelector('form[action*="/employer/jobs/store"], form[action*="/employer/jobs/update"]');
        if (jobForm) {
            jobForm.addEventListener('submit', (e) => this.validateForm(e));
        }
    }
};

document.addEventListener('DOMContentLoaded', () => JobPostController.init());
