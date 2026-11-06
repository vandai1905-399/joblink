<?php
// models/CandidateMeeting.php - Quản lý lịch phỏng vấn candidate_meetings (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class CandidateMeeting {

    public static function getBySeeker($seekerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT m.*, p.job_post_id, p.job_title, p.workplace_address, p.workplace_map_url, 
                       e.employer_id, e.employer_name, e.avatar_url, e.phone_number as employer_phone, e.account_id as employer_account_id,
                       r.seeker_id, r.request_status, r.onboarding_details, r.response_deadline
                FROM candidate_meetings m
                JOIN job_requests r ON m.request_id = r.request_id
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_employers e ON p.employer_id = e.employer_id
                WHERE r.seeker_id = :sid
                ORDER BY m.meeting_date DESC, m.start_time ASC
            ");
            $stmt->execute([':sid' => $seekerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[CandidateMeeting::getBySeeker Error] " . $e->getMessage());
            return [];
        }
    }

    public static function getByEmployer($employerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT m.*, r.seeker_id, r.request_status, r.onboarding_details, r.response_deadline, p.job_post_id, p.job_title, p.workplace_address,
                       s.full_name as seeker_name, s.phone_number, s.avatar_url as seeker_avatar, s.account_id as seeker_account_id
                FROM candidate_meetings m
                JOIN job_requests r ON m.request_id = r.request_id
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                WHERE p.employer_id = :eid
                ORDER BY m.meeting_date DESC, m.start_time ASC
            ");
            $stmt->execute([':eid' => $employerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[CandidateMeeting::getByEmployer Error] " . $e->getMessage());
            return [];
        }
    }

    public static function create($data) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                INSERT INTO candidate_meetings (
                    request_id, created_by_account_id, meeting_date, start_time, end_time,
                    location_or_meeting_link, meeting_format, meeting_note, prep_guide, interview_range, meeting_status
                ) VALUES (
                    :request_id, :created_by_account_id, :meeting_date, :start_time, :end_time,
                    :location_or_meeting_link, :meeting_format, :meeting_note, :prep_guide, :interview_range, 'scheduled'
                )
            ");
            $ok = $stmt->execute([
                ':request_id' => $data['request_id'],
                ':created_by_account_id' => $data['created_by_account_id'],
                ':meeting_date' => $data['meeting_date'],
                ':start_time' => $data['start_time'],
                ':end_time' => $data['end_time'] ?? null,
                ':location_or_meeting_link' => $data['location_or_meeting_link'],
                ':meeting_format' => $data['meeting_format'] ?? 'offline',
                ':meeting_note' => $data['meeting_note'] ?? null,
                ':prep_guide' => $data['prep_guide'] ?? null,
                ':interview_range' => $data['interview_range'] ?? null
            ]);

            if ($ok && !empty($data['meeting_date'])) {
                // Tự động gán hạn phản hồi kết quả (3 ngày sau ngày phỏng vấn)
                try {
                    $deadline = date('Y-m-d 23:59:59', strtotime($data['meeting_date'] . ' +3 days'));
                    $stmtUp = $db->prepare("UPDATE job_requests SET response_deadline = :dl WHERE request_id = :rid");
                    $stmtUp->execute([':dl' => $deadline, ':rid' => $data['request_id']]);
                } catch (Throwable $t) {}
            }

            return $ok;
        } catch (PDOException $e) {
            error_log("[CandidateMeeting::create Error] " . $e->getMessage());
            return false;
        }
    }

    public static function updateStatus($meetingId, $status) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("UPDATE candidate_meetings SET meeting_status = :st WHERE meeting_id = :id");
            $ok = $stmt->execute([':st' => $status, ':id' => $meetingId]);

            // Nếu hoàn thành phỏng vấn, đảm bảo hạn phản hồi kết quả là 3 ngày sau
            if ($ok && $status === 'completed') {
                try {
                    $deadline = date('Y-m-d 23:59:59', strtotime('+3 days'));
                    $stmtUp = $db->prepare("
                        UPDATE job_requests r 
                        JOIN candidate_meetings m ON m.request_id = r.request_id 
                        SET r.response_deadline = :dl 
                        WHERE m.meeting_id = :mid
                    ");
                    $stmtUp->execute([':dl' => $deadline, ':mid' => $meetingId]);
                } catch (Throwable $t) {}
            }

            return $ok;
        } catch (PDOException $e) {
            error_log("[CandidateMeeting::updateStatus Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Người lao động đề xuất đổi lịch phỏng vấn trong khoảng ngày khả dụng
     */
    public static function requestReschedule($meetingId, $seekerAccountId, $proposedDate, $proposedTime, $reason) {
        $db = Database::getConnection();
        if (!$db) return ['success' => false, 'message' => 'Lỗi kết nối CSDL'];

        try {
            $stmt = $db->prepare("
                SELECT m.*, p.job_title, e.employer_name, e.account_id as employer_account_id,
                       s.full_name as seeker_name, s.account_id as seeker_account_id
                FROM candidate_meetings m
                JOIN job_requests r ON m.request_id = r.request_id
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_employers e ON p.employer_id = e.employer_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                WHERE m.meeting_id = :id
            ");
            $stmt->execute([':id' => $meetingId]);
            $meeting = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$meeting || intval($meeting['seeker_account_id']) !== intval($seekerAccountId)) {
                return ['success' => false, 'message' => 'Không tìm thấy cuộc hẹn hoặc không có quyền thực hiện.'];
            }

            // Cập nhật trạng thái cuộc hẹn thành rescheduled
            $stmtUp = $db->prepare("UPDATE candidate_meetings SET meeting_status = 'rescheduled', meeting_note = CONCAT(IFNULL(meeting_note,''), ' [Đề xuất đổi: ', :prop, ' - Lý do: ', :rsn, ']') WHERE meeting_id = :id");
            $stmtUp->execute([
                ':prop' => date('d/m/Y', strtotime($proposedDate)) . ' ' . $proposedTime,
                ':rsn' => $reason,
                ':id' => $meetingId
            ]);

            // Gửi tin nhắn đến NTD
            $msgTitle = "Đề xuất đổi lịch PV: " . $meeting['seeker_name'] . " - " . $meeting['job_title'];
            $msgContent = "Xin chào " . $meeting['employer_name'] . ",\n\nNgười lao động **" . $meeting['seeker_name'] . "** xin phép đề xuất đổi lịch phỏng vấn vị trí **" . $meeting['job_title'] . "**.\n\n"
                . "• Lịch hẹn cũ: " . date('d/m/Y', strtotime($meeting['meeting_date'])) . " lúc " . substr($meeting['start_time'], 0, 5) . "\n"
                . "• Ngày giờ mới đề xuất: **" . date('d/m/Y', strtotime($proposedDate)) . "** lúc **" . $proposedTime . "**\n"
                . "• Lý do: " . $reason . "\n\n"
                . "Quý công ty vui lòng vào mục Quản Lý Lịch Phỏng Vấn để xác nhận điều chỉnh thời gian mới cho người lao động nhé!";

            $stmtMsg = $db->prepare("
                INSERT INTO messages (sender_account_id, receiver_account_id, message_title, message_content, message_type, sent_at)
                VALUES (:snd, :rcv, :title, :cnt, 'normal', NOW())
            ");
            $stmtMsg->execute([
                ':snd' => $meeting['seeker_account_id'],
                ':rcv' => $meeting['employer_account_id'],
                ':title' => $msgTitle,
                ':cnt' => $msgContent
            ]);

            return ['success' => true, 'message' => 'Đã gửi đề xuất đổi lịch phỏng vấn đến Nhà tuyển dụng thành công!'];
        } catch (PDOException $e) {
            error_log("[CandidateMeeting::requestReschedule Error] " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    public static function delete($meetingId) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("DELETE FROM candidate_meetings WHERE meeting_id = :id");
            return $stmt->execute([':id' => $meetingId]);
        } catch (PDOException $e) {
            error_log("[CandidateMeeting::delete Error] " . $e->getMessage());
            return false;
        }
    }
}
