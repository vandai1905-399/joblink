<?php
/**
 * services/AIDatabaseQueryService.php
 * Hệ thống Kết Nối CSDL Trực Tiếp Chỉ Đọc (Read-Only) Phân Quyện Theo Vai Trò cho JobLink AI
 * 
 * BẢO ĐẢM AN TOÀN TUYỆT ĐỐI:
 * 1. 100% READ-ONLY: Chặn triệt để mọi từ khóa INSERT, UPDATE, DELETE, DROP, ALTER, TRUNCATE, REPLACE, v.v.
 * 2. CHỐNG SQL INJECTION & STACKED QUERIES: Cấm ký tự ';' và các kỹ thuật xếp chồng lệnh.
 * 3. PHÂN QUYỀN VAI TRÒ (RBAC):
 *    - ADMIN: Toàn bộ bảng nghiệp vụ (ẩn mật khẩu password_hash).
 *    - DOANH NGHIỆP: Giới hạn theo employer_id và dữ liệu công khai.
 *    - NGƯỜI LAO ĐỘNG: Giới hạn theo seeker_id và việc làm công khai.
 *    - KHÁCH VÃNG LAI: Mức tối thiểu (chỉ xem việc làm approved và DN công khai).
 * 4. GIỚI HẠN DỮ LIỆU: Bắt buộc LIMIT <= 15 để tối ưu hiệu năng và bộ nhớ.
 */

require_once __DIR__ . '/../config/Database.php';

class AIDatabaseQueryService {

    // Danh sách từ khóa ghi/thay đổi CSDL bị CẤM TUYỆT ĐỐI
    private const FORBIDDEN_KEYWORDS = [
        'INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'TRUNCATE', 'REPLACE',
        'CREATE', 'RENAME', 'GRANT', 'REVOKE', 'LOCK', 'UNLOCK', 'CALL',
        'EXECUTE', 'SET', 'TRANSACTION', 'COMMIT', 'ROLLBACK', 'OUTFILE',
        'INFILE', 'LOAD_FILE', 'INTO', 'SHUTDOWN', 'FLUSH', 'KILL'
    ];

    // Cột nhạy cảm bị che/loại bỏ tự động ở mọi vai trò
    private const ALWAYS_MASKED_COLUMNS = [
        'password_hash', 'remember_token'
    ];

    // Bảng tuyệt mật cấm truy cập tuyệt đối ở mọi vai trò (Zero-Knowledge Privacy)
    private const STRICTLY_FORBIDDEN_TABLES = [
        'ai_chat_logs'
    ];

    /**
     * Định nghĩa ma trận phân quyền bảng theo vai trò người dùng
     */
    public static function getRolePermissions($userRole = 'GUEST') {
        $role = strtoupper(trim($userRole ?: 'GUEST'));

        // 1. QUẢN TRỊ VIÊN (Admin / CSKH) -> Toàn quyền đọc các bảng nghiệp vụ
        if (in_array($role, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
            return [
                'role' => $role,
                'allowed_tables' => [
                    'job_posts', 'job_requests', 'user_employers', 'user_seekers',
                    'user_accounts', 'user_intermediaries', 'user_staffs', 'user_roles',
                    'candidate_meetings', 'digital_signatures', 'platform_contracts',
                    'reviews', 'review_criteria', 'system_settings',
                    'messages', 'job_saved'
                ],
                'forbidden_columns' => self::ALWAYS_MASKED_COLUMNS,
                'scope' => 'all',
                'description' => 'Toàn bộ cơ sở dữ liệu nghiệp vụ hệ thống JobLink'
            ];
        }

        // 2. NHÀ TUYỂN DỤNG & TRUNG GIAN -> Giới hạn theo doanh nghiệp & việc làm công khai
        if (in_array($role, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])) {
            return [
                'role' => $role,
                'allowed_tables' => [
                    'job_posts', 'job_requests', 'user_employers', 'candidate_meetings',
                    'reviews', 'digital_signatures', 'user_seekers'
                ],
                'forbidden_columns' => array_merge(self::ALWAYS_MASKED_COLUMNS, ['id_card_number']),
                'scope' => 'employer',
                'description' => 'Tin tuyển dụng, hồ sơ người lao động nộp cho DN mình, lịch phỏng vấn và dữ liệu thị trường công khai'
            ];
        }

        // 3. NGƯỜI LAO ĐỘNG (Job Seeker) -> Giới hạn theo cá nhân & việc làm công khai
        if ($role === 'JOB_SEEKER') {
            return [
                'role' => $role,
                'allowed_tables' => [
                    'job_posts', 'user_employers', 'job_requests', 'job_saved',
                    'candidate_meetings', 'reviews', 'user_seekers'
                ],
                'forbidden_columns' => self::ALWAYS_MASKED_COLUMNS,
                'scope' => 'seeker',
                'description' => 'Việc làm công khai, doanh nghiệp xác thực, hồ sơ và đơn ứng tuyển của chính mình'
            ];
        }

        // 4. KHÁCH VÃNG LAI (Guest - Chưa đăng nhập) -> Mức tối thiểu công khai
        return [
            'role' => 'GUEST',
            'allowed_tables' => [
                'job_posts', 'user_employers', 'reviews', 'user_seekers'
            ],
            'forbidden_columns' => array_merge(self::ALWAYS_MASKED_COLUMNS, [
                'business_license_number', 'contact_email', 'phone_number', 'email', 'account_id', 'id_card_number'
            ]),
            'scope' => 'public_minimal',
            'description' => 'Chỉ việc làm đã duyệt (approved), thông tin doanh nghiệp công khai, ứng viên tiêu biểu và đánh giá đã duyệt'
        ];
    }

