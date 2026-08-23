<?php
// models/UserEmployer.php - Quản lý thông tin doanh nghiệp user_employers (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class UserEmployer {

    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            return $db->query("
                SELECT e.*, 
                    ds.serial_number as digital_cert_serial, ds.issuer_ca as digital_cert_ca, ds.cert_fingerprint as digital_cert_fp,
                    COALESCE((SELECT ROUND(AVG(overall_rating),1) FROM reviews r WHERE r.employer_id = e.employer_id AND r.review_type='employer_review' AND r.approval_status='approved'), e.avg_rating, 5.0) as avg_rating,
                    COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.employer_id = e.employer_id AND r.review_type='employer_review' AND r.approval_status='approved'), e.total_reviews, 0) as total_reviews
                FROM user_employers e 
                LEFT JOIN digital_signatures ds ON e.employer_id = ds.employer_id AND ds.status = 'active'
                ORDER BY (e.digital_signature_status = 'verified') DESC, e.employer_id ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[UserEmployer::all Error] " . $e->getMessage());
            return [];
        }
    }

    public static function findById($id) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT e.*, 
                    ds.serial_number as digital_cert_serial, ds.issuer_ca as digital_cert_ca, ds.cert_fingerprint as digital_cert_fp, ds.valid_from as cert_valid_from, ds.valid_to as cert_valid_to,
                    COALESCE((SELECT ROUND(AVG(overall_rating),1) FROM reviews r WHERE r.employer_id = e.employer_id AND r.review_type='employer_review' AND r.approval_status='approved'), e.avg_rating, 5.0) as avg_rating,
                    COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.employer_id = e.employer_id AND r.review_type='employer_review' AND r.approval_status='approved'), e.total_reviews, 0) as total_reviews
                FROM user_employers e 
                LEFT JOIN digital_signatures ds ON e.employer_id = ds.employer_id AND ds.status = 'active'
                WHERE e.employer_id = :id
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("[UserEmployer::findById Error] " . $e->getMessage());
            return null;
        }
    }

    public static function findByAccountId($accountId) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT e.*, ds.serial_number as digital_cert_serial, ds.issuer_ca as digital_cert_ca, ds.cert_fingerprint as digital_cert_fp, ds.valid_from as cert_valid_from, ds.valid_to as cert_valid_to
                FROM user_employers e
                LEFT JOIN digital_signatures ds ON e.employer_id = ds.employer_id AND ds.status = 'active'
                WHERE e.account_id = :account_id
            ");
            $stmt->execute([':account_id' => $accountId]);
            $emp = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($emp) {
                return $emp;
            }

            // Tự động tạo hồ sơ mặc định nếu tài khoản tồn tại
            $stmtAcc = $db->prepare("SELECT * FROM user_accounts WHERE account_id = :acc_id");
            $stmtAcc->execute([':acc_id' => $accountId]);
            $acc = $stmtAcc->fetch(PDO::FETCH_ASSOC);
            if ($acc) {
                $db->prepare("
                    INSERT INTO user_employers (account_id, employer_name, contact_email, created_at)
                    VALUES (:acc_id, :name, :email, NOW())
                ")->execute([
                    ':acc_id' => $acc['account_id'],
                    ':name' => $acc['username'],
                    ':email' => $acc['email']
                ]);
                $stmt->execute([':account_id' => $accountId]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            return null;
        } catch (PDOException $e) {
            error_log("[UserEmployer::findByAccountId Error] " . $e->getMessage());
            return null;
        }
    }

    public static function createDefault($accountId, $name, $email) {
        $db = Database::getConnection();
        if (!$db) return false;
        try {
            $stmt = $db->prepare("
                INSERT INTO user_employers (account_id, employer_name, contact_email, created_at)
                VALUES (:acc_id, :name, :email, NOW())
            ");
            return $stmt->execute([
                ':acc_id' => $accountId,
                ':name' => $name,
                ':email' => $email
            ]);
        } catch (PDOException $e) {
            error_log("[UserEmployer::createDefault Error] " . $e->getMessage());
            return false;
        }
    }



    public static function updateProfile($employerId, $data) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE user_employers SET 
                    employer_name = :employer_name,
                    representative_name = :representative_name,
                    business_license_number = :business_license_number,
                    contact_email = :contact_email,
                    phone_number = :phone_number,
                    address = :address,
                    map_url = :map_url,
                    branches = :branches,
                    website_url = :website_url,
                    business_field = :business_field
                WHERE employer_id = :id
            ");
            return $stmt->execute([
                ':id' => $employerId,
                ':employer_name' => $data['employer_name'],
                ':representative_name' => $data['representative_name'] ?? null,
                ':business_license_number' => $data['business_license_number'] ?? null,
                ':contact_email' => $data['contact_email'] ?? null,
                ':phone_number' => $data['phone_number'] ?? null,
                ':address' => $data['address'] ?? null,
                ':map_url' => $data['map_url'] ?? null,
                ':branches' => $data['branches'] ?? null,
                ':website_url' => $data['website_url'] ?? null,
                ':business_field' => $data['business_field'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("[UserEmployer::updateProfile Error] " . $e->getMessage());
            return false;
        }
    }

    public static function update($employerId, $data) {
        return self::updateProfile($employerId, $data);
    }

    public static function updateVerification($employerId, $status) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("UPDATE user_employers SET verification_status = :status WHERE employer_id = :id");
            return $stmt->execute([':id' => $employerId, ':status' => $status]);
        } catch (PDOException $e) {
            error_log("[UserEmployer::updateVerification Error] " . $e->getMessage());
            return false;
        }
    }
}
