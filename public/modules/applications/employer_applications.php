<?php 
$extraCss = ['css/applications/employer_applications.css', 'css/jobs/ai_matching.css'];
$extraJs = 'js/applications/applications.js';

// Danh sách các tin tuyển dụng của NTD để làm bộ lọc
$employerJobsList = [];
if (!empty($employer['employer_id'])) {
    $employerJobsList = JobPost::getByEmployer($employer['employer_id']);
}

$filterJobId = intval($_GET['job_id'] ?? 0);
$filterStatus = trim($_GET['status'] ?? 'all');

$filteredApplications = array_filter($applications, function($app) use ($filterJobId, $filterStatus) {
    if ($filterJobId > 0 && intval($app['job_post_id']) !== $filterJobId) {
        return false;
    }
    if ($filterStatus !== 'all') {
        if ($filterStatus === 'pending' && $app['request_status'] !== 'pending') return false;
        if ($filterStatus === 'accepted' && !in_array($app['request_status'], ['accepted', 'invitation_accepted'])) return false;
        if ($filterStatus === 'invited' && $app['request_status'] !== 'invited') return false;
        if ($filterStatus === 'rejected' && !in_array($app['request_status'], ['rejected', 'invitation_rejected'])) return false;
    }
    return true;
});

ob_start(); 
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Quản Lý Hồ Sơ Ứng Tuyển (Xem Chung)</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem;">Tổng hợp toàn bộ người lao động nộp hồ sơ hoặc nhận lời mời từ tất cả các vị trí tuyển dụng</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= url('/employer/jobs') ?>" class="btn btn-outline" style="font-weight: 600;">
            <?= icon('list', '', 15) ?> Xem Theo Từng Tin Tuyển Dụng (Riêng)
        </a>
    </div>
</div>

