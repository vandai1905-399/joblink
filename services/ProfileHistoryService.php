<?php
// services/ProfileHistoryService.php - Ghi nhận & truy xuất lịch sử thay đổi thông tin (Audit Trail)
// Hỗ trợ cả Người lao động và Nhà tuyển dụng (tự cập nhật hoặc bị Admin cập nhật)

require_once __DIR__ . '/../config/Database.php';

class ProfileHistoryService {

    // Nhãn tiếng Việt cho các trường của Người lao động
    const SEEKER_FIELD_LABELS = [
        'full_name' => 'Họ và tên',
        'date_of_birth' => 'Ngày sinh',
        'gender' => 'Giới tính',
        'email' => 'Email liên hệ',
        'phone_number' => 'Số điện thoại',
        'address' => 'Địa chỉ cư trú',
        'avatar_url' => 'Ảnh đại diện',
        'id_card_number' => 'Số CCCD / Định danh',
        'skills' => 'Kỹ năng chuyên môn',
        'skill_proofs' => 'Minh chứng kỹ năng / Chứng chỉ',
        'experience_years' => 'Số năm kinh nghiệm',
        'experience_summary' => 'Tóm tắt kinh nghiệm làm việc',
        'work_history' => 'Lịch sử công tác / Quá trình làm việc',
        'desired_position' => 'Vị trí mong muốn',
        'expected_salary' => 'Mức lương kỳ vọng',
        'preferred_employment_type' => 'Hình thức làm việc ưu tiên',
        'preferred_location' => 'Khu vực làm việc ưu tiên',
        'bio' => 'Giới thiệu bản thân',
        'education_level' => 'Trình độ học vấn'
    ];

    // Nhãn tiếng Việt cho các trường của Doanh nghiệp / Nhà tuyển dụng
    const EMPLOYER_FIELD_LABELS = [
        'employer_name' => 'Tên doanh nghiệp / Công ty',
        'employer_type' => 'Loại hình doanh nghiệp',
        'representative_name' => 'Người đại diện pháp luật',
        'business_license_number' => 'Mã số thuế / GPKD',
        'contact_email' => 'Email liên hệ công ty',
        'phone_number' => 'Số điện thoại công ty',
        'address' => 'Địa chỉ trụ sở chính',
        'avatar_url' => 'Logo công ty',
        'map_url' => 'Bản đồ vị trí (Google Maps)',
        'branches' => 'Cơ sở & Chi nhánh',
        'work_shifts' => 'Khung ca làm việc',
        'website_url' => 'Địa chỉ Website',
        'business_field' => 'Lĩnh vực kinh doanh',
        'verification_status' => 'Trạng thái xác minh pháp lý',
        'digital_signature_status' => 'Trạng thái chữ ký số'
    ];

