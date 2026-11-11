<?php 
$extraCss = ['css/jobs/ai_matching.css'];
$extraJs = ['js/jobs/ai_recommendation.js'];

// Danh sách ID việc làm đã lưu của ứng viên
$savedJobIds = !empty($savedJobs) ? array_map('intval', array_column($savedJobs, 'job_post_id')) : [];

// Danh sách đơn ứng tuyển đã được nhận việc / trúng tuyển
$acceptedJobs = array_filter($appliedJobs ?? [], function($j) {
    return in_array($j['request_status'] ?? '', ['accepted', 'invitation_accepted']);
});

// Lịch hẹn / phỏng vấn sắp diễn ra
$upcomingMeetings = array_filter($meetings ?? [], function($m) {
    $mStatus = $m['meeting_status'] ?? 'scheduled';
    return in_array($mStatus, ['scheduled', 'pending', 'confirmed']) || strtotime($m['meeting_date']) >= strtotime(date('Y-m-d'));
});

ob_start(); 
?>

<!-- KHU 1 CỦA KHU 3: HEADER CỐ ĐỊNH CHỈ CHỨA MỖI TIÊU ĐỀ -->
<div class="workspace-zone-header">
    <div class="workspace-header-title-box">
        <h1 class="workspace-page-title">
            Xin chào, <?= htmlspecialchars($seeker['full_name'] ?? 'Người lao động') ?>
        </h1>
        <p class="workspace-page-subtitle">Hoạt động & gợi ý việc làm hôm nay</p>
    </div>
</div>

<!-- KHU 2 CỦA KHU 3: BODY CHỨA TOÀN BỘ NỘI DUNG THỰC THI (CUỘN ĐỘC LẬP) -->
<div class="workspace-zone-body">
    <!-- 4 Ô THỐNG KÊ: [EMOJI] -> [NỘI DUNG] -> [SỐ LIỆU] -->
    <div class="dashboard-stats-strip" style="display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap; margin-bottom: 0.85rem;">
        <!-- Đã ứng tuyển -->
        <a href="<?= url('/seeker/applied-jobs') ?>" class="stat-pill-item workspace-nav-link" data-title="Đã Ứng Tuyển" title="Bấm để xem danh sách đơn đã ứng tuyển" style="text-decoration: none; color: inherit;">
            <span class="stat-pill-emoji">📄</span>
            <span class="stat-pill-label">Đã ứng tuyển</span>
            <span class="stat-pill-val" style="color: var(--primary);"><?= count($appliedJobs ?? []) ?></span>
        </a>

        <!-- Lịch phỏng vấn -->
        <a href="<?= url('/seeker/meetings') ?>" class="stat-pill-item workspace-nav-link" data-title="Lịch Phỏng Vấn" title="Bấm để xem lịch phỏng vấn" style="text-decoration: none; color: inherit;">
            <span class="stat-pill-emoji">📅</span>
            <span class="stat-pill-label">Lịch phỏng vấn</span>
            <span class="stat-pill-val" style="color: var(--success);"><?= count($meetings ?? []) ?></span>
        </a>

        <!-- Tin nhắn -->
        <a href="<?= url('/seeker/messages') ?>" class="stat-pill-item workspace-nav-link" data-title="Tin Nhắn" title="Bấm để mở hộp thư tin nhắn" style="text-decoration: none; color: inherit;">
            <span class="stat-pill-emoji">💬</span>
            <span class="stat-pill-label">Tin nhắn</span>
            <span class="stat-pill-val" style="color: #0284c7;"><?= count($messages ?? []) ?></span>
        </a>

        <!-- Đã lưu -->
        <a href="<?= url('/seeker/saved-jobs') ?>" class="stat-pill-item workspace-nav-link" data-title="Việc Đã Lưu" title="Bấm để xem việc làm đã lưu" style="text-decoration: none; color: inherit;">
            <span class="stat-pill-emoji">⭐</span>
            <span class="stat-pill-label">Đã lưu</span>
            <span class="stat-pill-val" id="header-saved-count" style="color: var(--warning);"><?= count($savedJobs ?? []) ?></span>
        </a>
    </div>

