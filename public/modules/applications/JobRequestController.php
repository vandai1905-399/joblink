<?php
// modules/applications/JobRequestController.php - Quản lý job_requests & job_saved

require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../services/AIService.php';
require_once __DIR__ . '/../jobs/JobPost.php';
require_once __DIR__ . '/../profiles/UserSeeker.php';
require_once __DIR__ . '/../profiles/UserEmployer.php';
require_once __DIR__ . '/../reviews/Review.php';
require_once __DIR__ . '/JobRequest.php';

class JobRequestController {

    /**
     * Quản lý danh sách Người lao động đề xuất & Kho hồ sơ hẹn dịp sau (Talent Pool)
     */
    public function recommendedCandidates() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        if (!$employer) {
            header('Location: ' . url('/employer/dashboard'));
            exit();
        }

        $activeTab = $_GET['tab'] ?? 'ai_recommendations'; // Mặc định hiển thị danh sách NLĐ được AI đề xuất đầu tiên
        $selectedJobId = intval($_GET['job_id'] ?? 0);
        $threshold = isset($_GET['threshold']) ? intval($_GET['threshold']) : 70;
        if ($threshold < 30 || $threshold > 100) {
            $threshold = 70;
        }

        // 1. Lấy danh sách tin tuyển dụng đang mở (approved) của NTD
        $allEmployerJobs = JobPost::getByEmployer($employer['employer_id']);
        $activeJobs = array_values(array_filter($allEmployerJobs, fn($j) => $j['post_status'] === 'approved'));

        // 2. Lấy danh sách Kho hồ sơ Talent Pool (Đã từng ứng tuyển / phỏng vấn nhưng hẹn dịp sau)
        $talentPoolCandidates = JobRequest::getTalentPoolByEmployer($employer['employer_id']);

        // Phân tích AI Matching giữa từng người lao động Talent Pool và các tin tuyển dụng đang mở
        foreach ($talentPoolCandidates as &$cand) {
            $bestMatch = null;
            foreach ($activeJobs as $job) {
                $analysis = AIService::calculateJobMatchScore($cand, $job);
                if (!$bestMatch || $analysis['score'] > $bestMatch['score']) {
                    $bestMatch = [
                        'job_post_id'       => $job['job_post_id'],
                        'job_title'         => $job['job_title'],
                        'workplace_address' => $job['workplace_address'] ?? '',
                        'score'             => $analysis['score'],
                        'level'             => $analysis['level'],
                        'label'             => $analysis['label'],
                        'matched_skills'    => $analysis['matched_skills'],
                        'reasons'           => $analysis['reasons'],
                    ];
                }
            }
            $cand['best_match'] = $bestMatch;
            // Gần đạt yêu cầu nếu điểm >= 50%
            $cand['is_nearly_qualified'] = ($bestMatch && $bestMatch['score'] >= 50);
        }
        unset($cand);

        // Sắp xếp Talent Pool: Ưu tiên người gần đạt yêu cầu lên đầu
        usort($talentPoolCandidates, function($a, $b) {
            $scoreA = $a['best_match']['score'] ?? 0;
            $scoreB = $b['best_match']['score'] ?? 0;
            return $scoreB <=> $scoreA;
        });

        // 3. Lấy danh sách Toàn bộ NLĐ được AI đề xuất (Tab 2)
        $aiCandidates = [];
        if ($selectedJobId > 0) {
            $aiCandidates = AIService::matchCandidatesForJob($selectedJobId, 50);
        } elseif (!empty($activeJobs)) {
            $aiCandidates = AIService::matchCandidatesForJob($activeJobs[0]['job_post_id'], 50);
            $selectedJobId = $activeJobs[0]['job_post_id'];
        }

