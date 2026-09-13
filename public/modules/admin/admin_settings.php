<?php ob_start(); 
$activeTab = $_GET['tab'] ?? 'mail';
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 0.5rem;">
        <?= icon('sliders', 'text-primary', 26) ?> Thiết Lập Hệ Thống & Cấu Hình Sàn JobLink
    </h1>
    <p style="color: var(--slate-500); font-size: 0.95rem; margin-top: 0.25rem;">
        Cấu hình toàn diện máy chủ gửi thư Gmail SMTP, Chữ ký số Sàn JobLink Root CA, Google AI Gemini và Quy chế ràng buộc điểm uy tín người lao động.
    </p>
</div>

<!-- TAB NAVIGATION -->
<div style="display: flex; gap: 0.5rem; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.75rem;">
    <a href="?tab=mail" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'mail' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'mail' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'mail' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('mail', '', 16) ?> <span>1. Máy Chủ Gmail (SMTP)</span>
    </a>
    <a href="?tab=ca" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'ca' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'ca' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'ca' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('certificate', '', 16) ?> <span>2. Chữ Ký Số Sàn (Root CA)</span>
    </a>
    <a href="?tab=ai" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'ai' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'ai' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'ai' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('zap', '', 16) ?> <span>3. Cấu Hình AI (Gemini)</span>
    </a>
    <a href="?tab=binding" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'binding' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'binding' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'binding' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('scale', '', 16) ?> <span>4. Điểm Uy Tín & Ràng Buộc NLĐ</span>
    </a>
</div>

