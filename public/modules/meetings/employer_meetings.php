<?php ob_start(); ?>

<style>
.btn-table-action {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.3rem !important;
    padding: 0.28rem 0.58rem !important;
    font-size: 0.76rem !important;
    font-weight: 600 !important;
    border-radius: 7px !important;
    white-space: nowrap !important;
    border: 1px solid #0d9488 !important;
    color: #0d9488 !important;
    background: #ffffff !important;
    text-decoration: none !important;
    transition: all 0.15s ease !important;
    line-height: 1.2 !important;
    cursor: pointer !important;
}
.btn-table-action:hover {
    background: #f0fdfa !important;
    border-color: #0f766e !important;
    color: #0f766e !important;
}
.btn-table-action.btn-table-reinvite {
    border-color: #d97706 !important;
    color: #d97706 !important;
}
.btn-table-action.btn-table-reinvite:hover {
    background: #fffbeb !important;
    border-color: #b45309 !important;
    color: #b45309 !important;
}
.result-choice-card {
    transition: all 0.2s ease !important;
}
.result-choice-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Quản Lý & Lên Lịch Phỏng Vấn</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem;">Theo dõi lịch hẹn trao đổi trực tiếp hoặc phỏng vấn trực tuyến với người lao động</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary" id="toggleCreateMeetingBtn" style="font-weight: 700;">
            <?= icon('meetings', '', 16) ?> <span style="margin-left: 4px;">+ Tạo Lịch Hẹn Mới</span>
        </button>
    </div>
</div>

