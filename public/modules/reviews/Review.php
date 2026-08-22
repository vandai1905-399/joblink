<?php
// models/Review.php - Quản lý đánh giá reviews (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class Review {

    public static function getByEmployer($employerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT r.*, s.full_name as seeker_name, s.avatar_url as seeker_avatar, c.criteria_name 
                FROM reviews r
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                LEFT JOIN review_criteria c ON r.criteria_id = c.criteria_id
                WHERE r.employer_id = :eid AND r.approval_status = 'approved'
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([':eid' => $employerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[Review::getByEmployer Error] " . $e->getMessage());
            return [];
        }
    }

    public static function getBySeeker($seekerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT r.*, e.employer_name, e.avatar_url as employer_avatar, c.criteria_name 
                FROM reviews r
                JOIN user_employers e ON r.employer_id = e.employer_id
                LEFT JOIN review_criteria c ON r.criteria_id = c.criteria_id
                WHERE r.seeker_id = :sid AND r.approval_status = 'approved'
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([':sid' => $seekerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[Review::getBySeeker Error] " . $e->getMessage());
            return [];
        }
    }

    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            return $db->query("
                SELECT r.*, e.employer_name, s.full_name as seeker_name, c.criteria_name 
                FROM reviews r
                JOIN user_employers e ON r.employer_id = e.employer_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                LEFT JOIN review_criteria c ON r.criteria_id = c.criteria_id
                ORDER BY r.review_id DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[Review::all Error] " . $e->getMessage());
            return [];
        }
    }

    public static function create($data) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                INSERT INTO reviews (
                    review_type, reviewer_account_id, employer_id, seeker_id, criteria_id, 
                    overall_rating, review_content, is_anonymous, approval_status, created_at
                ) VALUES (
                    :type, :reviewer, :eid, :sid, :crit, :rating, :content, :anon, 'approved', NOW()
                )
            ");
            return $stmt->execute([
                ':type' => $data['review_type'],
                ':reviewer' => $data['reviewer_account_id'],
                ':eid' => $data['employer_id'],
                ':sid' => $data['seeker_id'],
                ':crit' => $data['criteria_id'],
                ':rating' => $data['overall_rating'],
                ':content' => $data['review_content'],
                ':anon' => $data['is_anonymous'] ?? 0
            ]);
        } catch (PDOException $e) {
            error_log("[Review::create Error] " . $e->getMessage());
            return false;
        }
    }

    public static function moderate($reviewId, $status) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("UPDATE reviews SET approval_status = :status WHERE review_id = :id");
            return $stmt->execute([':id' => $reviewId, ':status' => $status]);
        } catch (PDOException $e) {
            error_log("[Review::moderate Error] " . $e->getMessage());
            return false;
        }
    }

    public static function updateStatus($reviewId, $status) {
        return self::moderate($reviewId, $status);
    }

    public static function delete($reviewId) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("DELETE FROM reviews WHERE review_id = :id");
            return $stmt->execute([':id' => $reviewId]);
        } catch (PDOException $e) {
            error_log("[Review::delete Error] " . $e->getMessage());
            return false;
        }
    }
}
