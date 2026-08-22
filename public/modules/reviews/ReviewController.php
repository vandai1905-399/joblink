<?php
// modules/reviews/ReviewController.php - Quản lý reviews & review_criteria

require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../config/Database.php';

class ReviewController {

    /**
     * Đánh giá nơi làm việc (Seeker)
     */
    public function seekerReviews() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $myReviews = Review::getBySeeker($seeker['seeker_id'] ?? 0);
        $criteriaList = ReviewCriteria::getByTargetType('employer');
        $employers = UserEmployer::all();
        $pageTitle = 'Đánh Giá & Nhận Xét Nơi Làm Việc - JobLink';
        require_once __DIR__ . '/seeker_reviews.php';
    }

    /**
     * Người lao động tạo đánh giá Doanh nghiệp (Seeker)
     */
    public function createReview() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = Review::create([
                'review_type' => 'employer_review',
                'reviewer_account_id' => $user['account_id'],
                'employer_id' => intval($_POST['employer_id']),
                'seeker_id' => $seeker['seeker_id'] ?? 0,
                'criteria_id' => intval($_POST['criteria_id']),
                'overall_rating' => intval($_POST['overall_rating']),
                'review_content' => trim($_POST['review_content']),
                'is_anonymous' => isset($_POST['is_anonymous']) ? 1 : 0
            ]);

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => (bool)$id, 'message' => 'Đã gửi đánh giá thành công!'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_msg'] = 'Đã gửi đánh giá thành công!';
        }
        header('Location: ' . url('/seeker/reviews'));
        exit();
    }

    /**
     * Đánh giá người lao động sau ca làm (Employer)
     */
    public function employerReviews() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $criteriaList = ReviewCriteria::getByTargetType('candidate');
        $seekers = UserSeeker::all();
        $reviews = Review::getByEmployer($employer['employer_id'] ?? 0);
        $pageTitle = 'Đánh Giá Người Lao Động Sau Ca Làm - JobLink';
        require_once __DIR__ . '/candidate_reviews.php';
    }

    /**
     * NTD lưu đánh giá người lao động (Employer)
     */
    public function storeCandidateReview() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = Review::create([
                'review_type' => 'candidate_review',
                'reviewer_account_id' => $user['account_id'],
                'employer_id' => $employer['employer_id'] ?? 0,
                'seeker_id' => intval($_POST['seeker_id']),
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
