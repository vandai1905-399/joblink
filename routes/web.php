<?php
// routes/web.php - Hệ thống định tuyến URL cho ứng dụng JobLink (6 Controllers)

require_once __DIR__ . '/../helpers/functions.php';

class Router {
    private static $routes = [];

    public static function get($path, $handler) {
        self::$routes['GET'][$path] = $handler;
    }

    public static function post($path, $handler) {
        self::$routes['POST'][$path] = $handler;
    }

    public static function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $rawUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        // Lấy route nếu truyền qua query param ?r=... hoặc ?action=...
        $queryRoute = $_GET['r'] ?? $_GET['route'] ?? null;
        if ($queryRoute) {
            $uri = '/' . trim($queryRoute, '/');
        } else {
            // Chuẩn hóa URI, loại bỏ base directory (vd /Joblink, /public, /Joblink/public)
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $scriptDir = str_replace('\\', '/', $scriptDir);
            
            $uri = $rawUri;
            if ($scriptDir !== '/' && $scriptDir !== '' && strpos($uri, $scriptDir) === 0) {
                $uri = substr($uri, strlen($scriptDir));
            }
            $uri = preg_replace('#^/(Joblink|public|Joblink/public)#i', '', $uri);
            $uri = '/' . trim($uri, '/');
            if ($uri === '//' || $uri === '') $uri = '/';
        }

        // ====================================================================
        // TỰ ĐỘNG PHỤC VỤ FILE TĨNH (CSS, JS, IMAGES) NẾU LỌT VÀO ROUTER
        // ====================================================================
        $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
        if (in_array($ext, ['css', 'js', 'png', 'jpg', 'jpeg', 'svg', 'webp', 'woff', 'woff2', 'ttf', 'ico'])) {
            $cleanAsset = ltrim($uri, '/');
            $cleanAsset = preg_replace('#^(public/)+#', '', $cleanAsset);

            $possibleFiles = [
                (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__)) . '/public/' . $cleanAsset,
                (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__)) . '/' . $cleanAsset
            ];

            foreach ($possibleFiles as $file) {
                if (file_exists($file) && is_file($file)) {
                    $mimes = [
                        'css' => 'text/css; charset=UTF-8',
                        'js' => 'application/javascript; charset=UTF-8',
                        'png' => 'image/png',
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'svg' => 'image/svg+xml',
                        'webp' => 'image/webp',
                        'ico' => 'image/x-icon',
                        'woff2' => 'font/woff2',
                        'ttf' => 'font/ttf'
                    ];
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_write_close();
                    }
                    header('Cache-Control: public, max-age=86400');
                    header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
                    readfile($file);
                    exit();
                }
            }
        }

        $routes = self::$routes[$method] ?? [];

        foreach ($routes as $routePath => $handler) {
            // Chuyển route có tham số /jobs/{id} thành Regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                if (is_callable($handler)) {
                    call_user_func_array($handler, array_values($params));
                    return;
                }
                [$controllerClass, $methodName] = explode('@', $handler);
                
                $controllerClassClean = basename(str_replace('\\', '/', $controllerClass));
                $controllerFile = null;

                $matches = glob(__DIR__ . '/../public/modules/*/' . $controllerClassClean . '.php');
                if (!empty($matches)) {
                    $controllerFile = $matches[0];
                }

                if ($controllerFile && file_exists($controllerFile)) {
                    require_once $controllerFile;
                    $controller = new $controllerClassClean();
                    call_user_func_array([$controller, $methodName], array_values($params));
                    return;
                }
            }
        }

        // 404 Not Found fallback
        http_response_code(404);
        echo "<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>404 - Không tìm thấy trang</title><link rel='stylesheet' href='/css/style.css'></head><body style='text-align:center;padding:4rem;font-family:sans-serif;'><h1 style='font-size:3rem;color:#0d9488'>404</h1><p>Trang bạn tìm kiếm (<code>" . htmlspecialchars($uri) . "</code>) không tồn tại.</p><a href='/' style='margin-top:1rem;display:inline-block;padding:0.6rem 1.5rem;background:#0d9488;color:#fff;border-radius:8px;text-decoration:none'>Về Trang Chủ</a></body></html>";
    }
}

