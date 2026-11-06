<?php 
$isHomePage = true;
$showFooter = true;
$extraCss = ['css/jobs/ai_matching.css', 'css/jobs/hero_slider.css'];
$extraJs = ['js/jobs/geo_search.js', 'js/jobs/hero_slider.js'];
$slides = get_homepage_slides();
ob_start(); 
?>

<!-- ================================================================= -->
<!-- CỔNG THÔNG TIN CHUNG & LANDING PAGE NỀN TẢNG JOBLINK (DÀNH CHO KHÁCH) -->
<!-- ================================================================= -->

<!-- HERO BANNER SLIDER (4 SLIDES - 4 DẢI MÀU PHA XANH LAM NGỌC RIÊNG BIỆT HOẶC ẢNH TỪ UPLOADS/SLIDES) -->
<section class="hero-slider-section">
    <div class="hero-slider-wrapper">
        <?php foreach ($slides as $index => $slide): ?>
            <?php 
                $hasCustomImg = !empty($slide['has_custom_image']) && !empty($slide['image_url']);
                $slideStyle = $hasCustomImg 
                    ? "background-image: url('{$slide['image_url']}');" 
                    : "background: {$slide['gradient']}; --glow-1: {$slide['glow_color_1']}; --glow-2: {$slide['glow_color_2']};";
            ?>
            <div class="hero-slide-item <?= $index === 0 ? 'active' : '' ?> <?= $hasCustomImg ? 'has-custom-img' : '' ?>" data-slide-index="<?= $index ?>">
                <!-- Nền Gradient Mesh / Ảnh tải lên -->
                <div class="hero-slide-bg" style="<?= $slideStyle ?>">
                    <?php if (!$hasCustomImg): ?>
                        <div class="slide-glow-1"></div>
                        <div class="slide-glow-2"></div>
                        <div class="slide-pattern-overlay"></div>
                    <?php endif; ?>
                </div>

                <!-- Nội dung Slide -->
                <div class="hero-slide-content">
                    <div class="hero-slide-badge">
                        <?= icon($slide['badge_icon'] ?? 'certificate', '', 16) ?> 
                        <span><?= htmlspecialchars($slide['badge']) ?></span>
                    </div>

                    <h1 class="hero-slide-title">
                        <?= htmlspecialchars($slide['title']) ?>
                    </h1>

                    <p class="hero-slide-subtitle">
                        <?= htmlspecialchars($slide['subtitle']) ?>
                    </p>

                    <div class="hero-slide-actions">
                        <a href="<?= $slide['cta_primary']['url'] ?>" class="hero-btn-primary">
                            <?php if (!empty($slide['cta_primary']['icon'])): ?>
                                <?= icon($slide['cta_primary']['icon'], '', 16) ?>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($slide['cta_primary']['text']) ?></span>
                        </a>
                        <a href="<?= $slide['cta_secondary']['url'] ?>" class="hero-btn-secondary">
                            <?php if (!empty($slide['cta_secondary']['icon'])): ?>
                                <?= icon($slide['cta_secondary']['icon'], '', 16) ?>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($slide['cta_secondary']['text']) ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Nút Điều Hướng Prev / Next -->
        <button type="button" class="hero-slider-nav prev" aria-label="Slide trước">
            <?= icon('chevron_left', '', 20) ?>
        </button>
        <button type="button" class="hero-slider-nav next" aria-label="Slide tiếp theo">
            <?= icon('chevron_right', '', 20) ?>
        </button>

        <!-- Thanh Chỉ Số Phân Trang (Chấm Dots) -->
        <div class="hero-slider-pagination">
            <?php foreach ($slides as $index => $slide): ?>
                <button type="button" class="hero-slider-dot <?= $index === 0 ? 'active' : '' ?>" data-slide-to="<?= $index ?>" aria-label="Chuyển đến slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>


    <!-- FORM TÌM KIẾM NHANH CÔNG KHAI TÍCH HỢP -->
    <div class="hero-search-wrapper">
        <form action="<?= url('/jobs') ?>" method="GET" class="hero-search-box geo-search-container">
            <div class="search-input-group" style="flex: 1.2; min-width: 180px;">
                <input type="text" name="keyword" placeholder="Vị trí, chức danh hoặc công ty..." class="search-input" style="border: none; width: 100%; font-size: 0.92rem;">
            </div>

            <!-- ĐỊA ĐIỂM + NÚT GPS + GỢI Ý -->
            <div class="search-input-group" style="flex: 1.5; min-width: 220px; position: relative; display: flex; align-items: center; border-left: 1px solid #e2e8f0; padding-left: 0.5rem;">
                <input type="text" name="location" placeholder="Địa điểm (Thủ Dầu Một, VSIP 1...)" class="search-input geo-location-input" style="border: none; width: 100%; padding-right: 70px; font-size: 0.92rem;" autocomplete="off">
                <button type="button" class="btn-get-current-location" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #f0fdfa; border: 1px solid #99f6e4; color: #0d9488; font-size: 0.75rem; font-weight: 700; border-radius: 6px; padding: 0.25rem 0.5rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;" title="Lấy vị trí GPS hiện tại">
                    <?= icon('map_pin', '', 12) ?> <span>GPS</span>
                </button>
                <input type="hidden" name="lat" value="">
                <input type="hidden" name="lng" value="">
                <div class="geo-suggestions-dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.12); z-index: 999; max-height: 240px; overflow-y: auto; margin-top: 8px; text-align: left;"></div>
            </div>

            <!-- HÌNH THỨC -->
            <div class="search-input-group" style="flex: 1; min-width: 130px; border-left: 1px solid #e2e8f0; padding-left: 0.5rem;">
                <select name="employment_type" class="search-input" style="border: none; width: 100%; background:#fff; font-size: 0.88rem; font-weight: 600;">
                    <option value="">Tất cả hình thức</option>
                    <option value="full_time">Full-time</option>
                    <option value="part_time">Part-time / Ca</option>
                    <option value="temporary">Thời vụ</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 0.8rem 1.6rem; font-weight: 800; border-radius: 10px; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(13,148,136,0.3);">
                Tìm Việc Ngay
            </button>
        </form>
    </div>
