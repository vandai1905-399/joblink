<?php ob_start(); ?>

<div class="container" style="max-width: 900px;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 class="section-title" style="font-size: 2.2rem; margin-bottom: 0.75rem;">Về Hệ Thống JobLink</h1>
        <p class="section-subtitle" style="font-size: 1.1rem;">Nền tảng kết nối cơ hội việc làm & đánh giá ca làm việc 2 chiều minh bạch hàng đầu.</p>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 1rem; color: var(--primary); display: flex; align-items: center; gap: 0.5rem;"><?= icon('target', '', 20) ?> <span>Sứ Mệnh Của Chúng Tôi</span></h2>
        <p style="margin-bottom: 1rem; line-height: 1.7;">
            JobLink ra đời nhằm giải quyết bài toán tuyển dụng lao động theo ca, việc làm linh hoạt và toàn thời gian. Chúng tôi mang đến môi trường kết nối trực tiếp, loại bỏ các đơn vị trung gian không rõ nguồn gốc và cung cấp cơ chế bảo vệ quyền lợi 2 chiều:
        </p>
        <ul style="padding-left: 1.5rem; line-height: 1.8; color: var(--slate-700);">
            <li><strong>Đối với Người Lao Động (Seeker):</strong> Dễ dàng tìm việc theo ca, theo dõi trạng thái ứng tuyển, lên lịch phỏng vấn và nhận xét môi trường làm việc sau ca.</li>
            <li><strong>Đối với Nhà Tuyển Dụng (Employer):</strong> Đăng tin tuyển dụng nhanh chóng, xác minh GPKD minh bạch, quản lý hồ sơ người lao động và đánh giá thái độ, kỹ năng của người lao động sau ca làm.</li>
            <li><strong>Đối với Quản Trị & CSKH (Admin & Staff):</strong> Kiểm duyệt tin tức, hỗ trợ giải quyết khiếu nại và duy trì chuẩn mực uy tín cho toàn bộ cộng đồng.</li>
        </ul>
    </div>

    <div class="grid-stats" style="margin-bottom: 2rem;">
        <div class="stat-card" style="text-align:center;">
            <div class="stat-value" style="color:var(--primary);">1,000+</div>
            <div class="stat-title">Doanh nghiệp đã xác minh</div>
        </div>
        <div class="stat-card" style="text-align:center;">
            <div class="stat-value" style="color:var(--success);">10,000+</div>
            <div class="stat-title">Ca làm việc hoàn thành</div>
        </div>
        <div class="stat-card" style="text-align:center;">
            <div class="stat-value" style="color:var(--warning);">98.5%</div>
            <div class="stat-title">Đánh giá hài lòng 2 chiều</div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/main_layout.php';
?>
