<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">
        Tổng quan người trung gian tuyển dụng
    </h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">
        Quản lý các đối tác doanh nghiệp ủy quyền và theo dõi hồ sơ người lao động kết nối.
    </p>
</div>

<!-- 3 THẺ THỐNG KÊ -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <div class="card" style="padding: 1.25rem; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Hợp đồng Ủy Quyền hợp pháp</div>
        <div style="font-size: 2rem; font-weight: 800; color: #0d9488; margin-top: 0.35rem;">
            <?= count(array_filter($authorizations, fn($a) => $a['admin_verification_status'] === 'verified')) ?>
        </div>
        <div style="font-size: 0.75rem; color: #0f766e;">Được cấp phép đại diện tuyển dụng</div>
    </div>
    <div class="card" style="padding: 1.25rem; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Ủy Quyền chờ BQT Duyệt</div>
        <div style="font-size: 2rem; font-weight: 800; color: #f59e0b; margin-top: 0.35rem;">
            <?= count(array_filter($authorizations, fn($a) => $a['admin_verification_status'] === 'pending')) ?>
        </div>
        <div style="font-size: 0.75rem; color: #d97706;">Đang xử lý thẩm định</div>
    </div>
    <div class="card" style="padding: 1.25rem; border-left: 4px solid #6366f1;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Tổng doanh nghiệp Liên kết</div>
        <div style="font-size: 2rem; font-weight: 800; color: #6366f1; margin-top: 0.35rem;">
            <?= count($authorizations) ?>
        </div>
        <div style="font-size: 0.75rem; color: #4f46e5;">Đối tác tuyển dụng</div>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a;">danh sách doanh nghiệp Ủy Quyền</h3>
        <a href="<?= url('/intermediary/settings') ?>" class="btn btn-outline btn-sm">
            <?= icon('settings', '', 14) ?> Cài đặt hồ sơ & Ủy Quyền
        </a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Mã UQ</th>
                    <th>doanh nghiệp</th>
                    <th>Thời hạn</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($authorizations)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: #64748b;">
                            Bạn chưa có văn bản ủy quyền nào từ doanh nghiệp.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($authorizations as $a): ?>
                        <tr>
                            <td><strong style="color: #0d9488; font-family: monospace;"><?= htmlspecialchars($a['authorization_code']) ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($a['employer_name']) ?></strong>
                                <div style="font-size: 0.75rem; color: #64748b;">MST: <?= htmlspecialchars($a['business_license_number'] ?? '—') ?></div>
                            </td>
                            <td style="font-size: 0.8rem;"><?= date('d/m/Y', strtotime($a['valid_from'])) ?> - <?= date('d/m/Y', strtotime($a['valid_to'])) ?></td>
                            <td>
                                <?php if ($a['admin_verification_status'] === 'verified'): ?>
                                    <span class="badge badge-success"><?= icon('shield_check', '', 12) ?> Hợp pháp</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">chờ duyệt</span>
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
