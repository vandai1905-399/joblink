/**
 * controllers/JobRequestController.js - Xử lý nộp đơn, lưu việc làm, duyệt đơn ứng tuyển Client
 */

const JobRequestController = {
    /**
     * Lưu / Bỏ lưu tin việc làm (Bookmark toggle)
     */
    async toggleSave(jobId, buttonElement) {
        if (!jobId) return;

        if (buttonElement) buttonElement.disabled = true;

        try {
            const res = await fetch(`/seeker/toggle-save/${jobId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (buttonElement) {
                buttonElement.disabled = false;
                if (data.saved) {
                    buttonElement.classList.add('btn-primary');
                    buttonElement.classList.remove('btn-outline');
                    buttonElement.innerHTML = `<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> <span>Đã Lưu</span>`;
                    Toast.show('Đã lưu việc làm vào danh sách quan tâm!', 'success');
                } else {
                    buttonElement.classList.remove('btn-primary');
                    buttonElement.classList.add('btn-outline');
                    buttonElement.innerHTML = `<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> <span>Lưu Tin</span>`;
                    Toast.show('Đã bỏ lưu việc làm!', 'info');
                }
            }
        } catch (err) {
            window.location.href = `/seeker/toggle-save/${jobId}`;
        }
    },

    /**
     * Nộp đơn ứng tuyển nhanh
     */
    async applyJob(jobId, jobTitle) {
        if (!confirm(`Bạn có chắc chắn muốn nộp hồ sơ ứng tuyển vị trí "${jobTitle || 'này'}"?`)) {
            return;
        }
        window.location.href = `/seeker/apply/${jobId}`;
    },

    /**
     * Nhà tuyển dụng duyệt / từ chối hồ sơ ứng tuyển
     */
    async updateApplicationStatus(requestId, status) {
        let reason = null;
        if (status === 'rejected') {
            reason = prompt('Nhập lý do từ chối (tùy chọn):');
        }

        try {
            const formData = new FormData();
            formData.append('status', status);
            if (reason) formData.append('rejection_reason', reason);

            const res = await fetch(`/employer/applications/update/${requestId}`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            Toast.show(data.message || 'Đã cập nhật trạng thái đơn ứng tuyển!', 'success');
            setTimeout(() => window.location.reload(), 600);
        } catch (err) {
            window.location.reload();
        }
    },

    init() {
        document.querySelectorAll('.btn-save-job').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleSave(btn.dataset.jobId, btn);
            });
        });
    }
};

document.addEventListener('DOMContentLoaded', () => JobRequestController.init());
