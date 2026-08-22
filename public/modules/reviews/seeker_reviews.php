<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Đánh Giá Môi Trường Làm Việc Sau Ca</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Gửi nhận xét về doanh nghiệp, thù lao ca làm và môi trường để xây dựng cộng đồng minh bạch</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 2rem; align-items: start;">
    <!-- Form gửi đánh giá -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem;">Viết Đánh Giá Mới</h3>
        <form action="/seeker/reviews/create" method="POST">
            <div class="form-group">
                <label class="form-label">Chọn Doanh Nghiệp / Cơ Sở Đã Làm Việc *</label>
                <select name="employer_id" class="form-control" required>
                    <?php foreach ($employers as $emp): ?>
                        <option value="<?= $emp['employer_id'] ?>">[<?= formatId('employer', $emp['employer_id']) ?>] <?= htmlspecialchars($emp['employer_name']) ?></option>
                    <?php endforeach; ?>
                </select>
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
                    <option value="5">5 sao (Rất hài lòng)</option>
                    <option value="4">4 sao (Hài lòng)</option>
                    <option value="3">3 sao (Bình thường)</option>
                    <option value="2">2 sao (Chưa hài lòng)</option>
                    <option value="1">1 sao (Kém)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Nội Dung Nhận Xét Chi Tiết *</label>
                <textarea name="review_content" class="form-control" rows="4" required placeholder="Chia sẻ về thù lao, sự hỗ trợ của quản lý ca, đồng nghiệp..."></textarea>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
                    <input type="checkbox" name="is_anonymous" value="1">
                    <span>Đánh giá ẩn danh (không hiển thị tên của bạn)</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Gửi Đánh Giá Ca Làm
            </button>
        </form>
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
                            <strong style="color: var(--dark); font-size: 0.95rem;"><?= htmlspecialchars($rev['employer_name']) ?></strong>
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