<!-- TAB 1: SMTP GMAIL -->
<?php if ($activeTab === 'mail'): ?>
    <div class="card" style="max-width: 860px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
            <div>
                <h3 style="margin: 0; font-size: 1.2rem; color: #0f172a;">Cấu Hình Máy Chủ Gửi Thư Gmail (SMTP)</h3>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">
                    Gửi email tự động khi có tin nhắn mới, đơn ứng tuyển mới và lịch phỏng vấn.
                </p>
            </div>
            <span class="badge badge-success"><?= icon('mail_check', '', 14) ?> SMTP Chuẩn RFC 5321</span>
        </div>

        <form action="<?= url('/admin/settings/mail-update') ?>" method="POST" style="display: flex; flex-direction: column; gap: 1.2rem;">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Máy Chủ SMTP (SMTP Host) *</label>
                    <input type="text" name="mail_host" class="form-control" required value="<?= htmlspecialchars($settings['mail_host'] ?? 'smtp.gmail.com') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Cổng (Port) *</label>
                    <input type="number" name="mail_port" class="form-control" required value="<?= htmlspecialchars($settings['mail_port'] ?? '587') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Tài Khoản Gmail (Username / Email) *</label>
                    <input type="email" name="mail_username" class="form-control" required value="<?= htmlspecialchars($settings['mail_username'] ?? 'joblink.system.notify@gmail.com') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Mật Khẩu Ứng Dụng (App Password) *</label>
                    <input type="password" name="mail_password" class="form-control" required value="<?= htmlspecialchars($settings['mail_password'] ?? '') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Loại Mã Hóa</label>
                    <select name="mail_encryption" class="form-control" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                        <option value="tls" <?= ($settings['mail_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS (Khuyên dùng - Port 587)</option>
                        <option value="ssl" <?= ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                    </select>
                </div>
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Email Người Gửi</label>
                    <input type="email" name="mail_from_address" class="form-control" value="<?= htmlspecialchars($settings['mail_from_address'] ?? 'joblink.system.notify@gmail.com') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Tên Hiển Thị Người Gửi</label>
                    <input type="text" name="mail_from_name" class="form-control" value="<?= htmlspecialchars($settings['mail_from_name'] ?? 'JobLink - Cổng Kết Nối Việc Làm') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
            </div>

            <div>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: 700; color: #0f172a;">
                    <input type="checkbox" name="mail_enabled" value="1" <?= ($settings['mail_enabled'] ?? '1') === '1' ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                    <span>Kích hoạt tính năng tự động gửi thông báo qua Gmail trên toàn hệ thống</span>
                </label>
            </div>

            <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1.25rem; margin-top: 0.5rem;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.95rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;"><?= icon('mail', '', 16) ?> Thử Nghiệm Kết Nối SMTP Trực Tiếp</h4>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <input type="email" id="testEmailInput" placeholder="Nhập email của bạn để nhận thư test..." class="form-control" style="flex: 1; min-width: 240px; padding: 0.55rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="sendTestMail()" id="btnTestMail" style="font-weight: 700; padding: 0.55rem 1.25rem;">
                        <?= icon('mail', '', 14) ?> Gửi Thư Thử Nghiệm
                    </button>
                </div>
                <div id="testMailResult" style="margin-top: 0.75rem; font-size: 0.85rem; display: none;"></div>
            </div>

            <div style="text-align: right; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
                <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 0.75rem 1.75rem;">
                    Lưu Cấu Hình SMTP Gmail
                </button>
            </div>
        </form>
    </div>
<?php elseif ($activeTab === 'ca'): ?>
    <!-- TAB 2: PLATFORM ROOT CA -->
    <div class="card" style="max-width: 860px;">
        <div style="margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
            <h3 style="margin: 0; font-size: 1.2rem; color: #0f172a;">Chứng Thư Số Gốc Sàn JobLink (Platform Root CA)</h3>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">
                Dùng để ký số đại diện Ban Quản Trị Hệ Thống JobLink trên các Hợp đồng điện tử với Doanh nghiệp và văn bản chứng thực.
            </p>
        </div>

        <form action="<?= url('/admin/settings/ca-update') ?>" method="POST" style="display: flex; flex-direction: column; gap: 1.2rem;">
            <div>
                <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Tổ Chức Phát Hành Chứng Thư Gốc *</label>
                <input type="text" name="platform_ca_name" class="form-control" required value="<?= htmlspecialchars($settings['platform_ca_name'] ?? 'JobLink Platform Root CA - Sàn Giao Dịch Việc Làm') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Mã Số Thuế / Pháp Nhân Sàn *</label>
                    <input type="text" name="platform_tax_code" class="form-control" required value="<?= htmlspecialchars($settings['platform_tax_code'] ?? '3702988899') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Số Serial Chứng Thư Số Gốc *</label>
                    <input type="text" name="platform_ca_serial" class="form-control" required value="<?= htmlspecialchars($settings['platform_ca_serial'] ?? 'JL-CA-2026-ROOT-9988') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
            </div>

            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1.25rem;">
                <div style="font-weight: 700; color: #166534; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.4rem;">
                    <?= icon('shield_check', '', 18) ?> <span>Tiêu Chuẩn Chữ Ký Số Việt Nam (PKI / X.509 SHA-256)</span>
                </div>
                <p style="margin: 0; font-size: 0.85rem; color: #166534; line-height: 1.6;">
                    Chứng thư số của sàn đã sẵn sàng đóng dấu ký số lên mọi Hợp đồng điện tử với Doanh nghiệp. Mọi bản ký đều có mã băm toàn vẹn SHA-256 chống sửa đổi.
                </p>
            </div>

            <div style="text-align: right; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
                <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 0.75rem 1.75rem;">
                    Lưu Chứng Thư Số Sàn
                </button>
            </div>
        </form>
    </div>
<?php elseif ($activeTab === 'ai'): ?>
    <!-- TAB 3: AI GEMINI -->
    <div class="card" style="max-width: 860px;">
        <div style="margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
            <h3 style="margin: 0; font-size: 1.2rem; color: #0f172a;">Cấu Hình Google Gemini AI & Chatbot Trợ Lý Sàn</h3>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">
                Tích hợp AI đề xuất việc làm thông minh và Chatbot hướng dẫn sử dụng web 24/7.
            </p>
        </div>

        <form action="<?= url('/admin/ai-settings/update') ?>" method="POST" style="display: flex; flex-direction: column; gap: 1.2rem;">
            <div>
                <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Khóa Google Gemini API Key *</label>
                <input type="text" name="gemini_api_key" class="form-control" required value="<?= htmlspecialchars($settings['gemini_api_key'] ?? '') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1; font-family: monospace;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Mô Hình (Model)</label>
                    <select name="gemini_model" class="form-control" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                        <option value="gemini-3.7-flash" <?= ($settings['gemini_model'] ?? '') === 'gemini-3.7-flash' ? 'selected' : '' ?>>gemini-3.7-flash (Khuyên dùng - Siêu nhanh & Thông minh)</option>
                        <option value="gemini-2.0-flash" <?= ($settings['gemini_model'] ?? '') === 'gemini-2.0-flash' ? 'selected' : '' ?>>gemini-2.0-flash</option>
                        <option value="gemini-1.5-flash" <?= ($settings['gemini_model'] ?? '') === 'gemini-1.5-flash' ? 'selected' : '' ?>>gemini-1.5-flash</option>
                    </select>
                </div>
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Trạng Thái AI</label>
                    <select name="gemini_is_active" class="form-control" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                        <option value="1" <?= ($settings['gemini_is_active'] ?? '1') === '1' ? 'selected' : '' ?>>Đang Bật</option>
                        <option value="0" <?= ($settings['gemini_is_active'] ?? '1') === '0' ? 'selected' : '' ?>>Tạm Tắt</option>
                    </select>
                </div>
            </div>

            <div style="text-align: right; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
                <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 0.75rem 1.75rem;">
                    Lưu Cấu Hình Gemini AI
                </button>
            </div>
        </form>
    </div>
<?php else: ?>
    <!-- TAB 4: QUY CHẾ & ĐIỂM UY TÍN NLĐ -->
    <div class="card" style="max-width: 860px;">
        <div style="margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
            <h3 style="margin: 0; font-size: 1.2rem; color: #0f172a;">Quy Chế Ràng Buộc & Điểm Uy Tín (Trust Score) Người Lao Động</h3>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">
                Thiết lập mức điểm uy tín tối thiểu và chế tài phạt khi người lao động bùng hẹn phỏng vấn hoặc hủy nhận việc trái quy định.
            </p>
        </div>

        <form action="<?= url('/admin/settings/binding-update') ?>" method="POST" style="display: flex; flex-direction: column; gap: 1.2rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Điểm Uy Tín Tối Thiểu Để Được Ứng Tuyển *</label>
                    <input type="number" name="seeker_min_trust" class="form-control" required min="10" max="100" value="<?= htmlspecialchars($settings['seeker_min_trust'] ?? '60') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <span style="font-size: 0.75rem; color: #64748b;">Nếu dưới mức này, tài khoản sẽ bị tạm khóa quyền nộp đơn.</span>
                </div>
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Mức Điểm Phạt Khi Bùng Hẹn Phỏng Vấn *</label>
                    <input type="number" name="seeker_penalty_no_show" class="form-control" required min="5" max="50" value="<?= htmlspecialchars($settings['seeker_penalty_no_show'] ?? '30') ?>" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <span style="font-size: 0.75rem; color: #64748b;">Trừ vào tài khoản người lao động khi vắng mặt không lý do.</span>
                </div>
            </div>

            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 1.25rem;">
                <div style="font-weight: 700; color: #92400e; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.35rem;"><?= icon('file_text', '', 16) ?> Điều Khoản Cam Kết Khi Người Lao Động Đăng Ký</div>
                <p style="margin: 0; font-size: 0.85rem; color: #92400e; line-height: 1.6;">
                    Khi đăng ký hoặc ứng tuyển lần đầu, người lao động phải xác nhận ký điện tử bản <strong>Cam Kết Tuân Thủ Quy Chế Ứng Tuyển</strong> (có mặt phỏng vấn đúng giờ, trung thực hồ sơ, không bỏ ca). Điểm khởi tạo: 100 điểm.
                </p>
            </div>

            <div style="text-align: right; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
                <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 0.75rem 1.75rem;">
                    Lưu Quy Chế Ràng Buộc
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<script>
function sendTestMail() {
    var email = document.getElementById('testEmailInput').value.trim();
    var resultDiv = document.getElementById('testMailResult');
    var btn = document.getElementById('btnTestMail');

    if (!email) {
        alert('Vui lòng nhập địa chỉ email nhận thư thử nghiệm!');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Đang gửi...';
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<span style="color:#0d9488; display:inline-flex; align-items:center; gap:4px;"><svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;display:inline-block;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> Đang kết nối tới máy chủ SMTP Gmail và gửi thư thử nghiệm...</span>';

    var formData = new FormData();
    formData.append('test_email', email);
    formData.append('mail_host', document.querySelector('input[name="mail_host"]').value);
    formData.append('mail_port', document.querySelector('input[name="mail_port"]').value);
    formData.append('mail_username', document.querySelector('input[name="mail_username"]').value);
    formData.append('mail_password', document.querySelector('input[name="mail_password"]').value);
    formData.append('mail_encryption', document.querySelector('select[name="mail_encryption"]').value);
    formData.append('mail_from_address', document.querySelector('input[name="mail_from_address"]').value);
    formData.append('mail_from_name', document.querySelector('input[name="mail_from_name"]').value);

    fetch('<?= url("/admin/settings/mail-test") ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Gửi Thư Thử Nghiệm';
        if (data.success) {
            resultDiv.innerHTML = '<div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:8px 12px; border-radius:6px; display:flex; align-items:center; gap:4px;"><svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;display:inline-block;"><polyline points="20 6 9 17 4 12"></polyline></svg> <strong>Thành công:</strong> ' + data.message + '</div>';
        } else {
            resultDiv.innerHTML = '<div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:8px 12px; border-radius:6px; display:flex; align-items:center; gap:4px;"><svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;display:inline-block;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> <strong>Lỗi:</strong> ' + data.message + '</div>';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Gửi Thư Thử Nghiệm';
        resultDiv.innerHTML = '<div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:8px 12px; border-radius:6px; display:flex; align-items:center; gap:4px;"><svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;display:inline-block;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> <strong>Lỗi kết nối:</strong> ' + err + '</div>';
    });
}
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php';
?>
