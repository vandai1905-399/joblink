<?php
// modules/jobs/JobPostController.php - Quản lý job_posts (Tìm kiếm, Đăng tin, Duyệt tin, Chi tiết, Nhà tuyển dụng)

require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../services/AIService.php';
require_once __DIR__ . '/../../../services/GeoService.php';
require_once __DIR__ . '/../../../services/SalaryBenchmarkService.php';

class JobPostController {

    /**
     * Trang chủ:
     * - Nếu ĐÃ ĐĂNG NHẬP: Tự động chuyển hướng về trang Dashboard riêng biệt cho từng role.
     * - Nếu CHƯA ĐĂNG NHẬP (Khách): Hiển thị Cổng thông tin chung & Giới thiệu nền tảng JobLink.
     */
    public function index() {
        if (isset($_SESSION['user']) && !empty($_SESSION['user']['role_code'])) {
            $roleCode = $_SESSION['user']['role_code'];
            switch ($roleCode) {
                case 'SUPER_ADMIN':
                case 'SUB_ADMIN':
                case 'CSKH_STAFF':
                    header('Location: ' . url('/admin/dashboard'));
                    exit();
                case 'EMPLOYER_ADMIN':
                    header('Location: ' . url('/employer/dashboard'));
                    exit();
                case 'INTERMEDIARY':
                    header('Location: ' . url('/intermediary/dashboard'));
                    exit();
                case 'JOB_SEEKER':
                    header('Location: ' . url('/seeker/dashboard'));
                    exit();
                default:
                    header('Location: ' . url('/jobs'));
                    exit();
            }
        }

        // CỔNG THÔNG TIN CHUNG DÀNH CHO KHÁCH CHƯA ĐĂNG NHẬP (LANDING PAGE)
        $jobs = JobPost::allApproved();
        $employers = UserEmployer::all();
        $pageTitle = 'JobLink - Cổng Thông Tin & Nền Tảng Kết Nối Việc Làm Linh Hoạt';
        require __DIR__ . '/home.php';
    }