// ==========================================
// 1. PUBLIC & JOB POSTS (JobPostController)
// ==========================================
Router::get('/', 'JobPostController@index');
Router::get('/about', 'JobPostController@about');
Router::get('/jobs', 'JobPostController@search');
Router::get('/jobs/{id}', 'JobPostController@detail');
Router::get('/employers', 'JobPostController@employers');
Router::get('/employers/{id}', 'JobPostController@employerDetail');
Router::get('/test-db', function() { require __DIR__ . '/../config/test_db.php'; exit; });
Router::get('/test_db.php', function() { require __DIR__ . '/../config/test_db.php'; exit; });

// ==========================================
// 2. AUTHENTICATION & PROFILES (AuthController)
// ==========================================
Router::get('/auth/login', 'AuthController@showLogin');
Router::post('/auth/login', 'AuthController@handleLogin');
Router::get('/auth/register', 'AuthController@showRegister');
Router::post('/auth/register', 'AuthController@handleRegister');
Router::post('/auth/register-step2', 'AuthController@handleRegisterStep2');

Router::get('/auth/forgot-password', 'AuthController@showForgotPassword');
Router::post('/auth/forgot-password', 'AuthController@handleForgotPassword');
Router::get('/auth/change-password', 'AuthController@showChangePassword');
Router::post('/auth/change-password', 'AuthController@handleChangePassword');
Router::get('/auth/logout', 'AuthController@logout');

// Dashboards
Router::get('/seeker/dashboard', 'AuthController@seekerDashboard');
Router::get('/employer/dashboard', 'AuthController@employerDashboard');
Router::get('/admin/dashboard', 'AuthController@adminDashboard');

// Profiles
Router::get('/seeker/profile', 'AuthController@seekerProfile');
Router::post('/seeker/profile/update', 'AuthController@updateSeekerProfile');
Router::get('/employer/company-profile', 'AuthController@employerProfile');
Router::post('/employer/company-profile/update', 'AuthController@updateEmployerProfile');
Router::post('/api/employer/shifts/save', 'AuthController@apiSaveWorkShifts');

// AI Matching & Recommendation Engine
Router::get('/api/ai/optimize-profile', 'AuthController@aiOptimizeProfile');
Router::get('/api/ai/job-fit/{id}', 'AuthController@aiJobFitModal');

// Admin Accounts & Staffs & Support & AI Settings
Router::get('/admin/notifications', 'AuthController@adminNotifications');
Router::post('/api/notifications/mark-all-read', 'AuthController@markAllNotificationsRead');
Router::get('/api/notifications/mark-all-read', 'AuthController@markAllNotificationsRead');
Router::get('/admin/accounts', 'AuthController@adminAccounts');
Router::post('/admin/accounts/toggle-status/{id}', 'AuthController@toggleAccountStatus');
Router::post('/admin/accounts/delete/{id}', 'AuthController@deleteAccount');
Router::get('/api/admin/accounts/{id}', 'AuthController@getAccountDetailApi');
Router::get('/admin/staffs', 'AuthController@adminStaffs');


Router::get('/admin/employers', 'AuthController@adminEmployers');
Router::post('/admin/employers/verify/{id}', 'AuthController@verifyEmployer');
Router::get('/admin/support', 'AuthController@adminSupport');
Router::get('/admin/ai-settings', 'AuthController@adminAiSettings');
Router::post('/admin/ai-settings/update', 'AuthController@updateAiSettings');
Router::get('/admin/ai-analytics', 'AuthController@adminAiAnalytics');
Router::get('/admin/ai-analytics/export', 'AuthController@exportAiTrainingData');
Router::post('/admin/ai-analytics/sync', 'AuthController@syncAiKnowledge');
Router::post('/admin/ai-analytics/delete-log/{id}', 'AuthController@deleteAiChatLog');
Router::post('/admin/api/ai-test', 'AuthController@testAiConnection');
Router::post('/admin/api/ai-models', 'AuthController@getAvailableModels');