</section>

<!-- 4 CỘT TRỤ TIÊU CHUẨN AN TOÀN NỀN TẢNG -->
<section class="container" style="margin-top: -28px; margin-bottom: 3rem; position: relative; z-index: 10;">
    <div style="background: #ffffff; border-radius: 16px; padding: 1.5rem 1.75rem; box-shadow: 0 12px 30px -8px rgba(0,0,0,0.08), 0 0 0 1px #e2e8f0; display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1.5rem; align-items: start;">
        
        <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(16,185,129,0.15);">
                <?= icon('certificate', '', 24) ?>
            </div>
            <div>
                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.98rem; font-weight: 800; color: #0f172a;">Chữ Ký Số Hợp Chuẩn</h4>
                <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.5;">100% Doanh nghiệp xác thực Chữ Ký Số (VNPT, Viettel, FPT...) & Hợp đồng điện tử.</p>
            </div>
        </div>

        <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(37,99,235,0.15);">
                <?= icon('handshake', '', 24) ?>
            </div>
            <div>
                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.98rem; font-weight: 800; color: #0f172a;">Ủy Quyền Pháp Lý</h4>
                <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.5;">Ràng buộc pháp lý rõ ràng giữa Doanh nghiệp và Người trung gian / Đại lý cung ứng.</p>
            </div>
        </div>

        <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(245,158,11,0.15);">
                <?= icon('scale', '', 24) ?>
            </div>
            <div>
                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.98rem; font-weight: 800; color: #0f172a;">Điểm Uy Tín Người Lao Động</h4>
                <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.5;">Hệ thống Trust Score (0-100) & Ký cam kết điện tử có trách nhiệm, không bùng ca.</p>
            </div>
        </div>

        <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #f0fdfa; color: #0d9488; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(13,148,136,0.15);">
                <?= icon('mail_check', '', 24) ?>
            </div>
            <div>
                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.98rem; font-weight: 800; color: #0f172a;">Thông Báo Gmail 24/7</h4>
                <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.5;">Tự động gửi email thông báo tin nhắn mới, lịch hẹn phỏng vấn và kết quả tuyển dụng.</p>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- TRUNG TÂM GỢI Ý 2 CHIỀU: DÀNH CHO NGƯỜI LAO ĐỘNG & NHÀ TUYỂN DỤNG         -->
