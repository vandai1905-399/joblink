<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Quản Lý & Lên Lịch Phỏng Vấn</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Lên lịch hẹn trao đổi trực tiếp tại quán/văn phòng hoặc phòng họp Google Meet</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; align-items: start;">
    <!-- Form tạo lịch hẹn -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--dark);">Tạo Cuộc Hẹn Mới</h3>
        <form action="<?= url('/employer/meetings/store') ?>" method="POST">
            <div class="form-group">
                <label class="form-label">Chọn Đơn Ứng Tuyển / Người Lao Động *</label>
                <select name="request_id" class="form-control" required>
                    <?php foreach ($applications as $app): ?>
                        <option value="<?= $app['request_id'] ?>">
                            <?= htmlspecialchars($app['seeker_name']) ?> - <?= htmlspecialchars($app['job_title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Ngày Hẹn *</label>
                    <input type="date" name="meeting_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Hình Thức *</label>
                    <select name="meeting_format" class="form-control">
                        <option value="offline">Trực tiếp (Offline)</option>
                        <option value="online">Trực tuyến (Online Meet)</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Giờ Bắt Đầu *</label>
                    <input type="time" name="start_time" class="form-control" required value="09:00">
                </div>
                <div class="form-group">
                    <label class="form-label">Giờ Kết Thúc</label>
                    <input type="time" name="end_time" class="form-control" value="10:00">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Địa Chỉ Hoặc Link Phòng Họp *</label>
                <input type="text" name="location_or_meeting_link" class="form-control" required placeholder="Địa chỉ cơ sở hoặc https://meet.google.com/...">
            </div>

            <div class="form-group">
                <label class="form-label">Ghi Chú Cho Người Lao Động</label>
                <textarea name="meeting_note" class="form-control" rows="3" placeholder="Mang theo CCCD, trang phục lịch sự hoặc chuẩn bị bài test..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <?= icon('meetings', '', 16) ?> <span style="margin-left: 5px;">Xác Nhận & Gửi Lịch Hẹn</span>
            </button>
        </form>
    </div>

    <!-- Danh sách cuộc hẹn -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--dark);">Danh Sách Lịch Phỏng Vấn (<?= count($meetings) ?>)</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Người lao động</th>
                        <th>Thời gian</th>
                        <th>Hình thức / Địa điểm</th>
                        <th>Trạng thái</th>
                        <th>Xử lý</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($meetings)): ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--slate-400); padding: 2rem;">Chưa có lịch hẹn nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($meetings as $m): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('/employer/candidates/' . ($m['seeker_id'] ?? '')) ?>" style="color: var(--primary); font-weight: 700; text-decoration: none;" title="Xem hồ sơ người lao động">
                                        <?= htmlspecialchars($m['seeker_name']) ?> &rarr;
                                    </a><br>
                                    <span style="font-size: 0.82rem; color: var(--slate-500);"><?= htmlspecialchars($m['job_title']) ?></span>
                                </td>
                                <td>
                                    <span style="color: #0f172a; font-weight: 600;"><?= date('d/m/Y', strtotime($m['meeting_date'])) ?></span><br>
                                    <span style="font-size: 0.82rem; color: var(--slate-500);"><?= substr($m['start_time'], 0, 5) ?></span>
                                </td>
                                <td style="font-size: 0.85rem;">
                                    <span class="badge <?= $m['meeting_format'] === 'online' ? 'badge-primary' : 'badge-slate' ?>">
                                        <?= $m['meeting_format'] === 'online' ? 'Online' : 'Offline' ?>
                                    </span><br>
                                    <span style="color: #475569;"><?= htmlspecialchars(mb_substr($m['location_or_meeting_link'], 0, 30)) ?>...</span>
                                </td>
                                <td>
                                    <?php if ($m['meeting_status'] === 'scheduled'): ?>
                                        <span class="badge badge-warning"><span>Đã lên lịch</span></span>
                                    <?php elseif ($m['meeting_status'] === 'completed'): ?>
                                        <span class="badge badge-success"><?= icon('check', '', 13) ?> <span>Hoàn thành</span></span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><span>Đã hủy</span></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.4rem; align-items: center;">
                                        <?php if ($m['meeting_status'] === 'scheduled'): ?>
                                            <form action="<?= url('/employer/meetings/update/' . $m['meeting_id']) ?>" method="POST" style="display:inline;">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-success btn-sm" style="font-weight: 600; padding: 0.35rem 0.65rem;">
                                                    Hoàn Thành
                                                </button>
                                            </form>
                                            <form action="<?= url('/employer/meetings/update/' . $m['meeting_id']) ?>" method="POST" style="display:inline;">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="button" class="btn btn-outline btn-sm btn-danger" style="color: var(--danger); border-color: var(--danger); font-weight: 600; padding: 0.35rem 0.65rem;" data-reject-prompt="Lý Do Hủy Lịch Hẹn Phỏng Vấn">
                                                    Hủy Lịch
                                                </button>
                                            </form>
                                        <?php elseif ($m['meeting_status'] === 'completed'): ?>
                                            <a href="<?= url('/employer/candidate-reviews') ?>" class="btn btn-primary btn-sm" style="font-weight: 600; padding: 0.35rem 0.65rem;">
                                                Đánh Giá
                                            </a>
                                            <form action="<?= url('/employer/meetings/delete/' . $m['meeting_id']) ?>" method="POST" style="display:inline;">
                                                <button type="button" class="btn btn-outline btn-sm btn-danger" style="color: var(--danger); border-color: var(--danger); font-weight: 600; padding: 0.35rem 0.65rem;" data-confirm="Bạn có chắc chắn muốn xóa lịch hẹn này?">
                                                    Xóa
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?= url('/employer/meetings/update/' . $m['meeting_id']) ?>" method="POST" style="display:inline;">
                                                <input type="hidden" name="status" value="scheduled">
                                                <button type="submit" class="btn btn-warning btn-sm" style="font-weight: 600; padding: 0.35rem 0.65rem;">
                                                    Lên Lại
                                                </button>
                                            </form>
                                            <form action="<?= url('/employer/meetings/delete/' . $m['meeting_id']) ?>" method="POST" style="display:inline;">
                                                <button type="button" class="btn btn-outline btn-sm btn-danger" style="color: var(--danger); border-color: var(--danger); font-weight: 600; padding: 0.35rem 0.65rem;" data-confirm="Bạn có chắc chắn muốn xóa lịch hẹn này?">
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
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
