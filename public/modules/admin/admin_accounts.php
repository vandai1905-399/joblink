<?php 
ob_start(); 
$searchVal = htmlspecialchars($search ?? '');
$roleVal = $role ?? '';
$statusVal = $status ?? '';
$sortVal = $sort ?? 'newest';
?>

<style>
.account-actions-group {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.35rem;
    white-space: nowrap;
}

.btn-acc-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
    padding: 0.32rem 0.65rem;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 6px;
    white-space: nowrap;
    cursor: pointer;
    line-height: 1;
    text-decoration: none;
    box-sizing: border-box;
    transition: all 0.15s ease;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-family: inherit;
}

.btn-acc-detail {
    background: #ffffff;
    color: #334155;
    border-color: #cbd5e1;
}
.btn-acc-detail:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}

.btn-acc-lock {
    background: #ffffff;
    color: #b45309;
    border-color: #cbd5e1;
}
.btn-acc-lock:hover {
    background: #fffbeb;
    color: #92400e;
    border-color: #fcd34d;
}

.btn-acc-unlock {
    background: #ffffff;
    color: #15803d;
    border-color: #cbd5e1;
}
.btn-acc-unlock:hover {
    background: #f0fdf4;
    color: #166534;
    border-color: #86efac;
}

.btn-acc-delete {
    background: #ffffff;
    color: #e11d48;
    border-color: #cbd5e1;
}
.btn-acc-delete:hover {
    background: #fff1f2;
    color: #be123c;
    border-color: #fda4af;
}
</style>


<!-- ========================================================================= -->
<!-- 1. THANH PHÂN LOẠI NHANH (TABS ĐƠN GIẢN, TỐI GIẢN)                       -->
<!-- ========================================================================= -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center;">
    <a href="<?= url('/admin/accounts') ?>" class="btn btn-sm <?= empty($roleVal) ? 'btn-primary' : 'btn-outline' ?>" style="font-weight: 600; text-decoration: none; border-radius: 6px;">
        Tất cả (<?= number_format($totalCount ?? count($accounts)) ?>)
    </a>
    <a href="<?= url('/admin/accounts?role=JOB_SEEKER') ?>" class="btn btn-sm <?= $roleVal === 'JOB_SEEKER' ? 'btn-primary' : 'btn-outline' ?>" style="font-weight: 600; text-decoration: none; border-radius: 6px;">
        Người Lao Động (<?= number_format($seekerCount ?? 0) ?>)
    </a>
    <a href="<?= url('/admin/accounts?role=EMPLOYER_ADMIN') ?>" class="btn btn-sm <?= $roleVal === 'EMPLOYER_ADMIN' ? 'btn-primary' : 'btn-outline' ?>" style="font-weight: 600; text-decoration: none; border-radius: 6px;">
        Nhà Tuyển Dụng (<?= number_format($employerCount ?? 0) ?>)
    </a>
    <a href="<?= url('/admin/accounts?role=SUPER_ADMIN') ?>" class="btn btn-sm <?= in_array($roleVal, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']) ? 'btn-primary' : 'btn-outline' ?>" style="font-weight: 600; text-decoration: none; border-radius: 6px;">
        Quản Trị & CSKH (<?= number_format($adminStaffCount ?? 0) ?>)
    </a>
</div>

