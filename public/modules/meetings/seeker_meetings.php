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
                    <tr><td colspan="7" style="text-align: center; color: var(--slate-400); padding: 2rem;">Chưa có cuộc hẹn phỏng vấn nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($meetings as $m): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($m['job_title']) ?></strong></td>
                            <td><?= htmlspecialchars($m['employer_name']) ?></td>
                            <td>
                                <strong><?= date('d/m/Y', strtotime($m['meeting_date'])) ?></strong><br>
                                <span style="font-size: 0.85rem; color: var(--slate-500);"><?= substr($m['start_time'], 0, 5) ?> - <?= substr($m['end_time'] ?? '', 0, 5) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $m['meeting_format'] === 'online' ? 'badge-primary' : 'badge-slate' ?>" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <?= $m['meeting_format'] === 'online' ? icon('globe', '', 12) . ' <span>Trực tuyến</span>' : icon('building', '', 12) . ' <span>Trực tiếp</span>' ?>
                                </span>
                            </td>
                            <td style="max-width: 250px;">
                                <?php if ($m['meeting_format'] === 'online'): ?>
                                    <a href="<?= htmlspecialchars($m['location_or_meeting_link']) ?>" target="_blank" style="color: var(--primary); word-break: break-all; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <?= icon('link', '', 13) ?> <span>Tham gia Meet/Zoom</span>
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 0.88rem;"><?= htmlspecialchars($m['location_or_meeting_link']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--slate-600);"><?= htmlspecialchars($m['meeting_note'] ?? '—') ?></td>
                            <td>
                                <?php if ($m['meeting_status'] === 'scheduled'): ?>
                                    <span class="badge badge-warning">Đã lên lịch</span>
                                <?php elseif ($m['meeting_status'] === 'completed'): ?>
                                    <span class="badge badge-success"><?= icon('check', '', 12) ?> Hoàn thành</span>
                                <?php elseif ($m['meeting_status'] === 'cancelled'): ?>
                                    <span class="badge badge-danger">Đã hủy</span>
                                <?php else: ?>
                                    <span class="badge badge-slate">Đã dời lịch</span>
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
