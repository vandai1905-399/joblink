<?php
$extraCss = ['css/jobs/job_detail.css', 'css/jobs/ai_matching.css'];
$extraJs = ['js/jobs/jobs.js', 'js/jobs/ai_recommendation.js'];
ob_start();
?>

<?php
$currentUserRole = $_SESSION['user']['role_code'] ?? '';
$isAdminUser = in_array($currentUserRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
$isExpired = !empty($job['application_deadline']) && (strtotime($job['application_deadline']) < strtotime(date('Y-m-d')));
?>

<div class="container" style="padding-top: 1rem; padding-bottom: 3rem;">
    <?php if (!empty($aiFitAnalysis)): ?>
    <!-- DIALOG / MODAL ĐÁNH GIÁ ĐỘ TƯƠNG THÍCH AI TOÀN DIỆN -->
    <div id="aiCompatibilityModal" class="ai-comp-modal-overlay" style="display: none;" onclick="handleAiModalBackdropClick(event)">
        <div class="ai-comp-modal-container" role="dialog" aria-modal="true" aria-labelledby="aiCompModalTitle">
            <!-- Modal header -->
            <div class="ai-comp-modal-header">
                <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #0d9488 0%, #059669 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);">
                        <?= icon('sparkle', '', 20) ?>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.15rem;">
                            <span class="ai-badge-header">AI Matching insights</span>
                            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">• Phân tích 4 tiêu chuẩn</span>
                        </div>
                        <h3 id="aiCompModalTitle" style="font-size: 1.18rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3;">
                            Độ tương thích của bạn với vị trí này
                        </h3>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <div style="background: <?= $aiFitAnalysis['color'] ?>15; border: 1.5px solid <?= $aiFitAnalysis['color'] ?>44; padding: 0.35rem 0.85rem; border-radius: 12px; display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 1.55rem; font-weight: 900; color: <?= $aiFitAnalysis['color'] ?>; line-height: 1;">
                            <?= $aiFitAnalysis['score'] ?>%
                        </span>
                        <span style="font-size: 0.82rem; font-weight: 800; color: <?= $aiFitAnalysis['color'] ?>; text-transform: uppercase; letter-spacing: 0.3px;">
                            <?= $aiFitAnalysis['label'] ?>
                        </span>
                    </div>
                    <button type="button" class="ai-comp-modal-close" onclick="closeAiCompatibilityDialog();" title="Đóng">&times;</button>
                </div>
            </div>

            <!-- Modal body (Scrollable) -->
            <div class="ai-comp-modal-body">

        <?php
            $breakdown = $aiFitAnalysis['breakdown'] ?? [];
            $empCriteria = $aiFitAnalysis['employer_criteria'] ?? [];
            $matchedSkills = $aiFitAnalysis['matched_skills'] ?? [];
            $missingSkills = $aiFitAnalysis['missing_skills'] ?? [];
            $reasons = $aiFitAnalysis['reasons'] ?? [];

            // 1. Kỹ năng & Chức danh
            $skillsScore = intval($breakdown['skills'] ?? 70);
            $skillsColor = $skillsScore >= 85 ? '#0d9488' : ($skillsScore >= 70 ? '#0284c7' : ($skillsScore >= 50 ? '#d97706' : '#ef4444'));
            $skillsBg = $skillsScore >= 85 ? '#ecfdf5' : ($skillsScore >= 70 ? '#f0f9ff' : ($skillsScore >= 50 ? '#fffbeb' : '#fef2f2'));
            $rawDesired = trim($seeker['desired_position'] ?? '');
            $rawSkills = trim($seeker['skills'] ?? '');
            $seekerPositionText = !empty($rawDesired) ? $rawDesired : (!empty($rawSkills) ? 'Kỹ năng: ' . mb_substr($rawSkills, 0, 32) : 'Người tìm việc');

            // 2. Địa điểm làm việc
            $locScore = intval($breakdown['location'] ?? 95);
            $locColor = $locScore >= 85 ? '#0d9488' : ($locScore >= 70 ? '#0284c7' : '#d97706');
            $locBg = $locScore >= 85 ? '#ecfdf5' : ($locScore >= 70 ? '#f0f9ff' : '#fffbeb');
            $jobAddress = trim($job['workplace_address'] ?? ($job['employer_address'] ?? 'Thủ Dầu Một, Bình Dương'));
            $seekerAddress = trim($seeker['address'] ?? ($seeker['preferred_location'] ?? 'Bình Dương'));

            // 3. Lương & Hình thức
            $empScore = intval($breakdown['employment'] ?? 90);
            $empColor = $empScore >= 85 ? '#0d9488' : ($empScore >= 70 ? '#0284c7' : '#d97706');
            $empBg = $empScore >= 85 ? '#ecfdf5' : ($empScore >= 70 ? '#f0f9ff' : '#fffbeb');
            $jobSalaryStr = formatSalary($job['salary_amount'] ?? 0, $job['employment_type'] ?? 'full_time');
            $jobTypeStr = ($job['employment_type'] ?? 'full_time') === 'full_time' ? 'Toàn thời gian' : 'Bán thời gian / Ca';
            $seekerSalaryStr = !empty($seeker['expected_salary']) ? formatSalary($seeker['expected_salary'], $seeker['preferred_employment_type'] ?? 'full_time') : 'Thỏa thuận theo năng lực';
            $seekerTypeStr = ($seeker['preferred_employment_type'] ?? 'full_time') === 'full_time' ? 'Toàn thời gian' : 'Linh hoạt theo ca';

            // 4. Kinh nghiệm & Trình độ
            $expScore = intval($breakdown['experience'] ?? 100);
            $expCrit = $empCriteria['experience'] ?? [];
            $eduCrit = $empCriteria['education'] ?? [];
            $expColor = $expScore >= 85 ? '#0d9488' : ($expScore >= 70 ? '#0284c7' : '#d97706');
            $expBg = $expScore >= 85 ? '#ecfdf5' : ($expScore >= 70 ? '#f0f9ff' : '#fffbeb');
            $expReqStr = $expCrit['required'] ?? 'Không yêu cầu kinh nghiệm';
            $eduReqStr = $eduCrit['required'] ?? 'Không yêu cầu bằng cấp';
            $expYears = intval($seeker['experience_years'] ?? 0);
            $seekerExpStr = $expYears > 0 ? "{$expYears} năm kinh nghiệm" : "Sẵn sàng học hỏi";
            $rawEdu = trim($seeker['education_level'] ?? '');
            $seekerEduStr = (!empty($rawEdu) && $rawEdu !== 'none') ? $rawEdu : 'Lao động phổ thông';
        ?>

        <!-- 4 Khối tiêu chí đánh giá chuẩn hóa -->
        <div class="ai-breakdown-grid">
            <!-- 1. KỸ NĂNG & CHỨC DANH -->
            <div class="ai-breakdown-card">
                <div class="ai-card-top">
                    <div class="ai-card-title">
                        <span>🎯</span> <span>Kỹ năng & Chức danh</span>
                    </div>
                    <span class="ai-card-badge" style="background: <?= $skillsBg ?>; color: <?= $skillsColor ?>; border-color: <?= $skillsColor ?>44;">
                        <?= $skillsScore ?>%
                    </span>
                </div>
                <div class="ai-card-progress">
                    <div class="ai-card-bar" style="width: <?= $skillsScore ?>%; background: <?= $skillsColor ?>;"></div>
                </div>
                <div class="ai-card-details">
                    <div class="ai-detail-row">
                        <span class="ai-detail-label">NTD yêu cầu:</span>
                        <span class="ai-detail-value" title="<?= htmlspecialchars($job['job_title'] ?? '') ?>">
                            <?= htmlspecialchars($job['job_title'] ?? 'Vị trí tuyển dụng') ?>
                        </span>
                    </div>
                    <div class="ai-detail-row">
                        <span class="ai-detail-label">Hồ sơ của bạn:</span>
                        <span class="ai-detail-value" title="<?= htmlspecialchars($seekerPositionText) ?>">
                            <?= htmlspecialchars($seekerPositionText) ?>
                        </span>
                    </div>
                    <div class="ai-detail-status" style="color: <?= $skillsColor ?>;">
                        <?= !empty($matchedSkills) ? ('✓ Kỹ năng trùng khớp: ' . htmlspecialchars(implode(', ', $matchedSkills))) : '✓ Chức danh & định hướng làm việc phù hợp' ?>
                    </div>
                </div>
            </div>

            <!-- 2. ĐỊA ĐIỂM LÀM VIỆC -->
            <div class="ai-breakdown-card">
                <div class="ai-card-top">
                    <div class="ai-card-title">
                        <span>📍</span> <span>Địa điểm làm việc</span>
                    </div>
                    <span class="ai-card-badge" style="background: <?= $locBg ?>; color: <?= $locColor ?>; border-color: <?= $locColor ?>44;">
                        <?= $locScore ?>%
                    </span>
                </div>
                <div class="ai-card-progress">
                    <div class="ai-card-bar" style="width: <?= $locScore ?>%; background: <?= $locColor ?>;"></div>
                </div>
                <div class="ai-card-details">
                    <div class="ai-detail-row">
                        <span class="ai-detail-label">Nơi làm việc:</span>
                        <span class="ai-detail-value" title="<?= htmlspecialchars($jobAddress) ?>">
                            <?= htmlspecialchars(mb_substr($jobAddress, 0, 38)) ?><?= mb_strlen($jobAddress) > 38 ? '...' : '' ?>
                        </span>
                    </div>
                    <div class="ai-detail-row">
                        <span class="ai-detail-label">Khu vực của bạn:</span>
                        <span class="ai-detail-value" title="<?= htmlspecialchars($seekerAddress) ?>">
                            <?= htmlspecialchars(mb_substr($seekerAddress, 0, 30)) ?>
                            <?= !empty($distanceInfo['distance_text']) ? ' (Cách ' . $distanceInfo['distance_text'] . ')' : '' ?>
                        </span>
                    </div>
                    <div class="ai-detail-status" style="color: <?= $locColor ?>;">
                        <?= $locScore >= 80 ? '✓ Vị trí thuận tiện đi lại trong khu vực' : '• Khoảng cách di chuyển tương đối' ?>
                    </div>
                </div>
            </div>

            <!-- 3. LƯƠNG & HÌNH THỨC -->
            <div class="ai-breakdown-card">
                <div class="ai-card-top">
                    <div class="ai-card-title">
                        <span>💰</span> <span>Lương & Hình thức</span>
                    </div>
                    <span class="ai-card-badge" style="background: <?= $empBg ?>; color: <?= $empColor ?>; border-color: <?= $empColor ?>44;">
                        <?= $empScore ?>%
                    </span>
                </div>
                <div class="ai-card-progress">
                    <div class="ai-card-bar" style="width: <?= $empScore ?>%; background: <?= $empColor ?>;"></div>
                </div>
                <div class="ai-card-details">
                    <div class="ai-detail-row">
                        <span class="ai-detail-label">Mức lương NTD:</span>
                        <span class="ai-detail-value">
                            <strong><?= $jobSalaryStr ?></strong> (<?= $jobTypeStr ?>)
                        </span>
                    </div>
                    <div class="ai-detail-row">
                        <span class="ai-detail-label">Kỳ vọng của bạn:</span>
                        <span class="ai-detail-value">
                            <?= $seekerSalaryStr ?> (<?= $seekerTypeStr ?>)
                        </span>
                    </div>
                    <div class="ai-detail-status" style="color: <?= $empColor ?>;">
                        ✓ Đáp ứng mức thu nhập & thời gian làm việc
                    </div>
                </div>
            </div>

            <!-- 4. KINH NGHIỆM & TRÌNH ĐỘ -->
            <div class="ai-breakdown-card">
                <div class="ai-card-top">
                    <div class="ai-card-title">
                        <span>💼</span> <span>Kinh nghiệm & Trình độ</span>
                    </div>
                    <span class="ai-card-badge" style="background: <?= $expBg ?>; color: <?= $expColor ?>; border-color: <?= $expColor ?>44;">
                        <?= $expScore ?>%
                    </span>
                </div>
                <div class="ai-card-progress">
                    <div class="ai-card-bar" style="width: <?= $expScore ?>%; background: <?= $expColor ?>;"></div>
                </div>
                <div class="ai-card-details">
                    <div class="ai-detail-row">
                        <span class="ai-detail-label">NTD yêu cầu:</span>
                        <span class="ai-detail-value">
                            <?= htmlspecialchars($expReqStr) ?><?= ($eduReqStr !== 'Không yêu cầu bằng cấp') ? ' • ' . htmlspecialchars($eduReqStr) : '' ?>
                        </span>
                    </div>
                    <div class="ai-detail-row">
                        <span class="ai-detail-label">Hồ sơ của bạn:</span>
                        <span class="ai-detail-value">
                            <?= htmlspecialchars($seekerExpStr) ?><?= ($seekerEduStr !== 'Lao động phổ thông') ? ' • ' . htmlspecialchars($seekerEduStr) : '' ?>
                        </span>
                    </div>
                    <div class="ai-detail-status" style="color: <?= $expColor ?>;">
                        <?= $expScore >= 80 ? '✓ Đạt tiêu chuẩn thâm niên & bằng cấp' : '✓ Phù hợp để ứng tuyển & đào tạo' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Khối dưới: Đối soát kỹ năng & Gợi ý phỏng vấn từ AI -->
        <div class="ai-bottom-section">
            <!-- Cột kỹ năng -->
            <div class="ai-skills-panel">
                <div class="ai-skills-group">
                    <div class="ai-skills-label matched">
                        <?= icon('check_circle', '', 14) ?> <span>Kỹ năng trùng khớp (<?= count($aiFitAnalysis['matched_skills'] ?? []) ?>):</span>
                    </div>
                    <div class="ai-skills-tags">
                        <?php if (!empty($aiFitAnalysis['matched_skills'])): ?>
                            <?php foreach ($aiFitAnalysis['matched_skills'] as $sk): ?>
                                <span class="ai-skill-tag matched" style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.76rem; padding: 0.2rem 0.55rem; border-radius: 6px;">
                                    <?= icon('check', '', 11) ?> <span><?= htmlspecialchars($sk) ?></span>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span style="font-size: 0.78rem; color: #64748b; font-style: italic;">Đang đối soát theo mô tả công việc</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($aiFitAnalysis['missing_skills'])): ?>
                    <div class="ai-skills-group" style="margin-top: 0.75rem;">
                        <div class="ai-skills-label missing">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <span>Kỹ năng NTD ưu tiên thêm (<?= count($aiFitAnalysis['missing_skills']) ?>):</span>
                        </div>
                        <div class="ai-skills-tags">
                            <?php foreach ($aiFitAnalysis['missing_skills'] as $msk): ?>
                                <span class="ai-skill-tag missing" style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.76rem; padding: 0.2rem 0.55rem; border-radius: 6px;">
                                    + <?= htmlspecialchars($msk) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-top: auto; padding-top: 0.65rem; font-size: 0.74rem; color: #64748b; display: flex; align-items: center; gap: 0.35rem;">
                    <span>💡</span> <span>Hệ thống tự động so khớp từ kỹ năng hồ sơ và bản tin tuyển dụng.</span>
                </div>
            </div>

            <!-- Cột lời khuyên phỏng vấn từ AI -->
            <div class="ai-advice-panel">
                <div class="ai-advice-header">
                    <span class="ai-header-icon"><?= icon('sparkle', '', 14) ?></span>
                    <span>Gợi ý phỏng vấn từ AI Matching:</span>
                </div>
                <div class="ai-advice-list">
                    <?php if (!empty($aiFitAnalysis['advice'])): ?>
                        <?php foreach (array_slice($aiFitAnalysis['advice'] ?? [], 0, 3) as $adv): ?>
                            <?php
                                // Parse markdown **bold** thành <strong> với màu teal thương hiệu đồng nhất
                                $parsedAdv = preg_replace('/\*\*(.*?)\*\*/', '<strong style="color: #0d9488; font-weight: 700;">$1</strong>', htmlspecialchars($adv, ENT_NOQUOTES, 'UTF-8'));
                            ?>
                            <div class="ai-advice-item">
                                <span class="ai-advice-bullet">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                                <div class="ai-advice-text"><?= $parsedAdv ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="ai-advice-item">
                            <span class="ai-advice-bullet">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            <div class="ai-advice-text">Hãy chuẩn bị sẵn sàng giới thiệu về kinh nghiệm thực tế và tinh thần trách nhiệm trong công việc.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div> <!-- Đóng .ai-comp-modal-body -->

    <!-- Modal footer -->
    <div class="ai-comp-modal-footer">
        <div style="font-size: 0.78rem; color: #64748b; display: flex; align-items: center; gap: 0.4rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            <span>Phân tích độc quyền theo thuật toán JobLink AI Matching.</span>
        </div>
        <button type="button" class="btn btn-primary" style="padding: 0.45rem 1.4rem; font-size: 0.85rem; font-weight: 700; border-radius: 8px;" onclick="closeAiCompatibilityDialog();">
            Đã hiểu
        </button>
    </div>
