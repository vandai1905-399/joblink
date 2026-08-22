<?php
/**
 * services/AIService.php
 * Hệ thống AI Đề Xuất Việc Làm & Khớp Nối Người Lao Động Thông Minh (JobLink AI Matching Engine)
 * Tích hợp Trực tiếp Google Gemini AI API (Gemini 1.5 Flash / 2.0 Flash) kết hợp Hybrid Fallback
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/AIConfig.php';

class AIService {

    // Bộ nhớ đệm RAM tức thì trong một request (Tránh truy vấn CSDL lặp lại trong vòng lặp)
    private static $cachedSeekers = [];
    private static $cachedJobs = [];
    private static $cachedApplied = [];
    private static $cachedSaved = [];
    private static $cachedFits = [];

    // Trọng số các yếu tố đánh giá độ tương thích (Local Engine)
    private const WEIGHT_SKILLS     = 0.40; // 40% Kỹ năng & Chức danh chuyên môn
    private const WEIGHT_LOCATION   = 0.20; // 20% Vị trí địa lý & Khoảng cách di chuyển
    private const WEIGHT_EMPLOYMENT = 0.20; // 20% Hình thức làm việc & Mức lương
    private const WEIGHT_EXPERIENCE = 0.20; // 20% Kinh nghiệm & Uy tín đánh giá

    /**
     * Gửi yêu cầu phân tích/tạo nội dung tới Google Gemini API
     * @param string $prompt
     * @param bool $jsonResponse Yêu cầu Gemini trả về JSON thuần túy
     * @return array|string|null
     */
    public static function callGeminiAPI($prompt, $jsonResponse = true, $customKey = null, $customModel = null) {
        $apiKey = !empty($customKey) ? trim($customKey) : AIConfig::getApiKey();
        if (empty($apiKey)) {
            return null; // Chưa cấu hình API Key -> Dùng engine nội bộ
        }

        $model = !empty($customModel) ? trim($customModel) : AIConfig::getModel();
        $url = AIConfig::API_BASE_URL . $model . ':generateContent?key=' . urlencode($apiKey);
        $temperature = floatval(AIConfig::get('gemini_temperature', 0.2));

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => 2048,
            ]
        ];

        if ($jsonResponse) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT        => AIConfig::REQUEST_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => false, // Chạy ổn định trên Windows / Laragon / Hosting
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            error_log("[AIService::callGeminiAPI Error] HTTP $httpCode - Error: $curlError - Response: $response");
            return null;
        }

        $result = json_decode($response, true);
        $rawText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$rawText) return null;

        if ($jsonResponse) {
            $cleanJson = trim($rawText);
            if (strpos($cleanJson, '```json') !== false) {
                $cleanJson = preg_replace('/^```json\s*/i', '', $cleanJson);
                $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);
            } elseif (strpos($cleanJson, '```') !== false) {
                $cleanJson = preg_replace('/^```\s*/i', '', $cleanJson);
                $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);
            }
            return json_decode(trim($cleanJson), true);
        }

        return $rawText;
    }

    /**
     * Kiểm tra kết nối và tính hợp lệ của Google Gemini API Key
     * @param string $apiKey
     * @param string|null $model
     * @return array
     */
    public static function testApiKey($apiKey, $model = null) {
        $apiKey = trim($apiKey);
        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập API Key để kiểm tra.'
            ];
        }

        $model = !empty($model) ? trim($model) : AIConfig::getModel();
        $model = str_replace('models/', '', $model);
        $startTime = microtime(true);
        $url = AIConfig::API_BASE_URL . $model . ':generateContent?key=' . urlencode($apiKey);

        $prompt = "Chào Gemini, hãy phản hồi ngắn: 'JobLink AI Connection OK' và kèm theo tên model của bạn.";
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 60,
                'temperature' => 0.1
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $latency = round((microtime(true) - $startTime) * 1000);

        if ($curlError) {
            return [
                'success' => false,
                'latency' => $latency,
                'message' => "Lỗi kết nối mạng: $curlError"
            ];
        }

        $result = json_decode($response, true);

        if ($httpCode === 200 && isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $reply = trim($result['candidates'][0]['content']['parts'][0]['text']);
            return [
                'success' => true,
                'latency' => $latency,
                'model' => $model,
                'reply' => $reply,
                'message' => "Kết nối thành công! Phản hồi sau {$latency}ms."
            ];
        }

        $errorMsg = $result['error']['message'] ?? "Lỗi phản hồi (Mã HTTP: $httpCode)";
        return [
            'success' => false,
            'latency' => $latency,
            'http_code' => $httpCode,
            'message' => "Kết nối không thành công: " . $errorMsg
        ];
    }

    /**
     * Lấy danh sách toàn bộ các Model khả dụng từ Google API Key
     * @param string $apiKey
     * @return array
     */
    public static function fetchModelsFromApiKey($apiKey) {
        $apiKey = trim($apiKey);
        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập API Key để lấy danh sách model.'
            ];
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode($apiKey);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'message' => "Lỗi kết nối mạng: $curlError"
            ];
        }

        $result = json_decode($response, true);

        if ($httpCode === 200 && isset($result['models']) && is_array($result['models'])) {
            $geminiModels = [];
            foreach ($result['models'] as $m) {
                // Lọc các model hỗ trợ generateContent
                $methods = $m['supportedGenerationMethods'] ?? [];
                if (!in_array('generateContent', $methods)) {
                    continue;
                }

                $rawName = $m['name'] ?? '';
                $cleanId = str_replace('models/', '', $rawName);

                // Lọc các model thuộc họ Gemini
                if (stripos($cleanId, 'gemini') === false) {
                    continue;
                }

                $displayName = $m['displayName'] ?? $cleanId;
                $description = $m['description'] ?? '';

                $geminiModels[] = [
                    'id' => $cleanId,
                    'raw_name' => $rawName,
                    'display_name' => $displayName,
                    'description' => $description,
                    'input_token_limit' => $m['inputTokenLimit'] ?? null,
                    'output_token_limit' => $m['outputTokenLimit'] ?? null,
                ];
            }

            // Sắp xếp ưu tiên: 3.7 -> 2.0 -> 2.5 -> 1.5-flash -> 1.5-pro
            usort($geminiModels, function($a, $b) {
                $priority = function($id) {
                    if (strpos($id, '3.7') !== false) return 100;
                    if (strpos($id, '2.0-flash') !== false) return 90;
                    if (strpos($id, '2.5') !== false) return 85;
                    if (strpos($id, '2.0') !== false) return 80;
                    if (strpos($id, '1.5-flash') !== false) return 70;
                    if (strpos($id, '1.5-pro') !== false) return 60;
                    return 50;
                };
                return $priority($b['id']) - $priority($a['id']);
            });

            return [
                'success' => true,
                'models' => $geminiModels,
                'count' => count($geminiModels)
            ];
        }

        $errorMsg = $result['error']['message'] ?? "Lỗi phản hồi (Mã HTTP: $httpCode)";
        return [
            'success' => false,
            'http_code' => $httpCode,
            'message' => "Không thể lấy danh sách Model: " . $errorMsg
        ];
    }

    /**
     * Đề xuất danh sách việc làm phù hợp nhất cho người lao động
     * @param int $seekerId
     * @param int $limit
     * @return array
     */
    public static function recommendJobsForSeeker($seekerId, $limit = 6) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            // 1. Lấy thông tin hồ sơ người lao động
            $stmt = $db->prepare("SELECT * FROM user_seekers WHERE seeker_id = :id");
            $stmt->execute([':id' => $seekerId]);
            $seeker = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$seeker) return [];

            // 2. Lấy danh sách việc làm đã duyệt còn hạn
            $stmtJobs = $db->query("
                SELECT p.*, e.employer_name, e.avatar_url, e.address as employer_address, e.avg_rating as employer_rating, e.business_field
                FROM job_posts p
                JOIN user_employers e ON p.employer_id = e.employer_id
                WHERE p.post_status = 'approved'
                ORDER BY p.posted_at DESC
            ");
            $allJobs = $stmtJobs->fetchAll(PDO::FETCH_ASSOC);

            if (empty($allJobs)) return [];

            // 3. Lấy lịch sử đã ứng tuyển và đã lưu của Seeker để tăng tính thông minh
            $appliedJobIds = $db->query("SELECT job_post_id FROM job_requests WHERE seeker_id = " . intval($seekerId))->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $savedJobIds = $db->query("SELECT job_post_id FROM job_saved WHERE seeker_id = " . intval($seekerId))->fetchAll(PDO::FETCH_COLUMN) ?: [];

            $scoredJobs = [];
            foreach ($allJobs as $job) {
                // Đánh giá độ phù hợp cho từng công việc
                $analysis = self::calculateJobMatchScore($seeker, $job, $appliedJobIds, $savedJobIds);
                
                $job['ai_match_score'] = $analysis['score'];
                $job['ai_match_level'] = $analysis['level'];
                $job['ai_match_label'] = $analysis['label'];
                $job['ai_match_color'] = $analysis['color'];
                $job['ai_matched_skills'] = $analysis['matched_skills'];
                $job['ai_missing_skills'] = $analysis['missing_skills'];
                $job['ai_reasons'] = $analysis['reasons'];
                $job['ai_breakdown'] = $analysis['breakdown'];
                $job['is_applied'] = in_array($job['job_post_id'], $appliedJobIds);
                $job['is_saved'] = in_array($job['job_post_id'], $savedJobIds);

                $scoredJobs[] = $job;
            }

            // Sắp xếp theo điểm tương thích giảm dần
            usort($scoredJobs, function ($a, $b) {
                if ($b['ai_match_score'] === $a['ai_match_score']) {
                    return strtotime($b['posted_at'] ?? 'now') - strtotime($a['posted_at'] ?? 'now');
                }
                return ($b['ai_match_score'] > $a['ai_match_score']) ? 1 : -1;
            });

            return array_slice($scoredJobs, 0, $limit);

        } catch (Throwable $e) {
            error_log("[AIService::recommendJobsForSeeker Error] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Phân tích chuyên sâu độ phù hợp giữa 1 người lao động và 1 tin tuyển dụng cụ thể
     * (Sử dụng Google Gemini AI và Fallback sang Local Smart Engine nếu cần)
    /**
     * Phân tích độ phù hợp giữa người lao động và tin tuyển dụng
     * @param int $seekerId
     * @param int $jobPostId
     * @param bool $useGemini true khi gọi qua AJAX API chi tiết, false khi tải trang để đạt tốc độ < 1ms
     * @return array
     */
    public static function analyzeJobFit($seekerId, $jobPostId, $useGemini = false) {
        $db = Database::getConnection();
        if (!$db) return null;

        $cacheKey = 'ai_fit_' . intval($seekerId) . '_' . intval($jobPostId);
        if (!$useGemini && isset(self::$cachedFits[$cacheKey])) {
            return self::$cachedFits[$cacheKey];
        }
        if (!$useGemini && isset($_SESSION[$cacheKey])) {
            return $_SESSION[$cacheKey];
        }

        try {
            // 1. Lấy hoặc dùng lại Seeker từ bộ nhớ đệm
            $sId = intval($seekerId);
            if (!isset(self::$cachedSeekers[$sId])) {
                $stmtSeeker = $db->prepare("SELECT * FROM user_seekers WHERE seeker_id = :id");
                $stmtSeeker->execute([':id' => $sId]);
                self::$cachedSeekers[$sId] = $stmtSeeker->fetch(PDO::FETCH_ASSOC) ?: false;
            }
            $seeker = self::$cachedSeekers[$sId];

            // 2. Lấy hoặc dùng lại Job từ bộ nhớ đệm
            $jId = intval($jobPostId);
            if (!isset(self::$cachedJobs[$jId])) {
                $stmtJob = $db->prepare("
                    SELECT p.*, e.employer_name, e.avatar_url, e.address as employer_address, e.avg_rating as employer_rating, e.business_field
                    FROM job_posts p
                    JOIN user_employers e ON p.employer_id = e.employer_id
                    WHERE p.job_post_id = :jid
                ");
                $stmtJob->execute([':jid' => $jId]);
                self::$cachedJobs[$jId] = $stmtJob->fetch(PDO::FETCH_ASSOC) ?: false;
            }
            $job = self::$cachedJobs[$jId];

            if (!$seeker || !$job) return null;

            // 3. Lấy hoặc dùng lại lịch sử ứng tuyển / đã lưu
            if (!isset(self::$cachedApplied[$sId])) {
                self::$cachedApplied[$sId] = $db->query("SELECT job_post_id FROM job_requests WHERE seeker_id = " . $sId)->fetchAll(PDO::FETCH_COLUMN) ?: [];
            }
            if (!isset(self::$cachedSaved[$sId])) {
                self::$cachedSaved[$sId] = $db->query("SELECT job_post_id FROM job_saved WHERE seeker_id = " . $sId)->fetchAll(PDO::FETCH_COLUMN) ?: [];
            }

            $appliedJobIds = self::$cachedApplied[$sId];
            $savedJobIds = self::$cachedSaved[$sId];

            // 4. Tính toán điểm chuẩn cơ sở đa tiêu chí nội bộ (chạy tức thì < 0.1ms)
            $baseAnalysis = self::calculateJobMatchScore($seeker, $job, $appliedJobIds, $savedJobIds);
            $advice = self::generateInterviewAdvice($seeker, $job, $baseAnalysis);

            $finalAnalysis = array_merge($baseAnalysis, [
                'ai_provider' => 'JobLink Smart Engine',
                'advice' => $advice,
                'job' => $job,
                'seeker' => $seeker
            ]);

            // 5. Chỉ gọi Google Gemini khi có cờ $useGemini (thường qua AJAX / modal API)
            if ($useGemini && AIConfig::isConfigured()) {
                $geminiAnalysis = self::analyzeJobFitWithGemini($seeker, $job, $baseAnalysis);
                if ($geminiAnalysis && isset($geminiAnalysis['score'])) {
                    $finalAnalysis = array_merge($geminiAnalysis, [
                        'ai_provider' => 'Google Gemini AI',
                        'job' => $job,
                        'seeker' => $seeker
                    ]);
                }
            }

            self::$cachedFits[$cacheKey] = $finalAnalysis;
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION[$cacheKey] = $finalAnalysis;
            }
            return $finalAnalysis;

        } catch (Throwable $e) {
            error_log("[AIService::analyzeJobFit Error] " . $e->getMessage());
            return null;
        }
    }

    /**
     * Phân tích Job Fit sử dụng Google Gemini AI (Đồng bộ chuẩn xác với điểm cơ sở)
     */
    private static function analyzeJobFitWithGemini($seeker, $job, $baseAnalysis = null) {
        $baseScore = $baseAnalysis['score'] ?? 75;
        $breakdown = $baseAnalysis['breakdown'] ?? ['skills' => 80, 'location' => 80, 'employment' => 80, 'experience' => 80];

        $prompt = "Bạn là chuyên gia tư vấn tuyển dụng và đánh giá nhân sự AI cấp cao của nền tảng việc làm JobLink.
Hãy phân tích chuyên sâu độ tương thích giữa Người lao động và Tin tuyển dụng sau đây:

THÔNG TIN NGƯỜI LAO ĐỘNG:
- Họ tên: " . ($seeker['full_name'] ?? 'Người lao động') . "
- Vị trí/Chức danh mong muốn: " . ($seeker['desired_position'] ?? 'Chưa xác định') . "
- Kỹ năng chuyên môn: " . ($seeker['skills'] ?? 'Chưa cập nhật') . "
- Kinh nghiệm làm việc: " . ($seeker['experience_years'] ?? 0) . " năm
- Giới thiệu bản thân: " . ($seeker['bio'] ?? 'Không có') . "
- Khu vực ưu tiên: " . ($seeker['preferred_location'] ?? 'Bình Dương') . "
- Hình thức làm việc mong muốn: " . ($seeker['preferred_employment_type'] ?? 'Toàn thời gian') . "
- Lương kỳ vọng: " . number_format($seeker['expected_salary'] ?? 0) . " VNĐ

THÔNG TIN CÔNG VIỆC:
- Tiêu đề công việc: " . ($job['job_title'] ?? '') . "
- Công ty / Nhà tuyển dụng: " . ($job['employer_name'] ?? '') . "
- Ngành nghề / Lĩnh vực: " . ($job['business_field'] ?? 'Tuyển dụng') . "
- Địa điểm làm việc: " . ($job['workplace_address'] ?? $job['employer_address'] ?? 'Bình Dương') . "
- Hình thức làm việc: " . ($job['employment_type'] ?? 'full_time') . "
- Mức lương: " . number_format($job['salary_amount'] ?? 0) . " VNĐ (" . ($job['salary_type'] ?? 'tháng') . ")
- Yêu cầu công việc: " . ($job['job_requirements'] ?? 'Không có') . "
- Mô tả & Yêu cầu thêm: " . ($job['additional_requirements'] ?? '') . "

KẾT QUẢ ĐÁNH GIÁ TIÊU CHÍ CƠ SỞ CỦA HỆ THỐNG:
- Điểm tương thích cơ sở: {$baseScore}%
- Phân rã điểm: Kỹ năng: {$breakdown['skills']}/100, Vị trí: {$breakdown['location']}/100, Hình thức & Lương: {$breakdown['employment']}/100, Kinh nghiệm: {$breakdown['experience']}/100.

HƯỚNG DẪN ĐỒNG BỘ:
Hãy sử dụng điểm cơ sở {$baseScore}% này làm mốc chuẩn để điểm số hiển thị trên thẻ ngoài danh sách và bảng phân tích chi tiết này hoàn toàn đồng nhất. Hãy đưa ra score là {$baseScore} (hoặc điều chỉnh tối đa +/- 2% nếu phân tích ngữ nghĩa hồ sơ người lao động có điểm khác biệt đặc biệt). Tập trung đưa ra phân tích lý do, điểm mạnh, điểm yếu và lời khuyên phỏng vấn sâu sắc.

YÊU CẦU TRẢ VỀ:
Hãy trả về DUY NHẤT một JSON hợp lệ (không kèm Markdown block) có cấu trúc chính xác sau:
{
  \"score\": {$baseScore},
  \"level\": (Chuỗi một trong các giá trị: \"excellent\", \"high\", \"medium\", \"low\"),
  \"label\": (Chuỗi nhãn tương ứng: \"Rất Phù Hợp\", \"Phù Hợp Cao\", \"Tiềm Năng\", \"Cân Nhắc\"),
  \"color\": (Mã màu hex: \"#059669\" nếu score >= 85, \"#0284c7\" nếu score >= 70, \"#d97706\" nếu score >= 55, \"#64748b\" nếu dưới 55),
  \"matched_skills\": [Danh sách 2-5 kỹ năng người lao động đáp ứng tốt nhất],
  \"missing_skills\": [Danh sách 1-3 kỹ năng hoặc yêu cầu người lao động cần bồi dưỡng thêm],
  \"reasons\": [Danh sách 2-3 câu phân tích ngắn gọn lý do tại sao công việc này phù hợp],
  \"breakdown\": {
    \"skills\": {$breakdown['skills']},
    \"location\": {$breakdown['location']},
    \"employment\": {$breakdown['employment']},
    \"experience\": {$breakdown['experience']}
  },
  \"advice\": [Danh sách 2-3 lời khuyên phỏng vấn thực tế và chiến lược gây ấn tượng với nhà tuyển dụng]
}";

        $res = self::callGeminiAPI($prompt, true);
        if (is_array($res)) {
            $res['matched_skills'] = self::deduplicateSkills($res['matched_skills'] ?? []);
            $res['missing_skills'] = self::deduplicateSkills($res['missing_skills'] ?? []);

            $matchedLower = array_map(function($s) { return mb_strtolower($s, 'UTF-8'); }, $res['matched_skills']);
            $res['missing_skills'] = array_values(array_filter($res['missing_skills'], function($ms) use ($matchedLower) {
                return !in_array(mb_strtolower($ms, 'UTF-8'), $matchedLower);
            }));
        }
        return $res;
    }

    /**
     * Đề xuất người lao động phù hợp nhất cho 1 tin tuyển dụng (Dành cho Nhà Tuyển Dụng)
     * @param int $jobPostId
     * @param int $limit
     * @return array
     */
    public static function matchCandidatesForJob($jobPostId, $limit = 10) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmtJob = $db->prepare("
                SELECT p.*, e.employer_name, e.business_field
                FROM job_posts p
                JOIN user_employers e ON p.employer_id = e.employer_id
                WHERE p.job_post_id = :id
            ");
            $stmtJob->execute([':id' => $jobPostId]);
            $job = $stmtJob->fetch(PDO::FETCH_ASSOC);
            if (!$job) return [];

            $seekers = $db->query("SELECT * FROM user_seekers ORDER BY seeker_id ASC")->fetchAll(PDO::FETCH_ASSOC);
            $results = [];

            foreach ($seekers as $seeker) {
                $analysis = self::calculateJobMatchScore($seeker, $job);
                $seeker['ai_match_score'] = $analysis['score'];
                $seeker['ai_match_level'] = $analysis['level'];
                $seeker['ai_match_label'] = $analysis['label'];
                $seeker['ai_matched_skills'] = $analysis['matched_skills'];
                $seeker['ai_reasons'] = $analysis['reasons'];
                $seeker['ai_breakdown'] = $analysis['breakdown'];

                $results[] = $seeker;
            }

            usort($results, function ($a, $b) {
                return ($b['ai_match_score'] > $a['ai_match_score']) ? 1 : -1;
            });

            return array_slice($results, 0, $limit);

        } catch (Throwable $e) {
            error_log("[AIService::matchCandidatesForJob Error] " . $e->getMessage());
            return [];
        }
    }

    /**
     * AI Đánh Giá & Gợi Ý Tối Ưu Hồ Sơ Người Lao Động (Profile Optimizer)
     * @param array $seeker
     * @return array
     */
    public static function optimizeProfileAdvice($seeker) {
        // 1. Ưu tiên đánh giá chuyên sâu qua Google Gemini AI nếu có API Key
        if (AIConfig::isConfigured() && !empty($seeker)) {
            $geminiAdvice = self::optimizeProfileAdviceWithGemini($seeker);
            if ($geminiAdvice && isset($geminiAdvice['score'])) {
                $geminiAdvice['ai_provider'] = 'Google Gemini AI';
                return $geminiAdvice;
            }
        }

        // 2. Fallback sang Local Engine
        $score = 0;
        $strengths = [];
        $suggestions = [];

        // Kiểm tra họ tên và thông tin cơ bản
        if (!empty($seeker['full_name']) && !empty($seeker['phone_number']) && !empty($seeker['email'])) {
            $score += 25;
            $strengths[] = "Thông tin liên lạc cơ bản đã đầy đủ và chính xác.";
        } else {
            $suggestions[] = "Cần cập nhật đầy đủ số điện thoại và email để nhà tuyển dụng liên hệ.";
        }

        // Kiểm tra kỹ năng chuyên môn
        $skillsRaw = $seeker['skills'] ?? '';
        $skillList = array_filter(array_map('trim', explode(',', $skillsRaw)));
        if (count($skillList) >= 4) {
            $score += 30;
            $strengths[] = "Hồ sơ kỹ năng phong phú (" . count($skillList) . " kỹ năng), giúp AI matching với nhiều công việc đa dạng.";
        } elseif (count($skillList) >= 1) {
            $score += 15;
            $suggestions[] = "Nên bổ sung thêm ít nhất 3-5 kỹ năng hoặc công nghệ bạn thành thạo để tăng 40% điểm đề xuất việc làm.";
        } else {
            $suggestions[] = "Chưa có kỹ năng chuyên môn. Hãy thêm các kỹ năng nổi bật (ví dụ: Tin học, Giao tiếp, Tiếng Anh, Bán hàng...).";
        }

        // Vị trí mong muốn & Trình độ học vấn
        if (!empty($seeker['desired_position'])) {
            $score += 15;
            $strengths[] = "Đã xác định rõ vị trí mong muốn (" . htmlspecialchars($seeker['desired_position']) . ").";
        } else {
            $suggestions[] = "Hãy ghi rõ chức danh nghề nghiệp mong muốn (ví dụ: Nhân viên IT, Kế toán, Barista...).";
        }

        // Giới thiệu bản thân / Mục tiêu
        $bio = trim($seeker['bio'] ?? '');
        if (mb_strlen($bio) >= 60) {
            $score += 20;
            $strengths[] = "Phần giới thiệu bản thân chi tiết, tạo ấn tượng chuyên nghiệp với nhà tuyển dụng.";
        } elseif (mb_strlen($bio) > 0) {
            $score += 10;
            $suggestions[] = "Hãy mở rộng phần giới thiệu bản thân (nêu rõ thế mạnh, kinh nghiệm thực tế và định hướng phát triển).";
        } else {
            $suggestions[] = "Hãy viết một đoạn giới thiệu ngắn (3-4 câu) để tăng 25% độ uy tín của hồ sơ.";
        }

        // Địa điểm & Mức lương
        if (!empty($seeker['preferred_location']) || !empty($seeker['expected_salary'])) {
            $score += 10;
            $strengths[] = "Đã định hình rõ khu vực làm việc và kỳ vọng thu nhập.";
        } else {
            $suggestions[] = "Hãy điền thêm khu vực ưu tiên (ví dụ: Thủ Dầu Một, Thuận An...) để nhận đề xuất việc làm gần nhà.";
        }

        $completenessLevel = 'Cần hoàn thiện';
        $badgeClass = 'badge-warning';
        if ($score >= 85) {
            $completenessLevel = 'Xuất sắc & Toàn diện';
            $badgeClass = 'badge-success';
        } elseif ($score >= 60) {
            $completenessLevel = 'Khá tốt';
            $badgeClass = 'badge-primary';
        }

        return [
            'ai_provider' => 'JobLink Smart Engine',
            'score' => min(100, $score),
            'completeness_level' => $completenessLevel,
            'badge_class' => $badgeClass,
            'strengths' => $strengths,
            'suggestions' => $suggestions,
            'recommended_keywords' => [
                'Kỹ năng tin học văn phòng', 'Giao tiếp khách hàng', 'Giải quyết vấn đề', 
                'Làm việc nhóm', 'Quản lý thời gian', 'Tiếng Anh giao tiếp'
            ]
        ];
    }

    /**
     * Tối ưu hồ sơ người lao động sử dụng Google Gemini AI
     */
    private static function optimizeProfileAdviceWithGemini($seeker) {
        $prompt = "Bạn là chuyên gia cố vấn nghề nghiệp và tối ưu hóa hồ sơ năng lực người lao động AI của JobLink.
Hãy phân tích, chấm điểm và đưa ra lời khuyên tối ưu cho hồ sơ người lao động sau:

HỒ SƠ NGƯỜI LAO ĐỘNG:
- Họ tên: " . ($seeker['full_name'] ?? 'Chưa cập nhật') . "
- Vị trí/Chức danh mong muốn: " . ($seeker['desired_position'] ?? 'Chưa cập nhật') . "
- Kỹ năng chuyên môn hiện có: " . ($seeker['skills'] ?? 'Chưa có') . "
- Số năm kinh nghiệm: " . ($seeker['experience_years'] ?? 0) . " năm
- Giới thiệu bản thân / Mục tiêu: " . ($seeker['bio'] ?? 'Chưa có') . "
- Khu vực làm việc ưu tiên: " . ($seeker['preferred_location'] ?? 'Chưa có') . "
- Mức lương mong muốn: " . number_format($seeker['expected_salary'] ?? 0) . " VNĐ

YÊU CẦU ĐÁNH GIÁ:
Trả về DUY NHẤT một JSON hợp lệ với cấu trúc sau:
{
  \"score\": (Số nguyên từ 30 đến 100 biểu thị % độ hoàn thiện và hấp dẫn của hồ sơ),
  \"completeness_level\": (Chuỗi: \"Xuất sắc & Toàn diện\", \"Khá tốt\", hoặc \"Cần hoàn thiện\"),
  \"badge_class\": (\"badge-success\" nếu >= 85, \"badge-primary\" nếu >= 60, \"badge-warning\" nếu < 60),
  \"strengths\": [2-4 câu ngắn đánh giá những điểm sáng nổi bật trong hồ sơ hiện tại],
  \"suggestions\": [2-4 gợi ý hành động thiết thực giúp người lao động nâng cấp hồ sơ và tăng 50% cơ hội trúng tuyển],
  \"recommended_keywords\": [4-6 từ khóa kỹ năng / chứng chỉ quan trọng nên bổ sung vào hồ sơ]
}";

        return self::callGeminiAPI($prompt, true);
    }

    /**
     * THUẬT TOÁN TÍNH ĐIỂM TƯƠNG THÍCH ĐA YẾU TỐ NỘI BỘ (Local Multi-Factor Engine)
     */
    private static function calculateJobMatchScore($seeker, $job, $appliedJobIds = [], $savedJobIds = []) {
        $reasons = [];

        // -------------------------------------------------------------
        // YẾU TỐ 1: KỸ NĂNG & CHỨC DANH (WEIGHT: 40%)
        // -------------------------------------------------------------
        $seekerSkillsRaw = array_filter(array_map('trim', explode(',', $seeker['skills'] ?? '')));
        $seekerKeywords = self::extractKeywords(($seeker['skills'] ?? '') . ' ' . ($seeker['desired_position'] ?? '') . ' ' . ($seeker['bio'] ?? ''));
        $jobContent = ($job['job_title'] ?? '') . ' ' . ($job['job_requirements'] ?? '') . ' ' . ($job['additional_requirements'] ?? '') . ' ' . ($job['business_field'] ?? '');
        $jobKeywords = self::extractKeywords($jobContent);

        $matchedSkills = [];
        $missingSkills = [];

        // 1. Kiểm tra ưu tiên từ danh sách kỹ năng khai báo trực tiếp của Seeker
        foreach ($seekerSkillsRaw as $rawSkill) {
            $rawClean = trim($rawSkill);
            if (mb_strlen($rawClean) < 2) continue;
            if (stripos($jobContent, $rawClean) !== false) {
                $matchedSkills[] = self::formatSkillName($rawClean);
            }
        }

        // 2. Tìm thêm các kỹ năng quan trọng giao nhau
        $importantJobSkills = self::extractImportantSkills($jobContent);
        foreach ($importantJobSkills as $iSkill) {
            $found = false;
            foreach ($seekerKeywords as $sKey) {
                if (self::isKeywordMatch($sKey, $iSkill)) {
                    $matchedSkills[] = self::formatSkillName($iSkill);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingSkills[] = self::formatSkillName($iSkill);
            }
        }

        // Khử trùng lặp không phân biệt chữ hoa/thường (Case-insensitive deduplication)
        $matchedSkills = self::deduplicateSkills($matchedSkills);
        $missingSkills = self::deduplicateSkills($missingSkills);

        // Loại bỏ khỏi missingSkills những kỹ năng đã có trong matchedSkills
        $matchedLower = array_map(function($s) { return mb_strtolower($s, 'UTF-8'); }, $matchedSkills);
        $missingSkills = array_values(array_filter($missingSkills, function($ms) use ($matchedLower) {
            return !in_array(mb_strtolower($ms, 'UTF-8'), $matchedLower);
        }));

        $matchedSkills = array_slice($matchedSkills, 0, 5);
        $missingSkills = array_slice($missingSkills, 0, 4);

        // Tính điểm kỹ năng
        $skillScore = 35; // Điểm cơ sở
        if (!empty($matchedSkills)) {
            $skillScore += min(65, count($matchedSkills) * 18);
            $reasons[] = "Trùng khớp các kỹ năng chuyên môn: " . implode(', ', array_slice($matchedSkills, 0, 3));
        }

        // So khớp trực tiếp chức danh mong muốn với tiêu đề công việc
        if (!empty($seeker['desired_position']) && !empty($job['job_title'])) {
            $sim = self::calculateStringSimilarity($seeker['desired_position'], $job['job_title']);
            if ($sim > 0.4) {
                $skillScore = min(100, $skillScore + 20);
                $reasons[] = "Vị trí tuyển dụng sát với chức danh bạn mong muốn ('" . htmlspecialchars($job['job_title']) . "')";
            }
        }
        $skillScore = min(100, max(20, $skillScore));

        // -------------------------------------------------------------
        // YẾU TỐ 2: VỊ TRÍ ĐỊA LÝ & KHOẢNG CÁCH (WEIGHT: 20%)
        // -------------------------------------------------------------
        $locationScore = 60; // Điểm mặc định nếu không rõ
        $seekerLoc = mb_strtolower(($seeker['preferred_location'] ?? '') . ' ' . ($seeker['address'] ?? ''));
        $jobLoc = mb_strtolower(($job['workplace_address'] ?? '') . ' ' . ($job['employer_address'] ?? ''));

        $provinces = ['thủ dầu một', 'thuận an', 'dĩ an', 'bến cát', 'tân uyên', 'bình dương', 'bàu bàng', 'bắc tân uyên', 'hồ chí minh', 'sài gòn'];
        $matchedLoc = false;
        foreach ($provinces as $p) {
            if (strpos($seekerLoc, $p) !== false && strpos($jobLoc, $p) !== false) {
                $locationScore = 95;
                $matchedLoc = true;
                $reasons[] = "Địa điểm làm việc rất gần bạn (" . ucfirst($p) . ")";
                break;
            }
        }
        if (!$matchedLoc && !empty($jobLoc)) {
            if (strpos($jobLoc, 'bình dương') !== false || strpos($seekerLoc, 'bình dương') !== false) {
                $locationScore = 80;
            }
        }

        // -------------------------------------------------------------
        // YẾU TỐ 3: HÌNH THỨC LÀM VIỆC & MỨC LƯƠNG (WEIGHT: 20%)
        // -------------------------------------------------------------
        $employmentScore = 70;
        $seekerPrefType = $seeker['preferred_employment_type'] ?? '';
        $jobType = $job['employment_type'] ?? 'full_time';

        if (!empty($seekerPrefType) && ($seekerPrefType === $jobType || $seekerPrefType === 'all')) {
            $employmentScore += 15;
            $reasons[] = "Hình thức làm việc đúng mong muốn (" . ($jobType === 'full_time' ? 'Toàn thời gian' : 'Bán thời gian/Linh hoạt') . ")";
        }

        $expectedSalary = floatval($seeker['expected_salary'] ?? 0);
        $jobSalary = floatval($job['salary_amount'] ?? 0);
        if ($expectedSalary > 0 && $jobSalary > 0) {
            if ($jobSalary >= $expectedSalary) {
                $employmentScore += 15;
                $reasons[] = "Mức thu nhập đáp ứng kỳ vọng của bạn";
            } elseif ($jobSalary >= $expectedSalary * 0.8) {
                $employmentScore += 5;
            }
        } else {
            $employmentScore += 10;
        }
        $employmentScore = min(100, $employmentScore);

        // -------------------------------------------------------------
        // YẾU TỐ 4: KINH NGHIỆM & UY TÍN (WEIGHT: 20%)
        // -------------------------------------------------------------
        $experienceScore = 65;
        $seekerExp = intval($seeker['experience_years'] ?? 0);

        if ($seekerExp >= 3) {
            $experienceScore += 20;
        } elseif ($seekerExp >= 1) {
            $experienceScore += 10;
        }

        // Thưởng điểm uy tín sao
        $rating = floatval($seeker['avg_rating'] ?? 5.0);
        if ($rating >= 4.5) {
            $experienceScore += 15;
        }

        // Thưởng nếu người dùng đã từng lưu hoặc tương tác
        if (in_array($job['job_post_id'], $savedJobIds)) {
            $experienceScore += 10;
            $reasons[] = "Bạn đã lưu công việc này vào danh sách quan tâm";
        }
        $experienceScore = min(100, $experienceScore);

        // -------------------------------------------------------------
        // TÍNH TỔNG ĐIỂM TƯƠNG THÍCH (MATCH SCORE %)
        // -------------------------------------------------------------
        $finalScore = (
            ($skillScore * self::WEIGHT_SKILLS) +
            ($locationScore * self::WEIGHT_LOCATION) +
            ($employmentScore * self::WEIGHT_EMPLOYMENT) +
            ($experienceScore * self::WEIGHT_EXPERIENCE)
        );

        $finalScore = round($finalScore);
        $finalScore = max(45, min(98, $finalScore)); // Giới hạn hợp lý 45% - 98%

        // Phân loại cấp độ
        if ($finalScore >= 85) {
            $level = 'excellent';
            $label = 'Rất Phù Hợp';
            $color = '#059669'; // Emerald
        } elseif ($finalScore >= 70) {
            $level = 'high';
            $label = 'Phù Hợp Cao';
            $color = '#0284c7'; // Sky/Blue
        } elseif ($finalScore >= 55) {
            $level = 'medium';
            $label = 'Tiềm Năng';
            $color = '#d97706'; // Amber
        } else {
            $level = 'low';
            $label = 'Cân Nhắc';
            $color = '#64748b'; // Slate
        }

        if (empty($reasons)) {
            $reasons[] = "Vị trí tuyển dụng phù hợp với định hướng tìm việc phổ biến tại khu vực";
        }

        return [
            'score' => $finalScore,
            'level' => $level,
            'label' => $label,
            'color' => $color,
            'matched_skills' => $matchedSkills,
            'missing_skills' => $missingSkills,
            'reasons' => array_slice(array_unique($reasons), 0, 3),
            'breakdown' => [
                'skills' => round($skillScore),
                'location' => round($locationScore),
                'employment' => round($employmentScore),
                'experience' => round($experienceScore)
            ]
        ];
    }

    /**
     * Tạo lời khuyên phỏng vấn từ AI (Local fallback)
     */
    private static function generateInterviewAdvice($seeker, $job, $analysis) {
        $advice = [];
        if (!empty($analysis['matched_skills'])) {
            $advice[] = "Hãy nhấn mạnh kinh nghiệm và kết quả thực tế với các kỹ năng **" . implode(', ', $analysis['matched_skills']) . "** trong phần phỏng vấn.";
        }
        if (!empty($analysis['missing_skills'])) {
            $advice[] = "Nhà tuyển dụng có yêu cầu thêm về **" . implode(', ', $analysis['missing_skills']) . "**. Hãy chuẩn bị tinh thần học hỏi nhanh hoặc thể hiện các kỹ năng tương đương.";
        }
        $advice[] = "Tìm hiểu kỹ về công ty **" . htmlspecialchars($job['employer_name'] ?? 'Nhà tuyển dụng') . "** và chuẩn bị câu hỏi thể hiện sự cam kết lâu dài.";
        return $advice;
    }

    /**
     * Trích xuất các từ khóa đặc trưng từ văn bản
     */
    private static function extractKeywords($text) {
        if (empty($text)) return [];
        $text = mb_strtolower($text);
        $clean = preg_replace('/[^\p{L}\p{N}\s\+\#\.]/u', ' ', $text);
        $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
        
        $stopWords = ['và', 'hoặc', 'là', 'của', 'trong', 'cho', 'với', 'các', 'những', 'được', 'có', 'để', 'tại', 'theo', 'này', 'khi', 'đến', 'làm'];
        $filtered = array_filter($words, function($w) use ($stopWords) {
            return mb_strlen($w) >= 2 && !in_array($w, $stopWords);
        });

        $ngrams = [];
        $wordsArray = array_values($filtered);
        for ($i = 0; $i < count($wordsArray) - 1; $i++) {
            $ngrams[] = $wordsArray[$i] . ' ' . $wordsArray[$i + 1];
        }

        return array_values(array_unique(array_merge($wordsArray, $ngrams)));
    }

    /**
     * Trích xuất các kỹ năng trọng tâm từ yêu cầu công việc
     */
    private static function extractImportantSkills($reqText) {
        $commonSkills = [
            'java', 'spring boot', 'reactjs', 'vuejs', 'nodejs', 'php', 'laravel', 'mysql', 'sql', 
            'html', 'css', 'javascript', 'docker', 'git', 'restful api', 'c#', '.net', 'python',
            'pha chế', 'barista', 'phục vụ', 'thu ngân', 'bán hàng', 'giao tiếp', 'tiếng anh', 
            'tin học văn phòng', 'excel', 'word', 'quản lý kho', 'lái xe', 'hàn cơ khí', 'cad', 'autocad',
            'thiết kế', 'photoshop', 'kế toán', 'báo cáo thuế', 'chăm sóc khách hàng', 'telesale'
        ];

        $found = [];
        $reqLower = mb_strtolower($reqText);
        foreach ($commonSkills as $skill) {
            if (strpos($reqLower, $skill) !== false) {
                $found[] = $skill;
            }
        }
        return $found;
    }

    /**
     * Kiểm tra so khớp từ khóa tương đương
     */
    private static function isKeywordMatch($k1, $k2) {
        $k1 = mb_strtolower(trim($k1));
        $k2 = mb_strtolower(trim($k2));
        if ($k1 === $k2) return true;
        if (strpos($k1, $k2) !== false || strpos($k2, $k1) !== false) return true;
        return false;
    }

    /**
     * Chuẩn hóa tên kỹ năng về định dạng chuẩn quốc tế (không phân biệt hoa/thường)
     */
    public static function formatSkillName($skill) {
        $skill = trim($skill);
        if ($skill === '') return '';

        $canonicalMap = [
            'reactjs'     => 'ReactJS',
            'react'       => 'ReactJS',
            'react.js'    => 'ReactJS',
            'nodejs'      => 'Node.js',
            'node'        => 'Node.js',
            'node.js'     => 'Node.js',
            'javascript'  => 'JavaScript',
            'js'          => 'JavaScript',
            'typescript'  => 'TypeScript',
            'ts'          => 'TypeScript',
            'vuejs'       => 'Vue.js',
            'vue'         => 'Vue.js',
            'angular'     => 'Angular',
            'spring'      => 'Spring Boot',
            'spring boot' => 'Spring Boot',
            'springboot'  => 'Spring Boot',
            'java'        => 'Java',
            'php'         => 'PHP',
            'laravel'     => 'Laravel',
            'c#'          => 'C#',
            'c++'         => 'C++',
            'python'      => 'Python',
            'django'      => 'Django',
            'flask'       => 'Flask',
            'golang'      => 'Golang',
            'go'          => 'Golang',
            'html'        => 'HTML5',
            'html5'       => 'HTML5',
            'css'         => 'CSS3',
            'css3'        => 'CSS3',
            'sass'        => 'Sass/SCSS',
            'scss'        => 'Sass/SCSS',
            'tailwind'    => 'Tailwind CSS',
            'bootstrap'   => 'Bootstrap',
            'sql'         => 'SQL / MySQL',
            'mysql'       => 'MySQL',
            'postgresql'  => 'PostgreSQL',
            'mongodb'     => 'MongoDB',
            'redis'       => 'Redis',
            'docker'      => 'Docker',
            'kubernetes'  => 'Kubernetes',
            'k8s'         => 'Kubernetes',
            'aws'         => 'AWS Cloud',
            'azure'       => 'Azure',
            'git'         => 'Git / GitHub',
            'github'      => 'GitHub',
            'rest api'    => 'RESTful API',
            'restful api' => 'RESTful API',
            'graphql'     => 'GraphQL',
            'photoshop'   => 'Photoshop',
            'figma'       => 'Figma',
            'ui/ux'       => 'UI/UX Design',
            'seo'         => 'SEO Marketing',
            'tieng anh'   => 'Tiếng Anh',
            'tiếng anh'   => 'Tiếng Anh'
        ];

        $lower = mb_strtolower($skill, 'UTF-8');
        if (isset($canonicalMap[$lower])) {
            return $canonicalMap[$lower];
        }

        // Tự động viết hoa chữ cái đầu mỗi từ
        return mb_convert_case($skill, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Khử trùng lặp kỹ năng không phân biệt hoa thường (Case-insensitive deduplication)
     */
    public static function deduplicateSkills(array $skills): array {
        $seen = [];
        $result = [];
        foreach ($skills as $s) {
            $formatted = self::formatSkillName($s);
            if (empty($formatted)) continue;
            $lower = mb_strtolower($formatted, 'UTF-8');
            if (!isset($seen[$lower])) {
                $seen[$lower] = true;
                $result[] = $formatted;
            }
        }
        return array_values($result);
    }

    /**
     * Trợ Lý Ảo AI JobLink Thông Minh Hỗ Trợ Toàn Diện Sử Dụng Web
     * Tích hợp Google Gemini kết hợp Hybrid NLP Engine
     * @param string $userMessage
     * @param string $userRole 'GUEST'|'JOB_SEEKER'|'EMPLOYER_ADMIN'|'INTERMEDIARY'|'SUPER_ADMIN'
     * @param array $chatHistory
     * @return array ['reply' => string, 'suggestions' => array]
     */
    public static function handleChatbotAssistant($userMessage, $userRole = 'GUEST', $chatHistory = []) {
        $msg = trim($userMessage);
        if (empty($msg)) {
            return [
                'reply' => 'Xin chào! Tôi là **Trợ lý Ảo AI JobLink**. Bạn cần tôi hướng dẫn cách sử dụng website, tìm kiếm việc làm hay giải đáp thủ tục xác minh chữ ký số?',
                'suggestions' => [
                    'Tìm việc làm mới nhất',
                    'Hướng dẫn xác thực chữ ký số DN',
                    'Quy định điểm uy tín người lao động',
                    'Cách ký hợp đồng điện tử với Sàn'
                ]
            ];
        }

        // Lấy dữ liệu việc làm thực tế để AI trả lời chính xác
        $db = Database::getConnection();
        $sampleJobs = [];
        if ($db) {
            try {
                $stmt = $db->query("
                    SELECT p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.digital_signature_status
                    FROM job_posts p
                    JOIN user_employers e ON p.employer_id = e.employer_id
                    WHERE p.post_status = 'approved'
                    ORDER BY p.priority_level DESC, p.posted_at DESC
                    LIMIT 5
                ");
                $sampleJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {}
        }

        $jobsSummary = "";
        foreach ($sampleJobs as $j) {
            $verifiedTag = ($j['digital_signature_status'] === 'verified') ? '[Đã Ký Số CA]' : '';
            $jobsSummary .= "- {$j['job_title']} tại {$j['employer_name']} ({$j['workplace_address']}) - Lương: " . number_format($j['salary_amount']) . " đ ({$j['employment_type']}) {$verifiedTag}\n";
        }

        $systemPrompt = "Bạn là Trợ Lý Ảo AI cao cấp của Sàn Giao Dịch & Kết Nối Việc Làm JobLink (Việt Nam).
Vai trò của người dùng hiện tại: {$userRole}.
Nhiệm vụ của bạn:
1. Hướng dẫn người dùng cách sử dụng tất cả các tính năng trên website JobLink một cách rõ ràng, ngắn gọn, thân thiện và chuyên nghiệp (bằng tiếng Việt có định dạng Markdown).
2. Các tính năng nổi bật của JobLink:
   - Xác minh doanh nghiệp bằng Chữ Ký Số CA (VNPT-CA, Viettel-CA, FPT-CA, BKAV-CA...) đảm bảo tính pháp lý và tin cậy tuyệt đối.
   - Admin và Doanh nghiệp ký Hợp Đồng Điện Tử trực tiếp qua Chữ Ký Số (SHA-256). Thông tin xác thực mật chỉ Admin thấy.
   - Xác minh tính pháp lý ủy quyền giữa Doanh nghiệp và Người trung gian / Nhà cung ứng lao động.
   - Tính ràng buộc & Điểm uy tín (Trust Score 0-100) cho Người lao động, bảo đảm không bùng hẹn phỏng vấn.
   - Tự động gửi thông báo tin nhắn và sự kiện về Gmail.
   - Ưu tiên thấy công việc trước (Job-First Feed) với các tin đăng từ doanh nghiệp đã ký số.
3. Dữ liệu một số việc làm đang tuyển nổi bật trên sàn:
{$jobsSummary}

Hãy trả lời câu hỏi sau của người dùng một cách trực tiếp, chu đáo, hữu ích:";

        $fullPrompt = $systemPrompt . "\n\nCâu hỏi của người dùng: " . $msg;

        // Gọi Gemini AI
        $aiReply = self::callGeminiAPI($fullPrompt, false);

        if (!empty($aiReply) && is_string($aiReply)) {
            return [
                'reply' => trim($aiReply),
                'suggestions' => self::generateSuggestionsByContext($msg, $userRole)
            ];
        }

        // Fallback Engine nếu chưa có API Key hoặc mạng gián đoạn
        return self::fallbackChatbotResponse($msg, $userRole, $sampleJobs);
    }

    /**
     * Fallback thông minh khi không có kết nối Gemini
     */
    private static function fallbackChatbotResponse($msg, $userRole, $sampleJobs) {
        $lower = mb_strtolower($msg, 'UTF-8');

        if (strpos($lower, 'chữ ký số') !== false || strpos($lower, 'chu ky so') !== false || strpos($lower, 'ca') !== false || strpos($lower, 'xác minh') !== false || strpos($lower, 'xac minh') !== false) {
            $reply = "### 🔐 Hướng Dẫn Xác Minh Chữ Ký Số Doanh Nghiệp (CA / E-Sign)
JobLink hỗ trợ thẩm định và tích hợp chữ ký số chuẩn Việt Nam (VNPT-CA, Viettel-CA, FPT-CA, BKAV-CA, MISA-CA...):
1. **Đối với Doanh nghiệp:** Truy cập menu `Cài Đặt` -> chọn tab `Chữ Ký Số & Hợp Đồng` -> Nhập Mã số thuế, Số Serial và Nhà cung cấp CA hoặc tải lên chứng thư số.
2. **Quyền lợi sau khi xác minh:**
   - Nhận ngay huy hiệu **Doanh Nghiệp Đã Xác Thực Chữ Ký Số**.
   - Tin tuyển dụng được thuật toán **Ưu tiên đẩy lên Top 1** trang chủ.
   - Ký kết hợp đồng điện tử pháp lý trực tiếp với Ban Quản Trị Sàn.
3. **Bảo mật tuyệt đối:** Toàn bộ thông tin pháp lý gốc chỉ có Admin/SuperAdmin có thẩm quyền xem và kiểm duyệt.";
            $suggestions = ['Xem mẫu hợp đồng điện tử', 'Quy trình ủy quyền Người trung gian', 'Tìm việc làm uy tín'];
        } elseif (strpos($lower, 'trung gian') !== false || strpos($lower, 'môi giới') !== false || strpos($lower, 'ủy quyền') !== false || strpos($lower, 'uy quyen') !== false) {
            $reply = "### 🤝 Xác Minh Tính Pháp Lý Giữa DN & Người Trung Gian
JobLink thiết lập cơ chế ủy quyền tuyển dụng pháp lý chặt chẽ:
1. Doanh nghiệp lập **Văn bản ủy quyền tuyển dụng** cho Người trung gian / Đại lý cung ứng lao động kèm mức hoa hồng và thời hạn.
2. DN ký số xác nhận vào văn bản ủy quyền.
3. Ban Quản Trị JobLink kiểm duyệt tính pháp lý và cấp chứng nhận **Đại Diện Tuyển Dụng Hợp Pháp**.
4. Mọi tin đăng do Trung gian phụ trách sẽ hiển thị nhãn *\"Được Doanh nghiệp X ủy quyền hợp pháp\"* để bảo vệ quyền lợi người lao động.";
            $suggestions = ['Hướng dẫn đăng ký Trung gian', 'Tìm việc từ DN đã xác thực', 'Cài đặt tài khoản'];
        } elseif (strpos($lower, 'điểm uy tín') !== false || strpos($lower, 'ràng buộc') !== false || strpos($lower, 'cam kết') !== false || strpos($lower, 'trust score') !== false) {
            $reply = "### 🛡️ Cơ Chế Ràng Buộc & Điểm Uy Tín (Trust Score) Người Lao Động
Để đảm bảo môi trường tuyển dụng nghiêm túc, JobLink áp dụng hệ thống điểm uy tín (0 - 100 điểm, mặc định khởi tạo 100 điểm):
- **Cộng điểm thưởng:** Tham gia phỏng vấn đúng hẹn (+5 điểm), hoàn thành tốt ca làm và nhận đánh giá 5 sao (+10 điểm).
- **Trừ điểm vi phạm:** Tự ý bỏ hẹn phỏng vấn không báo trước (-30 điểm), bùng ca làm việc (-40 điểm).
- **Chế tài bảo vệ:** Nếu điểm uy tín dưới 60 điểm, tài khoản sẽ bị tạm khóa quyền ứng tuyển cho đến khi hoàn thành cam kết xác thực.";
            $suggestions = ['Xem việc làm phù hợp', 'Cài đặt thông báo Gmail', 'Cách nâng cao điểm uy tín'];
        } elseif (strpos($lower, 'gmail') !== false || strpos($lower, 'email') !== false || strpos($lower, 'thông báo') !== false || strpos($lower, 'tin nhắn') !== false) {
            $reply = "### 📧 Tính Năng Thông Báo Tin Nhắn Qua Gmail
Hệ thống JobLink tự động đồng bộ gửi email thông báo trực tiếp về hòm thư Gmail của bạn:
- Nhận thông báo ngay khi có tin nhắn mới từ Nhà tuyển dụng hoặc Người lao động.
- Nhận thư mời phỏng vấn và lịch hẹn tuyển dụng kèm địa điểm Google Maps.
- Doanh nghiệp nhận email tức thì khi có người lao động nộp hồ sơ ứng tuyển mới.
- Bạn có thể bật/tắt hoặc tùy chỉnh tần suất nhận email trong mục `Cài Đặt` -> `Thông Báo Gmail`.";
            $suggestions = ['Kiểm tra hòm thư tin nhắn', 'Tìm việc làm gần tôi', 'Cài đặt tài khoản'];
        } elseif (strpos($lower, 'tìm việc') !== false || strpos($lower, 'việc làm') !== false || strpos($lower, 'tuyen dung') !== false || strpos($lower, 'lương') !== false) {
            $reply = "### 🚀 Việc Làm Ưu Tiên Nổi Bật Trên JobLink\nTrang chủ JobLink hiện đang ưu tiên các vị trí việc làm từ doanh nghiệp uy tín đã xác thực Chữ Ký Số:\n";
            foreach ($sampleJobs as $j) {
                $reply .= "- **{$j['job_title']}** - {$j['employer_name']} (" . number_format($j['salary_amount']) . " đ/{$j['employment_type']})\n";
            }
            $reply .= "\n👉 Bạn có thể vào mục **[Tìm Việc Làm](/jobs)** để lọc theo địa điểm, mức lương và hình thức làm việc tức thì!";
            $suggestions = ['Việc làm toàn thời gian', 'Việc làm bán thời gian / theo ca', 'Doanh nghiệp đã xác thực'];
        } else {
            $reply = "Chào bạn! Tôi là **Trợ lý AI JobLink**. Tôi có thể giúp bạn:
- 🔎 **Tìm kiếm việc làm** theo kỹ năng, mức lương, khu vực địa lý.
- 🔐 **Hướng dẫn xác minh Chữ Ký Số CA** cho doanh nghiệp và ký hợp đồng điện tử.
- 🤝 **Thẩm định tính pháp lý** ủy quyền giữa Doanh nghiệp và Người trung gian.
- 🛡️ **Giải đáp cơ chế Điểm uy tín & Cam kết** người lao động.
- ⚙️ **Hướng dẫn cài đặt riêng** cho từng vai trò tài khoản.

Bạn muốn tìm hiểu nội dung nào trước?";
            $suggestions = [
                'Tìm việc làm mới nhất',
                'Hướng dẫn xác thực chữ ký số DN',
                'Quy định điểm uy tín người lao động',
                'Xác minh pháp lý người trung gian'
            ];
        }

        return ['reply' => $reply, 'suggestions' => $suggestions];
    }

    /**
     * Sinh gợi ý câu hỏi tiếp theo
     */
    private static function generateSuggestionsByContext($msg, $userRole) {
        if ($userRole === 'EMPLOYER_ADMIN') {
            return [
                'Cách đăng ký Chữ Ký Số CA',
                'Ký hợp đồng điện tử với Admin',
                'Ủy quyền cho Người trung gian',
                'Cài đặt thông báo người lao động về Gmail'
            ];
        } elseif ($userRole === 'JOB_SEEKER') {
            return [
                'Việc làm lương cao mới nhất',
                'Cách nâng Điểm Uy Tín',
                'Cam kết không bùng ca',
                'Cài đặt nhận email qua Gmail'
            ];
        } elseif ($userRole === 'INTERMEDIARY') {
            return [
                'Quy trình xác minh pháp lý ủy quyền',
                'Đăng tin tuyển dụng đại diện DN',
                'Cài đặt hoa hồng & hợp đồng'
            ];
        }
        return [
            'Tìm việc làm nhanh',
            'Xác thực chữ ký số là gì?',
            'Ràng buộc điểm uy tín',
            'Cài đặt tài khoản'
        ];
    }

    /**
     * Tính toán độ tương đồng giữa hai chuỗi văn bản (0.0 đến 1.0)
     */
    public static function calculateStringSimilarity($str1, $str2) {
        $str1 = mb_strtolower(trim($str1 ?? ''), 'UTF-8');
        $str2 = mb_strtolower(trim($str2 ?? ''), 'UTF-8');
        if ($str1 === $str2 && !empty($str1)) return 1.0;
        if (empty($str1) || empty($str2)) return 0.0;
        if (strpos($str2, $str1) !== false || strpos($str1, $str2) !== false) return 0.85;
        similar_text($str1, $str2, $percent);
        return $percent / 100.0;
    }
}