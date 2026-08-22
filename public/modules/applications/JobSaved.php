<?php
// models/JobSaved.php - Quản lý việc đã lưu job_saved (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class JobSaved {
    public static function getBySeeker($seekerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT s.*, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.avatar_url 
                FROM job_saved s
                JOIN job_posts p ON s.job_post_id = p.job_post_id
                JOIN user_employers e ON p.employer_id = e.employer_id
                WHERE s.seeker_id = :sid
                ORDER BY s.saved_at DESC
            ");
            $stmt->execute([':sid' => $seekerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[JobSaved::getBySeeker Error] " . $e->getMessage());
            return [];
        }
    }

    public static function toggle($seekerId, $jobPostId) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("SELECT job_saved_id FROM job_saved WHERE seeker_id = :sid AND job_post_id = :jid");
            $stmt->execute([':sid' => $seekerId, ':jid' => $jobPostId]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($exists) {
                $del = $db->prepare("DELETE FROM job_saved WHERE job_saved_id = :id");
                $del->execute([':id' => $exists['job_saved_id']]);
                return false;
            } else {
                $ins = $db->prepare("INSERT INTO job_saved (seeker_id, job_post_id, saved_at) VALUES (:sid, :jid, NOW())");
                $ins->execute([':sid' => $seekerId, ':jid' => $jobPostId]);
                return true;
            }
        } catch (PDOException $e) {
            error_log("[JobSaved::toggle Error] " . $e->getMessage());
            return false;
        }
    }
}
