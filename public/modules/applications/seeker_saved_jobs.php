<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Danh Sách Việc Làm Đã Lưu</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Các vị trí bạn đã lưu để xem lại hoặc ứng tuyển sau</p>
</div>

<div class="grid-jobs">
    <?php if (empty($savedJobs)): ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
            <h3>Bạn chưa lưu công việc nào</h3>
            <p style="color: var(--slate-500); margin-top: 0.5rem;"><a href="/jobs" style="color: var(--primary); font-weight: 600;">Khám phá các việc làm đang tuyển &rarr;</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($savedJobs as $job): ?>
            <div class="card job-card">
                <div>
                    <div class="job-card-header">
                        <img src="<?= htmlspecialchars($job['avatar_url'] ?? 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=150') ?>" alt="logo" class="company-logo">
                        <div class="job-info">
                            <h3><a href="/jobs/<?= $job['job_post_id'] ?>"><?= htmlspecialchars($job['job_title']) ?></a></h3>
                            <p class="job-company"><?= htmlspecialchars($job['employer_name']) ?></p>
                        </div>
                    </div>
                    <div class="job-meta" style="margin-top: 1rem;">
                        <span class="badge badge-primary"><?= $job['employment_type'] === 'full_time' ? 'Toàn thời gian' : 'Bán thời gian' ?></span>
                        <span class="badge badge-slate" style="display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('location', '', 12) ?> <span><?= htmlspecialchars($job['workplace_address'] ?? 'Thủ Dầu Một') ?></span></span>
                    </div>
                </div>

                <div class="job-footer">
                    <div class="job-salary"><?= formatSalary($job['salary_amount'], $job['employment_type'] ?? 'full_time') ?></div>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="/seeker/toggle-save/<?= $job['job_post_id'] ?>" class="btn btn-outline btn-sm">Bỏ lưu</a>
                        <a href="/jobs/<?= $job['job_post_id'] ?>" class="btn btn-primary btn-sm">Ứng Tuyển</a>
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
