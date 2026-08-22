<?php 
$extraCss = ['css/jobs/ai_matching.css'];
ob_start(); 
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?= url('/employer/applications') ?>" style="color: var(--primary); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.95rem;">
        &larr; Quay lại danh sách hồ sơ ứng tuyển
    </a>
</div>

<!-- THẺ TỔNG QUAN HỒ SƠ NGƯỜI LAO ĐỘNG -->
<div class="card" style="margin-bottom: 2rem; border-left: 4px solid var(--primary); background: #ffffff;">
    <div style="display: flex; gap: 1.75rem; align-items: center; flex-wrap: wrap;">
        <img src="<?= htmlspecialchars(!empty($candidate['avatar_url']) ? $candidate['avatar_url'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150') ?>" alt="avatar" style="width: 110px; height: 110px; border-radius: var(--radius); object-fit: cover; border: 2px solid var(--slate-200); box-shadow: var(--shadow-sm);">
        <div style="flex: 1; min-width: 280px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <h1 style="font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem;">
                        <?= htmlspecialchars($candidate['full_name'] ?? 'Người lao động') ?>
                    </h1>
                    <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; margin-bottom: 0.75rem;">
                        <span class="badge badge-primary">Mã NLĐ: <?= formatId('seeker', $candidate['seeker_id']) ?></span>
                        <span class="badge badge-warning"><?= icon('star_fill', '', 12) ?> <?= $candidate['avg_rating'] ?? '5.0' ?> / 5.0 (<?= count($reviews) ?> lượt đánh giá)</span>
                        <span class="badge badge-slate">Giới tính: <?= htmlspecialchars($candidate['gender'] ?? 'Khác') ?></span>
                        <?php if (!empty($candidate['date_of_birth'])): ?>
                            <span class="badge badge-slate" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                <?= icon('cake', '', 12) ?> <span>Sinh: <?= date('d/m/Y', strtotime($candidate['date_of_birth'])) ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Các nút tác vụ nhanh -->
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="<?= url('/employer/meetings') ?>" class="btn btn-primary btn-sm" style="font-weight: 700;">
                        <?= icon('meetings', '', 15) ?> <span style="margin-left: 4px;">Lên Lịch Hẹn / PV</span>
                    </a>
                    <a href="<?= url('/employer/candidate-reviews') ?>" class="btn btn-outline btn-sm" style="font-weight: 700; color: #0d9488; border-color: #0d9488;">
                        <?= icon('reviews', '', 15) ?> <span style="margin-left: 4px;">Đánh Giá NLĐ</span>
                    </a>
                    <a href="<?= url('/employer/messages') ?>" class="btn btn-outline btn-sm" style="font-weight: 600;">
                        <?= icon('chat', '', 15) ?> <span style="margin-left: 4px;">Nhắn Tin</span>
                    </a>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; margin-top: 0.5rem; padding-top: 0.75rem; border-top: 1px dashed var(--slate-200); font-size: 0.9rem;">
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <span style="color: var(--slate-500); display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('phone', '', 14) ?> Hotline / SĐT:</span>
                    <strong style="color: #0f172a; margin-left: 0.35rem;"><?= htmlspecialchars($candidate['phone_number'] ?? 'Chưa cập nhật') ?></strong>
                </div>
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <span style="color: var(--slate-500); display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('mail', '', 14) ?> Email:</span>
                    <strong style="color: #0f172a; margin-left: 0.35rem;"><?= htmlspecialchars($candidate['email'] ?? 'Chưa cập nhật') ?></strong>
                </div>
                <div style="grid-column: 1 / -1; display: flex; align-items: center; gap: 0.35rem;">
                    <span style="color: var(--slate-500); display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('location', '', 14) ?> Địa chỉ cư trú:</span>
                    <strong style="color: #0f172a; margin-left: 0.35rem;"><?= htmlspecialchars($candidate['address'] ?? 'Thủ Dầu Một, Bình Dương') ?></strong>
                </div>
            </div>

            <!-- PHẦN THÔNG TIN NĂNG LỰC & KỸ NĂNG CỦA NGƯỜI LAO ĐỘNG -->
            <?php if (!empty($candidate['skills']) || !empty($candidate['desired_position']) || !empty($candidate['bio'])): ?>
                <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9; font-size: 0.88rem;">
                    <?php if (!empty($candidate['desired_position'])): ?>
                        <div style="margin-bottom: 0.35rem;">
                            <strong style="color: #0f766e;">🎯 Vị trí mong muốn:</strong> <span><?= htmlspecialchars($candidate['desired_position']) ?></span>
                            <?php if (!empty($candidate['experience_years'])): ?>
                                • <span>Kinh nghiệm: <strong><?= intval($candidate['experience_years']) ?> năm</strong></span>
                            <?php endif; ?>
                            <?php if (!empty($candidate['education_level'])): ?>
                                • <span>Học vấn: <strong><?= htmlspecialchars($candidate['education_level']) ?></strong></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($candidate['skills'])): ?>
                        <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 0.35rem;">
                            <strong style="color: #0f766e;">✨ Kỹ năng:</strong>
                            <?php 
                                $skillsArr = array_filter(array_map('trim', explode(',', $candidate['skills'])));
                                foreach ($skillsArr as $sk): 
                            ?>
                                <span class="ai-skill-tag matched" style="font-size: 0.75rem;"><?= htmlspecialchars($sk) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($candidate['bio'])): ?>
                        <p style="margin: 0.35rem 0 0 0; color: #475569; font-style: italic; font-size: 0.85rem; line-height: 1.5;">
                            "<?= htmlspecialchars($candidate['bio']) ?>"
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BỐ CỤC 2 CỘT: ĐƠN ỨNG TUYỂN & LỊCH SỬ ĐÁNH GIÁ NĂNG LỰC -->
<div style="display: grid; grid-template-columns: 1.1fr 1fr; gap: 2rem; align-items: start;">
    
    <!-- CỘT 1: CÁC ĐƠN ỨNG TUYỂN TẠI DOANH NGHIỆP NÀY -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <?= icon('posts', '', 18) ?>
                <span>Đơn Ứng Tuyển Tại Công Ty</span>
                <span class="badge badge-primary" style="font-size: 0.78rem;"><?= count($applications) ?> vị trí</span>
            </h2>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php if (empty($applications)): ?>
                <div class="card" style="text-align: center; padding: 2rem; color: var(--slate-500); background: #f8fafc;">
                    Người lao động chưa có đơn ứng tuyển nào trực tiếp tại các tin đăng của doanh nghiệp bạn.
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <div class="card" style="border: 1px solid #cbd5e1; border-radius: 10px; padding: 1.25rem; background: #ffffff; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.75rem;">
                            <div>
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 0.25rem;">
                                    <?= htmlspecialchars($app['job_title']) ?>
                                </h3>
                                <p style="font-size: 0.85rem; color: var(--slate-500); margin: 0;">
                                    Ngày nộp: <?= date('d/m/Y H:i', strtotime($app['applied_at'])) ?>
                                </p>
                            </div>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <?php if (!empty($app['ai_analysis'])): ?>
                                    <span class="ai-match-badge <?= $app['ai_analysis']['level'] ?>" style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                                        ✨ <?= $app['ai_analysis']['score'] ?>% Phù hợp
                                    </span>
                                <?php endif; ?>
                                <?php if ($app['request_status'] === 'accepted'): ?>
                                    <span class="badge badge-success"><?= icon('check', '', 13) ?> <span>Đã duyệt nhận</span></span>
                                <?php elseif ($app['request_status'] === 'rejected'): ?>
                                    <span class="badge badge-danger"><span>Đã từ chối</span></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><span>Chờ duyệt</span></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($app['ai_analysis'])): ?>
                            <div style="background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.75rem; font-size: 0.84rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <strong style="color: #0f766e;">🤖 Phân tích AI Matching:</strong>
                                    <span style="font-weight: 700; color: <?= $app['ai_analysis']['color'] ?>;"><?= $app['ai_analysis']['label'] ?></span>
                                </div>
                                <?php if (!empty($app['ai_analysis']['matched_skills'])): ?>
                                    <div style="margin-bottom: 0.25rem;">
                                        <span style="color: #475569;">Kỹ năng đáp ứng:</span>
                                        <?php foreach ($app['ai_analysis']['matched_skills'] as $sk): ?>
                                            <span class="ai-skill-tag matched" style="font-size: 0.7rem;"><?= htmlspecialchars($sk) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($app['ai_analysis']['reasons'])): ?>
                                    <div style="color: #115e59; font-size: 0.8rem; margin-top: 0.25rem;">
                                        💡 <?= htmlspecialchars($app['ai_analysis']['reasons'][0] ?? '') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($app['available_work_dates'])): ?>
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.55rem 0.85rem; margin-bottom: 0.85rem; font-size: 0.88rem; color: #334155; display: flex; align-items: center; gap: 0.35rem;">
                                <?= icon('calendar', '', 14) ?> <span><strong>Ca & Ngày làm đã đăng ký:</strong> <?= htmlspecialchars($app['available_work_dates']) ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Xử lý duyệt / từ chối nhanh -->
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.4rem;">
                                <?php if ($app['request_status'] === 'pending'): ?>
                                    <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="accepted">
                                        <button type="submit" class="btn btn-success btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <?= icon('check', '', 12) ?> <span>Duyệt Nhận Người Lao Động</span>
                                        </button>
                                    </form>
                                    <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-reject-prompt="Lý do từ chối hồ sơ người lao động">
                                            Từ Chối
                                        </button>
                                    </form>
                                <?php elseif ($app['request_status'] === 'accepted'): ?>
                                    <span style="font-size: 0.85rem; color: #166534; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('check', '', 13) ?> Hồ sơ đã được công ty phê duyệt</span>
                                <?php else: ?>
                                    <form action="<?= url('/employer/applications/update/' . $app['request_id']) ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="status" value="accepted">
                                        <button type="submit" class="btn btn-outline btn-sm" style="color: #0d9488; border-color: #0d9488;">
                                            Duyệt Lại Hồ Sơ
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <a href="<?= url('/employer/meetings') ?>" class="btn btn-outline btn-sm" style="font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <?= icon('calendar', '', 13) ?> <span>Lên Lịch Phỏng Vấn &rarr;</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- CỘT 2: ĐÁNH GIÁ NĂNG LỰC & CA LÀM VIỆC TỪ CÁC NHÀ TUYỂN DỤNG KHÁC -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <?= icon('star_fill', '', 18) ?>
                <span>Đánh Giá Năng Lực 2 Chiều</span>
                <span class="badge badge-warning" style="font-size: 0.78rem;"><?= count($reviews) ?> đánh giá</span>
            </h2>
        </div>

        <div class="card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 1.25rem;">
            <?php if (empty($reviews)): ?>
                <div style="text-align: center; padding: 2rem 1rem; color: var(--slate-500);">
                    <div style="color: #cbd5e1; margin-bottom: 0.5rem;"><?= icon('star', '', 36) ?></div>
                    <p style="margin-bottom: 0.25rem; font-weight: 600; color: #0f172a;">Chưa có đánh giá nào từ các nhà tuyển dụng khác</p>
                    <p style="font-size: 0.85rem; color: var(--slate-500); margin: 0;">Sau khi người lao động hoàn thành ca làm việc, các nhà tuyển dụng sẽ đánh giá kỹ năng và thái độ tại đây.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <?php foreach ($reviews as $rev): ?>
                        <div style="border-bottom: 1px solid var(--slate-100); padding-bottom: 1.1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.4rem;">
                                <div>
                                    <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.35rem;">
                                        <?= icon('building', '', 14) ?> <?= htmlspecialchars($rev['employer_name'] ?? 'Doanh Nghiệp Tuyển Dụng') ?>
                                    </strong>
                                    <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: 0.15rem;">
                                        Tiêu chí: <span style="color: #0d9488; font-weight: 600;"><?= htmlspecialchars($rev['criteria_name'] ?? 'Ca làm việc') ?></span> • <?= date('d/m/Y', strtotime($rev['created_at'])) ?>
                                    </div>
                                </div>
                                <span class="badge badge-warning" style="font-size: 0.82rem; font-weight: 700;">
                                    <?= icon('star_fill', '', 12) ?> <?= $rev['overall_rating'] ?> / 5
                                </span>
                            </div>

                            <p style="font-size: 0.92rem; color: var(--slate-700); line-height: 1.55; margin: 0.5rem 0; background: #f8fafc; padding: 0.65rem 0.85rem; border-radius: 6px; border: 1px solid #f1f5f9;">
                                <?= htmlspecialchars($rev['review_content'] ?? '') ?>
                            </p>

                            <?php if (!empty($rev['reply_content'])): ?>
                                <div style="background: #f0fdfa; border-left: 3px solid #0d9488; padding: 0.55rem 0.75rem; border-radius: 4px; font-size: 0.85rem; color: #0f766e; margin-top: 0.5rem;">
                                    <strong>Phản hồi từ người lao động:</strong> <?= htmlspecialchars($rev['reply_content']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>
