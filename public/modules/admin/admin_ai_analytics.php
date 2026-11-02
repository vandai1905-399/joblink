<?php 
ob_start(); 
$analytics = AIService::getTrainingAnalyticsData();
$ageStats = $analytics['age_stats'] ?? [];
$expStats = $analytics['exp_stats'] ?? [];
$skillStats = $analytics['skill_stats'] ?? [];
$caStats = $analytics['ca_stats'] ?? [];
$trustStats = $analytics['trust_stats'] ?? [];
$insights = $analytics['insights'] ?? [];

$chatFilterRole = $_GET['chat_role'] ?? '';
$chatSearch = trim($_GET['chat_search'] ?? '');
$chatLogs = AIService::getChatLogs(['role_code' => $chatFilterRole, 'search' => $chatSearch], 50);
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

<!-- 6. NHẬT KÝ HỘI THOẠI & DỮ LIỆU HỌC TẬP AI -->
<div class="card" style="margin-bottom: 1.5rem; border-radius: 12px;" id="ai-chat-logs-section">
    <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; display: flex; align-items: center; gap: 6px;">
                <?= icon('chat', '', 18) ?> <span>Nhật Ký Hội Thoại & Dữ Liệu Học Tập AI</span>
            </h3>
            <div style="font-size: 0.82rem; color: #64748b;">
                Lưu trữ toàn bộ câu hỏi & câu trả lời theo từng người dùng để phân tích và tối ưu hóa phản hồi của AI.
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <span class="badge" style="background: #e0f2fe; color: #0284c7; font-weight: 700; font-size: 0.8rem; padding: 6px 12px;">
                Tổng hội thoại: <?= number_format($chatStats['total'] ?? 0) ?>
            </span>
            <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 700; font-size: 0.8rem; padding: 6px 12px;">
                Phản hồi TB: <?= number_format($chatStats['avg_time'] ?? 0) ?> ms
            </span>
        </div>
    </div>

    <!-- BỘ LỌC TÌM KIẾM -->
    <form method="GET" action="<?= url('/admin/ai-analytics') ?>#ai-chat-logs-section" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem; margin-bottom: 1.25rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 200px;">
            <input type="text" name="chat_search" value="<?= htmlspecialchars($chatSearch) ?>" class="form-control" placeholder="Tìm theo nội dung câu hỏi hoặc câu trả lời..." style="font-size: 0.85rem; padding: 0.45rem 0.85rem;">
        </div>
        <div style="width: 190px;">
            <select name="chat_role" class="form-control" style="font-size: 0.85rem; padding: 0.45rem 0.85rem;">
                <option value="">-- Tất cả vai trò --</option>
                <option value="GUEST" <?= $chatFilterRole === 'GUEST' ? 'selected' : '' ?>>Khách vãng lai (GUEST)</option>
                <option value="JOB_SEEKER" <?= $chatFilterRole === 'JOB_SEEKER' ? 'selected' : '' ?>>Người tìm việc (SEEKER)</option>
                <option value="EMPLOYER_ADMIN" <?= $chatFilterRole === 'EMPLOYER_ADMIN' ? 'selected' : '' ?>>Doanh nghiệp (EMPLOYER)</option>
                <option value="INTERMEDIARY" <?= $chatFilterRole === 'INTERMEDIARY' ? 'selected' : '' ?>>Trung gian (INTERMEDIARY)</option>
                <option value="SUPER_ADMIN" <?= $chatFilterRole === 'SUPER_ADMIN' ? 'selected' : '' ?>>Quản trị viên (ADMIN)</option>
            </select>
        </div>
        <div style="display: flex; gap: 0.4rem;">
            <button type="submit" class="btn btn-primary" style="font-weight: 700; font-size: 0.82rem; padding: 0.45rem 1rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                <?= icon('search', '', 13) ?> Lọc dữ liệu
            </button>
            <?php if (!empty($chatSearch) || !empty($chatFilterRole)): ?>
                <a href="<?= url('/admin/ai-analytics') ?>#ai-chat-logs-section" class="btn btn-outline" style="font-size: 0.82rem; padding: 0.45rem 0.8rem;">
                    Xóa lọc
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- BẢNG NHẬT KÝ HỘI THOẠI -->
    <?php if (empty($chatLogs)): ?>
        <div style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8; font-size: 0.9rem;">
            Chưa có lịch sử hội thoại nào được ghi nhận. Khi người dùng hoặc khách vãng lai trò chuyện cùng Joby AI, toàn bộ dữ liệu sẽ tự động lưu trữ tại đây.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 0.78rem; text-transform: uppercase; color: #64748b;">
                        <th style="padding: 0.75rem 0.85rem; width: 140px;">Thời Gian</th>
                        <th style="padding: 0.75rem 0.85rem; width: 140px;">Vai Trò / User</th>
                        <th style="padding: 0.75rem 0.85rem; width: 28%;">Câu Hỏi Người Dùng</th>
                        <th style="padding: 0.75rem 0.85rem;">Phản Hồi Joby AI</th>
                        <th style="padding: 0.75rem 0.85rem; width: 90px; text-align: center;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chatLogs as $log): ?>
                        <?php 
                        $roleBadgeColor = '#64748b';
                        $roleBadgeBg = '#f1f5f9';
                        $roleName = 'Khách vãng lai';
                        if ($log['role_code'] === 'JOB_SEEKER') {
                            $roleBadgeColor = '#059669';
                            $roleBadgeBg = '#dcfce7';
                            $roleName = 'Người tìm việc';
                        } elseif (in_array($log['role_code'], ['EMPLOYER_ADMIN', 'INTERMEDIARY'])) {
                            $roleBadgeColor = '#0284c7';
                            $roleBadgeBg = '#e0f2fe';
                            $roleName = 'Doanh nghiệp';
                        } elseif (in_array($log['role_code'], ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
                            $roleBadgeColor = '#7c3aed';
                            $roleBadgeBg = '#ede9fe';
                            $roleName = 'Admin / CSKH';
                        }
                        $suggList = !empty($log['suggestions_json']) ? json_decode($log['suggestions_json'], true) : [];
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; vertical-align: top;">
                            <td style="padding: 0.85rem 0.75rem; color: #64748b; font-size: 0.8rem;">
                                <strong><?= date('d/m/Y', strtotime($log['created_at'])) ?></strong><br>
                                <span style="font-size: 0.75rem; color: #94a3b8;"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                                <?php if (!empty($log['response_time_ms'])): ?>
                                    <br><span style="font-size: 0.72rem; color: #0d9488; font-weight: 700;"><?= $log['response_time_ms'] ?>ms</span>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 0.85rem 0.75rem;">
                                <span class="badge" style="background: <?= $roleBadgeBg ?>; color: <?= $roleBadgeColor ?>; font-weight: 700; font-size: 0.72rem; margin-bottom: 4px; display: inline-block;">
                                    <?= $roleName ?>
                                </span>
                                <?php if (!empty($log['username'])): ?>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.8rem;"><?= htmlspecialchars($log['username']) ?></div>
                                <?php elseif (!empty($log['session_id'])): ?>
                                    <div style="font-size: 0.72rem; color: #94a3b8; font-family: monospace;" title="<?= htmlspecialchars($log['session_id']) ?>">
                                        <?= substr($log['session_id'], 0, 10) ?>...
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 0.85rem 0.75rem; color: #0f172a; font-weight: 600; line-height: 1.45;">
                                <?= nl2br(htmlspecialchars($log['user_message'])) ?>
                            </td>

                            <td style="padding: 0.85rem 0.75rem; color: #334155; line-height: 1.5; font-size: 0.82rem;">
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem; max-height: 160px; overflow-y: auto;">
                                    <?= nl2br(htmlspecialchars($log['ai_reply'])) ?>
                                </div>
                                <?php if (!empty($suggList) && is_array($suggList)): ?>
                                    <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 4px;">
                                        <?php foreach ($suggList as $s): ?>
                                            <span style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; font-size: 0.7rem; padding: 2px 7px; border-radius: 9999px;">
                                                <?= htmlspecialchars($s) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 0.85rem 0.75rem; text-align: center;">
                                <form action="<?= url('/admin/ai-analytics/delete-log/' . $log['log_id']) ?>" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dòng nhật ký này không?');" style="margin: 0;">
                                    <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; font-size: 0.75rem; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.2rem;" title="Xóa nhật ký">
                                        <?= icon('x', '', 12) ?> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
$extraJs = [];
require_once __DIR__ . '/../layouts/admin_layout.php';
?>