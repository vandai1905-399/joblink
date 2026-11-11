<?php 
ob_start(); 

// Đảm bảo dữ liệu danh sách hợp đồng & doanh nghiệp an toàn
$contracts = $contracts ?? [];
$employers = $employers ?? [];

// Nhóm danh sách hợp đồng theo từng doanh nghiệp
$contractsByEmployer = [];
foreach ($contracts as $c) {
    $contractsByEmployer[$c['employer_id']][] = $c;
}

// Chuẩn bị dữ liệu đầy đủ cho JavaScript
$employersJsonData = [];
foreach ($employers as $emp) {
    $eid = $emp['employer_id'];
    $empContracts = $contractsByEmployer[$eid] ?? [];
    $employersJsonData[$eid] = [
        'employer_id'               => $eid,
        'employer_name'             => $emp['employer_name'] ?? 'Doanh Nghiệp ' . $eid,
        'avatar_url'                => getAvatarUrl($emp['avatar_url'] ?? '', 'EMPLOYER'),
        'business_license_number'   => $emp['business_license_number'] ?? 'Chưa cập nhật',
        'representative_name'       => $emp['representative_name'] ?? 'Chưa cập nhật',
        'contact_email'             => $emp['contact_email'] ?? '—',
        'phone_number'              => $emp['phone_number'] ?? '—',
        'address'                   => $emp['address'] ?? 'Chưa cập nhật địa chỉ',
        'employer_type'             => $emp['employer_type'] ?? 'Doanh nghiệp',
        'business_field'            => $emp['business_field'] ?? 'Tuyển dụng',
        'verification_status'       => $emp['verification_status'] ?? 'pending',
        'digital_signature_status'  => $emp['digital_signature_status'] ?? 'unverified',
        'digital_cert_ca'           => $emp['digital_cert_ca'] ?? 'VNPT-CA',
        'digital_cert_serial'       => $emp['digital_cert_serial'] ?? 'VNPT-CA-001',
        'digital_cert_fp'           => $emp['digital_cert_fp'] ?? '',
        'contracts_count'           => count($empContracts),
        'contracts'                 => $empContracts,
        'profile_url'               => url('/employers/' . $eid),
    ];
}
?>

<style>
/* ==================================================================== */
/* CÁC THẺ NHỎ HIỆN NGẮN GỌN THÔNG TIN (COMPACT CHIPS)                  */
/* ==================================================================== */
.compact-chips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
    gap: 0.65rem;
    max-height: 240px;
    overflow-y: auto;
    padding-right: 4px;
}
.compact-chips-grid::-webkit-scrollbar {
    width: 4px;
}
.compact-chips-grid::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.compact-company-chip {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.55rem 0.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.6rem;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.compact-company-chip:hover {
    border-color: #0d9488;
    background: #f0fdfa;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(13, 148, 136, 0.08);
}
.compact-company-chip.active-chip {
    border-color: #0d9488;
    background: #e6fffa;
    box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.25);
}

