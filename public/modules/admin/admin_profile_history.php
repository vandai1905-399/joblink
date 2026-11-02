<?php 
ob_start(); 
$searchVal = htmlspecialchars($search ?? '');
$targetTypeVal = $targetType ?? '';
$changeTypeVal = $changeType ?? '';
$logs = $result['data'] ?? [];
$totalLogs = $result['total'] ?? 0;
$totalPages = $result['pages'] ?? 1;
$currentPage = $result['current_page'] ?? 1;

// Đếm thống kê nhanh từ CSDL
$db = Database::getConnection();
$totalAll = (int)$db->query("SELECT COUNT(*) FROM profile_change_logs")->fetchColumn();
$totalSeeker = (int)$db->query("SELECT COUNT(*) FROM profile_change_logs WHERE target_type = 'SEEKER'")->fetchColumn();
$totalEmployer = (int)$db->query("SELECT COUNT(*) FROM profile_change_logs WHERE target_type = 'EMPLOYER'")->fetchColumn();
$totalAdminChanges = (int)$db->query("SELECT COUNT(*) FROM profile_change_logs WHERE change_type = 'ADMIN_UPDATE'")->fetchColumn();
$totalSelfChanges = (int)$db->query("SELECT COUNT(*) FROM profile_change_logs WHERE change_type = 'SELF_UPDATE'")->fetchColumn();
?>

<!-- ========================================================================= -->
<!-- 1. HEADER & GIỚI THIỆU NHẬT KÝ THAY ĐỔI HỒ SƠ                             -->
<!-- ========================================================================= -->
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 0.5rem;">
            <?= icon('clock', 'text-primary', 26) ?> Nhật Ký Thay Đổi Thông Tin Hồ Sơ (Audit Trail)
        </h1>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin-top: 0.25rem;">
            Lưu vết tự động 100% mọi chỉnh sửa thông tin của Người lao động và Nhà tuyển dụng (tự cập nhật hoặc do Quản trị viên can thiệp).
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= url('/admin/accounts') ?>" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
            <?= icon('nav_accounts_id', '', 15) ?> <span>Quản Lý Tài Khoản</span>
        </a>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. THẺ CHỈ SỐ KPI TỔNG HỢP                                                -->
<!-- ========================================================================= -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
    <div class="card" style="padding: 1.15rem; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.82rem; color: #64748b; font-weight: 600;">Tổng Lượt Thay Đổi</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">
            <?= number_format($totalAll) ?>
        </div>
        <div style="font-size: 0.75rem; color: #0d9488;">Lưu vết toàn bộ hệ thống</div>
    </div>

    <div class="card" style="padding: 1.15rem; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.82rem; color: #64748b; font-weight: 600;">Hồ Sơ Người Lao Động</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #0284c7; margin-top: 0.2rem;">
            <?= number_format($totalSeeker) ?>
        </div>
        <div style="font-size: 0.75rem; color: #0369a1;">Thông tin NLĐ đã thay đổi</div>
    </div>

    <div class="card" style="padding: 1.15rem; border-left: 4px solid #b45309;">
        <div style="font-size: 0.82rem; color: #64748b; font-weight: 600;">Hồ Sơ Nhà Tuyển Dụng</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #b45309; margin-top: 0.2rem;">
            <?= number_format($totalEmployer) ?>
        </div>
        <div style="font-size: 0.75rem; color: #92400e;">Thông tin công ty đã thay đổi</div>
    </div>

    <div class="card" style="padding: 1.15rem; border-left: 4px solid #dc2626;">
        <div style="font-size: 0.82rem; color: #64748b; font-weight: 600;">Quản Trị Viên Can Thiệp Sửa</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #dc2626; margin-top: 0.2rem;">
            <?= number_format($totalAdminChanges) ?>
        </div>
        <div style="font-size: 0.75rem; color: #b91c1c;">Bị Admin thay đổi trực tiếp</div>
    </div>

    <div class="card" style="padding: 1.15rem; border-left: 4px solid #16a34a;">
        <div style="font-size: 0.82rem; color: #64748b; font-weight: 600;">Người Dùng Tự Cập Nhật</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #16a34a; margin-top: 0.2rem;">
            <?= number_format($totalSelfChanges) ?>
        </div>
        <div style="font-size: 0.75rem; color: #15803d;">Thao tác cá nhân / doanh nghiệp</div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 3. THANH BỘ LỌC VÀ TÌM KIẾM                                               -->
