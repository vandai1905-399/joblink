<?php ob_start(); ?>

<div class="notifications-page-header" style="margin-bottom: 1.75rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.35rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: rgba(13, 148, 136, 0.15); color: #0d9488;">
                    <?= icon('bell', '', 20) ?>
                </span>
                <h1 style="font-size: 1.65rem; font-weight: 800; color: var(--dark); margin: 0; letter-spacing: -0.02em;">Trung Tâm Thông Báo & Hoạt Động</h1>
                <span class="badge badge-primary" style="font-size: 0.8rem; padding: 4px 10px; border-radius: 9999px;">
                    <?= (count($pendingJobs) + count($pendingEmployers) + count($pendingAuthorizations) + count($systemMessages)) ?> mục mới
                </span>
            </div>
            <p style="color: var(--slate-500); font-size: 0.92rem; margin: 0;">Toàn bộ thông báo hệ thống, việc làm chờ duyệt, doanh nghiệp xác minh và nhật ký hoạt động trên sàn</p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button type="button" class="btn btn-outline btn-sm" onclick="location.reload();" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                <?= icon('refresh', '', 14) ?> Tải lại hoạt động
            </button>
            <a href="<?= url('/admin/messages') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Gửi Thông Báo Hệ Thống" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                <?= icon('nav_broadcast', '', 14) ?> Phát thông báo mới
            </a>
        </div>
    </div>
</div>

<!-- 1. JOBY AI SMART SUMMARY CARD -->
<div style="background: linear-gradient(135deg, #f0fdfa 0%, #ecfeff 100%); border: 1.5px solid #99f6e4; border-radius: 14px; padding: 1.15rem 1.4rem; margin-bottom: 1.75rem; box-shadow: 0 4px 16px rgba(13, 148, 136, 0.06);">
    <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: #0d9488; color: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(13, 148, 136, 0.35);">
            <?= icon('ai', '', 20) ?>
        </div>
        <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem;">
                <strong style="color: #0f766e; font-size: 0.95rem;">Joby AI Smart Summary (Tóm Tắt Tình Hình Hệ Thống)</strong>
                <span class="badge" style="background: #ccfbf1; color: #0f766e; font-size: 0.72rem; padding: 2px 7px;">Tự Động 24/7</span>
            </div>
            <div style="color: #134e4a; font-size: 0.92rem; line-height: 1.55;">
                <?= $aiSummary ?>
            </div>
        </div>
    </div>
</div>

<!-- 2. KPI THỐNG KÊ HOẠT ĐỘNG CHỜ XỬ LÝ -->
<div class="grid-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
    <div class="stat-card" style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: 12px; padding: 1.15rem; border-left: 4px solid #8b5cf6;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
            <div class="stat-title" style="font-size: 0.84rem; font-weight: 700; color: var(--slate-500);">Tin Tuyển Dụng Chờ Duyệt</div>
            <span style="color: #8b5cf6;"><?= icon('nav_job_post', '', 20) ?></span>
        </div>
        <div class="stat-value" style="font-size: 1.7rem; font-weight: 800; color: #6d28d9;"><?= count($pendingJobs) ?></div>
        <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: 0.2rem;">Cần kiểm duyệt nội dung & ca làm</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: 12px; padding: 1.15rem; border-left: 4px solid #0284c7;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
            <div class="stat-title" style="font-size: 0.84rem; font-weight: 700; color: var(--slate-500);">Doanh Nghiệp & GPKD</div>
            <span style="color: #0284c7;"><?= icon('nav_employer', '', 20) ?></span>
        </div>
        <div class="stat-value" style="font-size: 1.7rem; font-weight: 800; color: #0369a1;"><?= count($pendingEmployers) ?></div>
        <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: 0.2rem;">Đang chờ cấp tích xanh xác thực</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: 12px; padding: 1.15rem; border-left: 4px solid #f59e0b;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
            <div class="stat-title" style="font-size: 0.84rem; font-weight: 700; color: var(--slate-500);">Ủy Quyền Người Trung Gian</div>
            <span style="color: #f59e0b;"><?= icon('nav_authorization', '', 20) ?></span>
        </div>
        <div class="stat-value" style="font-size: 1.7rem; font-weight: 800; color: #b45309;"><?= count($pendingAuthorizations) ?></div>
        <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: 0.2rem;">Hồ sơ thẩm định đại diện sàn</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: 12px; padding: 1.15rem; border-left: 4px solid #0d9488;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
            <div class="stat-title" style="font-size: 0.84rem; font-weight: 700; color: var(--slate-500);">Thông Báo Hệ Thống & AI</div>
            <span style="color: #0d9488;"><?= icon('nav_broadcast', '', 20) ?></span>
        </div>
        <div class="stat-value" style="font-size: 1.7rem; font-weight: 800; color: #0f766e;"><?= count($systemMessages) ?></div>
        <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: 0.2rem;">Thông điệp & hoạt động toàn sàn</div>
    </div>
