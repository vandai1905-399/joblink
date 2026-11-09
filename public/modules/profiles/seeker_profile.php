<?php 
$extraCss = ['css/jobs/ai_matching.css'];
$extraJs = ['js/jobs/ai_recommendation.js'];
ob_start(); 

$sessionUser = $_SESSION['user'] ?? [];
$seekerName = $seeker['full_name'] ?? ($sessionUser['display_name'] ?? ($sessionUser['username'] ?? 'Người lao động'));
$seekerId = $seeker['seeker_id'] ?? ($sessionUser['account_id'] ?? 1);
$seekerEmail = $seeker['email'] ?? ($sessionUser['email'] ?? '');
$seekerPhone = $seeker['phone_number'] ?? (preg_match('/^[0-9]{9,12}$/', $sessionUser['username'] ?? '') ? $sessionUser['username'] : '');

// Giải mã minh chứng kỹ năng và lịch sử làm việc
$skillProofs = [];
if (!empty($seeker['skill_proofs'])) {
    $decoded = json_decode($seeker['skill_proofs'], true);
    if (is_array($decoded)) {
        $skillProofs = $decoded;
    }
}

$workHistory = [];
if (!empty($seeker['work_history'])) {
    $decoded = json_decode($seeker['work_history'], true);
    if (is_array($decoded)) {
        $workHistory = $decoded;
    }
}

