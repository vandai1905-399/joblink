<?php 
ob_start(); 
$totalPendingTasks = count($pendingJobs) + count($pendingEmployers) + $pendingReviewsCount;
?>

<!-- KHU 1 CỦA KHU 3: HEADER CỐ ĐỊNH CHỈ CHỨA MỖI TIÊU ĐỀ -->
<div class="workspace-zone-header">
    <div class="workspace-header-title-box">
        <h1 class="workspace-page-title">
            Chào mừng, <?= htmlspecialchars($currentUser['display_name'] ?? $currentUser['username'] ?? 'Quản Trị Viên') ?>
        </h1>
        <p class="workspace-page-subtitle">
            Trung tâm giám sát vận hành, kiểm duyệt nội dung & hỗ trợ người dùng toàn hệ thống JobLink.
        </p>
    </div>
</div>

<!-- KHU 2 CỦA KHU 3: BODY CHỨA TOÀN BỘ NỘI DUNG THỰC THI (CUỘN ĐỘC LẬP) -->
<div class="workspace-zone-body">

<?php if ($totalPendingTasks > 0): ?>
    <!-- THANH CẢNH BÁO VIỆC CẦN XỬ LÝ (STRIP NHẬN DIỆN MÀU VÀNG NỔI BẬT) -->
    <div class="admin-pending-alert-strip">
        <div class="pending-alert-left">
            <span class="pending-alert-badge">
                CẦN XỬ LÝ (<?= $totalPendingTasks ?>)
            </span>
            <div class="pending-alert-chips">
                <?php if (count($pendingEmployers) > 0): ?>
                    <a href="<?= url('/admin/employers') ?>" class="pending-chip workspace-nav-link" data-title="Xác Minh Doanh Nghiệp" title="Xác minh GPKD doanh nghiệp">
                        <?= icon('building', '', 14) ?>
                        <span><strong><?= count($pendingEmployers) ?></strong> Doanh nghiệp chờ GPKD</span>
                    </a>
                <?php endif; ?>
                <?php if ($pendingReviewsCount > 0): ?>
                    <a href="<?= url('/admin/reviews') ?>" class="pending-chip workspace-nav-link" data-title="Kiểm Duyệt Đánh Giá" title="Kiểm duyệt đánh giá">
                        <?= icon('reviews', '', 14) ?>
                        <span><strong><?= $pendingReviewsCount ?></strong> Đánh giá chờ duyệt</span>
                    </a>
                <?php endif; ?>
                <?php if (count($pendingJobs) > 0): ?>
                    <a href="<?= url('/admin/job-posts') ?>" class="pending-chip workspace-nav-link" data-title="Duyệt Tin Tuyển Dụng" title="Kiểm duyệt tin tuyển dụng">
                        <?= icon('posts', '', 14) ?>
                        <span><strong><?= count($pendingJobs) ?></strong> Tin tuyển dụng chờ duyệt</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <button type="button" class="pending-alert-detail-btn" onclick="openPendingTasksModal()" title="Xem danh sách chi tiết các việc cần xử lý">
            Xem danh sách chi tiết
        </button>
    </div>
<?php endif; ?>

