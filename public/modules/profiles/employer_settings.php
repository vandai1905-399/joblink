<?php ob_start(); 
$activeTab = $_GET['tab'] ?? 'cert';
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 0.5rem;">
        <?= icon('settings', 'text-primary', 26) ?> Thiết Lập Doanh Nghiệp, Chữ Ký Số & Hợp Đồng
    </h1>
    <p style="color: var(--slate-500); font-size: 0.95rem; margin-top: 0.25rem;">
        Quản lý chữ ký số, ký kết hợp đồng điện tử với Sàn JobLink, phân quyền ủy quyền người trung gian và cài đặt thông báo Gmail.
    </p>
</div>

<!-- TAB NAVIGATION -->
<div style="display: flex; gap: 0.5rem; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.75rem; flex-wrap: wrap;">
    <a href="?tab=cert" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'cert' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'cert' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'cert' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('certificate', '', 16) ?> <span>1. Chữ Ký Số Doanh Nghiệp</span>
    </a>
    <a href="?tab=contracts" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'contracts' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'contracts' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'contracts' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('file_signature', '', 16) ?> <span>2. Hợp Đồng Điện Tử Với Sàn (<?= count($contracts) ?>)</span>
    </a>
    <a href="?tab=auth" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'auth' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'auth' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'auth' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('handshake', '', 16) ?> <span>3. Ủy Quyền Người Trung Gian (<?= count($authorizations) ?>)</span>
    </a>
    <a href="?tab=mail" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'mail' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'mail' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'mail' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('mail', '', 16) ?> <span>4. Thông Báo Gmail Tuyển Dụng</span>
    </a>
    <a href="?tab=history" class="btn" style="border-radius: 8px 8px 0 0; font-weight: 700; padding: 0.75rem 1.25rem; text-decoration: none; border: none; border-bottom: 3px solid <?= $activeTab === 'history' ? '#0d9488' : 'transparent' ?>; background: <?= $activeTab === 'history' ? '#f0fdfa' : 'transparent' ?>; color: <?= $activeTab === 'history' ? '#0d9488' : '#64748b' ?>;">
        <?= icon('clock', '', 16) ?> <span>5. Lịch Sử Thay Đổi (<?= count($historyLogs ?? []) ?>)</span>
    </a>
</div>

