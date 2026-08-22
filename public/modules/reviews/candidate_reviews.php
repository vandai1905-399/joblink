<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Đánh Giá Người Lao Động Sau Ca Làm</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Đánh giá thái độ làm việc, tác phong và kỹ năng của người lao động sau ca</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 2rem; align-items: start;">
    <!-- Form đánh giá người lao động -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem;">Gửi Đánh Giá Người Lao Động</h3>
        <form action="/employer/candidate-reviews/store" method="POST">
            <div class="form-group">
                <label class="form-label">Chọn Người Lao Động *</label>
                <select name="seeker_id" class="form-control" required>
                    <?php foreach ($seekers as $s): ?>
                        <option value="<?= $s['seeker_id'] ?>">[<?= formatId('seeker', $s['seeker_id']) ?>] <?= htmlspecialchars($s['full_name']) ?> (<?= htmlspecialchars($s['phone_number']) ?>)</option>
                    <?php endforeach; ?>
                </select>
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
                    <option value="5">5 sao (Tác phong xuất sắc, đúng giờ)</option>
                    <option value="4">4 sao (Khá tốt)</option>
                    <option value="3">3 sao (Đạt yêu cầu)</option>
                    <option value="2">2 sao (Cần cải thiện)</option>
                    <option value="1">1 sao (Vi phạm nội quy)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Nội Dung Nhận Xét *</label>
                <textarea name="review_content" class="form-control" rows="4" required placeholder="Nhận xét cụ thể về kỹ năng, thái độ phục vụ khách hàng..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Lưu Đánh Giá Người Lao Động
            </button>
        </form>
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
