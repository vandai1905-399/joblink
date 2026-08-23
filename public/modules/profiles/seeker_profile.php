<?php 
$extraCss = ['css/jobs/ai_matching.css'];
$extraJs = ['js/jobs/ai_recommendation.js'];
ob_start(); 

$sessionUser = $_SESSION['user'] ?? [];
$seekerName = $seeker['full_name'] ?? ($sessionUser['display_name'] ?? ($sessionUser['username'] ?? 'Người lao động'));
$seekerId = $seeker['seeker_id'] ?? ($sessionUser['account_id'] ?? 1);
$seekerEmail = $seeker['email'] ?? ($sessionUser['email'] ?? '');
$seekerPhone = $seeker['phone_number'] ?? (preg_match('/^[0-9]{9,12}$/', $sessionUser['username'] ?? '') ? $sessionUser['username'] : '');
?>

<div style="max-width: 960px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.65rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Hồ Sơ Năng Lực & Tối Ưu AI Đề Xuất</h1>
            <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">Hoàn thiện thông tin kỹ năng để hệ thống AI đề xuất chính xác các công việc phù hợp với bạn nhất</p>
        </div>
        <button type="button" id="btn-ai-optimize-profile" class="btn" style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); color: #ffffff; font-weight: 700; box-shadow: 0 4px 12px rgba(13,148,136,0.25); display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 8px;">
            ✨ <span>AI Đánh Giá & Gợi Ý Tối Ưu Hồ Sơ</span>
        </button>
    </div>

    <!-- KHỐI KẾT QUẢ PHÂN TÍCH AI (ẨN/HIỆN QUA AJAX HOẶC NẠP SẴN) -->
    <div id="ai-profile-optimizer-result" style="margin-bottom: 1.75rem; <?= !empty($profileAdvice) ? '' : 'display: none;' ?>">
        <?php if (!empty($profileAdvice)): ?>
            <div style="background: linear-gradient(135deg, #f0fdfa 0%, #f0f9ff 100%); border: 1px solid #99f6e4; border-radius: 14px; padding: 1.35rem; box-shadow: 0 4px 14px rgba(13,148,136,0.06);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; border-bottom: 1px solid #ccfbf1; padding-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span class="badge <?= $profileAdvice['badge_class'] ?? 'badge-primary' ?>" style="font-size: 0.78rem; font-weight: 700;"><?= $profileAdvice['completeness_level'] ?? 'Hoàn thiện cơ bản' ?></span>
                            <span style="font-size: 0.72rem; padding: 0.15rem 0.45rem; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-weight: 700;">✨ <?= htmlspecialchars($profileAdvice['ai_provider'] ?? 'Google Gemini AI') ?></span>
                        </div>
                        <h4 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0.35rem 0 0 0;">Độ Hoàn Thiện Hồ Sơ Đạt: <?= $profileAdvice['score'] ?? 80 ?>/100</h4>
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 800; color: #0d9488; display: flex; align-items: center; gap: 0.3rem;">
                        ✨ <span><?= $profileAdvice['score'] ?? 80 ?>%</span>
                    </div>
                </div>

                <?php if (!empty($profileAdvice['strengths'])): ?>
                    <div style="margin-bottom: 0.75rem;">
                        <strong style="font-size: 0.85rem; color: #065f46;">🌟 Điểm mạnh của hồ sơ:</strong>
                        <ul style="margin: 0.25rem 0 0 0; padding-left: 1.2rem; font-size: 0.82rem; line-height: 1.5; color: #047857;">
                            <?php foreach ($profileAdvice['strengths'] as $st): ?>
                                <li><?= htmlspecialchars($st) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profileAdvice['suggestions'])): ?>
                    <div style="margin-bottom: 0.75rem;">
                        <strong style="font-size: 0.85rem; color: #92400e;">💡 Gợi ý cải thiện từ AI để tăng 45% cơ hội việc làm tốt:</strong>
                        <ul style="margin: 0.25rem 0 0 0; padding-left: 1.2rem; font-size: 0.82rem; line-height: 1.5; color: #b45309;">
                            <?php foreach ($profileAdvice['suggestions'] as $sug): ?>
                                <li><?= htmlspecialchars($sug) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card" style="box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 14px;">
        <form action="<?= url('/seeker/profile/update') ?>" method="POST">
            <!-- AVATAR & TỔNG QUAN -->
            <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--slate-200); flex-wrap: wrap;">
                <img src="<?= htmlspecialchars(!empty($seeker['avatar_url']) ? $seeker['avatar_url'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150') ?>" alt="avatar" style="width: 90px; height: 90px; border-radius: var(--radius-full); object-fit: cover; border: 3px solid #0d9488; box-shadow: 0 4px 10px rgba(0,0,0,0.08);">
                <div>
                    <h3 style="font-size: 1.3rem; font-weight: 800; display: flex; align-items: center; gap: 0.5rem; color: #0f172a; margin-bottom: 0.35rem;">
                        <span><?= htmlspecialchars($seekerName) ?></span>
                        <span class="badge badge-primary" style="font-size: 0.75rem;">Mã NLĐ: <?= formatId('seeker', $seekerId) ?></span>
                    </h3>
                    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; font-size: 0.85rem; color: var(--slate-600);">
                        <span><?= icon('star_fill', '', 14) ?> <strong><?= $seeker['avg_rating'] ?? '5.0' ?></strong> (<?= $seeker['total_reviews'] ?? 0 ?> đánh giá ca làm)</span>
                        <span>•</span>
                        <span><?= icon('mail', '', 14) ?> <?= htmlspecialchars($seekerEmail) ?></span>
                    </div>
                </div>
            </div>

            <!-- PHẦN 1: THÔNG TIN CÁ NHÂN CƠ BẢN -->
            <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('user', '', 16) ?> <span>1. Thông Tin Cơ Bản</span>
            </h4>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Họ và Tên *</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($seekerName) ?>" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Ngày Sinh</label>
                    <input type="date" name="date_of_birth" value="<?= htmlspecialchars($seeker['date_of_birth'] ?? '') ?>" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Giới Tính</label>
                    <select name="gender" class="form-control">
                        <option value="Nam" <?= ($seeker['gender'] ?? '') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                        <option value="Nữ" <?= ($seeker['gender'] ?? '') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                        <option value="Khác" <?= (!isset($seeker['gender']) || $seeker['gender'] === 'Khác') ? 'selected' : '' ?>>Khác</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Số Điện Thoại *</label>
                    <input type="text" name="phone_number" value="<?= htmlspecialchars($seekerPhone) ?>" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Địa Chỉ Thường Trú</label>
                <input type="text" name="address" value="<?= htmlspecialchars($seeker['address'] ?? '') ?>" placeholder="Ví dụ: Số 15 Đường Bùi Quốc Khánh, P. Chánh Nghĩa, TP. Thủ Dầu Một" class="form-control">
            </div>


            <!-- PHẦN 2: HỒ SƠ NĂNG LỰC & KỸ NĂNG CHUYÊN MÔN (CHO AI MATCHING) -->
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed var(--slate-200);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f766e; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                        ✨ <span>2. Năng Lực & Nguyện Vọng Nghề Nghiệp (AI Khớp Nối)</span>
                    </h4>
                    <span style="font-size: 0.8rem; color: #0d9488; font-weight: 600;">Trọng số 60% khi AI đề xuất việc làm</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Vị trí / Chức danh mong muốn</label>
                        <input type="text" name="desired_position" value="<?= htmlspecialchars($seeker['desired_position'] ?? '') ?>" placeholder="Ví dụ: Lập trình viên Web, Barista, Kế toán kho..." class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trình độ học vấn cao nhất</label>
                        <select name="education_level" class="form-control">
                            <option value="">-- Chọn trình độ --</option>
                            <option value="Đại học" <?= ($seeker['education_level'] ?? '') === 'Đại học' ? 'selected' : '' ?>>Đại học</option>
                            <option value="Cao đẳng" <?= ($seeker['education_level'] ?? '') === 'Cao đẳng' ? 'selected' : '' ?>>Cao đẳng</option>
                            <option value="Trung cấp" <?= ($seeker['education_level'] ?? '') === 'Trung cấp' ? 'selected' : '' ?>>Trung cấp / Nghề</option>
                            <option value="THPT" <?= ($seeker['education_level'] ?? '') === 'THPT' ? 'selected' : '' ?>>Tốt nghiệp THPT</option>
                            <option value="Sau đại học" <?= ($seeker['education_level'] ?? '') === 'Sau đại học' ? 'selected' : '' ?>>Sau đại học (Thạc sĩ, TS)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Kỹ năng chuyên môn & Năng khiếu (Phân cách bởi dấu phẩy) *</span>
                        <span style="font-size: 0.78rem; color: #64748b; font-weight: normal;">AI dùng trường này để so khớp yêu cầu bài đăng</span>
                    </label>
                    <input type="text" id="seeker_skills_input" name="skills" value="<?= htmlspecialchars($seeker['skills'] ?? '') ?>" placeholder="Ví dụ: PHP, ReactJS, Tiếng Anh giao tiếp, Pha chế, Bán hàng..." class="form-control">
                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.35rem;">
                        Gợi ý phổ biến: 
                        <span class="badge badge-slate" style="cursor: pointer;" onclick="addSkillToInput('Tin học văn phòng')">+ Tin học văn phòng</span>
                        <span class="badge badge-slate" style="cursor: pointer;" onclick="addSkillToInput('Giao tiếp tốt')">+ Giao tiếp tốt</span>
                        <span class="badge badge-slate" style="cursor: pointer;" onclick="addSkillToInput('Làm việc nhóm')">+ Làm việc nhóm</span>
                        <span class="badge badge-slate" style="cursor: pointer;" onclick="addSkillToInput('Tiếng Anh')">+ Tiếng Anh</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Số năm kinh nghiệm làm việc</label>
                        <select name="experience_years" class="form-control">
                            <option value="0" <?= intval($seeker['experience_years'] ?? 0) === 0 ? 'selected' : '' ?>>Chưa có kinh nghiệm / Mới tốt nghiệp</option>
                            <option value="1" <?= intval($seeker['experience_years'] ?? 0) === 1 ? 'selected' : '' ?>>Dưới 1 năm kinh nghiệm</option>
                            <option value="2" <?= intval($seeker['experience_years'] ?? 0) === 2 ? 'selected' : '' ?>>1 - 2 năm kinh nghiệm</option>
                            <option value="3" <?= intval($seeker['experience_years'] ?? 0) === 3 ? 'selected' : '' ?>>3 - 5 năm kinh nghiệm</option>
                            <option value="5" <?= intval($seeker['experience_years'] ?? 0) >= 5 ? 'selected' : '' ?>>Trên 5 năm kinh nghiệm</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mức lương kỳ vọng (VNĐ/tháng)</label>
                        <input type="number" name="expected_salary" value="<?= htmlspecialchars($seeker['expected_salary'] ?? '') ?>" placeholder="Ví dụ: 12000000" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Hình thức làm việc ưu tiên</label>
                        <select name="preferred_employment_type" class="form-control">
                            <option value="full_time" <?= ($seeker['preferred_employment_type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Toàn thời gian (Full-time)</option>
                            <option value="part_time" <?= ($seeker['preferred_employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Bán thời gian (Part-time)</option>
                            <option value="temporary" <?= ($seeker['preferred_employment_type'] ?? '') === 'temporary' ? 'selected' : '' ?>>Thời vụ / Xoay ca linh hoạt</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Khu vực làm việc ưu tiên</label>
                        <input type="text" name="preferred_location" value="<?= htmlspecialchars($seeker['preferred_location'] ?? '') ?>" placeholder="Ví dụ: TP. Thủ Dầu Một, Thuận An..." class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Giới thiệu bản thân & Mục tiêu nghề nghiệp</label>
                    <textarea name="bio" rows="3" class="form-control" placeholder="Tóm tắt ngắn về bản thân, kinh nghiệm nổi bật và mục tiêu phát triển công việc..."><?= htmlspecialchars($seeker['bio'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- PHẦN 3: ĐIỂM UY TÍN & BẢN CAM KẾT TUÂN THỦ (TRUST SCORE & COMMITMENT) -->
            <?php 
                $trustScore = intval($seeker['trust_score'] ?? 100);
                $scoreColor = $trustScore >= 80 ? '#10b981' : ($trustScore >= 60 ? '#f59e0b' : '#ef4444');
                $scoreLevel = $trustScore >= 80 ? 'Rất Uy Tín (Xuất Sắc)' : ($trustScore >= 60 ? 'Mức Trung Bình' : 'Cảnh Báo Vi Phạm');
                $isSigned = ($seeker['agreement_signed'] ?? 0) == 1;
            ?>
            <div id="trust-score-section" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed var(--slate-200);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                    <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                        <?= icon('award', '', 18) ?> <span>3. Điểm Uy Tín & Bản Cam Kết Tuân Thủ Quy Chế</span>
                    </h4>
                    <span class="badge" style="background: <?= $scoreColor ?>20; color: <?= $scoreColor ?>; font-weight: 700; font-size: 0.78rem;">
                        <?= $scoreLevel ?> (<?= $trustScore ?>/100)
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
                    <!-- THẺ 1: CHỈ SỐ ĐIỂM UY TÍN HIỆN TẠI -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-top: 4px solid <?= $scoreColor ?>; border-radius: 12px; padding: 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <strong style="font-size: 0.95rem; color: #0f172a;">Chỉ Số Điểm Uy Tín</strong>
                            <span style="font-size: 0.75rem; color: #64748b;">Trust Score</span>
                        </div>

                        <div style="text-align: center; padding: 1rem 0;">
                            <div style="font-size: 3rem; font-weight: 900; color: <?= $scoreColor ?>; line-height: 1;">
                                <?= $trustScore ?><span style="font-size: 1.2rem; color: #94a3b8; font-weight: 600;">/100</span>
                            </div>
                            <div style="width: 100%; background: #e2e8f0; height: 8px; border-radius: 9999px; margin-top: 0.75rem; overflow: hidden;">
                                <div style="width: <?= $trustScore ?>%; background: <?= $scoreColor ?>; height: 100%; border-radius: 9999px;"></div>
                            </div>
                        </div>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.9rem; font-size: 0.82rem; line-height: 1.6; color: #334155;">
                            <div><strong>Quyền Lợi:</strong> <?= $trustScore >= 80 ? 'Hồ sơ được ưu tiên đẩy TOP trước các Nhà tuyển dụng.' : 'Được phép nộp đơn ứng tuyển việc làm bình thường.' ?></div>
                            <div><strong>Số lần cảnh cáo:</strong> <span style="color: #ef4444; font-weight: 700;"><?= intval($seeker['warning_count'] ?? 0) ?> lần</span></div>
                        </div>

                        <div style="margin-top: 0.85rem;">
                            <strong style="font-size: 0.8rem; color: #0f172a;">Quy Tắc Ràng Buộc:</strong>
                            <ul style="margin: 0.35rem 0 0 0; padding-left: 1.1rem; font-size: 0.78rem; color: #475569; line-height: 1.5;">
                                <li><span style="color: #10b981; font-weight: 700;">+5 điểm:</span> Đến phỏng vấn đúng giờ theo lịch hẹn.</li>
                                <li><span style="color: #10b981; font-weight: 700;">+10 điểm:</span> Nhận đánh giá 5 sao sau ca làm.</li>
                                <li><span style="color: #ef4444; font-weight: 700;">-30 điểm:</span> Bùng hẹn phỏng vấn không báo trước 12h.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- THẺ 2: BẢN CAM KẾT ĐIỆN TỬ -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                                <strong style="font-size: 0.95rem; color: #0f172a;">Cam Kết Điện Tử</strong>
                                <?php if ($isSigned): ?>
                                    <span class="badge badge-success" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;">✓ Đã Ký Cam Kết</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;">Chưa Ký</span>
                                <?php endif; ?>
                            </div>

                            <div style="margin-bottom: 0.75rem;">
                                <label class="form-label" style="font-size: 0.82rem; margin-bottom: 0.25rem;">Số CCCD / Định Danh (Tùy chọn xác thực)</label>
                                <input type="text" name="id_card_number" value="<?= htmlspecialchars($seeker['id_card_number'] ?? '') ?>" placeholder="Nhập số CCCD (12 chữ số)..." class="form-control" style="font-size: 0.85rem; padding: 0.45rem 0.65rem;">
                            </div>

                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.75rem 0.9rem; font-size: 0.78rem; color: #166534; line-height: 1.55;">
                                <strong>NỘI DUNG CAM KẾT RÀNG BUỘC:</strong>
                                <ol style="margin: 0.35rem 0 0 0; padding-left: 1.1rem;">
                                    <li>Cung cấp thông tin kỹ năng & kinh nghiệm trung thực.</li>
                                    <li>Có mặt đúng giờ khi đã xác nhận lịch hẹn phỏng vấn.</li>
                                    <li>Báo trước ít nhất 12 giờ nếu có việc đột xuất không thể đến.</li>
                                    <li>Tuân thủ chế tài trừ Điểm Uy Tín nếu vi phạm quy chế sàn.</li>
                                </ol>
                            </div>
                        </div>

                        <div style="margin-top: 1rem; border-top: 1px solid #f1f5f9; padding-top: 0.75rem;">
                            <?php if ($isSigned): ?>
                                <div style="font-size: 0.8rem; color: #059669; font-weight: 700; display: flex; align-items: center; gap: 0.3rem;">
                                    <?= icon('shield_check', '', 16) ?>
                                    <span>Bạn đã ký cam kết điện tử thành công vào ngày <?= !empty($seeker['agreement_signed_at']) ? formatDate($seeker['agreement_signed_at']) : 'gần đây' ?></span>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                                    <span style="font-size: 0.78rem; color: #64748b;">Ký cam kết để nhận +5 Điểm Uy Tín</span>
                                    <a href="<?= url('/seeker/settings?tab=trust') ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem; font-weight: 700; border-color: #0d9488; color: #0d9488;">
                                        Ký Cam Kết Ngay
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; border-top: 1px solid var(--slate-200); padding-top: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <span style="font-size: 0.85rem; color: #64748b;">
                    💡 Điểm đề xuất việc làm AI & Hồ sơ sẽ tự động cập nhật ngay sau khi bạn lưu.
                </span>
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: 700;">
                    Lưu Thay Đổi & Cập Nhật Hồ Sơ
                </button>
            </div>
        </form>
    </div>
</div>


<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/user_layout.php';
?>