<!-- ========================================================================= -->
<!-- 2. BỘ CÔNG CỤ TÌM KIẾM, LỌC & SẮP XẾP GỌN GÀNG                           -->
<!-- ========================================================================= -->
<div class="card" style="margin-bottom: 1.25rem; padding: 1rem 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px;">
    <form id="filterForm" action="<?= url('/admin/accounts') ?>" method="GET">
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
            
            <!-- TÌM KIẾM TỪ KHÓA -->
            <div style="flex: 2; min-width: 220px; position: relative;">
                <input type="text" name="q" id="accountSearchInput" value="<?= $searchVal ?>" 
                       placeholder="Tìm theo tên, email, SĐT hoặc ID..." 
                       class="form-control" style="padding-left: 2rem; border-radius: 6px; font-size: 0.9rem; height: 38px;">
                <span style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                    <?= icon('search', '', 14) ?>
                </span>
                <?php if (!empty($searchVal)): ?>
                    <a href="<?= url('/admin/accounts' . (!empty($roleVal) ? '?role=' . urlencode($roleVal) : '')) ?>" 
                       style="position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; text-decoration: none; font-size: 1rem;">&times;</a>
                <?php endif; ?>
            </div>

            <!-- LỌC THEO VAI TRÒ -->
            <div style="flex: 1; min-width: 160px;">
                <select name="role" class="form-control" style="border-radius: 6px; font-size: 0.9rem; height: 38px;" onchange="this.form.submit()">
                    <option value="">-- Vai trò --</option>
                    <option value="JOB_SEEKER" <?= $roleVal === 'JOB_SEEKER' ? 'selected' : '' ?>>Người Lao Động</option>
                    <option value="EMPLOYER_ADMIN" <?= $roleVal === 'EMPLOYER_ADMIN' ? 'selected' : '' ?>>Nhà Tuyển Dụng</option>
                    <option value="INTERMEDIARY" <?= $roleVal === 'INTERMEDIARY' ? 'selected' : '' ?>>Người Trung Gian</option>
                    <option value="SUPER_ADMIN" <?= $roleVal === 'SUPER_ADMIN' ? 'selected' : '' ?>>Quản Trị Cấp Cao</option>
                    <option value="SUB_ADMIN" <?= $roleVal === 'SUB_ADMIN' ? 'selected' : '' ?>>Quản Trị Phụ</option>
                    <option value="CSKH_STAFF" <?= $roleVal === 'CSKH_STAFF' ? 'selected' : '' ?>>Nhân Viên CSKH</option>
                </select>
            </div>

            <!-- LỌC THEO TRẠNG THÁI -->
            <div style="flex: 1; min-width: 140px;">
                <select name="status" class="form-control" style="border-radius: 6px; font-size: 0.9rem; height: 38px;" onchange="this.form.submit()">
                    <option value="">-- Trạng thái --</option>
                    <option value="active" <?= $statusVal === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                    <option value="banned" <?= $statusVal === 'banned' ? 'selected' : '' ?>>Bị khóa</option>
                </select>
            </div>

            <!-- SẮP XẾP -->
            <div style="flex: 1; min-width: 150px;">
                <select name="sort" class="form-control" style="border-radius: 6px; font-size: 0.9rem; height: 38px;" onchange="this.form.submit()">
                    <option value="newest" <?= $sortVal === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="oldest" <?= $sortVal === 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                    <option value="username_asc" <?= $sortVal === 'username_asc' ? 'selected' : '' ?>>Tên (A &rarr; Z)</option>
                    <option value="email_asc" <?= $sortVal === 'email_asc' ? 'selected' : '' ?>>Email (A &rarr; Z)</option>
                </select>
            </div>

            <!-- NÚT THỰC THI -->
            <div style="display: flex; gap: 0.4rem;">
                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 600; height: 38px; padding: 0 1rem;">
                    Lọc
                </button>
                <a href="<?= url('/admin/accounts') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; font-weight: 600; height: 38px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;" title="Đặt lại">
                    Đặt lại
                </a>
            </div>
        </div>
    </form>

    <!-- THANH KẾT QUẢ TÌM THẤY -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.65rem; font-size: 0.82rem; color: #64748b;">
        <div>
            Tổng số: <strong><?= count($accounts) ?></strong> tài khoản
            <?php if (!empty($searchVal) || !empty($roleVal) || !empty($statusVal)): ?>
                (đang áp dụng lọc)
            <?php endif; ?>
        </div>
        <div id="liveSearchStatus" style="font-style: italic; color: #0d9488;"></div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 3. BẢNG DANH SÁCH TÀI KHOẢN HỆ THỐNG                                      -->
