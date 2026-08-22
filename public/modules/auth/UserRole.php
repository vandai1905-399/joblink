<?php
// models/UserRole.php - Quản lý quyền hệ thống user_roles (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class UserRole {
    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            return $db->query("SELECT * FROM user_roles ORDER BY user_role_id ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[UserRole::all Error] " . $e->getMessage());
            return [];
        }
    }
}
