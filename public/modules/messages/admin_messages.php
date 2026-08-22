<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Gửi Thông Báo Hệ Thống</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Phát đi các thông báo chính sách, bảo trì hoặc cảnh báo vi phạm tới người dùng</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; align-items: start;">
    <!-- Form gửi thông báo -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem;">Soạn Thông Báo Mới</h3>
        <form action="/admin/messages/broadcast" method="POST">
            <div class="form-group">
                <label class="form-label">Gửi Đến Tài Khoản *</label>
                <select name="receiver_account_id" class="form-control" required>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['account_id'] ?>">
                            [<?= formatId('account', $acc['account_id']) ?>] <?= htmlspecialchars($acc['username']) ?> (<?= htmlspecialchars($acc['email']) ?> - <?= $acc['role_name'] ?? $acc['role_code'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tiêu Đề Thông Báo *</label>
                <input type="text" name="message_title" class="form-control" required placeholder="VD: Duyệt tin tuyển dụng / Nhắc nhở quy định">
            </div>

            <div class="form-group">
                <label class="form-label">Nội Dung Thông Báo *</label>
                <textarea name="message_content" class="form-control" rows="4" required placeholder="Nội dung chi tiết thông báo..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Gửi Thông Báo Hệ Thống
            </button>
        </form>
    </div>

    <!-- Danh sách thông báo đã nhận/gửi -->
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem;">Lịch Sử Tin Nhắn & Thông Báo Gần Đây</h3>
        <?php if (empty($messages)): ?>
            <p style="color: var(--slate-400); text-align: center; padding: 2rem;">Chưa có thông báo nào.</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <div style="border-bottom: 1px solid var(--slate-100); padding-bottom: 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                        <strong><?= htmlspecialchars($msg['sender_name'] ?? 'Hệ Thống') ?></strong>
                        <span style="font-size: 0.8rem; color: var(--slate-400);"><?= date('d/m/Y H:i', strtotime($msg['sent_at'])) ?></span>
                    </div>
                    <?php if (!empty($msg['message_title'])): ?>
                        <div style="font-weight: 700; color: var(--primary); font-size: 0.92rem;"><?= htmlspecialchars($msg['message_title']) ?></div>
                    <?php endif; ?>
                    <p style="font-size: 0.9rem; color: var(--slate-700); margin-top: 0.25rem;"><?= nl2br(htmlspecialchars($msg['message_content'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php';
?>