// Geo Location APIs
Router::get('/api/geo/popular-locations', 'JobPostController@apiSuggestLocations');

// Smart Salary Benchmark API (CSDL & Tiêu chí)
Router::get('/api/jobs/salary-benchmark', 'JobPostController@apiSalaryBenchmark');
Router::post('/api/jobs/salary-benchmark', 'JobPostController@apiSalaryBenchmark');

// ==========================================
// 3. JOB POSTS MANAGEMENT (JobPostController)
// ==========================================
Router::get('/employer/jobs', 'JobPostController@employerJobs');
Router::get('/employer/jobs/create', 'JobPostController@create');
Router::post('/employer/jobs/store', 'JobPostController@store');
Router::get('/employer/jobs/edit/{id}', 'JobPostController@edit');
Router::post('/employer/jobs/update/{id}', 'JobPostController@update');
Router::get('/employer/jobs/delete/{id}', 'JobPostController@delete');
Router::get('/employer/jobs/{id}/candidates', 'JobPostController@manageJobCandidates');
Router::get('/admin/job-posts', 'JobPostController@adminList');
Router::post('/admin/job-posts/approve/{id}', 'JobPostController@approve');
Router::post('/admin/job-posts/reject/{id}', 'JobPostController@reject');
Router::post('/admin/job-posts/delete/{id}', 'JobPostController@adminDelete');

// ==========================================
// 4. JOB REQUESTS & SAVED (JobRequestController)
// ==========================================
Router::get('/seeker/applied-jobs', 'JobRequestController@getAppliedJobs');
Router::get('/seeker/apply/{id}', 'JobRequestController@apply');
Router::get('/jobs/{id}/apply', 'JobRequestController@apply');
Router::get('/seeker/saved-jobs', 'JobRequestController@getSavedJobs');
Router::get('/seeker/toggle-save/{id}', 'JobRequestController@toggleSave');
Router::get('/seeker/invitation/respond/{id}', 'JobRequestController@respondInvitation');
Router::post('/seeker/invitation/respond/{id}', 'JobRequestController@respondInvitation');

Router::get('/employer/applications', 'JobRequestController@getEmployerApplications');
Router::get('/employer/candidates/recommended', 'JobRequestController@recommendedCandidates');
Router::get('/employer/candidates/{id}', 'JobRequestController@candidateDetail');
Router::post('/employer/candidates/invite', 'JobRequestController@inviteCandidate');
Router::post('/employer/candidates/re-invite-meeting', 'MeetingController@reInviteCandidateMeeting');
Router::post('/employer/applications/update/{id}', 'JobRequestController@updateStatus');
Router::post('/employer/applications/accept-onboarding', 'JobRequestController@acceptWithOnboarding');
Router::post('/employer/applications/accept-onboarding/{id}', 'JobRequestController@acceptWithOnboarding');
Router::post('/seeker/applications/remind/{id}', 'JobRequestController@remindEmployer');
Router::post('/employer/applications/delete/{id}', 'JobRequestController@delete');
Router::get('/api/ai/suggest-meeting-slots', 'JobRequestController@apiSuggestMeetingSlots');

// ==========================================
// 5. CANDIDATE MEETINGS (MeetingController)
// ==========================================
Router::get('/seeker/meetings', 'MeetingController@seekerMeetings');
Router::post('/seeker/meetings/reschedule/{id}', 'MeetingController@seekerRequestReschedule');
Router::get('/employer/meetings', 'MeetingController@employerMeetings');
Router::post('/employer/meetings/store', 'MeetingController@store');
Router::post('/employer/meetings/schedule-from-chat', 'MeetingController@scheduleFromChat');
Router::post('/employer/meetings/update/{id}', 'MeetingController@updateStatus');
Router::post('/employer/meetings/delete/{id}', 'MeetingController@delete');

