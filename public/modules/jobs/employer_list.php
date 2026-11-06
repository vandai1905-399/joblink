<?php ob_start(); ?>

<div class="container">
    <div style="margin-bottom: 2rem;">
        <h1 class="section-title">Danh Sách Nhà Tuyển Dụng Uy Tín</h1>
        <p class="section-subtitle">Tất cả doanh nghiệp, cửa hàng và cơ sở kinh doanh đã nộp giấy phép kinh doanh và được thẩm định</p>
    </div>

    <div class="grid-employers" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
        <?php if (empty($employers)): ?>
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                <p style="color: var(--slate-500);">Chưa có dữ liệu nhà tuyển dụng nào hoặc chưa kết nối CSDL.</p>
                <a href="<?= url('/test-db') ?>" class="btn btn-outline btn-sm" style="margin-top: 1rem;">Kiểm tra kết nối CSDL</a>
            </div>
        <?php else: ?>
            <?php foreach ($employers as $emp): ?>
                <div class="card employer-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <img src="<?= htmlspecialchars(getAvatarUrl($emp['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                        <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.35rem;">
                            <a href="<?= url('/employers/' . $emp['employer_id']) ?>"><?= htmlspecialchars(!empty($emp['employer_name']) ? $emp['employer_name'] : 'Doanh Nghiệp ' . formatId('employer', $emp['employer_id'])) ?></a>
                        </h3>
                        <p style="font-size: 0.88rem; color: var(--slate-500); margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: center; gap: 0.35rem;">
                            <?= icon('building', '', 14) ?> <span><?= htmlspecialchars($emp['business_field'] ?? 'Doanh nghiệp') ?></span>
                        </p>
                        <p style="font-size: 0.82rem; color: var(--slate-600); margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.35rem;">
                            <?= icon('location', '', 14) ?> 
                            <?php if (!empty($emp['address'])): ?>
                                <a href="<?= htmlspecialchars(!empty($emp['map_url']) ? $emp['map_url'] : getGoogleMapsUrl($emp['address'])) ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;" title="Mở vị trí trên Google Maps">
                                    <?= htmlspecialchars($emp['address']) ?>
                                </a>
                            <?php else: ?>
                                <span>Chưa cập nhật địa chỉ</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 1rem; align-items: center;">
                            <span class="badge <?= $emp['verification_status'] === 'verified' ? 'badge-success' : 'badge-warning' ?>">
                                <?= $emp['verification_status'] === 'verified' ? icon('check', '', 12) . ' <span>Đã xác minh</span>' : '<span>Đang duyệt</span>' ?>
                            </span>
                            <span class="badge badge-warning"><?= icon('star_fill', '', 12) ?> <span><?= $emp['avg_rating'] ?? '5.0' ?> (<?= $emp['total_reviews'] ?? 0 ?> đánh giá)</span></span>
                        </div>
                        <a href="<?= url('/employers/' . $emp['employer_id']) ?>" class="btn btn-outline btn-sm" style="width: 100%;">Xem Hồ Sơ & Đánh Giá</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/main_layout.php';
?>
