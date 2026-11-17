<?php
/**
 * config/AIConfig.php
 * File cấu hình và quản lý AI cho Hệ thống JobLink
 * Hỗ trợ chế độ Local AI Engine (chạy 100% nội bộ, không cần API ngoài) và Google Gemini AI
 */

require_once __DIR__ . '/Database.php';

class AIConfig {
    // Chế độ AI mặc định: 'cloud' (Google Gemini AI Cloud kết hợp CSDL JobLink)
    public const DEFAULT_ENGINE_MODE = 'cloud';
    public const GOOGLE_API_KEY      = '';
    public const GEMINI_MODEL        = 'gemini-3.5-flash-lite';
    public const API_BASE_URL        = 'https://generativelanguage.googleapis.com/v1beta/models/';
    public const REQUEST_TIMEOUT     = 20;

    private static $cachedSettings = null;

    /**
     * Danh sách các model Gemini được hỗ trợ khi bật chế độ Gemini ngoài
     */
    public static function getSupportedModels() {
        return [
            'gemini-3.5-flash-lite' => [
                'name' => 'Gemini 3.5 Flash-Lite (Google AI - Khuyên dùng, Ổn Định nhất)',
                'desc' => 'Tốc độ siêu tốc, ổn định tuyệt đối, phản hồi chuẩn xác cho tìm việc, đề xuất CSDL và chatbot hỗ trợ.',
                'badge' => 'Google Gemini • Khuyên dùng'
            ],
            'gemini-flash-latest' => [
                'name' => 'Gemini Flash latest (Google AI - Bản mới nhất)',
                'desc' => 'Phiên bản Gemini Flash mới nhất phát hành từ Google AI.',
                'badge' => 'mới nhất'
            ],
            'gemini-3.7-flash' => [
                'name' => 'Gemini 3.7 Flash (Google AI - Suy luận nâng cao)',
                'desc' => 'Tích hợp khả năng suy luận sâu và phân tích dữ liệu tuyển dụng quy mô lớn.',
                'badge' => 'Nâng cao'
            ],
            'local-engine' => [
                'name' => 'JobLink Local AI Engine (Chạy nội bộ Dự phòng)',
                'desc' => 'Tự động chạy cục bộ 100%, phản hồi < 5ms khi không có kết nối Internet.',
                'badge' => 'Dự phòng nội bộ'
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
            'ai_engine_mode'     => self::DEFAULT_ENGINE_MODE, // 'local' hoặc 'gemini'
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
     * Lấy chế độ chạy AI: 'local' (chạy nội bộ) hoặc 'gemini' (API Google ngoài)
     */
    public static function getEngineMode() {
        return self::get('ai_engine_mode', self::DEFAULT_ENGINE_MODE);
    }

    /**
     * Kiểm tra xem hệ thống có đang ở chế độ Local engine không
     */
    public static function isLocalMode() {
        $mode = self::getEngineMode();
        if ($mode === 'local') return true;
        $key = self::getApiKey();
        // Nếu không có API Key thì tự động chạy chế độ Local
        return empty($key);
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
     * Lấy model Gemini đang cấu hình
     */
    public static function getModel() {
        $model = self::get('gemini_model');
        return !empty($model) ? trim($model) : self::GEMINI_MODEL;
    }

    /**
     * Lấy tên hiển thị của Engine AI hiện tại
     */
    public static function getEngineName() {
        return self::isLocalMode() ? 'JobLink Local AI Engine' : 'Google Gemini Cloud AI';
    }

    /**
     * Kiểm tra xem AI đã sẵn sàng hoạt động hay chưa
     * Ở chế độ Local engine: Luôn luôn sẵn sàng 100%, không cần bất kỳ API Key nào!
     */
    public static function isConfigured() {
        $isActive = self::get('gemini_is_active', '1');
        if ($isActive === '0' || $isActive === false || $isActive === 0) return false;

        // Chế độ Local: Sẵn sàng 100% không cần API Key từ ngoài!
        if (self::isLocalMode()) {
            return true;
        }

        $key = self::getApiKey();
        return !empty($key);
    }
}
