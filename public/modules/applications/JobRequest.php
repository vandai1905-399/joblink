<?php
// models/JobRequest.php - Quản lý hồ sơ ứng tuyển job_requests (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../services/MailService.php';

class JobRequest {

    public static function getBySeeker($seekerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT r.*, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.avatar_url 
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_employers e ON p.employer_id = e.employer_id
                WHERE r.seeker_id = :sid
                ORDER BY r.applied_at DESC
            ");
            $stmt->execute([':sid' => $seekerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[JobRequest::getBySeeker Error] " . $e->getMessage());
            return [];
        }
    }

    public static function getByEmployer($employerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT r.*, s.full_name as seeker_name, s.email, s.phone_number, s.avatar_url as seeker_avatar, p.job_title 
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                WHERE p.employer_id = :eid
                ORDER BY r.applied_at DESC
            ");
            $stmt->execute([':eid' => $employerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[JobRequest::getByEmployer Error] " . $e->getMessage());
            return [];
        }
    }

    public static function apply($seekerId, $jobPostId, $availableWorkDates = null) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            // Kiểm tra tính ràng buộc & Điểm uy tín của người lao động
            $stmtSeeker = $db->prepare("SELECT full_name, trust_score, agreement_signed FROM user_seekers WHERE seeker_id = :sid");
            $stmtSeeker->execute([':sid' => $seekerId]);
            $seeker = $stmtSeeker->fetch(PDO::FETCH_ASSOC);

            if ($seeker && intval($seeker['trust_score']) < 60) {
                error_log("[JobRequest] Seeker $seekerId trust score is below 60");
                return false;
            }

            $stmt = $db->prepare("
                INSERT INTO job_requests (seeker_id, job_post_id, available_work_dates, request_status, applied_at)
                VALUES (:sid, :jid, :dates, 'pending', NOW())
            ");
            $result = $stmt->execute([
                ':sid' => $seekerId, 
                ':jid' => $jobPostId,
                ':dates' => $availableWorkDates
            ]);

            if ($result) {
                // Lấy thông tin tin tuyển dụng & nhà tuyển dụng để gửi thông báo Gmail
                $stmtJob = $db->prepare("SELECT job_title, employer_id FROM job_posts WHERE job_post_id = :jid");
                $stmtJob->execute([':jid' => $jobPostId]);
                $job = $stmtJob->fetch(PDO::FETCH_ASSOC);

                if ($job) {
                    try {
                        MailService::notifyNewApplication($job['employer_id'], $seeker['full_name'] ?? 'Người lao động JobLink', $job['job_title']);
                    } catch (Throwable $e) {
                        error_log("[JobRequest Mail Warning] " . $e->getMessage());
                    }
                }
            }

            return $result;
        } catch (PDOException $e) {
            error_log("[JobRequest::apply Error] " . $e->getMessage());
            return false;
        }
    }

    public static function updateStatus($requestId, $status, $accountId, $rejectionReason = null) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE job_requests SET 
                    request_status = :st, 
                    processed_by_account_id = :acc, 
                    rejection_reason = :reason,
                    status_updated_at = NOW()
                WHERE request_id = :id
            ");
            return $stmt->execute([
                ':st' => $status,
                ':acc' => $accountId,
                ':reason' => $rejectionReason,
                ':id' => $requestId
            ]);
        } catch (PDOException $e) {
            error_log("[JobRequest::updateStatus Error] " . $e->getMessage());
            return false;
        }
    }

    public static function delete($requestId) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("DELETE FROM job_requests WHERE request_id = :id");
            return $stmt->execute([':id' => $requestId]);
        } catch (PDOException $e) {
            error_log("[JobRequest::delete Error] " . $e->getMessage());
            return false;
        }
    }
}
