<?php
// modules/messages/MessageController.php - Quản lý khung chat trao đổi 2 chiều & tin nhắn

require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../public/modules/auth/AuthController.php';
require_once __DIR__ . '/../../../public/modules/auth/UserAccount.php';
require_once __DIR__ . '/../../../public/modules/profiles/UserEmployer.php';
require_once __DIR__ . '/../../../public/modules/profiles/UserSeeker.php';
require_once __DIR__ . '/../jobs/JobPost.php';
require_once __DIR__ . '/../applications/JobRequest.php';
require_once __DIR__ . '/Message.php';

class MessageController {

    /**
     * Khung chat trao đổi phía Người lao động (Seeker)
     */
    public function seekerMessages() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $myAccountId = $user['account_id'];

        $conversations = Message::getConversations($myAccountId);
        
        $rawPartner = $_GET['partner_id'] ?? '';
        $isAiActive = ($rawPartner === 'ai_joby' || (empty($rawPartner) && empty($conversations)));
        $partnerId = $isAiActive ? 'ai_joby' : intval($rawPartner);
        
        if (!$isAiActive && $partnerId === 0 && !empty($conversations)) {
            $partnerId = intval($conversations[0]['partner_id']);
        }

        $activePartner = null;
        $thread = [];
        if (!$isAiActive && is_numeric($partnerId) && (int)$partnerId > 0) {
            $activePartner = Message::getPartnerInfo((int)$partnerId);
            $thread = Message::getThread($myAccountId, (int)$partnerId);
        }