.chip-left {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    min-width: 0;
    flex: 1;
}
.chip-logo {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
}
.chip-body {
    min-width: 0;
    overflow: hidden;
}
.chip-name {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.chip-meta {
    font-size: 0.72rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chip-right {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-shrink: 0;
}

/* Thẻ panel nội dung */
.chrome-tab-panel {
    display: none;
    animation: fadeInTab 0.18s ease-in-out;
}
.chrome-tab-panel.active {
    display: block;
}
@keyframes fadeInTab {
    from { opacity: 0; transform: translateY(2px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Thanh mini KPI gọn gàng (không chiếm diện tích) */
.mini-kpi-bar {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}
.mini-kpi-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.65rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #334155;
}
.mini-kpi-pill strong {
    font-size: 0.9rem;
}
</style>

<!-- ========================================================================================= -->
<!-- KHU VỰC CÁC PANELS NỘI DUNG (TAB SHELL DO MASTER WORKSPACE QUẢN LÝ)                       -->
<!-- ========================================================================================= -->
<div id="chromePanelsContainer">

    <!-- PANEL 0: TRANG GỐC (BẢNG HỢP ĐỒNG TỔNG HỢP & CÁC THẺ DOANH NGHIỆP NHỎ GỌN) -->
    <div class="chrome-tab-panel active" id="cpanel-root">

        <!-- HEADER NHỎ GỌN & CỤM NÚT THAO TÁC -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                    <?= icon('file_signature', 'text-primary', 20) ?> Hợp Đồng Điện Tử & Ký Số Doanh Nghiệp
                </h1>
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.15rem;">
                    Quản lý hợp đồng dịch vụ sàn, xác thực chữ ký số và thiết lập thỏa thuận với doanh nghiệp.
                </div>
            </div>

            <!-- CỤM THAO TÁC & MINI KPI -->
            <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                <button type="button" class="btn btn-outline btn-sm" onclick="location.reload()" title="Tải lại nội dung trang hợp đồng" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                    <?= icon('refresh', '', 12) ?> <span>Tải lại</span>
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="showNewContractModal()" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <?= icon('plus', '', 12) ?> <span>+ Lập HĐ Mới</span>
                </button>
                <div class="mini-kpi-bar">
                    <div class="mini-kpi-pill" style="border-left: 3px solid #10b981;">
                        <span style="color: #10b981;">●</span> Hiệu lực: <strong style="color: #10b981;"><?= count(array_filter($contracts, fn($c) => $c['status'] === 'active')) ?></strong>
                    </div>
                    <div class="mini-kpi-pill" style="border-left: 3px solid #3b82f6;">
                        <span style="color: #3b82f6;">●</span> Chờ DN ký: <strong style="color: #3b82f6;"><?= count(array_filter($contracts, fn($c) => $c['status'] === 'admin_signed')) ?></strong>
                    </div>
                    <div class="mini-kpi-pill" style="border-left: 3px solid #f59e0b;">
                        <span style="color: #f59e0b;">●</span> Chờ Admin ký: <strong style="color: #f59e0b;"><?= count(array_filter($contracts, fn($c) => $c['status'] === 'draft' || $c['status'] === 'employer_signed')) ?></strong>
                    </div>
                    <div class="mini-kpi-pill" style="border-left: 3px solid #6366f1;">
                        <span style="color: #6366f1;">●</span> Tổng DN: <strong style="color: #6366f1;"><?= count($employers) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- DANH MỤC CÁC THẺ DOANH NGHIỆP NHỎ GỌN (CHROME-STYLE CHIPS) -->
        <div class="card" style="margin-bottom: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; padding: 0.9rem 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem; flex-wrap: wrap; gap: 0.5rem;">
                <div style="font-size: 0.88rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 0.35rem;">
                    <?= icon('bookmark', 'text-primary', 16) ?> <span>Danh Mục Doanh Nghiệp (<?= count($employers) ?>):</span>
                </div>
                <div style="position: relative; min-width: 240px;">
                    <input type="text" id="titleSearchInput" placeholder="Tìm nhanh doanh nghiệp, MST..." class="form-control" style="padding: 0.35rem 0.65rem 0.35rem 1.9rem; font-size: 0.82rem; border-radius: 6px; width: 100%;" onkeyup="filterTitleCards()">
                    <div style="position: absolute; left: 0.6rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                        <?= icon('search', '', 13) ?>
                    </div>
                </div>
            </div>

            <!-- GRID CÁC THẺ NHỎ NGẮN GỌN THÔNG TIN -->
            <div class="compact-chips-grid" id="titlesGridContainer">
                <?php foreach ($employers as $emp): 
                    $eid = $emp['employer_id'];
                    $empContracts = $contractsByEmployer[$eid] ?? [];
                    $hasCA = ($emp['digital_signature_status'] ?? '') === 'verified';
                ?>
                    <div class="compact-company-chip" id="chip-card-<?= $eid ?>" onclick="openEmployerInDashboard(<?= $eid ?>)" title="Xem thông tin <?= htmlspecialchars($emp['employer_name']) ?>">
                        <div class="chip-left">
                            <img src="<?= htmlspecialchars(getAvatarUrl($emp['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" class="chip-logo" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                            <div class="chip-body">
                                <div class="chip-name" title="<?= htmlspecialchars($emp['employer_name']) ?>"><?= htmlspecialchars($emp['employer_name']) ?></div>
                                <div class="chip-meta">MST: <?= htmlspecialchars($emp['business_license_number'] ?? 'Chưa nộp') ?></div>
                            </div>
                        </div>
                        <div class="chip-right">
                            <?php if (count($empContracts) > 0): ?>
                                <span class="badge badge-success" style="font-size: 0.68rem; padding: 0.15rem 0.4rem; white-space: nowrap;"><?= count($empContracts) ?> HĐ</span>
                            <?php else: ?>
                                <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.68rem; padding: 0.15rem 0.4rem; white-space: nowrap;">Chưa có</span>
                            <?php endif; ?>
                            <?php if ($hasCA): ?>
                                <span class="badge badge-primary" style="font-size: 0.68rem; padding: 0.15rem 0.4rem; white-space: nowrap;"><?= icon('certificate', '', 10) ?> Ký Số</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card" style="border: 1px solid #e2e8f0; padding: 1rem 1.1rem;">
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
                    <th style="white-space: nowrap;">Mã Hợp Đồng</th>
                    <th>Doanh Nghiệp (Bấm để xem)</th>
                    <th>Tiêu Đề Hợp Đồng</th>
                    <th style="white-space: nowrap;">Chữ Ký Số Admin</th>
                    <th style="white-space: nowrap;">Chữ Ký Số Doanh Nghiệp</th>
                    <th style="white-space: nowrap; width: 130px; text-align: center;">Trạng Thái</th>
                    <th style="white-space: nowrap; width: 160px; text-align: center;">Thao Tác</th>
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
                                <div style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;" onclick="openEmployerInDashboard(<?= $c['employer_id'] ?>)" title="Xem thông tin <?= htmlspecialchars($c['employer_name']) ?>">
                                    <img src="<?= htmlspecialchars(getAvatarUrl($c['avatar_url'] ?? '', 'EMPLOYER')) ?>" alt="logo" style="width: 38px; height: 38px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                                    <div>
                                        <div style="color: #0f172a; font-weight: 700; font-size: 0.93rem; display: inline-flex; align-items: center; gap: 0.35rem;" onmouseover="this.style.color='var(--primary)';" onmouseout="this.style.color='#0f172a';">
                                            <span><?= htmlspecialchars($c['employer_name']) ?></span>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                            <span>MST: <code><?= htmlspecialchars($c['business_license_number'] ?? '—') ?></code></span>
                                            <span style="color: var(--primary); font-size: 0.74rem; font-weight: 600;">Xem chi tiết</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong style="font-size: 0.9rem; color: #1e293b;"><?= htmlspecialchars($c['contract_title']) ?></strong>
                                <div style="font-size: 0.75rem; color: #64748b;">Hiệu lực: <?= date('d/m/Y', strtotime($c['start_date'])) ?> - <?= !empty($c['end_date']) ? date('d/m/Y', strtotime($c['end_date'])) : 'Vô thời hạn' ?></div>
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

                            <td style="white-space: nowrap; text-align: center;">
                                <?php if ($c['status'] === 'active'): ?>
                                    <span class="badge badge-success" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('shield_check', '', 13) ?> Có Hiệu Lực</span>
                                <?php elseif ($c['status'] === 'admin_signed'): ?>
                                    <span class="badge badge-primary" style="white-space: nowrap;">Admin Đã Ký</span>
                                <?php elseif ($c['status'] === 'employer_signed'): ?>
                                    <span class="badge badge-warning" style="white-space: nowrap;">DN Đã Ký</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #e2e8f0; color: #475569; white-space: nowrap;">Bản Dự Thảo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                                    <button type="button" class="btn btn-sm" onclick="openEmployerInDashboard(<?= $c['employer_id'] ?>)" style="font-size: 0.75rem; padding: 0.25rem 0.55rem; color: #ffffff; border-color: #0d9488; background: #0d9488; display: inline-flex; align-items: center; gap: 0.25rem; white-space: nowrap;" title="Mở tab hiển thị thông tin doanh nghiệp">
                                        <?= icon('building', '', 12) ?> Xem DN
                                    </button>
                                    <button type="button" class="btn btn-sm" onclick="previewContract(<?= htmlspecialchars(json_encode($c)) ?>)" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; white-space: nowrap; background: #475569; color: #ffffff; border: 1px solid #475569;">
                                        <?= icon('file_text', '', 13) ?> Xem HĐ
                                    </button>
                                    <?php if (!$c['admin_signed']): ?>
                                        <form action="<?= url('/admin/contracts/sign/' . $c['contract_id']) ?>" method="POST" style="display:inline;">
                                            <button type="submit" class="btn btn-success btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.55rem; font-weight: 700; white-space: nowrap;">
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
</div> <!-- /cpanel-root -->

<!-- KHU VỰC CHỨA CÁC PANEL TAB DOANH NGHIỆP ĐƯỢC MỞ ĐỘNG (Rendered via JS) -->
<div id="dynamicChromePanelsContainer"></div>

</div> <!-- /chromePanelsContainer -->

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
                        <?= icon('external_link', '', 12) ?> Xem trang doanh nghiệp này
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
Hai bên cam kết sử dụng Chữ Ký Số Hợp Lệ có giá trị chứng thực toàn vẹn theo quy định của pháp luật. Hợp đồng điện tử có giá trị pháp lý tương đương hợp đồng văn bản giấy.</textarea>
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
// Dữ liệu doanh nghiệp và hợp đồng được chuẩn bị sẵn
const ALL_EMPLOYERS_DATA = <?= json_encode($employersJsonData, JSON_UNESCAPED_UNICODE) ?>;
const SIGN_CONTRACT_URL_BASE = '<?= url('/admin/contracts/sign/') ?>';
const APP_BASE_URL = '<?= url('') ?>';

// Trạng thái hiện tại
let currentActiveView = 'root';

/**
 * 1. Hiển thị chi tiết hợp đồng & hồ sơ của Doanh Nghiệp (Không tạo tab con thừa)
 */
function showEmployerDetail(empId) {
    empId = String(empId);
    const emp = ALL_EMPLOYERS_DATA[empId];
    if (!emp) {
        alert('Không tìm thấy thông tin doanh nghiệp!');
        return;
    }

    const tabKey = 'emp-' + empId;

    // Nếu panel chi tiết của doanh nghiệp này chưa được dựng, tạo mới
    if (!document.getElementById('cpanel-' + tabKey)) {
        createEmployerPanel(tabKey, emp);
    }

    currentActiveView = tabKey;

    // Ẩn tất cả panel, hiển thị panel của doanh nghiệp này
    document.querySelectorAll('.chrome-tab-panel').forEach(p => p.classList.remove('active'));
    const targetPanel = document.getElementById('cpanel-' + tabKey);
    if (targetPanel) targetPanel.classList.add('active');

    // Highlight thẻ chip tương ứng
    document.querySelectorAll('.compact-company-chip').forEach(el => el.classList.remove('active-chip'));
    const chip = document.getElementById('chip-card-' + empId);
    if (chip) chip.classList.add('active-chip');

    // Cuộn mượt lên đầu
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * 2. Quay lại danh sách tổng hợp tất cả hợp đồng
 */
function showAllContracts() {
    currentActiveView = 'root';

    // Ẩn tất cả panel chi tiết, hiện panel gốc
    document.querySelectorAll('.chrome-tab-panel').forEach(p => p.classList.remove('active'));
    const rootPanel = document.getElementById('cpanel-root');
    if (rootPanel) rootPanel.classList.add('active');

    // Bỏ highlight chip
    document.querySelectorAll('.compact-company-chip').forEach(el => el.classList.remove('active-chip'));

    // Cuộn mượt lên đầu
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * 3. Mở tab ngay tại Dashboard hiển thị toàn bộ thông tin chi tiết của Doanh Nghiệp
 */
function openEmployerInDashboard(empId) {
    empId = String(empId);
    const emp = ALL_EMPLOYERS_DATA[empId];
    const title = emp ? emp.employer_name : 'Doanh Nghiệp #' + empId;
    const url = '<?= url('/employers/') ?>' + empId;
    const iconSvg = '<svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';

    if (window.parent && window.parent !== window && window.parent.MasterWorkspace) {
        window.parent.MasterWorkspace.openTab(url, title, iconSvg);
    } else if (window.MasterWorkspace) {
        window.MasterWorkspace.openTab(url, title, iconSvg);
    } else {
        window.location.href = url;
    }
}

function openEmployerInMasterTab(empId) {
    openEmployerInDashboard(empId);
}

/**
 * 4. Tạo Panel nội dung chi tiết cho Doanh Nghiệp
 */
function createEmployerPanel(tabKey, emp) {
    const container = document.getElementById('dynamicChromePanelsContainer');
    const panel = document.createElement('div');
    panel.className = 'chrome-tab-panel';
    panel.id = 'cpanel-' + tabKey;

    let contractsHtml = '';
    if (emp.contracts && emp.contracts.length > 0) {
        contractsHtml = `
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th>Mã Hợp Đồng</th>
                            <th>Tiêu Đề Hợp Đồng</th>
                            <th>Hiệu Lực</th>
                            <th>Admin Ký</th>
                            <th>DN Ký</th>
                            <th>Trạng Thái</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        function formatContractDateTime(dtStr) {
            if (!dtStr || dtStr === '0000-00-00' || dtStr === '0000-00-00 00:00:00') return '—';
            const parts = dtStr.split(/[- :]/);
            if (parts.length >= 3) {
                const d = parts[2].padStart(2, '0');
                const m = parts[1].padStart(2, '0');
                const y = parts[0];
                if (parts.length >= 5) {
                    return `${d}/${m}/${y} ${parts[3]}:${parts[4]}`;
                }
                return `${d}/${m}/${y}`;
            }
            return dtStr;
        }

        function formatContractDateOnly(dtStr) {
            if (!dtStr || dtStr === '0000-00-00') return '';
            const parts = dtStr.split(/[- :]/);
            if (parts.length >= 3) {
                const d = parts[2].padStart(2, '0');
                const m = parts[1].padStart(2, '0');
                const y = parts[0];
                return `${d}/${m}/${y}`;
            }
            return dtStr;
        }

        emp.contracts.forEach(c => {
            const adminSignedBadge = c.admin_signed 
                ? `<span style="color: #10b981; font-weight:700; font-size:0.8rem; display:inline-flex; align-items:center; gap:3px;"><svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Đã Ký</span><div style="font-size:0.7rem; color:#64748b;">${formatContractDateTime(c.admin_signed_at)}</div>`
                : `<span class="badge badge-warning" style="font-size:0.72rem;">Chưa ký</span>`;

            const empSignedBadge = c.employer_signed
                ? `<span style="color: #10b981; font-weight:700; font-size:0.8rem; display:inline-flex; align-items:center; gap:3px;"><svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Đã Ký</span><div style="font-size:0.7rem; color:#64748b;">${formatContractDateTime(c.employer_signed_at)}</div>`
                : `<span class="badge" style="background:#f1f5f9; color:#64748b; font-size:0.72rem;">Chờ DN ký</span>`;

            let statusBadge = `<span class="badge" style="background:#e2e8f0; color:#475569; white-space:nowrap;">Bản Dự Thảo</span>`;
            if (c.status === 'active') statusBadge = `<span class="badge badge-success" style="white-space:nowrap;">Có Hiệu Lực</span>`;
            else if (c.status === 'admin_signed') statusBadge = `<span class="badge badge-primary" style="white-space:nowrap;">Admin Đã Ký</span>`;
            else if (c.status === 'employer_signed') statusBadge = `<span class="badge badge-warning" style="white-space:nowrap;">DN Đã Ký</span>`;

            const jsonC = JSON.stringify(c).replace(/"/g, '&quot;');

            let signBtn = '';
            if (!c.admin_signed) {
                signBtn = `
                    <form action="${SIGN_CONTRACT_URL_BASE}/${c.contract_id}" method="POST" style="display:inline;">
                        <button type="submit" class="btn btn-success btn-sm" style="font-size:0.75rem; padding:0.25rem 0.55rem; font-weight:700;">
                            Ký Số
                        </button>
                    </form>
                `;
            }

            const rangeDates = `${formatContractDateOnly(c.start_date) || '—'} - ${formatContractDateOnly(c.end_date) || 'Vô thời hạn'}`;

            contractsHtml += `
                <tr>
                    <td><strong style="color: #0d9488; font-family: monospace;">${c.contract_code}</strong></td>
                    <td><strong>${c.contract_title}</strong></td>
                    <td style="font-size:0.78rem; color:#475569;">${rangeDates}</td>
                    <td>${adminSignedBadge}</td>
                    <td>${empSignedBadge}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div style="display:flex; gap:0.35rem; align-items:center;">
                            <button type="button" class="btn btn-sm" onclick="previewContract(${jsonC})" style="font-size:0.75rem; padding:0.25rem 0.5rem; background: #475569; color: #ffffff; border: 1px solid #475569;">
                                Xem HĐ
                            </button>
                            ${signBtn}
                        </div>
                    </td>
                </tr>
            `;
        });

        contractsHtml += `</tbody></table></div>`;
    } else {
        contractsHtml = `
            <div style="text-align: center; padding: 2rem; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 1rem 0;">
                    Doanh nghiệp <strong>${emp.employer_name}</strong> chưa có hợp đồng điện tử nào với Sàn JobLink.
                </p>
                <button type="button" class="btn btn-primary btn-sm" onclick="showNewContractModal(${emp.employer_id})" style="font-weight:700;">
                    + Lập Dự Thảo Hợp Đồng Cho Doanh Nghiệp Này
                </button>
            </div>
        `;
    }

    panel.innerHTML = `
        <div class="card" style="border: 1px solid #e2e8f0; padding: 1.5rem; background: #ffffff;">
            <!-- THANH BREADCRUMB / QUAY LẠI TRÊN ĐẦU CHI TIẾT DOANH NGHIỆP -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.85rem; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; gap: 0.5rem;">
                <button type="button" class="btn btn-outline btn-sm" onclick="showAllContracts()" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    <span>Quay lại danh sách tất cả Hợp Đồng</span>
                </button>
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="openEmployerInDashboard(${emp.employer_id})" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;" title="Mở tab hiển thị thông tin doanh nghiệp">
                        <?= icon('building', '', 13) ?>
                        <span>Xem Trang Doanh Nghiệp</span>
                    </button>
                    <a href="${emp.profile_url}" target="_blank" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                        <span>Trang DN Ngoài Sàn</span>
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="showNewContractModal(${emp.employer_id})" style="font-weight: 700;">
                        + Lập HĐ Mới Cho DN Này
                    </button>
                </div>
            </div>

            <!-- HEADER THÔNG TIN DOANH NGHIỆP -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <img src="${emp.avatar_url}" alt="logo" style="width: 54px; height: 54px; border-radius: 10px; object-fit: cover; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);" onerror="this.onerror=null; this.src='<?= getAvatarUrl('', 'EMPLOYER') ?>';">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <h2 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">
                                ${emp.employer_name}
                            </h2>
                            ${emp.digital_signature_status === 'verified' 
                                ? `<span class="badge badge-success"><svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="7" y1="8" x2="17" y2="8"></line><line x1="7" y1="12" x2="17" y2="12"></line><polyline points="7 16 9 16 11 16"></polyline><circle cx="15" cy="16" r="2"></circle></svg> Đã Xác Thực Chữ Ký Số</span>` 
                                : `<span class="badge badge-warning">Chưa thẩm định chữ ký số</span>`}
                        </div>
                        <div style="font-size: 0.82rem; color: #64748b; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            <span>Mã DN: <strong>#DN-${emp.employer_id}</strong></span>
                            <span>&bull; MST: <strong>${emp.business_license_number}</strong></span>
                            <span>&bull; Lĩnh vực: <strong>${emp.business_field}</strong></span>
                            <span>&bull; Người đại diện: <strong>${emp.representative_name}</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- THÔNG TIN PHÁP LÝ & CHỨNG THƯ SỐ CA -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 0.5rem;">
                        Thông Tin Liên Hệ & Trụ Sở:
                    </div>
                    <div style="font-size: 0.85rem; line-height: 1.6; color: #1e293b;">
                        <div><strong>Hotline:</strong> ${emp.phone_number}</div>
                        <div><strong>Email:</strong> ${emp.contact_email}</div>
                        <div><strong>Trụ sở chính:</strong> ${emp.address}</div>
                    </div>
                </div>

                <div style="background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 8px; padding: 1rem;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #0d9488; text-transform: uppercase; margin-bottom: 0.5rem;">
                        Chứng Thư Chữ Ký Số Doanh Nghiệp:
                    </div>
                    <div style="font-size: 0.82rem; line-height: 1.6; color: #0f172a;">
                        <div><strong>Nhà cung cấp:</strong> ${emp.digital_cert_ca}</div>
                        <div><strong>Mã Serial:</strong> <code style="font-size: 0.75rem;">${emp.digital_cert_serial}</code></div>
                        <div style="word-break: break-all;"><strong>SHA-256 Fingerprint:</strong> <code style="font-size: 0.72rem; color: #0d9488;">${emp.digital_cert_fp ? emp.digital_cert_fp.substring(0, 32) + '...' : 'Đã tích hợp chuẩn SHA-256'}</code></div>
                    </div>
                </div>
            </div>

            <!-- DANH SÁCH HỢP ĐỒNG CỦA RIÊNG DOANH NGHIỆP NÀY -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                        <svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        <span>Hợp Đồng Điện Tử Giữa Doanh Nghiệp & Sàn JobLink (${emp.contracts_count})</span>
                    </h3>
                </div>
                ${contractsHtml}
            </div>
        </div>
    `;

    container.appendChild(panel);
}

// Các hàm tương thích ngược (aliases)
function openEmployerChromeTab(empId) { openEmployerInDashboard(empId); }
function switchChromeTab(tabKey) {
    if (tabKey === 'root') showAllContracts();
    else if (tabKey.startsWith('emp-')) showEmployerDetail(tabKey.replace('emp-', ''));
}
function closeEmployerChromeTab(tabKey) { showAllContracts(); }
function closeAllChromeTabs() { showAllContracts(); }
function createChromePanel(tabKey, emp) { createEmployerPanel(tabKey, emp); }

<?php if (!empty($_GET['emp_id'])): ?>
document.addEventListener('DOMContentLoaded', () => {
    showEmployerDetail(<?= (int)$_GET['emp_id'] ?>);
});
<?php endif; ?>

/**
 * 7. Lọc các thẻ tiêu đề doanh nghiệp (chips)
 */
function filterTitleCards() {
    const q = document.getElementById('titleSearchInput').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.compact-company-chip');
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.indexOf(q) !== -1 ? 'flex' : 'none';
    });
}

/**
 * 8. Lọc bảng hợp đồng tổng hợp
 */
function filterContractTable() {
    const q = document.getElementById('contractSearchInput').value.toLowerCase().trim();
    const rows = document.querySelectorAll('#contractTableBody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(q) !== -1 ? '' : 'none';
    });
}

/**
 * 9. Điều khiển Modal Lập Hợp Đồng Mới
 */
function showNewContractModal(preSelectedEmployerId = null) {
    const select = document.getElementById('newContractEmployerSelect');
    if (preSelectedEmployerId && select) {
        select.value = preSelectedEmployerId;
        updateNewContractEmpLink(preSelectedEmployerId);
    }
    document.getElementById('newContractModal').style.display = 'flex';
}
function closeNewContractModal() {
    document.getElementById('newContractModal').style.display = 'none';
}

function updateNewContractEmpLink(val) {
    const link = document.getElementById('newContractEmpLink');
    if (val) {
        link.href = APP_BASE_URL + '/employers/' + val;
        link.style.display = 'inline-flex';
        link.style.alignItems = 'center';
        link.style.gap = '0.25rem';
    } else {
        link.style.display = 'none';
    }
}

/**
 * 10. Preview Hợp Đồng & Con Dấu Ký Số
 */
function previewContract(c) {
    document.getElementById('prevTitle').textContent = c.contract_title;
    document.getElementById('prevCode').textContent = c.contract_code;
    document.getElementById('prevContent').textContent = c.contract_content;

    document.getElementById('prevEmpName').textContent = c.employer_name;
    const empDetailLink = document.getElementById('prevEmpDetailLink');
    empDetailLink.href = APP_BASE_URL + '/employers/' + c.employer_id;
    empDetailLink.title = 'Xem trang: ' + c.employer_name;

    // Con dấu chữ ký số Admin
    if (c.admin_signed) {
        document.getElementById('prevAdminSeal').innerHTML = `
            <div style="border:2px solid #16a34a; border-radius:6px; padding:8px; background:#fff; display:inline-block; text-align:left;">
                <div style="color:#16a34a; font-weight:800; font-size:0.8rem; display:flex; align-items:center; gap:4px;"><svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;display:inline-block;"><polyline points="20 6 9 17 4 12"></polyline></svg> KÝ SỐ BỞI: ${c.admin_signer_name || 'Ban Quản Trị JobLink'}</div>
                <div style="font-size:0.7rem; color:#475569;">Tổ chức: JobLink Platform Root CA</div>
                <div style="font-size:0.7rem; color:#475569;">Thời gian: ${formatContractDateTime(c.admin_signed_at)}</div>
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
                    KÝ SỐ BỞI: <a href="${APP_BASE_URL}/employers/${c.employer_id}" target="_blank" style="color:#2563eb; text-decoration:underline; font-weight:800;" title="Chuyển đến trang ${c.employer_name}">${c.employer_signer_name || c.employer_name}</a>
                </div>
                <div style="font-size:0.7rem; color:#475569;">Chứng thư: VNPT-CA / Viettel-CA</div>
                <div style="font-size:0.7rem; color:#475569;">Thời gian: ${formatContractDateTime(c.employer_signed_at)}</div>
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
