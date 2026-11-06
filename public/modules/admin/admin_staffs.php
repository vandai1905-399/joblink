<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Quản Lý Nhân Viên Nội Bộ</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Đội ngũ quản trị, kiểm duyệt nội dung và nhân viên chăm sóc khách hàng</p>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Mã NV</th>
                    <th>Họ và Tên</th>
                    <th>Bộ phận</th>
                    <th>Vai trò đảm nhiệm</th>
                    <th>Số điện thoại</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffs as $st): ?>
                    <tr>
                        <td><strong><?= formatId('staff', $st['staff_id']) ?></strong></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <img src="<?= htmlspecialchars(getAvatarUrl($st['avatar_url'] ?? '', 'STAFF')) ?>" alt="avatar" style="width: 38px; height: 38px; border-radius: var(--radius-full); object-fit: cover;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'STAFF') ?>';">
                                <strong><?= htmlspecialchars($st['full_name']) ?></strong>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($st['department']) ?></td>
                        <td><?= htmlspecialchars($st['position']) ?></td>
                        <td><?= htmlspecialchars($st['phone_number']) ?></td>
                        <td><span class="badge badge-success">Đang làm việc</span></td>
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