    /**
     * Tìm kiếm / Lọc việc làm (Hỗ trợ Định Vị GPS, Bán Kính Khoảng Cách & AI Matching)
     */
    public function search() {
        $keyword = trim($_GET['keyword'] ?? $_GET['q'] ?? '');
        $location = trim($_GET['location'] ?? '');
        $lat = floatval($_GET['lat'] ?? 0);
        $lng = floatval($_GET['lng'] ?? 0);
        $radius = floatval($_GET['radius'] ?? 0); // Bán kính quét km
        $employmentType = trim($_GET['employment_type'] ?? '');
        $salaryMin = intval($_GET['salary_min'] ?? $_GET['min_salary'] ?? 0);
        $aiFilter = isset($_GET['ai_recommend']) && $_GET['ai_recommend'] === '1';
        $sortBy = trim($_GET['sort'] ?? '');

        // Tự động phân giải tọa độ nếu người dùng nhập địa danh mà chưa có tọa độ GPS
        if (($lat == 0 || $lng == 0) && !empty($location)) {
            $resolved = GeoService::resolveCoordinates($location);
            if ($resolved) {
                $lat = $resolved['lat'];
                $lng = $resolved['lng'];
            }
        }

        $hasCoordinates = ($lat != 0 && $lng != 0);

        // Nếu có tọa độ GPS và quét theo bán kính, ta không lọc cứng text location trong SQL để bắt được việc làm ở các khu lân cận
        if ($hasCoordinates) {
            $_SESSION['last_search_lat'] = $lat;
            $_SESSION['last_search_lng'] = $lng;
            $_SESSION['last_search_location'] = $location;
        }

        // Tính điểm tương thích AI nếu người dùng là Người lao động
        $currentSeeker = null;
        if (isset($_SESSION['user']) && $_SESSION['user']['role_code'] === 'JOB_SEEKER') {
            $currentSeeker = UserSeeker::findByAccountId($_SESSION['user']['account_id']);
        }

        // Nếu chưa có tọa độ tìm kiếm, lấy tọa độ theo khu vực ưu tiên của Seeker
        if (!$hasCoordinates && $currentSeeker) {
            $userLoc = $currentSeeker['preferred_location'] ?: $currentSeeker['address'];
            if (!empty($userLoc)) {
                $userCoords = GeoService::resolveCoordinates($userLoc);
                if ($userCoords) {
                    $lat = $userCoords['lat'];
                    $lng = $userCoords['lng'];
                    $location = $userLoc;
                    $hasCoordinates = true;
                }
            }
        }

        $sqlLocation = ($hasCoordinates && $radius > 0) ? '' : $location;
        $jobs = JobPost::filter($keyword, $sqlLocation, $employmentType, $salaryMin);

        // Tính khoảng cách cho từng việc làm
        foreach ($jobs as &$j) {
            $jobCoords = GeoService::getJobCoordinates($j);
            $j['job_lat'] = $jobCoords['lat'];
            $j['job_lng'] = $jobCoords['lng'];
            if ($hasCoordinates) {
                $dist = GeoService::calculateDistance($lat, $lng, $jobCoords['lat'], $jobCoords['lng']);
                $j['distance_km'] = $dist;
                $j['distance_text'] = GeoService::formatDistance($dist);
                $j['from_location'] = !empty($location) ? $location : 'vị trí của bạn';
            }
        }
        unset($j);

        // Lọc theo bán kính quét tối đa (radius km)
        if ($hasCoordinates && $radius > 0) {
            $jobs = array_values(array_filter($jobs, function($item) use ($radius) {
                return isset($item['distance_km']) && $item['distance_km'] <= $radius;
            }));
        }

        if ($currentSeeker) {
            $seekerId = $currentSeeker['seeker_id'];
            $allAiJobs = AIService::recommendJobsForSeeker($seekerId, 100);
            $aiScoreMap = [];
            foreach ($allAiJobs as $aj) {
                $aiScoreMap[$aj['job_post_id']] = $aj;
            }

            foreach ($jobs as &$j) {
                if (isset($aiScoreMap[$j['job_post_id']])) {
                    $aiData = $aiScoreMap[$j['job_post_id']];
                    $j['ai_match_score'] = $aiData['ai_match_score'];
                    $j['ai_match_level'] = $aiData['ai_match_level'];
                    $j['ai_match_label'] = $aiData['ai_match_label'];
                    $j['ai_match_color'] = $aiData['ai_match_color'];
                    $j['ai_reasons'] = $aiData['ai_reasons'] ?? [];
                    $j['ai_matched_skills'] = $aiData['ai_matched_skills'] ?? [];
                    $j['ai_missing_skills'] = $aiData['ai_missing_skills'] ?? [];
                    $j['ai_breakdown'] = $aiData['ai_breakdown'] ?? ['skills' => 80, 'location' => 80, 'employment' => 80, 'experience' => 80];
                } else {
                    $j['ai_match_score'] = 50;
                    $j['ai_match_level'] = 'medium';
                    $j['ai_match_label'] = 'Tiềm Năng';
                    $j['ai_match_color'] = '#d97706';
                    $j['ai_reasons'] = [];
                    $j['ai_matched_skills'] = [];
                    $j['ai_missing_skills'] = [];
                    $j['ai_breakdown'] = ['skills' => 50, 'location' => 50, 'employment' => 50, 'experience' => 50];
                }
            }
            unset($j);
        }

        // Sắp xếp kết quả (Mặc định tự động phân tích & ưu tiên việc làm phù hợp AI cao nhất lên đầu)
        if ($sortBy === 'salary_desc') {
            usort($jobs, function($a, $b) {
                return ($b['salary_amount'] ?? 0) <=> ($a['salary_amount'] ?? 0);
            });
        } elseif ($sortBy === 'distance_asc' && $hasCoordinates) {
            usort($jobs, function($a, $b) {
                return ($a['distance_km'] ?? 9999) <=> ($b['distance_km'] ?? 9999);
            });
        } elseif ($sortBy === 'newest') {
            usort($jobs, function($a, $b) {
                return strtotime($b['posted_at'] ?? 'now') <=> strtotime($a['posted_at'] ?? 'now');
            });
        } else {
            usort($jobs, function($a, $b) {
                $scoreA = $a['ai_match_score'] ?? 0;
                $scoreB = $b['ai_match_score'] ?? 0;
                if ($scoreB === $scoreA) {
                    return strtotime($b['posted_at'] ?? 'now') <=> strtotime($a['posted_at'] ?? 'now');
                }
                return $scoreB <=> $scoreA;
            });
        }

        $isSeeker = isset($_SESSION['user']) && $_SESSION['user']['role_code'] === 'JOB_SEEKER';
        $pageTitle = 'Tìm Kiếm Việc Làm Phù Hợp - JobLink';
        require_once __DIR__ . '/job_list.php';
    }