<!-- ========================================================================= -->
<div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
    <form action="<?= url('/admin/profile-history') ?>" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="q" value="<?= $searchVal ?>" class="form-control" placeholder="Tìm theo tên người dùng, tên trường, người thực hiện, tóm tắt..." style="border-radius: 8px;">
        </div>

        <div style="min-width: 170px;">
            <select name="target_type" class="form-control" style="border-radius: 8px;">
                <option value="">-- Loại Đối Tượng --</option>
                <option value="SEEKER" <?= $targetTypeVal === 'SEEKER' ? 'selected' : '' ?>>Người Lao Động</option>
                <option value="EMPLOYER" <?= $targetTypeVal === 'EMPLOYER' ? 'selected' : '' ?>>Nhà Tuyển Dụng</option>
            </select>
        </div>

        <div style="min-width: 170px;">
            <select name="change_type" class="form-control" style="border-radius: 8px;">
                <option value="">-- Người Thay Đổi --</option>
                <option value="SELF_UPDATE" <?= $changeTypeVal === 'SELF_UPDATE' ? 'selected' : '' ?>>Bản Thân Tự Đổi</option>
                <option value="ADMIN_UPDATE" <?= $changeTypeVal === 'ADMIN_UPDATE' ? 'selected' : '' ?>>Quản Trị Viên Đổi</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.4rem;">
            <?= icon('search', '', 14) ?> <span>Lọc Dữ Liệu</span>
        </button>

        <?php if (!empty($searchVal) || !empty($targetTypeVal) || !empty($changeTypeVal)): ?>
            <a href="<?= url('/admin/profile-history') ?>" class="btn btn-outline" style="border-radius: 8px;">Xóa Lọc</a>
        <?php endif; ?>
    </form>
</div>

