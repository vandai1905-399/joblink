<?php 
$extraCss = ['css/jobs/ai_matching.css'];
$extraJs = ['js/jobs/ai_recommendation.js'];
ob_start(); 
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">
        Xin chào, <?= htmlspecialchars($seeker['full_name'] ?? 'Người lao động') ?>
    </h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Tổng quan hoạt động tìm việc, lịch phỏng vấn và danh sách việc làm gợi ý từ AI</p>
</div>

<div class="grid-stats" style="margin-bottom: 2rem;">
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= url('/seeker/applied-jobs') ?>'">
        <div class="stat-title">Đã Ứng Tuyển</div>
        <div class="stat-value" style="color: var(--primary);"><?= count($appliedJobs ?? []) ?></div>
        <a href="<?= url('/seeker/applied-jobs') ?>" style="font-size: 0.82rem; color: var(--primary); font-weight: 600;">Xem chi tiết &rarr;</a>
    </div>
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= url('/seeker/meetings') ?>'">
        <div class="stat-title">Lịch Phỏng Vấn Sắp Tới</div>
        <div class="stat-value" style="color: var(--success);"><?= count($meetings ?? []) ?></div>
        <a href="<?= url('/seeker/meetings') ?>" style="font-size: 0.82rem; color: var(--success); font-weight: 600;">Xem lịch hẹn &rarr;</a>
    </div>
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= url('/seeker/messages') ?>'">
        <div class="stat-title">Tin Nhắn Trao Đổi</div>
        <div class="stat-value" style="color: #0284c7;"><?= count($messages ?? []) ?></div>
        <a href="<?= url('/seeker/messages') ?>" style="font-size: 0.82rem; color: #0284c7; font-weight: 600;">Mở hộp thư &rarr;</a>
    </div>
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= url('/seeker/saved-jobs') ?>'">
        <div class="stat-title">Việc Làm Đã Lưu</div>
        <div class="stat-value" style="color: var(--warning);"><?= count($savedJobs ?? []) ?></div>
        <a href="<?= url('/seeker/saved-jobs') ?>" style="font-size: 0.82rem; color: var(--warning); font-weight: 600;">Xem danh sách &rarr;</a>
    </div>
</div>

<!-- ========================================================================= -->
<!-- THÔNG BÁO TRÚNG TUYỂN & NGÀY LÀM ĐẦU TIÊN (FIRST DAY ONBOARDING BANNER)    -->
<!-- ========================================================================= -->
<?php 
    $acceptedJobs = array_filter($appliedJobs ?? [], function($j) {
        return in_array($j['request_status'] ?? '', ['accepted', 'invitation_accepted']);
    });
?>