<!-- ========================================================================= -->
<!-- 2. KHỐI TRÚNG TUYỂN & HỒ SƠ ỨNG TUYỂN GẦN ĐÂY (TỈ LỆ 6:4 TRÊN CÙNG 1 DÒNG) -->
<!-- ========================================================================= -->
<?php if (!empty($acceptedJobs)): ?>
    <div class="dashboard-mid-grid">
        <!-- CỘT 60%: CHÚC MỪNG BẠN ĐÃ TRÚNG TUYỂN! VỚI THANH TIẾN TRÌNH TUYỂN DỤNG -->
        <div class="onboarding-banner" style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%); color: #ffffff; border-radius: 12px; padding: 0.65rem 0.85rem; box-shadow: 0 4px 14px rgba(5,150,105,0.15); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.45rem;">
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <div style="background: rgba(255,255,255,0.2); width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <?= icon('award', '', 16) ?>
                        </div>
                        <div>
                            <span style="font-size: 0.95rem; font-weight: 800; color: #ffffff; line-height: 1.2;">
                                Chúc Mừng Bạn Đã Trúng Tuyển!
                            </span>
                            <span style="font-size: 0.76rem; color: #d1fae5; margin-left: 0.35rem;">
                                Chuẩn bị cho ngày làm việc đầu tiên
                            </span>
                        </div>
                    </div>
                    <a href="<?= url('/seeker/applied-jobs') ?>" class="workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển" style="color: #ffffff; font-size: 0.72rem; font-weight: 700; background: rgba(255,255,255,0.2); padding: 0.2rem 0.55rem; border-radius: 6px; text-decoration: none; border: 1px solid rgba(255,255,255,0.3); transition: background 0.15s ease;">
                        Xem tất cả
                    </a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                    <?php foreach ($acceptedJobs as $aj): 
                        $onb = !empty($aj['onboarding_details']) ? json_decode($aj['onboarding_details'], true) : null;
                    ?>
                        <div style="background: #ffffff; color: #0f172a; border-radius: 10px; padding: 0.65rem 0.85rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                            <!-- Dòng 1: Tiêu đề việc làm + Doanh nghiệp + Mã hồ sơ + Nút thao tác -->
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.35rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                    <span class="badge badge-success" style="font-size: 0.68rem; font-weight: 700; padding: 0.15rem 0.45rem;">
                                        Đã nhận việc
                                    </span>
                                    <h4 style="margin: 0; font-size: 0.92rem; font-weight: 800; color: #0f172a; line-height: 1.3;">
                                        <a href="<?= url('/seeker/applied-jobs') ?>" class="workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển" style="color: #0f172a; text-decoration: none;">
                                            <?= htmlspecialchars($aj['job_title']) ?>
                                        </a>
                                    </h4>
                                    <span style="font-size: 0.8rem; color: #0d9488; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('building', '', 12) ?>
                                        <?php if (!empty($aj['employer_id'])): ?>
                                            <a href="<?= url('/employers/' . $aj['employer_id']) ?>" data-title="DN: <?= htmlspecialchars($aj['employer_name']) ?>" style="color: #0d9488; text-decoration: none;" title="Xem trang doanh nghiệp">
                                                <?= htmlspecialchars($aj['employer_name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span><?= htmlspecialchars($aj['employer_name']) ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <span style="font-size: 0.72rem; color: #64748b;">(Mã #<?= $aj['request_id'] ?>)</span>
                                </div>

                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                    <a href="<?= url('/seeker/messages?partner_id=' . ($aj['employer_account_id'] ?? 0)) ?>" class="btn btn-outline btn-sm workspace-nav-link" data-title="Tin Nhắn" style="font-size: 0.72rem; padding: 0.2rem 0.5rem; color: #0d9488; border-color: #0d9488; height: auto;">
                                        <?= icon('chat', '', 12) ?> <span>Nhắn tin</span>
                                    </a>
                                    <a href="<?= url('/seeker/applied-jobs') ?>" class="btn btn-success btn-sm workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển" style="font-size: 0.72rem; padding: 0.2rem 0.5rem; font-weight: 700; background: #059669; border-color: #059669; color: #ffffff; height: auto;">
                                        <span>Chi tiết</span>
                                    </a>
                                </div>
                            </div>

                            <!-- THANH TIẾN TRÌNH: NỘP HỒ SƠ ỨNG TUYỂN -> XÉT DUYỆT HỒ SƠ -> PHỎNG VẤN -> BẮT ĐẦU ĐI LÀM -->
                            <div class="hiring-stepper" style="margin: 0.45rem 0; padding: 0.45rem 0.65rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; position: relative;">
                                    <!-- Thanh nối ngang màu xanh -->
                                    <div style="position: absolute; top: 11px; left: 12%; right: 12%; height: 3px; background: #10b981; z-index: 1;"></div>
                                    
                                    <!-- Bước 1: Nộp hồ sơ ứng tuyển -->
                                    <div style="position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; text-align: center; flex: 1;">
                                        <div style="width: 22px; height: 22px; border-radius: 50%; background: #10b981; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                            ✓
                                        </div>
                                        <span style="font-size: 0.7rem; font-weight: 600; color: #334155; margin-top: 0.2rem; line-height: 1.2;">Nộp hồ sơ ứng tuyển</span>
                                    </div>

                                    <!-- Bước 2: Xét duyệt hồ sơ -->
                                    <div style="position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; text-align: center; flex: 1;">
                                        <div style="width: 22px; height: 22px; border-radius: 50%; background: #10b981; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                            ✓
                                        </div>
                                        <span style="font-size: 0.7rem; font-weight: 600; color: #334155; margin-top: 0.2rem; line-height: 1.2;">Xét duyệt hồ sơ</span>
                                    </div>

                                    <!-- Bước 3: Phỏng vấn -->
                                    <div style="position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; text-align: center; flex: 1;">
                                        <div style="width: 22px; height: 22px; border-radius: 50%; background: #10b981; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                            ✓
                                        </div>
                                        <span style="font-size: 0.7rem; font-weight: 600; color: #334155; margin-top: 0.2rem; line-height: 1.2;">Phỏng vấn</span>
                                    </div>

                                    <!-- Bước 4: Bắt đầu đi làm -->
                                    <div style="position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; text-align: center; flex: 1;">
                                        <div style="width: 22px; height: 22px; border-radius: 50%; background: #059669; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800; border: 2px solid #ffffff; box-shadow: 0 0 0 3px rgba(5,150,105,0.25);">
                                            ★
                                        </div>
                                        <span style="font-size: 0.7rem; font-weight: 800; color: #059669; margin-top: 0.2rem; line-height: 1.2;">Bắt đầu đi làm</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Dòng 2: Chi tiết bắt đầu, địa điểm, liên hệ -->
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 0.35rem 0.65rem; font-size: 0.78rem; color: #166534; display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem 1rem;">
                                <div style="display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <?= icon('calendar', '', 12) ?> <span><strong>Bắt đầu:</strong> 
                                    <?= !empty($onb['start_date']) ? date('d/m/Y', strtotime($onb['start_date'])) : 'Theo trao đổi' ?> 
                                    (<?= htmlspecialchars($onb['start_time'] ?? '08:00') ?>)</span>
                                </div>
                                <div style="display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <?= icon('map_pin', '', 12) ?> <span><strong>Địa điểm:</strong> 
                                    <?php 
                                        $dashWorkplace = $onb['workplace'] ?? ($aj['workplace_address'] ?? 'Tại nơi làm việc');
                                        $dashMapUrl = getGoogleMapsUrl($dashWorkplace, $aj['workplace_map_url'] ?? '');
                                    ?>
                                    <a href="<?= htmlspecialchars($dashMapUrl) ?>" target="_blank" rel="noopener noreferrer" style="color: #166534; font-weight: 700; text-decoration: none;" title="Mở vị trí trên Google Maps">
                                        <?= htmlspecialchars($dashWorkplace) ?>
                                    </a></span>
                                </div>
                                <div style="display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <?= icon('user', '', 12) ?> <span><strong>Liên hệ:</strong> <?= htmlspecialchars($onb['contact_person'] ?? ($aj['employer_name'] ?? 'Phòng Nhân Sự')) ?></span>
                                    <?php if (!empty($aj['employer_phone'])): ?>
                                        · <span style="display: inline-flex; align-items: center; gap: 0.15rem;"><?= icon('phone', '', 11) ?> <?= htmlspecialchars($aj['employer_phone']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($onb['notes'])): ?>
                                    <div style="display: inline-flex; align-items: center; gap: 0.25rem; font-style: italic; color: #475569;">
                                        <?= icon('chat', '', 11) ?> <span>"<?= htmlspecialchars($onb['notes']) ?>"</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- CỘT 40%: HỒ SƠ ỨNG TUYỂN GẦN ĐÂY -->
        <div class="card recent-apps-card" style="border-radius: 12px; padding: 0.65rem 0.85rem; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03); background: #ffffff;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem; padding-bottom: 0.35rem; border-bottom: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 6px; background: #f0fdfa; color: #0d9488;">
                            <?= icon('applications', '', 13) ?>
                        </span>
                        <a href="<?= url('/seeker/applied-jobs') ?>" class="workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển" style="font-size: 0.92rem; font-weight: 800; color: var(--dark); margin: 0; text-decoration: none;">
                            Hồ Sơ Ứng Tuyển Gần Đây
                        </a>
                    </div>
                    <a href="<?= url('/seeker/applied-jobs') ?>" class="workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển" style="font-size: 0.72rem; font-weight: 700; color: #0d9488; text-decoration: none;">
                        Xem tất cả
                    </a>
                </div>

                <?php if (empty($appliedJobs)): ?>
                    <p style="color: var(--slate-400); text-align: center; padding: 1.5rem 0; font-size: 0.8rem; margin: 0;">Chưa có đơn ứng tuyển nào.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                        <?php foreach (array_slice($appliedJobs, 0, 3) as $app): ?>
                            <a href="<?= url('/seeker/applied-jobs') ?>" class="workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển" style="padding: 0.4rem 0.55rem; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; cursor: pointer; transition: all 0.15s ease; text-decoration: none; color: inherit; display: block;">
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.35rem;">
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-weight: 700; font-size: 0.82rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars($app['job_title']) ?>
                                        </div>
                                        <div style="font-size: 0.74rem; color: #64748b; margin-top: 0.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars($app['employer_name']) ?>
                                        </div>
                                    </div>
                                    <div style="flex-shrink: 0;">
                                        <?php if (in_array($app['request_status'], ['accepted', 'invitation_accepted'])): ?>
                                            <span class="badge badge-success" style="font-size: 0.68rem; padding: 0.15rem 0.4rem; font-weight: 700;">Được nhận việc</span>
                                        <?php elseif ($app['request_status'] === 'rejected'): ?>
                                            <span class="badge badge-danger" style="font-size: 0.68rem; padding: 0.15rem 0.4rem;">Từ chối</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning" style="font-size: 0.68rem; padding: 0.15rem 0.4rem;">Đang duyệt</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- NẾU CHƯA CÓ ĐƠN TRÚNG TUYỂN: HIỂN THỊ HỒ SƠ ỨNG TUYỂN GẦN ĐÂY GỌN GÀNG -->
    <div class="card" style="border-radius: 12px; padding: 0.65rem 0.85rem; margin-bottom: 0.85rem; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03); background: #ffffff;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem; padding-bottom: 0.35rem; border-bottom: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; gap: 0.35rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 6px; background: #f0fdfa; color: #0d9488;">
                    <?= icon('applications', '', 13) ?>
                </span>
                <a href="<?= url('/seeker/applied-jobs') ?>" class="workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển" style="font-size: 0.92rem; font-weight: 800; color: var(--dark); margin: 0; text-decoration: none;">
                    Hồ Sơ Ứng Tuyển Gần Đây
                </a>
            </div>
            <a href="<?= url('/seeker/applied-jobs') ?>" class="workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển" style="font-size: 0.72rem; font-weight: 700; color: #0d9488; text-decoration: none;">
                Xem tất cả
            </a>
        </div>

        <?php if (empty($appliedJobs)): ?>
            <p style="color: var(--slate-400); text-align: center; padding: 1rem 0; font-size: 0.8rem; margin: 0;">Chưa có đơn ứng tuyển nào.</p>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 0.45rem;">
                <?php foreach (array_slice($appliedJobs, 0, 4) as $app): ?>
                    <a href="<?= url('/seeker/applied-jobs') ?>" class="workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển" style="padding: 0.45rem 0.6rem; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; cursor: pointer; transition: all 0.15s ease; text-decoration: none; color: inherit; display: block;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.35rem;">
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 700; font-size: 0.82rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($app['job_title']) ?>
                                </div>
                                <div style="font-size: 0.74rem; color: #64748b; margin-top: 0.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($app['employer_name']) ?>
                                </div>
                            </div>
                            <div style="flex-shrink: 0;">
                                <?php if (in_array($app['request_status'], ['accepted', 'invitation_accepted'])): ?>
                                    <span class="badge badge-success" style="font-size: 0.68rem; padding: 0.15rem 0.4rem; font-weight: 700;">Được nhận việc</span>
                                <?php elseif ($app['request_status'] === 'rejected'): ?>
                                    <span class="badge badge-danger" style="font-size: 0.68rem; padding: 0.15rem 0.4rem;">Từ chối</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.68rem; padding: 0.15rem 0.4rem;">Đang duyệt</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- 3. KHỐI LỊCH PHỎNG VẤN SẮP DIỄN RA (NẾU CÓ LỊCH HẸN)                       -->
<!-- ========================================================================= -->
<?php if (!empty($upcomingMeetings)): ?>
    <div class="onboarding-banner" style="background: linear-gradient(135deg, #065f46 0%, #0d9488 100%); color: #ffffff; border-radius: 12px; padding: 0.65rem 0.85rem; margin-bottom: 0.85rem; box-shadow: 0 4px 14px rgba(13,148,136,0.15);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.45rem;">
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <div style="background: rgba(255,255,255,0.2); width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <?= icon('calendar', '', 16) ?>
                </div>
                <div>
                    <span style="font-size: 0.95rem; font-weight: 800; color: #ffffff; line-height: 1.2;">
                        Lịch Phỏng Vấn Sắp Diễn Ra (<?= count($upcomingMeetings) ?>)
                    </span>
                    <span style="font-size: 0.78rem; color: #a7f3d0; margin-left: 0.4rem;">
                        Kiểm tra thời gian và tham gia đúng hẹn
                    </span>
                </div>
            </div>
            <a href="<?= url('/seeker/meetings') ?>" class="workspace-nav-link" data-title="Lịch Hẹn & Phỏng Vấn" style="color: #ffffff; font-size: 0.75rem; font-weight: 700; background: rgba(255,255,255,0.18); padding: 0.25rem 0.6rem; border-radius: 6px; text-decoration: none; border: 1px solid rgba(255,255,255,0.3); transition: background 0.15s ease;">
                Xem tất cả
            </a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.45rem;">
            <?php foreach (array_slice($upcomingMeetings, 0, 3) as $m): ?>
                <div style="background: #ffffff; color: #0f172a; border-radius: 10px; padding: 0.65rem 0.85rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.35rem;">
                        <div style="display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
                            <span class="badge badge-success" style="font-size: 0.7rem; padding: 0.15rem 0.45rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= ($m['meeting_format'] === 'online') ? (icon('globe', '', 11) . ' Trực tuyến') : (icon('map_pin', '', 11) . ' Trực tiếp') ?>
                            </span>
                            <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #0f172a; line-height: 1.3;">
                                <a href="<?= url('/seeker/meetings') ?>" class="workspace-nav-link" data-title="Lịch Hẹn & Phỏng Vấn" style="color: #0f172a; text-decoration: none;">
                                    <?= htmlspecialchars($m['job_title']) ?>
                                </a>
                            </h4>
                            <span style="font-size: 0.82rem; color: #475569; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('building', '', 12) ?> 
                                <?php if (!empty($m['employer_id'])): ?>
                                    <a href="<?= url('/employers/' . $m['employer_id']) ?>" data-title="DN: <?= htmlspecialchars($m['employer_name']) ?>" style="color: #0d9488; font-weight: 700; text-decoration: none;" title="Xem trang doanh nghiệp">
                                        <?= htmlspecialchars($m['employer_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <strong><?= htmlspecialchars($m['employer_name']) ?></strong>
                                <?php endif; ?>
                            </span>
                        </div>

                        <a href="<?= url('/seeker/meetings') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Lịch Hẹn & Phỏng Vấn" style="font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 6px; height: auto;">
                            Chi Tiết Lịch Hẹn
                        </a>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.35rem 0.65rem; font-size: 0.78rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem 1.25rem;">
                        <div style="color: #0d9488; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <?= icon('calendar', '', 12) ?>
                            <span><?= formatDate($m['meeting_date']) ?> lúc <?= substr($m['start_time'], 0, 5) ?><?= !empty($m['end_time']) ? ' - ' . substr($m['end_time'], 0, 5) : '' ?></span>
                        </div>
                        <?php if (!empty($m['location_or_meeting_link'])): ?>
                            <div style="color: #64748b; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <strong><?= ($m['meeting_format'] === 'online') ? (icon('link', '', 11) . ' Link:') : (icon('map_pin', '', 11) . ' Địa điểm:') ?></strong>
                                <?php if ($m['meeting_format'] === 'online' && (strpos($m['location_or_meeting_link'], 'http') === 0)): ?>
                                    <a href="<?= htmlspecialchars($m['location_or_meeting_link']) ?>" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none;">
                                        <?= htmlspecialchars($m['location_or_meeting_link']) ?>
                                    </a>
                                <?php else: ?>
                                    <?php 
                                        $dashMeetMap = getGoogleMapsUrl($m['location_or_meeting_link'], $m['workplace_map_url'] ?? '');
                                    ?>
                                    <a href="<?= htmlspecialchars($dashMeetMap) ?>" target="_blank" rel="noopener noreferrer" style="color: #0d9488; font-weight: 600; text-decoration: none;" title="Mở vị trí trên Google Maps">
                                        <?= htmlspecialchars($m['location_or_meeting_link']) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- 4. KHỐI TIN NHẮN TRAO ĐỔI TUYỂN DỤNG (NẾU CÓ TIN NHẮN)                    -->
<!-- ========================================================================= -->
<?php if (!empty($messages)): ?>
    <div class="card" style="margin-bottom: 0.85rem; border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.02); padding: 0.75rem 0.95rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.45rem;">
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <div style="background: #f0fdfa; color: #0d9488; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; border: 1px solid #99f6e4;">
                    <?= icon('chat', '', 14) ?>
                </div>
                <div>
                    <span style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">
                        Tin Nhắn Trao Đổi Tuyển Dụng (<?= count($messages) ?>)
                    </span>
                    <span style="font-size: 0.78rem; color: #64748b; margin-left: 0.4rem;">
                        Cuộc trò chuyện, thư phản hồi từ nhà tuyển dụng
                    </span>
                </div>
            </div>
            <a href="<?= url('/seeker/messages') ?>" class="btn btn-outline btn-sm workspace-nav-link" data-title="Tin Nhắn" style="font-weight: 700; font-size: 0.75rem; padding: 0.25rem 0.55rem; height: auto;">
                Mở hộp thư
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 0.5rem;">
            <?php foreach (array_slice($messages, 0, 3) as $msg): ?>
                <a href="<?= url('/seeker/messages') ?>" class="workspace-nav-link" data-title="Tin Nhắn" style="display: block; text-decoration: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.55rem 0.75rem; transition: background 0.15s ease;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                        <strong style="color: #0f172a; font-size: 0.85rem;"><?= htmlspecialchars($msg['sender_name'] ?? 'Nhà Tuyển Dụng') ?></strong>
                        <span style="font-size: 0.7rem; color: #94a3b8;"><?= date('d/m/Y H:i', strtotime($msg['sent_at'])) ?></span>
                    </div>
                    <p style="margin: 0; font-size: 0.78rem; color: #475569; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <?php if (!empty($msg['message_title'])): ?>
                            <strong style="color: #0d9488;"><?= htmlspecialchars($msg['message_title']) ?>:</strong> 
                        <?php endif; ?>
                        <?= htmlspecialchars($msg['message_content']) ?>
                    </p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- 5. KHỐI VIỆC LÀM JOBY ĐỀ XUẤT PHÙ HỢP DÀNH RIÊNG CHO BẠN                 -->
<!-- ========================================================================= -->
<div class="ai-recommend-container">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.65rem;">
        <div>
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('sparkle', '', 16) ?>
                <span>Việc Làm Joby Đề Xuất Phù Hợp Dành Riêng Cho Bạn</span>
            </h2>
        </div>
        <div class="ai-header-actions" style="display: flex; gap: 0.4rem; align-items: center;">
            <a href="<?= url('/seeker/profile') ?>" class="btn btn-outline btn-sm workspace-nav-link" data-title="Hồ Sơ Năng Lực" style="font-size: 0.75rem; padding: 0.25rem 0.55rem; background: #0d9488; border-color: #0d9488; color: #ffffff; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem; height: auto;">
                <?= icon('sparkle', '', 12) ?> <span>Tối Ưu Hồ Sơ</span>
            </a>
            <a href="<?= url('/jobs') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Tìm Việc & Gợi Ý AI" style="font-size: 0.75rem; padding: 0.25rem 0.6rem; font-weight: 700; height: auto;">
                <span>Xem tất cả</span>
            </a>
        </div>
    </div>

    <?php if (empty($aiRecommendedJobs)): ?>
        <div style="background: #ffffff; border-radius: 8px; padding: 1.25rem; text-align: center; border: 1px dashed #cbd5e1;">
            <p style="color: #64748b; margin: 0 0 0.4rem 0; font-size: 0.82rem;">Chưa có đủ dữ liệu đề xuất hoặc chưa có việc làm phù hợp.</p>
            <a href="<?= url('/seeker/profile') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Hồ Sơ Năng Lực" style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">Cập nhật kỹ năng ngay</a>
        </div>
    <?php else: ?>
        <div class="ai-job-grid">
            <?php foreach ($aiRecommendedJobs as $ajob): 
                $isJobSaved = in_array(intval($ajob['job_post_id']), $savedJobIds);
            ?>
                <div class="ai-job-card" onclick="if (!event.target.closest('a') && !event.target.closest('button') && !event.target.closest('.ai-match-badge')) { const l = this.querySelector('.ai-job-title a'); if (l) l.click(); }" style="cursor: pointer;">
                    <span class="ai-match-badge <?= $ajob['ai_match_level'] ?> ai-match-badge-interactive" 
                          data-score="<?= $ajob['ai_match_score'] ?>"
                          data-label="<?= htmlspecialchars($ajob['ai_match_label'] ?? 'Phù hợp') ?>"
                          data-level="<?= htmlspecialchars($ajob['ai_match_level'] ?? 'medium') ?>"
                          data-job-title="<?= htmlspecialchars($ajob['job_title'] ?? '') ?>"
                          data-employer-name="<?= htmlspecialchars($ajob['employer_name'] ?? '') ?>"
                          data-job-url="<?= url('/jobs/' . $ajob['job_post_id']) ?>"
                          data-breakdown='<?= htmlspecialchars(json_encode($ajob['ai_breakdown'] ?? []), ENT_QUOTES, 'UTF-8') ?>'
                          data-reasons='<?= htmlspecialchars(json_encode($ajob['ai_reasons'] ?? []), ENT_QUOTES, 'UTF-8') ?>'
                          data-matched='<?= htmlspecialchars(json_encode($ajob['ai_matched_skills'] ?? []), ENT_QUOTES, 'UTF-8') ?>'
                          data-missing='<?= htmlspecialchars(json_encode($ajob['ai_missing_skills'] ?? []), ENT_QUOTES, 'UTF-8') ?>'
                          data-criteria='<?= htmlspecialchars(json_encode($ajob['ai_employer_criteria'] ?? []), ENT_QUOTES, 'UTF-8') ?>'
                          onmouseenter="showAiScoreHoverCard(this, event);"
                          onmouseleave="hideAiScoreHoverCard();"
                          onclick="event.stopPropagation(); showAiScoreModal(this);"
                          title="Rê chuột xem phân tích thông số hoặc bấm xem đối chiếu chi tiết">
                        <?= icon('sparkle', '', 10) ?>
                        <span class="match-text-full">Tương thích </span><span><?= $ajob['ai_match_score'] ?>%</span>
                    </span>

                    <div class="ai-job-main-info">
                        <div class="ai-job-header-row">
                            <?php 
                                $cardCompanyLogo = !empty($ajob['avatar_url']) ? getAvatarUrl($ajob['avatar_url'], 'EMPLOYER') : asset('uploads/avatars/employers/employer_default.png');
                            ?>
                            <img src="<?= htmlspecialchars($cardCompanyLogo) ?>" 
                                 alt="<?= htmlspecialchars($ajob['employer_name'] ?? 'Doanh nghiệp') ?>" 
                                 class="ai-job-logo" 
                                 onerror="this.onerror=null; this.src='<?= asset('uploads/avatars/employers/employer_default.png') ?>';">
                            <div class="ai-job-titles">
                                <h4 class="ai-job-title" style="font-size: 0.88rem;">
                                    <a href="<?= url('/jobs/' . $ajob['job_post_id']) ?>" class="workspace-nav-link" data-title="Tin: <?= htmlspecialchars($ajob['job_title']) ?>">
                                        <?= htmlspecialchars($ajob['job_title']) ?>
                                    </a>
                                </h4>
                                <p class="ai-job-company" style="font-size: 0.78rem;">
                                    <?php if (!empty($ajob['employer_id'])): ?>
                                        <a href="<?= url('/employers/' . $ajob['employer_id']) ?>" class="workspace-nav-link" data-title="DN: <?= htmlspecialchars($ajob['employer_name']) ?>" style="color: #64748b; font-weight: 700; text-decoration: none;" title="Xem trang cá nhân của doanh nghiệp">
                                            <?= htmlspecialchars($ajob['employer_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($ajob['employer_name']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <!-- DÒNG 1: MỨC LƯƠNG -->
                        <div class="ai-job-salary-row" style="margin-top: 0.35rem; font-size: 0.84rem;">
                            <strong class="ai-job-salary" style="color: #0d9488; font-weight: 800;">
                                <?= formatSalary($ajob['salary_amount'], $ajob['employment_type'] ?? 'full_time') ?>
                            </strong>
                        </div>

                        <!-- DÒNG 2: ĐỊA ĐIỂM RIÊNG BIỆT DƯỚI DÒNG LƯƠNG (CÓ ICON ĐỊA ĐIỂM, KHÔNG GẠCH CHÂN) -->
                        <div class="ai-job-location-row" style="margin-top: 0.2rem; font-size: 0.76rem; color: #64748b; display: flex; align-items: center; gap: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <span style="flex-shrink: 0; color: #94a3b8; display: inline-flex; align-items: center;"><?= icon('map_pin', '', 12) ?></span>
                            <?php 
                                $ajMapUrl = getGoogleMapsUrl($ajob['workplace_address'] ?? 'Tại nơi làm việc', $ajob['workplace_map_url'] ?? '');
                            ?>
                            <a href="<?= htmlspecialchars($ajMapUrl) ?>" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();" style="color: #64748b; text-decoration: none; overflow: hidden; text-overflow: ellipsis;" title="Mở vị trí trên Google Maps: <?= htmlspecialchars($ajob['workplace_address'] ?? '') ?>">
                                <?= htmlspecialchars($ajob['workplace_address'] ?? 'Tại nơi làm việc') ?>
                            </a>
                        </div>

                        <!-- Progress bar tương thích -->
                        <div class="ai-progress-wrap">
                            <div class="ai-progress-bar <?= $ajob['ai_match_level'] ?>" style="width: <?= $ajob['ai_match_score'] ?>%;"></div>
                        </div>

                        <!-- Kỹ năng trùng khớp -->
                        <?php if (!empty($ajob['ai_matched_skills'])): ?>
                            <div class="ai-matched-skills-row" style="display: flex; flex-wrap: wrap; gap: 0.25rem; margin: 0.35rem 0;">
                                <?php foreach (array_slice($ajob['ai_matched_skills'], 0, 3) as $msk): ?>
                                    <span class="ai-skill-tag matched" style="display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.72rem; padding: 0.15rem 0.4rem;"><?= icon('check', '', 10) ?> <?= htmlspecialchars($msk) ?></span>
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

                    <!-- Nút thao tác: NÚT LƯU BÊN TRÁI + NÚT ỨNG TUYỂN BÊN PHẢI (ĐÃ BỎ NÚT AI MATCH) -->
                    <div class="job-card-actions" style="margin-top: 0.5rem; display: flex; gap: 0.4rem; align-items: center;">
                        <button type="button" 
                                class="btn btn-sm btn-seeker-save <?= $isJobSaved ? 'btn-primary' : 'btn-outline' ?>" 
                                data-job-id="<?= $ajob['job_post_id'] ?>" 
                                onclick="toggleSeekerSaveJob(this, <?= $ajob['job_post_id'] ?>); event.stopPropagation();"
                                title="<?= $isJobSaved ? 'Bỏ lưu việc làm' : 'Lưu vào danh sách việc làm đã lưu' ?>" 
                                style="font-size: 0.75rem; padding: 0.25rem 0.6rem; height: auto; display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 700; border-radius: 6px; white-space: nowrap;">
                            <?= icon('bookmark', '', 12) ?> 
                            <span class="save-btn-text"><?= $isJobSaved ? 'Đã lưu' : 'Lưu' ?></span>
                        </button>
                        <div style="flex: 1;">
                            <?php if ($ajob['is_applied']): ?>
                                <span class="badge badge-success" style="font-size: 0.72rem; padding: 0.25rem 0.6rem; display: inline-flex; align-items: center; justify-content: center; width: 100%; height: auto;">Đã nộp đơn</span>
                            <?php else: ?>
                                <a href="<?= url('/jobs/' . $ajob['job_post_id']) ?>" onclick="event.stopPropagation();" class="btn btn-primary btn-sm workspace-nav-link" data-title="Tin: <?= htmlspecialchars($ajob['job_title']) ?>" style="font-weight: 700; width: 100%; font-size: 0.75rem; padding: 0.25rem 0.6rem; height: auto; display: inline-flex; align-items: center; justify-content: center;">
                                    <span>Ứng Tuyển</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- FLOATING HOVER CARD GIẢI THÍCH THÔNG SỐ ĐỘ TƯƠNG THÍCH AI KHI RÊ CHUỘT -->
<div id="aiScoreHoverCard" class="ai-score-hover-card" onmouseenter="clearAiHoverTimeout();" onmouseleave="hideAiScoreHoverCard();"></div>

<!-- MODAL ĐỐI CHIẾU CHI TIẾT ĐỘ TƯƠNG THÍCH AI KHI CLICK VÀO % -->
<div id="seekerScoreReasonModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 100003; justify-content: center; align-items: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); padding: 1.5rem; box-sizing: border-box;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.45rem; font-weight: 800;">
                <span style="color: #0d9488;"><?= icon('sparkle', '', 18) ?></span>
                <span>Tại Sao Bạn Đạt <span id="seekerModalScoreVal" style="color: #0d9488;">--%</span> Tương Thích?</span>
            </h3>
            <button type="button" onclick="closeSeekerScoreModal()" style="border: none; background: #f1f5f9; color: #64748b; font-size: 1.2rem; cursor: pointer; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; line-height: 1;">
                ✕
            </button>
        </div>

        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 0.95rem; margin-bottom: 1rem; font-size: 0.82rem; color: #475569;">
            <div>Vị trí đối chiếu: <strong id="seekerModalJobTitle" style="color: #0d9488; font-size: 0.9rem;"></strong></div>
            <div style="margin-top: 3px;">Doanh nghiệp: <strong id="seekerModalEmployer" style="color: #0f172a;"></strong></div>
        </div>

        <!-- BẢNG ĐỐI CHIẾU TIÊU CHÍ (YÊU CẦU NTD VS THỰC TẾ NLD) -->
        <div style="margin-bottom: 1rem;">
            <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.45rem;">
                Bảng đối chiếu thông số thực tế giữa bạn và công việc:
            </div>
            <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem; text-align: left;">
                    <thead>
                        <tr style="background: #f1f5f9; color: #334155; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 0.5rem 0.65rem; font-weight: 700;">Tiêu chí tuyển dụng</th>
                            <th style="padding: 0.5rem 0.65rem; font-weight: 700;">Yêu cầu của NTD</th>
                            <th style="padding: 0.5rem 0.65rem; font-weight: 700;">Hồ sơ thực tế của bạn</th>
                            <th style="padding: 0.5rem 0.65rem; font-weight: 700; text-align: center;">Đánh giá</th>
                        </tr>
                    </thead>
                    <tbody id="seekerModalCriteriaTable">
                        <!-- Rendered by JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3 KHỐI: ĐẠT ĐƯỢC - CHƯA ĐẠT -> KẾT QUẢ TỔNG HỢP -->
        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.25rem;">
            <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 0.75rem 0.85rem;">
                <strong style="color: #166534; font-size: 0.82rem; display: flex; align-items: center; gap: 4px; margin-bottom: 0.3rem;">
                    ✓ 1. Tiêu chí đạt được (Điểm cộng nổi bật):
                </strong>
                <div id="seekerModalMatchedText" style="font-size: 0.78rem; color: #14532d; line-height: 1.45;"></div>
            </div>

            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.75rem 0.85rem;">
                <strong style="color: #92400e; font-size: 0.82rem; display: flex; align-items: center; gap: 4px; margin-bottom: 0.3rem;">
                    ✕ 2. Tiêu chí cần hoàn thiện hoặc lưu ý thêm:
                </strong>
                <div id="seekerModalMissingText" style="font-size: 0.78rem; color: #78350f; line-height: 1.45;"></div>
            </div>

            <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 0.75rem 0.85rem;">
                <strong style="color: #0f766e; font-size: 0.82rem; display: flex; align-items: center; gap: 4px; margin-bottom: 0.3rem;">
                    ➔ 3. Kết luận độ tương thích & Lời khuyên chuẩn bị:
                </strong>
                <div id="seekerModalResultText" style="font-size: 0.78rem; color: #115e59; line-height: 1.45;"></div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.5rem; align-items: center;">
            <button type="button" class="btn btn-outline btn-sm" onclick="closeSeekerScoreModal()" style="font-weight: 700; padding: 0.35rem 0.85rem;">
                Đóng
            </button>
            <a id="seekerModalApplyBtn" href="#" class="btn btn-primary btn-sm" style="font-weight: 700; padding: 0.35rem 0.95rem;">
                Xem Chi Tiết Việc Làm
            </a>
        </div>

    </div>
</div>

<script>
let aiHoverTimeout = null;

function clearAiHoverTimeout() {
    if (aiHoverTimeout) {
        clearTimeout(aiHoverTimeout);
        aiHoverTimeout = null;
    }
}

function hideAiScoreHoverCard(immediate = false) {
    clearAiHoverTimeout();
    if (immediate) {
        const card = document.getElementById('aiScoreHoverCard');
        if (card) {
            card.classList.remove('is-visible');
            card.style.display = 'none';
        }
    } else {
        aiHoverTimeout = setTimeout(() => {
            const card = document.getElementById('aiScoreHoverCard');
            if (card) {
                card.classList.remove('is-visible');
                setTimeout(() => { if (!card.classList.contains('is-visible')) card.style.display = 'none'; }, 200);
            }
        }, 180);
    }
}

function showAiScoreHoverCard(badgeEl, evt) {
    clearAiHoverTimeout();
    if (!badgeEl) return;

    const card = document.getElementById('aiScoreHoverCard');
    if (!card) return;

    const score = parseInt(badgeEl.getAttribute('data-score')) || 0;
    const label = badgeEl.getAttribute('data-label') || 'Phù hợp';
    const level = badgeEl.getAttribute('data-level') || 'medium';
    const jobTitle = badgeEl.getAttribute('data-job-title') || 'Việc làm';
    const employerName = badgeEl.getAttribute('data-employer-name') || '';

    let breakdown = {};
    let reasons = [];
    let matched = [];
    let missing = [];
    let criteria = {};

    try { breakdown = JSON.parse(badgeEl.getAttribute('data-breakdown') || '{}'); } catch(e){}
    try { reasons = JSON.parse(badgeEl.getAttribute('data-reasons') || '[]'); } catch(e){}
    try { matched = JSON.parse(badgeEl.getAttribute('data-matched') || '[]'); } catch(e){}
    try { missing = JSON.parse(badgeEl.getAttribute('data-missing') || '[]'); } catch(e){}
    try { criteria = JSON.parse(badgeEl.getAttribute('data-criteria') || '{}'); } catch(e){}

    const metrics = [
        {
            key: 'skills',
            icon: '🎯',
            name: 'Kỹ năng chuyên môn',
            score: breakdown.skills !== undefined ? breakdown.skills : 70,
            detail: matched.length > 0 ? ('Khớp: <b>' + matched.slice(0, 3).join(', ') + '</b>' + (missing.length > 0 ? (' · Thiếu: <span style="color:#d97706;">' + missing.slice(0, 2).join(', ') + '</span>') : '')) : (missing.length > 0 ? ('Cần thêm: <span style="color:#d97706;">' + missing.slice(0, 2).join(', ') + '</span>') : 'Tiêu chí cơ bản')
        },
        {
            key: 'experience',
            icon: '💼',
            name: 'Kinh nghiệm làm việc',
            score: breakdown.experience !== undefined ? breakdown.experience : 100,
            detail: criteria.experience ? (criteria.experience.actual + ' (' + criteria.experience.required + ')') : 'Đáp ứng yêu cầu NTD'
        },
        {
            key: 'location',
            icon: '📍',
            name: 'Vị trí & Khoảng cách',
            score: breakdown.location !== undefined ? breakdown.location : 95,
            detail: criteria.location ? criteria.location.actual : 'Rất gần khu vực của bạn'
        },
        {
            key: 'employment',
            icon: '💰',
            name: 'Thu nhập & Hình thức',
            score: breakdown.employment !== undefined ? breakdown.employment : 90,
            detail: criteria.employment ? criteria.employment.required : 'Đúng nguyện vọng'
        },
        {
            key: 'education',
            icon: '🎓',
            name: 'Trình độ học vấn',
            score: breakdown.education !== undefined ? breakdown.education : 100,
            detail: criteria.education ? (criteria.education.actual + ' / ' + criteria.education.required) : 'Phù hợp yêu cầu'
        },
        {
            key: 'traits',
            icon: '🌟',
            name: 'Tác phong & Tiêu chuẩn',
            score: breakdown.traits !== undefined ? breakdown.traits : 85,
            detail: 'Đáp ứng tiêu chuẩn chung của NTD'
        }
    ];

    let metricsHtml = '';
    metrics.forEach(m => {
        const val = Math.min(100, Math.max(0, m.score));
        const barColor = val >= 85 ? '#0d9488' : (val >= 70 ? '#0284c7' : (val >= 50 ? '#d97706' : '#ef4444'));
        metricsHtml += `
            <div style="margin-bottom: 0.45rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.74rem; margin-bottom: 2px;">
                    <span style="font-weight: 700; color: #1e293b; display: inline-flex; align-items: center; gap: 4px;">
                        <span>${m.icon}</span> <span>${m.name}</span>
                    </span>
                    <span style="font-weight: 800; color: ${barColor};">${val}%</span>
                </div>
                <div style="height: 5px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-bottom: 2px;">
                    <div style="width: ${val}%; height: 100%; background: ${barColor}; border-radius: 999px; transition: width 0.3s ease;"></div>
                </div>
                <div style="font-size: 0.68rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    ${m.detail}
                </div>
            </div>
        `;
    });

    let passList = reasons.slice(0, 2).map(r => `• ${r}`).join('<br>');
    if (!passList) passList = '• Hồ sơ đáp ứng tốt định hướng công việc và khu vực làm việc';

    let warnList = '';
    if (missing.length > 0) {
        warnList = `• NTD ưu tiên thêm kỹ năng: <b>${missing.slice(0, 3).join(', ')}</b>`;
    } else {
        warnList = '• Không có thiếu sót lớn, đáp ứng trọn vẹn tiêu chuẩn NTD!';
    }

    const badgeBg = level === 'excellent' ? 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;' :
                    (level === 'high' ? 'background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd;' :
                    'background: #fffbeb; color: #92400e; border: 1px solid #fde68a;');

    card.innerHTML = `
        <div style="padding-bottom: 0.5rem; margin-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 4px; font-weight: 800; font-size: 0.88rem; color: #0f172a;">
                    <span style="color: #0d9488;">✨</span>
                    <span>Độ Tương Thích: <strong style="color: #0d9488; font-size: 0.98rem;">${score}%</strong></span>
                </div>
                <div style="font-size: 0.72rem; color: #64748b; margin-top: 1px; max-width: 210px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${jobTitle}">
                    ${jobTitle}
                </div>
            </div>
            <span style="font-size: 0.7rem; font-weight: 800; padding: 2px 7px; border-radius: 999px; white-space: nowrap; ${badgeBg}">
                ${label}
            </span>
        </div>

        <div style="font-size: 0.72rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.4rem;">
            Cấu thành thông số dẫn đến ${score}%:
        </div>

        <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; padding: 0.5rem 0.65rem 0.35rem; margin-bottom: 0.55rem;">
            ${metricsHtml}
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.55rem;">
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 0.35rem 0.5rem; font-size: 0.7rem; color: #166534; line-height: 1.4;">
                <div style="font-weight: 800; margin-bottom: 1px;">✓ Điểm cộng thực tế:</div>
                <div>${passList}</div>
            </div>
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 0.35rem 0.5rem; font-size: 0.7rem; color: #92400e; line-height: 1.4;">
                <div style="font-weight: 800; margin-bottom: 1px;">ℹ️ Đánh giá thêm:</div>
                <div>${warnList}</div>
            </div>
        </div>

        <div style="text-align: center; padding-top: 0.35rem; border-top: 1px dashed #e2e8f0; font-size: 0.68rem; color: #0d9488; font-weight: 700; cursor: pointer;" onclick="showAiScoreModalFromHover();">
            👉 Bấm vào để xem bảng đối chiếu chi tiết ➔
        </div>
    `;

    card.style.display = 'block';
    const cardWidth = 360;
    const cardRect = card.getBoundingClientRect();
    const cardHeight = cardRect.height || 380;
    const badgeRect = badgeEl.getBoundingClientRect();

    let left = badgeRect.right - cardWidth;
    if (left < 12) left = 12;
    if (left + cardWidth > window.innerWidth - 12) left = window.innerWidth - cardWidth - 12;

    let top = badgeRect.bottom + 8;
    if (top + cardHeight > window.innerHeight - 12) {
        top = Math.max(12, badgeRect.top - cardHeight - 8);
    }

    card.style.left = `${left}px`;
    card.style.top = `${top}px`;

    window._lastActiveAiBadge = badgeEl;

    requestAnimationFrame(() => {
        card.classList.add('is-visible');
    });
}

function showAiScoreModal(badgeEl) {
    if (!badgeEl) return;
    hideAiScoreHoverCard(true);

    const score = parseInt(badgeEl.getAttribute('data-score')) || 0;
    const label = badgeEl.getAttribute('data-label') || 'Phù hợp';
    const jobTitle = badgeEl.getAttribute('data-job-title') || 'Việc làm';
    const employerName = badgeEl.getAttribute('data-employer-name') || '';
    const jobUrl = badgeEl.getAttribute('data-job-url') || '#';

    let criteria = {};
    let reasons = [];
    let matched = [];
    let missing = [];

    try { criteria = JSON.parse(badgeEl.getAttribute('data-criteria') || '{}'); } catch(e){}
    try { reasons = JSON.parse(badgeEl.getAttribute('data-reasons') || '[]'); } catch(e){}
    try { matched = JSON.parse(badgeEl.getAttribute('data-matched') || '[]'); } catch(e){}
    try { missing = JSON.parse(badgeEl.getAttribute('data-missing') || '[]'); } catch(e){}

    const modalScoreVal = document.getElementById('seekerModalScoreVal');
    if (modalScoreVal) modalScoreVal.textContent = score + '%';

    const modalJobTitle = document.getElementById('seekerModalJobTitle');
    if (modalJobTitle) modalJobTitle.textContent = jobTitle;

    const modalEmployer = document.getElementById('seekerModalEmployer');
    if (modalEmployer) modalEmployer.textContent = employerName || 'Nhà tuyển dụng';

    const modalApplyBtn = document.getElementById('seekerModalApplyBtn');
    if (modalApplyBtn) modalApplyBtn.href = jobUrl;

    const tableBody = document.getElementById('seekerModalCriteriaTable');
    if (tableBody) {
        let rowsHtml = '';
        const keys = Object.keys(criteria);
        if (keys.length > 0) {
            keys.forEach(k => {
                const c = criteria[k];
                if (!c) return;
                const statusBadge = c.status === 'pass' 
                    ? '<span class="badge badge-success" style="font-size:0.7rem; padding: 0.15rem 0.45rem; font-weight:700;">✓ Đạt</span>'
                    : (c.status === 'ignored' 
                        ? '<span class="badge badge-slate" style="font-size:0.7rem; padding: 0.15rem 0.45rem;">Miễn trừ</span>'
                        : '<span class="badge badge-warning" style="font-size:0.7rem; padding: 0.15rem 0.45rem; font-weight:700;">⚠️ Cần xem xét</span>');
                rowsHtml += `
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.5rem 0.65rem; font-weight: 700; color: #0f172a;">${c.label || k}</td>
                        <td style="padding: 0.5rem 0.65rem; color: #475569;">${c.required || 'Tiêu chuẩn'}</td>
                        <td style="padding: 0.5rem 0.65rem; color: #0f172a; font-weight: 600;">${c.actual || 'Đã cập nhật'}</td>
                        <td style="padding: 0.5rem 0.65rem; text-align: center;">${statusBadge}</td>
                    </tr>
                `;
            });
        } else {
            rowsHtml = `
                <tr>
                    <td colspan="4" style="padding: 1rem; text-align: center; color: #64748b;">
                        Hồ sơ đã được so sánh tự động theo thuật toán AI Matching.
                    </td>
                </tr>
            `;
        }
        tableBody.innerHTML = rowsHtml;
    }

    let matchedHtml = '';
    if (matched.length > 0) {
        matchedHtml += '• Kỹ năng chuyên môn trùng khớp: <b>' + matched.join(', ') + '</b><br>';
    }
    reasons.forEach(r => {
        matchedHtml += '• ' + r + '<br>';
    });
    if (!matchedHtml) {
        matchedHtml = '• Hồ sơ của bạn đáp ứng các tiêu chuẩn cơ bản theo yêu cầu của nhà tuyển dụng.';
    }
    const matchedEl = document.getElementById('seekerModalMatchedText');
    if (matchedEl) matchedEl.innerHTML = matchedHtml;

    let missingHtml = '';
    if (missing.length > 0) {
        missingHtml += '• Kỹ năng NTD mong muốn thêm: <b>' + missing.join(', ') + '</b> (Bạn có thể cập nhật thêm vào Hồ sơ năng lực để nâng cao % tương thích)<br>';
    } else {
        missingHtml += '• Không có thiếu sót lớn. Bạn đáp ứng trọn vẹn mọi điều kiện chính của công việc này!';
    }
    const missingEl = document.getElementById('seekerModalMissingText');
    if (missingEl) missingEl.innerHTML = missingHtml;

    let resultHtml = `Độ tương thích thực tế giữa bạn và vị trí này là <b>${score}% (${label})</b>. `;
    if (score >= 85) {
        resultHtml += 'Bạn thuộc nhóm ứng viên <b>Rất Phù Hợp</b>. Cơ hội được gọi phỏng vấn và nhận việc là rất cao, hãy nộp đơn ứng tuyển ngay!';
    } else if (score >= 70) {
        resultHtml += 'Bạn đáp ứng tốt hầu hết các tiêu chí của doanh nghiệp. Hãy chuẩn bị kỹ phần phỏng vấn để ghi điểm tối đa!';
    } else {
        resultHtml += 'Vị trí này có một số tiêu chí chưa thật sự khớp hoàn toàn. Bạn có thể trau dồi thêm kỹ năng hoặc tìm kiếm các vị trí khác gần gũi hơn.';
    }
    const resultEl = document.getElementById('seekerModalResultText');
    if (resultEl) resultEl.innerHTML = resultHtml;

    const modal = document.getElementById('seekerScoreReasonModal');
    if (modal) modal.style.display = 'flex';
}

function showAiScoreModalFromHover() {
    if (window._lastActiveAiBadge) {
        showAiScoreModal(window._lastActiveAiBadge);
    }
}

function closeSeekerScoreModal() {
    const modal = document.getElementById('seekerScoreReasonModal');
    if (modal) modal.style.display = 'none';
}

window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSeekerScoreModal();
        hideAiScoreHoverCard(true);
    }
});

document.addEventListener('click', function(e) {
    const modal = document.getElementById('seekerScoreReasonModal');
    if (modal && e.target === modal) {
        closeSeekerScoreModal();
    }
    if (!e.target.closest('.ai-match-badge') && !e.target.closest('#aiScoreHoverCard')) {
        hideAiScoreHoverCard(true);
    }
});

function toggleSeekerSaveJob(btn, jobId) {
    if (!jobId) return;
    btn.disabled = true;
    const saveText = btn.querySelector('.save-btn-text');
    const saveUrl = '<?= rtrim(url('/seeker/toggle-save'), '/') ?>/' + jobId;

    fetch(saveUrl, {
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (r.status === 401) {
            window.location.href = '<?= url('/auth/login') ?>';
            return null;
        }
        return r.json();
    })
    .then(data => {
        btn.disabled = false;
        if (!data) return;

        if (data.success === false) {
            if (typeof Toast !== 'undefined' && Toast.show) {
                Toast.show(data.message || 'Không thể thực hiện lưu việc làm!', 'warning');
            } else {
                alert(data.message || 'Không thể thực hiện lưu việc làm!');
            }
            return;
        }

        if (data.saved) {
            btn.classList.remove('btn-outline');
            btn.classList.add('btn-primary');
            if (saveText) saveText.textContent = 'Đã lưu';
            btn.title = 'Bỏ lưu việc làm';
        } else {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline');
            if (saveText) saveText.textContent = 'Lưu';
            btn.title = 'Lưu vào danh sách việc làm đã lưu';
        }

        const savedCountEl = document.getElementById('header-saved-count');
        if (savedCountEl) {
            let currentCount = parseInt(savedCountEl.textContent) || 0;
            savedCountEl.textContent = data.saved ? (currentCount + 1) : Math.max(0, currentCount - 1);
        }

        if (typeof Toast !== 'undefined' && Toast.show) {
            Toast.show(data.message || (data.saved ? 'Đã lưu việc làm!' : 'Đã bỏ lưu việc làm!'), data.saved ? 'success' : 'info');
        }
    })
    .catch(err => {
        btn.disabled = false;
        console.error('Save job error:', err);
        if (typeof Toast !== 'undefined' && Toast.show) {
            Toast.show('Không thể kết nối máy chủ. Vui lòng thử lại!', 'danger');
        }
    });
}
</script>
</div> <!-- /workspace-zone-body -->

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