<!-- ========================================================================= -->
<div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table" id="accountsTable" style="margin: 0;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="width: 80px;">Mã TK</th>
                    <th>Người Dùng</th>
                    <th>Email & SĐT</th>
                    <th>Vai Trò</th>
                    <th>Trạng Thái</th>
                    <th style="text-align: right; min-width: 120px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔍</div>
                            <h4 style="font-size: 1.1rem; color: #475569; margin-bottom: 0.25rem;">Không tìm thấy tài khoản nào phù hợp</h4>
                            <p style="font-size: 0.85rem; margin: 0;">Hãy thử đổi từ khóa tìm kiếm hoặc bấm <a href="<?= url('/admin/accounts') ?>" style="color: #0d9488; font-weight: 700;">Đặt lại bộ lọc</a></p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($accounts as $acc): ?>
                        <?php 
                            $badgeClass = 'badge-slate';
                            $roleName = $acc['role_name'] ?? $acc['role_code'];
                            if ($acc['role_code'] === 'SUPER_ADMIN') {
                                $badgeClass = 'badge-danger';
                            } elseif ($acc['role_code'] === 'SUB_ADMIN' || $acc['role_code'] === 'CSKH_STAFF') {
                                $badgeClass = 'badge-primary';
                            } elseif ($acc['role_code'] === 'EMPLOYER_ADMIN') {
                                $badgeClass = 'badge-warning';
                            } elseif ($acc['role_code'] === 'JOB_SEEKER') {
                                $badgeClass = 'badge-primary';
                            } elseif ($acc['role_code'] === 'INTERMEDIARY') {
                                $badgeClass = 'badge-slate';
                            }

                            $isActive = ($acc['account_status'] === 'active');
                            $initial = mb_strtoupper(mb_substr($acc['display_name'] ?? $acc['username'], 0, 1));
                        ?>
                        <tr class="account-row" data-account-id="<?= $acc['account_id'] ?>" 
                            data-username="<?= strtolower(htmlspecialchars($acc['username'])) ?>"
                            data-fullname="<?= strtolower(htmlspecialchars($acc['display_name'] ?? '')) ?>"
                            data-email="<?= strtolower(htmlspecialchars($acc['email'])) ?>"
                            data-phone="<?= htmlspecialchars($acc['phone_number'] ?? '') ?>"
                            data-role="<?= $acc['role_code'] ?>"
                            data-status="<?= $acc['account_status'] ?>">
                            
                            <!-- CỘT 1: ID -->
                            <td>
                                <strong style="font-family: monospace; color: #0d9488; font-size: 0.9rem;">
                                    <?= formatId('account', $acc['account_id']) ?>
                                </strong>
                            </td>

                            <!-- CỘT 2: NGƯỜI DÙNG & USERNAME -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.65rem;">
                                    <?php 
                                        $userAvatar = getAvatarUrl($acc['avatar_url'] ?? '', $acc['role_code']);
                                    ?>
                                    <img src="<?= htmlspecialchars($userAvatar) ?>" alt="avatar" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1;" onerror="this.src='<?= getSystemLogo() ?>'">
                                    <div>
                                        <div style="font-weight: 600; color: #0f172a; font-size: 0.9rem;">
                                            <?= htmlspecialchars($acc['display_name'] ?? $acc['username']) ?>
                                        </div>
                                        <div style="font-size: 0.78rem; color: #64748b;">
                                            @<?= htmlspecialchars($acc['username']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- CỘT 3: EMAIL & SĐT -->
                            <td>
                                <div style="font-size: 0.88rem; color: #334155; margin-bottom: 0.2rem;">
                                    <?= icon('mail', '', 13) ?> <a href="mailto:<?= htmlspecialchars($acc['email']) ?>" style="color: inherit; text-decoration: none;"><?= htmlspecialchars($acc['email']) ?></a>
                                </div>
                                <?php if (!empty($acc['phone_number'])): ?>
                                    <div style="font-size: 0.8rem; color: #64748b;">
                                        📞 <a href="tel:<?= htmlspecialchars($acc['phone_number']) ?>" style="color: inherit; text-decoration: none;"><?= htmlspecialchars($acc['phone_number']) ?></a>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- CỘT 4: VAI TRÒ & QUYỀN -->
                            <td style="white-space: nowrap;">
                                <span class="badge <?= $badgeClass ?>" style="font-size: 0.78rem; font-weight: 700; white-space: nowrap; display: inline-block;">
                                    <?= htmlspecialchars($roleName) ?>
                                </span>
                            </td>

                            <!-- CỘT 5: TRẠNG THÁI -->
                            <td style="white-space: nowrap;">
                                <?php if ($isActive): ?>
                                    <span class="badge badge-success" style="font-size: 0.78rem; white-space: nowrap; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('check', '', 12) ?> <span>Hoạt động</span>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-danger" style="font-size: 0.78rem; white-space: nowrap; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('lock', '', 12) ?> <span>Bị khóa</span>
                                    </span>
                                <?php endif; ?>
                            </td>


                            <!-- CỘT 6: THAO TÁC -->
                            <td style="text-align: right;">
                                <div class="account-actions-group">
                                    <!-- NÚT 1: CHI TIẾT -->
                                    <button type="button" class="btn-acc-action btn-acc-detail" onclick="showAccountDetail(<?= $acc['account_id'] ?>)" title="Xem thông tin chi tiết">
                                        <?= icon('eye', '', 14) ?>
                                        <span>Chi tiết</span>
                                    </button>

                                    <?php if ($acc['account_id'] != ($_SESSION['user']['account_id'] ?? 0)): ?>
                                        <!-- NÚT 2: KHÓA / MỞ KHÓA -->
                                        <form action="<?= url('/admin/accounts/toggle-status/' . $acc['account_id']) ?>" method="POST" style="display: inline-flex; margin: 0; padding: 0;" onsubmit="return confirm('Bạn có chắc muốn <?= $isActive ? 'TẠM KHÓA' : 'MỞ KHÓA' ?> tài khoản <?= htmlspecialchars($acc['username']) ?>?');">
                                            <?php if ($isActive): ?>
                                                <button type="submit" class="btn-acc-action btn-acc-lock" title="Tạm khóa tài khoản">
                                                    <?= icon('lock', '', 14) ?>
                                                    <span>Khóa</span>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn-acc-action btn-acc-unlock" title="Mở khóa tài khoản">
                                                    <?= icon('unlock', '', 14) ?>
                                                    <span>Mở</span>
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <!-- NÚT 3: XOÁ VĨNH VIỄN -->
                                        <button type="button" class="btn-acc-action btn-acc-delete" 
                                                onclick="openDeleteModal(<?= $acc['account_id'] ?>, '<?= htmlspecialchars(addslashes($acc['username'])) ?>', '<?= htmlspecialchars(addslashes($acc['display_name'])) ?>', '<?= htmlspecialchars(addslashes($acc['role_name'])) ?>', '<?= htmlspecialchars(addslashes($acc['email'])) ?>')"
                                                title="Xoá vĩnh viễn tài khoản và toàn bộ hồ sơ">
                                            <?= icon('trash', '', 14) ?>
                                            <span>Xoá</span>
                                        </button>
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

<!-- ========================================================================= -->
<!-- 4. MODAL CHI TIẾT TÀI KHOẢN (POPUP AJAX)                                  -->
<!-- ========================================================================= -->
<div id="accountDetailModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; backdrop-filter: blur(4px); justify-content: center; align-items: center; padding: 1rem;">
    <div class="card" style="max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); padding: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <span>📋 Thông Tin Chi Tiết Tài Khoản</span>
            </h3>
            <button type="button" onclick="closeAccountModal()" style="background: none; border: none; font-size: 1.5rem; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <div id="modalLoadingSpinner" style="text-align: center; padding: 3rem 1rem;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">⏳</div>
            <p style="color: #64748b; margin: 0;">Đang tải thông tin tài khoản...</p>
        </div>

        <div id="modalBodyContent" style="padding: 1.5rem; display: none;">
            <!-- Render nội dung từ Javascript -->
        </div>

        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; text-align: right;">
            <button type="button" class="btn btn-outline" onclick="closeAccountModal()">Đóng Cửa Sổ</button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 5. MODAL XÁC NHẬN XOÁ VĨNH VIỄN TÀI KHOẢN (BẢO MẬT & NHẬP MẬT KHẨU ADMIN) -->
<!-- ========================================================================= -->
<div id="deleteAccountModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 10000; backdrop-filter: blur(4px); justify-content: center; align-items: center; padding: 1rem;">
    <div class="card" style="max-width: 520px; width: 100%; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); padding: 0; border: 1px solid #fecdd3; overflow: hidden;">
        
        <!-- Header Cảnh Báo -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; background: #fff1f2; border-bottom: 1px solid #fecdd3;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #be123c; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <span>⚠️ XÁC NHẬN XOÁ VĨNH VIỄN TÀI KHOẢN</span>
            </h3>
            <button type="button" onclick="closeDeleteModal()" style="background: none; border: none; font-size: 1.5rem; color: #9f1239; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <div style="padding: 1.5rem;">
            <!-- Hộp Cảnh Báo Nguy Hiểm -->
            <div style="background: #fff1f2; border-left: 4px solid #e11d48; padding: 0.85rem 1rem; border-radius: 6px; margin-bottom: 1.25rem; font-size: 0.85rem; color: #9f1239; line-height: 1.5;">
                <strong>⚠️ CẢNH BÁO NGUY HIỂM:</strong><br>
                Tài khoản này được liên kết trực tiếp với <strong>Hồ sơ người dùng</strong> (Người tìm việc / Doanh nghiệp, đánh giá, lịch hẹn, bài đăng...). Khi xoá tài khoản gốc, <strong>toàn bộ hồ sơ liên quan sẽ bị xoá vĩnh viễn và KHÔNG THỂ KHÔI PHỤC!</strong>
            </div>

            <!-- Tóm Tắt Thông Tin Tài Khoản Sắp Xoá -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.88rem;">
                <div style="margin-bottom: 0.3rem;"><strong>Tài khoản:</strong> <span id="del_username" style="font-weight: 700; color: #0f172a;">—</span></div>
                <div style="margin-bottom: 0.3rem;"><strong>Tên hiển thị:</strong> <span id="del_display_name">—</span></div>
                <div style="margin-bottom: 0.3rem;"><strong>Vai trò:</strong> <span id="del_role_name" class="badge badge-primary">—</span></div>
                <div><strong>Email:</strong> <span id="del_email">—</span></div>
            </div>

            <!-- Bước 1: Checkbox Xác Nhận -->
            <div style="margin-bottom: 1.25rem;">
                <label style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.88rem; color: #334155; cursor: pointer; font-weight: 600;">
                    <input type="checkbox" id="del_confirm_checkbox" style="margin-top: 0.2rem; width: 18px; height: 18px; accent-color: #e11d48; cursor: pointer;">
                    <span>Tôi đã hiểu rõ và xác nhận muốn xoá vĩnh viễn tài khoản cùng toàn bộ hồ sơ dữ liệu này.</span>
                </label>
            </div>

            <!-- Bước 2: Nhập Mật Khẩu Admin Xác Thực -->
            <div style="margin-bottom: 1rem;">
                <label class="form-label" style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">
                    🔑 Nhập Mật Khẩu Admin Của Bạn Để Xác Thực: <span style="color: #e11d48;">*</span>
                </label>
                <input type="password" id="del_admin_password" class="form-control" placeholder="Nhập mật khẩu tài khoản Admin đang đăng nhập..." autocomplete="current-password" style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.85rem;">
                <div id="del_error_msg" style="display: none; color: #e11d48; font-size: 0.82rem; font-weight: 600; margin-top: 0.4rem;"></div>
            </div>
        </div>

        <!-- Nút Hành Động -->
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Hủy Bỏ</button>
            <button type="button" id="btn-confirm-delete" onclick="executeDeleteAccount()" class="btn" style="background: #e11d48; color: #ffffff; font-weight: 700; border: none; border-radius: 8px; padding: 0.6rem 1.25rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                <span>🔴 Xác Nhận Xoá Vĩnh Viễn</span>
            </button>
        </div>
    </div>
</div>

<script>
// Tìm kiếm nhanh Real-time Client side
const searchInput = document.getElementById('accountSearchInput');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.account-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const uId = row.getAttribute('data-account-id') || '';
            const uName = row.getAttribute('data-username') || '';
            const fName = row.getAttribute('data-fullname') || '';
            const email = row.getAttribute('data-email') || '';
            const phone = row.getAttribute('data-phone') || '';

            if (!query || uId.includes(query) || uName.includes(query) || fName.includes(query) || email.includes(query) || phone.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const statusLabel = document.getElementById('liveSearchStatus');
        if (statusLabel) {
            statusLabel.innerText = query ? `(Lọc nhanh: ${visibleCount} kết quả)` : '';
        }
    });
}

