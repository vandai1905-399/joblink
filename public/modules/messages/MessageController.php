<?php
// modules/messages/MessageController.php - Quản lý messages

require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../config/Database.php';

class MessageController {

    /**
     * Hộp thư phía Người lao động (Seeker)
     */
    public function seekerMessages() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $messages = Message::getInbox($user['account_id']);
        $pageTitle = 'Hộp Thư & Thông Báo - JobLink';
        require_once __DIR__ . '/seeker_messages.php';
    }

    /**
     * Hộp thư phía Nhà tuyển dụng (Employer)
     */
    public function employerMessages() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $messages = Message::getInbox($user['account_id']);

        // Lấy danh sách các ứng viên đã kết nối để NTD có thể chọn gửi tin nhắn & chốt lịch AI
        $connectedCandidates = [];
        if ($employer) {
            $db = Database::getConnection();
            if ($db) {
                $stmt = $db->prepare("
                    SELECT DISTINCT s.account_id, s.seeker_id, s.full_name, s.avatar_url, p.job_title, r.request_status, r.request_id
                    FROM job_requests r
                    JOIN job_posts p ON r.job_post_id = p.job_post_id
                    JOIN user_seekers s ON r.seeker_id = s.seeker_id
                    WHERE p.employer_id = :eid
                    ORDER BY r.status_updated_at DESC, r.applied_at DESC
                ");
                $stmt->execute([':eid' => $employer['employer_id']]);
                $connectedCandidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $pageTitle = 'Tin Nhắn Trực Tiếp Người Lao Động - JobLink';
        require_once __DIR__ . '/employer_messages.php';
    }

    /**
     * Quản lý thông báo phía Admin (Admin)
     */
    public function adminMessages() {
        $user = AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $messages = Message::getInbox($user['account_id']);
        $accounts = UserAccount::all();
        $pageTitle = 'Gửi Thông Báo Hệ Thống - JobLink Admin';
        require_once __DIR__ . '/admin_messages.php';
    }

    /**
     * Gửi tin nhắn trực tiếp
     */
    public function send() {
        $user = AuthController::checkRole(['JOB_SEEKER', 'EMPLOYER_ADMIN', 'SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $receiverId = intval($_POST['receiver_account_id']);
            $content = trim($_POST['message_content']);
            $title = trim($_POST['message_title'] ?? null);

            $sent = Message::send($user['account_id'], $receiverId, $content, $title, 'normal');

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => $sent, 'message' => 'Đã gửi tin nhắn thành công!'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_msg'] = 'Đã gửi tin nhắn thành công!';
        }

        if ($user['role_code'] === 'EMPLOYER_ADMIN') {
            header('Location: ' . url('/employer/messages'));
        } elseif ($user['role_code'] === 'JOB_SEEKER') {
            header('Location: ' . url('/seeker/messages'));
        } else {
            header('Location: ' . url('/admin/messages'));
        }
        exit();
    }

    /**
     * Gửi thông báo toàn hệ thống (Admin)
     */
    public function broadcast() {
        $user = AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $receiverId = intval($_POST['receiver_account_id']);
            $title = trim($_POST['message_title']);
            $content = trim($_POST['message_content']);

            $sent = Message::send($user['account_id'], $receiverId, $content, $title, 'notification');

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => $sent, 'message' => 'Đã gửi thông báo hệ thống thành công!'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_msg'] = 'Đã gửi thông báo hệ thống thành công!';
        }
        header('Location: ' . url('/admin/messages'));
        exit();
    }
}
