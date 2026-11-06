<?php ob_start(); ?>

<div style="margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin: 0;">Đánh Giá Nơi Làm Việc Sau Ca</h1>
        <span class="badge" style="background: linear-gradient(135deg, #ecfdf5, #f0fdfa); color: #047857; border: 1px solid #6ee7b7; font-weight: 800; font-size: 0.72rem; padding: 0.2rem 0.55rem; display: inline-flex; align-items: center; gap: 0.25rem;">
            <?= icon('sparkle', '', 11) ?> Dành Riêng Cho Part-time & Thời Vụ
        </span>
    </div>
    <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">
        Cơ chế đánh giá 2 chiều minh bạch dành riêng cho các ca làm việc Bán thời gian (Part-time) & Thời vụ sau khi hoàn thành ca làm.
    </p>
</div>

<!-- THÔNG BÁO QUY ĐỊNH PHẠM VI ÁP DỤNG -->
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #0d9488; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: #334155; line-height: 1.55;">
    <div style="font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.2rem;">
        <?= icon('info', '', 15) ?> Quy định phạm vi đánh giá ca làm việc:
    </div>
    Tính năng này <strong>chỉ dành riêng cho các ca làm việc Part-time & Thời vụ</strong> mà bạn đã được nhà tuyển dụng chấp thuận nhận việc. Các công việc <strong>Toàn thời gian (Full-time)</strong> được quản lý theo hợp đồng lao động chuẩn của doanh nghiệp nên không áp dụng hình thức đánh giá sau ca này.
</div>

<?php if (!empty($_SESSION['flash_msg'])): ?>
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
        <?= icon('check', '', 16) ?> <?= htmlspecialchars($_SESSION['flash_msg']) ?>
    </div>
    <?php unset($_SESSION['flash_msg']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
        <?= icon('x_circle', '', 16) ?> <?= htmlspecialchars($_SESSION['flash_error']) ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 2rem; align-items: start;">
    <!-- Form gửi đánh giá -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.4rem;">
            <?= icon('reviews', '', 18) ?> Viết Đánh Giá Mới
        </h3>

        <?php if (!empty($eligibleShifts)): ?>
            <form action="<?= url('/seeker/reviews/create') ?>" method="POST">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700;">Chọn Ca Làm Việc / Nơi Làm Việc (Part-time & Thời vụ) *</label>
                    <select name="employer_id" class="form-control" required style="font-size: 0.88rem;">
                        <option value="">-- Chọn ca làm đã nhận việc --</option>
                        <?php foreach ($eligibleShifts as $shift): ?>
                            <?php 
                                $typeName = ($shift['employment_type'] === 'part_time') ? 'Part-time' : 'Thời vụ';
                                $shiftText = !empty($shift['work_shift']) ? ' (' . $shift['work_shift'] . ')' : '';
                            ?>
                            <option value="<?= $shift['employer_id'] ?>">
                                [<?= $typeName ?>] <?= htmlspecialchars($shift['employer_name']) ?> — <?= htmlspecialchars($shift['job_title']) ?><?= htmlspecialchars($shiftText) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #64748b; font-size: 0.78rem; margin-top: 0.3rem; display: block;">
                        <?= icon('check', '', 11) ?> Chỉ liệt kê các ca làm Part-time / Thời vụ bạn đã được xác nhận nhận việc.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Tiêu Chí Nhận Xét *</label>
                    <select name="criteria_id" class="form-control" required>
                        <?php foreach ($criteriaList as $crit): ?>
                            <option value="<?= $crit['criteria_id'] ?>"><?= htmlspecialchars($crit['criteria_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Điểm Đánh Giá Ca Làm (1 - 5 sao) *</label>
                    <select name="overall_rating" class="form-control">
                        <option value="5">5 sao (Rất hài lòng, thù lao đúng hạn)</option>
                        <option value="4">4 sao (Hài lòng)</option>
                        <option value="3">3 sao (Bình thường)</option>
                        <option value="2">2 sao (Chưa hài lòng)</option>
                        <option value="1">1 sao (Kém, vi phạm cam kết)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nội Dung Nhận Xét Chi Tiết *</label>
                    <textarea name="review_content" class="form-control" rows="4" required placeholder="Chia sẻ về thù lao ca làm, sự hỗ trợ của quản lý ca, đồng nghiệp, môi trường..."></textarea>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
                        <input type="checkbox" name="is_anonymous" value="1">
                        <span>Đánh giá ẩn danh (không hiển thị tên của bạn)</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 700;">
                    Gửi Đánh Giá Ca Làm Việc
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem 1rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
                <div style="width: 48px; height: 48px; background: #ecfdf5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto;">
                    <?= icon('briefcase', '', 24) ?>
                </div>
                <h4 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">
                    Chưa Có Ca Làm Part-time Hoặc Thời Vụ Nào
                </h4>
                <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5; margin: 0 auto 1.15rem auto; max-width: 380px;">
                    Tính năng đánh giá chỉ mở cho các ca làm việc <strong>Part-time (Bán thời gian)</strong> và <strong>Thời vụ</strong> mà bạn đã được xác nhận nhận việc. Các công việc Toàn thời gian (Full-time) không áp dụng biểu mẫu này.
                </p>
                <a href="<?= url('/jobs?employment_type=part_time') ?>" class="btn btn-primary btn-sm" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                    <?= icon('search', '', 13) ?> <span>Tìm Việc Part-time & Thời Vụ</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Lịch sử đánh giá đã gửi -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem;">Lịch Sử Đánh Giá Của Bạn (<?= count($myReviews) ?>)</h3>
        <?php if (empty($myReviews)): ?>
            <p style="color: var(--slate-400); text-align: center; padding: 2rem;">Bạn chưa gửi đánh giá nào.</p>
        <?php else: ?>
            <?php foreach ($myReviews as $rev): ?>
                <div style="border-bottom: 1px solid var(--slate-100); padding-bottom: 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <strong style="color: var(--dark); font-size: 0.95rem;">
                                <?php if (!empty($rev['employer_id'])): ?>
                                    <a href="<?= url('/employers/' . $rev['employer_id']) ?>" data-title="<?= htmlspecialchars($rev['employer_name']) ?>" style="color: var(--dark); text-decoration: underline;" title="Xem hồ sơ doanh nghiệp">
                                        <?= htmlspecialchars($rev['employer_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <?= htmlspecialchars($rev['employer_name']) ?>
                                <?php endif; ?>
                            </strong>
                            <div style="font-size: 0.82rem; color: var(--slate-400); margin: 0.2rem 0;">
                                Tiêu chí: <?= htmlspecialchars($rev['criteria_name'] ?? 'Ca làm') ?> • <?= date('d/m/Y', strtotime($rev['created_at'])) ?>
                            </div>
                        </div>
                        <span class="badge badge-warning" style="display: inline-flex; align-items: center; gap: 0.2rem;"><?= icon('star_fill', '', 12) ?> <?= $rev['overall_rating'] ?>/5</span>
                    </div>

                    <p style="font-size: 0.92rem; color: var(--slate-700); margin-top: 0.4rem;">
                        <?= htmlspecialchars($rev['review_content']) ?>
                    </p>

                    <?php if (!empty($rev['reply_content'])): ?>
                        <div style="background: var(--primary-light); padding: 0.65rem; border-radius: var(--radius-sm); margin-top: 0.5rem; font-size: 0.85rem;">
                            <strong>Phản hồi từ NTD:</strong> <?= htmlspecialchars($rev['reply_content']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
