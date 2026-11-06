<?php
// modules/reviews/ReviewController.php - Quản lý reviews & review_criteria

require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../config/Database.php';

class ReviewController {

    /**
     * Đánh giá nơi làm việc (Seeker) - DÀNH RIÊNG CHO PART-TIME & THỜI VỤ
     */
    public function seekerReviews() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $seekerId = $seeker['seeker_id'] ?? 0;
        $myReviews = Review::getBySeeker($seekerId);
        $criteriaList = ReviewCriteria::getByTargetType('employer');

        // Chỉ lấy các ca làm Part-time & Thời vụ mà Người lao động đã trúng tuyển / nhận việc
        $eligibleShifts = Review::getEligibleShiftsForSeeker($seekerId);
        $pageTitle = 'Đánh Giá Ca Làm Việc (Part-time & Thời Vụ) - JobLink';
        require_once __DIR__ . '/seeker_reviews.php';
    }

    /**
     * Người lao động tạo đánh giá Doanh nghiệp (Seeker) - DÀNH RIÊNG CHO PART-TIME & THỜI VỤ
     */
    public function createReview() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $seekerId = $seeker['seeker_id'] ?? 0;
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $employerId = intval($_POST['employer_id'] ?? 0);

            // KIỂM TRA ĐIỀU KIỆN: Chỉ người lao động đã nhận việc các ca Part-time / Thời vụ mới được đánh giá
            $eligibleShifts = Review::getEligibleShiftsForSeeker($seekerId);
            $validEmployerIds = array_map(function($s) { return intval($s['employer_id']); }, $eligibleShifts);

            if (!in_array($employerId, $validEmployerIds, true)) {
                $errorMsg = 'Tính năng đánh giá chỉ dành riêng cho các ca làm việc Part-time & Thời vụ bạn đã được nhận việc.';
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $errorMsg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['flash_error'] = $errorMsg;
                header('Location: ' . url('/seeker/reviews'));
                exit();
            }

            $id = Review::create([
                'review_type' => 'employer_review',
                'reviewer_account_id' => $user['account_id'],
                'employer_id' => $employerId,
                'seeker_id' => $seekerId,
                'criteria_id' => intval($_POST['criteria_id']),
                'overall_rating' => intval($_POST['overall_rating']),
                'review_content' => trim($_POST['review_content']),
                'is_anonymous' => isset($_POST['is_anonymous']) ? 1 : 0
            ]);

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => (bool)$id, 'message' => 'Đã gửi đánh giá ca làm việc thành công!'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_msg'] = 'Đã gửi đánh giá ca làm việc thành công!';
        }
        header('Location: ' . url('/seeker/reviews'));
        exit();
    }

    /**
     * Đánh giá người lao động sau ca làm (Employer) - DÀNH RIÊNG CHO PART-TIME & THỜI VỤ
     */
    public function employerReviews() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $employerId = $employer['employer_id'] ?? 0;
        $criteriaList = ReviewCriteria::getByTargetType('candidate');

        // Chỉ lấy nhân sự đã nhận việc vào các ca Part-time & Thời vụ
        $eligibleCandidates = Review::getEligibleShiftsForEmployer($employerId);
        $reviews = Review::getByEmployer($employerId);
        $pageTitle = 'Đánh Giá Người Lao Động Sau Ca Làm (Part-time & Thời Vụ) - JobLink';
        require_once __DIR__ . '/candidate_reviews.php';
    }

    /**
     * NTD lưu đánh giá người lao động (Employer) - DÀNH RIÊNG CHO PART-TIME & THỜI VỤ
     */
    public function storeCandidateReview() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $employerId = $employer['employer_id'] ?? 0;
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $seekerId = intval($_POST['seeker_id'] ?? 0);

            // KIỂM TRA ĐIỀU KIỆN: Chỉ đánh giá ứng viên các ca Part-time & Thời vụ đã nhận việc
            $eligibleCandidates = Review::getEligibleShiftsForEmployer($employerId);
            $validSeekerIds = array_map(function($c) { return intval($c['seeker_id']); }, $eligibleCandidates);

            if (!in_array($seekerId, $validSeekerIds, true)) {
                $errorMsg = 'Tính năng đánh giá chỉ dành riêng cho người lao động đã nhận việc các ca Part-time & Thời vụ.';
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $errorMsg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['flash_error'] = $errorMsg;
                header('Location: ' . url('/employer/candidate-reviews'));
                exit();
            }

            $id = Review::create([
                'review_type' => 'candidate_review',
                'reviewer_account_id' => $user['account_id'],
                'employer_id' => $employerId,
                'seeker_id' => $seekerId,
                'criteria_id' => intval($_POST['criteria_id']),
                'overall_rating' => intval($_POST['overall_rating']),
                'review_content' => trim($_POST['review_content']),
                'is_anonymous' => 0
            ]);

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => (bool)$id, 'message' => 'Đã gửi đánh giá người lao động thành công!'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_msg'] = 'Đã gửi đánh giá người lao động thành công!';
        }
        header('Location: ' . url('/employer/candidate-reviews'));
        exit();
    }

    /**
     * Danh sách kiểm duyệt đánh giá (Admin)
     */
    public function adminReviews() {
        AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $reviews = Review::all();
        $pageTitle = 'Kiểm Duyệt Đánh Giá - JobLink Admin';
        require_once __DIR__ . '/admin_reviews.php';
    }

    /**
     * Admin duyệt / ẩn đánh giá
     */
    public function moderateReview($id) {
        AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $status = $_POST['status'] ?? 'approved';
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        $reason = trim($_POST['rejection_reason'] ?? '');
        Review::updateStatus($id, $status);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true, 'status' => $status, 'message' => 'Đã cập nhật trạng thái đánh giá!'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($status === 'approved') {
            $_SESSION['flash_msg'] = 'Đã duyệt công khai bài đánh giá!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = 'Đã ẩn/từ chối bài đánh giá!' . ($reason ? " (Lý do: {$reason})" : '');
            $_SESSION['flash_type'] = 'warning';
        }

        header('Location: ' . url('/admin/reviews'));
        exit();
    }

    /**
     * Cấu hình tiêu chí đánh giá (Admin)
     */
    public function reviewCriteria() {
        AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $criteria = ReviewCriteria::all();
        $pageTitle = 'Cấu Hình Tiêu Chí Đánh Giá - JobLink Admin';
        require_once __DIR__ . '/admin_review_criteria.php';
    }

    /**
     * Admin xóa đánh giá
     */
    public function deleteReview($id) {
        AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        Review::delete($id);
        $_SESSION['flash_msg'] = 'Đã xóa đánh giá thành công!';
        header('Location: ' . url('/admin/reviews'));
        exit();
    }
}
