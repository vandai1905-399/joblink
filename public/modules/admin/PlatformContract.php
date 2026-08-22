<?php
// models/PlatformContract.php - Quản lý hợp đồng điện tử ký số giữa Admin & Doanh Nghiệp (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class PlatformContract {

    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            return $db->query("
                SELECT c.*, e.employer_name, e.business_license_number, e.contact_email, e.avatar_url
                FROM platform_contracts c
                JOIN user_employers e ON c.employer_id = e.employer_id
                ORDER BY c.contract_id DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[PlatformContract::all Error] " . $e->getMessage());
            return [];
        }
    }

    public static function getByEmployerId($employerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT c.*, e.employer_name, e.business_license_number, e.contact_email
                FROM platform_contracts c
                JOIN user_employers e ON c.employer_id = e.employer_id
                WHERE c.employer_id = :eid
                ORDER BY c.contract_id DESC
            ");
            $stmt->execute([':eid' => $employerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function findById($contractId) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT c.*, e.employer_name, e.business_license_number, e.contact_email, e.phone_number, e.address, e.representative_name,
                       ds.serial_number as emp_cert_serial, ds.issuer_ca as emp_cert_ca, ds.cert_fingerprint as emp_cert_fp
                FROM platform_contracts c
                JOIN user_employers e ON c.employer_id = e.employer_id
                LEFT JOIN digital_signatures ds ON e.employer_id = ds.employer_id AND ds.status = 'active'
                WHERE c.contract_id = :id
            ");
            $stmt->execute([':id' => $contractId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function create($data) {
        $db = Database::getConnection();
        if (!$db) return false;

        $employerId = intval($data['employer_id']);
        $contractCode = 'HD-' . date('Y') . '-JL-' . str_pad($employerId, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);
        $title = trim($data['contract_title'] ?? 'Hợp Đồng Dịch Vụ Sàn Giao Dịch Việc Làm JobLink');
        $type = $data['contract_type'] ?? 'service_agreement';
        $content = trim($data['contract_content'] ?? '');
        $startDate = $data['start_date'] ?? date('Y-m-d');
        $endDate = $data['end_date'] ?? date('Y-m-d', strtotime('+1 year'));

        try {
            $stmt = $db->prepare("
                INSERT INTO platform_contracts 
                (contract_code, employer_id, contract_title, contract_type, contract_content, status, start_date, end_date, created_at)
                VALUES 
                (:code, :eid, :title, :type, :content, 'draft', :sdate, :edate, NOW())
            ");
            $stmt->execute([
                ':code'    => $contractCode,
                ':eid'     => $employerId,
                ':title'   => $title,
                ':type'    => $type,
                ':content' => $content,
                ':sdate'   => $startDate,
                ':edate'   => $endDate
            ]);
            return $db->lastInsertId();
        } catch (PDOException $e) {
            error_log("[PlatformContract::create Error] " . $e->getMessage());
            return false;
        }
    }
}
