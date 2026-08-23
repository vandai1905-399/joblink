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
                &larr; Quay lại danh sách tin
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
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
            📍 <?= htmlspecialchars($job['workplace_address']) ?> · 💰 <?= formatSalary($job['salary_amount'], $job['employment_type']) ?> · 👥 Tuyển: <?= $job['number_of_positions'] ?> người · 📅 Hạn: <?= date('d/m/Y', strtotime($job['application_deadline'])) ?>
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

<!-- TAB NAVIGATION: ỨNG VIÊN ĐÃ NỘP vs AI ĐỀ XUẤT NGƯỜI LAO ĐỘNG -->
<div style="display: flex; gap: 0.5rem; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <a href="?tab=applicants" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeSubTab === 'applicants' ? '#0d9488' : 'transparent' ?>; background: <?= $activeSubTab === 'applicants' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeSubTab === 'applicants' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('applications', '', 16) ?> <span>1. Ứng Viên Đã Nộp Hồ Sơ (<?= count($applications) ?>)</span>
    </a>
    <a href="?tab=ai_recommendations" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeSubTab === 'ai_recommendations' ? '#0d9488' : 'transparent' ?>; background: <?= $activeSubTab === 'ai_recommendations' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeSubTab === 'ai_recommendations' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('zap', '', 16) ?> <span>2. ✨ AI Đề Xuất Người Lao Động Phù Hợp (<?= count($aiRecommendations) ?>)</span>
    </a>
</div>

