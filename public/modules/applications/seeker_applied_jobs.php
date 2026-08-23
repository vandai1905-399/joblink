<?php 
// Phân loại lời mời từ NTD vs Hồ sơ chủ động nộp
$invitations = [];
$selfApplied = [];

foreach ($appliedJobs as $jobApp) {
    if (in_array($jobApp['request_status'], ['invited', 'invitation_accepted', 'invitation_rejected'])) {
        $invitations[] = $jobApp;
    } else {
        $selfApplied[] = $jobApp;
    }
}

ob_start(); 
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Tiến Độ Tuyển Dụng & Lời Mời Việc Làm</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Theo dõi trạng thái các vị trí bạn đã nộp đơn và phản hồi các lời mời trực tiếp từ Nhà tuyển dụng</p>
</div>

<!-- ========================================================================= -->
<!-- 1. KHU VỰC LỜI MỜI NHẬN VIỆC TRỰC TIẾP TỪ NHÀ TUYỂN DỤNG (2 CHIỀU)         -->
<!-- ========================================================================= -->
<div class="card" style="margin-bottom: 2rem; border: 1px solid #99f6e4; background: #fafdfc;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #ccfbf1; padding-bottom: 0.75rem;">
        <div>
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                <span>🎯</span> Lời Mời Nhận Việc Trực Tiếp Từ Doanh Nghiệp (<?= count($invitations) ?>)
            </h3>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.82rem; color: #64748b;">
                Nhà tuyển dụng đã xem hồ sơ năng lực của bạn và chủ động gửi lời mời phỏng vấn / làm việc
            </p>
        </div>
    </div>

    <?php if (empty($invitations)): ?>
        <div style="text-align: center; padding: 2rem; color: #64748b; font-size: 0.88rem;">
            Chưa có lời mời trực tiếp nào. Khi Nhà tuyển dụng gửi lời mời, bạn sẽ nhận được thông báo tại đây!
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($invitations as $inv): ?>
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div style="flex: 1; min-width: 250px;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                            <span class="badge badge-primary" style="font-size: 0.75rem;">Doanh nghiệp mời trực tiếp</span>
                            <span style="font-size: 0.78rem; color: #64748b;"><?= date('d/m/Y H:i', strtotime($inv['applied_at'])) ?></span>
                        </div>
                        <h4 style="margin: 0 0 0.25rem 0; font-size: 1.05rem;">
                            <a href="<?= url('/jobs/' . $inv['job_post_id']) ?>" style="color: #0d9488; text-decoration: none; font-weight: 700;">
                                <?= htmlspecialchars($inv['job_title']) ?>
                            </a>
                        </h4>
                        <div style="font-size: 0.88rem; color: #334155; margin-bottom: 0.35rem;">
                            🏢 <strong><?= htmlspecialchars($inv['employer_name']) ?></strong> · 📍 <?= htmlspecialchars($inv['workplace_address'] ?? 'Bình Dương') ?> · 💰 <span style="color: #0d9488; font-weight: 700;"><?= formatSalary($inv['salary_amount'], $inv['employment_type']) ?></span>
                        </div>
                        <?php if (!empty($inv['available_work_dates'])): ?>
                            <div style="background: #f8fafc; border-left: 3px solid #0d9488; padding: 0.4rem 0.75rem; border-radius: 0 4px 4px 0; font-size: 0.82rem; color: #475569; margin-top: 0.4rem;">
                                💬 <em>"<?= htmlspecialchars($inv['available_work_dates']) ?>"</em>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <?php if ($inv['request_status'] === 'invited'): ?>
                            <div style="display: flex; gap: 0.5rem;">
                                <form action="<?= url('/seeker/invitation/respond/' . $inv['request_id']) ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-success" style="font-weight: 700; padding: 0.5rem 1rem;">
                                        ✓ Đồng Ý Lời Mời
                                    </button>
                                </form>
                                <form action="<?= url('/seeker/invitation/respond/' . $inv['request_id']) ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-outline" style="color: #dc2626; border-color: #fca5a5; padding: 0.5rem 0.85rem;" onclick="return confirm('Bạn có chắc muốn từ chối lời mời này?')">
                                        Từ Chối
                                    </button>
                                </form>
                            </div>
                        <?php elseif ($inv['request_status'] === 'invitation_accepted'): ?>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.35rem;">
                                <span class="badge badge-success" style="font-size: 0.82rem;"><?= icon('check', '', 12) ?> Đã đồng ý nhận lời mời</span>
                                <div style="display: flex; gap: 0.35rem; margin-top: 0.25rem;">
                                    <a href="<?= url('/seeker/messages') ?>" class="btn btn-outline btn-sm" style="color: #0d9488; border-color: #0d9488;">
                                        💬 Nhắn tin với NTD
                                    </a>
                                    <a href="<?= url('/seeker/meetings') ?>" class="btn btn-primary btn-sm">
                                        📅 Xem lịch hẹn
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="badge badge-danger">Đã từ chối lời mời</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- 2. KHU VỰC HỒ SƠ BẠN ĐÃ CHỦ ĐỘNG ỨNG TUYỂN                                -->
<!-- ========================================================================= -->
<div class="card">
    <div style="margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
        <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
            <span>📄</span> Hồ Sơ Bạn Đã Nộp Ứng Tuyển (<?= count($selfApplied) ?>)
        </h3>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Chức danh công việc</th>
                    <th>Công ty</th>
                    <th>Mức lương</th>
                    <th>Ngày nộp</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($selfApplied)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--slate-400); padding: 2.5rem;">Bạn chưa nộp đơn vào vị trí nào. Hãy khám phá các công việc trên hệ thống!</td></tr>
                <?php else: ?>
                    <?php foreach ($selfApplied as $app): ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?= url('/jobs/' . $app['job_post_id']) ?>" style="color: #0f172a; text-decoration: none;">
                                        <?= htmlspecialchars($app['job_title']) ?>
                                    </a>
                                </strong>
                                <?php if (!empty($app['available_work_dates'])): ?>
                                    <br><span style="font-size: 0.78rem; color: #0d9488; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('calendar', '', 12) ?> <span>Lịch đăng ký: <?= htmlspecialchars($app['available_work_dates']) ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($app['employer_name']) ?></td>
                            <td style="font-weight: 700; color: var(--primary); white-space: nowrap;"><?= formatSalary($app['salary_amount'], $app['employment_type'] ?? 'full_time') ?></td>
                            <td style="font-size: 0.85rem; color: #475569;"><?= date('d/m/Y H:i', strtotime($app['applied_at'])) ?></td>
                            <td>
                                <?php if ($app['request_status'] === 'accepted'): ?>
                                    <span class="badge badge-success"><?= icon('check', '', 12) ?> Hồ sơ được duyệt</span>
                                <?php elseif ($app['request_status'] === 'rejected'): ?>
                                    <span class="badge badge-danger"><?= icon('x', '', 12) ?> Chưa phù hợp</span>
                                    <?php if (!empty($app['rejection_reason'])): ?>
                                        <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.2rem;"><?= htmlspecialchars($app['rejection_reason']) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= icon('clock', '', 12) ?> Chờ duyệt</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($app['request_status'] === 'accepted'): ?>
                                    <div style="display: flex; gap: 0.35rem;">
                                        <a href="<?= url('/seeker/messages') ?>" class="btn btn-outline btn-sm" style="color: #0d9488; border-color: #0d9488; font-size: 0.78rem;">
                                            💬 Nhắn tin
                                        </a>
                                        <a href="<?= url('/seeker/meetings') ?>" class="btn btn-primary btn-sm" style="font-size: 0.78rem;">
                                            📅 Lịch hẹn
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <a href="<?= url('/jobs/' . $app['job_post_id']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">
                                        Xem tin
                                    </a>
                                <?php endif; ?>
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
