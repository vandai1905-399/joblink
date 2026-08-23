<?php 
$extraCss = ['css/auth/register.css'];
$extraJs = ['js/auth/auth.js'];
ob_start(); 

$initialStep = isset($step) && in_array($step, [1, 2, 3]) ? $step : 1;
$currentUser = $user ?? ($_SESSION['user'] ?? null);
$currentRole = $currentUser['role_code'] ?? 'JOB_SEEKER';
$initialStep = ($currentUser && $initialStep === 1) ? 2 : $initialStep;
?>

<div class="register-wizard-wrapper">
    <div class="register-card">
        <!-- THANH TIẾN TRÌNH 3 BƯỚC (STEP INDICATOR TRACKER) -->
        <div class="wizard-progress-bar">
            <div class="wizard-progress-fill" id="wizard-progress-fill" style="width: <?= $initialStep === 1 ? '0%' : ($initialStep === 2 ? '50%' : '100%') ?>;"></div>
            
            <div class="wizard-step-item <?= $initialStep >= 1 ? ($initialStep === 1 ? 'active' : 'completed') : '' ?>" id="wizard-indicator-1">
                <div class="wizard-step-circle">1</div>
                <div class="wizard-step-label">Tài Khoản & Vai Trò</div>
            </div>

            <div class="wizard-step-item <?= $initialStep >= 2 ? ($initialStep === 2 ? 'active' : 'completed') : '' ?>" id="wizard-indicator-2">
                <div class="wizard-step-circle">2</div>
                <div class="wizard-step-label">Thông Tin Hồ Sơ</div>
            </div>

            <div class="wizard-step-item <?= $initialStep === 3 ? 'active' : '' ?>" id="wizard-indicator-3">
                <div class="wizard-step-circle">3</div>
                <div class="wizard-step-label">Hoàn Tất</div>
            </div>
        </div>

        <div class="wizard-card-body">
            <!-- ============================================================= -->
            <!-- BƯỚC 1: TÀI KHOẢN ĐĂNG NHẬP & CHỌN VAI TRÒ                     -->
            <!-- ============================================================= -->
            <div class="wizard-step-panel <?= $initialStep === 1 ? 'active' : '' ?>" id="wizard-panel-1">
                <div style="text-align: center; margin-bottom: 1.75rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem;">Tạo Tài Khoản Mới</h2>
                    <p style="font-size: 0.9rem; color: #64748b; margin: 0;">Bước 1/3: Chọn vai trò và thiết lập thông tin đăng nhập</p>
                </div>

                <form id="form-register-step-1" action="<?= url('/auth/register') ?>" method="POST" onsubmit="event.preventDefault(); AuthController.registerStep1(event);">
                    <!-- CHỌN VAI TRÒ -->

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700;">Bạn tham gia JobLink với vai trò nào? *</label>
                        <input type="hidden" name="role_type" id="input_role_type" value="JOB_SEEKER">
                        
                        <div class="role-select-container">
                            <div class="role-card-btn selected" data-role="JOB_SEEKER" onclick="selectRole('JOB_SEEKER')">
                                <div class="role-badge-check">✓</div>
                                <span class="role-icon">👤</span>
                                <div class="role-name">Người Lao Động</div>
                                <div class="role-desc">Tìm việc linh hoạt, nhận gợi ý AI & tích lũy điểm uy tín</div>
                            </div>
                            <div class="role-card-btn" data-role="EMPLOYER_ADMIN" onclick="selectRole('EMPLOYER_ADMIN')">
                                <div class="role-badge-check">✓</div>
                                <span class="role-icon">🏢</span>
                                <div class="role-name">Nhà Tuyển Dụng</div>
                                <div class="role-desc">Đăng tin tuyển dụng, quản lý ca làm & xác thực doanh nghiệp</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" id="username_label">Số điện thoại đăng nhập (Tên tài khoản) *</label>
                        <input type="tel" name="username" id="input_username" class="form-control" required 
                               placeholder="Ví dụ: 0901234567 hoặc 0912345678" autocomplete="username" maxlength="10">
                        <small id="username_hint" style="display: block; margin-top: 0.35rem; font-size: 0.8rem; color: #0d9488; font-weight: 500;">
                            💡 Đối với Người Lao Động, tên tài khoản chính là Số điện thoại của bạn (10 chữ số).
                        </small>
                    </div>


                    <div class="form-group">
                        <label class="form-label">Địa chỉ Email liên hệ *</label>
                        <input type="email" name="email" class="form-control" required placeholder="name@example.com" autocomplete="email">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Mật khẩu *</label>
                            <input type="password" name="password" id="input_password" class="form-control" required placeholder="Tối thiểu 6 ký tự" autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Xác nhận mật khẩu *</label>
                            <input type="password" name="confirm_password" id="input_confirm_password" class="form-control" required placeholder="Nhập lại mật khẩu" autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" id="btn-submit-step-1" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 1rem; font-weight: 700; margin-top: 0.75rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(13,148,136,0.25);">
                        Lưu Tài Khoản & Tiếp Tục Sang Bước 2 &rarr;
                    </button>
                </form>

                <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: #64748b;">
                    Đã có tài khoản? <a href="<?= url('/auth/login') ?>" style="color: #0d9488; font-weight: 700;">Đăng nhập ngay</a>
                </div>
            </div>


            <!-- ============================================================= -->
            <!-- BƯỚC 2: THÔNG TIN HỒ SƠ CHI TIẾT                               -->
            <!-- ============================================================= -->
            <div class="wizard-step-panel <?= $initialStep === 2 ? 'active' : '' ?>" id="wizard-panel-2">
                <div style="text-align: center; margin-bottom: 1.75rem;">
                    <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: #e0f2fe; color: #0369a1; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.78rem; font-weight: 700; margin-bottom: 0.5rem;">
                        ✨ Đã lưu thông tin tài khoản thành công
                    </div>
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem;">Hoàn Thiện Hồ Sơ Của Bạn</h2>
                    <p style="font-size: 0.9rem; color: #64748b; margin: 0;">Bước 2/3: Điền thông tin để hệ thống AI kết nối cơ hội phù hợp nhất</p>
                </div>

                <form id="form-register-step-2" action="<?= url('/auth/register-step2') ?>" method="POST" onsubmit="event.preventDefault(); AuthController.registerStep2(event);">
                    <!-- NỘI DUNG CHO NGƯỜI LAO ĐỘNG -->

                    <div id="step2-seeker-fields" style="<?= ($currentRole === 'JOB_SEEKER' || empty($currentRole)) ? '' : 'display: none;' ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Họ và Tên đầy đủ *</label>
                                <input type="text" name="full_name" id="step2_full_name" class="form-control" placeholder="Ví dụ: Nguyễn Văn An" value="<?= htmlspecialchars($profile['full_name'] ?? ($currentUser['display_name'] ?? '')) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Số điện thoại liên hệ *</label>
                                <input type="text" name="phone_number" id="step2_phone_number" class="form-control" placeholder="0901234567" value="<?= htmlspecialchars($profile['phone_number'] ?? ($currentUser['username'] ?? '')) ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Ngày sinh</label>
                                <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($profile['date_of_birth'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Giới tính</label>
                                <select name="gender" class="form-control">
                                    <option value="Nam" <?= ($profile['gender'] ?? '') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                    <option value="Nữ" <?= ($profile['gender'] ?? '') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                                    <option value="Khác" <?= ($profile['gender'] ?? '') === 'Khác' ? 'selected' : '' ?>>Khác</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Khu vực làm việc ưu tiên</label>
                            <input type="text" name="preferred_location" id="step2_preferred_location" class="form-control" placeholder="Ví dụ: TP. Thủ Dầu Một, Thuận An, KCN VSIP..." value="<?= htmlspecialchars($profile['preferred_location'] ?? '') ?>">
                            <div class="skill-tags-cloud">
                                <span class="skill-tag-item" onclick="setLocationTag('TP. Thủ Dầu Một')">+ Thủ Dầu Một</span>
                                <span class="skill-tag-item" onclick="setLocationTag('TP. Thuận An')">+ Thuận An</span>
                                <span class="skill-tag-item" onclick="setLocationTag('TP. Dĩ An')">+ Dĩ An</span>
                                <span class="skill-tag-item" onclick="setLocationTag('KCN VSIP 1 & 2')">+ KCN VSIP</span>
                                <span class="skill-tag-item" onclick="setLocationTag('KCN Sóng Thần')">+ KCN Sóng Thần</span>
                                <span class="skill-tag-item" onclick="setLocationTag('TP. Hồ Chí Minh')">+ TP.HCM</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Vị trí / Nghề nghiệp mong muốn</label>
                                <input type="text" name="desired_position" class="form-control" placeholder="Ví dụ: Nhân viên kho, Barista, Bán hàng, IT..." value="<?= htmlspecialchars($profile['desired_position'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hình thức làm việc</label>
                                <select name="preferred_employment_type" class="form-control">
                                    <option value="full_time">Toàn thời gian (Full-time)</option>
                                    <option value="part_time">Bán thời gian (Part-time)</option>
                                    <option value="temporary">Thời vụ / Xoay ca linh hoạt</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Kỹ năng nổi bật & Năng khiếu</span>
                                <span style="font-size: 0.75rem; color: #0d9488; font-weight: 600;">✨ Tăng 50% độ khớp việc làm AI</span>
                            </label>
                            <input type="text" name="skills" id="step2_skills_input" class="form-control" placeholder="Ví dụ: Tin học văn phòng, Bán hàng, Tiếng Anh giao tiếp..." value="<?= htmlspecialchars($profile['skills'] ?? '') ?>">
                            <div class="skill-tags-cloud">
                                <span class="skill-tag-item" onclick="addSkillTag('Tin học văn phòng')">+ Tin học văn phòng</span>
                                <span class="skill-tag-item" onclick="addSkillTag('Giao tiếp tốt')">+ Giao tiếp tốt</span>
                                <span class="skill-tag-item" onclick="addSkillTag('Làm việc nhóm')">+ Làm việc nhóm</span>
                                <span class="skill-tag-item" onclick="addSkillTag('Bán hàng & CSKH')">+ Bán hàng & CSKH</span>
                                <span class="skill-tag-item" onclick="addSkillTag('Tiếng Anh')">+ Tiếng Anh</span>
                                <span class="skill-tag-item" onclick="addSkillTag('Lái xe')">+ Lái xe</span>
                            </div>
                        </div>
                    </div>

                    <!-- NỘI DUNG CHO NHÀ TUYỂN DỤNG -->
                    <div id="step2-employer-fields" style="<?= ($currentRole === 'EMPLOYER_ADMIN') ? '' : 'display: none;' ?>">
                        <div class="form-group">
                            <label class="form-label">Tên Doanh Nghiệp / Công Ty / Hộ Kinh Doanh *</label>
                            <input type="text" name="employer_name" id="step2_employer_name" class="form-control" placeholder="Ví dụ: Công ty TNHH Giải Pháp Công Nghệ Toàn Cầu" value="<?= htmlspecialchars($profile['employer_name'] ?? ($currentUser['display_name'] ?? '')) ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Người đại diện pháp luật / Tuyển dụng</label>
                                <input type="text" name="representative_name" class="form-control" placeholder="Ví dụ: Ông Trần Quốc Bảo" value="<?= htmlspecialchars($profile['representative_name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mã số thuế / Giấy phép ĐKKD</label>
                                <input type="text" name="business_license_number" class="form-control" placeholder="Ví dụ: 3701234567" value="<?= htmlspecialchars($profile['business_license_number'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Số điện thoại liên hệ doanh nghiệp</label>
                                <input type="text" name="phone_number" class="form-control" placeholder="Ví dụ: 0274.3888.999" value="<?= htmlspecialchars($profile['phone_number'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Lĩnh vực hoạt động chính</label>
                                <input type="text" name="business_field" class="form-control" placeholder="Ví dụ: Sản xuất điện tử, F&B, Logistics..." value="<?= htmlspecialchars($profile['business_field'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Địa chỉ trụ sở / Nhà máy / Cơ sở làm việc</label>
                            <input type="text" name="address" class="form-control" placeholder="Ví dụ: Lô B1, KCN VSIP 1, TP. Thuận An, Bình Dương" value="<?= htmlspecialchars($profile['address'] ?? '') ?>">
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1.75rem; flex-wrap: wrap;">
                        <button type="button" id="btn-skip-step-2" class="btn" style="flex: 1; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; padding: 0.8rem; border-radius: 10px;">
                            Bỏ Qua & Bổ Sung Sau
                        </button>
                        <button type="submit" id="btn-submit-step-2" class="btn btn-primary" style="flex: 2; padding: 0.8rem; font-weight: 700; border-radius: 10px; box-shadow: 0 4px 12px rgba(13,148,136,0.25);">
                            Lưu Thông Tin & Hoàn Tất &rarr;
                        </button>
                    </div>
                </form>
            </div>

            <!-- ============================================================= -->
            <!-- BƯỚC 3: HOÀN TẤT & KHÁM PHÁ / ĐỀ XUẤT AI                       -->
            <!-- ============================================================= -->
            <div class="wizard-step-panel <?= $initialStep === 3 ? 'active' : '' ?>" id="wizard-panel-3">
                <div class="celebration-box">
                    <div class="celebration-icon">🎉✨</div>
                    <h2 class="celebration-title">Chào Mừng Bạn Đến Với JobLink!</h2>
                    <p class="celebration-subtitle">Tài khoản và hồ sơ của bạn đã được khởi tạo thành công trên hệ thống tuyển dụng việc làm uy tín.</p>

                    <div class="user-identity-card">
                        <div class="identity-stat-item">
                            <div class="identity-stat-label">Tài Khoản</div>
                            <div class="identity-stat-val" id="res-display-name"><?= htmlspecialchars($currentUser['display_name'] ?? 'Thành viên mới') ?></div>
                        </div>
                        <div class="identity-stat-item">
                            <div class="identity-stat-label">Mã Định Danh</div>
                            <div class="identity-stat-val" id="res-account-id"><?= $currentRole === 'JOB_SEEKER' ? formatId('seeker', $currentUser['account_id'] ?? 1) : formatId('employer', $currentUser['account_id'] ?? 1) ?></div>
                        </div>
                        <div class="identity-stat-item">
                            <div class="identity-stat-label">Điểm Uy Tín Khởi Đầu</div>
                            <div class="identity-stat-val" style="color: #0d9488;">100/100 ⭐</div>
                        </div>
                    </div>

                    <div class="action-buttons-group">
                        <a href="<?= $currentRole === 'JOB_SEEKER' ? url('/jobs?ai_recommend=1') : url('/employer/jobs/create') ?>" id="btn-action-primary" class="btn btn-primary" style="padding: 0.9rem; font-size: 1rem; font-weight: 800; border-radius: 10px; text-decoration: none; box-shadow: 0 4px 14px rgba(13,148,136,0.3);">
                            <?= $currentRole === 'JOB_SEEKER' ? '✨ Khám Phá Việc Làm Phù Hợp Gợi Ý Từ AI' : '📝 Đăng Tin Tuyển Dụng Đầu Tiên Ngay' ?>
                        </a>
                        <a href="<?= $currentRole === 'JOB_SEEKER' ? url('/seeker/dashboard') : url('/employer/dashboard') ?>" id="btn-action-secondary" class="btn" style="border: 1px solid #cbd5e1; color: #334155; padding: 0.8rem; font-weight: 700; border-radius: 10px; text-decoration: none;">
                            Vào Bảng Điều Khiển Của Tôi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectRole(role) {
    document.getElementById('input_role_type').value = role;
    document.querySelectorAll('.role-card-btn').forEach(btn => {
        if (btn.getAttribute('data-role') === role) {
            btn.classList.add('selected');
        } else {
            btn.classList.remove('selected');
        }
    });

    const usernameLabel = document.getElementById('username_label');
    const usernameInput = document.getElementById('input_username');
    const usernameHint = document.getElementById('username_hint');

    // Cập nhật quy tắc Tên tài khoản theo vai trò
    if (role === 'JOB_SEEKER') {
        if (usernameLabel) usernameLabel.innerText = 'Số điện thoại đăng nhập (Tên tài khoản) *';
        if (usernameInput) {
            usernameInput.type = 'tel';
            usernameInput.placeholder = 'Ví dụ: 0901234567 hoặc 0912345678';
            usernameInput.maxLength = 10;
            usernameInput.value = usernameInput.value.replace(/[^0-9]/g, '').slice(0, 10);
        }

        if (usernameHint) {
            usernameHint.innerHTML = '💡 Đối với Người Lao Động, tên tài khoản bắt buộc là Số điện thoại của bạn (10 chữ số).';
            usernameHint.style.color = '#0d9488';
        }
        // Cập nhật ẩn hiện trường ở Bước 2
        document.getElementById('step2-seeker-fields').style.display = 'block';
        document.getElementById('step2-employer-fields').style.display = 'none';
    } else {
        if (usernameLabel) usernameLabel.innerText = 'Tên tài khoản doanh nghiệp (Username chữ) *';
        if (usernameInput) {
            usernameInput.type = 'text';
            usernameInput.placeholder = 'Ví dụ: becamex_idc, vinamilk, fpt_software...';
            usernameInput.maxLength = 30;
        }
        if (usernameHint) {
            usernameHint.innerHTML = '🏢 Tên tài khoản Nhà tuyển dụng ở dạng chữ viết liền không dấu (tên doanh nghiệp hoặc thương hiệu).';
            usernameHint.style.color = '#d97706';
        }
        // Cập nhật ẩn hiện trường ở Bước 2
        document.getElementById('step2-seeker-fields').style.display = 'none';
        document.getElementById('step2-employer-fields').style.display = 'block';
    }
}

// Tự động hạn chế ký tự nhập theo vai trò
document.addEventListener('DOMContentLoaded', () => {
    const usernameInput = document.getElementById('input_username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            const role = document.getElementById('input_role_type')?.value || 'JOB_SEEKER';
            if (role === 'JOB_SEEKER') {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
            }
        });
    }
});

function addSkillTag(tag) {
    const input = document.getElementById('step2_skills_input');
    if (!input) return;
    let current = input.value.trim();
    let tags = current ? current.split(',').map(s => s.trim()) : [];
    if (!tags.includes(tag)) {
        tags.push(tag);
        input.value = tags.join(', ');
    }
}

function setLocationTag(loc) {
    const input = document.getElementById('step2_preferred_location');
    if (!input) return;
    input.value = loc;
}
</script>


<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/main_layout.php';
?>

