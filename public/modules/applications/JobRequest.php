<?php
// models/JobRequest.php - Quản lý hồ sơ ứng tuyển job_requests (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../services/MailService.php';

class JobRequest {

    public static function getBySeeker($seekerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT r.*, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, e.employer_name, e.avatar_url 
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_employers e ON p.employer_id = e.employer_id
                WHERE r.seeker_id = :sid
                ORDER BY r.applied_at DESC
            ");
            $stmt->execute([':sid' => $seekerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[JobRequest::getBySeeker Error] " . $e->getMessage());
            return [];
        }
    }

    public static function getByEmployer($employerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT r.*, s.full_name as seeker_name, s.email, s.phone_number, s.avatar_url as seeker_avatar, p.job_title 
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                WHERE p.employer_id = :eid
                ORDER BY r.applied_at DESC
            ");
            $stmt->execute([':eid' => $employerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[JobRequest::getByEmployer Error] " . $e->getMessage());
            return [];
        }
    }

    public static function apply($seekerId, $jobPostId, $availableWorkDates = null) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            // Kiểm tra tính ràng buộc & Điểm uy tín của người lao động
            $stmtSeeker = $db->prepare("SELECT full_name, trust_score, agreement_signed FROM user_seekers WHERE seeker_id = :sid");
            $stmtSeeker->execute([':sid' => $seekerId]);
            $seeker = $stmtSeeker->fetch(PDO::FETCH_ASSOC);

            if ($seeker && intval($seeker['trust_score']) < 60) {
                error_log("[JobRequest] Seeker $seekerId trust score is below 60");
                return false;
            }

            $stmt = $db->prepare("
                INSERT INTO job_requests (seeker_id, job_post_id, available_work_dates, request_status, applied_at)
                VALUES (:sid, :jid, :dates, 'pending', NOW())
            ");
            $result = $stmt->execute([
                ':sid' => $seekerId, 
                ':jid' => $jobPostId,
                ':dates' => $availableWorkDates
            ]);

            if ($result) {
                // Lấy thông tin tin tuyển dụng & nhà tuyển dụng để gửi thông báo Gmail
                $stmtJob = $db->prepare("SELECT job_title, employer_id FROM job_posts WHERE job_post_id = :jid");
                $stmtJob->execute([':jid' => $jobPostId]);
                $job = $stmtJob->fetch(PDO::FETCH_ASSOC);

                if ($job) {
                    try {
                        MailService::notifyNewApplication($job['employer_id'], $seeker['full_name'] ?? 'Người lao động JobLink', $job['job_title']);
                    } catch (Throwable $e) {
                        error_log("[JobRequest Mail Warning] " . $e->getMessage());
                    }
                }
            }

            return $result;
        } catch (PDOException $e) {
            error_log("[JobRequest::apply Error] " . $e->getMessage());
            return false;
        }
    }

    public static function updateStatus($requestId, $status, $accountId, $rejectionReason = null) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE job_requests SET 
                    request_status = :st, 
                    processed_by_account_id = :acc, 
                    rejection_reason = :reason,
                    status_updated_at = NOW()
                WHERE request_id = :id
            ");
            return $stmt->execute([
                ':st' => $status,
                ':acc' => $accountId,
                ':reason' => $rejectionReason,
                ':id' => $requestId
            ]);
        } catch (PDOException $e) {
            error_log("[JobRequest::updateStatus Error] " . $e->getMessage());
            return false;
        }
    }

    public static function getByJobPost($jobPostId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT r.*, s.full_name as seeker_name, s.email, s.phone_number, s.avatar_url as seeker_avatar, 
                       s.gender, s.date_of_birth, s.experience_years as years_of_experience, s.skills, s.trust_score, s.address, p.job_title,
                       s.account_id as seeker_account_id
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                WHERE r.job_post_id = :jid
                ORDER BY r.applied_at DESC
            ");
            $stmt->execute([':jid' => $jobPostId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[JobRequest::getByJobPost Error] " . $e->getMessage());
            return [];
        }
    }

    public static function inviteCandidate($employerId, $seekerId, $jobPostId, $note = '') {
        $db = Database::getConnection();
        if (!$db) return ['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu'];

        try {
            // Kiểm tra xem đã có request giữa seeker và job post này chưa
            $stmtCheck = $db->prepare("SELECT request_id, request_status FROM job_requests WHERE seeker_id = :sid AND job_post_id = :jid");
            $stmtCheck->execute([':sid' => $seekerId, ':jid' => $jobPostId]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['request_status'] === 'invited') {
                    return ['success' => false, 'message' => 'Bạn đã gửi lời mời cho ứng viên này rồi!'];
                } elseif (in_array($existing['request_status'], ['accepted', 'invitation_accepted'])) {
                    return ['success' => false, 'message' => 'Ứng viên này đã được duyệt nhận!'];
                } else {
                    // Cập nhật lại thành invited
                    $stmtUp = $db->prepare("UPDATE job_requests SET request_status = 'invited', available_work_dates = :note, applied_at = NOW() WHERE request_id = :id");
                    $stmtUp->execute([':note' => $note, ':id' => $existing['request_id']]);
                }
            } else {
                $stmtIns = $db->prepare("
                    INSERT INTO job_requests (seeker_id, job_post_id, available_work_dates, request_status, applied_at)
                    VALUES (:sid, :jid, :note, 'invited', NOW())
                ");
                $stmtIns->execute([':sid' => $seekerId, ':jid' => $jobPostId, ':note' => $note]);
            }

            // Gửi tin nhắn thông báo tự động đến ứng viên
            $stmtSeeker = $db->prepare("SELECT account_id, full_name FROM user_seekers WHERE seeker_id = :sid");
            $stmtSeeker->execute([':sid' => $seekerId]);
            $seeker = $stmtSeeker->fetch(PDO::FETCH_ASSOC);

            $stmtEmp = $db->prepare("SELECT e.employer_name, e.account_id, p.job_title FROM user_employers e JOIN job_posts p ON p.employer_id = e.employer_id WHERE p.job_post_id = :jid");
            $stmtEmp->execute([':jid' => $jobPostId]);
            $empInfo = $stmtEmp->fetch(PDO::FETCH_ASSOC);

            if ($seeker && $empInfo) {
                $msgTitle = "🎯 Lời mời nhận việc: " . $empInfo['job_title'];
                $msgContent = "Xin chào " . $seeker['full_name'] . ",\n\nDoanh nghiệp **" . $empInfo['employer_name'] . "** đã xem qua hồ sơ năng lực của bạn và đánh giá rất cao. Doanh nghiệp trân trọng gửi lời mời bạn tham gia ứng tuyển/phỏng vấn cho vị trí **" . $empInfo['job_title'] . "**.\n\n" . (!empty($note) ? "Lời nhắn từ NTD: *" . $note . "*\n\n" : "") . "Vui lòng vào mục 'Hồ Sơ Đã Ứng Tuyển' trên thanh menu để bấm Đồng ý nhận lời mời và chốt lịch phỏng vấn nhé!";
                
                $stmtMsg = $db->prepare("
                    INSERT INTO messages (sender_account_id, receiver_account_id, message_title, message_content, message_type, sent_at)
                    VALUES (:snd, :rcv, :title, :cnt, 'invitation', NOW())
                ");
                $stmtMsg->execute([
                    ':snd' => $empInfo['account_id'],
                    ':rcv' => $seeker['account_id'],
                    ':title' => $msgTitle,
                    ':cnt' => $msgContent
                ]);
            }

            return ['success' => true, 'message' => 'Đã gửi lời mời nhận việc đến người lao động thành công!'];
        } catch (PDOException $e) {
            error_log("[JobRequest::inviteCandidate Error] " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    public static function respondInvitation($requestId, $action, $seekerAccountId) {
        $db = Database::getConnection();
        if (!$db) return ['success' => false, 'message' => 'Lỗi kết nối CSDL'];

        try {
            $stmt = $db->prepare("
                SELECT r.*, s.account_id as seeker_account_id, s.full_name as seeker_name, 
                       p.job_title, e.account_id as employer_account_id, e.employer_name 
                FROM job_requests r
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_employers e ON p.employer_id = e.employer_id
                WHERE r.request_id = :id
            ");
            $stmt->execute([':id' => $requestId]);
            $req = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$req || intval($req['seeker_account_id']) !== intval($seekerAccountId)) {
                return ['success' => false, 'message' => 'Không tìm thấy lời mời hoặc bạn không có quyền thực hiện.'];
            }

            $newStatus = ($action === 'accept') ? 'invitation_accepted' : 'invitation_rejected';
            $stmtUp = $db->prepare("UPDATE job_requests SET request_status = :st, status_updated_at = NOW() WHERE request_id = :id");
            $stmtUp->execute([':st' => $newStatus, ':id' => $requestId]);

            // Gửi tin nhắn phản hồi tới Nhà Tuyển Dụng
            $statusText = ($action === 'accept') ? 'đã ĐỒNG Ý nhận lời mời' : 'đã từ chối lời mời';
            $msgTitle = ($action === 'accept') ? "✨ Ứng viên đã đồng ý lời mời: " . $req['job_title'] : "Thông báo: Ứng viên từ chối lời mời";
            $msgContent = "Xin chào " . $req['employer_name'] . ",\n\nỨng viên **" . $req['seeker_name'] . "** " . $statusText . " cho vị trí **" . $req['job_title'] . "**.\n\n" . ($action === 'accept' ? "Hai bên có thể bắt đầu trao đổi tin nhắn riêng và sử dụng tính năng **✨ AI Gợi Ý Khung Giờ Rảnh** để chốt lịch phỏng vấn ngay!" : "");

            $stmtMsg = $db->prepare("
                INSERT INTO messages (sender_account_id, receiver_account_id, message_title, message_content, message_type, sent_at)
                VALUES (:snd, :rcv, :title, :cnt, 'normal', NOW())
            ");
            $stmtMsg->execute([
                ':snd' => $req['seeker_account_id'],
                ':rcv' => $req['employer_account_id'],
                ':title' => $msgTitle,
                ':cnt' => $msgContent
            ]);

            return [
                'success' => true, 
                'message' => ($action === 'accept') ? 'Bạn đã đồng ý nhận lời mời! Giờ bạn có thể nhắn tin trực tiếp với NTD.' : 'Bạn đã từ chối lời mời này.'
            ];
        } catch (PDOException $e) {
            error_log("[JobRequest::respondInvitation Error] " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    public static function delete($requestId) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("DELETE FROM job_requests WHERE request_id = :id");
            return $stmt->execute([':id' => $requestId]);
        } catch (PDOException $e) {
            error_log("[JobRequest::delete Error] " . $e->getMessage());
            return false;
        }
    }
}
