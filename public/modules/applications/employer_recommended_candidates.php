<?php 
$extraCss = ['css/applications/employer_applications.css', 'css/jobs/ai_matching.css'];
$activeTab = $_GET['tab'] ?? 'ai_recommendations';
ob_start(); 
?>

<!-- KHU 1 CỦA KHU 3: HEADER CỐ ĐỊNH CHỈ CHỨA MỖI TIÊU ĐỀ & NÚT HÀNH ĐỘNG -->
<div class="workspace-zone-header">
    <div class="workspace-header-title-box">
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
            <span class="badge" style="background: #f0fdfa; color: #0d9488; border: 1px solid #ccfbf1; font-weight: 700; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; padding: 0.2rem 0.5rem; border-radius: 6px;">
                <?= icon('sparkle', '', 12) ?> AI Talent Sourcing
            </span>
        </div>
        <h1 class="workspace-page-title" style="font-size: 1.4rem;">
            Quản Lý NLĐ Đề Xuất & Kho Hồ Sơ Tiềm Năng
        </h1>
        <p class="workspace-page-subtitle">
            Chủ động khai thác nguồn nhân lực phù hợp do AI gợi ý & tái kết nối nhân tài đã hẹn dịp sau
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="<?= url('/employer/jobs/create') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Đăng Tin Mới" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
            <?= icon('plus', '', 14) ?> Đăng Tin Tuyển Dụng Mới
        </a>
        <a href="<?= url('/employer/applications') ?>" class="btn btn-outline btn-sm workspace-nav-link" data-title="Hồ Sơ Ứng Tuyển" style="display: inline-flex; align-items: center; gap: 6px;">
            <?= icon('applications', '', 14) ?> Tất Cả Hồ Sơ Ứng Tuyển
        </a>
    </div>
</div>

<!-- KHU 2 CỦA KHU 3: BODY CHỨA TOÀN BỘ NỘI DUNG THỰC THI (CUỘN ĐỘC LẬP) -->
<div class="workspace-zone-body">

<!-- KHỐI THỐNG KÊ TỔNG QUAN (METRIC OVERVIEW) -->
<div class="sourcing-stats-grid">
    <div class="sourcing-stat-card">
        <div class="sourcing-stat-icon">
            <?= icon('briefcase', '', 20) ?>
        </div>
        <div>
            <div class="sourcing-stat-num"><?= count($activeJobs) ?></div>
            <div class="sourcing-stat-label">Vị trí tuyển dụng đang mở</div>
        </div>
    </div>

    <a href="?tab=ai_recommendations<?= $selectedJobId > 0 ? '&job_id=' . $selectedJobId : '' ?>"
       class="sourcing-stat-card <?= $activeTab === 'ai_recommendations' ? 'active' : '' ?>">
        <div class="sourcing-stat-icon">
            <?= icon('sparkle', '', 20) ?>
        </div>
        <div>
            <div class="sourcing-stat-num"><?= count($aiCandidates) ?></div>
            <div class="sourcing-stat-label">Người lao động được AI đề xuất</div>
        </div>
    </a>

    <a href="?tab=talent_pool<?= $selectedJobId > 0 ? '&job_id=' . $selectedJobId : '' ?>"
       class="sourcing-stat-card <?= $activeTab === 'talent_pool' ? 'active' : '' ?>">
        <div class="sourcing-stat-icon">
            <?= icon('bookmark', '', 20) ?>
        </div>
        <div>
            <div class="sourcing-stat-num"><?= count($talentPoolCandidates) ?></div>
            <div class="sourcing-stat-label">Kho hồ sơ hẹn phỏng vấn dịp sau</div>
        </div>
    </a>
</div>

<!-- ĐIỀU HƯỚNG PHÂN ĐOẠN HIỆN ĐẠI (SEGMENTED SWITCHER) - KHÔNG ĐÁNH SỐ 1 2 -->
<div class="sourcing-nav-wrapper">
    <div class="sourcing-segmented-control">
        <a href="?tab=ai_recommendations<?= $selectedJobId > 0 ? '&job_id=' . $selectedJobId : '' ?>"
           class="sourcing-pill <?= $activeTab === 'ai_recommendations' ? 'active' : '' ?>">
            <?= icon('sparkle', '', 15) ?>
            <span class="pill-text">Người Lao Động AI Đề Xuất</span>
            <span class="pill-counter"><?= count($aiCandidates) ?></span>
        </a>
        <a href="?tab=talent_pool<?= $selectedJobId > 0 ? '&job_id=' . $selectedJobId : '' ?>"
           class="sourcing-pill <?= $activeTab === 'talent_pool' ? 'active' : '' ?>">
            <?= icon('bookmark', '', 15) ?>
            <span class="pill-text">Kho Hồ Sơ Hẹn Dịp Sau</span>
            <span class="pill-counter"><?= count($talentPoolCandidates) ?></span>
        </a>
    </div>
    <div style="font-size: 0.82rem; color: #64748b;">
        <?= $activeTab === 'ai_recommendations' ? 'Tự động quét & đề xuất dựa trên mô tả công việc' : 'Lưu trữ tự động từ các buổi phỏng vấn trước' ?>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MỤC 1 (HIỂN THỊ ĐẦU TIÊN): TOÀN BỘ NLĐ ĐƯỢC AI ĐỀ XUẤT CHO DOANH NGHIỆP   -->
