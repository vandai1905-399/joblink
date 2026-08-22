<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Danh Sách Việc Đã Ứng Tuyển</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Theo dõi trạng thái duyệt hồ sơ và phản hồi từ các Nhà tuyển dụng</p>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Chức danh công việc</th>
                    <th>Công ty</th>
                    <th>Mức lương</th>
                    <th>Ngày nộp</th>
                    <th>Trạng thái</th>
                    <th>Lý do (nếu từ chối)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appliedJobs)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--slate-400); padding: 2rem;">Bạn chưa nộp đơn vào vị trí nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($appliedJobs as $app): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($app['job_title']) ?></strong>
                                <?php if (!empty($app['available_work_dates'])): ?>
                                    <br><span style="font-size: 0.78rem; color: #0d9488; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('calendar', '', 12) ?> <span>Lịch đăng ký: <?= htmlspecialchars($app['available_work_dates']) ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($app['employer_name']) ?></td>
                            <td style="font-weight: 700; color: var(--primary); white-space: nowrap;"><?= formatSalary($app['salary_amount'], $app['employment_type'] ?? 'full_time') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($app['applied_at'])) ?></td>
                            <td>
                                <?php if ($app['request_status'] === 'accepted'): ?>
                                    <span class="badge badge-success"><?= icon('check', '', 12) ?> Hồ sơ được duyệt</span>
                                <?php elseif ($app['request_status'] === 'rejected'): ?>
                                    <span class="badge badge-danger"><?= icon('x', '', 12) ?> Chưa phù hợp</span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= icon('clock', '', 12) ?> Chờ duyệt</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--slate-500);">
                                <?= htmlspecialchars($app['rejection_reason'] ?? '—') ?>
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
