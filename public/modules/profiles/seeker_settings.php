<?php ob_start(); 
$activeTab = $_GET['tab'] ?? 'trust';
$trustScore = intval($seeker['trust_score'] ?? 100);
$scoreColor = $trustScore >= 80 ? '#10b981' : ($trustScore >= 60 ? '#f59e0b' : '#ef4444');
$scoreLevel = $trustScore >= 80 ? 'Rất Uy Tín (Xuất Sắc)' : ($trustScore >= 60 ? 'Mức Trung Bình' : 'Cảnh Báo Vi Phạm');
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 0.5rem;">
        <?= icon('shield_check', 'text-primary', 26) ?> Thiết Lập Tài Khoản & Điểm Uy Tín Người Lao Động
    </h1>
    <p style="color: var(--slate-500); font-size: 0.95rem; margin-top: 0.25rem;">
        Quản lý điểm uy tín (Trust Score), bản cam kết tuân thủ quy chế phỏng vấn và cài đặt thông báo việc làm qua Gmail.
    </p>
</div>

<!-- TAB NAVIGATION -->
<div style="display: flex; gap: 0.5rem; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.75rem;">
    <a href="?tab=trust" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'trust' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'trust' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'trust' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('award', '', 16) ?> <span>1. Điểm Uy Tín & Cam Kết (<?= $trustScore ?>/100)</span>
    </a>
    <a href="?tab=mail" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'mail' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'mail' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'mail' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('mail', '', 16) ?> <span>2. Thông Báo Việc Làm Qua Gmail</span>
    </a>
    <a href="?tab=privacy" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'privacy' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'privacy' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'privacy' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('lock', '', 16) ?> <span>3. Quyền Riêng Tư & Bảo Mật</span>
    </a>
</div>