    /**
     * API Gợi ý địa điểm tìm kiếm phổ biến tại Bình Dương
     */
    public function apiPopularLocations() {
        header('Content-Type: application/json; charset=UTF-8');
        $locations = GeoService::getPopularLocations();
        echo json_encode(['success' => true, 'locations' => $locations], JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * API Phân tích & Đề xuất mức lương phổ biến dựa trên CSDL và hệ số tiêu chí động
     */
    public function apiSalaryBenchmark() {
        header('Content-Type: application/json; charset=UTF-8');

        $jobTitle = trim($_REQUEST['job_title'] ?? '');
        $employmentType = trim($_REQUEST['employment_type'] ?? 'full_time');
        $jobRequirements = trim($_REQUEST['job_requirements'] ?? '');
        $workShift = trim($_REQUEST['work_shift'] ?? '');
        $workplaceAddress = trim($_REQUEST['workplace_address'] ?? '');
        $expReq = trim($_REQUEST['experience_required'] ?? '');
        $eduLevel = trim($_REQUEST['education_level'] ?? '');

        // Bổ sung kinh nghiệm & học vấn vào phân tích tiêu chí nếu có
        $expMap = [
            'none' => 'không yêu cầu kinh nghiệm, chưa có kinh nghiệm',
            'under_1_year' => 'kinh nghiệm dưới 1 năm',
            '1_to_2_years' => 'kinh nghiệm 2 năm',
            '1_2_years' => 'kinh nghiệm 2 năm',
            '3_to_5_years' => 'kinh nghiệm 3 năm, 5 năm kinh nghiệm',
            '3_5_years' => 'kinh nghiệm 3 năm, 5 năm kinh nghiệm',
            'over_5_years' => 'trên 5 năm kinh nghiệm, chuyên viên cấp cao'
        ];
        $eduMap = [
            'none' => 'không yêu cầu bằng cấp, lao động phổ thông',
            'high_school' => 'tốt nghiệp thpt',
            'vocational' => 'trung cấp',
            'college' => 'cao đẳng',
            'university' => 'đại học, cử nhân'
        ];
        if (!empty($expMap[$expReq])) {
            $jobRequirements .= ' ' . $expMap[$expReq];
        }
        if (!empty($eduMap[$eduLevel])) {
            $jobRequirements .= ' ' . $eduMap[$eduLevel];
        }

        $result = SalaryBenchmarkService::analyze($jobTitle, $employmentType, $jobRequirements, $workShift, $workplaceAddress);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Chi tiết bài đăng việc làm (Có tích hợp khoảng cách và phân tích AI chi tiết)
     */
    public function detail($id) {
        $job = JobPost::findById($id);
        if (!$job) {
            header('Location: ' . url('/jobs'));
            exit();
        }

        $aiFitAnalysis = null;
        $seeker = null;
        if (isset($_SESSION['user']) && $_SESSION['user']['role_code'] === 'JOB_SEEKER') {
            $seeker = UserSeeker::findByAccountId($_SESSION['user']['account_id']);
            if ($seeker) {
                $aiFitAnalysis = AIService::analyzeJobFit($seeker['seeker_id'], $id, false);
            }
        }

        // Tính khoảng cách từ vị trí tìm kiếm / vị trí của người dùng đến nơi làm việc
        $distanceInfo = null;
        $fromLocation = trim($_GET['location'] ?? $_SESSION['last_search_location'] ?? '');
        $fromLat = floatval($_GET['lat'] ?? $_SESSION['last_search_lat'] ?? 0);
        $fromLng = floatval($_GET['lng'] ?? $_SESSION['last_search_lng'] ?? 0);

        if ($fromLat == 0 && !empty($fromLocation)) {
            $res = GeoService::resolveCoordinates($fromLocation);
            if ($res) {
                $fromLat = $res['lat'];
                $fromLng = $res['lng'];
            }
        }

        if ($fromLat == 0 && $seeker && (!empty($seeker['preferred_location']) || !empty($seeker['address']))) {
            $fromLocation = $seeker['preferred_location'] ?: $seeker['address'];
            $res = GeoService::resolveCoordinates($fromLocation);
            if ($res) {
                $fromLat = $res['lat'];
                $fromLng = $res['lng'];
            }
        }

        if ($fromLat != 0 && $fromLng != 0) {
            $jobCoords = GeoService::getJobCoordinates($job);
            $dist = GeoService::calculateDistance($fromLat, $fromLng, $jobCoords['lat'], $jobCoords['lng']);
            $distanceInfo = [
                'distance_km' => $dist,
                'distance_text' => GeoService::formatDistance($dist),
                'from_location' => $fromLocation ?: 'Vị trí của bạn',
                'job_location' => $job['workplace_address'] ?? 'Bình Dương'
            ];
        }

        $employerReviews = Review::getByEmployer($job['employer_id']);
        $relatedJobs = array_slice(JobPost::allApproved(), 0, 3);
        $pageTitle = $job['job_title'] . ' - ' . $job['employer_name'];
        require_once __DIR__ . '/job_detail.php';
    }

    /**
     * Danh sách nhà tuyển dụng công khai
     */
    public function employers() {
        $employers = UserEmployer::all();
        $pageTitle = 'Danh Sách Nhà Tuyển Dụng Uy Tín - JobLink';
        require_once __DIR__ . '/employer_list.php';
    }

    /**
     * Chi tiết nhà tuyển dụng
     */
    public function employerDetail($id) {
        $employer = UserEmployer::findById($id);
        if (!$employer) {
            header('Location: ' . url('/employers'));
            exit();
        }
        $jobs = JobPost::getByEmployer($id);
        $reviews = Review::getByEmployer($id);
        $pageTitle = $employer['employer_name'] . ' - Hồ sơ Doanh Nghiệp';
        require_once __DIR__ . '/employer_detail.php';
    }

    /**
     * Giới thiệu
     */
    public function about() {
        $pageTitle = 'Giới Thiệu Về JobLink';
        require_once __DIR__ . '/about.php';
    }

    /**
     * Danh sách việc làm của Nhà tuyển dụng
     */
    public function employerJobs() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $jobs = JobPost::getByEmployer($employer['employer_id'] ?? 0);
        $pageTitle = 'Quản Lý Tin Tuyển Dụng - JobLink';
        require_once __DIR__ . '/employer_jobs.php';
    }

    /**
     * Quản lý người lao động riêng cho từng tin tuyển dụng & Danh sách AI đề xuất người lao động phù hợp
     */
    public function manageJobCandidates($jobPostId) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        
        $job = JobPost::findById(intval($jobPostId));
        if (!$job || intval($job['employer_id']) !== intval($employer['employer_id'] ?? 0)) {
            $_SESSION['flash_msg'] = 'Không tìm thấy tin tuyển dụng hoặc bạn không có quyền quản lý!';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . url('/employer/jobs'));
            exit();
        }

        // Chỉ tin tuyển dụng đã được duyệt (approved / Đang hiển thị) mới có thể xem người lao động gợi ý
        if (($job['post_status'] ?? '') !== 'approved') {
            $_SESSION['flash_msg'] = 'Tin tuyển dụng chưa được duyệt hoặc đã bị từ chối, không thể xem danh sách người lao động gợi ý!';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . url('/employer/jobs'));
            exit();
        }

