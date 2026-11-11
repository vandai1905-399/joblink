<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Danh Sách Việc Làm Đã Lưu</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Các vị trí bạn đã lưu để xem lại hoặc ứng tuyển sau</p>
</div>

<div class="grid-jobs">
    <?php if (empty($savedJobs)): ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
            <h3>Bạn chưa lưu công việc nào</h3>
            <p style="color: var(--slate-500); margin-top: 0.5rem;"><a href="<?= url('/jobs') ?>" style="color: var(--primary); font-weight: 600;">Khám phá các việc làm đang tuyển</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($savedJobs as $job): ?>
            <div class="card job-card">
                <div>
                    <div class="job-card-header" style="display: flex; gap: 0.85rem; align-items: flex-start;">
                        <img src="<?= htmlspecialchars(getAvatarUrl($job['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" class="company-logo" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                        <div class="job-info" style="min-width: 0; flex: 1; overflow: hidden;">
                            <h3 style="margin: 0 0 0.25rem 0; font-size: 1.05rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($job['job_title']) ?>">
                                <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" class="workspace-nav-link" data-title="Tin: <?= htmlspecialchars($job['job_title']) ?>" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--dark); text-decoration: none;" title="<?= htmlspecialchars($job['job_title']) ?>">
                                    <?= htmlspecialchars($job['job_title']) ?>
                                </a>
                            </h3>
                            <p class="job-company" style="margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($job['employer_name']) ?>">
                                <?php if (!empty($job['employer_id'])): ?>
                                    <a href="<?= url('/employers/' . $job['employer_id']) ?>" class="workspace-nav-link" data-title="DN: <?= htmlspecialchars($job['employer_name']) ?>" style="color: var(--slate-600); font-weight: 700; text-decoration: underline;" title="Xem trang doanh nghiệp">
                                        <?= htmlspecialchars($job['employer_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <?= htmlspecialchars($job['employer_name']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="job-meta" style="margin-top: 1rem;">
                        <span class="badge badge-primary"><?= $job['employment_type'] === 'full_time' ? 'Toàn thời gian' : 'Bán thời gian' ?></span>
                        <span class="badge badge-slate" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                            <?= icon('location', '', 12) ?> 
                            <?php 
                                $savedAddr = $job['workplace_address'] ?? 'Thủ Dầu Một';
                                $savedMapUrl = getGoogleMapsUrl($savedAddr, $job['workplace_map_url'] ?? '');
                            ?>
                            <a href="<?= htmlspecialchars($savedMapUrl) ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;" title="Mở vị trí trên Google Maps">
                                <?= htmlspecialchars($savedAddr) ?>
                            </a>
                        </span>
                    </div>
                </div>

                <div class="job-footer" style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; padding-top: 0.85rem; border-top: 1px solid var(--slate-100);">
                    <div class="job-salary" style="min-width: 0; flex: 1; font-size: 0.98rem; font-weight: 800; color: #0d9488; line-height: 1.3;">
                        <?= formatSalary($job['salary_amount'], $job['employment_type'] ?? 'full_time') ?>
                    </div>
                    <div style="display: flex; gap: 0.45rem; flex-shrink: 0; align-items: center;">
                        <a href="<?= url('/seeker/toggle-save/' . $job['job_post_id']) ?>" class="btn btn-outline btn-sm" style="white-space: nowrap; flex-shrink: 0; font-weight: 700; padding: 0.4rem 0.75rem; font-size: 0.82rem; background: #475569; color: #ffffff; border-color: #475569;">Bỏ lưu</a>
                        <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" class="btn btn-primary btn-sm workspace-nav-link" data-title="Tin: <?= htmlspecialchars($job['job_title']) ?>" style="white-space: nowrap; flex-shrink: 0; font-weight: 700; padding: 0.4rem 0.85rem; font-size: 0.82rem; background: #0d9488; color: #ffffff; border-color: #0d9488;">Ứng Tuyển</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
