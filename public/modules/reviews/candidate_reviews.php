<?php ob_start(); ?>

<div style="margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin: 0;">Đánh Giá Người Lao Động Sau Ca Làm</h1>
        <span class="badge" style="background: linear-gradient(135deg, #ecfdf5, #f0fdfa); color: #047857; border: 1px solid #6ee7b7; font-weight: 800; font-size: 0.72rem; padding: 0.2rem 0.55rem; display: inline-flex; align-items: center; gap: 0.25rem;">
            <?= icon('sparkle', '', 11) ?> Dành Riêng Cho Part-time & Thời Vụ
        </span>
    </div>
    <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">
        Đánh giá 2 chiều về thái độ làm việc, tác phong và kỹ năng của người lao động sau các ca làm việc Part-time & Thời vụ.
    </p>
</div>

<!-- THÔNG BÁO QUY ĐỊNH PHẠM VI ÁP DỤNG -->
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #0d9488; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: #334155; line-height: 1.55;">
    <div style="font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.2rem;">
        <?= icon('info', '', 15) ?> Quy định đánh giá nhân sự sau ca:
    </div>
    Cơ chế đánh giá người lao động <strong>chỉ áp dụng riêng cho các ca làm việc Part-time & Thời vụ</strong> đã được doanh nghiệp duyệt nhận việc. Nhân sự <strong>Toàn thời gian (Full-time)</strong> quản lý đánh giá hiệu quả theo hợp đồng lao động và quy chế nội bộ của doanh nghiệp.
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
    <!-- Form đánh giá người lao động -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.4rem;">
            <?= icon('reviews', '', 18) ?> Gửi Đánh Giá Người Lao Động
        </h3>

        <?php if (!empty($eligibleCandidates)): ?>
            <form action="<?= url('/employer/candidate-reviews/store') ?>" method="POST">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700;">Chọn Nhân Sự Đã Nhận Việc (Part-time & Thời vụ) *</label>
                    <select name="seeker_id" class="form-control" required style="font-size: 0.88rem;">
                        <option value="">-- Chọn người lao động sau ca --</option>
                        <?php foreach ($eligibleCandidates as $c): ?>
                            <?php 
                                $cType = ($c['employment_type'] === 'part_time') ? 'Part-time' : 'Thời vụ';
                                $cShift = !empty($c['work_shift']) ? ' (' . $c['work_shift'] . ')' : '';
                            ?>
                            <option value="<?= $c['seeker_id'] ?>">
                                [<?= $cType ?>] <?= htmlspecialchars($c['full_name']) ?> — <?= htmlspecialchars($c['job_title']) ?><?= htmlspecialchars($cShift) ?> (SĐT: <?= htmlspecialchars($c['phone_number']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #64748b; font-size: 0.78rem; margin-top: 0.3rem; display: block;">
                        <?= icon('check', '', 11) ?> Chỉ hiển thị người lao động đã được duyệt nhận việc các ca Part-time / Thời vụ.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Tiêu Chí Đánh Giá *</label>
                    <select name="criteria_id" class="form-control" required>
                        <?php foreach ($criteriaList as $crit): ?>
                            <option value="<?= $crit['criteria_id'] ?>"><?= htmlspecialchars($crit['criteria_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Điểm Số (1 - 5 sao) *</label>
                    <select name="overall_rating" class="form-control">
                        <option value="5">5 sao (Tác phong xuất sắc, đúng giờ, nhiệt tình)</option>
                        <option value="4">4 sao (Khá tốt, hoàn thành ca)</option>
                        <option value="3">3 sao (Đạt yêu cầu)</option>
                        <option value="2">2 sao (Cần cải thiện thái độ / kỹ năng)</option>
                        <option value="1">1 sao (Vi phạm nội quy ca làm / bỏ ca)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nội Dung Nhận Xét Chi Tiết *</label>
                    <textarea name="review_content" class="form-control" rows="4" required placeholder="Nhận xét cụ thể về kỹ năng, thái độ phục vụ khách hàng, tính trung thực trong ca làm..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 700;">
                    Lưu Đánh Giá Người Lao Động
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem 1rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
                <div style="width: 48px; height: 48px; background: #ecfdf5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto;">
                    <?= icon('user', '', 24) ?>
                </div>
                <h4 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">
                    Chưa Có Nhân Sự Part-time Hoặc Thời Vụ Nào
                </h4>
                <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5; margin: 0 auto 1.15rem auto; max-width: 380px;">
                    Cơ chế đánh giá sau ca chỉ kích hoạt khi doanh nghiệp đã chấp nhận ứng viên vào làm các vị trí <strong>Part-time (Bán thời gian)</strong> hoặc <strong>Thời vụ</strong>. Các vị trí Toàn thời gian không áp dụng tại đây.
                </p>
                <a href="<?= url('/employer/applications') ?>" class="btn btn-primary btn-sm" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                    <?= icon('applications', '', 13) ?> <span>Xem Hồ Sơ Ứng Tuyển</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Danh sách đánh giá -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem;">Lịch Sử Đánh Giá Người Lao Động</h3>
        <?php if (empty($reviews)): ?>
            <p style="color: var(--slate-400); text-align: center; padding: 2rem;">Chưa có đánh giá nào.</p>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
                <div style="border-bottom: 1px solid var(--slate-100); padding-bottom: 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                        <strong><?= htmlspecialchars($rev['seeker_name']) ?></strong>
                        <span class="badge badge-warning" style="display: inline-flex; align-items: center; gap: 0.2rem;"><?= icon('star_fill', '', 12) ?> <?= $rev['overall_rating'] ?>/5</span>
                    </div>
                    <div style="font-size: 0.82rem; color: var(--slate-400); margin-bottom: 0.4rem;">
                        Tiêu chí: <?= htmlspecialchars($rev['criteria_name'] ?? 'Ca làm') ?> • <?= date('d/m/Y', strtotime($rev['created_at'])) ?>
                    </div>
                    <p style="font-size: 0.92rem; color: var(--slate-700);"><?= htmlspecialchars($rev['review_content']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
