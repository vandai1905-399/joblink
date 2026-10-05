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
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="section-title">Danh Sách Bài Đăng Tuyển Dụng</h1>
            <p class="section-subtitle">Tự động phân tích AI & sắp xếp công việc phù hợp nhất với kỹ năng, vị trí và kinh nghiệm của bạn</p>
        </div>

        <?php if ($isSeeker): ?>
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <a href="<?= url('/jobs?sort=ai_match') ?>" class="btn <?= $isAiSorted ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="background: <?= $isAiSorted ? 'linear-gradient(135deg, #0d9488, #0284c7)' : '#ffffff' ?>; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
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

    <div class="grid-jobs">
        <?php if (empty($jobs)): ?>
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3.5rem 1.5rem; border-radius: 12px;">
                <div style="margin-bottom: 0.5rem; color: var(--slate-400);"><?= icon('search', '', 40) ?></div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a;">Không tìm thấy công việc phù hợp trong khu vực</h3>
                <p style="color: var(--slate-500); margin-top: 0.35rem; max-width: 500px; margin-left: auto; margin-right: auto; font-size: 0.9rem;">
                    Hãy thử mở rộng bán kính quét (ví dụ: 15 km hoặc 25 km), hoặc tìm kiếm theo tên thành phố / KCN lân cận.
                </p>
                <a href="<?= url('/jobs') ?>" class="btn btn-outline btn-sm" style="margin-top: 1rem; font-weight: 700;">Xóa bộ lọc & Xem tất cả</a>
            </div>
        <?php else: ?>
            <?php foreach ($jobs as $job): ?>
                <div class="card job-card" style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 1.25rem; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                    <div>
                        <!-- HEADER THẺ: LOGO + TIÊU ĐỀ + DOANH NGHIỆP + BADGE ĐỘ TƯƠNG THÍCH -->
                        <div style="display: flex; gap: 0.85rem; align-items: flex-start; justify-content: space-between;">
                            <div style="display: flex; gap: 0.85rem; align-items: flex-start; flex: 1;">
                                <img src="<?= htmlspecialchars(!empty($job['avatar_url']) ? $job['avatar_url'] : 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=150') ?>" 
                                     alt="logo" 
                                     style="width: 48px; height: 48px; border-radius: 10px; object-fit: cover; border: 1px solid #e2e8f0; flex-shrink: 0;">
                                
                                <div style="flex: 1;">
                                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; line-height: 1.35;">
                                        <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" style="color: #0f172a; text-decoration: none;">
                                            <?= htmlspecialchars($job['job_title']) ?>
                                        </a>
                                    </h3>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.84rem; color: #64748b; display: flex; align-items: center; gap: 0.3rem; flex-wrap: wrap;">
                                        <?= icon('building', '', 13) ?>
                                        <strong style="color: #334155;"><?= htmlspecialchars($job['employer_name']) ?></strong>
                                        <?php if (($job['digital_signature_status'] ?? '') === 'verified'): ?>
                                            <span class="badge badge-success" style="font-size: 0.68rem; padding: 1px 6px; display: inline-flex; align-items: center; gap: 2px;">
                                                <?= icon('certificate', '', 10) ?> Đã Ký Số
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <?php if (isset($job['ai_match_score']) && $isSeeker): ?>
                                <span class="ai-match-badge <?= $job['ai_match_level'] ?? 'medium' ?>" 
                                      data-job-badge="<?= $job['job_post_id'] ?>" 
                                      style="flex-shrink: 0;"
                                      title="Mức độ tương thích theo phân tích AI">
                                    <?= icon('sparkle', '', 12) ?>
                                    <span>Tương thích <?= $job['ai_match_score'] ?>%</span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- THÔNG TIN GỌN GÀNG: KHOẢNG CÁCH (CÁCH BAO XA) + CA LÀM -->
                        <div style="margin-top: 0.85rem; display: flex; flex-wrap: wrap; gap: 0.4rem; align-items: center;">
                            <?php if (isset($job['distance_km'])): ?>
                                <?php 
                                    $dist = $job['distance_km'];
                                    $distBadgeStyle = 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;';
                                    if ($dist > 15) {
                                        $distBadgeStyle = 'background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;';
                                    } elseif ($dist > 5) {
                                        $distBadgeStyle = 'background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd;';
                                    }
                                ?>
                                <span class="badge" style="font-size: 0.78rem; font-weight: 600; <?= $distBadgeStyle ?> display: inline-flex; align-items: center; gap: 0.3rem;" title="Khoảng cách theo vị trí bạn đã chọn">
                                    <?= icon('map_pin', '', 12) ?>
                                    <span>Cách <?= htmlspecialchars(!empty($job['from_location']) ? $job['from_location'] : 'bạn') ?>: <?= $job['distance_text'] ?></span>
                                </span>
                            <?php endif; ?>

                            <?= renderWorkShiftBadges($job['work_shift'] ?? '') ?>
                        </div>
                    </div>

                    <!-- FOOTER THẺ: MỨC LƯƠNG + NÚT ỨNG TUYỂN (BẤM VÀO XEM CHI TIẾT ĐẦY ĐỦ) -->
                    <div style="margin-top: 1.1rem; padding-top: 0.85rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                        <div style="min-width: 120px;">
                            <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #94a3b8; letter-spacing: 0.03em;">Mức lương</div>
                            <div style="font-size: 1.05rem; font-weight: 800; color: #0d9488; line-height: 1.2; margin-top: 0.15rem;">
                                <?= formatSalary($job['salary_amount'], $job['employment_type'] ?? 'full_time') ?>
                            </div>
                        </div>

                        <div style="display: flex; gap: 0.35rem; align-items: center;">
                            <?php if ($isSeeker): ?>
                                <button type="button" 
                                        class="btn btn-outline btn-sm btn-save-job" 
                                        data-job-id="<?= $job['job_post_id'] ?>" 
                                        title="Lưu việc làm" 
                                        style="padding: 0.45rem 0.6rem; color: #64748b; border-color: #cbd5e1; border-radius: 8px; display: inline-flex; align-items: center;">
                                    <?= icon('bookmark', '', 14) ?>
                                </button>
                            <?php endif; ?>

                            <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" 
                               class="btn btn-primary btn-sm" 
                               style="padding: 0.45rem 1rem; font-size: 0.85rem; font-weight: 800; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.35rem; white-space: nowrap;">
                                <span>Ứng Tuyển</span>
                                <?= icon('arrow_right', '', 12) ?>
                            </a>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/main_layout.php';
?>