<!-- ========================================================================= -->
<?php if ($activeTab === 'ai_recommendations'): ?>
    <!-- BỘ LỌC CHỌN CÔNG VIỆC & THANH CÔNG CỤ HẠN MỨC % TƯƠNG THÍCH DO NTD TỰ CHỈNH -->
    <div class="threshold-box" style="margin-bottom: 1.5rem;">
        <form method="GET" action="<?= url('/employer/candidates/recommended') ?>" id="recommendFilterForm">
            <input type="hidden" name="tab" value="ai_recommendations">
            <input type="hidden" name="threshold" id="formThresholdInput" value="<?= $threshold ?>">

            <div style="display: flex; gap: 1.25rem; align-items: center; flex-wrap: wrap; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 0.85rem; border-bottom: 1px solid #f1f5f9;">
                <div style="flex: 1; min-width: 320px; display: flex; align-items: flex-start; gap: 0.75rem;">
                    <span style="color: #0d9488; display: flex; align-items: center; margin-top: 1.5rem;">
                        <?= icon('briefcase', '', 22) ?>
                    </span>
                    <div style="flex: 1;">
                        <label style="font-size: 0.78rem; font-weight: 700; color: #475569; display: block; margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.3px;">
                            Vị trí tuyển dụng cần ghép nối người lao động (Có tìm kiếm nhanh):
                        </label>
                        <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem;">
                            <div style="position: relative; flex: 1;">
                                <input type="text" id="jobFilterSearchInput" placeholder="🔍 Gõ để tìm kiếm nhanh vị trí tuyển dụng..."
                                       class="form-control" style="padding: 0.45rem 0.65rem 0.45rem 2.1rem; font-size: 0.85rem; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; background: #ffffff;">
                                <span style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                                    <?= icon('search', '', 14) ?>
                                </span>
                            </div>
                        </div>
                        <select name="job_id" id="jobFilterSelectDropdown" class="form-control" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.9rem; font-weight: 600; color: #0f172a; background: #f8fafc;">
                            <?php foreach ($activeJobs as $j): ?>
                                <option value="<?= $j['job_post_id'] ?>" data-search="<?= htmlspecialchars(mb_strtolower($j['job_title'] . ' ' . ($j['workplace_address'] ?? ''))) ?>" <?= $selectedJobId === intval($j['job_post_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($j['job_title']) ?> (Tuyển <?= $j['number_of_positions'] ?> người)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span class="badge" style="background: #f0fdfa; color: #0d9488; border: 1px solid #ccfbf1; font-weight: 600; font-size: 0.8rem; padding: 0.45rem 0.8rem; display: inline-flex; align-items: center; gap: 6px; border-radius: 8px;">
                        <?= icon('shield_check', '', 14) ?> Tự động xếp hạng theo độ tương thích
                    </span>
                </div>
            </div>

            <!-- THANH ĐIỀU CHỈNH HẠN MỨC % (MATCH THRESHOLD) DO NTD TỰ ĐẶT -->
            <div class="threshold-header">
                <div class="threshold-title">
                    <span style="color: #0d9488;"><?= icon('sparkle', '', 17) ?></span>
                    <span>Hạn Mức % Tương Thích Hiển Thị (NTD Tự Điều Chỉnh):</span>
                    <span class="threshold-badge" id="thresholdValueBadge"><?= $threshold ?>%</span>
                </div>
                <div class="threshold-summary-pills">
                    <span class="pill-qualified">
                        <?= icon('check', '', 12) ?> Đạt chuẩn: <strong id="countQualifiedBadge" style="margin-left: 2px;"><?= count($qualifiedCandidates) ?></strong> ứng viên
                    </span>
                    <span class="pill-unqualified">
                        <?= icon('alert_circle', '', 12) ?> Chưa đạt: <strong id="countUnqualifiedBadge" style="margin-left: 2px;"><?= count($unqualifiedCandidates) ?></strong> ứng viên
                    </span>
                </div>
            </div>

            <div class="threshold-controls-row">
                <div class="threshold-slider-wrapper">
                    <span style="font-size: 0.76rem; font-weight: 700; color: #64748b;">30%</span>
                    <input type="range" min="30" max="95" step="5" value="<?= $threshold ?>" class="threshold-slider" id="thresholdSlider" title="Kéo để thay đổi hạn mức % tương thích">
                    <span style="font-size: 0.76rem; font-weight: 700; color: #64748b;">95%</span>
                </div>

                <div class="threshold-presets">
                    <span style="font-size: 0.78rem; color: #64748b; font-weight: 600; margin-right: 2px;">Mốc nhanh:</span>
                    <button type="button" class="btn-threshold-preset <?= $threshold === 60 ? 'active' : '' ?>" data-preset="60">60%</button>
                    <button type="button" class="btn-threshold-preset <?= $threshold === 70 ? 'active' : '' ?>" data-preset="70">70%</button>
                    <button type="button" class="btn-threshold-preset <?= $threshold === 80 ? 'active' : '' ?>" data-preset="80">80%</button>
                    <button type="button" class="btn-threshold-preset <?= $threshold === 90 ? 'active' : '' ?>" data-preset="90">90%</button>
                </div>
            </div>
            <p style="font-size: 0.78rem; color: #64748b; margin: 0.5rem 0 0 0;">
                <em>Hệ thống sẽ lọc những ứng viên có độ tương thích từ <strong><span class="threshold-display-val"><?= $threshold ?>%</span></strong> trở lên hiển thị ở danh sách chính Đạt chuẩn. Các ứng viên dưới hạn mức sẽ tự động đưa vào mục Chưa đạt bên dưới.</em>
            </p>
        </form>
    </div>

    <!-- PHẦN 1: DANH SÁCH ỨNG VIÊN ĐẠT CHUẨN (>= % HẠN MỨC) - HIỂN THỊ LÊN TRÊN -->
    <div style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; background: #ecfdf5; color: #059669; border-radius: 50%; font-size: 0.85rem; border: 1px solid #a7f3d0;">✓</span>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Ứng Viên Đạt Chuẩn Đề Xuất (Tương Thích ≥ <span class="threshold-display-val"><?= $threshold ?>%</span>)
                </h3>
                <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 0.8rem; padding: 0.2rem 0.6rem; border-radius: 9999px;">
                    <span id="badgeCountQualified"><?= count($qualifiedCandidates) ?></span> hồ sơ
                </span>
            </div>
            <span style="font-size: 0.8rem; color: #059669; font-weight: 600;">
                Đáp ứng tiêu chuẩn phỏng vấn & mời làm việc ngay
            </span>
        </div>

        <div id="qualifiedCandidatesList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.25rem;">
            <?php foreach ($qualifiedCandidates as $rec): ?>
                <div class="sourcing-card candidate-eval-card qualified-card <?= ($rec['ai_match_score'] >= 85) ? 'featured' : '' ?>" data-score="<?= $rec['ai_match_score'] ?>" data-seeker-id="<?= $rec['seeker_id'] ?>">
                    <div>
                        <!-- Header Card -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.85rem; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.75rem; align-items: center;">
                                <img src="<?= htmlspecialchars(getAvatarUrl($rec['avatar_url'] ?? '', 'JOB_SEEKER', $rec['gender'] ?? '')) ?>" alt="avatar" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $rec['gender'] ?? '') ?>';">
                                <div style="min-width: 0;">
                                    <a href="<?= url('/employer/candidates/' . $rec['seeker_id'] . ($selectedJobId > 0 ? '?job_id=' . $selectedJobId : '')) ?>" style="font-weight: 700; font-size: 1.02rem; color: #0f172a; text-decoration: none;" title="Xem hồ sơ chi tiết (Hiện thông tin %)">
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;"><?= htmlspecialchars($rec['full_name']) ?></span>
                                    </a>
                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                                        <?= htmlspecialchars($rec['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($rec['date_of_birth']) ? (date('Y') - date('Y', strtotime($rec['date_of_birth']))) . ' tuổi' : '' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                    </div>
                                </div>
                            </div>
                            <span class="candidate-status-indicator badge btn-candidate-score-info" 
                                  data-score="<?= $rec['ai_match_score'] ?>"
                                  data-level="<?= htmlspecialchars($rec['ai_match_label'] ?? 'Đạt tiêu chuẩn') ?>"
                                  data-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                  data-job="<?= htmlspecialchars($activeJobs[0]['job_title'] ?? '') ?>"
                                  data-matched="<?= htmlspecialchars(json_encode($rec['ai_matched_skills'] ?? [])) ?>"
                                  data-missing="<?= htmlspecialchars(json_encode($rec['ai_missing_skills'] ?? [])) ?>"
                                  data-breakdown="<?= htmlspecialchars(json_encode($rec['ai_breakdown'] ?? [])) ?>"
                                  data-reasons="<?= htmlspecialchars(json_encode($rec['ai_reasons'] ?? [])) ?>"
                                  data-exp="<?= intval($rec['years_of_experience'] ?? 0) ?>"
                                  data-edu="<?= htmlspecialchars($rec['education_level'] ?? 'Phổ thông') ?>"
                                  data-address="<?= htmlspecialchars($rec['address'] ?? 'Bình Dương') ?>"
                                  onclick="showScoreModalFromCard(this)"
                                  style="cursor: pointer; background: <?= $rec['ai_match_score'] >= 85 ? '#ecfdf5' : '#f0fdfa' ?>; color: <?= $rec['ai_match_score'] >= 85 ? '#065f46' : '#0d9488' ?>; border: 1.5px solid <?= $rec['ai_match_score'] >= 85 ? '#a7f3d0' : '#ccfbf1' ?>; font-size: 0.76rem; font-weight: 800; padding: 0.3rem 0.65rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; box-shadow: 0 1px 3px rgba(0,0,0,0.05);"
                                  title="Click vào % để xem giải thích: Tại sao có giá trị này?">
                                <?= icon('sparkle', '', 12) ?> <?= $rec['ai_match_score'] ?>% tương thích
                            </span>
                        </div>

                        <!-- Điểm uy tín & Khu vực -->
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.85rem;">
                            <span class="badge" style="background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px;">
                                <?= icon('shield', '', 12) ?> Điểm uy tín: <strong><?= $rec['trust_score'] ?? 80 ?>/100</strong>
                            </span>
                            <?php if (!empty($rec['address'])): ?>
                                <span class="badge" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px;">
                                    <?= icon('map_pin', '', 12) ?> <?= htmlspecialchars($rec['address']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- KHỐI 3 PHẦN: ĐẠT ĐƯỢC - KHÔNG ĐẠT ĐƯỢC -> KẾT QUẢ -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.65rem 0.75rem; margin-bottom: 0.85rem; font-size: 0.76rem; display: flex; flex-direction: column; gap: 0.45rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.35rem; color: #166534;">
                                <span style="font-weight: 800; color: #16a34a; flex-shrink: 0;">✓ Đạt được:</span>
                                <span style="font-weight: 600;">
                                    <?= !empty($rec['ai_matched_skills']) ? implode(', ', array_slice($rec['ai_matched_skills'], 0, 3)) : 'Đạt yêu cầu chung' ?>
                                    · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                </span>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 0.35rem; color: <?= !empty($rec['ai_missing_skills']) ? '#92400e' : '#64748b' ?>;">
                                <span style="font-weight: 800; color: <?= !empty($rec['ai_missing_skills']) ? '#d97706' : '#94a3b8' ?>; flex-shrink: 0;">✕ Chưa đạt:</span>
                                <span>
                                    <?= !empty($rec['ai_missing_skills']) ? implode(', ', array_slice($rec['ai_missing_skills'], 0, 2)) : 'Không có thiếu sót lớn' ?>
                                </span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 0.35rem; margin-top: 0.15rem;">
                                <span style="color: #0f766e; font-weight: 700;">➔ Kết quả: <strong><?= $rec['ai_match_score'] ?>%</strong> (<?= htmlspecialchars($rec['ai_match_label'] ?? 'Đạt chuẩn') ?>)</span>
                                <span onclick="showScoreModalFromCard(this.closest('.candidate-eval-card').querySelector('.btn-candidate-score-info'))" style="color: #0d9488; font-size: 0.72rem; font-weight: 700; cursor: pointer; text-decoration: underline;">
                                    Xem lý do %
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Card -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 0.85rem; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                        <a href="<?= url('/employer/candidates/' . $rec['seeker_id'] . ($selectedJobId > 0 ? '?job_id=' . $selectedJobId : '')) ?>" style="font-size: 0.82rem; color: #0d9488; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Xem hồ sơ hiển thị đầy đủ thông tin %">
                            <?= icon('user', '', 13) ?> Xem Hồ Sơ (%)
                        </a>
                            <?= icon('user', '', 13) ?> Hồ sơ
                        </a>
                        <div style="display: flex; gap: 0.4rem;">
                            <a href="<?= url('/employer/messages?partner_id=' . ($rec['account_id'] ?? 0) . '&seeker_id=' . ($rec['seeker_id'] ?? 0) . '&job_id=' . $selectedJobId . '&open_meeting_modal=1') ?>" class="btn btn-primary btn-sm" style="font-size: 0.78rem; font-weight: 700; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 4px; background: #0d9488; border-color: #0d9488;">
                                <?= icon('calendar', '', 13) ?> Hẹn Lịch PV
                            </a>
                            <button type="button" class="btn btn-outline btn-sm btn-open-invite-modal"
                                    data-seeker-id="<?= $rec['seeker_id'] ?>"
                                    data-job-id="<?= $selectedJobId ?>"
                                    data-seeker-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                    data-job-title="<?= htmlspecialchars($activeJobs[0]['job_title'] ?? '') ?>"
                                    style="font-size: 0.78rem; font-weight: 700; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 4px; color: #0284c7; border-color: #bae6fd;">
                                <?= icon('mail', '', 13) ?> Mời Làm Việc
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="emptyQualifiedNotice" class="card" style="display: <?= empty($qualifiedCandidates) ? 'block' : 'none' ?>; text-align: center; padding: 3rem 1.5rem; color: #64748b; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 12px; margin-top: 1rem;">
            <div style="color: #94a3b8; margin-bottom: 0.5rem;">
                <?= icon('search', '', 36) ?>
            </div>
            <h4 style="font-size: 1.05rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Không có ứng viên đạt hạn mức <span class="threshold-display-val"><?= $threshold ?>%</span></h4>
            <p style="font-size: 0.85rem; max-width: 480px; margin: 0 auto; color: #64748b;">
                Bạn có thể hạ hạn mức % bằng thanh trượt ở trên hoặc kiểm tra danh sách các ứng viên ở phần <strong>Chưa đạt hạn mức</strong> bên dưới.
            </p>
        </div>
    </div>

    <!-- PHẦN 2: DANH SÁCH ỨNG VIÊN CHƯA ĐẠT HẠN MỨC (< % HẠN MỨC) - NẰM Ở PHẦN CHƯA ĐẠT -->
    <div class="unqualified-section-card" id="unqualifiedSectionWrapper">
        <div class="unqualified-section-header" id="toggleUnqualifiedHeader" onclick="toggleUnqualifiedSection()">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <span style="color: #d97706; display: flex; align-items: center;"><?= icon('alert_circle', '', 18) ?></span>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: #92400e; margin: 0;">
                        Ứng Viên Chưa Đạt Hạn Mức (Tương Thích < <span class="threshold-display-val"><?= $threshold ?>%</span>)
                    </h3>
                    <span class="unqualified-badge-warning" style="margin-left: 4px;">
                        <span id="badgeCountUnqualified"><?= count($unqualifiedCandidates) ?></span> hồ sơ
                    </span>
                </div>
                <p style="font-size: 0.84rem; color: #78350f; margin: 0;">
                    Các hồ sơ dưới hạn mức bạn yêu cầu. Bạn có thể mở rộng tiêu chí tuyển dụng hoặc cân nhắc đào tạo thêm.
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-outline btn-sm" id="btnToggleUnqualified" style="background: #d97706; border-color: #d97706; color: #ffffff; font-weight: 700; font-size: 0.78rem; padding: 0.3rem 0.65rem;">
                    <span id="toggleUnqualifiedText">Mở Rộng Danh Sách (<?= count($unqualifiedCandidates) ?>)</span>
                    <span id="toggleUnqualifiedIcon" style="margin-left: 4px;">▼</span>
                </button>
            </div>
        </div>

        <div id="unqualifiedCandidatesList" style="display: none; margin-top: 1.25rem; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.25rem;">
            <?php foreach ($unqualifiedCandidates as $rec): ?>
                <div class="sourcing-card candidate-eval-card unqualified-card" data-score="<?= $rec['ai_match_score'] ?>" data-seeker-id="<?= $rec['seeker_id'] ?>">
                    <div>
                        <!-- Header Card -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.85rem; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.75rem; align-items: center;">
                                <img src="<?= htmlspecialchars(getAvatarUrl($rec['avatar_url'] ?? '', 'JOB_SEEKER', $rec['gender'] ?? '')) ?>" alt="avatar" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $rec['gender'] ?? '') ?>';">
                                <div style="min-width: 0;">
                                    <a href="<?= url('/employer/candidates/' . $rec['seeker_id'] . ($selectedJobId > 0 ? '?job_id=' . $selectedJobId : '')) ?>" style="font-weight: 700; font-size: 1.02rem; color: #0f172a; text-decoration: none;" title="Xem hồ sơ chi tiết (Hiện thông tin %)">
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;"><?= htmlspecialchars($rec['full_name']) ?></span>
                                    </a>
                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                                        <?= htmlspecialchars($rec['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($rec['date_of_birth']) ? (date('Y') - date('Y', strtotime($rec['date_of_birth']))) . ' tuổi' : '' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                    </div>
                                </div>
                            </div>
                            <span class="candidate-status-indicator badge unqualified-badge-warning btn-candidate-score-info" 
                                  data-score="<?= $rec['ai_match_score'] ?>"
                                  data-level="Dưới hạn mức"
                                  data-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                  data-job="<?= htmlspecialchars($activeJobs[0]['job_title'] ?? '') ?>"
                                  data-matched="<?= htmlspecialchars(json_encode($rec['ai_matched_skills'] ?? [])) ?>"
                                  data-missing="<?= htmlspecialchars(json_encode($rec['ai_missing_skills'] ?? [])) ?>"
                                  data-breakdown="<?= htmlspecialchars(json_encode($rec['ai_breakdown'] ?? [])) ?>"
                                  data-reasons="<?= htmlspecialchars(json_encode($rec['ai_reasons'] ?? [])) ?>"
                                  data-exp="<?= intval($rec['years_of_experience'] ?? 0) ?>"
                                  data-edu="<?= htmlspecialchars($rec['education_level'] ?? 'Phổ thông') ?>"
                                  data-address="<?= htmlspecialchars($rec['address'] ?? 'Bình Dương') ?>"
                                  onclick="showScoreModalFromCard(this)"
                                  style="cursor: pointer; font-size: 0.74rem; font-weight: 800; padding: 0.3rem 0.6rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 3px; white-space: nowrap;"
                                  title="Click vào % để xem giải thích: Tại sao có giá trị này?">
                                <?= icon('alert_circle', '', 12) ?> <?= $rec['ai_match_score'] ?>% (Dưới hạn mức)
                            </span>
                        </div>

                        <!-- Điểm uy tín & Khu vực -->
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.85rem;">
                            <span class="badge" style="background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px;">
                                <?= icon('shield', '', 12) ?> Điểm uy tín: <strong><?= $rec['trust_score'] ?? 80 ?>/100</strong>
                            </span>
                            <?php if (!empty($rec['address'])): ?>
                                <span class="badge" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px;">
                                    <?= icon('map_pin', '', 12) ?> <?= htmlspecialchars($rec['address']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- KHỐI 3 PHẦN: ĐẠT ĐƯỢC - KHÔNG ĐẠT ĐƯỢC -> KẾT QUẢ -->
                        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.65rem 0.75rem; margin-bottom: 0.85rem; font-size: 0.76rem; display: flex; flex-direction: column; gap: 0.45rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.35rem; color: #166534;">
                                <span style="font-weight: 800; color: #16a34a; flex-shrink: 0;">✓ Đạt được:</span>
                                <span style="font-weight: 600;">
                                    <?= !empty($rec['ai_matched_skills']) ? implode(', ', array_slice($rec['ai_matched_skills'], 0, 2)) : 'Tiêu chí chung' ?>
                                    · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                </span>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 0.35rem; color: #92400e;">
                                <span style="font-weight: 800; color: #d97706; flex-shrink: 0;">✕ Chưa đạt:</span>
                                <span>
                                    <?= !empty($rec['ai_missing_skills']) ? implode(', ', array_slice($rec['ai_missing_skills'], 0, 2)) : (!empty($rec['ai_reasons']) ? htmlspecialchars($rec['ai_reasons'][count($rec['ai_reasons']) - 1]) : 'Chưa tối ưu kỹ năng so với yêu cầu tin đăng') ?>
                                </span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px dashed #fcd34d; padding-top: 0.35rem; margin-top: 0.15rem;">
                                <span style="color: #b45309; font-weight: 700;">➔ Kết quả: <strong><?= $rec['ai_match_score'] ?>%</strong> (Dưới hạn mức)</span>
                                <span onclick="showScoreModalFromCard(this.closest('.candidate-eval-card').querySelector('.btn-candidate-score-info'))" style="color: #d97706; font-size: 0.72rem; font-weight: 700; cursor: pointer; text-decoration: underline;">
                                    Xem lý do %
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Card -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 0.85rem; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                        <a href="<?= url('/employer/candidates/' . $rec['seeker_id'] . ($selectedJobId > 0 ? '?job_id=' . $selectedJobId : '')) ?>" style="font-size: 0.82rem; color: #64748b; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Xem hồ sơ hiển thị đầy đủ thông tin %">
                            <?= icon('user', '', 13) ?> Xem Hồ sơ (%)
                        </a>
                            <?= icon('user', '', 13) ?> Xem Hồ sơ
                        </a>
                        <div style="display: flex; gap: 0.4rem;">
                            <button type="button" class="btn btn-outline btn-sm btn-open-invite-modal"
                                    data-seeker-id="<?= $rec['seeker_id'] ?>"
                                    data-job-id="<?= $selectedJobId ?>"
                                    data-seeker-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                    data-job-title="<?= htmlspecialchars($activeJobs[0]['job_title'] ?? '') ?>"
                                    style="font-size: 0.78rem; font-weight: 700; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 4px; color: #ffffff; background: #d97706; border-color: #d97706;">
                                <?= icon('mail', '', 13) ?> Vẫn Mời Phỏng Vấn
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="emptyUnqualifiedNotice" class="card" style="display: <?= empty($unqualifiedCandidates) ? 'block' : 'none' ?>; text-align: center; padding: 1.5rem; color: #64748b; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 8px; margin-top: 1rem;">
            <p style="font-size: 0.85rem; margin: 0; color: #059669; font-weight: 600;">
                <?= icon('check', '', 14) ?> Tuyệt vời! Toàn bộ ứng viên hiện tại đều đạt chuẩn theo hạn mức bạn chọn.
            </p>
        </div>
    </div>

<!-- ========================================================================= -->
<!-- MỤC 2: KHO HỒ SƠ HẸN PHỎNG VẤN DỊP SAU (TALENT POOL)                      -->
<!-- ========================================================================= -->
<?php else: ?>
    <!-- BANNER CƠ CHẾ TÁI TUYỂN DỤNG (ĐƠN SẮC, THANH LỊCH) -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #0d9488; border-radius: 10px; padding: 1.1rem 1.35rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 1rem;">
        <span style="color: #0d9488; margin-top: 2px;">
            <?= icon('info', '', 20) ?>
        </span>
        <div>
            <div style="font-size: 0.9rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">
                Cơ chế tái tuyển dụng & tối ưu chi phí
            </div>
            <div style="font-size: 0.86rem; color: #475569; line-height: 1.55;">
                Đây là những người lao động từng tham gia ứng tuyển hoặc phỏng vấn tại doanh nghiệp bạn và được lưu trữ lại hẹn cơ hội sau. Hệ thống tự động đối chiếu năng lực người lao động với <strong><?= count($activeJobs) ?> vị trí đang mở tuyển</strong> hiện tại. Nếu người lao động <strong>Gần đạt yêu cầu</strong>, bạn có thể gửi lời <strong>"Mời Phỏng Vấn Lại"</strong> ngay mà không mất thời gian tìm kiếm từ đầu.
            </div>
        </div>
    </div>

    <?php if (empty($talentPoolCandidates)): ?>
        <div class="card" style="text-align: center; padding: 3.5rem 1.5rem; color: #64748b; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
            <div style="color: #94a3b8; margin-bottom: 0.75rem;">
                <?= icon('inbox', '', 48) ?>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Kho hồ sơ hẹn dịp sau hiện đang trống</h3>
            <p style="font-size: 0.88rem; max-width: 540px; margin: 0 auto 1.25rem auto; line-height: 1.5; color: #64748b;">
                Khi bạn từ chối hồ sơ người lao động sau phỏng vấn nhưng hẹn cơ hội hợp tác sau, hệ thống JobLink sẽ tự động lưu trữ thông tin người lao động vào đây để bạn dễ dàng mời họ phỏng vấn lại khi có dự án mới phù hợp.
            </p>
            <a href="?tab=ai_recommendations" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                <?= icon('sparkle', '', 14) ?> Xem danh sách NLĐ được AI đề xuất
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.25rem;">
            <?php foreach ($talentPoolCandidates as $cand): ?>
                <?php 
                    $hasBestMatch = !empty($cand['best_match']);
                    $isNearlyQualified = !empty($cand['is_nearly_qualified']);
                    $bestJob = $cand['best_match'] ?? null;
                ?>
                <div class="sourcing-card <?= $isNearlyQualified ? 'featured' : '' ?>">
                    <?php if ($isNearlyQualified): ?>
                        <div style="position: absolute; top: -11px; right: 15px; background: #059669; color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.65rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 5px rgba(5,150,105,0.25);">
                            <?= icon('target', '', 11) ?> Gần Đạt Yêu Cầu (<?= $bestJob['score'] ?>%)
                        </div>
                    <?php endif; ?>

                    <div>
                        <!-- Header thông tin người lao động -->
                        <div style="display: flex; gap: 0.85rem; align-items: flex-start; margin-bottom: 0.85rem;">
                            <img src="<?= htmlspecialchars(getAvatarUrl($cand['seeker_avatar'] ?? '', 'JOB_SEEKER', $cand['gender'] ?? '')) ?>" alt="avatar" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid <?= $isNearlyQualified ? '#059669' : '#e2e8f0' ?>; flex-shrink: 0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $cand['gender'] ?? '') ?>';">
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <a href="<?= url('/employer/candidates/' . $cand['seeker_id']) ?>" style="font-weight: 700; font-size: 1.02rem; color: #0f172a; text-decoration: none;" title="Bấm xem hồ sơ chi tiết">
                                        <span><?= htmlspecialchars($cand['seeker_name']) ?></span>
                                    </a>
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                                    <?= htmlspecialchars($cand['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($cand['date_of_birth']) ? (date('Y') - date('Y', strtotime($cand['date_of_birth']))) . ' tuổi' : '' ?> · <?= intval($cand['experience_years'] ?? 0) > 0 ? ($cand['experience_years'] . ' năm KN') : 'Chưa có KN' ?>
                                </div>
                                <div style="font-size: 0.78rem; color: #475569; margin-top: 2px; display: flex; align-items: center; gap: 4px;">
                                    <?= icon('phone', '', 12) ?> <?= htmlspecialchars($cand['phone_number'] ?? 'Chưa cập nhật') ?>
                                </div>
                            </div>
                        </div>

                        <!-- Lịch sử từng ứng tuyển -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.55rem 0.8rem; margin-bottom: 0.85rem; font-size: 0.8rem; color: #475569;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="display: inline-flex; align-items: center; gap: 4px;">
                                    <?= icon('file_text', '', 13) ?> Từng ứng tuyển: <strong><?= htmlspecialchars($cand['past_job_title']) ?></strong>
                                </span>
                                <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.7rem; border: 1px solid #e2e8f0;">Hẹn dịp sau</span>
                            </div>
                            <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 0.25rem; display: flex; align-items: center; gap: 4px;">
                                <?= icon('calendar', '', 11) ?> Lưu hồ sơ ngày: <?= date('d/m/Y', strtotime($cand['applied_at'])) ?>
                            </div>
                        </div>

                        <!-- KHU VỰC ĐỐI CHIẾU VỚI CÔNG VIỆC ĐANG MỞ (AI MATCH) -->
                        <?php if ($hasBestMatch): ?>
                            <div style="background: <?= $isNearlyQualified ? '#f0fdfa' : '#f8fafc' ?>; border: 1px solid <?= $isNearlyQualified ? '#ccfbf1' : '#e2e8f0' ?>; border-radius: 8px; padding: 0.75rem 0.85rem; margin-bottom: 0.85rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                                    <strong style="color: <?= $isNearlyQualified ? '#0f766e' : '#334155' ?>; font-size: 0.82rem; display: flex; align-items: center; gap: 4px;">
                                        <?= icon('briefcase', '', 13) ?> Vị trí đang mở tương thích cao nhất:
                                    </strong>
                                    <span class="badge" style="background: #f0fdfa; color: #0d9488; border: 1px solid #99f6e4; font-size: 0.72rem; padding: 0.15rem 0.5rem; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;">
                                        <?= icon('check', '', 11) ?> <?= $bestJob['score'] ?>%
                                    </span>
                                </div>
                                <div style="font-size: 0.88rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">
                                    <?= htmlspecialchars($bestJob['job_title']) ?>
                                </div>
                                <?php if (!empty($bestJob['matched_skills'])): ?>
                                    <div style="font-size: 0.75rem; color: #047857; display: flex; align-items: center; gap: 4px;">
                                        <?= icon('check_circle', '', 12) ?> Kỹ năng đáp ứng: <?= htmlspecialchars(implode(', ', array_slice($bestJob['matched_skills'], 0, 3))) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Kỹ năng của người lao động -->
                        <?php if (!empty($cand['skills'])): ?>
                            <div style="margin-bottom: 0.85rem;">
                                <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; margin-bottom: 0.25rem;">Kỹ năng & Chuyên môn:</div>
                                <div style="display: flex; gap: 0.3rem; flex-wrap: wrap;">
                                    <?php 
                                        $skills = array_filter(array_map('trim', explode(',', $cand['skills'])));
                                        foreach (array_slice($skills, 0, 4) as $sk):
                                    ?>
                                        <span style="background: #f1f5f9; color: #334155; font-size: 0.72rem; padding: 0.15rem 0.45rem; border-radius: 4px;">
                                            <?= htmlspecialchars($sk) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- FOOTER HÀNH ĐỘNG -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 0.85rem; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                        <a href="<?= url('/employer/messages?partner_id=' . ($cand['seeker_account_id'] ?? 0)) ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem; padding: 0.35rem 0.65rem; color: #0369a1; border-color: #bae6fd; display: inline-flex; align-items: center; gap: 4px;">
                            <?= icon('message_circle', '', 13) ?> Nhắn tin
                        </a>

                        <div style="display: flex; gap: 0.4rem;">
                            <a href="<?= url('/employer/candidates/' . $cand['seeker_id']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem; padding: 0.35rem 0.6rem; color: #64748b; display: inline-flex; align-items: center; gap: 4px;">
                                <?= icon('user', '', 13) ?> Hồ sơ
                            </a>
                            <!-- NÚT MỜI PHỎNG VẤN LẠI -->
                            <a href="<?= url('/employer/messages?partner_id=' . ($cand['account_id'] ?? 0) . '&seeker_id=' . ($cand['seeker_id'] ?? 0) . '&job_id=' . ($bestJob['job_post_id'] ?? ($activeJobs[0]['job_post_id'] ?? 0)) . '&open_meeting_modal=1') ?>"
                               class="btn btn-success btn-sm"
                               style="font-size: 0.8rem; font-weight: 700; padding: 0.35rem 0.75rem; background: #059669; border-color: #059669; display: inline-flex; align-items: center; gap: 4px;">
                                <?= icon('calendar', '', 13) ?> Hẹn Lịch Phỏng Vấn
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- MODAL MỜI PHỎNG VẤN LẠI NGƯỜI LAO ĐỘNG TRONG TALENT POOL                  -->
<!-- ========================================================================= -->
<div id="reInviteMeetingModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 10001; justify-content: center; align-items: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.2rem; color: #047857; display: flex; align-items: center; gap: 0.5rem; font-weight: 800;">
                <?= icon('calendar', '', 18) ?> Mời Phỏng Vấn Lại Cơ Hội Mới (Kho Hồ Sơ Tiềm Năng)
            </h3>
            <button type="button" id="closeReInviteModalBtn" style="background: none; border: none; font-size: 1.35rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.88rem; color: #065f46;">
            Người lao động: <strong id="reInviteSeekerName" style="color: #047857;"></strong><br>
            <span style="font-size: 0.8rem; color: #059669;">Từng lưu hồ sơ từ vị trí: <span id="reInvitePastJob"></span>. Hệ thống sẽ gửi thông báo và email mời người lao động tham gia phỏng vấn cơ hội mới.</span>
        </div>

        <form action="<?= url('/employer/candidates/re-invite-meeting') ?>" method="POST" id="reInviteMeetingForm">
            <input type="hidden" name="seeker_id" id="reInviteSeekerId">

            <div style="margin-bottom: 0.85rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 4px; margin-bottom: 0.25rem;">
                    <?= icon('briefcase', '', 14) ?> Chọn vị trí tuyển dụng mới mời người lao động tham gia *
                </label>
                <select name="job_post_id" id="reInviteJobSelect" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                    <?php foreach ($activeJobs as $j): ?>
                        <option value="<?= $j['job_post_id'] ?>" data-workplace="<?= htmlspecialchars($j['workplace_address'] ?? '') ?>">
                            <?= htmlspecialchars($j['job_title']) ?> (Tuyển: <?= $j['number_of_positions'] ?> người)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 4px; margin-bottom: 0.25rem;">
                        <?= icon('calendar', '', 14) ?> Ngày phỏng vấn *
                    </label>
                    <input type="date" name="meeting_date" id="reInviteDate" class="form-control" required value="<?= date('Y-m-d', strtotime('+2 days')) ?>" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 4px; margin-bottom: 0.25rem;">
                        <?= icon('clock', '', 14) ?> Giờ bắt đầu *
                    </label>
                    <input type="time" name="start_time" class="form-control" required value="09:00" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
            </div>

            <div style="margin-bottom: 0.85rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 4px; margin-bottom: 0.25rem;">
                    <?= icon('calendar', '', 14) ?> Khoảng ngày dự kiến phỏng vấn (để người lao động chuẩn bị thời gian & xin dời lịch nếu bận):
                </label>
                <input type="text" name="interview_range" class="form-control" value="<?= date('d/m/Y', strtotime('+1 day')) ?> - <?= date('d/m/Y', strtotime('+5 days')) ?>" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Hình thức phỏng vấn *</label>
                    <select name="meeting_format" class="form-control" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                        <option value="offline">Trực tiếp tại cơ sở / văn phòng</option>
                        <option value="online">Trực tuyến (Google Meet / Zoom)</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 4px; margin-bottom: 0.25rem;">
                        <?= icon('map_pin', '', 14) ?> Địa điểm / Link phòng họp *
                    </label>
                    <input type="text" name="location_or_meeting_link" id="reInviteWorkplace" class="form-control" required value="<?= htmlspecialchars($activeJobs[0]['workplace_address'] ?? 'Văn phòng Công ty') ?>" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
            </div>

            <div style="margin-bottom: 0.85rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 4px; margin-bottom: 0.25rem;">
                    <?= icon('file_text', '', 14) ?> Cẩm nang hướng dẫn người lao động chuẩn bị trước buổi phỏng vấn:
                </label>
                <textarea name="prep_guide" rows="2" class="form-control" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">Tìm hiểu thông tin vị trí mới. Chuẩn bị hồ sơ năng lực cập nhật và mang theo CCCD/bản in thông tin khi đến tham gia phỏng vấn.</textarea>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 4px; margin-bottom: 0.25rem;">
                    <?= icon('message_square', '', 14) ?> Lời nhắn mời tái phỏng vấn từ NTD:
                </label>
                <textarea name="meeting_note" rows="2" class="form-control" placeholder="Chào bạn, trước đây hồ sơ của bạn đã được lưu giữ tại công ty chúng tôi..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">Chào bạn, căn cứ vào hồ sơ năng lực của bạn đã lưu tại công ty chúng tôi, hiện tại công ty có vị trí mới rất phù hợp với kỹ năng của bạn và trân trọng mời bạn tham gia buổi phỏng vấn này!</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                <button type="button" id="cancelReInviteModalBtn" class="btn btn-outline btn-sm">Hủy bỏ</button>
                <button type="submit" class="btn btn-success btn-sm" style="font-weight: 700; background: #059669; border-color: #059669; padding: 0.5rem 1.25rem; display: inline-flex; align-items: center; gap: 6px;">
                    <?= icon('mail_check', '', 15) ?> Xác Nhận Gửi Lời Mời Phỏng Vấn Lại
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL MỜI NHẬN VIỆC DÀNH CHO MỤC 1 (AI ĐỀ XUẤT)                           -->
<!-- ========================================================================= -->
<div id="inviteCandidateModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 10001; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 12px; max-width: 520px; width: 100%; padding: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 6px; font-weight: 700;">
                <?= icon('mail', '', 18) ?> Gửi Lời Mời Làm Việc
            </h3>
            <button type="button" id="closeInviteModalBtn" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= url('/employer/candidates/invite') ?>" method="POST">
            <input type="hidden" name="seeker_id" id="modalSeekerId">
            <input type="hidden" name="job_post_id" id="modalJobId">
            <div style="font-size: 0.88rem; color: #475569; margin-bottom: 1rem; background: #f8fafc; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                Bạn đang gửi lời mời đến người lao động <strong id="modalSeekerName" style="color: #0d9488;"></strong>.
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 4px; margin-bottom: 0.35rem;">
                    <?= icon('message_square', '', 13) ?> Lời nhắn từ Nhà tuyển dụng:
                </label>
                <textarea name="invitation_note" rows="3" class="form-control" placeholder="Chào bạn, công ty đã xem qua hồ sơ và rất ấn tượng..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">Chào bạn, doanh nghiệp chúng tôi rất ấn tượng với kỹ năng và hồ sơ năng lực của bạn. Trân trọng mời bạn tham gia ứng tuyển và phỏng vấn vị trí này!</textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                <button type="button" id="cancelInviteModalBtn" class="btn btn-outline btn-sm">Đóng</button>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <?= icon('mail_check', '', 15) ?> Gửi Lời Mời
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Chức năng Thu gọn / Mở rộng phần Ứng viên Chưa đạt hạn mức
function toggleUnqualifiedSection() {
    const list = document.getElementById('unqualifiedCandidatesList');
    const btnText = document.getElementById('toggleUnqualifiedText');
    const icon = document.getElementById('toggleUnqualifiedIcon');
    if (!list) return;

    const count = document.getElementById('badgeCountUnqualified') ? document.getElementById('badgeCountUnqualified').textContent : '0';

    if (list.style.display === 'none' || list.style.display === '') {
        list.style.display = 'grid';
        if (btnText) btnText.textContent = 'Thu Gọn Danh Sách (' + count + ')';
        if (icon) icon.textContent = '▲';
    } else {
        list.style.display = 'none';
        if (btnText) btnText.textContent = 'Mở Rộng Danh Sách (' + count + ')';
        if (icon) icon.textContent = '▼';
    }
}

// Hàm lọc và phân bổ ứng viên Realtime theo Hạn Mức %
function applyCandidateThreshold(threshold) {
    threshold = parseInt(threshold, 10);
    if (isNaN(threshold) || threshold < 30) threshold = 70;
    if (threshold > 100) threshold = 100;

    // Cập nhật text hiển thị
    document.querySelectorAll('.threshold-display-val').forEach(el => el.textContent = threshold + '%');
    const badge = document.getElementById('thresholdValueBadge');
    if (badge) badge.textContent = threshold + '%';
    const slider = document.getElementById('thresholdSlider');
    if (slider) slider.value = threshold;
    const formInput = document.getElementById('formThresholdInput');
    if (formInput) formInput.value = threshold;

    // Cập nhật active cho các nút Preset
    document.querySelectorAll('.btn-threshold-preset').forEach(btn => {
        const pVal = parseInt(btn.getAttribute('data-preset'), 10);
        if (pVal === threshold) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Lưu vào localStorage
    try {
        localStorage.setItem('joblink_employer_threshold', threshold);
    } catch(e) {}

    // Phân loại và chuyển DOM các thẻ ứng viên
    const allCards = document.querySelectorAll('.candidate-eval-card');
    const qualifiedList = document.getElementById('qualifiedCandidatesList');
    const unqualifiedList = document.getElementById('unqualifiedCandidatesList');
    const emptyQualified = document.getElementById('emptyQualifiedNotice');
    const emptyUnqualified = document.getElementById('emptyUnqualifiedNotice');

    if (!qualifiedList || !unqualifiedList) return;

    let countQual = 0;
    let countUnqual = 0;

    allCards.forEach(card => {
        const score = parseInt(card.getAttribute('data-score'), 10) || 0;
        const statusBadge = card.querySelector('.candidate-status-indicator');

        if (score >= threshold) {
            countQual++;
            card.classList.remove('unqualified-card');
            card.classList.add('qualified-card');
            if (statusBadge) {
                statusBadge.className = 'candidate-status-indicator badge';
                statusBadge.style.background = score >= 85 ? '#ecfdf5' : '#f0fdfa';
                statusBadge.style.color = score >= 85 ? '#065f46' : '#0d9488';
                statusBadge.style.borderColor = score >= 85 ? '#a7f3d0' : '#ccfbf1';
                statusBadge.innerHTML = '✓ ' + score + '% tương thích';
            }
            qualifiedList.appendChild(card);
        } else {
            countUnqual++;
            card.classList.remove('qualified-card');
            card.classList.add('unqualified-card');
            if (statusBadge) {
                statusBadge.className = 'candidate-status-indicator badge unqualified-badge-warning';
                statusBadge.style.background = '#fef3c7';
                statusBadge.style.color = '#b45309';
                statusBadge.style.borderColor = '#fde68a';
                statusBadge.innerHTML = '⚠ ' + score + '% (Dưới hạn mức)';
            }
            unqualifiedList.appendChild(card);
        }
    });

    // Cập nhật các bộ đếm
    const bQual = document.getElementById('badgeCountQualified');
    if (bQual) bQual.textContent = countQual;
    const bUnqual = document.getElementById('badgeCountUnqualified');
    if (bUnqual) bUnqual.textContent = countUnqual;
    const cQual = document.getElementById('countQualifiedBadge');
    if (cQual) cQual.textContent = countQual;
    const cUnqual = document.getElementById('countUnqualifiedBadge');
    if (cUnqual) cUnqual.textContent = countUnqual;

    const btnText = document.getElementById('toggleUnqualifiedText');
    if (btnText) {
        const isExpanded = unqualifiedList.style.display !== 'none' && unqualifiedList.style.display !== '';
        btnText.textContent = (isExpanded ? 'Thu Gọn Danh Sách (' : 'Mở Rộng Danh Sách (') + countUnqual + ')';
    }

    if (emptyQualified) emptyQualified.style.display = countQual === 0 ? 'block' : 'none';
    if (emptyUnqualified) emptyUnqualified.style.display = countUnqual === 0 ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    // Xử lý slider hạn mức %
    const slider = document.getElementById('thresholdSlider');
    if (slider) {
        slider.addEventListener('input', function() {
            applyCandidateThreshold(this.value);
        });
    }

    // Xử lý các nút preset mốc nhanh
    document.querySelectorAll('.btn-threshold-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            const pVal = parseInt(this.getAttribute('data-preset'), 10);
            applyCandidateThreshold(pVal);
        });
    });

    // 1. MODAL MỜI PHỎNG VẤN LẠI (TALENT POOL)
    const reInviteModal = document.getElementById('reInviteMeetingModal');
    const reInviteBtns = document.querySelectorAll('.btn-open-reinvite-modal');
    const closeReInviteBtn = document.getElementById('closeReInviteModalBtn');
    const cancelReInviteBtn = document.getElementById('cancelReInviteModalBtn');
    const reInviteJobSelect = document.getElementById('reInviteJobSelect');
    const reInviteWorkplace = document.getElementById('reInviteWorkplace');

    reInviteBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const seekerId = this.getAttribute('data-seeker-id');
            const seekerName = this.getAttribute('data-seeker-name');
            const pastJob = this.getAttribute('data-past-job');
            const bestJobId = this.getAttribute('data-best-job-id');
            const workplace = this.getAttribute('data-workplace') || '';

            document.getElementById('reInviteSeekerId').value = seekerId;
            document.getElementById('reInviteSeekerName').textContent = seekerName;
            document.getElementById('reInvitePastJob').textContent = pastJob;

            if (bestJobId && reInviteJobSelect) {
                reInviteJobSelect.value = bestJobId;
            }
            if (workplace && reInviteWorkplace) {
                reInviteWorkplace.value = workplace;
            }

            reInviteModal.style.display = 'flex';
        });
    });

    if (reInviteJobSelect) {
        reInviteJobSelect.addEventListener('change', function() {
            const selectedOpt = this.options[this.selectedIndex];
            const wp = selectedOpt.getAttribute('data-workplace');
            if (wp && reInviteWorkplace) {
                reInviteWorkplace.value = wp;
            }
        });
    }

    if (closeReInviteBtn) closeReInviteBtn.addEventListener('click', () => reInviteModal.style.display = 'none');
    if (cancelReInviteBtn) cancelReInviteBtn.addEventListener('click', () => reInviteModal.style.display = 'none');

    // 2. MODAL MỜI NHẬN VIỆC (AI RECOMMENDATIONS)
    const inviteModal = document.getElementById('inviteCandidateModal');
    const inviteBtns = document.querySelectorAll('.btn-open-invite-modal');
    const closeInviteBtn = document.getElementById('closeInviteModalBtn');
    const cancelInviteBtn = document.getElementById('cancelInviteModalBtn');

    inviteBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalSeekerId').value = this.getAttribute('data-seeker-id');
            document.getElementById('modalJobId').value = this.getAttribute('data-job-id');
            document.getElementById('modalSeekerName').textContent = this.getAttribute('data-seeker-name');
            inviteModal.style.display = 'flex';
        });
    });

    if (closeInviteBtn) closeInviteBtn.addEventListener('click', () => inviteModal.style.display = 'none');
    if (cancelInviteBtn) cancelInviteBtn.addEventListener('click', () => inviteModal.style.display = 'none');

    // 3. TÌM KIẾM NHANH VỊ TRÍ TUYỂN DỤNG ĐỂ ĐỐI CHIẾU
    const jobFilterSearchInput = document.getElementById('jobFilterSearchInput');
    const jobFilterSelectDropdown = document.getElementById('jobFilterSelectDropdown');
    if (jobFilterSearchInput && jobFilterSelectDropdown) {
        jobFilterSearchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            const options = jobFilterSelectDropdown.querySelectorAll('option');
            let firstMatch = null;
            options.forEach(opt => {
                const str = (opt.getAttribute('data-search') || opt.textContent).toLowerCase();
                if (!q || str.includes(q)) {
                    opt.style.display = '';
                    if (!firstMatch) firstMatch = opt.value;
                } else {
                    opt.style.display = 'none';
                }
            });
            if (q && firstMatch && options[jobFilterSelectDropdown.selectedIndex].style.display === 'none') {
                jobFilterSelectDropdown.value = firstMatch;
            }
        });
        jobFilterSearchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                jobFilterSelectDropdown.form.submit();
            }
        });
    }
});

