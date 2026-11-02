<?php
// modules/meetings/MeetingController.php - Quản lý candidate_meetings

require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../profiles/UserEmployer.php';
require_once __DIR__ . '/../profiles/UserSeeker.php';
require_once __DIR__ . '/../../../services/MailService.php';

class MeetingController {

    /**
     * Lịch phỏng vấn phía Người lao động (Seeker)
     */
    public function seekerMeetings() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $meetings = CandidateMeeting::getBySeeker($seeker['seeker_id'] ?? 0);
        $pageTitle = 'Lịch Phỏng Vấn & Cuộc Hẹn - JobLink';
        require_once __DIR__ . '/seeker_meetings.php';
    }

    /**
     * Lịch phỏng vấn phía Nhà tuyển dụng (Employer)
     */
    public function employerMeetings() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $meetings = CandidateMeeting::getByEmployer($employer['employer_id'] ?? 0);
        $applications = JobRequest::getByEmployer($employer['employer_id'] ?? 0);
        $pageTitle = 'Quản Lý Lịch Phỏng Vấn & Cuộc Hẹn - JobLink';
        require_once __DIR__ . '/employer_meetings.php';
    }

    /**
     * Lên lịch hẹn mới (Employer)
     */
    public function store() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestId = intval($_POST['request_id']);
            $prepGuide = trim($_POST['prep_guide'] ?? '');
            $interviewRange = trim($_POST['interview_range'] ?? '');

            $created = CandidateMeeting::create([
                'request_id' => $requestId,
                'created_by_account_id' => $user['account_id'],
                'meeting_date' => $_POST['meeting_date'],
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'] ?? null,
                'location_or_meeting_link' => trim($_POST['location_or_meeting_link']),
                'meeting_format' => $_POST['meeting_format'] ?? 'offline',
                'meeting_note' => trim($_POST['meeting_note'] ?? ''),
                'prep_guide' => $prepGuide,
                'interview_range' => $interviewRange
            ]);

            if ($created) {
                // Gửi thông báo Gmail cho người lao động kèm cẩm nang chuẩn bị
                try {
                    $db = Database::getConnection();
                    $stmt = $db->prepare("
                        SELECT r.seeker_id, e.employer_name 
                        FROM job_requests r 
                        JOIN job_posts p ON r.job_post_id = p.job_post_id 
                        JOIN user_employers e ON p.employer_id = e.employer_id 
                        WHERE r.request_id = :rid
                    ");
                    $stmt->execute([':rid' => $requestId]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        require_once __DIR__ . '/../../../services/MailService.php';
                        $dt = $_POST['meeting_date'] . ' ' . $_POST['start_time'];
                        MailService::notifyMeetingScheduled(
                            $row['seeker_id'], 
                            $row['employer_name'], 
                            $dt, 
                            $_POST['location_or_meeting_link'],
                            $prepGuide,
                            $interviewRange
                        );
                    }
                } catch (Throwable $e) {
                    error_log("[Meeting Mail Warning] " . $e->getMessage());
                }
            }

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => $created, 'message' => 'Đã lên lịch phỏng vấn và gửi thông báo qua Gmail thành công!'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_msg'] = 'Đã lên lịch phỏng vấn và gửi thông báo qua Gmail thành công!';
        }
        header('Location: ' . url('/employer/meetings'));
        exit();
    }

    /**
     * Người lao động gửi đề xuất đổi lịch hẹn phỏng vấn (Seeker)
     */
    public function seekerRequestReschedule($id) {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        $proposedDate = trim($_POST['proposed_date'] ?? date('Y-m-d', strtotime('+1 day')));
        $proposedTime = trim($_POST['proposed_time'] ?? '09:00');
        $reason = trim($_POST['reschedule_reason'] ?? 'Người lao động có lịch bận đột xuất');

        $res = CandidateMeeting::requestReschedule(intval($id), $user['account_id'], $proposedDate, $proposedTime, $reason);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }

        $_SESSION['flash_msg'] = $res['message'];
        $_SESSION['flash_type'] = $res['success'] ? 'success' : 'warning';
        header('Location: ' . url('/seeker/meetings'));
        exit();
    }

    /**
     * Cập nhật trạng thái cuộc hẹn
     */
    public function updateStatus($id) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN', 'JOB_SEEKER']);
        $status = $_POST['status'] ?? 'completed';
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        $reason = trim($_POST['rejection_reason'] ?? '');
        CandidateMeeting::updateStatus($id, $status);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true, 'status' => $status, 'message' => 'Đã cập nhật trạng thái cuộc hẹn!'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($status === 'completed') {
            $_SESSION['flash_msg'] = 'Đã hoàn thành buổi phỏng vấn!';
            $_SESSION['flash_type'] = 'success';
        } elseif ($status === 'cancelled') {
            $_SESSION['flash_msg'] = 'Đã hủy lịch phỏng vấn!' . ($reason ? " (Lý do: {$reason})" : '');
            $_SESSION['flash_type'] = 'warning';
        } else {
            $_SESSION['flash_msg'] = 'Đã lên lịch lại buổi phỏng vấn!';
            $_SESSION['flash_type'] = 'info';
        }

        if ($user['role_code'] === 'EMPLOYER_ADMIN') {
            header('Location: ' . url('/employer/meetings'));
        } else {
            header('Location: ' . url('/seeker/meetings'));
        }
        exit();
    }

    /**
     * Xóa lịch hẹn phỏng vấn
     */
    public function delete($id) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        CandidateMeeting::delete($id);
        $_SESSION['flash_msg'] = 'Đã xóa lịch hẹn thành công!';
        header('Location: ' . url('/employer/meetings'));
        exit();
    }

    /**
     * Mời phỏng vấn lại người lao động trong Talent Pool (cơ hội việc làm mới phù hợp)
     */
    public function reInviteCandidateMeeting() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $employer) {
            $seekerId = intval($_POST['seeker_id'] ?? 0);
            $jobPostId = intval($_POST['job_post_id'] ?? 0);
            $meetingDate = trim($_POST['meeting_date'] ?? date('Y-m-d', strtotime('+1 day')));
            $startTime = trim($_POST['start_time'] ?? '09:00');
            $endTime = trim($_POST['end_time'] ?? '10:00');
            $meetingFormat = trim($_POST['meeting_format'] ?? 'offline');
            $location = trim($_POST['location_or_meeting_link'] ?? 'Văn phòng công ty');
            $prepGuide = trim($_POST['prep_guide'] ?? '');
            $interviewRange = trim($_POST['interview_range'] ?? '');
            $meetingNote = trim($_POST['meeting_note'] ?? '');

            if ($seekerId <= 0 || $jobPostId <= 0) {
                $_SESSION['flash_msg'] = 'Thông tin người lao động hoặc vị trí công việc không hợp lệ!';
                $_SESSION['flash_type'] = 'danger';
                header('Location: ' . url('/employer/candidates/recommended'));
                exit();
            }

            $db = Database::getConnection();

            // 1. Kiểm tra hoặc tạo job_requests mới
            $stmtReq = $db->prepare("SELECT request_id FROM job_requests WHERE seeker_id = :sid AND job_post_id = :jid");
            $stmtReq->execute([':sid' => $seekerId, ':jid' => $jobPostId]);
            $existingReq = $stmtReq->fetch(PDO::FETCH_ASSOC);

            if ($existingReq) {
                $requestId = $existingReq['request_id'];
                $db->prepare("UPDATE job_requests SET request_status = 'accepted', applied_at = NOW() WHERE request_id = :id")
                   ->execute([':id' => $requestId]);
            } else {
                $stmtIns = $db->prepare("
                    INSERT INTO job_requests (seeker_id, job_post_id, request_status, applied_at)
                    VALUES (:sid, :jid, 'accepted', NOW())
                ");
                $stmtIns->execute([':sid' => $seekerId, ':jid' => $jobPostId]);
                $requestId = $db->lastInsertId();
            }

            // 2. Tạo cuộc hẹn phỏng vấn
            $meetingId = CandidateMeeting::create([
                'request_id' => $requestId,
                'meeting_date' => $meetingDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'meeting_format' => $meetingFormat,
                'location_or_meeting_link' => $location,
                'meeting_note' => $meetingNote,
                'prep_guide' => $prepGuide,
                'interview_range' => $interviewRange
            ]);

            // 3. Lấy thông tin job title để gửi thông báo
            $stmtJob = $db->prepare("SELECT job_title FROM job_posts WHERE job_post_id = :jid");
            $stmtJob->execute([':jid' => $jobPostId]);
            $jobInfo = $stmtJob->fetch(PDO::FETCH_ASSOC);
            $jobTitle = $jobInfo['job_title'] ?? 'Công việc mới';

            // 4. Gửi email mời phỏng vấn lại
            try {
                MailService::notifyReInviteMeeting(
                    $seekerId, 
                    $employer['employer_name'], 
                    $jobTitle, 
                    date('d/m/Y', strtotime($meetingDate)) . " lúc $startTime", 
                    $location, 
                    $prepGuide, 
                    $interviewRange, 
                    $meetingNote
                );
            } catch (Throwable $e) {
                error_log("[Re-Invite Mail Warning] " . $e->getMessage());
            }

            // 5. Gửi tin nhắn nội bộ
            $stmtSeeker = $db->prepare("SELECT account_id, full_name FROM user_seekers WHERE seeker_id = :sid");
            $stmtSeeker->execute([':sid' => $seekerId]);
            $seeker = $stmtSeeker->fetch(PDO::FETCH_ASSOC);

            if ($seeker) {
                $msgTitle = "Lời mời phỏng vấn cơ hội mới: " . $jobTitle;
                $msgContent = "Xin chào " . $seeker['full_name'] . ",\n\nDoanh nghiệp **" . $employer['employer_name'] . "** trân trọng mời bạn tham gia buổi phỏng vấn cho vị trí mới **\"" . $jobTitle . "\"**.\n\nThời gian: " . date('d/m/Y', strtotime($meetingDate)) . " lúc " . $startTime . "\nĐịa điểm: " . $location . (!empty($interviewRange) ? "\nKhung ngày dự kiến: " . $interviewRange : "") . "\n\n" . (!empty($meetingNote) ? "Lời nhắn từ NTD: *" . $meetingNote . "*\n\n" : "") . "Vui lòng vào mục 'Lịch Hẹn & Phỏng Vấn' để xem chi tiết hoặc đề xuất dời lịch nếu cần nhé!";
                
                $db->prepare("
                    INSERT INTO messages (sender_account_id, receiver_account_id, message_title, message_content, message_type, sent_at)
                    VALUES (:snd, :rcv, :title, :cnt, 'meeting_invite', NOW())
                ")->execute([
                    ':snd' => $user['account_id'],
                    ':rcv' => $seeker['account_id'],
                    ':title' => $msgTitle,
                    ':cnt' => $msgContent
                ]);
            }

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã gửi lời mời phỏng vấn lại cho người lao động thành công!'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_msg'] = 'Đã gửi lời mời phỏng vấn lại và cẩm nang chuẩn bị cho người lao động thành công!';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . url('/employer/candidates/recommended?tab=talent_pool'));
            exit();
        }

        header('Location: ' . url('/employer/candidates/recommended'));
        exit();
    }

    /**
     * Thiết lập lịch phỏng vấn trực tiếp từ khung chat trao đổi với người lao động
     */
    public function scheduleFromChat() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN', 'INTERMEDIARY']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (!empty($_POST['is_ajax']));

        $myAccountId = $user['account_id'];
        $employer = UserEmployer::findByAccountId($myAccountId);
        if (!$employer) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy hồ sơ doanh nghiệp!'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            header('Location: ' . url('/employer/dashboard'));
            exit();
        }

        $partnerId = intval($_POST['partner_id'] ?? 0);
        $seekerId = intval($_POST['seeker_id'] ?? 0);
        $jobPostId = intval($_POST['job_post_id'] ?? 0);
        $requestId = intval($_POST['request_id'] ?? 0);
        $meetingDate = trim($_POST['meeting_date'] ?? date('Y-m-d', strtotime('+1 day')));
        $startTime = trim($_POST['start_time'] ?? '09:00');
        $endTime = trim($_POST['end_time'] ?? '09:45');
        $meetingFormat = trim($_POST['meeting_format'] ?? 'offline');
        $location = trim($_POST['location_or_meeting_link'] ?? 'Văn phòng công ty');
        $prepGuide = trim($_POST['prep_guide'] ?? '');
        $interviewRange = trim($_POST['interview_range'] ?? '');
        $meetingNote = trim($_POST['meeting_note'] ?? '');

        $db = Database::getConnection();

        // 1. Resolve seekerId / partnerId
        if ($seekerId <= 0 && $partnerId > 0) {
            $stmtS = $db->prepare("SELECT seeker_id, full_name, email FROM user_seekers WHERE account_id = :aid");
            $stmtS->execute([':aid' => $partnerId]);
            $sRow = $stmtS->fetch(PDO::FETCH_ASSOC);
            if ($sRow) {
                $seekerId = intval($sRow['seeker_id']);
            }
        }
        if ($partnerId <= 0 && $seekerId > 0) {
            $stmtS = $db->prepare("SELECT account_id, full_name, email FROM user_seekers WHERE seeker_id = :sid");
            $stmtS->execute([':sid' => $seekerId]);
            $sRow = $stmtS->fetch(PDO::FETCH_ASSOC);
            if ($sRow) {
                $partnerId = intval($sRow['account_id']);
            }
        }

        if ($seekerId <= 0 || $jobPostId <= 0) {
            $errMsg = 'Vui lòng chọn người lao động và vị trí công việc hợp lệ!';
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $errMsg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_msg'] = $errMsg;
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . url('/employer/messages' . ($partnerId > 0 ? '?partner_id=' . $partnerId : '')));
            exit();
        }

        // 2. Resolve or create job_request
        if ($requestId <= 0) {
            $stmtReq = $db->prepare("SELECT request_id, request_status FROM job_requests WHERE seeker_id = :sid AND job_post_id = :jid");
            $stmtReq->execute([':sid' => $seekerId, ':jid' => $jobPostId]);
            $exReq = $stmtReq->fetch(PDO::FETCH_ASSOC);
            if ($exReq) {
                $requestId = intval($exReq['request_id']);
            } else {
                $stmtIns = $db->prepare("
                    INSERT INTO job_requests (seeker_id, job_post_id, request_status, applied_at)
                    VALUES (:sid, :jid, 'accepted', NOW())
                ");
                $stmtIns->execute([':sid' => $seekerId, ':jid' => $jobPostId]);
                $requestId = intval($db->lastInsertId());
            }
        }

        // 3. Create CandidateMeeting
        $meetingId = CandidateMeeting::create([
            'request_id' => $requestId,
            'created_by_account_id' => $myAccountId,
            'meeting_date' => $meetingDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'meeting_format' => $meetingFormat,
            'location_or_meeting_link' => $location,
            'meeting_note' => $meetingNote,
            'prep_guide' => $prepGuide,
            'interview_range' => $interviewRange
        ]);

        // 4. Job & Seeker info
        $stmtJob = $db->prepare("SELECT job_title FROM job_posts WHERE job_post_id = :jid");
        $stmtJob->execute([':jid' => $jobPostId]);
        $jobInfo = $stmtJob->fetch(PDO::FETCH_ASSOC);
        $jobTitle = $jobInfo['job_title'] ?? 'Vị trí công việc';

        $stmtSeeker = $db->prepare("SELECT full_name, email FROM user_seekers WHERE seeker_id = :sid");
        $stmtSeeker->execute([':sid' => $seekerId]);
        $seekerInfo = $stmtSeeker->fetch(PDO::FETCH_ASSOC);
        $seekerName = $seekerInfo['full_name'] ?? 'Bạn';

        // 5. Send notification email
        try {
            MailService::notifyMeetingScheduled(
                $seekerId,
                $employer['employer_name'],
                $meetingDate . ' ' . $startTime,
                $location,
                $prepGuide,
                $interviewRange
            );
        } catch (Throwable $e) {
            error_log("[Mail notifyMeetingScheduled Error] " . $e->getMessage());
        }

        // 6. Insert structured chat message
        $msgTitle = "Lời mời phỏng vấn: " . $jobTitle;
        $timeFormatted = date('d/m/Y', strtotime($meetingDate)) . " từ " . substr($startTime, 0, 5) . " đến " . substr($endTime, 0, 5);
        $formatLabel = ($meetingFormat === 'online') ? 'Online (Trực tuyến)' : 'Trực tiếp tại văn phòng/cơ sở';

        $msgContent = "Xin chào " . $seekerName . ",\n\n"
            . "Doanh nghiệp **" . $employer['employer_name'] . "** trân trọng gửi lời mời bạn tham gia buổi phỏng vấn cho vị trí **\"" . $jobTitle . "\"**.\n\n"
            . "**Thời gian:** " . $timeFormatted . "\n"
            . (!empty($interviewRange) ? "**Khung ngày dự kiến:** " . $interviewRange . "\n" : "")
            . "**Hình thức & Địa điểm:** " . $formatLabel . " — " . $location . "\n"
            . (!empty($prepGuide) ? "\n**Cẩm nang chuẩn bị:**\n" . $prepGuide . "\n" : "")
            . (!empty($meetingNote) ? "\n**Ghi chú thêm:** " . $meetingNote . "\n" : "")
            . "\nBạn có thể phản hồi trực tiếp tại khung chat này hoặc vào mục **Lịch Phỏng Vấn** để xem chi tiết nhé!";

        $newMsgId = 0;
        try {
            $stmtMsg = $db->prepare("
                INSERT INTO messages (sender_account_id, receiver_account_id, message_title, message_content, message_type, is_read, sent_at)
                VALUES (:snd, :rcv, :title, :cnt, 'meeting_invite', 0, NOW())
            ");
            $stmtMsg->execute([
                ':snd' => $myAccountId,
                ':rcv' => $partnerId,
                ':title' => $msgTitle,
                ':cnt' => $msgContent
            ]);
            $newMsgId = (int)$db->lastInsertId();
        } catch (Throwable $e) {
            error_log("[Insert Chat Meeting Msg Error] " . $e->getMessage());
        }

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => true,
                'message' => 'Đã thiết lập lịch phỏng vấn và gửi thông báo thành công!',
                'data' => [
                    'message_id' => $newMsgId,
                    'message_title' => $msgTitle,
                    'message_content' => $msgContent,
                    'message_type' => 'meeting_invite',
                    'time_formatted' => date('H:i'),
                    'meeting_date' => $meetingDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'job_title' => $jobTitle,
                    'location' => $location
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $_SESSION['flash_msg'] = 'Đã thiết lập lịch phỏng vấn và gửi thông báo thành công!';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . url('/employer/messages?partner_id=' . $partnerId));
        exit();
    }
}
