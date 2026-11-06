<?php ob_start(); ?>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">
            <?= icon('certificate', 'text-primary', 24) ?> Xác Minh Chữ Ký Số & Thẩm Định Pháp Lý Doanh Nghiệp
        </h1>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin-top: 0.25rem;">
            Bảo mật thông tin pháp lý doanh nghiệp. Chỉ Quản trị viên cấp cao có quyền truy cập và ký hợp đồng điện tử.
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= url('/admin/contracts') ?>" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
            <?= icon('file_signature', '', 16) ?> <span>Quản Lý Hợp Đồng Điện Tử (<?= count($employers) ?>)</span>
        </a>
    </div>
</div>

<!-- THỐNG KÊ NHANH -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #10b981;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Đã Xác Thực Chữ Ký Số</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #10b981; margin-top: 0.25rem;">
            <?= count(array_filter($employers, fn($e) => ($e['digital_signature_status'] ?? '') === 'verified')) ?>
        </div>
        <div style="font-size: 0.75rem; color: #059669;">Được cấp huy hiệu & ưu tiên Top 1</div>
    </div>
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Chờ Duyệt GPKD / Chữ Ký Số</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #f59e0b; margin-top: 0.25rem;">
            <?= count(array_filter($employers, fn($e) => ($e['verification_status'] ?? '') === 'pending')) ?>
        </div>
        <div style="font-size: 0.75rem; color: #d97706;">Cần BQT thẩm định pháp lý</div>
    </div>
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #6366f1;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Tổng Doanh Nghiệp Trên Sàn</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #6366f1; margin-top: 0.25rem;">
            <?= count($employers) ?>
        </div>
        <div style="font-size: 0.75rem; color: #4f46e5;">Đã khởi tạo tài khoản</div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Mã DN</th>
                    <th>Doanh Nghiệp</th>
                    <th>Thông Tin Bảo Mật (Chỉ Admin)</th>
                    <th>Chứng Thư Số (CA)</th>
                    <th>Trạng Thái Pháp Lý</th>
                    <th>Thao Tác Quản Trị</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employers as $emp): 
                    $isPending = ($emp['verification_status'] ?? '') === 'pending';
                ?>
                    <tr style="<?= $isPending ? 'background: #fffbeb;' : '' ?>">
                        <td><strong><?= formatId('employer', $emp['employer_id']) ?></strong></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <a href="<?= url('/employers/' . $emp['employer_id']) ?>" class="workspace-nav-link" data-title="DN: <?= htmlspecialchars($emp['employer_name']) ?>" title="Xem trang doanh nghiệp <?= htmlspecialchars($emp['employer_name']) ?>" style="display: block; flex-shrink: 0; text-decoration: none;">
                                    <img src="<?= htmlspecialchars(getAvatarUrl($emp['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" style="width: 38px; height: 38px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                                </a>
                                <div>
                                    <a href="<?= url('/employers/' . $emp['employer_id']) ?>" class="workspace-nav-link" data-title="DN: <?= htmlspecialchars($emp['employer_name']) ?>" title="Bấm mở tab xem trang doanh nghiệp: <?= htmlspecialchars($emp['employer_name']) ?>" style="color: #0f172a; font-size: 0.95rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" onmouseover="this.style.color='var(--primary)'; this.style.textDecoration='underline';" onmouseout="this.style.color='#0f172a'; this.style.textDecoration='none';">
                                        <span><?= htmlspecialchars($emp['employer_name']) ?></span>
                                    </a>
                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.1rem; display: flex; align-items: center; gap: 0.4rem;">
                                        <span><?= htmlspecialchars($emp['employer_type']) ?> &bull; <?= htmlspecialchars($emp['representative_name'] ?? 'Chưa cập nhật') ?></span>
                                        <a href="<?= url('/employers/' . $emp['employer_id']) ?>" class="workspace-nav-link" data-title="DN: <?= htmlspecialchars($emp['employer_name']) ?>" style="color: var(--primary); font-size: 0.74rem; font-weight: 600; text-decoration: none;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">Xem hồ sơ</a>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.82rem; line-height: 1.5;">
                                <div><strong>MST/GPKD:</strong> <code style="background: #f1f5f9; padding: 2px 5px; border-radius: 4px;"><?= htmlspecialchars($emp['business_license_number'] ?? 'Chưa nộp') ?></code></div>
                                <div><strong>Email:</strong> <?= htmlspecialchars($emp['contact_email'] ?? '—') ?></div>
                                <div><strong>Hotline:</strong> <?= htmlspecialchars($emp['phone_number'] ?? '—') ?></div>
                            </div>
                        </td>
                        <td>
                            <?php if (($emp['digital_signature_status'] ?? '') === 'verified'): ?>
                                <span class="badge badge-success" style="font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <?= icon('certificate', '', 13) ?> <span><?= htmlspecialchars($emp['digital_cert_ca'] ?? 'VNPT-CA') ?> (Đã Ký Số)</span>
                                </span>
                                <div style="font-size: 0.72rem; color: #059669; margin-top: 0.2rem; font-family: monospace;">
                                    SN: <?= htmlspecialchars(substr($emp['digital_cert_serial'] ?? 'VNPT-CA-001', 0, 16)) ?>...
                                </div>
                            <?php elseif (($emp['digital_signature_status'] ?? '') === 'pending'): ?>
                                <span class="badge badge-warning" style="font-size: 0.78rem;">Chờ thẩm định CA</span>
                            <?php else: ?>
                                <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.75rem;">Chưa đăng ký chữ ký số</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($emp['verification_status'] === 'verified'): ?>
                                <span class="badge badge-success"><?= icon('check_circle', '', 13) ?> <span>Đã Phê Duyệt</span></span>
                            <?php elseif ($emp['verification_status'] === 'failed'): ?>
                                <span class="badge badge-danger"><?= icon('x_circle', '', 13) ?> <span>Từ Chối</span></span>
                            <?php else: ?>
                                <span class="badge badge-warning"><?= icon('clock', '', 13) ?> <span>Chờ Duyệt</span></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                                <a href="<?= url('/employers/' . $emp['employer_id']) ?>" class="btn btn-outline btn-sm workspace-nav-link" data-title="DN: <?= htmlspecialchars($emp['employer_name']) ?>" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #0284c7; border-color: #bae6fd; background: #f0f9ff; display: inline-flex; align-items: center; gap: 0.25rem;" title="Xem trang doanh nghiệp">
                                    <?= icon('building', '', 12) ?> Xem Trang DN
                                </a>
                                <button type="button" class="btn btn-outline btn-sm" onclick="showSecuredModal(<?= htmlspecialchars(json_encode($emp)) ?>)" style="font-size: 0.75rem; padding: 0.25rem 0.55rem;">
                                    <?= icon('shield_check', '', 13) ?> Chi Tiết Pháp Lý
                                </button>

                                <?php if ($emp['verification_status'] === 'verified'): ?>
                                    <form action="<?= url('/admin/employers/verify/' . $emp['employer_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="failed">
                                        <button type="submit" class="btn btn-danger btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" onclick="return confirm('Bạn có chắc chắn muốn thu hồi xác thực của doanh nghiệp này?');">
                                            Thu Hồi
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= url('/admin/employers/verify/' . $emp['employer_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="verified">
                                        <button type="submit" class="btn btn-success btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                            Xác Minh
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

<!-- MODAL CHI TIẾT BẢO MẬT PHÁP LÝ & CHỨNG THƯ SỐ (CHỈ ADMIN THẤY) -->
<div id="securedModal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#fff; border-radius:12px; width:95%; max-width:650px; padding:2rem; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); position:relative; max-height:90vh; overflow-y:auto;">
        <button onclick="closeSecuredModal()" style="position:absolute; top:1.2rem; right:1.2rem; border:none; background:transparent; font-size:1.5rem; cursor:pointer; color:#64748b;">&times;</button>
        
        <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:1.25rem; border-bottom:1px solid #e2e8f0; padding-bottom:0.75rem;">
            <?= icon('shield_check', 'text-primary', 24) ?>
            <div>
                <h3 style="margin:0; font-size:1.2rem; color:#0f172a;" id="modalEmpName">Hồ Sơ Pháp Lý Doanh Nghiệp</h3>
                <span style="font-size:0.75rem; color:#dc2626; font-weight:700;">[CHẾ ĐỘ BẢO MẬT: CHỈ ADMIN QUẢN TRỊ ĐƯỢC PHÉP TRUY CẬP]</span>
            </div>
        </div>

        <div id="modalBody" style="font-size:0.88rem; color:#334155; line-height:1.7;">
            <!-- Render JS -->
        </div>

        <div style="margin-top:1.5rem; text-align:right; border-top:1px solid #e2e8f0; padding-top:1rem; display:flex; justify-content:flex-end; gap:0.5rem;">
            <a href="<?= url('/admin/contracts') ?>" class="btn btn-primary btn-sm">
                <?= icon('file_signature', '', 14) ?> Đi Đến Quản Lý Hợp Đồng Điện Tử
            </a>
            <button type="button" class="btn btn-outline btn-sm" onclick="closeSecuredModal()">Đóng</button>
        </div>
    </div>
</div>

<script>
function showSecuredModal(emp) {
    document.getElementById('modalEmpName').textContent = emp.employer_name;
    var html = `
        <table style="width:100%; border-collapse:collapse; margin-bottom:1rem;">
            <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:6px 0; font-weight:700; width:38%;">Mã Doanh Nghiệp:</td><td><code>EM${emp.employer_id}</code></td></tr>
            <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:6px 0; font-weight:700;">Mã Số Thuế / GPKD:</td><td><strong style="color:#0d9488;">${emp.business_license_number || 'Chưa cung cấp'}</strong></td></tr>
            <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:6px 0; font-weight:700;">Người Đại Diện Pháp Luật:</td><td>${emp.representative_name || 'Chưa cập nhật'}</td></tr>
            <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:6px 0; font-weight:700;">Email Liên Hệ Pháp Lý:</td><td>${emp.contact_email || '—'}</td></tr>
            <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:6px 0; font-weight:700;">Số Điện Thoại Trụ Sở:</td><td>${emp.phone_number || '—'}</td></tr>
            <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:6px 0; font-weight:700;">Địa Chỉ Đăng Ký Kinh Doanh:</td><td>${emp.address || '—'}</td></tr>
            <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:6px 0; font-weight:700;">Nhà Cung Cấp Chữ Ký Số:</td><td><strong>${emp.digital_cert_ca || 'VNPT-CA / Viettel-CA'}</strong></td></tr>
            <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:6px 0; font-weight:700;">Số Serial Chứng Thư Số:</td><td><code style="font-size:0.75rem; word-break:break-all;">${emp.digital_cert_serial || 'Chưa liên kết token'}</code></td></tr>
            <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:6px 0; font-weight:700;">Mã Băm SHA-256 Fingerprint:</td><td><code style="font-size:0.72rem; word-break:break-all; color:#6366f1;">${emp.digital_cert_fp || 'Chưa khởi tạo mã băm'}</code></td></tr>
        </table>
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px; font-size:0.82rem; color:#166534;">
            <strong style="display:inline-flex; align-items:center; gap:4px;"><svg class="icon-svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;display:inline-block;"><polyline points="20 6 9 17 4 12"></polyline></svg> Thẩm Định Pháp Lý:</strong> Doanh nghiệp này đã được xác thực mã số thuế và chữ ký số. Người tìm việc trên nền tảng chỉ xem được các thông tin công khai (Tên DN, Địa chỉ làm việc, Huy hiệu Đã Ký Số).
        </div>
    `;
    document.getElementById('modalBody').innerHTML = html;
    document.getElementById('securedModal').style.display = 'flex';
}
function closeSecuredModal() {
    document.getElementById('securedModal').style.display = 'none';
}
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php';
?>
