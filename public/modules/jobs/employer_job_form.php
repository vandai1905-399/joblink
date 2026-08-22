<?php 
ob_start(); 

if (!isset($employer) && isset($_SESSION['user']['account_id'])) {
    $employer = UserEmployer::findByAccountId($_SESSION['user']['account_id']);
}
$branches = !empty($employer['branches']) ? json_decode($employer['branches'], true) : [];
if (!is_array($branches)) $branches = [];
?>

<div style="max-width: 850px;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">
            <?= isset($job) ? 'Chỉnh Sửa Tin Tuyển Dụng' : 'Đăng Tin Tuyển Dụng Mới' ?>
        </h1>
        <p style="color: var(--slate-500); font-size: 0.95rem;">Điền đầy đủ thông tin ca làm việc và yêu cầu tuyển dụng</p>
    </div>

    <div class="card">
        <form action="<?= isset($job) ? url('/employer/jobs/update/' . $job['job_post_id']) : url('/employer/jobs/store') ?>" method="POST" id="jobPostForm">
            <div class="form-group">
                <label class="form-label">Chức Danh Công Việc *</label>
                <input type="text" name="job_title" value="<?= htmlspecialchars($job['job_title'] ?? '') ?>" class="form-control" required placeholder="VD: Nhân viên pha chế / Chuyên viên kỹ thuật">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Hình Thức Làm Việc *</label>
                    <select name="employment_type" id="employment_type_select" class="form-control" required>
                        <option value="full_time" <?= ($job['employment_type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Toàn thời gian (Full-time)</option>
                        <option value="part_time" <?= ($job['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Bán thời gian / Theo ca (Part-time)</option>
                        <option value="temporary" <?= ($job['employment_type'] ?? '') === 'temporary' ? 'selected' : '' ?>>Thời vụ (Temporary)</option>
                    </select>
                </div>
                <div class="form-group">
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

                    <!-- Gợi ý số 0 thông minh theo số vừa gõ & Mức phổ biến -->
                    <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.45rem;">
                        <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                            <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">Gợi ý số tiền:</span>
                            <div id="dynamic_zero_suggestions" style="display: flex; gap: 0.35rem; flex-wrap: wrap;"></div>
                            <button type="button" id="btn_clear_salary" style="padding: 0.15rem 0.5rem; font-size: 0.75rem; border-radius: 5px; border: 1px solid #fca5a5; background: #fef2f2; cursor: pointer; font-weight: 600; color: #b91c1c; margin-left: auto;">Xóa</button>
                        </div>

                        <!-- Mức lương mẫu phổ biến nếu chưa gõ -->
                        <div id="salary_presets" style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                            <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">Mức phổ biến:</span>
                            <div id="preset_buttons" style="display: flex; gap: 0.35rem; flex-wrap: wrap;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ĐỊA ĐIỂM LÀM VIỆC (CHỌN TỪ CƠ SỞ / TRỤ SỞ HOẶC TỰ NHẬP) -->
            <div class="form-group" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.15rem; margin-bottom: 1.5rem;">
                <label class="form-label" style="font-weight: 700; color: #0f172a; font-size: 0.95rem; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                    <?= icon('building', '', 16) ?> <span>Địa Chỉ / Cơ Sở Làm Việc *</span>
                </label>
                <div style="font-size: 0.82rem; color: #64748b; margin-bottom: 0.6rem;">
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
                    <a id="btn_preview_map" href="<?= htmlspecialchars($initialMapUrl) ?>" target="_blank" class="btn btn-outline" style="white-space: nowrap; padding: 0.6rem 0.9rem; font-size: 0.85rem; font-weight: 700; color: #0d9488; border-color: #0d9488; background: #fff; display: inline-flex; align-items: center; gap: 0.35rem;" title="Xem vị trí trên Google Maps">
                        <?= icon('location', '', 14) ?> <span>Mở Maps</span>
                    </a>
                </div>

                <!-- Ô nhập link Google Maps tùy chọn nếu chọn tự nhập địa chỉ -->
                <div id="custom_map_url_box" style="margin-top: 0.5rem; <?= (isset($job) && !empty($job['workplace_map_url']) && !in_array($job['workplace_address'], array_merge([$employer['address'] ?? ''], array_column($branches, 'address')))) ? 'display:block;' : 'display:none;' ?>">
                    <label class="form-label" style="font-size: 0.82rem; color: #64748b; margin-bottom: 0.25rem;">
                        Link Google Maps vị trí làm việc (Tự chọn trên Google Maps):
                    </label>
                    <input type="url" name="workplace_map_url" id="workplace_map_url_input" value="<?= htmlspecialchars($job['workplace_map_url'] ?? ($employer['map_url'] ?? '')) ?>" class="form-control" placeholder="https://maps.app.goo.gl/... hoặc https://maps.google.com/..." style="font-size: 0.88rem;">
                </div>
            </div>

            <!-- LỊCH & NGÀY LÀM VIỆC TRONG TUẦN / THÁNG (BỘ CHỌN LỊCH TƯƠNG TÁC) -->
            <div class="form-group" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.15rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                    <label class="form-label" style="margin-bottom: 0; font-weight: 700; color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                        <?= icon('calendar', '', 16) ?> <span>Lịch & Ngày Làm Việc Trong Tuần / Tháng *</span>
                    </label>
                    <span id="schedule_mode_badge" class="badge badge-primary" style="font-size: 0.75rem;">Toàn thời gian (1 tháng)</span>
                </div>

                <div style="font-size: 0.82rem; color: #64748b; margin-bottom: 0.6rem;">
                    Thông tin hiển thị cho người tìm việc để biết ca làm theo các ngày trong tuần hoặc lịch cụ thể
                </div>

                <input type="text" name="working_days" id="working_days_input" value="<?= htmlspecialchars($job['working_days'] ?? 'Thứ 2 - Thứ 7 (Cả tháng)') ?>" class="form-control" required placeholder="VD: Thứ 2 - Thứ 7 hoặc các ngày chọn cụ thể" style="background: #fff; font-weight: 600; color: #0f172a; margin-bottom: 0.75rem;">

                <!-- Khối Tự Động Cho Toàn Thời Gian (Full-Time) -->
                <div id="fulltime_schedule_box" style="display: block; padding: 0.85rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 0.88rem; color: #166534;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                        <?= icon('check', '', 14) ?> <span>Đã tự động chọn trọn gói 1 tháng (Thứ 2 - Thứ 7)</span>
                    </div>
                    <div style="font-size: 0.8rem; color: #15803d; margin-top: 0.25rem;">
                        Vị trí Toàn thời gian sẽ tự động áp dụng lịch làm việc trọn tháng tiêu chuẩn.
                    </div>
                </div>

                <!-- Khối Bộ Chọn Lịch Tương Tác Cho Bán Thời Gian & Thời Vụ (Part-Time / Temporary) -->
                <div id="parttime_schedule_box" style="display: none;">
                    <div style="margin-bottom: 0.85rem;">
                        <div style="font-size: 0.82rem; font-weight: 700; color: #475569; margin-bottom: 0.4rem;">
                            1. Bấm chọn các thứ làm việc trong tuần:
                        </div>
                        <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;" id="weekday_buttons">
                            <button type="button" class="btn-weekday" data-day="Thứ 2" style="padding: 0.3rem 0.65rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600; color: #334155;">Thứ 2</button>
                            <button type="button" class="btn-weekday" data-day="Thứ 3" style="padding: 0.3rem 0.65rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600; color: #334155;">Thứ 3</button>
                            <button type="button" class="btn-weekday" data-day="Thứ 4" style="padding: 0.3rem 0.65rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600; color: #334155;">Thứ 4</button>
                            <button type="button" class="btn-weekday" data-day="Thứ 5" style="padding: 0.3rem 0.65rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600; color: #334155;">Thứ 5</button>
                            <button type="button" class="btn-weekday" data-day="Thứ 6" style="padding: 0.3rem 0.65rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600; color: #334155;">Thứ 6</button>
                            <button type="button" class="btn-weekday" data-day="Thứ 7" style="padding: 0.3rem 0.65rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600; color: #334155;">Thứ 7</button>
                            <button type="button" class="btn-weekday" data-day="Chủ Nhật" style="padding: 0.3rem 0.65rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600; color: #334155;">Chủ Nhật</button>
                        </div>
                        <!-- Preset mẫu nhanh -->
                        <div style="display: flex; gap: 0.35rem; margin-top: 0.5rem; flex-wrap: wrap; align-items: center;">
                            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Mẫu nhanh:</span>
                            <button type="button" class="btn-preset-days" data-days="Thứ 2, Thứ 4, Thứ 6" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-radius: 5px; border: 1px dashed #0d9488; background: #f0fdfa; color: #0f766e; font-weight: 600; cursor: pointer;">2 - 4 - 6</button>
                            <button type="button" class="btn-preset-days" data-days="Thứ 3, Thứ 5, Thứ 7" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-radius: 5px; border: 1px dashed #0d9488; background: #f0fdfa; color: #0f766e; font-weight: 600; cursor: pointer;">3 - 5 - 7</button>
                            <button type="button" class="btn-preset-days" data-days="Thứ 2 - Thứ 6" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-radius: 5px; border: 1px dashed #0d9488; background: #f0fdfa; color: #0f766e; font-weight: 600; cursor: pointer;">Thứ 2 - Thứ 6</button>
                            <button type="button" class="btn-preset-days" data-days="Thứ 7, Chủ Nhật" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-radius: 5px; border: 1px dashed #0d9488; background: #f0fdfa; color: #0f766e; font-weight: 600; cursor: pointer;">Cuối tuần (T7 & CN)</button>
                            <button type="button" class="btn-preset-days" data-days="Xoay ca linh hoạt hàng tuần" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-radius: 5px; border: 1px dashed #6366f1; background: #eef2ff; color: #4338ca; font-weight: 600; cursor: pointer;">Xoay ca linh hoạt</button>
                        </div>
                    </div>

                    <!-- Lưới lịch 30 ngày tới để bấm chọn cụ thể ngày -->
                    <div style="border-top: 1px dashed #cbd5e1; padding-top: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem;">
                            <span style="font-size: 0.82rem; font-weight: 700; color: #475569;">
                                2. Hoặc bấm chọn ngày cụ thể trên lịch (30 ngày tới):
                            </span>
                            <button type="button" id="btn_clear_calendar_days" style="font-size: 0.75rem; color: #b91c1c; background: none; border: none; cursor: pointer; text-decoration: underline; font-weight: 600;">Xóa ngày đã chọn</button>
                        </div>
                        <div id="calendar_grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(68px, 1fr)); gap: 0.35rem; max-height: 160px; overflow-y: auto; padding: 0.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;">
                            <!-- Sẽ được JavaScript render 30 ngày -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Ca Làm Việc</label>
                    <input type="text" name="work_shift" value="<?= htmlspecialchars($job['work_shift'] ?? '') ?>" class="form-control" placeholder="VD: Xoay ca 7h-12h hoặc Hành chính 8h-17h">
                </div>
                <div class="form-group">
                    <label class="form-label">Số Lượng Tuyển Dụng</label>
                    <input type="number" name="number_of_positions" value="<?= htmlspecialchars($job['number_of_positions'] ?? 1) ?>" class="form-control" min="1">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Hạn Nộp Hồ Sơ *</label>
                <input type="date" name="application_deadline" value="<?= htmlspecialchars($job['application_deadline'] ?? date('Y-m-d', strtotime('+30 days'))) ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Yêu Cầu Công Việc & Kỹ Năng *</label>
                <textarea name="job_requirements" class="form-control" rows="4" required placeholder="Mô tả kỹ năng, số năm kinh nghiệm hoặc yêu cầu tác phong..."><?= htmlspecialchars($job['job_requirements'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Quyền Lợi & Yêu Cầu Bổ Sung</label>
                <textarea name="additional_requirements" class="form-control" rows="3" placeholder="Ăn ca, phụ cấp, thưởng KPI..."><?= htmlspecialchars($job['additional_requirements'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">
                    <?= isset($job) ? 'Lưu Thay Đổi' : 'Đăng Tin Tuyển Dụng' ?>
                </button>
                <a href="<?= url('/employer/jobs') ?>" class="btn btn-outline">Hủy Bỏ</a>
            </div>
        </form>
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

    // Workplace elements
    const workplaceSelect = document.getElementById('workplace_select');
    const workplaceInput = document.getElementById('workplace_address_input');
    const btnPreviewMap = document.getElementById('btn_preview_map');

    // Schedule elements
    const scheduleModeBadge = document.getElementById('schedule_mode_badge');
    const workingDaysInput = document.getElementById('working_days_input');
    const fulltimeBox = document.getElementById('fulltime_schedule_box');
    const parttimeBox = document.getElementById('parttime_schedule_box');
    const calendarGrid = document.getElementById('calendar_grid');
    const btnClearCalendar = document.getElementById('btn_clear_calendar_days');

    let selectedWeekdays = [];
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
        dynamicZeroContainer.innerHTML = '';
        if (!rawDigits || rawDigits === '0') return;

        const num = parseInt(rawDigits, 10);
        if (isNaN(num) || num <= 0) return;

        // Sinh các gợi ý thêm số 0 tiếp theo: +0, +00, +000, +0000, +00000
        const multipliers = [10, 100, 1000, 10000, 100000, 1000000];
        
        multipliers.forEach(mult => {
            const val = num * mult;
            if (val <= 1000000000) { // Giới hạn hợp lý dưới 1 tỷ
                const formatted = val.toLocaleString('vi-VN');
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-dyn-zero';
                btn.textContent = formatted;
                btn.style.cssText = 'padding: 0.2rem 0.55rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #0d9488; background: #f0fdfa; color: #0f766e; font-weight: 700; cursor: pointer; transition: all 0.15s ease;';
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    salaryInput.value = formatted;
                    handleSalaryInput();
                    salaryInput.focus();
                });
                dynamicZeroContainer.appendChild(btn);
            }
        });
    }

    function renderPresets(type) {
        const cfg = config[type] || config['full_time'];
        presetContainer.innerHTML = '';
        cfg.presets.forEach(p => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = p.label;
            btn.style.cssText = 'padding: 0.2rem 0.5rem; font-size: 0.78rem; border-radius: 6px; border: 1px dashed #0d9488; background: #f0fdfa; color: #0f766e; font-weight: 600; cursor: pointer;';
            btn.addEventListener('click', function() {
                salaryInput.value = p.value.toLocaleString('vi-VN');
                handleSalaryInput();
                salaryInput.focus();
            });
            presetContainer.appendChild(btn);
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

    // --- LỊCH & NGÀY LÀM VIỆC ---
    function renderCalendarGrid() {
        calendarGrid.innerHTML = '';
        const today = new Date();
        const daysOfWeek = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

        for (let i = 0; i < 30; i++) {
            const d = new Date();
            d.setDate(today.getDate() + i);
            const dateStr = d.getDate().toString().padStart(2, '0') + '/' + (d.getMonth() + 1).toString().padStart(2, '0');
            const dayName = daysOfWeek[d.getDay()];

            const cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'cal-day-cell';
            cell.setAttribute('data-date', dateStr);
            cell.style.cssText = 'padding: 0.35rem 0.2rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; text-align: center; cursor: pointer; transition: all 0.15s ease; font-size: 0.75rem;';
            cell.innerHTML = `<div style="font-weight: 700; color: #0f172a;">${dateStr}</div><div style="font-size: 0.68rem; color: #64748b;">${dayName}</div>`;

            cell.addEventListener('click', function(e) {
                e.preventDefault();
                const dVal = this.getAttribute('data-date');
                if (selectedCalendarDates.includes(dVal)) {
                    selectedCalendarDates = selectedCalendarDates.filter(x => x !== dVal);
                    this.style.background = '#ffffff';
                    this.style.borderColor = '#cbd5e1';
                    this.style.color = 'inherit';
                } else {
                    selectedCalendarDates.push(dVal);
                    this.style.background = '#0d9488';
                    this.style.borderColor = '#0d9488';
                    this.querySelector('div').style.color = '#ffffff';
                    this.querySelector('div:last-child').style.color = '#e6fffa';
                }
                updateWorkingDaysFromPicks();
            });

            calendarGrid.appendChild(cell);
        }
    }

    function updateWorkingDaysFromPicks() {
        let parts = [];
        if (selectedWeekdays.length > 0) {
            parts.push(selectedWeekdays.join(', '));
        }
        if (selectedCalendarDates.length > 0) {
            parts.push('Ngày: ' + selectedCalendarDates.join(', '));
        }
        if (parts.length > 0) {
            workingDaysInput.value = parts.join(' | ');
        }
    }

    // Weekdays buttons
    document.querySelectorAll('.btn-weekday').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const day = this.getAttribute('data-day');
            if (selectedWeekdays.includes(day)) {
                selectedWeekdays = selectedWeekdays.filter(d => d !== day);
                this.style.background = '#ffffff';
                this.style.borderColor = '#cbd5e1';
                this.style.color = '#334155';
            } else {
                selectedWeekdays.push(day);
                this.style.background = '#0d9488';
                this.style.borderColor = '#0d9488';
                this.style.color = '#ffffff';
            }
            updateWorkingDaysFromPicks();
        });
    });

    // Presets
    document.querySelectorAll('.btn-preset-days').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const val = this.getAttribute('data-days');
            workingDaysInput.value = val;
            selectedWeekdays = [];
            selectedCalendarDates = [];
            document.querySelectorAll('.btn-weekday').forEach(b => {
                b.style.background = '#ffffff';
                b.style.borderColor = '#cbd5e1';
                b.style.color = '#334155';
            });
            document.querySelectorAll('.cal-day-cell').forEach(c => {
                c.style.background = '#ffffff';
                c.style.borderColor = '#cbd5e1';
                c.querySelector('div').style.color = '#0f172a';
                c.querySelector('div:last-child').style.color = '#64748b';
            });
        });
    });

    if (btnClearCalendar) {
        btnClearCalendar.addEventListener('click', function(e) {
            e.preventDefault();
            selectedCalendarDates = [];
            document.querySelectorAll('.cal-day-cell').forEach(c => {
                c.style.background = '#ffffff';
                c.style.borderColor = '#cbd5e1';
                c.querySelector('div').style.color = '#0f172a';
                c.querySelector('div:last-child').style.color = '#64748b';
            });
            updateWorkingDaysFromPicks();
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
        renderPresets(val);
        handleSalaryInput();

        // Cập nhật chế độ lịch làm việc
        if (val === 'full_time') {
            scheduleModeBadge.textContent = 'Toàn thời gian (1 tháng)';
            scheduleModeBadge.className = 'badge badge-primary';
            fulltimeBox.style.display = 'block';
            parttimeBox.style.display = 'none';
            if (!workingDaysInput.value || workingDaysInput.value.includes('Ngày:') || workingDaysInput.value.includes('Ca')) {
                workingDaysInput.value = 'Thứ 2 - Thứ 7 (Cả tháng)';
            }
        } else {
            scheduleModeBadge.textContent = val === 'part_time' ? 'Bán thời gian (Tự chọn ngày)' : 'Thời vụ / Ca (Tự chọn ngày)';
            scheduleModeBadge.className = 'badge badge-warning';
            fulltimeBox.style.display = 'none';
            parttimeBox.style.display = 'block';
            if (workingDaysInput.value === 'Thứ 2 - Thứ 7 (Cả tháng)') {
                workingDaysInput.value = 'Thứ 2, Thứ 4, Thứ 6';
            }
        }
    }

    typeSelect.addEventListener('change', updateTypeDetails);

    // Khởi tạo
    renderCalendarGrid();
    updateTypeDetails();
    if (workplaceInput) {
        updateMapPreview(workplaceInput.value);
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
