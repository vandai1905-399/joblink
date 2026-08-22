<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Tin Nhắn Trực Tiếp Với Người Lao Động</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Trao đổi về ca làm việc, thời gian nhận việc và hướng dẫn</p>
</div>

<div class="card">
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <?php if (empty($messages)): ?>
            <p style="text-align: center; color: var(--slate-400); padding: 2rem;">Hộp thư trống.</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-sm); padding: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem;">
                        <strong><?= htmlspecialchars($msg['sender_name'] ?? 'Người lao động / Hệ thống') ?></strong>
                        <span style="font-size: 0.8rem; color: var(--slate-400);"><?= date('d/m/Y H:i', strtotime($msg['sent_at'])) ?></span>
                    </div>
                    <?php if (!empty($msg['message_title'])): ?>
                        <h4 style="color: var(--primary); font-size: 0.92rem; margin-bottom: 0.25rem;"><?= htmlspecialchars($msg['message_title']) ?></h4>
                    <?php endif; ?>
                    <p style="font-size: 0.92rem; color: var(--slate-700);"><?= nl2br(htmlspecialchars($msg['message_content'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