// Tính tuổi từ ngày sinh
$age = null;
if (!empty($seeker['date_of_birth'])) {
    try {
        $birthDate = new DateTime($seeker['date_of_birth']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
    } catch (Exception $e) {
        $age = null;
    }
}

$trustScore = intval($seeker['trust_score'] ?? 100);
$scoreColor = $trustScore >= 80 ? '#10b981' : ($trustScore >= 60 ? '#f59e0b' : '#ef4444');
$scoreLevel = $trustScore >= 80 ? 'Rất Uy Tín (Xuất Sắc)' : ($trustScore >= 60 ? 'Mức Trung Bình' : 'Cảnh Báo Vi Phạm');
$isSigned = ($seeker['agreement_signed'] ?? 0) == 1;

// Avatar chính thức: giải quyết qua getAvatarUrl để tạo đường dẫn tuyệt đối chính xác
$seekerAvatar = getAvatarUrl($seeker['avatar_url'] ?? '', 'JOB_SEEKER', $seeker['gender'] ?? '');
$fallbackAvatar = getAvatarUrl('', 'JOB_SEEKER', $seeker['gender'] ?? '');
?>

<style>
.profile-widescreen-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 370px;
    gap: 1.5rem;
    align-items: start;
}
.profile-form-row-4 {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}
.profile-form-row-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}
.profile-form-row-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}
#ai-profile-optimizer-result [style*="grid-template-columns: 1fr 1fr"] {
    grid-template-columns: 1fr !important;
}
@media (max-width: 1200px) {
    .profile-widescreen-grid {
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 1.25rem;
    }
    .profile-form-row-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 992px) {
    .profile-widescreen-grid {
        grid-template-columns: 1fr;
    }
    .profile-form-row-3 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 640px) {
    .profile-form-row-4,
    .profile-form-row-3,
    .profile-form-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>

<div style="width: 100%; max-width: 100%; margin: 0 auto; padding-bottom: 2.5rem;">
    <!-- TIÊU ĐỀ TRANG & NÚT AI TỐI ƯU -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.65rem; font-weight: 800; color: var(--dark); margin: 0 0 0.25rem 0;">Hồ Sơ Năng Lực & Tối Ưu AI Đề Xuất</h1>
            <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">Hoàn thiện thông tin kỹ năng để hệ thống AI đề xuất chính xác các công việc phù hợp với bạn nhất</p>
        </div>
        <button type="button" id="btn-ai-optimize-profile" class="btn" style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); color: #ffffff; font-weight: 700; box-shadow: 0 4px 12px rgba(13,148,136,0.25); display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 8px;">
            <?= icon('sparkle', '', 16) ?> <span>AI Đánh Giá & Gợi Ý Tối Ưu Hồ Sơ</span>
        </button>
    </div>

    <?php if (!empty($_SESSION['flash_msg'])): ?>
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
            <?= icon('check', '', 16) ?> <?= htmlspecialchars($_SESSION['flash_msg']) ?>
        </div>
        <?php unset($_SESSION['flash_msg']); ?>
    <?php endif; ?>

    <form action="<?= url('/seeker/profile/update') ?>" method="POST" id="seekerProfileForm" enctype="multipart/form-data" data-unsaved-bar="true">
        <div class="profile-widescreen-grid">
            
            <!-- ========================================================================= -->
            <!-- CỘT TRÁI (CHÍNH): BIỂU MẪU HỒ SƠ CHI TIẾT (~68% MÀN HÌNH)                  -->
            <!-- ========================================================================= -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem; min-width: 0;">

                <!-- THẺ AVATAR & DANH TÍNH TỔNG QUAN -->
                <div class="card" style="box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 14px; padding: 1.25rem 1.5rem;">
                    <div style="display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap;">
                        <div style="position: relative; flex-shrink: 0; text-align: center;">
                            <img id="seeker_avatar_img" 
                                 src="<?= htmlspecialchars($seekerAvatar) ?>" 
                                 alt="avatar" 
                                 style="width: 88px; height: 88px; border-radius: 50%; object-fit: cover; border: 3px solid #0d9488; box-shadow: 0 4px 10px rgba(0,0,0,0.08); display: block;" 
                                 onerror="this.src='<?= htmlspecialchars($fallbackAvatar) ?>'">
                            <input type="file" name="avatar_file" id="seeker_avatar_file" accept="image/png,image/jpeg,image/webp,image/jpg" style="display:none;" onchange="previewSeekerAvatar(this)">
                            <button type="button" onclick="document.getElementById('seeker_avatar_file').click()" class="btn btn-sm" style="margin-top: 0.5rem; font-size: 0.75rem; padding: 0.3rem 0.6rem; border-radius: 20px; background: #f0fdfa; color: #0d9488; border: 1px solid #99f6e4; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <?= icon('camera', '', 12) ?> <span>Đổi ảnh</span>
                            </button>
                        </div>
                        <div style="flex: 1; min-width: 240px;">
                            <h3 style="font-size: 1.35rem; font-weight: 800; display: flex; align-items: center; gap: 0.5rem; color: #0f172a; margin-bottom: 0.35rem; flex-wrap: wrap;">
                                <span><?= htmlspecialchars($seekerName) ?></span>
                                <span class="badge badge-primary" style="font-size: 0.75rem;">Mã NLĐ: <?= formatId('seeker', $seekerId) ?></span>
                            </h3>
                            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; font-size: 0.85rem; color: var(--slate-600);">
                                <span><?= icon('star_fill', '', 14) ?> <strong><?= $seeker['avg_rating'] ?? '5.0' ?></strong> (<?= $seeker['total_reviews'] ?? 0 ?> đánh giá ca làm)</span>
                                <span>•</span>
                                <span><?= icon('mail', '', 14) ?> <?= htmlspecialchars($seekerEmail) ?></span>
                                <span>•</span>
                                <span><?= icon('phone', '', 14) ?> <?= htmlspecialchars($seekerPhone) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PHẦN 1: THÔNG TIN CÁ NHÂN & LIÊN LẠC -->
                <div class="card" style="box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 14px; border-left: 4px solid var(--primary); padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.65rem;">
                        <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                            <span style="background: #e0f2fe; color: #0284c7; width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">1</span>
                            <span>Thông Tin Cá Nhân & Liên Lạc</span>
                        </h4>
                        <?php if ($age): ?>
                            <span class="badge badge-primary" style="font-size: 0.8rem; font-weight: 700;">Tuổi: <?= $age ?> tuổi</span>
                        <?php endif; ?>
                    </div>

                    <!-- Hàng 1: Họ tên, Ngày sinh, Giới tính, SĐT -->
                    <div class="profile-form-row-4">
                        <div class="form-group">
                            <label class="form-label">Họ và Tên *</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($seekerName) ?>" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ngày Sinh</label>
                            <input type="date" name="date_of_birth" value="<?= htmlspecialchars($seeker['date_of_birth'] ?? '') ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Giới Tính</label>
                            <select name="gender" class="form-control">
                                <option value="Nam" <?= ($seeker['gender'] ?? '') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                <option value="Nữ" <?= ($seeker['gender'] ?? '') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                                <option value="Khác" <?= (!isset($seeker['gender']) || $seeker['gender'] === 'Khác') ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số Điện Thoại / Zalo *</label>
                            <input type="text" name="phone_number" value="<?= htmlspecialchars($seekerPhone) ?>" class="form-control" required>
                        </div>
                    </div>

                    <!-- Hàng 1.5: Email & CCCD -->
                    <div class="profile-form-row-2" style="margin-top: 0.25rem;">
                        <div class="form-group">
                            <label class="form-label">Địa Chỉ Email Tài Khoản *</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($seekerEmail) ?>" class="form-control" required placeholder="name@example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số CCCD / Mã Định Danh Công Dân</label>
                            <input type="text" name="id_card_number" value="<?= htmlspecialchars($seeker['id_card_number'] ?? '') ?>" class="form-control" placeholder="12 chữ số CCCD...">
                        </div>
                    </div>

                    <!-- Hàng 2: Trình độ học vấn, Vị trí mong muốn, Mức lương kỳ vọng -->
                    <div class="profile-form-row-3" style="margin-top: 0.25rem;">
                        <div class="form-group">
                            <label class="form-label">Trình độ học vấn cao nhất</label>
                            <select name="education_level" class="form-control">
                                <option value="">-- Chọn trình độ --</option>
                                <option value="Đại học" <?= ($seeker['education_level'] ?? '') === 'Đại học' ? 'selected' : '' ?>>Đại học</option>
                                <option value="Cao đẳng" <?= ($seeker['education_level'] ?? '') === 'Cao đẳng' ? 'selected' : '' ?>>Cao đẳng</option>
                                <option value="Trung cấp" <?= ($seeker['education_level'] ?? '') === 'Trung cấp' ? 'selected' : '' ?>>Trung cấp / Nghề</option>
                                <option value="THPT" <?= ($seeker['education_level'] ?? '') === 'THPT' ? 'selected' : '' ?>>Tốt nghiệp THPT</option>
                                <option value="Sau đại học" <?= ($seeker['education_level'] ?? '') === 'Sau đại học' ? 'selected' : '' ?>>Sau đại học (Thạc sĩ, TS)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Vị trí / Chức danh mong muốn</label>
                            <input type="text" name="desired_position" value="<?= htmlspecialchars($seeker['desired_position'] ?? '') ?>" placeholder="Ví dụ: Kế toán kho, Barista, Bán hàng..." class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mức lương kỳ vọng (VNĐ/tháng)</label>
                            <input type="number" name="expected_salary" value="<?= htmlspecialchars($seeker['expected_salary'] ?? '') ?>" placeholder="Ví dụ: 8000000" class="form-control">
                        </div>
                    </div>

                    <!-- Hàng 3: Hình thức làm việc, Khu vực ưu tiên, Địa chỉ thường trú -->
                    <div class="profile-form-row-3" style="margin-top: 0.25rem;">
                        <div class="form-group">
                            <label class="form-label">Hình thức làm việc ưu tiên</label>
                            <select name="preferred_employment_type" class="form-control">
                                <option value="full_time" <?= ($seeker['preferred_employment_type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Toàn thời gian (Full-time)</option>
                                <option value="part_time" <?= ($seeker['preferred_employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Bán thời gian (Part-time)</option>
                                <option value="temporary" <?= ($seeker['preferred_employment_type'] ?? '') === 'temporary' ? 'selected' : '' ?>>Thời vụ / Xoay ca linh hoạt</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Khu vực làm việc ưu tiên</label>
                            <input type="text" name="preferred_location" value="<?= htmlspecialchars($seeker['preferred_location'] ?? '') ?>" placeholder="Ví dụ: TP. Thủ Dầu Một, Thuận An..." class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Địa Chỉ Thường Trú & Nơi Ở Hiện Tại</label>
                            <input type="text" name="address" value="<?= htmlspecialchars($seeker['address'] ?? '') ?>" placeholder="Ví dụ: Số 12 Đường Trần Ngọc Lên..." class="form-control">
                        </div>
                    </div>

                    <!-- Hàng 4: Giới thiệu bản thân -->
                    <div class="form-group" style="margin-bottom: 0; margin-top: 0.25rem;">
                        <label class="form-label">Giới thiệu bản thân & Mục tiêu nghề nghiệp</label>
                        <textarea name="bio" rows="2" class="form-control" placeholder="Tóm tắt ngắn về bản thân, kinh nghiệm nổi bật và mục tiêu phát triển công việc..."><?= htmlspecialchars($seeker['bio'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- PHẦN 2: CÁC KỸ NĂNG KÈM MINH CHỨNG NĂNG LỰC -->
                <div class="card" style="box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 14px; border-left: 4px solid #0284c7; padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.65rem; flex-wrap: wrap; gap: 0.5rem;">
                        <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                            <span style="background: #e0f2fe; color: #0284c7; width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">2</span>
                            <span>Kỹ Năng Chuyên Môn & Minh Chứng Kèm Theo</span>
                        </h4>
                        <span style="font-size: 0.8rem; color: #0284c7; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('sparkle', '', 12) ?> AI dùng trường này để so khớp tuyển dụng</span>
                    </div>

                    <div class="profile-form-row-3">
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">Kỹ năng chuyên môn & Năng khiếu (Phân cách bởi dấu phẩy) *</label>
                            <input type="text" id="seeker_skills_input" name="skills" value="<?= htmlspecialchars($seeker['skills'] ?? '') ?>" placeholder="Ví dụ: Tư vấn sản phẩm, Bán hàng, Tiếng Anh giao tiếp..." class="form-control">
                            <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.35rem; display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center;">
                                <span>Gợi ý:</span>
                                <span class="badge badge-slate" style="cursor: pointer;" onclick="addSkillToInput('Tin học văn phòng')">+ Tin học văn phòng</span>
                                <span class="badge badge-slate" style="cursor: pointer;" onclick="addSkillToInput('Giao tiếp tốt')">+ Giao tiếp tốt</span>
                                <span class="badge badge-slate" style="cursor: pointer;" onclick="addSkillToInput('Làm việc nhóm')">+ Làm việc nhóm</span>
                                <span class="badge badge-slate" style="cursor: pointer;" onclick="addSkillToInput('Tiếng Anh')">+ Tiếng Anh</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Số năm kinh nghiệm làm việc</label>
                            <select name="experience_years" class="form-control">
                                <option value="0" <?= intval($seeker['experience_years'] ?? 0) === 0 ? 'selected' : '' ?>>Chưa có kinh nghiệm / Mới tốt nghiệp</option>
                                <option value="1" <?= intval($seeker['experience_years'] ?? 0) === 1 ? 'selected' : '' ?>>Dưới 1 năm kinh nghiệm</option>
                                <option value="2" <?= intval($seeker['experience_years'] ?? 0) === 2 ? 'selected' : '' ?>>1 - 2 năm kinh nghiệm</option>
                                <option value="3" <?= intval($seeker['experience_years'] ?? 0) === 3 ? 'selected' : '' ?>>3 - 5 năm kinh nghiệm</option>
                                <option value="5" <?= intval($seeker['experience_years'] ?? 0) >= 5 ? 'selected' : '' ?>>Trên 5 năm kinh nghiệm</option>
                            </select>
                        </div>
                    </div>

                    <!-- MINH CHỨNG KỸ NĂNG & CHỨNG CHỈ (REPEATER) -->
                    <div style="margin-top: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.15rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                            <label class="form-label" style="font-weight: 800; color: #0f172a; margin: 0; font-size: 0.92rem; display: flex; align-items: center; gap: 0.35rem;">
                                <?= icon('file_text', '', 14) ?> Danh Sách Minh Chứng Kỹ Năng & Chứng Chỉ Nghề Nghiệp (Nếu có):
                            </label>
                            <button type="button" class="btn btn-outline btn-sm" onclick="addProofRow()" style="font-size: 0.78rem; font-weight: 700; color: #0284c7; border-color: #0284c7; background: #fff;">
                                + Thêm Minh Chứng
                            </button>
                        </div>

                        <div id="skillProofsContainer" style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <?php if (!empty($skillProofs)): ?>
                                <?php foreach ($skillProofs as $idx => $proof): ?>
                                    <div class="proof-row" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem; display: grid; grid-template-columns: 2fr 1.5fr 1fr 2fr auto; gap: 0.5rem; align-items: center;">
                                        <div>
                                            <label style="font-size: 0.72rem; color: #64748b; font-weight: 700;">Tên chứng chỉ / Kỹ năng *</label>
                                            <input type="text" name="proof_name[]" value="<?= htmlspecialchars($proof['name'] ?? '') ?>" class="form-control" style="font-size: 0.82rem; padding: 0.35rem 0.5rem;" placeholder="VD: Chứng chỉ B2...">
                                        </div>
                                        <div>
                                            <label style="font-size: 0.72rem; color: #64748b; font-weight: 700;">Cấp bởi (Trường/Tổ chức)</label>
                                            <input type="text" name="proof_issuer[]" value="<?= htmlspecialchars($proof['issuer'] ?? '') ?>" class="form-control" style="font-size: 0.82rem; padding: 0.35rem 0.5rem;" placeholder="VD: Cambridge...">
                                        </div>
                                        <div>
                                            <label style="font-size: 0.72rem; color: #64748b; font-weight: 700;">Năm cấp</label>
                                            <input type="text" name="proof_year[]" value="<?= htmlspecialchars($proof['year'] ?? '') ?>" class="form-control" style="font-size: 0.82rem; padding: 0.35rem 0.5rem;" placeholder="2023">
                                        </div>
                                        <div>
                                            <label style="font-size: 0.72rem; color: #64748b; font-weight: 700;">Link minh chứng / Bằng cấp</label>
                                            <input type="text" name="proof_link[]" value="<?= htmlspecialchars($proof['link'] ?? '') ?>" class="form-control" style="font-size: 0.82rem; padding: 0.35rem 0.5rem;" placeholder="https://drive.google...">
                                        </div>
                                        <div style="align-self: flex-end;">
                                            <button type="button" class="btn btn-outline btn-sm" onclick="this.closest('.proof-row').remove()" style="color: #dc2626; border-color: #fca5a5; font-size: 0.8rem; padding: 0.35rem 0.6rem;">&times;</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div id="noProofNotice" style="text-align: center; padding: 0.85rem; color: #64748b; font-size: 0.82rem; font-style: italic;">
                                    Chưa có minh chứng nào được thêm. Bấm "+ Thêm Minh Chứng" ở trên để bổ sung bằng cấp/chứng chỉ.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- PHẦN 4: LỊCH SỬ LÀM VIỆC & KINH NGHIỆM THỰC TẾ -->
                <div class="card" style="box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 14px; border-left: 4px solid #8b5cf6; padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.65rem; flex-wrap: wrap; gap: 0.5rem;">
                        <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                            <span style="background: #ede9fe; color: #7c3aed; width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">4</span>
                            <span>Lịch Sử Làm Việc & Kinh Nghiệm Thực Tế</span>
                        </h4>
                        <button type="button" class="btn btn-outline btn-sm" onclick="addWorkRow()" style="font-size: 0.78rem; font-weight: 700; color: #7c3aed; border-color: #7c3aed; background: #fff;">
                            + Thêm Lịch Sử Làm Việc
                        </button>
                    </div>
                    <p style="font-size: 0.82rem; color: #64748b; margin: 0 0 1rem 0;">
                        Khai báo các doanh nghiệp, cơ sở đã từng công tác, vị trí đảm nhiệm và thời gian làm việc để AI chấm điểm độ tin cậy cao hơn.
                    </p>

                    <div id="workHistoryContainer" style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php if (!empty($workHistory)): ?>
                            <?php foreach ($workHistory as $wIdx => $work): ?>
                                <div class="work-row" style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 1rem; position: relative;">
                                    <button type="button" onclick="this.closest('.work-row').remove()" style="position: absolute; right: 8px; top: 8px; background: none; border: none; font-size: 1.2rem; color: #ef4444; cursor: pointer;">&times;</button>
                                    
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
                                        <div>
                                            <label style="font-size: 0.78rem; font-weight: 700; color: #4c1d95;">Tên công ty / Doanh nghiệp *</label>
                                            <input type="text" name="work_company[]" value="<?= htmlspecialchars($work['company_name'] ?? '') ?>" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;" placeholder="VD: Công ty TNHH ABC..." required>
                                        </div>
                                        <div>
                                            <label style="font-size: 0.78rem; font-weight: 700; color: #4c1d95;">Vị trí / Chức danh</label>
                                            <input type="text" name="work_position[]" value="<?= htmlspecialchars($work['position'] ?? '') ?>" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;" placeholder="VD: Nhân viên kho, Barista...">
                                        </div>
                                        <div>
                                            <label style="font-size: 0.78rem; font-weight: 700; color: #4c1d95;">Từ ngày (bắt đầu)</label>
                                            <input type="date" name="work_start[]" value="<?= htmlspecialchars($work['start_date'] ?? '') ?>" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;">
                                        </div>
                                        <div>
                                            <label style="font-size: 0.78rem; font-weight: 700; color: #4c1d95;">Đến ngày (kết thúc)</label>
                                            <input type="date" name="work_end[]" value="<?= htmlspecialchars($work['end_date'] ?? '') ?>" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;">
                                        </div>
                                    </div>

                                    <div>
                                        <label style="font-size: 0.78rem; font-weight: 700; color: #4c1d95;">Mô tả công việc & Thành tích chính</label>
                                        <textarea name="work_desc[]" rows="2" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;" placeholder="Mô tả tóm tắt nhiệm vụ chính, kinh nghiệm tích lũy..."><?= htmlspecialchars($work['description'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div id="noWorkNotice" style="text-align: center; padding: 1.25rem; color: #64748b; font-size: 0.85rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
                                Bạn chưa thêm lịch sử làm việc nào. Bấm <strong>"+ Thêm Lịch Sử Làm Việc"</strong> để giới thiệu kinh nghiệm của bạn cho nhà tuyển dụng!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- THANH NÚT LƯU CUỐI PHẦN FORM CHÍNH -->
                <div class="card" style="box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 14px; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <span style="font-size: 0.85rem; color: #64748b;">
                        <?= icon('shield_check', '', 14) ?> Dữ liệu hồ sơ được mã hóa và tự động đồng bộ cùng hệ thống so khớp việc làm AI.
                    </span>
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2.25rem; font-weight: 800; font-size: 0.95rem; border-radius: 8px;">
                        Lưu Thay Đổi & Cập Nhật Hồ Sơ
                    </button>
                </div>

                <!-- LỊCH SỬ THAY ĐỔI THÔNG TIN HỒ SƠ (AUDIT TRAIL) - MẶC ĐỊNH THU GỌN -->
                <div class="card" style="box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 14px; padding: 1.15rem 1.4rem; transition: all 0.2s ease; border: 1px solid #e2e8f0;">
                    <div id="profileHistoryHeader" style="display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none; gap: 0.75rem;" onclick="toggleProfileHistory()">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: #f0fdfa; border: 1px solid #ccfbf1; color: #0d9488; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <?= icon('clock', '', 20) ?>
                            </div>
                            <div>
                                <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
                                    <span>Lịch Sử Thay Đổi Thông Tin Hồ Sơ</span>
                                    <span class="badge badge-primary" style="font-size: 0.75rem; padding: 0.15rem 0.55rem; border-radius: 20px; font-weight: 700;">
                                        <?= count($historyLogs ?? []) ?>
                                    </span>
                                </h3>
                                <p style="font-size: 0.82rem; color: #64748b; margin: 0.2rem 0 0 0;">
                                    Nhật ký ghi nhận mọi thay đổi thông tin (bạn tự chỉnh sửa hoặc do Quản trị viên cập nhật).
                                </p>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.65rem; flex-shrink: 0;">
                            <span class="badge badge-outline" style="font-size: 0.75rem; font-weight: 600;">Lưu vết tự động</span>
                            <span class="btn btn-sm btn-outline" style="pointer-events: none; font-size: 0.8rem; font-weight: 700; padding: 0.35rem 0.75rem; border-radius: 20px; color: #0d9488; border-color: #99f6e4; background: #f0fdfa; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <span id="toggleHistoryText">Mở rộng xem</span>
                                <svg id="toggleHistoryChevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.25s ease;">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <!-- NỘI DUNG BẢNG LỊCH SỬ (MẶC ĐỊNH THU GỌN, CHỈ HIỆN KHI BẤM MỞ) -->
                    <div id="profileHistoryContent" style="display: none; margin-top: 1.15rem; border-top: 1px dashed #e2e8f0; padding-top: 1rem;">
                        <?= class_exists('ProfileHistoryService') ? ProfileHistoryService::renderHistoryTableHtml($historyLogs ?? [], 'Chưa có lịch sử thay đổi thông tin hồ sơ nào.') : '' ?>
                    </div>
                </div>

                <script>
                function toggleProfileHistory() {
                    const content = document.getElementById('profileHistoryContent');
                    const text = document.getElementById('toggleHistoryText');
                    const chevron = document.getElementById('toggleHistoryChevron');
                    if (!content) return;

                    const isHidden = content.style.display === 'none';
                    if (isHidden) {
                        content.style.display = 'block';
                        if (text) text.innerText = 'Thu gọn lại';
                        if (chevron) chevron.style.transform = 'rotate(180deg)';
                    } else {
                        content.style.display = 'none';
                        if (text) text.innerText = 'Mở rộng xem';
                        if (chevron) chevron.style.transform = 'rotate(0deg)';
                    }
                }
                </script>

            </div>

            <!-- ========================================================================= -->
            <!-- CỘT PHẢI (SIDEBAR): TỐI ƯU AI & ĐIỂM UY TÍN (~32% MÀN HÌNH, STICKY)        -->
            <!-- ========================================================================= -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem; position: sticky; top: 1rem;">

                <!-- KHỐI KẾT QUẢ PHÂN TÍCH AI (TỰ ĐỘNG CẬP NHẬT HOẶC QUA NÚT BẤM) -->
                <div id="ai-profile-optimizer-result" style="<?= !empty($profileAdvice) ? '' : 'display: none;' ?>">
                    <?php if (!empty($profileAdvice)): ?>
                        <div class="card" style="background: linear-gradient(135deg, #f0fdfa 0%, #f0f9ff 100%); border: 1px solid #99f6e4; border-radius: 14px; padding: 1.25rem; box-shadow: 0 4px 14px rgba(13,148,136,0.06);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; border-bottom: 1px solid #ccfbf1; padding-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                        <span class="badge <?= $profileAdvice['badge_class'] ?? 'badge-primary' ?>" style="font-size: 0.75rem; font-weight: 700;"><?= $profileAdvice['completeness_level'] ?? 'Hoàn thiện cơ bản' ?></span>
                                        <span style="font-size: 0.7rem; padding: 0.15rem 0.45rem; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('sparkle', '', 11) ?> <?= htmlspecialchars($profileAdvice['ai_provider'] ?? 'Google Gemini AI') ?></span>
                                    </div>
                                    <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0.35rem 0 0 0;">Độ Hoàn Thiện Hồ Sơ</h4>
                                </div>
                                <div style="font-size: 1.6rem; font-weight: 900; color: #0d9488;">
                                    <span><?= $profileAdvice['score'] ?? 80 ?>%</span>
                                </div>
                            </div>

                            <!-- Thanh tiến độ -->
                            <div style="width: 100%; background: #ccfbf1; height: 8px; border-radius: 999px; margin-bottom: 1rem; overflow: hidden;">
                                <div style="width: <?= min(100, intval($profileAdvice['score'] ?? 80)) ?>%; background: linear-gradient(90deg, #0d9488, #0284c7); height: 100%; border-radius: 999px;"></div>
                            </div>

                            <?php if (!empty($profileAdvice['strengths'])): ?>
                                <div style="margin-bottom: 0.75rem; background: #ffffff; padding: 0.75rem 0.85rem; border-radius: 8px; border: 1px solid #bbf7d0;">
                                    <strong style="font-size: 0.82rem; color: #065f46; display: flex; align-items: center; gap: 0.3rem; margin-bottom: 0.35rem;"><?= icon('award', '', 13) ?> Điểm mạnh của hồ sơ:</strong>
                                    <ul style="margin: 0; padding-left: 1.15rem; font-size: 0.8rem; line-height: 1.5; color: #047857;">
                                        <?php foreach ($profileAdvice['strengths'] as $st): ?>
                                            <li><?= htmlspecialchars($st) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($profileAdvice['suggestions'])): ?>
                                <div style="margin-bottom: 0; background: #ffffff; padding: 0.75rem 0.85rem; border-radius: 8px; border: 1px solid #fed7aa;">
                                    <strong style="font-size: 0.82rem; color: #92400e; display: flex; align-items: center; gap: 0.3rem; margin-bottom: 0.35rem;"><?= icon('sparkle', '', 13) ?> Gợi ý cải thiện từ AI:</strong>
                                    <ul style="margin: 0; padding-left: 1.15rem; font-size: 0.8rem; line-height: 1.5; color: #b45309;">
                                        <?php foreach ($profileAdvice['suggestions'] as $sug): ?>
                                            <li><?= htmlspecialchars($sug) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- PHẦN 3: ĐIỂM ĐÁNH GIÁ & ĐIỂM UY TÍN NỀN TẢNG (HỆ 5 SAO, TRUST SCORE, CCCD) -->
                <div class="card" style="box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 14px; border-left: 4px solid #f59e0b; padding: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.65rem;">
                        <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                            <span style="background: #fef3c7; color: #b45309; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800;">3</span>
                            <span>Uy Tín & Đánh Giá</span>
                        </h4>
                        <span class="badge" style="background: <?= $scoreColor ?>20; color: <?= $scoreColor ?>; font-weight: 700; font-size: 0.72rem;">
                            <?= $scoreLevel ?>
                        </span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                        <!-- THẺ ĐIỂM THEO HỆ 5 SAO -->
                        <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a; border-radius: 10px; padding: 1rem; text-align: center;">
                            <span style="font-size: 0.78rem; font-weight: 700; color: #92400e; display: block; margin-bottom: 0.2rem;">
                                ĐIỂM ĐÁNH GIÁ NĂNG LỰC
                            </span>
                            <div style="font-size: 1.9rem; font-weight: 900; color: #b45309; line-height: 1.2; margin-bottom: 0.2rem;">
                                <?= $seeker['avg_rating'] ?? '5.0' ?> <span style="font-size: 1rem; font-weight: 700; color: #d97706;">/ 5.0</span>
                            </div>
                            <div style="color: #f59e0b; font-size: 1.05rem; display: flex; justify-content: center; gap: 3px; margin-bottom: 0.25rem;">
                                <?php for ($s = 0; $s < 5; $s++): ?><?= icon('star_fill', '', 15) ?><?php endfor; ?>
                            </div>
                            <span style="font-size: 0.78rem; color: #78350f;">
                                Dựa trên <?= intval($seeker['total_reviews'] ?? 0) ?> lượt đánh giá ca làm
                            </span>
                        </div>

                        <!-- THẺ ĐIỂM UY TÍN (TRUST SCORE) -->
                        <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1px solid #a7f3d0; border-radius: 10px; padding: 1rem; text-align: center;">
                            <span style="font-size: 0.78rem; font-weight: 700; color: #065f46; display: block; margin-bottom: 0.2rem;">
                                ĐIỂM UY TÍN (TRUST SCORE)
                            </span>
                            <div style="font-size: 1.9rem; font-weight: 900; color: #059669; line-height: 1.2; margin-bottom: 0.2rem;">
                                <?= $trustScore ?> <span style="font-size: 1rem; font-weight: 700; color: #10b981;">/ 100</span>
                            </div>
                            <div style="width: 85%; background: #a7f3d0; height: 7px; border-radius: 999px; margin: 0 auto 0.45rem auto; overflow: hidden;">
                                <div style="width: <?= min(100, $trustScore) ?>%; background: #059669; height: 100%; border-radius: 999px;"></div>
                            </div>
                            <span class="badge badge-success" style="font-size: 0.72rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.2rem;">
                                <?= icon('shield', '', 12) ?> <?= $trustScore >= 80 ? 'Ưu tiên hiển thị với NTD' : 'Được ứng tuyển bình thường' ?>
                            </span>
                        </div>

                        <!-- THẺ CAM KẾT & CCCD -->
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                                <strong style="font-size: 0.85rem; color: #0f172a;">CCCD / Định Danh</strong>
                                <?php if ($isSigned): ?>
                                    <span class="badge badge-success" style="font-size: 0.7rem; display: inline-flex; align-items: center; gap: 0.2rem;"><?= icon('check', '', 10) ?> Đã cam kết</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.7rem;">Chưa ký</span>
                                <?php endif; ?>
                            </div>
                            <input type="text" name="id_card_number" value="<?= htmlspecialchars($seeker['id_card_number'] ?? '') ?>" placeholder="Nhập số CCCD (12 số)..." class="form-control" style="font-size: 0.82rem; padding: 0.4rem 0.55rem; margin-bottom: 0.4rem;">
                            <div style="font-size: 0.75rem; color: #64748b; line-height: 1.4;">
                                Đúng giờ: <strong style="color: #166534;">100%</strong> · Cảnh cáo: <strong style="color: #dc2626;"><?= intval($seeker['warning_count'] ?? 0) ?> lần</strong>
                            </div>
                            <?php if (!$isSigned): ?>
                                <div style="margin-top: 0.5rem;">
                                    <a href="<?= url('/seeker/settings?tab=trust') ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; color: #0d9488; border-color: #0d9488; font-weight: 700; width: 100%; text-align: center;">
                                        Ký Cam Kết Tuân Thủ (+5đ)
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- THẺ THAO TÁC NHANH (QUICK SAVE CARD) -->
                <div class="card" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.25rem; text-align: center;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 800; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(13,148,136,0.25);">
                        <?= icon('check', '', 16) ?> <span>Lưu Hồ Sơ Ngay</span>
                    </button>
                    <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 0.5rem;">
                        Tất cả thay đổi sẽ có hiệu lực ngay lập tức
                    </small>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
function addSkillToInput(skillName) {
    const input = document.getElementById('seeker_skills_input');
    if (!input) return;
    let current = input.value.trim();
    let arr = current ? current.split(',').map(s => s.trim()) : [];
    if (!arr.includes(skillName)) {
        arr.push(skillName);
        input.value = arr.join(', ');
    }
}

function addProofRow() {
    const container = document.getElementById('skillProofsContainer');
    const notice = document.getElementById('noProofNotice');
    if (notice) notice.remove();

    const row = document.createElement('div');
    row.className = 'proof-row';
    row.style.cssText = 'background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem; display: grid; grid-template-columns: 2fr 1.5fr 1fr 2fr auto; gap: 0.5rem; align-items: center;';
    row.innerHTML = `
        <div>
            <label style="font-size: 0.72rem; color: #64748b; font-weight: 700;">Tên chứng chỉ / Kỹ năng *</label>
            <input type="text" name="proof_name[]" class="form-control" style="font-size: 0.82rem; padding: 0.35rem 0.5rem;" placeholder="VD: Chứng chỉ B2..." required>
        </div>
        <div>
            <label style="font-size: 0.72rem; color: #64748b; font-weight: 700;">Cấp bởi (Trường/Tổ chức)</label>
            <input type="text" name="proof_issuer[]" class="form-control" style="font-size: 0.82rem; padding: 0.35rem 0.5rem;" placeholder="VD: Cambridge...">
        </div>
        <div>
            <label style="font-size: 0.72rem; color: #64748b; font-weight: 700;">Năm cấp</label>
            <input type="text" name="proof_year[]" class="form-control" style="font-size: 0.82rem; padding: 0.35rem 0.5rem;" placeholder="2023">
        </div>
        <div>
            <label style="font-size: 0.72rem; color: #64748b; font-weight: 700;">Link minh chứng / Bằng cấp</label>
            <input type="text" name="proof_link[]" class="form-control" style="font-size: 0.82rem; padding: 0.35rem 0.5rem;" placeholder="https://drive.google...">
        </div>
        <div style="align-self: flex-end;">
            <button type="button" class="btn btn-outline btn-sm" onclick="this.closest('.proof-row').remove()" style="color: #dc2626; border-color: #fca5a5; font-size: 0.8rem; padding: 0.35rem 0.6rem;">&times;</button>
        </div>
    `;
    container.appendChild(row);
}

function addWorkRow() {
    const container = document.getElementById('workHistoryContainer');
    const notice = document.getElementById('noWorkNotice');
    if (notice) notice.remove();

    const row = document.createElement('div');
    row.className = 'work-row';
    row.style.cssText = 'background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 1rem; position: relative;';
    row.innerHTML = `
        <button type="button" onclick="this.closest('.work-row').remove()" style="position: absolute; right: 8px; top: 8px; background: none; border: none; font-size: 1.2rem; color: #ef4444; cursor: pointer;">&times;</button>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
            <div>
                <label style="font-size: 0.78rem; font-weight: 700; color: #4c1d95;">Tên công ty / Doanh nghiệp *</label>
                <input type="text" name="work_company[]" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;" placeholder="VD: Công ty TNHH ABC..." required>
            </div>
            <div>
                <label style="font-size: 0.78rem; font-weight: 700; color: #4c1d95;">Vị trí / Chức danh</label>
                <input type="text" name="work_position[]" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;" placeholder="VD: Nhân viên kho, Barista...">
            </div>
            <div>
                <label style="font-size: 0.78rem; font-weight: 700; color: #4c1d95;">Từ ngày (bắt đầu)</label>
                <input type="date" name="work_start[]" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;">
            </div>
            <div>
                <label style="font-size: 0.78rem; font-weight: 700; color: #4c1d95;">Đến ngày (kết thúc)</label>
                <input type="date" name="work_end[]" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;">
            </div>
        </div>
        <div>
            <label style="font-size: 0.78rem; font-weight: 700; color: #4c1d95;">Mô tả công việc & Thành tích chính</label>
            <textarea name="work_desc[]" rows="2" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;" placeholder="Mô tả tóm tắt nhiệm vụ chính, kinh nghiệm tích lũy..."></textarea>
        </div>
    `;
    container.appendChild(row);
}

function previewSeekerAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('seeker_avatar_img');
            if (preview) preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