<!-- BỘ LỌC TỔNG HỢP (FILTER BAR) -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #fff;">
    <form method="GET" action="<?= url('/employer/applications') ?>" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 220px;">
            <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Lọc theo công việc:</label>
            <select name="job_id" class="form-control" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                <option value="0">-- Tất cả vị trí công việc (<?= count($applications) ?>) --</option>
                <?php foreach ($employerJobsList as $j): ?>
                    <option value="<?= $j['job_post_id'] ?>" <?= $filterJobId === intval($j['job_post_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($j['job_title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="min-width: 180px;">
            <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Trạng thái hồ sơ:</label>
            <select name="status" class="form-control" onchange="this.form.submit()" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="accepted" <?= $filterStatus === 'accepted' ? 'selected' : '' ?>>Đã duyệt nhận</option>
                <option value="invited" <?= $filterStatus === 'invited' ? 'selected' : '' ?>>Lời mời từ NTD</option>
                <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Đã từ chối</option>
            </select>
        </div>

        <?php if ($filterJobId > 0 || $filterStatus !== 'all'): ?>
            <div style="align-self: flex-end;">
                <a href="<?= url('/employer/applications') ?>" class="btn btn-outline btn-sm" style="padding: 0.5rem 0.75rem;">
                    Xóa bộ lọc
                </a>
            </div>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Người lao động</th>
                    <th>Vị trí ứng tuyển</th>
                    <th>Độ phù hợp AI</th>
                    <th>Email & SĐT</th>
                    <th>Ngày nộp</th>
                    <th>Trạng thái</th>
                    <th style="min-width: 180px;">Xử lý</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filteredApplications)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--slate-400); padding: 2.5rem;">Không tìm thấy hồ sơ nào phù hợp với bộ lọc.</td></tr>
                <?php else: ?>
                    <?php foreach ($filteredApplications as $app): ?>
                        <tr>
                            <td style="min-width: 200px;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <img src="<?= htmlspecialchars(!empty($app['seeker_avatar']) ? $app['seeker_avatar'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150') ?>" alt="avatar" style="width: 40px; height: 40px; border-radius: var(--radius-full); object-fit: cover; border: 1px solid var(--slate-200); flex-shrink: 0;">
                                    <div>
                                        <a href="<?= url('/employer/candidates/' . $app['seeker_id']) ?>" style="font-weight: 700; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 0.3rem;" title="Bấm để xem hồ sơ & đánh giá năng lực người lao động">
                                            <span><?= htmlspecialchars($app['seeker_name']) ?></span>
                                            <span style="font-size: 0.75rem; color: #0d9488;">&rarr;</span>
                                        </a>
                                        <span style="font-size: 0.78rem; color: #64748b;">Mã NLĐ: <?= formatId('seeker', $app['seeker_id']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="color: #334155;">
                                <strong>
                                    <a href="<?= url('/employer/jobs/' . $app['job_post_id'] . '/candidates') ?>" style="color: #0f172a; text-decoration: none;">
                                        <?= htmlspecialchars($app['job_title']) ?>
                                    </a>
                                </strong>
                                <?php if (!empty($app['available_work_dates'])): ?>
                                    <br><span style="font-size: 0.8rem; color: #0d9488; font-weight: 600; background: #f0fdfa; border: 1px solid #ccfbf1; padding: 0.15rem 0.45rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.3rem;">
                                        <?= icon('calendar', '', 12) ?> <span><?= htmlspecialchars($app['available_work_dates']) ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($app['ai_match_score'])): ?>
                                    <span class="ai-match-badge <?= $app['ai_match_level'] ?? 'medium' ?>" style="font-size: 0.75rem; padding: 0.25rem 0.6rem; display: inline-flex; align-items: center; gap: 0.25rem;">
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
                                <span style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($app['phone_number']) ?></span><br>
                                <span style="font-size: 0.82rem; color: var(--slate-500);"><?= htmlspecialchars($app['email']) ?></span>
                            </td>
                            <td style="color: #475569; font-size: 0.82rem;"><?= date('d/m/Y H:i', strtotime($app['applied_at'])) ?></td>
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
                                    <span class="badge badge-success" style="font-size: 0.78rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('check', '', 13) ?> <span>100% Đã nhận việc</span>
                                    </span>
                                    <?php if (!empty($app['onboarding_details'])): ?>
                                        <div style="margin-top: 0.25rem;">
                                            <button type="button" class="btn btn-outline btn-sm btn-view-onboarding" 
                                                    data-details='<?= htmlspecialchars($app['onboarding_details'], ENT_QUOTES, 'UTF-8') ?>'
                                                    data-seeker-name="<?= htmlspecialchars($app['seeker_name']) ?>"
                                                    style="padding: 0.15rem 0.4rem; font-size: 0.72rem; color: #0d9488; border-color: #99f6e4; background: #f0fdfa; display: inline-flex; align-items: center; gap: 0.2rem;">
                                                <?= icon('file_text', '', 12) ?> Xem hành trang
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($isRejected): ?>
                                    <span class="badge badge-danger" style="font-size: 0.78rem; padding: 0.25rem 0.5rem;">
                                        <span>Đã từ chối (Hẹn dịp sau)</span>
                                    </span>
                                <?php elseif ($isPastMeeting): ?>
                                    <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.78rem; padding: 0.25rem 0.5rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('target', '', 12) ?> <span>75% Chờ kết quả PV</span>
                                    </span>
                                    <?php if ($isOverdue): ?>
                                        <div style="font-size: 0.72rem; color: #b91c1c; font-weight: 700; margin-top: 0.25rem; background: #fef2f2; border: 1px solid #fecaca; padding: 0.15rem 0.4rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.2rem;">
                                            <?= icon('alert_triangle', '', 11) ?> Quá hạn phản hồi (>3 ngày)
                                        </div>
                                    <?php elseif ($daysLeft !== null): ?>
                                        <div style="font-size: 0.72rem; color: #d97706; font-weight: 600; margin-top: 0.25rem; background: #fffbeb; border: 1px solid #fde68a; padding: 0.15rem 0.4rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.2rem;">
                                            <?= icon('clock', '', 11) ?> Còn <?= $daysLeft > 0 ? $daysLeft . ' ngày' : 'hôm nay' ?> phản hồi
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($hasMeeting): ?>
                                    <span class="badge badge-primary" style="font-size: 0.78rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('calendar', '', 12) ?> <span>50% Đã hẹn PV (<?= date('d/m', strtotime($app['meeting_date'])) ?>)</span>
                                    </span>
                                    <?php if (!empty($app['interview_range'])): ?>
                                        <div style="font-size: 0.72rem; color: #0d9488; margin-top: 0.2rem;">
                                            Khung: <?= htmlspecialchars($app['interview_range']) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($app['request_status'] === 'invited'): ?>
                                    <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:600; font-size:0.78rem;"><span>Đã gửi lời mời</span></span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.78rem; padding: 0.25rem 0.5rem;"><span>25% Chờ duyệt hồ sơ</span></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                                    <a href="<?= url('/employer/candidates/' . $app['seeker_id']) ?>" class="btn btn-outline btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem; font-weight: 700; color: #0d9488; border-color: #0d9488; background: #fff;" title="Xem chi tiết hồ sơ">
                                        <?= icon('user', '', 12) ?> Hồ Sơ
                                    </a>

                                    <?php if ($isAccepted): ?>
                                        <a href="<?= url('/employer/messages?partner_id=' . ($app['seeker_account_id'] ?? 0)) ?>" class="btn btn-outline btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem; font-weight: 600; color: #0284c7; border-color: #bae6fd; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <?= icon('chat', '', 12) ?> Chat
                                        </a>
                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="pending">
                                            <button type="button" class="btn btn-warning btn-sm" data-reject-prompt="Lý Do Hủy Duyệt Hồ Sơ Ứng Tuyển" style="padding: 0.3rem 0.55rem; font-size: 0.78rem;">
                                                Hủy
                                            </button>
                                        </form>
                                    <?php elseif ($isRejected): ?>
                                        <button type="button" class="btn btn-success btn-sm btn-open-accept-modal" 
                                                data-request-id="<?= $app['request_id'] ?>"
                                                data-seeker-name="<?= htmlspecialchars($app['seeker_name']) ?>"
                                                data-job-title="<?= htmlspecialchars($app['job_title']) ?>"
                                                data-workplace="<?= htmlspecialchars($app['workplace_address'] ?? '') ?>"
                                                style="padding: 0.3rem 0.55rem; font-size: 0.78rem; font-weight: 700;">
                                            Duyệt Lại
                                        </button>
                                    <?php else: ?>
                                        <!-- Nút Duyệt Nhận Việc mở Modal Onboarding Hành trang buổi đầu -->
                                        <button type="button" class="btn btn-success btn-sm btn-open-accept-modal" 
                                                data-request-id="<?= $app['request_id'] ?>"
                                                data-seeker-name="<?= htmlspecialchars($app['seeker_name']) ?>"
                                                data-job-title="<?= htmlspecialchars($app['job_title']) ?>"
                                                data-workplace="<?= htmlspecialchars($app['workplace_address'] ?? '') ?>"
                                                style="padding: 0.3rem 0.55rem; font-size: 0.78rem; font-weight: 700; background: #059669; border-color: #059669;"
                                                title="Duyệt nhận việc và gửi cẩm nang hành trang buổi đầu tiên đi làm">
                                            <?= icon('check', '', 12) ?> Duyệt Nhận Việc
                                        </button>

                                        <?php if (!$hasMeeting): ?>
                                            <a href="<?= url('/employer/messages?partner_id=' . ($app['account_id'] ?? 0) . '&seeker_id=' . ($app['seeker_id'] ?? 0) . '&request_id=' . $app['request_id'] . '&job_id=' . $app['job_post_id'] . '&open_meeting_modal=1') ?>" class="btn btn-primary btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                                <?= icon('calendar', '', 13) ?> Hẹn Lịch PV
                                            </a>
                                        <?php endif; ?>

                                        <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="button" class="btn btn-outline btn-sm" data-reject-prompt="Lý Do Từ Chối Hồ Sơ (Hệ thống sẽ gửi thư cảm ơn và lưu hồ sơ hẹn dịp sau)" style="color: var(--danger); border-color: #fecaca; padding: 0.3rem 0.55rem; font-size: 0.78rem;">
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
<!-- MODAL DUYỆT NHẬN VIỆC & THIẾT LẬP HÀNH TRANG BUỔI ĐẦU ĐI LÀM (ONBOARDING) -->
<!-- ========================================================================= -->
<div id="acceptOnboardingModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; justify-content: center; align-items: center; padding: 1rem; backdrop-filter: blur(4px);">
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

<!-- ========================================================================= -->
<!-- MODAL XEM CHI TIẾT ONBOARDING ĐÃ GỬI                                      -->
<!-- ========================================================================= -->
<div id="viewOnboardingModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; justify-content: center; align-items: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 550px; max-height: 85vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; font-weight: 700; display: flex; align-items: center; gap: 0.35rem;">
                <?= icon('file_text', '', 16) ?> Cẩm Nang Ngày Đầu Đã Gửi Người Lao Động
            </h3>
            <button type="button" id="closeViewOnboardingBtn" style="background: none; border: none; font-size: 1.35rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <div id="viewOnboardingBody" style="font-size: 0.88rem; line-height: 1.6; color: #334155;"></div>
        <div style="text-align: right; margin-top: 1.25rem;">
            <button type="button" id="dismissViewOnboardingBtn" class="btn btn-outline btn-sm">Đóng</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. MODAL DUYỆT NHẬN VIỆC (ONBOARDING)
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

    // 2. MODAL XEM HÀNH TRANG ONBOARDING ĐÃ GỬI
    const viewModal = document.getElementById('viewOnboardingModal');
    const viewBody = document.getElementById('viewOnboardingBody');
    const viewBtns = document.querySelectorAll('.btn-view-onboarding');
    const closeViewBtn = document.getElementById('closeViewOnboardingBtn');
    const dismissViewBtn = document.getElementById('dismissViewOnboardingBtn');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            try {
                const details = JSON.parse(this.getAttribute('data-details'));
                const seekerName = this.getAttribute('data-seeker-name');
                let html = `
                    <div style="margin-bottom: 0.75rem;"><strong>Người lao động:</strong> ${seekerName}</div>
                    <div style="margin-bottom: 0.5rem;"><strong>Ngày bắt đầu:</strong> ${details.start_date || 'Chưa xác định'} lúc <strong>${details.start_time || '08:30'}</strong></div>
                    <div style="margin-bottom: 0.5rem;"><strong>Địa điểm làm việc:</strong> ${details.workplace || 'Tại văn phòng công ty'}</div>
                    <div style="margin-bottom: 0.75rem;"><strong>Người tiếp đón:</strong> ${details.contact_person || 'Phòng Nhân sự'}</div>
                `;

                if (details.checklist && details.checklist.length > 0) {
                    html += `<div style="font-weight: 700; margin-top: 0.75rem; margin-bottom: 0.35rem;">Danh sách hành trang yêu cầu:</div><ul style="margin: 0; padding-left: 1.25rem;">`;
                    details.checklist.forEach(item => {
                        html += `<li>${item}</li>`;
                    });
                    html += `</ul>`;
                }

                if (details.notes) {
                    html += `<div style="margin-top: 0.75rem; background: #f8fafc; padding: 0.6rem; border-radius: 6px; border: 1px solid #e2e8f0;"><strong>Ghi chú & Trang phục:</strong> ${details.notes}</div>`;
                }

                viewBody.innerHTML = html;
                viewModal.style.display = 'flex';
            } catch (e) {
                console.error(e);
            }
        });
    });

    if (closeViewBtn) closeViewBtn.addEventListener('click', () => viewModal.style.display = 'none');
    if (dismissViewBtn) dismissViewBtn.addEventListener('click', () => viewModal.style.display = 'none');
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