</div>

<!-- 3. DANH SÁCH CHI TIẾT KÈM TAB BỘ LỌC -->
<div class="card" style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: 14px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
    <!-- Filter Tabs -->
    <div style="display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid var(--slate-200); padding-bottom: 0.85rem; margin-bottom: 1.25rem; overflow-x: auto;">
        <button type="button" class="btn btn-sm noti-filter-btn active" data-filter="all" style="font-weight: 700; border-radius: 8px;">
            Tất Cả Hoạt Động (<?= (count($pendingJobs) + count($pendingEmployers) + count($pendingAuthorizations) + count($systemMessages)) ?>)
        </button>
        <button type="button" class="btn btn-outline btn-sm noti-filter-btn" data-filter="jobs" style="font-weight: 600; border-radius: 8px;">
            Tin Tuyển Dụng (<?= count($pendingJobs) ?>)
        </button>
        <button type="button" class="btn btn-outline btn-sm noti-filter-btn" data-filter="employers" style="font-weight: 600; border-radius: 8px;">
            Doanh Nghiệp GPKD (<?= count($pendingEmployers) ?>)
        </button>
        <button type="button" class="btn btn-outline btn-sm noti-filter-btn" data-filter="auth" style="font-weight: 600; border-radius: 8px;">
            Ủy Quyền (<?= count($pendingAuthorizations) ?>)
        </button>
        <button type="button" class="btn btn-outline btn-sm noti-filter-btn" data-filter="messages" style="font-weight: 600; border-radius: 8px;">
            Thông Báo Hệ Thống (<?= count($systemMessages) ?>)
        </button>
    </div>

    <!-- Danh sách các thẻ thông báo -->
    <div class="noti-items-stream" style="display: flex; flex-direction: column; gap: 0.85rem;">

        <!-- 1. CÁC TIN TUYỂN DỤNG CHỜ DUYỆT -->
        <?php foreach ($pendingJobs as $job): ?>
            <div class="noti-card-row noti-type-jobs" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1rem 1.15rem; background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 12px; transition: all 0.18s ease;">
                <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(168, 85, 247, 0.2); color: #9333ea; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <?= icon('nav_job_post', '', 20) ?>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <span class="badge" style="background: #f3e8ff; color: #7e22ce; font-weight: 700; font-size: 0.72rem; padding: 2px 7px;">TIN TUYỂN DỤNG</span>
                            <span style="font-size: 0.8rem; color: var(--slate-400);"><?= date('H:i - d/m/Y', strtotime($job['posted_at'] ?? 'now')) ?></span>
                        </div>
                        <h4 style="margin: 0 0 0.3rem; font-size: 1rem; font-weight: 700; color: #1e1b4b;">
                            <?= htmlspecialchars($job['job_title'] ?? '') ?>
                        </h4>
                        <div style="font-size: 0.85rem; color: #4b5563; display: flex; flex-wrap: wrap; gap: 0.75rem;">
                            <span><strong>Doanh nghiệp:</strong> <?= htmlspecialchars($job['employer_name'] ?? 'N/A') ?></span>
                            <span><strong>Địa điểm:</strong> <?= htmlspecialchars($job['location'] ?? 'Bình Dương') ?></span>
                            <span><strong>Lương:</strong> <?= htmlspecialchars($job['salary_range'] ?? 'Thỏa thuận') ?></span>
                        </div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                    <a href="<?= url('/admin/job-posts') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Duyệt Tin Tuyển Dụng" style="white-space: nowrap;">
                        Duyệt Tin Này &rarr;
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- 2. CÁC DOANH NGHIỆP CHỜ XÁC MINH GPKD -->
        <?php foreach ($pendingEmployers as $emp): ?>
            <div class="noti-card-row noti-type-employers" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1rem 1.15rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; transition: all 0.18s ease;">
                <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(14, 165, 233, 0.2); color: #0284c7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <?= icon('nav_employer', '', 20) ?>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.72rem; padding: 2px 7px;">XÁC MINH GPKD</span>
                            <span style="font-size: 0.8rem; color: var(--slate-400);"><?= date('H:i - d/m/Y', strtotime($emp['created_at'] ?? 'now')) ?></span>
                        </div>
                        <h4 style="margin: 0 0 0.3rem; font-size: 1rem; font-weight: 700; color: #0c4a6e;">
                            <?= htmlspecialchars($emp['employer_name'] ?? 'Doanh nghiệp mới') ?>
                        </h4>
                        <div style="font-size: 0.85rem; color: #4b5563; display: flex; flex-wrap: wrap; gap: 0.75rem;">
                            <span><strong>Mã số thuế / GPKD:</strong> <?= htmlspecialchars($emp['business_license_number'] ?? 'Đang cập nhật') ?></span>
                            <span><strong>Lĩnh vực:</strong> <?= htmlspecialchars($emp['industry'] ?? 'Chưa phân loại') ?></span>
                            <span><strong>Email:</strong> <?= htmlspecialchars($emp['email'] ?? '') ?></span>
                        </div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                    <a href="<?= url('/admin/employers') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Xác Minh Doanh Nghiệp" style="white-space: nowrap; background:#0284c7; border-color:#0284c7;">
                        Kiểm Tra GPKD &rarr;
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- 3. CÁC HỒ SƠ ỦY QUYỀN TRUNG GIAN -->
        <?php foreach ($pendingAuthorizations as $auth): ?>
            <div class="noti-card-row noti-type-auth" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1rem 1.15rem; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; transition: all 0.18s ease;">
                <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(245, 158, 11, 0.2); color: #d97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <?= icon('nav_authorization', '', 20) ?>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 700; font-size: 0.72rem; padding: 2px 7px;">ỦY QUYỀN TRUNG GIAN</span>
                            <span style="font-size: 0.8rem; color: var(--slate-400);"><?= date('H:i - d/m/Y', strtotime($auth['created_at'] ?? 'now')) ?></span>
                        </div>
                        <h4 style="margin: 0 0 0.3rem; font-size: 1rem; font-weight: 700; color: #78350f;">
                            Ủy quyền từ <?= htmlspecialchars($auth['employer_name'] ?? 'Doanh nghiệp') ?> cho <?= htmlspecialchars($auth['intermediary_name'] ?? 'Đại lý') ?>
                        </h4>
                        <div style="font-size: 0.85rem; color: #4b5563;">
                            Mã hồ sơ: <strong>#AUTH-<?= htmlspecialchars($auth['authorization_id'] ?? '') ?></strong> &bull; Đang chờ thẩm định tính pháp lý
                        </div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                    <a href="<?= url('/admin/authorizations') ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Thẩm Định Ủy Quyền (TG)" style="white-space: nowrap; background:#d97706; border-color:#d97706;">
                        Thẩm Định Hồ Sơ &rarr;
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- 4. TIN NHẮN THÔNG BÁO HỆ THỐNG -->
        <?php foreach ($systemMessages as $msg): ?>
            <div class="noti-card-row noti-type-messages" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1rem 1.15rem; background: #f8fafc; border: 1px solid var(--slate-200); border-radius: 12px; transition: all 0.18s ease;">
                <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(13, 148, 136, 0.15); color: #0d9488; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <?= icon('nav_broadcast', '', 20) ?>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <span class="badge" style="background: #e2e8f0; color: #334155; font-weight: 700; font-size: 0.72rem; padding: 2px 7px;">THÔNG BÁO HỆ THỐNG</span>
                            <span style="font-size: 0.8rem; color: var(--slate-400);"><?= date('H:i - d/m/Y', strtotime($msg['sent_at'] ?? 'now')) ?></span>
                        </div>
                        <h4 style="margin: 0 0 0.3rem; font-size: 1rem; font-weight: 700; color: var(--dark);">
                            <?= htmlspecialchars($msg['message_title'] ?? 'Thông báo hệ thống') ?>
                        </h4>
                        <div style="font-size: 0.88rem; color: #475569; line-height: 1.5;">
                            <?= nl2br(htmlspecialchars($msg['message_content'] ?? '')) ?>
                        </div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                    <a href="<?= url('/admin/messages') ?>" class="btn btn-outline btn-sm workspace-nav-link" data-title="Gửi Thông Báo Hệ Thống" style="white-space: nowrap;">
                        Xem Thông Báo &rarr;
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($pendingJobs) && empty($pendingEmployers) && empty($pendingAuthorizations) && empty($systemMessages)): ?>
            <div style="text-align: center; padding: 3.5rem 1rem; color: var(--slate-400);">
                <div style="margin-bottom: 0.75rem; color: #10b981;"><?= icon('check_circle', '', 48) ?></div>
                <h3 style="color: var(--dark); font-size: 1.2rem; font-weight: 700; margin-bottom: 0.3rem;">Hệ Thống Đang Vận Hành Trơn Tru!</h3>
                <p style="font-size: 0.9rem;">Hiện tại không có thông báo khẩn cấp hoặc hoạt động chờ xử lý nào tồn đọng.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
// Bộ lọc Client-side mượt mà theo từng danh mục
document.querySelectorAll('.noti-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.noti-filter-btn').forEach(b => {
            b.classList.remove('active', 'btn-primary');
            b.classList.add('btn-outline');
        });
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-outline');

        const filter = this.getAttribute('data-filter');
        document.querySelectorAll('.noti-card-row').forEach(row => {
            if (filter === 'all') {
                row.style.display = 'flex';
            } else if (row.classList.contains('noti-type-' + filter)) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/admin_layout.php';
?>
