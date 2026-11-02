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
                SELECT r.*, p.job_title, p.salary_amount, p.employment_type, p.workplace_address, 
                       e.employer_name, e.avatar_url, e.phone_number as employer_phone, e.contact_email as employer_email, e.account_id as employer_account_id,
                       m.meeting_id, m.meeting_date, m.start_time, m.end_time, m.meeting_format, 
                       m.location_or_meeting_link, m.meeting_status, m.meeting_note, m.prep_guide, m.interview_range
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_employers e ON p.employer_id = e.employer_id
                LEFT JOIN candidate_meetings m ON m.request_id = r.request_id
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
                SELECT r.*, s.full_name as seeker_name, s.email, s.phone_number, s.avatar_url as seeker_avatar, p.job_title, p.workplace_address,
                       s.account_id as seeker_account_id,
                       m.meeting_id, m.meeting_date, m.start_time, m.end_time, m.meeting_format, 
                       m.location_or_meeting_link, m.meeting_status, m.meeting_note, m.prep_guide, m.interview_range
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                LEFT JOIN candidate_meetings m ON m.request_id = r.request_id
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

    /**
     * Lấy danh sách hồ sơ trong Kho Talent Pool (Hẹn Dịp Sau / Đã từng từ chối lưu lại) của Doanh Nghiệp
     */
    public static function getTalentPoolByEmployer($employerId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT r.request_id, r.seeker_id, r.job_post_id, r.request_status, r.applied_at, r.response_deadline,
                       s.full_name as seeker_name, s.avatar_url as seeker_avatar, s.phone_number, s.email,
                       s.skills, s.experience_years, s.education_level, s.desired_position, s.trust_score, s.address,
                       s.gender, s.date_of_birth, s.bio,
                       s.account_id as seeker_account_id,
                       p.job_title as past_job_title,
                       m.meeting_id, m.meeting_date, m.meeting_status, m.meeting_note, m.prep_guide
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                LEFT JOIN candidate_meetings m ON m.request_id = r.request_id
                WHERE p.employer_id = :eid
                  AND r.request_status IN ('rejected', 'invitation_rejected')
                ORDER BY r.applied_at DESC
            ");
            $stmt->execute([':eid' => $employerId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Gom nhóm theo seeker_id để mỗi người lao động chỉ xuất hiện 1 hồ sơ đại diện trong Talent Pool
            $uniqueCandidates = [];
            foreach ($rows as $row) {
                $sid = $row['seeker_id'];
                if (!isset($uniqueCandidates[$sid])) {
                    $uniqueCandidates[$sid] = $row;
                }
            }
            return array_values($uniqueCandidates);
        } catch (PDOException $e) {
            error_log("[JobRequest::getTalentPoolByEmployer Error] " . $e->getMessage());
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
            $ok = $stmt->execute([
                ':st' => $status,
                ':acc' => $accountId,
                ':reason' => $rejectionReason,
                ':id' => $requestId
            ]);

            if ($ok && $status === 'rejected') {
                self::sendRejectionNotification($requestId, $rejectionReason);
            }

            return $ok;
        } catch (PDOException $e) {
            error_log("[JobRequest::updateStatus Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Chấp nhận người lao động kèm thiết lập Onboarding ngày đầu đi làm
     */
    public static function acceptWithOnboarding($requestId, $accountId, $onboardingDetails) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $jsonDetails = is_array($onboardingDetails) ? json_encode($onboardingDetails, JSON_UNESCAPED_UNICODE) : $onboardingDetails;
            $stmt = $db->prepare("
                UPDATE job_requests SET 
                    request_status = 'accepted', 
                    processed_by_account_id = :acc, 
                    onboarding_details = :onboarding,
                    status_updated_at = NOW()
                WHERE request_id = :id
            ");
            $ok = $stmt->execute([
                ':acc' => $accountId,
                ':onboarding' => $jsonDetails,
                ':id' => $requestId
            ]);

            if ($ok) {
                // Tự động cập nhật trạng thái phỏng vấn liên quan thành hoàn thành (completed)
                $stmtM = $db->prepare("UPDATE candidate_meetings SET meeting_status = 'completed' WHERE request_id = ? AND meeting_status != 'completed'");
                $stmtM->execute([$requestId]);

                self::sendOnboardingNotification($requestId, $onboardingDetails);
            }
            return $ok;
        } catch (PDOException $e) {
            error_log("[JobRequest::acceptWithOnboarding Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi tin nhắn và email thông báo trúng tuyển kèm checklist ngày đầu đi làm
     */
    private static function sendOnboardingNotification($requestId, $onboardingDetails) {
        $db = Database::getConnection();
        if (!$db) return;

        try {
            $stmt = $db->prepare("
                SELECT r.seeker_id, p.job_title, e.employer_name, e.account_id as employer_account_id, 
                       s.account_id as seeker_account_id, s.full_name as seeker_name
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_employers e ON p.employer_id = e.employer_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                WHERE r.request_id = :id
            ");
            $stmt->execute([':id' => $requestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return;

            $data = is_array($onboardingDetails) ? $onboardingDetails : json_decode($onboardingDetails, true);
            $startDate = !empty($data['start_date']) ? date('d/m/Y', strtotime($data['start_date'])) : 'Theo thỏa thuận';
            $startTime = $data['start_time'] ?? '08:00';
            $workplace = $data['workplace'] ?? 'Địa điểm công ty';
            $contactPerson = $data['contact_person'] ?? $row['employer_name'];
            $checklist = $data['checklist'] ?? [];
            $notes = $data['notes'] ?? '';

            $msgTitle = "Chúc mừng bạn đã trúng tuyển vị trí: " . $row['job_title'];
            $msgContent = "Xin chúc mừng " . $row['seeker_name'] . ",\n\nDoanh nghiệp **" . $row['employer_name'] . "** xin trân trọng thông báo bạn đã xuất sắc vượt qua vòng phỏng vấn và chính thức trúng tuyển vị trí **" . $row['job_title'] . "**.\n\n"
                . "**HÀNH TRANG & YÊU CẦU BUỔI ĐẦU ĐI LÀM:**\n"
                . "• Ngày bắt đầu đi làm: **" . $startDate . "** (lúc " . $startTime . ")\n"
                . "• Địa chỉ làm việc: " . $workplace . "\n"
                . "• Người đón tiếp: " . $contactPerson . "\n\n"
                . "**Hồ sơ / Giấy tờ cần chuẩn bị mang theo:**\n";

            if (!empty($checklist)) {
                foreach ($checklist as $item) {
                    $msgContent .= "- [x] " . $item . "\n";
                }
            } else {
                $msgContent .= "- [x] Căn cước công dân (CCCD / VNeID) bản gốc & photo\n"
                             . "- [x] Hồ sơ xin việc / Bằng cấp liên quan\n"
                             . "- [x] Trang phục gọn gàng, lịch sự\n";
            }

            if (!empty($notes)) {
                $msgContent .= "\n*Dặn dò thêm từ công ty: " . $notes . "*\n";
            }

            $msgContent .= "\nVui lòng vào mục 'Việc Đã Ứng Tuyển' trên JobLink để xem đầy đủ tiến độ và danh mục chuẩn bị nhé!";

            // Lưu vào bảng messages
            $stmtMsg = $db->prepare("
                INSERT INTO messages (sender_account_id, receiver_account_id, message_title, message_content, message_type, is_read, sent_at)
                VALUES (:snd, :rcv, :title, :cnt, 'onboarding', 0, NOW())
            ");
            $stmtMsg->execute([
                ':snd' => $row['employer_account_id'],
                ':rcv' => $row['seeker_account_id'],
                ':title' => $msgTitle,
                ':cnt' => $msgContent
            ]);

            // Gửi qua Email
            MailService::notifyApplicationAccepted($row['seeker_id'], $row['employer_name'], $row['job_title'], $data);
        } catch (Throwable $e) {
            error_log("[sendOnboardingNotification Error] " . $e->getMessage());
        }
    }

    /**
     * Gửi tin nhắn và email từ chối văn minh kèm lời hẹn phỏng vấn dịp sau
     */
    private static function sendRejectionNotification($requestId, $rejectionReason) {
        $db = Database::getConnection();
        if (!$db) return;

        try {
            $stmt = $db->prepare("
                SELECT r.seeker_id, p.job_title, e.employer_name, e.account_id as employer_account_id, 
                       s.account_id as seeker_account_id, s.full_name as seeker_name
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_employers e ON p.employer_id = e.employer_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                WHERE r.request_id = :id
            ");
            $stmt->execute([':id' => $requestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return;

            $msgTitle = "Thư cảm ơn từ " . $row['employer_name'] . " - Vị trí " . $row['job_title'];
            $msgContent = "Kính gửi " . $row['seeker_name'] . ",\n\n"
                . "Doanh nghiệp **" . $row['employer_name'] . "** xin chân thành cảm ơn bạn đã dành thời gian quan tâm và tham gia ứng tuyển/phỏng vấn vị trí **" . $row['job_title'] . "**.\n\n"
                . "Dù ở thời điểm hiện tại, hồ sơ của bạn chưa thực sự phù hợp nhất với yêu cầu của vị trí này" . (!empty($rejectionReason) ? " (Lý do góp ý: " . $rejectionReason . ")" : "") . ", doanh nghiệp đánh giá rất cao năng lực và thái độ chuyên nghiệp của bạn.\n\n"
                . "**HẸN DỊP PHỎNG VẤN SAU:** Doanh nghiệp đã lưu giữ hồ sơ của bạn vào hệ thống nhân tài tiềm năng trên sàn JobLink và sẽ chủ động kết nối lại ngay khi có các vị trí tuyển dụng phù hợp hơn trong tương lai.\n\n"
                . "Chúc bạn luôn dồi dào sức khỏe và sớm tìm được công việc như ý trên sàn JobLink!";

            $stmtMsg = $db->prepare("
                INSERT INTO messages (sender_account_id, receiver_account_id, message_title, message_content, message_type, sent_at)
                VALUES (:snd, :rcv, :title, :cnt, 'normal', NOW())
            ");
            $stmtMsg->execute([
                ':snd' => $row['employer_account_id'],
                ':rcv' => $row['seeker_account_id'],
                ':title' => $msgTitle,
                ':cnt' => $msgContent
            ]);

            MailService::notifyApplicationRejected($row['seeker_id'], $row['employer_name'], $row['job_title'], $rejectionReason);
        } catch (Throwable $e) {
            error_log("[sendRejectionNotification Error] " . $e->getMessage());
        }
    }

    /**
     * Người lao động liên lạc nhắc nhở NTD khi quá hạn phản hồi kết quả
     */
    public static function remindEmployer($requestId, $seekerAccountId) {
        $db = Database::getConnection();
        if (!$db) return ['success' => false, 'message' => 'Lỗi kết nối CSDL'];

        try {
            $stmt = $db->prepare("
                SELECT r.*, p.job_title, e.employer_name, e.employer_id, e.account_id as employer_account_id, 
                       s.full_name as seeker_name, s.account_id as seeker_account_id,
                       m.meeting_date
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_employers e ON p.employer_id = e.employer_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                LEFT JOIN candidate_meetings m ON m.request_id = r.request_id
                WHERE r.request_id = :id
            ");
            $stmt->execute([':id' => $requestId]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$info || intval($info['seeker_account_id']) !== intval($seekerAccountId)) {
                return ['success' => false, 'message' => 'Không tìm thấy hồ sơ hoặc bạn không có quyền thực hiện.'];
            }

            $meetingDateStr = !empty($info['meeting_date']) ? date('d/m/Y', strtotime($info['meeting_date'])) : 'vừa qua';
            $msgTitle = "⏰ Nhắc phản hồi kết quả PV: " . $info['seeker_name'] . " - " . $info['job_title'];
            $msgContent = "Kính gửi Ban Tuyển Dụng **" . $info['employer_name'] . "**,\n\nTôi là **" . $info['seeker_name'] . "**, đã tham gia buổi phỏng vấn vị trí **" . $info['job_title'] . "** vào ngày " . $meetingDateStr . ".\n\nDo đã qua thời gian phản hồi cam kết (3 ngày làm việc), tôi xin phép liên hệ để hỏi thăm tiến độ đánh giá và kết quả phỏng vấn. Tôi rất mong muốn có cơ hội làm việc và cống hiến cho quý công ty.\n\nRất mong sớm nhận được phản hồi từ công ty. Trân trọng cảm ơn!";

            $stmtMsg = $db->prepare("
                INSERT INTO messages (sender_account_id, receiver_account_id, message_title, message_content, message_type, sent_at)
                VALUES (:snd, :rcv, :title, :cnt, 'normal', NOW())
            ");
            $stmtMsg->execute([
                ':snd' => $info['seeker_account_id'],
                ':rcv' => $info['employer_account_id'],
                ':title' => $msgTitle,
                ':cnt' => $msgContent
            ]);

            try {
                MailService::notifyEmployerResponseReminder($info['employer_id'] ?? 0, $info['seeker_name'], $info['job_title']);
            } catch (Throwable $t) {
                // silence
            }

            return ['success' => true, 'message' => 'Đã gửi tin nhắn nhắc nhở và thông báo đến Nhà tuyển dụng thành công!'];
        } catch (PDOException $e) {
            error_log("[JobRequest::remindEmployer Error] " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    public static function getByJobPost($jobPostId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT r.*, s.full_name as seeker_name, s.email, s.phone_number, s.avatar_url as seeker_avatar, 
                       s.gender, s.date_of_birth, s.experience_years as years_of_experience, s.skills, s.trust_score, s.address, p.job_title,
                       s.account_id as seeker_account_id,
                       m.meeting_id, m.meeting_date, m.start_time, m.end_time, m.meeting_format, 
                       m.location_or_meeting_link, m.meeting_status, m.meeting_note, m.prep_guide, m.interview_range
                FROM job_requests r
                JOIN job_posts p ON r.job_post_id = p.job_post_id
                JOIN user_seekers s ON r.seeker_id = s.seeker_id
                LEFT JOIN candidate_meetings m ON m.request_id = r.request_id
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
                    return ['success' => false, 'message' => 'Bạn đã gửi lời mời cho người lao động này rồi!'];
                } elseif (in_array($existing['request_status'], ['accepted', 'invitation_accepted'])) {
                    return ['success' => false, 'message' => 'Người lao động này đã được duyệt nhận!'];
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

            // Gửi tin nhắn thông báo tự động đến người lao động
            $stmtSeeker = $db->prepare("SELECT account_id, full_name FROM user_seekers WHERE seeker_id = :sid");
            $stmtSeeker->execute([':sid' => $seekerId]);
            $seeker = $stmtSeeker->fetch(PDO::FETCH_ASSOC);

            $stmtEmp = $db->prepare("SELECT e.employer_name, e.account_id, p.job_title FROM user_employers e JOIN job_posts p ON p.employer_id = e.employer_id WHERE p.job_post_id = :jid");
            $stmtEmp->execute([':jid' => $jobPostId]);
            $empInfo = $stmtEmp->fetch(PDO::FETCH_ASSOC);

            if ($seeker && $empInfo) {
                $msgTitle = "Lời mời nhận việc: " . $empInfo['job_title'];
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
            $msgTitle = ($action === 'accept') ? "Người lao động đã đồng ý lời mời: " . $req['job_title'] : "Thông báo: Người lao động từ chối lời mời";
            $msgContent = "Xin chào " . $req['employer_name'] . ",\n\nNgười lao động **" . $req['seeker_name'] . "** " . $statusText . " cho vị trí **" . $req['job_title'] . "**.\n\n" . ($action === 'accept' ? "Hai bên có thể bắt đầu trao đổi tin nhắn riêng và sử dụng tính năng **AI Gợi Ý Khung Giờ Rảnh** để chốt lịch phỏng vấn ngay!" : "");

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
