<?php 
// Phân loại lời mời từ NTD vs Hồ sơ chủ động nộp
$invitations = [];
$selfApplied = [];

foreach ($appliedJobs as $jobApp) {
    if (in_array($jobApp['request_status'], ['invited', 'invitation_accepted', 'invitation_rejected'])) {
        $invitations[] = $jobApp;
    } else {
        $selfApplied[] = $jobApp;
    }
}

/**
 * Hàm phân tích tiến độ hồ sơ tuyển dụng
 */
function evaluateAppProgress($app) {
    $status = $app['request_status'] ?? 'pending';
    $meetingDate = !empty($app['meeting_date']) ? $app['meeting_date'] : null;
    $meetingStatus = $app['meeting_status'] ?? null;
    $deadline = !empty($app['response_deadline']) ? strtotime($app['response_deadline']) : null;
    $today = strtotime(date('Y-m-d'));

    // 1. Nếu đã Accept
    if (in_array($status, ['accepted', 'invitation_accepted'])) {
        return [
            'percent' => 100,
            'step' => 4,
            'label' => 'Tuyển dụng thành công (100%)',
            'badge_class' => 'badge-success',
            'color' => '#10b981',
            'state_type' => 'accepted'
        ];
    }

    // 2. Nếu đã Reject
    if (in_array($status, ['rejected', 'invitation_rejected'])) {
        return [
            'percent' => 100,
            'step' => 4,
            'label' => 'Đã hoàn tất xét duyệt (Chưa phù hợp)',
            'badge_class' => 'badge-danger',
            'color' => '#ef4444',
            'state_type' => 'rejected'
        ];
    }

    // 3. Nếu đã có lịch hẹn phỏng vấn
    if (!empty($meetingDate)) {
        $mTime = strtotime($meetingDate);
        $isPastMeeting = ($mTime < $today) || ($meetingStatus === 'completed');

        // Nếu đã qua ngày phỏng vấn hoặc hoàn thành phỏng vấn -> Tiến độ 75%
        if ($isPastMeeting) {
            $isOverdue = false;
            $daysLeft = null;
            if (!$deadline) {
                // Mặc định 3 ngày sau ngày PV
                $deadline = strtotime($meetingDate . ' +3 days 23:59:59');
            }
            $now = time();
            if ($now > $deadline) {
                $isOverdue = true;
            } else {
                $daysLeft = ceil(($deadline - $now) / 86400);
            }

            return [
                'percent' => 75,
                'step' => 3,
                'label' => 'Đã phỏng vấn xong (75%) - Chờ kết quả',
                'badge_class' => 'badge-primary',
                'color' => '#0d9488',
                'state_type' => 'interview_completed',
                'deadline' => $deadline,
                'is_overdue' => $isOverdue,
                'days_left' => $daysLeft
            ];
        } else {
            // Đã lên lịch phỏng vấn sắp tới -> Tiến độ 50%
            $daysToPrep = ceil(($mTime - $today) / 86400);
            return [
                'percent' => 50,
                'step' => 2,
                'label' => 'Đã lên lịch PV (50%) - Đang chuẩn bị',
                'badge_class' => 'badge-warning',
                'color' => '#f59e0b',
                'state_type' => 'interview_scheduled',
                'days_to_prep' => $daysToPrep
            ];
        }
    }

    // 4. Mới nộp hồ sơ, chờ duyệt -> Tiến độ 25%
    return [
        'percent' => 25,
        'step' => 1,
        'label' => 'Đã nộp hồ sơ (25%) - Chờ xét duyệt',
        'badge_class' => 'badge-slate',
        'color' => '#64748b',
        'state_type' => 'pending'
    ];
}

ob_start(); 
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Tiến Độ Tuyển Dụng & Lời Mời Việc Làm</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Theo dõi trạng thái các vị trí bạn đã nộp đơn và phản hồi các lời mời trực tiếp từ Nhà tuyển dụng</p>
</div>

