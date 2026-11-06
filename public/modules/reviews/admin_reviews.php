<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Kiểm Duyệt Đánh Giá Ca Làm Việc</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Đảm bảo nội dung đánh giá 2 chiều minh bạch, không vi phạm tiêu chuẩn cộng đồng</p>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Loại</th>
                    <th>Doanh nghiệp</th>
                    <th>Người lao động</th>
                    <th>Tiêu chí & Điểm</th>
                    <th>Nội dung nhận xét</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $rev): ?>
                    <tr>
                        <td>
                            <span class="badge <?= $rev['review_type'] === 'employer_review' ? 'badge-primary' : 'badge-warning' ?>">
                                <?= $rev['review_type'] === 'employer_review' ? 'NLĐ đánh giá NTD' : 'NTD đánh giá NLĐ' ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($rev['employer_id'])): ?>
                                <a href="<?= url('/employers/' . $rev['employer_id']) ?>" class="workspace-nav-link" data-title="DN: <?= htmlspecialchars($rev['employer_name']) ?>" style="color: #0d9488; font-weight: 700; text-decoration: none;" title="Bấm mở tab xem hồ sơ doanh nghiệp">
                                    <?= htmlspecialchars($rev['employer_name']) ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($rev['employer_name']) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($rev['seeker_id'])): ?>
                                <a href="<?= url('/employer/candidates/' . $rev['seeker_id']) ?>" class="workspace-nav-link" data-title="NLĐ: <?= htmlspecialchars($rev['seeker_name']) ?>" style="color: #0f172a; font-weight: 700; text-decoration: underline;" title="Bấm mở tab xem hồ sơ người lao động">
                                    <?= htmlspecialchars($rev['seeker_name']) ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($rev['seeker_name']) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="display: inline-flex; align-items: center; gap: 0.2rem;"><?= icon('star_fill', '', 13) ?> <?= $rev['overall_rating'] ?>/5</strong><br>
                            <span style="font-size:0.8rem; color:var(--slate-400);"><?= htmlspecialchars($rev['criteria_name'] ?? 'Ca làm') ?></span>
                        </td>
                        <td style="font-size: 0.88rem; max-width: 300px;"><?= htmlspecialchars($rev['review_content']) ?></td>
                        <td>
                            <?php if ($rev['approval_status'] === 'approved'): ?>
                                <span class="badge badge-success"><?= icon('check', '', 13) ?> <span>Hiển thị</span></span>
                            <?php elseif ($rev['approval_status'] === 'pending'): ?>
                                <span class="badge badge-warning"><span>Chờ duyệt</span></span>
                            <?php else: ?>
                                <span class="badge badge-danger"><span>Đã ẩn</span></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem; align-items: center;">
                                <?php if ($rev['approval_status'] === 'approved'): ?>
                                    <!-- Khi đã duyệt: Hiện nút HỦY DUYỆT (ẨN) và nút XÓA -->
                                    <form action="<?= url('/admin/reviews/moderate/' . $rev['review_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="button" class="btn btn-warning btn-sm" style="font-weight: 600; padding: 0.35rem 0.65rem;" data-reject-prompt="Lý Do Hủy Duyệt (Ẩn) Đánh Giá">
                                            Hủy Duyệt
                                        </button>
                                    </form>
                                    <form action="<?= url('/admin/reviews/delete/' . $rev['review_id']) ?>" method="POST" style="display:inline;">
                                        <button type="button" class="btn btn-outline btn-sm btn-danger" style="color: var(--danger); border-color: var(--danger); font-weight: 600; padding: 0.35rem 0.65rem;" data-confirm="Bạn có chắc chắn muốn xóa vĩnh viễn đánh giá này?">
                                            Xóa
                                        </button>
                                    </form>
                                <?php elseif ($rev['approval_status'] === 'pending'): ?>
                                    <!-- Khi chờ duyệt: Hiện nút DUYỆT và ẨN -->
                                    <form action="<?= url('/admin/reviews/moderate/' . $rev['review_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success btn-sm" style="font-weight: 600; padding: 0.35rem 0.65rem;">
                                            Duyệt
                                        </button>
                                    </form>
                                    <form action="<?= url('/admin/reviews/moderate/' . $rev['review_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="button" class="btn btn-outline btn-sm btn-danger" style="color: var(--danger); border-color: var(--danger); font-weight: 600; padding: 0.35rem 0.65rem;" data-reject-prompt="Lý Do Từ Chối / Ẩn Đánh Giá">
                                            Ẩn
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Khi đã ẩn: Hiện nút DUYỆT LẠI và XÓA -->
                                    <form action="<?= url('/admin/reviews/moderate/' . $rev['review_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success btn-sm" style="font-weight: 600; padding: 0.35rem 0.65rem;">
                                            Duyệt Lại
                                        </button>
                                    </form>
                                    <form action="<?= url('/admin/reviews/delete/' . $rev['review_id']) ?>" method="POST" style="display:inline;">
                                        <button type="button" class="btn btn-outline btn-sm btn-danger" style="color: var(--danger); border-color: var(--danger); font-weight: 600; padding: 0.35rem 0.65rem;" data-confirm="Bạn có chắc chắn muốn xóa vĩnh viễn đánh giá này?">
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
