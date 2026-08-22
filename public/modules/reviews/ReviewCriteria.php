<?php
// models/ReviewCriteria.php - Quản lý tiêu chí đánh giá review_criteria (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class ReviewCriteria {

    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            return $db->query("SELECT * FROM review_criteria ORDER BY target_type ASC, display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[ReviewCriteria::all Error] " . $e->getMessage());
            return [];
        }
    }

    public static function getByTargetType($targetType) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("SELECT * FROM review_criteria WHERE target_type = :target ORDER BY display_order ASC");
            $stmt->execute([':target' => $targetType]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[ReviewCriteria::getByTargetType Error] " . $e->getMessage());
            return [];
        }
    }
}