<!-- ========================================================================= -->
<!-- 1. KHU VỰC LỜI MỜI NHẬN VIỆC TRỰC TIẾP TỪ NHÀ TUYỂN DỤNG (2 CHIỀU)         -->
<!-- ========================================================================= -->
<div class="card" style="margin-bottom: 2rem; border: 1px solid #99f6e4; background: #fafdfc;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #ccfbf1; padding-bottom: 0.75rem;">
        <div>
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('target', '', 18) ?> Lời Mời Nhận Việc Trực Tiếp Từ Doanh Nghiệp (<?= count($invitations) ?>)
            </h3>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.82rem; color: #64748b;">
                Nhà tuyển dụng đã xem hồ sơ năng lực của bạn và chủ động gửi lời mời phỏng vấn / làm việc
            </p>
        </div>
    </div>

    <?php if (empty($invitations)): ?>
        <div style="text-align: center; padding: 2rem; color: #64748b; font-size: 0.88rem;">
            Chưa có lời mời trực tiếp nào. Khi Nhà tuyển dụng gửi lời mời, bạn sẽ nhận được thông báo tại đây!
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($invitations as $inv): ?>
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div style="flex: 1; min-width: 250px;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                            <span class="badge badge-primary" style="font-size: 0.75rem;">Doanh nghiệp mời trực tiếp</span>
                            <span style="font-size: 0.78rem; color: #64748b;"><?= date('d/m/Y H:i', strtotime($inv['applied_at'])) ?></span>
                        </div>
                        <h4 style="margin: 0 0 0.25rem 0; font-size: 1.05rem;">
                            <a href="<?= url('/jobs/' . $inv['job_post_id']) ?>" style="color: #0d9488; text-decoration: none; font-weight: 700;">
                                <?= htmlspecialchars($inv['job_title']) ?>
                            </a>
                        </h4>
                        <div style="font-size: 0.88rem; color: #334155; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                            <?= icon('building', '', 14) ?> <strong><?= htmlspecialchars($inv['employer_name']) ?></strong> · <?= icon('map_pin', '', 14) ?> <?= htmlspecialchars($inv['workplace_address'] ?? 'Bình Dương') ?> · <?= icon('dollar', '', 14) ?> <span style="color: #0d9488; font-weight: 700;"><?= formatSalary($inv['salary_amount'], $inv['employment_type']) ?></span>
                        </div>
                        <?php if (!empty($inv['available_work_dates'])): ?>
                            <div style="background: #f8fafc; border-left: 3px solid #0d9488; padding: 0.4rem 0.75rem; border-radius: 0 4px 4px 0; font-size: 0.82rem; color: #475569; margin-top: 0.4rem; display: flex; align-items: center; gap: 0.3rem;">
                                <?= icon('chat', '', 13) ?> <em>"<?= htmlspecialchars($inv['available_work_dates']) ?>"</em>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <?php if ($inv['request_status'] === 'invited'): ?>
                            <div style="display: flex; gap: 0.5rem;">
                                <form action="<?= url('/seeker/invitation/respond/' . $inv['request_id']) ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-success" style="font-weight: 700; padding: 0.5rem 1rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                        <?= icon('check', '', 14) ?> Đồng Ý Lời Mời
                                    </button>
                                </form>
                                <form action="<?= url('/seeker/invitation/respond/' . $inv['request_id']) ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-outline" style="color: #dc2626; border-color: #fca5a5; padding: 0.5rem 0.85rem;" onclick="return confirm('Bạn có chắc muốn từ chối lời mời này?')">
                                        Từ Chối
                                    </button>
                                </form>
                            </div>
                        <?php elseif ($inv['request_status'] === 'invitation_accepted'): ?>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.35rem;">
                                <span class="badge badge-success" style="font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('check', '', 12) ?> Đã đồng ý nhận lời mời</span>
                                <div style="display: flex; gap: 0.35rem; margin-top: 0.25rem;">
                                    <a href="<?= url('/seeker/messages') ?>" class="btn btn-outline btn-sm" style="color: #0d9488; border-color: #0d9488; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('chat', '', 12) ?> Nhắn tin với NTD
                                    </a>
                                    <a href="<?= url('/seeker/meetings') ?>" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('calendar', '', 12) ?> Xem lịch hẹn
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="badge badge-danger">Đã từ chối lời mời</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- 2. DANH SÁCH TIẾN ĐỘ HỒ SƠ BẠN ĐÃ ỨNG TUYỂN (4 GIAI ĐOẠN - PROGRESS BAR)   -->
<!-- ========================================================================= -->
<div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
        <?= icon('chart', '', 18) ?> Hồ Sơ Đã Nộp & Tiến Trình Phỏng Vấn (<?= count($selfApplied) ?>)
    </h2>
    <p style="color: #64748b; font-size: 0.88rem; margin: 0;">
        Hệ thống tự động cập nhật tiến độ theo 4 giai đoạn: Nộp hồ sơ &rarr; Lên lịch PV &rarr; Hoàn thành PV (75%) &rarr; Nhận việc (100%)
    </p>
</div>

<?php if (empty($selfApplied)): ?>
    <div class="card" style="text-align: center; color: var(--slate-400); padding: 3rem 1rem;">
        <div style="margin-bottom: 0.5rem; color: #94a3b8;"><?= icon('file_text', '', 40) ?></div>
        <div style="font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Bạn chưa nộp đơn vào vị trí nào</div>
        <p style="font-size: 0.88rem; color: #94a3b8; margin-bottom: 1rem;">Hãy khám phá hàng trăm việc làm lương tốt tại Bình Dương ngay hôm nay!</p>
        <a href="<?= url('/jobs') ?>" class="btn btn-primary btn-sm">Tìm Việc Làm Ngay</a>
    </div>
