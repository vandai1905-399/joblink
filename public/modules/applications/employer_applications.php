<?php 
$extraCss = ['css/applications/employer_applications.css', 'css/jobs/ai_matching.css'];
$extraJs = 'js/applications/applications.js';

// Danh sách các tin tuyển dụng của NTD để làm bộ lọc
$employerJobsList = [];
if (!empty($employer['employer_id'])) {
    $employerJobsList = JobPost::getByEmployer($employer['employer_id']);
}

$filterJobId = intval($_GET['job_id'] ?? 0);
$filterStatus = trim($_GET['status'] ?? 'all');

$filteredApplications = array_filter($applications, function($app) use ($filterJobId, $filterStatus) {
    if ($filterJobId > 0 && intval($app['job_post_id']) !== $filterJobId) {
        return false;
    }
    if ($filterStatus !== 'all') {
        if ($filterStatus === 'pending' && $app['request_status'] !== 'pending') return false;
        if ($filterStatus === 'accepted' && !in_array($app['request_status'], ['accepted', 'invitation_accepted'])) return false;
        if ($filterStatus === 'invited' && $app['request_status'] !== 'invited') return false;
        if ($filterStatus === 'rejected' && !in_array($app['request_status'], ['rejected', 'invitation_rejected'])) return false;
    }
    return true;
});

ob_start(); 
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Quản Lý Hồ Sơ Ứng Tuyển (Xem Chung)</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem;">Tổng hợp toàn bộ người lao động nộp hồ sơ hoặc nhận lời mời từ tất cả các vị trí tuyển dụng</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= url('/employer/jobs') ?>" class="btn btn-outline" style="font-weight: 600;">
            <?= icon('list', '', 15) ?> Xem Theo Từng Tin Tuyển Dụng (Riêng)
        </a>
    </div>
</div>

<!-- BỘ LỌC TỔNG HỢP (FILTER BAR) -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #fff;">
    <form method="GET" action="<?= url('/employer/applications') ?>" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 220px;">
            <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Lọc theo công việc:</label>
            <select name="job_id" class="form-control" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                <option value="0">-- Tất cả vị trí công việc (<?= count($applications) ?>) --</option>
                <?php foreach ($employerJobsList as $j): ?>
                    <option value="<?= $j['job_post_id'] ?>" <?= $filterJobId === intval($j['job_post_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($j['job_title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="min-width: 180px;">
            <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Trạng thái hồ sơ:</label>
            <select name="status" class="form-control" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="accepted" <?= $filterStatus === 'accepted' ? 'selected' : '' ?>>Đã duyệt nhận</option>
                <option value="invited" <?= $filterStatus === 'invited' ? 'selected' : '' ?>>Lời mời từ NTD</option>
                <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Đã từ chối</option>
            </select>
        </div>

        <?php if ($filterJobId > 0 || $filterStatus !== 'all'): ?>
            <div style="align-self: flex-end;">
                <a href="<?= url('/employer/applications') ?>" class="btn btn-outline btn-sm" style="padding: 0.5rem 0.75rem;">
                    Xóa bộ lọc
                </a>
            </div>
        <?php endif; ?>
    </form>
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
                    <th style="min-width: 180px;">Xử lý</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filteredApplications)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--slate-400); padding: 2.5rem;">Không tìm thấy hồ sơ nào phù hợp với bộ lọc.</td></tr>
                <?php else: ?>
                    <?php foreach ($filteredApplications as $app): ?>
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
                                <strong>
                                    <a href="<?= url('/employer/jobs/' . $app['job_post_id'] . '/candidates') ?>" style="color: #0f172a; text-decoration: none;">
                                        <?= htmlspecialchars($app['job_title']) ?>
                                    </a>
                                </strong>
                                <?php if (!empty($app['available_work_dates'])): ?>
                                    <br><span style="font-size: 0.8rem; color: #0d9488; font-weight: 600; background: #f0fdfa; border: 1px solid #ccfbf1; padding: 0.15rem 0.45rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.3rem;">
                                        <?= icon('calendar', '', 12) ?> <span><?= htmlspecialchars($app['available_work_dates']) ?></span>
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
                            <td style="color: #475569; font-size: 0.82rem;"><?= date('d/m/Y H:i', strtotime($app['applied_at'])) ?></td>
                            <td>
                                <?php if ($app['request_status'] === 'accepted' || $app['request_status'] === 'invitation_accepted'): ?>
                                    <span class="badge badge-success"><?= icon('check', '', 13) ?> <span>Đã duyệt nhận</span></span>
                                <?php elseif ($app['request_status'] === 'invited'): ?>
                                    <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:600;"><span>Đã gửi lời mời</span></span>
                                <?php elseif ($app['request_status'] === 'rejected' || $app['request_status'] === 'invitation_rejected'): ?>
                                    <span class="badge badge-danger"><span>Đã từ chối</span></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><span>Chờ duyệt</span></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                                    <a href="<?= url('/employer/candidates/' . $app['seeker_id']) ?>" class="btn btn-outline btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem; font-weight: 700; color: #0d9488; border-color: #0d9488; background: #fff;" title="Xem chi tiết hồ sơ">
                                        <?= icon('user', '', 12) ?> Hồ Sơ
                                    </a>

                                    <?php if ($app['request_status'] === 'accepted' || $app['request_status'] === 'invitation_accepted'): ?>
                                        <a href="<?= url('/employer/jobs/' . $app['job_post_id'] . '/candidates?tab=applicants') ?>" class="btn btn-primary btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem; font-weight: 700;">
                                            💬 Chốt Lịch (AI)
                                        </a>
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="pending">
                                            <button type="button" class="btn btn-warning btn-sm" data-reject-prompt="Lý Do Hủy Duyệt Hồ Sơ Ứng Tuyển" style="padding: 0.3rem 0.55rem; font-size: 0.78rem;">
                                                Hủy
                                            </button>
                                        </form>
                                    <?php elseif ($app['request_status'] === 'pending'): ?>
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="accepted">
                                            <button type="submit" class="btn btn-success btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem;">
                                                Duyệt
                                            </button>
                                        </form>
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="button" class="btn btn-danger btn-sm" data-reject-prompt="Lý Do Từ Chối Hồ Sơ Người Lao Động" style="padding: 0.3rem 0.55rem; font-size: 0.78rem;">
                                                Từ chối
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="accepted">
                                            <button type="submit" class="btn btn-success btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem;">
                                                Duyệt Lại
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
