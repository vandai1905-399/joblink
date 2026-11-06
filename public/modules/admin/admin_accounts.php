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

.btn-acc-edit {
    background: #ffffff;
    color: #0284c7;
    border-color: #cbd5e1;
}
.btn-acc-edit:hover {
    background: #f0f9ff;
    color: #0369a1;
    border-color: #7dd3fc;
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
                    <option value="username_asc" <?= $sortVal === 'username_asc' ? 'selected' : '' ?>>Tên (A - Z)</option>
                    <option value="email_asc" <?= $sortVal === 'email_asc' ? 'selected' : '' ?>>Email (A - Z)</option>
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
                            <div style="margin-bottom: 0.5rem; display: flex; justify-content: center;"><?= icon('search', '', 40) ?></div>
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
                                <?php 
                                    $userAvatar = getAvatarUrl($acc['avatar_url'] ?? '', $acc['role_code']);
                                    $displayName = $acc['display_name'] ?? $acc['username'];
                                    $profileUrl = '';
                                    $profileTitle = htmlspecialchars($displayName);

                                    if ($acc['role_code'] === 'JOB_SEEKER' && !empty($acc['seeker_id'])) {
                                        $profileUrl = url('/employer/candidates/' . $acc['seeker_id']);
                                        $profileTitle = 'Hồ sơ: ' . $profileTitle;
                                    } elseif ($acc['role_code'] === 'EMPLOYER_ADMIN' && !empty($acc['employer_id'])) {
                                        $profileUrl = url('/employers/' . $acc['employer_id']);
                                        $profileTitle = 'Doanh nghiệp: ' . $profileTitle;
                                    } elseif (in_array($acc['role_code'], ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
                                        $profileUrl = url('/admin/staffs');
                                        $profileTitle = 'Nhân sự: ' . $profileTitle;
                                    } elseif ($acc['role_code'] === 'INTERMEDIARY') {
                                        $profileUrl = url('/admin/authorizations');
                                        $profileTitle = 'Ủy quyền: ' . $profileTitle;
                                    }
                                ?>
                                <?php if (!empty($profileUrl)): ?>
                                    <a href="<?= $profileUrl ?>" class="workspace-nav-link" data-title="<?= $profileTitle ?>" style="display: flex; align-items: center; gap: 0.65rem; text-decoration: none; color: inherit; cursor: pointer;" title="Xem hồ sơ <?= htmlspecialchars($displayName) ?>">
                                        <img src="<?= htmlspecialchars($userAvatar) ?>" alt="avatar" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1; flex-shrink: 0; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'" onerror="this.onerror=null; this.src='<?= getSystemLogo() ?>'">
                                        <div>
                                            <div class="acc-name-link" style="font-weight: 600; color: #0f172a; font-size: 0.9rem; transition: color 0.15s;" onmouseover="this.style.color='#0d9488'" onmouseout="this.style.color='#0f172a'">
                                                <?= htmlspecialchars($displayName) ?>
                                            </div>
                                            <div style="font-size: 0.78rem; color: #64748b;">
                                                @<?= htmlspecialchars($acc['username']) ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                                        <img src="<?= htmlspecialchars($userAvatar) ?>" alt="avatar" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1; flex-shrink: 0;" onerror="this.onerror=null; this.src='<?= getSystemLogo() ?>'">
                                        <div>
                                            <div style="font-weight: 600; color: #0f172a; font-size: 0.9rem;">
                                                <?= htmlspecialchars($displayName) ?>
                                            </div>
                                            <div style="font-size: 0.78rem; color: #64748b;">
                                                @<?= htmlspecialchars($acc['username']) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- CỘT 3: EMAIL & SĐT -->
                            <td>
                                <div style="font-size: 0.88rem; color: #334155; margin-bottom: 0.2rem;">
                                    <?= icon('mail', '', 13) ?> <a href="mailto:<?= htmlspecialchars($acc['email']) ?>" style="color: inherit; text-decoration: none;"><?= htmlspecialchars($acc['email']) ?></a>
                                </div>
                                <?php if (!empty($acc['phone_number'])): ?>
                                    <div style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('phone', '', 12) ?> <a href="tel:<?= htmlspecialchars($acc['phone_number']) ?>" style="color: inherit; text-decoration: none;"><?= htmlspecialchars($acc['phone_number']) ?></a>
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

                                    <!-- NÚT 1.5: SỬA HỒ SƠ & LƯU VẾT -->
                                    <?php if (in_array($acc['role_code'], ['JOB_SEEKER', 'EMPLOYER_ADMIN'])): ?>
                                        <button type="button" class="btn-acc-action btn-acc-edit" onclick="openAdminEditProfile(<?= $acc['account_id'] ?>)" title="Chỉnh sửa thông tin hồ sơ & ghi nhận lịch sử">
                                            <?= icon('edit', '', 14) ?>
                                            <span>Sửa</span>
                                        </button>
                                    <?php endif; ?>

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
<!-- 4. MODAL CHI TIẾT TÀI KHOẢN (POPUP AJAX & AUDIT TRAIL)                    -->
<!-- ========================================================================= -->
<div id="accountDetailModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; backdrop-filter: blur(4px); justify-content: center; align-items: center; padding: 1rem;">
    <div class="card" style="max-width: 760px; width: 100%; max-height: 90vh; overflow-y: auto; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); padding: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <?= icon('file_text', '', 18) ?> <span id="detailModalTitle">Thông Tin Chi Tiết Tài Khoản</span>
            </h3>
            <button type="button" onclick="closeAccountModal()" style="background: none; border: none; font-size: 1.5rem; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- TAB NAVIGATION -->
        <div style="display: flex; border-bottom: 1px solid #e2e8f0; background: #ffffff; padding: 0 1.5rem; gap: 0.5rem;">
            <button type="button" id="tabDetailBtn" onclick="switchDetailTab('info')" style="padding: 0.85rem 1rem; border: none; background: none; font-weight: 700; font-size: 0.88rem; color: #0d9488; border-bottom: 3px solid #0d9488; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
                <?= icon('user', '', 15) ?> <span>1. Thông Tin Chi Tiết</span>
            </button>
            <button type="button" id="tabHistoryBtn" onclick="switchDetailTab('history')" style="padding: 0.85rem 1rem; border: none; background: none; font-weight: 600; font-size: 0.88rem; color: #64748b; border-bottom: 3px solid transparent; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
                <?= icon('clock', '', 15) ?> <span>2. Lịch Sử Thay Đổi (Audit Trail)</span>
                <span id="detailHistoryBadge" style="display: none; background: #0d9488; color: #fff; font-size: 0.72rem; font-weight: 700; padding: 1px 7px; border-radius: 10px;">0</span>
            </button>
        </div>

        <div id="modalLoadingSpinner" style="text-align: center; padding: 3rem 1rem;">
            <div style="margin-bottom: 0.5rem; display: flex; justify-content: center;"><?= icon('clock', '', 32) ?></div>
            <p style="color: #64748b; margin: 0;">Đang tải thông tin tài khoản...</p>
        </div>

        <!-- TAB 1: THÔNG TIN HỒ SƠ -->
        <div id="modalBodyContent" style="padding: 1.5rem; display: none;">
            <!-- Render nội dung từ Javascript -->
        </div>

        <!-- TAB 2: LỊCH SỬ THAY ĐỔI -->
        <div id="modalHistoryContent" style="padding: 1.5rem; display: none;">
            <div id="historyLoadingSpinner" style="text-align: center; padding: 2rem 1rem;">
                <p style="color: #64748b; margin: 0;">Đang nạp lịch sử thay đổi...</p>
            </div>
            <div id="historyTableContainer" style="display: none;"></div>
        </div>

        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.8rem; color: #64748b;">
                Hệ thống lưu vết tự động mọi biến động dữ liệu người dùng
            </div>
            <button type="button" class="btn btn-outline" onclick="closeAccountModal()">Đóng Cửa Sổ</button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 4.1 MODAL CHỈNH SỬA HỒ SƠ NGƯỜI DÙNG (ADMIN UPDATE & AUDIT TRAIL)          -->
<!-- ========================================================================= -->
<div id="adminEditProfileModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; backdrop-filter: blur(4px); justify-content: center; align-items: center; padding: 1rem;">
    <div class="card" style="max-width: 680px; width: 100%; max-height: 90vh; overflow-y: auto; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.25); padding: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <?= icon('edit', '', 18) ?> <span id="editModalTitle">Chỉnh Sửa Thông Tin Hồ Sơ</span>
                </h3>
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.2rem;">
                    Mọi thay đổi của bạn sẽ được lưu vào lịch sử kiểm toán (Audit Trail)
                </div>
            </div>
            <button type="button" onclick="closeAdminEditProfile()" style="background: none; border: none; font-size: 1.5rem; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <div id="editLoadingSpinner" style="text-align: center; padding: 3rem 1rem;">
            <div style="margin-bottom: 0.5rem; display: flex; justify-content: center;"><?= icon('clock', '', 32) ?></div>
            <p style="color: #64748b; margin: 0;">Đang nạp dữ liệu hồ sơ...</p>
        </div>

        <form id="adminEditProfileForm" onsubmit="submitAdminEditProfile(event)" style="display: none;">
            <div style="padding: 1.5rem;">
                <input type="hidden" id="edit_account_id" name="account_id">
                <input type="hidden" id="edit_role_code" name="role_code">

                <!-- PHẦN DÀNH CHO NGƯỜI LAO ĐỘNG -->
                <div id="editSeekerFields" style="display: none;">
                    <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: #0f766e; font-weight: 600;">
                        <?= icon('user', '', 15) ?> Đang cập nhật hồ sơ Người Lao Động
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Họ và tên <span style="color: red;">*</span></label>
                            <input type="text" id="seeker_full_name" name="full_name" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Số điện thoại</label>
                            <input type="text" id="seeker_phone_number" name="phone_number" class="form-control">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Ngày sinh</label>
                            <input type="date" id="seeker_date_of_birth" name="date_of_birth" class="form-control">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Giới tính</label>
                            <select id="seeker_gender" name="gender" class="form-control">
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Số CCCD / CMND</label>
                            <input type="text" id="seeker_id_card_number" name="id_card_number" class="form-control" placeholder="12 chữ số CCCD">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Trình độ học vấn</label>
                            <input type="text" id="seeker_education_level" name="education_level" class="form-control" placeholder="Đại học, Cao đẳng, THPT...">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Vị trí mong muốn</label>
                            <input type="text" id="seeker_desired_position" name="desired_position" class="form-control" placeholder="Nhân viên bán hàng, pha chế...">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Mức lương mong muốn (VNĐ)</label>
                            <input type="number" id="seeker_expected_salary" name="expected_salary" class="form-control" placeholder="VD: 8000000">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Hình thức làm việc</label>
                            <select id="seeker_preferred_employment_type" name="preferred_employment_type" class="form-control">
                                <option value="full_time">Toàn thời gian (Full-time)</option>
                                <option value="part_time">Bán thời gian (Part-time)</option>
                                <option value="hourly">Theo ca / giờ (Shift/Hourly)</option>
                                <option value="internship">Thực tập sinh</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Số năm kinh nghiệm</label>
                            <input type="number" id="seeker_experience_years" name="experience_years" min="0" max="50" class="form-control">
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Địa chỉ liên hệ</label>
                        <input type="text" id="seeker_address" name="address" class="form-control">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Khu vực làm việc ưu tiên</label>
                        <input type="text" id="seeker_preferred_location" name="preferred_location" class="form-control">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Kỹ năng nổi bật</label>
                        <input type="text" id="seeker_skills" name="skills" class="form-control" placeholder="Giao tiếp, Pha chế, Thu ngân...">
                    </div>

                    <div style="margin-bottom: 0.5rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Giới thiệu bản thân / Tóm tắt kinh nghiệm</label>
                        <textarea id="seeker_bio" name="bio" rows="3" class="form-control" style="resize: vertical;"></textarea>
                    </div>
                </div>

                <!-- PHẦN DÀNH CHO NHÀ TUYỂN DỤNG -->
                <div id="editEmployerFields" style="display: none;">
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: #b45309; font-weight: 600;">
                        <?= icon('briefcase', '', 15) ?> Đang cập nhật hồ sơ Doanh Nghiệp / Tuyển Dụng
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tên công ty / Đơn vị tuyển dụng <span style="color: red;">*</span></label>
                        <input type="text" id="employer_name" name="employer_name" class="form-control" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Người đại diện</label>
                            <input type="text" id="employer_representative_name" name="representative_name" class="form-control">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Mã số thuế / GPKD</label>
                            <input type="text" id="employer_business_license_number" name="business_license_number" class="form-control">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Email liên hệ</label>
                            <input type="email" id="employer_contact_email" name="contact_email" class="form-control">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Số điện thoại</label>
                            <input type="text" id="employer_phone_number" name="phone_number" class="form-control">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Lĩnh vực kinh doanh</label>
                            <input type="text" id="employer_business_field" name="business_field" class="form-control" placeholder="F&B, Bán lẻ, Công nghệ...">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Website</label>
                            <input type="url" id="employer_website_url" name="website_url" class="form-control" placeholder="https://...">
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Địa chỉ trụ sở chính</label>
                        <input type="text" id="employer_address" name="address" class="form-control">
                    </div>

                    <div style="margin-bottom: 0.5rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Đường dẫn bản đồ (Google Maps URL)</label>
                        <input type="text" id="employer_map_url" name="map_url" class="form-control" placeholder="https://maps.google.com/...">
                    </div>
                </div>

                <div id="editAlertMsg" style="display: none; margin-top: 1rem;" class="alert"></div>
            </div>

            <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-outline" onclick="closeAdminEditProfile()">Hủy Bỏ</button>
                <button type="submit" id="btnSaveAdminEdit" class="btn btn-primary" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <?= icon('check', '', 15) ?> <span>Lưu Thay Đổi & Ghi Log</span>
                </button>
            </div>
        </form>
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
                <?= icon('alert_triangle', '', 18) ?> <span>XÁC NHẬN XOÁ VĨNH VIỄN TÀI KHOẢN</span>
            </h3>
            <button type="button" onclick="closeDeleteModal()" style="background: none; border: none; font-size: 1.5rem; color: #9f1239; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <div style="padding: 1.5rem;">
            <!-- Hộp Cảnh Báo Nguy Hiểm -->
            <div style="background: #fff1f2; border-left: 4px solid #e11d48; padding: 0.85rem 1rem; border-radius: 6px; margin-bottom: 1.25rem; font-size: 0.85rem; color: #9f1239; line-height: 1.5;">
                <strong>CẢNH BÁO NGUY HIỂM:</strong><br>
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
                    Nhập Mật Khẩu Admin Của Bạn Để Xác Thực: <span style="color: #e11d48;">*</span>
                </label>
                <input type="password" id="del_admin_password" class="form-control" placeholder="Nhập mật khẩu tài khoản Admin đang đăng nhập..." autocomplete="current-password" style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.85rem;">
                <div id="del_error_msg" style="display: none; color: #e11d48; font-size: 0.82rem; font-weight: 600; margin-top: 0.4rem;"></div>
            </div>
        </div>

        <!-- Nút Hành Động -->
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Hủy Bỏ</button>
            <button type="button" id="btn-confirm-delete" onclick="executeDeleteAccount()" class="btn" style="background: #e11d48; color: #ffffff; font-weight: 700; border: none; border-radius: 8px; padding: 0.6rem 1.25rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                <span>Xác Nhận Xoá Vĩnh Viễn</span>
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

// Biến toàn cục theo dõi modal chi tiết và modal sửa
let currentDetailAccountId = null;
let currentDetailHistoryLoaded = false;
let currentEditAccountId = null;

// Chuyển tab trong Modal Chi Tiết
function switchDetailTab(tab) {
    const btnInfo = document.getElementById('tabDetailBtn');
    const btnHistory = document.getElementById('tabHistoryBtn');
    const contentInfo = document.getElementById('modalBodyContent');
    const contentHistory = document.getElementById('modalHistoryContent');

    if (!btnInfo || !btnHistory || !contentInfo || !contentHistory) return;

    if (tab === 'info') {
        btnInfo.style.color = '#0d9488';
        btnInfo.style.borderBottom = '3px solid #0d9488';
        btnInfo.style.fontWeight = '700';

        btnHistory.style.color = '#64748b';
        btnHistory.style.borderBottom = '3px solid transparent';
        btnHistory.style.fontWeight = '600';

        contentInfo.style.display = 'block';
        contentHistory.style.display = 'none';
    } else if (tab === 'history') {
        btnHistory.style.color = '#0d9488';
        btnHistory.style.borderBottom = '3px solid #0d9488';
        btnHistory.style.fontWeight = '700';

        btnInfo.style.color = '#64748b';
        btnInfo.style.borderBottom = '3px solid transparent';
        btnInfo.style.fontWeight = '600';

        contentInfo.style.display = 'none';
        contentHistory.style.display = 'block';

        if (!currentDetailHistoryLoaded && currentDetailAccountId) {
            loadAccountHistory(currentDetailAccountId);
        }
    }
}

// Tải lịch sử thay đổi thông tin (Audit Trail)
function loadAccountHistory(accountId) {
    const spinner = document.getElementById('historyLoadingSpinner');
    const container = document.getElementById('historyTableContainer');
    const badge = document.getElementById('detailHistoryBadge');

    if (spinner) spinner.style.display = 'block';
    if (container) container.style.display = 'none';

    fetch('<?= url('/api/admin/accounts/') ?>' + accountId + '/profile-history')
        .then(res => res.json())
        .then(data => {
            if (spinner) spinner.style.display = 'none';
            if (container) container.style.display = 'block';
            currentDetailHistoryLoaded = true;

            const logs = (data && data.success && Array.isArray(data.data)) ? data.data : [];

            if (badge) {
                badge.innerText = logs.length;
                badge.style.display = logs.length > 0 ? 'inline-block' : 'none';
            }

            if (logs.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8; background: #f8fafc; border-radius: 10px; border: 1px dashed #cbd5e1;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📋</div>
                        <h5 style="color: #475569; margin-bottom: 0.25rem; font-size: 0.95rem;">Chưa có lịch sử thay đổi</h5>
                        <p style="margin: 0; font-size: 0.82rem;">Hồ sơ này chưa từng thực hiện chỉnh sửa hoặc can thiệp thông tin.</p>
                    </div>
                `;
                return;
            }

            let rowsHtml = '';
            logs.forEach(log => {
                const isSelf = (log.change_type === 'SELF_UPDATE');
                const badgeRole = isSelf 
                    ? '<span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Tự cập nhật</span>'
                    : '<span style="background: #fef3c7; color: #b45309; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Admin can thiệp</span>';

                rowsHtml += `
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.75rem 0.6rem; font-size: 0.8rem; color: #64748b; white-space: nowrap;">
                            <strong>${log.created_at || '—'}</strong>
                            ${log.ip_address ? `<div style="font-size: 0.72rem; color: #94a3b8;">IP: ${log.ip_address}</div>` : ''}
                        </td>
                        <td style="padding: 0.75rem 0.6rem;">
                            <span style="font-weight: 700; color: #0f172a; font-size: 0.84rem;">${log.field_label || log.field_name}</span>
                        </td>
                        <td style="padding: 0.75rem 0.6rem; font-size: 0.82rem; color: #b91c1c; background: #fff1f2; max-width: 140px; word-break: break-word;">
                            ${log.old_value !== null && log.old_value !== '' ? log.old_value : '<em style="color:#94a3b8;">(Trống)</em>'}
                        </td>
                        <td style="padding: 0.75rem 0.6rem; font-size: 0.82rem; color: #15803d; background: #f0fdf4; max-width: 140px; word-break: break-word;">
                            ${log.new_value !== null && log.new_value !== '' ? log.new_value : '<em style="color:#94a3b8;">(Trống)</em>'}
                        </td>
                        <td style="padding: 0.75rem 0.6rem; font-size: 0.8rem; white-space: nowrap;">
                            <div style="font-weight: 600; color: #334155;">${log.changed_by_name || 'Hệ thống'}</div>
                            <div style="margin-top: 2px;">${badgeRole}</div>
                        </td>
                    </tr>
                `;
            });

            container.innerHTML = `
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
                        <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <tr>
                                <th style="padding: 0.65rem 0.6rem; font-size: 0.78rem; font-weight: 700; color: #475569;">Thời gian</th>
                                <th style="padding: 0.65rem 0.6rem; font-size: 0.78rem; font-weight: 700; color: #475569;">Trường thông tin</th>
                                <th style="padding: 0.65rem 0.6rem; font-size: 0.78rem; font-weight: 700; color: #475569;">Nội dung cũ</th>
                                <th style="padding: 0.65rem 0.6rem; font-size: 0.78rem; font-weight: 700; color: #475569;">Nội dung mới</th>
                                <th style="padding: 0.65rem 0.6rem; font-size: 0.78rem; font-weight: 700; color: #475569;">Người thực hiện</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>
                </div>
            `;
        })
        .catch(err => {
            if (spinner) spinner.style.display = 'none';
            if (container) {
                container.style.display = 'block';
                container.innerHTML = '<div class="alert alert-danger" style="margin: 0;">Lỗi kết nối khi tải lịch sử kiểm toán!</div>';
            }
        });
}

// Xử lý Modal Xem Chi Tiết
function showAccountDetail(accountId) {
    currentDetailAccountId = accountId;
    currentDetailHistoryLoaded = false;

    const modal = document.getElementById('accountDetailModal');
    const spinner = document.getElementById('modalLoadingSpinner');
    const content = document.getElementById('modalBodyContent');
    const historyContainer = document.getElementById('historyTableContainer');
    const badge = document.getElementById('detailHistoryBadge');

    if (!modal) return;
    modal.style.display = 'flex';
    spinner.style.display = 'block';
    content.style.display = 'none';
    if (historyContainer) historyContainer.innerHTML = '';
    if (badge) badge.style.display = 'none';

    switchDetailTab('info');

    // Nạp trước số lượng lịch sử
    fetch('<?= url('/api/admin/accounts/') ?>' + accountId + '/profile-history')
        .then(res => res.json())
        .then(data => {
            if (data && data.success && Array.isArray(data.data) && badge) {
                badge.innerText = data.data.length;
                badge.style.display = data.data.length > 0 ? 'inline-block' : 'none';
            }
        }).catch(() => {});

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
                        <h5 style="color: #0f766e; font-weight: 800; margin-bottom: 0.6rem; font-size: 0.95rem;">Hồ Sơ Người Lao Động (NLĐ)</h5>
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
                        <h5 style="color: #b45309; font-weight: 800; margin-bottom: 0.6rem; font-size: 0.95rem;">Hồ Sơ Doanh Nghiệp / Tuyển Dụng</h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.85rem;">
                            <div style="grid-column: span 2;"><strong>Tên công ty:</strong> ${prof.employer_name || 'Chưa cập nhật'}</div>
                            <div><strong>Người đại diện:</strong> ${prof.representative_name || 'Chưa cập nhật'}</div>
                            <div><strong>Mã số thuế / GPKD:</strong> ${prof.business_license_number || 'Chưa cập nhật'}</div>
                            <div><strong>Lĩnh vực:</strong> ${prof.business_field || 'Chưa cập nhật'}</div>
                            <div><strong>Chữ ký số:</strong> ${prof.digital_signature_status === 'verified' ? '<span style="color: green;">Đã xác thực</span>' : '<span style="color: orange;">Chưa xác thực</span>'}</div>
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
                    <div><strong>Trạng thái:</strong> ${acc.account_status === 'active' ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge badge-danger">Bị khóa</span>'}</div>
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
    currentDetailAccountId = null;
}

// =========================================================================
// XỬ LÝ CHỈNH SỬA HỒ SƠ TỪ ADMIN VÀ GHI AUDIT TRAIL
// =========================================================================
function openAdminEditProfile(accountId) {
    currentEditAccountId = accountId;
    const modal = document.getElementById('adminEditProfileModal');
    const spinner = document.getElementById('editLoadingSpinner');
    const form = document.getElementById('adminEditProfileForm');
    const alertMsg = document.getElementById('editAlertMsg');
    
    if (!modal) return;
    modal.style.display = 'flex';
    spinner.style.display = 'block';
    form.style.display = 'none';
    if (alertMsg) alertMsg.style.display = 'none';

    fetch('<?= url('/api/admin/accounts/') ?>' + accountId)
        .then(res => res.json())
        .then(data => {
            spinner.style.display = 'none';
            if (!data.success || !data.account) {
                alert('Không tìm thấy thông tin tài khoản!');
                closeAdminEditProfile();
                return;
            }

            form.style.display = 'block';
            const acc = data.account;
            const prof = data.profile || {};

            document.getElementById('edit_account_id').value = acc.account_id;
            document.getElementById('edit_role_code').value = acc.role_code;
            document.getElementById('editModalTitle').innerText = 'Chỉnh Sửa Hồ Sơ: @' + acc.username;

            const seekerFields = document.getElementById('editSeekerFields');
            const employerFields = document.getElementById('editEmployerFields');

            if (acc.role_code === 'JOB_SEEKER') {
                seekerFields.style.display = 'block';
                employerFields.style.display = 'none';

                document.getElementById('seeker_full_name').value = prof.full_name || acc.display_name || '';
                document.getElementById('seeker_phone_number').value = prof.phone_number || acc.phone_number || '';
                document.getElementById('seeker_date_of_birth').value = prof.date_of_birth || '';
                document.getElementById('seeker_gender').value = prof.gender || 'Nam';
                document.getElementById('seeker_id_card_number').value = prof.id_card_number || '';
                document.getElementById('seeker_education_level').value = prof.education_level || '';
                document.getElementById('seeker_desired_position').value = prof.desired_position || '';
                document.getElementById('seeker_expected_salary').value = prof.expected_salary || '';
                document.getElementById('seeker_preferred_employment_type').value = prof.preferred_employment_type || 'full_time';
                document.getElementById('seeker_experience_years').value = prof.experience_years || 0;
                document.getElementById('seeker_address').value = prof.address || '';
                document.getElementById('seeker_preferred_location').value = prof.preferred_location || '';
                document.getElementById('seeker_skills').value = prof.skills || '';
                document.getElementById('seeker_bio').value = prof.bio || '';
            } else if (acc.role_code === 'EMPLOYER_ADMIN') {
                seekerFields.style.display = 'none';
                employerFields.style.display = 'block';

                document.getElementById('employer_name').value = prof.employer_name || acc.display_name || '';
                document.getElementById('employer_representative_name').value = prof.representative_name || '';
                document.getElementById('employer_business_license_number').value = prof.business_license_number || '';
                document.getElementById('employer_contact_email').value = prof.contact_email || acc.email || '';
                document.getElementById('employer_phone_number').value = prof.phone_number || acc.phone_number || '';
                document.getElementById('employer_business_field').value = prof.business_field || '';
                document.getElementById('employer_website_url').value = prof.website_url || '';
                document.getElementById('employer_address').value = prof.address || '';
                document.getElementById('employer_map_url').value = prof.map_url || '';
            } else {
                alert('Tài khoản vai trò này không hỗ trợ chỉnh sửa hồ sơ qua modal!');
                closeAdminEditProfile();
            }
        })
        .catch(err => {
            spinner.style.display = 'none';
            alert('Lỗi kết nối khi nạp thông tin hồ sơ!');
            closeAdminEditProfile();
        });
}

function closeAdminEditProfile() {
    const modal = document.getElementById('adminEditProfileModal');
    if (modal) modal.style.display = 'none';
    currentEditAccountId = null;
}

async function submitAdminEditProfile(e) {
    e.preventDefault();
    if (!currentEditAccountId) return;

    const form = document.getElementById('adminEditProfileForm');
    const saveBtn = document.getElementById('btnSaveAdminEdit');
    const alertMsg = document.getElementById('editAlertMsg');
    
    saveBtn.disabled = true;
    saveBtn.innerHTML = 'Đang lưu & ghi log...';
    if (alertMsg) alertMsg.style.display = 'none';

    try {
        const formData = new FormData(form);
        formData.append('is_ajax', '1');

        const res = await fetch('<?= url('/admin/accounts/update-profile/') ?>' + currentEditAccountId, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await res.json();

        if (res.ok && data.success) {
            if (alertMsg) {
                alertMsg.className = 'alert alert-success';
                alertMsg.style.display = 'block';
                alertMsg.innerText = data.message || 'Cập nhật thành công!';
            }
            if (typeof Toast !== 'undefined') {
                Toast.show(data.message || 'Cập nhật thành công!', 'success');
            }
            setTimeout(() => {
                closeAdminEditProfile();
                window.location.reload();
            }, 800);
        } else {
            if (alertMsg) {
                alertMsg.className = 'alert alert-danger';
                alertMsg.style.display = 'block';
                alertMsg.innerText = data.message || 'Lỗi khi cập nhật hồ sơ!';
            }
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<span>Lưu Thay Đổi & Ghi Log</span>';
        }
    } catch (err) {
        if (alertMsg) {
            alertMsg.className = 'alert alert-danger';
            alertMsg.style.display = 'block';
            alertMsg.innerText = 'Lỗi kết nối tới máy chủ, vui lòng thử lại!';
        }
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<span>Lưu Thay Đổi & Ghi Log</span>';
    }
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
        errMsg.innerText = 'Bạn cần tích chọn vào ô xác nhận đã hiểu rủi ro xoá dữ liệu!';
        errMsg.style.display = 'block';
        return;
    }

    const password = passwordInput.value.trim();
    if (!password) {
        errMsg.innerText = 'Vui lòng nhập mật khẩu tài khoản Admin của bạn!';
        errMsg.style.display = 'block';
        passwordInput.focus();
        return;
    }

    errMsg.style.display = 'none';
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = 'Đang xoá dữ liệu...';

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
            deleteBtn.innerHTML = 'Xác Nhận Xoá Vĩnh Viễn';
        }
    } catch (err) {
        errMsg.innerText = 'Đã xảy ra lỗi kết nối đến máy chủ, vui lòng thử lại!';
        errMsg.style.display = 'block';
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = 'Xác Nhận Xoá Vĩnh Viễn';
    }
}

// Đóng modal khi bấm ra ngoài
window.addEventListener('click', function(e) {
    const detailModal = document.getElementById('accountDetailModal');
    if (e.target === detailModal) {
        closeAccountModal();
    }
    const editModal = document.getElementById('adminEditProfileModal');
    if (e.target === editModal) {
        closeAdminEditProfile();
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

