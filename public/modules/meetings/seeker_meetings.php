<?php 
$acceptedMeetings = array_filter($meetings ?? [], function($m) {
    return ($m['request_status'] ?? '') === 'accepted';
});
ob_start(); 
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Lịch Phỏng Vấn & Cuộc Hẹn</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Danh sách lịch hẹn trao đổi trực tiếp hoặc online, cẩm nang chuẩn bị và thời gian phản hồi kết quả từ các Nhà tuyển dụng</p>
</div>

<!-- ========================================================================= -->
<!-- THÔNG BÁO PHỎNG VẤN THÀNH CÔNG & NGÀY ĐẦU TIÊN ĐI LÀM (FIRST DAY NOTICE)  -->
<!-- ========================================================================= -->
<?php if (!empty($acceptedMeetings)): ?>
    <div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 2rem;">
        <?php foreach ($acceptedMeetings as $acc): 
            $onb = !empty($acc['onboarding_details']) ? json_decode($acc['onboarding_details'], true) : null;
        ?>
            <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 2px solid #34d399; border-radius: 12px; padding: 1.5rem; box-shadow: 0 6px 18px rgba(16,185,129,0.12);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; border-bottom: 1px solid #a7f3d0; padding-bottom: 0.85rem;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span style="color: #059669; display: flex; align-items: center; justify-content: center;"><?= icon('award', '', 32) ?></span>
                        <div>
                            <span class="badge badge-success" style="font-size: 0.78rem; font-weight: 800; padding: 0.25rem 0.6rem; text-transform: uppercase;">
                                Tiến độ: 100% Phỏng Vấn Thành Công
                            </span>
                            <h3 style="margin: 0.35rem 0 0.15rem 0; font-size: 1.25rem; font-weight: 800; color: #065f46;">
                                Chúc Mừng Bạn Đã Trúng Tuyển: <?= htmlspecialchars($acc['job_title']) ?>
                            </h3>
                            <div style="font-size: 0.9rem; color: #047857; font-weight: 600; display: flex; align-items: center; gap: 0.3rem;">
                                <?= icon('building', '', 14) ?> Doanh nghiệp: 
                                <?php if (!empty($acc['employer_id'])): ?>
                                    <a href="<?= url('/employers/' . $acc['employer_id']) ?>" data-title="<?= htmlspecialchars($acc['employer_name']) ?>" style="color: #065f46; font-weight: 700; text-decoration: underline;" title="Xem trang cá nhân của doanh nghiệp">
                                        <?= htmlspecialchars($acc['employer_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="font-weight: 700;"><?= htmlspecialchars($acc['employer_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <a href="<?= url('/seeker/messages?partner_id=' . ($acc['employer_account_id'] ?? 0)) ?>" class="btn btn-outline btn-sm" style="color: #ffffff; border-color: #065f46; background: #065f46; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <?= icon('chat', '', 13) ?> Nhắn Tin NTD
                        </a>
                        <a href="<?= url('/seeker/applied-jobs') ?>" class="btn btn-primary btn-sm" style="font-weight: 700;">
                            Xem Chi Tiết Đơn
                        </a>
                    </div>
                </div>

                <!-- THẺ THÔNG TIN NGÀY ĐẦU ĐI LÀM -->
                <div style="background: #ffffff; border-radius: 10px; padding: 1.25rem; border: 1px solid #a7f3d0;">
                    <h4 style="margin: 0 0 0.75rem 0; font-size: 1rem; font-weight: 800; color: #065f46; display: flex; align-items: center; gap: 0.4rem;">
                        <?= icon('calendar', '', 16) ?> THÔNG BÁO NGÀY LÀM BỮA ĐẦU TIÊN & HÀNH TRANG CHUẨN BỊ:
                    </h4>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 0.85rem; font-size: 0.9rem; margin-bottom: 1rem; padding-bottom: 0.85rem; border-bottom: 1px dashed #cbd5e1;">
                        <div>
                            <span style="color: #64748b; font-size: 0.82rem; display: flex; align-items: center; gap: 0.25rem;"><?= icon('calendar', '', 13) ?> Ngày & Giờ Bắt Đầu:</span>
                            <strong style="color: #0f172a; font-size: 1rem;">
                                <?= !empty($onb['start_date']) ? date('d/m/Y', strtotime($onb['start_date'])) : 'Theo trao đổi trực tiếp' ?>
                            </strong>
                            <span style="color: #059669; font-weight: 700;">(lúc <?= htmlspecialchars($onb['start_time'] ?? '08:00') ?>)</span>
                        </div>
                        <div>
                            <span style="color: #64748b; font-size: 0.82rem; display: flex; align-items: center; gap: 0.25rem;"><?= icon('map_pin', '', 13) ?> Địa Điểm Tiếp Đón:</span>
                            <?php 
                                $accWorkplace = $onb['workplace'] ?? ($acc['workplace_address'] ?? 'Tại văn phòng công ty');
                                $accMapUrl = getGoogleMapsUrl($accWorkplace, $acc['workplace_map_url'] ?? '');
                            ?>
                            <a href="<?= htmlspecialchars($accMapUrl) ?>" target="_blank" rel="noopener noreferrer" style="color: #0d9488; font-weight: 700; text-decoration: underline; display: inline-flex; align-items: center; gap: 0.25rem;" title="Mở chỉ đường trên Google Maps">
                                <span><?= htmlspecialchars($accWorkplace) ?></span>
                                <svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            </a>
                        </div>
                        <div>
                            <span style="color: #64748b; font-size: 0.82rem; display: flex; align-items: center; gap: 0.25rem;"><?= icon('user', '', 13) ?> Người Đón Tiếp / Hỗ Trợ:</span>
                            <strong style="color: #0f172a;">
                                <?= htmlspecialchars($onb['contact_person'] ?? ($acc['employer_name'] ?? 'Phòng Nhân Sự')) ?>
                            </strong>
                            <?php if (!empty($acc['employer_phone'])): ?>
                                · <a href="tel:<?= htmlspecialchars($acc['employer_phone']) ?>" style="color: #0d9488; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.2rem;"><?= icon('phone', '', 13) ?> <?= htmlspecialchars($acc['employer_phone']) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- CHECKLIST HỒ SƠ & NOTE DẶN DÒ -->
                    <div style="font-size: 0.88rem; color: #334155;">
                        <strong style="color: #0f172a; display: flex; align-items: center; gap: 0.3rem; margin-bottom: 0.45rem;">
                            <?= icon('file_text', '', 14) ?> Những gì cần chuẩn bị cho buổi làm đầu tiên:
                        </strong>
                        <?php 
                            $chk = !empty($onb['checklist']) ? $onb['checklist'] : [
                                'Căn cước công dân (CCCD / VNeID) bản gốc & 01 bản photo',
                                'Sơ yếu lý lịch tự thuật & Giấy khám sức khỏe (trong 6 tháng)',
                                '02 ảnh thẻ 3x4 làm thẻ nhân viên & thủ tục công ty',
                                'Thông tin số tài khoản ngân hàng chính chủ để nhận lương',
                                'Trang phục: Quần dài lịch sự, áo có cổ, giày bít mũi'
                            ];
                        ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.4rem;">
                            <?php foreach ($chk as $ci): ?>
                                <label style="display: flex; align-items: center; gap: 0.45rem; background: #f8fafc; padding: 0.4rem 0.65rem; border-radius: 6px; border: 1px solid #f1f5f9; cursor: pointer;">
                                    <input type="checkbox" checked style="accent-color: #059669;">
                                    <span><?= htmlspecialchars($ci) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (!empty($onb['notes'])): ?>
                        <div style="margin-top: 0.85rem; background: #f0fdf4; border-left: 3px solid #059669; padding: 0.55rem 0.85rem; border-radius: 0 6px 6px 0; font-size: 0.85rem; color: #166534; display: flex; align-items: center; gap: 0.35rem;">
                            <?= icon('chat', '', 13) ?> <strong>Dặn dò thêm từ Nhà tuyển dụng:</strong> <?= htmlspecialchars($onb['notes']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Vị trí & NTD</th>
                    <th>Thời gian & Khoảng ngày</th>
                    <th>Chuẩn bị trước buổi PV</th>
                    <th>Hình thức & Địa điểm</th>
                    <th>Tiến độ & Kết quả</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($meetings)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--slate-400); padding: 2.5rem;">Chưa có cuộc hẹn phỏng vấn nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($meetings as $m): 
                        $mTime = strtotime($m['meeting_date']);
                        $today = strtotime(date('Y-m-d'));
                        $isCompleted = ($m['meeting_status'] === 'completed') || ($mTime < $today);
                        
                        $deadline = !empty($m['response_deadline']) ? strtotime($m['response_deadline']) : strtotime($m['meeting_date'] . ' +3 days 23:59:59');
                        $isOverdue = (time() > $deadline) && ($m['request_status'] === 'pending' || empty($m['request_status']));
                        $daysLeft = ceil(($deadline - time()) / 86400);
                        $daysToPrep = ceil(($mTime - $today) / 86400);
                        $mOnboarding = !empty($m['onboarding_details']) ? json_decode($m['onboarding_details'], true) : null;
                    ?>
                        <tr style="vertical-align: top;">
                            <td style="min-width: 190px;">
                                <strong style="color: #0f172a; font-size: 0.95rem; display: block; margin-bottom: 0.2rem;">
                                    <?= htmlspecialchars($m['job_title']) ?>
                                </strong>
                                <span style="font-weight: 600; color: #0d9488; font-size: 0.85rem; display: flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('building', '', 13) ?>
                                    <?php if (!empty($m['employer_id'])): ?>
                                        <a href="<?= url('/employers/' . $m['employer_id']) ?>" data-title="<?= htmlspecialchars($m['employer_name']) ?>" style="color: #0d9488; font-weight: 700; text-decoration: underline;" title="Xem trang cá nhân của doanh nghiệp">
                                            <?= htmlspecialchars($m['employer_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span><?= htmlspecialchars($m['employer_name']) ?></span>
                                    <?php endif; ?>
                                </span>
                                <?php if (!empty($m['employer_phone'])): ?>
                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.2rem; display: flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('phone', '', 12) ?> <?= htmlspecialchars($m['employer_phone']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td style="min-width: 170px;">
                                <div style="color: #0f172a; font-weight: 700;">
                                    <?= date('d/m/Y', strtotime($m['meeting_date'])) ?>
                                </div>
                                <div style="font-size: 0.82rem; color: #0d9488; font-weight: 700; margin-top: 0.15rem; display: flex; align-items: center; gap: 0.25rem;">
                                    <?= icon('clock', '', 13) ?> <?= substr($m['start_time'], 0, 5) ?><?= !empty($m['end_time']) ? ' - ' . substr($m['end_time'], 0, 5) : '' ?>
                                </div>
                                <?php if (!empty($m['interview_range'])): ?>
                                    <div style="font-size: 0.75rem; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; padding: 0.2rem 0.4rem; border-radius: 4px; margin-top: 0.35rem; display: flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('calendar', '', 12) ?> Đợt PV: <?= htmlspecialchars($m['interview_range']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!$isCompleted && $daysToPrep >= 0): ?>
                                    <div style="font-size: 0.75rem; color: #b45309; font-weight: 700; margin-top: 0.25rem;">
                                        <?= ($daysToPrep === 0) ? 'Diễn ra hôm nay!' : "Còn $daysToPrep ngày chuẩn bị" ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td style="min-width: 200px;">
                                <?php if (!empty($m['prep_guide'])): ?>
                                    <div style="background: #fefce8; border: 1px solid #fef08a; padding: 0.45rem 0.65rem; border-radius: 6px; font-size: 0.8rem; color: #78350f;">
                                        <?= nl2br(htmlspecialchars($m['prep_guide'])) ?>
                                    </div>
                                <?php else: ?>
                                    <div style="font-size: 0.8rem; color: #64748b;">
                                        • Mang theo CCCD/VNeID & CV bản in<br>
                                        • Trang phục gọn gàng, đến trước 10 phút
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($m['meeting_note'])): ?>
                                    <div style="font-size: 0.75rem; color: #475569; margin-top: 0.3rem; display: flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('file_text', '', 12) ?> <em><?= htmlspecialchars($m['meeting_note']) ?></em>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td style="min-width: 170px;">
                                <div style="margin-bottom: 0.35rem;">
                                    <span class="badge <?= $m['meeting_format'] === 'online' ? 'badge-primary' : 'badge-slate' ?>" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                        <?= $m['meeting_format'] === 'online' ? icon('globe', '', 12) . ' <span>Trực tuyến (Online)</span>' : icon('building', '', 12) . ' <span>Trực tiếp</span>' ?>
                                    </span>
                                </div>
                                <?php if ($m['meeting_format'] === 'online'): ?>
                                    <a href="<?= htmlspecialchars($m['location_or_meeting_link']) ?>" target="_blank" style="color: #0d9488; word-break: break-all; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.82rem;">
                                        <?= icon('link', '', 13) ?> <span>Vào phòng họp</span>
                                    </a>
                                <?php else: ?>
                                    <?php 
                                        $locText = $m['location_or_meeting_link'] ?: ($m['workplace_address'] ?? 'Tại văn phòng');
                                        $locMapUrl = getGoogleMapsUrl($locText, $m['workplace_map_url'] ?? '');
                                    ?>
                                    <a href="<?= htmlspecialchars($locMapUrl) ?>" target="_blank" rel="noopener noreferrer" style="font-size: 0.82rem; color: #0d9488; text-decoration: underline; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;" title="Mở vị trí trên Google Maps">
                                        <span><?= htmlspecialchars($locText) ?></span>
                                        <svg class="icon-svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    </a>
                                <?php endif; ?>
                            </td>

                            <td style="min-width: 180px;">
                                <?php if ($m['request_status'] === 'accepted'): ?>
                                    <span class="badge badge-success" style="font-size: 0.85rem; font-weight: 800; padding: 0.35rem 0.65rem;">
                                        100% Trúng Tuyển
                                    </span>
                                    <div style="font-size: 0.78rem; color: #059669; font-weight: 700; margin-top: 0.35rem; display: flex; align-items: center; gap: 0.25rem;">
                                        <?= icon('calendar', '', 12) ?> Ngày đi làm: <?= !empty($mOnboarding['start_date']) ? date('d/m/Y', strtotime($mOnboarding['start_date'])) : 'Theo trao đổi' ?> (<?= htmlspecialchars($mOnboarding['start_time'] ?? '08:00') ?>)
                                    </div>
                                    <?php 
                                        $workLoc = $mOnboarding['workplace'] ?? ($m['workplace_address'] ?? '');
                                        $workLocMap = getGoogleMapsUrl($workLoc, $m['workplace_map_url'] ?? '');
                                    ?>
                                    <?php if (!empty($workLoc)): ?>
                                        <div style="font-size: 0.74rem; color: #64748b; margin-top: 0.15rem; display: flex; align-items: center; gap: 0.25rem;">
                                            <?= icon('map_pin', '', 12) ?> 
                                            <a href="<?= htmlspecialchars($workLocMap) ?>" target="_blank" rel="noopener noreferrer" style="color: #0d9488; text-decoration: underline;" title="Mở vị trí trên Google Maps">
                                                <?= htmlspecialchars($workLoc) ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($m['request_status'] === 'rejected'): ?>
                                    <span class="badge badge-danger" style="font-size: 0.78rem;">
                                        Chưa phù hợp (Hẹn dịp sau)
                                    </span>
                                <?php elseif ($isCompleted): ?>
                                    <span class="badge badge-primary" style="font-size: 0.78rem;">
                                        75% Phỏng Vấn Xong
                                    </span>
                                    <?php if ($isOverdue): ?>
                                        <div style="font-size: 0.75rem; color: #ea580c; font-weight: 700; margin-top: 0.25rem;">
                                            Quá hạn phản hồi (3 ngày)
                                        </div>
                                    <?php else: ?>
                                        <div style="font-size: 0.75rem; color: #0d9488; margin-top: 0.25rem;">
                                            Chờ kết quả (còn <?= $daysLeft ?> ngày)
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($m['meeting_status'] === 'rescheduled'): ?>
                                    <span class="badge badge-warning" style="font-size: 0.78rem;">Đang xin đổi lịch</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.78rem;">Đã lên lịch (50%)</span>
                                <?php endif; ?>
                            </td>

                            <td style="white-space: nowrap;">
                                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                    <?php if ($m['request_status'] === 'accepted'): ?>
                                        <button type="button" class="btn btn-success btn-sm btn-open-seeker-onboarding"
                                                data-job="<?= htmlspecialchars($m['job_title']) ?>"
                                                data-emp="<?= htmlspecialchars($m['employer_name']) ?>"
                                                data-empid="<?= $m['employer_id'] ?? '' ?>"
                                                data-date="<?= !empty($mOnboarding['start_date']) ? date('d/m/Y', strtotime($mOnboarding['start_date'])) : 'Theo trao đổi trực tiếp' ?>"
                                                data-time="<?= htmlspecialchars($mOnboarding['start_time'] ?? '08:00') ?>"
                                                data-workplace="<?= htmlspecialchars($mOnboarding['workplace'] ?? ($m['workplace_address'] ?? 'Tại văn phòng công ty')) ?>"
                                                data-mapurl="<?= htmlspecialchars($m['workplace_map_url'] ?? '') ?>"
                                                data-contact="<?= htmlspecialchars($mOnboarding['contact_person'] ?? ($m['employer_name'] ?? 'Phòng Nhân Sự')) ?>"
                                                data-phone="<?= htmlspecialchars($m['employer_phone'] ?? '') ?>"
                                                data-notes="<?= htmlspecialchars($mOnboarding['notes'] ?? '') ?>"
                                                data-checklist="<?= htmlspecialchars(json_encode($mOnboarding['checklist'] ?? [])) ?>"
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem; font-weight: 700; background: #059669; border-color: #059669; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <?= icon('file_text', '', 12) ?> Cẩm Nang Ngày Đầu
                                        </button>
                                        <a href="<?= url('/seeker/messages?partner_id=' . ($m['employer_account_id'] ?? 0)) ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <?= icon('chat', '', 12) ?> Nhắn Tin NTD
                                        </a>
                                    <?php elseif ($isCompleted && $isOverdue && ($m['request_status'] === 'pending' || empty($m['request_status']))): ?>
                                        <form action="<?= url('/seeker/applications/remind/' . $m['request_id']) ?>" method="POST" style="margin:0;">
                                            <button type="submit" class="btn btn-warning btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                <?= icon('bell', '', 12) ?> Nhắc Phản Hồi
                                            </button>
                                        </form>
                                    <?php elseif (!$isCompleted && $m['meeting_status'] === 'scheduled'): ?>
                                        <button type="button" class="btn btn-outline btn-sm btn-open-reschedule" 
                                                data-meeting-id="<?= $m['meeting_id'] ?>"
                                                data-job-title="<?= htmlspecialchars($m['job_title']) ?>"
                                                data-range="<?= htmlspecialchars($m['interview_range'] ?? '') ?>"
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #b45309; border-color: #fcd34d;">
                                            Đổi Lịch Hẹn
                                        </button>
                                    <?php endif; ?>

                                    <a href="<?= url('/seeker/applied-jobs') ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                        Xem Tiến Độ
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL ĐỔI LỊCH PHỎNG VẤN -->
<div id="rescheduleModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 12px; max-width: 480px; width: 100%; padding: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('calendar', '', 18) ?> Đề Xuất Đổi Lịch Phỏng Vấn
            </h3>
            <button type="button" id="closeRescheduleBtn" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <form id="rescheduleForm" action="" method="POST">
            <div style="margin-bottom: 0.75rem; font-size: 0.88rem; color: #475569;">
                Vị trí: <strong id="reschedJobTitle" style="color: #0d9488;"></strong>
            </div>

            <div id="reschedRangeAlert" style="display: none; background: #f0fdfa; border: 1px solid #99f6e4; padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.82rem; color: #0f766e; margin-bottom: 0.85rem;">
                <span style="display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('calendar', '', 13) ?> <strong>Khoảng ngày phỏng vấn của đợt này:</strong></span> <span id="reschedRangeText"></span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Ngày mới mong muốn *</label>
                    <input type="date" name="proposed_date" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Giờ đề xuất *</label>
                    <input type="time" name="proposed_time" value="09:00" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.25rem;">Lý do đề xuất dời lịch *</label>
                <textarea name="reschedule_reason" rows="2" class="form-control" required placeholder="Ví dụ: Em bị trùng lịch bận đột xuất, xin phép dời sang ngày trên..." style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" id="cancelRescheduleBtn" class="btn btn-outline btn-sm">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700;">Gửi Đề Xuất Đến NTD</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL XEM CHI TIẾT CẨM NANG NGÀY ĐẦU ĐI LÀM -->
<div id="seekerOnboardingModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 14px; max-width: 580px; width: 100%; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); max-height: 90vh; display: flex; flex-direction: column;">
        <div style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%); color: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 0.35rem;" id="sobModalJobTitle">
                    <?= icon('award', '', 20) ?> Thông Báo Ngày Đầu Đi Làm
                </h3>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #d1fae5;" id="sobModalEmployer">
                    Doanh nghiệp tuyển dụng
                </p>
            </div>
            <button type="button" onclick="document.getElementById('seekerOnboardingModal').style.display='none'" style="background: none; border: none; color: #ffffff; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <div style="padding: 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem;">
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.85rem 1rem; font-size: 0.88rem; color: #166534;">
                Chúc mừng bạn đã hoàn thành xuất sắc buổi phỏng vấn và trúng tuyển! Dưới đây là thông tin đón tiếp và danh sách hành trang chuẩn bị.
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.9rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem;">
                <div>
                    <span style="color: #64748b; font-size: 0.8rem; display: flex; align-items: center; gap: 0.25rem;"><?= icon('calendar', '', 12) ?> Ngày giờ có mặt:</span>
                    <strong style="color: #0f172a;" id="sobModalDateTime"></strong>
                </div>
                <div>
                    <span style="color: #64748b; font-size: 0.8rem; display: flex; align-items: center; gap: 0.25rem;"><?= icon('map_pin', '', 12) ?> Địa điểm đón tiếp:</span>
                    <strong style="color: #0f172a;" id="sobModalWorkplace"></strong>
                </div>
                <div style="grid-column: 1 / -1;">
                    <span style="color: #64748b; font-size: 0.8rem; display: flex; align-items: center; gap: 0.25rem;"><?= icon('user', '', 12) ?> Người tiếp đón & Liên hệ:</span>
                    <strong style="color: #0f172a;" id="sobModalContact"></strong>
                </div>
            </div>

            <div>
                <strong style="color: #0f172a; font-size: 0.9rem; display: flex; align-items: center; gap: 0.3rem; margin-bottom: 0.45rem;">
                    <?= icon('file_text', '', 14) ?> Danh sách hồ sơ & trang phục cần mang theo:
                </strong>
                <div id="sobModalChecklist" style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.85rem;">
                </div>
            </div>

            <div id="sobModalNotesBox" style="display: none; background: #fffbeb; border-left: 3px solid #f59e0b; padding: 0.65rem 0.85rem; border-radius: 0 6px 6px 0; font-size: 0.85rem; color: #92400e;">
                <span style="display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('chat', '', 12) ?> <strong>Dặn dò thêm:</strong></span> <span id="sobModalNotes"></span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('seekerOnboardingModal').style.display='none'">Đóng</button>
                <button type="button" class="btn btn-success" style="font-weight: 700; background: #059669; border-color: #059669;" onclick="alert('Chúc mừng bạn! Chúc bạn có một khởi đầu công việc thật thuận lợi và thành công!'); document.getElementById('seekerOnboardingModal').style.display='none';">
                    Tôi Đã Nắm Rõ & Sẵn Sàng Đi Làm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('rescheduleModal');
    const closeBtn = document.getElementById('closeRescheduleBtn');
    const cancelBtn = document.getElementById('cancelRescheduleBtn');
    const form = document.getElementById('rescheduleForm');
    const btns = document.querySelectorAll('.btn-open-reschedule');

    btns.forEach(btn => {
        btn.addEventListener('click', function() {
            const meetingId = this.getAttribute('data-meeting-id');
            const jobTitle = this.getAttribute('data-job-title');
            const range = this.getAttribute('data-range');

            form.action = '<?= url('/seeker/meetings/reschedule/') ?>' + meetingId;
            document.getElementById('reschedJobTitle').textContent = jobTitle;

            const rangeBox = document.getElementById('reschedRangeAlert');
            if (range && range.trim() !== '') {
                document.getElementById('reschedRangeText').textContent = range;
                rangeBox.style.display = 'block';
            } else {
                rangeBox.style.display = 'none';
            }

            modal.style.display = 'flex';
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', () => modal.style.display = 'none');
    if (cancelBtn) cancelBtn.addEventListener('click', () => modal.style.display = 'none');

    // Xử lý modal cẩm nang ngày đầu đi làm
    const sobModal = document.getElementById('seekerOnboardingModal');
    const sobBtns = document.querySelectorAll('.btn-open-seeker-onboarding');

    sobBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const job = this.getAttribute('data-job');
            const emp = this.getAttribute('data-emp');
            const date = this.getAttribute('data-date');
            const time = this.getAttribute('data-time');
            const workplace = this.getAttribute('data-workplace');
            const contact = this.getAttribute('data-contact');
            const phone = this.getAttribute('data-phone');
            const notes = this.getAttribute('data-notes');
            let checklist = [];
            try {
                checklist = JSON.parse(this.getAttribute('data-checklist') || '[]');
            } catch (e) {
                checklist = [];
            }

            if (!checklist || checklist.length === 0) {
                checklist = [
                    'Căn cước công dân (CCCD / VNeID) bản gốc & 01 bản photo',
                    'Sơ yếu lý lịch tự thuật & Giấy khám sức khỏe (trong 6 tháng)',
                    '02 ảnh thẻ 3x4 làm thẻ nhân viên & thủ tục công ty',
                    'Thông tin số tài khoản ngân hàng chính chủ để nhận lương',
                    'Trang phục: Quần dài lịch sự, áo có cổ, giày bít mũi'
                ];
            }

            const empId = this.getAttribute('data-empid');
            const mapUrl = this.getAttribute('data-mapurl') || ('https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(workplace));

            document.getElementById('sobModalJobTitle').innerHTML = '<span style="display:inline-flex;align-items:center;gap:0.35rem;"><?= icon('award', '', 20) ?> ' + job + '</span>';
            const empLink = empId 
                ? '<a href="<?= url('/employers/') ?>/' + empId + '" data-title="' + emp + '" style="color: #ffffff; font-weight: 700; text-decoration: underline;" title="Xem trang doanh nghiệp">' + emp + '</a>' 
                : emp;
            document.getElementById('sobModalEmployer').innerHTML = 'Doanh nghiệp: ' + empLink;
            document.getElementById('sobModalDateTime').textContent = date + ' (lúc ' + time + ')';
            document.getElementById('sobModalWorkplace').innerHTML = '<a href="' + mapUrl + '" target="_blank" rel="noopener noreferrer" style="color: #0d9488; text-decoration: underline;" title="Mở trên Google Maps">' + workplace + '</a>';
            document.getElementById('sobModalContact').textContent = contact + (phone ? ' · SĐT: ' + phone : '');

            const chkContainer = document.getElementById('sobModalChecklist');
            chkContainer.innerHTML = '';
            checklist.forEach(item => {
                const lbl = document.createElement('label');
                lbl.style.cssText = 'display: flex; align-items: center; gap: 0.45rem; background: #f8fafc; padding: 0.4rem 0.65rem; border-radius: 6px; border: 1px solid #f1f5f9; cursor: pointer;';
                lbl.innerHTML = '<input type="checkbox" checked style="accent-color: #059669;"> <span>' + item + '</span>';
                chkContainer.appendChild(lbl);
            });

            const notesBox = document.getElementById('sobModalNotesBox');
            if (notes && notes.trim() !== '') {
                document.getElementById('sobModalNotes').textContent = notes;
                notesBox.style.display = 'block';
            } else {
                notesBox.style.display = 'none';
            }

            sobModal.style.display = 'flex';
        });
    });
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