</div> <!-- Đóng .ai-comp-modal-container -->
</div> <!-- Đóng #aiCompatibilityModal -->
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
    <!-- Chi tiết bài đăng -->
    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="display: flex; gap: 1.25rem; align-items: center; margin-bottom: 1.5rem; justify-content: space-between; flex-wrap: wrap;">
                <div style="display: flex; gap: 1.25rem; align-items: center;">
                    <img src="<?= htmlspecialchars(getAvatarUrl($job['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" style="width: 72px; height: 72px; border-radius: var(--radius-sm); object-fit: cover; border: 1px solid var(--slate-200);" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 0.25rem;">
                            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin: 0;">
                                <?= htmlspecialchars(formatJobTitle($job['job_title'])) ?>
                            </h1>
                            <?php if (!empty($aiFitAnalysis)): ?>
                                <button type="button" class="btn-ai-compatibility" onclick="openAiCompatibilityDialog();" title="Độ tương thích AI: <?= $aiFitAnalysis['score'] ?>% (Bấm xem đánh giá chi tiết)" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.92rem; font-weight: 800; cursor: pointer; border: none; background: #0d9488; color: #ffffff; box-shadow: 0 2px 8px rgba(13, 148, 136, 0.25); text-decoration: none; line-height: 1.2; letter-spacing: 0.3px; transition: all 0.2s ease;">
                                    <?= icon('sparkle', '', 14) ?>
                                    <span><?= $aiFitAnalysis['score'] ?>% Phù hợp</span>
                                </button>
                            <?php endif; ?>
                            <?php if ($isExpired): ?>
                                <span class="badge" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-size: 0.75rem; font-weight: 800; padding: 3px 8px; border-radius: 6px; letter-spacing: 0.3px;">
                                    Đã hết hạn nộp hồ sơ
                                </span>
                            <?php endif; ?>
                        </div>
                        <p style="font-size: 1.05rem; color: var(--primary); font-weight: 600;">
                            <a href="<?= url('/employers/' . $job['employer_id']) ?>" data-title="DN: <?= htmlspecialchars($job['employer_name']) ?>"><?= htmlspecialchars($job['employer_name']) ?></a>
                        </p>
                    </div>
                </div>
            </div>

            <?php if (!empty($aiFitAnalysis)): ?>
                <!-- Thanh tóm tắt độ tương thích AI nổi bật ngay trên các thông số chính -->
                <div class="ai-match-quick-bar" onclick="openAiCompatibilityDialog();" style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; background: linear-gradient(135deg, #f0fdfa 0%, #ecfdf5 100%); border: 1px solid #99f6e4; border-radius: 10px; padding: 0.65rem 1rem; margin-bottom: 1.35rem; cursor: pointer; transition: all 0.2s ease;" title="Bấm để xem phân tích chi tiết độ tương thích AI">
                    <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                        <span style="font-weight: 800; color: #0d9488; font-size: 0.92rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <?= icon('sparkle', '', 15) ?>
                            <span>Độ tương thích hồ sơ: <strong style="font-size: 1.05rem;"><?= $aiFitAnalysis['score'] ?>%</strong></span>
                            <span style="font-size: 0.75rem; font-weight: 700; padding: 1px 7px; border-radius: 999px; background: #ccfbf1; color: #0f766e;">
                                <?= $aiFitAnalysis['label'] ?? 'Rất phù hợp' ?>
                            </span>
                        </span>
                        <span style="color: #cbd5e1; display: inline-block;">|</span>
                        <span style="font-size: 0.82rem; color: #475569;">
                            <?= !empty($aiFitAnalysis['matched_skills']) ? ('✓ Trùng khớp: ' . htmlspecialchars(implode(', ', array_slice($aiFitAnalysis['matched_skills'], 0, 3)))) : '✓ Chức danh & địa điểm làm việc rất phù hợp' ?>
                        </span>
                    </div>
                    <span style="color: #0d9488; font-weight: 700; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.25rem; white-space: nowrap;">
                        <span>Xem đánh giá AI</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </div>
            <?php endif; ?>

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
                    <div class="stat-card" style="<?= $isExpired ? 'background: #fff1f2; border: 1px solid #fecdd3;' : '' ?>">
                        <div class="stat-title" style="<?= $isExpired ? 'color: #e11d48; font-weight: 700;' : '' ?>">Hạn nộp hồ sơ</div>
                        <div style="font-size: 1rem; font-weight: 700; color: <?= $isExpired ? '#dc2626' : 'var(--warning)' ?>; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                            <span><?= date('d/m/Y', strtotime($job['application_deadline'])) ?></span>
                            <?php if ($isExpired): ?>
                                <span class="badge" style="background: #fee2e2; color: #dc2626; font-size: 0.68rem; font-weight: 800; padding: 2px 6px; border-radius: 4px;">Hết hạn</span>
                            <?php endif; ?>
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
                        <div style="margin-top: 0.75rem; padding: 0.5rem 0.85rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 0.84rem; color: #166534; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <?= icon('map_pin', '', 14) ?>
                                <span>Cách bạn khoảng <strong><?= $distanceInfo['distance_text'] ?></strong></span>
                            </div>
                            <a href="<?= getGoogleMapsUrl($job['workplace_address'] ?? '', '', $distanceInfo['from_location']) ?>" target="_blank" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.65rem; font-size: 0.76rem; font-weight: 700; border-radius: 6px;">
                                Chỉ đường
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
                        <?= icon('user_check', '', 18) ?> <span>Tiêu chuẩn tuyển dụng</span>
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
                                <div style="font-size: 0.82rem; font-weight: 700; color: #475569; text-transform: uppercase;">Trạng thái tin tuyển dụng:</div>
                                <div style="margin-top: 0.25rem;">
                                    <?php if (($job['post_status'] ?? '') === 'approved'): ?>
                                        <span class="badge badge-success" style="font-size: 0.85rem;"><?= icon('check', '', 14) ?> Đã phê duyệt</span>
                                    <?php elseif (($job['post_status'] ?? '') === 'pending'): ?>
                                        <span class="badge badge-warning" style="font-size: 0.85rem;"><?= icon('clock', '', 14) ?> Đang chờ kiểm duyệt</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger" style="font-size: 0.85rem;"><?= icon('x_circle', '', 14) ?> Từ chối / đã ẩn</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                <?php if (($job['post_status'] ?? '') === 'pending'): ?>
                                    <form action="<?= url('/admin/job-posts/approve/' . $job['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success" style="padding: 0.55rem 1.25rem; font-weight: 700;">
                                            <?= icon('check', '', 14) ?> Duyệt tin này
                                        </button>
                                    </form>
                                    <form action="<?= url('/admin/job-posts/approve/' . $job['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="button" class="btn btn-danger" style="padding: 0.55rem 1.25rem; font-weight: 700;" data-reject-prompt="Lý do từ chối tin tuyển dụng">
                                            <?= icon('x_circle', '', 14) ?> Từ chối tin
                                        </button>
                                    </form>
                                <?php elseif (($job['post_status'] ?? '') === 'approved'): ?>
                                    <form action="<?= url('/admin/job-posts/approve/' . $job['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="pending">
                                        <button type="button" class="btn btn-warning" style="padding: 0.55rem 1.25rem; font-weight: 700;" data-reject-prompt="Lý do hủy duyệt tin tuyển dụng">
                                            Hủy duyệt
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= url('/admin/job-posts/approve/' . $job['job_post_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success" style="padding: 0.55rem 1.25rem; font-weight: 700;">
                                            Duyệt lại
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <a href="<?= url('/admin/job-posts') ?>" class="btn btn-outline workspace-nav-link" data-title="Duyệt tin tuyển dụng" style="padding: 0.55rem 1rem; font-weight: 600;">
                                    Danh sách tin
                                </a>
                            </div>
                        </div>
                    <?php elseif (isset($_SESSION['user']) && $_SESSION['user']['role_code'] === 'JOB_SEEKER'): ?>
                        <?php if (!empty($hasApplied)): ?>
                            <!-- Đã ứng tuyển: Vô hiệu hóa nút -->
                            <button type="button" class="btn btn-success btn-job-applied" disabled style="padding: 0.8rem 2rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.4rem; opacity: 0.85; cursor: not-allowed;">
                                <?= icon('check_circle', '', 18) ?> <span>Đã ứng tuyển</span>
                            </button>
                        <?php elseif ($isExpired): ?>
                            <!-- Đã hết hạn nộp hồ sơ: Vô hiệu hóa nút ứng tuyển -->
                            <button type="button" class="btn btn-job-expired" disabled style="padding: 0.8rem 2rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.45rem; cursor: not-allowed; opacity: 0.85; background: #64748b; border: 1px solid #64748b; color: #ffffff; border-radius: var(--radius-sm, 8px); font-weight: 700;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                                <span>Đã hết hạn nộp hồ sơ</span>
                            </button>
                        <?php elseif ($job['employment_type'] === 'full_time'): ?>
                            <!-- Toàn thời gian: Ứng tuyển trực tiếp trọn gói cả tháng -->
                            <form action="<?= url('/seeker/apply/' . $job['job_post_id']) ?>" method="POST" style="display:inline;">
                                <input type="hidden" name="available_work_dates" value="Toàn thời gian (Cả tháng)">
                                <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                                    <?= icon('rocket', '', 18) ?> <span>Nộp đơn ứng tuyển ngay</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Bán thời gian / Thời vụ / Xoay ca: Mở modal chọn ngày có thể đi làm -->
                            <button type="button" id="btnOpenApplyModal" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                                <?= icon('rocket', '', 18) ?> <span>Đăng ký ngày làm & ứng tuyển</span>
                            </button>
                        <?php endif; ?>

                        <button class="btn btn-outline btn-save-job" data-job-id="<?= $job['job_post_id'] ?>" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                            <?= icon('saved', '', 16) ?> <span>Lưu việc làm</span>
                        </button>
                    <?php elseif (!isset($_SESSION['user'])): ?>
                        <?php if ($isExpired): ?>
                            <button type="button" class="btn btn-job-expired" disabled style="padding: 0.8rem 2rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.45rem; cursor: not-allowed; opacity: 0.85; background: #64748b; border: 1px solid #64748b; color: #ffffff; border-radius: var(--radius-sm, 8px); font-weight: 700;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                                <span>Đã hết hạn nộp hồ sơ</span>
                            </button>
                        <?php else: ?>
                            <a href="<?= url('/auth/login') ?>" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem;">
                                Đăng nhập để ứng tuyển
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Đánh giá về nhà tuyển dụng này -->
            <div class="card">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">
                    Đánh giá từ người lao động đã làm việc (<?= count($employerReviews ?? []) ?>)
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
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem;">Thông tin doanh nghiệp</h3>
                <p style="font-weight: 700; color: var(--dark); font-size: 1.05rem;">
                    <a href="<?= url('/employers/' . $job['employer_id']) ?>" data-title="DN: <?= htmlspecialchars($job['employer_name']) ?>" style="color: var(--dark); text-decoration: underline;" title="Xem hồ sơ doanh nghiệp">
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
                        <?= icon('globe', '', 14) ?> <a href="<?= htmlspecialchars($job['website_url']) ?>" target="_blank" rel="noopener noreferrer" style="color: var(--primary);"><?= htmlspecialchars($job['website_url']) ?></a>
                    </p>
                <?php endif; ?>
                <a href="<?= url('/employers/' . $job['employer_id']) ?>" data-title="DN: <?= htmlspecialchars($job['employer_name']) ?>" class="btn btn-outline btn-sm" style="width: 100%; margin-top: 0.5rem;">Xem hồ sơ công ty</a>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL ĐĂNG KÝ NGÀY CÓ THỂ ĐI LÀM (CHO BÁN THỜI GIAN & THỜI VỤ / XOAY CA) -->
<!-- ========================================================================= -->
<?php if (!$isExpired): ?>
<div id="applyDateModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; backdrop-filter: blur(3px); justify-content: center; align-items: center; padding: 1rem;">
    <div style="background: #ffffff; width: 100%; max-width: 580px; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; animation: scaleIn 0.25s ease;">
        <!-- Modal header -->
        <div style="padding: 1.25rem 1.5rem; background: #0f172a; color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0; color: #ffffff; display: flex; align-items: center; gap: 0.4rem;">
                    <?= icon('calendar', '', 18) ?> <span>Đăng ký ngày có thể đi làm</span>
                </h3>
            </div>
            <button type="button" id="btnCloseApplyModal" style="background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Modal body -->
        <form action="<?= url('/seeker/apply/' . $job['job_post_id']) ?>" method="POST" style="padding: 1.5rem;">
            <!-- Lịch yêu cầu của công ty -->
            <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem;">
                <div style="font-size: 0.8rem; color: #0f766e; font-weight: 700;">
                    Lịch của công ty:
                </div>
                <div style="font-size: 0.88rem; font-weight: 700; color: #115e59; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
                    <span style="display: inline-flex; align-items: center; gap: 0.3rem;">
                        <?= icon('calendar', '', 14) ?> <span><?= htmlspecialchars($job['working_days'] ?? 'Linh hoạt') ?></span>
                    </span>
                    <span style="color: #99f6e4;">•</span>
                    <div style="display: inline-flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                        <?= renderWorkShiftBadges($job['work_shift'] ?? 'Hành chính') ?>
                    </div>
                </div>
            </div>

            <!-- 1. Nút chọn nhanh mẫu -->
            <div style="margin-bottom: 0.85rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">
                    Mẫu nhanh:
                </label>
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <button type="button" class="btn-candidate-preset" data-val="<?= htmlspecialchars($job['working_days'] ?? 'Theo đúng lịch yêu cầu của công ty') ?>" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #0d9488; background: #0d9488; color: #ffffff; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <?= icon('check', '', 13) ?> <span>Làm theo lịch công ty</span>
                    </button>
                    <button type="button" class="btn-candidate-preset" data-val="Thứ 2, Thứ 4, Thứ 6" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #475569; background: #475569; color: #ffffff; cursor: pointer; font-weight: 700;">
                        Thứ 2 - 4 - 6
                    </button>
                    <button type="button" class="btn-candidate-preset" data-val="Thứ 3, Thứ 5, Thứ 7" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #475569; background: #475569; color: #ffffff; cursor: pointer; font-weight: 700;">
                        Thứ 3 - 5 - 7
                    </button>
                    <button type="button" class="btn-candidate-preset" data-val="Thứ 7 & Chủ nhật (Cuối tuần)" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #475569; background: #475569; color: #ffffff; cursor: pointer; font-weight: 700;">
                        Cuối tuần (T7, CN)
                    </button>
                </div>
            </div>

            <!-- 2. Chọn thứ trong tuần -->
            <div style="margin-bottom: 0.85rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">
                    hoặc chọn theo thứ:
                </label>
                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                    <button type="button" class="btn-cand-day" data-day="Thứ 2" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #475569; background: #475569; color: #ffffff; cursor: pointer; font-weight: 700;">Thứ 2</button>
                    <button type="button" class="btn-cand-day" data-day="Thứ 3" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #475569; background: #475569; color: #ffffff; cursor: pointer; font-weight: 700;">Thứ 3</button>
                    <button type="button" class="btn-cand-day" data-day="Thứ 4" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #475569; background: #475569; color: #ffffff; cursor: pointer; font-weight: 700;">Thứ 4</button>
                    <button type="button" class="btn-cand-day" data-day="Thứ 5" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #475569; background: #475569; color: #ffffff; cursor: pointer; font-weight: 700;">Thứ 5</button>
                    <button type="button" class="btn-cand-day" data-day="Thứ 6" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #475569; background: #475569; color: #ffffff; cursor: pointer; font-weight: 700;">Thứ 6</button>
                    <button type="button" class="btn-cand-day" data-day="Thứ 7" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #475569; background: #475569; color: #ffffff; cursor: pointer; font-weight: 700;">Thứ 7</button>
                    <button type="button" class="btn-cand-day" data-day="Chủ nhật" style="padding: 0.3rem 0.6rem; font-size: 0.82rem; border-radius: 6px; border: 1px solid #475569; background: #475569; color: #ffffff; cursor: pointer; font-weight: 700;">Chủ nhật</button>
                </div>
            </div>

            <!-- 3. Lịch chi tiết 30 ngày tới -->
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin: 0;">
                        hoặc chọn theo ngày cụ thể:
                    </label>
                    <button type="button" id="btnClearCandDates" style="font-size: 0.75rem; color: #b91c1c; background: none; border: none; cursor: pointer; text-decoration: underline;">Xóa ngày</button>
                </div>
                <div id="cand_calendar_grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(62px, 1fr)); gap: 0.3rem; max-height: 130px; overflow-y: auto; padding: 0.4rem; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px;"></div>
            </div>

            <!-- Mục ngày có thể đi làm được ghi nhận -->
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label" style="font-weight: 700; font-size: 0.82rem; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                    <?= icon('calendar', '', 15) ?> <span>Lịch đăng ký:</span>
                </label>
                <input type="text" name="available_work_dates" id="available_work_dates_input" value="<?= htmlspecialchars($job['working_days'] ?? 'Linh hoạt theo ca') ?>" class="form-control" required style="font-weight: 700; color: #0d9488; background: #f0fdfa; border-color: #99f6e4;">
            </div>

            <!-- Nút gửi -->
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" id="btnCancelApplyModal" class="btn btn-outline">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <?= icon('check', '', 14) ?> <span>Xác nhận & nộp đơn ứng tuyển</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

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

// Dialog / Modal đánh giá Độ tương thích AI
function openAiCompatibilityDialog() {
    const modal = document.getElementById('aiCompatibilityModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeAiCompatibilityDialog() {
    const modal = document.getElementById('aiCompatibilityModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function handleAiModalBackdropClick(e) {
    const container = document.querySelector('.ai-comp-modal-container');
    if (container && !container.contains(e.target)) {
        closeAiCompatibilityDialog();
    }
}

window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAiCompatibilityDialog();
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