<!-- (CHUYỂN RA TRANG CHỦ KẾT HỢP VỚI TRANG NGƯỜI NLD ĐỀ XUẤT)                 -->
<!-- ========================================================================= -->
<section class="container" style="margin-bottom: 3.5rem;">
    <div style="background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%); border: 1px solid #ccfbf1; border-radius: 20px; padding: 2rem; box-shadow: 0 10px 30px rgba(13, 148, 136, 0.06);">
        <!-- Tiêu đề phân hệ -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <span class="badge" style="background: #ffffff; color: #0d9488; border: 1px solid #99f6e4; font-weight: 800; font-size: 0.78rem; padding: 0.3rem 0.75rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 5px rgba(13,148,136,0.1);">
                    <?= icon('sparkle', '', 14) ?> KHÁM PHÁ THÔNG MINH 2 CHIỀU
                </span>
                <h2 style="font-size: 1.7rem; font-weight: 800; color: #0f172a; margin: 0.4rem 0 0.2rem 0; letter-spacing: -0.02em;">
                    Gợi Ý Kết Nối Việc Làm & Người Lao Động
                </h2>
                <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
                    Trợ lý thông minh hỗ trợ Người tìm việc tìm cơ hội phù hợp và Doanh nghiệp tuyển đúng ứng viên theo hạn mức mong muốn.
                </p>
            </div>

            <!-- Nút chuyển Tab 2 vai trò -->
            <div style="background: #e2e8f0; padding: 4px; border-radius: 12px; display: inline-flex; gap: 4px;">
                <button type="button" id="tabBtnSeeker" class="btn btn-sm" onclick="switchHomeRecTab('seeker')" style="font-weight: 700; font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 8px; border: none; background: #ffffff; color: #0d9488; box-shadow: 0 2px 6px rgba(0,0,0,0.08); transition: all 0.2s;">
                    👔 Dành Cho Người Lao Động
                </button>
                <button type="button" id="tabBtnEmployer" class="btn btn-sm" onclick="switchHomeRecTab('employer')" style="font-weight: 700; font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 8px; border: none; background: transparent; color: #475569; transition: all 0.2s;">
                    🏢 Dành Cho Nhà Tuyển Dụng
                </button>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- TAB 1: GỢI Ý VIỆC LÀM DÀNH CHO NGƯỜI LAO ĐỘNG (JOB SEEKER)           -->
        <!-- ===================================================================== -->
        <div id="homeRecSeekerPane">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <span style="color: #0d9488;"><?= icon('briefcase', '', 20) ?></span>
                    <div>
                        <strong style="color: #0f172a; font-size: 0.92rem;">Các cơ hội việc làm nổi bật được đề xuất hôm nay</strong>
                        <div style="font-size: 0.78rem; color: #64748b;">Ưu tiên việc làm có ca linh hoạt, lương minh bạch và doanh nghiệp đã xác thực chữ ký số.</div>
                    </div>
                </div>
                <div style="display: flex; gap: 0.4rem; align-items: center;">
                    <a href="<?= url('/jobs') ?>" class="btn btn-outline btn-sm" style="font-size: 0.82rem; font-weight: 700; color: #0d9488; border-color: #0d9488;">
                        Xem Tất Cả Việc Làm
                    </a>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem;">
                <?php foreach (array_slice($recommendedJobs ?? $jobs ?? [], 0, 6) as $rJob): 
                    $isVerified = ($rJob['digital_signature_status'] ?? '') === 'verified';
                    $hasMatchScore = isset($rJob['ai_match_score']);
                ?>
                    <div class="card job-card" style="border-radius: 12px; border: 1px solid <?= $hasMatchScore ? '#99f6e4' : '#e2e8f0' ?>; padding: 1.15rem; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.65rem;">
                                <div style="display: flex; gap: 0.65rem; align-items: center;">
                                    <img src="<?= htmlspecialchars(getAvatarUrl($rJob['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" style="width: 44px; height: 44px; border-radius: 10px; object-fit: cover; border: 1px solid #e2e8f0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                                    <div>
                                        <h4 style="margin: 0; font-size: 0.98rem; font-weight: 800; line-height: 1.3;">
                                            <a href="<?= url('/jobs/' . $rJob['job_post_id']) ?>" style="color: #0f172a; text-decoration: none;">
                                                <?= htmlspecialchars($rJob['job_title']) ?>
                                            </a>
                                        </h4>
                                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">
                                            <?= htmlspecialchars($rJob['employer_name'] ?? 'Doanh nghiệp JobLink') ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($hasMatchScore): ?>
                                    <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700; font-size: 0.74rem; padding: 0.2rem 0.5rem; white-space: nowrap;">
                                        ✓ <?= $rJob['ai_match_score'] ?>% tương thích
                                    </span>
                                <?php elseif ($isVerified): ?>
                                    <span class="badge" style="background: #f0fdfa; color: #0d9488; border: 1px solid #ccfbf1; font-weight: 700; font-size: 0.72rem; padding: 0.2rem 0.5rem; white-space: nowrap;">
                                        Đã Ký Số
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div style="font-size: 0.82rem; color: #64748b; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 4px;">
                                <?= icon('location', '', 12) ?>
                                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($rJob['workplace_address'] ?? 'Tại nơi làm việc') ?></span>
                            </div>

                            <?php if (!empty($rJob['ai_matched_skills'])): ?>
                                <div style="display: flex; gap: 0.25rem; flex-wrap: wrap; margin-bottom: 0.65rem;">
                                    <?php foreach (array_slice($rJob['ai_matched_skills'], 0, 3) as $msk): ?>
                                        <span style="background: #f0fdf4; color: #166534; font-size: 0.7rem; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600;">
                                            <?= htmlspecialchars($msk) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="border-top: 1px solid #f1f5f9; padding-top: 0.65rem; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Thu nhập</div>
                                <div style="font-size: 0.95rem; font-weight: 800; color: #0d9488;">
                                    <?= formatSalary($rJob['salary_amount'] ?? 0, $rJob['employment_type'] ?? 'full_time') ?>
                                </div>
                            </div>
                            <a href="<?= url('/jobs/' . $rJob['job_post_id']) ?>" class="btn btn-primary btn-sm" style="font-size: 0.8rem; font-weight: 700; padding: 0.35rem 0.85rem; border-radius: 8px;">
                                Ứng Tuyển
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- TAB 2: GỢI Ý NGƯỜI LAO ĐỘNG DÀNH CHO NHÀ TUYỂN DỤNG (EMPLOYER)        -->
        <!-- (KẾT HỢP TRANG NGƯỜI NLD ĐỀ XUẤT VÀ BỘ ĐIỀU CHỈNH HẠN MỨC %)        -->
        <!-- ===================================================================== -->
        <div id="homeRecEmployerPane" style="display: none;">
            <!-- THANH ĐIỀU CHỈNH HẠN MỨC % DÀNH CHO NTD TRÊN TRANG CHỦ -->
            <div class="threshold-box" style="margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.75rem; padding-bottom: 0.65rem; border-bottom: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="color: #0d9488;"><?= icon('sparkle', '', 18) ?></span>
                        <div>
                            <strong style="color: #0f172a; font-size: 0.92rem;">Cài đặt hạn mức % tương thích đề xuất ứng viên</strong>
                            <div style="font-size: 0.78rem; color: #64748b;">Nhà tuyển dụng có thể tự do điều chỉnh tỷ lệ % phù hợp mong muốn để lọc ứng viên chuẩn xác.</div>
                        </div>
                    </div>
                    <div class="threshold-summary-pills">
                        <span class="pill-qualified">
                            <?= icon('check', '', 12) ?> Đạt chuẩn: <strong id="homeCountQualifiedBadge"><?= count($homeQualifiedCandidates) ?></strong> ứng viên
                        </span>
                        <span class="pill-unqualified">
                            <?= icon('alert_circle', '', 12) ?> Chưa đạt: <strong id="homeCountUnqualifiedBadge"><?= count($homeUnqualifiedCandidates) ?></strong> ứng viên
                        </span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                        <span>Hạn mức % hiển thị lên danh sách chính:</span>
                        <span class="threshold-badge" id="homeThresholdBadge"><?= $homeThreshold ?>%</span>
                    </div>
                    <div class="threshold-presets">
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Mốc nhanh:</span>
                        <button type="button" class="btn-threshold-preset home-preset <?= $homeThreshold === 60 ? 'active' : '' ?>" data-preset="60">60%</button>
                        <button type="button" class="btn-threshold-preset home-preset <?= $homeThreshold === 70 ? 'active' : '' ?>" data-preset="70">70%</button>
                        <button type="button" class="btn-threshold-preset home-preset <?= $homeThreshold === 80 ? 'active' : '' ?>" data-preset="80">80%</button>
                        <button type="button" class="btn-threshold-preset home-preset <?= $homeThreshold === 90 ? 'active' : '' ?>" data-preset="90">90%</button>
                    </div>
                </div>

                <div class="threshold-slider-wrapper">
                    <span style="font-size: 0.72rem; font-weight: 700; color: #64748b;">30%</span>
                    <input type="range" min="30" max="95" step="5" value="<?= $homeThreshold ?>" class="threshold-slider" id="homeThresholdSlider" title="Kéo để thay đổi hạn mức % tương thích">
                    <span style="font-size: 0.72rem; font-weight: 700; color: #64748b;">95%</span>
                </div>
            </div>

            <!-- PHẦN 1: ỨNG VIÊN ĐẠT CHUẨN (HIỂN THỊ LÊN) -->
            <div style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div style="font-size: 0.95rem; font-weight: 800; color: #065f46; display: flex; align-items: center; gap: 0.4rem;">
                        <span>✓ Ứng Viên Đạt Chuẩn Hạn Mức (≥ <span class="home-threshold-val"><?= $homeThreshold ?>%</span>)</span>
                        <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-size: 0.76rem; font-weight: 700;">
                            <span id="homeBadgeCountQualified"><?= count($homeQualifiedCandidates) ?></span> hồ sơ
                        </span>
                    </div>
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role_code'] === 'EMPLOYER_ADMIN'): ?>
                        <a href="<?= url('/employer/candidates/recommended') ?>" style="font-size: 0.8rem; color: #0d9488; font-weight: 700; text-decoration: none;">
                            Quản lý đầy đủ trong trang đề xuất
                        </a>
                    <?php else: ?>
                        <a href="<?= url('/auth/register') ?>" style="font-size: 0.8rem; color: #0d9488; font-weight: 700; text-decoration: none;">
                            Đăng ký tuyển dụng để phỏng vấn ngay
                        </a>
                    <?php endif; ?>
                </div>

                <div id="homeQualifiedContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem;">
                    <?php foreach ($homeQualifiedCandidates as $rec): ?>
                        <div class="sourcing-card home-cand-card qualified-card <?= ($rec['ai_match_score'] >= 85) ? 'featured' : '' ?>" data-score="<?= $rec['ai_match_score'] ?>" data-seeker-id="<?= $rec['seeker_id'] ?>">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.65rem; gap: 0.5rem;">
                                    <div style="display: flex; gap: 0.65rem; align-items: center;">
                                        <img src="<?= htmlspecialchars(getAvatarUrl($rec['avatar_url'] ?? '', 'JOB_SEEKER', $rec['gender'] ?? '')) ?>" alt="avatar" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $rec['gender'] ?? '') ?>';">
                                        <div>
                                            <h4 style="margin: 0; font-size: 0.98rem; font-weight: 800; color: #0f172a;">
                                                <?= htmlspecialchars($rec['full_name']) ?>
                                            </h4>
                                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 1px;">
                                                <?= htmlspecialchars($rec['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($rec['date_of_birth']) ? (date('Y') - date('Y', strtotime($rec['date_of_birth']))) . 't' : '' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                            </div>
                                        </div>
                                    </div>
                                    <span class="home-status-badge badge" style="background: <?= $rec['ai_match_score'] >= 85 ? '#ecfdf5' : '#f0fdfa' ?>; color: <?= $rec['ai_match_score'] >= 85 ? '#065f46' : '#0d9488' ?>; border: 1px solid <?= $rec['ai_match_score'] >= 85 ? '#a7f3d0' : '#ccfbf1' ?>; font-size: 0.74rem; font-weight: 700; padding: 0.2rem 0.55rem; white-space: nowrap;">
                                        ✓ <?= $rec['ai_match_score'] ?>% tương thích
                                    </span>
                                </div>

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

                            <div style="border-top: 1px solid #f1f5f9; padding-top: 0.65rem; display: flex; justify-content: space-between; align-items: center;">
                                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role_code'] === 'EMPLOYER_ADMIN'): ?>
                                    <a href="<?= url('/employer/candidates/' . $rec['seeker_id']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem; font-weight: 700;">
                                        Xem Hồ Sơ
                                    </a>
                                    <a href="<?= url('/employer/messages?partner_id=' . ($rec['account_id'] ?? 0) . '&seeker_id=' . ($rec['seeker_id'] ?? 0) . '&open_meeting_modal=1') ?>" class="btn btn-primary btn-sm" style="font-size: 0.78rem; font-weight: 700; background: #0d9488; border-color: #0d9488;">
                                        Hẹn Phỏng Vấn
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('/auth/login') ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem; font-weight: 700; color: #0d9488; border-color: #0d9488;">
                                        Đăng Nhập Kết Nối
                                    </a>
                                    <a href="<?= url('/auth/register') ?>" class="btn btn-primary btn-sm" style="font-size: 0.78rem; font-weight: 700;">
                                        Tuyển Dụng Ngay
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="homeEmptyQualified" class="card" style="display: <?= empty($homeQualifiedCandidates) ? 'block' : 'none' ?>; text-align: center; padding: 2rem 1rem; color: #64748b; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 10px; margin-top: 0.65rem;">
                    <p style="font-size: 0.85rem; margin: 0; color: #475569;">
                        Không có ứng viên nào đạt hạn mức <span class="home-threshold-val"><?= $homeThreshold ?>%</span>. Hãy kéo giảm hạn mức ở thanh trượt trên.
                    </p>
                </div>
            </div>

            <!-- PHẦN 2: ỨNG VIÊN CHƯA ĐẠT HẠN MỨC (NẰM Ở PHẦN CHƯA ĐẠT) -->
            <div class="unqualified-section-card" id="homeUnqualifiedWrapper" style="margin-top: 1rem; padding: 0.85rem 1rem;">
                <div class="unqualified-section-header" onclick="toggleHomeUnqualified()">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="color: #d97706;"><?= icon('alert_circle', '', 16) ?></span>
                            <strong style="font-size: 0.92rem; color: #92400e;">
                                Ứng Viên Chưa Đạt Hạn Mức (< <span class="home-threshold-val"><?= $homeThreshold ?>%</span>)
                            </strong>
                            <span class="unqualified-badge-warning" style="font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                                <span id="homeBadgeCountUnqualified"><?= count($homeUnqualifiedCandidates) ?></span> hồ sơ
                            </span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" id="btnToggleHomeUnqualified" style="background: #ffffff; border-color: #fde68a; color: #b45309; font-weight: 700; font-size: 0.74rem; padding: 0.25rem 0.55rem;">
                        <span id="homeToggleText">Mở Rộng (<?= count($homeUnqualifiedCandidates) ?>)</span>
                        <span id="homeToggleIcon">▼</span>
                    </button>
                </div>

                <div id="homeUnqualifiedContainer" style="display: none; margin-top: 0.85rem; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 0.85rem;">
                    <?php foreach ($homeUnqualifiedCandidates as $rec): ?>
                        <div class="sourcing-card home-cand-card unqualified-card" data-score="<?= $rec['ai_match_score'] ?>" data-seeker-id="<?= $rec['seeker_id'] ?>" style="padding: 0.85rem;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.65rem; gap: 0.5rem;">
                                    <div style="display: flex; gap: 0.65rem; align-items: center;">
                                        <img src="<?= htmlspecialchars(getAvatarUrl($rec['avatar_url'] ?? '', 'JOB_SEEKER', $rec['gender'] ?? '')) ?>" alt="avatar" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $rec['gender'] ?? '') ?>';">
                                        <div>
                                            <h4 style="margin: 0; font-size: 0.96rem; font-weight: 700; color: #0f172a;">
                                                <?= htmlspecialchars($rec['full_name']) ?>
                                            </h4>
                                            <div style="font-size: 0.74rem; color: #64748b; margin-top: 1px;">
                                                <?= htmlspecialchars($rec['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($rec['date_of_birth']) ? (date('Y') - date('Y', strtotime($rec['date_of_birth']))) . 't' : '' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                            </div>
                                        </div>
                                    </div>
                                    <span class="home-status-badge badge unqualified-badge-warning" style="font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 6px; white-space: nowrap;">
                                        ⚠ <?= $rec['ai_match_score'] ?>% (Dưới hạn mức)
                                    </span>
                                </div>

                                <div style="background: #fffbeb; border: 1px dashed #fde68a; border-radius: 6px; padding: 0.4rem 0.55rem; margin-bottom: 0.65rem; font-size: 0.72rem; color: #78350f;">
                                    <?= !empty($rec['ai_reasons']) ? htmlspecialchars($rec['ai_reasons'][0]) : 'Chưa đáp ứng đầy đủ tiêu chí kỹ năng tối ưu.' ?>
                                </div>
                            </div>

                            <div style="border-top: 1px solid #f1f5f9; padding-top: 0.65rem; display: flex; justify-content: flex-end;">
                                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role_code'] === 'EMPLOYER_ADMIN'): ?>
                                    <a href="<?= url('/employer/candidates/' . $rec['seeker_id']) ?>" style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-decoration: none;">
                                        Xem Chi Tiết Hồ Sơ
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('/auth/register') ?>" style="font-size: 0.78rem; color: #0d9488; font-weight: 700; text-decoration: none;">
                                        Đăng ký để xem hồ sơ
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="homeEmptyUnqualified" class="card" style="display: <?= empty($homeUnqualifiedCandidates) ? 'block' : 'none' ?>; text-align: center; padding: 1rem; color: #059669; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 8px; margin-top: 0.65rem;">
                    <p style="font-size: 0.8rem; margin: 0; font-weight: 600;">✓ Toàn bộ ứng viên đều đạt hạn mức bạn đã đặt.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BẢNG TIN VIỆC LÀM TUYỂN DỤNG CÔNG KHAI (JOB-FIRST INSTANT SHOWCASE) -->
<section class="container" style="margin-bottom: 3.5rem;">
    <div class="section-header" style="margin-bottom: 1.5rem;">
        <div>
            <h2 class="section-title" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.6rem; color: #0f172a;">
                <?= icon('briefcase', 'text-primary', 26) ?> Việc Làm Mới Nhất Được Xác Thực
            </h2>
            <p class="section-subtitle" style="font-size: 0.95rem; color: #64748b;">
                Ưu tiên hiển thị bài đăng từ các Doanh nghiệp đã Xác Thực Chữ Ký Số & Hợp đồng điện tử với Ban Quản Trị
            </p>
        </div>
        <a href="<?= url('/jobs') ?>" class="btn btn-outline btn-sm" style="font-weight: 700; color: #0d9488; border-color: #0d9488;">
            Xem tất cả <?= count($jobs) ?> việc làm
        </a>
    </div>

    <div class="grid-jobs">
        <?php if (empty($jobs)): ?>
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                <p style="color: var(--slate-500); font-size: 1.05rem;">Hiện chưa có bài đăng việc làm nào hoặc hệ thống đang cập nhật.</p>
                <a href="<?= url('/jobs') ?>" class="btn btn-primary btn-sm" style="margin-top: 1rem;">Xem trang tìm kiếm</a>
            </div>
        <?php else: ?>
            <?php foreach (array_slice($jobs, 0, 6) as $job): ?>
                <?php 
                    $isVerifiedCa = ($job['digital_signature_status'] ?? '') === 'verified';
                ?>
                <div class="card job-card" style="border-radius: 14px; border: 1px solid <?= $isVerifiedCa ? '#99f6e4' : '#e2e8f0' ?>; padding: 1.3rem; background: <?= $isVerifiedCa ? '#fbfdfd' : '#ffffff' ?>; display: flex; flex-direction: column; justify-content: space-between; box-shadow: <?= $isVerifiedCa ? '0 6px 18px rgba(13,148,136,0.08)' : '0 2px 8px rgba(0,0,0,0.03)' ?>; position: relative;">
                    
                    <?php if ($isVerifiedCa): ?>
                        <div style="position: absolute; top: -10px; right: 12px; background: linear-gradient(135deg, #0d9488 0%, #047857 100%); color: #fff; font-size: 0.68rem; font-weight: 800; padding: 3px 9px; border-radius: 9999px; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(13,148,136,0.35);">
                            <?= icon('certificate', '', 11) ?> <span>ƯU TIÊN TOP 1 &bull; ĐÃ KÝ SỐ</span>
                        </div>
                    <?php endif; ?>

                    <div>
                        <div style="display: flex; gap: 0.85rem; align-items: flex-start; justify-content: space-between; margin-top: <?= $isVerifiedCa ? '4px' : '0' ?>;">
                            <div style="display: flex; gap: 0.85rem; align-items: flex-start; flex: 1;">
                                <img src="<?= htmlspecialchars(getAvatarUrl($job['avatar_url'] ?? '', 'EMPLOYER')) ?>" 
                                     alt="logo" 
                                     style="width: 48px; height: 48px; border-radius: 12px; object-fit: cover; border: 1px solid #e2e8f0; flex-shrink: 0;"
                                     onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                                <div style="flex: 1;">
                                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; line-height: 1.35;">
                                        <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" style="color: #0f172a; text-decoration: none;">
                                            <?= htmlspecialchars($job['job_title']) ?>
                                        </a>
                                    </h3>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.83rem; color: #64748b; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                        <?= icon('building', '', 13) ?>
                                        <?php if (!empty($job['employer_id'])): ?>
                                            <a href="<?= url('/employers/' . $job['employer_id']) ?>" style="color: #334155; font-weight: 700; text-decoration: underline;" title="Xem trang doanh nghiệp">
                                                <?= htmlspecialchars($job['employer_name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <strong style="color: #334155;"><?= htmlspecialchars($job['employer_name']) ?></strong>
                                        <?php endif; ?>
                                        <?php if ($isVerifiedCa): ?>
                                            <span style="color: #059669; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 2px;">
                                                <?= icon('shield_check', '', 12) ?> Đã Ký Số
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 0.85rem; display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center;">
                            <?php if (!empty($job['workplace_address'])): ?>
                                <?php 
                                    $jobMapUrl = getGoogleMapsUrl($job['workplace_address'], $job['workplace_map_url'] ?? '');
                                ?>
                                <span class="badge badge-slate" style="font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('location', '', 11) ?>
                                    <a href="<?= htmlspecialchars($jobMapUrl) ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;" title="Mở vị trí trên Google Maps">
                                        <?= htmlspecialchars($job['workplace_address']) ?>
                                    </a>
                                </span>
                            <?php endif; ?>
                            <?= renderWorkShiftBadges($job['work_shift'] ?? '') ?>
                        </div>
                    </div>

                    <div style="margin-top: 1.15rem; padding-top: 0.85rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                        <div>
                            <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #94a3b8; letter-spacing: 0.03em;">Mức lương</div>
                            <div style="font-size: 1.05rem; font-weight: 800; color: #0d9488; line-height: 1.2; margin-top: 0.15rem;">
                                <?= formatSalary($job['salary_amount'], $job['employment_type'] ?? 'full_time') ?>
                            </div>
                        </div>
                        
                        <div>
                            <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" class="btn btn-primary btn-sm" style="font-weight: 800; padding: 0.45rem 1rem; border-radius: 8px; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap;">
                                <span>Xem & Ứng Tuyển</span>
                            </a>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- QUY TRÌNH 4 BƯỚC HOẠT ĐỘNG MINH BẠCH CỦA SÀN JOBLINK -->
<section style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 4rem 1.5rem; margin-bottom: 3.5rem;">
    <div class="container" style="max-width: 1140px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <span class="badge" style="background: #f0fdfa; color: #0d9488; font-weight: 700; border: 1px solid #99f6e4; padding: 0.35rem 0.85rem; border-radius: 9999px;">
                QUY TRÌNH KẾT NỐI CHUẨN HÓA
            </span>
            <h2 style="font-size: 1.85rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0 0 0;">
                Cách Thức JobLink Vận Hành Minh Bạch & An Toàn
            </h2>
            <p style="color: #64748b; font-size: 0.95rem; margin: 0.35rem auto 0 auto; max-width: 650px;">
                Mọi giao dịch tuyển dụng đều có chứng thực điện tử, cam kết điểm uy tín và hệ thống phản hồi 2 chiều.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem;">
            
            <div class="card" style="padding: 1.75rem; text-align: center; border-top: 4px solid #0d9488; border-radius: 12px; background: #ffffff;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #f0fdfa; color: #0d9488; font-weight: 900; font-size: 1.25rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; border: 2px solid #99f6e4;">
                    1
                </div>
                <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">Xác Thực Chữ Ký Số</h3>
                <p style="font-size: 0.84rem; color: #64748b; line-height: 1.6; margin: 0;">Doanh nghiệp đăng ký chữ ký số, ký hợp đồng điện tử 2 bên với Ban Quản Trị Sàn để kích hoạt quyền tuyển dụng.</p>
            </div>

            <div class="card" style="padding: 1.75rem; text-align: center; border-top: 4px solid #2563eb; border-radius: 12px; background: #ffffff;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #eff6ff; color: #2563eb; font-weight: 900; font-size: 1.25rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; border: 2px solid #bfdbfe;">
                    2
                </div>
                <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">Đăng Tin & Ủy Quyền</h3>
                <p style="font-size: 0.84rem; color: #64748b; line-height: 1.6; margin: 0;">Doanh nghiệp đăng tin việc làm rõ ràng ca làm/lương, hoặc lập văn bản ủy quyền pháp lý cho đại lý trung gian.</p>
            </div>

            <div class="card" style="padding: 1.75rem; text-align: center; border-top: 4px solid #d97706; border-radius: 12px; background: #ffffff;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #fef3c7; color: #d97706; font-weight: 900; font-size: 1.25rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; border: 2px solid #fde68a;">
                    3
                </div>
                <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">Cam Kết Ràng Buộc Uy Tín</h3>
                <p style="font-size: 0.84rem; color: #64748b; line-height: 1.6; margin: 0;">Người lao động ký cam kết điện tử không bùng ca/hẹn phỏng vấn, duy trì Điểm Uy Tín Trust Score 100 điểm.</p>
            </div>

            <div class="card" style="padding: 1.75rem; text-align: center; border-top: 4px solid #10b981; border-radius: 12px; background: #ffffff;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #ecfdf5; color: #10b981; font-weight: 900; font-size: 1.25rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; border: 2px solid #a7f3d0;">
                    4
                </div>
                <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">Đánh Giá Ca Làm 2 Chiều</h3>
                <p style="font-size: 0.84rem; color: #64748b; line-height: 1.6; margin: 0;">Sau mỗi ca làm, cả Doanh nghiệp và Người lao động đều đánh giá công khai, giúp minh bạch thu nhập và chất lượng.</p>
            </div>

        </div>
    </div>
</section>

<!-- DOANH NGHIỆP ĐỐI TÁC TIÊU BIỂU ĐÃ KÝ SỐ -->
<?php if (!empty($employers)): ?>
<section class="container" style="margin-bottom: 3.5rem;">
    <div class="section-header" style="margin-bottom: 1.5rem;">
        <div>
            <h2 class="section-title" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.5rem; color: #0f172a;">
                <?= icon('building', 'text-primary', 24) ?> Doanh Nghiệp Hàng Đầu Đã Xác Thực Chữ Ký Số
            </h2>
            <p class="section-subtitle" style="color: #64748b; font-size: 0.95rem;">Các doanh nghiệp uy tín cam kết trả lương đúng hạn và hợp đồng đầy đủ</p>
        </div>
        <a href="<?= url('/employers') ?>" class="btn btn-outline btn-sm" style="font-weight: 700; color: #0d9488; border-color: #0d9488;">
            Xem tất cả đối tác
        </a>
    </div>

    <div class="grid-employers">
        <?php foreach (array_slice($employers, 0, 4) as $emp): ?>
            <div class="card employer-card" style="border-radius: 14px; padding: 1.5rem; text-align: center; border: 1px solid #e2e8f0;">
                <img src="<?= htmlspecialchars(getAvatarUrl($emp['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" style="width: 64px; height: 64px; border-radius: 12px; object-fit: cover; margin: 0 auto 0.75rem auto; border: 1px solid #e2e8f0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                <h4 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 0.35rem;">
                    <a href="<?= url('/employers/' . $emp['employer_id']) ?>" style="color: #0f172a; text-decoration: none;"><?= htmlspecialchars($emp['employer_name']) ?></a>
                </h4>
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.85rem;"><?= htmlspecialchars($emp['business_field'] ?? 'Doanh nghiệp tuyển dụng') ?></p>
                <div style="display:flex; justify-content:center; gap:0.5rem; align-items:center; flex-wrap: wrap;">
                    <span class="badge badge-success" style="font-size: 0.75rem; padding: 0.25rem 0.55rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <?= icon('certificate', '', 12) ?> Đã Ký Số
                    </span>

                    <span class="badge badge-warning" style="font-size: 0.75rem; padding: 0.25rem 0.55rem;">
                        <?= icon('star_fill', '', 11) ?> <?= $emp['avg_rating'] ?? '5.0' ?> (<?= $emp['total_reviews'] ?? 0 ?>)
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- THỐNG KÊ ẤN TƯỢNG CỦA HỆ THỐNG -->
<section class="container" style="margin-bottom: 3.5rem;">
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 16px; padding: 2.5rem 2rem; color: #ffffff; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; text-align: center;">
        <div>
            <div style="font-size: 2.4rem; font-weight: 900; color: #34d399; line-height: 1;">5,000+</div>
            <div style="font-size: 0.9rem; color: #94a3b8; margin-top: 0.5rem; font-weight: 600;">Người lao động đã xác thực</div>
        </div>
        <div>
            <div style="font-size: 2.4rem; font-weight: 900; color: #38bdf8; line-height: 1;">1,200+</div>
            <div style="font-size: 0.9rem; color: #94a3b8; margin-top: 0.5rem; font-weight: 600;">Doanh nghiệp & Đại lý liên kết</div>
        </div>
        <div>
            <div style="font-size: 2.4rem; font-weight: 900; color: #fbbf24; line-height: 1;">100%</div>
            <div style="font-size: 0.9rem; color: #94a3b8; margin-top: 0.5rem; font-weight: 600;">Hợp đồng điện tử ký số SHA-256</div>
        </div>
        <div>
            <div style="font-size: 2.4rem; font-weight: 900; color: #a78bfa; line-height: 1;">99.8%</div>
            <div style="font-size: 0.9rem; color: #94a3b8; margin-top: 0.5rem; font-weight: 600;">Tỷ lệ phỏng vấn đúng giờ</div>
        </div>
    </div>
</section>

<!-- CALL TO ACTION BANNER -->
<section class="container" style="margin-bottom: 4rem;">
    <div style="background: linear-gradient(135deg, #f0fdfa 0%, #e6fffa 100%); border: 1px solid #99f6e4; border-radius: 16px; padding: 2.5rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
        <div style="max-width: 600px;">
            <h2 style="font-size: 1.6rem; font-weight: 800; color: #0f766e; margin: 0 0 0.5rem 0;">
                Bắt Đầu Kết Nối Việc Làm An Toàn Ngay Hôm Nay
            </h2>
            <p style="color: #115e59; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                Đăng ký tài khoản hoàn toàn miễn phí để tìm kiếm việc làm linh hoạt theo ca hoặc tìm kiếm nguồn nhân lực chất lượng cao có cam kết.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="<?= url('/auth/register') ?>" class="btn btn-primary" style="font-weight: 800; padding: 0.75rem 1.6rem; border-radius: 10px;">
                Đăng Ký Tài Khoản Mới
            </a>
            <a href="<?= url('/auth/login') ?>" class="btn btn-outline" style="font-weight: 700; color: #0d9488; border-color: #0d9488; padding: 0.75rem 1.4rem; border-radius: 10px; background: #ffffff;">
                Đăng Nhập Hệ Thống
            </a>
        </div>
    </div>
</section>

<script>
function switchHomeRecTab(tab) {
    const seekerPane = document.getElementById('homeRecSeekerPane');
    const employerPane = document.getElementById('homeRecEmployerPane');
    const btnSeeker = document.getElementById('tabBtnSeeker');
    const btnEmployer = document.getElementById('tabBtnEmployer');

    if (tab === 'seeker') {
        if (seekerPane) seekerPane.style.display = 'block';
        if (employerPane) employerPane.style.display = 'none';
        if (btnSeeker) {
            btnSeeker.style.background = '#ffffff';
            btnSeeker.style.color = '#0d9488';
            btnSeeker.style.boxShadow = '0 2px 6px rgba(0,0,0,0.08)';
        }
        if (btnEmployer) {
            btnEmployer.style.background = 'transparent';
            btnEmployer.style.color = '#475569';
            btnEmployer.style.boxShadow = 'none';
        }
    } else {
        if (seekerPane) seekerPane.style.display = 'none';
        if (employerPane) employerPane.style.display = 'block';
        if (btnEmployer) {
            btnEmployer.style.background = '#ffffff';
            btnEmployer.style.color = '#0d9488';
            btnEmployer.style.boxShadow = '0 2px 6px rgba(0,0,0,0.08)';
        }
        if (btnSeeker) {
            btnSeeker.style.background = 'transparent';
            btnSeeker.style.color = '#475569';
            btnSeeker.style.boxShadow = 'none';
        }
    }
}

function toggleHomeUnqualified() {
    const list = document.getElementById('homeUnqualifiedContainer');
    const btnText = document.getElementById('homeToggleText');
    const icon = document.getElementById('homeToggleIcon');
    if (!list) return;

    const count = document.getElementById('homeBadgeCountUnqualified') ? document.getElementById('homeBadgeCountUnqualified').textContent : '0';

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

function applyHomeThreshold(threshold) {
    threshold = parseInt(threshold, 10);
    if (isNaN(threshold) || threshold < 30) threshold = 70;
    if (threshold > 100) threshold = 100;

    document.querySelectorAll('.home-threshold-val').forEach(el => el.textContent = threshold + '%');
    const badge = document.getElementById('homeThresholdBadge');
    if (badge) badge.textContent = threshold + '%';
    const slider = document.getElementById('homeThresholdSlider');
    if (slider) slider.value = threshold;

    document.querySelectorAll('.home-preset').forEach(btn => {
        const pVal = parseInt(btn.getAttribute('data-preset'), 10);
        if (pVal === threshold) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    const allCards = document.querySelectorAll('.home-cand-card');
    const qualifiedList = document.getElementById('homeQualifiedContainer');
    const unqualifiedList = document.getElementById('homeUnqualifiedContainer');
    const emptyQualified = document.getElementById('homeEmptyQualified');
    const emptyUnqualified = document.getElementById('homeEmptyUnqualified');

    if (!qualifiedList || !unqualifiedList) return;

    let countQual = 0;
    let countUnqual = 0;

    allCards.forEach(card => {
        const score = parseInt(card.getAttribute('data-score'), 10) || 0;
        const statusBadge = card.querySelector('.home-status-badge');

        if (score >= threshold) {
            countQual++;
            card.classList.remove('unqualified-card');
            card.classList.add('qualified-card');
            if (statusBadge) {
                statusBadge.className = 'home-status-badge badge';
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
                statusBadge.className = 'home-status-badge badge unqualified-badge-warning';
                statusBadge.style.background = '#fef3c7';
                statusBadge.style.color = '#b45309';
                statusBadge.style.borderColor = '#fde68a';
                statusBadge.innerHTML = '⚠ ' + score + '% (Dưới hạn mức)';
            }
            unqualifiedList.appendChild(card);
        }
    });

    const bQual = document.getElementById('homeBadgeCountQualified');
    if (bQual) bQual.textContent = countQual;
    const bUnqual = document.getElementById('homeBadgeCountUnqualified');
    if (bUnqual) bUnqual.textContent = countUnqual;
    const cQual = document.getElementById('homeCountQualifiedBadge');
    if (cQual) cQual.textContent = countQual;
    const cUnqual = document.getElementById('homeCountUnqualifiedBadge');
    if (cUnqual) cUnqual.textContent = countUnqual;

    const btnText = document.getElementById('homeToggleText');
    if (btnText) {
        const isExpanded = unqualifiedList.style.display !== 'none' && unqualifiedList.style.display !== '';
        btnText.textContent = (isExpanded ? 'Thu Gọn (' : 'Mở Rộng (') + countUnqual + ')';
    }

    if (emptyQualified) emptyQualified.style.display = countQual === 0 ? 'block' : 'none';
    if (emptyUnqualified) emptyUnqualified.style.display = countUnqual === 0 ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('homeThresholdSlider');
    if (slider) {
        slider.addEventListener('input', function() {
            applyHomeThreshold(this.value);
        });
    }

    document.querySelectorAll('.home-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            const pVal = parseInt(this.getAttribute('data-preset'), 10);
            applyHomeThreshold(pVal);
        });
    });
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/main_layout.php';
?>