<!-- FORM TẠO LỊCH HẸN MỚI (CÓ THỂ ĐÓNG/MỞ) -->
<div class="card" id="createMeetingCard" style="display: none; margin-bottom: 1.5rem; border: 1px solid #99f6e4; background: #fafdfc;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #ccfbf1; padding-bottom: 0.75rem;">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--dark); display: flex; align-items: center; gap: 0.4rem;">
            <?= icon('calendar', '', 18) ?> Tạo Cuộc Hẹn Phỏng Vấn Mới
        </h3>
        <button type="button" id="closeCreateMeetingBtn" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
    </div>
    
    <form action="<?= url('/employer/meetings/store') ?>" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Chọn Đơn Ứng Tuyển / Người Lao Động *</label>
                <select name="request_id" class="form-control" required style="padding: 0.55rem;">
                    <?php if (empty($applications)): ?>
                        <option value="">-- Chưa có người lao động nào được duyệt --</option>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <option value="<?= $app['request_id'] ?>">
                                <?= htmlspecialchars($app['seeker_name'] ?? ($app['full_name'] ?? 'Người lao động #' . $app['request_id'])) ?> — Vị trí: <?= htmlspecialchars($app['job_title'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Hình Thức Phỏng Vấn *</label>
                <select name="meeting_format" class="form-control" style="padding: 0.55rem;">
                    <option value="offline">Trực tiếp tại cơ sở / văn phòng (Offline)</option>
                    <option value="online">Trực tuyến Google Meet / Zoom (Online)</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Ngày Hẹn *</label>
                <input type="date" name="meeting_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" style="padding: 0.55rem;">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Giờ Bắt Đầu *</label>
                <input type="time" name="start_time" class="form-control" required value="09:00" style="padding: 0.55rem;">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Giờ Kết Thúc</label>
                <input type="time" name="end_time" class="form-control" value="10:00" style="padding: 0.55rem;">
            </div>
        </div>

        <div style="margin-bottom: 1rem;">
            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Khoảng Ngày Dự Kiến Phỏng Vấn (để người lao động chuẩn bị thời gian & xin dời lịch nếu cần):</label>
            <input type="text" name="interview_range" class="form-control" value="<?= date('d/m/Y', strtotime('+1 day')) ?> - <?= date('d/m/Y', strtotime('+4 days')) ?>" placeholder="Ví dụ: <?= date('d/m/Y', strtotime('+1 day')) ?> - <?= date('d/m/Y', strtotime('+4 days')) ?>" style="padding: 0.55rem;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Địa Chỉ Hoặc Link Phòng Họp *</label>
            <input type="text" name="location_or_meeting_link" class="form-control" required placeholder="Địa chỉ cơ sở làm việc hoặc https://meet.google.com/..." style="padding: 0.55rem;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Cẩm Nang Hướng Dẫn Người Lao Động Chuẩn Bị Trước Buổi Phỏng Vấn:</label>
            <textarea name="prep_guide" class="form-control" rows="2" placeholder="Ví dụ: Tìm hiểu kỹ thông tin công ty, chuẩn bị phần giới thiệu kinh nghiệm bản thân, mang theo CCCD & CV..." style="padding: 0.55rem;">Tìm hiểu thông tin công ty và vị trí ứng tuyển. Chuẩn bị phần giới thiệu kinh nghiệm bản thân và mang theo CCCD/bản in CV khi đến phỏng vấn.</textarea>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Ghi Chú Thêm:</label>
            <textarea name="meeting_note" class="form-control" rows="2" placeholder="Mang theo CCCD/VNeID, hồ sơ gốc hoặc chuẩn bị bài test..." style="padding: 0.55rem;"></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" id="cancelCreateMeetingBtn" class="btn btn-outline">Hủy Bỏ</button>
            <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                <?= icon('meetings', '', 16) ?> <span style="margin-left: 5px;">Xác Nhận & Tạo Lịch Hẹn</span>
            </button>
        </div>
    </form>
</div>

<!-- DANH SÁCH CUỘC HẸN (FULL WIDTH, COMPACT 1-LINE ROWS) -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--dark);">
            Danh Sách Lịch Phỏng Vấn (<?= count($meetings) ?>)
        </h3>
    </div>

    <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 0.75rem 1rem; text-align: left; white-space: nowrap; min-width: 220px;">Người lao động</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; white-space: nowrap; min-width: 150px;">Thời gian</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; min-width: 220px;">Hình thức / Địa điểm</th>
                    <th style="padding: 0.75rem 1rem; text-align: center; white-space: nowrap; width: 150px;">Trạng thái & Tiến độ</th>
                    <th style="padding: 0.75rem 1rem; text-align: right; white-space: nowrap; min-width: 180px;">Xử lý</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($meetings)): ?>
                    <tr><td colspan="5" style="text-align: center; color: var(--slate-400); padding: 3rem 1rem;">Chưa có lịch hẹn nào. Bấm <strong>"+ Tạo Lịch Hẹn Mới"</strong> để lên lịch phỏng vấn!</td></tr>
                <?php else: ?>
                    <?php foreach ($meetings as $m): ?>
                        <?php 
                            $isPast = (!empty($m['meeting_date']) && strtotime($m['meeting_date']) <= strtotime(date('Y-m-d'))) || ($m['meeting_status'] === 'completed');
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; vertical-align: middle;">
                            <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                <div>
                                    <a href="<?= url('/employer/candidates/' . ($m['seeker_id'] ?? '')) ?>" style="color: #0d9488; font-weight: 700; text-decoration: none; font-size: 0.95rem;" title="Xem hồ sơ">
                                        <?= htmlspecialchars($m['seeker_name']) ?> &rarr;
                                    </a>
                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.15rem; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= htmlspecialchars($m['job_title']) ?>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                <div style="color: #0f172a; font-weight: 700; font-size: 0.9rem;">
                                    <?= date('d/m/Y', strtotime($m['meeting_date'])) ?>
                                </div>
                                <div style="font-size: 0.8rem; color: #0d9488; font-weight: 600; margin-top: 0.1rem;">
                                    <?= icon('clock', '', 13) ?> <?= substr($m['start_time'], 0, 5) ?><?= !empty($m['end_time']) ? ' - ' . substr($m['end_time'], 0, 5) : '' ?>
                                </div>
                                <?php if (!empty($m['interview_range'])): ?>
                                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 0.1rem;">
                                        Khung: <?= htmlspecialchars($m['interview_range']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: nowrap;">
                                    <span class="badge <?= $m['meeting_format'] === 'online' ? 'badge-primary' : 'badge-slate' ?>" style="font-size: 0.75rem; padding: 0.2rem 0.5rem; white-space: nowrap; flex-shrink: 0;">
                                        <?= $m['meeting_format'] === 'online' ? 'Online' : 'Trực tiếp' ?>
                                    </span>
                                    <?php if ($m['meeting_format'] === 'online'): ?>
                                        <a href="<?= htmlspecialchars($m['location_or_meeting_link']) ?>" target="_blank" style="font-size: 0.82rem; color: #0d9488; font-weight: 600; text-decoration: none; overflow: hidden; text-overflow: ellipsis; max-width: 200px; white-space: nowrap;" title="<?= htmlspecialchars($m['location_or_meeting_link']) ?>">
                                            <?= htmlspecialchars($m['location_or_meeting_link']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size: 0.82rem; color: #475569; overflow: hidden; text-overflow: ellipsis; max-width: 220px; white-space: nowrap;" title="<?= htmlspecialchars($m['location_or_meeting_link']) ?>">
                                            <?= htmlspecialchars($m['location_or_meeting_link']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($m['prep_guide'])): ?>
                                    <div style="font-size: 0.74rem; color: #0d9488; margin-top: 0.2rem; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($m['prep_guide']) ?>">
                                        <strong>Chuẩn bị:</strong> <?= htmlspecialchars($m['prep_guide']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($m['meeting_note'])): ?>
                                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.15rem; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($m['meeting_note']) ?>">
                                        <?= htmlspecialchars($m['meeting_note']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: center; white-space: nowrap;">
                                <?php if ($m['request_status'] === 'accepted'): ?>
                                    <span class="badge badge-success" style="font-size: 0.78rem; padding: 0.25rem 0.55rem; font-weight: 700; white-space: nowrap;">
                                        <?= icon('check_circle', '', 13) ?> Đã nhận việc (100%)
                                    </span>
                                <?php elseif ($m['meeting_status'] === 'scheduled'): ?>
                                    <?php if ($isPast): ?>
                                        <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.78rem; padding: 0.25rem 0.55rem; font-weight: 700; white-space: nowrap;">
                                            75% Đã PV xong (Chờ KQ)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="font-size: 0.78rem; padding: 0.25rem 0.55rem; white-space: nowrap;">
                                            50% Đang chuẩn bị
                                        </span>
                                    <?php endif; ?>
                                <?php elseif ($m['meeting_status'] === 'completed'): ?>
                                    <span class="badge badge-success" style="font-size: 0.78rem; padding: 0.25rem 0.55rem; font-weight: 700; white-space: nowrap;">
                                        <?= icon('check', '', 12) ?> Hoàn thành (75%)
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-danger" style="font-size: 0.78rem; padding: 0.25rem 0.55rem; white-space: nowrap;">Đã hủy</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; white-space: nowrap;">
                                <div style="display: flex; gap: 0.35rem; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                                    <?php if ($m['request_status'] === 'accepted'): ?>
                                        <span style="font-size: 0.78rem; color: #059669; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <?= icon('check_circle', '', 14) ?> Đã tiếp nhận chính thức
                                        </span>
                                        <a href="<?= url('/employer/candidate-reviews') ?>" class="btn-table-action" title="Đánh giá người lao động">
                                            <?= icon('star', '', 13) ?> Đánh Giá
                                        </a>
                                        <a href="<?= url('/employer/messages?partner_id=' . ($m['seeker_account_id'] ?? 0) . '&seeker_id=' . ($m['seeker_id'] ?? 0) . '&request_id=' . ($m['request_id'] ?? 0) . '&job_id=' . ($m['job_post_id'] ?? 0)) ?>" 
                                           class="btn-table-action" 
                                           title="Nhắn tin trao đổi với người lao động">
                                            <?= icon('chat', '', 13) ?> Nhắn tin
                                        </a>
                                    <?php else: ?>
                                        <?php if ($m['meeting_status'] === 'cancelled'): ?>
                                            <form action="<?= url('/employer/meetings/update/' . $m['meeting_id']) ?>" method="POST" style="display:inline;">
                                                <input type="hidden" name="status" value="scheduled">
                                                <button type="submit" class="btn-table-action btn-table-reinvite" title="Lên lại lịch phỏng vấn">
                                                    <?= icon('refresh', '', 13) ?> Lên Lại
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="btn-table-action btn-open-result-modal"
                                                    data-meeting-id="<?= $m['meeting_id'] ?>"
                                                    data-request-id="<?= $m['request_id'] ?>"
                                                    data-seeker-name="<?= htmlspecialchars($m['seeker_name']) ?>"
                                                    data-job-title="<?= htmlspecialchars($m['job_title']) ?>"
                                                    data-workplace="<?= htmlspecialchars($m['workplace_address'] ?? '') ?>"
                                                    title="Chọn kết quả phỏng vấn">
                                                <?= icon('check_circle', '', 13) ?> Kết Quả
                                            </button>

                                            <?php if ($m['meeting_status'] === 'completed'): ?>
                                                <a href="<?= url('/employer/candidate-reviews') ?>" class="btn-table-action" title="Đánh giá người lao động">
                                                    <?= icon('star', '', 13) ?> Đánh Giá
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <a href="<?= url('/employer/messages?partner_id=' . ($m['seeker_account_id'] ?? 0) . '&seeker_id=' . ($m['seeker_id'] ?? 0) . '&request_id=' . ($m['request_id'] ?? 0) . '&job_id=' . ($m['job_post_id'] ?? 0)) ?>" 
                                           class="btn-table-action" 
                                           title="Nhắn tin trao đổi với người lao động">
                                            <?= icon('chat', '', 13) ?> Nhắn tin
                                        </a>
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

<!-- ========================================================================= -->
<!-- MODAL THIẾT LẬP NGÀY LÀM ĐẦU TIÊN KHI PHỎNG VẤN THÀNH CÔNG (ONBOARDING) -->
<!-- ========================================================================= -->
<div id="acceptCandidateModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 14px; max-width: 600px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%); color: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #ffffff;">
                    <?= icon('check_circle', '', 20) ?> Phỏng Vấn Thành Công & Thiết Lập Ngày Làm Đầu Tiên
                </h3>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #d1fae5;" id="modalCandidateSubTitle">
                    Thông báo tiếp nhận & hành trang chuẩn bị sẽ gửi trực tiếp đến người lao động
                </p>
            </div>
            <button type="button" onclick="document.getElementById('acceptCandidateModal').style.display='none'" style="background: none; border: none; color: #ffffff; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form id="acceptCandidateForm" method="POST" style="overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.85rem 1rem; font-size: 0.88rem; color: #166534;">
                Hệ thống sẽ cập nhật tiến độ <strong>100% (Phỏng Vấn Thành Công)</strong> và chuyển trạng thái lịch hẹn sang thông báo ngày làm bữa đầu tiên cho người lao động.
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label class="form-label" style="font-weight: 700; font-size: 0.88rem;">Ngày Bắt Đầu Làm Việc *</label>
                    <input type="date" name="start_date" id="modal_start_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                </div>
                <div>
                    <label class="form-label" style="font-weight: 700; font-size: 0.88rem;">Giờ Có Mặt Buổi Đầu *</label>
                    <input type="time" name="start_time" id="modal_start_time" class="form-control" required value="08:00">
                </div>
            </div>

            <div>
                <label class="form-label" style="font-weight: 700; font-size: 0.88rem;">Địa Điểm Làm Việc / Tiếp Đón *</label>
                <input type="text" name="workplace" id="modal_workplace" class="form-control" required placeholder="Địa chỉ chi nhánh hoặc phòng ban tiếp nhận...">
            </div>

            <div>
                <label class="form-label" style="font-weight: 700; font-size: 0.88rem;">Người Đón Tiếp / Số Điện Thoại Liên Hệ *</label>
                <input type="text" name="contact_person" id="modal_contact_person" class="form-control" required placeholder="Ví dụ: Chị Lan (P. Nhân Sự) - 0988.xxx.xxx">
            </div>

            <div>
                <label class="form-label" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.4rem;">
                    Hồ Sơ & Trang Phục Cần Chuẩn Bị Cho Buổi Đầu:
                </label>
                <div style="display: flex; flex-direction: column; gap: 0.4rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem; font-size: 0.85rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Căn cước công dân (CCCD / VNeID) bản gốc & photo" checked style="accent-color: #059669;">
                        <span>Căn cước công dân (CCCD / VNeID) bản gốc & photo</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Sơ yếu lý lịch tự thuật & Giấy khám sức khỏe" checked style="accent-color: #059669;">
                        <span>Sơ yếu lý lịch tự thuật & Giấy khám sức khỏe</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="02 ảnh 3x4 làm thẻ nhân viên" checked style="accent-color: #059669;">
                        <span>02 ảnh 3x4 làm thẻ nhân viên</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Thông tin tài khoản ngân hàng chính chủ nhận lương" checked style="accent-color: #059669;">
                        <span>Thông tin tài khoản ngân hàng chính chủ nhận lương</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="checklist[]" value="Trang phục: Quần dài lịch sự, áo sơ mi/áo thun có cổ, giày bít mũi" checked style="accent-color: #059669;">
                        <span>Trang phục: Quần dài lịch sự, áo sơ mi/áo thun có cổ, giày bít mũi</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="form-label" style="font-weight: 700; font-size: 0.88rem;">Ghi Chú / Dặn Dò Thêm (Nếu có):</label>
                <textarea name="notes" id="modal_notes" class="form-control" rows="2" placeholder="Gửi xe ở tầng hầm B1, bấm thang máy lên tầng 3 gặp lễ tân..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('acceptCandidateModal').style.display='none'">Đóng</button>
                <button type="submit" class="btn btn-success" style="font-weight: 700; background: #059669; border-color: #059669; padding: 0.6rem 1.25rem;">
                    Xác Nhận Nhận Việc & Gửi Thông Báo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL KẾT QUẢ PHỎNG VẤN (ĐẬU / TRƯỢT - 2 NÚT NẰM 2 BÊN)                  -->
<!-- ========================================================================= -->
<div id="resultModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 16px; max-width: 480px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15); overflow: hidden;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); color: #fff; padding: 1.15rem 1.4rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: flex; align-items: center;"><?= icon('award', '', 20) ?></span>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #fff;">Kết Quả Phỏng Vấn</h3>
            </div>
            <button type="button" onclick="document.getElementById('resultModal').style.display='none'" style="background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Body -->
        <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1rem;">
            <div style="text-align: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                <div style="font-size: 1rem; font-weight: 700; color: #1e293b;" id="resultModalCandidateName">Người lao động</div>
                <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.2rem;" id="resultModalJobTitle">Vị trí ứng tuyển</div>
            </div>

            <p style="margin: 0; font-size: 0.88rem; color: #4b5563; text-align: center;">Chọn kết quả phỏng vấn để cập nhật tiến độ cho người lao động:</p>

            <!-- 2 Nút nằm 2 bên (Trái: Đậu / Phải: Trượt) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <!-- Bên trái: ĐẬU -->
                <button id="resultPassBtn" type="button" class="result-choice-card"
                        style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.6rem; padding: 1.25rem 0.75rem; background: #f0fdf4; border: 2px solid #059669; border-radius: 12px; cursor: pointer; text-align: center;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #059669; color: #ffffff; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.3);">
                        <?= icon('check_circle', '', 22) ?>
                    </div>
                    <div>
                        <div style="font-size: 1rem; font-weight: 800; color: #065f46;">ĐẠT (ĐẬU)</div>
                        <div style="font-size: 0.76rem; color: #047857; margin-top: 0.2rem; font-weight: 500;">Tiếp nhận & đi làm</div>
                    </div>
                </button>

                <!-- Bên phải: TRƯỢT -->
                <button id="resultFailToggleBtn" type="button" class="result-choice-card"
                        style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.6rem; padding: 1.25rem 0.75rem; background: #fef2f2; border: 2px solid #dc2626; border-radius: 12px; cursor: pointer; text-align: center;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #dc2626; color: #ffffff; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.3);">
                        <?= icon('x_circle', '', 22) ?>
                    </div>
                    <div>
                        <div style="font-size: 1rem; font-weight: 800; color: #991b1b;">KHÔNG ĐẠT (TRƯỢT)</div>
                        <div style="font-size: 0.76rem; color: #b91c1c; margin-top: 0.2rem; font-weight: 500;">Từ chối / Hủy lịch</div>
                    </div>
                </button>
            </div>

            <!-- Form xác nhận Không Đạt (hiện khi bấm nút Trượt) -->
            <div id="resultFailForm" style="display: none; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 0.85rem; flex-direction: column; gap: 0.65rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #991b1b;">Lý do không phù hợp (tùy chọn):</label>
                <textarea id="resultFailReason" rows="2" style="width: 100%; padding: 0.5rem 0.65rem; border: 1px solid #fca5a5; border-radius: 6px; font-size: 0.85rem; resize: vertical; box-sizing: border-box; background: #ffffff;" placeholder="Ví dụ: Chưa đáp ứng yêu cầu kinh nghiệm, kỹ năng phỏng vấn chưa phù hợp..."></textarea>
                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button id="resultFailCancelBtn" type="button" class="btn btn-outline btn-sm" style="font-size: 0.78rem; padding: 0.3rem 0.65rem;">
                        Hủy
                    </button>
                    <button id="resultFailConfirm" type="button" class="btn btn-danger btn-sm" style="font-size: 0.78rem; padding: 0.3rem 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <?= icon('check', '', 13) ?> Xác Nhận Không Đạt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleCreateMeetingBtn');
    const closeBtn = document.getElementById('closeCreateMeetingBtn');
    const cancelBtn = document.getElementById('cancelCreateMeetingBtn');
    const formCard = document.getElementById('createMeetingCard');

    if (toggleBtn && formCard) {
        toggleBtn.addEventListener('click', function() {
            formCard.style.display = (formCard.style.display === 'none') ? 'block' : 'none';
            if (formCard.style.display === 'block') {
                formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
    if (closeBtn && formCard) {
        closeBtn.addEventListener('click', () => formCard.style.display = 'none');
    }
    if (cancelBtn && formCard) {
        cancelBtn.addEventListener('click', () => formCard.style.display = 'none');
    }

    // Xử lý modal Phỏng Vấn Thành Công (Nhận Việc)
    const acceptBtns = document.querySelectorAll('.btn-open-accept-modal');
    const acceptModal = document.getElementById('acceptCandidateModal');
    const acceptForm = document.getElementById('acceptCandidateForm');
    const modalSubtitle = document.getElementById('modalCandidateSubTitle');
    const modalWorkplace = document.getElementById('modal_workplace');

    acceptBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const reqId = this.getAttribute('data-request-id');
            const seekerName = this.getAttribute('data-seeker-name');
            const jobTitle = this.getAttribute('data-job-title');
            const workplace = this.getAttribute('data-workplace');

            acceptForm.action = '<?= url('/employer/applications/accept-onboarding/') ?>' + reqId;
            modalSubtitle.textContent = 'Gửi thông báo trúng tuyển & ngày đi làm cho ' + seekerName + ' (' + jobTitle + ')';
            if (workplace) {
                modalWorkplace.value = workplace;
            }
            acceptModal.style.display = 'flex';
        });
    });

    // Xử lý modal Kết Quả Phỏng Vấn (Đậu / Trượt - 2 Nút 2 Bên)
    const resultModal = document.getElementById('resultModal');
    const resultModalTitle = document.getElementById('resultModalTitle');
    const resultModalCandidateName = document.getElementById('resultModalCandidateName');
    const resultModalJobTitle = document.getElementById('resultModalJobTitle');
    const resultPassBtn = document.getElementById('resultPassBtn');
    const resultFailForm = document.getElementById('resultFailForm');
    const resultFailConfirm = document.getElementById('resultFailConfirm');
    const resultFailCancelBtn = document.getElementById('resultFailCancelBtn');

    let _resultData = {};

    document.querySelectorAll('.btn-open-result-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            _resultData = {
                meetingId: this.getAttribute('data-meeting-id'),
                requestId: this.getAttribute('data-request-id'),
                seekerName: this.getAttribute('data-seeker-name'),
                jobTitle: this.getAttribute('data-job-title'),
                workplace: this.getAttribute('data-workplace'),
            };
            if (resultModalCandidateName) {
                resultModalCandidateName.textContent = _resultData.seekerName;
            }
            if (resultModalJobTitle) {
                resultModalJobTitle.textContent = _resultData.jobTitle ? '(' + _resultData.jobTitle + ')' : '';
            }
            // Reset fail section
            if (resultFailForm) {
                resultFailForm.style.display = 'none';
            }
            const failReasonInput = document.getElementById('resultFailReason');
            if (failReasonInput) {
                failReasonInput.value = '';
            }
            resultModal.style.display = 'flex';
        });
    });

    // Nút Đậu → mở acceptCandidateModal (onboarding flow)
    if (resultPassBtn) {
        resultPassBtn.addEventListener('click', function() {
            resultModal.style.display = 'none';
            acceptForm.action = '<?= url('/employer/applications/accept-onboarding/') ?>' + _resultData.requestId;
            modalSubtitle.textContent = 'Gửi thông báo trúng tuyển & ngày đi làm cho ' + _resultData.seekerName + ' (' + _resultData.jobTitle + ')';
            if (_resultData.workplace) {
                modalWorkplace.value = _resultData.workplace;
            }
            acceptModal.style.display = 'flex';
        });
    }

    // Nút Trượt → toggle phần lý do & xác nhận
    const resultFailToggleBtn = document.getElementById('resultFailToggleBtn');
    if (resultFailToggleBtn && resultFailForm) {
        resultFailToggleBtn.addEventListener('click', function() {
            resultFailForm.style.display = (resultFailForm.style.display === 'none' || resultFailForm.style.display === '') ? 'flex' : 'none';
        });
    }

    // Hủy bỏ nhập lý do trượt
    if (resultFailCancelBtn && resultFailForm) {
        resultFailCancelBtn.addEventListener('click', function() {
            resultFailForm.style.display = 'none';
        });
    }

    // Submit Trượt
    if (resultFailConfirm) {
        resultFailConfirm.addEventListener('click', function() {
            const reason = (document.getElementById('resultFailReason')?.value || '').trim();
            const url = '<?= url('/employer/meetings/update/') ?>' + _resultData.meetingId;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden'; 
            statusInput.name = 'status'; 
            statusInput.value = 'cancelled';
            form.appendChild(statusInput);
            if (reason) {
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden'; 
                reasonInput.name = 'reason'; 
                reasonInput.value = reason;
                form.appendChild(reasonInput);
            }
            document.body.appendChild(form);
            form.submit();
        });
    }

    // Đóng result modal khi click ngoài
    if (resultModal) {
        resultModal.addEventListener('click', function(e) {
            if (e.target === resultModal) resultModal.style.display = 'none';
        });
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
