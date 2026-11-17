<?php
ob_start();
$formatAboutNumber = static function ($value) {
    return number_format((int)$value, 0, ',', '.');
};
?>

<div class="about-page">
    <section class="about-hero">
        <div class="about-hero-copy">
            <span class="about-eyebrow"><?= icon('sparkle', '', 15) ?> Nền tảng việc làm minh bạch</span>
            <h1>Kết nối đúng người,<br><span>mở đúng cơ hội.</span></h1>
            <p>JobLink giúp người lao động tìm việc phù hợp và giúp doanh nghiệp tuyển dụng rõ ràng, nhanh chóng, an toàn hơn.</p>
            <div class="about-hero-actions">
                <a href="<?= url('/jobs') ?>" class="btn btn-primary"><?= icon('search', '', 16) ?> Khám phá việc làm</a>
                <a href="<?= url('/auth/register') ?>" class="btn btn-outline"><?= icon('user_plus', '', 16) ?> Tham gia JobLink</a>
            </div>
        </div>
        <div class="about-hero-visual" aria-hidden="true">
            <div class="about-orbit about-orbit-one"></div>
            <div class="about-orbit about-orbit-two"></div>
            <div class="about-hero-icon"><?= icon('handshake', '', 42) ?></div>
            <span class="about-float-card about-float-card-top"><?= icon('check', '', 15) ?> Tin tuyển dụng đã duyệt</span>
            <span class="about-float-card about-float-card-bottom"><?= icon('shield', '', 15) ?> Đánh giá hai chiều</span>
        </div>
    </section>

    <section class="about-stats" aria-label="Số liệu hoạt động JobLink">
        <div>
            <strong><?= $formatAboutNumber($aboutStats['seekers']) ?></strong>
            <span>Người lao động</span>
        </div>
        <div>
            <strong><?= $formatAboutNumber($aboutStats['employers']) ?></strong>
            <span>Nhà tuyển dụng</span>
        </div>
        <div>
            <strong><?= $formatAboutNumber($aboutStats['jobs']) ?></strong>
            <span>Việc làm đã duyệt</span>
        </div>
        <div>
            <strong><?= $formatAboutNumber($aboutStats['connections']) ?></strong>
            <span>Lượt ứng tuyển</span>
        </div>
        <div>
            <strong><?= $formatAboutNumber($aboutStats['reviews']) ?></strong>
            <span>Đánh giá đã duyệt</span>
        </div>
    </section>

    <section class="about-section about-mission">
        <div class="about-section-heading">
            <span class="about-eyebrow">Vì sao JobLink tồn tại?</span>
            <h2>Một cách kết nối việc làm <span>tử tế hơn</span></h2>
        </div>
        <div class="about-mission-grid">
            <article class="about-feature-card">
                <div class="about-feature-icon is-teal"><?= icon('target', '', 22) ?></div>
                <h3>Rõ ràng ngay từ đầu</h3>
                <p>Thông tin việc làm, địa điểm, hình thức và quy trình ứng tuyển được trình bày dễ hiểu.</p>
            </article>
            <article class="about-feature-card">
                <div class="about-feature-icon is-blue"><?= icon('shield', '', 22) ?></div>
                <h3>An tâm khi kết nối</h3>
                <p>Doanh nghiệp, tin tuyển dụng và phản hồi được kiểm duyệt theo quy trình của nền tảng.</p>
            </article>
            <article class="about-feature-card">
                <div class="about-feature-icon is-amber"><?= icon('star', '', 22) ?></div>
                <h3>Uy tín được ghi nhận</h3>
                <p>Đánh giá hai chiều giúp cộng đồng nhìn thấy chất lượng và trách nhiệm sau mỗi lần hợp tác.</p>
            </article>
        </div>
    </section>

    <section class="about-process">
        <div class="about-section-heading centered">
            <span class="about-eyebrow">Bắt đầu thật đơn giản</span>
            <h2>Ba bước để <span>bắt đầu kết nối</span></h2>
        </div>
        <div class="about-process-grid">
            <div class="about-process-step">
                <span>01</span>
                <div><?= icon('user_plus', '', 20) ?></div>
                <h3>Tạo hồ sơ</h3>
                <p>Chọn vai trò người lao động hoặc nhà tuyển dụng và hoàn thiện thông tin cơ bản.</p>
            </div>
            <div class="about-process-step">
                <span>02</span>
                <div><?= icon('search', '', 20) ?></div>
                <h3>Tìm cơ hội phù hợp</h3>
                <p>Khám phá việc làm theo kỹ năng, hình thức và địa điểm bạn quan tâm.</p>
            </div>
            <div class="about-process-step">
                <span>03</span>
                <div><?= icon('handshake', '', 20) ?></div>
                <h3>Kết nối & đánh giá</h3>
                <p>Trao đổi, phỏng vấn và cùng xây dựng môi trường làm việc đáng tin cậy.</p>
            </div>
        </div>
    </section>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php';
?>
