<?php 
ob_start(); 
$analytics = AIService::getTrainingAnalyticsData();
$ageStats = $analytics['age_stats'] ?? [];
$expStats = $analytics['exp_stats'] ?? [];
$skillStats = $analytics['skill_stats'] ?? [];
$caStats = $analytics['ca_stats'] ?? [];
$trustStats = $analytics['trust_stats'] ?? [];
$insights = $analytics['insights'] ?? [];

$chatStats = AIService::getChatStats();
?>

<!-- HEADER GỌN GÀNG -->
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: 0.6rem;">
        <span style="color: #0d9488; display: flex; align-items: center;"><?= icon('chart', '', 24) ?></span>
        <h1 style="font-size: 1.4rem; font-weight: 800; color: var(--dark); margin: 0;">Báo Cáo Dữ Liệu Huấn Luyện AI</h1>
        <span class="badge badge-success" style="font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('check', '', 11) ?> 100% CSDL Thực Tế</span>
    </div>

    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= url('/admin/ai-analytics/export') ?>" class="btn btn-outline" style="font-weight: 700; font-size: 0.82rem; padding: 0.45rem 0.9rem; display: inline-flex; align-items: center; gap: 0.25rem;" target="_blank">
            <?= icon('download', '', 13) ?> Xuất JSON
        </a>
        <form action="<?= url('/admin/ai-analytics/sync') ?>" method="POST" style="margin: 0;">
            <button type="submit" class="btn" style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); color: #ffffff; font-weight: 700; font-size: 0.82rem; padding: 0.45rem 0.9rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.25rem;">
                <?= icon('refresh', '', 13) ?> Đồng Bộ AI
            </button>
        </form>
    </div>
</div>

<!-- 1. THẺ CHỈ SỐ CỐT LÕI -->
<div class="grid-stats" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-title">Hồ Sơ Lao Động</div>
        <div class="stat-value" style="color: #0d9488;"><?= number_format($analytics['total_seekers'] ?? 0) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Tin Tuyển Dụng</div>
        <div class="stat-value" style="color: #0284c7;">
            <?= number_format($analytics['total_jobs'] ?? 0) ?>
            <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">/ <?= number_format($analytics['total_all_jobs'] ?? 0) ?> tin</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Đơn Ứng Tuyển</div>
        <div class="stat-value" style="color: #7c3aed;">
            <?= number_format($analytics['total_requests'] ?? 0) ?>
            <span style="font-size: 0.82rem; font-weight: 600; color: #059669;">(<?= $analytics['total_accepted'] ?? 0 ?> trúng tuyển)</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Lịch Phỏng Vấn</div>
        <div class="stat-value" style="color: #059669;"><?= number_format($analytics['total_interviews'] ?? 0) ?></div>
    </div>
</div>

