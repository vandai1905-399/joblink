<?php 
ob_start(); 

if (!isset($employer) && isset($_SESSION['user']['account_id'])) {
    $employer = UserEmployer::findByAccountId($_SESSION['user']['account_id']);
}
$branches = !empty($employer['branches']) ? json_decode($employer['branches'], true) : [];
if (!is_array($branches)) $branches = [];
$workShifts = !empty($employer['work_shifts']) ? json_decode($employer['work_shifts'], true) : [];
if (!is_array($workShifts)) $workShifts = [];

if (!function_exists('parseJobShiftHours')) {
    function parseJobShiftHours($str) {
        $start = ''; $end = ''; $name = '';
        $str = trim((string)$str);
        if (preg_match('/^(.*?)\((.*?)\)$/u', $str, $m)) {
            $name = trim($m[1]);
            $timePart = trim($m[2]);
        } else {
            $name = '';
            $timePart = $str;
        }
        if (preg_match('/^[\d\s:hH\-–\.\/]+$/u', $name)) {
            $name = '';
        }
        if (preg_match_all('/(\d{1,2})(?:[h:H:](\d{1,2})?)?/u', $timePart, $matches, PREG_SET_ORDER)) {
            if (isset($matches[0])) {
                $h = str_pad($matches[0][1], 2, '0', STR_PAD_LEFT);
                $m = !empty($matches[0][2]) ? str_pad($matches[0][2], 2, '0', STR_PAD_LEFT) : '00';
                $start = "$h:$m";
            }
            if (isset($matches[1])) {
                $h = str_pad($matches[1][1], 2, '0', STR_PAD_LEFT);
                $m = !empty($matches[1][2]) ? str_pad($matches[1][2], 2, '0', STR_PAD_LEFT) : '00';
                $end = "$h:$m";
            }
        }
        return [
            'name' => $name,
            'start' => !empty($start) ? $start : '08:00',
            'end' => !empty($end) ? $end : '17:00'
        ];
    }
}
$currentShiftParsed = parseJobShiftHours($job['work_shift'] ?? '');

$isNoAgeLimit = (isset($job) && empty($job['min_age']) && empty($job['max_age']));
$currentGenderReq = $job['gender_requirement'] ?? 'all';
$currentEduLevel = $job['education_level'] ?? 'none';
$currentExpReq = $job['experience_required'] ?? 'none';
$currentOtherReq = [];
if (!empty($job['other_requirements'])) {
    if (is_array($job['other_requirements'])) {
        $currentOtherReq = $job['other_requirements'];
    } else {
        $decoded = json_decode($job['other_requirements'], true);
        $currentOtherReq = is_array($decoded) ? $decoded : explode(',', $job['other_requirements']);
    }
}
$currentOtherReq = array_map('trim', $currentOtherReq);

$currentMatchingCriteria = [];
if (!empty($job['matching_criteria'])) {
    if (is_array($job['matching_criteria'])) {
        $currentMatchingCriteria = $job['matching_criteria'];
    } else {
        $decoded = json_decode($job['matching_criteria'], true);
        $currentMatchingCriteria = is_array($decoded) ? $decoded : [];
    }
}
$currentPriorityFocus = $currentMatchingCriteria['priority_focus'] ?? 'auto';
$isStrictGender = !empty($currentMatchingCriteria['strict_gender']);
$isStrictAge = !empty($currentMatchingCriteria['strict_age']);
$isStrictEdu = !empty($currentMatchingCriteria['strict_education']);
$isStrictExp = !empty($currentMatchingCriteria['strict_experience']);
$customMatchingNotes = $currentMatchingCriteria['custom_notes'] ?? '';
?>

<style>
    .job-form-grid {
        display: grid;
        grid-template-columns: 1.15fr 1fr;
        gap: 1.35rem;
        align-items: start;
    }
    @media (max-width: 1080px) {
        .job-form-grid {
            grid-template-columns: 1fr !important;
        }
    }
    .form-box-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.35rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .form-box-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.98rem;
        margin-bottom: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.45rem;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 0.65rem;
    }

    /* Full-time 7-Day & Perk Checkbox styling */
    .btn-ft-day {
        padding: 0.6rem 0.2rem;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.2rem;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
    }
    .btn-ft-day:hover {
        border-color: #0d9488;
        background: #f0fdfa;
        color: #0d9488;
    }
    .btn-ft-day.active {
        background: #0d9488;
        border-color: #0d9488;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(13, 148, 136, 0.25);
    }
    .btn-ft-day .ft-day-title {
        font-weight: 700;
        font-size: 0.85rem;
    }
    .btn-ft-day .ft-day-sub {
        font-size: 0.68rem;
        color: #64748b;
    }
    .btn-ft-day.active .ft-day-title {
        color: #ffffff;
    }
    .btn-ft-day.active .ft-day-sub {
        color: #ccfbf1;
        font-weight: 600;
    }

    .ft-perk-card {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        padding: 0.6rem 0.75rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
        margin-bottom: 0;
    }
    .ft-perk-card:hover {
        border-color: #99f6e4;
        background: #f0fdfa;
    }
    .ft-perk-card.active {
        border-color: #0d9488;
        background: #f0fdfa;
        box-shadow: 0 1px 3px rgba(13, 148, 136, 0.1);
    }
    .ft-perk-card input[type="checkbox"] {
        accent-color: #0d9488;
        width: 17px;
        height: 17px;
        margin-top: 2px;
        cursor: pointer;
    }
    .ft-perk-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #0d9488;
        margin-right: 0.2rem;
        vertical-align: middle;
    }
    .ft-perk-card:not(.active) .ft-perk-icon {
        color: #64748b;
    }

    /* Candidate Requirements Card & Pills */
    .req-trait-card {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.55rem 0.75rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
        margin-bottom: 0;
    }
    .req-trait-card:hover {
        border-color: #99f6e4;
        background: #f0fdfa;
    }
    .req-trait-card.active {
        border-color: #0d9488;
        background: #f0fdfa;
        box-shadow: 0 1px 3px rgba(13, 148, 136, 0.1);
    }
    .req-trait-card input[type="checkbox"] {
        accent-color: #0d9488;
        width: 16px;
        height: 16px;
        margin-top: 2px;
        cursor: pointer;
    }
    .req-trait-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #0d9488;
        margin-right: 0.2rem;
        vertical-align: middle;
    }
    .req-trait-card:not(.active) .req-trait-icon {
        color: #64748b;
    }
    .gender-pill-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        padding: 0.55rem 0.3rem;
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        font-size: 0.82rem;
        font-weight: 700;
        transition: all 0.15s ease;
        user-select: none;
    }
    .gender-pill-btn:hover {
        border-color: #99f6e4;
        background: #f8fafc;
    }
    .gender-pill-btn.active {
        border-color: #0d9488 !important;
        background: #f0fdfa !important;
        color: #0d9488 !important;
        box-shadow: 0 1px 3px rgba(13, 148, 136, 0.12);
    }
    .btn-age-preset {
        padding: 3px 8px;
        font-size: 0.74rem;
        border-radius: 5px;
        border: 1px solid #cbd5e1;
        background: #f1f5f9;
        color: #334155;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.15s ease;
    }
    .btn-age-preset:hover {
        border-color: #94a3b8;
        color: #0f172a;
        background: #e2e8f0;
    }
    .btn-age-preset.active {
        border-color: #0d9488 !important;
        background: #0d9488 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    /* Đồng bộ giao diện nút gợi ý mức lương: Tuyệt đối KHÔNG dùng nền trắng chữ màu */
    .btn-preset-salary {
        padding: 3px 10px;
        font-size: 0.75rem;
        border-radius: 6px;
        border: 1px solid #475569;
        background: #475569;
        color: #ffffff !important;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        white-space: nowrap;
        height: 26px;
        line-height: 1;
        flex-shrink: 0;
    }
    .btn-preset-salary:hover {
        background: #334155;
        color: #ffffff !important;
        border-color: #334155;
    }
    .btn-preset-salary.recommended {
        background: #0d9488 !important;
        border-color: #0d9488 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(13, 148, 136, 0.25);
    }
    .btn-preset-salary.recommended:hover {
        background: #0f766e !important;
        border-color: #0f766e !important;
    }
    .btn-preset-salary.recommended svg {
        stroke: #ffffff !important;
    }

    .btn-dyn-zero {
        padding: 2px 9px;
        font-size: 0.75rem;
        border-radius: 6px;
        border: 1px solid #0284c7;
        background: #0284c7;
        color: #ffffff !important;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
        height: 24px;
        display: inline-flex;
        align-items: center;
    }
    .btn-dyn-zero:hover {
        background: #0369a1;
        border-color: #0369a1;
        color: #ffffff !important;
    }

    .btn-salary-action {
        padding: 3px 9px;
        font-size: 0.73rem;
        border-radius: 6px;
        border: 1px solid #475569;
        background: #475569;
        cursor: pointer;
        font-weight: 700;
        color: #ffffff !important;
        flex-shrink: 0;
        line-height: 1.4;
        height: 26px;
        display: inline-flex;
        align-items: center;
        transition: all 0.15s ease;
    }
    .btn-salary-action:hover {
        background: #334155;
        border-color: #334155;
        color: #ffffff !important;
    }

    .btn-salary-view-similar {
        padding: 3px 9px;
        font-size: 0.73rem;
        border-radius: 6px;
        border: 1px solid #0f766e;
        background: #0f766e;
        cursor: pointer;
        font-weight: 700;
        color: #ffffff !important;
        flex-shrink: 0;
        line-height: 1.4;
        height: 26px;
        display: inline-flex;
        align-items: center;
        transition: all 0.15s ease;
    }
    .btn-salary-view-similar:hover {
        background: #115e59;
        border-color: #115e59;
        color: #ffffff !important;
    }
    .btn-salary-view-similar:hover {
        background: #0f766e;
        border-color: #0f766e;
    }

    /* Part-time & Temporary Month Calendar Widget */
    #joblink_calendar_widget {
        user-select: none;
    }
    .cal-day-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 38px;
    }
    .cal-day-circle {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        cursor: pointer;
        font-size: 0.88rem;
        user-select: none;
        transition: all 0.15s ease;
        font-weight: 500;
    }
    .cal-day-circle.adjacent-month {
        color: #94a3b8;
        font-weight: 400;
    }
    .cal-day-circle.current-month {
        color: #0f172a;
    }
    .cal-day-circle:hover {
        background: #f0fdfa;
        color: #0d9488;
    }
    .cal-day-circle.is-today:not(.selected) {
        border: 1.5px solid #0d9488;
        color: #0d9488;
        font-weight: 700;
    }
    .cal-day-circle.selected {
        background: #0d9488 !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(13, 148, 136, 0.4);
    }
    .cal-chip-item {
        font-size: 0.75rem;
        padding: 0.15rem 0.5rem;
        border-radius: 12px;
        background: #f0fdfa;
        color: #0f766e;
        border: 1px solid #99f6e4;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .cal-chip-remove {
        cursor: pointer;
        font-weight: 700;
        color: #0d9488;
    }
    .cal-chip-remove:hover {
        color: #b91c1c;
    }
    #btn_cal_prev_month:hover, #btn_cal_next_month:hover {
        background: #f0fdfa !important;
        color: #0d9488 !important;
    }
</style>

