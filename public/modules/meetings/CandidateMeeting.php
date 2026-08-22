<?php
// models/CandidateMeeting.php - Quản lý lịch phỏng vấn candidate_meetings (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';

class CandidateMeeting {

    public static function getBySeeker($seekerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT m.*, p.job_title, e.employer_name, e.avatar_url, r.seeker_id 
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
                SELECT m.*, r.seeker_id, p.job_title, s.full_name as seeker_name, s.phone_number, s.avatar_url as seeker_avatar 
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
                    location_or_meeting_link, meeting_format, meeting_note, meeting_status
                ) VALUES (
                    :request_id, :created_by_account_id, :meeting_date, :start_time, :end_time,
                    :location_or_meeting_link, :meeting_format, :meeting_note, 'scheduled'
                )
            ");
            return $stmt->execute([
                ':request_id' => $data['request_id'],
                ':created_by_account_id' => $data['created_by_account_id'],
                ':meeting_date' => $data['meeting_date'],
                ':start_time' => $data['start_time'],
                ':end_time' => $data['end_time'] ?? null,
                ':location_or_meeting_link' => $data['location_or_meeting_link'],
                ':meeting_format' => $data['meeting_format'] ?? 'offline',
                ':meeting_note' => $data['meeting_note'] ?? null
            ]);
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
            return $stmt->execute([':st' => $status, ':id' => $meetingId]);
        } catch (PDOException $e) {
            error_log("[CandidateMeeting::updateStatus Error] " . $e->getMessage());
            return false;
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
