<?php
/**
 * config/Database.php
 * File DUY NHẤT cấu hình và khởi tạo kết nối PDO MySQL cho toàn bộ hệ thống JobLink
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Database {

    // =========================================================================
    // THÔNG SỐ KẾT NỐI CSDL:
    // Khi chạy trên hosting (cPanel/DirectAdmin), host mặc định là 'localhost' hoặc '127.0.0.1'
    // =========================================================================
    private static $host     = "localhost";           // Hostname: 'localhost' trên hosting / 127.0.0.1
    private static $port     = 3306;                  // Cổng MySQL (3306)
    private static $db_name  = "wjatahed_joblink";    // Tên CSDL (ví dụ: wjatahed_joblink hoặc joblink)
    private static $username = "wjatahed_joblink";    // Tên user MySQL
    private static $password = "7GIsj6nD3i";          // Mật khẩu MySQL
    private static $charset  = "utf8mb4";             // Bảng mã tiếng Việt
    // =========================================================================

    private static $cachedConn = null;
    private static $connectionAttempted = false;
    private static $lastError = null;

    /**
     * Khởi tạo kết nối PDO MySQL tức thì (Dùng cơ chế Singleton & Tự nhận diện môi trường)
     * @return PDO|null
     */
    public static function getConnection() {
        if (self::$cachedConn !== null) {
            return self::$cachedConn;
        }

        if (self::$connectionAttempted) {
            return self::$cachedConn;
        }

        self::$connectionAttempted = true;

        $dbName   = "wjatahed_joblink";
        $username = "wjatahed_joblink";
        $password = "7GIsj6nD3i";
        $port     = 3306;

        // TỰ ĐỘNG ƯU TIÊN HOST CHÍNH XÁC ĐỂ KẾT NỐI TỨC THÌ (< 15MS)
        $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        if ($isWindows) {
            $hostsToTry = ['103.97.126.29'];
        } else {
            $hostsToTry = ['localhost', '127.0.0.1', '103.97.126.29'];
        }

        foreach ($hostsToTry as $host) {
            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
                $conn = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 3
                ]);
                self::$cachedConn = $conn;
                self::$lastError = null;
                return self::$cachedConn;
            } catch (Throwable $e) {
                self::$lastError = $e->getMessage();
            }
        }

        error_log("[JobLink DB Connection Failed] " . self::$lastError);
        return null;
    }

    public static function getLastError() {
        return self::$lastError;
    }

    public static function getConfig() {
        return [
            'host'     => self::$host,
            'port'     => self::$port,
            'db_name'  => self::$db_name,
            'username' => self::$username,
            'password' => self::$password
        ];
    }

    public function __call($name, $arguments) {
        if ($name === 'getConnection') {
            return self::getConnection();
        }
    }
}