    /**
     * Ghi nhận các trường thông tin bị thay đổi giữa dữ liệu cũ và dữ liệu mới
     *
     * @param string $targetType 'SEEKER' | 'EMPLOYER'
     * @param int $targetId seeker_id hoặc employer_id
     * @param int $targetAccountId account_id của chủ hồ sơ
     * @param array $oldData Dữ liệu trước khi sửa
     * @param array $newData Dữ liệu mới gửi lên
     * @param array|null $actor Thông tin người thực hiện [account_id, role_code, display_name] (mặc định lấy từ $_SESSION['user'])
     * @return int Số lượng trường đã ghi nhận thay đổi
     */
    public static function logChanges($targetType, $targetId, $targetAccountId, array $oldData, array $newData, $actor = null) {
        $db = Database::getConnection();
        if (!$db) return 0;

        if (!$actor && !empty($_SESSION['user'])) {
            $actor = $_SESSION['user'];
        }

        $actorAccountId = intval($actor['account_id'] ?? 0);
        $actorRole = $actor['role_code'] ?? 'GUEST';
        $actorName = $actor['display_name'] ?? ($actor['username'] ?? 'Người dùng');

        // Xác định loại thay đổi: Bản thân tự đổi hay Quản trị viên đổi
        $isAdminRole = in_array($actorRole, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        if ($isAdminRole && $actorAccountId !== intval($targetAccountId)) {
            $changeType = 'ADMIN_UPDATE';
            $changedByName = "Quản trị viên: {$actorName} ({$actorRole})";
        } elseif ($actorAccountId === intval($targetAccountId)) {
            $changeType = 'SELF_UPDATE';
            $changedByName = "Bản thân: {$actorName}";
        } else {
            $changeType = 'ADMIN_UPDATE';
            $changedByName = "{$actorName} ({$actorRole})";
        }

        $labels = ($targetType === 'SEEKER') ? self::SEEKER_FIELD_LABELS : self::EMPLOYER_FIELD_LABELS;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $changesCount = 0;

        foreach ($newData as $field => $newVal) {
            // Chỉ theo dõi các trường có trong danh sách định nghĩa
            if (!isset($labels[$field])) {
                continue;
            }

            $oldVal = $oldData[$field] ?? null;

            // Chuẩn hóa so sánh
            if (!self::isValueChanged($oldVal, $newVal, $field)) {
                continue;
            }

            // Định dạng chuỗi hiển thị dễ đọc cho người dùng
            $formattedOld = self::formatValueForDisplay($field, $oldVal);
            $formattedNew = self::formatValueForDisplay($field, $newVal);
            $fieldLabel = $labels[$field];

            $summary = "Cập nhật {$fieldLabel} từ '{$formattedOld}' thành '{$formattedNew}'";

            try {
                $stmt = $db->prepare("
                    INSERT INTO profile_change_logs (
                        target_type, target_id, target_account_id,
                        changed_by_account_id, changed_by_role, changed_by_name,
                        change_type, field_name, field_label,
                        old_value, new_value, change_summary,
                        ip_address, created_at
                    ) VALUES (
                        :ttype, :tid, :taid,
                        :caid, :crole, :cname,
                        :ctype, :fname, :flabel,
                        :oldv, :newv, :sum,
                        :ip, NOW()
                    )
                ");

                $stmt->execute([
                    ':ttype' => $targetType,
                    ':tid' => $targetId,
                    ':taid' => $targetAccountId,
                    ':caid' => $actorAccountId,
                    ':crole' => $actorRole,
                    ':cname' => $changedByName,
                    ':ctype' => $changeType,
                    ':fname' => $field,
                    ':flabel' => $fieldLabel,
                    ':oldv' => $formattedOld,
                    ':newv' => $formattedNew,
                    ':sum' => $summary,
                    ':ip' => $ip
                ]);

                $changesCount++;
            } catch (PDOException $e) {
                error_log("[ProfileHistoryService::logChanges Error] " . $e->getMessage());
            }
        }

        return $changesCount;
    }

    /**
     * So sánh 2 giá trị xem có thực sự thay đổi không
     */
    private static function isValueChanged($old, $new, $field) {
        // Chuẩn hóa null và chuỗi rỗng
        $normOld = is_null($old) ? '' : trim((string)$old);
        $normNew = is_null($new) ? '' : trim((string)$new);

        if ($normOld === $normNew) {
            return false;
        }

        // Với số tiền hoặc số năm
        if (in_array($field, ['expected_salary', 'experience_years'])) {
            $fOld = floatval($old ?? 0);
            $fNew = floatval($new ?? 0);
            return abs($fOld - $fNew) > 0.0001;
        }

        // Với JSON phức tạp (branches, work_shifts, work_history, skill_proofs)
        if (in_array($field, ['branches', 'work_shifts', 'work_history', 'skill_proofs'])) {
            $decOld = json_decode((string)$old, true);
            $decNew = json_decode((string)$new, true);
            if (is_array($decOld) && is_array($decNew)) {
                return json_encode($decOld) !== json_encode($decNew);
            }
        }

        return true;
    }

    /**
     * Định dạng giá trị hiển thị thân thiện với người đọc
     */
    private static function formatValueForDisplay($field, $val) {
        if ($val === null || $val === '') {
            return '(Trống)';
        }

        if ($field === 'expected_salary' && is_numeric($val) && $val > 0) {
            return number_format((float)$val, 0, ',', '.') . ' VNĐ';
        }

        if ($field === 'date_of_birth' && !empty($val) && $val !== '0000-00-00') {
            $ts = strtotime($val);
            return $ts ? date('d/m/Y', $ts) : $val;
        }

        if ($field === 'preferred_employment_type') {
            $map = [
                'full_time' => 'Toàn thời gian (Full-time)',
                'part_time' => 'Bán thời gian (Part-time)',
                'contract' => 'Hợp đồng ngắn hạn',
                'seasonal' => 'Thời vụ / Theo ca'
            ];
            return $map[$val] ?? $val;
        }

        if ($field === 'verification_status') {
            $map = ['pending' => 'Chờ thẩm định', 'verified' => 'Đã xác thực hợp pháp', 'failed' => 'Thẩm định không đạt'];
            return $map[$val] ?? $val;
        }

        if ($field === 'digital_signature_status') {
            $map = ['verified' => 'Đã tích hợp Chữ ký số', 'pending' => 'Chờ duyệt chữ ký số', 'expired' => 'Hết hạn chữ ký số', 'unregistered' => 'Chưa đăng ký chữ ký số'];
            return $map[$val] ?? $val;
        }

        // Xử lý JSON mảng ca làm việc
        if ($field === 'work_shifts') {
            $decoded = json_decode((string)$val, true);
            if (is_array($decoded)) {
                $shiftsStr = [];
                foreach ($decoded as $s) {
                    $name = $s['name'] ?? 'Ca';
                    $time = $s['time'] ?? '';
                    $shiftsStr[] = "{$name} ({$time})";
                }
                return !empty($shiftsStr) ? implode('; ', $shiftsStr) : '(Không có ca làm)';
            }
        }

        // Xử lý JSON mảng chi nhánh
        if ($field === 'branches') {
            $decoded = json_decode((string)$val, true);
            if (is_array($decoded)) {
                $branchesStr = [];
                foreach ($decoded as $b) {
                    $name = $b['name'] ?? 'Cơ sở';
                    $addr = $b['address'] ?? '';
                    $branchesStr[] = "{$name}: {$addr}";
                }
                return !empty($branchesStr) ? implode(' | ', $branchesStr) : '(Không có chi nhánh)';
            }
        }

        // Xử lý JSON lịch sử làm việc
        if ($field === 'work_history') {
            $decoded = json_decode((string)$val, true);
            if (is_array($decoded)) {
                $workStr = [];
                foreach ($decoded as $w) {
                    $c = $w['company_name'] ?? '';
                    $p = $w['position'] ?? '';
                    if ($c || $p) $workStr[] = "{$p} tại {$c}";
                }
                return !empty($workStr) ? implode('; ', $workStr) : '(Chưa có lịch sử làm việc)';
            }
        }

        // Xử lý JSON minh chứng kỹ năng
        if ($field === 'skill_proofs') {
            $decoded = json_decode((string)$val, true);
            if (is_array($decoded)) {
                $proofStr = [];
                foreach ($decoded as $p) {
                    $n = $p['name'] ?? '';
                    $iss = $p['issuer'] ?? '';
                    if ($n) $proofStr[] = "{$n}" . ($iss ? " ({$iss})" : "");
                }
                return !empty($proofStr) ? implode('; ', $proofStr) : '(Chưa có chứng chỉ)';
            }
        }

        return (string)$val;
    }

    /**
     * Lấy danh sách lịch sử thay đổi của một Người lao động hoặc Doanh nghiệp cụ thể
     */
    public static function getHistoryForTarget($targetType, $targetId, $limit = 50) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT * FROM profile_change_logs
                WHERE target_type = :ttype AND target_id = :tid
                ORDER BY created_at DESC, log_id DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':ttype', $targetType, PDO::PARAM_STR);
            $stmt->bindValue(':tid', intval($targetId), PDO::PARAM_INT);
            $stmt->bindValue(':lim', intval($limit), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[ProfileHistoryService::getHistoryForTarget Error] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách lịch sử thay đổi theo account_id của người dùng
     */
    public static function getHistoryForAccount($accountId, $limit = 50) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT * FROM profile_change_logs
                WHERE target_account_id = :aid
                ORDER BY created_at DESC, log_id DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':aid', intval($accountId), PDO::PARAM_INT);
            $stmt->bindValue(':lim', intval($limit), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[ProfileHistoryService::getHistoryForAccount Error] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách toàn bộ lịch sử hệ thống (Dành cho Admin) có hỗ trợ phân trang & lọc
     */
    public static function getAllHistory($page = 1, $limit = 20, $filters = []) {
        $db = Database::getConnection();
        if (!$db) return ['data' => [], 'total' => 0, 'pages' => 1];

        $page = max(1, intval($page));
        $limit = max(1, min(100, intval($limit)));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if (!empty($filters['target_type'])) {
            $where[] = "l.target_type = :target_type";
            $params[':target_type'] = $filters['target_type'];
        }

        if (!empty($filters['change_type'])) {
            $where[] = "l.change_type = :change_type";
            $params[':change_type'] = $filters['change_type'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $where[] = "(l.changed_by_name LIKE :s1 OR l.field_label LIKE :s2 OR l.change_summary LIKE :s3 OR a.username LIKE :s4)";
            $params[':s1'] = $search;
            $params[':s2'] = $search;
            $params[':s3'] = $search;
            $params[':s4'] = $search;
        }

        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        try {
            // Đếm tổng số bản ghi
            $countStmt = $db->prepare("
                SELECT COUNT(*) FROM profile_change_logs l
                LEFT JOIN user_accounts a ON l.target_account_id = a.account_id
                {$whereSql}
            ");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // Lấy dữ liệu phân trang kèm thông tin chủ tài khoản
            $sql = "
                SELECT l.*, a.username as target_username, a.email as target_email,
                       CASE 
                           WHEN l.target_type = 'SEEKER' THEN (SELECT full_name FROM user_seekers WHERE seeker_id = l.target_id)
                           WHEN l.target_type = 'EMPLOYER' THEN (SELECT employer_name FROM user_employers WHERE employer_id = l.target_id)
                           ELSE ''
                       END as target_display_name
                FROM profile_change_logs l
                LEFT JOIN user_accounts a ON l.target_account_id = a.account_id
                {$whereSql}
                ORDER BY l.created_at DESC, l.log_id DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data' => $data,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page,
                'limit' => $limit
            ];
        } catch (PDOException $e) {
            error_log("[ProfileHistoryService::getAllHistory Error] " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'pages' => 1];
        }
    }

    /**
     * Helper render bảng lịch sử thay đổi dùng chung cho giao diện Người lao động / Doanh nghiệp
     */
    public static function renderHistoryTableHtml($logs, $emptyMessage = 'Chưa có lịch sử thay đổi thông tin nào.') {
        if (empty($logs)) {
            return "
                <div style='text-align: center; padding: 2.5rem 1rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; color: #64748b;'>
                    <div style='font-size: 2rem; margin-bottom: 0.5rem;'>📋</div>
                    <div style='font-weight: 700; color: #334155; font-size: 1rem;'>{$emptyMessage}</div>
                    <div style='font-size: 0.85rem; margin-top: 0.25rem;'>Mọi cập nhật thông tin cá nhân hoặc do Quản trị viên thay đổi sẽ được lưu vết chi tiết tại đây.</div>
                </div>
            ";
        }

        $html = "
        <div class='table-responsive' style='overflow-x: auto;'>
            <table class='table' style='width: 100%; border-collapse: collapse; font-size: 0.88rem;'>
                <thead>
                    <tr style='background: #f1f5f9; border-bottom: 2px solid #cbd5e1; color: #334155; text-align: left;'>
                        <th style='padding: 0.75rem 1rem; width: 140px;'>Thời Gian</th>
                        <th style='padding: 0.75rem 1rem; width: 160px;'>Trường Thay Đổi</th>
                        <th style='padding: 0.75rem 1rem;'>Nội Dung Cũ</th>
                        <th style='padding: 0.75rem 1rem;'>Nội Dung Mới</th>
                        <th style='padding: 0.75rem 1rem; width: 190px;'>Người Thực Hiện</th>
                    </tr>
                </thead>
                <tbody>
        ";

        foreach ($logs as $log) {
            $dateFormatted = date('d/m/Y H:i:s', strtotime($log['created_at']));
            $fieldLabel = htmlspecialchars($log['field_label'] ?? $log['field_name']);
            $oldVal = htmlspecialchars($log['old_value'] ?? '');
            $newVal = htmlspecialchars($log['new_value'] ?? '');
            $actor = htmlspecialchars($log['changed_by_name'] ?? '');
            $isSelf = ($log['change_type'] === 'SELF_UPDATE');

            $badgeActor = $isSelf
                ? "<span style='display: inline-flex; align-items: center; gap: 4px; padding: 0.2rem 0.5rem; background: #e0f2fe; color: #0369a1; border-radius: 6px; font-weight: 700; font-size: 0.78rem;'>👤 {$actor}</span>"
                : "<span style='display: inline-flex; align-items: center; gap: 4px; padding: 0.2rem 0.5rem; background: #fef3c7; color: #92400e; border-radius: 6px; font-weight: 700; font-size: 0.78rem;'>🛡️ {$actor}</span>";

            $html .= "
                <tr style='border-bottom: 1px solid #e2e8f0; transition: background 0.15s;' onmouseover=\"this.style.background='#f8fafc'\" onmouseout=\"this.style.background='transparent'\">
                    <td style='padding: 0.75rem 1rem; white-space: nowrap; font-weight: 600; color: #475569;'>
                        {$dateFormatted}
                    </td>
                    <td style='padding: 0.75rem 1rem;'>
                        <span style='font-weight: 700; color: #0f172a;'>{$fieldLabel}</span>
                    </td>
                    <td style='padding: 0.75rem 1rem; color: #dc2626; word-break: break-word;'>
                        <span style='background: #fee2e2; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.82rem;'>{$oldVal}</span>
                    </td>
                    <td style='padding: 0.75rem 1rem; color: #16a34a; word-break: break-word;'>
                        <span style='background: #dcfce7; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.82rem; font-weight: 600;'>{$newVal}</span>
                    </td>
                    <td style='padding: 0.75rem 1rem;'>
                        {$badgeActor}
                    </td>
                </tr>
            ";
        }

        $html .= "
                </tbody>
            </table>
        </div>
        ";

        return $html;
    }
}
