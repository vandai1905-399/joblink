<?php ob_start(); ?>

<div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Tin Nhắn Trực Tiếp & Chốt Lịch Phỏng Vấn</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Trao đổi 1-1 với người lao động và sử dụng AI để đối chiếu khung giờ rảnh chống trùng lịch</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
    <!-- CỘT 1: SOẠN TIN NHẮN & CÔNG CỤ AI GỢI Ý LỊCH -->
    <div class="card">
        <div style="margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('chat', '', 18) ?> Soạn Tin Nhắn Trao Đổi
            </h3>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.82rem; color: #64748b;">
                Gửi tin nhắn hoặc đề xuất khung giờ phỏng vấn đến người lao động
            </p>
        </div>

        <form action="<?= url('/employer/messages/send') ?>" method="POST" id="empMsgForm">
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">Người nhận (Người lao động): *</label>
                <select name="receiver_account_id" id="receiverSelect" class="form-control" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
                    <option value="">-- Chọn người lao động đã kết nối / ứng tuyển --</option>
                    <?php foreach ($connectedCandidates as $cand): ?>
                        <option value="<?= $cand['account_id'] ?>" data-seeker-id="<?= $cand['seeker_id'] ?>" data-request-id="<?= $cand['request_id'] ?>">
                            <?= htmlspecialchars($cand['full_name']) ?> (Vị trí: <?= htmlspecialchars($cand['job_title']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- NÚT GỌI AI GỢI Ý LỊCH RẢNH -->
            <div id="aiSlotBox" style="margin-bottom: 1rem; display: none; background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 0.85rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: #0f766e; display: flex; align-items: center; gap: 0.3rem;">
                        <?= icon('sparkle', '', 14) ?> Khung giờ AI gợi ý rảnh cho cả 2 bên:
                    </span>
                    <span id="slotLoadingText" style="font-size: 0.78rem; color: #64748b; display: none;">Đang quét lịch...</span>
                </div>
                <div id="quickSlotsList" style="display: flex; flex-direction: column; gap: 0.35rem;"></div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">Tiêu đề tin nhắn:</label>
                <input type="text" name="message_title" id="msgTitleInput" class="form-control" placeholder="Ví dụ: Lời mời phỏng vấn & Hướng dẫn nhận việc..." style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">Nội dung tin nhắn: *</label>
                <textarea name="message_content" id="msgContentInput" rows="5" class="form-control" required placeholder="Nhập nội dung trao đổi, địa chỉ phỏng vấn, hồ sơ cần mang theo..." style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.88rem;"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 700; padding: 0.65rem;">
                Gửi Tin Nhắn
            </button>
        </form>
    </div>

    <!-- CỘT 2: LỊCH SỬ HỘP THƯ TIN NHẮN NHẬN ĐƯỢC -->
    <div class="card">
        <div style="margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('inbox', '', 18) ?> Hộp Thư Phản Hồi (<?= count($messages) ?>)
            </h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem; max-height: 600px; overflow-y: auto;">
            <?php if (empty($messages)): ?>
                <p style="text-align: center; color: var(--slate-400); padding: 2rem;">Hộp thư trống.</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem;">
                            <strong style="color: #0f172a; font-size: 0.92rem;"><?= htmlspecialchars($msg['sender_name'] ?? 'Người lao động / Hệ thống') ?></strong>
                            <span style="font-size: 0.78rem; color: #94a3b8;"><?= date('d/m/Y H:i', strtotime($msg['sent_at'])) ?></span>
                        </div>
                        <?php if (!empty($msg['message_title'])): ?>
                            <h4 style="color: #0d9488; font-size: 0.9rem; margin: 0.2rem 0;"><?= htmlspecialchars($msg['message_title']) ?></h4>
                        <?php endif; ?>
                        <p style="font-size: 0.88rem; color: #334155; margin: 0.35rem 0 0 0; line-height: 1.5; white-space: pre-wrap;"><?= htmlspecialchars($msg['message_content']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const receiverSelect = document.getElementById('receiverSelect');
    const aiSlotBox = document.getElementById('aiSlotBox');
    const quickSlotsList = document.getElementById('quickSlotsList');
    const slotLoadingText = document.getElementById('slotLoadingText');
    const msgContentInput = document.getElementById('msgContentInput');
    const msgTitleInput = document.getElementById('msgTitleInput');

    receiverSelect.addEventListener('change', function() {
        const selectedOpt = this.options[this.selectedIndex];
        const seekerId = selectedOpt.getAttribute('data-seeker-id');
        const requestId = selectedOpt.getAttribute('data-request-id');

        if (!seekerId) {
            aiSlotBox.style.display = 'none';
            return;
        }

        aiSlotBox.style.display = 'block';
        slotLoadingText.style.display = 'inline';
        quickSlotsList.innerHTML = '';

        fetch('<?= url('/api/ai/suggest-meeting-slots') ?>?seeker_id=' + seekerId + '&request_id=' + (requestId || 0))
            .then(res => res.json())
            .then(data => {
                slotLoadingText.style.display = 'none';
                if (data.slots && data.slots.length > 0) {
                    data.slots.forEach(slot => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.style.cssText = 'text-align: left; background: #fff; border: 1px solid #99f6e4; padding: 0.4rem 0.6rem; border-radius: 4px; font-size: 0.8rem; cursor: pointer; color: #0f172a; transition: all 0.2s;';
                        btn.innerHTML = `<strong>${slot.label}</strong> <span style="color:#059669; font-size:0.75rem;">(${slot.ai_reason})</span>`;
                        btn.addEventListener('click', function() {
                            msgTitleInput.value = 'Đề xuất lịch phỏng vấn: ' + slot.day_title;
                            msgContentInput.value = `Chào bạn,\n\nCông ty trân trọng mời bạn tham gia phỏng vấn vào khung giờ sau:\nThời gian: ${slot.label}\nĐịa điểm: Văn phòng Công ty\n\nBạn vui lòng phản hồi xem khung giờ này có thuận tiện với bạn không nhé!`;
                        });
                        quickSlotsList.appendChild(btn);
                    });
                } else {
                    quickSlotsList.innerHTML = '<span style="font-size:0.8rem; color:#64748b;">Không có khung giờ rảnh đối chiếu tự động. Bạn có thể tự nhập thời gian mong muốn.</span>';
                }
            })
            .catch(err => {
                slotLoadingText.style.display = 'none';
                quickSlotsList.innerHTML = '<span style="font-size:0.8rem; color:#64748b;">Không thể tải gợi ý AI lúc này.</span>';
            });
    });
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
