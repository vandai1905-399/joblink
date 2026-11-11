<?php 
$extraCss = ['css/applications/employer_applications.css', 'css/jobs/ai_matching.css'];
ob_start(); 
?>

<!-- KHU 1 CỦA KHU 3: HEADER CỐ ĐỊNH CHỈ CHỨA MỖI TIÊU ĐỀ -->
<div class="workspace-zone-header">
    <div class="workspace-header-title-box">
        <h1 class="workspace-page-title">
            Tổng quan tuyển dụng – <?= htmlspecialchars($employer['employer_name'] ?? 'Doanh Nghiệp') ?>
        </h1>
        <p class="workspace-page-subtitle">Quản lý tuyển dụng & hồ sơ ứng viên hôm nay</p>
    </div>
</div>

<!-- KHU 2 CỦA KHU 3: BODY CHỨA TOÀN BỘ NỘI DUNG THỰC THI (CUỘN ĐỘC LẬP) -->
<div class="workspace-zone-body">

<div class="grid-stats" style="margin-bottom: 0.85rem; display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.65rem;">
    <a href="<?= url('/employer/jobs') ?>" class="stat-card compact-stat workspace-nav-link" data-title="Quản Lý Tin" style="cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; justify-content: space-between;">
        <div class="stat-row-top">
            <span class="stat-title">Tin Đang Mở</span>
            <span class="stat-icon-badge" style="background: rgba(13, 148, 136, 0.1); color: var(--primary);">
                <?= icon('posts', '', 14) ?>
            </span>
        </div>
        <div class="stat-row-bottom">
            <span class="stat-value" style="color: var(--primary);"><?= count($jobs ?? []) ?></span>
            <span class="stat-link-arrow" style="color: var(--primary);">Quản lý tin</span>
        </div>
    </a>
    <a href="<?= url('/employer/applications') ?>" class="stat-card compact-stat workspace-nav-link" data-title="Hồ Sơ Ứng Tuyển" style="cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; justify-content: space-between;">
        <div class="stat-row-top">
            <span class="stat-title">Hồ Sơ Nhận Được</span>
            <span class="stat-icon-badge" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                <?= icon('applications', '', 14) ?>
            </span>
        </div>
        <div class="stat-row-bottom">
            <span class="stat-value" style="color: var(--warning);"><?= count($applications ?? []) ?></span>
            <span class="stat-link-arrow" style="color: var(--warning);">Xem hồ sơ</span>
        </div>
    </a>
    <a href="<?= url('/employer/meetings') ?>" class="stat-card compact-stat workspace-nav-link" data-title="Lịch Phỏng Vấn" style="cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; justify-content: space-between;">
        <div class="stat-row-top">
            <span class="stat-title">Lịch Phỏng Vấn</span>
            <span class="stat-icon-badge" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                <?= icon('meetings', '', 14) ?>
            </span>
        </div>
        <div class="stat-row-bottom">
            <span class="stat-value" style="color: var(--success);"><?= count($meetings ?? []) ?></span>
            <span class="stat-link-arrow" style="color: var(--success);">Quản lý lịch</span>
        </div>
    </a>
    <a href="<?= url('/employer/candidate-reviews') ?>" class="stat-card compact-stat workspace-nav-link" data-title="Đánh Giá Sau Ca" style="cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; justify-content: space-between;">
        <div class="stat-row-top">
            <span class="stat-title">Đánh Giá Từ NLĐ</span>
            <span class="stat-icon-badge" style="background: rgba(13, 148, 136, 0.1); color: var(--primary);">
                <?= icon('star_fill', '', 14) ?>
            </span>
        </div>
        <div class="stat-row-bottom">
            <span class="stat-value" style="color: var(--primary); display: flex; align-items: center; gap: 0.2rem; font-size: 1.35rem;">
                <?= $employer['avg_rating'] ?? '5.0' ?> <span style="font-size: 0.9rem; color: #f59e0b;">★</span>
            </span>
            <span style="font-size: 0.72rem; color: var(--slate-500);"><?= $employer['total_reviews'] ?? 0 ?> đánh giá</span>
        </div>
    </a>
</div>

<div class="employer-quick-actions" style="display: flex; gap: 0.5rem; margin-bottom: 0.85rem; flex-wrap: wrap;">
    <a href="<?= url('/employer/jobs/create') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Đăng Tin Mới" style="font-size: 0.78rem; font-weight: 700; padding: 0.3rem 0.65rem;">
        <?= icon('posts', '', 14) ?> <span style="margin-left: 4px;">+ Đăng Tin Mới</span>
    </a>
    <a href="<?= url('/employer/candidates/recommended') ?>" class="btn btn-outline btn-sm workspace-nav-link" data-title="NLĐ Đề Xuất AI" style="color: #0d9488; border-color: #0d9488; font-weight: 700; background: #f0fdfa; font-size: 0.78rem; padding: 0.3rem 0.65rem;">
        <?= icon('zap', '', 14) ?> <span style="margin-left: 4px;">NLĐ Đề Xuất AI</span>
    </a>
    <a href="<?= url('/employer/meetings') ?>" class="btn btn-outline btn-sm workspace-nav-link" data-title="Lịch Phỏng Vấn" style="font-size: 0.78rem; padding: 0.3rem 0.65rem;">
        <?= icon('meetings', '', 14) ?> <span style="margin-left: 4px;">Lên Lịch Phỏng Vấn</span>
    </a>
    <a href="<?= url('/employer/candidate-reviews') ?>" class="btn btn-outline btn-sm workspace-nav-link" data-title="Đánh Giá Sau Ca" style="font-size: 0.78rem; padding: 0.3rem 0.65rem;">
        <?= icon('reviews', '', 14) ?> <span style="margin-left: 4px;">Đánh Giá Sau Ca</span>
    </a>
</div>

