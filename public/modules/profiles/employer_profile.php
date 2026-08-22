<?php 
ob_start(); 
$branches = !empty($employer['branches']) ? json_decode($employer['branches'], true) : [];
if (!is_array($branches)) $branches = [];
?>

<div style="max-width: 900px;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Hồ Sơ Doanh Nghiệp & Hệ Thống Cơ Sở</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem;">Cập nhật thông tin công ty, trụ sở chính và các chi nhánh / cơ sở làm việc kèm định vị Google Maps</p>
    </div>

    <div class="card">
        <form action="<?= url('/employer/company-profile/update') ?>" method="POST" id="employerProfileForm">
            <!-- Header thông tin đại diện -->
            <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--slate-200);">
                <img src="<?= htmlspecialchars($employer['avatar_url'] ?? 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=150') ?>" alt="logo" style="width: 84px; height: 84px; border-radius: var(--radius); object-fit: cover; border: 1px solid var(--slate-200);">
                <div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark);"><?= htmlspecialchars($employer['employer_name']) ?></h3>
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.35rem; align-items: center; flex-wrap: wrap;">
                        <span class="badge <?= ($employer['verification_status'] ?? '') === 'verified' ? 'badge-success' : 'badge-warning' ?>">
                            <?= ($employer['verification_status'] ?? '') === 'verified' ? icon('check', '', 12) . ' <span>Đã xác minh GPKD</span>' : '<span>Đang chờ duyệt</span>' ?>
                        </span>
                        <span class="badge badge-warning"><?= icon('star_fill', '', 12) ?> <span><?= $employer['avg_rating'] ?? '5.0' ?> (<?= $employer['total_reviews'] ?? 0 ?> đánh giá)</span></span>
                    </div>
                </div>
            </div>

            <!-- Thông tin pháp lý & Liên hệ chung -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tên Doanh Nghiệp / Cửa Hàng *</label>
                    <input type="text" name="employer_name" value="<?= htmlspecialchars($employer['employer_name'] ?? '') ?>" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Người Đại Diện</label>
                    <input type="text" name="representative_name" value="<?= htmlspecialchars($employer['representative_name'] ?? '') ?>" class="form-control" placeholder="Họ và tên người đại diện">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Số GPKD / Mã Số Thuế *</label>
                    <input type="text" name="business_license_number" value="<?= htmlspecialchars($employer['business_license_number'] ?? '') ?>" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Lĩnh Vực Hoạt Động</label>
                    <input type="text" name="business_field" value="<?= htmlspecialchars($employer['business_field'] ?? '') ?>" class="form-control" placeholder="F&B, Bán lẻ, Sản xuất, CNTT...">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Email Liên Hệ Tuyển Dụng</label>
                    <input type="email" name="contact_email" value="<?= htmlspecialchars($employer['contact_email'] ?? '') ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Số Điện Thoại Hotline</label>
                    <input type="text" name="phone_number" value="<?= htmlspecialchars($employer['phone_number'] ?? '') ?>" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Website Doanh Nghiệp</label>
                <input type="url" name="website_url" value="<?= htmlspecialchars($employer['website_url'] ?? '') ?>" class="form-control" placeholder="https://...">
            </div>

            <!-- TRỤ SỞ CHÍNH -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; margin-top: 1.5rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="font-weight: 700; color: #0f172a; font-size: 1rem; display: flex; align-items: center; gap: 0.4rem;">
                        <?= icon('building', '', 16) ?>
                        <span>Địa Chỉ Trụ Sở Chính *</span>
                        <span class="badge badge-primary" style="font-size: 0.75rem;">Trụ sở chính</span>
                    </div>
                    <?php 
                        $mainMapUrl = !empty($employer['map_url']) ? $employer['map_url'] : (!empty($employer['address']) ? getGoogleMapsUrl($employer['address']) : '');
                    ?>
                    <?php if (!empty($mainMapUrl)): ?>
                        <a href="<?= htmlspecialchars($mainMapUrl) ?>" target="_blank" class="btn btn-outline btn-sm" style="background:#fff; font-size: 0.8rem; color: #0d9488; border-color: #0d9488; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <?= icon('location', '', 13) ?> <span>Xem Vị Trí Trên Google Maps &rarr;</span>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" style="font-size: 0.85rem;">Địa Chỉ Trụ Sở *</label>
                    <input type="text" name="address" id="main_address_input" value="<?= htmlspecialchars($employer['address'] ?? '') ?>" class="form-control" required placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố...">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.85rem; color: #334155; font-weight: 600; display: flex; align-items: center; gap: 0.35rem;">
                        <?= icon('location', '', 14) ?> <span>Link Chỉ Đường / Vị Trí Google Maps (Dán link công ty tự chọn trên Google Maps)</span>
                    </label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="url" name="map_url" id="main_map_url_input" value="<?= htmlspecialchars($employer['map_url'] ?? '') ?>" class="form-control" placeholder="Dán link chia sẻ vị trí: https://maps.app.goo.gl/... hoặc https://maps.google.com/..." style="font-size: 0.88rem;">
                        <a href="https://www.google.com/maps" target="_blank" class="btn btn-outline btn-sm" style="white-space: nowrap; padding: 0.55rem 0.85rem; font-size: 0.82rem; font-weight: 700; color: #0d9488; border-color: #0d9488; background: #fff; display: inline-flex; align-items: center; gap: 0.3rem;" title="Mở Google Maps để tìm vị trí và copy link">
                            <?= icon('search', '', 13) ?> <span>Mở Google Maps</span>
                        </a>
                    </div>
                    <small style="color: #64748b; font-size: 0.8rem; margin-top: 0.35rem; display: flex; align-items: center; gap: 0.35rem;">
                        <?= icon('lightbulb', '', 13) ?> <em>Cách lấy link: Mở Google Maps &rarr; Chọn đúng vị trí công ty của bạn &rarr; Bấm nút <strong>Chia sẻ (Share)</strong> &rarr; Chọn <strong>Sao chép đường liên kết</strong> và dán vào đây.</em>
                    </small>
                </div>
            </div>

            <!-- HỆ THỐNG CƠ SỞ & CHI NHÁNH KHÁC -->
            <div style="border-top: 2px dashed #e2e8f0; padding-top: 1.75rem; margin-top: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--dark); margin-bottom: 0.2rem; display: flex; align-items: center; gap: 0.4rem;">
                            <?= icon('location', '', 18) ?> <span>Danh Sách Các Cơ Sở / Chi Nhánh Khác</span>
                        </h3>
                        <p style="font-size: 0.88rem; color: var(--slate-500); margin: 0;">
                            Lưu các địa điểm làm việc, cơ sở hoặc chi nhánh của doanh nghiệp kèm link Google Maps tự chọn
                        </p>
                    </div>
                    <button type="button" id="btnAddBranch" class="btn btn-outline btn-sm" style="border-color: #0d9488; color: #0d9488; font-weight: 700;">
                        + Thêm Cơ Sở Mới
                    </button>
                </div>

                <div id="branchesContainer" style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php if (empty($branches)): ?>
                        <div id="noBranchAlert" style="text-align: center; padding: 1.5rem; background: #fafafa; border: 1px dashed #cbd5e1; border-radius: 8px; color: var(--slate-500); font-size: 0.9rem;">
                            Chưa có cơ sở chi nhánh phụ nào. Bấm nút <strong>"+ Thêm Cơ Sở Mới"</strong> để thêm địa điểm làm việc cho người lao động.
                        </div>
                    <?php else: ?>
                        <?php foreach ($branches as $idx => $b): ?>
                            <div class="branch-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 1.15rem; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                                    <span style="font-weight: 700; color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.35rem;">
                                        <?= icon('location', '', 14) ?> <span>Cơ sở / Chi nhánh #<?= $idx + 1 ?></span>
                                    </span>
                                    <button type="button" class="btn-remove-branch" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; cursor: pointer; font-weight: 600;">
                                        Xóa
                                    </button>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="margin-bottom: 0.75rem;">
                                        <label class="form-label" style="font-size: 0.85rem;">Tên Cơ Sở / Chi Nhánh *</label>
                                        <input type="text" name="branches[<?= $idx ?>][name]" value="<?= htmlspecialchars($b['name'] ?? '') ?>" class="form-control" placeholder="VD: Cơ sở 2 - Thuận An" required>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0.75rem;">
                                        <label class="form-label" style="font-size: 0.85rem;">Hotline / SĐT Cơ Sở</label>
                                        <input type="text" name="branches[<?= $idx ?>][phone]" value="<?= htmlspecialchars($b['phone'] ?? '') ?>" class="form-control" placeholder="VD: 0274 3888 999">
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom: 0.75rem;">
                                    <label class="form-label" style="font-size: 0.85rem;">Địa Chỉ Chi Tiết *</label>
                                    <input type="text" name="branches[<?= $idx ?>][address]" value="<?= htmlspecialchars($b['address'] ?? '') ?>" class="form-control branch-address" placeholder="Địa chỉ cơ sở này..." required>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="font-size: 0.82rem; color: #334155; font-weight: 600;">Link Chia Sẻ Vị Trí Google Maps</label>
                                        <?php if (!empty($b['map_url'])): ?>
                                            <a href="<?= htmlspecialchars($b['map_url']) ?>" target="_blank" style="font-size: 0.8rem; color: #0d9488; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                <?= icon('location', '', 12) ?> <span>Xem vị trí &rarr;</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="url" name="branches[<?= $idx ?>][map_url]" value="<?= htmlspecialchars($b['map_url'] ?? '') ?>" class="form-control" placeholder="https://maps.app.goo.gl/... hoặc https://maps.google.com/..." style="font-size: 0.88rem;">
                                        <a href="https://www.google.com/maps" target="_blank" class="btn btn-outline btn-sm" style="white-space: nowrap; padding: 0.5rem 0.75rem; font-size: 0.8rem; color: #0d9488; border-color: #0d9488; background: #fff; display: inline-flex; align-items: center; gap: 0.25rem;" title="Mở Google Maps để lấy link">
                                            <?= icon('search', '', 12) ?> <span>Lấy Link</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">
                    Lưu Thay Đổi Hồ Sơ & Hệ Thống Cơ Sở
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('branchesContainer');
    const btnAdd = document.getElementById('btnAddBranch');
    const noBranchAlert = document.getElementById('noBranchAlert');

    let branchCount = container.querySelectorAll('.branch-card').length;

    function checkEmptyAlert() {
        const cards = container.querySelectorAll('.branch-card');
        if (cards.length === 0) {
            if (!document.getElementById('noBranchAlert')) {
                const alertDiv = document.createElement('div');
                alertDiv.id = 'noBranchAlert';
                alertDiv.style.cssText = 'text-align: center; padding: 1.5rem; background: #fafafa; border: 1px dashed #cbd5e1; border-radius: 8px; color: var(--slate-500); font-size: 0.9rem;';
                alertDiv.innerHTML = 'Chưa có cơ sở chi nhánh phụ nào. Bấm nút <strong>"+ Thêm Cơ Sở Mới"</strong> để thêm địa điểm làm việc cho người lao động.';
                container.appendChild(alertDiv);
            }
        } else {
            const alertElem = document.getElementById('noBranchAlert');
            if (alertElem) alertElem.remove();
        }
    }

    btnAdd.addEventListener('click', function() {
        const alertElem = document.getElementById('noBranchAlert');
        if (alertElem) alertElem.remove();

        const index = Date.now();
        const displayIndex = container.querySelectorAll('.branch-card').length + 1;

        const card = document.createElement('div');
        card.className = 'branch-card';
        card.style.cssText = 'background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 1.15rem; box-shadow: 0 2px 6px rgba(0,0,0,0.03); animation: fadeIn 0.3s ease;';
        card.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                <span style="font-weight: 700; color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.35rem;">
                    <?= icon('location', '', 14) ?> <span>Cơ sở / Chi nhánh mới #\${displayIndex}</span>
                </span>
                <button type="button" class="btn-remove-branch" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; cursor: pointer; font-weight: 600;">
                    Xóa
                </button>
            </div>
            <div class="form-row">
                <div class="form-group" style="margin-bottom: 0.75rem;">
                    <label class="form-label" style="font-size: 0.85rem;">Tên Cơ Sở / Chi Nhánh *</label>
                    <input type="text" name="branches[\${index}][name]" class="form-control" placeholder="VD: Cơ sở 2 - Thuận An" required>
                </div>
                <div class="form-group" style="margin-bottom: 0.75rem;">
                    <label class="form-label" style="font-size: 0.85rem;">Hotline / SĐT Cơ Sở</label>
                    <input type="text" name="branches[\${index}][phone]" class="form-control" placeholder="VD: 0274 3888 999">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label" style="font-size: 0.85rem;">Địa Chỉ Chi Tiết *</label>
                <input type="text" name="branches[\${index}][address]" class="form-control branch-address" placeholder="Địa chỉ cơ sở này..." required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="font-size: 0.82rem; color: #334155; font-weight: 600;">Link Chia Sẻ Vị Trí Google Maps</label>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="url" name="branches[\${index}][map_url]" class="form-control" placeholder="https://maps.app.goo.gl/... hoặc https://maps.google.com/..." style="font-size: 0.88rem;">
                    <a href="https://www.google.com/maps" target="_blank" class="btn btn-outline btn-sm" style="white-space: nowrap; padding: 0.5rem 0.75rem; font-size: 0.8rem; color: #0d9488; border-color: #0d9488; background: #fff; display: inline-flex; align-items: center; gap: 0.25rem;" title="Mở Google Maps để lấy link">
                        <?= icon('search', '', 12) ?> <span>Lấy Link</span>
                    </a>
                </div>
            </div>
        `;

        container.appendChild(card);
        card.querySelector('input[type="text"]').focus();
    });

    container.addEventListener('click', function(e) {
        const btnRemove = e.target.closest('.btn-remove-branch');
        if (btnRemove) {
            const card = btnRemove.closest('.branch-card');
            if (card) {
                card.remove();
                checkEmptyAlert();
            }
        }
    });
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
