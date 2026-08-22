<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Quản Lý Tài Khoản Hệ Thống</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Danh sách toàn bộ tài khoản Admin, Staff, Doanh nghiệp và Người lao động</p>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Email</th>
                    <th>Vai trò / Quyền</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accounts as $acc): ?>
                    <tr>
                        <td><strong><?= formatId('account', $acc['account_id']) ?></strong></td>
                        <td><code><?= htmlspecialchars($acc['username']) ?></code></td>
                        <td><?= htmlspecialchars($acc['email']) ?></td>
                        <td>
                            <?php 
                                $badgeClass = 'badge-primary';
                                if ($acc['role_code'] === 'SUPER_ADMIN') $badgeClass = 'badge-danger';
                                elseif ($acc['role_code'] === 'EMPLOYER_ADMIN') $badgeClass = 'badge-warning';
                                elseif ($acc['role_code'] === 'JOB_SEEKER') $badgeClass = 'badge-slate';
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($acc['role_name'] ?? $acc['role_code']) ?></span>
                        </td>
                        <td>
                            <span class="badge badge-success"><?= icon('check', '', 12) ?> Hoạt động</span>
                        </td>
                        <td>
                            <button class="btn btn-outline btn-sm">Chi tiết</button>
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