<?php if (!empty($acceptedJobs)): ?>
    <div style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%); color: #ffffff; border-radius: 14px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 10px 25px rgba(5,150,105,0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="background: rgba(255,255,255,0.2); width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <?= icon('award', '', 22) ?>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #ffffff;">
                        Chúc Mừng Bạn Đã Trúng Tuyển & Nhận Việc Thành Công!
                    </h3>
                    <p style="margin: 0.15rem 0 0 0; font-size: 0.85rem; color: #d1fae5;">
                        Bạn có <?= count($acceptedJobs) ?> thông báo tiếp nhận công việc & hướng dẫn chuẩn bị ngày làm việc đầu tiên
                    </p>
                </div>
            </div>
            <a href="<?= url('/seeker/applied-jobs') ?>" style="color: #ffffff; font-size: 0.85rem; font-weight: 700; background: rgba(255,255,255,0.2); padding: 0.45rem 0.95rem; border-radius: 8px; text-decoration: none; border: 1px solid rgba(255,255,255,0.3); transition: background 0.15s ease;">
                Xem toàn bộ hồ sơ &rarr;
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
            <?php foreach ($acceptedJobs as $aj): 
                $onb = !empty($aj['onboarding_details']) ? json_decode($aj['onboarding_details'], true) : null;
            ?>
                <div style="background: #ffffff; color: #0f172a; border-radius: 12px; padding: 1.25rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <span class="badge badge-success" style="font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.55rem;">
                                100% ĐÃ NHẬN VIỆC
                            </span>
                            <span style="font-size: 0.75rem; color: #64748b;">Mã đơn: #<?= $aj['request_id'] ?></span>
                        </div>
                        <h4 style="margin: 0 0 0.25rem 0; font-size: 1.1rem; font-weight: 800; color: #0f172a;">
                            <a href="<?= url('/seeker/applied-jobs') ?>" style="color: #0f172a; text-decoration: none;">
                                <?= htmlspecialchars($aj['job_title']) ?>
                            </a>
                        </h4>
                        <div style="font-size: 0.88rem; color: #0d9488; font-weight: 700; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.35rem;">
                            <?= icon('building', '', 14) ?> <?= htmlspecialchars($aj['employer_name']) ?>
                        </div>

                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.75rem; font-size: 0.85rem; color: #166534; margin-bottom: 0.85rem;">
                            <div style="margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.35rem;">
                                <?= icon('calendar', '', 14) ?> <span><strong>Ngày làm việc đầu tiên:</strong> 
                                <?= !empty($onb['start_date']) ? date('d/m/Y', strtotime($onb['start_date'])) : 'Theo trao đổi' ?> 
                                (lúc <?= htmlspecialchars($onb['start_time'] ?? '08:00') ?>)</span>
                            </div>
                            <div style="margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.35rem;">
                                <?= icon('map_pin', '', 14) ?> <span><strong>Địa điểm:</strong> <?= htmlspecialchars($onb['workplace'] ?? ($aj['workplace_address'] ?? 'Tại văn phòng công ty')) ?></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                <?= icon('user', '', 14) ?> <span><strong>Người đón tiếp:</strong> <?= htmlspecialchars($onb['contact_person'] ?? ($aj['employer_name'] ?? 'Phòng Nhân Sự')) ?></span>
                                <?php if (!empty($aj['employer_phone'])): ?>
                                    · <span style="display: inline-flex; align-items: center; gap: 0.2rem;"><?= icon('phone', '', 13) ?> <?= htmlspecialchars($aj['employer_phone']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($onb['notes'])): ?>
                            <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.75rem; font-style: italic; display: flex; align-items: flex-start; gap: 0.3rem;">
                                <?= icon('chat', '', 14) ?> <span>"<?= htmlspecialchars($onb['notes']) ?>"</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 0.75rem;">
                        <a href="<?= url('/seeker/messages?partner_id=' . ($aj['employer_account_id'] ?? 0)) ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem; padding: 0.3rem 0.6rem; color: #0d9488; border-color: #0d9488; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <?= icon('chat', '', 13) ?> Nhắn tin
                        </a>
                        <a href="<?= url('/seeker/applied-jobs') ?>" class="btn btn-success btn-sm" style="font-size: 0.78rem; padding: 0.3rem 0.75rem; font-weight: 700; background: #059669; border-color: #059669;">
                            Xem Chi Tiết Hành Trang &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- 1. KHỐI LỊCH PHỎNG VẤN SẮP DIỄN RA (NẾU ĐANG CÓ LỊCH HẸN / CHỜ PHỎNG VẤN) -->
<!-- ========================================================================= -->
<?php 
    $upcomingMeetings = array_filter($meetings ?? [], function($m) {
        $mStatus = $m['meeting_status'] ?? 'scheduled';
        return in_array($mStatus, ['scheduled', 'pending', 'confirmed']) || strtotime($m['meeting_date']) >= strtotime(date('Y-m-d'));
    });
?>

