<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Cấu Hình Tiêu Chí Đánh Giá Ca Làm Việc</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Quản lý các tiêu chí đánh giá chuẩn hóa cho Người lao động và Nhà tuyển dụng</p>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Đối tượng áp dụng</th>
                    <th>Tên tiêu chí nhận xét</th>
                    <th>Thứ tự hiển thị</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($criteria as $crit): ?>
                    <tr>
                        <td>#<?= $crit['criteria_id'] ?></td>
                        <td>
                            <span class="badge <?= $crit['target_type'] === 'employer' ? 'badge-primary' : 'badge-warning' ?>" style="display: inline-flex; align-items: center; gap: 0.3rem;">
                                <?= $crit['target_type'] === 'employer' ? icon('building', '', 13) . ' <span>Đánh giá nơi làm việc</span>' : icon('user', '', 13) . ' <span>Đánh giá người lao động</span>' ?>
                            </span>
                        </td>
                        <td><strong><?= htmlspecialchars($crit['criteria_name']) ?></strong></td>
                        <td>Thứ tự: <?= $crit['display_order'] ?></td>
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
