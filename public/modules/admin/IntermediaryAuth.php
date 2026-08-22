<?php
// models/IntermediaryAuth.php - Quản lý ủy quyền pháp lý giữa DN & Người Trung Gian (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class IntermediaryAuth {

    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            return $db->query("
                SELECT ia.*, e.employer_name, e.business_license_number, e.contact_email,
                       COALESCE(ui.full_name, a.username) as intermediary_name,
                       ui.company_agency_name, ui.phone_number as intermediary_phone,
                       a.email as intermediary_email
                FROM intermediary_authorizations ia
                JOIN user_employers e ON ia.employer_id = e.employer_id
                JOIN user_accounts a ON ia.intermediary_account_id = a.account_id
                LEFT JOIN user_intermediaries ui ON a.account_id = ui.account_id
                ORDER BY ia.auth_id DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[IntermediaryAuth::all Error] " . $e->getMessage());
            return [];
        }
    }

    public static function getByEmployerId($employerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT ia.*, COALESCE(ui.full_name, a.username) as intermediary_name,
                       ui.company_agency_name, ui.phone_number as intermediary_phone, a.email as intermediary_email
                FROM intermediary_authorizations ia
                JOIN user_accounts a ON ia.intermediary_account_id = a.account_id
                LEFT JOIN user_intermediaries ui ON a.account_id = ui.account_id
                WHERE ia.employer_id = :eid
                ORDER BY ia.auth_id DESC
            ");
            $stmt->execute([':eid' => $employerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function getByIntermediaryAccountId($accountId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT ia.*, e.employer_name, e.business_license_number, e.contact_email, e.avatar_url, e.phone_number as employer_phone
                FROM intermediary_authorizations ia
                JOIN user_employers e ON ia.employer_id = e.employer_id
                WHERE ia.intermediary_account_id = :acc
                ORDER BY ia.auth_id DESC
            ");
            $stmt->execute([':acc' => $accountId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function findById($authId) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT ia.*, e.employer_name, e.business_license_number, e.contact_email, e.address, e.representative_name,
                       COALESCE(ui.full_name, a.username) as intermediary_name,
                       ui.company_agency_name, ui.phone_number as intermediary_phone, ui.id_card_number,
                       a.email as intermediary_email
                FROM intermediary_authorizations ia
                JOIN user_employers e ON ia.employer_id = e.employer_id
                JOIN user_accounts a ON ia.intermediary_account_id = a.account_id
                LEFT JOIN user_intermediaries ui ON a.account_id = ui.account_id
                WHERE ia.auth_id = :id
            ");
            $stmt->execute([':id' => $authId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function create($employerId, $intermediaryAccountId, $data) {
        $db = Database::getConnection();
        if (!$db) return false;

        $authCode = 'UQ-' . date('Y') . '-JL-' . str_pad($employerId, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);
        $scope = trim($data['scope_of_work'] ?? 'Ủy quyền tuyển dụng lao động và tiếp nhận hồ sơ người lao động');
        $commission = trim($data['commission_rate'] ?? 'Theo thỏa thuận (10-15%)');
        $validFrom = $data['valid_from'] ?? date('Y-m-d');
        $validTo = $data['valid_to'] ?? date('Y-m-d', strtotime('+1 year'));
        $sigHash = hash('sha256', 'AUTH_DN_' . $employerId . '_' . $intermediaryAccountId . '_' . microtime());

        try {
            $stmt = $db->prepare("
                INSERT INTO intermediary_authorizations 
                (authorization_code, employer_id, intermediary_account_id, scope_of_work, commission_rate, dn_digital_signature_hash, admin_verification_status, valid_from, valid_to, created_at)
                VALUES 
                (:code, :eid, :iacc, :scope, :comm, :sig, 'pending', :vfrom, :vto, NOW())
            ");
            $stmt->execute([
                ':code'  => $authCode,
                ':eid'   => $employerId,
                ':iacc'  => $intermediaryAccountId,
                ':scope' => $scope,
                ':comm'  => $commission,
                ':sig'   => $sigHash,
                ':vfrom' => $validFrom,
                ':vto'   => $validTo
            ]);
            return $db->lastInsertId();
        } catch (PDOException $e) {
            error_log("[IntermediaryAuth::create Error] " . $e->getMessage());
            return false;
        }
    }

    public static function verifyByAdmin($authId, $status, $adminNote = null) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE intermediary_authorizations SET
                    admin_verification_status = :st,
                    admin_note = :note,
                    updated_at = NOW()
                WHERE auth_id = :id
            ");
            return $stmt->execute([
                ':st'   => $status,
                ':note' => $adminNote,
                ':id'   => $authId
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