<?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <?php foreach ($selfApplied as $app): 
            $prog = evaluateAppProgress($app);
            $onboarding = !empty($app['onboarding_details']) ? json_decode($app['onboarding_details'], true) : null;
        ?>
            <div class="card" style="border: 1px solid <?= ($prog['percent'] === 100 && $prog['state_type'] === 'accepted') ? '#6ee7b7' : '#e2e8f0' ?>; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border-radius: 12px; padding: 1.5rem; background: #ffffff;">
                
                <!-- HEADER THẺ ỨNG VIÊN: TÊN VIỆC LÀM, NTD, MỨC LƯƠNG & TIẾN ĐỘ -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                            <span class="badge badge-slate" style="font-size: 0.75rem;">Mã đơn: #<?= $app['request_id'] ?></span>
                            <span style="font-size: 0.78rem; color: #64748b;">Nộp lúc: <?= date('d/m/Y H:i', strtotime($app['applied_at'])) ?></span>
                        </div>
                        <h3 style="margin: 0 0 0.35rem 0; font-size: 1.2rem; font-weight: 800;">
                            <a href="<?= url('/jobs/' . $app['job_post_id']) ?>" style="color: #0f172a; text-decoration: none;">
                                <?= htmlspecialchars($app['job_title']) ?>
                            </a>
                        </h3>
                        <div style="font-size: 0.9rem; color: #475569; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('building', '', 14) ?> <strong><?= htmlspecialchars($app['employer_name']) ?></strong></span>
                            <span>•</span>
                            <span style="color: #0d9488; font-weight: 700;"><?= formatSalary($app['salary_amount'], $app['employment_type'] ?? 'full_time') ?></span>
                            <span>•</span>
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('map_pin', '', 14) ?> <?= htmlspecialchars($app['workplace_address'] ?? 'Bình Dương') ?></span>
                        </div>
                    </div>

                    <!-- BADGE % TIẾN ĐỘ -->
                    <div style="text-align: right;">
                        <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: <?= ($prog['percent'] === 100 && $prog['state_type'] === 'accepted') ? '#ecfdf5' : (($prog['percent'] === 75) ? '#f0fdfa' : '#f8fafc') ?>; border: 1px solid <?= $prog['color'] ?>; padding: 0.4rem 0.85rem; border-radius: 9999px;">
                            <span style="font-weight: 800; font-size: 1rem; color: <?= $prog['color'] ?>;"><?= $prog['percent'] ?>%</span>
                            <span style="font-size: 0.8rem; font-weight: 700; color: #0f172a;"><?= $prog['label'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- THANH TIẾN TRÌNH TRỰC QUAN (VISUAL PROGRESS STEPPER 4 BƯỚC) -->
                <div style="margin-bottom: 1.5rem; padding: 0.5rem 0;">
                    <div style="position: relative; margin-bottom: 1.5rem;">
                        <!-- Đường line nền -->
                        <div style="position: absolute; top: 14px; left: 5%; right: 5%; height: 4px; background: #e2e8f0; z-index: 1;"></div>
                        <!-- Đường line đã đi qua -->
                        <div style="position: absolute; top: 14px; left: 5%; width: <?= ($prog['step'] === 1 ? '15%' : ($prog['step'] === 2 ? '45%' : ($prog['step'] === 3 ? '75%' : '90%'))) ?>; height: 4px; background: <?= $prog['color'] ?>; z-index: 2; transition: width 0.3s;"></div>
                        
                        <!-- 4 Mốc Bước -->
                        <div style="position: relative; display: flex; justify-content: space-between; z-index: 3;">
                            
                            <!-- Bước 1: Nộp hồ sơ -->
                            <div style="display: flex; flex-direction: column; align-items: center; text-align: center; width: 110px;">
                                <div style="width: 30px; height: 30px; border-radius: 50%; background: <?= ($prog['step'] >= 1) ? '#0d9488' : '#cbd5e1' ?>; color: #fff; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <?= icon('check', '', 14) ?>
                                </div>
                                <span style="font-size: 0.78rem; font-weight: 700; color: #0f172a; margin-top: 0.35rem;">1. Nộp hồ sơ</span>
                                <span style="font-size: 0.7rem; color: #64748b;">(25%)</span>
                            </div>

                            <!-- Bước 2: Lên lịch phỏng vấn -->
                            <div style="display: flex; flex-direction: column; align-items: center; text-align: center; width: 120px;">
                                <div style="width: 30px; height: 30px; border-radius: 50%; background: <?= ($prog['step'] >= 2) ? '#0d9488' : '#cbd5e1' ?>; color: #fff; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <?= ($prog['step'] >= 2) ? icon('check', '', 14) : '2' ?>
                                </div>
                                <span style="font-size: 0.78rem; font-weight: 700; color: #0f172a; margin-top: 0.35rem;">2. Lên lịch PV</span>
                                <span style="font-size: 0.7rem; color: #64748b;">(50%)</span>
                            </div>

                            <!-- Bước 3: Hoàn thành PV & Chờ kết quả -->
                            <div style="display: flex; flex-direction: column; align-items: center; text-align: center; width: 140px;">
                                <div style="width: 30px; height: 30px; border-radius: 50%; background: <?= ($prog['step'] >= 3) ? '#0d9488' : '#cbd5e1' ?>; color: #fff; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <?= ($prog['step'] >= 3) ? icon('check', '', 14) : '3' ?>
                                </div>
                                <span style="font-size: 0.78rem; font-weight: 700; color: #0f172a; margin-top: 0.35rem;">3. Hoàn thành PV</span>
                                <span style="font-size: 0.7rem; color: #64748b;">(75% Chờ kết quả)</span>
                            </div>

                            <!-- Bước 4: Nhận việc / Phê duyệt -->
                            <div style="display: flex; flex-direction: column; align-items: center; text-align: center; width: 120px;">
                                <div style="width: 30px; height: 30px; border-radius: 50%; background: <?= ($prog['step'] >= 4) ? ($prog['state_type'] === 'accepted' ? '#10b981' : '#ef4444') : '#cbd5e1' ?>; color: #fff; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <?= ($prog['step'] >= 4) ? ($prog['state_type'] === 'accepted' ? icon('check', '', 14) : icon('x', '', 14)) : '4' ?>
                                </div>
                                <span style="font-size: 0.78rem; font-weight: 700; color: #0f172a; margin-top: 0.35rem;">4. Kết quả PV</span>
                                <span style="font-size: 0.7rem; color: #64748b;">(100% Nhận việc)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NỘI DUNG CHI TIẾT TỪNG TRẠNG THÁI -->

                <!-- ========================================================= -->
                <!-- TRƯỜNG HỢP 1: ACCEPT (100% - CHÚC MỪNG & CHECKLIST NGÀY ĐẦU) -->
                <!-- ========================================================= -->
                <?php if ($prog['state_type'] === 'accepted'): ?>
                    <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 2px solid #34d399; border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.75rem;">
                            <span style="color: #059669;"><?= icon('award', '', 28) ?></span>
                            <div>
                                <h4 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #065f46;">
                                    CHÚC MỪNG BẠN ĐÃ TRÚNG TUYỂN VÀ CHÍNH THỨC NHẬN VIỆC!
                                </h4>
                                <p style="margin: 0.2rem 0 0 0; font-size: 0.85rem; color: #047857;">
                                    Doanh nghiệp đánh giá xuất sắc năng lực của bạn qua buổi phỏng vấn. Hãy chuẩn bị hành trang để sẵn sàng cho ngày đầu tiên đi làm.
                                </p>
                            </div>
                        </div>

                        <!-- HÀNH TRANG & YÊU CẦU BUỔI ĐẦU ĐI LÀM -->
                        <div style="background: #ffffff; border-radius: 8px; padding: 1.1rem; border: 1px solid #a7f3d0; margin-top: 0.75rem;">
                            <h5 style="margin: 0 0 0.6rem 0; font-size: 0.95rem; font-weight: 800; color: #065f46; display: flex; align-items: center; gap: 0.35rem;">
                                <span><?= icon('file_text', '', 16) ?></span> YÊU CẦU & HÀNH TRANG CHO BUỔI ĐẦU TIÊN ĐI LÀM:
                            </h5>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; font-size: 0.88rem; margin-bottom: 0.85rem; padding-bottom: 0.85rem; border-bottom: 1px dashed #e2e8f0;">
                                <div>
                                    <span style="color: #64748b;"><?= icon('calendar', '', 13) ?> Ngày bắt đầu nhận việc:</span><br>
                                    <strong style="color: #0f172a; font-size: 0.95rem;"><?= !empty($onboarding['start_date']) ? date('d/m/Y', strtotime($onboarding['start_date'])) : 'Theo trao đổi trực tiếp' ?></strong> 
                                    <span style="color: #0d9488; font-weight: 600;">(lúc <?= htmlspecialchars($onboarding['start_time'] ?? '08:00') ?>)</span>
                                </div>
                                <div>
                                    <span style="color: #64748b;"><?= icon('map_pin', '', 13) ?> Địa điểm làm việc:</span><br>
                                    <strong style="color: #0f172a;"><?= htmlspecialchars($onboarding['workplace'] ?? ($app['workplace_address'] ?? 'Văn phòng Công ty')) ?></strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;"><?= icon('user', '', 13) ?> Người đón tiếp / Liên hệ:</span><br>
                                    <strong style="color: #0f172a;"><?= htmlspecialchars($onboarding['contact_person'] ?? ($app['employer_name'] ?? 'Phòng Nhân Sự')) ?></strong>
                                    <?php if (!empty($app['employer_phone'])): ?>
                                        · <a href="tel:<?= htmlspecialchars($app['employer_phone']) ?>" style="color: #0d9488; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.2rem;"><?= icon('phone', '', 13) ?> <?= htmlspecialchars($app['employer_phone']) ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- CHECKLIST NHỮNG THỨ CẦN MANG THEO -->
                            <div style="font-size: 0.85rem; color: #334155;">
                                <strong style="color: #0f172a; display: block; margin-bottom: 0.4rem;">
                                    Danh sách hồ sơ, giấy tờ & trang phục cần chuẩn bị:
                                </strong>
                                <?php 
                                    $checklist = !empty($onboarding['checklist']) ? $onboarding['checklist'] : [
                                        'Căn cước công dân (CCCD / VNeID) bản gốc & 01 bản photo công chứng',
                                        'Sơ yếu lý lịch tự thuật & Giấy khám sức khỏe (trong vòng 6 tháng)',
                                        'Bằng cấp, chứng chỉ chuyên môn hoặc giấy phép lái xe liên quan (nếu có)',
                                        '02 ảnh thẻ 3x4 để làm thẻ nhân viên và thủ tục bảo hiểm',
                                        'Thông tin số tài khoản ngân hàng chính chủ để làm thủ tục nhận lương',
                                        'Trang phục: Đồng phục công ty hoặc quần tây / áo sơ mi lịch sự, giày bít mũi'
                                    ];
                                ?>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.4rem;">
                                    <?php foreach ($checklist as $idx => $item): ?>
                                        <label style="display: flex; align-items: center; gap: 0.45rem; background: #f8fafc; padding: 0.4rem 0.65rem; border-radius: 6px; border: 1px solid #f1f5f9; cursor: pointer;">
                                            <input type="checkbox" checked style="accent-color: #10b981;">
                                            <span><?= htmlspecialchars($item) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <?php if (!empty($onboarding['notes'])): ?>
                                <div style="margin-top: 0.75rem; background: #f0fdf4; border-left: 3px solid #10b981; padding: 0.5rem 0.75rem; border-radius: 0 4px 4px 0; font-size: 0.82rem; color: #166534; display: flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('chat', '', 13) ?> <strong>Dặn dò thêm từ công ty:</strong> <?= htmlspecialchars($onboarding['notes']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                            <span style="font-size: 0.82rem; color: #047857; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('check', '', 13) ?> Hãy đến đúng giờ để buổi nhận việc đầu tiên diễn ra thuận lợi nhất!
                            </span>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="<?= url('/seeker/messages') ?>" class="btn btn-outline btn-sm" style="color: #065f46; border-color: #065f46; background: #fff; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('chat', '', 13) ?> Nhắn tin với NTD
                                </a>
                                <button type="button" class="btn btn-success btn-sm" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;" onclick="alert('Cảm ơn bạn! Hệ thống đã ghi nhận bạn đã sẵn sàng nhận việc. Chúc bạn có một ngày đầu làm việc thật hứng khởi!')">
                                    <?= icon('check_circle', '', 14) ?> Tôi Đã Nắm Rõ & Sẵn Sàng Đi Làm
                                </button>
                            </div>
                        </div>
                    </div>

                <!-- ========================================================= -->
                <!-- TRƯỜNG HỢP 2: REJECT (TỪ CHỐI LỊCH SỰ & HẸN DỊP PHỎNG VẤN SAU) -->
                <!-- ========================================================= -->
                <?php elseif ($prog['state_type'] === 'rejected'): ?>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                            <span style="color: #64748b;"><?= icon('mail', '', 28) ?></span>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.35rem 0; font-size: 1rem; font-weight: 800; color: #0f172a;">
                                    THƯ CẢM ƠN TỪ <?= htmlspecialchars($app['employer_name']) ?> & HẸN DỊP PHỎNG VẤN SAU
                                </h4>
                                <p style="margin: 0 0 0.5rem 0; font-size: 0.88rem; color: #475569; line-height: 1.55;">
                                    Cảm ơn bạn đã dành thời gian và sự quan tâm tham gia phỏng vấn vị trí <strong><?= htmlspecialchars($app['job_title']) ?></strong>. Dù ở thời điểm này hồ sơ chưa thực sự phù hợp nhất với yêu cầu hiện tại của dự án, doanh nghiệp đánh giá rất cao năng lực và tác phong của bạn.
                                </p>
                                <?php if (!empty($app['rejection_reason'])): ?>
                                    <div style="background: #fff; border: 1px solid #fed7aa; padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.82rem; color: #9a3412; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('lightbulb', '', 14) ?> <strong>Góp ý từ nhà tuyển dụng:</strong> <?= htmlspecialchars($app['rejection_reason']) ?>
                                    </div>
                                <?php endif; ?>
                                <div style="background: #f0fdfa; border: 1px solid #ccfbf1; padding: 0.65rem 0.85rem; border-radius: 6px; font-size: 0.82rem; color: #0f766e; display: flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('sparkle', '', 14) ?> <strong>Hồ sơ của bạn đã được lưu:</strong> Thông tin của bạn đã được lưu giữ trong danh sách nhân tài tiềm năng của công ty trên hệ thống JobLink. Doanh nghiệp sẽ ưu tiên liên hệ lại với bạn cho các đợt tuyển dụng hoặc vị trí phù hợp hơn tiếp theo!
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.85rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
                            <a href="<?= url('/jobs') ?>" class="btn btn-primary btn-sm" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('search', '', 14) ?> Khám Phá Việc Làm Tương Tự Khác &rarr;
                            </a>
                        </div>
                    </div>

                <!-- ========================================================= -->
                <!-- TRƯỜNG HỢP 3: ĐÃ PHỎNG VẤN XONG (75% - CHỜ KẾT QUẢ & SLA) -->
                <!-- ========================================================= -->
                <?php elseif ($prog['state_type'] === 'interview_completed'): ?>
                    <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f766e; display: flex; align-items: center; gap: 0.35rem;">
                                    <?= icon('target', '', 16) ?> Buổi Phỏng Vấn Đã Hoàn Thành - Đang Đánh Giá Kết Quả
                                </h4>
                                <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #115e59;">
                                    Bạn đã tham gia phỏng vấn vào ngày <strong><?= date('d/m/Y', strtotime($app['meeting_date'])) ?></strong>. Doanh nghiệp đang tổng hợp đánh giá và chuẩn bị công bố kết quả tuyển dụng.
                                </p>
                            </div>
                            <span class="badge badge-primary" style="font-size: 0.82rem; padding: 0.3rem 0.65rem;">
                                75% Đã Phỏng Vấn Xong
                            </span>
                        </div>

                        <!-- THỜI GIAN PHẢN HỒI (SLA 3 NGÀY) & CẢNH BÁO QUÁ HẠN -->
                        <div style="background: #ffffff; border-radius: 8px; padding: 1rem; border: 1px solid <?= ($prog['is_overdue'] ? '#fdba74' : '#ccfbf1') ?>; margin-top: 0.5rem;">
                            <?php if ($prog['is_overdue']): ?>
                                <div style="display: flex; align-items: flex-start; gap: 0.65rem;">
                                    <span style="color: #c2410c;"><?= icon('alert_triangle', '', 24) ?></span>
                                    <div style="flex: 1;">
                                        <strong style="color: #c2410c; font-size: 0.92rem; display: block; margin-bottom: 0.2rem;">
                                            ĐÃ QUÁ THỜI GIAN CAM KẾT PHẢN HỒI KẾT QUẢ TỪ NHÀ TUYỂN DỤNG!
                                        </strong>
                                        <p style="margin: 0 0 0.65rem 0; font-size: 0.84rem; color: #9a3412;">
                                            Đã qua hơn 3 ngày làm việc kể từ ngày phỏng vấn (<?= date('d/m/Y', strtotime($app['meeting_date'])) ?>) mà doanh nghiệp chưa cập nhật kết quả chính thức. Bạn có thể sử dụng các hình thức bên dưới để chủ động liên lạc và hỏi thăm tiến độ:
                                        </p>
                                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                            <form action="<?= url('/seeker/applications/remind/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                                <button type="submit" class="btn btn-warning btn-sm" style="font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                    <?= icon('bell', '', 14) ?> Gửi Tin Nhắn Nhắc NTD Phản Hồi Kết Quả
                                                </button>
                                            </form>
                                            <?php if (!empty($app['employer_phone'])): ?>
                                                <a href="tel:<?= htmlspecialchars($app['employer_phone']) ?>" class="btn btn-outline btn-sm" style="color: #0f172a; border-color: #cbd5e1; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                    <?= icon('phone', '', 13) ?> Gọi Hotline NTD: <?= htmlspecialchars($app['employer_phone']) ?>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= url('/seeker/messages') ?>" class="btn btn-outline btn-sm" style="color: #0d9488; border-color: #0d9488; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                <?= icon('chat', '', 13) ?> Mở Chat Riêng
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                    <div style="font-size: 0.85rem; color: #065f46; display: flex; align-items: center; gap: 0.3rem;">
                                        <strong><?= icon('clock', '', 13) ?> Thời gian phản hồi cam kết:</strong> Trong vòng 3 ngày làm việc (dự kiến trước <strong><?= date('d/m/Y', $prog['deadline']) ?></strong>). 
                                        <span style="color: #0d9488; font-weight: 700;">(Còn khoảng <?= $prog['days_left'] ?> ngày)</span>
                                    </div>
                                    <span class="badge badge-success" style="font-size: 0.75rem;">Đang trong hạn phản hồi</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <!-- ========================================================= -->
                <!-- TRƯỜNG HỢP 4: ĐÃ LÊN LỊCH PV (50% - ĐANG CHUẨN BỊ BUỔI PV) -->
                <!-- ========================================================= -->
                <?php elseif ($prog['state_type'] === 'interview_scheduled'): ?>
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #92400e; display: flex; align-items: center; gap: 0.35rem;">
                                    <?= icon('calendar', '', 16) ?> Lịch Phỏng Vấn Đã Được Xếp - Hãy Chuẩn Bị Thật Tốt!
                                </h4>
                                <div style="font-size: 0.85rem; color: #b45309; margin-top: 0.25rem;">
                                    <?php if ($prog['days_to_prep'] > 0): ?>
                                        <?= icon('clock', '', 13) ?> Còn <strong><?= $prog['days_to_prep'] ?> ngày</strong> để bạn chuẩn bị kiến thức và hành trang phỏng vấn.
                                    <?php else: ?>
                                        <?= icon('bell', '', 13) ?> Buổi phỏng vấn diễn ra vào <strong>HÔM NAY</strong>! Vui lòng chuẩn bị sẵn sàng.
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="badge badge-warning" style="font-size: 0.82rem; padding: 0.3rem 0.65rem;">
                                50% Đã Có Lịch Hẹn
                            </span>
                        </div>

                        <!-- CHI TIẾT LỊCH HẸN & KHOẢNG NGÀY & HƯỚNG DẪN CHUẨN BỊ -->
                        <div style="background: #ffffff; border-radius: 8px; padding: 1rem; border: 1px solid #fcd34d; font-size: 0.88rem;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px dashed #e2e8f0;">
                                <div>
                                    <span style="color: #64748b; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('clock', '', 13) ?> Giờ phỏng vấn chính thức:</span><br>
                                    <strong style="color: #0f172a; font-size: 0.95rem;"><?= date('d/m/Y', strtotime($app['meeting_date'])) ?></strong> 
                                    <span style="color: #0d9488; font-weight: 700;">lúc <?= substr($app['start_time'], 0, 5) ?><?= !empty($app['end_time']) ? ' - ' . substr($app['end_time'], 0, 5) : '' ?></span>
                                </div>
                                <?php if (!empty($app['interview_range'])): ?>
                                    <div>
                                        <span style="color: #64748b; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('calendar', '', 13) ?> Khoảng ngày dự kiến của đợt PV:</span><br>
                                        <strong style="color: #0d9488;"><?= htmlspecialchars($app['interview_range']) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span style="color: #64748b; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('globe', '', 13) ?> Hình thức phỏng vấn:</span><br>
                                    <span class="badge <?= ($app['meeting_format'] === 'online') ? 'badge-primary' : 'badge-slate' ?>" style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= ($app['meeting_format'] === 'online') ? (icon('globe', '', 12) . ' Trực tuyến (Online)') : (icon('building', '', 12) . ' Trực tiếp tại cơ sở') ?>
                                    </span>
                                </div>
                                <div style="grid-column: 1 / -1;">
                                    <span style="color: #64748b; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('map_pin', '', 13) ?> Địa điểm / Link phòng họp:</span><br>
                                    <?php if ($app['meeting_format'] === 'online' && strpos($app['location_or_meeting_link'], 'http') === 0): ?>
                                        <a href="<?= htmlspecialchars($app['location_or_meeting_link']) ?>" target="_blank" style="color: #0d9488; font-weight: 700; text-decoration: underline; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <?= icon('link', '', 13) ?> <?= htmlspecialchars($app['location_or_meeting_link']) ?> (Bấm để vào phòng họp)
                                        </a>
                                    <?php else: ?>
                                        <strong style="color: #0f172a;"><?= htmlspecialchars($app['location_or_meeting_link'] ?? 'Văn phòng Công ty') ?></strong>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- CẨM NANG CHUẨN BỊ PHỎNG VẤN -->
                            <div style="background: #fefce8; padding: 0.65rem 0.85rem; border-radius: 6px; border-left: 3px solid #f59e0b; font-size: 0.82rem; color: #78350f;">
                                <strong style="display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('file_text', '', 14) ?> Hướng dẫn & Dặn dò chuẩn bị cho buổi phỏng vấn:</strong><br>
                                <?= !empty($app['prep_guide']) ? nl2br(htmlspecialchars($app['prep_guide'])) : '• Mang theo CCCD/VNeID bản gốc và bản in CV/Hồ sơ xin việc.<br>• Trang phục lịch sự, tác phong đúng giờ (nên đến trước 10 phút hoặc kiểm tra đường truyền mic/camera nếu phỏng vấn online).' ?>
                            </div>
                        </div>

                        <!-- NÚT HÀNH ĐỘNG CHO LỊCH HẸN -->
                        <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.85rem;">
                            <button type="button" class="btn btn-outline btn-sm btn-open-reschedule" 
                                    data-meeting-id="<?= $app['meeting_id'] ?>"
                                    data-job-title="<?= htmlspecialchars($app['job_title']) ?>"
                                    data-meeting-date="<?= $app['meeting_date'] ?>"
                                    data-start-time="<?= $app['start_time'] ?>"
                                    data-range="<?= htmlspecialchars($app['interview_range'] ?? '') ?>"
                                    style="color: #b45309; border-color: #fcd34d; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('calendar', '', 13) ?> Đề Xuất Đổi Lịch Trong Khoảng Cho Phép
                            </button>
                            <a href="<?= url('/seeker/meetings') ?>" class="btn btn-primary btn-sm" style="font-weight: 700; font-size: 0.78rem;">
                                Xem Chi Tiết Cuộc Hẹn &rarr;
                            </a>
                        </div>
                    </div>

                <!-- ========================================================= -->
                <!-- TRƯỜNG HỢP 5: CHỜ DUYỆT (25% - MỚI NỘP HỒ SƠ)             -->
                <!-- ========================================================= -->
                <?php else: ?>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                        <div>
                            <div style="font-weight: 700; color: #334155; font-size: 0.9rem; margin-bottom: 0.2rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <?= icon('clock', '', 14) ?> Hồ sơ đang chờ Nhà tuyển dụng tiếp nhận & xét duyệt
                            </div>
                            <div style="font-size: 0.82rem; color: #64748b;">
                                Doanh nghiệp sẽ đối chiếu hồ sơ và liên hệ đặt lịch phỏng vấn trong khoảng ngày phù hợp.
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.35rem;">
                            <a href="<?= url('/jobs/' . $app['job_post_id']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">
                                Xem Tin Tuyển Dụng
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- MODAL ĐỀ XUẤT ĐỔI LỊCH PHỎNG VẤN (SEEKER RESCHEDULE MODAL) -->
<div id="rescheduleModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 12px; max-width: 480px; width: 100%; padding: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('calendar', '', 18) ?> Đề Xuất Đổi Lịch Phỏng Vấn
            </h3>
            <button type="button" id="closeRescheduleBtn" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <form id="rescheduleForm" action="" method="POST">
            <div style="margin-bottom: 0.75rem; font-size: 0.88rem; color: #475569;">
                Vị trí: <strong id="reschedJobTitle" style="color: #0d9488;"></strong>
            </div>

            <div id="reschedRangeAlert" style="display: none; background: #f0fdfa; border: 1px solid #99f6e4; padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.82rem; color: #0f766e; margin-bottom: 0.85rem;">
                <span style="display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('calendar', '', 13) ?> <strong>Khoảng ngày phỏng vấn của đợt này:</strong></span> <span id="reschedRangeText"></span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Ngày mới mong muốn *</label>
                    <input type="date" name="proposed_date" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Giờ đề xuất *</label>
                    <input type="time" name="proposed_time" value="09:00" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Lý do đề xuất dời lịch *</label>
                <textarea name="reschedule_reason" rows="2" class="form-control" required placeholder="Ví dụ: Em bị trùng lịch học/ca làm việc cũ, kính xin dời sang ngày trên..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" id="cancelRescheduleBtn" class="btn btn-outline btn-sm">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700;">Gửi Đề Xuất Đến NTD</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('rescheduleModal');
    const closeBtn = document.getElementById('closeRescheduleBtn');
    const cancelBtn = document.getElementById('cancelRescheduleBtn');
    const form = document.getElementById('rescheduleForm');
    const btns = document.querySelectorAll('.btn-open-reschedule');

    btns.forEach(btn => {
        btn.addEventListener('click', function() {
            const meetingId = this.getAttribute('data-meeting-id');
            const jobTitle = this.getAttribute('data-job-title');
            const range = this.getAttribute('data-range');

            form.action = '<?= url('/seeker/meetings/reschedule/') ?>' + meetingId;
            document.getElementById('reschedJobTitle').textContent = jobTitle;

            const rangeBox = document.getElementById('reschedRangeAlert');
            if (range && range.trim() !== '') {
                document.getElementById('reschedRangeText').textContent = range;
                rangeBox.style.display = 'block';
            } else {
                rangeBox.style.display = 'none';
            }

            modal.style.display = 'flex';
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', () => modal.style.display = 'none');
    if (cancelBtn) cancelBtn.addEventListener('click', () => modal.style.display = 'none');
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
