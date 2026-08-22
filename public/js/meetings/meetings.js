/**
 * controllers/MeetingController.js - Xử lý lên lịch phỏng vấn & cập nhật cuộc hẹn Client
 */

const MeetingController = {
    /**
     * Cập nhật trạng thái cuộc hẹn (Đã xong / Đã hủy)
     */
    async updateMeetingStatus(meetingId, status, userRole) {
        if (!confirm(`Xác nhận đánh dấu cuộc hẹn là "${status === 'completed' ? 'Đã hoàn thành' : 'Đã hủy'}"?`)) {
            return;
        }

        const endpoint = (userRole === 'EMPLOYER_ADMIN') 
            ? `/employer/meetings/update/${meetingId}` 
            : `/seeker/meetings/update/${meetingId}`;

        try {
            const formData = new FormData();
            formData.append('status', status);

            const res = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            Toast.show(data.message || 'Đã cập nhật trạng thái cuộc hẹn!', 'success');
            setTimeout(() => window.location.reload(), 600);
        } catch (err) {
            window.location.reload();
        }
    },

    /**
     * Validate form lên lịch hẹn mới
     */
    validateScheduleForm(event) {
        const form = event.target;
        const meetingDate = form.querySelector('input[name="meeting_date"]')?.value;
        const startTime = form.querySelector('input[name="start_time"]')?.value;
        const location = form.querySelector('input[name="location_or_meeting_link"]')?.value.trim();

        if (!meetingDate || !startTime || !location) {
            event.preventDefault();
            Toast.show('Vui lòng điền đầy đủ ngày, giờ và địa điểm / link phỏng vấn!', 'error');
            return false;
        }
        return true;
    },

    init() {
        const scheduleForm = document.querySelector('form[action*="/employer/meetings/store"]');
        if (scheduleForm) {
            scheduleForm.addEventListener('submit', (e) => this.validateScheduleForm(e));
        }
    }
};

document.addEventListener('DOMContentLoaded', () => MeetingController.init());
