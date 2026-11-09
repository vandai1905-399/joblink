<?php 
$extraCss = ['css/jobs/job_detail.css', 'css/jobs/ai_matching.css'];
$extraJs = ['js/jobs/jobs.js', 'js/jobs/ai_recommendation.js'];
ob_start(); 
?>

<?php 
$currentUserRole = $_SESSION['user']['role_code'] ?? '';
$isAdminUser = in_array($currentUserRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
$backUrl = $isAdminUser ? url('/admin/job-posts') : url('/jobs');
$backText = $isAdminUser ? 'Quay lại danh sách duyệt tin' : 'Quay lại danh sách việc làm';
?>

<div class="container" style="padding-top: 1rem; padding-bottom: 3rem;">
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <a href="<?= $backUrl ?>" class="workspace-nav-link" data-title="Duyệt Tin Tuyển Dụng" style="color: var(--primary); font-weight: 600; text-decoration: none;">
            <?= $backText ?>
        </a>
        <?php if (!empty($aiFitAnalysis)): ?>
            <span class="ai-match-badge <?= $aiFitAnalysis['level'] ?>" style="font-size: 0.82rem; padding: 0.35rem 0.75rem;">
                <?= icon('sparkle', '', 12) ?>
                <span>Tương thích <?= $aiFitAnalysis['score'] ?>% (<?= $aiFitAnalysis['label'] ?>)</span>
            </span>
        <?php endif; ?>
    </div>

    <?php if (!empty($aiFitAnalysis)): ?>
    <!-- KHỐI PHÂN TÍCH ĐỘ TƯƠNG THÍCH AI TOÀN DIỆN -->
    <div class="ai-recommend-container" style="margin-bottom: 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <div class="ai-badge-header">
                    <?= icon('sparkle', '', 12) ?> <span>AI Matching Insights</span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Đánh Giá Độ Tương Thích Của Bạn Với Vị Trí Này
                </h3>
            </div>
            <div style="font-size: 1.8rem; font-weight: 800; color: <?= $aiFitAnalysis['color'] ?>;">
                <?= $aiFitAnalysis['score'] ?>%
            </div>
        </div>

        <div class="ai-breakdown-grid">
            <div class="ai-breakdown-card">
                <div class="ai-breakdown-score"><?= $aiFitAnalysis['breakdown']['skills'] ?>%</div>
                <div class="ai-breakdown-label">Kỹ năng & Chức danh</div>
            </div>
            <div class="ai-breakdown-card">
                <div class="ai-breakdown-score"><?= $aiFitAnalysis['breakdown']['location'] ?>%</div>
                <div class="ai-breakdown-label">Địa điểm làm việc</div>
            </div>
            <div class="ai-breakdown-card">
                <div class="ai-breakdown-score"><?= $aiFitAnalysis['breakdown']['employment'] ?>%</div>
                <div class="ai-breakdown-label">Lương & Hình thức</div>
            </div>
            <div class="ai-breakdown-card">
                <div class="ai-breakdown-score"><?= $aiFitAnalysis['breakdown']['experience'] ?>%</div>
                <div class="ai-breakdown-label">Kinh nghiệm & Uy tín</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <!-- Cột kỹ năng -->
            <div style="background: #ffffff; border-radius: 10px; padding: 1rem; border: 1px solid #ccfbf1;">
                <div style="font-weight: 700; font-size: 0.88rem; color: #0f766e; margin-bottom: 0.45rem; display: flex; align-items: center; gap: 0.35rem;">
                    <?= icon('check_circle', '', 15) ?> <span>Kỹ năng bạn đã đáp ứng:</span>
                </div>
                <?php if (!empty($aiFitAnalysis['matched_skills'])): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                        <?php foreach ($aiFitAnalysis['matched_skills'] as $sk): ?>
                            <span class="ai-skill-tag matched" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('check', '', 12) ?> <span><?= htmlspecialchars($sk) ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="font-size: 0.82rem; color: #64748b; margin: 0;">Chưa phát hiện kỹ năng trùng khớp trực tiếp.</p>
                <?php endif; ?>

                <?php if (!empty($aiFitAnalysis['missing_skills'])): ?>
                    <div style="font-weight: 700; font-size: 0.88rem; color: #c2410c; margin-top: 0.75rem; margin-bottom: 0.45rem;">
                        + Kỹ năng NTD yêu cầu thêm:
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                        <?php foreach ($aiFitAnalysis['missing_skills'] as $msk): ?>
                            <span class="ai-skill-tag missing">+ <?= htmlspecialchars($msk) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cột lời khuyên ứng tuyển -->
            <div style="background: #eff6ff; border-radius: 10px; padding: 1rem; border: 1px solid #bfdbfe;">
                <div style="font-weight: 700; font-size: 0.88rem; color: #1e40af; margin-bottom: 0.45rem; display: flex; align-items: center; gap: 0.35rem;">
                    <?= icon('ai', '', 15) ?> <span>Lời khuyên phỏng vấn từ AI:</span>
                </div>
                <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.83rem; color: #1e3a8a; line-height: 1.55; display: flex; flex-direction: column; gap: 0.35rem;">
                    <?php foreach ($aiFitAnalysis['advice'] as $adv): ?>
                        <li><?= $adv ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
        <!-- Chi tiết bài đăng -->
        <div>
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="display: flex; gap: 1.25rem; align-items: center; margin-bottom: 1.5rem;">
                    <img src="<?= htmlspecialchars(getAvatarUrl($job['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" style="width: 72px; height: 72px; border-radius: var(--radius-sm); object-fit: cover; border: 1px solid var(--slate-200);" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                    <div>
                        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">
                            <?= htmlspecialchars($job['job_title']) ?>
                        </h1>
                        <p style="font-size: 1.05rem; color: var(--primary); font-weight: 600;">
                            <a href="<?= url('/employers/' . $job['employer_id']) ?>"><?= htmlspecialchars($job['employer_name']) ?></a>
                        </p>
                    </div>
                </div>

                <div class="grid-stats" style="margin-bottom: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
                    <div class="stat-card">
                        <div class="stat-title">Mức lương</div>
                        <div style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-top: 0.25rem;">
                            <?= formatSalary($job['salary_amount'], $job['employment_type'] ?? 'full_time') ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Hình thức</div>
                        <div style="font-size: 1rem; font-weight: 700; color: var(--dark); margin-top: 0.25rem;">
                            <?php 
                                if ($job['employment_type'] === 'full_time') echo 'Toàn thời gian (Full-time)';
                                elseif ($job['employment_type'] === 'part_time') echo 'Bán thời gian (Part-time)';
                                elseif ($job['employment_type'] === 'temporary') echo 'Thời vụ (Temporary)';
                                else echo 'Toàn thời gian';
                            ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Số lượng tuyển</div>
                        <div style="font-size: 1.2rem; font-weight: 800; color: var(--dark); margin-top: 0.25rem;">
                            <?= $job['number_of_positions'] ?> người
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Hạn nộp hồ sơ</div>
                        <div style="font-size: 1rem; font-weight: 700; color: var(--warning); margin-top: 0.25rem;">
                            <?= date('d/m/Y', strtotime($job['application_deadline'])) ?>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">Ca làm & Ngày làm việc</h3>
                    <div style="margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <strong>Ca làm việc:</strong>
                        <div style="display: inline-flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                            <?= renderWorkShiftBadges($job['work_shift'] ?? 'Theo thỏa thuận') ?>
                        </div>
                    </div>
                    <p>
                        <strong>Lịch / Ngày làm việc:</strong> 
                        <span class="badge badge-primary" style="font-size: 0.85rem; font-weight: 600; margin-left: 0.35rem;">
                            <?= htmlspecialchars($job['working_days'] ?? 'Theo thỏa thuận') ?>
                        </span>
                    </p>
                    <p style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
                        <strong>Địa điểm làm việc:</strong> 
                        <?php 
                            $jobAddrText = $job['workplace_address'] ?? 'Thủ Dầu Một, Bình Dương';
                            $jobMapUrl = !empty($job['workplace_map_url']) ? $job['workplace_map_url'] : getGoogleMapsUrl($jobAddrText);
                        ?>
                        <a href="<?= htmlspecialchars($jobMapUrl) ?>" target="_blank" rel="noopener noreferrer" style="color: #0d9488; text-decoration: underline; font-weight: 600;" title="Mở trên Google Maps">
                            <?= htmlspecialchars($jobAddrText) ?>
                        </a>
                        <?php if (!empty($jobMapUrl)): ?>
                            <a href="<?= htmlspecialchars($jobMapUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm" style="padding: 0.15rem 0.55rem; font-size: 0.78rem; color: #0d9488; border-color: #0d9488; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <?= icon('location', '', 13) ?> <span>Xem trên Google Maps</span>
                            </a>
                        <?php endif; ?>
                    </p>

                    <?php if (!empty($distanceInfo)): ?>
                        <div style="margin-top: 0.75rem; padding: 0.75rem 1rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 0.88rem; color: #166534; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <?= icon('map_pin', '', 16) ?>
                                <span>Khoảng cách: <strong><?= $distanceInfo['distance_text'] ?></strong> (từ <em><?= htmlspecialchars($distanceInfo['from_location']) ?></em> đến nơi làm việc)</span>
                            </div>
                            <a href="<?= getGoogleMapsUrl($job['workplace_address'] ?? '', '', $distanceInfo['from_location']) ?>" target="_blank" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.65rem; font-size: 0.78rem; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;">
                                <span>Chỉ đường Google Maps</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tiêu chuẩn tuyển dụng người lao động -->
                <?php
                    $minAge = !empty($job['min_age']) ? (int)$job['min_age'] : null;
                    $maxAge = !empty($job['max_age']) ? (int)$job['max_age'] : null;
                    $genderReq = $job['gender_requirement'] ?? 'all';
                    $eduLevel = $job['education_level'] ?? 'none';
                    $expReq = $job['experience_required'] ?? 'none';

                    $ageText = 'Mọi lứa tuổi (Không giới hạn)';
                    if ($minAge && $maxAge) {
                        $ageText = "{$minAge} - {$maxAge} tuổi";
                    } elseif ($minAge) {
                        $ageText = "Từ {$minAge} tuổi trở lên";
                    } elseif ($maxAge) {
                        $ageText = "Dưới {$maxAge} tuổi";
                    }

                    $genderMap = [
                        'all' => 'Không yêu cầu (Nam / Nữ)',
                        'male' => 'Nam',
                        'female' => 'Nữ'
                    ];
                    $genderText = $genderMap[$genderReq] ?? 'Không yêu cầu';

                    $eduMap = [
                        'none' => 'Không yêu cầu bằng cấp',
                        'high_school' => 'Tốt nghiệp THPT / Cấp 3',
                        'vocational' => 'Trung cấp / Trường nghề',
                        'college' => 'Cao đẳng',
                        'university' => 'Đại học trở lên'
                    ];
                    $eduText = $eduMap[$eduLevel] ?? 'Không yêu cầu bằng cấp';

                    $expMap = [
                        'none' => 'Không yêu cầu kinh nghiệm (Được đào tạo)',
                        'under_1_year' => 'Dưới 1 năm kinh nghiệm',
                        '1_to_2_years' => '1 - 2 năm kinh nghiệm',
                        '3_to_5_years' => '3 - 5 năm kinh nghiệm',
                        'over_5_years' => 'Trên 5 năm kinh nghiệm'
                    ];
                    $expText = $expMap[$expReq] ?? 'Không yêu cầu kinh nghiệm';

                    $otherTraits = [];
                    if (!empty($job['other_requirements'])) {
                        if (is_array($job['other_requirements'])) {
                            $otherTraits = $job['other_requirements'];
                        } else {
                            $dec = json_decode($job['other_requirements'], true);
                            $otherTraits = is_array($dec) ? $dec : explode(',', $job['other_requirements']);
                        }
                    }
                    $otherTraits = array_filter(array_map('trim', $otherTraits));
                ?>
                <div style="margin-bottom: 1.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.85rem; color: #0f172a; display: flex; align-items: center; gap: 0.45rem;">
                        <?= icon('user_check', '', 18) ?> <span>Tiêu Chuẩn Tuyển Dụng</span>
                    </h3>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem; margin-bottom: <?= !empty($otherTraits) ? '1rem' : '0' ?>;">
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.9rem;">
                            <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 0.2rem; display: flex; align-items: center; gap: 0.35rem;">
                                <?= icon('target', '', 13) ?> <span>Độ tuổi</span>
                            </div>
                            <div style="font-size: 0.92rem; font-weight: 700; color: #0f172a;">
                                <?= htmlspecialchars($ageText) ?>
                            </div>
                        </div>

                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.9rem;">
                            <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 0.2rem; display: flex; align-items: center; gap: 0.35rem;">
                                <?= icon('user', '', 13) ?> <span>Giới tính</span>
                            </div>
                            <div style="font-size: 0.92rem; font-weight: 700; color: #0f172a;">
                                <?= htmlspecialchars($genderText) ?>
                            </div>
                        </div>

                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.9rem;">
                            <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 0.2rem; display: flex; align-items: center; gap: 0.35rem;">
                                <?= icon('briefcase', '', 13) ?> <span>Kinh nghiệm</span>
                            </div>
                            <div style="font-size: 0.92rem; font-weight: 700; color: #0f172a;">
                                <?= htmlspecialchars($expText) ?>
                            </div>
                        </div>

                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.9rem;">
                            <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 0.2rem; display: flex; align-items: center; gap: 0.35rem;">
                                <?= icon('award', '', 13) ?> <span>Trình độ học vấn</span>
                            </div>
                            <div style="font-size: 0.92rem; font-weight: 700; color: #0f172a;">
                                <?= htmlspecialchars($eduText) ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($otherTraits)): ?>
                        <div style="border-top: 1px dashed #cbd5e1; padding-top: 0.75rem;">
                            <div style="font-size: 0.8rem; color: #475569; font-weight: 700; margin-bottom: 0.45rem;">
                                Tiêu chí & Kỹ năng mong muốn:
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                <?php foreach ($otherTraits as $trait): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 0.35rem; background: #ffffff; border: 1px solid #99f6e4; color: #0f766e; padding: 0.3rem 0.65rem; border-radius: 20px; font-size: 0.82rem; font-weight: 600;">
                                        <?= icon('check_circle', '', 13) ?>
                                        <span><?= htmlspecialchars($trait) ?></span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">Yêu cầu công việc</h3>
                    <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-sm); line-height: 1.7;">
                        <?= nl2br(htmlspecialchars($job['job_requirements'])) ?>
                    </div>
                </div>

                <?php if (!empty($job['additional_requirements'])): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">Yêu cầu bổ sung & Quyền lợi</h3>
                        <div style="background: var(--slate-50); padding: 1rem; border-radius: var(--radius-sm); line-height: 1.7;">
                            <?= nl2br(htmlspecialchars($job['additional_requirements'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="display: flex; gap: 1rem; margin-top: 2rem; align-items: center; flex-wrap: wrap;">
                    <?php if ($isAdminUser): ?>
                        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 1rem 1.25rem; width: 100%; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <div style="font-size: 0.82rem; font-weight: 700; color: #475569; text-transform: uppercase;">Trạng Thái Tin Tuyển Dụng:</div>
                                <div style="margin-top: 0.25rem;">
                                    <?php if (($job['post_status'] ?? '') === 'approved'): ?>
                                        <span class="badge badge-success" style="font-size: 0.85rem;"><?= icon('check', '', 14) ?> Đã Phê Duyệt</span>
                                    <?php elseif (($job['post_status'] ?? '') === 'pending'): ?>
                                        <span class="badge badge-warning" style="font-size: 0.85rem;"><?= icon('clock', '', 14) ?> Đang Chờ Kiểm Duyệt</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger" style="font-size: 0.85rem;"><?= icon('x_circle', '', 14) ?> Từ Chối / Đã Ẩn</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                <?php if (($job['post_status'] ?? '') === 'pending'): ?>
                                    <form action="<?= url('/admin/job-posts/approve/' . $job['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success" style="padding: 0.55rem 1.25rem; font-weight: 700;">
                                            <?= icon('check', '', 14) ?> Duyệt Tin Này
                                        </button>
                                    </form>
                                    <form action="<?= url('/admin/job-posts/approve/' . $job['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="button" class="btn btn-danger" style="padding: 0.55rem 1.25rem; font-weight: 700;" data-reject-prompt="Lý Do Từ Chối Tin Tuyển Dụng">
                                            <?= icon('x_circle', '', 14) ?> Từ Chối Tin
                                        </button>
                                    </form>
                                <?php elseif (($job['post_status'] ?? '') === 'approved'): ?>
                                    <form action="<?= url('/admin/job-posts/approve/' . $job['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="pending">
                                        <button type="button" class="btn btn-warning" style="padding: 0.55rem 1.25rem; font-weight: 700;" data-reject-prompt="Lý Do Hủy Duyệt Tin Tuyển Dụng">
                                            Hủy Duyệt
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= url('/admin/job-posts/approve/' . $job['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success" style="padding: 0.55rem 1.25rem; font-weight: 700;">
                                            Duyệt Lại
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <a href="<?= url('/admin/job-posts') ?>" class="btn btn-outline workspace-nav-link" data-title="Duyệt Tin Tuyển Dụng" style="padding: 0.55rem 1rem; font-weight: 600;">
                                    Danh Sách Tin
                                </a>
                            </div>
                        </div>
                    <?php elseif (isset($_SESSION['user']) && $_SESSION['user']['role_code'] === 'JOB_SEEKER'): ?>
                        <?php if (!empty($hasApplied)): ?>
                            <!-- Đã ứng tuyển: Vô hiệu hóa nút -->
                            <button type="button" class="btn btn-success" disabled style="padding: 0.8rem 2rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.4rem; opacity: 0.85; cursor: not-allowed;">
                                <?= icon('check_circle', '', 18) ?> <span>Đã Ứng Tuyển</span>
                            </button>
                            <span style="font-size: 0.85rem; color: #059669; font-weight: 600;">Bạn đã nộp hồ sơ cho vị trí này. Vui lòng chờ NTD phản hồi.</span>
                        <?php elseif ($job['employment_type'] === 'full_time'): ?>
                            <!-- Toàn thời gian: Ứng tuyển trực tiếp trọn gói cả tháng -->
                            <form action="<?= url('/seeker/apply/' . $job['job_post_id']) ?>" method="POST" style="display:inline;">
                                <input type="hidden" name="available_work_dates" value="Toàn thời gian (Cả tháng)">
                                <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                                    <?= icon('rocket', '', 18) ?> <span>Nộp Đơn Ứng Tuyển Ngay</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Bán thời gian / Thời vụ / Xoay ca: Mở Modal chọn ngày có thể đi làm -->
                            <button type="button" id="btnOpenApplyModal" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                                <?= icon('rocket', '', 18) ?> <span>Đăng Ký Ngày Làm & Ứng Tuyển</span>
                            </button>
                        <?php endif; ?>

                        <button class="btn btn-outline btn-save-job" data-job-id="<?= $job['job_post_id'] ?>" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                            <?= icon('saved', '', 16) ?> <span>Lưu Việc Làm</span>
                        </button>
                    <?php elseif (!isset($_SESSION['user'])): ?>
                        <a href="<?= url('/auth/login') ?>" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem;">
                            Đăng Nhập Để Ứng Tuyển
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Đánh giá về nhà tuyển dụng này -->
            <div class="card">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">
                    Đánh Giá Từ Người Lao Động Đã Làm Việc (<?= count($employerReviews) ?>)
                </h3>
                <?php if (empty($employerReviews)): ?>
                    <p style="color: var(--slate-500);">Chưa có đánh giá nào cho nhà tuyển dụng này.</p>
                <?php else: ?>
                    <?php foreach ($employerReviews as $rev): ?>
                        <div style="border-bottom: 1px solid var(--slate-100); padding-bottom: 1rem; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <strong><?= $rev['is_anonymous'] ? 'Người lao động ẩn danh' : htmlspecialchars($rev['seeker_name']) ?></strong>
                                <span class="badge badge-warning"><?= icon('star_fill', '', 12) ?> <?= $rev['overall_rating'] ?>/5</span>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--slate-400); margin-bottom: 0.5rem;">
                                Tiêu chí: <?= htmlspecialchars($rev['criteria_name'] ?? 'Ca làm việc') ?> • <?= date('d/m/Y', strtotime($rev['created_at'])) ?>
                            </div>
                            <p style="font-size: 0.95rem; color: var(--slate-700);"><?= htmlspecialchars($rev['review_content']) ?></p>

                            <?php if (!empty($rev['reply_content'])): ?>
                                <div style="background: var(--primary-light); padding: 0.75rem; border-radius: var(--radius-sm); margin-top: 0.5rem; font-size: 0.9rem;">
                                    <strong>Phản hồi từ NTD:</strong> <?= htmlspecialchars($rev['reply_content']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cột thông tin công ty -->
        <div>
            <div class="card" style="position: sticky; top: 85px;">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem;">Thông Tin Doanh Nghiệp</h3>
                <p style="font-weight: 700; color: var(--dark); font-size: 1.05rem;">
                    <a href="<?= url('/employers/' . $job['employer_id']) ?>" data-title="<?= htmlspecialchars($job['employer_name']) ?>" style="color: var(--dark); text-decoration: underline;" title="Xem hồ sơ doanh nghiệp">
                        <?= htmlspecialchars($job['employer_name']) ?>
                    </a>
                </p>
                <div style="margin: 0.75rem 0;">
                    <span class="badge badge-success"><?= icon('check', '', 12) ?> Đã xác minh GPKD</span>
                    <span class="badge badge-warning"><?= icon('star_fill', '', 12) ?> <?= $job['avg_rating'] ?? '5.0' ?> (<?= $job['total_reviews'] ?? 0 ?> đánh giá)</span>
                </div>
                <p style="font-size: 0.9rem; color: var(--slate-600); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                    <?= icon('mail', '', 14) ?> <span>Email: <?= htmlspecialchars($job['contact_email'] ?? 'Chưa cập nhật') ?></span>
                </p>
                <p style="font-size: 0.9rem; color: var(--slate-600); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                    <?= icon('phone', '', 14) ?> <span>Điện thoại: <?= htmlspecialchars($job['employer_phone'] ?? 'Chưa cập nhật') ?></span>
                </p>
                <?php if (!empty($job['website_url'])): ?>
                    <p style="font-size: 0.9rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.4rem;">
                        <?= icon('globe', '', 14) ?> <a href="<?= htmlspecialchars($job['website_url']) ?>" target="_blank" style="color: var(--primary);"><?= htmlspecialchars($job['website_url']) ?></a>
                    </p>
                <?php endif; ?>
                <a href="<?= url('/employers/' . $job['employer_id']) ?>" class="btn btn-outline btn-sm" style="width: 100%; margin-top: 0.5rem;">Xem Hồ Sơ Công Ty</a>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL ĐĂNG KÝ NGÀY CÓ THỂ ĐI LÀM (CHO BÁN THỜI GIAN & THỜI VỤ / XOAY CA) -->
<!-- ========================================================================= -->
<div id="applyDateModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; backdrop-filter: blur(3px); justify-content: center; align-items: center; padding: 1rem;">
    <div style="background: #ffffff; width: 100%; max-width: 580px; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; animation: scaleIn 0.25s ease;">
        <!-- Modal Header -->
        <div style="padding: 1.25rem 1.5rem; background: #0f172a; color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0; color: #ffffff; display: flex; align-items: center; gap: 0.4rem;">
                    <?= icon('calendar', '', 18) ?> <span>Đăng Ký Ngày Có Thể Đi Làm</span>
                </h3>
                <p style="font-size: 0.82rem; color: #94a3b8; margin: 0.2rem 0 0 0;">
                    Chọn các ngày bạn có thể nhận ca để Nhà tuyển dụng chủ động sắp xếp
                </p>
            </div>
            <button type="button" id="btnCloseApplyModal" style="background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Modal Body -->
        <form action="<?= url('/seeker/apply/' . $job['job_post_id']) ?>" method="POST" style="padding: 1.5rem;">
            <!-- Lịch yêu cầu của công ty -->
            <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
                <div style="font-size: 0.82rem; color: #0f766e; font-weight: 700;">
                    Yêu cầu ca làm từ <?= htmlspecialchars($job['employer_name']) ?>:
                </div>
                <div style="font-size: 0.92rem; font-weight: 700; color: #115e59; margin-top: 0.35rem; display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
                    <span style="display: inline-flex; align-items: center; gap: 0.3rem;">
                        <?= icon('calendar', '', 15) ?> <span><?= htmlspecialchars($job['working_days'] ?? 'Linh hoạt') ?></span>
                    </span>
                    <span style="color: #99f6e4;">•</span>
                    <div style="display: inline-flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                        <?= renderWorkShiftBadges($job['work_shift'] ?? 'Hành chính') ?>
                    </div>
                </div>
            </div>

            <!-- 1. Nút chọn nhanh mẫu -->
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.4rem;">
                    1. Chọn nhanh theo mẫu:
                </label>
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <button type="button" class="btn-candidate-preset" data-val="<?= htmlspecialchars($job['working_days'] ?? 'Theo đúng lịch yêu cầu của công ty') ?>" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #0d9488; background: #f0fdfa; color: #0f766e; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <?= icon('check', '', 13) ?> <span>Làm theo toàn bộ lịch công ty</span>
                    </button>
                    <button type="button" class="btn-candidate-preset" data-val="Thứ 2, Thứ 4, Thứ 6" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600;">
                        Thứ 2 - 4 - 6
                    </button>
                    <button type="button" class="btn-candidate-preset" data-val="Thứ 3, Thứ 5, Thứ 7" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600;">
                        Thứ 3 - 5 - 7
                    </button>
                    <button type="button" class="btn-candidate-preset" data-val="Thứ 7 & Chủ Nhật (Cuối tuần)" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600;">
                        Cuối tuần (T7, CN)
                    </button>
                </div>
            </div>

            <!-- 2. Chọn thứ trong tuần -->
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.4rem;">
                    2. Hoặc bấm chọn các thứ bạn rảnh:
                </label>
                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                    <button type="button" class="btn-cand-day" data-day="Thứ 2" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600;">Thứ 2</button>
                    <button type="button" class="btn-cand-day" data-day="Thứ 3" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600;">Thứ 3</button>
                    <button type="button" class="btn-cand-day" data-day="Thứ 4" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600;">Thứ 4</button>
                    <button type="button" class="btn-cand-day" data-day="Thứ 5" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600;">Thứ 5</button>
                    <button type="button" class="btn-cand-day" data-day="Thứ 6" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600;">Thứ 6</button>
                    <button type="button" class="btn-cand-day" data-day="Thứ 7" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600;">Thứ 7</button>
                    <button type="button" class="btn-cand-day" data-day="Chủ Nhật" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600;">Chủ Nhật</button>
                </div>
            </div>

            <!-- 3. Lịch chi tiết 30 ngày tới -->
            <div style="margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin: 0;">
                        3. Hoặc chọn các ngày cụ thể trên lịch:
                    </label>
                    <button type="button" id="btnClearCandDates" style="font-size: 0.75rem; color: #b91c1c; background: none; border: none; cursor: pointer; text-decoration: underline;">Xóa ngày</button>
                </div>
                <div id="cand_calendar_grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(62px, 1fr)); gap: 0.3rem; max-height: 130px; overflow-y: auto; padding: 0.4rem; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px;"></div>
            </div>

            <!-- Mục ngày có thể đi làm được ghi nhận -->
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" style="font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                    <?= icon('calendar', '', 16) ?> <span>Ngày bạn đăng ký có thể đi làm *</span>
                </label>
                <input type="text" name="available_work_dates" id="available_work_dates_input" value="<?= htmlspecialchars($job['working_days'] ?? 'Linh hoạt theo ca') ?>" class="form-control" required style="font-weight: 700; color: #0d9488; background: #f0fdfa; border-color: #99f6e4;">
            </div>

            <!-- Nút gửi -->
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" id="btnCancelApplyModal" class="btn btn-outline">Hủy Bỏ</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <?= icon('check', '', 14) ?> <span>Xác Nhận & Nộp Đơn Ứng Tuyển</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('applyDateModal');
    const btnOpen = document.getElementById('btnOpenApplyModal');
    const btnClose = document.getElementById('btnCloseApplyModal');
    const btnCancel = document.getElementById('btnCancelApplyModal');
    const datesInput = document.getElementById('available_work_dates_input');
    const candGrid = document.getElementById('cand_calendar_grid');
    const btnClearDates = document.getElementById('btnClearCandDates');

    let candSelectedDays = [];
    let candSelectedDates = [];

    if (btnOpen) {
        btnOpen.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.display = 'flex';
        });
    }

    function closeModal() {
        if (modal) modal.style.display = 'none';
    }

    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);

    // Click outside to close
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

    // Render 30 days in candidate modal
    if (candGrid) {
        candGrid.innerHTML = '';
        const today = new Date();
        const daysOfWeek = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

        for (let i = 0; i < 30; i++) {
            const d = new Date();
            d.setDate(today.getDate() + i);
            const dateStr = d.getDate().toString().padStart(2, '0') + '/' + (d.getMonth() + 1).toString().padStart(2, '0');
            const dayName = daysOfWeek[d.getDay()];

            const cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'cand-date-cell';
            cell.setAttribute('data-date', dateStr);
            cell.style.cssText = 'padding: 0.25rem 0.15rem; border-radius: 5px; border: 1px solid #cbd5e1; background: #ffffff; text-align: center; cursor: pointer; font-size: 0.72rem;';
            cell.innerHTML = `<div style="font-weight: 700; color: #0f172a;">${dateStr}</div><div style="font-size: 0.65rem; color: #64748b;">${dayName}</div>`;

            cell.addEventListener('click', function(e) {
                e.preventDefault();
                const dVal = this.getAttribute('data-date');
                if (candSelectedDates.includes(dVal)) {
                    candSelectedDates = candSelectedDates.filter(x => x !== dVal);
                    this.style.background = '#ffffff';
                    this.style.borderColor = '#cbd5e1';
                } else {
                    candSelectedDates.push(dVal);
                    this.style.background = '#0d9488';
                    this.style.borderColor = '#0d9488';
                    this.querySelector('div').style.color = '#ffffff';
                    this.querySelector('div:last-child').style.color = '#e6fffa';
                }
                updateCandidateDatesInput();
            });

            candGrid.appendChild(cell);
        }
    }

    function updateCandidateDatesInput() {
        let parts = [];
        if (candSelectedDays.length > 0) {
            parts.push(candSelectedDays.join(', '));
        }
        if (candSelectedDates.length > 0) {
            parts.push('Ngày: ' + candSelectedDates.join(', '));
        }
        if (parts.length > 0) {
            datesInput.value = parts.join(' | ');
        }
    }

    // Candidate weekday buttons
    document.querySelectorAll('.btn-cand-day').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const day = this.getAttribute('data-day');
            if (candSelectedDays.includes(day)) {
                candSelectedDays = candSelectedDays.filter(d => d !== day);
                this.style.background = '#ffffff';
                this.style.borderColor = '#cbd5e1';
                this.style.color = '#0f172a';
            } else {
                candSelectedDays.push(day);
                this.style.background = '#0d9488';
                this.style.borderColor = '#0d9488';
                this.style.color = '#ffffff';
            }
            updateCandidateDatesInput();
        });
    });

    // Candidate preset buttons
    document.querySelectorAll('.btn-candidate-preset').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            datesInput.value = this.getAttribute('data-val');
            candSelectedDays = [];
            candSelectedDates = [];
            document.querySelectorAll('.btn-cand-day').forEach(b => {
                b.style.background = '#ffffff';
                b.style.borderColor = '#cbd5e1';
                b.style.color = '#0f172a';
            });
            document.querySelectorAll('.cand-date-cell').forEach(c => {
                c.style.background = '#ffffff';
                c.style.borderColor = '#cbd5e1';
                c.querySelector('div').style.color = '#0f172a';
                c.querySelector('div:last-child').style.color = '#64748b';
            });
        });
    });

    if (btnClearDates) {
        btnClearDates.addEventListener('click', function(e) {
            e.preventDefault();
            candSelectedDates = [];
            document.querySelectorAll('.cand-date-cell').forEach(c => {
                c.style.background = '#ffffff';
                c.style.borderColor = '#cbd5e1';
                c.querySelector('div').style.color = '#0f172a';
                c.querySelector('div:last-child').style.color = '#64748b';
            });
            updateCandidateDatesInput();
        });
    }
});
</script>

<?php 
$content = ob_get_clean(); 
if (in_array($_SESSION['user']['role_code'] ?? '', ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
    require_once __DIR__ . '/../layouts/admin_layout.php';
} else {
    require_once __DIR__ . '/../layouts/main_layout.php';
}
?>