// Xử lý Modal Xem Chi Tiết
function showAccountDetail(accountId) {
    const modal = document.getElementById('accountDetailModal');
    const spinner = document.getElementById('modalLoadingSpinner');
    const content = document.getElementById('modalBodyContent');

    if (!modal) return;
    modal.style.display = 'flex';
    spinner.style.display = 'block';
    content.style.display = 'none';

    fetch('<?= url('/api/admin/accounts/') ?>' + accountId)
        .then(res => res.json())
        .then(data => {
            spinner.style.display = 'none';
            content.style.display = 'block';

            if (!data.success || !data.account) {
                content.innerHTML = '<div class="alert alert-danger">Không tìm thấy thông tin tài khoản!</div>';
                return;
            }

            const acc = data.account;
            const prof = data.profile || {};
            let profileHtml = '';

            const formatDateStr = (str) => {
                if (!str || str === '0000-00-00' || str === '0000-00-00 00:00:00') return '—';
                const parts = str.split(/[- :]/);
                if (parts.length >= 3) {
                    const d = parts[2].padStart(2, '0');
                    const m = parts[1].padStart(2, '0');
                    const y = parts[0];
                    if (parts.length >= 5) {
                        return `${d}/${m}/${y} ${parts[3]}:${parts[4]}`;
                    }
                    return `${d}/${m}/${y}`;
                }
                return str;
            };

            if (acc.role_code === 'JOB_SEEKER') {
                profileHtml = `
                    <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 10px; padding: 1rem; margin-top: 1rem;">
                        <h5 style="color: #0f766e; font-weight: 800; margin-bottom: 0.6rem; font-size: 0.95rem;">👤 Hồ Sơ Người Lao Động (NLĐ)</h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.85rem;">
                            <div><strong>Họ và tên:</strong> ${prof.full_name || 'Chưa cập nhật'}</div>
                            <div><strong>Giới tính:</strong> ${prof.gender || 'Chưa cập nhật'}</div>
                            <div><strong>Ngày sinh:</strong> ${formatDateStr(prof.date_of_birth)}</div>
                            <div><strong>Điểm uy tín:</strong> <span style="color: #059669; font-weight: 800;">${prof.trust_score || 100}/100</span></div>
                            <div style="grid-column: span 2;"><strong>Khu vực ưu tiên:</strong> ${prof.preferred_location || 'Toàn quốc'}</div>
                            <div style="grid-column: span 2;"><strong>Vị trí mong muốn:</strong> ${prof.desired_position || 'Chưa cập nhật'}</div>
                            <div style="grid-column: span 2;"><strong>Kỹ năng:</strong> ${prof.skills || 'Chưa cập nhật'}</div>
                        </div>
                    </div>
                `;
            } else if (acc.role_code === 'EMPLOYER_ADMIN') {
                profileHtml = `
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 1rem; margin-top: 1rem;">
                        <h5 style="color: #b45309; font-weight: 800; margin-bottom: 0.6rem; font-size: 0.95rem;">🏢 Hồ Sơ Doanh Nghiệp / Tuyển Dụng</h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.85rem;">
                            <div style="grid-column: span 2;"><strong>Tên công ty:</strong> ${prof.employer_name || 'Chưa cập nhật'}</div>
                            <div><strong>Người đại diện:</strong> ${prof.representative_name || 'Chưa cập nhật'}</div>
                            <div><strong>Mã số thuế / GPKD:</strong> ${prof.business_license_number || 'Chưa cập nhật'}</div>
                            <div><strong>Lĩnh vực:</strong> ${prof.business_field || 'Chưa cập nhật'}</div>
                            <div><strong>Chữ ký số CA:</strong> ${prof.digital_signature_status === 'verified' ? '<span style="color: green;">✓ Đã xác thực</span>' : '<span style="color: orange;">Chưa xác thực</span>'}</div>
                            <div style="grid-column: span 2;"><strong>Trụ sở:</strong> ${prof.address || 'Chưa cập nhật'}</div>
                        </div>
                    </div>
                `;
            }

            content.innerHTML = `
                <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #0d9488, #0284c7); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem;">
                        ${acc.username.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <h4 style="margin: 0 0 0.2rem 0; font-size: 1.15rem; font-weight: 800; color: #0f172a;">${acc.username}</h4>
                        <div style="font-size: 0.85rem; color: #64748b;">Mã tài khoản: <strong>TK-${String(acc.account_id).padStart(5, '0')}</strong></div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.88rem;">
                    <div><strong>Email:</strong> ${acc.email}</div>
                    <div><strong>Vai trò:</strong> <span class="badge badge-primary">${acc.role_name || acc.role_code}</span></div>
                    <div><strong>Trạng thái:</strong> ${acc.account_status === 'active' ? '<span class="badge badge-success">✓ Hoạt động</span>' : '<span class="badge badge-danger">🔒 Bị khóa</span>'}</div>
                    <div><strong>Ngày tạo:</strong> ${formatDateStr(acc.created_at)}</div>
                </div>

                ${profileHtml}
            `;

        })
        .catch(err => {
            spinner.style.display = 'none';
            content.style.display = 'block';
            content.innerHTML = '<div class="alert alert-danger">Lỗi kết nối khi tải chi tiết tài khoản!</div>';
        });
}

