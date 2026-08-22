<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 0.5rem;">
        <?= icon('handshake', 'text-primary', 26) ?> Hồ Sơ Người Trung Gian & Hợp Đồng Pháp Lý
    </h1>
    <p style="color: var(--slate-500); font-size: 0.95rem; margin-top: 0.25rem;">
        Quản lý thông tin pháp nhân đại lý tuyển dụng và các văn bản ủy quyền hợp pháp từ Doanh nghiệp.
    </p>
</div>

<div style="display: grid; grid-template-columns: 1.1fr 1.4fr; gap: 1.5rem; align-items: start;">
    <!-- THÔNG TIN ĐẠI LÝ MÔI GIỚI -->
    <div class="card">
        <h3 style="margin: 0 0 1.25rem 0; font-size: 1.15rem; color: #0f172a;">Thông Tin Cá Nhân / Đơn Vị Trung Gian</h3>
        
        <form action="<?= url('/intermediary/settings') ?>" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Họ Và Tên Người Đại Diện *</label>
                <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($intermediary['full_name'] ?? $_SESSION['user']['username']) ?>" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
            </div>

            <div>
                <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Tên Văn Phòng / Đại Lý Cung Ứng Nhân Lực</label>
                <input type="text" name="company_agency_name" class="form-control" placeholder="Ví dụ: Đại lý Nhân lực Bình Dương Phát..." value="<?= htmlspecialchars($intermediary['company_agency_name'] ?? '') ?>" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Số CCCD / CMND</label>
                    <input type="text" name="id_card_number" class="form-control" value="<?= htmlspecialchars($intermediary['id_card_number'] ?? '') ?>" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Số Điện Thoại</label>
                    <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($intermediary['phone_number'] ?? '') ?>" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
            </div>

            <div>
                <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($intermediary['email'] ?? $_SESSION['user']['email'] ?? '') ?>" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
            </div>

            <div>
                <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Địa Chỉ Văn Phòng</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($intermediary['address'] ?? '') ?>" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
            </div>

            <div style="text-align: right; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                    Lưu Thông Tin Đại Lý
                </button>
            </div>
        </form>
    </div>

    <!-- DANH SÁCH ỦY QUYỀN TỪ CÁC DOANH NGHIỆP -->
    <div class="card">
        <h3 style="margin: 0 0 1rem 0; font-size: 1.15rem; color: #0f172a;">Hợp Đồng Ủy Quyền Pháp Lý Từ Doanh Nghiệp</h3>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã UQ</th>
                        <th>Doanh Nghiệp</th>
                        <th>Thời Hạn</th>
                        <th>Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($authorizations)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: #64748b;">
                                Bạn chưa nhận được văn bản ủy quyền nào từ Doanh nghiệp. Hãy liên hệ các đối tác để được cấp quyền tuyển dụng.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($authorizations as $a): ?>
                            <tr>
                                <td><strong style="color: #0d9488; font-family: monospace;"><?= htmlspecialchars($a['authorization_code']) ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($a['employer_name']) ?></strong>
                                    <div style="font-size: 0.72rem; color: #64748b;">MST: <?= htmlspecialchars($a['business_license_number'] ?? '—') ?></div>
                                </td>
                                <td style="font-size: 0.78rem;">
                                    <?= date('d/m/Y', strtotime($a['valid_from'])) ?> &rarr; <?= date('d/m/Y', strtotime($a['valid_to'])) ?>
                                </td>
                                <td>
                                    <?php if ($a['admin_verification_status'] === 'verified'): ?>
                                        <span class="badge badge-success"><?= icon('shield_check', '', 12) ?> Hợp Pháp</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Chờ BQT Duyệt</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