<!-- ========================================================================= -->
<!-- TAB 1: ỨNG VIÊN ĐÃ NỘP HỒ SƠ CHO TIN NÀY                                 -->
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
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📭</div>
                                <div style="font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Chưa có người lao động nào nộp hồ sơ vào tin này</div>
                                <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 1rem;">Bạn có thể chuyển qua tab <strong>"✨ AI Đề Xuất Người Lao Động"</strong> để chủ động gửi lời mời làm việc ngay!</div>
                                <a href="?tab=ai_recommendations" class="btn btn-primary btn-sm">Xem danh sách ứng viên phù hợp</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td style="min-width: 190px;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <img src="<?= htmlspecialchars(!empty($app['seeker_avatar']) ? $app['seeker_avatar'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150') ?>" alt="avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #e2e8f0; flex-shrink: 0;">
                                        <div>
                                            <a href="<?= url('/employer/candidates/' . $app['seeker_id']) ?>" style="font-weight: 700; color: #0d9488; text-decoration: none; display: flex; align-items: center; gap: 0.25rem;">
                                                <span><?= htmlspecialchars($app['seeker_name']) ?></span>
                                                <span style="font-size: 0.75rem;">&rarr;</span>
                                            </a>
                                            <div style="font-size: 0.75rem; color: #64748b;">
                                                <?= htmlspecialchars($app['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($app['date_of_birth']) ? (date('Y') - date('Y', strtotime($app['date_of_birth']))) . ' tuổi' : '' ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (isset($app['ai_match_score'])): ?>
                                        <span class="ai-match-badge <?= $app['ai_match_level'] ?? 'medium' ?>" style="font-size: 0.75rem; padding: 0.25rem 0.55rem;">
                                            ✨ <?= $app['ai_match_score'] ?>%
                                        </span>
                                        <?php if (!empty($app['ai_matched_skills'])): ?>
                                            <div style="font-size: 0.72rem; color: #059669; margin-top: 0.25rem; max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars(implode(', ', $app['ai_matched_skills'])) ?>">
                                                ✓ <?= htmlspecialchars(implode(', ', array_slice($app['ai_matched_skills'], 0, 2))) ?>
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
                                    <?php if ($app['request_status'] === 'accepted' || $app['request_status'] === 'invitation_accepted'): ?>
                                        <span class="badge badge-success" style="font-size: 0.75rem;"><?= icon('check', '', 12) ?> Đã duyệt nhận</span>
                                    <?php elseif ($app['request_status'] === 'invited'): ?>
                                        <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:600; font-size:0.75rem;">Đã gửi lời mời</span>
                                    <?php elseif ($app['request_status'] === 'rejected' || $app['request_status'] === 'invitation_rejected'): ?>
                                        <span class="badge badge-danger" style="font-size: 0.75rem;">Đã từ chối</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="font-size: 0.75rem;">Chờ duyệt</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                                        <a href="<?= url('/employer/candidates/' . $app['seeker_id']) ?>" class="btn btn-outline btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem; color: #0d9488; border-color: #0d9488;">
                                            <?= icon('user', '', 12) ?> Hồ Sơ
                                        </a>

                                        <?php if ($app['request_status'] === 'accepted' || $app['request_status'] === 'invitation_accepted'): ?>
                                            <!-- Khi đã duyệt: Nút Nhắn tin riêng & Chốt lịch AI -->
                                            <a href="<?= url('/employer/messages?partner_id=' . ($app['seeker_account_id'] ?? 0)) ?>" class="btn btn-outline btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem; font-weight: 700; color: #0d9488; border-color: #0d9488;" title="Mở khung chat trực tiếp">
                                                💬 Chat
                                            </a>
                                            <button type="button" class="btn btn-primary btn-sm btn-open-schedule-modal" 
                                                    data-request-id="<?= $app['request_id'] ?>" 
                                                    data-seeker-id="<?= $app['seeker_id'] ?>" 
                                                    data-seeker-name="<?= htmlspecialchars($app['seeker_name']) ?>"
                                                    data-seeker-account-id="<?= $app['seeker_account_id'] ?>"
                                                    style="padding: 0.3rem 0.6rem; font-size: 0.78rem; background: #0d9488; border-color: #0d9488; font-weight: 700;">
                                                📅 Chốt Lịch (AI)
                                            </button>
                                        <?php elseif ($app['request_status'] === 'pending'): ?>
                                            <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                                <input type="hidden" name="status" value="accepted">
                                                <button type="submit" class="btn btn-success btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem;">
                                                    Duyệt
                                                </button>
                                            </form>
                                            <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="button" class="btn btn-danger btn-sm" data-reject-prompt="Lý Do Từ Chối Ứng Viên" style="padding: 0.3rem 0.55rem; font-size: 0.78rem;">
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
<!-- TAB 2: ✨ AI ĐỀ XUẤT NGƯỜI LAO ĐỘNG PHÙ HỢP (2 CHIỀU)                   -->
<!-- ========================================================================= -->
<?php else: ?>
    <div style="background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-size: 1.5rem;">🤖</span>
        <div style="font-size: 0.88rem; color: #134e4a;">
            <strong>Cơ Chế Tuyển Dụng 2 Chiều Thông Minh:</strong> Hệ thống AI tự động phân tích và xếp hạng người lao động có kỹ năng, độ tuổi, kinh nghiệm và vị trí địa lý tương thích cao nhất với tin <em>"<?= htmlspecialchars($job['job_title']) ?>"</em>. Doanh nghiệp có thể bấm <strong>"Mời Nhận Việc"</strong> để gửi lời mời trực tiếp đến người lao động!
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem;">
        <?php if (empty($aiRecommendations)): ?>
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #64748b;">
                Chưa có dữ liệu ứng viên phù hợp trong hệ thống.
            </div>
        <?php else: ?>
            <?php foreach ($aiRecommendations as $rec): ?>
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border: 1px solid <?= ($rec['ai_match_score'] >= 85) ? '#99f6e4' : '#e2e8f0' ?>; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                    <div>
                        <!-- Header Card: Avatar & Tên & AI Score -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                            <div style="display: flex; gap: 0.75rem; align-items: center;">
                                <img src="<?= htmlspecialchars(!empty($rec['avatar_url']) ? $rec['avatar_url'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150') ?>" alt="avatar" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid #0d9488;">
                                <div>
                                    <a href="<?= url('/employer/candidates/' . $rec['seeker_id']) ?>" style="font-weight: 700; font-size: 1rem; color: #0f172a; text-decoration: none;">
                                        <?= htmlspecialchars($rec['full_name']) ?>
                                    </a>
                                    <div style="font-size: 0.78rem; color: #64748b;">
                                        <?= htmlspecialchars($rec['gender'] ?? 'Nam/Nữ') ?> · <?= !empty($rec['date_of_birth']) ? (date('Y') - date('Y', strtotime($rec['date_of_birth']))) . ' tuổi' : '' ?> · <?= intval($rec['years_of_experience'] ?? 0) ?> năm KN
                                    </div>
                                </div>
                            </div>
                            <span class="ai-match-badge <?= $rec['ai_match_level'] ?? 'medium' ?>" style="font-size: 0.8rem; padding: 0.3rem 0.6rem;">
                                ✨ <?= $rec['ai_match_score'] ?>%
                            </span>
                        </div>

                        <!-- Điểm uy tín & Khu vực -->
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                            <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.75rem;">
                                ⭐ Trust Score: <strong><?= $rec['trust_score'] ?? 80 ?>/100</strong>
                            </span>
                            <?php if (!empty($rec['address'])): ?>
                                <span class="badge" style="background: #f8fafc; color: #64748b; font-size: 0.75rem;">
                                    📍 <?= htmlspecialchars($rec['address']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Kỹ năng trùng khớp -->
                        <?php if (!empty($rec['ai_matched_skills'])): ?>
                            <div style="margin-bottom: 0.75rem;">
                                <div style="font-size: 0.75rem; color: #059669; font-weight: 700; margin-bottom: 0.25rem;">
                                    ✓ Kỹ năng đáp ứng tốt:
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
                            Xem hồ sơ &rarr;
                        </a>

                        <?php if ($rec['request_status'] === 'invited'): ?>
                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 600; font-size: 0.78rem;">
                                ⏳ Đã gửi lời mời
                            </span>
                        <?php elseif ($rec['request_status'] === 'accepted' || $rec['request_status'] === 'invitation_accepted'): ?>
                            <span class="badge badge-success" style="font-size: 0.78rem;">
                                ✓ Đã kết nối thành công
                            </span>
                        <?php elseif ($rec['request_status'] === 'pending'): ?>
                            <span class="badge badge-warning" style="font-size: 0.78rem;">
                                📩 Ứng viên đã nộp hồ sơ
                            </span>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary btn-sm btn-open-invite-modal"
                                    data-seeker-id="<?= $rec['seeker_id'] ?>"
                                    data-seeker-name="<?= htmlspecialchars($rec['full_name']) ?>"
                                    data-job-id="<?= $job['job_post_id'] ?>"
                                    data-job-title="<?= htmlspecialchars($job['job_title']) ?>"
                                    style="padding: 0.35rem 0.75rem; font-size: 0.82rem; font-weight: 700;">
                                🎯 Mời Nhận Việc
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
                <span>🎯</span> Gửi Lời Mời Nhận Việc
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
                <span>✨</span> AI Gợi Ý Khung Giờ Phỏng Vấn Rảnh
            </h3>
            <button type="button" id="closeScheduleModalBtn" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <div style="font-size: 0.88rem; color: #475569; margin-bottom: 1rem;">
            Ứng viên: <strong id="schedSeekerName" style="color: #0d9488;"></strong>
        </div>

        <!-- KHU VỰC SLOT AI GỢI Ý TRỐNG LỊCH CHỐNG TRÙNG -->
        <div style="margin-bottom: 1.25rem;">
            <div style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                <span>🎯 Khung giờ AI đối chiếu trống lịch cho cả 2 bên (Bấm 1 chạm để chọn):</span>
            </div>
            <div id="aiSlotsLoading" style="text-align: center; padding: 1rem; color: #64748b; font-size: 0.85rem;">
                🤖 Đang phân tích lịch phỏng vấn của 2 bên...
            </div>
            <div id="aiSlotsContainer" style="display: flex; flex-direction: column; gap: 0.5rem;"></div>
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

            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Ghi chú / Hướng dẫn ứng viên:</label>
                <textarea name="meeting_note" rows="2" class="form-control" placeholder="Mang theo CCCD/VNeID, hồ sơ gốc..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;"></textarea>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="#" id="directChatLink" target="_blank" class="btn btn-outline btn-sm" style="color: #0d9488; border-color: #0d9488;">
                    💬 Nhắn tin riêng
                </a>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" id="cancelScheduleModalBtn" class="btn btn-outline btn-sm">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700;">Xác Nhận Lên Lịch</button>
                </div>
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

    // 2. MODAL CHỐT LỊCH PHỎNG VẤN & AI GỢI Ý SLOT
    const schedModal = document.getElementById('smartScheduleModal');
    const schedBtns = document.querySelectorAll('.btn-open-schedule-modal');
    const closeSchedBtn = document.getElementById('closeScheduleModalBtn');
    const cancelSchedBtn = document.getElementById('cancelScheduleModalBtn');
    const aiSlotsContainer = document.getElementById('aiSlotsContainer');
    const aiSlotsLoading = document.getElementById('aiSlotsLoading');

    schedBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const reqId = this.getAttribute('data-request-id');
            const seekerId = this.getAttribute('data-seeker-id');
            const seekerName = this.getAttribute('data-seeker-name');
            const seekerAccId = this.getAttribute('data-seeker-account-id');

            document.getElementById('schedRequestId').value = reqId;
            document.getElementById('schedSeekerName').textContent = seekerName;
            document.getElementById('directChatLink').href = '<?= url('/employer/messages') ?>';

            schedModal.style.display = 'flex';
            aiSlotsLoading.style.display = 'block';
            aiSlotsContainer.innerHTML = '';

            // Gọi API lấy các slot rảnh AI
            fetch('<?= url('/api/ai/suggest-meeting-slots') ?>?request_id=' + reqId + '&seeker_id=' + seekerId)
                .then(res => res.json())
                .then(data => {
                    aiSlotsLoading.style.display = 'none';
                    if (data.slots && data.slots.length > 0) {
                        data.slots.forEach(slot => {
                            const slotDiv = document.createElement('div');
                            slotDiv.style.cssText = 'background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 6px; padding: 0.6rem 0.85rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;';
                            slotDiv.innerHTML = `
                                <div>
                                    <div style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">${slot.label}</div>
                                    <div style="font-size: 0.75rem; color: #059669;">${slot.ai_reason}</div>
                                </div>
                                <span class="btn btn-sm btn-primary" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">Chọn</span>
                            `;
                            slotDiv.addEventListener('mouseenter', () => slotDiv.style.background = '#ccfbf1');
                            slotDiv.addEventListener('mouseleave', () => slotDiv.style.background = '#f0fdfa');
                            slotDiv.addEventListener('click', () => {
                                document.getElementById('schedDateInput').value = slot.date;
                                document.getElementById('schedStartTimeInput').value = slot.start_time;
                            });
                            aiSlotsContainer.appendChild(slotDiv);
                        });

                        // Tự động điền slot đầu tiên
                        document.getElementById('schedDateInput').value = data.slots[0].date;
                        document.getElementById('schedStartTimeInput').value = data.slots[0].start_time;
                    } else {
                        aiSlotsContainer.innerHTML = '<div style="color: #64748b; font-size: 0.82rem; padding: 0.5rem;">Không có slot trống sẵn, vui lòng nhập thủ công.</div>';
                    }
                })
                .catch(err => {
                    aiSlotsLoading.style.display = 'none';
                    aiSlotsContainer.innerHTML = '<div style="color: #64748b; font-size: 0.82rem;">Không thể tải gợi ý AI lúc này.</div>';
                });
        });
    });

    if (closeSchedBtn) closeSchedBtn.addEventListener('click', () => schedModal.style.display = 'none');
    if (cancelSchedBtn) cancelSchedBtn.addEventListener('click', () => schedModal.style.display = 'none');
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>