function closeAccountModal() {
    const modal = document.getElementById('accountDetailModal');
    if (modal) modal.style.display = 'none';
}

// =========================================================================
// XỬ LÝ XOÁ TÀI KHOẢN VÀ XÁC THỰC MẬT KHẨU ADMIN
// =========================================================================
let currentDeleteTargetId = null;

function openDeleteModal(accountId, username, displayName, roleName, email) {
    currentDeleteTargetId = accountId;
    document.getElementById('del_username').innerText = '@' + username;
    document.getElementById('del_display_name').innerText = displayName || username;
    document.getElementById('del_role_name').innerText = roleName;
    document.getElementById('del_email').innerText = email;

    document.getElementById('del_confirm_checkbox').checked = false;
    document.getElementById('del_admin_password').value = '';
    
    const errMsg = document.getElementById('del_error_msg');
    errMsg.style.display = 'none';
    errMsg.innerText = '';

    const modal = document.getElementById('deleteAccountModal');
    if (modal) modal.style.display = 'flex';
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteAccountModal');
    if (modal) modal.style.display = 'none';
    currentDeleteTargetId = null;
}

async function executeDeleteAccount() {
    if (!currentDeleteTargetId) return;

    const checkbox = document.getElementById('del_confirm_checkbox');
    const passwordInput = document.getElementById('del_admin_password');
    const errMsg = document.getElementById('del_error_msg');
    const deleteBtn = document.getElementById('btn-confirm-delete');

    if (!checkbox.checked) {
        errMsg.innerText = '⚠️ Bạn cần tích chọn vào ô xác nhận đã hiểu rủi ro xoá dữ liệu!';
        errMsg.style.display = 'block';
        return;
    }

    const password = passwordInput.value.trim();
    if (!password) {
        errMsg.innerText = '⚠️ Vui lòng nhập mật khẩu tài khoản Admin của bạn!';
        errMsg.style.display = 'block';
        passwordInput.focus();
        return;
    }

    errMsg.style.display = 'none';
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '⏳ Đang xoá dữ liệu...';

    try {
        const formData = new FormData();
        formData.append('admin_password', password);

        const res = await fetch('<?= url('/admin/accounts/delete/') ?>' + currentDeleteTargetId, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await res.json();

        if (res.ok && data.success) {
            closeDeleteModal();
            if (typeof Toast !== 'undefined') {
                Toast.show(data.message || 'Đã xoá tài khoản thành công!', 'success');
            } else {
                alert(data.message || 'Đã xoá tài khoản thành công!');
            }
            // Tải lại trang sau 600ms
            setTimeout(() => {
                window.location.reload();
            }, 600);
        } else {
            errMsg.innerText = data.message || 'Mật khẩu quản trị viên không chính xác!';
            errMsg.style.display = 'block';
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '🔴 Xác Nhận Xoá Vĩnh Viễn';
        }
    } catch (err) {
        errMsg.innerText = 'Đã xảy ra lỗi kết nối đến máy chủ, vui lòng thử lại!';
        errMsg.style.display = 'block';
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = '🔴 Xác Nhận Xoá Vĩnh Viễn';
    }
}

// Đóng modal khi bấm ra ngoài
window.addEventListener('click', function(e) {
    const detailModal = document.getElementById('accountDetailModal');
    if (e.target === detailModal) {
        closeAccountModal();
    }
    const deleteModal = document.getElementById('deleteAccountModal');
    if (e.target === deleteModal) {
        closeDeleteModal();
    }
});
</script>


<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php';
?>