        // Phân loại danh sách ứng viên theo hạn mức % (Threshold do NTD đặt)
        $qualifiedCandidates = [];
        $unqualifiedCandidates = [];
        foreach ($aiCandidates as $cand) {
            if (($cand['ai_match_score'] ?? 0) >= $threshold) {
                $qualifiedCandidates[] = $cand;
            } else {
                $unqualifiedCandidates[] = $cand;
            }
        }

        require_once __DIR__ . '/../profiles/EmployerCriteria.php';
        $employerCriteriaList = EmployerCriteria::getByEmployer($employer['employer_id']);

        $pageTitle = 'Quản Lý NLĐ Đề Xuất & Kho Hồ Sơ Tiềm Năng - JobLink';
        require_once __DIR__ . '/employer_recommended_candidates.php';
    }

    /**
     * Xem hồ sơ chi tiết và đánh giá năng lực của Người lao động (Employer)
     */
    public function candidateDetail($id = null) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN', 'SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        if (!$employer && $user['role_code'] === 'EMPLOYER_ADMIN') {
            header('Location: ' . url('/employer/dashboard'));
            exit();
        }
        $employerId = $employer['employer_id'] ?? 0;

        $seekerId = intval($id);
        if ($seekerId <= 0) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin người lao động!';
            header('Location: ' . url('/employer/applications'));
            exit();
        }

        $candidate = UserSeeker::findById($seekerId);
        if (!$candidate) {
            $_SESSION['flash_error'] = 'Không tìm thấy hồ sơ người lao động!';
            header('Location: ' . url('/employer/applications'));
            exit();
        }

        // Lấy danh sách đánh giá năng lực của người lao động từ các NTD
        $reviews = Review::getBySeeker($seekerId);
        if (!empty($reviews)) {
            $sumRating = array_sum(array_column($reviews, 'overall_rating'));
            $candidate['avg_rating'] = number_format($sumRating / count($reviews), 1);
        } else {
            $candidate['avg_rating'] = !empty($candidate['avg_rating']) ? number_format((float)$candidate['avg_rating'], 1) : '5.0';
        }

        // Lấy các tin tuyển dụng đang mở của doanh nghiệp
        $allEmployerJobs = $employerId > 0 ? JobPost::getByEmployer($employerId) : [];
        $activeJobs = array_values(array_filter($allEmployerJobs, fn($j) => ($j['post_status'] ?? '') === 'approved'));

        // Lấy các đơn ứng tuyển của người lao động này tại doanh nghiệp hiện tại
        $allEmployerApps = $employerId > 0 ? JobRequest::getByEmployer($employerId) : [];
        $applications = array_values(array_filter($allEmployerApps, function($app) use ($seekerId) {
            return intval($app['seeker_id']) === $seekerId;
        }));

        // Xác định vị trí tuyển dụng được chọn để đối chiếu ứng viên
        $selectedJobId = intval($_GET['job_id'] ?? 0);
        if ($selectedJobId <= 0) {
            if (!empty($applications)) {
                $selectedJobId = intval($applications[0]['job_post_id']);
            } elseif (!empty($activeJobs)) {
                $selectedJobId = intval($activeJobs[0]['job_post_id']);
            } elseif (!empty($allEmployerJobs)) {
                $selectedJobId = intval($allEmployerJobs[0]['job_post_id']);
            }
        }

        $selectedJob = null;
        $matchAnalysis = null;
        if ($selectedJobId > 0) {
            foreach ($allEmployerJobs as $j) {
                if (intval($j['job_post_id']) === $selectedJobId) {
                    $selectedJob = $j;
                    break;
                }
            }
            if (!$selectedJob) {
                $selectedJob = JobPost::findById($selectedJobId);
            }
            if ($selectedJob) {
                $matchAnalysis = AIService::analyzeJobFit($seekerId, $selectedJobId);
            }
        }

        // Phân tích độ tương thích AI cho từng đơn ứng tuyển
        foreach ($applications as &$app) {
            $analysis = AIService::analyzeJobFit($app['seeker_id'], $app['job_post_id']);
            if ($analysis) {
                $app['ai_analysis'] = $analysis;
            }
        }
        unset($app);

        $pageTitle = 'Hồ Sơ: ' . ($candidate['full_name'] ?? 'Người Lao Động') . ' - JobLink';
        require_once __DIR__ . '/candidate_detail.php';
    }

    /**
     * Nộp hồ sơ ứng tuyển (Seeker)
     */
    public function apply($jobId = null) {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $targetJobId = $jobId ?? ($_POST['job_id'] ?? 0);
        $availableWorkDates = trim($_POST['available_work_dates'] ?? '');
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($targetJobId && $seeker) {
            $result = JobRequest::apply($seeker['seeker_id'], $targetJobId, $availableWorkDates ?: null);

            if ($result === 'already_applied') {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'already_applied' => true, 'message' => 'Bạn đã ứng tuyển vị trí này rồi! Không thể nộp đơn trùng lặp.'], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['flash_error'] = 'Bạn đã ứng tuyển vị trí này rồi! Không thể nộp đơn trùng lặp.';
            } elseif ($result) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => true, 'message' => 'Nộp đơn ứng tuyển thành công! Nhà tuyển dụng sẽ sớm liên hệ.'], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['flash_msg'] = 'Nộp đơn ứng tuyển thành công! Nhà tuyển dụng sẽ sớm liên hệ.';
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => 'Không thể nộp đơn ứng tuyển. Vui lòng thử lại!'], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['flash_error'] = 'Không thể nộp đơn ứng tuyển. Vui lòng thử lại!';
            }
        }
        header('Location: ' . url('/jobs/' . $targetJobId));
        exit();
    }

    /**
     * Danh sách việc đã ứng tuyển (Seeker)
     */
    public function getAppliedJobs() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $appliedJobs = JobRequest::getBySeeker($seeker['seeker_id'] ?? 0);
        $pageTitle = 'Việc Đã Ứng Tuyển - JobLink';
        require_once __DIR__ . '/seeker_applied_jobs.php';
    }

    /**
     * Danh sách việc làm đã lưu (Seeker)
     */
    public function getSavedJobs() {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $savedJobs = JobSaved::getBySeeker($seeker['seeker_id'] ?? 0);
        $pageTitle = 'Việc Làm Đã Lưu - JobLink';
        require_once __DIR__ . '/seeker_saved_jobs.php';
    }

    /**
     * Lưu / Bỏ lưu tin việc làm (Seeker)
     */
    public function toggleSave($jobId = null) {
        $jobId = intval($jobId ?? $_GET['id'] ?? $_GET['job_id'] ?? $_POST['job_id'] ?? $_POST['job_post_id'] ?? 0);

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if (!isset($_SESSION['user'])) {
            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để lưu việc làm!'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập để lưu việc làm!';
            header('Location: ' . url('/auth/login'));
            exit();
        }

        $user = $_SESSION['user'];
        if (($user['role_code'] ?? '') !== 'JOB_SEEKER') {
            if ($isAjax) {
                http_response_code(403);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Chức năng lưu việc làm chỉ dành cho Người lao động!'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_error'] = 'Chức năng lưu việc làm chỉ dành cho Người lao động!';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('/jobs')));
            exit();
        }

        $seeker = UserSeeker::findByAccountId($user['account_id']);
        if (!$seeker) {
            $seeker = UserSeeker::createDefault($user['account_id'], $user['username'] ?? '', $user['email'] ?? '');
        }

        $seekerId = intval($seeker['seeker_id'] ?? 0);
        if ($seekerId <= 0 || $jobId <= 0) {
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Thông tin việc làm không hợp lệ!'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('/seeker/dashboard')));
            exit();
        }

        $saved = JobSaved::toggle($seekerId, $jobId);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => true,
                'saved' => $saved,
                'job_id' => $jobId,
                'message' => $saved ? 'Đã lưu việc làm vào danh sách quan tâm!' : 'Đã bỏ lưu việc làm!'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $_SESSION['flash_msg'] = $saved ? 'Đã lưu việc làm vào danh sách quan tâm!' : 'Đã bỏ lưu việc làm!';
        $_SESSION['flash_type'] = $saved ? 'success' : 'info';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('/seeker/saved-jobs')));
        exit();
    }

    /**
     * Danh sách hồ sơ ứng tuyển nhận được (Employer)
     */
    public function getEmployerApplications() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $applications = JobRequest::getByEmployer($employer['employer_id'] ?? 0);

        // Tính điểm tương thích AI cho từng đơn ứng tuyển
        foreach ($applications as &$app) {
            $analysis = AIService::analyzeJobFit($app['seeker_id'], $app['job_post_id']);
            if ($analysis) {
                $app['ai_match_score'] = $analysis['score'];
                $app['ai_match_level'] = $analysis['level'];
                $app['ai_match_label'] = $analysis['label'];
                $app['ai_matched_skills'] = $analysis['matched_skills'];
                $app['ai_missing_skills'] = $analysis['missing_skills'] ?? [];
                $app['ai_reasons'] = $analysis['reasons'] ?? [];
                $app['ai_breakdown'] = $analysis['breakdown'] ?? [];
            }
        }
        unset($app);

        $pageTitle = 'Quản Lý Hồ Sơ Ứng Tuyển - JobLink';
        require_once __DIR__ . '/employer_applications.php';
    }

    /**
     * Duyệt hoặc từ chối đơn ứng tuyển (Employer)
     */
    public function updateStatus($id) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $status = $_POST['status'] ?? 'accepted';
        $reason = $_POST['rejection_reason'] ?? null;
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        JobRequest::updateStatus($id, $status, $user['account_id'], $reason);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true, 'status' => $status, 'message' => 'Đã cập nhật trạng thái đơn ứng tuyển!'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($status === 'accepted') {
            $_SESSION['flash_msg'] = 'Đã duyệt hồ sơ người lao động thành công!';
            $_SESSION['flash_type'] = 'success';
        } elseif ($status === 'rejected') {
            $_SESSION['flash_msg'] = 'Đã từ chối hồ sơ người lao động!' . ($reason ? " (Lý do: {$reason})" : '');
            $_SESSION['flash_type'] = 'warning';
        } else {
            $_SESSION['flash_msg'] = 'Đã chuyển hồ sơ về trạng thái chờ duyệt!' . ($reason ? " (Lý do: {$reason})" : '');
            $_SESSION['flash_type'] = 'info';
        }

        $redirectUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : url('/employer/applications');
        header('Location: ' . $redirectUrl);
        exit();
    }

    /**
     * Chấp nhận đơn ứng tuyển kèm thiết lập Onboarding ngày đầu đi làm (Employer)
     */
    public function acceptWithOnboarding($id = 0) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN', 'INTERMEDIARY']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (!empty($_POST['is_ajax']));

        $requestId = intval($id);
        $partnerId = intval($_POST['partner_id'] ?? 0);
        $seekerId = intval($_POST['seeker_id'] ?? 0);
        $jobPostId = intval($_POST['job_post_id'] ?? 0);
        $db = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $startDate = trim($_POST['start_date'] ?? date('Y-m-d', strtotime('+3 days')));
            $startTime = trim($_POST['start_time'] ?? '08:30');
            $workplace = trim($_POST['workplace'] ?? '');
            $contactPerson = trim($_POST['contact_person'] ?? '');
            $checklist = $_POST['checklist'] ?? [];
            $notes = trim($_POST['notes'] ?? '');

            // Nếu chưa có requestId nhưng có seeker_id và job_post_id (duyệt trực tiếp từ chat)
            if ($requestId <= 0 && $db) {
                if ($seekerId <= 0 && $partnerId > 0) {
                    $stmtS = $db->prepare("SELECT seeker_id FROM user_seekers WHERE account_id = :aid");
                    $stmtS->execute([':aid' => $partnerId]);
                    $seekerId = (int)$stmtS->fetchColumn();
                }
                if ($seekerId > 0 && $jobPostId > 0) {
                    $stmtReq = $db->prepare("SELECT request_id FROM job_requests WHERE seeker_id = :sid AND job_post_id = :jid");
                    $stmtReq->execute([':sid' => $seekerId, ':jid' => $jobPostId]);
                    $exId = $stmtReq->fetchColumn();
                    if ($exId) {
                        $requestId = (int)$exId;
                    } else {
                        $stmtIns = $db->prepare("INSERT INTO job_requests (seeker_id, job_post_id, request_status, applied_at) VALUES (:sid, :jid, 'accepted', NOW())");
                        $stmtIns->execute([':sid' => $seekerId, ':jid' => $jobPostId]);
                        $requestId = (int)$db->lastInsertId();
                    }
                }
            }

            if ($requestId <= 0) {
                $err = 'Không tìm thấy hồ sơ hoặc đơn ứng tuyển để duyệt nhận việc!';
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $err], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['flash_msg'] = $err;
                $_SESSION['flash_type'] = 'danger';
                header('Location: ' . url('/employer/applications'));
                exit();
            }

            // Tra cứu tài khoản của người lao động để chuyển hướng vào khung chat
            if ($partnerId <= 0 && $db) {
                $stmtSeeker = $db->prepare("SELECT s.account_id FROM job_requests r JOIN user_seekers s ON r.seeker_id = s.seeker_id WHERE r.request_id = :id");
                $stmtSeeker->execute([':id' => $requestId]);
                $partnerId = (int)$stmtSeeker->fetchColumn();
            }

            $onboardingData = [
                'start_date' => $startDate,
                'start_time' => $startTime,
                'workplace'  => $workplace,
                'contact_person' => $contactPerson,
                'checklist'  => is_array($checklist) ? $checklist : [],
                'notes'      => $notes
            ];

            $res = JobRequest::acceptWithOnboarding($requestId, $user['account_id'], $onboardingData);

            // Lấy tin nhắn Onboarding vừa được hệ thống tự động sinh ra để trả về cho giao diện chat
            $lastMsg = null;
            if ($partnerId > 0 && $db) {
                $stmtLastMsg = $db->prepare("
                    SELECT * FROM messages 
                    WHERE sender_account_id = :snd AND receiver_account_id = :rcv 
                    ORDER BY message_id DESC LIMIT 1
                ");
                $stmtLastMsg->execute([':snd' => $user['account_id'], ':rcv' => $partnerId]);
                $lastMsg = $stmtLastMsg->fetch(PDO::FETCH_ASSOC);
            }

            $redirectUrl = ($partnerId > 0) ? url('/employer/messages?partner_id=' . $partnerId) : url('/employer/messages');

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'success' => $res,
                    'message' => 'Đã duyệt nhận người lao động và gửi cẩm nang ngày đầu đi làm thành công!',
                    'redirect_url' => $redirectUrl,
                    'data' => [
                        'partner_id' => $partnerId,
                        'message_id' => $lastMsg['message_id'] ?? null,
                        'message_title' => $lastMsg['message_title'] ?? 'Chúc mừng bạn đã trúng tuyển nhận việc',
                        'message_content' => $lastMsg['message_content'] ?? '',
                        'message_type' => 'onboarding',
                        'time_formatted' => date('H:i')
                    ]
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_msg'] = 'Đã duyệt nhận việc thành công! Hệ thống chuyển bạn vào khung chat để tiện trao đổi thêm với người lao động.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . $redirectUrl);
            exit();
        }

        header('Location: ' . url('/employer/messages'));
        exit();
    }

    /**
     * Người lao động gửi tin nhắn / liên lạc nhắc nhở NTD khi quá hạn phản hồi (Seeker)
     */
    public function remindEmployer($id) {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        $res = JobRequest::remindEmployer(intval($id), $user['account_id']);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }

        $_SESSION['flash_msg'] = $res['message'];
        $_SESSION['flash_type'] = $res['success'] ? 'success' : 'warning';
        header('Location: ' . url('/seeker/applied-jobs'));
        exit();
    }

    /**
     * Xóa đơn ứng tuyển (Employer)
     */
    public function delete($id) {
        AuthController::checkRole(['EMPLOYER_ADMIN']);
        JobRequest::delete($id);
        $_SESSION['flash_msg'] = 'Đã xóa đơn ứng tuyển thành công!';
        $redirectUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : url('/employer/applications');
        header('Location: ' . $redirectUrl);
        exit();
    }

    /**
     * Gửi lời mời nhận việc / phỏng vấn đến Người lao động (Employer)
     */
    public function inviteCandidate() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $employer) {
            $seekerId = intval($_POST['seeker_id'] ?? 0);
            $jobPostId = intval($_POST['job_post_id'] ?? 0);
            $note = trim($_POST['invitation_note'] ?? ($_POST['note'] ?? ''));

            if ($seekerId > 0 && $jobPostId > 0) {
                $res = JobRequest::inviteCandidate($employer['employer_id'], $seekerId, $jobPostId, $note);
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['flash_msg'] = $res['message'];
                $_SESSION['flash_type'] = $res['success'] ? 'success' : 'warning';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('/employer/jobs/' . $jobPostId . '/candidates')));
                exit();
            }
        }

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], JSON_UNESCAPED_UNICODE);
            exit();
        }
        header('Location: ' . url('/employer/jobs'));
        exit();
    }

    /**
     * Phản hồi lời mời nhận việc (Seeker: Đồng ý / Từ chối)
     */
    public function respondInvitation($requestId) {
        $user = AuthController::checkRole(['JOB_SEEKER']);
        $action = trim($_POST['action'] ?? ($_GET['action'] ?? 'accept'));
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        $res = JobRequest::respondInvitation(intval($requestId), $action, $user['account_id']);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }

        $_SESSION['flash_msg'] = $res['message'];
        $_SESSION['flash_type'] = $res['success'] ? 'success' : 'warning';
        header('Location: ' . url('/seeker/applied-jobs'));
        exit();
    }

    /**
     * API Gợi Ý Khung Giờ Phỏng Vấn Rảnh Chống Trùng Lịch (AI Smart Time Slots)
     */
    public function apiSuggestMeetingSlots() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN', 'JOB_SEEKER', 'SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $employerId = intval($_GET['employer_id'] ?? 0);
        $seekerId = intval($_GET['seeker_id'] ?? 0);
        $requestId = intval($_GET['request_id'] ?? 0);

        $db = Database::getConnection();
        if ($requestId > 0 && ($employerId === 0 || $seekerId === 0) && $db) {
            $stmt = $db->prepare("
                SELECT r.seeker_id, p.employer_id 
                FROM job_requests r 
                JOIN job_posts p ON r.job_post_id = p.job_post_id 
                WHERE r.request_id = :id
            ");
            $stmt->execute([':id' => $requestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $seekerId = intval($row['seeker_id']);
                $employerId = intval($row['employer_id']);
            }
        }

        if ($employerId === 0 && $user['role_code'] === 'EMPLOYER_ADMIN') {
            $emp = UserEmployer::findByAccountId($user['account_id']);
            if ($emp) $employerId = intval($emp['employer_id']);
        }

        if ($seekerId === 0 && $user['role_code'] === 'JOB_SEEKER') {
            $sk = UserSeeker::findByAccountId($user['account_id']);
            if ($sk) $seekerId = intval($sk['seeker_id']);
        }

        $slots = AIService::suggestInterviewTimeSlots($employerId, $seekerId);

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => true,
            'employer_id' => $employerId,
            'seeker_id' => $seekerId,
            'slots' => $slots
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
}
