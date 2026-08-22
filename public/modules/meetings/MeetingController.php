<?php
// modules/meetings/MeetingController.php - Quản lý candidate_meetings

require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../config/Database.php';

class MeetingController {

    /**
     * Lịch phỏng vấn phía Người lao động (Seeker)
     */
    public function seekerMeetings() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $meetings = CandidateMeeting::getBySeeker($seeker['seeker_id'] ?? 0);
        $pageTitle = 'Lịch Phỏng Vấn & Cuộc Hẹn - JobLink';
        require_once __DIR__ . '/seeker_meetings.php';
    }

    /**
     * Lịch phỏng vấn phía Nhà tuyển dụng (Employer)
     */
    public function employerMeetings() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $meetings = CandidateMeeting::getByEmployer($employer['employer_id'] ?? 0);
        $applications = JobRequest::getByEmployer($employer['employer_id'] ?? 0);
        $pageTitle = 'Quản Lý Lịch Phỏng Vấn & Cuộc Hẹn - JobLink';
        require_once __DIR__ . '/employer_meetings.php';
    }

    /**
     * Lên lịch hẹn mới (Employer)
     */
    public function store() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestId = intval($_POST['request_id']);
            $created = CandidateMeeting::create([
                'request_id' => $requestId,
                'created_by_account_id' => $user['account_id'],
                'meeting_date' => $_POST['meeting_date'],
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'] ?? null,
                'location_or_meeting_link' => trim($_POST['location_or_meeting_link']),
                'meeting_format' => $_POST['meeting_format'] ?? 'offline',
                'meeting_note' => trim($_POST['meeting_note'] ?? '')
            ]);

            if ($created) {
                // Gửi thông báo Gmail cho người lao động
                try {
                    $db = Database::getConnection();
                    $stmt = $db->prepare("
                        SELECT r.seeker_id, e.employer_name 
                        FROM job_requests r 
                        JOIN job_posts p ON r.job_post_id = p.job_post_id 
                        JOIN user_employers e ON p.employer_id = e.employer_id 
                        WHERE r.request_id = :rid
                    ");
                    $stmt->execute([':rid' => $requestId]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        require_once __DIR__ . '/../../../services/MailService.php';
                        $dt = $_POST['meeting_date'] . ' ' . $_POST['start_time'];
                        MailService::notifyMeetingScheduled($row['seeker_id'], $row['employer_name'], $dt, $_POST['location_or_meeting_link']);
                    }
                } catch (Throwable $e) {
                    error_log("[Meeting Mail Warning] " . $e->getMessage());
                }
            }

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => $created, 'message' => 'Đã lên lịch phỏng vấn và gửi thông báo qua Gmail thành công!'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_msg'] = 'Đã lên lịch phỏng vấn và gửi thông báo qua Gmail thành công!';
        }
        header('Location: ' . url('/employer/meetings'));
        exit();
    }

    /**
     * Cập nhật trạng thái cuộc hẹn
     */
    public function updateStatus($id) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN', 'JOB_SEEKER']);
        $status = $_POST['status'] ?? 'completed';
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        $reason = trim($_POST['rejection_reason'] ?? '');
        CandidateMeeting::updateStatus($id, $status);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true, 'status' => $status, 'message' => 'Đã cập nhật trạng thái cuộc hẹn!'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($status === 'completed') {
            $_SESSION['flash_msg'] = 'Đã hoàn thành buổi phỏng vấn!';
            $_SESSION['flash_type'] = 'success';
        } elseif ($status === 'cancelled') {
            $_SESSION['flash_msg'] = 'Đã hủy lịch phỏng vấn!' . ($reason ? " (Lý do: {$reason})" : '');
            $_SESSION['flash_type'] = 'warning';
        } else {
            $_SESSION['flash_msg'] = 'Đã lên lịch lại buổi phỏng vấn!';
            $_SESSION['flash_type'] = 'info';
        }

        if ($user['role_code'] === 'EMPLOYER_ADMIN') {
            header('Location: ' . url('/employer/meetings'));
        } else {
            header('Location: ' . url('/seeker/meetings'));
        }
        exit();
    }

    /**
     * Xóa lịch hẹn phỏng vấn
     */
    public function delete($id) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        CandidateMeeting::delete($id);
        $_SESSION['flash_msg'] = 'Đã xóa lịch hẹn thành công!';
        header('Location: ' . url('/employer/meetings'));
        exit();
    }
}
