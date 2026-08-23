<?php
// modules/applications/JobRequestController.php - Quản lý job_requests & job_saved

require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../services/AIService.php';

class JobRequestController {

    /**
     * Nộp hồ sơ ứng tuyển (Seeker)
     */
    public function apply($jobId = null) {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $targetJobId = $jobId ?? ($_POST['job_id'] ?? 0);
        $availableWorkDates = trim($_POST['available_work_dates'] ?? '');
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($targetJobId && $seeker) {
            $success = JobRequest::apply($seeker['seeker_id'], $targetJobId, $availableWorkDates ?: null);
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => $success, 'message' => 'Nộp đơn ứng tuyển thành công! Nhà tuyển dụng sẽ sớm liên hệ.'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_msg'] = 'Nộp đơn ứng tuyển thành công! Nhà tuyển dụng sẽ sớm liên hệ.';
        }
        header('Location: ' . url('/jobs/' . $targetJobId));
        exit();
    }

    /**
     * Danh sách việc đã ứng tuyển (Seeker)
     */
    public function getAppliedJobs() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $appliedJobs = JobRequest::getBySeeker($seeker['seeker_id'] ?? 0);
        $pageTitle = 'Việc Đã Ứng Tuyển - JobLink';
        require_once __DIR__ . '/seeker_applied_jobs.php';
    }

    /**
     * Danh sách việc làm đã lưu (Seeker)
     */
    public function getSavedJobs() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $savedJobs = JobSaved::getBySeeker($seeker['seeker_id'] ?? 0);
        $pageTitle = 'Việc Làm Đã Lưu - JobLink';
        require_once __DIR__ . '/seeker_saved_jobs.php';
    }

    /**
     * Lưu / Bỏ lưu tin việc làm (Seeker)
     */
    public function toggleSave($jobId) {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $saved = JobSaved::toggle($seeker['seeker_id'] ?? 0, $jobId);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true, 'saved' => $saved, 'message' => $saved ? 'Đã lưu việc làm!' : 'Đã bỏ lưu việc làm!'], JSON_UNESCAPED_UNICODE);
            exit();
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('/seeker/saved-jobs')));
        exit();
    }

    /**
     * Danh sách hồ sơ ứng tuyển nhận được (Employer)
     */
    public function getEmployerApplications() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $applications = JobRequest::getByEmployer($employer['employer_id'] ?? 0);

        // Tính điểm tương thích AI cho từng đơn ứng tuyển
        foreach ($applications as &$app) {
            $analysis = AIService::analyzeJobFit($app['seeker_id'], $app['job_post_id']);
            if ($analysis) {
                $app['ai_match_score'] = $analysis['score'];
                $app['ai_match_level'] = $analysis['level'];
                $app['ai_match_label'] = $analysis['label'];
                $app['ai_matched_skills'] = $analysis['matched_skills'];
            }
        }
        unset($app);

        $pageTitle = 'Quản Lý Hồ Sơ Ứng Tuyển - JobLink';
        require_once __DIR__ . '/employer_applications.php';
    }

    /**
     * Duyệt hoặc từ chối đơn ứng tuyển (Employer)
     */
    public function updateStatus($id) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $status = $_POST['status'] ?? 'accepted';
        $reason = $_POST['rejection_reason'] ?? null;
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        JobRequest::updateStatus($id, $status, $user['account_id'], $reason);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true, 'status' => $status, 'message' => 'Đã cập nhật trạng thái đơn ứng tuyển!'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($status === 'accepted') {
            $_SESSION['flash_msg'] = 'Đã duyệt hồ sơ người lao động thành công!';
            $_SESSION['flash_type'] = 'success';
        } elseif ($status === 'rejected') {
            $_SESSION['flash_msg'] = 'Đã từ chối hồ sơ người lao động!' . ($reason ? " (Lý do: {$reason})" : '');
            $_SESSION['flash_type'] = 'warning';
        } else {
            $_SESSION['flash_msg'] = 'Đã chuyển hồ sơ về trạng thái chờ duyệt!' . ($reason ? " (Lý do: {$reason})" : '');
            $_SESSION['flash_type'] = 'info';
        }

        $redirectUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : url('/employer/applications');
        header('Location: ' . $redirectUrl);
        exit();
    }

    /**
     * Xóa đơn ứng tuyển (Employer)
     */
    public function delete($id) {
        AuthController::checkRole(['EMPLOYER_ADMIN']);
        JobRequest::delete($id);
        $_SESSION['flash_msg'] = 'Đã xóa đơn ứng tuyển thành công!';
        $redirectUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : url('/employer/applications');
        header('Location: ' . $redirectUrl);
        exit();
    }

    /**
     * Gửi lời mời nhận việc / phỏng vấn đến Người lao động (Employer)
     */
    public function inviteCandidate() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $employer) {
            $seekerId = intval($_POST['seeker_id'] ?? 0);
            $jobPostId = intval($_POST['job_post_id'] ?? 0);
            $note = trim($_POST['invitation_note'] ?? '');

            if ($seekerId > 0 && $jobPostId > 0) {
                $res = JobRequest::inviteCandidate($employer['employer_id'], $seekerId, $jobPostId, $note);
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['flash_msg'] = $res['message'];
                $_SESSION['flash_type'] = $res['success'] ? 'success' : 'warning';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('/employer/jobs/' . $jobPostId . '/candidates')));
                exit();
            }
        }

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], JSON_UNESCAPED_UNICODE);
            exit();
        }
        header('Location: ' . url('/employer/jobs'));
        exit();
    }

    /**
     * Phản hồi lời mời nhận việc (Seeker: Đồng ý / Từ chối)
     */
    public function respondInvitation($requestId) {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $action = trim($_POST['action'] ?? ($_GET['action'] ?? 'accept'));
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        $res = JobRequest::respondInvitation(intval($requestId), $action, $user['account_id']);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }

        $_SESSION['flash_msg'] = $res['message'];
        $_SESSION['flash_type'] = $res['success'] ? 'success' : 'warning';
        header('Location: ' . url('/seeker/applied-jobs'));
        exit();
    }

    /**
     * API Gợi Ý Khung Giờ Phỏng Vấn Rảnh Chống Trùng Lịch (AI Smart Time Slots)
     */
    public function apiSuggestMeetingSlots() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN', 'JOB_SEEKER', 'SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $employerId = intval($_GET['employer_id'] ?? 0);
        $seekerId = intval($_GET['seeker_id'] ?? 0);
        $requestId = intval($_GET['request_id'] ?? 0);

        $db = Database::getConnection();
        if ($requestId > 0 && ($employerId === 0 || $seekerId === 0) && $db) {
            $stmt = $db->prepare("
                SELECT r.seeker_id, p.employer_id 
                FROM job_requests r 
                JOIN job_posts p ON r.job_post_id = p.job_post_id 
                WHERE r.request_id = :id
            ");
            $stmt->execute([':id' => $requestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $seekerId = intval($row['seeker_id']);
                $employerId = intval($row['employer_id']);
            }
        }

        if ($employerId === 0 && $user['role_code'] === 'EMPLOYER_ADMIN') {
            $emp = UserEmployer::findByAccountId($user['account_id']);
            if ($emp) $employerId = intval($emp['employer_id']);
        }

        if ($seekerId === 0 && $user['role_code'] === 'JOB_SEEKER') {
            $sk = UserSeeker::findByAccountId($user['account_id']);
            if ($sk) $seekerId = intval($sk['seeker_id']);
        }

        $slots = AIService::suggestInterviewTimeSlots($employerId, $seekerId);

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => true,
            'employer_id' => $employerId,
            'seeker_id' => $seekerId,
            'slots' => $slots
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
}
