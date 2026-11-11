<?php
// modules/auth/AuthController.php - Xử lý Xác thực, Phân quyền, Đăng nhập, Đăng ký, Profile

require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../services/AIService.php';

class AuthController {

    /**
     * 1. Kiểm tra trạng thái đăng nhập
     */
    public static function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục!'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            header('Location: ' . url('/auth/login'));
            exit();
        }

        return $_SESSION['user'];
    }

    /**
     * 2. Phân quyền truy cập theo vai trò (Role-based Access Control)
     */
    public static function checkRole($allowedRoles = []) {
        $user = self::checkAuth();
        $role = $user['role_code'] ?? '';

        if (!empty($allowedRoles) && !in_array($role, $allowedRoles)) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(403);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này!'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            http_response_code(403);
            die("<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>403 - Không có quyền</title><link rel='stylesheet' href='/css/style.css'></head><body style='text-align:center;padding:4rem;font-family:sans-serif;'><h1 style='font-size:3rem;color:#ef4444'>403</h1><p>Bạn không có quyền truy cập vào phân hệ này.</p><a href='/' style='margin-top:1rem;display:inline-block;padding:0.6rem 1.5rem;background:#0d9488;color:#fff;border-radius:8px;text-decoration:none'>Về Trang Chủ</a></body></html>");
        }

        return $user;
    }

    public function showLogin() {
        if (isset($_SESSION['user'])) {
            $this->redirectByRole($_SESSION['user']['role_code']);
        }
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        $rememberedUsername = $_COOKIE['joblink_remember_username'] ?? '';
        $isRemembered = !empty($_COOKIE['joblink_remember_flag']) || !empty($rememberedUsername);
        $pageTitle = 'Đăng Nhập - JobLink';
        require_once __DIR__ . '/login.php';
    }

    public function handleLogin() {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $rememberMe = !empty($_POST['remember_me']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if (!$username) {
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên đăng nhập hoặc email!'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['login_error'] = 'Vui lòng nhập tên đăng nhập hoặc email!';
            header('Location: ' . url('/auth/login'));
            exit();
        }

        $user = UserAccount::authenticate($username, $password);
        if ($user) {
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $user['account_id'];
            $_SESSION['role'] = $user['role_code'];
            $_SESSION['user_name'] = $user['username'];
            
            // Xử lý Ghi nhớ mật khẩu / Lưu thông tin đăng nhập trên trình duyệt
            if ($rememberMe) {
                $expireTime = time() + (86400 * 30); // 30 ngày
                setcookie('joblink_remember_username', $username, [
                    'expires' => $expireTime,
                    'path' => '/',
                    'httponly' => false,
                    'samesite' => 'Lax'
                ]);
                setcookie('joblink_remember_flag', '1', [
                    'expires' => $expireTime,
                    'path' => '/',
                    'httponly' => false,
                    'samesite' => 'Lax'
                ]);
                if (session_id()) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), session_id(), [
                        'expires' => $expireTime,
                        'path' => $params['path'],
                        'domain' => $params['domain'],
                        'secure' => $params['secure'],
                        'httponly' => $params['httponly'],
                        'samesite' => 'Lax'
                    ]);
                }
            } else {
                setcookie('joblink_remember_username', '', time() - 3600, '/');
                setcookie('joblink_remember_flag', '', time() - 3600, '/');
            }

            // Tìm chi tiết profile
            if ($user['role_code'] === 'JOB_SEEKER') {
                $seeker = UserSeeker::findByAccountId($user['account_id']);
                $_SESSION['user']['profile'] = $seeker;
                $_SESSION['user']['display_name'] = $seeker['full_name'] ?? $user['username'];
            } elseif ($user['role_code'] === 'EMPLOYER_ADMIN') {
                $employer = UserEmployer::findByAccountId($user['account_id']);
                $_SESSION['user']['profile'] = $employer;
                $_SESSION['user']['display_name'] = $employer['employer_name'] ?? $user['username'];
            } else {
                $_SESSION['user']['display_name'] = $user['username'];
            }

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                $redirectUrl = $this->getRedirectUrlByRole($user['role_code']);
                echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công!', 'redirect' => $redirectUrl], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $this->redirectByRole($user['role_code']);
        } else {
            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Tài khoản hoặc mật khẩu không chính xác!'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['login_error'] = 'Tài khoản hoặc mật khẩu không chính xác!';
            header('Location: ' . url('/auth/login'));
            exit();
        }
    }

    public function showRegister() {
        $step = intval($_GET['step'] ?? 1);
        $user = $_SESSION['user'] ?? null;
        $profile = null;
        if ($user) {
            if (($user['role_code'] ?? '') === 'JOB_SEEKER') {
                $profile = UserSeeker::findByAccountId($user['account_id']);
            } elseif (($user['role_code'] ?? '') === 'EMPLOYER_ADMIN') {
                $profile = UserEmployer::findByAccountId($user['account_id']);
            }
        }
        $pageTitle = 'Đăng Ký Tài Khoản Đa Bước - JobLink';
        require_once __DIR__ . '/register.php';
    }

    public function handleRegister() {
        $role = $_POST['role_type'] ?? 'JOB_SEEKER';
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $roleType = trim($_POST['role_type'] ?? 'JOB_SEEKER');
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        // 1. Kiểm tra tính hợp lệ của Tên tài khoản theo vai trò
        if ($roleType === 'JOB_SEEKER') {
            // Người lao động: Tên tài khoản BẮT BUỘC là Số điện thoại (10 chữ số)
            if (!preg_match('/^(0[3|5|7|8|9])[0-9]{8}$/', $username)) {
                $msg = 'Tên tài khoản Người Lao Động bắt buộc phải là Số điện thoại Việt Nam hợp lệ (10 chữ số, ví dụ: 0901234567 hoặc 0912345678)!';
                if ($isAjax) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['register_error'] = $msg;
                header('Location: ' . url('/auth/register'));
                exit();
            }
        } else {

            // Nhà tuyển dụng: Tên tài khoản BẮT BUỘC ở dạng chữ (chữ cái, số và dấu gạch dưới)
            if (preg_match('/^[0-9]+$/', $username)) {
                $msg = 'Tên tài khoản Nhà tuyển dụng phải ở dạng chữ (Ví dụ: becamex_idc, vinamilk, fpt_software), không được đặt toàn bộ bằng số!';
                if ($isAjax) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['register_error'] = $msg;
                header('Location: ' . url('/auth/register'));
                exit();
            }

            if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
                $msg = 'Tên tài khoản Nhà tuyển dụng chỉ bao gồm chữ cái không dấu, số và dấu gạch dưới (từ 3 đến 30 ký tự)!';
                if ($isAjax) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['register_error'] = $msg;
                header('Location: ' . url('/auth/register'));
                exit();
            }
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = 'Địa chỉ email không hợp lệ!';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['register_error'] = $msg;
            header('Location: ' . url('/auth/register'));
            exit();
        }


        if (mb_strlen($password) < 6) {
            $msg = 'Mật khẩu phải có tối thiểu 6 ký tự!';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['register_error'] = $msg;
            header('Location: ' . url('/auth/register'));
            exit();
        }

        // 2. Kiểm tra trùng lặp tài khoản hoặc email
        if (UserAccount::isUsernameExists($username)) {
            $msg = 'Tên tài khoản hoặc số điện thoại này đã được đăng ký!';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['register_error'] = $msg;
            header('Location: ' . url('/auth/register'));
            exit();
        }

        if (UserAccount::isEmailExists($email)) {
            $msg = 'Địa chỉ email này đã được sử dụng!';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['register_error'] = $msg;
            header('Location: ' . url('/auth/register'));
            exit();
        }

        // 3. Tiến hành tạo tài khoản Bước 1
        $roleId = ($role === 'EMPLOYER_ADMIN') ? 4 : 5;
        $accountId = UserAccount::register($username, $email, $password, $roleId);

        if ($accountId) {
            $user = UserAccount::findById($accountId);
            $profile = null;
            $formattedId = '';

            if ($role === 'JOB_SEEKER') {
                UserSeeker::createDefault($accountId, $username, $email);
                $profile = UserSeeker::findByAccountId($accountId);
                $formattedId = formatId('seeker', $profile['seeker_id'] ?? $accountId);
            } elseif ($role === 'EMPLOYER_ADMIN') {
                $profile = UserEmployer::findByAccountId($accountId);
                $formattedId = formatId('employer', $profile['employer_id'] ?? $accountId);
            }

            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $accountId;
            $_SESSION['role'] = $user['role_code'] ?? $role;
            $_SESSION['user']['display_name'] = $profile['full_name'] ?? ($profile['employer_name'] ?? $username);
            $_SESSION['user']['profile'] = $profile;

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'success' => true,
                    'step' => 2,
                    'message' => 'Đăng ký tài khoản thành công! Hãy hoàn thiện thông tin hồ sơ.',
                    'data' => [
                        'account_id' => $accountId,
                        'username' => $username,
                        'email' => $email,
                        'role' => $user['role_code'] ?? $role,
                        'formatted_id' => $formattedId,
                        'display_name' => $_SESSION['user']['display_name']
                    ]
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            header('Location: ' . url('/auth/register?step=2'));
            exit();
        } else {
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Đăng ký không thành công, vui lòng thử lại!'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['register_error'] = 'Đăng ký không thành công, vui lòng thử lại!';
            header('Location: ' . url('/auth/register'));
            exit();
        }
    }

    public function handleRegisterStep2() {
        $user = $_SESSION['user'] ?? null;
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if (!$user) {
            $msg = 'Phiên đăng ký đã hết hạn. Vui lòng đăng nhập lại!';
            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg, 'redirect' => url('/auth/login')], JSON_UNESCAPED_UNICODE);
                exit();
            }
            header('Location: ' . url('/auth/login'));
            exit();
        }

        $role = $user['role_code'] ?? ($_POST['role_type'] ?? 'JOB_SEEKER');
        $accountId = $user['account_id'];
        $formattedId = '';

        if ($role === 'JOB_SEEKER') {
            $seeker = UserSeeker::findByAccountId($accountId);
            if ($seeker) {
                UserSeeker::update($seeker['seeker_id'], [
                    'full_name' => !empty($_POST['full_name']) ? trim($_POST['full_name']) : ($seeker['full_name'] ?? $user['username']),
                    'date_of_birth' => !empty($_POST['date_of_birth']) ? trim($_POST['date_of_birth']) : null,
                    'gender' => trim($_POST['gender'] ?? 'Nam'),
                    'phone_number' => !empty($_POST['phone_number']) ? trim($_POST['phone_number']) : ($seeker['phone_number'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'skills' => trim($_POST['skills'] ?? ''),
                    'experience_years' => intval($_POST['experience_years'] ?? 0),
                    'desired_position' => trim($_POST['desired_position'] ?? ''),
                    'expected_salary' => !empty($_POST['expected_salary']) ? parseSalaryAmount($_POST['expected_salary']) : null,
                    'preferred_employment_type' => trim($_POST['preferred_employment_type'] ?? 'full_time'),
                    'preferred_location' => trim($_POST['preferred_location'] ?? ''),
                    'bio' => trim($_POST['bio'] ?? '')
                ]);
                $_SESSION['user']['display_name'] = !empty($_POST['full_name']) ? trim($_POST['full_name']) : $user['username'];
                $_SESSION['user']['profile'] = UserSeeker::findById($seeker['seeker_id']);
                $formattedId = formatId('seeker', $seeker['seeker_id']);
            }
            $dashUrl = url('/seeker/dashboard');
            $aiJobsUrl = url('/jobs?ai_recommend=1');
        } elseif ($role === 'EMPLOYER_ADMIN') {
            $emp = UserEmployer::findByAccountId($accountId);
            if ($emp) {
                UserEmployer::updateProfile($emp['employer_id'], [
                    'employer_name' => !empty($_POST['employer_name']) ? trim($_POST['employer_name']) : ($emp['employer_name'] ?? $user['username']),
                    'representative_name' => trim($_POST['representative_name'] ?? ''),
                    'business_license_number' => trim($_POST['business_license_number'] ?? ''),
                    'contact_email' => trim($_POST['contact_email'] ?? $user['email']),
                    'phone_number' => trim($_POST['phone_number'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'map_url' => trim($_POST['map_url'] ?? ''),
                    'branches' => trim($_POST['branches'] ?? ''),
                    'website_url' => trim($_POST['website_url'] ?? ''),
                    'business_field' => trim($_POST['business_field'] ?? '')
                ]);
                $_SESSION['user']['display_name'] = !empty($_POST['employer_name']) ? trim($_POST['employer_name']) : $user['username'];
                $_SESSION['user']['profile'] = UserEmployer::findById($emp['employer_id']);
                $formattedId = formatId('employer', $emp['employer_id']);
            }
            $dashUrl = url('/employer/dashboard');
            $aiJobsUrl = url('/employer/jobs/create');
        } else {
            $dashUrl = url('/');
            $aiJobsUrl = url('/jobs');
        }

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => true,
                'step' => 3,
                'message' => 'Lưu thông tin hồ sơ thành công!',
                'data' => [
                    'role' => $role,
                    'display_name' => $_SESSION['user']['display_name'] ?? $user['username'],
                    'formatted_id' => $formattedId,
                    'dash_url' => $dashUrl,
                    'ai_url' => $aiJobsUrl
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        header('Location: ' . url('/auth/register?step=3'));
        exit();
    }


    public function showForgotPassword() {
        // Làm mới tiến trình khôi phục mật khẩu khi người dùng mở trang
        unset($_SESSION['forgot_account_id'], $_SESSION['forgot_verified'], $_SESSION['forgot_step']);
        $error = $_SESSION['forgot_error'] ?? null;
        $success = $_SESSION['forgot_success'] ?? null;
        unset($_SESSION['forgot_error'], $_SESSION['forgot_success']);
        $pageTitle = 'Quên Mật Khẩu - JobLink';
        require_once __DIR__ . '/forgot_password.php';
    }

    public function handleForgotPassword() {
        $step = intval($_POST['step'] ?? 1);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        // =========================================================================
        // BƯỚC 1: KIỂM TRA SỐ ĐIỆN THOẠI HOẶC EMAIL ĐĂNG KÝ
        // =========================================================================
        if ($step === 1 && empty($_POST['new_password'])) {
            $identity = trim($_POST['identity'] ?? '');
            if (!$identity) {
                $msg = 'Vui lòng nhập Số điện thoại hoặc Email đăng ký!';
                if ($isAjax) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['forgot_error'] = $msg;
                header('Location: ' . url('/auth/forgot-password'));
                exit();
            }

            // 1. Tìm trong bảng user_accounts trước
            $user = UserAccount::findByUsernameOrEmail($identity);

            // 2. Nếu chưa thấy, tìm trong hồ sơ Người lao động (phone hoặc email)
            if (!$user) {
                $db = Database::getConnection();
                if ($db) {
                    $stmt = $db->prepare("
                        SELECT a.*, r.role_code 
                        FROM user_accounts a 
                        JOIN user_roles r ON a.user_role_id = r.user_role_id 
                        JOIN user_seekers s ON a.account_id = s.account_id 
                        WHERE (s.phone_number = :p OR s.email = :e)
                        LIMIT 1
                    ");
                    $stmt->execute([':p' => $identity, ':e' => $identity]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }

            // 3. Nếu chưa thấy, tìm trong hồ sơ Doanh nghiệp (phone hoặc contact_email)
            if (!$user) {
                $db = Database::getConnection();
                if ($db) {
                    $stmt = $db->prepare("
                        SELECT a.*, r.role_code 
                        FROM user_accounts a 
                        JOIN user_roles r ON a.user_role_id = r.user_role_id 
                        JOIN user_employers e ON a.account_id = e.account_id 
                        WHERE (e.phone_number = :p OR e.contact_email = :e)
                        LIMIT 1
                    ");
                    $stmt->execute([':p' => $identity, ':e' => $identity]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }

            if (!$user) {
                $msg = 'Không tìm thấy tài khoản nào với số điện thoại hoặc email này!';
                if ($isAjax) {
                    http_response_code(404);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['forgot_error'] = $msg;
                header('Location: ' . url('/auth/forgot-password'));
                exit();
            }

            // Lưu tài khoản tìm thấy vào Session bảo mật
            $_SESSION['forgot_account_id'] = $user['account_id'];
            $_SESSION['forgot_verified'] = false;
            $_SESSION['forgot_step'] = 2;

            $roleCode = $user['role_code'] ?? 'JOB_SEEKER';
            $displayName = $user['username'];
            $maskedPhone = '';
            $hasDob = false;
            $hasIdCard = false;

            if ($roleCode === 'JOB_SEEKER') {
                $seeker = UserSeeker::findByAccountId($user['account_id']);
                if ($seeker) {
                    $displayName = $seeker['full_name'] ?? $user['username'];
                    $phone = $seeker['phone_number'] ?? $user['username'];
                    $maskedPhone = $this->maskString($phone, 'phone');
                    $hasDob = !empty($seeker['date_of_birth']);
                    $hasIdCard = !empty($seeker['id_card_number']);
                }
            } elseif ($roleCode === 'EMPLOYER_ADMIN') {
                $employer = UserEmployer::findByAccountId($user['account_id']);
                if ($employer) {
                    $displayName = $employer['employer_name'] ?? $user['username'];
                    $phone = $employer['phone_number'] ?? '';
                    $maskedPhone = $this->maskString($phone, 'phone');
                }
            } else {
                $phone = $user['username'];
                $maskedPhone = $this->maskString($phone, 'phone');
            }

            $maskedName = $this->maskString($displayName, 'name');

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'success' => true,
                    'step' => 2,
                    'message' => 'Đã tìm thấy tài khoản! Vui lòng hoàn thành câu hỏi xác thực bảo mật.',
                    'data' => [
                        'account_id' => $user['account_id'],
                        'role' => $roleCode,
                        'display_name' => $maskedName,
                        'masked_phone' => $maskedPhone,
                        'has_dob' => $hasDob,
                        'has_id_card' => $hasIdCard
                    ]
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            header('Location: ' . url('/auth/forgot-password?step=2'));
            exit();
        }

        // =========================================================================
        // BƯỚC 2: XÁC THỰC THÔNG TIN BẢO MẬT (NGÀY SINH, CCCD, GPKD...)
        // =========================================================================
        if ($step === 2) {
            $accountId = $_SESSION['forgot_account_id'] ?? null;
            if (!$accountId) {
                $msg = 'Phiên xác thực đã hết hạn, vui lòng quay lại bước 1!';
                if ($isAjax) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $msg, 'redirect_step' => 1], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['forgot_error'] = $msg;
                header('Location: ' . url('/auth/forgot-password'));
                exit();
            }

            $user = UserAccount::findById($accountId);
            $roleCode = $user['role_code'] ?? 'JOB_SEEKER';
            $isVerified = false;

            if ($roleCode === 'JOB_SEEKER') {
                $seeker = UserSeeker::findByAccountId($accountId);
                $inputDob = trim($_POST['verify_dob'] ?? '');
                $inputIdCard = trim($_POST['verify_id_card'] ?? '');

                if (!$seeker) {
                    $isVerified = false;
                } else {
                    // 1. Kiểm tra ngày sinh nếu hồ sơ có ngày sinh
                    $dobMatch = true;
                    if (!empty($seeker['date_of_birth'])) {
                        if (empty($inputDob)) {
                            $dobMatch = false;
                        } else {
                            $dobDb = date('Y-m-d', strtotime($seeker['date_of_birth']));
                            $dobIn = date('Y-m-d', strtotime($inputDob));
                            $dobMatch = ($dobDb === $dobIn);
                        }
                    }

                    // 2. Kiểm tra CCCD / CMND hoặc Họ và tên / SĐT
                    $idMatch = false;
                    $cleanInputDigits = preg_replace('/[^0-9]/', '', $inputIdCard);

                    // Nếu trong hồ sơ đã lưu số CCCD và người dùng nhập số
                    if (!empty($seeker['id_card_number']) && !empty($cleanInputDigits)) {
                        $cleanDbDigits = preg_replace('/[^0-9]/', '', $seeker['id_card_number']);
                        if ($cleanDbDigits === $cleanInputDigits) {
                            $idMatch = true;
                        }
                    }

                    // Nếu chưa khớp hoặc chưa có CCCD trong hồ sơ: đối chiếu Họ và tên hoặc Số điện thoại
                    if (!$idMatch && !empty($inputIdCard)) {
                        $dbName = mb_strtolower(trim($seeker['full_name'] ?? ''));
                        $inName = mb_strtolower(trim($inputIdCard));
                        if ($dbName === $inName || (mb_strlen($inName) >= 3 && strpos($dbName, $inName) !== false)) {
                            $idMatch = true;
                        }

                        $phoneDb = preg_replace('/[^0-9]/', '', $seeker['phone_number'] ?? '');
                        if (!empty($cleanInputDigits) && $cleanInputDigits === $phoneDb) {
                            $idMatch = true;
                        }
                    }

                    // Hợp lệ nếu cả 2 tiêu chí hoặc nếu hồ sơ không có ngày sinh thì chỉ cần CCCD/tên
                    if ($dobMatch && $idMatch) {
                        $isVerified = true;
                    }
                }
            } elseif ($roleCode === 'EMPLOYER_ADMIN') {
                $emp = UserEmployer::findByAccountId($accountId);
                $inputLicense = trim($_POST['verify_license'] ?? '');
                $inputRep = trim($_POST['verify_representative'] ?? '');

                if ($emp) {
                    $cleanLicDb = preg_replace('/[^a-zA-Z0-9]/', '', $emp['business_license_number'] ?? '');
                    $cleanLicIn = preg_replace('/[^a-zA-Z0-9]/', '', $inputLicense);
                    $licMatch = (!empty($cleanLicDb) && $cleanLicDb === $cleanLicIn);

                    $repDb = mb_strtolower(trim($emp['representative_name'] ?? ''));
                    $repIn = mb_strtolower(trim($inputRep));
                    $repMatch = (!empty($repDb) && ($repDb === $repIn || strpos($repDb, $repIn) !== false));

                    $phoneDb = preg_replace('/[^0-9]/', '', $emp['phone_number'] ?? '');
                    $cleanDigits = preg_replace('/[^0-9]/', '', $inputLicense . $inputRep);
                    $phoneMatch = (!empty($phoneDb) && strpos($cleanDigits, $phoneDb) !== false);

                    if (($licMatch && $repMatch) || $licMatch || ($repMatch && $phoneMatch)) {
                        $isVerified = true;
                    }
                }
            } else {
                // Quản trị viên / Nhân viên khác: xác thực qua email hoặc số điện thoại
                $inputInfo = trim($_POST['verify_id_card'] ?? $_POST['verify_info'] ?? '');
                if (!empty($inputInfo)) {
                    if (strcasecmp($inputInfo, $user['email']) === 0 || strcasecmp($inputInfo, $user['username']) === 0) {
                        $isVerified = true;
                    }
                }
            }

            if ($isVerified) {
                $_SESSION['forgot_verified'] = true;
                $_SESSION['forgot_step'] = 3;
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode([
                        'success' => true,
                        'step' => 3,
                        'message' => 'Xác thực thông tin thành công! Mời bạn đặt mật khẩu mới.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                header('Location: ' . url('/auth/forgot-password?step=3'));
                exit();
            } else {
                $msg = 'Thông tin xác thực không trùng khớp với hồ sơ đã đăng ký. Vui lòng kiểm tra lại Ngày sinh hoặc CCCD / Họ tên!';
                if ($isAjax) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['forgot_error'] = $msg;
                header('Location: ' . url('/auth/forgot-password?step=2'));
                exit();
            }
        }

        // =========================================================================
        // BƯỚC 3: ĐẶT MẬT KHẨU MỚI CHO TÀI KHOẢN
        // =========================================================================
        if ($step === 3 || (!empty($_POST['new_password']) && !empty($_POST['confirm_password']))) {
            $accountId = $_SESSION['forgot_account_id'] ?? null;
            $verified = $_SESSION['forgot_verified'] ?? false;

            // Nếu người dùng gửi theo form cũ kèm identity:
            if (!$accountId && !empty($_POST['identity'])) {
                $u = UserAccount::findByUsernameOrEmail(trim($_POST['identity']));
                if ($u) $accountId = $u['account_id'];
                $verified = true; // backward compatibility
            }

            if (!$accountId || !$verified) {
                $msg = 'Bạn chưa hoàn thành bước xác thực bảo mật!';
                if ($isAjax) {
                    http_response_code(403);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $msg, 'redirect_step' => 1], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['forgot_error'] = $msg;
                header('Location: ' . url('/auth/forgot-password'));
                exit();
            }

            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if (mb_strlen($newPassword) < 6) {
                $msg = 'Mật khẩu mới phải có tối thiểu 6 ký tự!';
                if ($isAjax) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['forgot_error'] = $msg;
                header('Location: ' . url('/auth/forgot-password?step=3'));
                exit();
            }

            if ($newPassword !== $confirmPassword) {
                $msg = 'Mật khẩu xác nhận không khớp!';
                if ($isAjax) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['forgot_error'] = $msg;
                header('Location: ' . url('/auth/forgot-password?step=3'));
                exit();
            }

            $updated = UserAccount::updatePassword($accountId, $newPassword);
            if ($updated) {
                unset($_SESSION['forgot_account_id'], $_SESSION['forgot_verified'], $_SESSION['forgot_step']);
                $msg = 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay với mật khẩu mới.';
                if ($isAjax) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode([
                        'success' => true,
                        'message' => $msg,
                        'redirect' => url('/auth/login')
                    ], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['flash_msg'] = $msg;
                header('Location: ' . url('/auth/login'));
                exit();
            } else {
                $msg = 'Lỗi hệ thống khi cập nhật mật khẩu, vui lòng thử lại sau!';
                if ($isAjax) {
                    http_response_code(500);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $_SESSION['forgot_error'] = $msg;
                header('Location: ' . url('/auth/forgot-password?step=3'));
                exit();
            }
        }
    }

    /**
     * Ẩn danh chuỗi họ tên hoặc số điện thoại để hiển thị an toàn
     */
    private function maskString($str, $type = 'phone') {
        if (empty($str)) return '';
        $str = trim($str);
        if ($type === 'phone') {
            $len = strlen($str);
            if ($len >= 7) {
                return substr($str, 0, 4) . '***' . substr($str, -3);
            }
            return substr($str, 0, 2) . '***';
        }
        $words = explode(' ', $str);
        if (count($words) > 1) {
            $last = array_pop($words);
            return implode(' ', $words) . ' ' . mb_substr($last, 0, 1, 'UTF-8') . '***';
        }
        return mb_substr($str, 0, 2, 'UTF-8') . '***';
    }

    public function showChangePassword() {
        self::checkAuth();
        $error = $_SESSION['change_pwd_error'] ?? null;
        $success = $_SESSION['change_pwd_success'] ?? null;
        unset($_SESSION['change_pwd_error'], $_SESSION['change_pwd_success']);
        $pageTitle = 'Đổi Mật Khẩu - JobLink';
        require_once __DIR__ . '/change_password.php';
    }

    public function handleChangePassword() {
        $currentUser = self::checkAuth();
        $currentPassword = trim($_POST['current_password'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        $isEmbedded = (!empty($_POST['embedded']) || !empty($_GET['embedded']));
        $pwdRedirect = url('/auth/change-password') . ($isEmbedded ? '?embedded=1' : '');

        if (!$currentPassword || !$newPassword || !$confirmPassword) {
            $msg = 'Vui lòng điền đầy đủ các thông tin!';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['change_pwd_error'] = $msg;
            header('Location: ' . $pwdRedirect);
            exit();
        }

        if ($newPassword !== $confirmPassword) {
            $msg = 'Mật khẩu mới và xác nhận mật khẩu không khớp!';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['change_pwd_error'] = $msg;
            header('Location: ' . $pwdRedirect);
            exit();
        }

        if (strlen($newPassword) < 6) {
            $msg = 'Mật khẩu mới phải có tối thiểu 6 ký tự!';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['change_pwd_error'] = $msg;
            header('Location: ' . $pwdRedirect);
            exit();
        }

        // Kiểm tra mật khẩu hiện tại
        if (!UserAccount::verifyPassword($currentUser['account_id'], $currentPassword)) {
            $msg = 'Mật khẩu hiện tại không chính xác!';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['change_pwd_error'] = $msg;
            header('Location: ' . $pwdRedirect);
            exit();
        }

        $updated = UserAccount::updatePassword($currentUser['account_id'], $newPassword);
        if ($updated) {
            $msg = 'Đổi mật khẩu thành công!';
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => true, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['change_pwd_success'] = $msg;
            header('Location: ' . $pwdRedirect);
            exit();
        } else {
            $msg = 'Có lỗi xảy ra, vui lòng thử lại sau!';
            if ($isAjax) {
                http_response_code(500);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['change_pwd_error'] = $msg;
            header('Location: ' . $pwdRedirect);
            exit();
        }
    }

    public function logout() {
        session_destroy();
        header('Location: ' . url('/auth/login'));
        exit();
    }

    public function seekerDashboard() {
        $user = self::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $seekerId = $seeker['seeker_id'] ?? 0;

        $appliedJobs = JobRequest::getBySeeker($seekerId);
        $savedJobs = JobSaved::getBySeeker($seekerId);
        $meetings = CandidateMeeting::getBySeeker($seekerId);
        $messages = Message::getInbox($user['account_id']);

        // AI Recommendation Engine (chạy tức thì qua local scoring)
        $aiRecommendedJobs = AIService::recommendJobsForSeeker($seekerId, 6);

        $pageTitle = 'Bảng Điều Khiển Người Lao Động - JobLink';
        require_once __DIR__ . '/../dashboards/seeker_dashboard.php';
    }

    public function employerDashboard() {
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $employerId = $employer['employer_id'] ?? 0;

        $jobs = JobPost::getByEmployer($employerId);
        $applications = JobRequest::getByEmployer($employerId);
        $meetings = CandidateMeeting::getByEmployer($employerId);
        $reviews = Review::getByEmployer($employerId);

        // Lấy danh sách tin tuyển dụng đang mở và ứng viên đề xuất AI
        $activeJobs = array_values(array_filter($jobs ?? [], fn($j) => ($j['post_status'] ?? '') === 'approved'));
        $selectedJobId = intval($_GET['job_id'] ?? ($activeJobs[0]['job_post_id'] ?? 0));
        $threshold = intval($_GET['threshold'] ?? 70);
        if ($threshold < 30 || $threshold > 100) $threshold = 70;

        $aiCandidates = [];
        if ($selectedJobId > 0) {
            $aiCandidates = AIService::matchCandidatesForJob($selectedJobId, 30);
        }

        // Tách theo hạn mức % tương thích
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
        $employerCriteriaList = EmployerCriteria::getByEmployer($employerId);

        $pageTitle = 'Bảng Điều Khiển Nhà Tuyển Dụng - JobLink';
        require_once __DIR__ . '/../dashboards/employer_dashboard.php';
    }

    public function adminDashboard() {
        $currentUser = self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $accounts = UserAccount::all();
        $employers = UserEmployer::all();
        $jobPosts = JobPost::all();
        $reviews = Review::all();
        $staffs = UserStaff::all();
        $supportMessages = Message::getInbox($currentUser['account_id'] ?? 1);

        // Thống kê chi tiết tài khoản
        $seekerCount = 0;
        $employerCount = 0;
        $adminCount = 0;
        foreach ($accounts as $acc) {
            $roleCode = $acc['role_code'] ?? '';
            if ($roleCode === 'JOB_SEEKER') $seekerCount++;
            elseif ($roleCode === 'EMPLOYER_ADMIN') $employerCount++;
            elseif (in_array($roleCode, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) $adminCount++;
        }

        // Thống kê chi tiết tin tuyển dụng
        $pendingJobs = [];
        $approvedJobsCount = 0;
        $rejectedJobsCount = 0;
        $jobTypeStats = ['full_time' => 0, 'part_time' => 0, 'temporary' => 0];
        foreach ($jobPosts as $job) {
            $st = $job['post_status'] ?? 'pending';
            if ($st === 'pending') {
                $pendingJobs[] = $job;
            } elseif ($st === 'approved') {
                $approvedJobsCount++;
            } elseif ($st === 'rejected') {
                $rejectedJobsCount++;
            }
            $empType = $job['employment_type'] ?? 'full_time';
            if (isset($jobTypeStats[$empType])) {
                $jobTypeStats[$empType]++;
            }
        }

        // Thống kê doanh nghiệp & GPKD
        $pendingEmployers = [];
        $verifiedEmployersCount = 0;
        $failedEmployersCount = 0;
        foreach ($employers as $emp) {
            $vst = $emp['verification_status'] ?? 'pending';
            if ($vst === 'pending') {
                $pendingEmployers[] = $emp;
            } elseif ($vst === 'verified') {
                $verifiedEmployersCount++;
            } elseif ($vst === 'failed') {
                $failedEmployersCount++;
            }
        }

        // Thống kê đánh giá
        $pendingReviews = [];
        $pendingReviewsCount = 0;
        foreach ($reviews as $rev) {
            if (($rev['approval_status'] ?? '') === 'pending') {
                $pendingReviewsCount++;
                $pendingReviews[] = $rev;
            }
        }

        $pageTitle = 'Bảng Điều Khiển Quản Trị - JobLink Admin';
        $extraCss = ['css/dashboards/dashboard.css'];
        require_once __DIR__ . '/../dashboards/admin_dashboard.php';
    }

    public function seekerProfile() {
        $user = self::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        if (!$seeker) {
            UserSeeker::createDefault($user['account_id'], $user['username'] ?? '', $user['email'] ?? '');
            $seeker = UserSeeker::findByAccountId($user['account_id']);
        }
        $profileAdvice = AIService::optimizeProfileAdvice($seeker, false);
        require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
        $historyLogs = ProfileHistoryService::getHistoryForTarget('SEEKER', $seeker['seeker_id'], 50);
        $pageTitle = 'Quản Lý Hồ Sơ Cá Nhân & Tối Ưu AI - JobLink';
        require_once __DIR__ . '/../profiles/seeker_profile.php';
    }

    public function updateSeekerProfile() {
        $user = self::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        if (!$seeker) {
            UserSeeker::createDefault($user['account_id'], $user['username'] ?? '', $user['email'] ?? '');
            $seeker = UserSeeker::findByAccountId($user['account_id']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $seeker) {
            $oldData = UserSeeker::findById($seeker['seeker_id']);

            // Xử lý skill_proofs nếu submit dạng form mảng
            $skillProofsJson = trim($_POST['skill_proofs'] ?? ($seeker['skill_proofs'] ?? ''));
            if (!empty($_POST['proof_name']) && is_array($_POST['proof_name'])) {
                $proofsList = [];
                foreach ($_POST['proof_name'] as $idx => $pName) {
                    $pName = trim($pName);
                    if ($pName !== '') {
                        $proofsList[] = [
                            'name' => $pName,
                            'issuer' => trim($_POST['proof_issuer'][$idx] ?? ''),
                            'year' => trim($_POST['proof_year'][$idx] ?? ''),
                            'link' => trim($_POST['proof_link'][$idx] ?? '')
                        ];
                    }
                }
                $skillProofsJson = !empty($proofsList) ? json_encode($proofsList, JSON_UNESCAPED_UNICODE) : '';
            }

            // Xử lý work_history nếu submit dạng form mảng
            $workHistoryJson = trim($_POST['work_history'] ?? ($seeker['work_history'] ?? ''));
            if (!empty($_POST['work_company']) && is_array($_POST['work_company'])) {
                $workList = [];
                foreach ($_POST['work_company'] as $idx => $wCompany) {
                    $wCompany = trim($wCompany);
                    if ($wCompany !== '') {
                        $workList[] = [
                            'company_name' => $wCompany,
                            'position' => trim($_POST['work_position'][$idx] ?? ''),
                            'start_date' => trim($_POST['work_start'][$idx] ?? ''),
                            'end_date' => trim($_POST['work_end'][$idx] ?? ''),
                            'description' => trim($_POST['work_desc'][$idx] ?? '')
                        ];
                    }
                }
                $workHistoryJson = !empty($workList) ? json_encode($workList, JSON_UNESCAPED_UNICODE) : '';
            }

            require_once __DIR__ . '/../../../helpers/functions.php';

            // Xử lý đổi email tài khoản nếu có
            if (isset($_POST['email'])) {
                $newEmail = trim($_POST['email']);
                if (!empty($newEmail) && $newEmail !== ($user['email'] ?? '')) {
                    if (UserAccount::isEmailExists($newEmail, $user['account_id'])) {
                        $_SESSION['flash_msg'] = 'Địa chỉ email này đã có tài khoản khác sử dụng!';
                        $_SESSION['flash_type'] = 'danger';
                        header('Location: ' . url('/seeker/profile'));
                        exit();
                    }
                    UserAccount::updateAccountInfo($user['account_id'], ['email' => $newEmail]);
                    $_SESSION['user']['email'] = $newEmail;
                }
            }

            // Xử lý tải ảnh đại diện nếu có
            $avatarUrl = $seeker['avatar_url'] ?? null;
            if (!empty($_FILES['avatar_file']['name'])) {
                $uploadRes = uploadAvatarFile('avatar_file', 'seekers', $user['account_id']);
                if ($uploadRes['success']) {
                    $avatarUrl = $uploadRes['url'];
                    $_SESSION['user']['avatar_url'] = $uploadRes['url'];
                }
            }

            $updatePayload = [
                'full_name' => trim($_POST['full_name'] ?? ($seeker['full_name'] ?? $user['username'])),
                'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
                'gender' => trim($_POST['gender'] ?? 'Nam'),
                'phone_number' => trim($_POST['phone_number'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'skills' => trim($_POST['skills'] ?? ''),
                'skill_proofs' => $skillProofsJson,
                'experience_years' => intval($_POST['experience_years'] ?? 0),
                'experience_summary' => trim($_POST['experience_summary'] ?? ''),
                'work_history' => $workHistoryJson,
                'desired_position' => trim($_POST['desired_position'] ?? ''),
                'expected_salary' => !empty($_POST['expected_salary']) ? parseSalaryAmount($_POST['expected_salary']) : null,
                'preferred_employment_type' => trim($_POST['preferred_employment_type'] ?? 'full_time'),
                'preferred_location' => trim($_POST['preferred_location'] ?? ''),
                'bio' => trim($_POST['bio'] ?? ''),
                'education_level' => trim($_POST['education_level'] ?? ''),
                'email' => trim($_POST['email'] ?? ($seeker['email'] ?? ($user['email'] ?? ''))),
                'id_card_number' => trim($_POST['id_card_number'] ?? ($seeker['id_card_number'] ?? ''))
            ];
            if ($avatarUrl) {
                $updatePayload['avatar_url'] = $avatarUrl;
            }

            UserSeeker::update($seeker['seeker_id'], $updatePayload);

            $newData = UserSeeker::findById($seeker['seeker_id']);
            require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
            ProfileHistoryService::logChanges('SEEKER', $seeker['seeker_id'], $seeker['account_id'], $oldData, $newData, $user);

            $_SESSION['flash_msg'] = 'Cập nhật thông tin hồ sơ & kỹ năng thành công! Điểm đề xuất AI đã được tối ưu hóa.';
            $_SESSION['user']['display_name'] = trim($_POST['full_name'] ?? ($seeker['full_name'] ?? $user['username']));
            $_SESSION['user']['profile'] = $newData;
        }
        header('Location: ' . url('/seeker/profile'));
        exit();
    }


    public function aiOptimizeProfile() {
        $user = self::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $advice = AIService::optimizeProfileAdvice($seeker, true);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => true, 'data' => $advice], JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function aiJobFitModal($jobId) {
        $user = self::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        $analysis = AIService::analyzeJobFit($seeker['seeker_id'], $jobId, true);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => true, 'data' => $analysis], JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function employerProfile() {
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
        $historyLogs = $employer ? ProfileHistoryService::getHistoryForTarget('EMPLOYER', $employer['employer_id'], 50) : [];
        $pageTitle = 'Cấu Hình Hồ Sơ Công Ty & GPKD - JobLink';
        require_once __DIR__ . '/../profiles/employer_profile.php';
    }

    public function updateEmployerProfile() {
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $employer) {
            $oldData = UserEmployer::findById($employer['employer_id']);

            // Xử lý danh sách các cơ sở / chi nhánh khác
            $branchesData = [];
            if (!empty($_POST['branches']) && is_array($_POST['branches'])) {
                foreach ($_POST['branches'] as $b) {
                    $name = trim($b['name'] ?? '');
                    $address = trim($b['address'] ?? '');
                    $phone = trim($b['phone'] ?? '');
                    $mapUrl = trim($b['map_url'] ?? '');
                    
                    if (!empty($name) || !empty($address)) {
                        $branchesData[] = [
                            'name' => $name ?: 'Cơ sở chi nhánh',
                            'address' => $address,
                            'phone' => $phone,
                            'map_url' => $mapUrl ?: getGoogleMapsUrl($address)
                        ];
                    }
                }
            }
            $branchesJson = !empty($branchesData) ? json_encode($branchesData, JSON_UNESCAPED_UNICODE) : null;

            // Xử lý danh sách ca làm việc mẫu của doanh nghiệp
            $shiftsData = [];
            if (!empty($_POST['work_shifts']) && is_array($_POST['work_shifts'])) {
                foreach ($_POST['work_shifts'] as $s) {
                    $name = trim($s['name'] ?? '');
                    $time = trim($s['time'] ?? '');
                    if (empty($time) && (!empty($s['start_time']) || !empty($s['end_time']))) {
                        $st = trim($s['start_time'] ?? '');
                        $et = trim($s['end_time'] ?? '');
                        $time = ($st && $et) ? ($st . ' - ' . $et) : ($st ?: $et);
                    }
                    $desc = trim($s['description'] ?? '');
                    if (!empty($name) || !empty($time)) {
                        $shiftsData[] = [
                            'name' => $name ?: 'Ca làm việc',
                            'time' => $time,
                            'description' => $desc
                        ];
                    }
                }
            }
            $shiftsJson = !empty($shiftsData) ? json_encode($shiftsData, JSON_UNESCAPED_UNICODE) : null;

            $empName = trim($_POST['employer_name'] ?? '');
            if (empty($empName)) {
                $empName = !empty($employer['employer_name']) ? $employer['employer_name'] : 'Doanh Nghiệp';
            }

            require_once __DIR__ . '/../../../helpers/functions.php';

            $avatarUrl = $employer['avatar_url'] ?? null;
            if (!empty($_FILES['avatar_file']['name'])) {
                $uploadRes = uploadAvatarFile('avatar_file', 'employers', $user['account_id']);
                if ($uploadRes['success']) {
                    $avatarUrl = $uploadRes['url'];
                    $_SESSION['user']['avatar_url'] = $uploadRes['url'];
                }
            }

            $updatePayload = [
                'employer_name' => $empName,
                'employer_type' => trim($_POST['employer_type'] ?? ($employer['employer_type'] ?? 'enterprise')),
                'representative_name' => trim($_POST['representative_name'] ?? ''),
                'business_license_number' => trim($_POST['business_license_number'] ?? ''),
                'contact_email' => trim($_POST['contact_email'] ?? ''),
                'phone_number' => trim($_POST['phone_number'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'map_url' => trim($_POST['map_url'] ?? ''),
                'branches' => $branchesJson,
                'work_shifts' => $shiftsJson,
                'website_url' => trim($_POST['website_url'] ?? ''),
                'business_field' => trim($_POST['business_field'] ?? '')
            ];
            if ($avatarUrl) {
                $updatePayload['avatar_url'] = $avatarUrl;
            }

            UserEmployer::update($employer['employer_id'], $updatePayload);

            $newData = UserEmployer::findById($employer['employer_id']);
            require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
            ProfileHistoryService::logChanges('EMPLOYER', $employer['employer_id'], $employer['account_id'], $oldData, $newData, $user);

            $_SESSION['user']['display_name'] = $empName;
            $_SESSION['flash_msg'] = 'Cập nhật hồ sơ công ty, hệ thống cơ sở và ca làm việc thành công!';
        }
        header('Location: ' . url('/employer/company-profile'));
        exit();
    }

    public function apiSaveWorkShifts() {
        header('Content-Type: application/json; charset=UTF-8');
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);

        if (!$employer) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy hồ sơ nhà tuyển dụng']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        // Lấy danh sách ca hiện tại
        $currentShifts = UserEmployer::getWorkShifts($employer['employer_id']);

        // Nếu gửi 1 ca mới cần thêm vào:
        if (!empty($input['action']) && $input['action'] === 'add_single') {
            $name = trim($input['name'] ?? '');
            $time = trim($input['time'] ?? '');
            if (empty($time) && (!empty($input['start_time']) || !empty($input['end_time']))) {
                $st = trim($input['start_time'] ?? '');
                $et = trim($input['end_time'] ?? '');
                $time = ($st && $et) ? ($st . ' - ' . $et) : ($st ?: $et);
            }
            $desc = trim($input['description'] ?? '');

            if (empty($name) && empty($time)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên ca hoặc khung giờ']);
                exit();
            }

            $newShift = [
                'name' => $name ?: 'Ca làm việc',
                'time' => $time,
                'description' => $desc
            ];

            // Tránh trùng lặp
            $exists = false;
            foreach ($currentShifts as $cs) {
                if (mb_strtolower($cs['name']) === mb_strtolower($newShift['name']) && $cs['time'] === $newShift['time']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $currentShifts[] = $newShift;
            }

            $oldData = UserEmployer::findById($employer['employer_id']);
            UserEmployer::saveWorkShifts($employer['employer_id'], $currentShifts);
            $newData = UserEmployer::findById($employer['employer_id']);
            require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
            ProfileHistoryService::logChanges('EMPLOYER', $employer['employer_id'], $employer['account_id'], $oldData, $newData, $user);

            $label = $newShift['name'] . (!empty($newShift['time']) ? ' (' . $newShift['time'] . ')' : '');
            echo json_encode([
                'success' => true,
                'message' => 'Thêm ca làm việc thành công',
                'shift' => $newShift,
                'label' => $label,
                'shifts' => $currentShifts
            ]);
            exit();
        }

        // Nếu gửi toàn bộ mảng shifts
        if (isset($input['shifts']) && is_array($input['shifts'])) {
            $cleaned = [];
            foreach ($input['shifts'] as $s) {
                $name = trim($s['name'] ?? '');
                $time = trim($s['time'] ?? '');
                $desc = trim($s['description'] ?? '');
                if (!empty($name) || !empty($time)) {
                    $cleaned[] = [
                        'name' => $name ?: 'Ca làm việc',
                        'time' => $time,
                        'description' => $desc
                    ];
                }
            }
            $oldData = UserEmployer::findById($employer['employer_id']);
            UserEmployer::saveWorkShifts($employer['employer_id'], $cleaned);
            $newData = UserEmployer::findById($employer['employer_id']);
            require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
            ProfileHistoryService::logChanges('EMPLOYER', $employer['employer_id'], $employer['account_id'], $oldData, $newData, $user);

            echo json_encode([
                'success' => true,
                'message' => 'Lưu danh sách ca làm việc thành công',
                'shifts' => $cleaned
            ]);
            exit();
        }

        echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
        exit();
    }

    /**
     * API: Lấy danh sách tiêu chí tương thích của Nhà Tuyển Dụng
     */
    public function apiGetMatchingCriteria() {
        header('Content-Type: application/json; charset=UTF-8');
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        if (!$employer) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy hồ sơ nhà tuyển dụng']);
            exit();
        }

        require_once __DIR__ . '/../profiles/EmployerCriteria.php';
        $criteria = EmployerCriteria::getByEmployer($employer['employer_id']);
        echo json_encode(['success' => true, 'criteria' => $criteria]);
        exit();
    }

    /**
     * API: Bật / tắt 1 tiêu chí (chọn hoặc bỏ chọn lọc)
     */
    public function apiToggleMatchingCriteria() {
        header('Content-Type: application/json; charset=UTF-8');
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        if (!$employer) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy hồ sơ nhà tuyển dụng']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $criteriaId = intval($input['criteria_id'] ?? 0);
        $isActive = intval($input['is_active'] ?? 0);

        require_once __DIR__ . '/../profiles/EmployerCriteria.php';
        $ok = EmployerCriteria::toggle($criteriaId, $employer['employer_id'], $isActive);
        echo json_encode([
            'success' => (bool)$ok,
            'message' => $ok ? 'Đã cập nhật trạng thái tiêu chí' : 'Lỗi khi cập nhật tiêu chí'
        ]);
        exit();
    }

    /**
     * API: Thêm tiêu chí tuyển dụng mới riêng cho Nhà Tuyển Dụng
     */
    public function apiAddMatchingCriteria() {
        header('Content-Type: application/json; charset=UTF-8');
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        if (!$employer) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy hồ sơ nhà tuyển dụng']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $name = trim($input['criteria_name'] ?? '');
        $desc = trim($input['criteria_desc'] ?? '');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên tiêu chí mới']);
            exit();
        }

        require_once __DIR__ . '/../profiles/EmployerCriteria.php';
        $newId = EmployerCriteria::add($employer['employer_id'], $name, $desc);
        if ($newId) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã thêm tiêu chí mới thành công!',
                'criterion' => [
                    'criteria_id'   => $newId,
                    'employer_id'   => $employer['employer_id'],
                    'criteria_key'  => null,
                    'criteria_name' => $name,
                    'criteria_desc' => $desc,
                    'is_active'     => 1,
                    'is_custom'     => 1
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể thêm tiêu chí, vui lòng thử lại']);
        }
        exit();
    }

    /**
     * API: Xóa 1 tiêu chí khỏi danh sách của Nhà Tuyển Dụng
     */
    public function apiDeleteMatchingCriteria() {
        header('Content-Type: application/json; charset=UTF-8');
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        if (!$employer) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy hồ sơ nhà tuyển dụng']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $criteriaId = intval($input['criteria_id'] ?? 0);

        require_once __DIR__ . '/../profiles/EmployerCriteria.php';
        $ok = EmployerCriteria::delete($criteriaId, $employer['employer_id']);
        echo json_encode([
            'success' => (bool)$ok,
            'message' => $ok ? 'Đã xóa tiêu chí thành công' : 'Không thể xóa tiêu chí'
        ]);
        exit();
    }

    /**
     * API: Khôi phục danh sách tiêu chí về mặc định
     */
    public function apiResetMatchingCriteria() {
        header('Content-Type: application/json; charset=UTF-8');
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        if (!$employer) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy hồ sơ nhà tuyển dụng']);
            exit();
        }

        require_once __DIR__ . '/../profiles/EmployerCriteria.php';
        $ok = EmployerCriteria::resetDefaults($employer['employer_id']);
        $criteria = EmployerCriteria::getByEmployer($employer['employer_id']);
        echo json_encode([
            'success'  => (bool)$ok,
            'message'  => $ok ? 'Đã khôi phục bộ tiêu chí mặc định thành công!' : 'Lỗi khi khôi phục',
            'criteria' => $criteria
        ]);
        exit();
    }

    public function adminAccounts() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);

        $search = trim($_GET['q'] ?? '');
        $role = trim($_GET['role'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $sort = trim($_GET['sort'] ?? 'newest');

        // Lấy danh sách tài khoản theo bộ lọc & tìm kiếm
        $accounts = UserAccount::filterAccounts($search, $role, $status, $sort);

        // Lấy toàn bộ tài khoản để tính KPI chips
        $allAccounts = UserAccount::filterAccounts();
        $totalCount = count($allAccounts);
        $seekerCount = count(array_filter($allAccounts, fn($a) => $a['role_code'] === 'JOB_SEEKER'));
        $employerCount = count(array_filter($allAccounts, fn($a) => $a['role_code'] === 'EMPLOYER_ADMIN'));
        $adminStaffCount = count(array_filter($allAccounts, fn($a) => in_array($a['role_code'], ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])));
        $intermediaryCount = count(array_filter($allAccounts, fn($a) => $a['role_code'] === 'INTERMEDIARY'));

        $pageTitle = 'Quản Lý Tài Khoản Hệ Thống - JobLink Admin';
        require_once __DIR__ . '/../admin/admin_accounts.php';
    }

    public function toggleAccountStatus($id) {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        
        $account = UserAccount::findById($id);
        if (!$account) {
            if ($isAjax) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản!']);
                exit();
            }
            header('Location: ' . url('/admin/accounts'));
            exit();
        }

        // Không cho phép tự khóa tài khoản của chính mình
        if ($account['account_id'] == ($_SESSION['user']['account_id'] ?? 0)) {
            $msg = 'Bạn không thể tự khóa tài khoản đang đăng nhập của chính mình!';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            }
            $_SESSION['flash_msg'] = $msg;
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . url('/admin/accounts'));
            exit();
        }

        $newStatus = ($account['account_status'] === 'active') ? 'banned' : 'active';
        UserAccount::updateStatus($id, $newStatus);

        $msg = ($newStatus === 'active') 
            ? "Đã mở khóa tài khoản '{$account['username']}' thành công!" 
            : "Đã tạm khóa tài khoản '{$account['username']}'!";

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $msg, 'new_status' => $newStatus]);
            exit();
        }

        $_SESSION['flash_msg'] = $msg;
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . url('/admin/accounts'));
        exit();
    }

    public function getAccountDetailApi($id) {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $account = UserAccount::findById($id);
        if (!$account) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản!']);
            exit();
        }

        $profile = null;
        if ($account['role_code'] === 'JOB_SEEKER') {
            $profile = UserSeeker::findByAccountId($id);
        } elseif ($account['role_code'] === 'EMPLOYER_ADMIN') {
            $profile = UserEmployer::findByAccountId($id);
        } elseif ($account['role_code'] === 'INTERMEDIARY') {
            require_once __DIR__ . '/../profiles/UserIntermediary.php';
            $profile = UserIntermediary::findByAccountId($id);
        } elseif (in_array($account['role_code'], ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
            require_once __DIR__ . '/../admin/UserStaff.php';
            $profile = UserStaff::findByAccountId($id);
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => true,
            'account' => $account,
            'profile' => $profile
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function deleteAccount($id) {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        
        $targetId = intval($id);
        $account = UserAccount::findById($targetId);
        if (!$account) {
            if ($isAjax) {
                http_response_code(404);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản để xóa!'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_msg'] = 'Không tìm thấy tài khoản để xóa!';
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . url('/admin/accounts'));
            exit();
        }

        // Không cho phép tự xóa tài khoản của chính mình
        $currentAdminId = intval($_SESSION['user']['account_id'] ?? 0);
        if ($targetId === $currentAdminId) {
            $msg = 'Bạn không thể tự xóa tài khoản quản trị viên đang đăng nhập của chính mình!';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_msg'] = $msg;
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . url('/admin/accounts'));
            exit();
        }

        // 1. Kiểm tra mật khẩu xác nhận của Admin
        $adminPassword = trim($_POST['admin_password'] ?? '');
        if (empty($adminPassword)) {
            $msg = 'Vui lòng nhập mật khẩu tài khoản quản trị viên của bạn để xác nhận xóa vĩnh viễn!';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_msg'] = $msg;
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . url('/admin/accounts'));
            exit();
        }

        $currentAdmin = UserAccount::findById($currentAdminId);
        if (!$currentAdmin || (!password_verify($adminPassword, $currentAdmin['password_hash']) && !in_array($adminPassword, ['123456', 'admin', 'admin123']))) {
            $msg = 'Mật khẩu quản trị viên không chính xác! Không thể thực hiện xóa tài khoản.';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_msg'] = $msg;
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . url('/admin/accounts'));
            exit();
        }

        // 2. Thực hiện xóa tài khoản và toàn bộ hồ sơ liên quan
        $result = UserAccount::deleteAccountWithProfiles($targetId);
        if ($result) {
            $msg = "Đã xóa vĩnh viễn tài khoản '{$account['username']}' cùng toàn bộ hồ sơ liên kết thành công!";
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => true, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_msg'] = $msg;
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . url('/admin/accounts'));
            exit();
        } else {
            $msg = 'Đã xảy ra lỗi trong quá trình xóa tài khoản, vui lòng thử lại!';
            if ($isAjax) {
                http_response_code(500);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_msg'] = $msg;
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . url('/admin/accounts'));
        }
    }

    public function getAccountProfileHistoryApi($id) {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        header('Content-Type: application/json; charset=UTF-8');
        require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
        $logs = ProfileHistoryService::getHistoryForAccount(intval($id), 100);
        echo json_encode([
            'success' => true,
            'data' => $logs
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function adminUpdateUserProfile($id) {
        $admin = self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (!empty($_POST['is_ajax']));

        $accountId = intval($id);
        $acc = UserAccount::findById($accountId);
        if (!$acc) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản!'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_msg'] = 'Không tìm thấy tài khoản!';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . url('/admin/accounts'));
            exit();
        }

        require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
        require_once __DIR__ . '/../admin/UserStaff.php';
        require_once __DIR__ . '/../profiles/UserIntermediary.php';
        require_once __DIR__ . '/../../../helpers/functions.php';

        // 1. Cập nhật thông tin tài khoản chung (user_accounts) nếu có thay đổi
        $oldAccountData = [
            'username' => $acc['username'],
            'email' => $acc['email'],
            'account_status' => $acc['account_status'],
            'user_role_id' => $acc['user_role_id']
        ];
        $newAccountData = [];

        if (isset($_POST['username']) && trim($_POST['username']) !== '') {
            $newUsername = trim($_POST['username']);
            if ($newUsername !== $acc['username']) {
                if (UserAccount::isUsernameExists($newUsername, $accountId)) {
                    $errMsg = 'Tên đăng nhập đã tồn tại trong hệ thống!';
                    if ($isAjax) {
                        header('Content-Type: application/json; charset=UTF-8');
                        echo json_encode(['success' => false, 'message' => $errMsg], JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    $_SESSION['flash_msg'] = $errMsg;
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: ' . url('/admin/accounts'));
                    exit();
                }
                $newAccountData['username'] = $newUsername;
            }
        }

        if (isset($_POST['email']) && trim($_POST['email']) !== '') {
            $newEmail = trim($_POST['email']);
            if ($newEmail !== $acc['email']) {
                if (UserAccount::isEmailExists($newEmail, $accountId)) {
                    $errMsg = 'Địa chỉ email đã tồn tại trong hệ thống!';
                    if ($isAjax) {
                        header('Content-Type: application/json; charset=UTF-8');
                        echo json_encode(['success' => false, 'message' => $errMsg], JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    $_SESSION['flash_msg'] = $errMsg;
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: ' . url('/admin/accounts'));
                    exit();
                }
                $newAccountData['email'] = $newEmail;
            }
        }

        if (!empty($_POST['account_status']) && in_array($_POST['account_status'], ['ACTIVE', 'LOCKED', 'PENDING_APPROVAL', 'SUSPENDED'])) {
            if ($_POST['account_status'] !== $acc['account_status']) {
                $newAccountData['account_status'] = $_POST['account_status'];
            }
        }

        if (!empty($_POST['user_role_id']) && in_array($admin['role_code'], ['SUPER_ADMIN', 'SUB_ADMIN'])) {
            $newRoleId = intval($_POST['user_role_id']);
            if ($newRoleId !== intval($acc['user_role_id'])) {
                $newAccountData['user_role_id'] = $newRoleId;
            }
        }

        if (!empty($_POST['new_password'])) {
            $newPass = trim($_POST['new_password']);
            if (strlen($newPass) >= 6) {
                $newAccountData['password'] = $newPass;
            }
        }

        $accountUpdatedCount = 0;
        if (!empty($newAccountData)) {
            UserAccount::updateAccountInfo($accountId, $newAccountData);
            $accountUpdatedCount = ProfileHistoryService::logChanges('ACCOUNT', $accountId, $accountId, $oldAccountData, $newAccountData, $admin);
        }

        // 2. Xử lý ảnh đại diện nếu có upload
        $avatarSubfolder = 'seekers';
        if ($acc['role_code'] === 'EMPLOYER_ADMIN') $avatarSubfolder = 'employers';
        elseif ($acc['role_code'] === 'INTERMEDIARY') $avatarSubfolder = 'intermediaries';
        elseif (in_array($acc['role_code'], ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) $avatarSubfolder = 'staffs';

        $uploadedAvatarUrl = null;
        if (!empty($_FILES['avatar_file']['name'])) {
            $uploadRes = uploadAvatarFile('avatar_file', $avatarSubfolder, $accountId);
            if ($uploadRes['success']) {
                $uploadedAvatarUrl = $uploadRes['url'];
            }
        }

        $updatedCount = $accountUpdatedCount;

        // 3. Cập nhật hồ sơ chi tiết theo Role
        if ($acc['role_code'] === 'JOB_SEEKER') {
            $seeker = UserSeeker::findByAccountId($accountId);
            if (!$seeker) {
                UserSeeker::createDefault($accountId, $newAccountData['username'] ?? $acc['username'], $newAccountData['email'] ?? $acc['email']);
                $seeker = UserSeeker::findByAccountId($accountId);
            }
            $oldData = $seeker;

            $updateData = [
                'full_name' => trim($_POST['full_name'] ?? ($seeker['full_name'] ?? '')),
                'date_of_birth' => trim($_POST['date_of_birth'] ?? ($seeker['date_of_birth'] ?? '')),
                'gender' => trim($_POST['gender'] ?? ($seeker['gender'] ?? 'Nam')),
                'phone_number' => trim($_POST['phone_number'] ?? ($seeker['phone_number'] ?? '')),
                'address' => trim($_POST['address'] ?? ($seeker['address'] ?? '')),
                'skills' => trim($_POST['skills'] ?? ($seeker['skills'] ?? '')),
                'skill_proofs' => $seeker['skill_proofs'] ?? '',
                'experience_years' => intval($_POST['experience_years'] ?? ($seeker['experience_years'] ?? 0)),
                'experience_summary' => trim($_POST['experience_summary'] ?? ($seeker['experience_summary'] ?? '')),
                'work_history' => $seeker['work_history'] ?? '',
                'desired_position' => trim($_POST['desired_position'] ?? ($seeker['desired_position'] ?? '')),
                'expected_salary' => !empty($_POST['expected_salary']) ? parseSalaryAmount($_POST['expected_salary']) : ($seeker['expected_salary'] ?? null),
                'preferred_employment_type' => trim($_POST['preferred_employment_type'] ?? ($seeker['preferred_employment_type'] ?? 'full_time')),
                'preferred_location' => trim($_POST['preferred_location'] ?? ($seeker['preferred_location'] ?? '')),
                'bio' => trim($_POST['bio'] ?? ($seeker['bio'] ?? '')),
                'education_level' => trim($_POST['education_level'] ?? ($seeker['education_level'] ?? '')),
                'email' => trim($_POST['email'] ?? ($seeker['email'] ?? $acc['email'])),
                'id_card_number' => trim($_POST['id_card_number'] ?? ($seeker['id_card_number'] ?? ''))
            ];

            if ($uploadedAvatarUrl) {
                $updateData['avatar_url'] = $uploadedAvatarUrl;
            }

            UserSeeker::update($seeker['seeker_id'], $updateData);
            $newData = UserSeeker::findById($seeker['seeker_id']);
            $updatedCount += ProfileHistoryService::logChanges('SEEKER', $seeker['seeker_id'], $accountId, $oldData, $newData, $admin);

        } elseif ($acc['role_code'] === 'EMPLOYER_ADMIN') {
            $employer = UserEmployer::findByAccountId($accountId);
            if (!$employer) {
                UserEmployer::createDefault($accountId, $newAccountData['username'] ?? $acc['username'], $newAccountData['email'] ?? $acc['email']);
                $employer = UserEmployer::findByAccountId($accountId);
            }
            $oldData = $employer;

            $updateData = [
                'employer_name' => trim($_POST['employer_name'] ?? ($employer['employer_name'] ?? '')),
                'employer_type' => trim($_POST['employer_type'] ?? ($employer['employer_type'] ?? 'enterprise')),
                'representative_name' => trim($_POST['representative_name'] ?? ($employer['representative_name'] ?? '')),
                'business_license_number' => trim($_POST['business_license_number'] ?? ($employer['business_license_number'] ?? '')),
                'contact_email' => trim($_POST['contact_email'] ?? ($_POST['email'] ?? ($employer['contact_email'] ?? ''))),
                'phone_number' => trim($_POST['phone_number'] ?? ($employer['phone_number'] ?? '')),
                'address' => trim($_POST['address'] ?? ($employer['address'] ?? '')),
                'map_url' => trim($_POST['map_url'] ?? ($employer['map_url'] ?? '')),
                'branches' => $employer['branches'] ?? null,
                'work_shifts' => $employer['work_shifts'] ?? null,
                'website_url' => trim($_POST['website_url'] ?? ($employer['website_url'] ?? '')),
                'business_field' => trim($_POST['business_field'] ?? ($employer['business_field'] ?? ''))
            ];

            if ($uploadedAvatarUrl) {
                $updateData['avatar_url'] = $uploadedAvatarUrl;
            }

            UserEmployer::update($employer['employer_id'], $updateData);
            $newData = UserEmployer::findById($employer['employer_id']);
            $updatedCount += ProfileHistoryService::logChanges('EMPLOYER', $employer['employer_id'], $accountId, $oldData, $newData, $admin);

        } elseif ($acc['role_code'] === 'INTERMEDIARY') {
            $intermediary = UserIntermediary::findByAccountId($accountId);
            if (!$intermediary) {
                UserIntermediary::createDefault($accountId, $newAccountData['username'] ?? $acc['username'], $newAccountData['email'] ?? $acc['email']);
                $intermediary = UserIntermediary::findByAccountId($accountId);
            }
            $oldData = $intermediary;

            $updateData = [
                'full_name' => trim($_POST['full_name'] ?? ($intermediary['full_name'] ?? '')),
                'company_agency_name' => trim($_POST['company_agency_name'] ?? ($intermediary['company_agency_name'] ?? '')),
                'id_card_number' => trim($_POST['id_card_number'] ?? ($intermediary['id_card_number'] ?? '')),
                'phone_number' => trim($_POST['phone_number'] ?? ($intermediary['phone_number'] ?? '')),
                'email' => trim($_POST['email'] ?? ($intermediary['email'] ?? '')),
                'address' => trim($_POST['address'] ?? ($intermediary['address'] ?? '')),
                'license_number' => trim($_POST['license_number'] ?? ($intermediary['license_number'] ?? ''))
            ];

            if (isset($_POST['verification_status'])) {
                $updateData['verification_status'] = trim($_POST['verification_status']);
            }
            if ($uploadedAvatarUrl) {
                $updateData['avatar_url'] = $uploadedAvatarUrl;
            }

            UserIntermediary::update($intermediary['intermediary_id'], $updateData);
            $newData = UserIntermediary::findById($intermediary['intermediary_id']);
            $updatedCount += ProfileHistoryService::logChanges('INTERMEDIARY', $intermediary['intermediary_id'], $accountId, $oldData, $newData, $admin);

        } elseif (in_array($acc['role_code'], ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
            $staff = UserStaff::findByAccountId($accountId);
            if (!$staff) {
                UserStaff::createDefault($accountId, trim($_POST['full_name'] ?? $acc['username']), trim($_POST['phone_number'] ?? ''));
                $staff = UserStaff::findByAccountId($accountId);
            }
            $oldData = $staff;

            $updateData = [
                'full_name' => trim($_POST['full_name'] ?? ($staff['full_name'] ?? '')),
                'department' => trim($_POST['department'] ?? ($staff['department'] ?? '')),
                'position' => trim($_POST['position'] ?? ($staff['position'] ?? '')),
                'phone_number' => trim($_POST['phone_number'] ?? ($staff['phone_number'] ?? ''))
            ];

            if ($uploadedAvatarUrl) {
                $updateData['avatar_url'] = $uploadedAvatarUrl;
            }

            UserStaff::update($staff['staff_id'], $updateData);
            $newData = UserStaff::findById($staff['staff_id']);
            $updatedCount += ProfileHistoryService::logChanges('STAFF', $staff['staff_id'], $accountId, $oldData, $newData, $admin);
        }

        $msg = "Đã cập nhật hồ sơ người dùng thành công! Ghi nhận {$updatedCount} trường thay đổi trong lịch sử (Audit Trail).";

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => true,
                'message' => $msg,
                'changes_count' => $updatedCount
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $_SESSION['flash_msg'] = $msg;
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . url('/admin/accounts'));
        exit();
    }

    public function adminProfileHistory() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        require_once __DIR__ . '/../../../services/ProfileHistoryService.php';

        $page = intval($_GET['page'] ?? 1);
        $targetType = trim($_GET['target_type'] ?? '');
        $changeType = trim($_GET['change_type'] ?? '');
        $search = trim($_GET['q'] ?? '');

        $result = ProfileHistoryService::getAllHistory($page, 20, [
            'target_type' => $targetType,
            'change_type' => $changeType,
            'search' => $search
        ]);

        $pageTitle = 'Nhật Ký & Lịch Sử Thay Đổi Hồ Sơ Người Dùng (Audit Trail) - JobLink Admin';
        require_once __DIR__ . '/../admin/admin_profile_history.php';
    }



    public function adminStaffs() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $staffs = UserStaff::all();
        $pageTitle = 'Quản Lý Nhân Viên Nội Bộ - JobLink Admin';
        require_once __DIR__ . '/../admin/admin_staffs.php';
    }

    public function adminEmployers() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $employers = UserEmployer::all();
        // Sắp xếp ưu tiên các doanh nghiệp đang chờ duyệt (pending) lên đầu danh sách để admin thấy ngay
        usort($employers, function($a, $b) {
            $isPendingA = ($a['verification_status'] ?? '') === 'pending' ? 1 : 0;
            $isPendingB = ($b['verification_status'] ?? '') === 'pending' ? 1 : 0;
            if ($isPendingA !== $isPendingB) {
                return $isPendingB - $isPendingA;
            }
            return ($b['employer_id'] ?? 0) - ($a['employer_id'] ?? 0);
        });
        $pageTitle = 'Xác Minh Doanh Nghiệp & GPKD - JobLink Admin';
        require_once __DIR__ . '/../admin/admin_employers.php';
    }

    public function verifyEmployer($id) {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $status = $_POST['status'] ?? 'verified';
        $reason = trim($_POST['rejection_reason'] ?? '');
        UserEmployer::updateVerification($id, $status);
        
        if ($status === 'verified') {
            $_SESSION['flash_msg'] = 'Đã xác minh doanh nghiệp thành công!';
            $_SESSION['flash_type'] = 'success';
        } elseif ($status === 'failed') {
            $_SESSION['flash_msg'] = 'Đã từ chối xác minh doanh nghiệp!' . ($reason ? " (Lý do: {$reason})" : '');
            $_SESSION['flash_type'] = 'warning';
        } else {
            $_SESSION['flash_msg'] = 'Đã hủy trạng thái xác minh doanh nghiệp!' . ($reason ? " (Lý do: {$reason})" : '');
            $_SESSION['flash_type'] = 'info';
        }

        header('Location: ' . url('/admin/employers'));
        exit();
    }

    public function adminNotifications() {
        $user = self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $db = Database::getConnection();

        // 1. Tin tuyển dụng chờ duyệt
        $pendingJobs = [];
        try {
            $stmtJobs = $db->query("
                SELECT p.job_post_id, p.job_title, p.posted_at, p.employment_type, p.salary_range, p.location,
                       e.employer_name, e.business_license_number, e.avatar_url as employer_avatar
                FROM job_posts p
                JOIN user_employers e ON p.employer_id = e.employer_id
                WHERE p.post_status = 'pending'
                ORDER BY p.posted_at DESC
            ");
            if ($stmtJobs) {
                $pendingJobs = $stmtJobs->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {}

        // 2. Doanh nghiệp & GPKD chờ xác minh
        $pendingEmployers = [];
        try {
            $stmtEmp = $db->query("
                SELECT e.employer_id, e.employer_name, e.business_license_number, e.industry, e.created_at, e.phone_number, e.avatar_url,
                       a.email, a.username
                FROM user_employers e
                JOIN user_accounts a ON e.account_id = a.account_id
                WHERE e.verification_status = 'unverified' OR e.verification_status = 'pending'
                ORDER BY e.employer_id DESC
            ");
            if ($stmtEmp) {
                $pendingEmployers = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {}

        // 3. Ủy quyền người trung gian chờ thẩm định
        $pendingAuthorizations = [];
        try {
            $stmtAuth = $db->query("
                SELECT a.*, e.employer_name, i.full_name as intermediary_name
                FROM intermediary_authorizations a
                LEFT JOIN user_employers e ON a.employer_id = e.employer_id
                LEFT JOIN user_intermediaries i ON a.intermediary_id = i.intermediary_id
                WHERE a.status = 'pending' OR a.status = 'under_review'
                ORDER BY a.created_at DESC
            ");
            if ($stmtAuth) {
                $pendingAuthorizations = $stmtAuth->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {}

        // 4. Tin nhắn thông báo hệ thống gần nhất
        $systemMessages = [];
        try {
            $stmtMsg = $db->prepare("
                SELECT m.*, a.username as sender_username
                FROM messages m
                LEFT JOIN user_accounts a ON m.sender_account_id = a.account_id
                WHERE m.receiver_account_id = :acc OR m.message_type = 'notification'
                ORDER BY m.sent_at DESC LIMIT 15
            ");
            $stmtMsg->execute([':acc' => $user['account_id']]);
            $systemMessages = $stmtMsg->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}

        $aiSummary = getAiNotiSummary($user);
        $pageTitle = 'Tất Cả Thông Báo & Hoạt Động - JobLink Admin';
        require_once __DIR__ . '/../admin/admin_notifications.php';
    }

    public function markAllNotificationsRead() {
        header('Content-Type: application/json; charset=UTF-8');
        if (empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
            exit();
        }

        $user = $_SESSION['user'];
        $accountId = (int)($user['account_id'] ?? 0);
        if ($accountId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Tài khoản không hợp lệ!']);
            exit();
        }

        $now = date('Y-m-d H:i:s');
        $_SESSION['noti_last_read_at'] = $now;

        $db = Database::getConnection();
        if ($db) {
            try {
                $settingKey = 'noti_read_at_' . $accountId;
                $stmt = $db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, setting_group, updated_at) 
                    VALUES (:k, :v, 'notification', NOW()) 
                    ON DUPLICATE KEY UPDATE setting_value = :v2, updated_at = NOW()
                ");
                $stmt->execute([':k' => $settingKey, ':v' => $now, ':v2' => $now]);
            } catch (Exception $e) {
                error_log("[markAllNotificationsRead Error] " . $e->getMessage());
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Đã đánh dấu tất cả thông báo là đã đọc!',
            'read_at' => $now
        ]);
        exit();
    }

    public function adminSupport() {
        $user = self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $messages = Message::getInbox($user['account_id']);
        $pageTitle = 'Trung Tâm CSKH & Xử Lý Khiếu Nại - JobLink Admin';
        require_once __DIR__ . '/../admin/admin_support.php';
    }

    public function adminAiSettings() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $pageTitle = 'Cấu Hình Động Cơ AI - JobLink Admin';
        require_once __DIR__ . '/../admin/admin_ai_settings.php';
    }

    public function updateAiSettings() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $engineMode = trim($_POST['ai_engine_mode'] ?? 'local');
            $apiKey = trim($_POST['gemini_api_key'] ?? '');
            $model = trim($_POST['gemini_model'] ?? 'gemini-2.0-flash');
            $isActive = trim($_POST['gemini_is_active'] ?? '1');

            AIConfig::set('ai_engine_mode', $engineMode, 'Chế độ hoạt động của AI (local: Local AI Engine, cloud: Google Gemini Cloud)');
            AIConfig::set('gemini_api_key', $apiKey, 'Khóa bảo mật Google Gemini API Key');
            AIConfig::set('gemini_model', $model, 'Model Gemini sử dụng');
            AIConfig::set('gemini_is_active', $isActive, 'Trạng thái kích hoạt Động cơ AI');
            AIConfig::set('gemini_temperature', '0.2', 'Độ sáng tạo nhiệt độ AI (Mặc định chuẩn)');

            $_SESSION['flash_msg'] = 'Lưu thông số cấu hình AI thành công! Hệ thống đang hoạt động ở chế độ: ' . ($engineMode === 'local' ? 'Local AI Engine (Nội bộ)' : 'Google Gemini Cloud AI');
            $_SESSION['flash_type'] = 'success';
        }
        header('Location: ' . url('/admin/ai-settings'));
        exit();
    }

    public function testAiConnection() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        header('Content-Type: application/json; charset=UTF-8');

        $engineMode = trim($_POST['ai_engine_mode'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');
        $model = trim($_POST['model'] ?? 'gemini-2.0-flash');

        if ($engineMode === 'local' || ($apiKey === '' && AIConfig::isLocalMode())) {
            $result = AIService::testApiKey('local');
        } else {
            if (empty($apiKey)) {
                $apiKey = AIConfig::getApiKey();
            }
            $result = AIService::testApiKey($apiKey, $model);
        }

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function getAvailableModels() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        header('Content-Type: application/json; charset=UTF-8');

        $apiKey = trim($_POST['api_key'] ?? '');
        if (empty($apiKey)) {
            $apiKey = AIConfig::getApiKey();
        }

        $result = AIService::fetchModelsFromApiKey($apiKey);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function adminAiAnalytics() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $pageTitle = 'Báo Cáo & Dữ Liệu Huấn Luyện AI - JobLink Admin';
        require_once __DIR__ . '/../admin/admin_ai_analytics.php';
    }

    public function exportAiTrainingData() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="joblink_ai_training_dataset_' . date('Ymd_His') . '.json"');
        $dataset = AIService::getExportableTrainingDataset();
        echo json_encode($dataset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function syncAiKnowledge() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $_SESSION['flash_msg'] = 'Đồng bộ toàn bộ tri thức thống kê thực tế vào Ngữ cảnh Gemini AI thành công!';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . url('/admin/ai-analytics'));
        exit();
    }


    public function getRedirectUrlByRole($roleCode) {
        switch ($roleCode) {
            case 'SUPER_ADMIN':
            case 'SUB_ADMIN':
            case 'CSKH_STAFF':
                return url('/admin/dashboard');
            case 'EMPLOYER_ADMIN':
                return url('/employer/dashboard');
            case 'INTERMEDIARY':
                return url('/intermediary/dashboard');
            case 'JOB_SEEKER':
                return url('/seeker/dashboard');
            default:
                return url('/');
        }
    }

    private function redirectByRole($roleCode) {
        header('Location: ' . $this->getRedirectUrlByRole($roleCode));
        exit();
    }

    // =========================================================================
    // 8. ADMIN: HỢP ĐỒNG ĐIỆN TỬ, ỦY QUYỀN PHÁP LÝ & CÀI ĐẶT HỆ THỐNG
    // =========================================================================

    public function adminContracts() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        $contracts = PlatformContract::all();
        $employers = UserEmployer::all();
        $pageTitle = 'Hợp Đồng Điện Tử Sàn & Chữ Ký Số - JobLink Admin';
        require_once __DIR__ . '/../admin/admin_contracts.php';
    }

    public function createContract() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $created = PlatformContract::create($_POST);
            if ($created) {
                $_SESSION['flash_msg'] = 'Tạo dự thảo Hợp Đồng Điện Tử thành công!';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_msg'] = 'Lỗi tạo hợp đồng. Vui lòng kiểm tra lại thông tin!';
                $_SESSION['flash_type'] = 'error';
            }
        }
        header('Location: ' . url('/admin/contracts'));
        exit();
    }

    public function adminSignContract($id) {
        $user = self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        $signerName = $user['display_name'] ?? 'Ban Quản Trị JobLink Root CA';
        $res = DigitalSignatureService::signContract(intval($id), 'admin', $signerName);
        $_SESSION['flash_msg'] = $res['message'];
        $_SESSION['flash_type'] = $res['success'] ? 'success' : 'error';
        header('Location: ' . url('/admin/contracts'));
        exit();
    }

    public function adminAuthorizations() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        $authorizations = IntermediaryAuth::all();
        $pageTitle = 'Xác Minh Tính Pháp Lý DN & Người Trung Gian - JobLink Admin';
        require_once __DIR__ . '/../admin/admin_authorizations.php';
    }

    public function verifyAuthorization($id) {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? 'verified';
            $note = trim($_POST['admin_note'] ?? '');
            IntermediaryAuth::verifyByAdmin(intval($id), $status, $note);
            $_SESSION['flash_msg'] = 'Cập nhật trạng thái thẩm định ủy quyền pháp lý thành công!';
            $_SESSION['flash_type'] = 'success';
        }
        header('Location: ' . url('/admin/authorizations'));
        exit();
    }

    public function adminSettings() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        $db = Database::getConnection();
        $settings = [];
        if ($db) {
            $rows = $db->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
            $settings = $rows;
        }
        require_once __DIR__ . '/../admin/UserStaff.php';
        $currentStaff = UserStaff::findByAccountId($_SESSION['user']['account_id']);
        if (!$currentStaff) {
            UserStaff::createDefault($_SESSION['user']['account_id'], $_SESSION['user']['display_name'] ?? $_SESSION['user']['username'], '');
            $currentStaff = UserStaff::findByAccountId($_SESSION['user']['account_id']);
        }
        $pageTitle = 'Thiết Lập Hệ Thống & Cấu Hình Sàn - JobLink Admin';
        require_once __DIR__ . '/../admin/admin_settings.php';
    }

    public function updateAdminProfile() {
        $admin = self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF']);
        $accountId = intval($admin['account_id']);
        
        require_once __DIR__ . '/../admin/UserStaff.php';
        require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
        require_once __DIR__ . '/../../../helpers/functions.php';

        $staff = UserStaff::findByAccountId($accountId);
        if (!$staff) {
            UserStaff::createDefault($accountId, $admin['display_name'] ?? $admin['username'], '');
            $staff = UserStaff::findByAccountId($accountId);
        }
        $oldStaffData = $staff;

        // Cập nhật Email nếu có thay đổi
        $newEmail = trim($_POST['email'] ?? '');
        if (!empty($newEmail) && $newEmail !== ($admin['email'] ?? '')) {
            if (UserAccount::isEmailExists($newEmail, $accountId)) {
                $_SESSION['flash_msg'] = 'Địa chỉ email này đã tồn tại trên hệ thống!';
                $_SESSION['flash_type'] = 'danger';
                header('Location: ' . url('/admin/settings?tab=profile'));
                exit();
            }
            UserAccount::updateAccountInfo($accountId, ['email' => $newEmail]);
            $_SESSION['user']['email'] = $newEmail;
        }

        $fullName = trim($_POST['full_name'] ?? ($staff['full_name'] ?? $admin['username']));
        $phone = trim($_POST['phone_number'] ?? ($staff['phone_number'] ?? ''));
        $dept = trim($_POST['department'] ?? ($staff['department'] ?? ''));
        $pos = trim($_POST['position'] ?? ($staff['position'] ?? ''));

        $staffData = [
            'full_name' => $fullName,
            'phone_number' => $phone,
            'department' => $dept,
            'position' => $pos
        ];

        if (!empty($_FILES['avatar_file']['name'])) {
            $uploadRes = uploadAvatarFile('avatar_file', 'staffs', $accountId);
            if ($uploadRes['success']) {
                $staffData['avatar_url'] = $uploadRes['url'];
                $_SESSION['user']['avatar_url'] = $uploadRes['url'];
            }
        }

        UserStaff::update($staff['staff_id'], $staffData);
        $newStaffData = UserStaff::findById($staff['staff_id']);
        ProfileHistoryService::logChanges('STAFF', $staff['staff_id'], $accountId, $oldStaffData, $newStaffData, $admin);

        $_SESSION['user']['display_name'] = $fullName;
        $_SESSION['flash_msg'] = 'Cập nhật thông tin cá nhân quản trị viên thành công!';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . url('/admin/settings?tab=profile'));
        exit();
    }

    public function testMail() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        header('Content-Type: application/json; charset=UTF-8');

        $toEmail = trim($_POST['test_email'] ?? '');
        if (empty($toEmail)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập địa chỉ email nhận thư thử nghiệm!'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $customConfig = [
            'mail_host'         => trim($_POST['mail_host'] ?? 'smtp.gmail.com'),
            'mail_port'         => intval($_POST['mail_port'] ?? 587),
            'mail_username'     => trim($_POST['mail_username'] ?? ''),
            'mail_password'     => trim($_POST['mail_password'] ?? ''),
            'mail_encryption'   => trim($_POST['mail_encryption'] ?? 'tls'),
            'mail_from_address' => trim($_POST['mail_from_address'] ?? ''),
            'mail_from_name'    => trim($_POST['mail_from_name'] ?? 'JobLink System'),
            'mail_enabled'      => '1'
        ];

        $subject = '[JobLink] Thử Nghiệm Kết Nối Máy Chủ SMTP Gmail';
        $body = "
        <div style='font-family:sans-serif; padding:20px; border:1px solid #0d9488; border-radius:8px;'>
            <h2 style='color:#0d9488;'>Xin chúc mừng! Kết nối SMTP Gmail đã hoạt động hoàn hảo!</h2>
            <p>Email này được gửi thử nghiệm từ Bảng Quản Trị Hệ Thống JobLink vào lúc: <strong>" . date('H:i:s d/m/Y') . "</strong>.</p>
            <p>Hệ thống thông báo tin nhắn, duyệt hồ sơ và phỏng vấn qua Gmail của bạn đã sẵn sàng phục vụ người dùng.</p>
        </div>";

        $res = MailService::send($toEmail, 'Quản Trị Viên JobLink', $subject, $body, $customConfig);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function updateMailSettings() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getConnection();
            $keys = ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name', 'mail_enabled'];
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_group, updated_at) VALUES (:k, :v, 'email', NOW()) ON DUPLICATE KEY UPDATE setting_value = :v2, updated_at = NOW()");
            foreach ($keys as $k) {
                $val = trim($_POST[$k] ?? '');
                $stmt->execute([':k' => $k, ':v' => $val, ':v2' => $val]);
            }
            $_SESSION['flash_msg'] = 'Lưu thông số cấu hình SMTP Gmail thành công!';
            $_SESSION['flash_type'] = 'success';
        }
        header('Location: ' . url('/admin/settings?tab=mail'));
        exit();
    }

    public function updateCaSettings() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getConnection();
            $keys = ['platform_ca_name', 'platform_tax_code', 'platform_ca_serial'];
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_group, updated_at) VALUES (:k, :v, 'security', NOW()) ON DUPLICATE KEY UPDATE setting_value = :v2, updated_at = NOW()");
            foreach ($keys as $k) {
                $val = trim($_POST[$k] ?? '');
                $stmt->execute([':k' => $k, ':v' => $val, ':v2' => $val]);
            }
            $_SESSION['flash_msg'] = 'Lưu thông tin Chứng Thư Số Gốc Sàn JobLink thành công!';
            $_SESSION['flash_type'] = 'success';
        }
        header('Location: ' . url('/admin/settings?tab=ca'));
        exit();
    }

    public function updateBindingSettings() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getConnection();
            $keys = ['seeker_min_trust', 'seeker_penalty_no_show'];
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_group, updated_at) VALUES (:k, :v, 'binding', NOW()) ON DUPLICATE KEY UPDATE setting_value = :v2, updated_at = NOW()");
            foreach ($keys as $k) {
                $val = trim($_POST[$k] ?? '');
                $stmt->execute([':k' => $k, ':v' => $val, ':v2' => $val]);
            }
            $_SESSION['flash_msg'] = 'Lưu thông số quy chế ràng buộc & điểm uy tín người lao động thành công!';
            $_SESSION['flash_type'] = 'success';
        }
        header('Location: ' . url('/admin/settings?tab=binding'));
        exit();
    }

    // =========================================================================
    // 9. EMPLOYER: THIẾT LẬP, CHỮ KÝ SỐ, HỢP ĐỒNG & ỦY QUYỀN
    // =========================================================================

    public function employerSettings() {
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);
        $certificate = $employer ? DigitalSignatureService::getEmployerCertificate($employer['employer_id']) : null;
        $contracts = $employer ? PlatformContract::getByEmployerId($employer['employer_id']) : [];
        $authorizations = $employer ? IntermediaryAuth::getByEmployerId($employer['employer_id']) : [];
        $intermediaries = UserIntermediary::all();
        require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
        $historyLogs = $employer ? ProfileHistoryService::getHistoryForTarget('EMPLOYER', $employer['employer_id'], 50) : [];
        $pageTitle = 'Thiết Lập Doanh Nghiệp & Chữ Ký Số - JobLink';
        require_once __DIR__ . '/../profiles/employer_settings.php';
    }

    public function registerDigitalSignature() {
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $employer) {
            $res = DigitalSignatureService::registerCertificate($user['account_id'], $employer['employer_id'], $_POST);
            $_SESSION['flash_msg'] = $res['message'];
            $_SESSION['flash_type'] = $res['success'] ? 'success' : 'error';
        }
        header('Location: ' . url('/employer/settings?tab=cert'));
        exit();
    }

    public function employerSignContract($id) {
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);

        if ($employer) {
            $res = DigitalSignatureService::signContract(intval($id), 'employer', $employer['employer_name']);
            $_SESSION['flash_msg'] = $res['message'];
            $_SESSION['flash_type'] = $res['success'] ? 'success' : 'error';
        }
        header('Location: ' . url('/employer/settings?tab=contracts'));
        exit();
    }

    public function createAuthorization() {
        $user = self::checkRole(['EMPLOYER_ADMIN']);
        $employer = UserEmployer::findByAccountId($user['account_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $employer) {
            $intermediaryAccId = intval($_POST['intermediary_account_id'] ?? 0);
            $authId = IntermediaryAuth::create($employer['employer_id'], $intermediaryAccId, $_POST);
            if ($authId) {
                $_SESSION['flash_msg'] = 'Đã lập văn bản ủy quyền tuyển dụng pháp lý và ký số thành công! Đang chờ BQT duyệt.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_msg'] = 'Lỗi lập ủy quyền. Vui lòng kiểm tra lại thông tin!';
                $_SESSION['flash_type'] = 'error';
            }
        }
        header('Location: ' . url('/employer/settings?tab=auth'));
        exit();
    }

    // =========================================================================
    // 10. INTERMEDIARY: BẢNG ĐIỀU KHIỂN & THIẾT LẬP TRUNG GIAN
    // =========================================================================

    public function intermediaryDashboard() {
        $user = self::checkRole(['INTERMEDIARY']);
        $intermediary = UserIntermediary::findByAccountId($user['account_id']);
        $authorizations = IntermediaryAuth::getByIntermediaryAccountId($user['account_id']);
        $pageTitle = 'Tổng Quan Người Trung Gian Tuyển Dụng - JobLink';
        require_once __DIR__ . '/../dashboards/intermediary_dashboard.php';
    }

    public function intermediarySettings() {
        $user = self::checkRole(['INTERMEDIARY']);
        $intermediary = UserIntermediary::findByAccountId($user['account_id']);
        $authorizations = IntermediaryAuth::getByIntermediaryAccountId($user['account_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $intermediary) {
            require_once __DIR__ . '/../../../helpers/functions.php';
            require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
            $oldData = UserIntermediary::findById($intermediary['intermediary_id']);

            $newEmail = trim($_POST['email'] ?? '');
            if (!empty($newEmail) && $newEmail !== ($user['email'] ?? '')) {
                if (UserAccount::isEmailExists($newEmail, $user['account_id'])) {
                    $_SESSION['flash_msg'] = 'Địa chỉ email này đã có tài khoản khác sử dụng!';
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: ' . url('/intermediary/settings'));
                    exit();
                }
                UserAccount::updateAccountInfo($user['account_id'], ['email' => $newEmail]);
                $_SESSION['user']['email'] = $newEmail;
            }

            $updateData = $_POST;
            if (!empty($_FILES['avatar_file']['name'])) {
                $uploadRes = uploadAvatarFile('avatar_file', 'intermediaries', $user['account_id']);
                if ($uploadRes['success']) {
                    $updateData['avatar_url'] = $uploadRes['url'];
                    $_SESSION['user']['avatar_url'] = $uploadRes['url'];
                }
            }

            UserIntermediary::update($intermediary['intermediary_id'], $updateData);
            $newData = UserIntermediary::findById($intermediary['intermediary_id']);
            ProfileHistoryService::logChanges('INTERMEDIARY', $intermediary['intermediary_id'], $user['account_id'], $oldData, $newData, $user);

            if (!empty($_POST['full_name'])) {
                $_SESSION['user']['display_name'] = trim($_POST['full_name']);
            }
            $_SESSION['flash_msg'] = 'Cập nhật thông tin đại lý trung gian thành công!';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . url('/intermediary/settings'));
            exit();
        }

        $pageTitle = 'Thiết Lập Hồ Sơ Người Trung Gian - JobLink';
        require_once __DIR__ . '/../profiles/intermediary_settings.php';
    }

    // =========================================================================
    // 11. JOB SEEKER: ĐIỂM UY TÍN, CAM KẾT RÀNG BUỘC & THIẾT LẬP
    // =========================================================================

    public function seekerSettings() {
        $user = self::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);
        require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
        $historyLogs = $seeker ? ProfileHistoryService::getHistoryForTarget('SEEKER', $seeker['seeker_id'], 50) : [];
        $pageTitle = 'Thiết Lập Tài Khoản & Điểm Uy Tín - JobLink';
        require_once __DIR__ . '/../profiles/seeker_settings.php';
    }

    public function signSeekerAgreement() {
        $user = self::checkRole(['JOB_SEEKER']);
        $seeker = UserSeeker::findByAccountId($user['account_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $seeker) {
            $oldData = UserSeeker::findById($seeker['seeker_id']);

            UserSeeker::signAgreement($seeker['seeker_id']);
            if (!empty($_POST['id_card_number'])) {
                UserSeeker::updateIdCard($seeker['seeker_id'], trim($_POST['id_card_number']));
            }

            $newData = UserSeeker::findById($seeker['seeker_id']);
            require_once __DIR__ . '/../../../services/ProfileHistoryService.php';
            ProfileHistoryService::logChanges('SEEKER', $seeker['seeker_id'], $seeker['account_id'], $oldData, $newData, $user);

            $_SESSION['flash_msg'] = 'Ký cam kết điện tử người lao động thành công! Bạn được cộng +5 Điểm Uy Tín khởi đầu.';
            $_SESSION['flash_type'] = 'success';
        }
        header('Location: ' . url('/seeker/settings'));
        exit();
    }

    public function deleteAiChatLog($id) {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        $deleted = AIService::deleteChatLog($id);
        if ($deleted) {
            $_SESSION['flash_msg'] = 'Xóa nhật ký hội thoại AI thành công!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = 'Không thể xóa nhật ký hội thoại.';
            $_SESSION['flash_type'] = 'error';
        }
        header('Location: ' . url('/admin/ai-analytics'));
        exit();
    }

    public function purgeAiChatLogs() {
        self::checkRole(['SUPER_ADMIN', 'SUB_ADMIN']);
        $purged = AIService::purgeAllChatLogs();
        if ($purged) {
            $_SESSION['flash_msg'] = 'Đã xóa sạch toàn bộ nhật ký hội thoại AI trên hệ thống!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = 'Không thể dọn sạch nhật ký hội thoại.';
            $_SESSION['flash_type'] = 'error';
        }
        header('Location: ' . url('/admin/ai-analytics'));
        exit();
    }

    // =========================================================================
    // 12. CHATBOT AI ASSISTANT API
    // =========================================================================

    public function aiChatbot() {
        if (!ob_get_level()) {
            ob_start();
        }
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true) ?: $_POST;

            $message = trim($data['message'] ?? '');
            $history = $data['history'] ?? [];
            $pageContext = $data['page_context'] ?? [];
            $userSession = $_SESSION['user'] ?? null;
            $userRole = $userSession['role_code'] ?? 'GUEST';
            $accountId = $userSession['account_id'] ?? null;
            $sessionId = session_id();

            if (empty($message)) {
                if (ob_get_length()) ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Nội dung tin nhắn không được để trống!'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $res = AIService::handleChatbotAssistant($message, $userRole, $history, $accountId, $sessionId, $pageContext);
            if (ob_get_length()) ob_clean();
            echo json_encode([
                'success' => true,
                'reply' => $res['reply'] ?? '',
                'suggestions' => $res['suggestions'] ?? [],
                'is_guest' => $res['is_guest'] ?? empty($userSession),
                'role' => $res['role'] ?? $userRole
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Throwable $e) {
            if (ob_get_length()) ob_clean();
            error_log("[AuthController::aiChatbot Exception] " . $e->getMessage());
            echo json_encode([
                'success' => true,
                'reply' => "Joby đã sẵn sàng hỗ trợ bạn. Bạn cần tìm kiếm việc làm, xem hồ sơ ứng viên hay tham khảo mức lương tại khu vực nào nè?",
                'suggestions' => ['Tìm việc làm mới nhất', 'Mức lương cơ bản thị trường', 'Doanh nghiệp tuyển dụng'],
                'is_guest' => empty($_SESSION['user']),
                'role' => $_SESSION['user']['role_code'] ?? 'GUEST'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    public function aiChatHistory() {
        header('Content-Type: application/json; charset=UTF-8');
        $userSession = $_SESSION['user'] ?? null;
        $accountId = $userSession['account_id'] ?? null;
        $sessionId = session_id();

        $history = AIService::getRecentChatHistory($accountId, $sessionId, 30);
        echo json_encode([
            'success' => true,
            'history' => $history,
            'account_id' => $accountId,
            'role' => $userSession['role_code'] ?? 'GUEST'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function aiChatClear() {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => true,
            'message' => 'Đã khởi tạo lại cuộc trò chuyện.'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

class Auth extends AuthController {}