<!-- ========================================================================= -->
<!-- KHỐI GỢI Ý NGƯỜI LAO ĐỘNG ĐỀ XUẤT CHO NHÀ TUYỂN DỤNG (KẾT HỢP TRANG CHỦ)  -->
<!-- ========================================================================= -->
<div class="ai-recommend-container" style="margin-bottom: 1.25rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h2 style="font-size: 1.22rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3;">
                Gợi Ý Người Lao Động Phù Hợp Cho Tin Tuyển Dụng
            </h2>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <button type="button" id="btnOpenCriteriaModal" class="btn btn-outline btn-sm" style="font-size: 0.78rem; font-weight: 700; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 6px; color: #ffffff; border-color: #0d9488; background: #0d9488; cursor: pointer; border-radius: 6px; transition: all 0.2s ease;">
                <?= icon('settings', '', 14) ?>
                <span>Thiết Lập Cài Đặt Độ Tương Thích</span>
                <span id="activeCriteriaCountBadge" style="background: #0d9488; color: #ffffff; font-size: 0.68rem; font-weight: 700; padding: 0.1rem 0.45rem; border-radius: 10px; margin-left: 2px;">
                    <?= count(array_filter($employerCriteriaList ?? [], fn($c) => !empty($c['is_active']))) ?>/<?= count($employerCriteriaList ?? []) ?>
                </span>
            </button>
            <a href="<?= url('/employer/candidates/recommended?job_id=' . $selectedJobId . '&threshold=' . $threshold) ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="NLĐ Đề Xuất AI" style="font-size: 0.78rem; font-weight: 700; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 4px;">
                <span>Quản Lý Toàn Bộ NLĐ Đề Xuất</span>
            </a>
        </div>
    </div>

    <!-- BỘ LỌC CÔNG VIỆC VÀ HẠN MỨC % DO NTD TỰ CHỈNH TRÊN TRANG CHỦ -->
    <?php
    $selectedJobTitle = 'Vị trí tuyển dụng';
    if (!empty($activeJobs)) {
        foreach ($activeJobs as $j) {
            if ($selectedJobId === intval($j['job_post_id'])) {
                $selectedJobTitle = $j['job_title'];
                break;
            }
        }
        if ($selectedJobTitle === 'Vị trí tuyển dụng' && !empty($activeJobs[0]['job_title'])) {
            $selectedJobTitle = $activeJobs[0]['job_title'];
        }
    }
    ?>
    <div class="threshold-box" style="margin-bottom: 1rem; padding: 0.85rem 1rem;">
        <form method="GET" action="<?= url('/employer/dashboard') ?>" id="dashThresholdForm">
            <input type="hidden" name="threshold" id="dashFormThresholdInput" value="<?= $threshold ?>">

            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; justify-content: space-between; margin-bottom: 0.75rem; padding-bottom: 0.65rem; border-bottom: 1px solid #f1f5f9;">
                <div style="flex: 1; min-width: 280px; display: flex; align-items: flex-start; gap: 0.5rem;">
                    <span style="color: #0d9488; display: flex; align-items: center; margin-top: 1.25rem;">
                        <?= icon('briefcase', '', 18) ?>
                    </span>
                    <div style="flex: 1;">
                        <label style="font-size: 0.76rem; font-weight: 700; color: #475569; display: block; margin-bottom: 0.25rem; text-transform: uppercase;">
                            Chọn vị trí tuyển dụng để đối chiếu ứng viên (Có ô tìm kiếm nhanh):
                        </label>
                        <div style="display: flex; gap: 0.4rem; align-items: center; margin-bottom: 0.35rem;">
                            <div style="position: relative; flex: 1;">
                                <input type="text" id="dashJobSearchInput" placeholder="🔍 Gõ từ khóa để tìm nhanh vị trí tuyển dụng..."
                                       class="form-control" style="padding: 0.4rem 0.65rem 0.4rem 2rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; background: #ffffff;">
                                <span style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                                    <?= icon('search', '', 13) ?>
                                </span>
                            </div>
                        </div>
                        <select name="job_id" id="dashJobSelectDropdown" class="form-control" onchange="this.form.submit()" style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.88rem; font-weight: 600; color: #0f172a; background: #f8fafc;">
                            <?php if (empty($activeJobs)): ?>
                                <option value="0">Chưa có tin tuyển dụng nào đang mở</option>
                            <?php else: ?>
                                <?php foreach ($activeJobs as $j): ?>
                                    <option value="<?= $j['job_post_id'] ?>" data-search="<?= htmlspecialchars(mb_strtolower($j['job_title'] . ' ' . ($j['workplace_address'] ?? ''))) ?>" <?= $selectedJobId === intval($j['job_post_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($j['job_title']) ?> (Tuyển <?= $j['number_of_positions'] ?> người)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="threshold-summary-pills">
                    <span class="pill-qualified" style="font-size: 0.78rem; padding: 0.2rem 0.5rem;">
                        <?= icon('check', '', 11) ?> Đạt chuẩn: <strong id="dashCountQualifiedBadge" style="margin-left: 2px;"><?= count($qualifiedCandidates) ?></strong>
                    </span>
                    <span class="pill-unqualified" style="font-size: 0.78rem; padding: 0.2rem 0.5rem;">
                        <?= icon('alert_circle', '', 11) ?> Chưa đạt: <strong id="dashCountUnqualifiedBadge" style="margin-left: 2px;"><?= count($unqualifiedCandidates) ?></strong>
                    </span>
                </div>
            </div>

            <!-- THANH ĐIỀU CHỈNH HẠN MỨC % TRANG CHỦ -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                <div style="font-size: 0.84rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                    <span>Hạn mức % tương thích hiển thị:</span>
                    <span class="threshold-badge" id="dashThresholdBadge" style="font-size: 0.8rem; padding: 0.15rem 0.55rem;"><?= $threshold ?>%</span>
                </div>
                <div class="threshold-presets">
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Mốc nhanh:</span>
                    <button type="button" class="btn-threshold-preset dash-preset <?= $threshold === 60 ? 'active' : '' ?>" data-preset="60" style="padding: 0.2rem 0.5rem; font-size: 0.74rem;">60%</button>
                    <button type="button" class="btn-threshold-preset dash-preset <?= $threshold === 70 ? 'active' : '' ?>" data-preset="70" style="padding: 0.2rem 0.5rem; font-size: 0.74rem;">70%</button>
                    <button type="button" class="btn-threshold-preset dash-preset <?= $threshold === 80 ? 'active' : '' ?>" data-preset="80" style="padding: 0.2rem 0.5rem; font-size: 0.74rem;">80%</button>
                    <button type="button" class="btn-threshold-preset dash-preset <?= $threshold === 90 ? 'active' : '' ?>" data-preset="90" style="padding: 0.2rem 0.5rem; font-size: 0.74rem;">90%</button>
                </div>
            </div>

            <div class="threshold-slider-wrapper">
                <span style="font-size: 0.72rem; font-weight: 700; color: #64748b;">30%</span>
                <input type="range" min="30" max="95" step="5" value="<?= $threshold ?>" class="threshold-slider" id="dashThresholdSlider" title="Kéo để điều chỉnh hạn mức %">
                <span style="font-size: 0.72rem; font-weight: 700; color: #64748b;">95%</span>
            </div>
        </form>
    </div>

    <!-- DANH SÁCH ỨNG VIÊN ĐẠT CHUẨN (>= HẠN MỨC %) -->
    <div style="margin-bottom: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
            <div style="font-size: 0.92rem; font-weight: 800; color: #065f46; display: flex; align-items: center; gap: 0.35rem;">
                <span>✓ Ứng Viên Đạt Chuẩn (≥ <span class="dash-threshold-val"><?= $threshold ?>%</span>)</span>
                <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-size: 0.74rem; font-weight: 700;">
                    <span id="dashBadgeCountQualified"><?= count($qualifiedCandidates) ?></span> ứng viên
                </span>
            </div>
            <span style="font-size: 0.75rem; color: #64748b;">Hiển thị trực tiếp trên trang chủ</span>
        </div>

        <?php
        $formatDashCriteriaTooltip = static function ($candidate) {
            $criteria = $candidate['ai_unmet_criteria'] ?? [];
            $lines = [];

            foreach ($criteria as $criterion) {
                $label = trim(html_entity_decode(strip_tags((string) ($criterion['label'] ?? '')), ENT_QUOTES, 'UTF-8'));
                if ($label === '') {
                    continue;
                }

                $details = [];
                foreach (['required' => 'Yêu cầu', 'actual' => 'Hồ sơ'] as $key => $caption) {
                    $value = trim(html_entity_decode(strip_tags((string) ($criterion[$key] ?? '')), ENT_QUOTES, 'UTF-8'));
                    if ($value !== '') {
                        $details[] = $caption . ': ' . $value;
                    }
                }

                $lines[] = $label . (!empty($details) ? ' (' . implode('; ', $details) . ')' : '');
            }

            $missingSkills = array_values(array_filter(
                $candidate['ai_missing_skills'] ?? [],
                static fn($skill) => trim((string) $skill) !== ''
            ));
            if (!empty($missingSkills)) {
                $lines[] = 'Kỹ năng còn thiếu: ' . implode(', ', array_map(
                    static fn($skill) => html_entity_decode(strip_tags((string) $skill), ENT_QUOTES, 'UTF-8'),
                    $missingSkills
                ));
            }

            return empty($lines)
                ? 'Ứng viên đang đáp ứng các tiêu chí áp dụng.'
                : "Tiêu chí chưa đạt hoặc cần kiểm tra:\n• " . implode("\n• ", $lines);
        };
        ?>
        <div id="dashQualifiedContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 0.85rem;">
            <?php foreach ($qualifiedCandidates as $rec): ?>
                <?php $criteriaTooltip = htmlspecialchars($formatDashCriteriaTooltip($rec), ENT_QUOTES, 'UTF-8'); ?>
                <div class="sourcing-card dash-cand-card qualified-card <?= ($rec['ai_match_score'] >= 85) ? 'featured' : '' ?>" data-score="<?= $rec['ai_match_score'] ?>" data-seeker-id="<?= $rec['seeker_id'] ?>" style="padding: 0.85rem;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.65rem; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.65rem; align-items: center;">
                                <img src="<?= htmlspecialchars(getAvatarUrl($rec['avatar_url'] ?? '', 'JOB_SEEKER', $rec['gender'] ?? '')) ?>" alt="avatar" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $rec['gender'] ?? '') ?>';">
                                <div style="min-width: 0;">
                                    <a href="<?= url('/employer/candidates/' . $rec['seeker_id'] . ($selectedJobId > 0 ? '?job_id=' . $selectedJobId : '')) ?>" class="workspace-nav-link" data-title="Hồ sơ: <?= htmlspecialchars($rec['full_name']) ?>" style="font-weight: 700; font-size: 0.96rem; color: #0f172a; text-decoration: none;" title="Bấm xem hồ sơ nhân sự (Hiện thông tin % tương thích)">
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;"><?= htmlspecialchars($rec['full_name']) ?></span>
                                    </a>
                                    <div style="font-size: 0.74rem; color: #64748b; margin-top: 1px;">
                                        <?= htmlspecialchars($rec['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($rec['date_of_birth']) ? (date('Y') - date('Y', strtotime($rec['date_of_birth']))) . 't' : '' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                    </div>
                                </div>
                            </div>
                            <span class="dash-status-badge badge btn-candidate-score-info" 
                                  data-score="<?= $rec['ai_match_score'] ?>"
                                  data-level="<?= htmlspecialchars($rec['ai_match_label'] ?? 'Phù hợp') ?>"
                                  data-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                  data-job="<?= htmlspecialchars($selectedJobTitle) ?>"
                                  data-matched="<?= htmlspecialchars(json_encode($rec['ai_matched_skills'] ?? [])) ?>"
                                  data-missing="<?= htmlspecialchars(json_encode($rec['ai_missing_skills'] ?? [])) ?>"
                                  data-reasons="<?= htmlspecialchars(json_encode($rec['ai_reasons'] ?? [])) ?>"
                                  data-exp="<?= intval($rec['years_of_experience'] ?? 0) ?>"
                                  data-edu="<?= htmlspecialchars($rec['education_level'] ?? 'Phổ thông') ?>"
                                  data-address="<?= htmlspecialchars($rec['address'] ?? 'Bình Dương') ?>"
                                  onclick="showScoreModalFromCard(this)"
                                  style="background: <?= $rec['ai_match_score'] >= 85 ? '#ecfdf5' : '#f0fdfa' ?>; color: <?= $rec['ai_match_score'] >= 85 ? '#065f46' : '#0d9488' ?>; border: 1.5px solid <?= $rec['ai_match_score'] >= 85 ? '#a7f3d0' : '#ccfbf1' ?>; font-size: 0.74rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 6px; white-space: nowrap; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;"
                                  title="Click vào % để xem giải thích: Tại sao có giá trị này?">
                                <?= icon('sparkle', '', 11) ?> <?= $rec['ai_match_score'] ?>%
                            </span>
                        </div>

                        <!-- Kỹ năng & Uy tín -->
                        <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 0.65rem;">
                            <span class="badge" style="background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; font-size: 0.72rem; padding: 0.15rem 0.4rem;">
                                <?= icon('shield', '', 11) ?> Uy tín: <?= $rec['trust_score'] ?? 80 ?>/100
                            </span>
                            <?php if (!empty($rec['address'])): ?>
                                <span class="badge" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-size: 0.72rem; padding: 0.15rem 0.4rem;">
                                    <?= icon('map_pin', '', 11) ?> <?= htmlspecialchars(mb_substr($rec['address'], 0, 20)) ?>...
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- KHỐI 3 PHẦN: ĐẠT ĐƯỢC - KHÔNG ĐẠT ĐƯỢC -> KẾT QUẢ -->
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 0.45rem 0.6rem; margin-bottom: 0.65rem; font-size: 0.72rem; display: flex; flex-direction: column; gap: 0.3rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.3rem; color: #166534;">
                                <span style="font-weight: 800; color: #16a34a; flex-shrink: 0;">✓ Đạt:</span>
                                <span style="font-weight: 600;"><?= !empty($rec['ai_matched_skills']) ? implode(', ', array_slice($rec['ai_matched_skills'], 0, 2)) : 'Tiêu chí chung' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN</span>
                            </div>
                            <?php if (!empty($rec['ai_missing_skills'])): ?>
                            <div style="display: flex; align-items: flex-start; gap: 0.3rem; color: #92400e;">
                                <span style="font-weight: 800; color: #d97706; flex-shrink: 0;">✕ Cải thiện:</span>
                                <span><?= implode(', ', array_slice($rec['ai_missing_skills'], 0, 2)) ?></span>
                            </div>
                            <?php endif; ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px dashed #86efac; padding-top: 0.25rem;">
                                <span style="color: #065f46; font-weight: 700;">➔ Kết quả: <strong><?= $rec['ai_match_score'] ?>%</strong></span>
                                <span onclick="showScoreModalFromCard(this.closest('.dash-cand-card').querySelector('.btn-candidate-score-info'))" style="color: #0d9488; font-size: 0.7rem; font-weight: 700; cursor: pointer; text-decoration: underline;">
                                    Chi tiết %
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Thao Tác -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 0.65rem; display: flex; justify-content: space-between; align-items: center; gap: 0.4rem;">
                        <a href="<?= url('/employer/candidates/' . $rec['seeker_id'] . ($selectedJobId > 0 ? '?job_id=' . $selectedJobId : '')) ?>" class="workspace-nav-link" data-title="Hồ sơ: <?= htmlspecialchars($rec['full_name']) ?>" style="font-size: 0.78rem; color: #0d9488; font-weight: 700; text-decoration: none;" title="Bấm xem hồ sơ nhân sự (Hiện thông tin % tương thích)">
                            Hồ sơ
                        </a>
                        <div style="display: flex; gap: 0.35rem;">
                            <a href="<?= url('/employer/messages?partner_id=' . ($rec['account_id'] ?? 0) . '&seeker_id=' . ($rec['seeker_id'] ?? 0) . '&job_id=' . $selectedJobId . '&open_meeting_modal=1') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Tin Nhắn Trao Đổi" style="font-size: 0.74rem; font-weight: 700; padding: 0.25rem 0.6rem; background: #0d9488; border-color: #0d9488;">
                                Hẹn PV
                            </a>
                            <button type="button" class="btn btn-sm btn-open-invite-modal"
                                    data-seeker-id="<?= $rec['seeker_id'] ?>"
                                    data-job-id="<?= $selectedJobId ?>"
                                    data-seeker-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                    data-job-title="<?= htmlspecialchars($selectedJobTitle) ?>"
                                    style="font-size: 0.74rem; font-weight: 700; padding: 0.25rem 0.6rem; color: #ffffff; background: #0284c7; border: 1px solid #0284c7;">
                                Mời Nhận Việc
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="dashEmptyQualified" class="card" style="display: <?= empty($qualifiedCandidates) ? 'block' : 'none' ?>; text-align: center; padding: 2rem 1rem; color: #64748b; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 10px; margin-top: 0.65rem;">
            <p style="font-size: 0.84rem; margin: 0; color: #475569;">
                Không có ứng viên nào đạt hạn mức <span class="dash-threshold-val"><?= $threshold ?>%</span>. Hãy kéo giảm hạn mức ở thanh trượt trên.
            </p>
        </div>
    </div>

    <!-- PHẦN CHƯA ĐẠT HẠN MỨC (< HẠN MỨC %) -->
    <div class="unqualified-section-card" id="dashUnqualifiedWrapper" style="margin-top: 1rem; padding: 0.85rem 1rem;">
        <div class="unqualified-section-header" onclick="toggleDashUnqualified()">
            <div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span style="color: #d97706;"><?= icon('alert_circle', '', 16) ?></span>
                    <strong style="font-size: 0.92rem; color: #92400e;">
                        Ứng Viên Chưa Đạt Hạn Mức (< <span class="dash-threshold-val"><?= $threshold ?>%</span>)
                    </strong>
                    <span class="unqualified-badge-warning" style="font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                        <span id="dashBadgeCountUnqualified"><?= count($unqualifiedCandidates) ?></span> hồ sơ
                    </span>
                </div>
            </div>
            <button type="button" class="btn btn-outline btn-sm" id="btnToggleDashUnqualified" style="background: #d97706; border-color: #d97706; color: #ffffff; font-weight: 700; font-size: 0.74rem; padding: 0.25rem 0.55rem;">
                <span id="dashToggleText">Mở Rộng (<?= count($unqualifiedCandidates) ?>)</span>
                <span id="dashToggleIcon">▼</span>
            </button>
        </div>

        <div id="dashUnqualifiedContainer" style="display: none; margin-top: 0.85rem; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 0.85rem;">
            <?php foreach ($unqualifiedCandidates as $rec): ?>
                <?php $criteriaTooltip = htmlspecialchars($formatDashCriteriaTooltip($rec), ENT_QUOTES, 'UTF-8'); ?>
                <div class="sourcing-card dash-cand-card unqualified-card" data-score="<?= $rec['ai_match_score'] ?>" data-seeker-id="<?= $rec['seeker_id'] ?>" style="padding: 0.85rem;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.65rem; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.65rem; align-items: center;">
                                <img src="<?= htmlspecialchars(getAvatarUrl($rec['avatar_url'] ?? '', 'JOB_SEEKER', $rec['gender'] ?? '')) ?>" alt="avatar" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $rec['gender'] ?? '') ?>';">
                                <div style="min-width: 0;">
                                    <a href="<?= url('/employer/candidates/' . $rec['seeker_id'] . ($selectedJobId > 0 ? '?job_id=' . $selectedJobId : '')) ?>" class="workspace-nav-link" data-title="Hồ sơ: <?= htmlspecialchars($rec['full_name']) ?>" style="font-weight: 700; font-size: 0.96rem; color: #0f172a; text-decoration: none;" title="Bấm xem hồ sơ nhân sự (Hiện thông tin % tương thích)">
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;"><?= htmlspecialchars($rec['full_name']) ?></span>
                                    </a>
                                    <div style="font-size: 0.74rem; color: #64748b; margin-top: 1px;">
                                        <?= htmlspecialchars($rec['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($rec['date_of_birth']) ? (date('Y') - date('Y', strtotime($rec['date_of_birth']))) . 't' : '' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                    </div>
                                </div>
                            </div>
                            <span class="dash-status-badge badge btn-candidate-score-info unqualified-badge-warning" 
                                  data-score="<?= $rec['ai_match_score'] ?>"
                                  data-level="<?= htmlspecialchars($rec['ai_match_label'] ?? 'Chưa đạt hạn mức') ?>"
                                  data-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                  data-job="<?= htmlspecialchars($selectedJobTitle) ?>"
                                  data-matched="<?= htmlspecialchars(json_encode($rec['ai_matched_skills'] ?? [])) ?>"
                                  data-missing="<?= htmlspecialchars(json_encode($rec['ai_missing_skills'] ?? [])) ?>"
                                  data-reasons="<?= htmlspecialchars(json_encode($rec['ai_reasons'] ?? [])) ?>"
                                  data-exp="<?= intval($rec['years_of_experience'] ?? 0) ?>"
                                  data-edu="<?= htmlspecialchars($rec['education_level'] ?? 'Phổ thông') ?>"
                                  data-address="<?= htmlspecialchars($rec['address'] ?? 'Bình Dương') ?>"
                                  onclick="showScoreModalFromCard(this)"
                                  style="font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 6px; white-space: nowrap; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;"
                                  title="Click vào % để xem giải thích: Tại sao có giá trị này?">
                                <?= $rec['ai_match_score'] ?>%
                            </span>
                        </div>

                        <!-- KHỐI 3 PHẦN: ĐẠT ĐƯỢC - KHÔNG ĐẠT ĐƯỢC -> KẾT QUẢ -->
                        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 0.45rem 0.6rem; margin-bottom: 0.65rem; font-size: 0.72rem; display: flex; flex-direction: column; gap: 0.3rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.3rem; color: #166534;">
                                <span style="font-weight: 800; color: #16a34a; flex-shrink: 0;">✓ Đạt:</span>
                                <span style="font-weight: 600;"><?= !empty($rec['ai_matched_skills']) ? implode(', ', array_slice($rec['ai_matched_skills'], 0, 2)) : 'Tiêu chí cơ bản' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN</span>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 0.3rem; color: #92400e;">
                                <span style="font-weight: 800; color: #d97706; flex-shrink: 0;">✕ Chưa đạt:</span>
                                <span><?= !empty($rec['ai_missing_skills']) ? implode(', ', array_slice($rec['ai_missing_skills'], 0, 2)) : (!empty($rec['ai_reasons']) ? htmlspecialchars($rec['ai_reasons'][0]) : 'Chưa tối ưu kỹ năng yêu cầu') ?></span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px dashed #fcd34d; padding-top: 0.25rem;">
                                <span style="color: #b45309; font-weight: 700;">➔ Kết quả: <strong><?= $rec['ai_match_score'] ?>%</strong> (Dưới mốc)</span>
                                <span onclick="showScoreModalFromCard(this.closest('.dash-cand-card').querySelector('.btn-candidate-score-info'))" style="color: #d97706; font-size: 0.7rem; font-weight: 700; cursor: pointer; text-decoration: underline;">
                                    Chi tiết %
                                </span>
                            </div>
                        </div>
                    </div>

                    <div style="border-top: 1px solid #f1f5f9; padding-top: 0.65rem; display: flex; justify-content: space-between; align-items: center; gap: 0.4rem;">
                        <a href="<?= url('/employer/candidates/' . $rec['seeker_id'] . ($selectedJobId > 0 ? '?job_id=' . $selectedJobId : '')) ?>" class="workspace-nav-link" data-title="Hồ sơ: <?= htmlspecialchars($rec['full_name']) ?>" style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-decoration: none;" title="Bấm xem hồ sơ nhân sự (Hiện thông tin % tương thích)">
                            Hồ sơ
                        </a>
                        <button type="button" class="btn btn-sm btn-open-invite-modal"
                                data-seeker-id="<?= $rec['seeker_id'] ?>"
                                data-job-id="<?= $selectedJobId ?>"
                                data-seeker-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                data-job-title="<?= htmlspecialchars($selectedJobTitle) ?>"
                                style="font-size: 0.74rem; font-weight: 700; padding: 0.25rem 0.6rem; color: #ffffff; background: #d97706; border: 1px solid #d97706;">
                            Vẫn Mời
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="dashEmptyUnqualified" class="card" style="display: <?= empty($unqualifiedCandidates) ? 'block' : 'none' ?>; text-align: center; padding: 1rem; color: #059669; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 8px; margin-top: 0.65rem;">
            <p style="font-size: 0.8rem; margin: 0; font-weight: 600;">✓ Tất cả ứng viên đều đạt hạn mức bạn đã đặt.</p>
        </div>
    </div>
</div>


<div class="card" style="border-radius: 10px; padding: 0.85rem 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
        <a href="<?= url('/employer/applications') ?>" class="workspace-nav-link" data-title="Hồ Sơ Ứng Tuyển" style="font-size: 0.95rem; font-weight: 700; color: var(--dark); text-decoration: none; margin: 0;">
            Hồ Sơ Ứng Tuyển Mới Nhất
        </a>
        <a href="<?= url('/employer/applications') ?>" class="btn btn-outline btn-sm workspace-nav-link" data-title="Hồ Sơ Ứng Tuyển" style="font-size: 0.75rem; padding: 0.2rem 0.5rem; height: auto;">Xem tất cả</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Người lao động</th>
                    <th>Vị trí nộp</th>
                    <th>Email / SĐT</th>
                    <th>Ngày nộp</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--slate-400); padding: 1.25rem; font-size: 0.82rem;">Chưa có người lao động nộp hồ sơ.</td></tr>
                <?php else: ?>
                    <?php foreach (array_slice($applications, 0, 5) as $app): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/employer/candidates/' . $app['seeker_id'] . (!empty($app['job_post_id']) ? '?job_id=' . $app['job_post_id'] : ($selectedJobId > 0 ? '?job_id=' . $selectedJobId : ''))) ?>" class="workspace-nav-link" data-title="Hồ sơ: <?= htmlspecialchars($app['seeker_name']) ?>" style="text-decoration: none; color: var(--primary); font-weight: 700;" title="Xem hồ sơ người lao động & % tương thích">
                                    <?= htmlspecialchars($app['seeker_name']) ?>
                                </a>
                            </td>
                            <td style="color: #334155; font-weight: 500;">
                                <strong><?= htmlspecialchars($app['job_title']) ?></strong>
                                <?php if (!empty($app['available_work_dates'])): ?>
                                    <br><span style="font-size: 0.75rem; color: #0d9488; font-weight: 600; background: #f0fdfa; border: 1px solid #ccfbf1; padding: 0.1rem 0.35rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.2rem; margin-top: 0.2rem;">
                                        <?= icon('calendar', '', 11) ?> <span><?= htmlspecialchars($app['available_work_dates']) ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="color: #0f172a; font-weight: 600; font-size: 0.82rem;"><?= htmlspecialchars($app['phone_number']) ?></span><br>
                                <span style="font-size: 0.78rem; color: var(--slate-500);"><?= htmlspecialchars($app['email']) ?></span>
                            </td>
                            <td style="color: #475569; font-size: 0.82rem;"><?= date('d/m/Y', strtotime($app['applied_at'])) ?></td>
                            <td>
                                <?php if ($app['request_status'] === 'accepted'): ?>
                                    <span class="badge badge-success" style="font-size: 0.7rem; padding: 0.15rem 0.4rem;"><?= icon('check', '', 11) ?> <span>Đã duyệt</span></span>
                                <?php elseif ($app['request_status'] === 'rejected'): ?>
                                    <span class="badge badge-danger" style="font-size: 0.7rem; padding: 0.15rem 0.4rem;"><span>Từ chối</span></span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.7rem; padding: 0.15rem 0.4rem;"><span>Chờ duyệt</span></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.25rem; align-items: center;">
                                    <?php if ($app['request_status'] === 'accepted'): ?>
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="pending">
                                            <button type="button" class="btn btn-warning btn-sm" data-reject-prompt="Lý Do Hủy Duyệt Hồ Sơ Ứng Tuyển" style="font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                                                 Hủy Duyệt
                                            </button>
                                        </form>
                                        <a href="<?= url('/employer/meetings') ?>" class="btn btn-outline btn-sm workspace-nav-link" data-title="Lịch Phỏng Vấn" title="Lên lịch phỏng vấn" style="font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                                            Hẹn PV
                                        </a>
                                    <?php elseif ($app['request_status'] === 'pending'): ?>
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="accepted">
                                            <button type="submit" class="btn btn-success btn-sm" style="font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                                                Duyệt
                                            </button>
                                        </form>
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="button" class="btn btn-danger btn-sm" data-reject-prompt="Lý Do Từ Chối Hồ Sơ Người Lao Động" style="font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                                                Từ chối
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="accepted">
                                            <button type="submit" class="btn btn-success btn-sm" style="font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                                                Duyệt Lại
                                            </button>
                                        </form>
                                        <form action="<?= url('/employer/applications/delete/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-confirm="Bạn có chắc chắn muốn xóa hồ sơ ứng tuyển này?" style="font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                                                Xóa
                                            </button>
                                        </form>
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
<!-- MODAL THIẾT LẬP CÀI ĐẶT ĐỘ TƯƠNG THÍCH RIÊNG CỦA NHÀ TUYỂN DỤNG          -->
<!-- ========================================================================= -->
<div id="criteriaModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 10002; justify-content: center; align-items: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 660px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; animation: criteriaModalFadeIn 0.2s ease-out;">
        
        <!-- Header Dialog -->
        <div style="padding: 1rem 1.35rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="color: #0d9488; display: flex; align-items: center;">
                    <?= icon('settings', '', 18) ?>
                </span>
                <h3 style="font-size: 1.08rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Cài Đặt Tiêu Chí Độ Tương Thích Tuyển Dụng
                </h3>
            </div>
            <button type="button" id="closeCriteriaModalBtn" style="background: transparent; border: none; font-size: 1.5rem; line-height: 1; color: #94a3b8; cursor: pointer; padding: 0.2rem 0.4rem; border-radius: 6px; transition: color 0.15s;" title="Đóng">&times;</button>
        </div>

        <!-- Body Dialog: Scrollable -->
        <div style="padding: 0 1.35rem 1rem 1.35rem; overflow-y: auto; flex: 1;">
            
            <!-- MỤC THÊM TIÊU CHÍ MỚI (NẰM TRÊN CÙNG & STICKY KHI CUỘN) -->
            <div style="position: sticky; top: 0; z-index: 20; background: #ffffff; padding: 1rem 0 0.85rem 0; margin-bottom: 0.85rem; border-bottom: 1px solid #f1f5f9; box-shadow: 0 4px 6px -4px rgba(0, 0, 0, 0.04);">
                <div style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 0.75rem 0.9rem;">
                    <div style="font-size: 0.82rem; font-weight: 800; color: #0f172a; margin-bottom: 0.45rem; display: flex; align-items: center; justify-content: space-between;">
                        <span style="display: flex; align-items: center; gap: 0.35rem;">
                            <?= icon('plus', '', 14) ?>
                            <span>Thêm Tiêu Chí Tuyển Dụng Mới Riêng Cho Doanh Nghiệp:</span>
                        </span>
                        <span id="dialogCriteriaActiveSummary" style="font-size: 0.72rem; font-weight: 700; color: #0d9488; background: #ffffff; padding: 0.15rem 0.5rem; border-radius: 6px; border: 1px solid #99f6e4;">
                            Đang chọn: <?= count(array_filter($employerCriteriaList ?? [], fn($c) => !empty($c['is_active']))) ?>/<?= count($employerCriteriaList ?? []) ?>
                        </span>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        <input type="text" id="newCriteriaNameInput" placeholder="Tên tiêu chí mới (Ví dụ: Có chứng chỉ an toàn, Tiếng Trung giao tiếp...)" style="flex: 1.2; min-width: 220px; padding: 0.45rem 0.7rem; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 0.82rem; outline: none; background: #ffffff;">
                        <input type="text" id="newCriteriaDescInput" placeholder="Mô tả tiêu chuẩn kỳ vọng (không bắt buộc)" style="flex: 1; min-width: 170px; padding: 0.45rem 0.7rem; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 0.8rem; outline: none; background: #ffffff;">
                        <button type="button" id="btnSubmitAddCriteria" class="btn btn-primary btn-sm" style="font-size: 0.8rem; font-weight: 700; padding: 0.45rem 0.85rem; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                            <?= icon('plus', '', 13) ?>
                            <span>Thêm Tiêu Chí</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- DANH SÁCH TIÊU CHÍ HIỆN TẠI -->
            <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.6rem; display: flex; justify-content: space-between; align-items: center;">
                    <span>DANH SÁCH TIÊU CHÍ ĐỐI SOÁT ỨNG VIÊN:</span>
                    <button type="button" id="btnResetDefaultCriteria" style="background: none; border: none; color: #0d9488; font-size: 0.74rem; font-weight: 600; text-decoration: underline; cursor: pointer; padding: 0;">
                        Khôi phục mặc định
                    </button>
                </div>

                <div id="criteriaListContainer" style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <?php if (empty($employerCriteriaList)): ?>
                        <div id="criteriaEmptyNotice" style="text-align: center; padding: 1.5rem; color: #64748b; font-size: 0.82rem; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                            Chưa có tiêu chí nào. Bấm nút "Khôi phục mặc định" bên trên hoặc thêm tiêu chí mới bên dưới.
                        </div>
                    <?php else: ?>
                        <?php foreach ($employerCriteriaList as $item): ?>
                            <div class="criteria-item-row <?= empty($item['is_active']) ? 'is-inactive' : '' ?>" id="criteria-row-<?= $item['criteria_id'] ?>" data-id="<?= $item['criteria_id'] ?>" style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.65rem 0.8rem; background: <?= !empty($item['is_active']) ? '#ffffff' : '#f8fafc' ?>; border: 1px solid <?= !empty($item['is_active']) ? '#cbd5e1' : '#e2e8f0' ?>; border-radius: 8px; transition: all 0.2s;">
                                
                                <!-- Ô Checkbox & Tên tiêu chí -->
                                <label style="display: flex; align-items: flex-start; gap: 0.65rem; flex: 1; cursor: pointer; margin: 0;">
                                    <input type="checkbox" class="criteria-checkbox" data-id="<?= $item['criteria_id'] ?>" <?= !empty($item['is_active']) ? 'checked' : '' ?> style="width: 17px; height: 17px; accent-color: #0d9488; margin-top: 2px; cursor: pointer;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                            <span class="criteria-title-text" style="font-size: 0.86rem; font-weight: 700; color: <?= !empty($item['is_active']) ? '#0f172a' : '#64748b' ?>;">
                                                <?= htmlspecialchars($item['criteria_name']) ?>
                                            </span>
                                            <?php if (!empty($item['is_custom'])): ?>
                                                <span style="background: #e0f2fe; color: #0284c7; font-size: 0.65rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 4px; border: 1px solid #bae6fd;">
                                                    Tiêu chí riêng
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($item['criteria_desc'])): ?>
                                            <div style="font-size: 0.74rem; color: #64748b; margin-top: 2px; line-height: 1.35;">
                                                <?= htmlspecialchars($item['criteria_desc']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </label>

                                <!-- Nút hiển thị trạng thái lọc & Nút xoá tiêu chí -->
                                <div style="display: flex; align-items: center; gap: 0.45rem; flex-shrink: 0;">
                                    <span class="criteria-badge-status badge" style="font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 5px; background: <?= !empty($item['is_active']) ? '#ecfdf5' : '#f1f5f9' ?>; color: <?= !empty($item['is_active']) ? '#065f46' : '#64748b' ?>; border: 1px solid <?= !empty($item['is_active']) ? '#a7f3d0' : '#e2e8f0' ?>;">
                                        <?= !empty($item['is_active']) ? '✓ Đang lọc' : '○ Bỏ lọc' ?>
                                    </span>
                                    <button type="button" class="btn-delete-criterion" data-id="<?= $item['criteria_id'] ?>" data-name="<?= htmlspecialchars($item['criteria_name']) ?>" title="Xóa tiêu chí này" style="background: #dc2626; border: 1px solid #dc2626; color: #ffffff; width: 30px; height: 30px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                                        <?= icon('trash', '', 14) ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Footer Dialog -->
        <div style="padding: 0.85rem 1.35rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; align-items: center; gap: 0.5rem; background: #f8fafc;">
            <button type="button" id="btnCancelCriteriaModal" class="btn btn-outline btn-sm" style="font-size: 0.8rem; padding: 0.4rem 0.85rem;">
                Đóng
            </button>
            <button type="button" id="btnApplyCriteriaAndReload" class="btn btn-primary btn-sm" style="font-size: 0.8rem; font-weight: 700; padding: 0.4rem 1.15rem; display: inline-flex; align-items: center; gap: 4px;">
                <?= icon('check', '', 13) ?>
                <span>Xác nhận</span>
            </button>
        </div>

    </div>
</div>

<!-- MODAL MỜI NHẬN VIỆC / PHỎNG VẤN TRÊN TRANG CHỦ DASHBOARD -->
<div id="dashInviteModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 10001; justify-content: center; align-items: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                <?= icon('mail', 'text-primary', 18) ?> Gửi Lời Mời Làm Việc
            </h3>
            <button type="button" id="closeDashInviteBtn" style="background: none; border: none; font-size: 1.35rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <div style="margin-bottom: 1rem; background: #f0fdfa; border: 1px solid #ccfbf1; padding: 0.75rem; border-radius: 8px;">
            Người lao động: <strong id="dashModalSeekerName" style="color: #0d9488;"></strong><br>
            <span style="font-size: 0.8rem; color: #0f766e;">Vị trí tuyển dụng: <strong id="dashModalJobTitle"><?= htmlspecialchars($activeJobs[0]['job_title'] ?? 'Công việc đang tuyển') ?></strong></span>
        </div>

        <form action="<?= url('/employer/candidates/invite') ?>" method="POST" id="dashInviteForm">
            <input type="hidden" name="seeker_id" id="dashModalSeekerId">
            <input type="hidden" name="job_post_id" id="dashModalJobId" value="<?= $selectedJobId ?>">

            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">
                    Lời nhắn / Lời mời gửi tới người lao động:
                </label>
                <textarea name="invitation_note" rows="3" class="form-control" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">Chào bạn, doanh nghiệp chúng tôi rất ấn tượng với kỹ năng và hồ sơ năng lực của bạn. Trân trọng mời bạn tham gia ứng tuyển và phỏng vấn vị trí này!</textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                <button type="button" id="cancelDashInviteBtn" class="btn btn-outline btn-sm">Đóng</button>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <?= icon('mail_check', '', 15) ?> Gửi Lời Mời
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDashUnqualified() {
    const list = document.getElementById('dashUnqualifiedContainer');
    const btnText = document.getElementById('dashToggleText');
    const icon = document.getElementById('dashToggleIcon');
    if (!list) return;

    const count = document.getElementById('dashBadgeCountUnqualified') ? document.getElementById('dashBadgeCountUnqualified').textContent : '0';

    if (list.style.display === 'none' || list.style.display === '') {
        list.style.display = 'grid';
        if (btnText) btnText.textContent = 'Thu Gọn (' + count + ')';
        if (icon) icon.textContent = '▲';
    } else {
        list.style.display = 'none';
        if (btnText) btnText.textContent = 'Mở Rộng (' + count + ')';
        if (icon) icon.textContent = '▼';
    }
}

function applyDashThreshold(threshold) {
    threshold = parseInt(threshold, 10);
    if (isNaN(threshold) || threshold < 30) threshold = 70;
    if (threshold > 100) threshold = 100;

    document.querySelectorAll('.dash-threshold-val').forEach(el => el.textContent = threshold + '%');
    const badge = document.getElementById('dashThresholdBadge');
    if (badge) badge.textContent = threshold + '%';
    const slider = document.getElementById('dashThresholdSlider');
    if (slider) slider.value = threshold;
    const formInput = document.getElementById('dashFormThresholdInput');
    if (formInput) formInput.value = threshold;

    document.querySelectorAll('.dash-preset').forEach(btn => {
        const pVal = parseInt(btn.getAttribute('data-preset'), 10);
        if (pVal === threshold) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    try {
        localStorage.setItem('joblink_employer_threshold', threshold);
    } catch(e) {}

    const allCards = document.querySelectorAll('.dash-cand-card');
    const qualifiedList = document.getElementById('dashQualifiedContainer');
    const unqualifiedList = document.getElementById('dashUnqualifiedContainer');
    const emptyQualified = document.getElementById('dashEmptyQualified');
    const emptyUnqualified = document.getElementById('dashEmptyUnqualified');

    if (!qualifiedList || !unqualifiedList) return;

    let countQual = 0;
    let countUnqual = 0;

    allCards.forEach(card => {
        const score = parseInt(card.getAttribute('data-score'), 10) || 0;
        const statusBadge = card.querySelector('.dash-status-badge');

        if (score >= threshold) {
            countQual++;
            card.classList.remove('unqualified-card');
            card.classList.add('qualified-card');
            if (statusBadge) {
                statusBadge.className = 'dash-status-badge badge';
                statusBadge.style.background = score >= 85 ? '#ecfdf5' : '#f0fdfa';
                statusBadge.style.color = score >= 85 ? '#065f46' : '#0d9488';
                statusBadge.style.borderColor = score >= 85 ? '#a7f3d0' : '#ccfbf1';
                statusBadge.textContent = score + '%';
            }
            qualifiedList.appendChild(card);
        } else {
            countUnqual++;
            card.classList.remove('qualified-card');
            card.classList.add('unqualified-card');
            if (statusBadge) {
                statusBadge.className = 'dash-status-badge badge unqualified-badge-warning';
                statusBadge.style.background = '#fef3c7';
                statusBadge.style.color = '#b45309';
                statusBadge.style.borderColor = '#fde68a';
                statusBadge.textContent = score + '%';
            }
            unqualifiedList.appendChild(card);
        }
    });

    const bQual = document.getElementById('dashBadgeCountQualified');
    if (bQual) bQual.textContent = countQual;
    const bUnqual = document.getElementById('dashBadgeCountUnqualified');
    if (bUnqual) bUnqual.textContent = countUnqual;
    const cQual = document.getElementById('dashCountQualifiedBadge');
    if (cQual) cQual.textContent = countQual;
    const cUnqual = document.getElementById('dashCountUnqualifiedBadge');
    if (cUnqual) cUnqual.textContent = countUnqual;

    const btnText = document.getElementById('dashToggleText');
    if (btnText) {
        const isExpanded = unqualifiedList.style.display !== 'none' && unqualifiedList.style.display !== '';
        btnText.textContent = (isExpanded ? 'Thu Gọn (' : 'Mở Rộng (') + countUnqual + ')';
    }

    if (emptyQualified) emptyQualified.style.display = countQual === 0 ? 'block' : 'none';
    if (emptyUnqualified) emptyUnqualified.style.display = countUnqual === 0 ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    const dashSlider = document.getElementById('dashThresholdSlider');
    if (dashSlider) {
        dashSlider.addEventListener('input', function() {
            applyDashThreshold(this.value);
        });
    }

    document.querySelectorAll('.dash-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            const pVal = parseInt(this.getAttribute('data-preset'), 10);
            applyDashThreshold(pVal);
        });
    });

    // Modal mời phỏng vấn trên dashboard
    const modal = document.getElementById('dashInviteModal');
    const closeBtn = document.getElementById('closeDashInviteBtn');
    const cancelBtn = document.getElementById('cancelDashInviteBtn');

    document.querySelectorAll('.btn-open-invite-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const sId = this.getAttribute('data-seeker-id');
            const jId = this.getAttribute('data-job-id');
            const sName = this.getAttribute('data-seeker-name');
            const jTitle = this.getAttribute('data-job-title');

            document.getElementById('dashModalSeekerId').value = sId;
            document.getElementById('dashModalJobId').value = jId;
            document.getElementById('dashModalSeekerName').textContent = sName;
            if (jTitle) document.getElementById('dashModalJobTitle').textContent = jTitle;

            if (modal) modal.style.display = 'flex';
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', () => modal.style.display = 'none');
    if (cancelBtn) cancelBtn.addEventListener('click', () => modal.style.display = 'none');

    // =========================================================================
    // QUẢN LÝ TIÊU CHÍ ĐỘ TƯƠNG THÍCH RIÊNG CỦA NHÀ TUYỂN DỤNG
    // =========================================================================
    const criteriaModal = document.getElementById('criteriaModal');
    const btnOpenCriteriaModal = document.getElementById('btnOpenCriteriaModal');
    const closeCriteriaModalBtn = document.getElementById('closeCriteriaModalBtn');
    const btnCancelCriteriaModal = document.getElementById('btnCancelCriteriaModal');
    const btnApplyCriteriaAndReload = document.getElementById('btnApplyCriteriaAndReload');
    const btnSubmitAddCriteria = document.getElementById('btnSubmitAddCriteria');
    const btnResetDefaultCriteria = document.getElementById('btnResetDefaultCriteria');
    const newCriteriaNameInput = document.getElementById('newCriteriaNameInput');
    const newCriteriaDescInput = document.getElementById('newCriteriaDescInput');
    const criteriaListContainer = document.getElementById('criteriaListContainer');
    const activeBadge = document.getElementById('activeCriteriaCountBadge');
    const summaryBadge = document.getElementById('dialogCriteriaActiveSummary');

    let criteriaChanged = false;
    const CRITERIA_API = '<?= url('/api/employer/matching-criteria') ?>';

    function updateCriteriaBadges() {
        const rows = document.querySelectorAll('.criteria-item-row');
        const activeRows = document.querySelectorAll('.criteria-checkbox:checked');
        const total = rows.length;
        const active = activeRows.length;
        if (activeBadge) activeBadge.textContent = active + '/' + total;
        if (summaryBadge) summaryBadge.textContent = 'Đang chọn: ' + active + '/' + total;
    }

    function showToastMsg(msg, type = 'success') {
        if (window.Toast && typeof window.Toast[type] === 'function') {
            window.Toast[type](msg);
        } else if (window.Toast && typeof window.Toast.show === 'function') {
            window.Toast.show(msg, type);
        }
    }

    if (btnOpenCriteriaModal && criteriaModal) {
        btnOpenCriteriaModal.addEventListener('click', function () {
            criteriaModal.style.display = 'flex';
        });
    }

    function closeCritModal() {
        if (criteriaModal) criteriaModal.style.display = 'none';
        if (criteriaChanged) {
            window.location.reload();
        }
    }

    if (closeCriteriaModalBtn) closeCriteriaModalBtn.addEventListener('click', closeCritModal);
    if (btnCancelCriteriaModal) btnCancelCriteriaModal.addEventListener('click', closeCritModal);

    if (btnApplyCriteriaAndReload) {
        btnApplyCriteriaAndReload.addEventListener('click', function () {
            criteriaModal.style.display = 'none';
            window.location.reload();
        });
    }

    // Đóng khi click ra ngoài backdrop
    if (criteriaModal) {
        criteriaModal.addEventListener('click', function (e) {
            if (e.target === criteriaModal) {
                closeCritModal();
            }
        });
    }

    // Bật / Tắt Checkbox tiêu chí (chọn hoặc bỏ chọn lọc)
    function attachCriteriaEventListeners(row) {
        const chk = row.querySelector('.criteria-checkbox');
        const delBtn = row.querySelector('.btn-delete-criterion');
        const cid = row.getAttribute('data-id');

        if (chk) {
            chk.addEventListener('change', function () {
                const isChecked = this.checked;
                const statusBadge = row.querySelector('.criteria-badge-status');
                const titleText = row.querySelector('.criteria-title-text');

                if (isChecked) {
                    row.classList.remove('is-inactive');
                    row.style.background = '#ffffff';
                    row.style.borderColor = '#cbd5e1';
                    if (titleText) titleText.style.color = '#0f172a';
                    if (statusBadge) {
                        statusBadge.style.background = '#ecfdf5';
                        statusBadge.style.color = '#065f46';
                        statusBadge.style.borderColor = '#a7f3d0';
                        statusBadge.textContent = '✓ Đang lọc';
                    }
                } else {
                    row.classList.add('is-inactive');
                    row.style.background = '#f8fafc';
                    row.style.borderColor = '#e2e8f0';
                    if (titleText) titleText.style.color = '#64748b';
                    if (statusBadge) {
                        statusBadge.style.background = '#f1f5f9';
                        statusBadge.style.color = '#64748b';
                        statusBadge.style.borderColor = '#e2e8f0';
                        statusBadge.textContent = '○ Bỏ lọc';
                    }
                }

                updateCriteriaBadges();
                criteriaChanged = true;

                fetch(CRITERIA_API + '/toggle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ criteria_id: cid, is_active: isChecked ? 1 : 0 })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToastMsg(isChecked ? 'Đã kích hoạt lọc theo tiêu chí' : 'Đã tắt lọc tiêu chí');
                    } else {
                        showToastMsg(data.message || 'Lỗi cập nhật tiêu chí', 'error');
                    }
                })
                .catch(() => {
                    showToastMsg('Lỗi kết nối máy chủ', 'error');
                });
            });
        }

        if (delBtn) {
            delBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const name = this.getAttribute('data-name') || 'tiêu chí này';
                if (!confirm(`Bạn có chắc chắn muốn xóa "${name}" khỏi danh sách tiêu chí của doanh nghiệp?`)) {
                    return;
                }

                fetch(CRITERIA_API + '/delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ criteria_id: cid })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        row.style.transition = 'all 0.25s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            row.remove();
                            updateCriteriaBadges();
                        }, 250);
                        criteriaChanged = true;
                        showToastMsg('Đã xóa tiêu chí thành công');
                    } else {
                        showToastMsg(data.message || 'Không thể xóa tiêu chí', 'error');
                    }
                })
                .catch(() => {
                    showToastMsg('Lỗi kết nối máy chủ', 'error');
                });
            });
        }
    }

    document.querySelectorAll('.criteria-item-row').forEach(row => {
        attachCriteriaEventListeners(row);
    });

    // Thêm tiêu chí mới
    function submitAddCriteria() {
        if (!newCriteriaNameInput) return;
        const name = newCriteriaNameInput.value.trim();
        const desc = newCriteriaDescInput ? newCriteriaDescInput.value.trim() : '';

        if (!name) {
            showToastMsg('Vui lòng nhập tên tiêu chí mới!', 'warning');
            newCriteriaNameInput.focus();
            return;
        }

        if (btnSubmitAddCriteria) btnSubmitAddCriteria.disabled = true;

        fetch(CRITERIA_API + '/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ criteria_name: name, criteria_desc: desc })
        })
        .then(res => res.json())
        .then(data => {
            if (btnSubmitAddCriteria) btnSubmitAddCriteria.disabled = false;
            if (data.success && data.criterion) {
                const c = data.criterion;
                const newRow = document.createElement('div');
                newRow.className = 'criteria-item-row';
                newRow.id = 'criteria-row-' + c.criteria_id;
                newRow.setAttribute('data-id', c.criteria_id);
                newRow.style.cssText = 'display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.65rem 0.8rem; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; transition: all 0.2s;';

                const escapeHtml = str => String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));

                newRow.innerHTML = `
                    <label style="display: flex; align-items: flex-start; gap: 0.65rem; flex: 1; cursor: pointer; margin: 0;">
                        <input type="checkbox" class="criteria-checkbox" data-id="${c.criteria_id}" checked style="width: 17px; height: 17px; accent-color: #0d9488; margin-top: 2px; cursor: pointer;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                <span class="criteria-title-text" style="font-size: 0.86rem; font-weight: 700; color: #0f172a;">
                                    ${escapeHtml(c.criteria_name)}
                                </span>
                                <span style="background: #e0f2fe; color: #0284c7; font-size: 0.65rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 4px; border: 1px solid #bae6fd;">
                                    Tiêu chí riêng
                                </span>
                            </div>
                            ${c.criteria_desc ? `<div style="font-size: 0.74rem; color: #64748b; margin-top: 2px; line-height: 1.35;">${escapeHtml(c.criteria_desc)}</div>` : ''}
                        </div>
                    </label>
                    <div style="display: flex; align-items: center; gap: 0.45rem; flex-shrink: 0;">
                        <span class="criteria-badge-status badge" style="font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 5px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                            ✓ Đang lọc
                        </span>
                        <button type="button" class="btn-delete-criterion" data-id="${c.criteria_id}" data-name="${escapeHtml(c.criteria_name)}" title="Xóa tiêu chí này" style="background: #dc2626; border: 1px solid #dc2626; color: #ffffff; width: 30px; height: 30px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                    </div>
                `;

                const emptyNotice = document.getElementById('criteriaEmptyNotice');
                if (emptyNotice) emptyNotice.remove();

                criteriaListContainer.prepend(newRow);
                attachCriteriaEventListeners(newRow);
                updateCriteriaBadges();

                newCriteriaNameInput.value = '';
                if (newCriteriaDescInput) newCriteriaDescInput.value = '';
                criteriaChanged = true;
                showToastMsg('Đã thêm tiêu chí mới thành công!');
            } else {
                showToastMsg(data.message || 'Lỗi khi thêm tiêu chí', 'error');
            }
        })
        .catch(() => {
            if (btnSubmitAddCriteria) btnSubmitAddCriteria.disabled = false;
            showToastMsg('Lỗi kết nối máy chủ', 'error');
        });
    }

    if (btnSubmitAddCriteria) {
        btnSubmitAddCriteria.addEventListener('click', submitAddCriteria);
    }
    if (newCriteriaNameInput) {
        newCriteriaNameInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitAddCriteria();
            }
        });
    }

    // Khôi phục mặc định
    if (btnResetDefaultCriteria) {
        btnResetDefaultCriteria.addEventListener('click', function () {
            if (!confirm('Khôi phục toàn bộ danh sách tiêu chí về mặc định ban đầu của hệ thống?')) {
                return;
            }

            fetch(CRITERIA_API + '/reset', {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToastMsg('Đã khôi phục bộ tiêu chí mặc định!');
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    showToastMsg(data.message || 'Lỗi khôi phục mặc định', 'error');
                }
            })
            .catch(() => {
                showToastMsg('Lỗi kết nối máy chủ', 'error');
            });
        });
    }

    // TÌM KIẾM NHANH VỊ TRÍ TUYỂN DỤNG ĐỐI CHIẾU
    const dashJobSearchInput = document.getElementById('dashJobSearchInput');
    const dashJobSelectDropdown = document.getElementById('dashJobSelectDropdown');
    if (dashJobSearchInput && dashJobSelectDropdown) {
        dashJobSearchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const options = Array.from(dashJobSelectDropdown.options);
            let firstMatch = null;
            options.forEach(opt => {
                if (opt.value === '0') return;
                const text = (opt.getAttribute('data-search') || opt.textContent).toLowerCase();
                const match = term === '' || text.includes(term);
                opt.hidden = !match;
                if (match && !firstMatch) firstMatch = opt;
            });
            if (firstMatch && term !== '' && dashJobSelectDropdown.selectedOptions[0]?.hidden) {
                firstMatch.selected = true;
            }
        });
        dashJobSearchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const firstVisible = Array.from(dashJobSelectDropdown.options).find(opt => !opt.hidden && opt.value !== '0');
                if (firstVisible) {
                    dashJobSelectDropdown.value = firstVisible.value;
                    dashJobSelectDropdown.form.submit();
                }
            }
        });
    }
});

// MODAL GIẢI THÍCH CHI TIẾT ĐỘ TƯƠNG THÍCH % KHI CLICK VÀO %
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
                    ✕ 2. Tiêu chí chưa đạt hoặc cần đào tạo thêm:
                </strong>
                <div id="modalMissingText" style="font-size: 0.82rem; color: #78350f; line-height: 1.45;"></div>
            </div>

            <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 0.85rem;">
                <strong style="color: #0f766e; font-size: 0.85rem; display: flex; align-items: center; gap: 4px; margin-bottom: 0.35rem;">
                    ➔ 3. Kết quả đánh giá & Khuyến nghị hành động:
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
