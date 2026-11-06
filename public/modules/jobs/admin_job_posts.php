<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Kiểm Duyệt Tin Tuyển Dụng</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Duyệt các bài đăng việc làm mới từ Nhà tuyển dụng trước khi hiển thị công khai</p>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Chức danh công việc</th>
                    <th>Doanh nghiệp</th>
                    <th>Mức lương</th>
                    <th>Hạn nộp</th>
                    <th>Ngày đăng</th>
                    <th>Trạng thái</th>
                    <th>Kiểm duyệt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $j): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?= url('/jobs/' . $j['job_post_id']) ?>" class="workspace-nav-link" data-title="Tin: <?= htmlspecialchars($j['job_title']) ?>" style="color: #0f172a; text-decoration: underline;" title="Bấm mở tab xem trước tin tuyển dụng">
                                    <?= htmlspecialchars($j['job_title']) ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <?php if (!empty($j['employer_id'])): ?>
                                <a href="<?= url('/employers/' . $j['employer_id']) ?>" class="workspace-nav-link" data-title="DN: <?= htmlspecialchars($j['employer_name']) ?>" style="color: #0d9488; font-weight: 700; text-decoration: none;" title="Bấm mở tab xem trang doanh nghiệp">
                                    <?= htmlspecialchars($j['employer_name'] ?? 'NTD') ?>
                                </a>
                            <?php else: ?>
                                <span style="color: #334155; font-weight: 500;"><?= htmlspecialchars($j['employer_name'] ?? 'NTD') ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--primary); font-weight: 800; font-size: 0.95rem; white-space: nowrap;"><?= formatSalary($j['salary_amount'], $j['employment_type'] ?? 'full_time') ?></td>
                        <td style="color: #475569;"><?= date('d/m/Y', strtotime($j['application_deadline'])) ?></td>
                        <td style="color: #475569;"><?= date('d/m/Y', strtotime($j['posted_at'])) ?></td>
                        <td>
                            <?php if ($j['post_status'] === 'approved'): ?>
                                <span class="badge badge-success"><?= icon('check', '', 13) ?> <span>Đã duyệt</span></span>
                            <?php elseif ($j['post_status'] === 'pending'): ?>
                                <span class="badge badge-warning"><span>Chờ duyệt</span></span>
                            <?php else: ?>
                                <span class="badge badge-danger"><span>Từ chối</span></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem; align-items: center;">
                                <?php if ($j['post_status'] === 'approved'): ?>
                                    <!-- Khi đã duyệt: Hiện nút HỦY DUYỆT và nút XÓA -->
                                    <form action="<?= url('/admin/job-posts/approve/' . $j['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="pending">
                                        <button type="button" class="btn btn-warning btn-sm" data-reject-prompt="Lý Do Hủy Duyệt Tin Tuyển Dụng">
                                            Hủy Duyệt
                                        </button>
                                    </form>
                                    <form action="<?= url('/admin/job-posts/delete/' . $j['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-confirm="Bạn có chắc chắn muốn xóa vĩnh viễn tin tuyển dụng này?">
                                            Xóa
                                        </button>
                                    </form>
                                <?php elseif ($j['post_status'] === 'pending'): ?>
                                    <!-- Khi chờ duyệt: Hiện nút DUYỆT TIN và TỪ CHỐI -->
                                    <form action="<?= url('/admin/job-posts/approve/' . $j['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            Duyệt Tin
                                        </button>
                                    </form>
                                    <form action="<?= url('/admin/job-posts/approve/' . $j['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="button" class="btn btn-danger btn-sm" data-reject-prompt="Lý Do Từ Chối Tin Tuyển Dụng">
                                            Từ Chối
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Khi từ chối: Hiện nút DUYỆT LẠI và XÓA -->
                                    <form action="<?= url('/admin/job-posts/approve/' . $j['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            Duyệt Lại
                                        </button>
                                    </form>
                                    <form action="<?= url('/admin/job-posts/delete/' . $j['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-confirm="Bạn có chắc chắn muốn xóa vĩnh viễn tin tuyển dụng này?">
                                            Xóa
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php';
?>