// Hàm hiển thị Modal giải thích % điểm số khi click vào % hoặc nút xem lý do %
function showScoreModalFromCard(el) {
    if (!el) return;
    const score = el.getAttribute('data-score') || '0';
    const name = el.getAttribute('data-name') || 'Ứng viên';
    const job = el.getAttribute('data-job') || 'Vị trí tuyển dụng';
    let matched = [];
    let missing = [];
    let reasons = [];
    try { matched = JSON.parse(el.getAttribute('data-matched') || '[]'); } catch(e){}
    try { missing = JSON.parse(el.getAttribute('data-missing') || '[]'); } catch(e){}
    try { reasons = JSON.parse(el.getAttribute('data-reasons') || '[]'); } catch(e){}

    const exp = el.getAttribute('data-exp') || '0';
    const edu = el.getAttribute('data-edu') || '';
    const address = el.getAttribute('data-address') || '';

    const modalScoreVal = document.getElementById('modalScoreVal');
    if (modalScoreVal) modalScoreVal.textContent = score + '%';
    const modalCandidateName = document.getElementById('modalCandidateName');
    if (modalCandidateName) modalCandidateName.textContent = name;
    const modalJobTitle = document.getElementById('modalJobTitle');
    if (modalJobTitle) modalJobTitle.textContent = job;

    let matchedHtml = '• Trùng khớp ' + (matched.length > 0 ? ('<b>' + matched.length + ' kỹ năng</b>: ' + matched.join(', ')) : 'các tiêu chí cơ bản') + '<br>';
    matchedHtml += '• Kinh nghiệm thực tế: <b>' + exp + ' năm KN</b> đáp ứng điều kiện tuyển dụng<br>';
    if (address) matchedHtml += '• Khu vực cư trú: ' + address + ' thuận tiện di chuyển';
    const modalMatchedText = document.getElementById('modalMatchedText');
    if (modalMatchedText) modalMatchedText.innerHTML = matchedHtml;

    let missingHtml = '';
    if (missing.length > 0) {
        missingHtml += '• Còn thiếu kỹ năng: <b>' + missing.join(', ') + '</b><br>';
    }
    if (reasons.length > 0) {
        missingHtml += '• Đánh giá thêm: ' + reasons[reasons.length - 1];
    } else {
        missingHtml = 'Không có thiếu sót lớn. Ứng viên đáp ứng trọn vẹn tiêu chuẩn!';
    }
    const modalMissingText = document.getElementById('modalMissingText');
    if (modalMissingText) modalMissingText.innerHTML = missingHtml;

    let resultHtml = 'Độ tương thích thực tế: <b>' + score + '%</b>. ';
    if (parseInt(score) >= 85) {
        resultHtml += 'Ứng viên thuộc nhóm <b>Rất Phù Hợp (Hạng Xuất Sắc)</b>. Đề xuất gửi lời mời phỏng vấn và tiếp nhận ngay!';
    } else if (parseInt(score) >= 70) {
        resultHtml += 'Ứng viên thuộc nhóm <b>Phù Hợp Cao</b>. Đạt chuẩn tiêu chí doanh nghiệp, có thể đào tạo thêm trong 1-2 tuần.';
    } else {
        resultHtml += 'Ứng viên thuộc nhóm <b>Tiềm Năng (Dưới hạn mức yêu cầu)</b>. Cân nhắc đào tạo bổ sung nếu tuyển dụng.';
    }
    const modalResultText = document.getElementById('modalResultText');
    if (modalResultText) modalResultText.innerHTML = resultHtml;

    const modal = document.getElementById('cardScoreReasonModal');
    if (modal) modal.style.display = 'flex';
}
</script>

