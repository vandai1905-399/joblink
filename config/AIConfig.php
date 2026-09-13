<?php
/**
 * config/AIConfig.php
 * File cấu hình và quản lý Google Gemini AI cho Hệ thống JobLink
 */

require_once __DIR__ . '/Database.php';

class AIConfig {
    // Giá trị mặc định khi chưa có cấu hình trong CSDL
    public const GOOGLE_API_KEY   = ''; 
    public const GEMINI_MODEL     = 'gemini-3.1-flash-lite'; 
    public const API_BASE_URL     = 'https://generativelanguage.googleapis.com/v1beta/models/';
    public const REQUEST_TIMEOUT  = 8;

    private static $cachedSettings = null;

    /**
     * Danh sách các model Gemini được hỗ trợ
     */
    public static function getSupportedModels() {
        return [
            'gemini-3.1-flash-lite' => [
                'name' => 'Gemini 3.1 Flash-Lite (Khuyên dùng - Phản hồi < 1s)',
                'desc' => 'Tốc độ siêu tốc, ổn định, phản hồi mượt mà cho tìm việc, đề xuất AI và chatbot hỗ trợ.',
                'badge' => 'Hot / Khuyên Dùng'
            ],
            'gemini-3.1-flash-lite-preview' => [
                'name' => 'Gemini 3.1 Flash-Lite Preview',
                'desc' => 'Bản thử nghiệm nhanh chóng cho chatbot.',
                'badge' => 'Nhanh'
            ],
            'gemini-3-flash-preview' => [
                'name' => 'Gemini 3 Flash Preview (Cân bằng)',
                'desc' => 'Độ chính xác cao, phân tích hồ sơ và so khớp kỹ năng tuyển dụng.',
                'badge' => 'Cân Bằng'
            ],
            'gemini-3.5-flash-lite' => [
                'name' => 'Gemini 3.5 Flash-Lite',
                'desc' => 'Phiên bản Flash-Lite thế hệ 3.5.',
                'badge' => 'Flash-Lite'
            ],
            'gemini-3.7-flash' => [
                'name' => 'Gemini 3.7 Flash (Suy luận nâng cao)',
                'desc' => 'Tích hợp khả năng suy luận sâu và phân tích dữ liệu tuyển dụng quy mô lớn.',
                'badge' => 'Nâng Cao'
            ]
        ];
    }

    /**
     * Khởi tạo bảng system_settings nếu chưa có và lấy toàn bộ setting
     */
    public static function loadSettings() {
        if (self::$cachedSettings !== null) {
            return self::$cachedSettings;
        }

        self::$cachedSettings = [
            'gemini_api_key'     => self::GOOGLE_API_KEY,
            'gemini_model'       => self::GEMINI_MODEL,
            'gemini_is_active'   => '1',
            'gemini_temperature' => '0.2'
        ];

        try {
            $db = Database::getConnection();
            if ($db) {
                // Tự động tạo bảng nếu chưa có
                $db->exec("
                    CREATE TABLE IF NOT EXISTS system_settings (
                        setting_key VARCHAR(100) PRIMARY KEY,
                        setting_value TEXT NULL,
                        setting_group VARCHAR(50) DEFAULT 'general',
                        description VARCHAR(255) NULL,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");

                $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_group = 'ai'");
                $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                if (!empty($rows)) {
                    foreach ($rows as $k => $v) {
                        self::$cachedSettings[$k] = $v;
                    }
                }
            }
        } catch (Throwable $e) {
            error_log("[AIConfig::loadSettings Error] " . $e->getMessage());
        }

        return self::$cachedSettings;
    }

    /**
     * Lấy giá trị của một thiết lập AI
     */
    public static function get($key, $default = null) {
        $settings = self::loadSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Lưu một thiết lập vào CSDL
     */
    public static function set($key, $value, $description = '') {
        try {
            $db = Database::getConnection();
            if ($db) {
                self::loadSettings(); // đảm bảo bảng đã được tạo
                $stmt = $db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, setting_group, description, updated_at)
                    VALUES (:key, :val, 'ai', :desc, NOW())
                    ON DUPLICATE KEY UPDATE setting_value = :val2, description = :desc2, updated_at = NOW()
                ");
                $stmt->execute([
                    ':key'   => $key,
                    ':val'   => $value,
                    ':desc'  => $description,
                    ':val2'  => $value,
                    ':desc2' => $description
                ]);
                if (self::$cachedSettings !== null) {
                    self::$cachedSettings[$key] = $value;
                }
                return true;
            }
        } catch (Throwable $e) {
            error_log("[AIConfig::set Error] " . $e->getMessage());
        }
        return false;
    }

    /**
     * Lấy API Key đang hoạt động (Ưu tiên DB, tiếp đến ENV, tiếp đến hằng số)
     */
    public static function getApiKey() {
        $dbKey = self::get('gemini_api_key');
        if (!empty($dbKey)) return trim($dbKey);

        $envKey = getenv('GEMINI_API_KEY') ?: (getenv('GOOGLE_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? ($_ENV['GOOGLE_API_KEY'] ?? '')));
        if (!empty($envKey)) return trim($envKey);

        return trim(self::GOOGLE_API_KEY);
    }

    /**
     * Lấy Model Gemini đang cấu hình
     */
    public static function getModel() {
        $model = self::get('gemini_model');
        return !empty($model) ? trim($model) : self::GEMINI_MODEL;
    }

    /**
     * Kiểm tra xem Google Gemini API Key đã được cấu hình hay chưa
     */
    public static function isConfigured() {
        $isActive = self::get('gemini_is_active', '1');
        if ($isActive === '0' || $isActive === false || $isActive === 0) return false;
        $key = self::getApiKey();
        return !empty($key);
    }
}
