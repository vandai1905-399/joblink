<?php 
$extraCss = ['css/applications/employer_applications.css', 'css/jobs/ai_matching.css'];
$activeTab = $_GET['tab'] ?? 'ai_recommendations';
ob_start(); 
?>

<!-- HEADER TIÊU ĐỀ TRANG -->
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
            <a href="<?= url('/employer/dashboard') ?>" class="btn btn-sm btn-outline" style="padding: 0.25rem 0.6rem; font-size: 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                <?= icon('chevron_left', '', 14) ?> <span>Tổng quan</span>
            </a>
            <span class="badge" style="background: #f0fdfa; color: #0d9488; border: 1px solid #ccfbf1; font-weight: 700; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 4px; padding: 0.25rem 0.6rem; border-radius: 6px;">
                <?= icon('sparkle', '', 13) ?> AI Talent Sourcing
            </span>
        </div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0.25rem 0; letter-spacing: -0.02em;">
            Quản Lý NLĐ Đề Xuất & Kho Hồ Sơ Tiềm Năng
        </h1>
        <p style="color: #64748b; font-size: 0.92rem; margin: 0;">
            Chủ động khai thác nguồn nhân lực phù hợp do AI gợi ý & tái kết nối nhân tài đã hẹn dịp sau
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="<?= url('/employer/jobs/create') ?>" class="btn btn-primary btn-sm" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
            <?= icon('plus', '', 14) ?> Đăng Tin Tuyển Dụng Mới
        </a>
        <a href="<?= url('/employer/applications') ?>" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
            <?= icon('applications', '', 14) ?> Tất Cả Hồ Sơ Ứng Tuyển
        </a>
    </div>
</div>

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
    <!-- BỘ LỌC CHỌN CÔNG VIỆC -->
    <div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
        <form method="GET" action="<?= url('/employer/candidates/recommended') ?>" style="display: flex; gap: 1.25rem; align-items: center; flex-wrap: wrap; justify-content: space-between;">
            <input type="hidden" name="tab" value="ai_recommendations">
            <div style="flex: 1; min-width: 280px; display: flex; align-items: center; gap: 0.75rem;">
                <span style="color: #0d9488; display: flex; align-items: center;">
                    <?= icon('briefcase', '', 20) ?>
                </span>
                <div style="flex: 1;">
                    <label style="font-size: 0.78rem; font-weight: 700; color: #475569; display: block; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.3px;">
                        Vị trí tuyển dụng cần ghép nối người lao động:
                    </label>
                    <select name="job_id" class="form-control" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.9rem; font-weight: 600; color: #0f172a; background: #f8fafc;">
                        <?php foreach ($activeJobs as $j): ?>
                            <option value="<?= $j['job_post_id'] ?>" <?= $selectedJobId === intval($j['job_post_id']) ? 'selected' : '' ?>>
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
        </form>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.25rem;">
        <?php if (empty($aiCandidates)): ?>
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3.5rem 1.5rem; color: #64748b; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
                <div style="color: #94a3b8; margin-bottom: 0.75rem;">
                    <?= icon('search', '', 44) ?>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Chưa có người lao động phù hợp với tiêu chí hiện tại</h3>
                <p style="font-size: 0.88rem; max-width: 500px; margin: 0 auto; color: #64748b;">
                    Hệ thống AI liên tục cập nhật hồ sơ mới. Bạn có thể mở rộng yêu cầu tin tuyển dụng hoặc chọn vị trí khác để xem đề xuất.
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($aiCandidates as $rec): ?>
                <div class="sourcing-card <?= ($rec['ai_match_score'] >= 85) ? 'featured' : '' ?>">
                    <div>
                        <!-- Header Card -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.85rem; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.75rem; align-items: center;">
                                <img src="<?= htmlspecialchars(!empty($rec['avatar_url']) ? $rec['avatar_url'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150') ?>" alt="avatar" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;">
                                <div style="min-width: 0;">
                                    <a href="<?= url('/employer/candidates/' . $rec['seeker_id']) ?>" style="font-weight: 700; font-size: 1.02rem; color: #0f172a; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Xem hồ sơ chi tiết">
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;"><?= htmlspecialchars($rec['full_name']) ?></span>
                                        <span style="color: #0d9488;"><?= icon('arrow_right', '', 13) ?></span>
                                    </a>
                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                                        <?= htmlspecialchars($rec['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($rec['date_of_birth']) ? (date('Y') - date('Y', strtotime($rec['date_of_birth']))) . ' tuổi' : '' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                    </div>
                                </div>
                            </div>
                            <span class="badge" style="background: <?= $rec['ai_match_score'] >= 80 ? '#ecfdf5' : '#f0fdfa' ?>; color: <?= $rec['ai_match_score'] >= 80 ? '#065f46' : '#0d9488' ?>; border: 1px solid <?= $rec['ai_match_score'] >= 80 ? '#a7f3d0' : '#ccfbf1' ?>; font-size: 0.76rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                                <?= icon('check_circle', '', 12) ?> <?= $rec['ai_match_score'] ?>% tương thích
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

                        <!-- Kỹ năng trùng khớp -->
                        <?php if (!empty($rec['ai_matched_skills'])): ?>
                            <div style="margin-bottom: 0.85rem;">
                                <div style="font-size: 0.75rem; color: #047857; font-weight: 700; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 4px;">
                                    <?= icon('check', '', 12) ?> Kỹ năng đáp ứng tốt:
                                </div>
                                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                    <?php foreach ($rec['ai_matched_skills'] as $sk): ?>
                                        <span style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600;">
                                            <?= htmlspecialchars($sk) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer Card -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 0.85rem; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                        <a href="<?= url('/employer/candidates/' . $rec['seeker_id']) ?>" style="font-size: 0.82rem; color: #0d9488; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
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
        <?php endif; ?>
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
                            <img src="<?= htmlspecialchars(!empty($cand['seeker_avatar']) ? $cand['seeker_avatar'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150') ?>" alt="avatar" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid <?= $isNearlyQualified ? '#059669' : '#e2e8f0' ?>; flex-shrink: 0;">
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <a href="<?= url('/employer/candidates/' . $cand['seeker_id']) ?>" style="font-weight: 700; font-size: 1.02rem; color: #0f172a; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Bấm xem hồ sơ chi tiết">
                                        <span><?= htmlspecialchars($cand['seeker_name']) ?></span>
                                        <span style="color: #0d9488;"><?= icon('arrow_right', '', 13) ?></span>
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
document.addEventListener('DOMContentLoaded', function () {
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
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>