// ==========================================
// 6. MESSAGES & CHAT MESSENGER (MessageController)
// ==========================================
Router::get('/seeker/messages', 'MessageController@seekerMessages');
Router::get('/employer/messages', 'MessageController@employerMessages');
Router::post('/employer/messages/send', 'MessageController@send');
Router::get('/admin/messages', 'MessageController@adminMessages');
Router::post('/admin/messages/broadcast', 'MessageController@broadcast');
Router::get('/api/messages/thread', 'MessageController@getThreadAjax');
Router::get('/api/messages/conversations', 'MessageController@getConversationsAjax');
Router::post('/api/messages/send-ajax', 'MessageController@sendAjax');

// ==========================================
// 7. REVIEWS & CRITERIA (ReviewController)
// ==========================================
Router::get('/seeker/reviews', 'ReviewController@seekerReviews');
Router::post('/seeker/reviews/create', 'ReviewController@createReview');
Router::get('/employer/candidate-reviews', 'ReviewController@employerReviews');
Router::post('/employer/candidate-reviews/store', 'ReviewController@storeCandidateReview');
Router::get('/admin/reviews', 'ReviewController@adminReviews');
Router::post('/admin/reviews/moderate/{id}', 'ReviewController@moderateReview');
Router::post('/admin/reviews/delete/{id}', 'ReviewController@deleteReview');
Router::get('/admin/review-criteria', 'ReviewController@reviewCriteria');

// ==========================================
// 8. HỢP ĐỒNG ĐIỆN TỬ, CHỮ KÝ SỐ & ỦY QUYỀN PHÁP LÝ
// ==========================================
Router::get('/admin/contracts', 'AuthController@adminContracts');
Router::post('/admin/contracts/create', 'AuthController@createContract');
Router::post('/admin/contracts/sign/{id}', 'AuthController@adminSignContract');
Router::get('/employer/contracts', 'AuthController@employerSettings');
Router::post('/employer/contracts/sign/{id}', 'AuthController@employerSignContract');

Router::get('/admin/authorizations', 'AuthController@adminAuthorizations');
Router::post('/admin/authorizations/verify/{id}', 'AuthController@verifyAuthorization');
Router::post('/employer/authorizations/create', 'AuthController@createAuthorization');

// ==========================================
// 9. THIẾT LẬP RIÊNG CHO TỪNG VAI TRÒ (ROLE SETTINGS)
// ==========================================
// Admin Settings
Router::get('/admin/settings', 'AuthController@adminSettings');
Router::post('/admin/settings/mail-test', 'AuthController@testMail');
Router::post('/admin/settings/mail-update', 'AuthController@updateMailSettings');
Router::post('/admin/settings/ca-update', 'AuthController@updateCaSettings');
Router::post('/admin/settings/binding-update', 'AuthController@updateBindingSettings');

// Employer Settings & Chữ Ký Số
Router::get('/employer/settings', 'AuthController@employerSettings');
Router::post('/employer/digital-signature/register', 'AuthController@registerDigitalSignature');

// Intermediary Dashboard & Settings
Router::get('/intermediary/dashboard', 'AuthController@intermediaryDashboard');
Router::get('/intermediary/settings', 'AuthController@intermediarySettings');
Router::post('/intermediary/settings', 'AuthController@intermediarySettings');

// Seeker Settings & Cam Kết Điểm Uy Tín
Router::get('/seeker/settings', 'AuthController@seekerSettings');
Router::post('/seeker/agreement/sign', 'AuthController@signSeekerAgreement');

// Chatbot AI Assistant API
Router::post('/api/ai/chat', 'AuthController@aiChatbot');
Router::get('/api/ai/chat/history', 'AuthController@aiChatHistory');
Router::post('/api/ai/chat/clear', 'AuthController@aiChatClear');
