<?php
// models/JobPost.php - Quản lý việc làm job_posts (100% Thuần CSDL MySQL)

require_once __DIR__ . '/../../../config/Database.php';

class JobPost {

    public static function allApproved() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->query("
                SELECT p.*, e.employer_name, e.avatar_url, e.address as employer_address, e.avg_rating,
                       e.digital_signature_status, ds.serial_number as digital_cert_serial, ds.issuer_ca as digital_cert_ca
                FROM job_posts p
                JOIN user_employers e ON p.employer_id = e.employer_id
                LEFT JOIN digital_signatures ds ON e.employer_id = ds.employer_id AND ds.status = 'active'
                WHERE p.post_status = 'approved'
                ORDER BY p.priority_level DESC, (e.digital_signature_status = 'verified') DESC, p.is_featured DESC, p.posted_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[JobPost::allApproved Error] " . $e->getMessage());
            return [];
        }
    }

    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->query("
                SELECT p.*, e.employer_name, e.avatar_url, e.digital_signature_status 
                FROM job_posts p
                JOIN user_employers e ON p.employer_id = e.employer_id
                ORDER BY p.job_post_id DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[JobPost::all Error] " . $e->getMessage());
            return [];
        }
    }

    public static function findById($id) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT p.*, e.employer_name, e.avatar_url, e.contact_email, e.phone_number as employer_phone, e.website_url, e.avg_rating, e.total_reviews,
                       e.digital_signature_status, ds.serial_number as digital_cert_serial, ds.issuer_ca as digital_cert_ca, ds.cert_fingerprint as digital_cert_fp
                FROM job_posts p
                JOIN user_employers e ON p.employer_id = e.employer_id
                LEFT JOIN digital_signatures ds ON e.employer_id = ds.employer_id AND ds.status = 'active'
                WHERE p.job_post_id = :id
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("[JobPost::findById Error] " . $e->getMessage());
            return null;
        }
    }

    public static function getByEmployerId($employerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT p.*, e.employer_name, e.avatar_url, e.address as employer_address, e.avg_rating, e.digital_signature_status 
                FROM job_posts p
                LEFT JOIN user_employers e ON p.employer_id = e.employer_id
                WHERE p.employer_id = :emp_id 
                ORDER BY p.job_post_id DESC
            ");
            $stmt->execute([':emp_id' => $employerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[JobPost::getByEmployerId Error] " . $e->getMessage());
            return [];
        }
    }

    public static function getByEmployer($employerId) {
        return self::getByEmployerId($employerId);
    }

    public static function filter($keyword = '', $location = '', $employmentType = '', $verifiedOnly = false) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $sql = "
                SELECT p.*, e.employer_name, e.avatar_url, e.address as employer_address, e.avg_rating,
                       e.digital_signature_status, ds.serial_number as digital_cert_serial, ds.issuer_ca as digital_cert_ca
                FROM job_posts p
                JOIN user_employers e ON p.employer_id = e.employer_id
                LEFT JOIN digital_signatures ds ON e.employer_id = ds.employer_id AND ds.status = 'active'
                WHERE p.post_status = 'approved'
            ";
            $params = [];

            if (!empty($keyword)) {
                $sql .= " AND (p.job_title LIKE :kw1 OR e.employer_name LIKE :kw2 OR p.job_requirements LIKE :kw3)";
                $params[':kw1'] = "%{$keyword}%";
                $params[':kw2'] = "%{$keyword}%";
                $params[':kw3'] = "%{$keyword}%";
            }
            if (!empty($location)) {
                $sql .= " AND (p.workplace_address LIKE :loc1 OR e.address LIKE :loc2)";
                $params[':loc1'] = "%{$location}%";
                $params[':loc2'] = "%{$location}%";
            }
            if (!empty($employmentType)) {
                $sql .= " AND p.employment_type = :type";
                $params[':type'] = $employmentType;
            }
            if ($verifiedOnly) {
                $sql .= " AND e.digital_signature_status = 'verified'";
            }

            $sql .= " ORDER BY p.priority_level DESC, (e.digital_signature_status = 'verified') DESC, p.is_featured DESC, p.posted_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[JobPost::filter Error] " . $e->getMessage());
            return [];
        }
    }

    public static function create($data) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                INSERT INTO job_posts (
                    employer_id, job_title, employment_type, work_shift, working_days, 
                    job_requirements, number_of_positions, application_deadline, 
                    salary_amount, workplace_address, workplace_map_url, additional_requirements, post_status, posted_at
                ) VALUES (
                    :employer_id, :job_title, :employment_type, :work_shift, :working_days, 
                    :job_requirements, :number_of_positions, :application_deadline, 
                    :salary_amount, :workplace_address, :workplace_map_url, :additional_requirements, :post_status, NOW()
                )
            ");
            return $stmt->execute([
                ':employer_id' => $data['employer_id'],
                ':job_title' => $data['job_title'],
                ':employment_type' => $data['employment_type'] ?? 'full_time',
                ':work_shift' => $data['work_shift'] ?? null,
                ':working_days' => $data['working_days'] ?? null,
                ':job_requirements' => $data['job_requirements'],
                ':number_of_positions' => $data['number_of_positions'] ?? 1,
                ':application_deadline' => $data['application_deadline'],
                ':salary_amount' => $data['salary_amount'],
                ':workplace_address' => $data['workplace_address'],
                ':workplace_map_url' => $data['workplace_map_url'] ?? null,
                ':additional_requirements' => $data['additional_requirements'] ?? null,
                ':post_status' => $data['post_status'] ?? 'pending'
            ]);
        } catch (PDOException $e) {
            error_log("[JobPost::create Error] " . $e->getMessage());
            return false;
        }
    }

    public static function update($id, $data) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE job_posts SET 
                    job_title = :job_title,
                    employment_type = :employment_type,
                    work_shift = :work_shift,
                    working_days = :working_days,
                    job_requirements = :job_requirements,
                    number_of_positions = :number_of_positions,
                    application_deadline = :application_deadline,
                    salary_amount = :salary_amount,
                    workplace_address = :workplace_address,
                    workplace_map_url = :workplace_map_url,
                    additional_requirements = :additional_requirements
                WHERE job_post_id = :id
            ");
            return $stmt->execute([
                ':id' => $id,
                ':job_title' => $data['job_title'],
                ':employment_type' => $data['employment_type'] ?? 'full_time',
                ':work_shift' => $data['work_shift'] ?? null,
                ':working_days' => $data['working_days'] ?? null,
                ':job_requirements' => $data['job_requirements'],
                ':number_of_positions' => $data['number_of_positions'] ?? 1,
                ':application_deadline' => $data['application_deadline'],
                ':salary_amount' => $data['salary_amount'],
                ':workplace_address' => $data['workplace_address'],
                ':workplace_map_url' => $data['workplace_map_url'] ?? null,
                ':additional_requirements' => $data['additional_requirements'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("[JobPost::update Error] " . $e->getMessage());
            return false;
        }
    }

    public static function updateStatus($id, $status) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("UPDATE job_posts SET post_status = :status WHERE job_post_id = :id");
            return $stmt->execute([':id' => $id, ':status' => $status]);
        } catch (PDOException $e) {
            error_log("[JobPost::updateStatus Error] " . $e->getMessage());
            return false;
        }
    }

    public static function delete($id) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("DELETE FROM job_posts WHERE job_post_id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("[JobPost::delete Error] " . $e->getMessage());
            return false;
        }
    }
}
