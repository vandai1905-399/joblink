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

    public static function findByAccountId($accountId) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM user_staffs WHERE account_id = :acc_id LIMIT 1");
            $stmt->execute([':acc_id' => $accountId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("[UserStaff::findByAccountId Error] " . $e->getMessage());
            return null;
        }
    }

    public static function findById($staffId) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM user_staffs WHERE staff_id = :id LIMIT 1");
            $stmt->execute([':id' => $staffId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("[UserStaff::findById Error] " . $e->getMessage());
            return null;
        }
    }

    public static function createDefault($accountId, $fullName = 'Nhân Viên', $phone = '') {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                INSERT INTO user_staffs (account_id, full_name, phone_number, created_at)
                VALUES (:acc_id, :full_name, :phone, NOW())
            ");
            $stmt->execute([
                ':acc_id' => $accountId,
                ':full_name' => $fullName,
                ':phone' => $phone
            ]);
            return $db->lastInsertId();
        } catch (PDOException $e) {
            error_log("[UserStaff::createDefault Error] " . $e->getMessage());
            return false;
        }
    }

    public static function update($staffId, $data) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE user_staffs SET 
                    full_name = :full_name,
                    department = :dept,
                    position = :pos,
                    phone_number = :phone,
                    avatar_url = COALESCE(:avatar, avatar_url)
                WHERE staff_id = :id
            ");
            return $stmt->execute([
                ':full_name' => $data['full_name'] ?? '',
                ':dept' => $data['department'] ?? null,
                ':pos' => $data['position'] ?? null,
                ':phone' => $data['phone_number'] ?? null,
                ':avatar' => $data['avatar_url'] ?? null,
                ':id' => $staffId
            ]);
        } catch (PDOException $e) {
            error_log("[UserStaff::update Error] " . $e->getMessage());
            return false;
        }
    }
}
