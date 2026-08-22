<?php
// models/UserSeeker.php - Quản lý thông tin người lao động user_seekers (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class UserSeeker {

    public static function findByAccountId($accountId) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM user_seekers WHERE account_id = :acc_id");
            $stmt->execute([':acc_id' => $accountId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("[UserSeeker::findByAccountId Error] " . $e->getMessage());
            return null;
        }
    }

    public static function findById($seekerId) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM user_seekers WHERE seeker_id = :id");
            $stmt->execute([':id' => $seekerId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("[UserSeeker::findById Error] " . $e->getMessage());
            return null;
        }
    }

    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            return $db->query("SELECT * FROM user_seekers ORDER BY seeker_id ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[UserSeeker::all Error] " . $e->getMessage());
            return [];
        }
    }

    public static function update($seekerId, $data) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE user_seekers SET 
                    full_name = :full_name,
                    date_of_birth = :dob,
                    gender = :gender,
                    phone_number = :phone,
                    address = :address,
                    skills = :skills,
                    experience_years = :experience_years,
                    experience_summary = :experience_summary,
                    desired_position = :desired_position,
                    expected_salary = :expected_salary,
                    preferred_employment_type = :preferred_employment_type,
                    preferred_location = :preferred_location,
                    bio = :bio,
                    education_level = :education_level
                WHERE seeker_id = :id
            ");
            return $stmt->execute([
                ':full_name' => $data['full_name'],
                ':dob' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                ':gender' => $data['gender'] ?? 'Khác',
                ':phone' => $data['phone_number'],
                ':address' => $data['address'] ?? null,
                ':skills' => $data['skills'] ?? null,
                ':experience_years' => isset($data['experience_years']) ? intval($data['experience_years']) : 0,
                ':experience_summary' => $data['experience_summary'] ?? null,
                ':desired_position' => $data['desired_position'] ?? null,
                ':expected_salary' => !empty($data['expected_salary']) ? floatval($data['expected_salary']) : null,
                ':preferred_employment_type' => $data['preferred_employment_type'] ?? 'full_time',
                ':preferred_location' => $data['preferred_location'] ?? null,
                ':bio' => $data['bio'] ?? null,
                ':education_level' => $data['education_level'] ?? null,
                ':id' => $seekerId
            ]);
        } catch (PDOException $e) {
            error_log("[UserSeeker::update Error] " . $e->getMessage());
            return false;
        }
    }

    public static function updateTrustScore($seekerId, $delta, $reason = '') {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE user_seekers SET 
                    trust_score = GREATEST(0, LEAST(100, trust_score + :delta)),
                    warning_count = CASE WHEN :delta < 0 THEN warning_count + 1 ELSE warning_count END
                WHERE seeker_id = :id
            ");
            return $stmt->execute([':delta' => $delta, ':id' => $seekerId]);
        } catch (PDOException $e) {
            error_log("[UserSeeker::updateTrustScore Error] " . $e->getMessage());
            return false;
        }
    }

    public static function signAgreement($seekerId) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE user_seekers SET 
                    agreement_signed = 1,
                    agreement_signed_at = NOW()
                WHERE seeker_id = :id
            ");
            return $stmt->execute([':id' => $seekerId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function updateIdCard($seekerId, $idCardNumber) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE user_seekers SET 
                    id_card_number = :idcard
                WHERE seeker_id = :id
            ");
            return $stmt->execute([':idcard' => $idCardNumber, ':id' => $seekerId]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