<div style="width: 100%; max-width: 1350px; margin: 0 auto; padding-bottom: 2.5rem;">
    <!-- Header trang & Nút hành động nhanh -->
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.65rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">
                <?= isset($job) ? 'Chỉnh Sửa Tin Tuyển Dụng' : 'Đăng Tin Tuyển Dụng Mới' ?>
            </h1>
            <p style="color: var(--slate-500); font-size: 0.92rem; margin: 0;">Điền đầy đủ thông tin vị trí, chế độ đãi ngộ và ca kíp làm việc</p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <a href="<?= url('/employer/jobs') ?>" class="btn btn-outline" style="padding: 0.6rem 1.25rem; font-size: 0.9rem;">Hủy Bỏ</a>
            <button type="submit" form="jobPostForm" class="btn btn-primary" style="padding: 0.6rem 1.5rem; font-size: 0.9rem; font-weight: 700; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);">
                <?= isset($job) ? 'Lưu Thay Đổi' : 'Đăng Tin Tuyển Dụng' ?>
            </button>
        </div>
    </div>

    <form action="<?= isset($job) ? url('/employer/jobs/update/' . $job['job_post_id']) : url('/employer/jobs/store') ?>" method="POST" id="jobPostForm">
        <div class="job-form-grid">
            
            <!-- CỘT TRÁI: VỊ TRÍ, LƯƠNG, ĐỊA ĐIỂM, YÊU CẦU & ĐÃI NGỘ -->
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                
                <!-- Khối 1: Chức danh, Hình thức & Mức Lương -->
                <div class="form-box-card">
                    <div class="form-box-title">
                        <?= icon('briefcase', '', 16) ?> <span>Thông Tin Vị Trí & Thu Nhập</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Chức Danh Công Việc *</label>
                        <input type="text" name="job_title" id="job_title_input" value="<?= htmlspecialchars($job['job_title'] ?? '') ?>" class="form-control" required placeholder="VD: Nhân viên pha chế / Chuyên viên kỹ thuật">
                    </div>

                    <div class="form-row" style="align-items: flex-start;">
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label class="form-label">Hình Thức Làm Việc *</label>
                            <select name="employment_type" id="employment_type_select" class="form-control" required>
                                <option value="full_time" <?= ($job['employment_type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Toàn thời gian (Full-time)</option>
                                <option value="part_time" <?= ($job['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Bán thời gian / Theo ca (Part-time)</option>
                                <option value="temporary" <?= ($job['employment_type'] ?? '') === 'temporary' ? 'selected' : '' ?>>Thời vụ (Temporary)</option>
                            </select>
                            <div style="font-size: 0.76rem; color: #64748b; margin-top: 0.35rem; height: 26px; display: flex; align-items: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                Lịch làm việc tự động đổi theo hình thức
                            </div>
                        </div>
                        <div class="form-group" style="flex: 1.3; margin-bottom: 0;">
                            <label class="form-label" id="salary_label">Mức Lương (VNĐ / 1 tháng) *</label>
                            <div style="display: flex; align-items: stretch;">
                                <input type="text" inputmode="numeric" name="salary_amount" id="salary_input" 
                                       value="<?= !empty($job['salary_amount']) ? number_format((float)$job['salary_amount'], 0, ',', '.') : '' ?>" 
                                       class="form-control" required placeholder="VD: 15.000.000" 
                                       style="border-top-right-radius: 0; border-bottom-right-radius: 0; font-weight: 700; font-size: 1rem; color: #0f172a;">
                                <span id="salary_addon" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-left: none; padding: 0.5rem 0.95rem; display: inline-flex; align-items: center; font-weight: 700; color: #0d9488; font-size: 0.88rem; border-top-right-radius: 8px; border-bottom-right-radius: 8px; white-space: nowrap; user-select: none;">
                                    VNĐ / 1 tháng
                                </span>
                            </div>

                            <!-- Gợi ý mức lương thông minh & tinh gọn cho NTD - Đồng bộ nút solid, không nền trắng chữ màu -->
                            <div id="salary_quick_wrapper" style="margin-top: 0.4rem; display: flex; align-items: center; justify-content: flex-start; gap: 0.4rem; flex-wrap: wrap; min-height: 26px;">
                                <span style="font-size: 0.75rem; color: #475569; font-weight: 700; display: inline-flex; align-items: center; gap: 0.2rem; flex-shrink: 0; white-space: nowrap;">
                                    <?= icon('sparkle', '', 12) ?> <span>Gợi ý lương:</span>
                                </span>
                                <!-- Các nút mức lương thông minh -->
                                <div id="preset_buttons" style="display: inline-flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;"></div>
                                <button type="button" id="btn_view_similar_jobs" class="btn-salary-view-similar" style="display: none;" title="Xem các tin có tiêu chí tương tự">
                                    Xem thêm
                                </button>
                                <button type="button" id="btn_clear_salary" class="btn-salary-action" title="Xóa ô nhập mức lương">
                                    Xóa
                                </button>
                            </div>

                            <!-- Gợi ý số 0 nhanh khi người dùng gõ số -->
                            <div id="dynamic_zero_box" style="display: none; margin-top: 0.35rem; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                <span style="font-size: 0.73rem; color: #475569; font-weight: 700; flex-shrink: 0;">Gợi ý nhanh:</span>
                                <div id="dynamic_zero_suggestions" style="display: inline-flex; gap: 0.3rem; flex-wrap: wrap;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Khối 2: Địa điểm / Cơ sở làm việc -->
                <div class="form-box-card">
                    <div class="form-box-title">
                        <?= icon('building', '', 16) ?> <span>Địa Chỉ / Cơ Sở Làm Việc *</span>
                    </div>
                    <div style="font-size: 0.82rem; color: #64748b; margin-bottom: 0.75rem;">
                        Chọn nhanh từ trụ sở chính, các chi nhánh đã lưu của công ty hoặc tự nhập địa chỉ mới
                    </div>

                    <select id="workplace_select" class="form-control" style="margin-bottom: 0.6rem; background: #fff; font-weight: 600; color: #0f172a;">
                        <?php if (!empty($employer['address'])): ?>
                            <option value="<?= htmlspecialchars($employer['address']) ?>" data-map-url="<?= htmlspecialchars($employer['map_url'] ?? '') ?>" <?= (!isset($job) || ($job['workplace_address'] ?? '') === $employer['address']) ? 'selected' : '' ?>>
                                [Trụ sở chính]: <?= htmlspecialchars($employer['address']) ?>
                            </option>
                        <?php endif; ?>
                        <?php foreach ($branches as $idx => $br): ?>
                            <?php if (!empty($br['address'])): ?>
                                <option value="<?= htmlspecialchars($br['address']) ?>" data-map-url="<?= htmlspecialchars($br['map_url'] ?? '') ?>" <?= (isset($job) && ($job['workplace_address'] ?? '') === $br['address']) ? 'selected' : '' ?>>
                                    [<?= htmlspecialchars($br['name'] ?? ('Cơ sở ' . ($idx + 1))) ?>]: <?= htmlspecialchars($br['address']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <option value="custom" data-map-url="" <?= (isset($job) && !empty($job['workplace_address']) && ($job['workplace_address'] !== ($employer['address'] ?? '')) && !in_array($job['workplace_address'], array_column($branches, 'address'))) ? 'selected' : '' ?>>
                            Tự nhập địa chỉ khác...
                        </option>
                    </select>

                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="text" name="workplace_address" id="workplace_address_input" value="<?= htmlspecialchars($job['workplace_address'] ?? ($employer['address'] ?? '')) ?>" class="form-control" required placeholder="Địa chỉ chi nhánh hoặc cơ sở làm việc">
                        <?php 
                            $initialMapUrl = !empty($job['workplace_map_url']) ? $job['workplace_map_url'] : (!empty($employer['map_url']) ? $employer['map_url'] : getGoogleMapsUrl($job['workplace_address'] ?? ($employer['address'] ?? '')));
                        ?>
                        <a id="btn_preview_map" href="<?= htmlspecialchars($initialMapUrl) ?>" target="_blank" class="btn btn-outline" style="white-space: nowrap; padding: 0.6rem 0.9rem; font-size: 0.85rem; font-weight: 700; color: #ffffff; border-color: #0d9488; background: #0d9488; display: inline-flex; align-items: center; gap: 0.35rem;" title="Xem vị trí trên Google Maps">
                            <?= icon('location', '', 14) ?> <span>Mở Maps</span>
                        </a>
                    </div>

                    <div id="custom_map_url_box" style="margin-top: 0.5rem; <?= (isset($job) && !empty($job['workplace_map_url']) && !in_array($job['workplace_address'], array_merge([$employer['address'] ?? ''], array_column($branches, 'address')))) ? 'display:block;' : 'display:none;' ?>">
                        <label class="form-label" style="font-size: 0.82rem; color: #64748b; margin-bottom: 0.25rem;">
                            Link Google Maps vị trí làm việc (Tự chọn trên Google Maps):
                        </label>
                        <input type="url" name="workplace_map_url" id="workplace_map_url_input" value="<?= htmlspecialchars($job['workplace_map_url'] ?? ($employer['map_url'] ?? '')) ?>" class="form-control" placeholder="https://maps.app.goo.gl/... hoặc https://maps.google.com/..." style="font-size: 0.88rem;">
                    </div>
                </div>

                <!-- Khối 3: Tiêu Chuẩn & Yêu Cầu Tuyển Dụng -->
                <div class="form-box-card">
                    <div class="form-box-title">
                        <?= icon('user_check', '', 16) ?> <span>Tiêu Chuẩn & Yêu Cầu Tuyển Dụng</span>
                    </div>

                    <!-- Hàng 1: Độ tuổi & Giới tính -->
                    <div class="form-row" style="margin-bottom: 1rem;">
                        <!-- Độ tuổi -->
                        <div class="form-group" style="flex: 1.25; margin-bottom: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.25rem;">
                                <label class="form-label" style="margin-bottom: 0; font-weight: 700; color: #0f172a;">Độ Tuổi Tuyển Dụng</label>
                                <label style="font-size: 0.78rem; color: #475569; display: inline-flex; align-items: center; gap: 0.35rem; cursor: pointer; user-select: none; font-weight: 600;">
                                    <input type="checkbox" name="no_age_limit" id="cb_no_age_limit" value="1" <?= $isNoAgeLimit ? 'checked' : '' ?> style="accent-color: #0d9488; width: 15px; height: 15px; cursor: pointer;">
                                    <span>Mọi lứa tuổi (Không giới hạn)</span>
                                </label>
                            </div>

                            <!-- Ô nhập min_age và max_age -->
                            <div id="age_inputs_box" style="display: flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; <?= $isNoAgeLimit ? 'opacity: 0.45; pointer-events: none;' : '' ?>">
                                <div style="display: flex; align-items: center; flex: 1;">
                                    <span style="background: #f1f5f9; border: 1px solid #cbd5e1; border-right: none; padding: 0.45rem 0.65rem; font-size: 0.82rem; color: #64748b; font-weight: 600; border-top-left-radius: 8px; border-bottom-left-radius: 8px; white-space: nowrap;">Từ</span>
                                    <input type="number" name="min_age" id="min_age_input" min="15" max="75" placeholder="18" value="<?= !empty($job['min_age']) ? htmlspecialchars($job['min_age']) : ($isNoAgeLimit ? '' : '18') ?>" class="form-control" style="border-radius: 0; border-top-right-radius: 8px; border-bottom-right-radius: 8px; text-align: center; font-weight: 700; font-size: 0.95rem; padding: 0.45rem;">
                                </div>
                                <span style="font-weight: 700; color: #94a3b8;">-</span>
                                <div style="display: flex; align-items: center; flex: 1;">
                                    <span style="background: #f1f5f9; border: 1px solid #cbd5e1; border-right: none; padding: 0.45rem 0.65rem; font-size: 0.82rem; color: #64748b; font-weight: 600; border-top-left-radius: 8px; border-bottom-left-radius: 8px; white-space: nowrap;">Đến</span>
                                    <input type="number" name="max_age" id="max_age_input" min="15" max="75" placeholder="35" value="<?= !empty($job['max_age']) ? htmlspecialchars($job['max_age']) : ($isNoAgeLimit ? '' : '35') ?>" class="form-control" style="border-radius: 0; border-top-right-radius: 8px; border-bottom-right-radius: 8px; text-align: center; font-weight: 700; font-size: 0.95rem; padding: 0.45rem;">
                                </div>
                                <span style="font-size: 0.84rem; color: #64748b; font-weight: 600; white-space: nowrap;">tuổi</span>
                            </div>

                            <!-- Gợi ý lứa tuổi phổ biến -->
                            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; align-items: center; margin-top: 0.45rem;">
                                <span style="font-size: 0.74rem; color: #64748b; font-weight: 600;">Gợi ý:</span>
                                <button type="button" class="btn-age-preset" data-min="18" data-max="25">18 - 25</button>
                                <button type="button" class="btn-age-preset <?= (!$isNoAgeLimit && ($job['min_age'] ?? '') == 18 && ($job['max_age'] ?? '') == 35) ? 'active' : '' ?>" data-min="18" data-max="35">18 - 35 (Chuẩn)</button>
                                <button type="button" class="btn-age-preset" data-min="20" data-max="45">20 - 45</button>
                                <button type="button" class="btn-age-preset" data-min="22" data-max="50">22 - 50</button>
                                <button type="button" class="btn-age-preset <?= $isNoAgeLimit ? 'active' : '' ?>" data-min="" data-max="" data-all="1">Mọi tuổi</button>
                            </div>
                        </div>

                        <!-- Giới tính (Chỉ chọn 1 trong 3) -->
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 700; margin-bottom: 0.35rem; color: #0f172a;">Yêu Cầu Giới Tính</label>
                            <input type="hidden" name="gender_requirement" id="gender_requirement_input" value="<?= htmlspecialchars($currentGenderReq) ?>">
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.4rem;">
                                <button type="button" class="gender-pill-btn <?= $currentGenderReq === 'all' ? 'active' : '' ?>" data-val="all">
                                    <?= icon('users', '', 16) ?>
                                    <span>Tất cả</span>
                                </button>
                                <button type="button" class="gender-pill-btn <?= $currentGenderReq === 'male' ? 'active' : '' ?>" data-val="male">
                                    <?= icon('user', '', 16) ?>
                                    <span>Nam</span>
                                </button>
                                <button type="button" class="gender-pill-btn <?= $currentGenderReq === 'female' ? 'active' : '' ?>" data-val="female">
                                    <?= icon('user', '', 16) ?>
                                    <span>Nữ</span>
                                </button>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.45rem;">
                                Phù hợp cho các vị trí đặc thù giới tính
                            </div>
                        </div>
                    </div>

                    <!-- Hàng 2: Kinh nghiệm & Trình độ học vấn -->
                    <div class="form-row" style="margin-bottom: 1rem;">
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 700; color: #0f172a;">Kinh Nghiệm Làm Việc</label>
                            <select name="experience_required" id="experience_required_select" class="form-control" style="background: #fff; font-size: 0.88rem; font-weight: 600;">
                                <option value="none" <?= $currentExpReq === 'none' ? 'selected' : '' ?>>Không yêu cầu kinh nghiệm (Được đào tạo)</option>
                                <option value="under_1_year" <?= $currentExpReq === 'under_1_year' ? 'selected' : '' ?>>Dưới 1 năm kinh nghiệm</option>
                                <option value="1_to_2_years" <?= $currentExpReq === '1_to_2_years' ? 'selected' : '' ?>>1 - 2 năm kinh nghiệm</option>
                                <option value="3_to_5_years" <?= $currentExpReq === '3_to_5_years' ? 'selected' : '' ?>>3 - 5 năm kinh nghiệm</option>
                                <option value="over_5_years" <?= $currentExpReq === 'over_5_years' ? 'selected' : '' ?>>Trên 5 năm kinh nghiệm (Chuyên sâu)</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 700; color: #0f172a;">Trình Độ Học Vấn</label>
                            <select name="education_level" id="education_level_select" class="form-control" style="background: #fff; font-size: 0.88rem; font-weight: 600;">
                                <option value="none" <?= $currentEduLevel === 'none' ? 'selected' : '' ?>>Không yêu cầu bằng cấp</option>
                                <option value="high_school" <?= $currentEduLevel === 'high_school' ? 'selected' : '' ?>>Tốt nghiệp THPT / Cấp 3</option>
                                <option value="vocational" <?= $currentEduLevel === 'vocational' ? 'selected' : '' ?>>Trung cấp / Trường nghề</option>
                                <option value="college" <?= $currentEduLevel === 'college' ? 'selected' : '' ?>>Cao đẳng</option>
                                <option value="university" <?= $currentEduLevel === 'university' ? 'selected' : '' ?>>Đại học trở lên</option>
                            </select>
                        </div>
                    </div>

                    <!-- Hàng 3: Tiêu chí & Kỹ năng bổ sung (Checkboxes) -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; margin-bottom: 0.45rem; color: #0f172a;">Tiêu Chí & Kỹ Năng Bổ Sung</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.55rem;">
                            <?php
                            $presetTraits = [
                                'vehicle' => ['label' => 'Có phương tiện đi lại (Xe máy)', 'icon' => 'target'],
                                'honest' => ['label' => 'Nhanh nhẹn, trung thực, chịu khó', 'icon' => 'check_circle'],
                                'healthy' => ['label' => 'Sức khỏe tốt, dẻo dai', 'icon' => 'shield_check'],
                                'communication' => ['label' => 'Kỹ năng giao tiếp tốt', 'icon' => 'message_circle'],
                                'teamwork' => ['label' => 'Khả năng làm việc nhóm', 'icon' => 'users'],
                                'shift_ready' => ['label' => 'Sẵn sàng làm xoay ca / Tăng ca', 'icon' => 'clock'],
                                'office_it' => ['label' => 'Tin học văn phòng cơ bản', 'icon' => 'file_text'],
                                'english' => ['label' => 'Tiếng Anh giao tiếp cơ bản', 'icon' => 'globe']
                            ];
                            foreach ($presetTraits as $k => $item):
                                $isChecked = in_array($item['label'], $currentOtherReq);
                            ?>
                                <label class="req-trait-card <?= $isChecked ? 'active' : '' ?>">
                                    <input type="checkbox" name="other_requirements[]" value="<?= htmlspecialchars($item['label']) ?>" <?= $isChecked ? 'checked' : '' ?>>
                                    <span style="font-size: 0.83rem; font-weight: 600; color: #1e293b; display: inline-flex; align-items: center; gap: 0.35rem;">
                                        <span class="req-trait-icon"><?= icon($item['icon'], '', 14) ?></span>
                                        <span><?= htmlspecialchars($item['label']) ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Khối 4: Yêu cầu & Quyền lợi tuyển dụng -->
                <div class="form-box-card">
                    <div class="form-box-title">
                        <?= icon('file_text', '', 16) ?> <span>Yêu Cầu & Chế Độ Đãi Ngộ</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Yêu Cầu Công Việc & Kỹ Năng *</label>
                        <textarea name="job_requirements" id="job_requirements_input" class="form-control" rows="4" required placeholder="Mô tả kỹ năng, số năm kinh nghiệm hoặc yêu cầu tác phong..."><?= htmlspecialchars($job['job_requirements'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Quyền Lợi & Yêu Cầu Bổ Sung</label>
                        <textarea name="additional_requirements" class="form-control" rows="3" placeholder="Ăn ca, phụ cấp, thưởng KPI, hỗ trợ chỗ ở..."><?= htmlspecialchars($job['additional_requirements'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Khối 5: Tiêu Chí Riêng Đánh Giá Độ Tương Thích AI Của Doanh Nghiệp -->
                <div class="form-box-card" style="border: 1px solid #99f6e4; background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%);">
                    <div class="form-box-title" style="color: #0f766e; border-color: #ccfbf1;">
                        <?= icon('sparkle', '', 16) ?> <span>Tiêu Chí Riêng Đánh Giá Độ Tương Thích AI (JobLink Matching Engine)</span>
                    </div>

                    <div style="font-size: 0.82rem; color: #475569; margin-bottom: 0.85rem; line-height: 1.45;">
                        Hệ thống AI sẽ đánh giá độ tương thích của ứng viên dựa trên <strong>chính các tiêu chí cụ thể của tin tuyển dụng này</strong> (không dùng thông số mặc định chung).
                    </div>

                    <!-- 1. Trọng tâm ưu tiên của NTD -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Trọng Tâm Ưu Tiên Của Doanh Nghiệp Cho Vị Trí Này</label>
                        <select name="matching_priority" class="form-control" style="font-weight: 600;">
                            <option value="auto" <?= $currentPriorityFocus === 'auto' ? 'selected' : '' ?>>Tự động thông minh theo tính chất công việc (Khuyên dùng)</option>
                            <option value="skills_first" <?= $currentPriorityFocus === 'skills_first' ? 'selected' : '' ?>>Ưu tiên Kỹ năng chuyên môn & Chức danh (Chuyên môn, kỹ thuật)</option>
                            <option value="experience_first" <?= $currentPriorityFocus === 'experience_first' ? 'selected' : '' ?>>Ưu tiên Kinh nghiệm thực tế (Quản lý, thợ lành nghề, trưởng ca)</option>
                            <option value="location_shifts" <?= $currentPriorityFocus === 'location_shifts' ? 'selected' : '' ?>>Ưu tiên Gần nơi làm việc & Ca làm (Sản xuất, phục vụ, giao nhận, F&B)</option>
                            <option value="attitude_traits" <?= $currentPriorityFocus === 'attitude_traits' ? 'selected' : '' ?>>Ưu tiên Tác phong & Tiêu chí bổ sung (Xe máy, giao tiếp, trung thực, xoay ca)</option>
                            <option value="balanced" <?= $currentPriorityFocus === 'balanced' ? 'selected' : '' ?>>Đánh giá cân bằng tất cả các tiêu chí</option>
                        </select>
                    </div>

                    <!-- 2. Tiêu chí bắt buộc (Must-Have Criteria) -->
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-weight: 700; color: #0f172a; margin-bottom: 0.4rem;">Điều Kiện Bắt Buộc Tuyệt Đối (Không thỏa mãn sẽ bị hạ điểm tương thích)</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.82rem; color: #334155; cursor: pointer; background: #ffffff; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <input type="checkbox" name="strict_gender" value="1" <?= $isStrictGender ? 'checked' : '' ?>>
                                <span>Bắt buộc đúng giới tính</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.82rem; color: #334155; cursor: pointer; background: #ffffff; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <input type="checkbox" name="strict_age" value="1" <?= $isStrictAge ? 'checked' : '' ?>>
                                <span>Bắt buộc trong độ tuổi quy định</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.82rem; color: #334155; cursor: pointer; background: #ffffff; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <input type="checkbox" name="strict_education" value="1" <?= $isStrictEdu ? 'checked' : '' ?>>
                                <span>Bắt buộc đạt từ học vấn yêu cầu</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.82rem; color: #334155; cursor: pointer; background: #ffffff; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <input type="checkbox" name="strict_experience" value="1" <?= $isStrictExp ? 'checked' : '' ?>>
                                <span>Bắt buộc đủ số năm kinh nghiệm</span>
                            </label>
                        </div>
                    </div>

                    <!-- 3. Ghi chú tiêu chí riêng -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">Ghi Chú Tiêu Chí Ưu Tiên Riêng Của Doanh Nghiệp (Tùy chọn)</label>
                        <input type="text" name="matching_custom_notes" class="form-control" placeholder="Ví dụ: Cần người nhanh nhẹn, ưu tiên sống gần Thủ Dầu Một, có xe máy..." value="<?= htmlspecialchars($customMatchingNotes) ?>">
                    </div>
                </div>

            </div>

            <!-- CỘT PHẢI: CA LÀM VIỆC, LỊCH TRÌNH & NÚT ĐĂNG TIN -->
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                
                <!-- Khối 4: Ca làm việc & Quy mô tuyển dụng -->
                <div class="form-box-card">
                    <div class="form-box-title">
                        <?= icon('clock', '', 16) ?> <span>Ca Làm Việc & Chỉ Tiêu Tuyển Dụng</span>
                    </div>

                    <!-- Ca làm việc -->
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.25rem;">
                            <label class="form-label" style="margin-bottom: 0; font-weight: 700; color: #0f172a;">Ca Làm Việc *</label>
                            <div style="display: flex; gap: 0.4rem; align-items: center;">
                                <button type="button" id="btn_open_modal_shift" class="btn btn-xs btn-outline" style="border-color: #0d9488; color: #ffffff; font-size: 0.76rem; padding: 2px 8px; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem; background: #0d9488;">
                                    <?= icon('plus', '', 11) ?> <span>Thêm ca mới</span>
                                </button>
                                <a href="<?= url('/employer/profile') ?>#work_shifts_section" target="_blank" style="font-size: 0.76rem; color: #64748b; text-decoration: underline;" title="Cài đặt danh sách ca làm việc trong Hồ sơ doanh nghiệp">
                                    Cài đặt mẫu ca
                                </a>
                            </div>
                        </div>
                        <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.45rem;">
                            Chọn nhanh từ mẫu của công ty hoặc gõ ca tự do
                        </div>

                        <!-- Dropdown chọn từ ca mẫu -->
                        <select id="work_shift_select" class="form-control" style="margin-bottom: 0.45rem; background: #fff; font-size: 0.88rem; font-weight: 600; color: #0f172a;">
                            <option value="">-- Chọn ca làm việc từ danh sách mẫu --</option>
                            <?php foreach ($workShifts as $s): 
                                $valStr = trim(($s['name'] ?? '') . (!empty($s['time']) ? ' (' . $s['time'] . ')' : ''));
                                $descStr = !empty($s['description']) ? ' - ' . $s['description'] : '';
                                $isSelected = (isset($job['work_shift']) && $job['work_shift'] === $valStr);
                            ?>
                                <option value="<?= htmlspecialchars($valStr) ?>" 
                                        data-name="<?= htmlspecialchars($s['name'] ?? '') ?>" 
                                        data-time="<?= htmlspecialchars($s['time'] ?? '') ?>" 
                                        data-desc="<?= htmlspecialchars($s['description'] ?? '') ?>"
                                        <?= $isSelected ? 'selected' : '' ?>>
                                    [<?= htmlspecialchars($s['name'] ?? '') ?>]: <?= htmlspecialchars($s['time'] ?? '') ?><?= htmlspecialchars($descStr) ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="__custom__" <?= (isset($job['work_shift']) && !empty($job['work_shift']) && !in_array($job['work_shift'], array_map(function($s) { return trim(($s['name'] ?? '') . (!empty($s['time']) ? ' (' . $s['time'] . ')' : '')); }, $workShifts))) ? 'selected' : '' ?>>
                                Tự nhập ca làm việc khác...
                            </option>
                        </select>

                        <!-- Danh sách các nút chọn nhanh 1 chạm (Quick Pills) -->
                        <div id="quick_shifts_pills" style="display: flex; flex-wrap: wrap; gap: 0.35rem; margin-bottom: 0.55rem;">
                            <?php if (!empty($workShifts)): ?>
                                <?php foreach ($workShifts as $s): 
                                    $valStr = trim(($s['name'] ?? '') . (!empty($s['time']) ? ' (' . $s['time'] . ')' : ''));
                                    $isActive = (isset($job['work_shift']) && $job['work_shift'] === $valStr);
                                ?>
                                    <button type="button" class="shift-pill-btn <?= $isActive ? 'active' : '' ?>" 
                                            data-val="<?= htmlspecialchars($valStr) ?>" 
                                            data-name="<?= htmlspecialchars($s['name'] ?? '') ?>"
                                            data-time="<?= htmlspecialchars($s['time'] ?? '') ?>"
                                            style="border: 1px solid <?= $isActive ? '#0d9488' : '#cbd5e1' ?>; background: <?= $isActive ? '#f0fdfa' : '#ffffff' ?>; color: <?= $isActive ? '#0d9488' : '#334155' ?>; padding: 0.2rem 0.55rem; border-radius: 16px; font-size: 0.78rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; transition: all 0.15s ease;">
                                        <?= icon('clock', '', 11) ?>
                                        <span><?= htmlspecialchars($s['name'] ?? '') ?><?= !empty($s['time']) ? ' (' . htmlspecialchars($s['time']) . ')' : '' ?></span>
                                    </button>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div id="no_shifts_hint" style="font-size: 0.78rem; color: #94a3b8; font-style: italic;">
                                    (Chưa có ca làm mẫu. Bấm <strong>"+ Thêm ca mới"</strong> để lưu mẫu)
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Thời Gian Làm Việc: 2 Ô Nhập Giờ Bên Trái - Bên Phải (Định dạng time chuẩn, tránh gõ text sai sót) -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.85rem; margin-top: 0.5rem; margin-bottom: 0.2rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem;">
                                <label style="margin-bottom: 0; font-weight: 700; color: #0f172a; font-size: 0.83rem; display: flex; align-items: center; gap: 0.35rem;">
                                    <?= icon('clock', '', 14) ?> <span>Khung Giờ Làm Việc Trong Ngày *</span>
                                </label>
                                <span style="font-size: 0.72rem; color: #0d9488; font-weight: 600; background: #f0fdfa; padding: 1px 6px; border-radius: 10px; border: 1px solid #99f6e4;">Định dạng chuẩn Giờ:Phút (24h)</span>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 0.65rem; align-items: center;">
                                <div>
                                    <label for="shift_start_time" style="display: block; font-size: 0.74rem; color: #475569; font-weight: 600; margin-bottom: 0.2rem;">
                                        Từ giờ (Bắt đầu):
                                    </label>
                                    <input type="time" name="shift_start_time" id="shift_start_time" 
                                           value="<?= htmlspecialchars($currentShiftParsed['start']) ?>" 
                                           class="form-control" required 
                                           style="font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: center; background: #ffffff; cursor: pointer;">
                                </div>
                                <div style="padding-top: 1.15rem; font-weight: 700; color: #64748b; font-size: 0.88rem; text-align: center; user-select: none;">
                                    đến
                                </div>
                                <div>
                                    <label for="shift_end_time" style="display: block; font-size: 0.74rem; color: #475569; font-weight: 600; margin-bottom: 0.2rem;">
                                        Đến giờ (Kết thúc):
                                    </label>
                                    <input type="time" name="shift_end_time" id="shift_end_time" 
                                           value="<?= htmlspecialchars($currentShiftParsed['end']) ?>" 
                                           class="form-control" required 
                                           style="font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: center; background: #ffffff; cursor: pointer;">
                                </div>
                            </div>

                            <div style="margin-top: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <label for="shift_custom_name" style="font-size: 0.75rem; color: #475569; font-weight: 600; white-space: nowrap; margin-bottom: 0;">
                                    Tên gọi ca:
                                </label>
                                <input type="text" name="shift_custom_name" id="shift_custom_name" 
                                       value="<?= htmlspecialchars($currentShiftParsed['name']) ?>" 
                                       class="form-control" placeholder="VD: Ca sáng, Hành chính, Ca tối... (hoặc để trống)" 
                                       style="font-size: 0.82rem; padding: 0.35rem 0.6rem; background: #ffffff;">
                            </div>
                        </div>

                        <!-- Input tổng hợp gửi lên Backend (đồng bộ liên tục từ 2 ô giờ và tên ca) -->
                        <input type="hidden" name="work_shift" id="work_shift_input" value="<?= htmlspecialchars($job['work_shift'] ?? '') ?>">
                    </div>

                    <div class="form-row" style="margin-bottom: 0;">
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label class="form-label">Số Lượng Tuyển Dụng</label>
                            <input type="number" name="number_of_positions" value="<?= htmlspecialchars($job['number_of_positions'] ?? 1) ?>" class="form-control" min="1">
                        </div>
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label class="form-label">Hạn Nộp Hồ Sơ *</label>
                            <input type="date" name="application_deadline" value="<?= htmlspecialchars($job['application_deadline'] ?? date('Y-m-d', strtotime('+30 days'))) ?>" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Khối 5: Lịch & Ngày làm việc trong tuần / tháng -->
                <div class="form-box-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.65rem;">
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.98rem; display: flex; align-items: center; gap: 0.45rem;">
                            <?= icon('calendar', '', 16) ?> <span>Lịch & Ngày Làm Việc *</span>
                        </div>
                        <span id="schedule_mode_badge" class="badge badge-primary" style="font-size: 0.75rem;">Toàn thời gian (1 tháng)</span>
                    </div>

                    <div style="font-size: 0.82rem; color: #64748b; margin-bottom: 0.75rem;">
                        Thông tin hiển thị cho người tìm việc theo dõi lịch làm việc
                    </div>

                    <input type="text" name="working_days" id="working_days_input" value="<?= htmlspecialchars($job['working_days'] ?? 'Thứ 2 - Thứ 7 (Nghỉ lễ theo quy định; Làm lễ tăng ca x3 lương)') ?>" class="form-control" required placeholder="VD: Thứ 2 - Thứ 7 (Nghỉ lễ; Làm lễ x3 lương)" style="background: #fff; font-weight: 600; color: #0f172a; margin-bottom: 0.75rem;">

                    <!-- Khối Cho Toàn Thời Gian (Full-Time): 7 ô ngày + Checkbox Nghỉ Lễ & Ưu Đãi -->
                    <div id="fulltime_schedule_box" style="display: block; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem;">
                        <!-- 1. 7 ô tương ứng 7 ngày trong tuần -->
                        <div style="margin-bottom: 0.9rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.4rem;">
                                <div style="font-size: 0.82rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                                    <?= icon('calendar', '', 13) ?> <span>1. Ngày làm việc trong tuần (bấm để bật/tắt):</span>
                                </div>
                                <div style="display: flex; gap: 0.3rem; flex-wrap: wrap; align-items: center;">
                                    <span style="font-size: 0.73rem; color: #64748b; font-weight: 600;">Mẫu nhanh:</span>
                                    <button type="button" class="btn-ft-preset" data-preset="2-6" style="padding: 0.18rem 0.5rem; font-size: 0.73rem; border-radius: 6px; border: 1px solid #475569; background: #475569; color: #ffffff; font-weight: 600; cursor: pointer;">T2 - T6</button>
                                    <button type="button" class="btn-ft-preset active" data-preset="2-7" style="padding: 0.18rem 0.5rem; font-size: 0.73rem; border-radius: 6px; border: 1px solid #0d9488; background: #0d9488; color: #ffffff; font-weight: 700; cursor: pointer;">T2 - T7 (Chuẩn)</button>
                                    <button type="button" class="btn-ft-preset" data-preset="all" style="padding: 0.18rem 0.5rem; font-size: 0.73rem; border-radius: 6px; border: 1px solid #6366f1; background: #6366f1; color: #ffffff; font-weight: 600; cursor: pointer;">Cả tuần</button>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.4rem;" id="fulltime_days_grid">
                                <button type="button" class="btn-ft-day active" data-day="Thứ 2">
                                    <span class="ft-day-title">T2</span>
                                    <span class="ft-day-sub">Làm</span>
                                </button>
                                <button type="button" class="btn-ft-day active" data-day="Thứ 3">
                                    <span class="ft-day-title">T3</span>
                                    <span class="ft-day-sub">Làm</span>
                                </button>
                                <button type="button" class="btn-ft-day active" data-day="Thứ 4">
                                    <span class="ft-day-title">T4</span>
                                    <span class="ft-day-sub">Làm</span>
                                </button>
                                <button type="button" class="btn-ft-day active" data-day="Thứ 5">
                                    <span class="ft-day-title">T5</span>
                                    <span class="ft-day-sub">Làm</span>
                                </button>
                                <button type="button" class="btn-ft-day active" data-day="Thứ 6">
                                    <span class="ft-day-title">T6</span>
                                    <span class="ft-day-sub">Làm</span>
                                </button>
                                <button type="button" class="btn-ft-day active" data-day="Thứ 7">
                                    <span class="ft-day-title">T7</span>
                                    <span class="ft-day-sub">Làm</span>
                                </button>
                                <button type="button" class="btn-ft-day" data-day="Chủ Nhật">
                                    <span class="ft-day-title">CN</span>
                                    <span class="ft-day-sub">Nghỉ</span>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Các tích chọn ưu đãi & quyền lợi -->
                        <div style="border-top: 1px dashed #cbd5e1; padding-top: 0.8rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.35rem;">
                                <div style="font-size: 0.82rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                                    <?= icon('check', '', 13) ?> <span>2. Chế độ nghỉ lễ & Ưu đãi phúc lợi đi kèm:</span>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.5rem;" id="fulltime_perks_grid">
                                <label class="ft-perk-card active" data-label="Nghỉ lễ">
                                    <input type="checkbox" class="ft-perk-cb" value="Được nghỉ lễ theo quy định" checked>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.8rem; display: flex; align-items: center;">
                                            <span class="ft-perk-icon"><?= icon('calendar', '', 13) ?></span>
                                            <span>Được nghỉ lễ theo quy định</span>
                                        </div>
                                        <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.1rem;">Nghỉ Lễ, Tết theo luật hưởng nguyên lương</div>
                                    </div>
                                </label>
                                <label class="ft-perk-card active" data-label="Làm lễ x3 lương">
                                    <input type="checkbox" class="ft-perk-cb" value="Làm lễ tăng ca x3 lương" checked>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.8rem; display: flex; align-items: center;">
                                            <span class="ft-perk-icon"><?= icon('zap', '', 13) ?></span>
                                            <span>Làm lễ tăng ca x3 lương</span>
                                        </div>
                                        <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.1rem;">Làm ngày Lễ/Tết được tính x3 (300%) lương</div>
                                    </div>
                                </label>
                                <label class="ft-perk-card" data-label="Phép năm 12 ngày">
                                    <input type="checkbox" class="ft-perk-cb" value="Nghỉ phép năm 12 ngày có lương">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.8rem; display: flex; align-items: center;">
                                            <span class="ft-perk-icon"><?= icon('clock', '', 13) ?></span>
                                            <span>Nghỉ phép năm có lương</span>
                                        </div>
                                        <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.1rem;">12 ngày phép năm hưởng 100% lương</div>
                                    </div>
                                </label>
                                <label class="ft-perk-card" data-label="Thưởng chuyên cần">
                                    <input type="checkbox" class="ft-perk-cb" value="Thưởng chuyên cần">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.8rem; display: flex; align-items: center;">
                                            <span class="ft-perk-icon"><?= icon('award', '', 13) ?></span>
                                            <span>Thưởng chuyên cần tháng</span>
                                        </div>
                                        <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.1rem;">Thưởng thêm khi làm đủ công tháng</div>
                                    </div>
                                </label>
                                <label class="ft-perk-card" data-label="Phụ cấp ăn ca">
                                    <input type="checkbox" class="ft-perk-cb" value="Phụ cấp ăn ca / cơm trưa">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.8rem; display: flex; align-items: center;">
                                            <span class="ft-perk-icon"><?= icon('check_circle', '', 13) ?></span>
                                            <span>Phụ cấp ăn ca / cơm trưa</span>
                                        </div>
                                        <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.1rem;">Bao cơm trưa hoặc hỗ trợ tiền ăn ca</div>
                                    </div>
                                </label>
                                <label class="ft-perk-card" data-label="Phụ cấp xăng xe">
                                    <input type="checkbox" class="ft-perk-cb" value="Phụ cấp xăng xe & đi lại">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.8rem; display: flex; align-items: center;">
                                            <span class="ft-perk-icon"><?= icon('map_pin', '', 13) ?></span>
                                            <span>Phụ cấp xăng xe & đi lại</span>
                                        </div>
                                        <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.1rem;">Hỗ trợ chi phí đi lại hàng tháng</div>
                                    </div>
                                </label>
                                <label class="ft-perk-card" data-label="Thưởng tháng 13">
                                    <input type="checkbox" class="ft-perk-cb" value="Thưởng lương tháng 13 & Lễ Tết">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.8rem; display: flex; align-items: center;">
                                            <span class="ft-perk-icon"><?= icon('dollar', '', 13) ?></span>
                                            <span>Thưởng tháng 13 & Lễ Tết</span>
                                        </div>
                                        <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.1rem;">Lương tháng 13 và thưởng các đợt lễ lớn</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Khối Bộ Chọn Lịch Tương Tác Cho Bán Thời Gian & Thời Vụ (Part-Time / Temporary) -->
                    <div id="parttime_schedule_box" style="display: none; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.4rem;">
                            <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                                <?= icon('calendar', '', 14) ?> <span>Lịch làm việc chi tiết (Bán thời gian / Thời vụ)</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                <span id="cal_selected_counter_badge" style="font-size: 0.73rem; padding: 0.2rem 0.55rem; border-radius: 16px; background: #f0fdfa; color: #0d9488; border: 1px solid #99f6e4; font-weight: 700;">
                                    Chưa chọn ngày nào
                                </span>
                                <button type="button" id="btn_cal_clear_all" style="font-size: 0.73rem; color: #b91c1c; background: none; border: 1px solid #fca5a5; border-radius: 5px; padding: 0.18rem 0.45rem; cursor: pointer; font-weight: 600;">
                                    Xóa tất cả
                                </button>
                            </div>
                        </div>

                        <!-- Mẫu chọn nhanh -->
                        <div style="display: flex; gap: 0.3rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center; background: #f8fafc; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <span style="font-size: 0.73rem; color: #475569; font-weight: 700;">Mẫu nhanh:</span>
                            <button type="button" class="btn-cal-quick-pick" data-pattern="2-4-6" style="padding: 0.2rem 0.55rem; font-size: 0.73rem; border-radius: 5px; border: 1px solid #0d9488; background: #0d9488; color: #ffffff; font-weight: 700; cursor: pointer;">T2, T4, T6</button>
                            <button type="button" class="btn-cal-quick-pick" data-pattern="3-5-7" style="padding: 0.2rem 0.55rem; font-size: 0.73rem; border-radius: 5px; border: 1px solid #0d9488; background: #0d9488; color: #ffffff; font-weight: 700; cursor: pointer;">T3, T5, T7</button>
                            <button type="button" class="btn-cal-quick-pick" data-pattern="weekend" style="padding: 0.2rem 0.55rem; font-size: 0.73rem; border-radius: 5px; border: 1px solid #0d9488; background: #0d9488; color: #ffffff; font-weight: 700; cursor: pointer;">Cuối tuần</button>
                            <button type="button" class="btn-cal-quick-pick" data-pattern="all-month" style="padding: 0.2rem 0.55rem; font-size: 0.73rem; border-radius: 5px; border: 1px solid #0d9488; background: #0d9488; color: #ffffff; font-weight: 700; cursor: pointer;">Cả tháng</button>
                            <button type="button" class="btn-cal-quick-pick" data-pattern="flexible" style="padding: 0.2rem 0.55rem; font-size: 0.73rem; border-radius: 5px; border: 1px solid #6366f1; background: #6366f1; color: #ffffff; font-weight: 700; cursor: pointer;">Xoay ca</button>
                        </div>

                        <!-- BỐ CỤC LỊCH CHUẨN (JobLink Signature Teal) -->
                        <div style="display: flex; justify-content: center; margin-bottom: 0.85rem;">
                            <div id="joblink_calendar_widget" style="width: 100%; max-width: 360px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 0 0.25rem;">
                                    <div id="cal_header_title" style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">
                                        Tháng 9 2026
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                                        <button type="button" id="btn_cal_prev_month" title="Tháng trước (▲)" style="background: none; border: none; cursor: pointer; color: #64748b; font-size: 0.9rem; padding: 3px 6px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center;">
                                            ▲
                                        </button>
                                        <button type="button" id="btn_cal_next_month" title="Tháng tiếp theo (▼)" style="background: none; border: none; cursor: pointer; color: #64748b; font-size: 0.9rem; padding: 3px 6px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center;">
                                            ▼
                                        </button>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; margin-bottom: 0.65rem; font-weight: 700; font-size: 0.82rem; color: #475569;">
                                    <div>CN</div>
                                    <div>T2</div>
                                    <div>T3</div>
                                    <div>T4</div>
                                    <div>T5</div>
                                    <div>T6</div>
                                    <div>T7</div>
                                </div>

                                <div id="cal_days_matrix" style="display: grid; grid-template-columns: repeat(7, 1fr); row-gap: 6px; text-align: center;">
                                </div>
                            </div>
                        </div>

                        <!-- Hàng hiển thị danh sách các ngày đã chọn (Chips) -->
                        <div id="cal_chips_container" style="display: none; border-top: 1px dashed #cbd5e1; padding-top: 0.65rem;">
                            <div style="font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">
                                Danh sách ngày đã chọn:
                            </div>
                            <div id="cal_chips_list" style="display: flex; flex-wrap: wrap; gap: 0.3rem; max-height: 80px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Khối 6: Thao tác Xác nhận Đăng Tin Tuyển Dụng -->
                <div class="form-box-card" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.75rem 1.5rem; font-size: 1rem; font-weight: 700; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);">
                            <?= isset($job) ? 'Lưu Thay Đổi' : 'Đăng Tin Tuyển Dụng' ?>
                        </button>
                        <a href="<?= url('/employer/jobs') ?>" class="btn btn-outline" style="padding: 0.75rem 1.25rem; font-size: 0.92rem;">Hủy Bỏ</a>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<!-- Modal Thêm Ca Làm Mẫu Nhanh Cho Doanh Nghiệp -->
<div id="modal_add_shift" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px); align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; width: 100%; max-width: 480px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #cbd5e1;">
        <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: 700; color: #0f172a; font-size: 1rem; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('clock', '', 16) ?> <span>Thêm Ca Làm Việc Mẫu Mới</span>
            </div>
            <button type="button" id="btn_close_modal_shift" style="background: none; border: none; font-size: 1.25rem; line-height: 1; color: #64748b; cursor: pointer; padding: 0.2rem;" title="Đóng">&times;</button>
        </div>
        <div style="padding: 1.25rem;">
            <p style="font-size: 0.83rem; color: #64748b; margin-top: 0; margin-bottom: 0.9rem; line-height: 1.45;">
                Ca làm việc này sẽ được lưu trực tiếp vào Hồ Sơ Doanh Nghiệp để bạn có thể chọn lại nhanh ở bất kỳ tin tuyển dụng nào sau này.
            </p>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label" style="font-size: 0.83rem; font-weight: 700; margin-bottom: 0.25rem;">Tên Ca Làm Việc *</label>
                <input type="text" id="modal_shift_name" class="form-control" placeholder="VD: Ca sáng, Ca tối, Ca gãy..." style="font-size: 0.88rem;">
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label" style="font-size: 0.83rem; font-weight: 700; margin-bottom: 0.25rem;">Khung Giờ Làm Việc *</label>
                <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 0.5rem; align-items: center;">
                    <div>
                        <label style="display: block; font-size: 0.72rem; color: #475569; margin-bottom: 0.15rem;">Từ giờ (Bắt đầu):</label>
                        <input type="time" id="modal_shift_start_time" class="form-control" value="08:00" required style="font-weight: 700; text-align: center; font-size: 0.92rem; background: #fff; cursor: pointer;">
                    </div>
                    <div style="padding-top: 1rem; font-weight: 700; color: #64748b; font-size: 0.85rem; text-align: center; user-select: none;">đến</div>
                    <div>
                        <label style="display: block; font-size: 0.72rem; color: #475569; margin-bottom: 0.15rem;">Đến giờ (Kết thúc):</label>
                        <input type="time" id="modal_shift_end_time" class="form-control" value="17:00" required style="font-weight: 700; text-align: center; font-size: 0.92rem; background: #fff; cursor: pointer;">
                    </div>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" style="font-size: 0.83rem; font-weight: 700; margin-bottom: 0.25rem;">Ghi Chú / Chế Độ Ca (Không bắt buộc)</label>
                <input type="text" id="modal_shift_desc" class="form-control" placeholder="VD: Nghỉ trưa 1 tiếng, bao cơm trưa" style="font-size: 0.88rem;">
            </div>
            <div id="modal_shift_error" style="display: none; padding: 0.5rem 0.75rem; background: #fef2f2; border: 1px solid #fca5a5; border-radius: 6px; color: #b91c1c; font-size: 0.82rem; margin-bottom: 0.85rem;"></div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" id="btn_cancel_modal_shift" class="btn btn-outline" style="padding: 0.45rem 0.9rem; font-size: 0.85rem;">Hủy Bỏ</button>
                <button type="button" id="btn_submit_modal_shift" class="btn btn-primary" style="padding: 0.45rem 1.15rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <?= icon('check_circle', '', 14) ?> <span>Lưu & Áp Dụng Ngay</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="similar_salary_jobs_modal" role="dialog" aria-modal="true" aria-labelledby="similar_salary_jobs_title" style="display: none; position: fixed; inset: 0; z-index: 10000; background: rgba(15, 23, 42, 0.5); align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; width: min(760px, 100%); max-height: 85vh; overflow: hidden; border-radius: 12px; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
            <div>
                <h3 id="similar_salary_jobs_title" style="margin: 0; color: #0f172a; font-size: 1rem;">Các tin tuyển dụng có tiêu chí tương tự</h3>
                <p id="similar_salary_jobs_summary" style="margin: 0.25rem 0 0; color: #64748b; font-size: 0.78rem;"></p>
            </div>
            <button type="button" id="btn_close_similar_salary_jobs" aria-label="Đóng" style="border: 1px solid #475569; background: #475569; color: #ffffff; border-radius: 7px; width: 30px; height: 30px; cursor: pointer; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">&times;</button>
        </div>
        <div id="similar_salary_jobs_list" style="padding: 1rem 1.25rem; max-height: calc(85vh - 90px); overflow-y: auto;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Elements
    const typeSelect = document.getElementById('employment_type_select');
    const salaryInput = document.getElementById('salary_input');
    const salaryLabel = document.getElementById('salary_label');
    const salaryAddon = document.getElementById('salary_addon');
    const presetContainer = document.getElementById('preset_buttons');
    const dynamicZeroContainer = document.getElementById('dynamic_zero_suggestions');
    const btnClear = document.getElementById('btn_clear_salary');
    const btnViewSimilarJobs = document.getElementById('btn_view_similar_jobs');
    const similarJobsModal = document.getElementById('similar_salary_jobs_modal');
    const btnCloseSimilarJobs = document.getElementById('btn_close_similar_salary_jobs');
    const similarJobsList = document.getElementById('similar_salary_jobs_list');
    const similarJobsSummary = document.getElementById('similar_salary_jobs_summary');
    let latestBenchmarkData = null;

    // Smart Benchmark elements
    const jobTitleInput = document.getElementById('job_title_input');
    const jobReqInput = document.getElementById('job_requirements_input');
    const workShiftInput = document.getElementById('work_shift_input');
    const workShiftSelect = document.getElementById('work_shift_select');
    const quickShiftsPillsContainer = document.getElementById('quick_shifts_pills');
    const btnOpenModalShift = document.getElementById('btn_open_modal_shift');
    const modalAddShift = document.getElementById('modal_add_shift');
    const btnCloseModalShift = document.getElementById('btn_close_modal_shift');
    const btnCancelModalShift = document.getElementById('btn_cancel_modal_shift');
    const btnSubmitModalShift = document.getElementById('btn_submit_modal_shift');
    const shiftStartTimeInput = document.getElementById('shift_start_time');
    const shiftEndTimeInput = document.getElementById('shift_end_time');
    const shiftCustomNameInput = document.getElementById('shift_custom_name');
    const modalShiftName = document.getElementById('modal_shift_name');
    const modalShiftStartTime = document.getElementById('modal_shift_start_time');
    const modalShiftEndTime = document.getElementById('modal_shift_end_time');
    const modalShiftDesc = document.getElementById('modal_shift_desc');
    const modalShiftError = document.getElementById('modal_shift_error');

    let benchmarkDebounceTimer = null;

    // Workplace elements
    const workplaceSelect = document.getElementById('workplace_select');
    const workplaceInput = document.getElementById('workplace_address_input');
    const btnPreviewMap = document.getElementById('btn_preview_map');

    // Schedule elements
    const scheduleModeBadge = document.getElementById('schedule_mode_badge');
    const workingDaysInput = document.getElementById('working_days_input');
    const fulltimeBox = document.getElementById('fulltime_schedule_box');
    const parttimeBox = document.getElementById('parttime_schedule_box');

    // Calendar elements (Part-time / Temporary)
    const calHeaderTitle = document.getElementById('cal_header_title');
    const btnCalPrev = document.getElementById('btn_cal_prev_month');
    const btnCalNext = document.getElementById('btn_cal_next_month');
    const calDaysMatrix = document.getElementById('cal_days_matrix');
    const calSelectedCounterBadge = document.getElementById('cal_selected_counter_badge');
    const btnCalClearAll = document.getElementById('btn_cal_clear_all');
    const calChipsContainer = document.getElementById('cal_chips_container');
    const calChipsList = document.getElementById('cal_chips_list');

    let calCurrentYear = new Date().getFullYear();
    let calCurrentMonth = new Date().getMonth();
    let selectedCalendarDates = [];

    const config = {
        'full_time': { 
            unit: '1 tháng', 
            label: 'Mức Lương (VNĐ / 1 tháng) *', 
            addon: 'VNĐ / 1 tháng', 
            placeholder: 'VD: 15.000.000',
            presets: [
                { label: '8 triệu', value: 8000000 },
                { label: '10 triệu', value: 10000000 },
                { label: '12 triệu', value: 12000000 },
                { label: '15 triệu', value: 15000000 },
                { label: '20 triệu', value: 20000000 }
            ]
        },
        'part_time': { 
            unit: '1 giờ', 
            label: 'Mức Lương (VNĐ / 1 giờ) *', 
            addon: 'VNĐ / 1 giờ', 
            placeholder: 'VD: 30.000',
            presets: [
                { label: '25k / giờ', value: 25000 },
                { label: '30k / giờ', value: 30000 },
                { label: '35k / giờ', value: 35000 },
                { label: '45k / giờ', value: 45000 },
                { label: '60k / giờ', value: 60000 }
            ]
        },
        'temporary': { 
            unit: '1 ca', 
            label: 'Mức Lương (VNĐ / 1 ca) *', 
            addon: 'VNĐ / 1 ca', 
            placeholder: 'VD: 350.000',
            presets: [
                { label: '200k / ca', value: 200000 },
                { label: '250k / ca', value: 250000 },
                { label: '300k / ca', value: 300000 },
                { label: '400k / ca', value: 400000 },
                { label: '500k / ca', value: 500000 }
            ]
        }
    };

    // --- ĐỊNH DẠNG SỐ HÀNG NGHÌN & GỢI Ý MỞ RỘNG SỐ 0 ---
    function formatDots(numStr) {
        if (!numStr) return '';
        const clean = numStr.toString().replace(/\D/g, '');
        if (!clean) return '';
        return Number(clean).toLocaleString('vi-VN');
    }

    function updateDynamicZeroSuggestions(rawDigits) {
        if (!dynamicZeroContainer) return;
        dynamicZeroContainer.innerHTML = '';
        const zeroBox = document.getElementById('dynamic_zero_box');

        if (!rawDigits || rawDigits === '0') {
            if (zeroBox) zeroBox.style.display = 'none';
            return;
        }

        const num = parseInt(rawDigits, 10);
        if (isNaN(num) || num <= 0) {
            if (zeroBox) zeroBox.style.display = 'none';
            return;
        }

        const curType = typeSelect ? typeSelect.value : 'full_time';
        let sensibleMultipliers = [];

        if (curType === 'full_time') {
            sensibleMultipliers = [100000, 1000000, 10000000];
        } else if (curType === 'part_time') {
            sensibleMultipliers = [1000, 10000];
        } else {
            sensibleMultipliers = [10000, 100000];
        }

        let count = 0;
        sensibleMultipliers.forEach(mult => {
            const val = num * mult;
            if (val >= 10000 && val <= 500000000) {
                const formatted = val.toLocaleString('vi-VN');
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-dyn-zero';
                btn.textContent = formatted + ' đ';
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    salaryInput.value = formatted;
                    handleSalaryInput();
                    salaryInput.focus();
                });
                dynamicZeroContainer.appendChild(btn);
                count++;
            }
        });

        if (zeroBox) {
            zeroBox.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    function formatCompactPresetLabel(label) {
        if (!label) return '';
        return label
            .replace(/\s*triệu\s*đ/gi, ' tr')
            .replace(/\s*nghìn\s*đ/gi, 'k')
            .replace(/\s*đ\/h/gi, ' đ/h')
            .replace(/\s*đ\/ca/gi, ' đ/ca')
            .trim();
    }

    function renderFallbackPresets(type) {
        const cfg = config[type] || config['full_time'];
        if (!presetContainer) return;
        presetContainer.innerHTML = '';
        const fallbackList = (cfg.presets || []).slice(0, 3);
        fallbackList.forEach((p, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            const isRec = (idx === 1);
            btn.className = 'btn-preset-salary' + (isRec ? ' recommended' : '');
            const shortLabel = formatCompactPresetLabel(p.label);
            const checkIcon = isRec ? `<?= icon('check_circle', '', 11) ?> ` : '';
            const prefix = idx === 0 ? 'Thấp nhất: ' : (isRec ? 'Trung bình: ' : 'Cao nhất: ');
            btn.innerHTML = `${checkIcon}<span>${prefix}<b>${escapeHtml(shortLabel)}</b></span>`;
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                salaryInput.value = p.value.toLocaleString('vi-VN');
                handleSalaryInput();
                salaryInput.focus();
            });
            presetContainer.appendChild(btn);
        });
    }

    function renderBenchmarkPresets(data) {
        if (!presetContainer) return;
        presetContainer.innerHTML = '';

        if (!data || !Array.isArray(data.presets) || data.presets.length === 0) {
            renderFallbackPresets(typeSelect ? typeSelect.value : 'full_time');
            return;
        }

        const presetsToShow = data.presets.slice(0, 3);

        presetsToShow.forEach(p => {
            const btn = document.createElement('button');
            btn.type = 'button';
            const isRec = p.is_recommended;
            btn.className = 'btn-preset-salary' + (isRec ? ' recommended' : '');
            btn.title = (p.badge ? p.badge + ': ' : '') + p.label;

            const shortLabel = formatCompactPresetLabel(p.label);
            const checkIcon = isRec ? `<?= icon('check_circle', '', 11) ?> ` : '';
            const prefix = `${p.badge}: `;
            btn.innerHTML = `${checkIcon}<span>${prefix}<b>${escapeHtml(shortLabel)}</b></span>`;

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                salaryInput.value = Number(p.value).toLocaleString('vi-VN');
                handleSalaryInput();
                salaryInput.focus();

                salaryInput.style.transition = 'box-shadow 0.2s ease, border-color 0.2s ease';
                salaryInput.style.borderColor = '#0d9488';
                salaryInput.style.boxShadow = '0 0 0 3px rgba(13, 148, 136, 0.18)';
                setTimeout(() => {
                    salaryInput.style.boxShadow = 'none';
                }, 600);
            });

            presetContainer.appendChild(btn);
        });
    }

    function renderSimilarJobs(data) {
        if (!btnViewSimilarJobs || !similarJobsList) return;
        latestBenchmarkData = data;
        const jobs = Array.isArray(data.matched_sample) ? data.matched_sample : [];
        btnViewSimilarJobs.style.display = jobs.length ? 'inline-flex' : 'none';
        if (!jobs.length) return;

        const stats = data.base_stats || {};
        const unit = data.unit || 'tháng';
        similarJobsSummary.textContent = `Đã tìm thấy ${data.matched_count || jobs.length} tin tương đồng · Thấp nhất ${formatMoney(stats.min)} · Trung bình ${formatMoney(stats.avg)} · Cao nhất ${formatMoney(stats.max)} / ${unit}`;
        similarJobsList.innerHTML = jobs.map(job => {
            const salary = Number(job.normalized_salary || 0);
            const requirements = job.job_requirements || 'Không có mô tả yêu cầu';
            const shift = job.work_shift || 'Chưa cập nhật ca làm';
            const address = job.workplace_address || 'Chưa cập nhật địa điểm';
            return `<div style="border: 1px solid #e2e8f0; border-radius: 9px; padding: 0.75rem; margin-bottom: 0.6rem; background: #fff;">
                <div style="display: flex; justify-content: space-between; gap: 0.75rem; align-items: flex-start;">
                    <div>
                        <div style="font-weight: 700; color: #0f766e; font-size: 0.88rem;">${escapeHtml(job.job_title || 'Tin tuyển dụng')}</div>
                        <div style="color: #64748b; font-size: 0.75rem; margin-top: 0.2rem;">${escapeHtml(job.employer_name || 'Doanh nghiệp')} · ${escapeHtml(job.employment_type || '')}</div>
                    </div>
                    <strong style="color: #0f172a; white-space: nowrap;">${formatMoney(salary)} / ${escapeHtml(unit)}</strong>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 0.5rem; color: #475569; font-size: 0.74rem;">
                    <span style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; padding: 0.2rem 0.4rem;">Ca: ${escapeHtml(shift)}</span>
                    <span style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; padding: 0.2rem 0.4rem;">${escapeHtml(address)}</span>
                </div>
                <div style="color: #64748b; font-size: 0.76rem; margin-top: 0.45rem; line-height: 1.4;">${escapeHtml(requirements)}</div>
            </div>`;
        }).join('');
    }

    function formatMoney(value) {
        return Number(value || 0).toLocaleString('vi-VN') + ' đ';
    }

    function openSimilarJobsModal() {
        if (similarJobsModal && latestBenchmarkData) {
            similarJobsModal.style.display = 'flex';
        }
    }

    function closeSimilarJobsModal() {
        if (similarJobsModal) similarJobsModal.style.display = 'none';
    }

    if (btnViewSimilarJobs) btnViewSimilarJobs.addEventListener('click', openSimilarJobsModal);
    if (btnCloseSimilarJobs) btnCloseSimilarJobs.addEventListener('click', closeSimilarJobsModal);
    if (similarJobsModal) {
        similarJobsModal.addEventListener('click', function(event) {
            if (event.target === similarJobsModal) closeSimilarJobsModal();
        });
    }

    function fetchSalaryBenchmark() {
        const title = jobTitleInput ? jobTitleInput.value.trim() : '';
        const type = typeSelect ? typeSelect.value : 'full_time';
        const req = jobReqInput ? jobReqInput.value.trim() : '';
        const shift = workShiftInput ? workShiftInput.value.trim() : '';

        if (!presetContainer) return;

        let baseUrl = (typeof window.APP_URL !== 'undefined' ? window.APP_URL : '');
        if (!baseUrl) {
            const segments = window.location.pathname.split('/').filter(Boolean);
            if (segments.length > 0 && segments[0].toLowerCase() === 'joblink') {
                baseUrl = '/' + segments[0];
            }
        }
        const apiUrl = (baseUrl ? baseUrl.replace(/\/+$/, '') : '') + '/api/jobs/salary-benchmark';

        const expSelect = document.getElementById('experience_required_select');
        const eduSelect = document.getElementById('education_level_select');

        const params = new URLSearchParams({
            job_title: title,
            employment_type: type,
            job_requirements: req,
            work_shift: shift,
            experience_required: expSelect ? expSelect.value : 'none',
            education_level: eduSelect ? eduSelect.value : 'none'
        });

        fetch(apiUrl + '?' + params.toString())
            .then(res => res.json())
            .then(data => {
                if (data && data.success && Array.isArray(data.presets)) {
                    renderSimilarJobs(data);
                    renderBenchmarkPresets(data);
                } else {
                    if (btnViewSimilarJobs) btnViewSimilarJobs.style.display = 'none';
                    renderFallbackPresets(type);
                }
            })
            .catch(() => {
                if (btnViewSimilarJobs) btnViewSimilarJobs.style.display = 'none';
                renderFallbackPresets(type);
            });
    }

    function handleSalaryInput() {
        const raw = salaryInput.value.replace(/\D/g, '');
        if (raw) {
            salaryInput.value = formatDots(raw);
            updateDynamicZeroSuggestions(raw);
        } else {
            dynamicZeroContainer.innerHTML = '';
        }
    }

    salaryInput.addEventListener('input', handleSalaryInput);

    if (btnClear) {
        btnClear.addEventListener('click', function(e) {
            e.preventDefault();
            salaryInput.value = '';
            dynamicZeroContainer.innerHTML = '';
            salaryInput.focus();
        });
    }

    // --- ĐỊA CHỈ LÀM VIỆC & GOOGLE MAPS ---
    const workplaceMapUrlInput = document.getElementById('workplace_map_url_input');
    const customMapBox = document.getElementById('custom_map_url_box');

    function updateMapPreview(address, explicitMapUrl = null) {
        let finalUrl = explicitMapUrl || (workplaceMapUrlInput ? workplaceMapUrlInput.value : '');
        if (!finalUrl && address && address.trim()) {
            finalUrl = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(address.trim());
        }
        if (finalUrl) {
            btnPreviewMap.href = finalUrl;
            btnPreviewMap.style.display = 'inline-flex';
        } else {
            btnPreviewMap.style.display = 'none';
        }
    }

    if (workplaceSelect) {
        workplaceSelect.addEventListener('change', function() {
            if (this.value !== 'custom') {
                workplaceInput.value = this.value;
                const opt = this.options[this.selectedIndex];
                const mapUrl = opt ? (opt.getAttribute('data-map-url') || '') : '';
                if (workplaceMapUrlInput) workplaceMapUrlInput.value = mapUrl;
                if (customMapBox) customMapBox.style.display = 'none';
                updateMapPreview(this.value, mapUrl);
            } else {
                workplaceInput.value = '';
                if (workplaceMapUrlInput) workplaceMapUrlInput.value = '';
                if (customMapBox) customMapBox.style.display = 'block';
                workplaceInput.focus();
            }
        });
    }

    if (workplaceInput) {
        workplaceInput.addEventListener('input', function() {
            updateMapPreview(this.value);
        });
    }

    if (workplaceMapUrlInput) {
        workplaceMapUrlInput.addEventListener('input', function() {
            if (this.value.trim()) {
                btnPreviewMap.href = this.value.trim();
            } else {
                updateMapPreview(workplaceInput.value);
            }
        });
    }

    // --- CẤU HÌNH & CHỌN CA LÀM VIỆC (WORK SHIFTS) ---
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function parseTimeFromStr(str) {
        if (!str) return { name: '', start: '08:00', end: '17:00' };
        let name = '';
        let timePart = str;
        const parenMatch = str.match(/^(.*?)\((.*?)\)$/);
        if (parenMatch) {
            name = parenMatch[1].trim();
            timePart = parenMatch[2].trim();
        }
        if (/^[\d\s:hH\-–\.\/]+$/.test(name)) {
            name = '';
        }
        const matches = [...timePart.matchAll(/(\d{1,2})(?:[h:H:](\d{1,2})?)?/g)];
        let start = '08:00';
        let end = '17:00';
        if (matches.length > 0 && matches[0][1]) {
            const h = String(matches[0][1]).padStart(2, '0');
            const m = matches[0][2] ? String(matches[0][2]).padStart(2, '0') : '00';
            start = `${h}:${m}`;
        }
        if (matches.length > 1 && matches[1][1]) {
            const h = String(matches[1][1]).padStart(2, '0');
            const m = matches[1][2] ? String(matches[1][2]).padStart(2, '0') : '00';
            end = `${h}:${m}`;
        }
        return { name, start, end };
    }

    function syncCombinedWorkShift() {
        const start = shiftStartTimeInput ? shiftStartTimeInput.value : '08:00';
        const end = shiftEndTimeInput ? shiftEndTimeInput.value : '17:00';
        const name = shiftCustomNameInput ? shiftCustomNameInput.value.trim() : '';

        const timeRange = `${start} - ${end}`;
        const combined = name ? `${name} (${timeRange})` : timeRange;

        if (workShiftInput) {
            workShiftInput.value = combined;
            clearTimeout(benchmarkDebounceTimer);
            benchmarkDebounceTimer = setTimeout(fetchSalaryBenchmark, 300);
        }
        syncShiftActivePill(combined);
        syncShiftSelect(combined);
    }

    function syncShiftActivePill(val) {
        if (!quickShiftsPillsContainer) return;
        const pills = quickShiftsPillsContainer.querySelectorAll('.shift-pill-btn');
        pills.forEach(pill => {
            const pVal = pill.getAttribute('data-val');
            const match = (pVal && pVal.trim() === (val || '').trim());
            pill.classList.toggle('active', match);
            if (match) {
                pill.style.borderColor = '#0d9488';
                pill.style.backgroundColor = '#0d9488';
                pill.style.color = '#ffffff';
            } else {
                pill.style.borderColor = '#cbd5e1';
                pill.style.backgroundColor = '#e2e8f0';
                pill.style.color = '#1e293b';
            }
        });
    }

    function syncShiftSelect(val) {
        if (!workShiftSelect) return;
        let matched = false;
        for (let i = 0; i < workShiftSelect.options.length; i++) {
            if (workShiftSelect.options[i].value && workShiftSelect.options[i].value === val) {
                workShiftSelect.selectedIndex = i;
                matched = true;
                break;
            }
        }
        if (!matched) {
            if (val && val.trim()) {
                workShiftSelect.value = '__custom__';
            } else {
                workShiftSelect.selectedIndex = 0;
            }
        }
    }

    if (quickShiftsPillsContainer) {
        quickShiftsPillsContainer.addEventListener('click', function(e) {
            const pill = e.target.closest('.shift-pill-btn');
            if (!pill) return;
            const val = pill.getAttribute('data-val') || '';
            const pName = pill.getAttribute('data-name') || '';
            const pTime = pill.getAttribute('data-time') || '';

            const parsed = parseTimeFromStr(pTime || val);
            if (shiftStartTimeInput) shiftStartTimeInput.value = parsed.start;
            if (shiftEndTimeInput) shiftEndTimeInput.value = parsed.end;
            if (shiftCustomNameInput) shiftCustomNameInput.value = pName || parsed.name;

            syncCombinedWorkShift();
        });
    }

    if (workShiftSelect) {
        workShiftSelect.addEventListener('change', function() {
            if (this.value === '__custom__') {
                if (shiftStartTimeInput) shiftStartTimeInput.focus();
                syncShiftActivePill('');
            } else if (this.value) {
                const opt = this.options[this.selectedIndex];
                const pName = opt ? (opt.getAttribute('data-name') || '') : '';
                const pTime = opt ? (opt.getAttribute('data-time') || '') : '';
                const parsed = parseTimeFromStr(pTime || this.value);
                if (shiftStartTimeInput) shiftStartTimeInput.value = parsed.start;
                if (shiftEndTimeInput) shiftEndTimeInput.value = parsed.end;
                if (shiftCustomNameInput) shiftCustomNameInput.value = pName || parsed.name;
                syncCombinedWorkShift();
            } else {
                syncShiftActivePill('');
            }
        });
    }

    if (shiftStartTimeInput) {
        shiftStartTimeInput.addEventListener('change', syncCombinedWorkShift);
    }
    if (shiftEndTimeInput) {
        shiftEndTimeInput.addEventListener('change', syncCombinedWorkShift);
    }
    if (shiftCustomNameInput) {
        shiftCustomNameInput.addEventListener('input', syncCombinedWorkShift);
    }

    // Modal thêm ca làm mới nhanh
    function openShiftModal() {
        if (!modalAddShift) return;
        modalAddShift.style.display = 'flex';
        if (modalShiftError) modalShiftError.style.display = 'none';
        if (modalShiftName) {
            modalShiftName.value = '';
            modalShiftName.focus();
        }
        if (modalShiftStartTime) modalShiftStartTime.value = '08:00';
        if (modalShiftEndTime) modalShiftEndTime.value = '17:00';
        if (modalShiftDesc) modalShiftDesc.value = '';
    }

    function closeShiftModal() {
        if (!modalAddShift) return;
        modalAddShift.style.display = 'none';
    }

    if (btnOpenModalShift) {
        btnOpenModalShift.addEventListener('click', openShiftModal);
    }
    if (btnCloseModalShift) {
        btnCloseModalShift.addEventListener('click', closeShiftModal);
    }
    if (btnCancelModalShift) {
        btnCancelModalShift.addEventListener('click', closeShiftModal);
    }
    if (modalAddShift) {
        modalAddShift.addEventListener('click', function(e) {
            if (e.target === modalAddShift) {
                closeShiftModal();
            }
        });
    }

    if (btnSubmitModalShift) {
        btnSubmitModalShift.addEventListener('click', function() {
            const name = modalShiftName ? modalShiftName.value.trim() : '';
            const startTime = modalShiftStartTime ? modalShiftStartTime.value : '08:00';
            const endTime = modalShiftEndTime ? modalShiftEndTime.value : '17:00';
            const time = `${startTime} - ${endTime}`;
            const desc = modalShiftDesc ? modalShiftDesc.value.trim() : '';

            if (!name && !time) {
                if (modalShiftError) {
                    modalShiftError.textContent = 'Vui lòng nhập tên ca làm hoặc khung giờ làm việc!';
                    modalShiftError.style.display = 'block';
                }
                return;
            }

            btnSubmitModalShift.disabled = true;
            btnSubmitModalShift.innerHTML = '<span>Đang lưu...</span>';

            let baseUrl = (typeof window.APP_URL !== 'undefined' ? window.APP_URL : '');
            if (!baseUrl) {
                const segments = window.location.pathname.split('/').filter(Boolean);
                if (segments.length > 0 && segments[0].toLowerCase() === 'joblink') {
                    baseUrl = '/' + segments[0];
                }
            }
            const saveUrl = (baseUrl ? baseUrl.replace(/\/+$/, '') : '') + '/api/employer/shifts/save';

            fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'add_single',
                    name: name,
                    start_time: startTime,
                    end_time: endTime,
                    time: time,
                    description: desc
                })
            })
            .then(res => res.json())
            .then(data => {
                btnSubmitModalShift.disabled = false;
                btnSubmitModalShift.innerHTML = '<?= icon('check_circle', '', 14) ?> <span>Lưu & Áp Dụng Ngay</span>';

                if (data && data.success) {
                    closeShiftModal();

                    const shiftVal = data.label || (name + (time ? ' (' + time + ')' : ''));
                    const optText = '[' + (name || 'Ca mới') + ']: ' + time + (desc ? ' - ' + desc : '');

                    // Thêm option vào dropdown
                    if (workShiftSelect) {
                        const customOpt = workShiftSelect.querySelector('option[value="__custom__"]');
                        const newOpt = document.createElement('option');
                        newOpt.value = shiftVal;
                        newOpt.setAttribute('data-name', name);
                        newOpt.setAttribute('data-time', time);
                        newOpt.setAttribute('data-desc', desc);
                        newOpt.textContent = optText;
                        if (customOpt) {
                            workShiftSelect.insertBefore(newOpt, customOpt);
                        } else {
                            workShiftSelect.appendChild(newOpt);
                        }
                        workShiftSelect.value = shiftVal;
                    }

                    // Thêm pill vào quick pills
                    if (quickShiftsPillsContainer) {
                        const noShiftsHint = document.getElementById('no_shifts_hint');
                        if (noShiftsHint) noShiftsHint.remove();

                        const newPill = document.createElement('button');
                        newPill.type = 'button';
                        newPill.className = 'shift-pill-btn active';
                        newPill.setAttribute('data-val', shiftVal);
                        newPill.setAttribute('data-name', name);
                        newPill.setAttribute('data-time', time);
                        newPill.style.cssText = 'border: 1px solid #0d9488; background: #f0fdfa; color: #0d9488; padding: 0.2rem 0.55rem; border-radius: 16px; font-size: 0.78rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; transition: all 0.15s ease;';
                        newPill.innerHTML = `<?= icon('clock', '', 11) ?> <span>${escapeHtml(name)}${time ? ' (' + escapeHtml(time) + ')' : ''}</span>`;
                        quickShiftsPillsContainer.appendChild(newPill);
                    }

                    // Cập nhật 2 ô giờ và tên ca
                    if (shiftStartTimeInput) shiftStartTimeInput.value = startTime;
                    if (shiftEndTimeInput) shiftEndTimeInput.value = endTime;
                    if (shiftCustomNameInput) shiftCustomNameInput.value = name;
                    syncCombinedWorkShift();
                } else {
                    if (modalShiftError) {
                        modalShiftError.textContent = (data && data.message) ? data.message : 'Có lỗi xảy ra khi lưu ca làm việc.';
                        modalShiftError.style.display = 'block';
                    }
                }
            })
            .catch(err => {
                btnSubmitModalShift.disabled = false;
                btnSubmitModalShift.innerHTML = '<?= icon('check_circle', '', 14) ?> <span>Lưu & Áp Dụng Ngay</span>';
                if (modalShiftError) {
                    modalShiftError.textContent = 'Lỗi kết nối máy chủ. Vui lòng thử lại!';
                    modalShiftError.style.display = 'block';
                }
            });
        });
    }

    // --- LỊCH & NGÀY LÀM VIỆC: FULL-TIME & BỘ LỊCH THÁNG (PART-TIME / THỜI VỤ) ---

    // 1. XỬ LÝ LỊCH TOÀN THỜI GIAN (FULL-TIME)
    function syncFulltimeSchedule() {
        const activeDays = [];
        document.querySelectorAll('.btn-ft-day.active').forEach(b => {
            activeDays.push(b.getAttribute('data-day'));
        });

        let daysText = '';
        const all7 = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];
        const t2_t7 = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
        const t2_t6 = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6'];

        const isMatch = (arr1, arr2) => arr1.length === arr2.length && arr1.every((v, i) => v === arr2[i]);

        if (isMatch(activeDays, all7)) {
            daysText = 'Cả tuần (Thứ 2 - Chủ Nhật)';
        } else if (isMatch(activeDays, t2_t7)) {
            daysText = 'Thứ 2 - Thứ 7';
        } else if (isMatch(activeDays, t2_t6)) {
            daysText = 'Thứ 2 - Thứ 6';
        } else if (activeDays.length > 0) {
            daysText = activeDays.join(', ');
        } else {
            daysText = 'Linh hoạt theo thỏa thuận';
        }

        const perks = [];
        document.querySelectorAll('.ft-perk-card.active').forEach(card => {
            const cb = card.querySelector('.ft-perk-cb');
            if (cb && cb.checked) {
                const shortLabel = card.getAttribute('data-label') || cb.value;
                perks.push(shortLabel);
            }
        });

        if (perks.length > 0) {
            workingDaysInput.value = `${daysText} (${perks.join('; ')})`;
        } else {
            workingDaysInput.value = daysText;
        }

        // Cập nhật highlight cho nút mẫu nhanh tương ứng
        document.querySelectorAll('.btn-ft-preset').forEach(b => {
            const preset = b.getAttribute('data-preset');
            let isCurrent = false;
            if (preset === '2-6' && isMatch(activeDays, t2_t6)) isCurrent = true;
            else if (preset === '2-7' && isMatch(activeDays, t2_t7)) isCurrent = true;
            else if (preset === 'all' && isMatch(activeDays, all7)) isCurrent = true;

            if (isCurrent) {
                b.style.background = '#0d9488';
                b.style.borderColor = '#0d9488';
                b.style.color = '#ffffff';
                b.style.fontWeight = '700';
            } else {
                b.style.background = preset === 'all' ? '#eef2ff' : '#f0fdfa';
                b.style.borderColor = preset === 'all' ? '#6366f1' : '#0d9488';
                b.style.color = preset === 'all' ? '#4338ca' : '#0f766e';
                b.style.fontWeight = '600';
            }
        });
    }

    // Bấm chọn các thứ trong tuần của Full-time
    document.querySelectorAll('.btn-ft-day').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            const sub = this.querySelector('.ft-day-sub');
            if (this.classList.contains('active')) {
                if (sub) sub.textContent = 'Làm việc';
            } else {
                if (sub) sub.textContent = 'Nghỉ tuần';
            }
            syncFulltimeSchedule();
        });
    });

    // Mẫu nhanh Full-time (2-6, 2-7, all)
    document.querySelectorAll('.btn-ft-preset').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const preset = this.getAttribute('data-preset');

            document.querySelectorAll('.btn-ft-day').forEach(b => {
                const day = b.getAttribute('data-day');
                let shouldActive = false;
                if (preset === '2-6') {
                    shouldActive = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6'].includes(day);
                } else if (preset === '2-7') {
                    shouldActive = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'].includes(day);
                } else if (preset === 'all') {
                    shouldActive = true;
                }

                const sub = b.querySelector('.ft-day-sub');
                if (shouldActive) {
                    b.classList.add('active');
                    if (sub) sub.textContent = 'Làm việc';
                } else {
                    b.classList.remove('active');
                    if (sub) sub.textContent = 'Nghỉ tuần';
                }
            });

            syncFulltimeSchedule();
        });
    });

    // Checkbox ưu đãi & chính sách nghỉ lễ
    document.querySelectorAll('.ft-perk-card').forEach(card => {
        const cb = card.querySelector('.ft-perk-cb');
        if (!cb) return;

        card.addEventListener('click', function(e) {
            if (e.target !== cb) {
                cb.checked = !cb.checked;
            }
            if (cb.checked) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
            syncFulltimeSchedule();
        });

        cb.addEventListener('change', function() {
            if (cb.checked) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
            syncFulltimeSchedule();
        });
    });

    // 2. XỬ LÝ BỘ LỊCH THÁNG CHUẨN (PART-TIME / TEMPORARY)
    function renderMonthCalendar() {
        if (!calDaysMatrix || !calHeaderTitle) return;

        // Header tiêu đề: Tháng [M] [YYYY]
        calHeaderTitle.textContent = `Tháng ${calCurrentMonth + 1} ${calCurrentYear}`;
        calDaysMatrix.innerHTML = '';

        const firstDayIndex = new Date(calCurrentYear, calCurrentMonth, 1).getDay(); // 0 = CN, 1 = T2...
        const daysInCurrentMonth = new Date(calCurrentYear, calCurrentMonth + 1, 0).getDate();
        const daysInPrevMonth = new Date(calCurrentYear, calCurrentMonth, 0).getDate();

        const today = new Date();
        const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

        // Đúng chuẩn 42 ô (6 hàng x 7 cột) như ảnh mẫu
        const totalGridCells = 42;

        // Ô tháng trước (Trailing days)
        const prevYear = calCurrentMonth === 0 ? calCurrentYear - 1 : calCurrentYear;
        const prevMonth = calCurrentMonth === 0 ? 11 : calCurrentMonth - 1;
        for (let i = firstDayIndex - 1; i >= 0; i--) {
            const dayNum = daysInPrevMonth - i;
            const dateStr = `${prevYear}-${String(prevMonth + 1).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
            createCalendarCell(dayNum, dateStr, 'adjacent-month');
        }

        // Ô tháng hiện tại
        for (let dayNum = 1; dayNum <= daysInCurrentMonth; dayNum++) {
            const dateStr = `${calCurrentYear}-${String(calCurrentMonth + 1).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
            const isToday = dateStr === todayStr;
            createCalendarCell(dayNum, dateStr, 'current-month', isToday);
        }

        // Ô tháng sau (Leading days)
        const cellsRendered = firstDayIndex + daysInCurrentMonth;
        const remainingCells = totalGridCells - cellsRendered;
        const nextYear = calCurrentMonth === 11 ? calCurrentYear + 1 : calCurrentYear;
        const nextMonth = calCurrentMonth === 11 ? 0 : calCurrentMonth + 1;

        for (let dayNum = 1; dayNum <= remainingCells; dayNum++) {
            const dateStr = `${nextYear}-${String(nextMonth + 1).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
            createCalendarCell(dayNum, dateStr, 'adjacent-month');
        }

        updateCalChipsAndBadge();
    }

    function createCalendarCell(dayNum, dateStr, monthType, isToday = false) {
        const wrapper = document.createElement('div');
        wrapper.className = 'cal-day-cell';

        const circle = document.createElement('div');
        circle.className = `cal-day-circle ${monthType}` + (isToday ? ' is-today' : '');
        circle.textContent = dayNum;
        circle.setAttribute('data-date', dateStr);

        if (selectedCalendarDates.includes(dateStr)) {
            circle.classList.add('selected');
        }

        circle.addEventListener('click', function(e) {
            e.preventDefault();
            const d = this.getAttribute('data-date');
            if (selectedCalendarDates.includes(d)) {
                selectedCalendarDates = selectedCalendarDates.filter(x => x !== d);
                this.classList.remove('selected');
            } else {
                selectedCalendarDates.push(d);
                this.classList.add('selected');
            }
            updateCalChipsAndBadge();
            syncParttimeSchedule();
        });

        wrapper.appendChild(circle);
        calDaysMatrix.appendChild(wrapper);
    }

    function updateCalChipsAndBadge() {
        if (calSelectedCounterBadge) {
            if (selectedCalendarDates.length === 0) {
                calSelectedCounterBadge.textContent = 'Chưa chọn ngày nào';
                calSelectedCounterBadge.style.background = '#f1f5f9';
                calSelectedCounterBadge.style.color = '#64748b';
                calSelectedCounterBadge.style.borderColor = '#cbd5e1';
            } else {
                calSelectedCounterBadge.textContent = `Đã chọn: ${selectedCalendarDates.length} ngày`;
                calSelectedCounterBadge.style.background = '#f0fdfa';
                calSelectedCounterBadge.style.color = '#0d9488';
                calSelectedCounterBadge.style.borderColor = '#99f6e4';
            }
        }

        if (calChipsContainer && calChipsList) {
            if (selectedCalendarDates.length === 0) {
                calChipsContainer.style.display = 'none';
                calChipsList.innerHTML = '';
            } else {
                calChipsContainer.style.display = 'block';
                calChipsList.innerHTML = '';

                // Sắp xếp ngày tăng dần
                const sorted = selectedCalendarDates.slice().sort();
                sorted.forEach(dStr => {
                    const parts = dStr.split('-');
                    const displayStr = `${parts[2]}/${parts[1]}/${parts[0]}`;
                    const chip = document.createElement('span');
                    chip.className = 'cal-chip-item';
                    chip.innerHTML = `<span>${displayStr}</span> <span class="cal-chip-remove" data-date="${dStr}" title="Bỏ chọn">&times;</span>`;
                    chip.querySelector('.cal-chip-remove').addEventListener('click', function(e) {
                        e.stopPropagation();
                        const removeDate = this.getAttribute('data-date');
                        selectedCalendarDates = selectedCalendarDates.filter(x => x !== removeDate);
                        renderMonthCalendar();
                        syncParttimeSchedule();
                    });
                    calChipsList.appendChild(chip);
                });
            }
        }
    }

    function syncParttimeSchedule() {
        if (selectedCalendarDates.length === 0) {
            if (!workingDaysInput.value || workingDaysInput.value.includes('Lịch ngày:')) {
                workingDaysInput.value = 'Xoay ca linh hoạt hàng tuần';
            }
            return;
        }

        const sorted = selectedCalendarDates.slice().sort();
        const formattedDates = sorted.map(dStr => {
            const p = dStr.split('-');
            return `${p[2]}/${p[1]}`;
        });

        if (formattedDates.length <= 6) {
            workingDaysInput.value = `Lịch ngày: ${formattedDates.join(', ')} (${formattedDates.length} buổi)`;
        } else {
            const preview = formattedDates.slice(0, 5).join(', ');
            workingDaysInput.value = `Lịch ngày: ${preview}... (Tổng ${formattedDates.length} buổi)`;
        }
    }

    // Điều hướng Tháng Lịch: Tháng trước (▲) & Tháng sau (▼)
    if (btnCalPrev) {
        btnCalPrev.addEventListener('click', function(e) {
            e.preventDefault();
            calCurrentMonth--;
            if (calCurrentMonth < 0) {
                calCurrentMonth = 11;
                calCurrentYear--;
            }
            renderMonthCalendar();
        });
    }

    if (btnCalNext) {
        btnCalNext.addEventListener('click', function(e) {
            e.preventDefault();
            calCurrentMonth++;
            if (calCurrentMonth > 11) {
                calCurrentMonth = 0;
                calCurrentYear++;
            }
            renderMonthCalendar();
        });
    }

    // Mẫu chọn nhanh ngày trên lịch tháng
    document.querySelectorAll('.btn-cal-quick-pick').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const pattern = this.getAttribute('data-pattern');
            const daysInMonth = new Date(calCurrentYear, calCurrentMonth + 1, 0).getDate();

            if (pattern === 'flexible') {
                selectedCalendarDates = [];
                workingDaysInput.value = 'Xoay ca linh hoạt hàng tuần';
                renderMonthCalendar();
                return;
            }

            if (pattern === 'all-month') {
                for (let d = 1; d <= daysInMonth; d++) {
                    const dStr = `${calCurrentYear}-${String(calCurrentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    if (!selectedCalendarDates.includes(dStr)) {
                        selectedCalendarDates.push(dStr);
                    }
                }
            } else {
                for (let d = 1; d <= daysInMonth; d++) {
                    const dt = new Date(calCurrentYear, calCurrentMonth, d);
                    const dayOfWeek = dt.getDay(); // 0 = CN, 1 = T2, 2 = T3, 3 = T4, 4 = T5, 5 = T6, 6 = T7
                    let match = false;

                    if (pattern === '2-4-6' && [1, 3, 5].includes(dayOfWeek)) match = true;
                    if (pattern === '3-5-7' && [2, 4, 6].includes(dayOfWeek)) match = true;
                    if (pattern === 'weekend' && [0, 6].includes(dayOfWeek)) match = true;

                    if (match) {
                        const dStr = `${calCurrentYear}-${String(calCurrentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        if (!selectedCalendarDates.includes(dStr)) {
                            selectedCalendarDates.push(dStr);
                        }
                    }
                }
            }

            renderMonthCalendar();
            syncParttimeSchedule();
        });
    });

    if (btnCalClearAll) {
        btnCalClearAll.addEventListener('click', function(e) {
            e.preventDefault();
            selectedCalendarDates = [];
            renderMonthCalendar();
            workingDaysInput.value = '';
            workingDaysInput.focus();
        });
    }

    function updateTypeDetails() {
        const val = typeSelect.value || 'full_time';
        const cfg = config[val] || config['full_time'];
        salaryLabel.textContent = cfg.label;
        salaryAddon.textContent = cfg.addon;
        if (!salaryInput.value) {
            salaryInput.placeholder = cfg.placeholder;
        }
        fetchSalaryBenchmark();
        handleSalaryInput();

        // Cập nhật chế độ lịch làm việc
        if (val === 'full_time') {
            scheduleModeBadge.textContent = 'Toàn thời gian (1 tháng)';
            scheduleModeBadge.className = 'badge badge-primary';
            fulltimeBox.style.display = 'block';
            parttimeBox.style.display = 'none';
            if (!workingDaysInput.value || workingDaysInput.value.includes('Lịch ngày:') || workingDaysInput.value.includes('Xoay ca')) {
                syncFulltimeSchedule();
            }
        } else {
            scheduleModeBadge.textContent = val === 'part_time' ? 'Bán thời gian (Tự chọn ngày)' : 'Thời vụ / Ca (Tự chọn ngày)';
            scheduleModeBadge.className = 'badge badge-warning';
            fulltimeBox.style.display = 'none';
            parttimeBox.style.display = 'block';
            renderMonthCalendar();
            if (!workingDaysInput.value || workingDaysInput.value.includes('Thứ 2 - Thứ 7')) {
                syncParttimeSchedule();
            }
        }
    }

    typeSelect.addEventListener('change', () => {
        updateTypeDetails();
        fetchSalaryBenchmark();
    });

    if (jobTitleInput) {
        jobTitleInput.addEventListener('input', function() {
            clearTimeout(benchmarkDebounceTimer);
            benchmarkDebounceTimer = setTimeout(fetchSalaryBenchmark, 300);
        });
    }

    if (jobReqInput) {
        jobReqInput.addEventListener('input', function() {
            clearTimeout(benchmarkDebounceTimer);
            benchmarkDebounceTimer = setTimeout(fetchSalaryBenchmark, 300);
        });
    }

    if (workShiftInput) {
        workShiftInput.addEventListener('input', function() {
            clearTimeout(benchmarkDebounceTimer);
            benchmarkDebounceTimer = setTimeout(fetchSalaryBenchmark, 300);
        });
    }

    // Tiêu chuẩn người lao động: Độ tuổi, Giới tính, Học vấn, Kinh nghiệm, Kỹ năng
    const cbNoAgeLimit = document.getElementById('cb_no_age_limit');
    const ageInputsBox = document.getElementById('age_inputs_box');
    const minAgeInput = document.getElementById('min_age_input');
    const maxAgeInput = document.getElementById('max_age_input');

    if (cbNoAgeLimit && ageInputsBox) {
        cbNoAgeLimit.addEventListener('change', function() {
            if (this.checked) {
                ageInputsBox.style.opacity = '0.45';
                ageInputsBox.style.pointerEvents = 'none';
                if (minAgeInput) minAgeInput.value = '';
                if (maxAgeInput) maxAgeInput.value = '';
            } else {
                ageInputsBox.style.opacity = '1';
                ageInputsBox.style.pointerEvents = 'auto';
                if (minAgeInput && !minAgeInput.value) minAgeInput.value = '18';
                if (maxAgeInput && !maxAgeInput.value) maxAgeInput.value = '35';
            }
        });
    }

    document.querySelectorAll('.btn-age-preset').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.btn-age-preset').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const isAll = this.getAttribute('data-all') === '1';
            if (isAll) {
                if (cbNoAgeLimit) cbNoAgeLimit.checked = true;
                if (ageInputsBox) {
                    ageInputsBox.style.opacity = '0.45';
                    ageInputsBox.style.pointerEvents = 'none';
                }
                if (minAgeInput) minAgeInput.value = '';
                if (maxAgeInput) maxAgeInput.value = '';
            } else {
                if (cbNoAgeLimit) cbNoAgeLimit.checked = false;
                if (ageInputsBox) {
                    ageInputsBox.style.opacity = '1';
                    ageInputsBox.style.pointerEvents = 'auto';
                }
                const min = this.getAttribute('data-min') || '';
                const max = this.getAttribute('data-max') || '';
                if (minAgeInput) minAgeInput.value = min;
                if (maxAgeInput) maxAgeInput.value = max;
            }
        });
    });

    [minAgeInput, maxAgeInput].forEach(inp => {
        if (inp) {
            inp.addEventListener('input', function() {
                document.querySelectorAll('.btn-age-preset').forEach(b => b.classList.remove('active'));
            });
        }
    });

    // Giới tính: Chỉ chọn đúng 1 trong 3 (Tất cả, Nam hoặc Nữ)
    const genderInput = document.getElementById('gender_requirement_input');
    document.querySelectorAll('.gender-pill-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.gender-pill-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (genderInput) {
                genderInput.value = this.getAttribute('data-val');
            }
        });
    });

    // Checkbox kỹ năng bổ sung
    document.querySelectorAll('.req-trait-card').forEach(card => {
        const cb = card.querySelector('input[type="checkbox"]');
        if (!cb) return;
        cb.addEventListener('change', () => {
            card.classList.toggle('active', cb.checked);
        });
    });

    // Kinh nghiệm & Học vấn -> Tự động cập nhật mức lương gợi ý
    const expReqSelect = document.getElementById('experience_required_select');
    if (expReqSelect) {
        expReqSelect.addEventListener('change', () => {
            fetchSalaryBenchmark();
        });
    }
    const eduLevelSelect = document.getElementById('education_level_select');
    if (eduLevelSelect) {
        eduLevelSelect.addEventListener('change', () => {
            fetchSalaryBenchmark();
        });
    }

    // Khởi tạo trạng thái ban đầu từ dữ liệu có sẵn
    const initialWorkingDays = workingDaysInput ? workingDaysInput.value : '';
    if (initialWorkingDays) {
        const curType = typeSelect ? typeSelect.value : 'full_time';
        if (curType === 'full_time') {
            if (initialWorkingDays.includes('Thứ 2 - Thứ 6')) {
                document.querySelectorAll('.btn-ft-day').forEach(b => {
                    const d = b.getAttribute('data-day');
                    const act = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6'].includes(d);
                    b.classList.toggle('active', act);
                    const sub = b.querySelector('.ft-day-sub');
                    if (sub) sub.textContent = act ? 'Làm việc' : 'Nghỉ tuần';
                });
            } else if (initialWorkingDays.includes('Cả tuần')) {
                document.querySelectorAll('.btn-ft-day').forEach(b => {
                    b.classList.add('active');
                    const sub = b.querySelector('.ft-day-sub');
                    if (sub) sub.textContent = 'Làm việc';
                });
            }

            // Đồng bộ checkbox ưu đãi
            document.querySelectorAll('.ft-perk-card').forEach(card => {
                const cb = card.querySelector('.ft-perk-cb');
                const label = card.getAttribute('data-label') || '';
                if (cb && label) {
                    const isChecked = initialWorkingDays.toLowerCase().includes(label.toLowerCase());
                    cb.checked = isChecked;
                    card.classList.toggle('active', isChecked);
                }
            });
        } else {
            const matches = initialWorkingDays.match(/\b(\d{1,2})\/(\d{1,2})(?:\/(\d{4}))?\b/g);
            if (matches && matches.length > 0) {
                matches.forEach(m => {
                    const p = m.split('/');
                    const day = p[0].padStart(2, '0');
                    const month = p[1].padStart(2, '0');
                    const year = p[2] || calCurrentYear;
                    const dateStr = `${year}-${month}-${day}`;
                    if (!selectedCalendarDates.includes(dateStr)) {
                        selectedCalendarDates.push(dateStr);
                    }
                });
                const firstDateParts = selectedCalendarDates[0].split('-');
                calCurrentYear = parseInt(firstDateParts[0], 10);
                calCurrentMonth = parseInt(firstDateParts[1], 10) - 1;
            }
        }
    }

    renderMonthCalendar();
    updateTypeDetails();
    if (workplaceInput) {
        updateMapPreview(workplaceInput.value);
    }
    if (workShiftInput && workShiftInput.value) {
        const parsed = parseTimeFromStr(workShiftInput.value);
        if (shiftStartTimeInput && !shiftStartTimeInput.value) shiftStartTimeInput.value = parsed.start;
        if (shiftEndTimeInput && !shiftEndTimeInput.value) shiftEndTimeInput.value = parsed.end;
        if (shiftCustomNameInput && !shiftCustomNameInput.value) shiftCustomNameInput.value = parsed.name;
        syncShiftActivePill(workShiftInput.value);
        syncShiftSelect(workShiftInput.value);
    } else {
        syncCombinedWorkShift();
    }
    setTimeout(fetchSalaryBenchmark, 150);
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