        // 1. Danh sách người lao động đã nộp hồ sơ vào riêng tin này
        $applications = JobRequest::getByJobPost(intval($jobPostId));
        foreach ($applications as &$app) {
            $analysis = AIService::analyzeJobFit($app['seeker_id'], $jobPostId);
            if ($analysis) {
                $app['ai_match_score'] = $analysis['score'];
                $app['ai_match_level'] = $analysis['level'];
                $app['ai_match_label'] = $analysis['label'];
                $app['ai_matched_skills'] = $analysis['matched_skills'];
            }
        }
        unset($app);

        // 2. Danh sách AI đề xuất Người lao động phù hợp nhất (2 chiều)
        $aiRecommendations = AIService::matchCandidatesForJob(intval($jobPostId), 12);

        $pageTitle = 'Người Lao Động Gợi Ý: ' . $job['job_title'] . ' - JobLink';
        require_once __DIR__ . '/employer_job_candidates.php';
    }

    /**
     * Form tạo tin tuyển dụng mới (Employer)
     */
    public function create() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $pageTitle = 'Đăng Tin Tuyển Dụng Mới - JobLink';
        $job = null;
        require_once __DIR__ . '/employer_job_form.php';
    }

    /**
     * Lưu tin tuyển dụng mới
     */
    public function store() {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rawSalary = str_replace(['.', ',', ' '], '', $_POST['salary_amount'] ?? '0');
            
            // Xử lý chuẩn hóa thời gian làm việc từ 2 ô giờ (Bắt đầu - Kết thúc)
            $startTime = trim($_POST['shift_start_time'] ?? '');
            $endTime = trim($_POST['shift_end_time'] ?? '');
            $shiftCustomName = trim($_POST['shift_custom_name'] ?? '');
            if (!empty($startTime) && !empty($endTime)) {
                $timeRange = $startTime . ' - ' . $endTime;
                $finalWorkShift = !empty($shiftCustomName) ? ($shiftCustomName . ' (' . $timeRange . ')') : $timeRange;
            } else {
                $finalWorkShift = trim($_POST['work_shift'] ?? '');
            }

            // Xử lý tiêu chuẩn yêu cầu người lao động
            $noAgeLimit = !empty($_POST['no_age_limit']) ? 1 : 0;
            $minAge = (!$noAgeLimit && !empty($_POST['min_age'])) ? intval($_POST['min_age']) : null;
            $maxAge = (!$noAgeLimit && !empty($_POST['max_age'])) ? intval($_POST['max_age']) : null;
            $genderReq = trim($_POST['gender_requirement'] ?? 'all');
            $eduLevel = trim($_POST['education_level'] ?? 'none');
            $expReq = trim($_POST['experience_required'] ?? 'none');
            
            $otherReqArray = [];
            if (!empty($_POST['other_requirements']) && is_array($_POST['other_requirements'])) {
                $otherReqArray = array_values(array_filter(array_map('trim', $_POST['other_requirements'])));
            }
            $otherReqJson = !empty($otherReqArray) ? json_encode($otherReqArray, JSON_UNESCAPED_UNICODE) : null;

            JobPost::create([
                'employer_id' => $employer['employer_id'] ?? 0,
                'job_title' => trim($_POST['job_title']),
                'employment_type' => $_POST['employment_type'] ?? 'full_time',
                'work_shift' => $finalWorkShift,
                'working_days' => trim($_POST['working_days'] ?? ''),
                'job_requirements' => trim($_POST['job_requirements'] ?? ''),
                'min_age' => $minAge,
                'max_age' => $maxAge,
                'gender_requirement' => $genderReq,
                'education_level' => $eduLevel,
                'experience_required' => $expReq,
                'other_requirements' => $otherReqJson,
                'number_of_positions' => intval($_POST['number_of_positions'] ?? 1),
                'application_deadline' => $_POST['application_deadline'] ?? date('Y-m-d', strtotime('+30 days')),
                'salary_amount' => floatval($rawSalary),
                'workplace_address' => trim($_POST['workplace_address'] ?? ''),
                'workplace_map_url' => trim($_POST['workplace_map_url'] ?? ''),
                'additional_requirements' => trim($_POST['additional_requirements'] ?? '')
            ]);
            $_SESSION['flash_msg'] = 'Đăng tin thành công! Tin tuyển dụng đang chờ quản trị viên duyệt.';
        }
        header('Location: ' . url('/employer/jobs'));
        exit();
    }

    /**
     * Form chỉnh sửa tin tuyển dụng (Employer)
     */
    public function edit($id) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $job = JobPost::findById($id);

        if (!$job || $job['employer_id'] != ($employer['employer_id'] ?? 0)) {
            header('Location: ' . url('/employer/jobs'));
            exit();
        }
        $pageTitle = 'Chỉnh Sửa Tin Tuyển Dụng - JobLink';
        require_once __DIR__ . '/employer_job_form.php';
    }

    /**
     * Cập nhật tin tuyển dụng
     */
    public function update($id) {
        $user = AuthController::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rawSalary = str_replace(['.', ',', ' '], '', $_POST['salary_amount'] ?? '0');
            
            // Xử lý chuẩn hóa thời gian làm việc từ 2 ô giờ (Bắt đầu - Kết thúc)
            $startTime = trim($_POST['shift_start_time'] ?? '');
            $endTime = trim($_POST['shift_end_time'] ?? '');
            $shiftCustomName = trim($_POST['shift_custom_name'] ?? '');
            if (!empty($startTime) && !empty($endTime)) {
                $timeRange = $startTime . ' - ' . $endTime;
                $finalWorkShift = !empty($shiftCustomName) ? ($shiftCustomName . ' (' . $timeRange . ')') : $timeRange;
            } else {
                $finalWorkShift = trim($_POST['work_shift'] ?? '');
            }

            // Xử lý tiêu chuẩn yêu cầu người lao động
            $noAgeLimit = !empty($_POST['no_age_limit']) ? 1 : 0;
            $minAge = (!$noAgeLimit && !empty($_POST['min_age'])) ? intval($_POST['min_age']) : null;
            $maxAge = (!$noAgeLimit && !empty($_POST['max_age'])) ? intval($_POST['max_age']) : null;
            $genderReq = trim($_POST['gender_requirement'] ?? 'all');
            $eduLevel = trim($_POST['education_level'] ?? 'none');
            $expReq = trim($_POST['experience_required'] ?? 'none');
            
            $otherReqArray = [];
            if (!empty($_POST['other_requirements']) && is_array($_POST['other_requirements'])) {
                $otherReqArray = array_values(array_filter(array_map('trim', $_POST['other_requirements'])));
            }
            $otherReqJson = !empty($otherReqArray) ? json_encode($otherReqArray, JSON_UNESCAPED_UNICODE) : null;

            JobPost::update($id, [
                'job_title' => trim($_POST['job_title']),
                'employment_type' => $_POST['employment_type'] ?? 'full_time',
                'work_shift' => $finalWorkShift,
                'working_days' => trim($_POST['working_days'] ?? ''),
                'job_requirements' => trim($_POST['job_requirements'] ?? ''),
                'min_age' => $minAge,
                'max_age' => $maxAge,
                'gender_requirement' => $genderReq,
                'education_level' => $eduLevel,
                'experience_required' => $expReq,
                'other_requirements' => $otherReqJson,
                'number_of_positions' => intval($_POST['number_of_positions'] ?? 1),
                'application_deadline' => $_POST['application_deadline'] ?? null,
                'salary_amount' => floatval($rawSalary),
                'workplace_address' => trim($_POST['workplace_address'] ?? ''),
                'workplace_map_url' => trim($_POST['workplace_map_url'] ?? ''),
                'additional_requirements' => trim($_POST['additional_requirements'] ?? '')
            ]);
            $_SESSION['flash_msg'] = 'Cập nhật tin tuyển dụng thành công!';
        }
        header('Location: ' . url('/employer/jobs'));
        exit();
    }

    /**
     * Xóa tin tuyển dụng
     */
    public function delete($id) {
        AuthController::checkRole(['EMPLOYER_ADMIN']);
        JobPost::delete($id);
        $_SESSION['flash_msg'] = 'Đã xóa tin tuyển dụng!';
        header('Location: ' . url('/employer/jobs'));
        exit();
    }

    /**
     * Danh sách tin cho Admin duyệt (Admin)
     */
    public function adminList() {
        AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $jobs = JobPost::all();
        $pageTitle = 'Duyệt Tin Tuyển Dụng - JobLink Admin';
        require_once __DIR__ . '/admin_job_posts.php';
    }

    /**
     * Admin duyệt tin
     */
    public function approve($id) {
        AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $status = $_POST['status'] ?? 'approved';
        $reason = trim($_POST['rejection_reason'] ?? '');
        JobPost::updateStatus($id, $status);
        
        if ($status === 'approved') {
            $_SESSION['flash_msg'] = 'Đã duyệt tin tuyển dụng thành công!';
            $_SESSION['flash_type'] = 'success';
        } elseif ($status === 'rejected') {
            $_SESSION['flash_msg'] = 'Đã từ chối tin tuyển dụng!' . ($reason ? " (Lý do: {$reason})" : '');
            $_SESSION['flash_type'] = 'warning';
        } else {
            $_SESSION['flash_msg'] = 'Đã chuyển tin về trạng thái chờ duyệt!' . ($reason ? " (Lý do: {$reason})" : '');
            $_SESSION['flash_type'] = 'info';
        }

        header('Location: ' . url('/admin/job-posts'));
        exit();
    }

    /**
     * Admin từ chối tin
     */
    public function reject($id) {
        AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        JobPost::updateStatus($id, 'rejected');
        $_SESSION['flash_msg'] = 'Đã từ chối tin tuyển dụng!';
        header('Location: ' . url('/admin/job-posts'));
        exit();
    }

    /**
     * Admin xóa tin
     */
    public function adminDelete($id) {
        AuthController::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        JobPost::delete($id);
        $_SESSION['flash_msg'] = 'Đã xóa tin tuyển dụng thành công!';
        header('Location: ' . url('/admin/job-posts'));
        exit();
    }

    /**
     * API Gợi ý danh sách địa danh & KCN trọng điểm cho Autocomplete tìm kiếm
     */
    public function apiSuggestLocations() {
        header('Content-Type: application/json; charset=utf-8');
        $query = mb_strtolower(trim($_GET['q'] ?? ''));
        $locations = GeoService::getPopularLocations();

        if (!empty($query)) {
            $locations = array_values(array_filter($locations, function($loc) use ($query) {
                $name = mb_strtolower($loc['name'] ?? '');
                $group = mb_strtolower($loc['group'] ?? '');
                return (mb_strpos($name, $query) !== false) || (mb_strpos($group, $query) !== false);
            }));
        }

        echo json_encode([
            'success' => true,
            'locations' => array_slice($locations, 0, 10)
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

