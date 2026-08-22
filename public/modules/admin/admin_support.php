<?php ob_start(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">Trung Tâm CSKH & Xử Lý Khiếu Nại</h1>
    <p style="color: var(--slate-500); font-size: 0.95rem;">Tiếp nhận và phản hồi các yêu cầu hỗ trợ, khiếu nại ca làm việc từ Người lao động & Doanh nghiệp</p>
</div>

<div class="grid-stats">
    <div class="stat-card">
        <div class="stat-title">Yêu Cầu Hỗ Trợ Mới</div>
        <div class="stat-value" style="color: var(--warning);">3</div>
        <div style="font-size: 0.82rem; color: var(--slate-500);">Chờ chuyên viên xử lý</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Khiếu Nại Ca Làm Việc</div>
        <div class="stat-value" style="color: var(--danger);">1</div>
        <div style="font-size: 0.82rem; color: var(--slate-500);">Về trang bị bảo hộ tại xưởng gỗ</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Đã Xử Lý Thành Công</div>
        <div class="stat-value" style="color: var(--success);">14</div>
        <div style="font-size: 0.82rem; color: var(--slate-500);">Tỷ lệ hài lòng 98%</div>
    </div>
</div>

<div class="card">
    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem;">Danh Sách Tiếp Nhận Hỗ Trợ Gần Đây</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Khách hàng / Đối tác</th>
                    <th>Nội dung yêu cầu / Khiếu nại</th>
                    <th>Phân loại</th>
                    <th>Chuyên viên phụ trách</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Bùi Quang Huy</strong><br><span style="font-size:0.8rem; color:var(--slate-400);">Người lao động</span></td>
                    <td>Góp ý xưởng gỗ Minh Dương trang bị thêm khẩu trang y tế chuẩn sau ca làm việc ngày 12/08.</td>
                    <td><span class="badge badge-warning">Môi trường làm việc</span></td>
                    <td>Bùi Anh Tuấn (Xử lý khiếu nại)</td>
                    <td><span class="badge badge-success"><?= icon('check', '', 12) ?> Đã tiếp nhận & phản hồi</span></td>
                    <td><button class="btn btn-outline btn-sm">Xem chi tiết</button></td>
                </tr>
                <tr>
                    <td><strong>Shop Thời Trang Bốn Mùa</strong><br><span style="font-size:0.8rem; color:var(--slate-400);">Nhà tuyển dụng</span></td>
                    <td>Hỏi về thủ tục nộp lại bản scan Giấy phép kinh doanh để được gắn tích xanh xác minh.</td>
                    <td><span class="badge badge-primary">Xác minh GPKD</span></td>
                    <td>Lê Minh Hoàng (Hỗ trợ DN)</td>
                    <td><span class="badge badge-warning">Đang hướng dẫn</span></td>
                    <td><button class="btn btn-outline btn-sm">Phản hồi</button></td>
                </tr>
                <tr>
                    <td><strong>Lê Thị Khánh Vân</strong><br><span style="font-size:0.8rem; color:var(--slate-400);">Người lao động</span></td>
                    <td>Hỏi thông tin ca làm và khu vực gửi xe của nhân viên tại quán Mây Coffee.</td>
                    <td><span class="badge badge-slate">Hỗ trợ ca làm</span></td>
                    <td>Đặng Mỹ Linh (Hỗ trợ người lao động)</td>
                    <td><span class="badge badge-success"><?= icon('check', '', 12) ?> Đã giải đáp</span></td>
                    <td><button class="btn btn-outline btn-sm">Xem chi tiết</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php';
?>
