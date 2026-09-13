<?php ob_start(); ?>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 0.5rem;">
            <?= icon('file_signature', 'text-primary', 26) ?> Hợp Đồng Điện Tử & Ký Số Doanh Nghiệp
        </h1>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin-top: 0.25rem;">
            Quản lý và thực hiện ký số điện tử (2 chiều) giữa Ban Quản Trị Sàn JobLink và Doanh nghiệp tuyển dụng theo Luật Giao dịch Điện tử.
        </p>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm" onclick="showNewContractModal()" style="display: inline-flex; align-items: center; gap: 0.4rem;">
            <?= icon('plus', '', 16) ?> <span>+ Lập Dự Thảo Hợp Đồng Mới</span>
        </button>
    </div>
</div>

<!-- THỐNG KÊ HỢP ĐỒNG -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #10b981;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Hợp Đồng Đã Ký 2 Bên (Hiệu Lực)</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #10b981; margin-top: 0.25rem;">
            <?= count(array_filter($contracts, fn($c) => $c['status'] === 'active')) ?>
        </div>
        <div style="font-size: 0.75rem; color: #059669;">Có đầy đủ 2 chữ ký số SHA-256</div>
    </div>
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #3b82f6;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Admin Đã Ký (Chờ DN Ký)</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #3b82f6; margin-top: 0.25rem;">
            <?= count(array_filter($contracts, fn($c) => $c['status'] === 'admin_signed')) ?>
        </div>
        <div style="font-size: 0.75rem; color: #2563eb;">Doanh nghiệp đã nhận thông báo</div>
    </div>
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Dự Thảo Chờ Admin Ký Số</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #f59e0b; margin-top: 0.25rem;">
            <?= count(array_filter($contracts, fn($c) => $c['status'] === 'draft' || $c['status'] === 'employer_signed')) ?>
        </div>
        <div style="font-size: 0.75rem; color: #d97706;">Cần Admin ký số đại diện Sàn</div>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; font-weight: 800; display: flex; align-items: center; gap: 0.5rem;">
                <?= icon('building', 'text-primary', 18) ?> Danh Sách Hợp Đồng & Doanh Nghiệp (<?= count($contracts) ?>)
            </h3>
            <p style="margin: 0.2rem 0 0 0; font-size: 0.82rem; color: #64748b;">
                Bấm trực tiếp vào tên hoặc logo doanh nghiệp để chuyển ngay đến hồ sơ doanh nghiệp.
            </p>
        </div>
        <div style="position: relative; min-width: 260px;">
            <input type="text" id="contractSearchInput" placeholder="Tìm tên doanh nghiệp, mã HĐ..." class="form-control" style="padding: 0.45rem 0.75rem 0.45rem 2.1rem; font-size: 0.85rem; border-radius: 6px; width: 100%;" onkeyup="filterContractTable()">
            <div style="position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                <?= icon('search', '', 14) ?>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Mã Hợp Đồng</th>
                    <th>Doanh Nghiệp (Bấm để xem)</th>
                    <th>Tiêu Đề Hợp Đồng</th>
                    <th>Chữ Ký Số Admin</th>
                    <th>Chữ Ký Số Doanh Nghiệp</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody id="contractTableBody">
                <?php if (empty($contracts)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: #64748b;">
                            Chưa có hợp đồng điện tử nào. Hãy tạo hợp đồng mới!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contracts as $c): ?>
                        <tr>
                            <td><strong style="color: #0d9488; font-family: monospace;"><?= htmlspecialchars($c['contract_code']) ?></strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <a href="<?= url('/employers/' . $c['employer_id']) ?>" target="_blank" title="Xem trực tiếp trang doanh nghiệp <?= htmlspecialchars($c['employer_name']) ?>" style="display: block; flex-shrink: 0; text-decoration: none;">
                                        <img src="<?= htmlspecialchars(!empty($c['avatar_url']) ? $c['avatar_url'] : 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=100') ?>" alt="logo" style="width: 38px; height: 38px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                    </a>
                                    <div>
                                        <a href="<?= url('/employers/' . $c['employer_id']) ?>" target="_blank" title="Bấm để chuyển trực tiếp đến trang doanh nghiệp: <?= htmlspecialchars($c['employer_name']) ?>" style="color: #0f172a; font-weight: 700; font-size: 0.93rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" onmouseover="this.style.color='var(--primary)'; this.style.textDecoration='underline';" onmouseout="this.style.color='#0f172a'; this.style.textDecoration='none';">
                                            <span><?= htmlspecialchars($c['employer_name']) ?></span>
                                            <?= icon('external_link', 'text-slate-400', 13) ?>
                                        </a>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                            <span>MST: <code><?= htmlspecialchars($c['business_license_number'] ?? '—') ?></code></span>
                                            <a href="<?= url('/employers/' . $c['employer_id']) ?>" target="_blank" style="color: var(--primary); font-size: 0.74rem; text-decoration: none; font-weight: 600;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
                                                Xem hồ sơ &rarr;
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong style="font-size: 0.9rem; color: #1e293b;"><?= htmlspecialchars($c['contract_title']) ?></strong>
                                <div style="font-size: 0.75rem; color: #64748b;">Hiệu lực: <?= date('d/m/Y', strtotime($c['start_date'])) ?> &rarr; <?= !empty($c['end_date']) ? date('d/m/Y', strtotime($c['end_date'])) : 'Vô thời hạn' ?></div>
                            </td>
                            <td>
                                <?php if ($c['admin_signed']): ?>
                                    <div style="display: flex; align-items: center; gap: 0.3rem; color: #10b981; font-weight: 700; font-size: 0.8rem; white-space: nowrap;">
                                        <?= icon('check_circle', '', 14) ?> <span>Đã Ký Số</span>
                                    </div>
                                    <div style="font-size: 0.7rem; color: #64748b; font-family: monospace; white-space: nowrap;">
                                        <?= date('d/m/Y H:i', strtotime($c['admin_signed_at'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.72rem; white-space: nowrap;">Chưa ký</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($c['employer_signed']): ?>
                                    <div style="display: flex; align-items: center; gap: 0.3rem; color: #10b981; font-weight: 700; font-size: 0.8rem; white-space: nowrap;">
                                        <?= icon('check_circle', '', 14) ?> <span>Đã Ký Số</span>
                                    </div>
                                    <div style="font-size: 0.7rem; color: #64748b; font-family: monospace; white-space: nowrap;">
                                        <?= date('d/m/Y H:i', strtotime($c['employer_signed_at'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.72rem; white-space: nowrap;">Chờ DN ký</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($c['status'] === 'active'): ?>
                                    <span class="badge badge-success"><?= icon('shield_check', '', 13) ?> Có Hiệu Lực</span>
                                <?php elseif ($c['status'] === 'admin_signed'): ?>
                                    <span class="badge badge-primary">Admin Đã Ký</span>
                                <?php elseif ($c['status'] === 'employer_signed'): ?>
                                    <span class="badge badge-warning">DN Đã Ký</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #e2e8f0; color: #475569;">Bản Dự Thảo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                                    <a href="<?= url('/employers/' . $c['employer_id']) ?>" target="_blank" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #0284c7; border-color: #bae6fd; background: #f0f9ff; display: inline-flex; align-items: center; gap: 0.25rem;" title="Chuyển trực tiếp tới trang doanh nghiệp">
                                        <?= icon('building', '', 12) ?> Đến DN
                                    </a>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="previewContract(<?= htmlspecialchars(json_encode($c)) ?>)" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                        <?= icon('file_text', '', 13) ?> Xem HĐ
                                    </button>
                                    <?php if (!$c['admin_signed']): ?>
                                        <form action="<?= url('/admin/contracts/sign/' . $c['contract_id']) ?>" method="POST" style="display:inline;">
                                            <button type="submit" class="btn btn-success btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.55rem; font-weight: 700;">
                                                <?= icon('file_signature', '', 13) ?> Ký Số
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL LẬP HỢP ĐỒNG MỚI -->
<div id="newContractModal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#fff; border-radius:12px; width:95%; max-width:680px; padding:2rem; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); position:relative; max-height:90vh; overflow-y:auto;">
        <button onclick="closeNewContractModal()" style="position:absolute; top:1.2rem; right:1.2rem; border:none; background:transparent; font-size:1.5rem; cursor:pointer; color:#64748b;">&times;</button>
        
        <h3 style="margin-top:0; font-size:1.3rem; color:#0f172a; display:flex; align-items:center; gap:0.5rem;">
            <?= icon('file_signature', 'text-primary', 22) ?> Lập Dự Thảo Hợp Đồng Điện Tử Sàn
        </h3>
        
        <form action="<?= url('/admin/contracts/create') ?>" method="POST" style="margin-top:1.25rem; display:flex; flex-direction:column; gap:1rem;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.35rem;">
                    <label style="font-weight:700; font-size:0.85rem; color:#334155; margin:0;">Chọn Doanh Nghiệp Đối Tác *</label>
                    <a href="#" id="newContractEmpLink" target="_blank" style="display:none; font-size:0.78rem; color:var(--primary); font-weight:700; text-decoration:none;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
                        <?= icon('external_link', '', 12) ?> Xem trang doanh nghiệp này &rarr;
                    </a>
                </div>
                <select name="employer_id" id="newContractEmployerSelect" class="form-control" required style="width:100%; padding:0.6rem; border-radius:6px; border:1px solid #cbd5e1;" onchange="updateNewContractEmpLink(this.value)">
                    <option value="">-- Chọn Doanh Nghiệp --</option>
                    <?php foreach ($employers as $emp): ?>
                        <option value="<?= $emp['employer_id'] ?>">
                            <?= htmlspecialchars($emp['employer_name']) ?> (MST: <?= htmlspecialchars($emp['business_license_number'] ?? 'Chưa có') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="font-weight:700; font-size:0.85rem; color:#334155; margin-bottom:0.35rem; display:block;">Tiêu Đề Hợp Đồng *</label>
                <input type="text" name="contract_title" class="form-control" required value="Hợp Đồng Cung Cấp Dịch Vụ Sàn Giao Dịch Việc Làm Điện Tử JobLink" style="width:100%; padding:0.6rem; border-radius:6px; border:1px solid #cbd5e1;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label style="font-weight:700; font-size:0.85rem; color:#334155; margin-bottom:0.35rem; display:block;">Ngày Bắt Đầu *</label>
                    <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d') ?>" style="width:100%; padding:0.6rem; border-radius:6px; border:1px solid #cbd5e1;">
                </div>
                <div>
                    <label style="font-weight:700; font-size:0.85rem; color:#334155; margin-bottom:0.35rem; display:block;">Ngày Kết Thúc</label>
                    <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 year')) ?>" style="width:100%; padding:0.6rem; border-radius:6px; border:1px solid #cbd5e1;">
                </div>
            </div>

            <div>
                <label style="font-weight:700; font-size:0.85rem; color:#334155; margin-bottom:0.35rem; display:block;">Nội Dung Điều Khoản Hợp Đồng Điện Tử *</label>
                <textarea name="contract_content" rows="6" class="form-control" required style="width:100%; padding:0.6rem; border-radius:6px; border:1px solid #cbd5e1; font-size:0.85rem; line-height:1.5;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM
Độc lập - Tự do - Hạnh phúc

HỢP ĐỒNG ĐIỆN TỬ DỊCH VỤ TUYỂN DỤNG & XÁC THỰC SỐ
Căn cứ Luật Giao dịch Điện tử và Bộ luật Dân sự Việt Nam.

ĐIỀU 1: PHẠM VI HỢP TÁC
Bên A (Sàn JobLink) cung cấp hạ tầng tuyển dụng số hóa, thẩm định chữ ký số và kết nối nguồn lao động chất lượng cho Bên B (Doanh Nghiệp).

ĐIỀU 2: GIÁ TRỊ PHÁP LÝ CỦA CHỮ KÝ SỐ
Hai bên cam kết sử dụng Chữ Ký Số Hợp Lệ (CA) có giá trị chứng thực toàn vẹn theo quy định của pháp luật. Hợp đồng điện tử có giá trị pháp lý tương đương hợp đồng văn bản giấy.</textarea>
            </div>

            <div style="text-align:right; border-top:1px solid #e2e8f0; padding-top:1rem; display:flex; justify-content:flex-end; gap:0.5rem;">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeNewContractModal()">Hủy</button>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight:700;">Tạo & Lưu Dự Thảo</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL XEM CHI TIẾT & CON DẤU CHỮ KÝ SỐ 2 BÊN -->
<div id="contractPreviewModal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#fff; border-radius:12px; width:95%; max-width:780px; padding:2rem; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); position:relative; max-height:90vh; overflow-y:auto;">
        <button onclick="closeContractPreview()" style="position:absolute; top:1.2rem; right:1.2rem; border:none; background:transparent; font-size:1.5rem; cursor:pointer; color:#64748b;">&times;</button>
        
        <div style="text-align:center; margin-bottom:1.25rem;">
            <span style="font-size:0.8rem; font-weight:700; color:#0d9488; text-transform:uppercase; letter-spacing:1px;">CỔNG THÔNG TIN KẾT NỐI VIỆC LÀM JOBLINK</span>
            <h2 style="margin:0.3rem 0; font-size:1.35rem; color:#0f172a;" id="prevTitle">HỢP ĐỒNG ĐIỆN TỬ DỊCH VỤ SÀN</h2>
            <div style="font-size:0.85rem; color:#64748b;">Mã số: <strong id="prevCode" style="color:#0d9488;">HD-2026-JL-0001</strong></div>
        </div>

        <!-- KHU VỰC THÔNG TIN DOANH NGHIỆP KÈM LINK CHUYỂN TRỰC TIẾP -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1.25rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <span style="font-size: 0.78rem; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; display: block;">Doanh Nghiệp Đối Tác:</span>
                <span id="prevEmpName" style="font-size: 1.05rem; font-weight: 800; color: #0f172a;"></span>
            </div>
            <a href="#" id="prevEmpDetailLink" target="_blank" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; font-weight: 700; padding: 0.4rem 0.85rem;">
                <?= icon('building', '', 14) ?> <span>Chuyển Đến Trang Doanh Nghiệp</span> <?= icon('external_link', '', 12) ?>
            </a>
        </div>

        <div id="prevContent" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:1.25rem; font-size:0.88rem; color:#1e293b; white-space:pre-wrap; line-height:1.6; max-height:300px; overflow-y:auto; font-family:inherit;"></div>

        <!-- KHU VỰC 2 CHỮ KÝ SỐ ĐIỆN TỬ -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-top:1.5rem; border-top:2px dashed #cbd5e1; padding-top:1.25rem;">
            <!-- CHỮ KÝ BÊN A (ADMIN ROOT CA) -->
            <div style="border:1px solid #bbf7d0; background:#f0fdf4; border-radius:8px; padding:1rem; text-align:center;">
                <div style="font-size:0.82rem; font-weight:700; color:#166534;">ĐẠI DIỆN SÀN JOBLINK (BÊN A)</div>
                <div id="prevAdminSeal" style="margin-top:0.75rem;">
                    <!-- Render JS -->
                </div>
            </div>

            <!-- CHỮ KÝ BÊN B (DOANH NGHIỆP) -->
            <div style="border:1px solid #bfdbfe; background:#eff6ff; border-radius:8px; padding:1rem; text-align:center;">
                <div style="font-size:0.82rem; font-weight:700; color:#1e40af;">ĐẠI DIỆN DOANH NGHIỆP (BÊN B)</div>
                <div id="prevEmpSeal" style="margin-top:0.75rem;">
                    <!-- Render JS -->
                </div>
            </div>
        </div>

        <div style="margin-top:1.5rem; text-align:right; border-top:1px solid #e2e8f0; padding-top:1rem; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-size:0.75rem; color:#64748b; display:flex; align-items:center; gap:0.35rem;">
                <?= icon('shield_check', '', 14) ?> Chứng thực toàn vẹn bằng SHA-256 Digital Fingerprint
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="closeContractPreview()">Đóng</button>
        </div>
    </div>
</div>

<script>
function filterContractTable() {
    var q = document.getElementById('contractSearchInput').value.toLowerCase().trim();
    var rows = document.querySelectorAll('#contractTableBody tr');
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(q) !== -1 ? '' : 'none';
    });
}

function updateNewContractEmpLink(val) {
    var link = document.getElementById('newContractEmpLink');
    if (val) {
        link.href = '<?= url('/employers/') ?>/' + val;
        link.style.display = 'inline-flex';
        link.style.alignItems = 'center';
        link.style.gap = '0.25rem';
    } else {
        link.style.display = 'none';
    }
}

function showNewContractModal() {
    document.getElementById('newContractModal').style.display = 'flex';
}
function closeNewContractModal() {
    document.getElementById('newContractModal').style.display = 'none';
}

function previewContract(c) {
    document.getElementById('prevTitle').textContent = c.contract_title;
    document.getElementById('prevCode').textContent = c.contract_code;
    document.getElementById('prevContent').textContent = c.contract_content;

    // Cập nhật thông tin và link Doanh nghiệp đối tác
    document.getElementById('prevEmpName').textContent = c.employer_name;
    var empDetailLink = document.getElementById('prevEmpDetailLink');
    empDetailLink.href = '<?= url('/employers/') ?>/' + c.employer_id;
    empDetailLink.title = 'Chuyển trực tiếp tới trang: ' + c.employer_name;

    // Con dấu chữ ký số Admin
    if (c.admin_signed) {
        document.getElementById('prevAdminSeal').innerHTML = `
            <div style="border:2px solid #16a34a; border-radius:6px; padding:8px; background:#fff; display:inline-block; text-align:left;">
                <div style="color:#16a34a; font-weight:800; font-size:0.8rem; display:flex; align-items:center; gap:4px;"><svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;display:inline-block;"><polyline points="20 6 9 17 4 12"></polyline></svg> KÝ SỐ BỞI: ${c.admin_signer_name || 'Ban Quản Trị JobLink'}</div>
                <div style="font-size:0.7rem; color:#475569;">Tổ chức: JobLink Platform Root CA</div>
                <div style="font-size:0.7rem; color:#475569;">Thời gian: ${c.admin_signed_at || ''}</div>
                <div style="font-size:0.65rem; color:#16a34a; font-family:monospace; word-break:break-all;">Hash: ${(c.admin_signature_hash || '').substring(0,24)}...</div>
            </div>
        `;
    } else {
        document.getElementById('prevAdminSeal').innerHTML = `<span style="color:#94a3b8; font-size:0.8rem; font-style:italic;">Chưa ký số</span>`;
    }

    // Con dấu chữ ký số Doanh nghiệp
    if (c.employer_signed) {
        document.getElementById('prevEmpSeal').innerHTML = `
            <div style="border:2px solid #2563eb; border-radius:6px; padding:8px; background:#fff; display:inline-block; text-align:left;">
                <div style="color:#2563eb; font-weight:800; font-size:0.8rem; display:flex; align-items:center; gap:4px;">
                    <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;display:inline-block;"><polyline points="20 6 9 17 4 12"></polyline></svg> 
                    KÝ SỐ BỞI: <a href="<?= url('/employers/') ?>/${c.employer_id}" target="_blank" style="color:#2563eb; text-decoration:underline; font-weight:800;" title="Chuyển đến trang ${c.employer_name}">${c.employer_signer_name || c.employer_name}</a>
                </div>
                <div style="font-size:0.7rem; color:#475569;">Chứng thư: VNPT-CA / Viettel-CA</div>
                <div style="font-size:0.7rem; color:#475569;">Thời gian: ${c.employer_signed_at || ''}</div>
                <div style="font-size:0.65rem; color:#2563eb; font-family:monospace; word-break:break-all;">Hash: ${(c.employer_signature_hash || '').substring(0,24)}...</div>
            </div>
        `;
    } else {
        document.getElementById('prevEmpSeal').innerHTML = `<span style="color:#94a3b8; font-size:0.8rem; font-style:italic;">Doanh nghiệp chưa ký</span>`;
    }

    document.getElementById('contractPreviewModal').style.display = 'flex';
}
function closeContractPreview() {
    document.getElementById('contractPreviewModal').style.display = 'none';
}
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php';
?>
