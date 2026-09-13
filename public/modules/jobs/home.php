<?php 
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
                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.98rem; font-weight: 800; color: #0f172a;">Chữ Ký Số CA Hợp Chuẩn</h4>
                <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.5;">100% Doanh nghiệp xác thực Chứng Thư Số CA (VNPT, Viettel, FPT) & Hợp đồng điện tử.</p>
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

<!-- BẢNG TIN VIỆC LÀM TUYỂN DỤNG CÔNG KHAI (JOB-FIRST INSTANT SHOWCASE) -->
<section class="container" style="margin-bottom: 3.5rem;">
    <div class="section-header" style="margin-bottom: 1.5rem;">
        <div>
            <h2 class="section-title" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.6rem; color: #0f172a;">
                <?= icon('briefcase', 'text-primary', 26) ?> Việc Làm Mới Nhất Được Xác Thực
            </h2>
            <p class="section-subtitle" style="font-size: 0.95rem; color: #64748b;">
                Ưu tiên hiển thị bài đăng từ các Doanh nghiệp đã Xác Thực Chữ Ký Số CA & Hợp đồng điện tử với Ban Quản Trị
            </p>
        </div>
        <a href="<?= url('/jobs') ?>" class="btn btn-outline btn-sm" style="font-weight: 700; color: #0d9488; border-color: #0d9488;">
            Xem tất cả <?= count($jobs) ?> việc làm &rarr;
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
                            <?= icon('certificate', '', 11) ?> <span>ƯU TIÊN TOP 1 &bull; ĐÃ KÝ SỐ CA</span>
                        </div>
                    <?php endif; ?>

                    <div>
                        <div style="display: flex; gap: 0.85rem; align-items: flex-start; justify-content: space-between; margin-top: <?= $isVerifiedCa ? '4px' : '0' ?>;">
                            <div style="display: flex; gap: 0.85rem; align-items: flex-start; flex: 1;">
                                <img src="<?= htmlspecialchars($job['avatar_url'] ?? 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=150') ?>" 
                                     alt="logo" 
                                     style="width: 48px; height: 48px; border-radius: 12px; object-fit: cover; border: 1px solid #e2e8f0; flex-shrink: 0;">
                                <div style="flex: 1;">
                                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; line-height: 1.35;">
                                        <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" style="color: #0f172a; text-decoration: none;">
                                            <?= htmlspecialchars($job['job_title']) ?>
                                        </a>
                                    </h3>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.83rem; color: #64748b; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                        <?= icon('building', '', 13) ?>
                                        <strong style="color: #334155;"><?= htmlspecialchars($job['employer_name']) ?></strong>
                                        <?php if ($isVerifiedCa): ?>
                                            <span style="color: #059669; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 2px;">
                                                <?= icon('shield_check', '', 12) ?> Đã Ký Số CA
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 0.85rem; display: flex; flex-wrap: wrap; gap: 0.4rem; align-items: center;">
                            <span class="badge badge-slate" style="font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <?= icon('clock', '', 12) ?>
                                <span>Ca: <?= htmlspecialchars($job['work_shift'] ?? 'Linh hoạt') ?></span>
                            </span>
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
                            <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" class="btn btn-primary btn-sm" style="font-weight: 800; padding: 0.45rem 1rem; border-radius: 8px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; white-space: nowrap;">
                                <span>Xem & Ứng Tuyển</span>
                                <?= icon('arrow_right', '', 12) ?>
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
                <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">Xác Thực Chữ Ký Số CA</h3>
                <p style="font-size: 0.84rem; color: #64748b; line-height: 1.6; margin: 0;">Doanh nghiệp đăng ký chứng thư số CA, ký hợp đồng điện tử 2 bên với Ban Quản Trị Sàn để kích hoạt quyền tuyển dụng.</p>
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

<!-- DOANH NGHIỆP ĐỐI TÁC TIÊU BIỂU ĐÃ KÝ SỐ CA -->
<?php if (!empty($employers)): ?>
<section class="container" style="margin-bottom: 3.5rem;">
    <div class="section-header" style="margin-bottom: 1.5rem;">
        <div>
            <h2 class="section-title" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.5rem; color: #0f172a;">
                <?= icon('building', 'text-primary', 24) ?> Doanh Nghiệp Hàng Đầu Đã Xác Thực Chữ Ký Số (CA)
            </h2>
            <p class="section-subtitle" style="color: #64748b; font-size: 0.95rem;">Các doanh nghiệp uy tín cam kết trả lương đúng hạn và hợp đồng đầy đủ</p>
        </div>
        <a href="<?= url('/employers') ?>" class="btn btn-outline btn-sm" style="font-weight: 700; color: #0d9488; border-color: #0d9488;">
            Xem tất cả đối tác &rarr;
        </a>
    </div>

    <div class="grid-employers">
        <?php foreach (array_slice($employers, 0, 4) as $emp): ?>
            <div class="card employer-card" style="border-radius: 14px; padding: 1.5rem; text-align: center; border: 1px solid #e2e8f0;">
                <img src="<?= htmlspecialchars($emp['avatar_url'] ?? 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=150') ?>" alt="logo" style="width: 64px; height: 64px; border-radius: 12px; object-fit: cover; margin: 0 auto 0.75rem auto; border: 1px solid #e2e8f0;">
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

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/main_layout.php';
?>
