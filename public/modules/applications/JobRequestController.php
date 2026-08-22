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
     * Xem chi tiết hồ sơ & đánh giá năng lực của người lao động (Employer)
     */
    public function candidateDetail($seekerId) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN', 'SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $employer = ($user['role_code'] === 'EMPLOYER_ADMIN') ? UserEmployer::findByAccountId($user['account_id']) : null;
        
        $candidate = UserSeeker::findById($seekerId);
        if (!$candidate) {
            $_SESSION['flash_msg'] = 'Không tìm thấy thông tin người lao động!';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . url('/employer/applications'));
            exit();
        }

        // Lấy danh sách đánh giá năng lực từ các NTD khác
        $reviews = Review::getBySeeker($seekerId);

        // Lấy các đơn ứng tuyển của người lao động này tại doanh nghiệp hiện tại
        $applications = [];
        if ($employer) {
            $db = Database::getConnection();
            if ($db) {
                $stmt = $db->prepare("
                    SELECT r.*, p.job_title 
                    FROM job_requests r
                    JOIN job_posts p ON r.job_post_id = p.job_post_id
                    WHERE p.employer_id = :eid AND r.seeker_id = :sid
                    ORDER BY r.applied_at DESC
                ");
                $stmt->execute([':eid' => $employer['employer_id'], ':sid' => $seekerId]);
                $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($applications as &$app) {
                    $analysis = AIService::analyzeJobFit($seekerId, $app['job_post_id']);
                    if ($analysis) {
                        $app['ai_analysis'] = $analysis;
                    }
                }
                unset($app);
            }
        }

        $pageTitle = 'Hồ Sơ Người Lao Động: ' . ($candidate['full_name'] ?? 'Chi Tiết') . ' - JobLink';
        require_once __DIR__ . '/candidate_detail.php';
    }
}
