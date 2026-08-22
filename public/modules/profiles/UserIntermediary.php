<?php
// models/UserIntermediary.php - Quản lý thông tin người trung gian / đại lý môi giới (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class UserIntermediary {

    public static function findByAccountId($accountId) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM user_intermediaries WHERE account_id = :acc_id");
            $stmt->execute([':acc_id' => $accountId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("[UserIntermediary::findByAccountId Error] " . $e->getMessage());
            return null;
        }
    }

    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            return $db->query("
                SELECT ui.*, a.username, a.email as account_email
                FROM user_intermediaries ui
                JOIN user_accounts a ON ui.account_id = a.account_id
                ORDER BY ui.intermediary_id ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function update($intermediaryId, $data) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE user_intermediaries SET 
                    full_name = :fname,
                    company_agency_name = :agency,
                    id_card_number = :idcard,
                    phone_number = :phone,
                    email = :email,
                    address = :address,
                    license_number = :license
                WHERE intermediary_id = :id
            ");
            return $stmt->execute([
                ':fname'   => $data['full_name'],
                ':agency'  => $data['company_agency_name'] ?? null,
                ':idcard'  => $data['id_card_number'] ?? null,
                ':phone'   => $data['phone_number'] ?? null,
                ':email'   => $data['email'] ?? null,
                ':address' => $data['address'] ?? null,
                ':license' => $data['license_number'] ?? null,
                ':id'      => $intermediaryId
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
