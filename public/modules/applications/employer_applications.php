<?php 
$extraCss = ['css/applications/employer_applications.css', 'css/jobs/ai_matching.css'];
$extraJs = 'js/applications/applications.js';
ob_start(); 
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Quản Lý Hồ Sơ Ứng Tuyển</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Xem xét danh sách người lao động nộp hồ sơ kèm đánh giá độ tương thích AI đa chiều</p>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Người lao động</th>
                    <th>Vị trí ứng tuyển</th>
                    <th>Độ phù hợp AI</th>
                    <th>Email & SĐT</th>
                    <th>Ngày nộp</th>
                    <th>Trạng thái</th>
                    <th>Xử lý</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--slate-400); padding: 2rem;">Chưa có hồ sơ ứng tuyển nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td style="min-width: 200px;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <img src="<?= htmlspecialchars(!empty($app['seeker_avatar']) ? $app['seeker_avatar'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150') ?>" alt="avatar" style="width: 40px; height: 40px; border-radius: var(--radius-full); object-fit: cover; border: 1px solid var(--slate-200); flex-shrink: 0;">
                                    <div>
                                        <a href="<?= url('/employer/candidates/' . $app['seeker_id']) ?>" style="font-weight: 700; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 0.3rem;" title="Bấm để xem hồ sơ & đánh giá năng lực người lao động">
                                            <span><?= htmlspecialchars($app['seeker_name']) ?></span>
                                            <span style="font-size: 0.75rem; color: #0d9488;">&rarr;</span>
                                        </a>
                                        <span style="font-size: 0.78rem; color: #64748b;">Mã NLĐ: <?= formatId('seeker', $app['seeker_id']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="color: #334155;">
                                <strong><?= htmlspecialchars($app['job_title']) ?></strong>
                                <?php if (!empty($app['available_work_dates'])): ?>
                                    <br><span style="font-size: 0.8rem; color: #0d9488; font-weight: 600; background: #f0fdfa; border: 1px solid #ccfbf1; padding: 0.15rem 0.45rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.3rem;">
                                        <?= icon('calendar', '', 12) ?> <span>Ngày làm: <?= htmlspecialchars($app['available_work_dates']) ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($app['ai_match_score'])): ?>
                                    <span class="ai-match-badge <?= $app['ai_match_level'] ?? 'medium' ?>" style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                                        ✨ <?= $app['ai_match_score'] ?>%
                                    </span>
                                    <?php if (!empty($app['ai_matched_skills'])): ?>
                                        <div style="font-size: 0.72rem; color: #059669; margin-top: 0.25rem; max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars(implode(', ', $app['ai_matched_skills'])) ?>">
                                            ✓ <?= htmlspecialchars(implode(', ', array_slice($app['ai_matched_skills'], 0, 2))) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; color: #94a3b8;">--</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($app['phone_number']) ?></span><br>
                                <span style="font-size: 0.82rem; color: var(--slate-500);"><?= htmlspecialchars($app['email']) ?></span>
                            </td>
                            <td style="color: #475569;"><?= date('d/m/Y H:i', strtotime($app['applied_at'])) ?></td>
                            <td>
                                <?php if ($app['request_status'] === 'accepted'): ?>
                                    <span class="badge badge-success"><?= icon('check', '', 13) ?> <span>Đã duyệt nhận</span></span>
                                <?php elseif ($app['request_status'] === 'rejected'): ?>
                                    <span class="badge badge-danger"><span>Đã từ chối</span></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><span>Chờ duyệt</span></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                                    <a href="<?= url('/employer/candidates/' . $app['seeker_id']) ?>" class="btn btn-outline btn-sm" style="padding: 0.35rem 0.65rem; font-size: 0.82rem; font-weight: 700; color: #0d9488; border-color: #0d9488; background: #fff; display: inline-flex; align-items: center; gap: 0.3rem;" title="Xem chi tiết hồ sơ & đánh giá">
                                        <?= icon('user', '', 13) ?> <span>Hồ Sơ</span>
                                    </a>
                                    <?php if ($app['request_status'] === 'accepted'): ?>
                                        <!-- Khi đã duyệt: Hiện nút HỦY DUYỆT và nút XÓA -->
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="pending">
                                            <button type="button" class="btn btn-warning btn-sm" data-reject-prompt="Lý Do Hủy Duyệt Hồ Sơ Ứng Tuyển">
                                                Hủy Duyệt
                                            </button>
                                        </form>
                                        <form action="<?= url('/employer/applications/delete/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-confirm="Bạn có chắc chắn muốn xóa hồ sơ ứng tuyển này?">
                                                Xóa
                                            </button>
                                        </form>
                                    <?php elseif ($app['request_status'] === 'pending'): ?>
                                        <!-- Khi chờ duyệt: Hiện nút DUYỆT và TỪ CHỐI -->
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