<!-- 2. KPI METRICS CARDS -->
<div class="admin-kpi-grid">
    <!-- Card 1: Tổng Tài Khoản -->
    <a href="<?= url('/admin/accounts') ?>" class="kpi-card">
        <div>
            <div class="kpi-header">
                <span class="kpi-title">Tổng Tài Khoản</span>
                <div class="kpi-icon-wrap kpi-icon-teal">
                    <?= icon('accounts', '', 17) ?>
                </div>
            </div>
            <div class="kpi-value-row">
                <span class="kpi-value"><?= number_format(count($accounts)) ?></span>
                <span class="kpi-badge kpi-badge-info">Active</span>
            </div>
        </div>
        <div class="kpi-meta">
            <span><strong><?= $seekerCount ?></strong> Người lao động</span> &bull; 
            <span><strong><?= $employerCount ?></strong> NTD</span> &bull; 
            <span><strong><?= $adminCount ?></strong> QTV</span>
        </div>
    </a>

    <!-- Card 2: Tin Tuyển Dụng -->
    <a href="<?= url('/admin/job-posts') ?>" class="kpi-card">
        <div>
            <div class="kpi-header">
                <span class="kpi-title">Tin Tuyển Dụng</span>
                <div class="kpi-icon-wrap kpi-icon-amber">
                    <?= icon('posts', '', 17) ?>
                </div>
            </div>
            <div class="kpi-value-row">
                <span class="kpi-value"><?= number_format(count($jobPosts)) ?></span>
                <?php if (count($pendingJobs) > 0): ?>
                    <span class="kpi-badge kpi-badge-warning"><?= count($pendingJobs) ?> chờ duyệt</span>
                <?php else: ?>
                    <span class="kpi-badge kpi-badge-success">Đã duyệt hết</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="kpi-meta">
            <span><strong style="color: #16a34a;"><?= $approvedJobsCount ?></strong> đã duyệt</span> &bull; 
            <span><strong style="color: #dc2626;"><?= $rejectedJobsCount ?></strong> từ chối</span>
        </div>
    </a>

    <!-- Card 3: Doanh Nghiệp & GPKD -->
    <a href="<?= url('/admin/employers') ?>" class="kpi-card">
        <div>
            <div class="kpi-header">
                <span class="kpi-title">Doanh Nghiệp / NTD</span>
                <div class="kpi-icon-wrap kpi-icon-emerald">
                    <?= icon('employers', '', 17) ?>
                </div>
            </div>
            <div class="kpi-value-row">
                <span class="kpi-value"><?= number_format(count($employers)) ?></span>
                <?php if (count($pendingEmployers) > 0): ?>
                    <span class="kpi-badge kpi-badge-warning"><?= count($pendingEmployers) ?> chờ GPKD</span>
                <?php else: ?>
                    <span class="kpi-badge kpi-badge-success">100% Xác thực</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="kpi-meta">
            <span><strong style="color: #16a34a;"><?= $verifiedEmployersCount ?></strong> đã xác minh</span> &bull; 
            <span><strong style="color: #dc2626;"><?= $failedEmployersCount ?></strong> từ chối</span>
        </div>
    </a>

    <!-- Card 4: Đánh Giá & CSKH -->
    <a href="<?= url('/admin/reviews') ?>" class="kpi-card">
        <div>
            <div class="kpi-header">
                <span class="kpi-title">Đánh Giá & Khiếu Nại</span>
                <div class="kpi-icon-wrap kpi-icon-indigo">
                    <?= icon('reviews', '', 17) ?>
                </div>
            </div>
            <div class="kpi-value-row">
                <span class="kpi-value"><?= number_format(count($reviews)) ?></span>
                <?php if ($pendingReviewsCount > 0): ?>
                    <span class="kpi-badge kpi-badge-warning"><?= $pendingReviewsCount ?> chờ duyệt</span>
                <?php else: ?>
                    <span class="kpi-badge kpi-badge-info"><?= count($supportMessages) ?> thư CSKH</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="kpi-meta">
            <span><strong><?= count($supportMessages) ?></strong> liên hệ / khiếu nại</span>
        </div>
    </a>
</div>

