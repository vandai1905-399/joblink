<?php
// models/EmployerCriteria.php - Quản lý cấu hình tiêu chí tương thích riêng cho từng Nhà Tuyển Dụng

require_once __DIR__ . '/../../../config/Database.php';

class EmployerCriteria {

    /**
     * Danh sách tiêu chí mặc định ban đầu của hệ thống
     */
    public static function getDefaultCriteriaList() {
        return [
            [
                'key' => 'skills',
                'name' => 'Kỹ năng chuyên môn & Vị trí ứng tuyển',
                'desc' => 'Đối chiếu kỹ năng, chuyên môn và chức danh công việc',
                'is_custom' => 0,
                'order' => 1
            ],
            [
                'key' => 'experience',
                'name' => 'Số năm kinh nghiệm làm việc',
                'desc' => 'Đối soát số năm kinh nghiệm thực tế với yêu cầu của tin tuyển dụng',
                'is_custom' => 0,
                'order' => 2
            ],
            [
                'key' => 'age',
                'name' => 'Độ tuổi phù hợp yêu cầu',
                'desc' => 'Nằm trong khung độ tuổi tuyển dụng của doanh nghiệp',
                'is_custom' => 0,
                'order' => 3
            ],
            [
                'key' => 'gender',
                'name' => 'Giới tính theo yêu cầu',
                'desc' => 'Khớp đúng yêu cầu giới tính theo đặc thù công việc',
                'is_custom' => 0,
                'order' => 4
            ],
            [
                'key' => 'education',
                'name' => 'Trình độ học vấn & Bằng cấp',
                'desc' => 'Đạt chuẩn cấp học vấn tối thiểu doanh nghiệp đề ra',
                'is_custom' => 0,
                'order' => 5
            ],
            [
                'key' => 'location',
                'name' => 'Địa điểm làm việc & Khoảng cách gần',
                'desc' => 'Thuận tiện di chuyển, gần nơi làm việc của doanh nghiệp',
                'is_custom' => 0,
                'order' => 6
            ],
            [
                'key' => 'employment',
                'name' => 'Hình thức làm việc & Thu nhập kỳ vọng',
                'desc' => 'Khớp hình thức toàn thời gian / bán thời gian và mức lương',
                'is_custom' => 0,
                'order' => 7
            ],
            [
                'key' => 'shifts',
                'name' => 'Sẵn sàng làm việc xoay ca / Tăng ca',
                'desc' => 'Linh hoạt thời gian, sẵn sàng tăng ca khi có nhu cầu',
                'is_custom' => 0,
                'order' => 8
            ],
            [
                'key' => 'vehicle',
                'name' => 'Có phương tiện đi lại cá nhân (Xe máy)',
                'desc' => 'Chủ động phương tiện di chuyển phục vụ công việc',
                'is_custom' => 0,
                'order' => 9
            ],
            [
                'key' => 'communication',
                'name' => 'Kỹ năng giao tiếp & Thái độ làm việc',
                'desc' => 'Nhanh nhẹn, nhiệt tình, trung thực, có tinh thần trách nhiệm',
                'is_custom' => 0,
                'order' => 10
            ],
            [
                'key' => 'office_it',
                'name' => 'Tin học văn phòng cơ bản',
                'desc' => 'Biết sử dụng máy tính, Word, Excel cơ bản',
                'is_custom' => 0,
                'order' => 11
            ],
        ];
    }

