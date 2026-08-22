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
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= url('/seeker/saved-jobs') ?>'">
        <div class="stat-title">Việc Làm Đã Lưu</div>
        <div class="stat-value" style="color: var(--warning);"><?= count($savedJobs ?? []) ?></div>
        <a href="<?= url('/seeker/saved-jobs') ?>" style="font-size: 0.82rem; color: var(--warning); font-weight: 600;">Xem danh sách &rarr;</a>
    </div>
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= url('/seeker/meetings') ?>'">
        <div class="stat-title">Lịch Phỏng Vấn Sắp Tới</div>
        <div class="stat-value" style="color: var(--success);"><?= count($meetings ?? []) ?></div>
        <a href="<?= url('/seeker/meetings') ?>" style="font-size: 0.82rem; color: var(--success); font-weight: 600;">Xem lịch hẹn &rarr;</a>
    </div>
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= url('/seeker/reviews') ?>'">
        <div class="stat-title">Điểm Đánh Giá Ca Làm</div>
        <div class="stat-value" style="color: var(--primary); display: flex; align-items: center; gap: 0.25rem;"><?= $seeker['avg_rating'] ?? '5.0' ?> <?= icon('star_fill', '', 20) ?></div>
        <div style="font-size: 0.82rem; color: var(--slate-500);"><?= $seeker['total_reviews'] ?? 0 ?> lượt đánh giá</div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- KHỐI VIỆC LÀM ĐỀ XUẤT BỞI AI (AI MATCHING RECOMMENDATIONS)               -->
<!-- ========================================================================= -->
<div class="ai-recommend-container">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
        <div>
            <div class="ai-badge-header">
                ✨ <span>JobLink AI Matching Engine</span>
            </div>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0;">
                Việc Làm Đề Xuất Phù Hợp Dành Riêng Cho Bạn
            </h2>
            <p style="color: #475569; font-size: 0.88rem; margin: 0.25rem 0 0 0;">
                Dựa trên kỹ năng chuyên môn, kinh nghiệm, khoảng cách địa lý và kỳ vọng thu nhập trong hồ sơ của bạn
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="<?= url('/seeker/profile') ?>" class="btn btn-outline btn-sm" style="font-size: 0.8rem; background: #ffffff; border-color: #0d9488; color: #0d9488; font-weight: 700;">
                ✨ Tối Ưu Hồ Sơ
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
                                    <span class="ai-skill-tag matched">✓ <?= htmlspecialchars($msk) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Lý do AI đề xuất -->
                        <?php if (!empty($ajob['ai_reasons'])): ?>
                            <ul class="ai-reasons-list">
                                <?php foreach (array_slice($ajob['ai_reasons'], 0, 2) as $reason): ?>
                                    <li class="ai-reason-item">
                                        <span style="color: #0d9488; font-size: 0.7rem;">⚡</span>
                                        <span><?= htmlspecialchars($reason) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; border-top: 1px solid #f1f5f9; padding-top: 0.75rem; gap: 0.5rem;">
                        <button type="button" class="btn btn-outline btn-sm" data-ai-job-fit="<?= $ajob['job_post_id'] ?>" style="font-size: 0.75rem; padding: 0.3rem 0.6rem; color: #0f766e; border-color: #99f6e4;">
                            ✨ Phân tích AI
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