<!-- 3. QUICK ACTIONS TOOLBAR -->
<div class="admin-quick-actions">
    <div class="quick-actions-header">
        <div class="quick-actions-title">
            <span style="color: #0d9488;"><?= icon('zap', '', 18) ?></span>
            <span>Tác Vụ & Phím Tắt Nhanh</span>
        </div>
        <span style="font-size: 0.8rem; color: #94a3b8;">Thao tác nhanh các module quan trọng</span>
    </div>
    <div class="quick-actions-grid">
        <a href="<?= url('/admin/job-posts') ?>" class="action-btn-card">
            <div class="action-icon" style="color: #d97706;"><?= icon('posts', '', 17) ?></div>
            <span>Duyệt Tin Tuyển Dụng</span>
            <?php if (count($pendingJobs) > 0): ?>
                <span class="action-badge-count"><?= count($pendingJobs) ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= url('/admin/employers') ?>" class="action-btn-card">
            <div class="action-icon" style="color: #16a34a;"><?= icon('employers', '', 17) ?></div>
            <span>Xác Minh GPKD</span>
            <?php if (count($pendingEmployers) > 0): ?>
                <span class="action-badge-count"><?= count($pendingEmployers) ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= url('/admin/support') ?>" class="action-btn-card">
            <div class="action-icon" style="color: #0d9488;"><?= icon('support', '', 17) ?></div>
            <span>Trung Tâm CSKH</span>
            <?php if (count($supportMessages) > 0): ?>
                <span class="action-badge-count" style="background: #0d9488;"><?= count($supportMessages) ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= url('/admin/accounts') ?>" class="action-btn-card">
            <div class="action-icon" style="color: #6366f1;"><?= icon('accounts', '', 17) ?></div>
            <span>Quản Trị Tài Khoản</span>
        </a>

        <a href="<?= url('/admin/staffs') ?>" class="action-btn-card">
            <div class="action-icon" style="color: #0284c7;"><?= icon('staffs', '', 17) ?></div>
            <span>Đội Ngũ Quản Trị</span>
        </a>

        <a href="<?= url('/admin/messages') ?>" class="action-btn-card">
            <div class="action-icon" style="color: #8b5cf6;"><?= icon('messages', '', 17) ?></div>
            <span>Gửi Thông Báo</span>
        </a>
    </div>
</div>