<!-- TAB 1: CHỮ KÝ SỐ DOANH NGHIỆP -->
<?php if ($activeTab === 'cert'): ?>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
        <!-- FORM ĐĂNG KÝ / CẬP NHẬT CHỨNG THƯ SỐ -->
        <div class="card">
            <div style="margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
                <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a;">Đăng Ký / Cập Nhật Chữ Ký Số</h3>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.82rem; color: #64748b;">
                    Hỗ trợ tất cả các nhà cung cấp chữ ký số hợp chuẩn tại Việt Nam (VNPT, Viettel, FPT, BKAV, MISA...)
                </p>
            </div>

            <form action="<?= url('/employer/digital-signature/register') ?>" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Tên Doanh Nghiệp Theo GPKD *</label>
                    <input type="text" name="certificate_owner" class="form-control" required value="<?= htmlspecialchars($certificate['certificate_owner'] ?? $employer['employer_name']) ?>" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>

                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Mã Số Thuế Doanh Nghiệp *</label>
                    <input type="text" name="tax_code" class="form-control" required value="<?= htmlspecialchars($certificate['tax_code'] ?? ($employer['business_license_number'] ?? '')) ?>" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>

                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Nhà Cung Cấp Chữ Ký Số *</label>
                    <select name="issuer_ca" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                        <?php foreach (DigitalSignatureService::SUPPORTED_CAS as $k => $caName): ?>
                            <option value="<?= $k ?>" <?= ($certificate['issuer_ca'] ?? 'VNPT-CA') === $k ? 'selected' : '' ?>>
                                <?= htmlspecialchars($caName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Số Serial Chứng Thư Số / USB Token (Nếu có)</label>
                    <input type="text" name="serial_number" class="form-control" placeholder="Tự động khởi tạo nếu để trống..." value="<?= htmlspecialchars($certificate['serial_number'] ?? '') ?>" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1; font-family: monospace;">
                </div>

                <div style="text-align: right; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                        <?= icon('certificate', '', 15) ?> Lưu & Xác Thực Chữ Ký Số
                    </button>
                </div>
            </form>
        </div>

        <!-- THẺ THÔNG TIN TRẠNG THÁI CHỮ KÝ SỐ HIỆN TẠI -->
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="card" style="border: 2px solid <?= !empty($certificate) ? '#10b981' : '#cbd5e1' ?>; background: <?= !empty($certificate) ? '#f0fdf4' : '#fff' ?>;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 700; color: #166534; text-transform: uppercase;">Chứng Thư Số Hiện Tại</span>
                        <h4 style="margin: 0.2rem 0 0 0; font-size: 1.1rem; color: #0f172a;">
                            <?= htmlspecialchars($employer['employer_name']) ?>
                        </h4>
                    </div>
                    <?php if (!empty($certificate) && $certificate['status'] === 'active'): ?>
                        <span class="badge badge-success" style="font-size: 0.8rem; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <?= icon('check_circle', '', 14) ?> ĐÃ XÁC THỰC
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning" style="font-size: 0.8rem;">CHƯA XÁC THỰC</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($certificate)): ?>
                    <div style="font-size: 0.85rem; line-height: 1.8; color: #1e293b;">
                        <div><strong>Nhà cung cấp:</strong> <span style="color: #0d9488; font-weight: 700;"><?= htmlspecialchars($certificate['issuer_ca']) ?></span></div>
                        <div><strong>Mã số thuế:</strong> <code><?= htmlspecialchars($certificate['tax_code']) ?></code></div>
                        <div><strong>Số Serial:</strong> <code style="word-break: break-all;"><?= htmlspecialchars($certificate['serial_number']) ?></code></div>
                        <div><strong>Hiệu lực:</strong> <?= date('d/m/Y', strtotime($certificate['valid_from'])) ?> - <?= date('d/m/Y', strtotime($certificate['valid_to'])) ?></div>
                        <div style="margin-top: 0.5rem; padding: 0.5rem; background: #fff; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 0.75rem; font-family: monospace; color: #059669; word-break: break-all;">
                            SHA-256: <?= htmlspecialchars($certificate['cert_fingerprint']) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="color: #64748b; font-size: 0.88rem; line-height: 1.6; margin: 0;">
                        Doanh nghiệp của bạn chưa đăng ký chữ ký số. Hãy điền thông tin bên cạnh để xác thực ngay!
                    </p>
                <?php endif; ?>
            </div>

            <!-- QUYỀN LỢI KHI XÁC THỰC SỐ -->
            <div class="card" style="background: linear-gradient(135deg, #f0fdfa 0%, #e6fffa 100%); border: 1px solid #99f6e4;">
                <h4 style="margin: 0 0 0.65rem 0; font-size: 0.95rem; color: #0f766e; display: flex; align-items: center; gap: 0.35rem;">
                    <?= icon('award', '', 18) ?> Quyền Lợi Doanh Nghiệp Đã Xác Thực Chữ Ký Số
                </h4>
                <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.83rem; color: #115e59; line-height: 1.6; display: flex; flex-direction: column; gap: 0.35rem;">
                    <li><strong>Ưu tiên hiển thị Top 1:</strong> Tin tuyển dụng luôn xuất hiện đầu tiên trên Trang chủ và kết quả tìm kiếm.</li>
                    <li><strong>Huy hiệu Tích Xanh:</strong> Tăng 85% độ tin cậy từ người lao động ứng tuyển.</li>
                    <li><strong>Ký hợp đồng pháp lý:</strong> Ký hợp đồng điện tử trực tiếp với Ban Quản Trị Sàn theo Luật Giao dịch Điện tử.</li>
                </ul>
            </div>
        </div>
    </div>
<?php elseif ($activeTab === 'contracts'): ?>
    <!-- TAB 2: HỢP ĐỒNG ĐIỆN TỬ VỚI SÀN -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
            <div>
                <h3 style="margin: 0; font-size: 1.2rem; color: #0f172a;">Hợp Đồng Điện Tử Giữa Doanh Nghiệp & Sàn JobLink</h3>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">
                    Ký kết bằng Chữ Ký Số của Doanh Nghiệp. Giá trị pháp lý tương đương hợp đồng văn bản giấy.
                </p>
            </div>
            <span class="badge badge-success"><?= icon('shield_check', '', 14) ?> Bảo Mật & Toàn Vẹn</span>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã Hợp Đồng</th>
                        <th>Tiêu Đề</th>
                        <th>Thời Hạn</th>
                        <th>Chữ Ký Sàn JobLink</th>
                        <th>Chữ Ký Doanh Nghiệp</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contracts)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #64748b;">
                                Doanh nghiệp chưa có hợp đồng điện tử nào với sàn. Ban Quản Trị sẽ tạo dự thảo và gửi tới quý công ty sớm nhất!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contracts as $c): ?>
                            <tr>
                                <td><strong style="color: #0d9488; font-family: monospace;"><?= htmlspecialchars($c['contract_code']) ?></strong></td>
                                <td><strong><?= htmlspecialchars($c['contract_title']) ?></strong></td>
                                <td style="font-size: 0.8rem;"><?= date('d/m/Y', strtotime($c['start_date'])) ?> - <?= !empty($c['end_date']) ? date('d/m/Y', strtotime($c['end_date'])) : 'Vô thời hạn' ?></td>
                                <td>
                                    <?php if ($c['admin_signed']): ?>
                                        <span class="badge badge-success"><?= icon('check_circle', '', 12) ?> Sàn Đã Ký</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Chờ Sàn Ký</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($c['employer_signed']): ?>
                                        <span class="badge badge-success"><?= icon('check_circle', '', 12) ?> DN Đã Ký</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Chưa Ký</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($c['status'] === 'active'): ?>
                                        <span class="badge badge-success"><?= icon('shield_check', '', 12) ?> Có Hiệu Lực</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Chờ Ký Hoàn Tất</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem; align-items: center;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="previewEmpContract(<?= htmlspecialchars(json_encode($c)) ?>)" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                            Xem HĐ
                                        </button>
                                        <?php if (!$c['employer_signed']): ?>
                                            <form action="<?= url('/employer/contracts/sign/' . $c['contract_id']) ?>" method="POST" style="display:inline;">
                                                <button type="submit" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.55rem; font-weight: 700;">
                                                    <?= icon('file_signature', '', 12) ?> Ký Số Ngay
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
<?php elseif ($activeTab === 'auth'): ?>
    <!-- TAB 3: ỦY QUYỀN NGƯỜI TRUNG GIAN -->
    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem; align-items: start;">
        <!-- FORM LẬP VĂN BẢN ỦY QUYỀN -->
        <div class="card">
            <div style="margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
                <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a;">Lập Văn Bản Ủy Quyền Tuyển Dụng</h3>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.82rem; color: #64748b;">
                    Ủy quyền cho Người trung gian / Đại lý cung ứng đại diện tuyển dụng nhân sự hợp pháp.
                </p>
            </div>

            <form action="<?= url('/employer/authorizations/create') ?>" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Chọn Đại Lý / Người Trung Gian *</label>
                    <select name="intermediary_account_id" class="form-control" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                        <option value="">-- Chọn Người Trung Gian --</option>
                        <?php foreach ($intermediaries as $inter): ?>
                            <option value="<?= $inter['account_id'] ?>">
                                <?= htmlspecialchars($inter['full_name']) ?> (<?= htmlspecialchars($inter['company_agency_name'] ?? 'Môi giới cá nhân') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Phạm Vi Tuyển Dụng Được Ủy Quyền *</label>
                    <textarea name="scope_of_work" rows="3" class="form-control" required placeholder="Ví dụ: Đại diện tiếp nhận và tuyển dụng 50 công nhân may ca xoay..." style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;"></textarea>
                </div>

                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Tỷ Lệ / Mức Phí Hoa Hồng</label>
                    <input type="text" name="commission_rate" class="form-control" value="Theo thỏa thuận (10-15%)" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Hiệu Lực Từ *</label>
                        <input type="date" name="valid_from" class="form-control" required value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 0.55rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                    </div>
                    <div>
                        <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Đến Ngày *</label>
                        <input type="date" name="valid_to" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 year')) ?>" style="width: 100%; padding: 0.55rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                    </div>
                </div>

                <div style="text-align: right; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                        <?= icon('handshake', '', 15) ?> Ký Số & Ban Hành Ủy Quyền
                    </button>
                </div>
            </form>
        </div>

        <!-- DANH SÁCH ỦY QUYỀN ĐÃ BAN HÀNH -->
        <div class="card">
            <h3 style="margin: 0 0 1rem 0; font-size: 1.15rem; color: #0f172a;">Danh Sách Văn Bản Ủy Quyền Đã Ban Hành</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã UQ</th>
                            <th>Người Trung Gian</th>
                            <th>Phạm Vi</th>
                            <th>Trạng Thái Thẩm Định</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($authorizations)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 1.5rem; color: #64748b;">
                                    Doanh nghiệp chưa ủy quyền cho người trung gian nào.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($authorizations as $a): ?>
                                <tr>
                                    <td><strong style="color: #0d9488; font-family: monospace;"><?= htmlspecialchars($a['authorization_code']) ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($a['intermediary_name']) ?></strong>
                                        <div style="font-size: 0.72rem; color: #64748b;"><?= htmlspecialchars($a['company_agency_name'] ?? 'Môi giới cá nhân') ?></div>
                                    </td>
                                    <td style="font-size: 0.8rem;"><?= htmlspecialchars(mb_substr($a['scope_of_work'], 0, 35)) ?>...</td>
                                    <td>
                                        <?php if ($a['admin_verification_status'] === 'verified'): ?>
                                            <span class="badge badge-success"><?= icon('shield_check', '', 12) ?> Hợp Pháp</span>
                                        <?php elseif ($a['admin_verification_status'] === 'rejected'): ?>
                                            <span class="badge badge-danger">Từ Chối</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Chờ BQT Duyệt</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- TAB 4: THÔNG BÁO GMAIL -->
    <div class="card" style="max-width: 780px;">
        <h3 style="margin: 0 0 1rem 0; font-size: 1.15rem; color: #0f172a;">Cài Đặt Nhận Email Thông Báo Tuyển Dụng Qua Gmail</h3>
        <p style="color: #64748b; font-size: 0.88rem; line-height: 1.6; margin-bottom: 1.5rem;">
            Email sẽ được tự động gửi đến hòm thư: <strong><?= htmlspecialchars($employer['contact_email'] ?? $_SESSION['user']['email'] ?? 'Gmail của bạn') ?></strong>.
        </p>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <label style="display: flex; align-items: center; gap: 0.75rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.92rem;">Thông báo hồ sơ ứng tuyển mới</strong>
                    <div style="color: #64748b; font-size: 0.8rem;">Gửi email ngay khi có người lao động nộp đơn ứng tuyển vào tin tuyển dụng.</div>
                </div>
            </label>

            <label style="display: flex; align-items: center; gap: 0.75rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.92rem;">Thông báo tin nhắn trực tiếp</strong>
                    <div style="color: #64748b; font-size: 0.8rem;">Gửi email kèm trích đoạn nội dung khi người lao động gửi tin nhắn trao đổi.</div>
                </div>
            </label>

            <label style="display: flex; align-items: center; gap: 0.75rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.92rem;">Nhắc nhở lịch phỏng vấn trước 2 giờ</strong>
                    <div style="color: #64748b; font-size: 0.8rem;">Gửi email nhắc nhở nhà tuyển dụng và người lao động trước ca phỏng vấn.</div>
                </div>
            </label>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="button" class="btn btn-primary" onclick="alert('Đã lưu tùy chọn thông báo Gmail thành công!')" style="font-weight: 700;">
                Lưu Tùy Chọn Gmail
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- TAB 5: LỊCH SỬ THAY ĐỔI THÔNG TIN DOANH NGHIỆP -->
<?php if ($activeTab === 'history'): ?>
    <div class="card" style="box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 14px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                    <?= icon('clock', 'text-primary', 20) ?>
                    <span>Nhật Ký Thay Đổi Thông Tin Doanh Nghiệp</span>
                </h3>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">
                    Ghi nhận chi tiết thời gian, nội dung cũ, nội dung mới và người thực hiện (doanh nghiệp tự cập nhật hoặc bị Admin can thiệp).
                </p>
            </div>
            <span class="badge badge-primary" style="font-size: 0.8rem; font-weight: 700;">Tổng cộng: <?= count($historyLogs ?? []) ?> lần thay đổi</span>
        </div>

        <?= class_exists('ProfileHistoryService') ? ProfileHistoryService::renderHistoryTableHtml($historyLogs ?? [], 'Chưa có lịch sử thay đổi thông tin doanh nghiệp nào.') : '' ?>
    </div>
<?php endif; ?>

<!-- MODAL PREVIEW CONTRACT -->
<div id="empContractModal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#fff; border-radius:12px; width:95%; max-width:760px; padding:2rem; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); position:relative; max-height:90vh; overflow-y:auto;">
        <button onclick="closeEmpContractModal()" style="position:absolute; top:1.2rem; right:1.2rem; border:none; background:transparent; font-size:1.5rem; cursor:pointer; color:#64748b;">&times;</button>
        <h3 id="modalContractTitle" style="margin-top:0; color:#0f172a;"></h3>
        <div id="modalContractCode" style="color:#0d9488; font-weight:700; margin-bottom:1rem; font-family:monospace;"></div>
        <div id="modalContractBody" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:1.25rem; font-size:0.88rem; color:#1e293b; white-space:pre-wrap; line-height:1.6; max-height:280px; overflow-y:auto;"></div>
        <div style="margin-top:1.5rem; text-align:right;">
            <button type="button" class="btn btn-outline btn-sm" onclick="closeEmpContractModal()">Đóng</button>
        </div>
    </div>
</div>

<script>
function previewEmpContract(c) {
    document.getElementById('modalContractTitle').textContent = c.contract_title;
    document.getElementById('modalContractCode').textContent = 'MÃ SỐ: ' + c.contract_code;
    document.getElementById('modalContractBody').textContent = c.contract_content;
    document.getElementById('empContractModal').style.display = 'flex';
}
function closeEmpContractModal() {
    document.getElementById('empContractModal').style.display = 'none';
}
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