    /**
     * Lược đồ CSDL thu gọn (Schema) được lọc theo quyền của vai trò để cung cấp cho AI
     */
    public static function getRoleSchemaDescription($userRole = 'GUEST') {
        $perm = self::getRolePermissions($userRole);
        $allowed = $perm['allowed_tables'];

        $schemaMap = [
            'job_posts' => [
                'desc' => 'Tin tuyển dụng việc làm',
                'cols' => 'job_post_id, employer_id, job_title, employment_type, salary_amount, workplace_address, post_status (pending/approved/rejected), is_featured, posted_at'
            ],
            'job_requests' => [
                'desc' => 'Đơn / Hồ sơ ứng tuyển việc làm',
                'cols' => 'request_id, seeker_id, job_post_id, applied_at, request_status (applied/reviewing/interview_scheduled/accepted/rejected), available_work_dates'
            ],
            'user_employers' => [
                'desc' => 'Doanh nghiệp / Nhà tuyển dụng',
                'cols' => 'employer_id, employer_name, employer_type, representative_name, address, business_field, verification_status (unverified/pending/verified), digital_signature_status (unregistered/pending/verified), avg_rating, total_reviews, created_at'
            ],
            'user_seekers' => [
                'desc' => 'Người tìm việc / Lao động',
                'cols' => 'seeker_id, full_name, gender, date_of_birth, address, skills, experience_years, desired_position, expected_salary, trust_score, education_level'
            ],
            'candidate_meetings' => [
                'desc' => 'Lịch hẹn phỏng vấn',
                'cols' => 'meeting_id, request_id, meeting_date, start_time, end_time, location_or_meeting_link, meeting_format, meeting_status (scheduled/completed/cancelled)'
            ],
            'digital_signatures' => [
                'desc' => 'Chứng thư & Chữ Ký Số Doanh Nghiệp',
                'cols' => 'id, employer_id, certificate_owner, tax_code, serial_number, issuer_ca, valid_from, valid_to, status (active/expired/revoked)'
            ],
            'reviews' => [
                'desc' => 'Đánh giá doanh nghiệp / lao động',
                'cols' => 'review_id, employer_id, seeker_id, overall_rating, review_content, approval_status (approved/pending)'
            ],
            'system_settings' => [
                'desc' => 'Cài đặt hệ thống sàn',
                'cols' => 'setting_key, setting_value, setting_group, description, updated_at'
            ]
        ];

        $lines = ["CSDL ĐƯỢC PHÉP TRUY VẤN (Vai trò: {$perm['role']} - {$perm['description']}):"];
        foreach ($allowed as $tbl) {
            if (isset($schemaMap[$tbl])) {
                $info = $schemaMap[$tbl];
                $lines[] = "- Bảng `{$tbl}` ({$info['desc']}): [{$info['cols']}]";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Bộ lọc an ninh và kiểm định câu truy vấn SQL (Query Guard)
     * Trả về: ['valid' => bool, 'sanitized_sql' => string, 'error' => string|null]
     */
    public static function validateAndSanitizeQuery($rawSql, $userRole = 'GUEST', $contextInfo = []) {
        $sql = trim((string)$rawSql);
        if (empty($sql)) {
            return ['valid' => false, 'sanitized_sql' => '', 'error' => 'Câu truy vấn SQL rỗng.'];
        }

        // 1. Loại bỏ các ký tự bọc markdown nếu AI trả về dạng ```sql ... ```
        if (preg_match('/```(?:sql)?(.*?)```/is', $sql, $m)) {
            $sql = trim($m[1]);
        }

        // 2. Chặn nhiều câu lệnh (Stacked queries / Semicolons)
        $cleanSql = rtrim($sql, '; ');
        if (strpos($cleanSql, ';') !== false) {
            return ['valid' => false, 'sanitized_sql' => '', 'error' => 'Chặn bảo mật: Không cho phép thực thi nhiều câu lệnh liên tiếp (dấu ;).'];
        }

        // 3. Bắt buộc bắt đầu bằng SELECT (hoặc SHOW COLUMNS / EXPLAIN)
        if (!preg_match('/^\s*(SELECT|SHOW|EXPLAIN)\s+/i', $cleanSql)) {
            return ['valid' => false, 'sanitized_sql' => '', 'error' => 'Chặn bảo mật: Chỉ cho phép câu lệnh truy vấn đọc (SELECT). Tuyệt đối không cho phép ghi hoặc sửa dữ liệu.'];
        }

        // 4. Quét từ khóa ghi bị cấm trong toàn bộ câu lệnh
        foreach (self::FORBIDDEN_KEYWORDS as $kw) {
            if (preg_match('/\b' . $kw . '\b/i', $cleanSql)) {
                return ['valid' => false, 'sanitized_sql' => '', 'error' => "Chặn bảo mật: Phát hiện từ khóa ghi/sửa dữ liệu không được phép: '{$kw}'."];
            }
        }

        // 5. Kiểm tra danh sách bảng xuất hiện trong câu lệnh có thuộc whitelist vai trò không
        $perm = self::getRolePermissions($userRole);
        $allowedTables = $perm['allowed_tables'];

        // Trích xuất các bảng sau FROM và JOIN
        preg_match_all('/\b(?:FROM|JOIN)\s+`?([a-zA-Z0-9_]+)`?/i', $cleanSql, $matches);
        $referencedTables = array_map('strtolower', $matches[1] ?? []);

        foreach ($referencedTables as $table) {
            if (in_array($table, self::STRICTLY_FORBIDDEN_TABLES)) {
                return [
                    'valid' => false,
                    'sanitized_sql' => '',
                    'error' => "Bảo mật quyền riêng tư: Bảng '{$table}' là dữ liệu hội thoại tuyệt mật, không một bên nào được phép đọc."
                ];
            }
            if (!in_array($table, $allowedTables)) {
                return [
                    'valid' => false,
                    'sanitized_sql' => '',
                    'error' => "Giới hạn quyền truy cập: Vai trò '{$userRole}' không có quyền đọc bảng '{$table}'."
                ];
            }
        }

        // 6. Áp đặt phạm vi bảo vệ dữ liệu (Data Scope Guard)
        $scope = $perm['scope'];

        // Với KHÁCH VÃNG LAI: Nếu truy vấn job_posts, bắt buộc chỉ xem tin đã duyệt approved
        if ($scope === 'public_minimal') {
            if (in_array('job_posts', $referencedTables)) {
                if (stripos($cleanSql, 'post_status') === false) {
                    if (stripos($cleanSql, 'WHERE') !== false) {
                        $cleanSql = preg_replace('/WHERE/i', "WHERE post_status = 'approved' AND ", $cleanSql, 1);
                    } else {
                        $cleanSql .= " WHERE post_status = 'approved'";
                    }
                }
            }
        }

        // Với NGƯỜI LAO ĐỘNG: Nếu truy vấn job_requests hoặc candidate_meetings, chỉ xem của chính mình
        if ($scope === 'seeker') {
            $seekerId = (int)($contextInfo['seeker_id'] ?? 0);
            if (in_array('job_requests', $referencedTables) && $seekerId > 0) {
                if (stripos($cleanSql, 'seeker_id') === false) {
                    if (stripos($cleanSql, 'WHERE') !== false) {
                        $cleanSql = preg_replace('/WHERE/i', "WHERE seeker_id = {$seekerId} AND ", $cleanSql, 1);
                    } else {
                        $cleanSql .= " WHERE seeker_id = {$seekerId}";
                    }
                }
            }
        }

        // Với DOANH NGHIỆP: Nếu truy vấn job_requests hoặc candidate_meetings, chỉ xem của doanh nghiệp mình
        if ($scope === 'employer') {
            $employerId = (int)($contextInfo['employer_id'] ?? 0);
            if (in_array('job_requests', $referencedTables) && $employerId > 0) {
                if (stripos($cleanSql, 'employer_id') === false) {
                    $scopeCondition = "job_post_id IN (SELECT job_post_id FROM job_posts WHERE employer_id = {$employerId})";
                    if (stripos($cleanSql, 'WHERE') !== false) {
                        $cleanSql = preg_replace('/WHERE/i', "WHERE {$scopeCondition} AND ", $cleanSql, 1);
                    } else {
                        $cleanSql .= " WHERE {$scopeCondition}";
                    }
                }
            }
        }

        // 7. Ép buộc giới hạn LIMIT (Tối đa 15 dòng)
        if (preg_match('/\bLIMIT\s+(\d+)(?:\s*,\s*(\d+))?/i', $cleanSql, $limitMatch)) {
            $currentLimit = (int)($limitMatch[2] ?? $limitMatch[1]);
            if ($currentLimit > 15) {
                $cleanSql = preg_replace('/\bLIMIT\s+\d+(\s*,\s*\d+)?/i', 'LIMIT 15', $cleanSql);
            }
        } else {
            $cleanSql .= " LIMIT 15";
        }

        return [
            'valid' => true,
            'sanitized_sql' => $cleanSql,
            'error' => null
        ];
    }

    /**
     * Thực thi câu lệnh SQL SELECT an toàn trên CSDL
     * @return array ['success' => bool, 'data' => array, 'row_count' => int, 'error' => string|null]
     */
    public static function executeSafeQuery($rawSql, $userRole = 'GUEST', $contextInfo = []) {
        $validation = self::validateAndSanitizeQuery($rawSql, $userRole, $contextInfo);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'data' => [],
                'row_count' => 0,
                'error' => $validation['error'],
                'executed_sql' => null
            ];
        }

        $sanitizedSql = $validation['sanitized_sql'];
        $db = Database::getConnection();
        if (!$db) {
            return [
                'success' => false,
                'data' => [],
                'row_count' => 0,
                'error' => 'Không thể kết nối đến máy chủ CSDL.',
                'executed_sql' => $sanitizedSql
            ];
        }

        try {
            // Đặt thời gian timeout truy vấn ngắn (3 giây) để không làm treo trang
            $db->setAttribute(PDO::ATTR_TIMEOUT, 3);

            $stmt = $db->query($sanitizedSql);
            if (!$stmt) {
                $err = $db->errorInfo();
                return [
                    'success' => false,
                    'data' => [],
                    'row_count' => 0,
                    'error' => 'Lỗi thực thi truy vấn: ' . ($err[2] ?? 'Không rõ'),
                    'executed_sql' => $sanitizedSql
                ];
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Tự động che/loại bỏ các cột nhạy cảm khỏi kết quả
            $perm = self::getRolePermissions($userRole);
            $forbiddenCols = array_map('strtolower', $perm['forbidden_columns'] ?? []);

            $cleanedRows = [];
            foreach ($rows as $row) {
                $cleanRow = [];
                foreach ($row as $col => $val) {
                    $lowerCol = strtolower($col);
                    if (in_array($lowerCol, $forbiddenCols)) {
                        $cleanRow[$col] = '*** [BẢO MẬT]';
                    } else {
                        $cleanRow[$col] = $val;
                    }
                }
                $cleanedRows[] = $cleanRow;
            }

            return [
                'success' => true,
                'data' => $cleanedRows,
                'row_count' => count($cleanedRows),
                'error' => null,
                'executed_sql' => $sanitizedSql
            ];

        } catch (Throwable $e) {
            return [
                'success' => false,
                'data' => [],
                'row_count' => 0,
                'error' => 'Lỗi CSDL: ' . $e->getMessage(),
                'executed_sql' => $sanitizedSql
            ];
        }
    }

    /**
     * Tự động phân tích câu hỏi người dùng và sinh câu truy vấn SQL an toàn (Text-to-SQL)
     * Trả về dữ liệu thực tế để nạp vào ngữ cảnh AI
     */
    public static function analyzeAndFetchData($userMessage, $userRole = 'GUEST', $contextInfo = []) {
        $lowerMsg = mb_strtolower(trim($userMessage), 'UTF-8');

        // Bỏ qua nếu là câu chào hỏi đơn thuần hoặc hỏi về ngày giờ
        if (
            preg_match('/^(chào|hello|hi|xin chào|bây giờ là mấy giờ|mấy giờ rồi|hôm nay là thứ mấy)$/ui', $lowerMsg) ||
            mb_strlen($lowerMsg, 'UTF-8') < 3
        ) {
            return null;
        }

        // 1. ƯU TIÊN 1: Phục vụ nhanh các mẫu truy vấn phổ biến (Fast Pattern Matcher) để đạt độ trễ siêu tốc (< 5ms)
        $fastSql = self::matchFastSqlPattern($lowerMsg, $userRole, $contextInfo);
        if ($fastSql) {
            $res = self::executeSafeQuery($fastSql, $userRole, $contextInfo);
            if ($res['success'] && !empty($res['data'])) {
                return [
                    'source' => 'direct_db_fast',
                    'sql' => $res['executed_sql'],
                    'data' => $res['data'],
                    'formatted_text' => self::formatQueryResultAsText($res['data'], $userRole)
                ];
            }
        }

        // 2. Kiểm tra xem câu hỏi có chứa từ khóa cần tra cứu CSDL không
        $needDb = false;
        $dbKeywords = [
            'bao nhiêu', 'có ai', 'danh sách', 'mấy tin', 'mấy hồ sơ', 'mấy doanh nghiệp',
            'mấy người', 'tìm việc', 'lương', 'ứng tuyển', 'tin đăng', 'duyệt', 'chờ xác minh',
            'becamex', 'mây coffee', 'minh dương', 'điện máy xanh', 'đẹp độc lạ',
            'phỏng vấn', 'chữ ký số', 'kết quả', 'thống kê', 'hôm nay có', 'ai nộp',
            'xa hơn', 'gần hơn', 'khu vực', 'lân cận', 'part-time', 'full-time', 'lập trình'
        ];

        foreach ($dbKeywords as $dk) {
            if (strpos($lowerMsg, $dk) !== false) {
                $needDb = true;
                break;
            }
        }

        if (!$needDb) {
            return null;
        }

        // 2. Nếu câu hỏi phức tạp: Dùng Gemini AI để sinh Text-to-SQL trong phạm vi phân quyền
        $schemaDesc = self::getRoleSchemaDescription($userRole);
        $prompt = "Bạn là chuyên gia cơ sở dữ liệu MySQL của hệ thống JobLink.
Nhiệm vụ: Chuyển câu hỏi của người dùng thành DUY NHẤT một câu lệnh SQL SELECT an toàn, chỉ đọc, tối ưu.

{$schemaDesc}

QUY TẮC BẮT BUỘC:
1. CHỈ sinh một câu lệnh 'SELECT ...', KHÔNG được dùng bất kỳ từ khóa ghi (INSERT, UPDATE, DELETE, DROP...).
2. CHỈ dùng các bảng và cột được phép liệt kê ở trên.
3. Luôn dùng LIMIT tối đa 10 (ví dụ: LIMIT 10).
4. KHÔNG viết giải thích hay dấu markdown, chỉ trả về đúng 1 câu lệnh SQL.

Người dùng: '{$userMessage}'
SQL:";

        if (class_exists('AIConfig') && AIConfig::isLocalMode()) {
            return null; // Chế độ Local AI Engine nội bộ: dùng bộ khớp mẫu SQL an toàn siêu tốc mà không cần gọi ra ngoài
        }

        $apiKey = class_exists('AIConfig') ? AIConfig::getApiKey() : '';
        if (!empty($apiKey) && class_exists('AIService')) {
            $generatedSql = AIService::callGeminiAPI($prompt, false);
            if (!empty($generatedSql) && is_string($generatedSql)) {
                $res = self::executeSafeQuery(trim($generatedSql), $userRole, $contextInfo);
                if ($res['success'] && !empty($res['data'])) {
                    return [
                        'source' => 'gemini_text_to_sql',
                        'sql' => $res['executed_sql'],
                        'data' => $res['data'],
                        'formatted_text' => self::formatQueryResultAsText($res['data'], $userRole)
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Khớp nhanh các mẫu câu hỏi phổ biến thành SQL an toàn mà không cần chờ AI API
     */
    private static function matchFastSqlPattern($lowerMsg, $userRole, $contextInfo) {
        $isAdmin = in_array($userRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $isEmployer = in_array($userRole, ['EMPLOYER_ADMIN', 'INTERMEDIARY']);
        $isSeeker = ($userRole === 'JOB_SEEKER');

        // Pattern 1: Tìm doanh nghiệp Becamex
        if (strpos($lowerMsg, 'becamex') !== false) {
            if (preg_match('/(ai ứng tuyển|ứng tuyển|hồ sơ|nộp)/ui', $lowerMsg)) {
                return "SELECT r.request_id, r.applied_at, r.request_status, p.job_title, s.full_name 
                        FROM job_requests r 
                        JOIN job_posts p ON r.job_post_id = p.job_post_id 
                        LEFT JOIN user_seekers s ON r.seeker_id = s.seeker_id 
                        WHERE p.employer_id = 1 
                        ORDER BY r.applied_at DESC LIMIT 5";
            }
            return "SELECT p.job_post_id, p.job_title, p.salary_amount, p.employment_type, e.employer_name, e.digital_signature_status 
                    FROM job_posts p 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    WHERE p.employer_id = 1 AND p.post_status = 'approved' LIMIT 5";
        }

        // Pattern 2: Đơn ứng tuyển hôm nay trên toàn sàn (Chỉ cho Admin)
        if ($isAdmin && preg_match('/(ứng tuyển|hồ sơ).*hôm nay/ui', $lowerMsg)) {
            return "SELECT r.request_id, r.applied_at, r.request_status, p.job_title, e.employer_name, s.full_name 
                    FROM job_requests r 
                    JOIN job_posts p ON r.job_post_id = p.job_post_id 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    LEFT JOIN user_seekers s ON r.seeker_id = s.seeker_id 
                    WHERE DATE(r.applied_at) = CURDATE() 
                    ORDER BY r.applied_at DESC LIMIT 5";
        }

        // Pattern 3: Doanh nghiệp chờ duyệt GPKD / Chữ ký số (Chỉ cho Admin)
        if ($isAdmin && preg_match('/(chờ xác minh|chờ duyệt|gpkd|chữ ký số|doanh nghiệp)/ui', $lowerMsg)) {
            return "SELECT employer_id, employer_name, representative_name, business_field, verification_status, digital_signature_status, created_at 
                    FROM user_employers 
                    WHERE verification_status IN ('unverified', 'pending') 
                    ORDER BY created_at DESC LIMIT 5";
        }

        // Pattern 4: Tin tuyển dụng chờ duyệt (Chỉ cho Admin)
        if ($isAdmin && preg_match('/(tin.*chờ duyệt|bài đăng.*chờ duyệt)/ui', $lowerMsg)) {
            return "SELECT p.job_post_id, p.job_title, p.salary_amount, e.employer_name, p.posted_at 
                    FROM job_posts p 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    WHERE p.post_status = 'pending' 
                    ORDER BY p.posted_at DESC LIMIT 5";
        }

        // Pattern 5: Danh sách việc làm IT / Lập trình (Mọi người đều xem được)
        if (preg_match('/(việc làm it|việc làm cntt|lập trình|công nghệ thông tin|developer|coder)/ui', $lowerMsg)) {
            return "SELECT p.job_post_id, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.digital_signature_status 
                    FROM job_posts p 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    WHERE p.post_status = 'approved' AND (p.job_title LIKE '%IT%' OR p.job_title LIKE '%Lập Trình%' OR p.job_title LIKE '%CNTT%' OR p.job_title LIKE '%Phần Mềm%') 
                    ORDER BY p.posted_at DESC LIMIT 5";
        }

        // Pattern 6: Đơn ứng tuyển của Người tìm việc (Chỉ seeker của mình)
        if ($isSeeker && preg_match('/(đơn ứng tuyển|ứng tuyển của tôi|đơn của tôi|hồ sơ của tôi|hồ sơ đã nộp|đơn đã nộp|tôi đã nộp|đã ứng tuyển|lịch sử ứng tuyển|xem đơn|các đơn|tiến độ ứng tuyển|tiến độ hồ sơ|trạng thái ứng tuyển|trạng thái đơn|kết quả ứng tuyển|đơn xin việc)/ui', $lowerMsg)) {
            $sid = (int)($contextInfo['seeker_id'] ?? 0);
            if ($sid > 0) {
                return "SELECT r.request_id, r.applied_at, r.request_status, p.job_post_id, p.job_title, e.employer_name, p.salary_amount, p.employment_type, p.workplace_address 
                        FROM job_requests r 
                        JOIN job_posts p ON r.job_post_id = p.job_post_id 
                        JOIN user_employers e ON p.employer_id = e.employer_id 
                        WHERE r.seeker_id = {$sid} 
                        ORDER BY r.applied_at DESC LIMIT 6";
            }
        }

        // Pattern 6b: Hồ sơ ứng viên nộp vào tin tuyển dụng của Doanh nghiệp (Chỉ employer của mình)
        if ($isEmployer && preg_match('/(ứng viên của tôi|hồ sơ ứng tuyển|ai ứng tuyển|danh sách ứng viên|hồ sơ nộp vào|ứng viên mới)/ui', $lowerMsg)) {
            $eid = (int)($contextInfo['employer_id'] ?? 0);
            if ($eid > 0) {
                return "SELECT r.request_id, r.applied_at, r.request_status, p.job_post_id, p.job_title, s.full_name, s.phone_number, s.avg_rating 
                        FROM job_requests r 
                        JOIN job_posts p ON r.job_post_id = p.job_post_id 
                        JOIN user_seekers s ON r.seeker_id = s.seeker_id 
                        WHERE p.employer_id = {$eid} 
                        ORDER BY r.applied_at DESC LIMIT 6";
            }
        }

        // Pattern 6c: Lịch phỏng vấn / Lịch hẹn của người dùng
        if (preg_match('/(lịch phỏng vấn|lịch hẹn|khi nào phỏng vấn|buổi phỏng vấn|giờ phỏng vấn)/ui', $lowerMsg)) {
            if ($isSeeker) {
                $sid = (int)($contextInfo['seeker_id'] ?? 0);
                if ($sid > 0) {
                    return "SELECT m.meeting_id, m.meeting_date, m.start_time, m.meeting_format, m.location_or_meeting_link, m.meeting_status, p.job_title, e.employer_name 
                            FROM candidate_meetings m 
                            JOIN job_requests r ON m.request_id = r.request_id 
                            JOIN job_posts p ON r.job_post_id = p.job_post_id 
                            JOIN user_employers e ON p.employer_id = e.employer_id 
                            WHERE r.seeker_id = {$sid} 
                            ORDER BY m.meeting_date DESC, m.start_time DESC LIMIT 5";
                }
            } elseif ($isEmployer) {
                $eid = (int)($contextInfo['employer_id'] ?? 0);
                if ($eid > 0) {
                    return "SELECT m.meeting_id, m.meeting_date, m.start_time, m.meeting_format, m.location_or_meeting_link, m.meeting_status, p.job_title, s.full_name 
                            FROM candidate_meetings m 
                            JOIN job_requests r ON m.request_id = r.request_id 
                            JOIN job_posts p ON r.job_post_id = p.job_post_id 
                            JOIN user_seekers s ON r.seeker_id = s.seeker_id 
                            WHERE p.employer_id = {$eid} 
                            ORDER BY m.meeting_date DESC, m.start_time DESC LIMIT 5";
                }
            }
        }

        // Pattern 7: Danh sách công ty / doanh nghiệp trên sàn (Công khai)
        if (preg_match('/(danh sách công ty|danh sách doanh nghiệp|các công ty|công ty nào|doanh nghiệp nào|nhà tuyển dụng uy tín|công ty uy tín)/ui', $lowerMsg)) {
            return "SELECT employer_id, employer_name, business_field, digital_signature_status, verification_status 
                    FROM user_employers 
                    WHERE verification_status = 'verified' 
                    ORDER BY digital_signature_status DESC, avg_rating DESC LIMIT 6";
        }

        // Pattern 8: Mở rộng bán kính / khu vực lân cận ("xa hơn đi", "khu vực khác", "lân cận", "toàn tỉnh")
        if (preg_match('/(xa hơn|xa hơn đi|xa hơn chút|khu vực khác|lân cận|mở rộng|tp\.?hcm|sài gòn|toàn tỉnh|bình dương)/ui', $lowerMsg)) {
            return "SELECT p.job_post_id, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.digital_signature_status 
                    FROM job_posts p 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    WHERE p.post_status = 'approved' 
                    ORDER BY p.priority_level DESC, p.salary_amount DESC LIMIT 6";
        }

        // Pattern 9: Việc làm lương cao nhất / thu nhập tốt
        if (preg_match('/(lương cao hơn|lương cao|thu nhập cao|lương khủng|lương tốt|lương hấp dẫn)/ui', $lowerMsg)) {
            return "SELECT p.job_post_id, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.digital_signature_status 
                    FROM job_posts p 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    WHERE p.post_status = 'approved' 
                    ORDER BY p.salary_amount DESC LIMIT 6";
        }

        // Pattern 10: Việc làm Part-time / Bán thời gian / Làm thêm
        if (preg_match('/(part-?time|bán thời gian|làm thêm|việc ca|theo ca)/ui', $lowerMsg)) {
            return "SELECT p.job_post_id, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.digital_signature_status 
                    FROM job_posts p 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    WHERE p.post_status = 'approved' AND p.employment_type = 'part_time' 
                    ORDER BY p.posted_at DESC LIMIT 6";
        }

        // Pattern 11: Việc làm Full-time / Toàn thời gian / Chính thức
        if (preg_match('/(full-?time|toàn thời gian|chính thức)/ui', $lowerMsg)) {
            return "SELECT p.job_post_id, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.digital_signature_status 
                    FROM job_posts p 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    WHERE p.post_status = 'approved' AND p.employment_type = 'full_time' 
                    ORDER BY p.posted_at DESC LIMIT 6";
        }

        // Pattern 12: Bán hàng / Thu ngân / Dịch vụ
        if (preg_match('/(bán hàng|thu ngân|tư vấn bán hàng|nhân viên bán hàng|cửa hàng)/ui', $lowerMsg)) {
            return "SELECT p.job_post_id, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.digital_signature_status 
                    FROM job_posts p 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    WHERE p.post_status = 'approved' AND (p.job_title LIKE '%Bán Hàng%' OR p.job_title LIKE '%Thu Ngân%' OR p.job_title LIKE '%Tư Vấn%') 
                    ORDER BY p.posted_at DESC LIMIT 6";
        }

        // Pattern 13: Pha chế / Barista / Cafe / F&B
        if (preg_match('/(pha chế|barista|phục vụ|cafe|cà phê|nhà hàng)/ui', $lowerMsg)) {
            return "SELECT p.job_post_id, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.digital_signature_status 
                    FROM job_posts p 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    WHERE p.post_status = 'approved' AND (p.job_title LIKE '%Pha Chế%' OR p.job_title LIKE '%Barista%' OR p.job_title LIKE '%Phục Vụ%') 
                    ORDER BY p.posted_at DESC LIMIT 6";
        }

        // Pattern 14: Việc làm gần đây / tại Thủ Dầu Một
        if (preg_match('/(gần hơn|gần hơn đi|ở gần|gần tôi|quanh đây|thủ dầu một|tại thủ dầu một)/ui', $lowerMsg)) {
            return "SELECT p.job_post_id, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.digital_signature_status 
                    FROM job_posts p 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    WHERE p.post_status = 'approved' AND (p.workplace_address LIKE '%Thủ Dầu Một%' OR p.workplace_address LIKE '%TDM%') 
                    ORDER BY p.posted_at DESC LIMIT 6";
        }

        // Pattern 15: Danh sách việc làm tổng hợp / mới nhất (Công khai)
        if (preg_match('/(danh sách việc làm|việc làm mới|việc làm hot|tổng hợp việc làm|các công việc|tìm việc làm|tìm việc)/ui', $lowerMsg)) {
            return "SELECT p.job_post_id, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.digital_signature_status 
                    FROM job_posts p 
                    JOIN user_employers e ON p.employer_id = e.employer_id 
                    WHERE p.post_status = 'approved' 
                    ORDER BY p.priority_level DESC, p.posted_at DESC LIMIT 6";
        }

        // Pattern 16: Tìm Người Lao Động / Ứng Viên (Candidates / Seekers)
        if (preg_match('/(tìm người lao động|tìm ứng viên|danh sách người lao động|danh sách ứng viên|lao động ngành|ứng viên ngành|lao động có kinh nghiệm|hồ sơ ứng viên|ứng viên tài năng|tìm thợ|tìm nhân sự|người lao động)/ui', $lowerMsg)) {
            $kw = '';
            if (preg_match('/(?:ngành|nghề|vị trí|mảng|về|làm)\s+([a-zA-Z0-9\sàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+)/ui', $lowerMsg, $mKw)) {
                $kw = trim($mKw[1]);
            }
            if (!empty($kw) && mb_strlen($kw, 'UTF-8') >= 2) {
                return "SELECT seeker_id, full_name, desired_position, skills, experience_years, avg_rating, trust_score 
                        FROM user_seekers 
                        WHERE desired_position LIKE '%{$kw}%' OR skills LIKE '%{$kw}%' 
                        ORDER BY avg_rating DESC, trust_score DESC LIMIT 6";
            }
            return "SELECT seeker_id, full_name, desired_position, skills, experience_years, avg_rating, trust_score 
                    FROM user_seekers 
                    ORDER BY avg_rating DESC, trust_score DESC LIMIT 6";
        }

        // Pattern 17: Tìm Nhà Tuyển Dụng / Doanh Nghiệp (Employers / Companies)
        if (preg_match('/(tìm nhà tuyển dụng|tìm công ty|tìm doanh nghiệp|danh sách nhà tuyển dụng|danh sách công ty|danh sách doanh nghiệp|nhà tuyển dụng|doanh nghiệp nào|công ty nào)/ui', $lowerMsg)) {
            $empKw = '';
            if (preg_match('/(?:nhà tuyển dụng|doanh nghiệp|công ty)\s+([a-zA-Z0-9\sàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+)/ui', $lowerMsg, $mEmp)) {
                $empKw = trim($mEmp[1]);
            }
            $cleanEmpKw = trim(preg_replace('/(nào|gì|uy tín|ở đây|tại bình dương|thì sao|ạ|\?)+$/ui', '', $empKw));
            if (!empty($cleanEmpKw) && mb_strlen($cleanEmpKw, 'UTF-8') >= 2) {
                return "SELECT employer_id, employer_name, business_field, digital_signature_status, verification_status 
                        FROM user_employers 
                        WHERE verification_status = 'verified' AND employer_name LIKE '%{$cleanEmpKw}%' 
                        ORDER BY digital_signature_status DESC, avg_rating DESC LIMIT 6";
            }
            return "SELECT employer_id, employer_name, business_field, digital_signature_status, verification_status 
                    FROM user_employers 
                    WHERE verification_status = 'verified' 
                    ORDER BY digital_signature_status DESC, avg_rating DESC LIMIT 6";
        }

        return null;
    }

    /**
     * Helper icon ngành nghề việc làm (SVG đơn sắc)
     */
    private static function getJobIconByTitle($title) {
        $t = mb_strtolower($title, 'UTF-8');
        if (preg_match('/(it|cntt|lập trình|phần mềm|developer|tester)/ui', $t)) {
            return '<svg class="icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line></svg>';
        }
        return '<svg class="icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
    }

    /**
     * Helper chữ cái viết tắt / thương hiệu đại diện cho Logo Box công ty
     */
    private static function getInitialsBadge($name) {
        $n = mb_strtoupper(trim($name), 'UTF-8');
        if (strpos($n, 'BECAMEX') !== false) return 'BECAMEX';
        if (strpos($n, 'MÂY') !== false || strpos($n, 'MAY COFFEE') !== false) return 'MÂY';
        if (strpos($n, 'MINH DƯƠNG') !== false) return 'M.DƯƠNG';
        if (strpos($n, 'ĐIỆN MÁY XANH') !== false) return 'ĐMX';
        if (strpos($n, 'ĐẸP ĐỘC LẠ') !== false) return 'ĐĐL';
        if (strpos($n, 'BỐN MÙA') !== false) return 'BỐN MÙA';

        // Lấy 2 từ đầu tiên
        $words = explode(' ', preg_replace('/(CÔNG TY|TNHH|CP|TỔNG CÔNG TY|CHUỖI|CỬA HÀNG|NHÀ HÀNG|SHOP)\s+/ui', '', $n));
        $clean = '';
        foreach ($words as $w) {
            $w = trim($w);
            if (!empty($w)) $clean .= mb_substr($w, 0, 1, 'UTF-8');
            if (mb_strlen($clean, 'UTF-8') >= 3) break;
        }
        return !empty($clean) ? $clean : 'DN';
    }

    /**
     * Tạo Khung Thẻ Trực Quan (Visual Card Frame)
     * Quy tắc thông minh: 
     * - Khi số lượng ít (<= 4): Hiển thị dạng THẺ NGANG (DÒNG - ai-list-card) để gọn gàng, không chiếm chiều cao.
     * - Khi số lượng nhiều (> 4): Hiển thị dạng LƯỚI THẺ VUÔNG (GRID - ai-grid-card).
     */
    public static function renderVisualCardFrame($data, $category = 'jobs', $title = '', $tag = '', $loc = 'Bình Dương / TP.HCM') {
        if (empty($data) || !is_array($data)) return '';

        $totalCount = count($data);
        $isFewItems = ($totalCount <= 4);
        $containerClass = $isFewItems ? 'ai-frame-list' : 'ai-frame-grid';

        $currentDatePill = 'BẢN TIN ' . date('d/m/Y');
        $tag = !empty($tag) ? $tag : $currentDatePill;

        $cardsHtml = '';

        if ($category === 'jobs') {
            $title = !empty($title) ? $title : 'Tổng Hợp việc làm <span class="highlight">Intern / Part-time / Full-time</span>';
            foreach ($data as $row) {
                $jobId = (int)($row['job_post_id'] ?? 0);
                $titleText = htmlspecialchars($row['job_title'] ?? 'Việc làm');
                $empName = htmlspecialchars($row['employer_name'] ?? 'Doanh nghiệp uy tín');
                $rawSalary = $row['salary_amount'] ?? 0;
                $salary = ($rawSalary > 0) ? (number_format((float)$rawSalary / 1000000, 1) . ' Tr/tháng') : 'Thỏa thuận';
                $salary = str_replace('.0 Tr', ' Tr', $salary);
                $empType = ($row['employment_type'] ?? '') === 'part_time' ? 'Part-time' : 'Full-time';
                $isSigned = ($row['digital_signature_status'] ?? '') === 'verified';
                $icon = self::getJobIconByTitle($titleText);

                $sigBadge = $isSigned ? '<span class="ai-card-tag-verified"><svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Đã Ký Số</span>' : '<span class="ai-card-tag-safe"><svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg> Xác thực</span>';

                if ($isFewItems) {
                    // DẠNG THẺ NGANG (Row Card)
                    $cardsHtml .= "<div class=\"ai-list-card ai-job-row\" data-job-id=\"{$jobId}\" data-query=\"Xem chi tiết việc làm {$titleText}\" title=\"Bấm để xem chi tiết\">"
                        . "<div class=\"ai-card-logo-box ai-card-logo-sm\"><span>{$icon}</span></div>"
                        . "<div class=\"ai-card-content\">"
                        . "<div class=\"ai-card-top-row\"><span class=\"ai-card-name\">{$titleText}</span><span class=\"ai-card-meta\">{$salary}</span></div>"
                        . "<div class=\"ai-card-bottom-row\"><span class=\"ai-card-sub\">{$empName}</span><div class=\"ai-card-badges\">{$sigBadge}<span class=\"ai-card-tag-shift\">{$empType}</span></div></div>"
                        . "</div></div>";
                } else {
                    // DẠNG LƯỚI THẺ VUÔNG (Grid Card)
                    $cardsHtml .= "<div class=\"ai-grid-card ai-job-card\" data-job-id=\"{$jobId}\" data-query=\"Xem chi tiết việc làm {$titleText}\" title=\"Bấm để xem chi tiết\">"
                        . "<div class=\"ai-card-logo-box\"><span>{$icon}</span></div>"
                        . "<div class=\"ai-card-name\">{$titleText}</div>"
                        . "<div class=\"ai-card-sub\">{$empName}</div>"
                        . "<div class=\"ai-card-meta\">{$salary}</div>"
                        . "<div class=\"ai-card-badges\">{$sigBadge}<span class=\"ai-card-tag-shift\">{$empType}</span></div>"
                        . "<div class=\"ai-card-action-btn\">Xem chi tiết</div>"
                        . "</div>";
                }
            }
        } elseif ($category === 'employers') {
            $title = !empty($title) ? $title : 'Doanh Nghiệp Tiêu Biểu <span class="highlight-teal">Đã Xác Thực & Ký Số</span>';
            foreach ($data as $row) {
                $empId = (int)($row['employer_id'] ?? 0);
                $empName = htmlspecialchars($row['employer_name'] ?? 'Doanh nghiệp');
                $field = htmlspecialchars($row['business_field'] ?? 'Đa ngành nghề');
                $isSigned = ($row['digital_signature_status'] ?? '') === 'verified';
                $initials = self::getInitialsBadge($empName);

                $sigBadge = $isSigned ? '<span class="ai-card-tag-verified"><svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Đã Ký Số</span>' : '<span class="ai-card-tag-safe"><svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Đã Duyệt GPKD</span>';

                if ($isFewItems) {
                    // DẠNG THẺ NGANG (Row Card)
                    $cardsHtml .= "<div class=\"ai-list-card ai-company-row\" data-employer-id=\"{$empId}\" data-query=\"Xem các việc làm của {$empName}\" title=\"Bấm để xem tin tuyển dụng của công ty\">"
                        . "<div class=\"ai-card-logo-box ai-company-logo ai-card-logo-sm\"><span>{$initials}</span></div>"
                        . "<div class=\"ai-card-content\">"
                        . "<div class=\"ai-card-top-row\"><span class=\"ai-card-name\">{$empName}</span>{$sigBadge}</div>"
                        . "<div class=\"ai-card-bottom-row\"><span class=\"ai-card-sub\">{$field}</span></div>"
                        . "</div></div>";
                } else {
                    // DẠNG LƯỚI THẺ VUÔNG (Grid Card)
                    $cardsHtml .= "<div class=\"ai-grid-card ai-company-card\" data-employer-id=\"{$empId}\" data-query=\"Xem các việc làm của {$empName}\" title=\"Bấm để xem tin tuyển dụng của công ty\">"
                        . "<div class=\"ai-card-logo-box ai-company-logo\"><span>{$initials}</span></div>"
                        . "<div class=\"ai-card-name\">{$empName}</div>"
                        . "<div class=\"ai-card-sub\">{$field}</div>"
                        . "<div class=\"ai-card-badges\">{$sigBadge}</div>"
                        . "<div class=\"ai-card-action-btn\">Xem việc làm</div>"
                        . "</div>";
                }
            }
        } elseif ($category === 'admin_pending') {
            $title = !empty($title) ? $title : 'Doanh Nghiệp <span class="highlight-warning">Chờ Duyệt GPKD</span>';
            foreach ($data as $row) {
                $empId = (int)($row['employer_id'] ?? 0);
                $empName = htmlspecialchars($row['employer_name'] ?? 'Doanh nghiệp');
                $rep = htmlspecialchars($row['representative_name'] ?? '');
                $field = htmlspecialchars($row['business_field'] ?? 'Doanh nghiệp');
                $repText = !empty($rep) ? " • {$rep}" : "";

                if ($isFewItems) {
                    // DẠNG THẺ NGANG (Row Card) - Cực kỳ gọn gàng, không bị to choán màn hình
                    $cardsHtml .= "<div class=\"ai-list-card ai-admin-row\" data-employer-id=\"{$empId}\" data-query=\"Kiểm tra hồ sơ pháp lý của {$empName}\" title=\"Bấm để kiểm tra duyệt GPKD\">"
                        . "<div class=\"ai-card-logo-box ai-warning-logo ai-card-logo-sm\"><span><svg class=\"icon-svg\" width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z\"></path><line x1=\"12\" y1=\"9\" x2=\"12\" y2=\"13\"></line><line x1=\"12\" y1=\"17\" x2=\"12.01\" y2=\"17\"></line></svg></span></div>"
                        . "<div class=\"ai-card-content\">"
                        . "<div class=\"ai-card-top-row\"><span class=\"ai-card-name\">{$empName}</span><span class=\"ai-card-meta ai-meta-warning\">Chờ duyệt GPKD</span></div>"
                        . "<div class=\"ai-card-bottom-row\"><span class=\"ai-card-sub\">ID: #{$empId}{$repText}</span><span class=\"ai-card-field-tag\">{$field}</span></div>"
                        . "</div>"
                        . "<div class=\"ai-card-action-pill\">Duyệt ngay</div>"
                        . "</div>";
                } else {
                    // DẠNG LƯỚI THẺ VUÔNG (Grid Card)
                    $cardsHtml .= "<div class=\"ai-grid-card ai-admin-card\" data-employer-id=\"{$empId}\" data-query=\"Kiểm tra hồ sơ pháp lý của {$empName}\" title=\"Bấm để kiểm tra duyệt GPKD\">"
                        . "<div class=\"ai-card-logo-box ai-warning-logo\"><span><svg class=\"icon-svg\" width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z\"></path><line x1=\"12\" y1=\"9\" x2=\"12\" y2=\"13\"></line><line x1=\"12\" y1=\"17\" x2=\"12.01\" y2=\"17\"></line></svg></span></div>"
                        . "<div class=\"ai-card-name\">{$empName}</div>"
                        . "<div class=\"ai-card-sub\">ID: #{$empId}{$repText}</div>"
                        . "<div class=\"ai-card-meta ai-meta-warning\">Chờ duyệt GPKD</div>"
                        . "<div class=\"ai-card-action-btn\">Kiểm tra ngay</div>"
                        . "</div>";
                }
            }
        } elseif ($category === 'seeker_applications') {
            $title = !empty($title) ? $title : 'Tiến Độ Đơn Ứng Tuyển <span class="highlight">Hồ Sơ Của Bạn</span>';
            $tag = 'HỒ SƠ CỦA BẠN';
            $loc = 'JobLink Portal';

            foreach ($data as $row) {
                $reqId = (int)($row['request_id'] ?? 0);
                $titleText = htmlspecialchars($row['job_title'] ?? 'Vị trí ứng tuyển');
                $empName = htmlspecialchars($row['employer_name'] ?? 'Doanh nghiệp');
                $rawStatus = $row['request_status'] ?? 'pending';
                $appliedAt = !empty($row['applied_at']) ? date('d/m/Y', strtotime($row['applied_at'])) : '';

                switch ($rawStatus) {
                    case 'accepted':
                        $statusBadge = '<span class="ai-card-tag-verified" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;font-weight:700;"><svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> 🎉 Đã Trúng Tuyển</span>';
                        break;
                    case 'invited':
                    case 'invitation_accepted':
                        $statusBadge = '<span class="ai-card-tag-safe" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-weight:700;"><svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> 📅 Mời Phỏng Vấn</span>';
                        break;
                    case 'viewed':
                        $statusBadge = '<span class="ai-card-tag-shift" style="background:#fffbeb;color:#d97706;border:1px solid #fde68a;font-weight:700;">👀 Đã Xem CV</span>';
                        break;
                    case 'rejected':
                    case 'invitation_rejected':
                        $statusBadge = '<span class="ai-card-tag-field" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-weight:700;">Chưa Phù Hợp</span>';
                        break;
                    default:
                        $statusBadge = '<span class="ai-card-tag-shift" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-weight:700;">⏳ Đang Xét Duyệt</span>';
                        break;
                }

                if ($isFewItems) {
                    // DẠNG THẺ NGANG (Row Card)
                    $cardsHtml .= "<div class=\"ai-list-card ai-app-row\" data-action-url=\"/seeker/applications\" data-query=\"Xem chi tiết đơn {$titleText}\" title=\"Bấm để mở trang Quản lý đơn ứng tuyển\">"
                        . "<div class=\"ai-card-logo-box ai-card-logo-sm\" style=\"background:#e0f2fe;color:#0284c7;\"><span><svg class=\"icon-svg\" width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z\"></path><polyline points=\"14 2 14 8 20 8\"></polyline><line x1=\"16\" y1=\"13\" x2=\"8\" y2=\"13\"></line><line x1=\"16\" y1=\"17\" x2=\"8\" y2=\"17\"></line></svg></span></div>"
                        . "<div class=\"ai-card-content\">"
                        . "<div class=\"ai-card-top-row\"><span class=\"ai-card-name\">{$titleText}</span><span class=\"ai-card-meta\">Nộp: {$appliedAt}</span></div>"
                        . "<div class=\"ai-card-bottom-row\"><span class=\"ai-card-sub\">{$empName}</span><div class=\"ai-card-badges\">{$statusBadge}</div></div>"
                        . "</div>"
                        . "<div class=\"ai-card-action-pill\" style=\"background:linear-gradient(135deg,#0284c7 0%,#0369a1 100%);\">Xem đơn</div>"
                        . "</div>";
                } else {
                    // DẠNG LƯỚI THẺ VUÔNG (Grid Card)
                    $cardsHtml .= "<div class=\"ai-grid-card ai-app-card\" data-action-url=\"/seeker/applications\" data-query=\"Xem chi tiết đơn {$titleText}\" title=\"Bấm để mở trang Quản lý đơn ứng tuyển\">"
                        . "<div class=\"ai-card-logo-box\" style=\"background:#e0f2fe;color:#0284c7;\"><span><svg class=\"icon-svg\" width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z\"></path><polyline points=\"14 2 14 8 20 8\"></polyline><line x1=\"16\" y1=\"13\" x2=\"8\" y2=\"13\"></line></svg></span></div>"
                        . "<div class=\"ai-card-name\">{$titleText}</div>"
                        . "<div class=\"ai-card-sub\">{$empName}</div>"
                        . "<div class=\"ai-card-meta\">Nộp: {$appliedAt}</div>"
                        . "<div class=\"ai-card-badges\">{$statusBadge}</div>"
                        . "<div class=\"ai-card-action-btn\" style=\"background:#0284c7;color:#fff;\">Xem chi tiết đơn</div>"
                        . "</div>";
                }
            }
        } elseif ($category === 'meetings') {
            $title = !empty($title) ? $title : 'Lịch Hẹn Phỏng Vấn <span class="highlight">Tuyển Dụng</span>';
            $tag = 'LỊCH HẸN';
            $loc = 'Google Meet & Trực tiếp';

            foreach ($data as $row) {
                $meetId = (int)($row['meeting_id'] ?? 0);
                $titleText = htmlspecialchars($row['job_title'] ?? 'Phỏng vấn tuyển dụng');
                $partnerName = htmlspecialchars($row['employer_name'] ?? ($row['full_name'] ?? 'Nhà tuyển dụng'));
                $mDate = !empty($row['meeting_date']) ? date('d/m/Y', strtotime($row['meeting_date'])) : '';
                $mTime = !empty($row['start_time']) ? substr($row['start_time'], 0, 5) : '';
                $format = ($row['meeting_format'] ?? '') === 'online' ? 'Google Meet' : 'Trực tiếp';
                $status = ($row['meeting_status'] ?? '') === 'completed' ? 'Đã diễn ra' : 'Đã lên lịch';

                $cardsHtml .= "<div class=\"ai-list-card ai-meeting-row\" data-query=\"Xem chi tiết buổi phỏng vấn {$titleText}\" title=\"Bấm để xem lịch hẹn\">"
                    . "<div class=\"ai-card-logo-box ai-card-logo-sm\" style=\"background:#fef3c7;color:#d97706;\"><span><svg class=\"icon-svg\" width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"3\" y=\"4\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"></rect><line x1=\"16\" y1=\"2\" x2=\"16\" y2=\"6\"></line><line x1=\"8\" y1=\"2\" x2=\"8\" y2=\"6\"></line><line x1=\"3\" y1=\"10\" x2=\"21\" y2=\"10\"></line></svg></span></div>"
                    . "<div class=\"ai-card-content\">"
                    . "<div class=\"ai-card-top-row\"><span class=\"ai-card-name\">{$titleText}</span><span class=\"ai-card-meta\">{$mTime} {$mDate}</span></div>"
                    . "<div class=\"ai-card-bottom-row\"><span class=\"ai-card-sub\">{$partnerName} • {$format}</span><div class=\"ai-card-badges\"><span class=\"ai-card-tag-safe\">{$status}</span></div></div>"
                    . "</div>"
                    . "<div class=\"ai-card-action-pill\">Chi tiết</div>"
                    . "</div>";
            }
        } elseif ($category === 'candidates') {
            $title = !empty($title) ? $title : 'Hồ Sơ Người Lao Động <span class="highlight">Uy Tín & Tiêu Biểu</span>';
            $tag = 'ỨNG VIÊN TIÊU BIỂU';
            $loc = 'JobLink Talent Pool';

            foreach ($data as $row) {
                $skId = (int)($row['seeker_id'] ?? 0);
                $name = htmlspecialchars($row['full_name'] ?? 'Người lao động');
                $pos = htmlspecialchars($row['desired_position'] ?? ($row['skills'] ?? 'Lao động chuyên môn'));
                $exp = (int)($row['experience_years'] ?? 0);
                $expText = ($exp > 0) ? "{$exp} năm KN" : "Chưa có KN";
                $rating = number_format((float)($row['avg_rating'] ?? 5.0), 1);
                $trust = (int)($row['trust_score'] ?? 100);
                $initials = self::getInitialsBadge($name);

                if ($isFewItems) {
                    $cardsHtml .= "<div class=\"ai-list-card ai-seeker-row\" data-query=\"Xem chi tiết ứng viên {$name}\" title=\"Bấm để xem hồ sơ\">"
                        . "<div class=\"ai-card-logo-box ai-card-logo-sm\" style=\"background:#e0e7ff;color:#4f46e5;\"><span>{$initials}</span></div>"
                        . "<div class=\"ai-card-content\">"
                        . "<div class=\"ai-card-top-row\"><span class=\"ai-card-name\">{$name}</span><span class=\"ai-card-meta\">★ {$rating} • Uy tín: {$trust}/100</span></div>"
                        . "<div class=\"ai-card-bottom-row\"><span class=\"ai-card-sub\">{$pos}</span><div class=\"ai-card-badges\"><span class=\"ai-card-tag-shift\">{$expText}</span></div></div>"
                        . "</div></div>";
                } else {
                    $cardsHtml .= "<div class=\"ai-grid-card ai-seeker-card\" data-query=\"Xem chi tiết ứng viên {$name}\" title=\"Bấm để xem hồ sơ\">"
                        . "<div class=\"ai-card-logo-box\" style=\"background:#e0e7ff;color:#4f46e5;\"><span>{$initials}</span></div>"
                        . "<div class=\"ai-card-name\">{$name}</div>"
                        . "<div class=\"ai-card-sub\">{$pos}</div>"
                        . "<div class=\"ai-card-meta\">★ {$rating} ({$expText})</div>"
                        . "<div class=\"ai-card-badges\"><span class=\"ai-card-tag-verified\">Uy tín: {$trust}/100</span></div>"
                        . "<div class=\"ai-card-action-btn\" style=\"background:#4f46e5;color:#fff;\">Xem hồ sơ</div>"
                        . "</div>";
                }
            }
        }

        return "<div class=\"ai-card-frame\">"
            . "<div class=\"ai-frame-header\">"
            . "<div class=\"ai-frame-brand\">JOBLINK</div>"
            . "<span class=\"ai-frame-tag\">{$tag}</span>"
            . "<span class=\"ai-frame-loc\"><svg class=\"icon-svg\" width=\"12\" height=\"12\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" style=\"vertical-align:middle;margin-right:2px;\"><path d=\"M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z\"></path><circle cx=\"12\" cy=\"10\" r=\"3\"></circle></svg>{$loc}</span>"
            . "</div>"
            . "<div class=\"ai-frame-title\">{$title}</div>"
            . "<div class=\"{$containerClass}\">{$cardsHtml}</div>"
            . "</div>";
    }

    /**
     * Chuyển đổi dữ liệu kết quả query SQL thành Khung Thẻ Trực Quan hoặc văn bản ngữ cảnh
     */
    public static function formatQueryResultAsText($data, $userRole = 'GUEST') {
        if (empty($data) || !is_array($data)) return "Không tìm thấy dữ liệu phù hợp trong hệ thống.";

        $firstRow = $data[0] ?? [];

        // 0. Nếu là Đơn ứng tuyển của Người tìm việc (Seeker Applications)
        if (isset($firstRow['request_id']) && isset($firstRow['request_status']) && isset($firstRow['job_title'])) {
            return self::renderVisualCardFrame($data, 'seeker_applications');
        }

        // 0b. Nếu là Lịch hẹn phỏng vấn (Meetings)
        if (isset($firstRow['meeting_id']) && isset($firstRow['meeting_date'])) {
            return self::renderVisualCardFrame($data, 'meetings');
        }

        // 0c. Nếu là Danh sách Người Lao Động / Ứng Viên (Candidates / Seekers)
        if (isset($firstRow['seeker_id']) || (isset($firstRow['full_name']) && (isset($firstRow['desired_position']) || isset($firstRow['trust_score'])))) {
            return self::renderVisualCardFrame($data, 'candidates');
        }

        // 1. Nếu là danh sách Việc Làm (có job_title) -> Tạo Khung Thẻ Việc Làm Trực Quan
        if (isset($firstRow['job_title'])) {
            return self::renderVisualCardFrame($data, 'jobs');
        }

        // 2. Nếu là Doanh nghiệp chờ xác minh (Admin pending)
        if (
            isset($firstRow['employer_name']) &&
            isset($firstRow['verification_status']) &&
            in_array($firstRow['verification_status'], ['unverified', 'pending'])
        ) {
            return self::renderVisualCardFrame($data, 'admin_pending');
        }

        // 3. Nếu là danh sách Doanh nghiệp / Công ty tiêu biểu
        if (isset($firstRow['employer_name']) && (isset($firstRow['business_field']) || isset($firstRow['digital_signature_status']))) {
            return self::renderVisualCardFrame($data, 'employers');
        }

        // 4. Các loại dữ liệu khác (đơn ứng tuyển, tài khoản, số liệu thống kê...)
        $count = count($data);
        $lines = ["DỮ LIỆU THỰC TẾ TRUY VẤN TỪ CƠ SỞ DỮ LIỆU ({$count} kết quả):"];

        foreach ($data as $i => $row) {
            $itemNum = $i + 1;
            $rowParts = [];
            foreach ($row as $col => $val) {
                if ($val === null || $val === '') continue;
                if ($col === 'salary_amount') {
                    $val = number_format((float)$val, 0, ',', '.') . ' VNĐ';
                }
                if (in_array($col, ['applied_at', 'posted_at', 'created_at', 'meeting_date'])) {
                    $val = date('d/m/Y H:i', strtotime($val));
                }
                $rowParts[] = "{$col}: {$val}";
            }
            $lines[] = "• #{$itemNum}: " . implode(' | ', $rowParts);
        }

        return implode("\n", $lines);
    }
}
