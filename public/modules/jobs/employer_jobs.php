<?php ob_start(); ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Quản Lý Tin Tuyển Dụng</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem;">Danh sách các bài đăng tuyển dụng của doanh nghiệp bạn</p>
    </div>
    <a href="/employer/jobs/create" class="btn btn-primary">+ Đăng Tin Mới</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Chức danh công việc</th>
                    <th>Hình thức / Ca làm</th>
                    <th>Mức lương</th>
                    <th>Số lượng</th>
                    <th>Hạn nộp</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--slate-400); padding: 2rem;">Chưa có bài đăng nào. Hãy đăng tin đầu tiên!</td></tr>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td>
                                <strong><a href="/jobs/<?= $job['job_post_id'] ?>"><?= htmlspecialchars($job['job_title']) ?></a></strong>
                            </td>
                            <td>
                                <?php 
                                    if (($job['employment_type'] ?? '') === 'full_time') echo '<span class="badge badge-primary">Toàn thời gian</span>';
                                    elseif (($job['employment_type'] ?? '') === 'part_time') echo '<span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:600;">Bán thời gian</span>';
                                    elseif (($job['employment_type'] ?? '') === 'temporary') echo '<span class="badge" style="background:#fef3c7; color:#92400e; font-weight:600;">Thời vụ</span>';
                                    else echo '<span class="badge badge-primary">Toàn thời gian</span>';
                                ?><br>
                                <span style="font-size: 0.82rem; color: var(--slate-500);"><?= htmlspecialchars($job['work_shift'] ?? 'Hành chính') ?></span>
                            </td>
                            <td style="font-weight: 700; color: var(--primary); white-space: nowrap;"><?= formatSalary($job['salary_amount'], $job['employment_type'] ?? 'full_time') ?></td>
                            <td><?= $job['number_of_positions'] ?> người</td>
                            <td><?= date('d/m/Y', strtotime($job['application_deadline'])) ?></td>
                            <td>
                                <?php if ($job['post_status'] === 'approved'): ?>
                                    <span class="badge badge-success"><?= icon('check', '', 12) ?> Đang hiển thị</span>
                                <?php elseif ($job['post_status'] === 'pending'): ?>
                                    <span class="badge badge-warning"><?= icon('clock', '', 12) ?> Chờ duyệt</span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><?= icon('x', '', 12) ?> Đã ẩn / Từ chối</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.35rem;">
                                    <a href="/employer/jobs/edit/<?= $job['job_post_id'] ?>" class="btn btn-outline btn-sm">Sửa</a>
                                    <a href="/employer/jobs/delete/<?= $job['job_post_id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa tin này?')" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:var(--danger-light);">Xóa</a>
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
