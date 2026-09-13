<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">
        Tổng Quan Tuyển Dụng - <?= htmlspecialchars($employer['employer_name'] ?? 'Doanh Nghiệp') ?>
    </h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Bảng điều khiển tuyển dụng, quản lý hồ sơ người lao động và lịch phỏng vấn</p>
</div>

<div class="grid-stats">
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= url('/employer/jobs') ?>'">
        <div class="stat-title">Tin Tuyển Dụng Đang Mở</div>
        <div class="stat-value" style="color: var(--primary);"><?= count($jobs ?? []) ?></div>
        <a href="<?= url('/employer/jobs') ?>" style="font-size: 0.82rem; color: var(--primary); font-weight: 600;">Quản lý tin &rarr;</a>
    </div>
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= url('/employer/applications') ?>'">
        <div class="stat-title">Hồ Sơ Ứng Tuyển Nhận Được</div>
        <div class="stat-value" style="color: var(--warning);"><?= count($applications ?? []) ?></div>
        <a href="<?= url('/employer/applications') ?>" style="font-size: 0.82rem; color: var(--warning); font-weight: 600;">Xem hồ sơ &rarr;</a>
    </div>
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= url('/employer/meetings') ?>'">
        <div class="stat-title">Lịch Phỏng Vấn / Cuộc Hẹn</div>
        <div class="stat-value" style="color: var(--success);"><?= count($meetings ?? []) ?></div>
        <a href="<?= url('/employer/meetings') ?>" style="font-size: 0.82rem; color: var(--success); font-weight: 600;">Quản lý lịch &rarr;</a>
    </div>
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= url('/employer/candidate-reviews') ?>'">
        <div class="stat-title">Đánh Giá Từ Người Lao Động</div>
        <div class="stat-value" style="color: var(--primary); display: flex; align-items: center; gap: 0.25rem;"><?= $employer['avg_rating'] ?? '5.0' ?> <?= icon('star_fill', '', 20) ?></div>
        <div style="font-size: 0.82rem; color: var(--slate-500);"><?= $employer['total_reviews'] ?? 0 ?> lượt đánh giá</div>
    </div>
</div>

<div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
    <a href="<?= url('/employer/jobs/create') ?>" class="btn btn-primary">
        <?= icon('posts', '', 16) ?> <span style="margin-left: 5px;">+ Đăng Tin Tuyển Dụng Mới</span>
    </a>
    <a href="<?= url('/employer/candidates/recommended') ?>" class="btn btn-outline" style="color: #0d9488; border-color: #0d9488; font-weight: 700; background: #f0fdfa;">
        <?= icon('zap', '', 16) ?> <span style="margin-left: 5px;">Quản Lý NLĐ Đề Xuất & Tiềm Năng</span>
    </a>
    <a href="<?= url('/employer/meetings') ?>" class="btn btn-outline">
        <?= icon('meetings', '', 16) ?> <span style="margin-left: 5px;">Lên Lịch Hẹn Phỏng Vấn</span>
    </a>
    <a href="<?= url('/employer/candidate-reviews') ?>" class="btn btn-outline">
        <?= icon('reviews', '', 16) ?> <span style="margin-left: 5px;">Đánh Giá Người Lao Động Sau Ca</span>
    </a>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--dark); cursor: pointer;" onclick="window.location.href='<?= url('/employer/applications') ?>'">
            Hồ Sơ Ứng Tuyển Mới Nhất
        </h3>
        <a href="<?= url('/employer/applications') ?>" class="btn btn-outline btn-sm">Xem tất cả</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Người lao động</th>
                    <th>Vị trí nộp</th>
                    <th>Email / SĐT</th>
                    <th>Ngày nộp</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--slate-400); padding: 2rem;">Chưa có người lao động nộp hồ sơ.</td></tr>
                <?php else: ?>
                    <?php foreach (array_slice($applications, 0, 5) as $app): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/employer/candidates/' . $app['seeker_id']) ?>" style="text-decoration: none; color: var(--primary); font-weight: 700;" title="Xem hồ sơ người lao động">
                                    <?= htmlspecialchars($app['seeker_name']) ?> &rarr;
                                </a>
                            </td>
                            <td style="color: #334155; font-weight: 500;">
                                <strong><?= htmlspecialchars($app['job_title']) ?></strong>
                                <?php if (!empty($app['available_work_dates'])): ?>
                                    <br><span style="font-size: 0.78rem; color: #0d9488; font-weight: 600; background: #f0fdfa; border: 1px solid #ccfbf1; padding: 0.1rem 0.4rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.25rem;">
                                        <?= icon('calendar', '', 12) ?> <span><?= htmlspecialchars($app['available_work_dates']) ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="color: #0f172a; font-weight: 600;"><?= htmlspecialchars($app['phone_number']) ?></span><br>
                                <span style="font-size: 0.82rem; color: var(--slate-500);"><?= htmlspecialchars($app['email']) ?></span>
                            </td>
                            <td style="color: #475569;"><?= date('d/m/Y', strtotime($app['applied_at'])) ?></td>
                            <td>
                                <?php if ($app['request_status'] === 'accepted'): ?>
                                    <span class="badge badge-success"><?= icon('check', '', 13) ?> <span>Đã duyệt</span></span>
                                <?php elseif ($app['request_status'] === 'rejected'): ?>
                                    <span class="badge badge-danger"><span>Từ chối</span></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><span>Chờ duyệt</span></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.35rem; align-items: center;">
                                    <?php if ($app['request_status'] === 'accepted'): ?>
                                        <!-- Khi đã duyệt: Hiện nút HỦY DUYỆT và LÊN LỊCH PV -->
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="pending">
                                            <button type="button" class="btn btn-warning btn-sm" data-reject-prompt="Lý Do Hủy Duyệt Hồ Sơ Ứng Tuyển">
                                                Hủy Duyệt
                                            </button>
                                        </form>
                                        <a href="<?= url('/employer/meetings') ?>" class="btn btn-outline btn-sm" title="Lên lịch phỏng vấn">
                                            Hẹn PV
                                        </a>
                                    <?php elseif ($app['request_status'] === 'pending'): ?>
                                        <!-- Khi chờ duyệt: Hiện trực tiếp nút DUYỆT và TỪ CHỐI -->
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="accepted">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                Duyệt
                                            </button>
                                        </form>
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="button" class="btn btn-danger btn-sm" data-reject-prompt="Lý Do Từ Chối Hồ Sơ Người Lao Động">
                                                Từ chối
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <!-- Khi đã từ chối: Hiện nút DUYỆT LẠI và XÓA -->
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="accepted">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                Duyệt Lại
                                            </button>
                                        </form>
                                        <form action="<?= url('/employer/applications/delete/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-confirm="Bạn có chắc chắn muốn xóa hồ sơ ứng tuyển này?">
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

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
