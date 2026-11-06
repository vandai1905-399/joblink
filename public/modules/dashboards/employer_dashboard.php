<?php 
$extraCss = ['css/applications/employer_applications.css', 'css/jobs/ai_matching.css'];
ob_start(); 
?>

<div class="dashboard-greeting" style="margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 0.5rem;">
    <div>
        <h1 style="font-size: 1.35rem; font-weight: 800; color: var(--dark); margin: 0; line-height: 1.25;">
            Tổng quan tuyển dụng - <?= htmlspecialchars($employer['employer_name'] ?? 'Doanh Nghiệp') ?>
        </h1>
        <p class="greeting-subtitle" style="color: var(--slate-500); font-size: 0.82rem; margin: 0.15rem 0 0 0;">Quản lý tuyển dụng & hồ sơ ứng viên hôm nay</p>
    </div>
</div>

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
            <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.2rem;">
                <span class="badge" style="background: #f0fdfa; color: #0d9488; border: 1px solid #ccfbf1; font-weight: 700; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                    <?= icon('sparkle', '', 12) ?> AI Talent Matching
                </span>
                <span style="font-size: 0.8rem; color: #64748b;">Đề xuất thông minh 2 chiều</span>
            </div>
            <h2 style="font-size: 1.22rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3;">
                Gợi Ý Người Lao Động Phù Hợp Cho Tin Tuyển Dụng
            </h2>
        </div>
        <div style="display: flex; gap: 0.4rem; align-items: center;">
            <a href="<?= url('/employer/candidates/recommended?job_id=' . $selectedJobId . '&threshold=' . $threshold) ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="NLĐ Đề Xuất AI" style="font-size: 0.78rem; font-weight: 700; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 4px;">
                <span>Quản Lý Toàn Bộ NLĐ Đề Xuất</span>
            </a>
        </div>
    </div>

    <!-- BỘ LỌC CÔNG VIỆC VÀ HẠN MỨC % DO NTD TỰ CHỈNH TRÊN TRANG CHỦ -->
    <div class="threshold-box" style="margin-bottom: 1rem; padding: 0.85rem 1rem;">
        <form method="GET" action="<?= url('/employer/dashboard') ?>" id="dashThresholdForm">
            <input type="hidden" name="threshold" id="dashFormThresholdInput" value="<?= $threshold ?>">

            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; justify-content: space-between; margin-bottom: 0.75rem; padding-bottom: 0.65rem; border-bottom: 1px solid #f1f5f9;">
                <div style="flex: 1; min-width: 260px; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: #0d9488; display: flex; align-items: center;">
                        <?= icon('briefcase', '', 18) ?>
                    </span>
                    <div style="flex: 1;">
                        <label style="font-size: 0.76rem; font-weight: 700; color: #475569; display: block; margin-bottom: 0.2rem; text-transform: uppercase;">
                            Chọn vị trí tuyển dụng để đối chiếu ứng viên:
                        </label>
                        <select name="job_id" class="form-control" onchange="this.form.submit()" style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.88rem; font-weight: 600; color: #0f172a; background: #f8fafc;">
                            <?php if (empty($activeJobs)): ?>
                                <option value="0">Chưa có tin tuyển dụng nào đang mở</option>
                            <?php else: ?>
                                <?php foreach ($activeJobs as $j): ?>
                                    <option value="<?= $j['job_post_id'] ?>" <?= $selectedJobId === intval($j['job_post_id']) ? 'selected' : '' ?>>
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

        <div id="dashQualifiedContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 0.85rem;">
            <?php foreach ($qualifiedCandidates as $rec): ?>
                <div class="sourcing-card dash-cand-card qualified-card <?= ($rec['ai_match_score'] >= 85) ? 'featured' : '' ?>" data-score="<?= $rec['ai_match_score'] ?>" data-seeker-id="<?= $rec['seeker_id'] ?>" style="padding: 0.85rem;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.65rem; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.65rem; align-items: center;">
                                <img src="<?= htmlspecialchars(getAvatarUrl($rec['avatar_url'] ?? '', 'JOB_SEEKER', $rec['gender'] ?? '')) ?>" alt="avatar" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $rec['gender'] ?? '') ?>';">
                                <div style="min-width: 0;">
                                    <a href="<?= url('/employer/candidates/' . $rec['seeker_id']) ?>" class="workspace-nav-link" data-title="Hồ sơ: <?= htmlspecialchars($rec['full_name']) ?>" style="font-weight: 700; font-size: 0.96rem; color: #0f172a; text-decoration: none;">
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;"><?= htmlspecialchars($rec['full_name']) ?></span>
                                    </a>
                                    <div style="font-size: 0.74rem; color: #64748b; margin-top: 1px;">
                                        <?= htmlspecialchars($rec['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($rec['date_of_birth']) ? (date('Y') - date('Y', strtotime($rec['date_of_birth']))) . 't' : '' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                    </div>
                                </div>
                            </div>
                            <span class="dash-status-badge badge" style="background: <?= $rec['ai_match_score'] >= 85 ? '#ecfdf5' : '#f0fdfa' ?>; color: <?= $rec['ai_match_score'] >= 85 ? '#065f46' : '#0d9488' ?>; border: 1px solid <?= $rec['ai_match_score'] >= 85 ? '#a7f3d0' : '#ccfbf1' ?>; font-size: 0.74rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 6px; white-space: nowrap;">
                                ✓ <?= $rec['ai_match_score'] ?>% tương thích
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

                        <?php if (!empty($rec['ai_matched_skills'])): ?>
                            <div style="display: flex; gap: 0.25rem; flex-wrap: wrap; margin-bottom: 0.65rem;">
                                <?php foreach (array_slice($rec['ai_matched_skills'], 0, 3) as $sk): ?>
                                    <span style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 0.68rem; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600;">
                                        <?= htmlspecialchars($sk) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer Thao Tác -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 0.65rem; display: flex; justify-content: space-between; align-items: center; gap: 0.4rem;">
                        <a href="<?= url('/employer/candidates/' . $rec['seeker_id']) ?>" class="workspace-nav-link" data-title="Hồ sơ: <?= htmlspecialchars($rec['full_name']) ?>" style="font-size: 0.78rem; color: #0d9488; font-weight: 700; text-decoration: none;">
                            Hồ sơ
                        </a>
                        <div style="display: flex; gap: 0.35rem;">
                            <a href="<?= url('/employer/messages?partner_id=' . ($rec['account_id'] ?? 0) . '&seeker_id=' . ($rec['seeker_id'] ?? 0) . '&job_id=' . $selectedJobId . '&open_meeting_modal=1') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Tin Nhắn Trao Đổi" style="font-size: 0.74rem; font-weight: 700; padding: 0.25rem 0.6rem; background: #0d9488; border-color: #0d9488;">
                                Hẹn PV
                            </a>
                            <button type="button" class="btn btn-outline btn-sm btn-open-invite-modal"
                                    data-seeker-id="<?= $rec['seeker_id'] ?>"
                                    data-job-id="<?= $selectedJobId ?>"
                                    data-seeker-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                    data-job-title="<?= htmlspecialchars($activeJobs[0]['job_title'] ?? '') ?>"
                                    style="font-size: 0.74rem; font-weight: 700; padding: 0.25rem 0.6rem; color: #0284c7; border-color: #bae6fd;">
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
            <button type="button" class="btn btn-outline btn-sm" id="btnToggleDashUnqualified" style="background: #ffffff; border-color: #fde68a; color: #b45309; font-weight: 700; font-size: 0.74rem; padding: 0.25rem 0.55rem;">
                <span id="dashToggleText">Mở Rộng (<?= count($unqualifiedCandidates) ?>)</span>
                <span id="dashToggleIcon">▼</span>
            </button>
        </div>

        <div id="dashUnqualifiedContainer" style="display: none; margin-top: 0.85rem; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 0.85rem;">
            <?php foreach ($unqualifiedCandidates as $rec): ?>
                <div class="sourcing-card dash-cand-card unqualified-card" data-score="<?= $rec['ai_match_score'] ?>" data-seeker-id="<?= $rec['seeker_id'] ?>" style="padding: 0.85rem;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.65rem; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.65rem; align-items: center;">
                                <img src="<?= htmlspecialchars(getAvatarUrl($rec['avatar_url'] ?? '', 'JOB_SEEKER', $rec['gender'] ?? '')) ?>" alt="avatar" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $rec['gender'] ?? '') ?>';">
                                <div style="min-width: 0;">
                                    <a href="<?= url('/employer/candidates/' . $rec['seeker_id']) ?>" class="workspace-nav-link" data-title="Hồ sơ: <?= htmlspecialchars($rec['full_name']) ?>" style="font-weight: 700; font-size: 0.96rem; color: #0f172a; text-decoration: none;">
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;"><?= htmlspecialchars($rec['full_name']) ?></span>
                                    </a>
                                    <div style="font-size: 0.74rem; color: #64748b; margin-top: 1px;">
                                        <?= htmlspecialchars($rec['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($rec['date_of_birth']) ? (date('Y') - date('Y', strtotime($rec['date_of_birth']))) . 't' : '' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                    </div>
                                </div>
                            </div>
                            <span class="dash-status-badge badge unqualified-badge-warning" style="font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 6px; white-space: nowrap;">
                                ⚠ <?= $rec['ai_match_score'] ?>% (Dưới hạn mức)
                            </span>
                        </div>

                        <div style="background: #fffbeb; border: 1px dashed #fde68a; border-radius: 6px; padding: 0.4rem 0.55rem; margin-bottom: 0.65rem; font-size: 0.72rem; color: #78350f;">
                            <?= !empty($rec['ai_reasons']) ? htmlspecialchars($rec['ai_reasons'][0]) : 'Chưa đạt đủ điểm kỹ năng yêu cầu.' ?>
                        </div>
                    </div>

                    <div style="border-top: 1px solid #f1f5f9; padding-top: 0.65rem; display: flex; justify-content: space-between; align-items: center; gap: 0.4rem;">
                        <a href="<?= url('/employer/candidates/' . $rec['seeker_id']) ?>" class="workspace-nav-link" data-title="Hồ sơ: <?= htmlspecialchars($rec['full_name']) ?>" style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-decoration: none;">
                            Hồ sơ
                        </a>
                        <button type="button" class="btn btn-outline btn-sm btn-open-invite-modal"
                                data-seeker-id="<?= $rec['seeker_id'] ?>"
                                data-job-id="<?= $selectedJobId ?>"
                                data-seeker-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                data-job-title="<?= htmlspecialchars($activeJobs[0]['job_title'] ?? '') ?>"
                                style="font-size: 0.74rem; font-weight: 700; padding: 0.25rem 0.6rem; color: #d97706; border-color: #fde68a;">
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
                                <a href="<?= url('/employer/candidates/' . $app['seeker_id']) ?>" class="workspace-nav-link" data-title="Hồ sơ: <?= htmlspecialchars($app['seeker_name']) ?>" style="text-decoration: none; color: var(--primary); font-weight: 700;" title="Xem hồ sơ người lao động">
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
                statusBadge.innerHTML = '✓ ' + score + '% tương thích';
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
                statusBadge.innerHTML = '⚠ ' + score + '% (Dưới hạn mức)';
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
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>