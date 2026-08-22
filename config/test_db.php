<?php
// config/test_db.php - Kiểm tra kết nối CSDL và hiển thị thông tin bảng hệ thống

require_once __DIR__ . '/database.php';

$t0 = microtime(true);
$db = Database::getConnection();
$t1 = microtime(true);
$duration = round(($t1 - $t0) * 1000, 2);
$config = Database::getConfig();

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra kết nối CSDL - JobLink</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; padding: 2rem 1rem; color: #1e293b; }
        .container { max-width: 720px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); padding: 2rem; }
        h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; color: #0f172a; }
        .badge { display: inline-block; padding: 0.35rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.875rem; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: 0.75rem; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem; }
        th { background: #f1f5f9; font-weight: 600; }
        .code { background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 4px; font-family: monospace; }
        .btn { display: inline-block; padding: 0.6rem 1.2rem; background: #4f46e5; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kiểm Tra Kết Nối Cơ Sở Dữ Liệu</h1>

        <div style="margin-bottom: 1.5rem;">
            <strong>Trạng thái kết nối: </strong>
            <?php if ($db): ?>
                <span class="badge badge-success">Thành công (<?= $duration ?> ms)</span>
            <?php else: ?>
                <span class="badge badge-danger">Thất bại</span>
            <?php endif; ?>
        </div>

        <?php if (!$db): ?>
            <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <strong>Lỗi kết nối:</strong> <?= htmlspecialchars(Database::getLastError() ?? 'Không xác định') ?>
            </div>
        <?php endif; ?>

        <h2 style="font-size: 1.15rem; font-weight: 600; margin-top: 1.5rem;">Thông Số Cấu Hình</h2>
        <table>
            <tr><th>Tham số</th><th>Giá trị hiện tại</th></tr>
            <tr><td>Host</td><td><span class="code"><?= htmlspecialchars($config['host']) ?></span></td></tr>
            <tr><td>Port</td><td><span class="code"><?= htmlspecialchars($config['port']) ?></span></td></tr>
            <tr><td>Database Name</td><td><span class="code"><?= htmlspecialchars($config['db_name']) ?></span></td></tr>
            <tr><td>Username</td><td><span class="code"><?= htmlspecialchars($config['username']) ?></span></td></tr>
        </table>

        <?php if ($db): ?>
            <h2 style="font-size: 1.15rem; font-weight: 600; margin-top: 1.5rem;">Dữ Liệu Các Bảng Hệ Thống</h2>
            <table>
                <thead>
                    <tr><th>Tên Bảng (Table)</th><th>Số dòng dữ liệu (Rows)</th></tr>
                </thead>
                <tbody>
                    <?php
                    $tables = [
                        'user_roles', 'user_accounts', 'user_seekers', 'user_employers', 'user_staffs',
                        'job_posts', 'job_requests', 'job_saved', 'candidate_meetings', 'messages',
                        'review_criteria', 'reviews'
                    ];
                    foreach ($tables as $tbl) {
                        try {
                            $cnt = $db->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
                            echo "<tr><td><span class='code'>$tbl</span></td><td><strong>$cnt</strong></td></tr>";
                        } catch (Throwable $e) {
                            echo "<tr><td><span class='code'>$tbl</span></td><td style='color:#ef4444;'>Bảng chưa tạo hoặc lỗi</td></tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="/" class="btn">← Về Trang Chủ</a>
    </div>
</body>
</html>
