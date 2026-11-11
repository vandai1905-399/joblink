<?php 
$extraCss = ['css/jobs/job_list.css', 'css/jobs/ai_matching.css'];
$extraJs = ['js/jobs/jobs.js', 'js/jobs/ai_recommendation.js', 'js/jobs/geo_search.js'];
ob_start(); 

$isSeeker = isset($_SESSION['user']) && $_SESSION['user']['role_code'] === 'JOB_SEEKER';
$currentSort = $_GET['sort'] ?? ($isSeeker ? 'ai_match' : 'newest');
$isAiSorted = ($currentSort === 'ai_match') || (empty($_GET['sort']) && $isSeeker) || (isset($_GET['ai_recommend']) && $_GET['ai_recommend'] === '1');

$currentKeyword = $_GET['keyword'] ?? $_GET['q'] ?? '';
$currentLocation = $_GET['location'] ?? '';
$currentLat = $_GET['lat'] ?? '';
$currentLng = $_GET['lng'] ?? '';
$currentRadius = $_GET['radius'] ?? '0';
$currentType = $_GET['employment_type'] ?? '';
?>

<div class="container">
    <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="section-title" style="margin-bottom: 0.25rem;">
                <?= $isSeeker ? 'Khám Phá Việc Làm & Gợi Ý AI' : 'Danh Sách Bài Đăng Tuyển Dụng' ?>
            </h1>
            <p class="section-subtitle" style="margin-bottom: 0;">
                <?= $isSeeker ? 'Tự động phân tích AI & xếp hạng công việc theo tiêu chí riêng của từng nhà tuyển dụng' : 'Tra cứu việc làm nhanh chóng theo kỹ năng, địa điểm và mức lương' ?>
            </p>
        </div>

        <?php if ($isSeeker): ?>
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <a href="<?= url('/jobs?sort=ai_match') ?>" class="btn <?= $isAiSorted ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="<?= $isAiSorted ? 'background: linear-gradient(135deg, #0d9488, #0284c7); border: none; color: #fff;' : 'background: #475569; color: #ffffff; border-color: #475569;' ?> font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <?= icon('ai', '', 15) ?> <span>AI Phù Hợp Nhất (Tự Động)</span>
                </a>
                <a href="<?= url('/jobs?sort=newest') ?>" class="btn <?= ($currentSort === 'newest') ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="font-weight: 700;">
                    Mới nhất
                </a>
                <?php if (!empty($currentLat) && !empty($currentLng)): ?>
                    <a href="<?= url('/jobs?' . http_build_query(array_merge($_GET, ['sort' => 'distance']))) ?>" class="btn <?= $currentSort === 'distance' ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <?= icon('map_pin', '', 15) ?> <span>Gần Tôi Nhất</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($isSeeker && $isAiSorted): ?>
        <div style="background: linear-gradient(135deg, #f0fdfa 0%, #ecfeff 100%); border: 1px solid #99f6e4; border-radius: 8px; padding: 0.55rem 0.85rem; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.45rem;">
                <span style="background: #0d9488; color: #ffffff; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; flex-shrink: 0;">✦</span>
                <span style="font-size: 0.82rem; color: #0f766e; font-weight: 600;">
                    AI đang tự động so khớp hồ sơ của bạn với tiêu chí cụ thể của từng doanh nghiệp để gợi ý các vị trí phù hợp nhất lên đầu.
                </span>
            </div>
            <a href="<?= url('/seeker/profile') ?>" style="font-size: 0.75rem; color: #0d9488; font-weight: 700; text-decoration: underline;">Tối ưu hồ sơ để AI gợi ý chuẩn hơn</a>
        </div>
    <?php endif; ?>

    <!-- BỘ LỌC TÌM KIẾM THEO VỊ TRÍ, BÁN KÍNH GPS & TỪ KHÓA -->
    <form action="<?= url('/jobs') ?>" method="GET" class="card" style="margin-bottom: 2rem; background: var(--slate-100); border-radius: 12px; padding: 1.25rem;">
        <div style="display: grid; grid-template-columns: 1.2fr 1.5fr 1fr 1fr auto; gap: 0.75rem; align-items: flex-end;" class="geo-search-container">
            
            <!-- TỪ KHÓA -->
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">Từ khóa</label>
                <input type="text" name="keyword" value="<?= htmlspecialchars($currentKeyword) ?>" placeholder="Chức danh, kỹ năng..." class="form-control">
            </div>

            <!-- ĐỊA ĐIỂM + NÚT LẤY VỊ TRÍ GPS HIỆN TẠI -->
            <div class="form-group" style="margin-bottom:0; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #1e293b; margin: 0;">Địa điểm / Tọa độ</label>
                    <button type="button" class="btn-get-current-location" style="background: none; border: none; color: #0d9488; font-size: 0.78rem; font-weight: 700; cursor: pointer; padding: 0; display: inline-flex; align-items: center; gap: 0.2rem;" title="Lấy tọa độ GPS thiết bị của bạn">
                        <?= icon('map_pin', '', 14) ?> <span>Vị trí hiện tại</span>
                    </button>
                </div>
                <div style="position: relative;">
                    <input type="text" name="location" value="<?= htmlspecialchars($currentLocation) ?>" 
                           placeholder="Nhập địa danh Google Maps (Thủ Dầu Một, VSIP 1...)" 
                           class="form-control geo-location-input" autocomplete="off" style="padding-right: 28px;">
                    <span style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;"><?= icon('map_pin', '', 16) ?></span>
                </div>
                <input type="hidden" name="lat" value="<?= htmlspecialchars($currentLat) ?>">
                <input type="hidden" name="lng" value="<?= htmlspecialchars($currentLng) ?>">

                <!-- DROPDOWN GỢI Ý ĐỊA DANH / KCN -->
                <div class="geo-suggestions-dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.12); z-index: 999; max-height: 250px; overflow-y: auto; margin-top: 4px;"></div>
            </div>

            <!-- BÁN KÍNH QUÉT TỐI ĐA (KHOẢNG CÁCH KM) -->
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">Bán kính quét</label>
                <select name="radius" class="form-control" style="font-weight: 600;">
                    <option value="0" <?= $currentRadius === '0' ? 'selected' : '' ?>>Tất cả khoảng cách</option>
                    <option value="3" <?= $currentRadius === '3' ? 'selected' : '' ?>>Trong 3 km (Rất gần)</option>
                    <option value="5" <?= $currentRadius === '5' ? 'selected' : '' ?>>Trong 5 km</option>
                    <option value="10" <?= ($currentRadius === '10' || (empty($currentRadius) && !empty($currentLat))) ? 'selected' : '' ?>>Trong 10 km (Khuyên dùng)</option>
                    <option value="15" <?= $currentRadius === '15' ? 'selected' : '' ?>>Trong 15 km</option>
                    <option value="25" <?= $currentRadius === '25' ? 'selected' : '' ?>>Trong 25 km</option>
                    <option value="50" <?= $currentRadius === '50' ? 'selected' : '' ?>>Trong 50 km (Mở rộng)</option>
                </select>
            </div>

            <!-- HÌNH THỨC LÀM VIỆC -->
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">Hình thức</label>
                <select name="employment_type" class="form-control">
                    <option value="">-- Tất cả --</option>
                    <option value="full_time" <?= $currentType === 'full_time' ? 'selected' : '' ?>>Full-time</option>
                    <option value="part_time" <?= $currentType === 'part_time' ? 'selected' : '' ?>>Part-time / Ca</option>
                    <option value="temporary" <?= $currentType === 'temporary' ? 'selected' : '' ?>>Thời vụ</option>
                </select>
            </div>

            <?php if ($isAiSorted): ?>
                <input type="hidden" name="sort" value="ai_match">
                <input type="hidden" name="ai_recommend" value="1">
            <?php endif; ?>

            <div class="form-group" style="margin-bottom:0;">
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.25rem; font-weight: 700; height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem;">
                    <?= icon('search', '', 15) ?> <span>Tìm Việc</span>
                </button>
            </div>
        </div>
    </form>

    <!-- THANH THÔNG TIN BỘ LỌC ĐỊA LÝ HIỆN TẠI -->
    <?php if (!empty($currentLocation) || (!empty($currentLat) && !empty($currentLng)) || floatval($currentRadius) > 0): ?>
        <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 10px; padding: 0.75rem 1.15rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div style="font-size: 0.88rem; color: #0f766e; display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                <span style="display: inline-flex; align-items: center; gap: 0.3rem;"><?= icon('map_pin', '', 15) ?> <strong>Khu vực tìm kiếm:</strong> <?= htmlspecialchars($currentLocation ?: 'Vị trí GPS') ?></span>
                <?php if (floatval($currentRadius) > 0): ?>
                    <span class="badge badge-primary" style="font-size: 0.78rem;">Bán kính &le; <?= htmlspecialchars($currentRadius) ?> km</span>
                <?php endif; ?>
                <span style="color: #0d9488;">— Tìm thấy <strong><?= count($jobs) ?></strong> công việc phù hợp</span>
            </div>
            <a href="<?= url('/jobs') ?>" style="font-size: 0.8rem; font-weight: 700; color: #0d9488; text-decoration: underline;">
                Xóa bộ lọc vị trí
            </a>
        </div>
    <?php endif; ?>

    <div class="ai-job-grid" id="jobsGridContainer">
        <?php if (empty($jobs)): ?>
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3.5rem 1.5rem; border-radius: 12px; background: #ffffff;">
                <div style="margin-bottom: 0.5rem; color: var(--slate-400);"><?= icon('search', '', 40) ?></div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a;">Không tìm thấy công việc phù hợp trong khu vực</h3>
                <p style="color: var(--slate-500); margin-top: 0.35rem; max-width: 500px; margin-left: auto; margin-right: auto; font-size: 0.9rem;">
                    Hãy thử mở rộng bán kính quét (ví dụ: 15 km hoặc 25 km), hoặc tìm kiếm theo tên thành phố / KCN lân cận.
                </p>
                <a href="<?= url('/jobs') ?>" class="btn btn-outline btn-sm" style="margin-top: 1rem; font-weight: 700;">Xóa bộ lọc & Xem tất cả</a>
            </div>
        <?php else: ?>
            <?php foreach ($jobs as $job): 
                $isJobSaved = !empty($job['is_saved']) || (isset($savedJobIds) && in_array(intval($job['job_post_id']), $savedJobIds));
            ?>
                <div class="ai-job-card" onclick="if (!event.target.closest('a') && !event.target.closest('button')) { const l = this.querySelector('.ai-job-title a'); if (l) l.click(); }" style="cursor: pointer;">
                    <?php if (isset($job['ai_match_score']) && $isSeeker): ?>
                        <span class="ai-match-badge <?= $job['ai_match_level'] ?? 'medium' ?>" title="Mức độ tương thích AI: <?= $job['ai_match_score'] ?>%">
                            <?= icon('sparkle', '', 10) ?>
                            <span class="match-text-full">Tương thích </span><span><?= $job['ai_match_score'] ?>%</span>
                        </span>
                    <?php endif; ?>

                    <div class="ai-job-main-info">
                        <div class="ai-job-header-row">
                            <?php 
                                $jobCardLogo = !empty($job['avatar_url']) ? getAvatarUrl($job['avatar_url'], 'EMPLOYER') : asset('uploads/avatars/employers/employer_default.png');
                            ?>
                            <img src="<?= htmlspecialchars($jobCardLogo) ?>" 
                                 alt="<?= htmlspecialchars($job['employer_name'] ?? 'Doanh nghiệp') ?>" 
                                 class="ai-job-logo" 
                                 onerror="this.onerror=null; this.src='<?= asset('uploads/avatars/employers/employer_default.png') ?>';">
                            <div class="ai-job-titles">
                                <h4 class="ai-job-title" style="font-size: 0.88rem;">
                                    <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" class="workspace-nav-link" data-title="Tin: <?= htmlspecialchars($job['job_title']) ?>">
                                        <?= htmlspecialchars($job['job_title']) ?>
                                    </a>
                                </h4>
                                <p class="ai-job-company" style="font-size: 0.78rem;">
                                    <?php if (!empty($job['employer_id'])): ?>
                                        <a href="<?= url('/employers/' . $job['employer_id']) ?>" class="workspace-nav-link" data-title="DN: <?= htmlspecialchars($job['employer_name']) ?>" style="color: #64748b; font-weight: 700; text-decoration: none;" title="Xem trang cá nhân của doanh nghiệp">
                                            <?= htmlspecialchars($job['employer_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($job['employer_name']) ?>
                                    <?php endif; ?>
                                    <?php if (($job['digital_signature_status'] ?? '') === 'verified'): ?>
                                        <span class="badge badge-success" style="font-size: 0.65rem; padding: 1px 5px; display: inline-flex; align-items: center; gap: 2px; margin-left: 3px;" title="Doanh nghiệp đã xác thực chữ ký số điện tử">
                                            <?= icon('certificate', '', 9) ?> Đã Ký Số
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <!-- DÒNG 1: MỨC LƯƠNG -->
                        <div class="ai-job-salary-row" style="margin-top: 0.35rem; font-size: 0.84rem;">
                            <strong class="ai-job-salary" style="color: #0d9488; font-weight: 800;">
                                <?= formatSalary($job['salary_amount'], $job['employment_type'] ?? 'full_time') ?>
                            </strong>
                        </div>

                        <!-- DÒNG 2: ĐỊA ĐIỂM RIÊNG BIỆT DƯỚI DÒNG LƯƠNG (CÓ ICON ĐỊA ĐIỂM, KHÔNG GẠCH CHÂN) -->
                        <div class="ai-job-location-row" style="margin-top: 0.2rem; font-size: 0.76rem; color: #64748b; display: flex; align-items: center; gap: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <span style="flex-shrink: 0; color: #94a3b8; display: inline-flex; align-items: center;"><?= icon('map_pin', '', 12) ?></span>
                            <?php 
                                $ajMapUrl = getGoogleMapsUrl($job['workplace_address'] ?? 'Tại nơi làm việc', $job['workplace_map_url'] ?? '');
                            ?>
                            <a href="<?= htmlspecialchars($ajMapUrl) ?>" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();" style="color: #64748b; text-decoration: none; overflow: hidden; text-overflow: ellipsis;" title="Mở vị trí trên Google Maps: <?= htmlspecialchars($job['workplace_address'] ?? '') ?>">
                                <?= htmlspecialchars($job['workplace_address'] ?? 'Tại nơi làm việc') ?>
                            </a>
                        </div>

                        <!-- DÒNG 3: KHOẢNG CÁCH (NẾU CÓ ĐỊNH VỊ GPS HOẶC TỌA ĐỘ) -->
                        <?php if (isset($job['distance_km'])): ?>
                            <div style="margin-top: 0.2rem; font-size: 0.74rem; color: #0f766e; display: flex; align-items: center; gap: 0.25rem; font-weight: 600;">
                                <span style="flex-shrink: 0; display: inline-flex; align-items: center;"><?= icon('map_pin', '', 11) ?></span>
                                <span>Cách <?= htmlspecialchars(!empty($job['from_location']) ? $job['from_location'] : 'bạn') ?>: <strong><?= $job['distance_text'] ?></strong></span>
                            </div>
                        <?php endif; ?>

                        <!-- Progress bar tương thích -->
                        <?php if ($isSeeker && isset($job['ai_match_score'])): ?>
                            <div class="ai-progress-wrap" style="margin: 0.35rem 0 0.25rem;">
                                <div class="ai-progress-bar <?= $job['ai_match_level'] ?? 'medium' ?>" style="width: <?= $job['ai_match_score'] ?>%;"></div>
                            </div>
                        <?php endif; ?>

                        <!-- Kỹ năng trùng khớp -->
                        <?php if (!empty($job['ai_matched_skills'])): ?>
                            <div class="ai-matched-skills-row" style="display: flex; flex-wrap: wrap; gap: 0.25rem; margin: 0.35rem 0;">
                                <?php foreach (array_slice($job['ai_matched_skills'], 0, 3) as $msk): ?>
                                    <span class="ai-skill-tag matched" style="display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.72rem; padding: 0.15rem 0.4rem;"><?= icon('check', '', 10) ?> <?= htmlspecialchars($msk) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Lý do AI đề xuất -->
                        <?php if (!empty($job['ai_reasons'])): ?>
                            <ul class="ai-reasons-list">
                                <?php foreach (array_slice($job['ai_reasons'], 0, 2) as $reason): ?>
                                    <li class="ai-reason-item">
                                        <span style="color: #0d9488; display: inline-flex; align-items: center;"><?= icon('check', '', 11) ?></span>
                                        <span><?= htmlspecialchars($reason) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- Nút thao tác: NÚT LƯU BÊN TRÁI + NÚT ỨNG TUYỂN BÊN PHẢI -->
                    <div class="job-card-actions" style="margin-top: 0.5rem; display: flex; gap: 0.4rem; align-items: center;">
                        <?php if ($isSeeker): ?>
                            <button type="button" 
                                    class="btn btn-sm btn-seeker-save <?= $isJobSaved ? 'btn-primary' : 'btn-outline' ?>" 
                                    data-job-id="<?= $job['job_post_id'] ?>" 
                                    onclick="toggleSeekerSaveJob(this, <?= $job['job_post_id'] ?>); event.stopPropagation();"
                                    title="<?= $isJobSaved ? 'Bỏ lưu việc làm' : 'Lưu vào danh sách việc làm đã lưu' ?>" 
                                    style="font-size: 0.75rem; padding: 0.25rem 0.6rem; height: auto; display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 700; border-radius: 6px; white-space: nowrap;">
                                <?= icon('bookmark', '', 12) ?> 
                                <span class="save-btn-text"><?= $isJobSaved ? 'Đã lưu' : 'Lưu' ?></span>
                            </button>
                        <?php endif; ?>
                        <div style="flex: 1;">
                            <?php if (!empty($job['is_applied'])): ?>
                                <span class="badge badge-success" style="font-size: 0.72rem; padding: 0.25rem 0.6rem; display: inline-flex; align-items: center; justify-content: center; width: 100%; height: auto;">Đã nộp đơn</span>
                            <?php else: ?>
                                <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" onclick="event.stopPropagation();" class="btn btn-primary btn-sm workspace-nav-link" data-title="Tin: <?= htmlspecialchars($job['job_title']) ?>" style="font-weight: 700; width: 100%; font-size: 0.75rem; padding: 0.25rem 0.6rem; height: auto; display: inline-flex; align-items: center; justify-content: center;">
                                    <span>Ứng Tuyển</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleSeekerSaveJob(btn, jobId) {
    if (!jobId) return;
    btn.disabled = true;
    const saveText = btn.querySelector('.save-btn-text');
    const saveUrl = '<?= rtrim(url('/seeker/toggle-save'), '/') ?>/' + jobId;

    fetch(saveUrl, {
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (r.status === 401) {
            window.location.href = '<?= url('/auth/login') ?>';
            return null;
        }
        return r.json();
    })
    .then(data => {
        btn.disabled = false;
        if (!data) return;

        if (data.success === false) {
            if (typeof Toast !== 'undefined' && Toast.show) {
                Toast.show(data.message || 'Không thể thực hiện lưu việc làm!', 'warning');
            } else {
                alert(data.message || 'Không thể thực hiện lưu việc làm!');
            }
            return;
        }

        if (data.saved) {
            btn.classList.remove('btn-outline');
            btn.classList.add('btn-primary');
            if (saveText) saveText.textContent = 'Đã lưu';
            btn.title = 'Bỏ lưu việc làm';
        } else {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline');
            if (saveText) saveText.textContent = 'Lưu';
            btn.title = 'Lưu vào danh sách việc làm đã lưu';
        }

        const savedCountEl = document.getElementById('header-saved-count');
        if (savedCountEl) {
            let currentCount = parseInt(savedCountEl.textContent) || 0;
            savedCountEl.textContent = data.saved ? (currentCount + 1) : Math.max(0, currentCount - 1);
        }

        if (typeof Toast !== 'undefined' && Toast.show) {
            Toast.show(data.message || (data.saved ? 'Đã lưu việc làm!' : 'Đã bỏ lưu việc làm!'), data.saved ? 'success' : 'info');
        }
    })
    .catch(err => {
        btn.disabled = false;
        console.error('Save job error:', err);
        if (typeof Toast !== 'undefined' && Toast.show) {
            Toast.show('Không thể kết nối máy chủ. Vui lòng thử lại!', 'danger');
        }
    });
}
</script>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/main_layout.php';
?>
