<?php
// modules/messages/MessageController.php - Quản lý khung chat trao đổi 2 chiều & tin nhắn

require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../public/modules/auth/AuthController.php';
require_once __DIR__ . '/../../../public/modules/auth/UserAccount.php';
require_once __DIR__ . '/../../../public/modules/profiles/UserEmployer.php';
require_once __DIR__ . '/../../../public/modules/profiles/UserSeeker.php';
require_once __DIR__ . '/Message.php';

class MessageController {

    /**
     * Khung chat trao đổi phía Người lao động (Seeker)
     */
    public function seekerMessages() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $myAccountId = $user['account_id'];

        $conversations = Message::getConversations($myAccountId);
        
        $partnerId = intval($_GET['partner_id'] ?? 0);
        if ($partnerId === 0 && !empty($conversations)) {
            $partnerId = intval($conversations[0]['partner_id']);
        }

        $activePartner = null;
        $thread = [];
        if ($partnerId > 0) {
            $activePartner = Message::getPartnerInfo($partnerId);
            $thread = Message::getThread($myAccountId, $partnerId);
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

        $partnerId = intval($_GET['partner_id'] ?? 0);
        if ($partnerId === 0 && !empty($conversations)) {
            $partnerId = intval($conversations[0]['partner_id']);
        }

        $activePartner = null;
        $thread = [];
        if ($partnerId > 0) {
            $activePartner = Message::getPartnerInfo($partnerId);
            $thread = Message::getThread($myAccountId, $partnerId);
        }

        $pageTitle = 'Tin Nhắn Trao Đổi Ứng Viên & Chốt Lịch - JobLink';
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

        $partnerId = intval($_GET['partner_id'] ?? 0);
        if ($partnerId === 0 && !empty($conversations)) {
            $partnerId = intval($conversations[0]['partner_id']);
        }

        $activePartner = null;
        $thread = [];
        if ($partnerId > 0) {
            $activePartner = Message::getPartnerInfo($partnerId);
            $thread = Message::getThread($myAccountId, $partnerId);
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