        $pageTitle = 'Tin Nhắn & Trao Đổi Nhà Tuyển Dụng - JobLink';
        $layoutFile = __DIR__ . '/../layouts/user_layout.php';
        require_once __DIR__ . '/chat_messenger.php';
    }

    /**
     * Khung chat trao đổi phía Nhà tuyển dụng (Employer)
     */
    public function employerMessages() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN', 'INTERMEDIARY']);
        $myAccountId = $user['account_id'];
        $employer = UserEmployer::findByAccountId($myAccountId);

        $conversations = Message::getConversations($myAccountId);

        $rawPartner = $_GET['partner_id'] ?? '';
        $isAiActive = ($rawPartner === 'ai_joby');
        $partnerId = $isAiActive ? 'ai_joby' : intval($rawPartner);
        $seekerId = intval($_GET['seeker_id'] ?? 0);
        $requestId = intval($_GET['request_id'] ?? 0);
        $selectedJobId = intval($_GET['job_id'] ?? 0);
        $openMeetingModal = !empty($_GET['open_meeting_modal']);

        // 1. Nếu chưa có partnerId nhưng có seekerId, tra cứu account_id của NLĐ
        if (!$isAiActive && $partnerId === 0 && $seekerId > 0) {
            $seeker = UserSeeker::findById($seekerId);
            if ($seeker && !empty($seeker['account_id'])) {
                $partnerId = intval($seeker['account_id']);
            }
        }

        // 2. Nếu chưa có partnerId nhưng có request_id, tra cứu thông tin ứng tuyển
        if (!$isAiActive && $partnerId === 0 && $requestId > 0) {
            $req = JobRequest::findById($requestId);
            if ($req && !empty($req['seeker_id'])) {
                $seeker = UserSeeker::findById($req['seeker_id']);
                if ($seeker && !empty($seeker['account_id'])) {
                    $partnerId = intval($seeker['account_id']);
                    if ($seekerId === 0) $seekerId = intval($seeker['seeker_id']);
                    if ($selectedJobId === 0) $selectedJobId = intval($req['job_post_id']);
                }
            }
        }

        // 3. Mặc định chọn hội thoại đầu tiên nếu không chỉ định, hoặc chuyển sang Joby AI nếu trống
        if (!$isAiActive && $partnerId === 0) {
            if (!empty($conversations)) {
                $partnerId = intval($conversations[0]['partner_id']);
            } else {
                $isAiActive = true;
                $partnerId = 'ai_joby';
            }
        }

        $activePartner = null;
        $thread = [];
        if (!$isAiActive && is_numeric($partnerId) && (int)$partnerId > 0) {
            $activePartner = Message::getPartnerInfo((int)$partnerId);
            $thread = Message::getThread($myAccountId, (int)$partnerId);

            if ($activePartner && empty($seekerId) && !empty($activePartner['seeker_id'])) {
                $seekerId = intval($activePartner['seeker_id']);
            }

            // Đảm bảo đối tác hiển thị trong danh sách hội thoại bên trái kể cả khi chưa có tin nhắn trước đó
            $found = false;
            foreach ($conversations as $c) {
                if ((int)$c['partner_id'] === $partnerId) {
                    $found = true;
                    break;
                }
            }
            if (!$found && $activePartner) {
                array_unshift($conversations, [
                    'partner_id' => $activePartner['account_id'],
                    'partner_role_code' => $activePartner['role_code'] ?? 'JOB_SEEKER',
                    'partner_role_name' => $activePartner['role_name'] ?? 'Người lao động',
                    'partner_name' => $activePartner['display_name'] ?? 'Người lao động',
                    'partner_avatar' => $activePartner['avatar_url'] ?? '',
                    'seeker_id' => $activePartner['seeker_id'] ?? $seekerId,
                    'employer_id' => $activePartner['employer_id'] ?? null,
                    'last_message' => 'Bắt đầu cuộc trò chuyện & hẹn lịch...',
                    'last_message_time' => null,
                    'unread_count' => 0
                ]);
            }
        }

        // 4. Lấy danh sách tin tuyển dụng đang mở của NTD để chọn trong dialog hẹn lịch
        $employerId = $employer['employer_id'] ?? 0;
        $allEmployerJobs = $employerId > 0 ? JobPost::getByEmployer($employerId) : [];
        $activeJobs = array_values(array_filter($allEmployerJobs, fn($j) => ($j['post_status'] ?? '') === 'approved'));

        foreach ($activeJobs as &$job) {
            $job['salary_display'] = !empty($job['salary_amount']) ? formatCurrency($job['salary_amount']) : 'Thỏa thuận';
        }
        unset($job);

        if ($selectedJobId === 0 && !empty($activeJobs)) {
            $selectedJobId = intval($activeJobs[0]['job_post_id']);
        }

        // 5. Lấy danh sách các đơn ứng tuyển của người lao động này tại công ty của NTD (nếu có)
        $seekerApplications = [];
        if ($employerId > 0 && !empty($seekerId)) {
            $db = Database::getConnection();
            if ($db) {
                $stmtSeekerApps = $db->prepare("
                    SELECT r.*, p.job_title, p.workplace_address, p.salary_amount
                    FROM job_requests r
                    JOIN job_posts p ON r.job_post_id = p.job_post_id
                    WHERE r.seeker_id = :sid AND p.employer_id = :eid
                    ORDER BY r.applied_at DESC
                ");
                $stmtSeekerApps->execute([
                    ':sid' => $seekerId,
                    ':eid' => $employerId
                ]);
                $seekerApplications = $stmtSeekerApps->fetchAll(PDO::FETCH_ASSOC);
                foreach ($seekerApplications as &$app) {
                    $app['salary_display'] = !empty($app['salary_amount']) ? formatCurrency($app['salary_amount']) : 'Thỏa thuận';
                }
                unset($app);
            }
        }

        $pageTitle = 'Tin Nhắn Trao Đổi Người Lao Động & Chốt Lịch - JobLink';
        $layoutFile = __DIR__ . '/../layouts/user_layout.php';
        require_once __DIR__ . '/chat_messenger.php';
    }

    /**
     * Khung chat trao đổi phía Admin & CSKH (Admin)
     */
    public function adminMessages() {
        $user = AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $myAccountId = $user['account_id'];

        $conversations = Message::getConversations($myAccountId);

        $rawPartner = $_GET['partner_id'] ?? '';
        $isAiActive = ($rawPartner === 'ai_joby');
        $partnerId = $isAiActive ? 'ai_joby' : intval($rawPartner);
        if (!$isAiActive && $partnerId === 0) {
            if (!empty($conversations)) {
                $partnerId = intval($conversations[0]['partner_id']);
            } else {
                $isAiActive = true;
                $partnerId = 'ai_joby';
            }
        }

        $activePartner = null;
        $thread = [];
        if (!$isAiActive && is_numeric($partnerId) && (int)$partnerId > 0) {
            $activePartner = Message::getPartnerInfo((int)$partnerId);
            $thread = Message::getThread($myAccountId, (int)$partnerId);
        }

        $accounts = UserAccount::all();
        $pageTitle = 'Tin Nhắn & Hỗ Trợ Người Dùng - JobLink Admin';
        $layoutFile = __DIR__ . '/../layouts/admin_layout.php';
        require_once __DIR__ . '/chat_messenger.php';
    }

    /**
     * API AJAX: Lấy toàn bộ luồng chat với một đối tác
     */
    public function getThreadAjax() {
        header('Content-Type: application/json; charset=UTF-8');
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit();
        }

        $partnerId = intval($_GET['partner_id'] ?? 0);
        if ($partnerId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Đối tác không hợp lệ']);
            exit();
        }

        $partner = Message::getPartnerInfo($partnerId);
        $thread = Message::getThread($user['account_id'], $partnerId);

        echo json_encode([
            'success' => true,
            'my_account_id' => $user['account_id'],
            'partner' => $partner,
            'messages' => $thread
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * API AJAX: Lấy danh sách cuộc trò chuyện cập nhật
     */
    public function getConversationsAjax() {
        header('Content-Type: application/json; charset=UTF-8');
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit();
        }

        $conversations = Message::getConversations($user['account_id']);
        echo json_encode([
            'success' => true,
            'conversations' => $conversations
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * API AJAX: Gửi tin nhắn tức thì không reload trang
     */
    public function sendAjax() {
        header('Content-Type: application/json; charset=UTF-8');
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit();
        }

        $receiverId = intval($_POST['receiver_account_id'] ?? 0);
        $content = trim($_POST['message_content'] ?? '');
        $title = trim($_POST['message_title'] ?? null);

        if ($receiverId <= 0 || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập nội dung tin nhắn!']);
            exit();
        }

        $data = Message::send($user['account_id'], $receiverId, $content, $title, 'normal');

        if ($data) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã gửi tin nhắn thành công!',
                'data' => $data
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể gửi tin nhắn lúc này.']);
        }
        exit();
    }

    /**
     * Gửi tin nhắn trực tiếp qua Form POST (fallback)
     */
    public function send() {
        $user = AuthController::checkRole(['JOB_SEEKER', 'EMPLOYER_ADMIN', 'INTERMEDIARY', 'SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $receiverId = intval($_POST['receiver_account_id'] ?? 0);
            $content = trim($_POST['message_content'] ?? '');
            $title = trim($_POST['message_title'] ?? null);

            $sent = Message::send($user['account_id'], $receiverId, $content, $title, 'normal');

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => !empty($sent), 'message' => 'Đã gửi tin nhắn thành công!'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_msg'] = 'Đã gửi tin nhắn thành công!';
        }

        $redirectUrl = url('/seeker/messages');
        if (in_array($user['role_code'], ['EMPLOYER_ADMIN', 'INTERMEDIARY'])) {
            $redirectUrl = url('/employer/messages');
        } elseif (in_array($user['role_code'], ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
            $redirectUrl = url('/admin/messages');
        }

        header('Location: ' . $redirectUrl . ($receiverId > 0 ? '?partner_id=' . $receiverId : ''));
        exit();
    }

    /**
     * Gửi thông báo toàn hệ thống (Admin)
     */
    public function broadcast() {
        $user = AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $receiverId = intval($_POST['receiver_account_id'] ?? 0);
            $title = trim($_POST['message_title'] ?? '');
            $content = trim($_POST['message_content'] ?? '');

            $sent = Message::send($user['account_id'], $receiverId, $content, $title, 'notification');

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => !empty($sent), 'message' => 'Đã gửi thông báo hệ thống thành công!'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_msg'] = 'Đã gửi thông báo hệ thống thành công!';
        }
        header('Location: ' . url('/admin/messages'));
        exit();
    }
}

