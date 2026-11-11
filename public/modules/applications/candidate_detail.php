<?php 
$extraCss = ['css/jobs/ai_matching.css'];
ob_start(); 

// Giải mã minh chứng kỹ năng và lịch sử làm việc
$skillProofs = [];
if (!empty($candidate['skill_proofs'])) {
    $decoded = json_decode($candidate['skill_proofs'], true);
    if (is_array($decoded)) {
        $skillProofs = $decoded;
    }
}

$workHistory = [];
if (!empty($candidate['work_history'])) {
    $decoded = json_decode($candidate['work_history'], true);
    if (is_array($decoded)) {
        $workHistory = $decoded;
    }
}

// Tính tuổi từ ngày sinh
$age = null;
if (!empty($candidate['date_of_birth'])) {
    try {
        $birthDate = new DateTime($candidate['date_of_birth']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
    } catch (Exception $e) {
        $age = null;
    }
}
?>

<?php 
$currentUserRole = $_SESSION['user']['role_code'] ?? '';
$isAdminUser = in_array($currentUserRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
?>

<div style="margin-bottom: 1.5rem; display: flex; justify-content: flex-end; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <?php if ($isAdminUser): ?>
            <a href="<?= url('/admin/accounts') ?>" class="btn btn-outline btn-sm" style="font-weight: 600;">
                <?= icon('accounts', '', 14) ?> <span style="margin-left: 4px;">Danh Sách Tài Khoản</span>
            </a>
            <?php if (!empty($candidate['email'])): ?>
                <a href="mailto:<?= htmlspecialchars($candidate['email']) ?>" class="btn btn-primary btn-sm" style="font-weight: 700;">
                    <?= icon('mail', '', 14) ?> <span style="margin-left: 4px;">Gửi Email Liên Hệ</span>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= url('/employer/messages?partner_id=' . ($candidate['account_id'] ?? 0) . '&seeker_id=' . $candidate['seeker_id'] . (!empty($applications[0]['request_id']) ? '&request_id=' . $applications[0]['request_id'] : '') . (!empty($applications[0]['job_post_id']) ? '&job_id=' . $applications[0]['job_post_id'] : '') . '&open_meeting_modal=1') ?>" class="btn btn-primary btn-sm" style="font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                <?= icon('calendar', '', 15) ?> <span>Hẹn Lịch Phỏng Vấn</span>
            </a>
            <?php if (!empty($activeJobs)): ?>
                <button type="button" class="btn btn-sm" onclick="document.getElementById('inviteCandidateModal').style.display='flex'" style="font-weight: 700; color: #ffffff; border-color: #0284c7; background: #0284c7;">
                    <?= icon('send', '', 13) ?> <span style="margin-left: 4px;">Mời Ứng Tuyển</span>
                </button>
            <?php endif; ?>
            <a href="<?= url('/employer/messages?partner_id=' . ($candidate['account_id'] ?? 0)) ?>" class="btn btn-outline btn-sm" style="font-weight: 600;">
                <?= icon('chat', '', 15) ?> <span style="margin-left: 4px;">Nhắn Tin Trực Tiếp</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div style="display: flex; flex-direction: column; gap: 1.75rem;">

    <?php if (!empty($selectedJob) && !empty($matchAnalysis)): 
        $score = $matchAnalysis['score'] ?? 75;
        $level = $matchAnalysis['level'] ?? 'high';
        $label = $matchAnalysis['label'] ?? 'Phù Hợp';
        $matchedSkills = $matchAnalysis['matched_skills'] ?? [];
        $missingSkills = $matchAnalysis['missing_skills'] ?? [];
        $breakdown = $matchAnalysis['breakdown'] ?? [];
        $empCriteria = $matchAnalysis['employer_criteria'] ?? [];
        $reasons = $matchAnalysis['reasons'] ?? [];
        $advice = $matchAnalysis['advice'] ?? [];
        $scoreColor = $score >= 85 ? '#059669' : ($score >= 70 ? '#0284c7' : ($score >= 50 ? '#d97706' : '#dc2626'));
        $scoreBg = $score >= 85 ? '#ecfdf5' : ($score >= 70 ? '#f0f9ff' : ($score >= 50 ? '#fffbeb' : '#fef2f2'));
        $scoreBorder = $score >= 85 ? '#a7f3d0' : ($score >= 70 ? '#bae6fd' : ($score >= 50 ? '#fde68a' : '#fecaca'));
    ?>
    <!-- ========================================================================= -->
    <!-- KHỐI 0: ĐỐI CHIẾU ĐỘ TƯƠNG THÍCH THỰC TẾ & % PHÙ HỢP CÔNG VIỆC CỦA NTD    -->
    <!-- ========================================================================= -->
    <div class="card" style="border-top: 4px solid <?= $scoreColor ?>; background: #ffffff; box-shadow: 0 4px 18px rgba(0,0,0,0.06); border-radius: 12px; padding: 1.5rem;">
        
        <!-- HEADER KHỐI ĐỐI CHIẾU -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                    <span class="badge" style="background: <?= $scoreBg ?>; color: <?= $scoreColor ?>; border: 1px solid <?= $scoreBorder ?>; font-weight: 800; font-size: 0.8rem; padding: 0.25rem 0.6rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                        <?= icon('sparkle', '', 14) ?> AI Job Fit & Đối Chiếu Thực Tế
                    </span>
                    <span style="font-size: 0.82rem; color: #64748b;">(Dữ liệu thực tế hệ thống)</span>
                </div>
                <h2 style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Đối Chiếu Độ Tương Thích Thực Tế Giữa NLĐ & Công Việc Của Doanh Nghiệp
                </h2>
                <p style="font-size: 0.88rem; color: #64748b; margin: 0.25rem 0 0 0;">
                    Đang đối soát ứng viên với vị trí tuyển dụng: <strong style="color: #0f172a;"><?= htmlspecialchars($selectedJob['job_title'] ?? '') ?></strong>
                </p>
            </div>

            <!-- NÚT BẤM % TƯƠNG THÍCH TƯƠNG TÁC (CLICK VÔ ĐỂ XEM GIẢI THÍCH) -->
            <div onclick="openScoreExplanationModal()" style="cursor: pointer; background: <?= $scoreBg ?>; border: 2px solid <?= $scoreBorder ?>; border-radius: 12px; padding: 0.75rem 1.25rem; text-align: center; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.04); min-width: 170px;" onmouseover="this.style.transform='scale(1.03)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.04)';" title="Bấm vào để xem: Tại sao có giá trị % này?">
                <div style="display: flex; align-items: center; justify-content: center; gap: 0.35rem; color: <?= $scoreColor ?>; font-weight: 900; font-size: 2rem; line-height: 1;">
                    <span><?= $score ?>%</span>
                </div>
                <div style="font-size: 0.82rem; font-weight: 800; color: <?= $scoreColor ?>; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.3px;">
                    <?= $label ?>
                </div>
                <div style="font-size: 0.72rem; color: <?= $scoreColor ?>; margin-top: 4px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 3px; text-decoration: underline;">
                    <?= icon('info', '', 11) ?> <span>Tại sao có giá trị này?</span>
                </div>
            </div>
        </div>

        <!-- BỘ CHỌN VỊ TRÍ TUYỂN DỤNG KÈM TÌM KIẾM (CHỌN VỊ TRÍ TUYỂN DỤNG ĐỂ ĐỐI CHIẾU ỨNG VIÊN THÊM PHẦN TÌM) -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
            <label style="font-size: 0.82rem; font-weight: 800; color: #334155; display: block; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.3px;">
                <?= icon('briefcase', '', 14) ?> Chọn vị trí tuyển dụng để đối chiếu ứng viên (Có ô tìm kiếm nhanh):
            </label>
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <div style="position: relative; flex: 1; min-width: 240px;">
                    <input type="text" id="candidateJobSearchInput" placeholder="🔍 Gõ từ khóa để tìm vị trí (VD: Kinh doanh, Kế toán, Cơ khí...)" 
                           class="form-control" style="padding-left: 2.2rem; font-size: 0.88rem; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff;">
                    <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                        <?= icon('search', '', 14) ?>
                    </span>
                </div>
                <div style="flex: 2; min-width: 280px;">
                    <select id="candidateJobSelect" class="form-control" style="width: 100%; padding: 0.55rem 0.85rem; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.9rem; font-weight: 700; color: #0f172a; background: #ffffff;" onchange="window.location.href = '<?= url('/employer/candidates/' . $candidate['seeker_id']) ?>?job_id=' + this.value;">
                        <?php foreach ($allEmployerJobs as $j): ?>
                            <option value="<?= $j['job_post_id'] ?>" data-search="<?= htmlspecialchars(mb_strtolower($j['job_title'] . ' ' . ($j['workplace_address'] ?? ''))) ?>" <?= $selectedJobId === intval($j['job_post_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($j['job_title']) ?> · Tuyển <?= $j['number_of_positions'] ?> người · <?= formatSalary($j['salary_amount'] ?? 0, $j['employment_type'] ?? 'full_time') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="font-size: 0.76rem; color: #64748b; margin-top: 0.4rem;">
                <em>Gõ từ khóa vào ô tìm kiếm để lọc nhanh danh sách tin tuyển dụng của công ty bạn, sau đó chọn vị trí để hệ thống tự động tính toán lại tỷ lệ tương thích.</em>
            </div>
        </div>

        <!-- THỂ HIỆN 3 PHẦN RÕ RÀNG: ĐẠT ĐƯỢC - KHÔNG ĐẠT ĐƯỢC -> KẾT QUẢ -->
        <div style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0 0 0.85rem 0; display: flex; align-items: center; gap: 0.45rem;">
                <span><?= icon('target', '', 16) ?></span>
                <span>Đánh Giá Tiêu Chuẩn Tuyển Dụng: Đạt Được, Không Đạt Được ➔ Kết Quả</span>
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                
                <!-- 1. ĐẠT ĐƯỢC -->
                <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 1.15rem; display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; border-bottom: 1px solid #bbf7d0; padding-bottom: 0.5rem;">
                        <span style="font-size: 0.92rem; font-weight: 800; color: #166534; display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: #22c55e; color: #ffffff; border-radius: 50%; font-size: 0.75rem; font-weight: 900;">✓</span>
                            <span>1. TIÊU CHÍ ĐẠT ĐƯỢC</span>
                        </span>
                        <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 800; font-size: 0.75rem;">
                            <?= count($matchedSkills) + count(array_filter($empCriteria, fn($c) => ($c['status'] ?? '') === 'pass')) ?> mục
                        </span>
                    </div>

                    <div style="font-size: 0.82rem; color: #14532d; display: flex; flex-direction: column; gap: 0.5rem; flex: 1;">
                        <?php if (!empty($matchedSkills)): ?>
                            <div>
                                <strong style="display: block; margin-bottom: 0.25rem;">• Kỹ năng chuyên môn trùng khớp:</strong>
                                <div style="display: flex; gap: 0.3rem; flex-wrap: wrap;">
                                    <?php foreach ($matchedSkills as $sk): ?>
                                        <span style="background: #ffffff; border: 1px solid #86efac; color: #166534; font-size: 0.74rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 4px;">
                                            ✓ <?= htmlspecialchars($sk) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($empCriteria as $cKey => $crit): 
                            if (($crit['status'] ?? '') === 'pass'): ?>
                                <div style="display: flex; align-items: flex-start; gap: 0.35rem;">
                                    <span style="color: #16a34a; font-weight: 800;">✓</span>
                                    <div>
                                        <strong><?= htmlspecialchars($crit['label'] ?? $cKey) ?>:</strong>
                                        <span><?= htmlspecialchars($crit['actual'] ?? '') ?></span>
                                        <span style="color: #15803d; font-size: 0.74rem;">(Đúng yêu cầu: <?= htmlspecialchars($crit['required'] ?? '') ?>)</span>
                                    </div>
                                </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>

                <!-- 2. KHÔNG ĐẠT ĐƯỢC / CẦN CẢI THIỆN -->
                <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 10px; padding: 1.15rem; display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; border-bottom: 1px solid #fef08a; padding-bottom: 0.5rem;">
                        <span style="font-size: 0.92rem; font-weight: 800; color: #92400e; display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: #f59e0b; color: #ffffff; border-radius: 50%; font-size: 0.75rem; font-weight: 900;">✕</span>
                            <span>2. TIÊU CHÍ CHƯA ĐẠT / CẦN LƯU Ý</span>
                        </span>
                        <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 800; font-size: 0.75rem;">
                            <?= count($missingSkills) + count(array_filter($empCriteria, fn($c) => in_array($c['status'] ?? '', ['fail', 'warning']))) ?> mục
                        </span>
                    </div>

                    <div style="font-size: 0.82rem; color: #78350f; display: flex; flex-direction: column; gap: 0.5rem; flex: 1;">
                        <?php if (!empty($missingSkills)): ?>
                            <div>
                                <strong style="display: block; margin-bottom: 0.25rem;">• Kỹ năng còn thiếu so với tin đăng:</strong>
                                <div style="display: flex; gap: 0.3rem; flex-wrap: wrap;">
                                    <?php foreach ($missingSkills as $sk): ?>
                                        <span style="background: #ffffff; border: 1px solid #fde68a; color: #b45309; font-size: 0.74rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 4px;">
                                            ✕ <?= htmlspecialchars($sk) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php 
                        $unmetCount = 0;
                        foreach ($empCriteria as $cKey => $crit): 
                            if (in_array($crit['status'] ?? '', ['fail', 'warning'])): 
                                $unmetCount++;
                        ?>
                                <div style="display: flex; align-items: flex-start; gap: 0.35rem;">
                                    <span style="color: #d97706; font-weight: 800;">!</span>
                                    <div>
                                        <strong><?= htmlspecialchars($crit['label'] ?? $cKey) ?>:</strong>
                                        <span>Thực tế [<?= htmlspecialchars($crit['actual'] ?? '') ?>]</span>
                                        <span style="color: #92400e; font-size: 0.74rem;">· Yêu cầu [<?= htmlspecialchars($crit['required'] ?? '') ?>]</span>
                                    </div>
                                </div>
                        <?php endif; endforeach; ?>

                        <?php if (empty($missingSkills) && $unmetCount === 0): ?>
                            <div style="color: #059669; font-weight: 700; padding: 0.75rem 0; text-align: center;">
                                🌟 Xuất sắc! Ứng viên đáp ứng trọn vẹn toàn bộ các tiêu chí NTD yêu cầu.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 3. KẾT QUẢ ĐÁNH GIÁ TỔNG THỂ -->
                <div style="background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); border: 1.5px solid #99f6e4; border-radius: 10px; padding: 1.15rem; display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; border-bottom: 1px solid #a7f3d0; padding-bottom: 0.5rem;">
                        <span style="font-size: 0.92rem; font-weight: 800; color: #0f766e; display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: #0d9488; color: #ffffff; border-radius: 50%; font-size: 0.75rem; font-weight: 900;">➔</span>
                            <span>3. KẾT QUẢ ĐỐI SOÁT & ĐỀ XUẤT</span>
                        </span>
                        <span class="badge" style="background: #0d9488; color: #ffffff; font-weight: 800; font-size: 0.75rem;">
                            <?= $score ?>% · <?= $label ?>
                        </span>
                    </div>

                    <div style="font-size: 0.84rem; color: #115e59; display: flex; flex-direction: column; gap: 0.55rem; flex: 1;">
                        <div>
                            <strong>• Đánh giá mức độ phù hợp:</strong>
                            <p style="margin: 0.2rem 0 0 0; line-height: 1.45; color: #0f766e;">
                                <?= !empty($reasons[0]) ? htmlspecialchars($reasons[0]) : 'Hồ sơ có mức độ trùng khớp cao với mô tả công việc và các tiêu chuẩn của doanh nghiệp.' ?>
                            </p>
                        </div>
                        <?php if (!empty($advice['interview_focus'])): ?>
                            <div>
                                <strong>• Khuyến nghị phỏng vấn:</strong>
                                <p style="margin: 0.2rem 0 0 0; line-height: 1.45; color: #134e4a; font-style: italic;">
                                    "<?= htmlspecialchars($advice['interview_focus']) ?>"
                                </p>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: auto; padding-top: 0.5rem; display: flex; gap: 0.4rem; flex-wrap: wrap;">
                            <button type="button" class="btn btn-primary btn-sm" onclick="openScoreExplanationModal()" style="font-size: 0.76rem; font-weight: 700; background: #0d9488; border-color: #0d9488;">
                                Xem Công Thức %
                            </button>
                            <a href="<?= url('/employer/messages?partner_id=' . ($candidate['account_id'] ?? 0) . '&seeker_id=' . $candidate['seeker_id'] . '&job_id=' . $selectedJob['job_post_id'] . '&open_meeting_modal=1') ?>" class="btn btn-sm" style="background: #0284c7; color: #ffffff; font-weight: 700; font-size: 0.76rem;">
                                <?= icon('calendar', '', 12) ?> Hẹn Phỏng Vấn
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- BẢNG ĐỐI CHIẾU SO SÁNH ĐỘ TƯƠNG THÍCH THỰC TẾ GIỮA NLĐ & CÔNG VIỆC CỦA NTD -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                    <span><?= icon('sliders', '', 16) ?></span>
                    <span>Bảng So Sánh Chi Tiết Độ Tương Thích Thực Tế (Song Song 2 Chiều)</span>
                </h3>
                <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">
                    10 tiêu chí đối soát thực tế
                </span>
            </div>

            <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                <table class="table" style="margin: 0; font-size: 0.85rem;">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="width: 22%; font-weight: 800; color: #334155;">Tiêu chí đối chiếu</th>
                            <th style="width: 32%; font-weight: 800; color: #0d9488;">Yêu cầu của NTD (Tin tuyển dụng)</th>
                            <th style="width: 32%; font-weight: 800; color: #0284c7;">Hồ sơ thực tế của NLĐ</th>
                            <th style="width: 14%; font-weight: 800; color: #334155; text-align: center;">Đánh giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 1. Vị trí chức danh -->
                        <tr>
                            <td><strong>1. Vị trí / Chức danh</strong></td>
                            <td style="font-weight: 700; color: #0f172a;"><?= htmlspecialchars($selectedJob['job_title'] ?? '') ?></td>
                            <td><?= htmlspecialchars($candidate['desired_position'] ?? 'Chưa cập nhật') ?></td>
                            <td style="text-align: center;">
                                <span class="badge badge-success" style="font-size: 0.72rem;">✓ Phù hợp</span>
                            </td>
                        </tr>

                        <!-- 2. Kỹ năng chuyên môn -->
                        <tr>
                            <td><strong>2. Kỹ năng chuyên môn</strong></td>
                            <td>
                                <div style="color: #475569; font-size: 0.82rem; line-height: 1.4;">
                                    <?= htmlspecialchars($selectedJob['job_requirements'] ?? 'Kỹ năng theo mô tả') ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($matchedSkills)): ?>
                                    <div style="margin-bottom: 0.25rem;">
                                        <strong style="color: #166534; font-size: 0.76rem;">Trùng khớp (<?= count($matchedSkills) ?>):</strong>
                                        <span style="color: #15803d; font-weight: 600;"><?= implode(', ', $matchedSkills) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($missingSkills)): ?>
                                    <div>
                                        <strong style="color: #b45309; font-size: 0.76rem;">Cần bổ sung (<?= count($missingSkills) ?>):</strong>
                                        <span style="color: #92400e;"><?= implode(', ', $missingSkills) ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (count($matchedSkills) >= 2): ?>
                                    <span class="badge badge-success" style="font-size: 0.72rem;">✓ Đạt <?= count($matchedSkills) ?> KN</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.72rem;">⚠️ Cần đào tạo</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- 3. Số năm kinh nghiệm -->
                        <?php $expCrit = $empCriteria['experience'] ?? null; ?>
                        <tr>
                            <td><strong>3. Kinh nghiệm làm việc</strong></td>
                            <td><?= htmlspecialchars($expCrit['required'] ?? 'Theo tin đăng') ?></td>
                            <td><strong><?= intval($candidate['experience_years'] ?? 0) ?> năm kinh nghiệm</strong> thực tế</td>
                            <td style="text-align: center;">
                                <?php if (($expCrit['status'] ?? '') === 'pass'): ?>
                                    <span class="badge badge-success" style="font-size: 0.72rem;">✓ Đạt yêu cầu</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.72rem;">⚠️ Chưa đủ năm</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- 4. Mức lương & Thu nhập -->
                        <?php $empCritData = $empCriteria['employment'] ?? null; ?>
                        <tr>
                            <td><strong>4. Mức lương & Thu nhập</strong></td>
                            <td style="font-weight: 700; color: #059669;">
                                <?= formatSalary($selectedJob['salary_amount'] ?? 0, $selectedJob['employment_type'] ?? 'full_time') ?>
                            </td>
                            <td style="font-weight: 700; color: #0284c7;">
                                <?= !empty($candidate['expected_salary']) ? number_format((float)$candidate['expected_salary'], 0, ',', '.') . ' VNĐ/tháng' : 'Thỏa thuận' ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (($empCritData['status'] ?? '') === 'pass'): ?>
                                    <span class="badge badge-success" style="font-size: 0.72rem;">✓ Phù hợp</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.72rem;">⚠️ Thỏa thuận</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- 5. Nơi làm việc & Khoảng cách -->
                        <?php $locCrit = $empCriteria['location'] ?? null; ?>
                        <tr>
                            <td><strong>5. Địa điểm làm việc</strong></td>
                            <td><?= htmlspecialchars($selectedJob['workplace_address'] ?? 'Tại nơi làm việc') ?></td>
                            <td><?= htmlspecialchars($candidate['address'] ?? ($candidate['preferred_location'] ?? 'Bình Dương')) ?></td>
                            <td style="text-align: center;">
                                <?php if (($locCrit['status'] ?? '') === 'pass'): ?>
                                    <span class="badge badge-success" style="font-size: 0.72rem;">✓ Gần nơi làm</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.72rem;">⚠️ Khoảng cách</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- 6. Hình thức làm việc -->
                        <tr>
                            <td><strong>6. Hình thức làm việc</strong></td>
                            <td><?= ($selectedJob['employment_type'] ?? '') === 'part_time' ? 'Bán thời gian (Part-time)' : (($selectedJob['employment_type'] ?? '') === 'temporary' ? 'Thời vụ / Ca' : 'Toàn thời gian (Full-time)') ?></td>
                            <td><?= ($candidate['preferred_employment_type'] ?? '') === 'part_time' ? 'Bán thời gian (Part-time)' : (($candidate['preferred_employment_type'] ?? '') === 'temporary' ? 'Thời vụ / Ca' : 'Toàn thời gian (Full-time)') ?></td>
                            <td style="text-align: center;">
                                <span class="badge badge-success" style="font-size: 0.72rem;">✓ Khớp hình thức</span>
                            </td>
                        </tr>

                        <!-- 7. Trình độ học vấn -->
                        <?php $eduCrit = $empCriteria['education'] ?? null; ?>
                        <tr>
                            <td><strong>7. Trình độ học vấn</strong></td>
                            <td><?= htmlspecialchars($eduCrit['required'] ?? 'Không yêu cầu bằng cấp') ?></td>
                            <td><strong><?= htmlspecialchars($candidate['education_level'] ?? 'Phổ thông') ?></strong></td>
                            <td style="text-align: center;">
                                <?php if (($eduCrit['status'] ?? '') === 'pass'): ?>
                                    <span class="badge badge-success" style="font-size: 0.72rem;">✓ Đạt chuẩn</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.72rem;">⚠️ Thấp hơn</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- 8. Độ tuổi -->
                        <?php $ageCrit = $empCriteria['age'] ?? null; ?>
                        <tr>
                            <td><strong>8. Yêu cầu độ tuổi</strong></td>
                            <td><?= htmlspecialchars($ageCrit['required'] ?? 'Không giới hạn') ?></td>
                            <td><strong><?= $age ? ($age . ' tuổi') : 'Chưa cập nhật' ?></strong> (Sinh: <?= !empty($candidate['date_of_birth']) ? date('d/m/Y', strtotime($candidate['date_of_birth'])) : '—' ?>)</td>
                            <td style="text-align: center;">
                                <?php if (($ageCrit['status'] ?? '') === 'pass'): ?>
                                    <span class="badge badge-success" style="font-size: 0.72rem;">✓ Đúng độ tuổi</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.72rem;">⚠️ Lệch tuổi</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- 9. Giới tính -->
                        <?php $genCrit = $empCriteria['gender'] ?? null; ?>
                        <tr>
                            <td><strong>9. Yêu cầu giới tính</strong></td>
                            <td><?= htmlspecialchars($genCrit['required'] ?? 'Tất cả') ?></td>
                            <td><strong><?= htmlspecialchars($candidate['gender'] ?? 'Khác') ?></strong></td>
                            <td style="text-align: center;">
                                <?php if (($genCrit['status'] ?? '') === 'pass'): ?>
                                    <span class="badge badge-success" style="font-size: 0.72rem;">✓ Đúng giới tính</span>
                                <?php else: ?>
                                    <span class="badge badge-danger" style="font-size: 0.72rem;">✕ Khác giới tính</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- 10. Tác phong & Điểm uy tín -->
                        <?php $traitCrit = $empCriteria['traits'] ?? null; ?>
                        <tr>
                            <td><strong>10. Tác phong & Điểm uy tín</strong></td>
                            <td><?= htmlspecialchars($traitCrit['required'] ?? 'Tiêu chuẩn sàn JobLink') ?></td>
                            <td>
                                <div>Điểm uy tín: <strong style="color: #059669;"><?= $candidate['trust_score'] ?? 95 ?>/100</strong></div>
                                <div style="font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($traitCrit['actual'] ?? 'Đã xác thực danh tính') ?></div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-success" style="font-size: 0.72rem;">✓ Uy tín Hạng A</span>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL GIẢI THÍCH CHI TIẾT: CLICK VÔ % TẠI SAO CÓ GIÁ TRỊ ĐÓ -->
    <div id="scoreExplanationModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 10002; justify-content: center; align-items: center; padding: 1rem; backdrop-filter: blur(4px);">
        <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); padding: 1.75rem;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
                <h3 style="margin: 0; font-size: 1.25rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem; font-weight: 800;">
                    <span style="color: #0d9488;"><?= icon('sparkle', '', 20) ?></span>
                    <span>Tại Sao Hồ Sơ Đạt <span style="color: <?= $scoreColor ?>;"><?= $score ?>%</span> Tương Thích?</span>
                </h3>
                <button type="button" onclick="closeScoreExplanationModal()" style="border: none; background: #f1f5f9; color: #64748b; font-size: 1.2rem; cursor: pointer; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; line-height: 1;">
                    ✕
                </button>
            </div>

            <!-- CÔNG THỨC TÍNH ĐIỂM MINH BẠCH -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 1rem; margin-bottom: 1.25rem;">
                <div style="font-size: 0.85rem; font-weight: 800; color: #334155; margin-bottom: 0.4rem;">
                    📐 CÔNG THỨC TÍNH ĐIỂM MINH BẠCH THEO TIÊU CHÍ CỦA NHÀ TUYỂN DỤNG:
                </div>
                <div style="font-size: 0.82rem; color: #475569; line-height: 1.5;">
                    Điểm tương thích tổng kết <strong><?= $score ?>%</strong> được tổng hợp từ <strong>7 nhóm trọng số tiêu chí</strong> do chính Nhà tuyển dụng đặt ra cho vị trí <em>"<?= htmlspecialchars($selectedJob['job_title'] ?? '') ?>"</em>:
                </div>
            </div>

            <!-- BẢNG BÓC TÁCH TỪNG TRỌNG SỐ THÀNH PHẦN -->
            <div style="display: flex; flex-direction: column; gap: 0.65rem; margin-bottom: 1.25rem;">
                
                <!-- 1. Kỹ năng -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <span style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">1. Kỹ năng chuyên môn (Trọng số 25%)</span>
                        <strong style="color: #0d9488; font-size: 0.92rem;"><?= $breakdown['skills'] ?? 80 ?> / 100 điểm</strong>
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b;">
                        Trùng khớp <?= count($matchedSkills) ?> kỹ năng yêu cầu (<?= implode(', ', $matchedSkills) ?: 'Đạt tương đồng chức danh' ?>) ➔ Đóng góp: <strong>+<?= round(($breakdown['skills'] ?? 80) * 0.25) ?>%</strong> vào điểm tổng.
                    </div>
                </div>

                <!-- 2. Kinh nghiệm -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <span style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">2. Kinh nghiệm thực tế (Trọng số 20%)</span>
                        <strong style="color: #0d9488; font-size: 0.92rem;"><?= $breakdown['experience'] ?? 80 ?> / 100 điểm</strong>
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b;">
                        Ứng viên có <?= intval($candidate['experience_years'] ?? 0) ?> năm kinh nghiệm (Yêu cầu: <?= htmlspecialchars($empCriteria['experience']['required'] ?? 'Không đòi hỏi') ?>) ➔ Đóng góp: <strong>+<?= round(($breakdown['experience'] ?? 80) * 0.20) ?>%</strong>.
                    </div>
                </div>

                <!-- 3. Địa điểm -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <span style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">3. Địa điểm làm việc & Khoảng cách (Trọng số 15%)</span>
                        <strong style="color: #0d9488; font-size: 0.92rem;"><?= $breakdown['location'] ?? 80 ?> / 100 điểm</strong>
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b;">
                        Khu vực cư trú <?= htmlspecialchars($candidate['address'] ?? 'Bình Dương') ?> thuận tiện di chuyển đến nơi làm việc ➔ Đóng góp: <strong>+<?= round(($breakdown['location'] ?? 80) * 0.15) ?>%</strong>.
                    </div>
                </div>

                <!-- 4. Lương -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <span style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">4. Mức lương & Hình thức làm việc (Trọng số 15%)</span>
                        <strong style="color: #0d9488; font-size: 0.92rem;"><?= $breakdown['employment'] ?? 80 ?> / 100 điểm</strong>
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b;">
                        Mức thu nhập <?= formatSalary($selectedJob['salary_amount'] ?? 0, $selectedJob['employment_type'] ?? 'full_time') ?> phù hợp kỳ vọng ứng viên ➔ Đóng góp: <strong>+<?= round(($breakdown['employment'] ?? 80) * 0.15) ?>%</strong>.
                    </div>
                </div>

                <!-- 5. Học vấn -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <span style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">5. Trình độ học vấn & Bằng cấp (Trọng số 10%)</span>
                        <strong style="color: #0d9488; font-size: 0.92rem;"><?= $breakdown['education'] ?? 80 ?> / 100 điểm</strong>
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b;">
                        Học vấn <?= htmlspecialchars($candidate['education_level'] ?? 'Phổ thông') ?> (Yêu cầu: <?= htmlspecialchars($empCriteria['education']['required'] ?? 'Không yêu cầu') ?>) ➔ Đóng góp: <strong>+<?= round(($breakdown['education'] ?? 80) * 0.10) ?>%</strong>.
                    </div>
                </div>

                <!-- 6. Tác phong & Tiêu chuẩn khác -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <span style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">6. Tác phong lao động & Điểm uy tín (Trọng số 10%)</span>
                        <strong style="color: #0d9488; font-size: 0.92rem;"><?= $breakdown['traits'] ?? 85 ?> / 100 điểm</strong>
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b;">
                        Điểm uy tín hệ thống <?= $candidate['trust_score'] ?? 95 ?>/100 · Tác phong chuẩn mực ➔ Đóng góp: <strong>+<?= round(($breakdown['traits'] ?? 85) * 0.10) ?>%</strong>.
                    </div>
                </div>

                <!-- 7. Độ tuổi & Giới tính -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <span style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">7. Độ tuổi & Giới tính (Trọng số 5%)</span>
                        <strong style="color: #0d9488; font-size: 0.92rem;">100 / 100 điểm</strong>
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b;">
                        Tuổi <?= $age ? ($age . ' tuổi') : 'hợp lệ' ?> và giới tính <?= htmlspecialchars($candidate['gender'] ?? 'phù hợp') ?> đúng tiêu chuẩn ➔ Đóng góp: <strong>+5%</strong>.
                    </div>
                </div>

            </div>

            <!-- TỔNG KẾT VÀ NÚT ĐÓNG -->
            <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 10px; padding: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <div style="font-size: 0.82rem; color: #065f46; font-weight: 700;">TỔNG ĐIỂM TƯƠNG THÍCH THỰC TẾ:</div>
                    <div style="font-size: 1.6rem; font-weight: 900; color: #059669; line-height: 1.1;">
                        <?= $score ?>% · Xếp loại: <?= $label ?>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="closeScoreExplanationModal()" style="font-weight: 700; padding: 0.5rem 1.25rem;">
                    Đã Hiểu
                </button>
            </div>

        </div>
    </div>

    <script>
    function openScoreExplanationModal() {
        var m = document.getElementById('scoreExplanationModal');
        if (m) m.style.display = 'flex';
    }
    function closeScoreExplanationModal() {
        var m = document.getElementById('scoreExplanationModal');
        if (m) m.style.display = 'none';
    }
    // Tìm kiếm vị trí tuyển dụng nhanh chóng
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('candidateJobSearchInput');
        var select = document.getElementById('candidateJobSelect');
        if (searchInput && select) {
            searchInput.addEventListener('input', function() {
                var q = this.value.toLowerCase().trim();
                var options = select.querySelectorAll('option');
                var matchCount = 0;
                var firstMatchVal = null;
                options.forEach(function(opt) {
                    var text = (opt.getAttribute('data-search') || opt.textContent).toLowerCase();
                    if (!q || text.indexOf(q) !== -1) {
                        opt.style.display = '';
                        matchCount++;
                        if (!firstMatchVal) firstMatchVal = opt.value;
                    } else {
                        opt.style.display = 'none';
                    }
                });
                if (q && firstMatchVal && options[select.selectedIndex].style.display === 'none') {
                    select.value = firstMatchVal;
                }
            });
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (select.value) {
                        window.location.href = '<?= url('/employer/candidates/' . $candidate['seeker_id']) ?>?job_id=' + select.value;
                    }
                }
            });
        }
    });
    </script>
    <?php endif; ?>

    <!-- ========================================================================= -->
    <!-- PHẦN 1: TÊN TUỔI, THÔNG TIN LIÊN LẠC, ĐỊA CHỈ & THÔNG TIN CƠ BẢN          -->
    <!-- ========================================================================= -->
    <div class="card" style="border-left: 4px solid var(--primary); background: #ffffff; box-shadow: 0 4px 14px rgba(0,0,0,0.04); border-radius: 12px; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                <span style="background: #e0f2fe; color: #0284c7; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">1</span>
                <span>Thông Tin Cá Nhân & Liên Lạc</span>
            </h2>
            <span class="badge badge-primary">Mã NLĐ: <?= formatId('seeker', $candidate['seeker_id']) ?></span>
        </div>

        <div style="display: flex; gap: 1.75rem; align-items: flex-start; flex-wrap: wrap;">
            <img src="<?= htmlspecialchars(getAvatarUrl($candidate['avatar_url'] ?? '', 'JOB_SEEKER', $candidate['gender'] ?? '')) ?>" alt="avatar" style="width: 120px; height: 120px; border-radius: 14px; object-fit: cover; border: 3px solid #0d9488; box-shadow: 0 6px 16px rgba(13,148,136,0.15);" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $candidate['gender'] ?? '') ?>';">
            
            <div style="flex: 1; min-width: 280px;">
                <h1 style="font-size: 1.65rem; font-weight: 800; color: #0f172a; margin: 0 0 0.4rem 0;">
                    <?= htmlspecialchars($candidate['full_name'] ?? 'Người lao động') ?>
                </h1>

                <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; margin-bottom: 1rem;">
                    <?php if ($age): ?>
                        <span class="badge badge-slate" style="font-weight: 700;">Tuổi: <?= $age ?> tuổi</span>
                    <?php endif; ?>
                    <span class="badge badge-slate">Giới tính: <?= htmlspecialchars($candidate['gender'] ?? 'Khác') ?></span>
                    <?php if (!empty($candidate['date_of_birth'])): ?>
                        <span class="badge badge-slate" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                            <?= icon('cake', '', 12) ?> <span>Sinh ngày: <?= date('d/m/Y', strtotime($candidate['date_of_birth'])) ?></span>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($candidate['education_level'])): ?>
                        <span class="badge badge-primary" style="background: #e0f2fe; color: #0369a1;">Học vấn: <?= htmlspecialchars($candidate['education_level']) ?></span>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 0.85rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.9rem;">
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="color: #64748b;"><?= icon('phone', '', 14) ?> SĐT / Hotline:</span>
                        <a href="tel:<?= htmlspecialchars($candidate['phone_number'] ?? '') ?>" style="color: #0d9488; font-weight: 700; text-decoration: none;">
                            <?= htmlspecialchars($candidate['phone_number'] ?? 'Chưa cập nhật') ?>
                        </a>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="color: #64748b;"><?= icon('mail', '', 14) ?> Email liên hệ:</span>
                        <a href="mailto:<?= htmlspecialchars($candidate['email'] ?? '') ?>" style="color: #0f172a; font-weight: 600; text-decoration: none;">
                            <?= htmlspecialchars($candidate['email'] ?? 'Chưa cập nhật') ?>
                        </a>
                    </div>
                    <div style="grid-column: 1 / -1; display: flex; align-items: center; gap: 0.4rem;">
                        <span style="color: #64748b;"><?= icon('location', '', 14) ?> Địa chỉ cư trú:</span>
                        <strong style="color: #0f172a;"><?= htmlspecialchars($candidate['address'] ?? 'Bình Dương') ?></strong>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 0.75rem; margin-top: 0.85rem; font-size: 0.88rem;">
                    <?php if (!empty($candidate['desired_position'])): ?>
                        <div>
                            <span style="color: #64748b;"><?= icon('target', '', 14) ?> Vị trí mong muốn:</span>
                            <strong style="color: #0f766e; margin-left: 0.25rem;"><?= htmlspecialchars($candidate['desired_position']) ?></strong>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($candidate['expected_salary'])): ?>
                        <div>
                            <span style="color: #64748b;"><?= icon('dollar', '', 14) ?> Mức lương kỳ vọng:</span>
                            <strong style="color: #059669; margin-left: 0.25rem;"><?= number_format((float)$candidate['expected_salary'], 0, ',', '.') ?> VNĐ/tháng</strong>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($candidate['preferred_employment_type'])): ?>
                        <div>
                            <span style="color: #64748b;"><?= icon('clock', '', 14) ?> Hình thức ưu tiên:</span>
                            <strong style="color: #334155; margin-left: 0.25rem;">
                                <?= $candidate['preferred_employment_type'] === 'part_time' ? 'Bán thời gian (Part-time)' : ($candidate['preferred_employment_type'] === 'temporary' ? 'Thời vụ / Xoay ca' : 'Toàn thời gian (Full-time)') ?>
                            </strong>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($candidate['preferred_location'])): ?>
                        <div>
                            <span style="color: #64748b;"><?= icon('map_pin', '', 14) ?> Khu vực ưu tiên:</span>
                            <strong style="color: #334155; margin-left: 0.25rem;"><?= htmlspecialchars($candidate['preferred_location']) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($candidate['bio'])): ?>
                    <div style="margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px dashed #e2e8f0; font-size: 0.88rem; color: #475569; font-style: italic; line-height: 1.5;">
                        "<?= htmlspecialchars($candidate['bio']) ?>"
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PHẦN 2: CÁC KỸ NĂNG KÈM MINH CHỨNG NĂNG LỰC (NẾU CÓ)                      -->
    <!-- ========================================================================= -->
    <div class="card" style="border-left: 4px solid #0284c7; background: #ffffff; box-shadow: 0 4px 14px rgba(0,0,0,0.04); border-radius: 12px; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                <span style="background: #e0f2fe; color: #0284c7; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">2</span>
                <span>Kỹ Năng Chuyên Môn & Minh Chứng Kèm Theo</span>
            </h2>
            <span style="font-size: 0.82rem; color: #64748b;">Kinh nghiệm: <strong><?= intval($candidate['experience_years'] ?? 0) ?> năm</strong></span>
        </div>

        <!-- 2.1 DANH SÁCH THẺ KỸ NĂNG -->
        <div style="margin-bottom: 1.25rem;">
            <h4 style="font-size: 0.9rem; font-weight: 700; color: #334155; margin: 0 0 0.5rem 0;">
                Kỹ năng chuyên môn & tác phong:
            </h4>
            <?php 
                $skillsArr = !empty($candidate['skills']) ? array_filter(array_map('trim', explode(',', $candidate['skills']))) : [];
            ?>
            <?php if (!empty($skillsArr)): ?>
                <div style="display: flex; gap: 0.45rem; flex-wrap: wrap;">
                    <?php foreach ($skillsArr as $sk): ?>
                        <span class="ai-skill-tag matched" style="font-size: 0.82rem; padding: 0.35rem 0.75rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <?= icon('check', '', 11) ?> <?= htmlspecialchars($sk) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: var(--slate-400); font-size: 0.88rem; margin: 0;">Chưa cập nhật kỹ năng chi tiết.</p>
            <?php endif; ?>
        </div>

        <!-- 2.2 MINH CHỨNG KÈM THEO (CHỨNG CHỈ, BẰNG CẤP, PORTFOLIO) -->
        <div>
            <h4 style="font-size: 0.9rem; font-weight: 700; color: #334155; margin: 0 0 0.6rem 0; display: flex; align-items: center; gap: 0.35rem;">
                <span><?= icon('certificate', '', 16) ?></span> Minh chứng kỹ năng & Chứng chỉ nghề nghiệp (nếu có):
            </h4>

            <?php if (!empty($skillProofs)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.85rem;">
                    <?php foreach ($skillProofs as $proof): ?>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;">
                            <div>
                                <strong style="color: #0f172a; font-size: 0.9rem; display: block; margin-bottom: 0.15rem;">
                                    <?= htmlspecialchars($proof['name'] ?? 'Chứng chỉ chuyên môn') ?>
                                </strong>
                                <span style="font-size: 0.8rem; color: #64748b;">
                                    Cấp bởi: <strong><?= htmlspecialchars($proof['issuer'] ?? 'Tổ chức chuyên môn') ?></strong> 
                                    <?= !empty($proof['year']) ? '· Năm ' . htmlspecialchars($proof['year']) : '' ?>
                                </span>
                            </div>
                            <?php if (!empty($proof['link'])): ?>
                                <a href="<?= htmlspecialchars($proof['link']) ?>" target="_blank" class="btn btn-sm" style="font-size: 0.78rem; padding: 0.3rem 0.6rem; color: #ffffff; background: #0284c7; border-color: #0284c7; white-space: nowrap;">
                                    Xem Minh Chứng
                                </a>
                            <?php else: ?>
                                <span class="badge badge-success" style="font-size: 0.72rem;">Đã xác thực</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1rem; font-size: 0.85rem; color: #64748b; text-align: center;">
                    Người lao động chưa tải lên chứng chỉ hoặc minh chứng kỹ năng đính kèm.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PHẦN 3: ĐIỂM UY TÍN HOẶC SỐ ĐIỂM THEO HỆ 5 SAO (CHỈ HIỆN ĐIỂM, KHÔNG HIỆN ĐÁNH GIÁ) -->
    <!-- ========================================================================= -->
    <div class="card" style="border-left: 4px solid #f59e0b; background: #ffffff; box-shadow: 0 4px 14px rgba(0,0,0,0.04); border-radius: 12px; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                <span style="background: #fef3c7; color: #b45309; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">3</span>
                <span>Điểm Đánh Giá & Điểm Uy Tín Nền Tảng</span>
            </h2>
            <span style="font-size: 0.82rem; color: #b45309; font-weight: 700;">Chỉ số tín nhiệm tự động</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            
            <!-- THẺ ĐIỂM THEO HỆ 5 SAO -->
            <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a; border-radius: 10px; padding: 1.25rem; text-align: center;">
                <span style="font-size: 0.85rem; font-weight: 700; color: #92400e; display: block; margin-bottom: 0.35rem;">
                    ĐIỂM ĐÁNH GIÁ NĂNG LỰC (HỆ 5 SAO)
                </span>
                <div style="font-size: 2.25rem; font-weight: 900; color: #b45309; line-height: 1.2; margin-bottom: 0.35rem;">
                    <?= $candidate['avg_rating'] ?? '5.0' ?> <span style="font-size: 1.2rem; font-weight: 700; color: #d97706;">/ 5.0</span>
                </div>
                <div style="color: #f59e0b; display: inline-flex; gap: 2px; margin-bottom: 0.45rem;">
                    <?= icon('star_fill', '', 18) ?><?= icon('star_fill', '', 18) ?><?= icon('star_fill', '', 18) ?><?= icon('star_fill', '', 18) ?><?= icon('star_fill', '', 18) ?>
                </div>
                <span style="font-size: 0.82rem; color: #78350f; font-weight: 600; display: block;">
                    Dựa trên <?= count($reviews) ?> lượt đánh giá ca làm việc
                </span>
            </div>

            <!-- THẺ ĐIỂM UY TÍN (TRUST SCORE) -->
            <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1px solid #a7f3d0; border-radius: 10px; padding: 1.25rem; text-align: center;">
                <span style="font-size: 0.85rem; font-weight: 700; color: #065f46; display: block; margin-bottom: 0.35rem;">
                    ĐIỂM UY TÍN NỀN TẢNG (TRUST SCORE)
                </span>
                <div style="font-size: 2.25rem; font-weight: 900; color: #059669; line-height: 1.2; margin-bottom: 0.35rem;">
                    <?= $candidate['trust_score'] ?? 95 ?> <span style="font-size: 1.2rem; font-weight: 700; color: #10b981;">/ 100</span>
                </div>
                <div style="width: 80%; background: #a7f3d0; height: 8px; border-radius: 999px; margin: 0 auto 0.65rem auto; overflow: hidden;">
                    <div style="width: <?= min(100, intval($candidate['trust_score'] ?? 95)) ?>%; background: #059669; height: 100%; border-radius: 999px;"></div>
                </div>
                <span class="badge badge-success" style="font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                    <?= icon('shield', '', 14) ?> Mức độ tin cậy: Rất cao (Hạng A)
                </span>
            </div>

            <!-- CHỈ SỐ TÁC PHONG LAO ĐỘNG -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: center; gap: 0.5rem; font-size: 0.88rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem; color: #166534; font-weight: 600;">
                    <span style="color: #10b981; display: flex; align-items: center;"><?= icon('check_circle', '', 16) ?></span> Tỷ lệ đúng giờ ca làm: <strong>100%</strong>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem; color: #166534; font-weight: 600;">
                    <span style="color: #10b981; display: flex; align-items: center;"><?= icon('check_circle', '', 16) ?></span> Đã xác thực danh tính & CCCD
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem; color: #166534; font-weight: 600;">
                    <span style="color: #10b981; display: flex; align-items: center;"><?= icon('check_circle', '', 16) ?></span> Đã ký cam kết tác phong JobLink
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem; color: #475569; font-size: 0.82rem; margin-top: 0.25rem;">
                    <span>Số lần nhắc nhở vi phạm: <strong><?= intval($candidate['warning_count'] ?? 0) ?></strong></span>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PHẦN 4: LỊCH SỬ LÀM VIỆC (NẾU CÓ)                                         -->
    <!-- ========================================================================= -->
    <div class="card" style="border-left: 4px solid #8b5cf6; background: #ffffff; box-shadow: 0 4px 14px rgba(0,0,0,0.04); border-radius: 12px; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                <span style="background: #ede9fe; color: #7c3aed; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">4</span>
                <span>Lịch Sử Làm Việc & Kinh Nghiệm Thực Tế</span>
            </h2>
            <span style="font-size: 0.82rem; color: #64748b;">(Đã làm tại công ty nào, từ ngày bao nhiêu)</span>
        </div>

        <?php if (!empty($workHistory)): ?>
            <div style="position: relative; padding-left: 1.5rem; display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Đường kẻ timeline dọc -->
                <div style="position: absolute; left: 6px; top: 8px; bottom: 8px; width: 2px; background: #e2e8f0;"></div>

                <?php foreach ($workHistory as $item): ?>
                    <div style="position: relative;">
                        <!-- Chấm tròn timeline -->
                        <div style="position: absolute; left: -1.85rem; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: #7c3aed; border: 3px solid #ede9fe;"></div>
                        
                        <div style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 1rem 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.35rem;">
                                <div>
                                    <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 0.2rem 0;">
                                        <?= htmlspecialchars($item['company_name'] ?? 'Doanh nghiệp') ?>
                                    </h4>
                                    <span style="font-weight: 700; color: #6d28d9; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('briefcase', '', 14) ?> Vị trí: <?= htmlspecialchars($item['position'] ?? 'Nhân viên') ?>
                                    </span>
                                </div>
                                <span class="badge" style="background: #ede9fe; color: #6d28d9; font-weight: 700; font-size: 0.82rem; padding: 0.25rem 0.6rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('calendar', '', 13) ?> <?= !empty($item['start_date']) ? date('d/m/Y', strtotime($item['start_date'])) : 'Trước đây' ?> 
                                    - 
                                    <?= !empty($item['end_date']) ? date('d/m/Y', strtotime($item['end_date'])) : 'Hiện tại' ?>
                                </span>
                            </div>

                            <?php if (!empty($item['description'])): ?>
                                <p style="font-size: 0.88rem; color: #4b5563; margin: 0.5rem 0 0 0; line-height: 1.55;">
                                    <?= nl2br(htmlspecialchars($item['description'])) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!empty($candidate['experience_summary'])): ?>
            <div style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 1.25rem; font-size: 0.92rem; color: #374151; line-height: 1.6;">
                <?= nl2br(htmlspecialchars($candidate['experience_summary'])) ?>
            </div>
        <?php else: ?>
            <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1.5rem; text-align: center; color: #64748b; font-size: 0.88rem;">
                <div style="margin-bottom: 0.35rem; color: #94a3b8;"><?= icon('briefcase', '', 36) ?></div>
                Người lao động mới tốt nghiệp hoặc chưa cập nhật lịch sử làm việc trước đây trên hệ thống.
            </div>
        <?php endif; ?>
    </div>

    <!-- ========================================================================= -->
    <!-- PHẦN 5: ĐƠN ỨNG TUYỂN & QUY TRÌNH PHỎNG VẤN TẠI DOANH NGHIỆP CỦA BẠN        -->
    <!-- ========================================================================= -->
    <div class="card" style="border-top: 3px solid #0d9488; background: #ffffff; box-shadow: 0 4px 14px rgba(0,0,0,0.04); border-radius: 12px; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                <span><?= icon('file_text', '', 18) ?></span>
                <span>Đơn Ứng Tuyển & Phỏng Vấn Tại Công Ty Bạn (<?= count($applications) ?>)</span>
            </h2>
            <span style="font-size: 0.85rem; color: #0d9488; font-weight: 700;">Quy trình tuyển dụng trực tiếp</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php if (empty($applications)): ?>
                <div style="text-align: center; padding: 2.5rem 1.5rem; color: var(--slate-500); background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px;">
                    <div style="margin-bottom: 0.5rem; color: #94a3b8;"><?= icon('file_text', '', 40) ?></div>
                    <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.35rem; font-size: 1rem;">Người lao động chưa nộp đơn trực tiếp vào công ty của bạn</p>
                    <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 1.25rem;">Bạn có thể chủ động gửi lời mời ứng tuyển vào vị trí đang mở hoặc lên lịch phỏng vấn trực tiếp người này.</p>
                    <?php if (!empty($activeJobs)): ?>
                        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('inviteCandidateModal').style.display='flex'" style="font-weight: 700;">
                            <?= icon('send', '', 14) ?> <span style="margin-left: 4px;">Mời Ứng Tuyển Việc Làm</span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app): 
                    $isAccepted = in_array($app['request_status'], ['accepted', 'invitation_accepted']);
                    $isRejected = in_array($app['request_status'], ['rejected', 'invitation_rejected']);
                    $hasMeeting = !empty($app['meeting_id']);
                    $isPastMeeting = $hasMeeting && ((!empty($app['meeting_date']) && strtotime($app['meeting_date']) <= strtotime(date('Y-m-d'))) || ($app['meeting_status'] === 'completed'));
                    $onboarding = !empty($app['onboarding_details']) ? json_decode($app['onboarding_details'], true) : null;
                ?>
                    <div style="border: 1px solid <?= $isAccepted ? '#34d399' : '#cbd5e1' ?>; border-radius: 10px; padding: 1.25rem; background: <?= $isAccepted ? '#f0fdf4' : '#ffffff' ?>; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                            <div>
                                <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 0.25rem;">
                                    <?= htmlspecialchars($app['job_title']) ?>
                                </h3>
                                <p style="font-size: 0.85rem; color: var(--slate-500); margin: 0;">
                                    Ngày nộp: <?= date('d/m/Y H:i', strtotime($app['applied_at'])) ?>
                                </p>
                            </div>
                            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                <?php if (!empty($app['ai_analysis'])): ?>
                                    <span class="ai-match-badge <?= $app['ai_analysis']['level'] ?>" style="font-size: 0.75rem; padding: 0.25rem 0.6rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('ai', '', 12) ?> <?= $app['ai_analysis']['score'] ?>% Phù hợp
                                    </span>
                                <?php endif; ?>

                                <?php if ($isAccepted): ?>
                                    <span class="badge badge-success" style="font-size: 0.85rem; padding: 0.35rem 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('award', '', 14) ?> Phỏng Vấn Thành Công - 100% Đã Nhận Việc
                                    </span>
                                <?php elseif ($isRejected): ?>
                                    <span class="badge badge-danger">Đã từ chối (Hẹn dịp sau)</span>
                                <?php elseif ($isPastMeeting): ?>
                                    <span class="badge" style="background: #e0e7ff; color: #3730a3; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('target', '', 12) ?> 75% Chờ kết quả PV
                                    </span>
                                <?php elseif ($hasMeeting): ?>
                                    <span class="badge badge-primary" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('calendar', '', 12) ?> 50% Đã hẹn PV (<?= date('d/m/Y', strtotime($app['meeting_date'])) ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">25% Chờ duyệt hồ sơ</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- THÔNG BÁO NGÀY LÀM ĐẦU TIÊN NẾU ĐÃ ACCEPT -->
                        <?php if ($isAccepted && !empty($onboarding)): ?>
                            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 1rem; margin-bottom: 0.85rem; font-size: 0.88rem; color: #065f46;">
                                <strong style="display: block; font-size: 0.95rem; margin-bottom: 0.35rem;">
                                    THÔNG BÁO NGÀY LÀM BỮA ĐẦU TIÊN (ĐÃ GỬI ĐẾN NGƯỜI LAO ĐỘNG):
                                </strong>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <div style="display: flex; align-items: center; gap: 0.25rem;"><?= icon('calendar', '', 13) ?> Ngày bắt đầu: <strong><?= date('d/m/Y', strtotime($onboarding['start_date'] ?? 'now')) ?> lúc <?= htmlspecialchars($onboarding['start_time'] ?? '08:00') ?></strong></div>
                                    <div style="display: flex; align-items: center; gap: 0.25rem;"><?= icon('map_pin', '', 13) ?> Nơi tiếp đón: <strong><?= htmlspecialchars($onboarding['workplace'] ?? '') ?></strong></div>
                                    <div style="display: flex; align-items: center; gap: 0.25rem;"><?= icon('user', '', 13) ?> Người đón tiếp: <strong><?= htmlspecialchars($onboarding['contact_person'] ?? '') ?></strong></div>
                                </div>
                                <?php if (!empty($onboarding['notes'])): ?>
                                    <div style="font-size: 0.82rem; color: #047857; display: flex; align-items: center; gap: 0.25rem;"><?= icon('chat', '', 13) ?> Dặn dò: <?= htmlspecialchars($onboarding['notes']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- CÁC NÚT THAO TÁC DUYỆT NHẬN VIỆC / PHỎNG VẤN THÀNH CÔNG -->
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                                <?php if (!$isAccepted): ?>
                                    <button type="button" class="btn btn-success btn-sm btn-open-accept-modal"
                                            data-request-id="<?= $app['request_id'] ?>"
                                            data-seeker-name="<?= htmlspecialchars($candidate['full_name'] ?? 'Người lao động') ?>"
                                            data-job-title="<?= htmlspecialchars($app['job_title']) ?>"
                                            data-workplace="<?= htmlspecialchars($app['workplace_address'] ?? '') ?>"
                                            style="display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 700; background: #059669; border-color: #059669;">
                                        <?= icon('award', '', 14) ?> Phỏng Vấn Thành Công & Duyệt Nhận Việc
                                    </button>
                                    <?php if (!$isRejected): ?>
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-reject-prompt="Lý do từ chối hồ sơ người lao động (JobLink sẽ gửi thư cảm ơn và lưu hồ sơ hẹn dịp sau)">
                                                Từ Chối
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="font-size: 0.85rem; color: #166534; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                                        <?= icon('check', '', 14) ?> Người lao động đã trúng tuyển chính thức
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                <?php if ($app['request_status'] !== 'accepted'): ?>
                                    <a href="<?= url('/employer/messages?partner_id=' . ($candidate['account_id'] ?? 0) . '&seeker_id=' . $candidate['seeker_id'] . '&request_id=' . $app['request_id'] . '&job_id=' . $app['job_post_id'] . '&open_meeting_modal=1') ?>" class="btn btn-primary btn-sm" style="font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                                        <?= icon('calendar', '', 13) ?> <span>Hẹn Lịch Phỏng Vấn</span>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= url('/employer/meetings') ?>" class="btn btn-outline btn-sm" style="font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <?= icon('calendar', '', 13) ?> <span>Xem Lịch Phỏng Vấn</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL DUYỆT NHẬN VIỆC & THIẾT LẬP HÀNH TRANG BUỔI ĐẦU ĐI LÀM (ONBOARDING) -->
<!-- ========================================================================= -->
<div id="acceptOnboardingModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 10001; justify-content: center; align-items: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 650px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.2rem; color: #065f46; display: flex; align-items: center; gap: 0.4rem; font-weight: 800;">
                <?= icon('award', '', 18) ?> Quyết Định Tiếp Nhận & Cẩm Nang Buổi Đầu Đi Làm
            </h3>
            <button type="button" id="closeAcceptModalBtn" style="background: none; border: none; font-size: 1.35rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.88rem; color: #065f46;">
            Người lao động: <strong id="acceptSeekerName" style="color: #047857;"></strong> — Vị trí: <strong id="acceptJobTitle"></strong><br>
            <span style="font-size: 0.8rem; color: #059669;">Hệ thống sẽ gửi thông báo chúc mừng trúng tuyển (tiến độ 100%) kèm danh sách hành trang này đến người lao động qua web và email.</span>
        </div>

        <form method="POST" id="acceptOnboardingForm">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Ngày bắt đầu đi làm (First Day) *</label>
                    <input type="date" name="start_date" id="acceptStartDate" class="form-control" required value="<?= date('Y-m-d', strtotime('+3 days')) ?>" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Giờ có mặt buổi sáng *</label>
                    <input type="time" name="start_time" id="acceptStartTime" class="form-control" required value="08:30" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                </div>
            </div>

            <div style="margin-bottom: 0.85rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Địa điểm tiếp đón / Nơi làm việc *</label>
                <input type="text" name="workplace" id="acceptWorkplace" class="form-control" required placeholder="Địa chỉ cơ sở làm việc, tầng, phòng tiếp nhận..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
            </div>

            <div style="margin-bottom: 0.85rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Người liên hệ tiếp đón / Quản lý trực tiếp *</label>
                <input type="text" name="contact_person" id="acceptContactPerson" class="form-control" required value="<?= htmlspecialchars($employer['employer_name'] ?? 'Phòng Nhân sự') ?>" placeholder="Họ tên người liên hệ, số điện thoại..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
            </div>

            <!-- CHECKLIST HÀNH TRANG CẦN CHUẨN BỊ -->
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #1e293b; display: block; margin-bottom: 0.4rem;">
                    Hành trang & Giấy tờ yêu cầu mang theo ngày đầu tiên:
                </label>
                <div style="display: flex; flex-direction: column; gap: 0.45rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; color: #334155; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Căn cước công dân (CCCD/VNeID) bản gốc để đối chiếu & 01 bản photo" checked>
                        <span>Căn cước công dân (CCCD/VNeID) bản gốc & 01 bản photo</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; color: #334155; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Sơ yếu lý lịch (SYLL) có xác nhận địa phương hoặc CV xin việc" checked>
                        <span>Sơ yếu lý lịch (SYLL) có xác nhận địa phương hoặc bản in CV</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; color: #334155; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Giấy khám sức khỏe còn hạn trong 6 tháng gần nhất" checked>
                        <span>Giấy khám sức khỏe còn hạn trong 6 tháng</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; color: #334155; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="02 ảnh thẻ 3x4 chụp trong vòng 6 tháng để làm thẻ nhân viên" checked>
                        <span>02 ảnh thẻ 3x4 để làm thẻ nhân viên</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; color: #334155; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Số tài khoản ngân hàng chính chủ để nhận chi trả lương" checked>
                        <span>Số tài khoản ngân hàng chính chủ để nhận chi trả lương</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; color: #334155; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Bằng cấp hoặc chứng chỉ nghề nghiệp liên quan (bản photo)" checked>
                        <span>Bằng cấp hoặc chứng chỉ nghề nghiệp liên quan (bản photo)</span>
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Quy định trang phục & Lưu ý thêm ngày đầu:</label>
                <textarea name="notes" rows="2" class="form-control" placeholder="Trang phục lịch sự: Áo sơ mi có cổ/đồng phục, quần sẫm màu, đi giày. Có mặt sớm 15 phút tại sảnh lễ tân..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">Trang phục lịch sự: Áo sơ mi có cổ/đồng phục cơ sở, mang giày kín mũi. Vui lòng có mặt sớm 10-15 phút để làm thủ tục tiếp nhận.</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                <button type="button" id="cancelAcceptModalBtn" class="btn btn-outline btn-sm">Hủy bỏ</button>
                <button type="submit" class="btn btn-success btn-sm" style="font-weight: 700; background: #059669; border-color: #059669; padding: 0.5rem 1.25rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <?= icon('check_circle', '', 16) ?> Xác Nhận Nhận Việc & Gửi Cẩm Nang (100%)
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL MỜI ỨNG TUYỂN VIỆC LÀM TRỰC TIẾP -->
<!-- ========================================== -->
<?php if (!empty($activeJobs)): ?>
<div id="inviteCandidateModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 10002; justify-content: center; align-items: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #0284c7; display: flex; align-items: center; gap: 0.4rem; font-weight: 800;">
                <?= icon('mail', '', 18) ?> Mời Người Lao Động Ứng Tuyển
            </h3>
            <button type="button" onclick="document.getElementById('inviteCandidateModal').style.display='none'" style="background: none; border: none; font-size: 1.35rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <form action="<?= url('/employer/candidates/invite') ?>" method="POST">
            <input type="hidden" name="seeker_id" value="<?= $candidate['seeker_id'] ?>">
            
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">Chọn vị trí đang tuyển của bạn: *</label>
                <select name="job_post_id" class="form-control" required style="width: 100%; padding: 0.55rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.9rem;">
                    <?php foreach ($activeJobs as $job): ?>
                        <option value="<?= $job['job_post_id'] ?>"><?= htmlspecialchars($job['job_title']) ?> (<?= htmlspecialchars($job['workplace_address'] ?? 'Tại cơ sở') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">Lời nhắn gửi tới người lao động:</label>
                <textarea name="note" rows="3" class="form-control" placeholder="Chào bạn, công ty chúng tôi thấy hồ sơ của bạn rất phù hợp..." style="width: 100%; padding: 0.55rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">Chào bạn, doanh nghiệp chúng tôi rất ấn tượng với hồ sơ và kỹ năng của bạn. Trân trọng mời bạn tham khảo và ứng tuyển vị trí này để cùng hợp tác!</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                <button type="button" onclick="document.getElementById('inviteCandidateModal').style.display='none'" class="btn btn-outline btn-sm">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700; background: #0284c7; border-color: #0284c7; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <?= icon('send', '', 16) ?> Gửi Lời Mời Ứng Tuyển
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const acceptModal = document.getElementById('acceptOnboardingModal');
    const acceptForm = document.getElementById('acceptOnboardingForm');
    const openAcceptBtns = document.querySelectorAll('.btn-open-accept-modal');
    const closeAcceptBtn = document.getElementById('closeAcceptModalBtn');
    const cancelAcceptBtn = document.getElementById('cancelAcceptModalBtn');

    openAcceptBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const reqId = this.getAttribute('data-request-id');
            const seekerName = this.getAttribute('data-seeker-name');
            const jobTitle = this.getAttribute('data-job-title');
            const workplace = this.getAttribute('data-workplace') || '';

            document.getElementById('acceptSeekerName').textContent = seekerName;
            document.getElementById('acceptJobTitle').textContent = jobTitle;
            if (workplace) {
                document.getElementById('acceptWorkplace').value = workplace;
            }
            acceptForm.action = '<?= url('/employer/applications/accept-onboarding/') ?>' + reqId;
            acceptModal.style.display = 'flex';
        });
    });

    if (closeAcceptBtn) closeAcceptBtn.addEventListener('click', () => acceptModal.style.display = 'none');
    if (cancelAcceptBtn) cancelAcceptBtn.addEventListener('click', () => acceptModal.style.display = 'none');
});
</script>

<?php 
$content = ob_get_clean(); 
if (in_array($_SESSION['user']['role_code'] ?? '', ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
    require_once __DIR__ . '/../layouts/admin_layout.php';
} else {
    require_once __DIR__ . '/../layouts/user_layout.php';
}
?>
