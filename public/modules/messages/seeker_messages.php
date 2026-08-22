<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Tin Nhắn & Thông Báo</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Lịch sử trao đổi với Nhà tuyển dụng và thông báo từ hệ thống JobLink</p>
</div>

<div class="card" style="padding: 1.5rem;">
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <?php if (empty($messages)): ?>
            <p style="text-align: center; color: var(--slate-400); padding: 2rem;">Hộp thư của bạn hiện đang trống.</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-sm); padding: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <div>
                            <strong style="color: var(--dark); font-size: 0.95rem;">
                                <?= htmlspecialchars($msg['sender_name'] ?? 'Hệ Thống') ?>
                            </strong>
                            <?php if ($msg['message_type'] === 'notification'): ?>
                                <span class="badge badge-primary" style="margin-left: 0.5rem;">Thông báo hệ thống</span>
                            <?php endif; ?>
                        </div>
                        <span style="font-size: 0.8rem; color: var(--slate-400);">
                            <?= date('d/m/Y H:i', strtotime($msg['sent_at'])) ?>
                        </span>
                    </div>

                    <?php if (!empty($msg['message_title'])): ?>
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 0.35rem;">
                            <?= htmlspecialchars($msg['message_title']) ?>
                        </h4>
                    <?php endif; ?>

                    <p style="font-size: 0.92rem; color: var(--slate-700); line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($msg['message_content'])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