<!-- 4. TWO-COLUMN WORK QUEUE LAYOUT -->
<div class="admin-grid-2col">
    <!-- CỘT 1: TIN TUYỂN DỤNG CẦN DUYỆT / MỚI NHẤT -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <?= icon('posts', '', 18) ?>
                <span>Tin Tuyển Dụng Cần Duyệt (<?= count($pendingJobs) ?>)</span>
            </h3>
            <a href="<?= url('/admin/job-posts') ?>" class="btn btn-outline btn-sm">Xem tất cả</a>
        </div>

        <div>
            <?php if (empty($pendingJobs)): ?>
                <?php if (empty($jobPosts)): ?>
                    <div class="empty-queue-box">
                        <span class="empty-queue-icon"><?= icon('inbox', '', 36) ?></span>
                        <p style="margin: 0;">Chưa có tin tuyển dụng nào trên hệ thống.</p>
                    </div>
                <?php else: ?>
                    <div style="padding: 1rem 1.5rem; background: #f0fdf4; color: #166534; font-size: 0.88rem; display: flex; align-items: center; gap: 0.5rem;">
                        <?= icon('check', '', 16) ?>
                        <span>Tất cả tin tuyển dụng hiện tại đều đã được kiểm duyệt. Dưới đây là các tin mới nhất:</span>
                    </div>
                    <?php foreach (array_slice($jobPosts, 0, 4) as $job): ?>
                        <div class="admin-list-item">
                            <div class="item-main">
                                <img src="<?= htmlspecialchars(getAvatarUrl($job['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" class="item-avatar" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                                <div class="item-text">
                                    <a href="<?= url('/admin/job-posts') ?>" class="item-title-link">
                                        <?= htmlspecialchars($job['job_title']) ?>
                                    </a>
                                    <div class="item-sub">
                                        <span style="font-weight: 600; color: #334155;"><?= htmlspecialchars($job['employer_name'] ?? 'NTD') ?></span>
                                        <span>&bull;</span>
                                        <span><?= formatSalary($job['salary_amount'] ?? 0, $job['employment_type'] ?? 'full_time') ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="item-actions">
                                <?php if (($job['post_status'] ?? '') === 'approved'): ?>
                                    <span class="badge badge-success"><?= icon('check', '', 12) ?> Đã duyệt</span>
                                <?php elseif (($job['post_status'] ?? '') === 'rejected'): ?>
                                    <span class="badge badge-danger">Từ chối</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Chờ duyệt</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else: ?>
                <?php foreach (array_slice($pendingJobs, 0, 5) as $job): ?>
                    <div class="admin-list-item">
                        <div class="item-main">
                            <img src="<?= htmlspecialchars(getAvatarUrl($job['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" class="item-avatar" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                            <div class="item-text">
                                <a href="<?= url('/admin/job-posts') ?>" class="item-title-link">
                                    <?= htmlspecialchars($job['job_title']) ?>
                                </a>
                                <div class="item-sub">
                                    <span style="font-weight: 600; color: #334155;"><?= htmlspecialchars($job['employer_name'] ?? 'NTD') ?></span>
                                    <span>&bull;</span>
                                    <span style="color: #0d9488; font-weight: 600;"><?= formatSalary($job['salary_amount'] ?? 0, $job['employment_type'] ?? 'full_time') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="item-actions">
                            <a href="<?= url('/admin/job-posts') ?>" class="btn btn-primary btn-sm" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                                Duyệt Tin
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- CỘT 2: DOANH NGHIỆP CẦN XÁC MINH GPKD -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <?= icon('employers', '', 18) ?>
                <span>Doanh Nghiệp Chờ GPKD (<?= count($pendingEmployers) ?>)</span>
            </h3>
            <a href="<?= url('/admin/employers') ?>" class="btn btn-outline btn-sm">Xem tất cả</a>
        </div>

        <div>
            <?php if (empty($pendingEmployers)): ?>
                <?php if (empty($employers)): ?>
                    <div class="empty-queue-box">
                        <span class="empty-queue-icon"><?= icon('building', '', 36) ?></span>
                        <p style="margin: 0;">Chưa có doanh nghiệp nào đăng ký.</p>
                    </div>
                <?php else: ?>
                    <div style="padding: 1rem 1.5rem; background: #f0fdf4; color: #166534; font-size: 0.88rem; display: flex; align-items: center; gap: 0.5rem;">
                        <?= icon('check', '', 16) ?>
                        <span>Tất cả hồ sơ pháp lý doanh nghiệp đã được thẩm định. Danh sách gần nhất:</span>
                    </div>
                    <?php foreach (array_slice($employers, 0, 4) as $emp): ?>
                        <div class="admin-list-item">
                            <div class="item-main">
                                <img src="<?= htmlspecialchars(getAvatarUrl($emp['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" class="item-avatar" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                                <div class="item-text">
                                    <a href="<?= url('/admin/employers') ?>" class="item-title-link">
                                        <?= htmlspecialchars($emp['employer_name'] ?? 'Doanh nghiệp') ?>
                                    </a>
                                    <div class="item-sub">
                                        <span>GPKD: <code><?= htmlspecialchars($emp['business_license_number'] ?? 'Chưa cập nhật') ?></code></span>
                                    </div>
                                </div>
                            </div>
                            <div class="item-actions">
                                <span class="badge <?= ($emp['verification_status'] ?? '') === 'verified' ? 'badge-success' : (($emp['verification_status'] ?? '') === 'failed' ? 'badge-danger' : 'badge-warning') ?>">
                                    <?= ($emp['verification_status'] ?? '') === 'verified' ? icon('check', '', 12) . ' Đã xác minh' : (($emp['verification_status'] ?? '') === 'failed' ? 'Từ chối' : 'Chờ duyệt') ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else: ?>
                <?php foreach (array_slice($pendingEmployers, 0, 5) as $emp): ?>
                    <div class="admin-list-item">
                        <div class="item-main">
                            <img src="<?= htmlspecialchars(getAvatarUrl($emp['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" class="item-avatar" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                            <div class="item-text">
                                <a href="<?= url('/admin/employers') ?>" class="item-title-link workspace-nav-link" data-title="Xác Minh Doanh Nghiệp">
                                    <?= htmlspecialchars($emp['employer_name'] ?? 'Doanh nghiệp') ?>
                                </a>
                                <div class="item-sub">
                                    <span>Mã GPKD: <code style="background: #f1f5f9; padding: 0.15rem 0.35rem; border-radius: 4px;"><?= htmlspecialchars($emp['business_license_number'] ?? 'Chưa có') ?></code></span>
                                </div>
                            </div>
                        </div>
                        <div class="item-actions">
                            <a href="<?= url('/admin/employers') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Xác Minh Doanh Nghiệp" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                                Thẩm Định
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 5. ANALYTICS & DISTRIBUTION WIDGETS -->
<div class="admin-analytics-grid">
    <!-- Widget 1: Tỷ lệ phân bổ nền tảng -->
    <div class="card">
        <div class="admin-card-header" style="padding: 0 0 1rem 0; margin-bottom: 1.25rem;">
            <h3 class="admin-card-title">
                <?= icon('dashboard', '', 18) ?>
                <span>Phân Bổ Tài Khoản & Tuyển Dụng</span>
            </h3>
        </div>

        <?php
        $totalAcc = max(1, count($accounts));
        $seekerPct = round(($seekerCount / $totalAcc) * 100);
        $employerPct = round(($employerCount / $totalAcc) * 100);
        $adminPct = round(($adminCount / $totalAcc) * 100);

        $totalJobs = max(1, count($jobPosts));
        $ftPct = round((($jobTypeStats['full_time'] ?? 0) / $totalJobs) * 100);
        $ptPct = round((($jobTypeStats['part_time'] ?? 0) / $totalJobs) * 100);
        $tempPct = round((($jobTypeStats['temporary'] ?? 0) / $totalJobs) * 100);
        ?>

        <div style="margin-bottom: 1.75rem;">
            <div style="font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">
                Cơ cấu tài khoản hệ thống (<?= count($accounts) ?> người dùng)
            </div>
            
            <div class="dist-bar-wrap">
                <div class="dist-bar-label">
                    <span>Người lao động tìm việc</span>
                    <strong><?= $seekerCount ?> (<?= $seekerPct ?>%)</strong>
                </div>
                <div class="dist-progress">
                    <div class="dist-progress-fill fill-teal" style="width: <?= $seekerPct ?>%;"></div>
                </div>
            </div>

            <div class="dist-bar-wrap">
                <div class="dist-bar-label">
                    <span>Nhà tuyển dụng / Doanh nghiệp</span>
                    <strong><?= $employerCount ?> (<?= $employerPct ?>%)</strong>
                </div>
                <div class="dist-progress">
                    <div class="dist-progress-fill fill-amber" style="width: <?= $employerPct ?>%;"></div>
                </div>
            </div>

            <div class="dist-bar-wrap">
                <div class="dist-bar-label">
                    <span>Đội ngũ quản trị & vận hành</span>
                    <strong><?= $adminCount ?> (<?= $adminPct ?>%)</strong>
                </div>
                <div class="dist-progress">
                    <div class="dist-progress-fill fill-indigo" style="width: <?= $adminPct ?>%;"></div>
                </div>
            </div>
        </div>

        <div>
            <div style="font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">
                Phân loại hình thức việc làm (<?= count($jobPosts) ?> tin)
            </div>

            <div class="dist-bar-wrap">
                <div class="dist-bar-label">
                    <span>Toàn thời gian (Full-time)</span>
                    <strong><?= $jobTypeStats['full_time'] ?? 0 ?> (<?= $ftPct ?>%)</strong>
                </div>
                <div class="dist-progress">
                    <div class="dist-progress-fill fill-emerald" style="width: <?= $ftPct ?>%;"></div>
                </div>
            </div>

            <div class="dist-bar-wrap">
                <div class="dist-bar-label">
                    <span>Bán thời gian (Part-time)</span>
                    <strong><?= $jobTypeStats['part_time'] ?? 0 ?> (<?= $ptPct ?>%)</strong>
                </div>
                <div class="dist-progress">
                    <div class="dist-progress-fill fill-indigo" style="width: <?= $ptPct ?>%;"></div>
                </div>
            </div>

            <div class="dist-bar-wrap">
                <div class="dist-bar-label">
                    <span>Thời vụ / Theo ca (Shift work)</span>
                    <strong><?= $jobTypeStats['temporary'] ?? 0 ?> (<?= $tempPct ?>%)</strong>
                </div>
                <div class="dist-progress">
                    <div class="dist-progress-fill fill-rose" style="width: <?= $tempPct ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Widget 2: Tin Nhắn & Hỗ Trợ CSKH Mới Nhất -->
    <div class="card">
        <div class="admin-card-header" style="padding: 0 0 1rem 0; margin-bottom: 1rem;">
            <h3 class="admin-card-title">
                <?= icon('support', '', 18) ?>
                <span>Yêu Cầu Hỗ Trợ & CSKH Mới Nhất</span>
            </h3>
            <a href="<?= url('/admin/support') ?>" class="btn btn-outline btn-sm">Vào CSKH</a>
        </div>

        <div>
            <?php if (empty($supportMessages)): ?>
                <div class="empty-queue-box">
                    <span class="empty-queue-icon"><?= icon('chat', '', 36) ?></span>
                    <p style="margin: 0;">Hiện không có yêu cầu khiếu nại hoặc tin nhắn CSKH nào.</p>
                </div>
            <?php else: ?>
                <?php foreach (array_slice($supportMessages, 0, 4) as $msg): ?>
                    <div style="padding: 0.85rem 0; border-bottom: 1px solid #f1f5f9; display: flex; align-items: flex-start; gap: 0.75rem;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: #f0fdfa; color: #0d9488; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                            <?= strtoupper(substr($msg['sender_name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.2rem;">
                                <strong style="font-size: 0.88rem; color: #0f172a;"><?= htmlspecialchars($msg['sender_name'] ?? 'Người dùng') ?></strong>
                                <span style="font-size: 0.75rem; color: #94a3b8;"><?= date('d/m H:i', strtotime($msg['sent_at'] ?? 'now')) ?></span>
                            </div>
                            <div style="font-size: 0.83rem; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= htmlspecialchars($msg['message_content'] ?? '') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="<?= url('/admin/support') ?>" style="font-size: 0.85rem; color: #0d9488; font-weight: 600; text-decoration: none;">
                        Xử lý toàn bộ hộp thư hỗ trợ
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 6. MODAL CHI TIẾT CÁC VIỆC CẦN XỬ LÝ TRONG HỆ THỐNG -->
<div class="pending-modal-backdrop" id="pendingTasksModalBackdrop" style="display:none;" onclick="closePendingTasksModal()">
    <div class="pending-modal-dialog" onclick="event.stopPropagation();">
        <div class="pending-modal-header">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:50%; background:#fef3c7; color:#d97706; display:flex; align-items:center; justify-content:center; font-size:18px;">📣</div>
                <div>
                    <h3 class="pending-modal-title">Danh Sách Việc Cần Xử Lý (<?= $totalPendingTasks ?>)</h3>
                    <p class="pending-modal-subtitle">Các mục chờ quản trị viên phê duyệt trên hệ thống JobLink</p>
                </div>
            </div>
            <button type="button" class="pending-modal-close" onclick="closePendingTasksModal()">&times;</button>
        </div>
        <div class="pending-modal-body">
            <!-- 1. DOANH NGHIỆP CHỜ GPKD -->
            <?php if (!empty($pendingEmployers)): ?>
                <div class="pending-task-group">
                    <div class="pending-task-group-title">
                        <span style="color:#16a34a;"><?= icon('building', '', 16) ?></span>
                        <strong>1. Doanh Nghiệp Chờ Xác Minh GPKD (<?= count($pendingEmployers) ?>)</strong>
                    </div>
                    <div class="pending-task-items">
                        <?php foreach ($pendingEmployers as $emp): ?>
                            <div class="pending-task-row">
                                <div class="pending-task-info">
                                    <div class="pending-task-name"><strong><?= htmlspecialchars($emp['employer_name'] ?? 'Doanh nghiệp') ?></strong></div>
                                    <div class="pending-task-desc">Mã GPKD/MST: <code style="background:#f1f5f9; padding:1px 5px; border-radius:4px;"><?= htmlspecialchars($emp['business_license_number'] ?? 'Chưa cập nhật') ?></code></div>
                                </div>
                                <a href="<?= url('/admin/employers') ?>" class="btn btn-sm btn-primary workspace-nav-link" data-title="Xác Minh Doanh Nghiệp" onclick="closePendingTasksModal()">
                                    Thẩm định
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 2. ĐÁNH GIÁ CHỜ DUYỆT -->
            <?php if (!empty($pendingReviews)): ?>
                <div class="pending-task-group">
                    <div class="pending-task-group-title">
                        <span style="color:#6366f1;"><?= icon('reviews', '', 16) ?></span>
                        <strong>2. Đánh Giá Của Người Dùng Chờ Kiểm Duyệt (<?= count($pendingReviews) ?>)</strong>
                    </div>
                    <div class="pending-task-items">
                        <?php foreach ($pendingReviews as $rev): ?>
                            <div class="pending-task-row">
                                <div class="pending-task-info">
                                    <div class="pending-task-name">Đánh giá cho: <strong><?= htmlspecialchars($rev['employer_name'] ?? 'Doanh nghiệp') ?></strong> (bởi <?= htmlspecialchars($rev['seeker_name'] ?? 'Người lao động') ?>)</div>
                                    <div class="pending-task-desc">"<?= htmlspecialchars($rev['review_content'] ?? '') ?>"</div>
                                </div>
                                <a href="<?= url('/admin/reviews') ?>" class="btn btn-sm btn-primary workspace-nav-link" data-title="Kiểm Duyệt Đánh Giá" onclick="closePendingTasksModal()">
                                    Kiểm duyệt
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 3. TIN TUYỂN DỤNG CHỜ DUYỆT -->
            <?php if (!empty($pendingJobs)): ?>
                <div class="pending-task-group">
                    <div class="pending-task-group-title">
                        <span style="color:#d97706;"><?= icon('posts', '', 16) ?></span>
                        <strong>3. Tin Tuyển Dụng Chờ Duyệt (<?= count($pendingJobs) ?>)</strong>
                    </div>
                    <div class="pending-task-items">
                        <?php foreach ($pendingJobs as $job): ?>
                            <div class="pending-task-row">
                                <div class="pending-task-info">
                                    <div class="pending-task-name"><strong><?= htmlspecialchars($job['job_title'] ?? 'Tin tuyển dụng') ?></strong></div>
                                    <div class="pending-task-desc"><?= htmlspecialchars($job['employer_name'] ?? 'NTD') ?> &bull; <?= formatSalary($job['salary_amount'] ?? 0, $job['employment_type'] ?? 'full_time') ?></div>
                                </div>
                                <a href="<?= url('/admin/job-posts') ?>" class="btn btn-sm btn-primary workspace-nav-link" data-title="Duyệt Tin Tuyển Dụng" onclick="closePendingTasksModal()">
                                    Duyệt tin
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openPendingTasksModal() {
    var modal = document.getElementById('pendingTasksModalBackdrop');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closePendingTasksModal() {
    var modal = document.getElementById('pendingTasksModalBackdrop');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePendingTasksModal();
    }
});
</script>
</div> <!-- /workspace-zone-body -->

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php';
?>
