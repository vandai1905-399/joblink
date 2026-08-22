<?php
// models/DigitalSignature.php - Quản lý chứng thư số & chữ ký số doanh nghiệp (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class DigitalSignature {

    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            return $db->query("
                SELECT ds.*, e.employer_name, e.contact_email, a.username 
                FROM digital_signatures ds
                LEFT JOIN user_employers e ON ds.employer_id = e.employer_id
                LEFT JOIN user_accounts a ON ds.account_id = a.account_id
                ORDER BY ds.id DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[DigitalSignature::all Error] " . $e->getMessage());
            return [];
        }
    }

    public static function findById($id) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT ds.*, e.employer_name, e.contact_email, a.username 
                FROM digital_signatures ds
                LEFT JOIN user_employers e ON ds.employer_id = e.employer_id
                LEFT JOIN user_accounts a ON ds.account_id = a.account_id
                WHERE ds.id = :id
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function getByEmployerId($employerId) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM digital_signatures WHERE employer_id = :eid ORDER BY id DESC LIMIT 1");
            $stmt->execute([':eid' => $employerId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
