<?php ob_start(); ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Quản Lý & Lên Lịch Phỏng Vấn</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem;">Theo dõi lịch hẹn trao đổi trực tiếp hoặc phỏng vấn trực tuyến với người lao động</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary" id="toggleCreateMeetingBtn" style="font-weight: 700;">
            <?= icon('meetings', '', 16) ?> <span style="margin-left: 4px;">+ Tạo Lịch Hẹn Mới</span>
        </button>
    </div>
</div>

<!-- FORM TẠO LỊCH HẸN MỚI (CÓ THỂ ĐÓNG/MỞ) -->
<div class="card" id="createMeetingCard" style="display: none; margin-bottom: 1.5rem; border: 1px solid #99f6e4; background: #fafdfc;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #ccfbf1; padding-bottom: 0.75rem;">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--dark); display: flex; align-items: center; gap: 0.4rem;">
            <span>📅</span> Tạo Cuộc Hẹn Phỏng Vấn Mới
        </h3>
        <button type="button" id="closeCreateMeetingBtn" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
    </div>
    
    <form action="<?= url('/employer/meetings/store') ?>" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Chọn Đơn Ứng Tuyển / Người Lao Động *</label>
                <select name="request_id" class="form-control" required style="padding: 0.55rem;">
                    <?php if (empty($applications)): ?>
                        <option value="">-- Chưa có ứng viên nào được duyệt --</option>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <option value="<?= $app['request_id'] ?>">
                                <?= htmlspecialchars($app['seeker_name']) ?> — Vị trí: <?= htmlspecialchars($app['job_title']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Hình Thức Phỏng Vấn *</label>
                <select name="meeting_format" class="form-control" style="padding: 0.55rem;">
                    <option value="offline">Trực tiếp tại cơ sở / văn phòng (Offline)</option>
                    <option value="online">Trực tuyến Google Meet / Zoom (Online)</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Ngày Hẹn *</label>
                <input type="date" name="meeting_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" style="padding: 0.55rem;">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Giờ Bắt Đầu *</label>
                <input type="time" name="start_time" class="form-control" required value="09:00" style="padding: 0.55rem;">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Giờ Kết Thúc</label>
                <input type="time" name="end_time" class="form-control" value="10:00" style="padding: 0.55rem;">
            </div>
        </div>

        <div style="margin-bottom: 1rem;">
            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Địa Chỉ Hoặc Link Phòng Họp *</label>
            <input type="text" name="location_or_meeting_link" class="form-control" required placeholder="Địa chỉ cơ sở làm việc hoặc https://meet.google.com/..." style="padding: 0.55rem;">
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Ghi Chú Cho Người Lao Động:</label>
            <textarea name="meeting_note" class="form-control" rows="2" placeholder="Mang theo CCCD/VNeID, hồ sơ gốc hoặc chuẩn bị bài test..." style="padding: 0.55rem;"></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" id="cancelCreateMeetingBtn" class="btn btn-outline">Hủy Bỏ</button>
            <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                <?= icon('meetings', '', 16) ?> <span style="margin-left: 5px;">Xác Nhận & Tạo Lịch Hẹn</span>
            </button>
        </div>
    </form>
</div>

<!-- DANH SÁCH CUỘC HẸN (FULL WIDTH, COMPACT 1-LINE ROWS) -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--dark);">
            Danh Sách Lịch Phỏng Vấn (<?= count($meetings) ?>)
        </h3>
    </div>

    <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 0.75rem 1rem; text-align: left; white-space: nowrap; min-width: 220px;">Người lao động</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; white-space: nowrap; min-width: 150px;">Thời gian</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; min-width: 220px;">Hình thức / Địa điểm</th>
                    <th style="padding: 0.75rem 1rem; text-align: center; white-space: nowrap; width: 130px;">Trạng thái</th>
                    <th style="padding: 0.75rem 1rem; text-align: right; white-space: nowrap; min-width: 180px;">Xử lý</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($meetings)): ?>
                    <tr><td colspan="5" style="text-align: center; color: var(--slate-400); padding: 3rem 1rem;">Chưa có lịch hẹn nào. Bấm <strong>"+ Tạo Lịch Hẹn Mới"</strong> để lên lịch phỏng vấn!</td></tr>
                <?php else: ?>
                    <?php foreach ($meetings as $m): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; vertical-align: middle;">
                            <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                <div>
                                    <a href="<?= url('/employer/candidates/' . ($m['seeker_id'] ?? '')) ?>" style="color: #0d9488; font-weight: 700; text-decoration: none; font-size: 0.95rem;" title="Xem hồ sơ">
                                        <?= htmlspecialchars($m['seeker_name']) ?> &rarr;
                                    </a>
                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.15rem; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= htmlspecialchars($m['job_title']) ?>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                <div style="color: #0f172a; font-weight: 700; font-size: 0.9rem;">
                                    <?= date('d/m/Y', strtotime($m['meeting_date'])) ?>
                                </div>
                                <div style="font-size: 0.8rem; color: #0d9488; font-weight: 600; margin-top: 0.1rem;">
                                    🕒 <?= substr($m['start_time'], 0, 5) ?><?= !empty($m['end_time']) ? ' - ' . substr($m['end_time'], 0, 5) : '' ?>
                                </div>
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: nowrap;">
                                    <span class="badge <?= $m['meeting_format'] === 'online' ? 'badge-primary' : 'badge-slate' ?>" style="font-size: 0.75rem; padding: 0.2rem 0.5rem; white-space: nowrap; flex-shrink: 0;">
                                        <?= $m['meeting_format'] === 'online' ? 'Online' : 'Trực tiếp' ?>
                                    </span>
                                    <?php if ($m['meeting_format'] === 'online'): ?>
                                        <a href="<?= htmlspecialchars($m['location_or_meeting_link']) ?>" target="_blank" style="font-size: 0.82rem; color: #0d9488; font-weight: 600; text-decoration: none; overflow: hidden; text-overflow: ellipsis; max-width: 200px; white-space: nowrap;" title="<?= htmlspecialchars($m['location_or_meeting_link']) ?>">
                                            <?= htmlspecialchars($m['location_or_meeting_link']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size: 0.82rem; color: #475569; overflow: hidden; text-overflow: ellipsis; max-width: 220px; white-space: nowrap;" title="<?= htmlspecialchars($m['location_or_meeting_link']) ?>">
                                            <?= htmlspecialchars($m['location_or_meeting_link']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($m['meeting_note'])): ?>
                                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.2rem; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($m['meeting_note']) ?>">
                                        📝 <?= htmlspecialchars($m['meeting_note']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: center; white-space: nowrap;">
                                <?php if ($m['meeting_status'] === 'scheduled'): ?>
                                    <span class="badge badge-warning" style="font-size: 0.78rem; padding: 0.25rem 0.55rem; white-space: nowrap;">Đã lên lịch</span>
                                <?php elseif ($m['meeting_status'] === 'completed'): ?>
                                    <span class="badge badge-success" style="font-size: 0.78rem; padding: 0.25rem 0.55rem; white-space: nowrap;"><?= icon('check', '', 12) ?> Hoàn thành</span>
                                <?php else: ?>
                                    <span class="badge badge-danger" style="font-size: 0.78rem; padding: 0.25rem 0.55rem; white-space: nowrap;">Đã hủy</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; white-space: nowrap;">
                                <div style="display: flex; gap: 0.35rem; justify-content: flex-end; align-items: center;">
                                    <?php if ($m['meeting_status'] === 'scheduled'): ?>
                                        <form action="<?= url('/employer/meetings/update/' . $m['meeting_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-success btn-sm" style="font-weight: 600; padding: 0.3rem 0.6rem; font-size: 0.78rem; white-space: nowrap;">
                                                Hoàn Thành
                                            </button>
                                        </form>
                                        <form action="<?= url('/employer/meetings/update/' . $m['meeting_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="button" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: var(--danger); font-weight: 600; padding: 0.3rem 0.6rem; font-size: 0.78rem; white-space: nowrap;" data-reject-prompt="Lý Do Hủy Lịch Hẹn Phỏng Vấn">
                                                Hủy
                                            </button>
                                        </form>
                                    <?php elseif ($m['meeting_status'] === 'completed'): ?>
                                        <a href="<?= url('/employer/candidate-reviews') ?>" class="btn btn-primary btn-sm" style="font-weight: 600; padding: 0.3rem 0.6rem; font-size: 0.78rem; white-space: nowrap;">
                                            Đánh Giá
                                        </a>
                                        <form action="<?= url('/employer/meetings/delete/' . $m['meeting_id']) ?>" method="POST" style="display:inline;">
                                            <button type="button" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: var(--danger); font-weight: 600; padding: 0.3rem 0.6rem; font-size: 0.78rem; white-space: nowrap;" data-confirm="Bạn có chắc chắn muốn xóa lịch hẹn này?">
                                                Xóa
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= url('/employer/meetings/update/' . $m['meeting_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="scheduled">
                                            <button type="submit" class="btn btn-warning btn-sm" style="font-weight: 600; padding: 0.3rem 0.6rem; font-size: 0.78rem; white-space: nowrap;">
                                                Lên Lại
                                            </button>
                                        </form>
                                        <form action="<?= url('/employer/meetings/delete/' . $m['meeting_id']) ?>" method="POST" style="display:inline;">
                                            <button type="button" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: var(--danger); font-weight: 600; padding: 0.3rem 0.6rem; font-size: 0.78rem; white-space: nowrap;" data-confirm="Bạn có chắc chắn muốn xóa lịch hẹn này?">
                                                Xóa
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleCreateMeetingBtn');
    const closeBtn = document.getElementById('closeCreateMeetingBtn');
    const cancelBtn = document.getElementById('cancelCreateMeetingBtn');
    const formCard = document.getElementById('createMeetingCard');

    if (toggleBtn && formCard) {
        toggleBtn.addEventListener('click', function() {
            formCard.style.display = (formCard.style.display === 'none') ? 'block' : 'none';
            if (formCard.style.display === 'block') {
                formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
    if (closeBtn && formCard) {
        closeBtn.addEventListener('click', () => formCard.style.display = 'none');
    }
    if (cancelBtn && formCard) {
        cancelBtn.addEventListener('click', () => formCard.style.display = 'none');
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