<?php if (!empty($upcomingMeetings)): ?>

    <div style="background: linear-gradient(135deg, #065f46 0%, #0d9488 100%); color: #ffffff; border-radius: 14px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 10px 25px rgba(13,148,136,0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="background: rgba(255,255,255,0.2); width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <?= icon('calendar', '', 20) ?>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #ffffff;">
                        Lịch Phỏng Vấn Sắp Diễn Ra (<?= count($upcomingMeetings) ?> cuộc hẹn)
                    </h3>
                    <p style="margin: 0.15rem 0 0 0; font-size: 0.85rem; color: #a7f3d0;">
                        Bạn có lịch hẹn phỏng vấn từ nhà tuyển dụng. Vui lòng kiểm tra thời gian và tham gia đúng giờ!
                    </p>
                </div>
            </div>
            <a href="<?= url('/seeker/meetings') ?>" style="color: #ffffff; font-size: 0.85rem; font-weight: 700; background: rgba(255,255,255,0.18); padding: 0.45rem 0.95rem; border-radius: 8px; text-decoration: none; border: 1px solid rgba(255,255,255,0.3); transition: background 0.15s ease;">
                Xem tất cả lịch hẹn &rarr;
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(290px, 1fr)); gap: 1rem;">
            <?php foreach (array_slice($upcomingMeetings, 0, 3) as $m): ?>
                <div style="background: #ffffff; color: #0f172a; border-radius: 12px; padding: 1.15rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.65rem;">
                            <h4 style="margin: 0; font-size: 1rem; font-weight: 800; color: #0f172a; line-height: 1.35;">
                                <a href="<?= url('/seeker/meetings') ?>" style="color: #0f172a; text-decoration: none;">
                                    <?= htmlspecialchars($m['job_title']) ?>
                                </a>
                            </h4>
                            <span class="badge badge-success" style="font-size: 0.72rem; padding: 0.2rem 0.55rem; white-space: nowrap; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= ($m['meeting_format'] === 'online') ? (icon('globe', '', 12) . ' Trực tuyến') : (icon('map_pin', '', 12) . ' Trực tiếp') ?>
                            </span>
                        </div>

                        <div style="font-size: 0.86rem; color: #475569; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.35rem;">
                            <?= icon('building', '', 14) ?> <strong><?= htmlspecialchars($m['employer_name']) ?></strong>
                        </div>

                        <div style="font-size: 0.86rem; color: #0d9488; font-weight: 700; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                            <?= icon('calendar', '', 14) ?>
                            <span><?= formatDate($m['meeting_date']) ?> lúc <?= substr($m['start_time'], 0, 5) ?><?= !empty($m['end_time']) ? ' - ' . substr($m['end_time'], 0, 5) : '' ?></span>
                        </div>

                        <?php if (!empty($m['location_or_meeting_link'])): ?>
                            <div style="font-size: 0.8rem; color: #64748b; background: #f8fafc; padding: 0.5rem 0.65rem; border-radius: 6px; margin-bottom: 0.75rem; border: 1px solid #e2e8f0; word-break: break-all;">
                                <strong><?= ($m['meeting_format'] === 'online') ? (icon('link', '', 12) . ' Link phòng họp:') : (icon('map_pin', '', 12) . ' Địa điểm:') ?></strong>
                                <?php if ($m['meeting_format'] === 'online' && (strpos($m['location_or_meeting_link'], 'http') === 0)): ?>
                                    <a href="<?= htmlspecialchars($m['location_or_meeting_link']) ?>" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: underline;">
                                        <?= htmlspecialchars($m['location_or_meeting_link']) ?>
                                    </a>
                                <?php else: ?>
                                    <?= htmlspecialchars($m['location_or_meeting_link']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid #f1f5f9; padding-top: 0.65rem;">
                        <a href="<?= url('/seeker/meetings') ?>" class="btn btn-primary btn-sm" style="font-size: 0.78rem; font-weight: 700; padding: 0.35rem 0.75rem; border-radius: 6px;">
                            Chi Tiết Lịch Hẹn &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- 2. KHỐI TIN NHẮN TRAO ĐỔI TUYỂN DỤNG (NẾU CÓ TIN NHẮN)                    -->
<!-- ========================================================================= -->
<?php if (!empty($messages)): ?>
    <div class="card" style="margin-bottom: 2rem; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); padding: 1.35rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="background: #f0fdfa; color: #0d9488; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid #99f6e4;">
                    <?= icon('chat', '', 17) ?>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #0f172a;">
                        Tin Nhắn Trao Đổi Tuyển Dụng (<?= count($messages) ?>)
                    </h3>
                    <p style="margin: 0.15rem 0 0 0; font-size: 0.82rem; color: #64748b;">
                        Các cuộc trò chuyện, thư phản hồi và liên lạc từ nhà tuyển dụng
                    </p>
                </div>
            </div>
            <a href="<?= url('/seeker/messages') ?>" class="btn btn-outline btn-sm" style="font-weight: 700; font-size: 0.8rem;">
                Mở hộp thư &rarr;
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.85rem;">
            <?php foreach (array_slice($messages, 0, 3) as $msg): ?>
                <a href="<?= url('/seeker/messages') ?>" style="display: block; text-decoration: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.9rem; transition: background 0.15s ease;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <strong style="color: #0f172a; font-size: 0.9rem;"><?= htmlspecialchars($msg['sender_name'] ?? 'Nhà Tuyển Dụng') ?></strong>
                        <span style="font-size: 0.72rem; color: #94a3b8;"><?= date('d/m/Y H:i', strtotime($msg['sent_at'])) ?></span>
                    </div>
                    <?php if (!empty($msg['message_title'])): ?>
                        <div style="font-size: 0.82rem; font-weight: 700; color: #0d9488; margin-bottom: 0.2rem;"><?= htmlspecialchars($msg['message_title']) ?></div>
                    <?php endif; ?>
                    <p style="margin: 0; font-size: 0.8rem; color: #475569; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <?= htmlspecialchars($msg['message_content']) ?>
                    </p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- 3. KHỐI TRẠNG THÁI ỨNG TUYỂN (NẾU ĐÃ CÓ HỒ SƠ ỨNG TUYỂN)                   -->
<!-- ========================================================================= -->
<?php if (!empty($appliedJobs)): ?>

    <div class="card" style="margin-bottom: 2rem; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
            <div>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.45rem;">
                    <?= icon('applications', '', 18) ?> <span>Tiến Độ & Trạng Thái Các Hồ Sơ Ứng Tuyển</span>
                </h3>
                <p style="margin: 0.2rem 0 0 0; font-size: 0.85rem; color: #64748b;">
                    Theo dõi tiến độ duyệt hồ sơ và phản hồi từ các nhà tuyển dụng trong thời gian thực
                </p>
            </div>
            <a href="<?= url('/seeker/applied-jobs') ?>" class="btn btn-outline btn-sm" style="font-weight: 700; font-size: 0.82rem;">
                Xem tất cả (<?= count($appliedJobs) ?> đơn) &rarr;
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
            <?php foreach (array_slice($appliedJobs, 0, 4) as $app): ?>
                <?php
                    $reqStatus = $app['request_status'] ?? 'pending';
                    $hasMeeting = !empty($app['meeting_id']) || !empty($app['meeting_date']);
                    $meetingDate = $app['meeting_date'] ?? null;
                    $isCompleted = ($app['meeting_status'] ?? '') === 'completed' || (!empty($meetingDate) && strtotime($meetingDate) < strtotime(date('Y-m-d')));

                    if ($reqStatus === 'accepted') {
                        $percent = 100;
                        $statusLabel = 'Trúng Tuyển (100%)';
                        $statusClass = 'badge-success';
                        $statusDesc = 'Đã duyệt nhận việc! Hãy xem checklist ngày đầu đi làm';
                        $barColor = '#10b981';
                    } elseif ($reqStatus === 'rejected') {
                        $percent = 100;
                        $statusLabel = 'Đã Xét Duyệt';
                        $statusClass = 'badge-danger';
                        $statusDesc = 'Cảm ơn bạn! Đã lưu hồ sơ hẹn dịp phỏng vấn sau';
                        $barColor = '#ef4444';
                    } elseif ($isCompleted) {
                        $percent = 75;
                        $statusLabel = 'Đã PV Xong (75%)';
                        $statusClass = 'badge-primary';
                        $statusDesc = 'Hoàn thành phỏng vấn - Đang chờ công bố kết quả';
                        $barColor = '#0d9488';
                    } elseif ($hasMeeting) {
                        $percent = 50;
                        $statusLabel = 'Đã Lên Lịch (50%)';
                        $statusClass = 'badge-warning';
                        $statusDesc = 'Chuẩn bị phỏng vấn vào ' . (!empty($meetingDate) ? date('d/m/Y', strtotime($meetingDate)) : 'sắp tới');
                        $barColor = '#f59e0b';
                    } else {
                        $percent = 25;
                        $statusLabel = 'Đã Nộp (25%)';
                        $statusClass = 'badge-slate';
                        $statusDesc = 'Hồ sơ đang chờ doanh nghiệp xem xét';
                        $barColor = '#64748b';
                    }
                ?>
                <div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.1rem; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.4rem;">
                            <h4 style="margin: 0; font-size: 0.98rem; font-weight: 800; color: #0f172a; line-height: 1.35;">
                                <a href="<?= url('/jobs/' . $app['job_post_id']) ?>" style="color: #0f172a; text-decoration: none;">
                                    <?= htmlspecialchars($app['job_title']) ?>
                                </a>
                            </h4>
                            <span class="badge <?= $statusClass ?>" style="font-size: 0.72rem; padding: 0.2rem 0.55rem; white-space: nowrap;">
                                <span><?= $statusLabel ?></span>
                            </span>
                        </div>

                        <!-- MINI PROGRESS BAR -->
                        <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 999px; overflow: hidden; margin-bottom: 0.6rem;">
                            <div style="width: <?= $percent ?>%; height: 100%; background: <?= $barColor ?>; border-radius: 999px;"></div>
                        </div>

                        <p style="margin: 0 0 0.5rem 0; font-size: 0.83rem; color: #64748b;">
                            <?= icon('building', '', 13) ?> <strong style="color: #334155;"><?= htmlspecialchars($app['employer_name']) ?></strong>
                        </p>

                        <div style="background: #f8fafc; border-radius: 6px; padding: 0.5rem 0.65rem; font-size: 0.78rem; color: #475569; margin-bottom: 0.75rem; border: 1px solid #f1f5f9;">
                            <div style="font-weight: 600; color: #334155;"><?= $statusDesc ?></div>
                            <div style="color: #94a3b8; margin-top: 0.2rem;">Ngày nộp: <?= formatDate($app['applied_at']) ?> • Lương: <?= formatSalary($app['salary_amount'], $app['employment_type'] ?? 'full_time') ?></div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 0.65rem;">
                        <a href="<?= url('/jobs/' . $app['job_post_id']) ?>" style="font-size: 0.78rem; color: #64748b; text-decoration: none; font-weight: 600;">
                            Xem bài đăng
                        </a>
                        <a href="<?= url('/seeker/applied-jobs') ?>" class="btn btn-outline btn-sm" style="font-size: 0.76rem; font-weight: 700; padding: 0.25rem 0.6rem;">
                            Chi tiết đơn &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>


<!-- ========================================================================= -->
<!-- KHỐI VIỆC LÀM ĐỀ XUẤT BỞI AI (AI MATCHING RECOMMENDATIONS)               -->
<!-- ========================================================================= -->
<div class="ai-recommend-container">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
        <div>
            <div class="ai-badge-header">
                <?= icon('sparkle', '', 14) ?> <span>JobLink AI Matching Engine</span>
            </div>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0;">
                Việc Làm Đề Xuất Phù Hợp Dành Riêng Cho Bạn
            </h2>
            <p style="color: #475569; font-size: 0.88rem; margin: 0.25rem 0 0 0;">
                Dựa trên kỹ năng chuyên môn, kinh nghiệm, khoảng cách địa lý và kỳ vọng thu nhập trong hồ sơ của bạn
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="<?= url('/seeker/profile') ?>" class="btn btn-outline btn-sm" style="font-size: 0.8rem; background: #ffffff; border-color: #0d9488; color: #0d9488; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                <?= icon('sparkle', '', 13) ?> Tối Ưu Hồ Sơ
            </a>
            <a href="<?= url('/jobs?ai_recommend=1') ?>" class="btn btn-primary btn-sm" style="font-size: 0.8rem; font-weight: 700;">
                Xem tất cả đề xuất &rarr;
            </a>
        </div>
    </div>

    <?php if (empty($aiRecommendedJobs)): ?>
        <div style="background: #ffffff; border-radius: 10px; padding: 2rem; text-align: center; border: 1px dashed #cbd5e1;">
            <p style="color: #64748b; margin: 0 0 0.5rem 0;">Chưa có đủ dữ liệu đề xuất hoặc chưa có việc làm phù hợp.</p>
            <a href="<?= url('/seeker/profile') ?>" class="btn btn-primary btn-sm">Cập nhật kỹ năng ngay</a>
        </div>
    <?php else: ?>
        <div class="ai-job-grid">
            <?php foreach ($aiRecommendedJobs as $ajob): ?>
                <div class="ai-job-card">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <div style="display: flex; gap: 0.75rem; align-items: center;">
                                <img src="<?= htmlspecialchars(!empty($ajob['avatar_url']) ? $ajob['avatar_url'] : 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=150') ?>" alt="logo" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0;">
                                <div>
                                    <h4 style="font-size: 1rem; font-weight: 800; margin: 0; color: #0f172a; line-height: 1.3;">
                                        <a href="<?= url('/jobs/' . $ajob['job_post_id']) ?>" style="text-decoration: none; color: inherit;">
                                            <?= htmlspecialchars($ajob['job_title']) ?>
                                        </a>
                                    </h4>
                                    <p style="font-size: 0.82rem; color: #64748b; margin: 0.2rem 0 0 0;"><?= htmlspecialchars($ajob['employer_name']) ?></p>
                                </div>
                            </div>
                            <span class="ai-match-badge <?= $ajob['ai_match_level'] ?>">
                                <?= icon('sparkle', '', 12) ?>
                                <span>Tương thích <?= $ajob['ai_match_score'] ?>%</span>
                            </span>
                        </div>

                        <!-- Progress bar -->
                        <div class="ai-progress-wrap">
                            <div class="ai-progress-bar <?= $ajob['ai_match_level'] ?>" style="width: <?= $ajob['ai_match_score'] ?>%;"></div>
                        </div>

                        <!-- Lương & Địa điểm -->
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; margin: 0.5rem 0;">
                            <strong style="color: #0d9488; font-size: 0.92rem;">
                                <?= formatSalary($ajob['salary_amount'], $ajob['employment_type'] ?? 'full_time') ?>
                            </strong>
                            <span style="color: #64748b; font-size: 0.78rem; display: flex; align-items: center; gap: 0.25rem;">
                                <?= icon('location', '', 12) ?> <?= htmlspecialchars(mb_substr($ajob['workplace_address'] ?? '', 0, 22)) ?>...
                            </span>
                        </div>

                        <!-- Kỹ năng trùng khớp -->
                        <?php if (!empty($ajob['ai_matched_skills'])): ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.3rem; margin: 0.5rem 0;">
                                <?php foreach (array_slice($ajob['ai_matched_skills'], 0, 3) as $msk): ?>
                                    <span class="ai-skill-tag matched" style="display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('check', '', 11) ?> <?= htmlspecialchars($msk) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Lý do AI đề xuất -->
                        <?php if (!empty($ajob['ai_reasons'])): ?>
                            <ul class="ai-reasons-list">
                                <?php foreach (array_slice($ajob['ai_reasons'], 0, 2) as $reason): ?>
                                    <li class="ai-reason-item">
                                        <span style="color: #0d9488; display: inline-flex; align-items: center;"><?= icon('check', '', 11) ?></span>
                                        <span><?= htmlspecialchars($reason) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; border-top: 1px solid #f1f5f9; padding-top: 0.75rem; gap: 0.5rem;">
                        <button type="button" class="btn btn-outline btn-sm" data-ai-job-fit="<?= $ajob['job_post_id'] ?>" style="font-size: 0.75rem; padding: 0.3rem 0.6rem; color: #0f766e; border-color: #99f6e4; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <?= icon('sparkle', '', 13) ?> Phân tích AI
                        </button>
                        <div style="display: flex; gap: 0.35rem;">
                            <?php if ($ajob['is_applied']): ?>
                                <span class="badge badge-success" style="font-size: 0.75rem;">Đã nộp đơn</span>
                            <?php else: ?>
                                <a href="<?= url('/jobs/' . $ajob['job_post_id']) ?>" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 0.3rem 0.75rem; font-weight: 700;">
                                    Ứng Tuyển
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: 3fr 2fr; gap: 1.5rem;">
    <!-- Đơn ứng tuyển gần đây -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--dark); cursor: pointer;" onclick="window.location.href='<?= url('/seeker/applied-jobs') ?>'">
                Hồ Sơ Ứng Tuyển Gần Đây
            </h3>
            <a href="<?= url('/seeker/applied-jobs') ?>" class="btn btn-outline btn-sm">Xem tất cả</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Công việc</th>
                        <th>Công ty</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appliedJobs)): ?>
                        <tr><td colspan="3" style="text-align: center; color: var(--slate-400); padding: 1.5rem;">Chưa có đơn ứng tuyển nào.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($appliedJobs, 0, 4) as $app): ?>
                            <tr style="cursor: pointer;" onclick="window.location.href='<?= url('/seeker/applied-jobs') ?>'">
                                <td>
                                    <a href="<?= url('/seeker/applied-jobs') ?>" style="text-decoration: none; color: #0f172a; font-weight: 700;">
                                        <?= htmlspecialchars($app['job_title']) ?>
                                    </a>
                                </td>
                                <td style="color: #334155;"><?= htmlspecialchars($app['employer_name']) ?></td>
                                <td>
                                    <?php if ($app['request_status'] === 'accepted'): ?>
                                        <span class="badge badge-success"><?= icon('check', '', 13) ?> <span>Được chấp nhận</span></span>
                                    <?php elseif ($app['request_status'] === 'rejected'): ?>
                                        <span class="badge badge-danger"><span>Từ chối</span></span>
                                    <?php else: ?>
                                        <span class="badge badge-warning"><span>Chờ duyệt</span></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lịch phỏng vấn sắp tới -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--dark); cursor: pointer;" onclick="window.location.href='<?= url('/seeker/meetings') ?>'">
                Cuộc Hẹn Gần Nhất
            </h3>
            <a href="<?= url('/seeker/meetings') ?>" class="btn btn-outline btn-sm">Tất cả</a>
        </div>
        <?php if (empty($meetings)): ?>
            <p style="color: var(--slate-400); text-align: center; padding: 1.5rem;">Chưa có lịch phỏng vấn nào.</p>
        <?php else: ?>
            <?php foreach (array_slice($meetings, 0, 3) as $m): ?>
                <div style="border-bottom: 1px solid var(--slate-100); padding-bottom: 0.75rem; margin-bottom: 0.75rem; cursor: pointer;" onclick="window.location.href='<?= url('/seeker/meetings') ?>'">
                    <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem;">
                        <a href="<?= url('/seeker/meetings') ?>" style="text-decoration: none; color: var(--primary);">
                            <?= htmlspecialchars($m['job_title']) ?>
                        </a>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--slate-600); margin: 0.2rem 0;">
                        <?= icon('employers', '', 14) ?> <span style="margin-left: 3px;"><?= htmlspecialchars($m['employer_name']) ?></span>
                    </div>
                    <div style="font-size: 0.82rem; color: var(--slate-500);">
                        <?= icon('calendar', '', 14) ?> <span style="margin-left: 3px;"><?= date('d/m/Y', strtotime($m['meeting_date'])) ?> lúc <?= substr($m['start_time'], 0, 5) ?> (<?= $m['meeting_format'] === 'online' ? 'Trực tuyến' : 'Trực tiếp' ?>)</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
