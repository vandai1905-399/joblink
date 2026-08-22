<?php
// models/UserStaff.php - Quản lý nhân sự nội bộ user_staffs (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class UserStaff {

    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            return $db->query("SELECT * FROM user_staffs ORDER BY staff_id ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[UserStaff::all Error] " . $e->getMessage());
            return [];
        }
    }
}