<!-- ========================================================================= -->
<!-- 4. BẢNG DỮ LIỆU AUDIT TRAIL                                              -->
<!-- ========================================================================= -->
<div class="card" style="padding: 0; overflow: hidden; border-radius: 12px;">
    <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
        <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;">
            Danh Sách Lịch Sử Thay Đổi (<?= number_format($totalLogs) ?> bản ghi)
        </h3>
        <span style="font-size: 0.82rem; color: #64748b;">Trang <?= $currentPage ?> / <?= max(1, $totalPages) ?></span>
    </div>

    <?php if (empty($logs)): ?>
        <div style="text-align: center; padding: 3.5rem 1rem; color: #64748b;">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📋</div>
            <h4 style="color: #334155; font-weight: 700;">Không tìm thấy lịch sử thay đổi nào!</h4>
            <p style="font-size: 0.88rem; margin: 0.25rem 0 0 0;">Khi Người lao động hoặc Nhà tuyển dụng cập nhật hồ sơ, mọi thay đổi sẽ xuất hiện tại đây.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.88rem; margin: 0;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1; color: #334155; text-align: left;">
                        <th style="padding: 0.75rem 1rem; width: 80px;">Mã Log</th>
                        <th style="padding: 0.75rem 1rem; width: 150px;">Thời Gian</th>
                        <th style="padding: 0.75rem 1rem; width: 220px;">Hồ Sơ Được Sửa</th>
                        <th style="padding: 0.75rem 1rem; width: 170px;">Trường Thay Đổi</th>
                        <th style="padding: 0.75rem 1rem;">Nội Dung Cũ</th>
                        <th style="padding: 0.75rem 1rem;">Nội Dung Mới</th>
                        <th style="padding: 0.75rem 1rem; width: 220px;">Người Thay Đổi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): 
                        $isSelf = ($log['change_type'] === 'SELF_UPDATE');
                        $targetTypeLabel = ($log['target_type'] === 'SEEKER') ? 'Người Lao Động' : 'Nhà Tuyển Dụng';
                        $targetBadgeClass = ($log['target_type'] === 'SEEKER') ? 'badge-primary' : 'badge-warning';
                    ?>
                        <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 0.75rem 1rem; font-weight: 700; color: #64748b;">
                                #<?= $log['log_id'] ?>
                            </td>
                            <td style="padding: 0.75rem 1rem; font-weight: 600; color: #475569; white-space: nowrap;">
                                <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                <div style="font-weight: 800; color: #0f172a;">
                                    <?= htmlspecialchars($log['target_display_name'] ?: ($log['target_username'] ?? 'Tài khoản #' . $log['target_account_id'])) ?>
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.15rem; display: flex; align-items: center; gap: 0.35rem;">
                                    <span class="badge <?= $targetBadgeClass ?>" style="font-size: 0.7rem;"><?= $targetTypeLabel ?></span>
                                    <span>@<?= htmlspecialchars($log['target_username'] ?? '') ?></span>
                                </div>
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                <strong style="color: #0f172a;"><?= htmlspecialchars($log['field_label'] ?? $log['field_name']) ?></strong>
                                <div style="font-size: 0.74rem; color: #94a3b8; font-family: monospace;"><?= htmlspecialchars($log['field_name']) ?></div>
                            </td>
                            <td style="padding: 0.75rem 1rem; color: #dc2626; word-break: break-word; max-width: 250px;">
                                <span style="background: #fee2e2; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.82rem; display: inline-block;">
                                    <?= htmlspecialchars($log['old_value'] ?? '') ?>
                                </span>
                            </td>
                            <td style="padding: 0.75rem 1rem; color: #16a34a; word-break: break-word; max-width: 250px;">
                                <span style="background: #dcfce7; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.82rem; font-weight: 600; display: inline-block;">
                                    <?= htmlspecialchars($log['new_value'] ?? '') ?>
                                </span>
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                <?php if ($isSelf): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 0.25rem 0.6rem; background: #e0f2fe; color: #0369a1; border-radius: 6px; font-weight: 700; font-size: 0.78rem;">
                                        👤 <?= htmlspecialchars($log['changed_by_name'] ?? 'Bản thân') ?>
                                    </span>
                                <?php else: ?>
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 0.25rem 0.6rem; background: #fee2e2; color: #b91c1c; border-radius: 6px; font-weight: 800; font-size: 0.78rem; border: 1px solid #fca5a5;">
                                        🛡️ <?= htmlspecialchars($log['changed_by_name'] ?? 'Quản trị viên') ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($log['ip_address'])): ?>
                                    <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 0.2rem;">IP: <?= htmlspecialchars($log['ip_address']) ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- PHÂN TRANG -->
        <?php if ($totalPages > 1): ?>
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; gap: 0.35rem; background: #f8fafc;">
                <?php 
                $baseUrl = url('/admin/profile-history') . '?' . http_build_query([
                    'q' => $searchVal,
                    'target_type' => $targetTypeVal,
                    'change_type' => $changeTypeVal
                ]) . '&page=';
                ?>
                <?php if ($currentPage > 1): ?>
                    <a href="<?= $baseUrl . ($currentPage - 1) ?>" class="btn btn-sm btn-outline">&laquo; Trước</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                        <a href="<?= $baseUrl . $i ?>" class="btn btn-sm <?= $i == $currentPage ? 'btn-primary' : 'btn-outline' ?>" style="font-weight: 700;">
                            <?= $i ?>
                        </a>
                    <?php elseif ($i == $currentPage - 3 || $i == $currentPage + 3): ?>
                        <span style="padding: 0.35rem 0.5rem; color: #94a3b8;">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= $baseUrl . ($currentPage + 1) ?>" class="btn btn-sm btn-outline">Sau &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php';
?>