<!-- 2. BẢNG TỶ LỆ THEO ĐỘ TUỔI -->
<div class="card" style="margin-bottom: 1.5rem; border-radius: 12px;">
    <div style="margin-bottom: 1rem;">
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0;">
            1. Tỷ Lệ Tuyển Dụng Theo Độ Tuổi
        </h3>
    </div>

    <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                    <th style="padding: 0.75rem 0.85rem; text-align: left;">Độ Tuổi</th>
                    <th style="padding: 0.75rem 0.85rem; text-align: center;">Số Lượt Nộp</th>
                    <th style="padding: 0.75rem 0.85rem; text-align: center;">Mời Phỏng Vấn</th>
                    <th style="padding: 0.75rem 0.85rem; text-align: center;">Trúng Tuyển</th>
                    <th style="padding: 0.75rem 0.85rem; text-align: left;">Vị Trí Tiêu Biểu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ageStats as $ageKey => $stat): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.88rem;">
                        <td style="padding: 0.85rem;">
                            <strong style="color: #0f172a;"><?= htmlspecialchars($stat['label']) ?></strong>
                        </td>
                        <td style="padding: 0.85rem; text-align: center; font-weight: 700; color: #0f172a;">
                            <?= number_format($stat['total_applied']) ?>
                        </td>
                        <td style="padding: 0.85rem; text-align: center;">
                            <?php if ($stat['total_applied'] > 0): ?>
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                                    <div style="flex: 1; max-width: 60px; background: #e2e8f0; height: 6px; border-radius: 999px; overflow: hidden;">
                                        <div style="width: <?= $stat['interview_rate'] ?>%; background: #0d9488; height: 100%;"></div>
                                    </div>
                                    <strong style="color: #0d9488;"><?= $stat['interview_rate'] ?>%</strong>
                                </div>
                            <?php else: ?>
                                <span style="color: #94a3b8;">0%</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 0.85rem; text-align: center;">
                            <?php if ($stat['total_applied'] > 0): ?>
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                                    <div style="flex: 1; max-width: 60px; background: #e2e8f0; height: 6px; border-radius: 999px; overflow: hidden;">
                                        <div style="width: <?= $stat['accepted_rate'] ?>%; background: #059669; height: 100%;"></div>
                                    </div>
                                    <strong style="color: #059669;"><?= $stat['accepted_rate'] ?>%</strong>
                                </div>
                            <?php else: ?>
                                <span style="color: #94a3b8;">0%</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 0.85rem; color: #334155; font-size: 0.82rem;">
                            <?= htmlspecialchars($stat['top_field']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 3. KINH NGHIỆM & KỸ NĂNG -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
    <!-- KINH NGHIỆM -->
    <div class="card" style="border-radius: 12px;">
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 0.85rem;">
            2. Số Năm Kinh Nghiệm
        </h3>
        <div style="display: flex; flex-direction: column; gap: 0.6rem;">
            <?php foreach ($expStats as $eKey => $eVal): ?>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.7rem 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <strong style="font-size: 0.85rem; color: #0f172a;"><?= htmlspecialchars($eVal['label']) ?></strong>
                        <span style="font-size: 0.82rem; font-weight: 800; color: #0d9488;">
                            <?= $eVal['applied'] > 0 ? $eVal['interview_rate'] . '% mời PV' : '0%' ?>
                        </span>
                    </div>
                    <?php if ($eVal['applied'] > 0): ?>
                        <div style="width: 100%; background: #e2e8f0; height: 5px; border-radius: 999px; overflow: hidden; margin-bottom: 0.3rem;">
                            <div style="width: <?= $eVal['interview_rate'] ?>%; background: #0d9488; height: 100%;"></div>
                        </div>
                    <?php endif; ?>
                    <div style="font-size: 0.75rem; color: #64748b;">
                        <?= $eVal['applied'] ?> đơn • <?= $eVal['accepted'] ?> trúng tuyển
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- KỸ NĂNG -->
    <div class="card" style="border-radius: 12px;">
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 0.85rem;">
            3. Số Lượng Kỹ Năng Hồ Sơ
        </h3>
        <div style="display: flex; flex-direction: column; gap: 0.6rem;">
            <?php foreach ($skillStats as $sKey => $sVal): ?>
                <?php $sColor = $sKey === 'many' ? '#059669' : ($sKey === 'few' ? '#0284c7' : '#f59e0b'); ?>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid <?= $sColor ?>; border-radius: 8px; padding: 0.7rem 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <strong style="font-size: 0.85rem; color: #0f172a;"><?= htmlspecialchars($sVal['label']) ?></strong>
                        <span style="font-size: 0.82rem; font-weight: 800; color: <?= $sColor ?>;">
                            <?= $sVal['applied'] > 0 ? $sVal['accepted_rate'] . '% trúng tuyển' : '0%' ?>
                        </span>
                    </div>
                    <?php if ($sVal['applied'] > 0): ?>
                        <div style="width: 100%; background: #e2e8f0; height: 5px; border-radius: 999px; overflow: hidden; margin-bottom: 0.3rem;">
                            <div style="width: <?= $sVal['accepted_rate'] ?>%; background: <?= $sColor ?>; height: 100%;"></div>
                        </div>
                    <?php endif; ?>
                    <div style="font-size: 0.75rem; color: #64748b;">
                        <?= $sVal['applied'] ?> đơn • <?= $sVal['interviewed'] ?> mời PV • <?= $sVal['accepted'] ?> trúng tuyển
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 4. CHỮ KÝ SỐ & ĐIỂM UY TÍN -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
    <!-- CHỮ KÝ SỐ -->
    <div class="card" style="border-radius: 12px;">
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 0.85rem;">
            4. Doanh Nghiệp Ký Số
        </h3>
        <div style="display: flex; flex-direction: column; gap: 0.6rem;">
            <?php foreach ($caStats as $cKey => $cVal): ?>
                <?php $cColor = $cKey === 'verified' ? '#0d9488' : '#64748b'; ?>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid <?= $cColor ?>; border-radius: 8px; padding: 0.7rem 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <strong style="font-size: 0.85rem; color: #0f172a;"><?= htmlspecialchars($cVal['label']) ?></strong>
                        <span class="badge" style="background: <?= $cColor ?>15; color: <?= $cColor ?>; font-weight: 700; font-size: 0.72rem;">
                            <?= $cVal['posts_count'] ?> tin
                        </span>
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b;">
                        <?= $cVal['applied'] ?> đơn nộp • <?= $cVal['interviewed'] ?> mời PV • <?= $cVal['accepted'] ?> trúng tuyển
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ĐIỂM UY TÍN -->
    <div class="card" style="border-radius: 12px;">
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 0.85rem;">
            5. Phân Bố Điểm Uy Tín
        </h3>
        <div style="display: flex; flex-direction: column; gap: 0.6rem;">
            <?php foreach ($trustStats as $tKey => $tVal): ?>
                <?php $tColor = $tKey === 'high' ? '#059669' : ($tKey === 'medium' ? '#0284c7' : '#ef4444'); ?>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid <?= $tColor ?>; border-radius: 8px; padding: 0.7rem 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong style="font-size: 0.85rem; color: #0f172a;"><?= htmlspecialchars($tVal['range']) ?></strong>
                        <span class="badge" style="background: <?= $tColor ?>15; color: <?= $tColor ?>; font-weight: 700; font-size: 0.72rem;">
                            <?= $tVal['count'] ?> người lao động
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 5. QUY LUẬT TÓM TẮT -->
<div style="margin-bottom: 1.5rem;">
    <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
        <?= icon('sparkle', '', 16) ?> <span>Quy Luật Tuyển Dụng AI Rút Trích</span>
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 0.75rem;">
        <?php foreach ($insights as $item): ?>
            <div style="background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0; border-left: 3px solid <?= $item['color'] ?>; padding: 0.9rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: <?= $item['color'] ?>;">
                        <?= htmlspecialchars($item['tag']) ?>
                    </span>
                </div>
                <div style="font-size: 0.82rem; color: #334155; line-height: 1.5;">
                    <?= htmlspecialchars($item['content']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 6. CHÍNH SÁCH BẢO MẬT: NHẬT KÝ HỘI THOẠI & DỮ LIỆU HỌC TẬP AI -->
<div class="card" style="margin-bottom: 1.5rem; border-radius: 12px; border-left: 4px solid #0d9488;" id="ai-chat-logs-section">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px;">
                <span style="color: #0d9488;"><?= icon('shield', '', 20) ?></span>
                <span>Nhật Ký Hội Thoại & Dữ Liệu Học Tập AI</span>
                <span class="badge" style="background: #f0fdf4; color: #166534; font-size: 0.72rem; font-weight: 700; border: 1px solid #bbf7d0;">
                    Chính Sách Bảo Mật Tuyệt Đối
                </span>
            </h3>
            <p style="font-size: 0.88rem; color: #475569; margin: 0; line-height: 1.5; max-width: 820px;">
                Theo quy chuẩn an toàn và bảo vệ quyền riêng tư người dùng: <strong>Không có bất kỳ bên nào (kể cả Quản trị viên, CSKH hay Nhà tuyển dụng) được quyền đọc, xem hoặc truy xuất nội dung hội thoại cá nhân</strong> giữa người dùng và AI.
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <span class="badge" style="background: #e0f2fe; color: #0284c7; font-weight: 700; font-size: 0.8rem; padding: 6px 12px;">
                Tổng hội thoại ẩn danh: <?= number_format($chatStats['total'] ?? 0) ?>
            </span>
            <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 700; font-size: 0.8rem; padding: 6px 12px;">
                Phản hồi TB: <?= number_format($chatStats['avg_time'] ?? 0) ?> ms
            </span>
        </div>
    </div>

    <!-- KHỐI PHONG TỎA DỮ LIỆU & BẢO VỆ QUYỀN RIÊNG TƯ -->
    <div style="margin-top: 1.25rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #0d9488; flex-shrink: 0;">
            <?= icon('lock', '', 22) ?>
        </div>
        <div style="flex: 1; min-width: 260px;">
            <div style="font-weight: 700; color: #0f172a; font-size: 0.92rem; margin-bottom: 2px;">
                Chế độ cô lập toàn diện (Zero-Access Privacy Guard)
            </div>
            <div style="font-size: 0.82rem; color: #64748b; line-height: 1.45;">
                Tất cả tin nhắn, nội dung trao đổi và lịch sử hỏi đáp của người dùng đều được cô lập. Giao diện quản trị và các API tuyệt đối không hiển thị hay xuất dữ liệu hội thoại ra ngoài nhằm tôn trọng quyền riêng tư tối thượng của Người tìm việc và Doanh nghiệp.
            </div>
        </div>
        <div>
            <form action="<?= url('/admin/ai-analytics/purge-logs') ?>" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn dọn sạch toàn bộ nhật ký hội thoại trên hệ thống không? Hành động này không thể hoàn tác.');" style="margin: 0;">
                <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; font-size: 0.8rem; font-weight: 700; padding: 0.5rem 0.9rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.35rem;" title="Dọn sạch nhật ký hội thoại">
                    <?= icon('x', '', 14) ?> Xóa Sạch Nhật Ký
                </button>
            </form>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
$extraJs = [];
require_once __DIR__ . '/../layouts/admin_layout.php';
?>