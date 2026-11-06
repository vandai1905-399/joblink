<?php 
$extraCss = ['css/applications/employer_applications.css', 'css/jobs/ai_matching.css'];
$activeSubTab = $_GET['tab'] ?? 'applicants';
ob_start(); 
?>

<!-- HEADER THÔNG TIN TIN TUYỂN DỤNG -->
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
            <a href="<?= url('/employer/jobs') ?>" class="btn btn-sm btn-outline" style="padding: 0.25rem 0.6rem; font-size: 0.8rem; border-radius: 6px;">
                Quay lại danh sách tin
            </a>
            <span class="badge badge-primary" style="font-size: 0.78rem;">Mã tin: <?= formatId('job', $job['job_post_id']) ?></span>
            <?php if ($job['post_status'] === 'approved'): ?>
                <span class="badge badge-success" style="font-size: 0.78rem;"><?= icon('check', '', 12) ?> Đang hiển thị</span>
            <?php else: ?>
                <span class="badge badge-warning" style="font-size: 0.78rem;"><?= icon('clock', '', 12) ?> <?= htmlspecialchars($job['post_status']) ?></span>
            <?php endif; ?>
        </div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0.25rem 0;">
            <?= htmlspecialchars($job['job_title']) ?>
        </h1>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
            <?= icon('map_pin', '', 14) ?> <?= htmlspecialchars($job['workplace_address']) ?> · <?= icon('dollar', '', 14) ?> <?= formatSalary($job['salary_amount'], $job['employment_type']) ?> · <?= icon('users', '', 14) ?> Tuyển: <?= $job['number_of_positions'] ?> người · <?= icon('calendar', '', 14) ?> Hạn: <?= date('d/m/Y', strtotime($job['application_deadline'])) ?>
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" target="_blank" class="btn btn-outline btn-sm">
            <?= icon('search', '', 14) ?> Xem tin ngoài sàn
        </a>
        <a href="<?= url('/employer/jobs/edit/' . $job['job_post_id']) ?>" class="btn btn-outline btn-sm">
            <?= icon('sliders', '', 14) ?> Chỉnh sửa tin
        </a>
    </div>
</div>

<!-- TAB NAVIGATION: NGƯỜI LAO ĐỘNG ĐÃ NỘP vs GỢI Ý NGƯỜI LAO ĐỘNG TỪ AI -->
<div style="display: flex; gap: 0.5rem; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <a href="?tab=applicants" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeSubTab === 'applicants' ? '#0d9488' : 'transparent' ?>; background: <?= $activeSubTab === 'applicants' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeSubTab === 'applicants' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('applications', '', 16) ?> <span>1. Người Lao Động Đã Nộp Hồ Sơ (<?= count($applications) ?>)</span>
    </a>
    <a href="?tab=ai_recommendations" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeSubTab === 'ai_recommendations' ? '#0d9488' : 'transparent' ?>; background: <?= $activeSubTab === 'ai_recommendations' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeSubTab === 'ai_recommendations' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('zap', '', 16) ?> <span>2. Người Lao Động Gợi Ý Từ AI (<?= count($aiRecommendations) ?>)</span>
    </a>
</div>