<!-- MODAL GIẢI THÍCH CHI TIẾT: CLICK VÔ % TẠI SAO CÓ GIÁ TRỊ ĐÓ -->
<div id="cardScoreReasonModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 10003; justify-content: center; align-items: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 650px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); padding: 1.5rem;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.2rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem; font-weight: 800;">
                <span style="color: #0d9488;"><?= icon('sparkle', '', 18) ?></span>
                <span>Tại Sao Hồ Sơ Đạt <span id="modalScoreVal" style="color: #0d9488;">--%</span> Tương Thích?</span>
            </h3>
            <button type="button" onclick="document.getElementById('cardScoreReasonModal').style.display='none'" style="border: none; background: #f1f5f9; color: #64748b; font-size: 1.2rem; cursor: pointer; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; line-height: 1;">
                ✕
            </button>
        </div>

        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem; font-size: 0.84rem; color: #475569;">
            <div>Ứng viên: <strong id="modalCandidateName" style="color: #0f172a;"></strong></div>
            <div style="margin-top: 2px;">Vị trí đối chiếu: <strong id="modalJobTitle" style="color: #0d9488;"></strong></div>
        </div>

        <!-- 3 KHỐI: ĐẠT ĐƯỢC - KHÔNG ĐẠT ĐƯỢC -> KẾT QUẢ -->
        <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1.25rem;">
            <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 0.85rem;">
                <strong style="color: #166534; font-size: 0.85rem; display: flex; align-items: center; gap: 4px; margin-bottom: 0.35rem;">
                    ✓ 1. Tiêu chí đạt được (Cộng điểm):
                </strong>
                <div id="modalMatchedText" style="font-size: 0.82rem; color: #14532d; line-height: 1.45;"></div>
            </div>

            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.85rem;">
                <strong style="color: #92400e; font-size: 0.85rem; display: flex; align-items: center; gap: 4px; margin-bottom: 0.35rem;">
                    ✕ 2. Tiêu chí chưa đạt / Cần lưu ý (Trừ điểm hoặc cần đào tạo):
                </strong>
                <div id="modalMissingText" style="font-size: 0.82rem; color: #78350f; line-height: 1.45;"></div>
            </div>

            <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 0.85rem;">
                <strong style="color: #0f766e; font-size: 0.85rem; display: flex; align-items: center; gap: 4px; margin-bottom: 0.35rem;">
                    ➔ 3. Kết quả đánh giá & Khuyến nghị:
                </strong>
                <div id="modalResultText" style="font-size: 0.82rem; color: #115e59; line-height: 1.45;"></div>
            </div>
        </div>

        <div style="text-align: right;">
            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('cardScoreReasonModal').style.display='none'" style="font-weight: 700;">
                Đã Hiểu
            </button>
        </div>

    </div>
</div>
</div> <!-- /workspace-zone-body -->

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>