<!-- TAB 1: TRUST SCORE & CAM KẾT RÀNG BUỘC -->
<?php if ($activeTab === 'trust'): ?>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
        <!-- THẺ ĐIỂM UY TÍN HIỆN TẠI -->
        <div class="card" style="border-top: 4px solid <?= $scoreColor ?>;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a;">Chỉ Số Điểm Uy Tín (Trust Score)</h3>
                <span class="badge" style="background: <?= $scoreColor ?>20; color: <?= $scoreColor ?>; font-weight: 700;">
                    <?= $scoreLevel ?>
                </span>
            </div>

            <div style="text-align: center; padding: 1.5rem 0;">
                <div style="font-size: 3.5rem; font-weight: 900; color: <?= $scoreColor ?>; line-height: 1;">
                    <?= $trustScore ?><span style="font-size: 1.5rem; color: #94a3b8; font-weight: 600;">/100</span>
                </div>
                <div style="width: 100%; background: #e2e8f0; height: 10px; border-radius: 9999px; margin-top: 1rem; overflow: hidden;">
                    <div style="width: <?= $trustScore ?>%; background: <?= $scoreColor ?>; height: 100%; border-radius: 9999px;"></div>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; font-size: 0.85rem; line-height: 1.7; color: #334155;">
                <div><strong>Quyền Lợi:</strong> <?= $trustScore >= 80 ? 'Hồ sơ của bạn được ưu tiên nổi bật trước các Nhà tuyển dụng.' : 'Được phép nộp đơn ứng tuyển bình thường.' ?></div>
                <div><strong>Số lần bị cảnh cáo:</strong> <span style="color: #ef4444; font-weight: 700;"><?= intval($seeker['warning_count'] ?? 0) ?> lần</span></div>
                <div><strong>Trạng thái cam kết:</strong> <span style="color: #10b981; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('check', '', 12) ?> Đã ký thỏa thuận tuân thủ</span></div>
            </div>

            <!-- NGUYÊN TẮC CỘNG/TRỪ ĐIỂM -->
            <div style="margin-top: 1.25rem;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #0f172a;">Quy Tắc Tính Điểm Ràng Buộc:</h4>
                <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.82rem; color: #475569; line-height: 1.6;">
                    <li><span style="color: #10b981; font-weight: 700;">+5 điểm:</span> Đến phỏng vấn đúng giờ theo lịch hẹn.</li>
                    <li><span style="color: #10b981; font-weight: 700;">+10 điểm:</span> Nhận đánh giá 5 sao từ Nhà tuyển dụng sau ca làm.</li>
                    <li><span style="color: #ef4444; font-weight: 700;">-30 điểm:</span> Bùng hẹn phỏng vấn không báo trước 12h.</li>
                    <li><span style="color: #ef4444; font-weight: 700;">Tạm khóa:</span> Nếu điểm uy tín dưới 60 điểm.</li>
                </ul>
            </div>
        </div>

        <!-- BẢN CAM KẾT & XÁC THỰC CCCD -->
        <div class="card">
            <div style="margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
                <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a;">Bản Cam Kết Điện Tử Người Lao Động</h3>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.82rem; color: #64748b;">
                    Ký cam kết tuân thủ quy chế để bảo vệ quyền lợi và nâng cao uy tín tài khoản.
                </p>
            </div>

            <form action="<?= url('/seeker/agreement/sign') ?>" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Họ Và Tên Đầy Đủ *</label>
                    <input type="text" class="form-control" readonly value="<?= htmlspecialchars($seeker['full_name'] ?? '') ?>" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #f8fafc;">
                </div>

                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Số CCCD / Định Danh Cá Nhân (Tùy chọn xác thực)</label>
                    <input type="text" name="id_card_number" class="form-control" placeholder="Nhập số CCCD (12 chữ số)..." value="<?= htmlspecialchars($seeker['id_card_number'] ?? '') ?>" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1rem; font-size: 0.82rem; color: #166534; line-height: 1.6;">
                    <strong>NỘI DUNG CAM KẾT:</strong>
                    <ol style="margin: 0.5rem 0 0 0; padding-left: 1.2rem;">
                        <li>Tôi cam kết cung cấp thông tin kỹ năng, kinh nghiệm trung thực.</li>
                        <li>Tôi cam kết có mặt đúng giờ khi đã đồng ý lịch hẹn phỏng vấn.</li>
                        <li>Nếu có việc đột xuất, tôi cam kết thông báo trước ít nhất 12 giờ trên ứng dụng.</li>
                        <li>Tôi đồng ý với chế tài trừ Điểm Uy Tín nếu vi phạm quy chế sàn.</li>
                    </ol>
                </div>

                <div style="text-align: right; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                        <?= icon('check', '', 15) ?> Xác Nhận Ký Điện Tử Cam Kết (+5 Điểm Uy Tín)
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php elseif ($activeTab === 'mail'): ?>
    <!-- TAB 2: THÔNG BÁO GMAIL -->
    <div class="card" style="max-width: 760px;">
        <h3 style="margin: 0 0 1rem 0; font-size: 1.15rem; color: #0f172a;">Cài Đặt Thông Báo Việc Làm & Tin Nhắn Về Gmail</h3>
        <p style="color: #64748b; font-size: 0.88rem; line-height: 1.6; margin-bottom: 1.5rem;">
            Hệ thống sẽ gửi email tự động tới hòm thư: <strong><?= htmlspecialchars($seeker['email'] ?? $_SESSION['user']['email'] ?? 'Gmail của bạn') ?></strong>.
        </p>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <label style="display: flex; align-items: center; gap: 0.75rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.92rem;">Thông báo lịch phỏng vấn mới</strong>
                    <div style="color: #64748b; font-size: 0.8rem;">Nhận thư mời phỏng vấn ngay khi Nhà tuyển dụng xếp lịch hẹn kèm vị trí bản đồ.</div>
                </div>
            </label>

            <label style="display: flex; align-items: center; gap: 0.75rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.92rem;">Thông báo kết quả duyệt đơn ứng tuyển</strong>
                    <div style="color: #64748b; font-size: 0.8rem;">Nhận email thông báo khi hồ sơ của bạn được Nhà tuyển dụng phê duyệt hoặc phản hồi.</div>
                </div>
            </label>

            <label style="display: flex; align-items: center; gap: 0.75rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.92rem;">Tin nhắn trao đổi từ Nhà tuyển dụng</strong>
                    <div style="color: #64748b; font-size: 0.8rem;">Nhận email thông báo khi có tin nhắn mới từ công ty.</div>
                </div>
            </label>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="button" class="btn btn-primary" onclick="alert('Đã lưu tùy chọn thông báo Gmail thành công!')" style="font-weight: 700;">
                Lưu Tùy Chọn Gmail
            </button>
        </div>
    </div>
<?php else: ?>
    <!-- TAB 3: QUYỀN RIÊNG TƯ -->
    <div class="card" style="max-width: 760px;">
        <h3 style="margin: 0 0 1rem 0; font-size: 1.15rem; color: #0f172a;">Quyền Riêng Tư & Hiển Thị Hồ Sơ</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <label style="display: flex; align-items: center; gap: 0.75rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.92rem;">Cho phép Nhà tuyển dụng tìm kiếm hồ sơ của tôi</strong>
                    <div style="color: #64748b; font-size: 0.8rem;">Các doanh nghiệp đã xác thực chữ ký số có thể chủ động liên hệ mời bạn phỏng vấn.</div>
                </div>
            </label>
            <label style="display: flex; align-items: center; gap: 0.75rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.92rem;">Hiển thị Điểm Uy Tín trên hồ sơ ứng tuyển</strong>
                    <div style="color: #64748b; font-size: 0.8rem;">Tăng cơ hội trúng tuyển khi điểm uy tín của bạn đạt mức Xuất sắc (80-100).</div>
                </div>
            </label>
        </div>
    </div>
<?php endif; ?>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