<!-- ========================================================================= -->
<!-- TAB 1: NGƯỜI LAO ĐỘNG ĐÃ NỘP HỒ SƠ CHO TIN NÀY                           -->
<!-- ========================================================================= -->
<?php if ($activeSubTab === 'applicants'): ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Người lao động</th>
                        <th>Độ phù hợp AI</th>
                        <th>Kinh nghiệm & Kỹ năng</th>
                        <th>Email & SĐT</th>
                        <th>Ngày nộp</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--slate-400); padding: 3rem 1rem;">
                                <div style="margin-bottom: 0.5rem; color: var(--slate-400);"><?= icon('inbox', '', 36) ?></div>
                                <div style="font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Chưa có người lao động nào nộp hồ sơ vào tin này</div>
                                <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 1rem;">Bạn có thể chuyển qua tab <strong>"Người Lao Động Gợi Ý Từ AI"</strong> để chủ động gửi lời mời làm việc ngay!</div>
                                <a href="?tab=ai_recommendations" class="btn btn-primary btn-sm">Xem danh sách người lao động gợi ý</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td style="min-width: 190px;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <img src="<?= htmlspecialchars(getAvatarUrl($app['seeker_avatar'] ?? '', 'JOB_SEEKER', $app['gender'] ?? '')) ?>" alt="avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #e2e8f0; flex-shrink: 0;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $app['gender'] ?? '') ?>';">
                                        <div>
                                            <a href="<?= url('/employer/candidates/' . $app['seeker_id']) ?>" style="font-weight: 700; color: #0d9488; text-decoration: none;">
                                                <span><?= htmlspecialchars($app['seeker_name']) ?></span>
                                            </a>
                                            <div style="font-size: 0.75rem; color: #64748b;">
                                                <?= htmlspecialchars($app['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($app['date_of_birth']) ? (date('Y') - date('Y', strtotime($app['date_of_birth']))) . ' tuổi' : '' ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (isset($app['ai_match_score'])): ?>
                                        <span class="ai-match-badge <?= $app['ai_match_level'] ?? 'medium' ?>" style="font-size: 0.75rem; padding: 0.25rem 0.55rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <?= icon('ai', '', 12) ?> <?= $app['ai_match_score'] ?>%
                                        </span>
                                        <?php if (!empty($app['ai_matched_skills'])): ?>
                                            <div style="font-size: 0.72rem; color: #059669; margin-top: 0.25rem; max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 0.2rem;" title="<?= htmlspecialchars(implode(', ', $app['ai_matched_skills'])) ?>">
                                                <?= icon('check', '', 10) ?> <?= htmlspecialchars(implode(', ', array_slice($app['ai_matched_skills'], 0, 2))) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="font-size: 0.8rem; color: #94a3b8;">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-size: 0.82rem; font-weight: 600; color: #334155;">
                                        <?= intval($app['years_of_experience'] ?? 0) > 0 ? ($app['years_of_experience'] . ' năm kinh nghiệm') : 'Chưa có kinh nghiệm' ?>
                                    </span>
                                    <?php if (!empty($app['skills'])): ?>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem; max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars($app['skills']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: #0f172a; font-size: 0.85rem;"><?= htmlspecialchars($app['phone_number']) ?></span><br>
                                    <span style="font-size: 0.78rem; color: #64748b;"><?= htmlspecialchars($app['email']) ?></span>
                                </td>
                                <td style="color: #475569; font-size: 0.82rem; white-space: nowrap;">
                                    <?= date('d/m/Y H:i', strtotime($app['applied_at'])) ?>
                                </td>
                                <td>
                                    <?php 
                                        $isAccepted = in_array($app['request_status'], ['accepted', 'invitation_accepted']);
                                        $isRejected = in_array($app['request_status'], ['rejected', 'invitation_rejected']);
                                        $hasMeeting = !empty($app['meeting_id']);
                                        $isPastMeeting = $hasMeeting && ((!empty($app['meeting_date']) && strtotime($app['meeting_date']) <= strtotime(date('Y-m-d'))) || ($app['meeting_status'] === 'completed'));
                                        $isOverdue = false;
                                        $daysLeft = null;
                                        if (!empty($app['response_deadline']) && !$isAccepted && !$isRejected) {
                                            $deadlineTs = strtotime($app['response_deadline']);
                                            $diffSec = $deadlineTs - time();
                                            $daysLeft = ceil($diffSec / 86400);
                                            if ($diffSec < 0) {
                                                $isOverdue = true;
                                            }
                                        }
                                    ?>
                                    <?php if ($isAccepted): ?>
                                        <span class="badge badge-success" style="font-size: 0.75rem;"><?= icon('check', '', 12) ?> 100% Đã nhận việc</span>
                                    <?php elseif ($isRejected): ?>
                                        <span class="badge badge-danger" style="font-size: 0.75rem;">Đã từ chối (Hẹn dịp sau)</span>
                                    <?php elseif ($isPastMeeting): ?>
                                        <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.2rem;">
                                            <?= icon('target', '', 12) ?> 75% Chờ kết quả PV
                                        </span>
                                        <?php if ($isOverdue): ?>
                                            <div style="font-size: 0.7rem; color: #b91c1c; font-weight: 700; margin-top: 0.2rem; background: #fef2f2; border: 1px solid #fecaca; padding: 0.1rem 0.35rem; border-radius: 4px; display: flex; align-items: center; gap: 0.2rem;">
                                                <?= icon('alert_triangle', '', 11) ?> Quá hạn phản hồi (>3 ngày)
                                            </div>
                                        <?php elseif ($daysLeft !== null): ?>
                                            <div style="font-size: 0.7rem; color: #d97706; font-weight: 600; margin-top: 0.2rem; background: #fffbeb; border: 1px solid #fde68a; padding: 0.1rem 0.35rem; border-radius: 4px; display: flex; align-items: center; gap: 0.2rem;">
                                                <?= icon('clock', '', 11) ?> Còn <?= $daysLeft > 0 ? $daysLeft . ' ngày' : 'hôm nay' ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($hasMeeting): ?>
                                        <span class="badge badge-primary" style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.2rem;">
                                            <?= icon('calendar', '', 12) ?> 50% Đã hẹn PV (<?= date('d/m', strtotime($app['meeting_date'])) ?>)
                                        </span>
                                    <?php elseif ($app['request_status'] === 'invited'): ?>
                                        <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:600; font-size:0.75rem;">Đã gửi lời mời</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="font-size: 0.75rem;">25% Chờ duyệt</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                                        <a href="<?= url('/employer/candidates/' . $app['seeker_id']) ?>" class="btn btn-outline btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem; color: #0d9488; border-color: #0d9488;">
                                            <?= icon('user', '', 12) ?> Hồ Sơ
                                        </a>

                                        <?php if ($isAccepted): ?>
                                            <!-- Khi đã duyệt: Nút Nhắn tin riêng & Chốt lịch AI -->
                                            <a href="<?= url('/employer/messages?partner_id=' . ($app['seeker_account_id'] ?? 0)) ?>" class="btn btn-outline btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem; font-weight: 700; color: #0d9488; border-color: #0d9488; display: inline-flex; align-items: center; gap: 0.25rem;" title="Mở khung chat trực tiếp">
                                                <?= icon('chat', '', 12) ?> Chat
                                            </a>
                                            <button type="button" class="btn btn-primary btn-sm btn-open-schedule-modal" 
                                                    data-request-id="<?= $app['request_id'] ?>" 
                                                    data-seeker-id="<?= $app['seeker_id'] ?>" 
                                                    data-seeker-name="<?= htmlspecialchars($app['seeker_name']) ?>"
                                                    data-seeker-account-id="<?= $app['seeker_account_id'] ?>"
                                                    style="padding: 0.3rem 0.6rem; font-size: 0.78rem; background: #0d9488; border-color: #0d9488; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                <?= icon('calendar', '', 12) ?> Chốt Lịch (AI)
                                            </button>
                                        <?php elseif ($isRejected): ?>
                                            <button type="button" class="btn btn-success btn-sm btn-open-accept-modal" 
                                                    data-request-id="<?= $app['request_id'] ?>"
                                                    data-seeker-name="<?= htmlspecialchars($app['seeker_name']) ?>"
                                                    data-job-title="<?= htmlspecialchars($job['job_title']) ?>"
                                                    data-workplace="<?= htmlspecialchars($job['workplace_address'] ?? '') ?>"
                                                    style="padding: 0.3rem 0.55rem; font-size: 0.78rem; font-weight: 700;">
                                                Duyệt Lại
                                            </button>
                                        <?php else: ?>
                                            <!-- Nút Duyệt Nhận Việc mở Modal Onboarding -->
                                            <button type="button" class="btn btn-success btn-sm btn-open-accept-modal" 
                                                    data-request-id="<?= $app['request_id'] ?>"
                                                    data-seeker-name="<?= htmlspecialchars($app['seeker_name']) ?>"
                                                    data-job-title="<?= htmlspecialchars($job['job_title']) ?>"
                                                    data-workplace="<?= htmlspecialchars($job['workplace_address'] ?? '') ?>"
                                                    style="padding: 0.3rem 0.55rem; font-size: 0.78rem; font-weight: 700; background: #059669; border-color: #059669;"
                                                    title="Duyệt nhận việc và gửi cẩm nang hành trang buổi đầu tiên đi làm">
                                                <?= icon('check', '', 12) ?> Duyệt Nhận Việc
                                            </button>

                                            <a href="<?= url('/employer/messages?partner_id=' . ($app['seeker_account_id'] ?? 0) . '&seeker_id=' . ($app['seeker_id'] ?? 0) . '&request_id=' . $app['request_id'] . '&job_id=' . $jobId . '&open_meeting_modal=1') ?>" 
                                               class="btn btn-primary btn-sm" 
                                               style="padding: 0.3rem 0.6rem; font-size: 0.78rem; background: #0d9488; border-color: #0d9488; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                                <?= icon('calendar', '', 13) ?> Hẹn Lịch PV
                                            </a>

                                            <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="button" class="btn btn-outline btn-sm" data-reject-prompt="Lý Do Từ Chối Người Lao Động (JobLink sẽ gửi thư cảm ơn và lưu hồ sơ hẹn dịp sau)" style="color: var(--danger); border-color: #fecaca; padding: 0.3rem 0.55rem; font-size: 0.78rem;">
                                                    Từ chối
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- ========================================================================= -->
<!-- TAB 2: AI ĐỀ XUẤT NGƯỜI LAO ĐỘNG PHÙ HỢP (2 CHIỀU)                   -->
<!-- ========================================================================= -->
<?php else: ?>
    <div style="background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <span style="color: #0d9488;"><?= icon('ai', '', 24) ?></span>
        <div style="font-size: 0.88rem; color: #134e4a;">
            <strong>Cơ Chế Tuyển Dụng 2 Chiều Thông Minh:</strong> Hệ thống AI tự động phân tích và xếp hạng người lao động có kỹ năng, độ tuổi, kinh nghiệm và vị trí địa lý tương thích cao nhất với tin <em>"<?= htmlspecialchars($job['job_title']) ?>"</em>. Doanh nghiệp có thể bấm <strong>"Mời Nhận Việc"</strong> để gửi lời mời trực tiếp đến người lao động!
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem;">
        <?php if (empty($aiRecommendations)): ?>
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #64748b;">
                Chưa có dữ liệu người lao động phù hợp trong hệ thống.
            </div>
        <?php else: ?>
            <?php foreach ($aiRecommendations as $rec): ?>
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border: 1px solid <?= ($rec['ai_match_score'] >= 85) ? '#99f6e4' : '#e2e8f0' ?>; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                    <div>
                        <!-- Header Card: Avatar & Tên & AI Score -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                            <div style="display: flex; gap: 0.75rem; align-items: center;">
                                <img src="<?= htmlspecialchars(getAvatarUrl($rec['avatar_url'] ?? '', 'JOB_SEEKER', $rec['gender'] ?? '')) ?>" alt="avatar" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid #0d9488;" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'JOB_SEEKER', $rec['gender'] ?? '') ?>';">
                                <div>
                                    <a href="<?= url('/employer/candidates/' . $rec['seeker_id']) ?>" style="font-weight: 700; font-size: 1rem; color: #0f172a; text-decoration: none;">
                                        <?= htmlspecialchars($rec['full_name']) ?>
                                    </a>
                                    <div style="font-size: 0.78rem; color: #64748b;">
                                        <?= htmlspecialchars($rec['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($rec['date_of_birth']) ? (date('Y') - date('Y', strtotime($rec['date_of_birth']))) . ' tuổi' : '' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                    </div>
                                </div>
                            </div>
                            <span class="ai-match-badge <?= $rec['ai_match_level'] ?? 'medium' ?>" style="font-size: 0.8rem; padding: 0.3rem 0.6rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('ai', '', 12) ?> <?= $rec['ai_match_score'] ?>%
                            </span>
                        </div>

                        <!-- Điểm uy tín & Khu vực -->
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                            <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('star', '', 12) ?> Trust Score: <strong><?= $rec['trust_score'] ?? 80 ?>/100</strong>
                            </span>
                            <?php if (!empty($rec['address'])): ?>
                                <span class="badge" style="background: #f8fafc; color: #64748b; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('map_pin', '', 12) ?> <?= htmlspecialchars($rec['address']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Kỹ năng trùng khớp -->
                        <?php if (!empty($rec['ai_matched_skills'])): ?>
                            <div style="margin-bottom: 0.75rem;">
                                <div style="font-size: 0.75rem; color: #059669; font-weight: 700; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('check', '', 12) ?> Kỹ năng đáp ứng tốt:
                                </div>
                                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                    <?php foreach ($rec['ai_matched_skills'] as $sk): ?>
                                        <span style="background: #dcfce7; color: #166534; font-size: 0.72rem; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 600;">
                                            <?= htmlspecialchars($sk) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer Card: Nút mời hoặc Trạng thái -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 0.75rem; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <a href="<?= url('/employer/candidates/' . $rec['seeker_id']) ?>" style="font-size: 0.82rem; color: #0d9488; font-weight: 600; text-decoration: none;">
                            Xem hồ sơ
                        </a>

                        <?php if ($rec['request_status'] === 'invited'): ?>
                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 600; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('clock', '', 12) ?> Đã gửi lời mời
                            </span>
                        <?php elseif ($rec['request_status'] === 'accepted' || $rec['request_status'] === 'invitation_accepted'): ?>
                            <span class="badge badge-success" style="font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('check', '', 12) ?> Đã kết nối thành công
                            </span>
                        <?php elseif ($rec['request_status'] === 'pending'): ?>
                            <span class="badge badge-warning" style="font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('mail', '', 12) ?> Người lao động đã nộp hồ sơ
                            </span>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary btn-sm btn-open-invite-modal"
                                    data-seeker-id="<?= $rec['seeker_id'] ?>"
                                    data-seeker-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                    data-job-id="<?= $job['job_post_id'] ?>"
                                    data-job-title="<?= htmlspecialchars($job['job_title']) ?>"
                                    style="padding: 0.35rem 0.75rem; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('target', '', 13) ?> Mời Nhận Việc
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- MODAL GỬI LỜI MỜI NHẬN VIỆC (EMPLOYER INVITE CANDIDATE) -->
<div id="inviteCandidateModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 12px; max-width: 500px; width: 100%; padding: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('target', '', 18) ?> Gửi Lời Mời Nhận Việc
            </h3>
            <button type="button" id="closeInviteModalBtn" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <form action="<?= url('/employer/candidates/invite') ?>" method="POST">
            <input type="hidden" name="seeker_id" id="modalSeekerId">
            <input type="hidden" name="job_post_id" id="modalJobId">

            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">Người lao động được mời:</label>
                <div id="modalSeekerName" style="font-weight: 700; color: #0d9488; font-size: 1rem;"></div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">Vị trí công việc:</label>
                <div id="modalJobTitle" style="color: #0f172a; font-weight: 600; font-size: 0.92rem;"></div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">Lời nhắn từ nhà tuyển dụng (tùy chọn):</label>
                <textarea name="invitation_note" rows="3" class="form-control" placeholder="Ví dụ: Công ty Becamex rất ấn tượng với hồ sơ của bạn và muốn mời bạn vào phỏng vấn ca làm việc..." style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" id="cancelInviteModalBtn" class="btn btn-outline">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Gửi Lời Mời Ngay</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CHỐT LỊCH PHỎNG VẤN VỚI AI GỢI Ý NGÀY/GIỜ RẢNH (SMART SCHEDULE) -->
<div id="smartScheduleModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 12px; max-width: 580px; width: 100%; padding: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('ai', '', 18) ?> AI Gợi Ý Khung Giờ Phỏng Vấn Rảnh
            </h3>
            <button type="button" id="closeScheduleModalBtn" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <div style="font-size: 0.88rem; color: #475569; margin-bottom: 1rem;">
            Người lao động: <strong id="schedSeekerName" style="color: #0d9488;"></strong>
        </div>

        <!-- KHU VỰC SLOT AI GỢI Ý TRỐNG LỊCH CHỐNG TRÙNG (THANH NGANG TIMELINE) -->
        <div style="margin-bottom: 1.25rem;">
            <div style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
                <span style="display: flex; align-items: center; gap: 0.35rem;">
                    <?= icon('calendar', '', 15) ?>
                    <span>Khung giờ AI đề xuất trống lịch (Bấm mốc để chọn):</span>
                </span>
                <span style="font-size: 0.72rem; color: #0d9488; background: #f0fdfa; border: 1px solid #99f6e4; padding: 2px 8px; border-radius: 999px; font-weight: 600;">
                    Chống trùng lịch
                </span>
            </div>

            <div id="aiSlotsLoading" style="text-align: center; padding: 1.25rem; color: #64748b; font-size: 0.85rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                Đang phân tích lịch phỏng vấn của 2 bên qua AI...
            </div>

            <div id="aiTimelineWrapper" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.85rem 0.75rem; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
                <!-- Khung thanh ngang timeline -->
                <div style="position: relative; padding: 0 0.5rem;">
                    <!-- Đường ray ngang xám -->
                    <div id="aiTimelineBgBar" style="position: absolute; height: 3px; background: #cbd5e1; top: 38px; z-index: 1; border-radius: 2px;"></div>
                    <!-- Đường ray tiến trình đang chọn (active fill) -->
                    <div id="aiTimelineActiveBar" style="position: absolute; height: 3px; background: #0d9488; top: 38px; z-index: 2; border-radius: 2px; transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                    <!-- Các mốc timeline -->
                    <div id="aiTimelineNodes" style="position: relative; z-index: 3; display: flex; justify-content: space-between; align-items: flex-start;"></div>
                </div>

                <!-- Thẻ thông tin chi tiết mốc đang chọn -->
                <div id="aiActiveSlotDetail" style="margin-top: 0.75rem; padding: 0.6rem 0.85rem; background: #ffffff; border: 1px solid #ccfbf1; border-left: 4px solid #0d9488; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.84rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                            <span style="color: #0d9488; font-weight: 800; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('check', '', 13) ?> Mốc đang chọn:</span>
                            <span id="aiActiveSlotLabel" style="color: #0f172a;"></span>
                        </div>
                        <div id="aiActiveSlotReason" style="font-size: 0.75rem; color: #059669; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
                    </div>
                    <span style="font-size: 0.72rem; color: #0f766e; background: #f0fdfa; border: 1px solid #99f6e4; padding: 0.25rem 0.55rem; border-radius: 6px; white-space: nowrap; font-weight: 600; flex-shrink: 0;">
                        Đã tự điền form
                    </span>
                </div>
            </div>

            <!-- Fallback khi không có slot -->
            <div id="aiSlotsEmpty" style="display: none; color: #64748b; font-size: 0.82rem; padding: 0.85rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center;">
                Không có khung giờ rảnh đối chiếu tự động. Vui lòng chọn ngày giờ thủ công bên dưới.
            </div>
        </div>

        <!-- FORM LÊN LỊCH CHÍNH THỨC -->
        <form action="<?= url('/employer/meetings/store') ?>" method="POST" id="scheduleMeetingForm">
            <input type="hidden" name="request_id" id="schedRequestId">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Ngày phỏng vấn *</label>
                    <input type="date" name="meeting_date" id="schedDateInput" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Giờ bắt đầu *</label>
                    <input type="time" name="start_time" id="schedStartTimeInput" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
            </div>

            <div style="margin-bottom: 0.75rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">
                    Khoảng ngày dự kiến phỏng vấn (để người lao động chủ động thời gian chuẩn bị & dời lịch nếu bận)
                </label>
                <input type="text" name="interview_range" class="form-control" placeholder="Ví dụ: <?= date('d/m/Y', strtotime('+1 day')) ?> - <?= date('d/m/Y', strtotime('+4 days')) ?>" value="<?= date('d/m/Y', strtotime('+1 day')) ?> - <?= date('d/m/Y', strtotime('+4 days')) ?>" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">
            </div>

            <div style="margin-bottom: 0.75rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Hình thức phỏng vấn *</label>
                <select name="meeting_format" class="form-control" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <option value="offline">Trực tiếp tại văn phòng / cơ sở</option>
                    <option value="online">Trực tuyến (Google Meet / Zoom / Zalo)</option>
                </select>
            </div>

            <div style="margin-bottom: 0.75rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Địa điểm hoặc Link meeting *</label>
                <input type="text" name="location_or_meeting_link" class="form-control" required value="<?= htmlspecialchars($job['workplace_address'] ?? 'Văn phòng Công ty') ?>" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1;">
            </div>

            <div style="margin-bottom: 0.75rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">
                    Cẩm nang hướng dẫn người lao động chuẩn bị trước buổi phỏng vấn:
                </label>
                <textarea name="prep_guide" rows="2" class="form-control" placeholder="Ví dụ: Tìm hiểu thông tin công ty, chuẩn bị phần tự giới thiệu bản thân, mang theo hồ sơ xin việc..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">Tìm hiểu thông tin công ty và vị trí tuyển dụng. Chuẩn bị phần giới thiệu kinh nghiệm bản thân và mang theo CCCD/bản in thông tin khi đến phỏng vấn.</textarea>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Ghi chú thêm:</label>
                <textarea name="meeting_note" rows="2" class="form-control" placeholder="Mang theo CCCD/VNeID, trang phục lịch sự..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;"></textarea>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="#" id="directChatLink" target="_blank" class="btn btn-outline btn-sm" style="color: #0d9488; border-color: #0d9488; display: inline-flex; align-items: center; gap: 0.25rem;">
                    <?= icon('chat', '', 14) ?> Nhắn tin riêng
                </a>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" id="cancelScheduleModalBtn" class="btn btn-outline btn-sm">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700;">Xác Nhận Lên Lịch</button>
                </div>
            </div>
        </form>
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
                <input type="text" name="workplace" id="acceptWorkplace" class="form-control" required value="<?= htmlspecialchars($job['workplace_address'] ?? '') ?>" placeholder="Địa chỉ cơ sở làm việc, tầng, phòng tiếp nhận..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
            </div>

            <div style="margin-bottom: 0.85rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Người liên hệ tiếp đón / Quản lý trực tiếp *</label>
                <input type="text" name="contact_person" id="acceptContactPerson" class="form-control" required value="<?= htmlspecialchars($employer['employer_name'] ?? 'Phòng Nhân sự') ?> (<?= htmlspecialchars($employer['phone_number'] ?? '') ?>)" placeholder="Họ tên người liên hệ, số điện thoại..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
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
                    <?= icon('check_circle', '', 14) ?> Xác Nhận Nhận Việc & Gửi Cẩm Nang (100%)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. MODAL GỬI LỜI MỜI
    const inviteModal = document.getElementById('inviteCandidateModal');
    const inviteBtns = document.querySelectorAll('.btn-open-invite-modal');
    const closeInviteBtn = document.getElementById('closeInviteModalBtn');
    const cancelInviteBtn = document.getElementById('cancelInviteModalBtn');

    inviteBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalSeekerId').value = this.getAttribute('data-seeker-id');
            document.getElementById('modalJobId').value = this.getAttribute('data-job-id');
            document.getElementById('modalSeekerName').textContent = this.getAttribute('data-seeker-name');
            document.getElementById('modalJobTitle').textContent = this.getAttribute('data-job-title');
            inviteModal.style.display = 'flex';
        });
    });

    if (closeInviteBtn) closeInviteBtn.addEventListener('click', () => inviteModal.style.display = 'none');
    if (cancelInviteBtn) cancelInviteBtn.addEventListener('click', () => inviteModal.style.display = 'none');

    // 2. MODAL CHỐT LỊCH PHỎNG VẤN & AI GỢI Ý SLOT (THANH NGANG TIMELINE)
    const schedModal = document.getElementById('smartScheduleModal');
    const schedBtns = document.querySelectorAll('.btn-open-schedule-modal');
    const closeSchedBtn = document.getElementById('closeScheduleModalBtn');
    const cancelSchedBtn = document.getElementById('cancelScheduleModalBtn');
    const aiTimelineWrapper = document.getElementById('aiTimelineWrapper');
    const aiSlotsLoading = document.getElementById('aiSlotsLoading');
    const aiSlotsEmpty = document.getElementById('aiSlotsEmpty');

    function renderAiTimeline(slots) {
        const nodesContainer = document.getElementById('aiTimelineNodes');
        const bgBar = document.getElementById('aiTimelineBgBar');
        const activeBar = document.getElementById('aiTimelineActiveBar');
        const activeLabel = document.getElementById('aiActiveSlotLabel');
        const activeReason = document.getElementById('aiActiveSlotReason');
        const dateInput = document.getElementById('schedDateInput');
        const timeInput = document.getElementById('schedStartTimeInput');

        if (!aiTimelineWrapper || !nodesContainer) return;

        aiTimelineWrapper.style.display = 'block';
        if (aiSlotsEmpty) aiSlotsEmpty.style.display = 'none';
        nodesContainer.innerHTML = '';

        const total = slots.length;
        const halfCol = (100 / total) / 2;
        const trackWidth = 100 - (halfCol * 2);

        if (bgBar) {
            bgBar.style.left = halfCol + '%';
            bgBar.style.width = trackWidth + '%';
        }
        if (activeBar) {
            activeBar.style.left = halfCol + '%';
        }

        let activeIndex = 0;

        function selectMilestone(idx) {
            activeIndex = idx;
            const curSlot = slots[idx];

            if (activeBar) {
                const activeWidth = total > 1 ? (idx / (total - 1)) * trackWidth : 0;
                activeBar.style.width = activeWidth + '%';
            }

            if (activeLabel) activeLabel.textContent = curSlot.label;
            if (activeReason) activeReason.textContent = curSlot.ai_reason || 'Hoàn toàn trống lịch cho cả NTD và Người lao động';

            if (dateInput) dateInput.value = curSlot.date;
            if (timeInput) timeInput.value = curSlot.start_time;

            const nodeEls = nodesContainer.querySelectorAll('.ai-milestone-node');
            nodeEls.forEach((node, i) => {
                const isSel = (i === idx);
                const circle = node.querySelector('.ai-milestone-circle');
                const timeText = node.querySelector('.ai-milestone-time');
                const badge = node.querySelector('.ai-milestone-badge');

                if (isSel) {
                    circle.style.background = '#0d9488';
                    circle.style.borderColor = '#0d9488';
                    circle.style.color = '#ffffff';
                    circle.style.boxShadow = '0 0 0 4px rgba(13, 148, 136, 0.25)';
                    circle.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
                    timeText.style.color = '#0d9488';
                    timeText.style.fontWeight = '800';
                    if (badge) {
                        badge.style.background = '#0d9488';
                        badge.style.color = '#ffffff';
                    }
                } else {
                    circle.style.background = '#ffffff';
                    circle.style.borderColor = '#cbd5e1';
                    circle.style.color = '#475569';
                    circle.style.boxShadow = 'none';
                    circle.textContent = (i + 1);
                    timeText.style.color = '#334155';
                    timeText.style.fontWeight = '700';
                    if (badge) {
                        badge.style.background = '#e2e8f0';
                        badge.style.color = '#475569';
                    }
                }
            });
        }

        slots.forEach((slot, index) => {
            const node = document.createElement('div');
            node.className = 'ai-milestone-node';
            node.style.cssText = 'flex: 1; display: flex; flex-direction: column; align-items: center; cursor: pointer; text-align: center; user-select: none; padding: 0 4px;';

            let dayConcise = slot.day_title || '';
            if (dayConcise.includes('Ngày mai')) {
                dayConcise = slot.session + ' mai';
            } else {
                const m = dayConcise.match(/^(Thứ\s+[^\s(]+)/i);
                dayConcise = slot.session + (m ? ' · ' + m[1] : '');
            }

            node.innerHTML = `
                <div class="ai-milestone-time" style="font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 6px; white-space: nowrap; transition: all 0.2s;">
                    ${slot.start_time} - ${slot.end_time}
                </div>
                <div class="ai-milestone-circle" style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; border: 2px solid #cbd5e1; background: #ffffff; color: #475569; z-index: 4; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); margin-bottom: 6px;">
                    ${index + 1}
                </div>
                <div style="font-size: 0.74rem; font-weight: 600; color: #475569; line-height: 1.2; white-space: nowrap;">
                    ${dayConcise}
                </div>
                <span class="ai-milestone-badge" style="display: inline-block; margin-top: 4px; font-size: 0.67rem; font-weight: 600; padding: 1px 6px; border-radius: 999px; background: #e2e8f0; color: #475569; transition: all 0.2s;">
                    Mốc ${index + 1}
                </span>
            `;

            node.addEventListener('mouseenter', () => {
                if (activeIndex !== index) {
                    const c = node.querySelector('.ai-milestone-circle');
                    c.style.borderColor = '#0d9488';
                    c.style.transform = 'scale(1.08)';
                }
            });

            node.addEventListener('mouseleave', () => {
                if (activeIndex !== index) {
                    const c = node.querySelector('.ai-milestone-circle');
                    c.style.borderColor = '#cbd5e1';
                    c.style.transform = 'scale(1)';
                }
            });

            node.addEventListener('click', () => {
                selectMilestone(index);
            });

            nodesContainer.appendChild(node);
        });

        // Tự động chọn mốc đầu tiên
        selectMilestone(0);
    }

    schedBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const reqId = this.getAttribute('data-request-id');
            const seekerId = this.getAttribute('data-seeker-id');
            const seekerName = this.getAttribute('data-seeker-name');

            document.getElementById('schedRequestId').value = reqId;
            document.getElementById('schedSeekerName').textContent = seekerName;
            document.getElementById('directChatLink').href = '<?= url('/employer/messages') ?>';

            schedModal.style.display = 'flex';
            if (aiSlotsLoading) aiSlotsLoading.style.display = 'block';
            if (aiTimelineWrapper) aiTimelineWrapper.style.display = 'none';
            if (aiSlotsEmpty) aiSlotsEmpty.style.display = 'none';

            // Gọi API lấy các slot rảnh AI
            fetch('<?= url('/api/ai/suggest-meeting-slots') ?>?request_id=' + reqId + '&seeker_id=' + seekerId)
                .then(res => res.json())
                .then(data => {
                    if (aiSlotsLoading) aiSlotsLoading.style.display = 'none';
                    if (data.slots && data.slots.length > 0) {
                        renderAiTimeline(data.slots);
                    } else {
                        if (aiSlotsEmpty) aiSlotsEmpty.style.display = 'block';
                    }
                })
                .catch(err => {
                    if (aiSlotsLoading) aiSlotsLoading.style.display = 'none';
                    if (aiSlotsEmpty) {
                        aiSlotsEmpty.style.display = 'block';
                        aiSlotsEmpty.textContent = 'Không thể tải gợi ý AI lúc này. Vui lòng chọn ngày giờ thủ công.';
                    }
                });
        });
    });

    if (closeSchedBtn) closeSchedBtn.addEventListener('click', () => schedModal.style.display = 'none');
    if (cancelSchedBtn) cancelSchedBtn.addEventListener('click', () => schedModal.style.display = 'none');

    // 3. MODAL DUYỆT NHẬN VIỆC (ONBOARDING)
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
require_once __DIR__ . '/../layouts/user_layout.php';
?>