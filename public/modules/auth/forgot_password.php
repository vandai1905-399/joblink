<?php 
$extraCss = 'css/auth/login.css';
$extraJs = 'js/auth/auth.js';
ob_start(); 
?>

<div class="container forgot-pwd-container" style="max-width: 520px; padding-top: 2.5rem; padding-bottom: 4rem;">
    <div class="card forgot-card">
        <!-- THANH TIẾN TRÌNH 3 BƯỚC WIZARD -->
        <div class="forgot-wizard-bar">
            <div class="wizard-progress-track">
                <div class="wizard-progress-fill" id="forgot-progress-fill" style="width: 0%;"></div>
            </div>
            
            <div class="wizard-step-node active" id="wizard-node-1">
                <div class="node-circle">1</div>
                <div class="node-label">Tìm tài khoản</div>
            </div>

            <div class="wizard-step-node" id="wizard-node-2">
                <div class="node-circle">2</div>
                <div class="node-label">Xác thực hồ sơ</div>
            </div>

            <div class="wizard-step-node" id="wizard-node-3">
                <div class="node-circle">3</div>
                <div class="node-label">Mật khẩu mới</div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- BƯỚC 1: NHẬP SỐ ĐIỆN THOẠI HOẶC EMAIL ĐỂ TÌM TÀI KHOẢN            -->
        <!-- ================================================================= -->
        <div class="forgot-panel active" id="forgot-panel-1">
            <div style="text-align: center; margin-bottom: 1.75rem;">
                <div style="width: 48px; height: 48px; margin: 0 auto 0.75rem; border-radius: 12px; background: #f0fdfa; color: #0d9488; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem;">Quên Mật Khẩu</h1>
                <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Bước 1/3: Nhập số điện thoại hoặc email đã đăng ký để hệ thống kiểm tra tài khoản</p>
            </div>

            <form id="form-forgot-step-1" action="<?= url('/auth/forgot-password') ?>" method="POST" onsubmit="event.preventDefault(); AuthController.forgotStep1(event);">
                <input type="hidden" name="step" value="1">
                
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="identity" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.4rem; color: #334155;">
                        Số điện thoại hoặc Email đăng ký <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" 
                           id="identity" 
                           name="identity" 
                           class="form-control" 
                           placeholder="Ví dụ: 0938111222 hoặc email@example.com" 
                           required 
                           autofocus
                           style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
                    <small style="color: #64748b; font-size: 0.8rem; margin-top: 4px; display: block;">
                        Người lao động có thể nhập số điện thoại dùng để đăng nhập.
                    </small>
                </div>

                <button type="submit" id="btn-submit-step-1" class="btn btn-primary" style="width: 100%; padding: 0.8rem; font-size: 0.95rem; font-weight: 700; border-radius: 8px; margin-top: 0.5rem;">
                    Kiểm Tra Tài Khoản
                </button>
            </form>
        </div>

        <!-- ================================================================= -->
        <!-- BƯỚC 2: XÁC THỰC THÔNG TIN BẢO MẬT (NGÀY SINH, CCCD, GPKD...)     -->
        <!-- ================================================================= -->
        <div class="forgot-panel" id="forgot-panel-2">
            <!-- Thẻ hiển thị tài khoản đã tìm thấy -->
            <div class="account-match-card">
                <div class="match-avatar" id="match-avatar-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="match-info">
                    <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #0d9488; font-weight: 700;">Đã tìm thấy tài khoản</div>
                    <div class="match-name" id="verify-account-name">Người dùng JobLink</div>
                    <div class="match-meta" id="verify-account-phone">0938***222</div>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-bottom: 0.25rem;">Xác Thực Bảo Mật</h2>
                <p style="color: #64748b; font-size: 0.88rem; margin: 0;">Bước 2/3: Trả lời thông tin bảo mật đã lưu trong hồ sơ để xác nhận bạn là chủ tài khoản</p>
            </div>

            <form id="form-forgot-step-2" action="<?= url('/auth/forgot-password') ?>" method="POST" onsubmit="event.preventDefault(); AuthController.forgotStep2(event);">
                <input type="hidden" name="step" value="2">
                
                <!-- Nhóm câu hỏi cho Người Lao Động (JOB_SEEKER) -->
                <div id="fields-seeker-verify">
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" for="verify_dob" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.4rem; color: #334155;">
                            Ngày tháng năm sinh trong hồ sơ <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" 
                               id="verify_dob" 
                               name="verify_dob" 
                               class="form-control" 
                               style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
                        <small style="color: #64748b; font-size: 0.8rem; margin-top: 4px; display: block;">
                            Nhập ngày/tháng/năm sinh bạn đã đăng ký trên hệ thống.
                        </small>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" for="verify_id_card" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.4rem; color: #334155;">
                            Số CCCD / CMND hoặc Họ và tên đầy đủ <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" 
                               id="verify_id_card" 
                               name="verify_id_card" 
                               class="form-control" 
                               placeholder="Nhập 12 số CCCD hoặc Họ và tên trong hồ sơ"
                               style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
                        <small style="color: #64748b; font-size: 0.8rem; margin-top: 4px; display: block;">
                            Nhập số Căn cước công dân hoặc Họ và tên chính xác đã điền trong hồ sơ.
                        </small>
                    </div>
                </div>

                <!-- Nhóm câu hỏi cho Nhà Tuyển Dụng (EMPLOYER_ADMIN) -->
                <div id="fields-employer-verify" style="display: none;">
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" for="verify_license" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.4rem; color: #334155;">
                            Mã số GPKD / Mã số thuế công ty <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" 
                               id="verify_license" 
                               name="verify_license" 
                               class="form-control" 
                               placeholder="Ví dụ: 3700145020"
                               style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" for="verify_representative" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.4rem; color: #334155;">
                            Họ và tên người đại diện pháp luật <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" 
                               id="verify_representative" 
                               name="verify_representative" 
                               class="form-control" 
                               placeholder="Ví dụ: Nguyễn Văn Hùng"
                               style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
                    </div>
                </div>

                <button type="submit" id="btn-submit-step-2" class="btn btn-primary" style="width: 100%; padding: 0.8rem; font-size: 0.95rem; font-weight: 700; border-radius: 8px;">
                    Xác Thực Danh Tính
                </button>

                <button type="button" onclick="AuthController.forgotGoToStep(1)" style="width: 100%; margin-top: 0.65rem; padding: 0.65rem; border: 1.5px solid #cbd5e1; background: #fff; color: #475569; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.2s ease;">
                    Chọn tài khoản khác
                </button>
            </form>
        </div>

        <!-- ================================================================= -->
        <!-- BƯỚC 3: ĐẶT MẬT KHẨU MỚI                                          -->
        <!-- ================================================================= -->
        <div class="forgot-panel" id="forgot-panel-3">
            <div style="text-align: center; margin-bottom: 1.75rem;">
                <div style="display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; color: #059669; padding: 0.35rem 0.85rem; border-radius: 99px; font-size: 0.82rem; font-weight: 700; margin-bottom: 0.75rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Đã xác thực danh tính thành công
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 0.25rem;">Đặt Lại Mật Khẩu</h2>
                <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Bước 3/3: Nhập mật khẩu mới an toàn để đăng nhập lại vào JobLink</p>
            </div>

            <form id="form-forgot-step-3" action="<?= url('/auth/forgot-password') ?>" method="POST" onsubmit="event.preventDefault(); AuthController.forgotStep3(event);">
                <input type="hidden" name="step" value="3">
                
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="forgot_new_password" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.4rem; color: #334155;">
                        Mật khẩu mới muốn đặt <span style="color: #ef4444;">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" 
                               id="forgot_new_password" 
                               name="new_password" 
                               class="form-control" 
                               placeholder="Tối thiểu 6 ký tự" 
                               minlength="6" 
                               required
                               autocomplete="new-password"
                               style="width: 100%; padding: 0.75rem 2.85rem 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
                        <button type="button" 
                                class="btn-toggle-password" 
                                title="Hiện / Ẩn mật khẩu"
                                onclick="AuthController.toggleInputVisibility('forgot_new_password', this)">
                            <svg class="eye-icon eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-icon eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                <line x1="2" y1="2" x2="22" y2="22"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" for="forgot_confirm_password" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.4rem; color: #334155;">
                        Xác nhận lại mật khẩu mới <span style="color: #ef4444;">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" 
                               id="forgot_confirm_password" 
                               name="confirm_password" 
                               class="form-control" 
                               placeholder="Nhập lại mật khẩu mới" 
                               minlength="6" 
                               required
                               autocomplete="new-password"
                               style="width: 100%; padding: 0.75rem 2.85rem 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
                        <button type="button" 
                                class="btn-toggle-password" 
                                title="Hiện / Ẩn mật khẩu"
                                onclick="AuthController.toggleInputVisibility('forgot_confirm_password', this)">
                            <svg class="eye-icon eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-icon eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                <line x1="2" y1="2" x2="22" y2="22"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" id="btn-submit-step-3" class="btn btn-primary" style="width: 100%; padding: 0.8rem; font-size: 0.95rem; font-weight: 700; border-radius: 8px;">
                    Hoàn Tất & Lưu Mật Khẩu Mới
                </button>
            </form>
        </div>

        <!-- FOOTER ĐĂNG NHẬP -->
        <div style="text-align: center; padding: 1.25rem 2.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0;">
            <p style="font-size: 0.9rem; color: #64748b; margin: 0;">
                Bạn đã nhớ lại mật khẩu? <a href="<?= url('/auth/login') ?>" style="color: #0d9488; font-weight: 700; text-decoration: none;">Đăng nhập ngay</a>
            </p>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/main_layout.php';
?>
