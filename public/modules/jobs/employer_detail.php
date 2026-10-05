<?php 
ob_start(); 
$branches = !empty($employer['branches']) ? json_decode($employer['branches'], true) : [];
if (!is_array($branches)) $branches = [];
?>

<div class="container" style="padding-top: 1.5rem; padding-bottom: 3rem;">
    <div style="margin-bottom: 1.5rem;">
        <a href="<?= url('/employers') ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">&larr; Danh sách nhà tuyển dụng</a>
    </div>

    <!-- Banner thông tin NTD -->
    <div class="card" style="margin-bottom: 2rem; display: flex; gap: 2rem; align-items: center; flex-wrap: wrap;">
        <img src="<?= htmlspecialchars($employer['avatar_url'] ?? 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=150') ?>" alt="logo" style="width: 100px; height: 100px; border-radius: var(--radius); object-fit: cover; border: 1px solid var(--slate-200);">
        <div style="flex: 1; min-width: 280px;">
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--dark); margin-bottom: 0.35rem;">
                <?= htmlspecialchars(!empty($employer['employer_name']) ? $employer['employer_name'] : 'Doanh Nghiệp ' . formatId('employer', $employer['employer_id'] ?? '')) ?>
            </h1>
            <p style="font-size: 0.95rem; color: var(--slate-600); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span><?= icon('location', '', 14) ?> Trụ sở chính: <?= htmlspecialchars($employer['address'] ?? 'Chưa cập nhật địa chỉ') ?></span>
                <?php 
                    $mainMapUrl = !empty($employer['map_url']) ? $employer['map_url'] : (!empty($employer['address']) ? getGoogleMapsUrl($employer['address']) : '');
                ?>
                <?php if (!empty($mainMapUrl)): ?>
                    <a href="<?= htmlspecialchars($mainMapUrl) ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 0.15rem 0.55rem; font-size: 0.78rem; font-weight: 700; color: #0d9488; border-color: #0d9488; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <?= icon('location', '', 12) ?> <span>Xem trên Google Maps</span>
                    </a>
                <?php endif; ?>
            </p>
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <?php if (!empty($employer['business_license_number'])): ?>
                    <span class="badge badge-success"><?= icon('check', '', 12) ?> Xác minh GPKD: <?= htmlspecialchars($employer['business_license_number']) ?></span>
                <?php else: ?>
                    <span class="badge badge-success"><?= icon('check', '', 12) ?> Doanh nghiệp đã thẩm định</span>
                <?php endif; ?>
                <span class="badge badge-warning"><?= icon('star_fill', '', 12) ?> <?= $employer['avg_rating'] ?? '5.0' ?> / 5.0 (<?= $employer['total_reviews'] ?? 0 ?> lượt đánh giá)</span>
                <span class="badge badge-primary"><?= htmlspecialchars($employer['business_field'] ?? 'Doanh nghiệp') ?></span>
            </div>
        </div>
        <div>
            <?php if (!empty($employer['website_url'])): ?>
                <a href="<?= htmlspecialchars($employer['website_url']) ?>" target="_blank" class="btn btn-outline">Truy cập Website</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- HỆ THỐNG TRỤ SỞ & CÁC CƠ SỞ / CHI NHÁNH KÈM GOOGLE MAPS -->
    <div class="card" style="margin-bottom: 2rem; background: #ffffff; border-left: 4px solid #0d9488;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h2 style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin-bottom: 0.2rem; display: flex; align-items: center; gap: 0.5rem;">
                    <?= icon('location', '', 18) ?>
                    <span>Hệ Thống Trụ Sở & Cơ Sở Chi Nhánh</span>
                    <span class="badge badge-slate" style="font-size: 0.8rem;"><?= 1 + count($branches) ?> địa điểm</span>
                </h2>
                <p style="font-size: 0.88rem; color: var(--slate-500); margin: 0;">
                    Danh sách các địa điểm làm việc, cơ sở trực thuộc của doanh nghiệp có tích hợp chỉ đường Google Maps
                </p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.25rem;">
            <!-- Trụ sở chính -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <strong style="color: #0f172a; font-size: 1rem; display: flex; align-items: center; gap: 0.35rem;"><?= icon('building', '', 16) ?> <span>Trụ Sở Chính</span></strong>
                        <span class="badge badge-primary" style="font-size: 0.72rem;">Trụ sở chính</span>
                    </div>
                    <p style="font-size: 0.9rem; color: #334155; margin-bottom: 0.5rem; line-height: 1.45;">
                        <?= htmlspecialchars($employer['address'] ?? 'Chưa cập nhật địa chỉ') ?>
                    </p>
                    <?php if (!empty($employer['phone_number'])): ?>
                        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.35rem;">
                            <?= icon('phone', '', 14) ?> Hotline: <strong><?= htmlspecialchars($employer['phone_number']) ?></strong>
                        </p>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="<?= htmlspecialchars(!empty($employer['map_url']) ? $employer['map_url'] : getGoogleMapsUrl($employer['address'] ?? '')) ?>" target="_blank" class="btn btn-outline btn-sm" style="width: 100%; text-align: center; color: #0d9488; border-color: #0d9488; font-weight: 700; background: #fff; display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem;">
                        <?= icon('location', '', 14) ?> <span>Chỉ Đường Trên Google Maps &rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Các cơ sở chi nhánh khác -->
            <?php foreach ($branches as $idx => $branch): ?>
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <strong style="color: #0f172a; font-size: 1rem; display: flex; align-items: center; gap: 0.35rem;"><?= icon('location', '', 15) ?> <span><?= htmlspecialchars($branch['name'] ?? ('Cơ sở #' . ($idx + 1))) ?></span></strong>
                            <span class="badge badge-slate" style="font-size: 0.72rem;">Chi nhánh</span>
                        </div>
                        <p style="font-size: 0.9rem; color: #334155; margin-bottom: 0.5rem; line-height: 1.45;">
                            <?= htmlspecialchars($branch['address'] ?? '') ?>
                        </p>
                        <?php if (!empty($branch['phone'])): ?>
                            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.35rem;">
                                <?= icon('phone', '', 14) ?> SĐT: <strong><?= htmlspecialchars($branch['phone']) ?></strong>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="<?= htmlspecialchars(!empty($branch['map_url']) ? $branch['map_url'] : getGoogleMapsUrl($branch['address'] ?? '')) ?>" target="_blank" class="btn btn-outline btn-sm" style="width: 100%; text-align: center; color: #0d9488; border-color: #0d9488; font-weight: 700; background: #f0fdfa; display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem;">
                            <?= icon('location', '', 14) ?> <span>Chỉ Đường Trên Google Maps &rarr;</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Layout 2 cột: Tin tuyển dụng & Đánh giá -->
    <div style="display: grid; grid-template-columns: 3fr 2fr; gap: 2rem; align-items: start;">
        <!-- Tin tuyển dụng đang mở -->
        <div>
            <h2 class="section-title" style="font-size: 1.35rem; margin-bottom: 1.25rem;">
                Tin Tuyển Dụng Đang Tuyển (<?= count($jobs) ?>)
            </h2>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php if (empty($jobs)): ?>
                    <div class="card" style="text-align: center; padding: 2rem; color: var(--slate-500);">
                        Hiện tại doanh nghiệp chưa có tin đăng mới.
                    </div>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="card job-card">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                                <div>
                                    <h3 style="font-size: 1.1rem; font-weight: 700;">
                                        <a href="<?= url('/jobs/' . $job['job_post_id']) ?>"><?= htmlspecialchars($job['job_title'] ?? '') ?></a>
                                    </h3>
                                    <div style="font-size: 0.85rem; color: var(--slate-500); margin-top: 0.35rem; display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                        <?= renderWorkShiftBadges($job['work_shift'] ?? 'Hành chính') ?>
                                        <span style="color: #cbd5e1;">•</span>
                                        <span>Hạn nộp: <?= !empty($job['application_deadline']) ? date('d/m/Y', strtotime($job['application_deadline'])) : '—' ?></span>
                                    </div>
                                </div>
                                <div class="job-salary" style="font-size: 1rem; white-space: nowrap;">
                                    <?= formatSalary($job['salary_amount'] ?? 0, $job['employment_type'] ?? 'full_time') ?>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span class="badge badge-slate">
                                        <?php 
                                            if (($job['employment_type'] ?? '') === 'full_time') echo 'Toàn thời gian';
                                             elseif (($job['employment_type'] ?? '') === 'part_time') echo 'Bán thời gian';
                                             elseif (($job['employment_type'] ?? '') === 'temporary') echo 'Thời vụ';
                                             else echo 'Toàn thời gian';
                                        ?>
                                    </span>
                                    <span style="font-size: 0.82rem; color: var(--slate-500); display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('location', '', 12) ?> <?= htmlspecialchars(mb_substr($job['workplace_address'] ?? '', 0, 30)) ?>...
                                    </span>
                                </div>
                                <div style="display: flex; gap: 0.4rem;">
                                    <?php if (!empty($job['workplace_address'])): ?>
                                        <a href="<?= getGoogleMapsUrl($job['workplace_address']) ?>" target="_blank" class="btn btn-outline btn-sm" title="Xem vị trí làm việc trên Google Maps" style="padding: 0.25rem 0.5rem; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <?= icon('location', '', 12) ?> <span>Bản đồ</span>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= url('/jobs/' . $job['job_post_id']) ?>" class="btn btn-primary btn-sm">Xem Chi Tiết</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lịch sử đánh giá ca làm việc -->
        <div>
            <h2 class="section-title" style="font-size: 1.35rem; margin-bottom: 1.25rem;">
                Đánh Giá Từ Người Lao Động (<?= count($reviews) ?>)
            </h2>
            <div class="card">
                <?php if (empty($reviews)): ?>
                    <p style="color: var(--slate-500);">Chưa có đánh giá nào cho doanh nghiệp này.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div style="border-bottom: 1px solid var(--slate-100); padding-bottom: 1rem; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <strong><?= !empty($rev['is_anonymous']) ? 'Người lao động ẩn danh' : htmlspecialchars($rev['seeker_name'] ?? 'Người lao động') ?></strong>
                                <span class="badge badge-warning"><?= icon('star_fill', '', 12) ?> <?= $rev['overall_rating'] ?? 5 ?>/5</span>
                            </div>
                            <div style="font-size: 0.82rem; color: var(--slate-400); margin-bottom: 0.5rem;">
                                Tiêu chí: <?= htmlspecialchars($rev['criteria_name'] ?? 'Ca làm việc') ?> • <?= !empty($rev['created_at']) ? date('d/m/Y', strtotime($rev['created_at'])) : '' ?>
                            </div>
                            <p style="font-size: 0.92rem; color: var(--slate-700); line-height: 1.5;"><?= htmlspecialchars($rev['review_content'] ?? '') ?></p>

                            <?php if (!empty($rev['reply_content'])): ?>
                                <div style="background: var(--primary-light); padding: 0.65rem; border-radius: var(--radius-sm); margin-top: 0.5rem; font-size: 0.88rem;">
                                    <strong>Phản hồi từ NTD:</strong> <?= htmlspecialchars($rev['reply_content']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/main_layout.php';
?>