    /**
     * Khởi tạo bộ tiêu chí mặc định riêng cho một nhà tuyển dụng
     */
    public static function seedDefaults($employerId) {
        $db = Database::getConnection();
        if (!$db || empty($employerId)) return false;

        try {
            $defaults = self::getDefaultCriteriaList();
            $stmt = $db->prepare("
                INSERT INTO employer_matching_criteria 
                (employer_id, criteria_key, criteria_name, criteria_desc, is_active, is_custom, display_order, created_at)
                VALUES (:eid, :key, :name, :desc, 1, :custom, :order, NOW())
            ");

            foreach ($defaults as $d) {
                $stmt->execute([
                    ':eid'    => $employerId,
                    ':key'    => $d['key'],
                    ':name'   => $d['name'],
                    ':desc'   => $d['desc'],
                    ':custom' => $d['is_custom'],
                    ':order'  => $d['order']
                ]);
            }
            return true;
        } catch (Throwable $e) {
            error_log("[EmployerCriteria::seedDefaults Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy toàn bộ danh sách tiêu chí của 1 nhà tuyển dụng
     */
    public static function getByEmployer($employerId) {
        $db = Database::getConnection();
        if (!$db || empty($employerId)) return [];

        try {
            $stmt = $db->prepare("
                SELECT * FROM employer_matching_criteria 
                WHERE employer_id = :eid 
                ORDER BY display_order ASC, criteria_id ASC
            ");
            $stmt->execute([':eid' => $employerId]);
            $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Nếu nhà tuyển dụng chưa có bộ tiêu chí riêng -> tự động khởi tạo bộ mặc định
            if (empty($list)) {
                self::seedDefaults($employerId);
                $stmt->execute([':eid' => $employerId]);
                $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $list;
        } catch (Throwable $e) {
            error_log("[EmployerCriteria::getByEmployer Error] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Bật / Tắt (chọn hoặc bỏ chọn lọc) 1 tiêu chí
     */
    public static function toggle($criteriaId, $employerId, $isActive) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE employer_matching_criteria 
                SET is_active = :act, updated_at = NOW() 
                WHERE criteria_id = :cid AND employer_id = :eid
            ");
            return $stmt->execute([
                ':act' => $isActive ? 1 : 0,
                ':cid' => $criteriaId,
                ':eid' => $employerId
            ]);
        } catch (Throwable $e) {
            error_log("[EmployerCriteria::toggle Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Thêm tiêu chí mới riêng cho nhà tuyển dụng
     */
    public static function add($employerId, $name, $desc = '') {
        $db = Database::getConnection();
        if (!$db || empty($name)) return false;

        try {
            // Lấy thứ tự lớn nhất hiện tại
            $stmtMax = $db->prepare("SELECT MAX(display_order) as max_ord FROM employer_matching_criteria WHERE employer_id = :eid");
            $stmtMax->execute([':eid' => $employerId]);
            $row = $stmtMax->fetch(PDO::FETCH_ASSOC);
            $nextOrder = intval($row['max_ord'] ?? 0) + 1;

            $stmt = $db->prepare("
                INSERT INTO employer_matching_criteria 
                (employer_id, criteria_key, criteria_name, criteria_desc, is_active, is_custom, display_order, created_at)
                VALUES (:eid, NULL, :name, :desc, 1, 1, :order, NOW())
            ");
            $ok = $stmt->execute([
                ':eid'   => $employerId,
                ':name'  => trim($name),
                ':desc'  => trim($desc),
                ':order' => $nextOrder
            ]);

            return $ok ? $db->lastInsertId() : false;
        } catch (Throwable $e) {
            error_log("[EmployerCriteria::add Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa 1 tiêu chí của nhà tuyển dụng
     */
    public static function delete($criteriaId, $employerId) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                DELETE FROM employer_matching_criteria 
                WHERE criteria_id = :cid AND employer_id = :eid
            ");
            return $stmt->execute([
                ':cid' => $criteriaId,
                ':eid' => $employerId
            ]);
        } catch (Throwable $e) {
            error_log("[EmployerCriteria::delete Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Khôi phục toàn bộ tiêu chí về mặc định
     */
    public static function resetDefaults($employerId) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmtDel = $db->prepare("DELETE FROM employer_matching_criteria WHERE employer_id = :eid");
            $stmtDel->execute([':eid' => $employerId]);
            return self::seedDefaults($employerId);
        } catch (Throwable $e) {
            error_log("[EmployerCriteria::resetDefaults Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy cấu hình các tiêu chí đang active và custom của nhà tuyển dụng để truyền vào AI Matching
     */
    public static function getActiveConfig($employerId) {
        $all = self::getByEmployer($employerId);
        $activeKeys = [];
        $inactiveKeys = [];
        $activeCustom = [];

        foreach ($all as $item) {
            $isActive = !empty($item['is_active']);
            $key = $item['criteria_key'] ?? '';
            $isCustom = !empty($item['is_custom']);

            if ($key) {
                if ($isActive) {
                    $activeKeys[] = $key;
                } else {
                    $inactiveKeys[] = $key;
                }
            } else if ($isCustom && $isActive) {
                $activeCustom[] = [
                    'id'   => $item['criteria_id'],
                    'name' => $item['criteria_name'],
                    'desc' => $item['criteria_desc'] ?? ''
                ];
            }
        }

        return [
            'active_keys'   => $activeKeys,
            'inactive_keys' => $inactiveKeys,
            'active_custom' => $activeCustom,
            'all'           => $all
        ];
    }
}
