<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 0.5rem;">
        <?= icon('handshake', 'text-primary', 26) ?> Thẩm Định Pháp Lý: Doanh Nghiệp & Người Trung Gian
    </h1>
    <p style="color: var(--slate-500); font-size: 0.95rem; margin-top: 0.25rem;">
        Kiểm tra văn bản ủy quyền tuyển dụng pháp lý có chữ ký số của Doanh nghiệp để cấp quyền đại diện tuyển dụng cho Người trung gian / Đại lý cung ứng.
    </p>
</div>

<!-- THỐNG KÊ ỦY QUYỀN -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #10b981;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Đã Thẩm Định Hợp Pháp</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #10b981; margin-top: 0.25rem;">
            <?= count(array_filter($authorizations, fn($a) => $a['admin_verification_status'] === 'verified')) ?>
        </div>
        <div style="font-size: 0.75rem; color: #059669;">Được phép đại diện DN đăng tin</div>
    </div>
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Văn Bản Chờ Thẩm Định</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #f59e0b; margin-top: 0.25rem;">
            <?= count(array_filter($authorizations, fn($a) => $a['admin_verification_status'] === 'pending')) ?>
        </div>
        <div style="font-size: 0.75rem; color: #d97706;">Cần Admin kiểm tra chữ ký số DN</div>
    </div>
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #ef4444;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Bị Từ Chối / Hết Hạn</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #ef4444; margin-top: 0.25rem;">
            <?= count(array_filter($authorizations, fn($a) => $a['admin_verification_status'] === 'rejected')) ?>
        </div>
        <div style="font-size: 0.75rem; color: #dc2626;">Không đủ điều kiện pháp lý</div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Mã Ủy Quyền</th>
                    <th>Doanh Nghiệp Ủy Quyền</th>
                    <th>Người Trung Gian / Đại Lý</th>
                    <th>Phạm Vi Công Việc & Hoa Hồng</th>
                    <th>Thời Hạn</th>
                    <th>Chữ Ký Số DN</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($authorizations)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: #64748b;">
                            Chưa có hồ sơ ủy quyền pháp lý nào giữa Doanh nghiệp và Người trung gian.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($authorizations as $auth): ?>
                        <tr>
                            <td><strong style="color: #0d9488; font-family: monospace;"><?= htmlspecialchars($auth['authorization_code']) ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($auth['employer_name']) ?></strong>
                                <div style="font-size: 0.75rem; color: #64748b;">MST: <?= htmlspecialchars($auth['business_license_number'] ?? '—') ?></div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($auth['intermediary_name']) ?></strong>
                                <div style="font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($auth['company_agency_name'] ?? 'Môi giới cá nhân') ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem; color: #1e293b;">
                                    <div><strong>Phạm vi:</strong> <?= htmlspecialchars(mb_substr($auth['scope_of_work'], 0, 45)) ?>...</div>
                                    <div style="color: #0d9488;"><strong>Hoa hồng:</strong> <?= htmlspecialchars($auth['commission_rate']) ?></div>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.78rem; color: #475569;">
                                    <?= date('d/m/Y', strtotime($auth['valid_from'])) ?> &rarr; <?= date('d/m/Y', strtotime($auth['valid_to'])) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.72rem; color: #10b981; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('certificate', '', 12) ?> <span>Đã Ký Số</span>
                                </div>
                                <div style="font-size: 0.65rem; color: #64748b; font-family: monospace;">
                                    <?= substr($auth['dn_digital_signature_hash'] ?? '', 0, 16) ?>...
                                </div>
                            </td>
                            <td>
                                <?php if ($auth['admin_verification_status'] === 'verified'): ?>
                                    <span class="badge badge-success"><?= icon('shield_check', '', 12) ?> Hợp Pháp</span>
                                <?php elseif ($auth['admin_verification_status'] === 'rejected'): ?>
                                    <span class="badge badge-danger">Từ Chối</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Chờ Thẩm Định</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.35rem; align-items: center;">
                                    <?php if ($auth['admin_verification_status'] === 'verified'): ?>
                                        <form action="<?= url('/admin/authorizations/verify/' . $auth['auth_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-danger btn-sm" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;" onclick="return confirm('Bạn có chắc muốn thu hồi quyền đại diện tuyển dụng này?');">
                                                Thu Hồi
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= url('/admin/authorizations/verify/' . $auth['auth_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="verified">
                                            <button type="submit" class="btn btn-success btn-sm" style="font-size: 0.72rem; padding: 0.2rem 0.5rem; font-weight: 700;">
                                                Phê Duyệt
                                            </button>
                                        </form>
                                        <form action="<?= url('/admin/authorizations/verify/' . $auth['auth_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-danger btn-sm" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;">
                                                Từ Chối
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
require_once __DIR__ . '/../layouts/admin_layout.php';
?>
