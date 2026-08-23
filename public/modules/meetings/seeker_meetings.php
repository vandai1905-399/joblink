<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Lịch Phỏng Vấn & Cuộc Hẹn</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Danh sách lịch hẹn trao đổi trực tiếp hoặc online từ các Nhà tuyển dụng</p>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Vị trí công việc</th>
                    <th>Nhà tuyển dụng</th>
                    <th>Thời gian hẹn</th>
                    <th>Hình thức</th>
                    <th>Địa điểm / Link họp</th>
                    <th>Ghi chú</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($meetings)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--slate-400); padding: 2.5rem;">Chưa có cuộc hẹn phỏng vấn nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($meetings as $m): ?>
                        <tr style="vertical-align: middle;">
                            <td style="white-space: nowrap;"><strong><?= htmlspecialchars($m['job_title']) ?></strong></td>
                            <td style="white-space: nowrap; font-weight: 600; color: #0d9488;"><?= htmlspecialchars($m['employer_name']) ?></td>
                            <td style="white-space: nowrap;">
                                <strong style="color: #0f172a;"><?= date('d/m/Y', strtotime($m['meeting_date'])) ?></strong>
                                <span style="font-size: 0.8rem; color: #0d9488; font-weight: 600; margin-left: 0.25rem;">🕒 <?= substr($m['start_time'], 0, 5) ?><?= !empty($m['end_time']) ? ' - ' . substr($m['end_time'], 0, 5) : '' ?></span>
                            </td>
                            <td style="white-space: nowrap;">
                                <span class="badge <?= $m['meeting_format'] === 'online' ? 'badge-primary' : 'badge-slate' ?>" style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                    <?= $m['meeting_format'] === 'online' ? icon('globe', '', 12) . ' <span>Trực tuyến</span>' : icon('building', '', 12) . ' <span>Trực tiếp</span>' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($m['meeting_format'] === 'online'): ?>
                                    <a href="<?= htmlspecialchars($m['location_or_meeting_link']) ?>" target="_blank" style="color: #0d9488; word-break: break-all; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.85rem;">
                                        <?= icon('link', '', 13) ?> <span>Tham gia phòng họp</span>
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 0.85rem; color: #334155;"><?= htmlspecialchars($m['location_or_meeting_link']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--slate-600); max-width: 200px;"><?= htmlspecialchars($m['meeting_note'] ?? '—') ?></td>
                            <td style="white-space: nowrap; text-align: center;">
                                <?php if ($m['meeting_status'] === 'scheduled'): ?>
                                    <span class="badge badge-warning" style="font-size: 0.78rem;">Đã lên lịch</span>
                                <?php elseif ($m['meeting_status'] === 'completed'): ?>
                                    <span class="badge badge-success" style="font-size: 0.78rem;"><?= icon('check', '', 12) ?> Hoàn thành</span>
                                <?php elseif ($m['meeting_status'] === 'cancelled'): ?>
                                    <span class="badge badge-danger" style="font-size: 0.78rem;">Đã hủy</span>
                                <?php else: ?>
                                    <span class="badge badge-slate" style="font-size: 0.78rem;">Đã dời lịch</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
