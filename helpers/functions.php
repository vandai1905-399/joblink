<?php
/**
 * helpers/functions.php
 * Các hàm tiện ích dùng chung và Class Autoloader thông minh trong toàn bộ ứng dụng JobLink.
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Thiết lập múi giờ chuẩn Việt Nam (GMT+7)
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ============================================================================
// CLASS AUTOLOADER TỰ ĐỘNG TÌM FILE TRONG TẤT CẢ MODULES
// ============================================================================
spl_autoload_register(function ($class) {
    $classClean = basename(str_replace('\\', '/', $class));
    $snakeCase = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $classClean));

    // Tìm trong helpers, config, services
    $corePaths = [
        ROOT_PATH . '/helpers/' . $classClean . '.php',
        ROOT_PATH . '/helpers/' . $snakeCase . '.php',
        ROOT_PATH . '/config/' . $classClean . '.php',
        ROOT_PATH . '/config/' . $snakeCase . '.php',
        ROOT_PATH . '/services/' . $classClean . '.php',
        ROOT_PATH . '/services/' . $snakeCase . '.php',
    ];

    foreach ($corePaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }

    // Tìm trong tất cả các module nằm trong public/modules/
    $modulePatterns = [
        ROOT_PATH . '/public/modules/*/' . $classClean . '.php',
        ROOT_PATH . '/public/modules/' . $classClean . '.php',
    ];

    foreach ($modulePatterns as $pattern) {
        $files = glob($pattern);
        if (!empty($files)) {
            require_once $files[0];
            return;
        }
    }
});

// ============================================================================
// CÁC HÀM TIỆN ÍCH URL & GIAO DIỆN
// ============================================================================
if (!function_exists('getBaseUrl')) {
    function getBaseUrl() {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = str_replace('\\', '/', $scriptDir);
        $scriptDir = preg_replace('#/(public|modules)$#', '', $scriptDir);
        return rtrim($scriptDir, '/');
    }
}

if (!function_exists('url')) {
    function url($path = '') {
        $base = getBaseUrl();
        $cleanPath = '/' . ltrim($path, '/');
        if ($cleanPath === '//') $cleanPath = '/';
        return ($base === '' || $base === '/') ? $cleanPath : $base . $cleanPath;
    }
}

if (!function_exists('asset')) {
    function asset($path = '') {
        return url($path);
    }
}

/**
 * Escape chuỗi HTML để chống XSS.
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Lấy giá trị GET param an toàn.
 */
function getParam($key, $default = '') {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

/**
 * Lấy mảng GET param an toàn.
 */
function getParamArray($key) {
    return isset($_GET[$key]) && is_array($_GET[$key]) ? $_GET[$key] : [];
}

/**
 * Định dạng số tiền sang định dạng tiền tệ Việt Nam.
 */
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currency = 'VNĐ') {
        if ($amount === null || $amount === '' || !is_numeric($amount) || (float)$amount <= 0) {
            return 'Lương thỏa thuận';
        }
        return number_format((float)$amount, 0, ',', '.') . ' ' . $currency;
    }
}

/**
 * Kiểm tra xem một giá trị có đang được chọn trong select hay không.
 */
function selected($current, $value) {
    return $current === $value ? 'selected' : '';
}

/**
 * Kiểm tra xem một checkbox có đang được tick hay không.
 */
function checked($needle, $haystack) {
    return is_array($haystack) && in_array($needle, $haystack) ? 'checked' : '';
}

/**
 * Định dạng tiền tệ chuẩn VNĐ với dấu chấm (.) ngăn cách mỗi 1.000 đơn vị (VD: 10.000.000 VNĐ)
 */
function formatMoney($amount, $showUnit = true) {
    if ($amount === null || $amount === '') return $showUnit ? '0 VNĐ' : '0';
    $formatted = number_format((float)$amount, 0, ',', '.');
    return $showUnit ? $formatted . ' VNĐ' : $formatted;
}

/**
 * Định dạng mức lương theo hình thức làm việc với đơn vị VNĐ:
 * - full_time: ... VNĐ/tháng
 * - part_time: ... VNĐ/giờ
 * - temporary: ... VNĐ/ca
 */
function formatSalary($amount, $employmentType = 'full_time') {
    $formatted = number_format((float)$amount, 0, ',', '.');
    switch ($employmentType) {
        case 'part_time':
            return $formatted . ' VNĐ/giờ';
        case 'temporary':
            return $formatted . ' VNĐ/ca';
        case 'full_time':
        default:
            return $formatted . ' VNĐ/tháng';
    }
}

/**
 * Định dạng ngày tháng dạng chuẩn dd/MM/yyyy (VD: 23/08/2026)
 */
function formatDate($dateStr, $format = 'd/m/Y') {
    if (empty($dateStr) || $dateStr === '0000-00-00' || $dateStr === '0000-00-00 00:00:00') {
        return '—';
    }
    $timestamp = is_numeric($dateStr) ? (int)$dateStr : strtotime($dateStr);
    if (!$timestamp) return $dateStr;
    return date($format, $timestamp);
}

/**
 * Định dạng ngày giờ dạng dd/MM/yyyy H:i (VD: 23/08/2026 15:30)
 */
function formatDateTime($dateStr, $format = 'd/m/Y H:i') {
    return formatDate($dateStr, $format);
}

/**
 * Định dạng thời gian tương đối kiểu Messenger (Vừa xong, 5 phút, 1 giờ, 2 ngày, 25/09...)
 */
function formatRelativeTime($dateStr) {
    if (empty($dateStr) || $dateStr === '0000-00-00 00:00:00' || $dateStr === '—') return '';
    $ts = is_numeric($dateStr) ? (int)$dateStr : strtotime($dateStr);
    if (!$ts) return '';
    $diff = time() - $ts;
    if ($diff < 0) return 'Vừa xong';
    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ';
    if ($diff < 86400 * 7) return floor($diff / 86400) . ' ngày';
    return date('d/m', $ts);
}


/**
 * Lấy đơn vị tính lương theo hình thức làm việc:
 * - full_time: 1 tháng
 * - part_time: 1 giờ
 * - temporary: 1 ca
 */
function getSalaryUnit($employmentType = 'full_time') {
    switch ($employmentType) {
        case 'part_time':
            return '1 giờ';
        case 'temporary':
            return '1 ca';
        case 'full_time':
        default:
            return '1 tháng';
    }
}

/**
 * Định dạng ID dạng tiền tố viết tắt 2 chữ cái cho nhóm user_ và các thực thể (VD: AC1, SK1, EM1, ST1...)
 * - account_id -> AC1, AC2...
 * - seeker_id -> SK1, SK2...
 * - employer_id -> EM1, EM2...
 * - staff_id -> ST1, ST2...
 * - user_role_id -> UR1, UR2...
 */
function formatId($type, $id) {
    if ($id === null || $id === '') return '';
    $map = [
        'account' => 'AC',
        'account_id' => 'AC',
        'ac' => 'AC',
        'user_account' => 'AC',
        'user_accounts' => 'AC',

        'seeker' => 'SK',
        'seeker_id' => 'SK',
        'sk' => 'SK',
        'se' => 'SK',
        'user_seeker' => 'SK',
        'user_seekers' => 'SK',

        'employer' => 'EM',
        'employer_id' => 'EM',
        'em' => 'EM',
        'user_employer' => 'EM',
        'user_employers' => 'EM',

        'staff' => 'ST',
        'staff_id' => 'ST',
        'st' => 'ST',
        'user_staff' => 'ST',
        'user_staffs' => 'ST',

        'role' => 'UR',
        'user_role_id' => 'UR',
        'user_role' => 'UR',
        'user_roles' => 'UR',
        'ur' => 'UR',

        'job' => 'JP',
        'job_post_id' => 'JP',
        'request' => 'RQ',
        'request_id' => 'RQ',
        'meeting' => 'MT',
        'meeting_id' => 'MT',
        'review' => 'RV',
        'review_id' => 'RV',
    ];
    $key = strtolower(trim($type));
    $prefix = $map[$key] ?? strtoupper(substr($key, 0, 2));
    return $prefix . $id;
}

/**
 * Tạo link Google Maps trực tiếp từ địa chỉ hoặc URL tùy chỉnh
 */
function getGoogleMapsUrl($address = '', $customMapUrl = '') {
    if (!empty($customMapUrl) && (strpos($customMapUrl, 'http://') === 0 || strpos($customMapUrl, 'https://') === 0)) {
        return $customMapUrl;
    }
    if (empty($address)) return 'https://maps.google.com';
    return 'https://www.google.com/maps/search/?api=1&query=' . urlencode(trim($address));
}

/**
 * Render Icon SVG đơn sắc chuẩn (1 màu thuần túy, kế thừa currentColor hoặc tùy biến theo class)
 */
function icon($name, $class = '', $size = 18) {
    $icons = [
        'home'           => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>',
        'map'            => '<polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line>',
        'map_pin'        => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>',
        'location'       => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>',
        'dashboard'      => '<rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect>',
        'accounts'       => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
        'user'           => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
        'users'          => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
        'staffs'         => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>',
        'shield'         => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>',
        'employers'      => '<rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="22" x2="9" y2="18"></line><line x1="15" y1="22" x2="15" y2="18"></line><line x1="9" y1="6" x2="9" y2="6.01"></line><line x1="15" y1="6" x2="15" y2="6.01"></line><line x1="9" y1="10" x2="9" y2="10.01"></line><line x1="15" y1="10" x2="15" y2="10.01"></line><line x1="9" y1="14" x2="9" y2="14.01"></line><line x1="15" y1="14" x2="15" y2="14.01"></line>',
        'building'       => '<rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="22" x2="9" y2="18"></line><line x1="15" y1="22" x2="15" y2="18"></line><line x1="9" y1="6" x2="9" y2="6.01"></line><line x1="15" y1="6" x2="15" y2="6.01"></line><line x1="9" y1="10" x2="9" y2="10.01"></line><line x1="15" y1="10" x2="15" y2="10.01"></line><line x1="9" y1="14" x2="9" y2="14.01"></line><line x1="15" y1="14" x2="15" y2="14.01"></line>',
        'posts'          => '<path d="m3 11 18-5v12L3 14v-3z"></path><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path>',
        'briefcase'      => '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>',
        'criteria'       => '<line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line>',
        'settings'       => '<circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>',
        'reviews'        => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>',
        'star'           => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>',
        'star_fill'      => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="currentColor"></polygon>',
        'messages'       => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>',
        'bell'           => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>',
        'message_circle' => '<path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"></path>',
        'message_square' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>',
        'support'        => '<path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>',
        'lock'           => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>',
        'key'            => '<circle cx="7.5" cy="15.5" r="5.5"></circle><path d="m21 2-9.6 9.6"></path><path d="m15.5 7.5 3 3"></path>',
        'logout'         => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line>',
        'applied'        => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line>',
        'saved'          => '<path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>',
        'meetings'       => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
        'chat'           => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>',
        'applications'   => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>',
        'chevron_down'   => '<polyline points="6 9 12 15 18 9"></polyline>',
        'chevron_up'     => '<polyline points="18 15 12 9 6 15"></polyline>',
        'chevron_right'  => '<polyline points="9 18 15 12 9 6"></polyline>',
        'chevron_left'   => '<polyline points="15 18 9 12 15 6"></polyline>',
        'globe'          => '<circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>',
        'check'          => '<polyline points="20 6 9 17 4 12"></polyline>',
        'check_circle'   => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>',
        'x'              => '<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>',
        'x_circle'        => '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>',
        'search'         => '<circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>',
        'calendar'       => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
        'clock'          => '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>',
        'phone'          => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>',
        'mail'           => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>',
        'email'          => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>',
        'cake'           => '<path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"></path><path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"></path><line x1="2" y1="21" x2="22" y2="21"></line><line x1="12" y1="8" x2="12" y2="11"></line><line x1="12" y1="3" x2="12" y2="4"></line>',
        'chart'          => '<line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line>',
        'file_text'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>',
        'edit'           => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>',
        'target'         => '<circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle>',
        'rocket'         => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path>',
        'zap'            => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>',
        'lightning'      => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>',
        'lightbulb'      => '<line x1="9" y1="18" x2="15" y2="18"></line><line x1="10" y1="22" x2="14" y2="22"></line><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5.76.76 1.23 1.52 1.41 2.5"></path>',
        'plus'           => '<line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>',
        'inbox'          => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>',
        'link'           => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>',
        'arrow_right'    => '<line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline>',
        'dollar'         => '<line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>',
        'alert_triangle' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
        'sparkle'        => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path>',
        'ai'             => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path>',
        'bookmark'       => '<path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>',
        'info'           => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>',
        'award'          => '<circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>',
        'certificate'    => '<rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="7" y1="8" x2="17" y2="8"></line><line x1="7" y1="12" x2="17" y2="12"></line><polyline points="7 16 9 16 11 16"></polyline><circle cx="15" cy="16" r="2"></circle>',
        'shield_check'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path>',
        'mail_check'     => '<path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h9"></path><polyline points="22,6 12,13 2,6"></polyline><polyline points="16 19 18 21 22 17"></polyline>',
        'bot'            => '<rect width="18" height="12" x="3" y="6" rx="2"></rect><circle cx="9" cy="12" r="1"></circle><circle cx="15" cy="12" r="1"></circle><path d="M12 2v4"></path><path d="M2 12h1"></path><path d="M21 12h1"></path>',
        'file_signature' => '<path d="M20 19.5v.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8.5L18 5.5"></path><path d="M18 11.5 15.5 14l-3-3 2.5-2.5"></path><path d="m14 15.5-2 2H8.5v-3.5l2-2"></path>',
        'handshake'      => '<path d="m11 17 2 2a1 1 0 0 0 1.4 0l4.3-4.3a1 1 0 0 0 0-1.4l-2.6-2.6a1 1 0 0 0-1.4 0L13 12.4"></path><path d="m13 7.6 1.7-1.7a1 1 0 0 1 1.4 0l4.3 4.3a1 1 0 0 1 0 1.4L18.7 13"></path><path d="m3 14 3-3a1 1 0 0 1 1.4 0l2.6 2.6a1 1 0 0 1 0 1.4L8.7 16.3"></path><path d="M8.7 16.3 7 18a1 1 0 0 1-1.4 0L1.3 13.7a1 1 0 0 1 0-1.4L3 11"></path>',
        'sliders'        => '<line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line>',
        'user_check'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline>',
        'scale'          => '<path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path><path d="M7 21h10"></path><path d="M12 3v18"></path><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"></path>',
        'eye'            => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>',
        'unlock'         => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path>',
        'trash'          => '<polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line>',
        'trash_2'        => '<polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line>',
        'cpu'            => '<rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line>',
        'refresh'        => '<polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>',
        'activity'       => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>',
        'download'       => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line>',
        'external_link'  => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line>',
        'reports'        => '<line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line><path d="m19 5 1-2 1 2 2 1-2 1-1 2-1-2-2-1 2-1z"></path>',
        // Bộ Icon Avatar Đặc Trưng Chuẩn Hóa Nhận Diện Nhanh Cho Admin Sidebar
        'nav_dashboard'      => '<rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect><path d="M6.5 7.5h.01M17.5 16.5h.01"></path>',
        'nav_contract'       => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><path d="m14 17 2 2 4-4"></path><path d="M8 9h2"></path>',
        'nav_authorization'  => '<circle cx="12" cy="8" r="5"></circle><path d="m8.5 13-2.5 8 6-3 6 3-2.5-8"></path><path d="m9.5 8 1.5 1.5 3.5-3.5"></path>',
        'nav_employer'       => '<path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16"></path><path d="M9 7h2"></path><path d="M9 11h2"></path><path d="M9 15h2"></path><path d="m16 11 2 2 4-4"></path>',
        'nav_job_post'       => '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path><path d="m9 14 2 2 4-4"></path>',
        'nav_scale_criteria' => '<path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path><path d="M7 21h10"></path><path d="M12 3v18"></path><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"></path>',
        'nav_review_bubble'  => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path><polygon points="12 7 13.3 9.7 16.3 10.1 14.1 12.3 14.6 15.3 12 13.9 9.4 15.3 9.9 12.3 7.7 10.1 10.7 9.7 12 7" fill="currentColor"></polygon>',
        'nav_accounts_id'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
        'nav_staff_badge'    => '<rect x="4" y="3" width="16" height="18" rx="3"></rect><circle cx="12" cy="9.5" r="2.5"></circle><path d="M7.5 17c0-2 2-3.5 4.5-3.5s4.5 1.5 4.5 3.5"></path><path d="M9.5 3h5"></path>',
        'nav_broadcast'      => '<path d="m3 11 18-5v12L3 14v-3z"></path><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path><path d="M21 9a3.5 3.5 0 0 1 0 6"></path><path d="M23 7a6.5 6.5 0 0 1 0 10"></path>',
        'nav_support_headset'=> '<path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path><path d="M19 19a3 3 0 0 1-3 3h-2"></path>',
        'nav_ai_brain'       => '<line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line><path d="m19 5 1-2 1 2 2 1-2 1-1 2-1-2-2-1 2-1z"></path>',
        'nav_gemini_star'    => '<path d="M12 2c0 5.5-4.5 10-10 10 5.5 0 10 4.5 10 10 0-5.5 4.5-10 10-10-5.5 0-10-4.5-10-10z" fill="currentColor"></path><path d="M19 4c0 1.6-1.4 3-3 3 1.6 0 3 1.4 3 3 0-1.6 1.4-3 3-3-1.6 0-3-1.4-3-3z"></path>',
        'nav_security_key'   => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path><circle cx="12" cy="16" r="1.5"></circle><path d="M12 17.5v2"></path>',
        'sidebar_toggle_collapse' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line><path d="m15 9-3 3 3 3"></path>',
        'sidebar_toggle_expand'   => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line><path d="m13 15 3-3-3-3"></path>',
        'menu'                    => '<line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>'
    ];


    $path = $icons[$name] ?? '<circle cx="12" cy="12" r="10"></circle>';
    $cls = 'icon-svg ' . $class;
    return '<svg class="' . trim($cls) . '" width="' . (int)$size . '" height="' . (int)$size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; display: inline-block;">' . $path . '</svg>';
}

/**
 * Phân tích chuỗi ca làm việc thành cấu trúc gồm chế độ ca (mode) và danh sách các ca (shifts)
 * Ví dụ:
 * - "Xoay ca (Ca 1: 7h–12h, Ca 2: 12h–17h, Ca 3: 17h–22h)" -> mode: "Xoay ca", shifts: ["Ca 1: 7h-12h", "Ca 2: 12h-17h", "Ca 3: 17h-22h"]
 * - "Hành chính (8:00 - 17:00)" -> mode: "Hành chính", shifts: ["8:00 - 17:00"]
 * - "Ca gãy (10:00 - 14:00 & 17:00 - 21:30)" -> mode: "Ca gãy", shifts: ["10:00 - 14:00", "17:00 - 21:30"]
 * - "Ca 1: 7h-12h, Ca 2: 12h-17h" -> mode: null, shifts: ["Ca 1: 7h-12h", "Ca 2: 12h-17h"]
 * - "xoay ca 12h -17h" -> mode: "Xoay ca", shifts: ["12h - 17h"]
 * - "Linh hoạt" -> mode: null, shifts: ["Linh hoạt"]
 */
function parseWorkShiftBadges($str) {
    $str = trim((string)$str);
    if (empty($str)) {
        return [
            'mode' => null,
            'shifts' => ['Linh hoạt']
        ];
    }

    $mode = null;
    $shifts = [];

    // Chuẩn hóa ký tự gạch ngang dài
    $normalized = str_replace(['–', '—'], '-', $str);

    // Pattern 1: Tên Chế Độ / Loại Ca (Ca 1: ..., Ca 2: ...) HOẶC (8:00 - 17:00)
    if (preg_match('/^(.*?)\((.*)\)$/u', $normalized, $m)) {
        $prefix = trim($m[1]);
        $inner = trim($m[2]);

        // Nếu trong ngoặc có nhiều ca (vd: "Ca 1: 7h-12h, Ca 2: 12h-17h, Ca 3: 17h-22h" hoặc "10:00 - 14:00 & 17:00 - 21:30")
        if (preg_match('/ca\s*\d+/ui', $inner) || strpos($inner, '&') !== false || strpos($inner, ',') !== false || strpos($inner, ';') !== false) {
            $mode = !empty($prefix) ? $prefix : null;
            // Tách các ca theo dấu phẩy, chấm phẩy hoặc dấu &
            $parts = preg_split('/[,;]|\s+&\s+/u', $inner);
            foreach ($parts as $p) {
                $p = trim($p);
                if (!empty($p)) {
                    $shifts[] = $p;
                }
            }
        } else {
            // Chỉ có 1 khung giờ đơn: vd "Hành chính (8:00 - 17:00)"
            if (!empty($prefix)) {
                $mode = $prefix;
                $shifts[] = $inner;
            } else {
                $shifts[] = $inner;
            }
        }
    } else {
        // Không có dấu ngoặc: kiểm tra xem có "Ca 1: ..., Ca 2: ..." hay không
        if (preg_match('/ca\s*1\s*:/ui', $normalized) && preg_match('/ca\s*2\s*:/ui', $normalized)) {
            $parts = preg_split('/[,;]|\s+&\s+/u', $normalized);
            foreach ($parts as $p) {
                $p = trim($p);
                if (!empty($p)) {
                    $shifts[] = $p;
                }
            }
        } elseif (preg_match('/^(xoay ca|hành chính|ca sáng|ca chiều|ca tối|ca đêm)\s+(\d{1,2}.*)$/ui', $normalized, $m)) {
            $mode = trim($m[1]);
            $shifts[] = trim($m[2]);
        } else {
            $shifts[] = $str;
        }
    }

    return [
        'mode' => $mode,
        'shifts' => $shifts
    ];
}

/**
 * Render danh sách các thẻ ca (badges) riêng biệt, bố trí gọn gàng, chống vỡ dòng
 */
function renderWorkShiftBadges($workShift, $extraClass = '') {
    $parsed = parseWorkShiftBadges($workShift);
    $mode = $parsed['mode'];
    $shifts = $parsed['shifts'];

    $html = '';

    // Nếu có Mode riêng biệt
    if (!empty($mode)) {
        $isXoayCa = (stripos($mode, 'xoay ca') !== false);
        $modeIcon = $isXoayCa ? 'refresh' : (stripos($mode, 'gãy') !== false ? 'zap' : 'briefcase');
        $modeBg = $isXoayCa ? '#eef2ff' : '#f0fdfa';
        $modeColor = $isXoayCa ? '#4338ca' : '#0f766e';
        $modeBorder = $isXoayCa ? '#c7d2fe' : '#99f6e4';

        // Nếu chỉ có 1 ca và shift là khung giờ đơn -> gộp hiển thị 1 thẻ gọn gàng
        if (count($shifts) === 1 && (preg_match('/\d{1,2}[:hH]/u', $shifts[0]) || stripos($shifts[0], '-') !== false)) {
            $html .= '<span class="badge badge-slate ' . htmlspecialchars($extraClass) . '" style="font-size: 0.76rem; font-weight: 600; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; border-radius: 6px; padding: 2.5px 8px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">';
            $html .= icon('clock', '', 11);
            $html .= '<span>' . htmlspecialchars($mode . ': ' . $shifts[0]) . '</span>';
            $html .= '</span>';
            return $html;
        }

        // Nếu có nhiều ca (như Xoay ca: Ca 1, Ca 2, Ca 3...) -> hiển thị thẻ Chế độ làm việc trước
        $html .= '<span class="badge ' . htmlspecialchars($extraClass) . '" style="font-size: 0.74rem; font-weight: 700; background: ' . $modeBg . '; color: ' . $modeColor . '; border: 1px solid ' . $modeBorder . '; border-radius: 6px; padding: 2.5px 8px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;" title="Chế độ làm việc: ' . htmlspecialchars($mode) . '">';
        $html .= icon($modeIcon, '', 11);
        $html .= '<span>' . htmlspecialchars($mode) . '</span>';
        $html .= '</span>';
    }

    // Hiển thị từng thẻ Ca riêng biệt
    foreach ($shifts as $idx => $s) {
        $html .= '<span class="badge badge-slate ' . htmlspecialchars($extraClass) . '" style="font-size: 0.76rem; font-weight: 600; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; border-radius: 6px; padding: 2.5px 8px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">';
        $html .= icon('clock', '', 11);
        $html .= '<span>' . htmlspecialchars($s) . '</span>';
        $html .= '</span>';
    }

    return $html;
}


/**
 * Lấy danh sách 4 slide hiển thị trên Hero Banner Trang Chủ:
 * - Quét thư mục public/uploads/slides/ để tìm ảnh do người dùng bỏ vào (slide1, slide2, 1.jpg, ...).
 * - Nếu KHÔNG CÓ ẢNH (hoặc thiếu ảnh): Tự động áp dụng 4 dải màu xanh lam ngọc pha màu (Mesh Gradient)
 *   riêng biệt, độc bản cho từng slide, đảm bảo thẩm mỹ hiện đại và hài hòa với màu chủ đạo JobLink.
 */
function get_homepage_slides() {
    $slidesDir = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__)) . '/public/uploads/slides';
    if (!is_dir($slidesDir)) {
        @mkdir($slidesDir, 0777, true);
    }

    // 4 Bộ cấu hình mặc định với 4 dải màu pha xanh lam ngọc riêng biệt
    $slides = [
        [
            'id' => 1,
            'badge' => 'Chữ Ký Số (SHA-256) & Hợp Đồng Pháp Lý',
            'badge_icon' => 'certificate',
            'title' => 'Cổng Kết Nối Việc Làm Linh Hoạt & An Toàn Pháp Lý',
            'subtitle' => 'Tra cứu việc làm theo ca, toàn thời gian và bán thời gian tại Bình Dương & Toàn quốc. 100% doanh nghiệp xác thực Chữ Ký Số, minh bạch pháp lý và điểm uy tín người lao động.',
            'cta_primary' => ['text' => 'Đăng Ký Người Lao Động (+100 Điểm Uy Tín)', 'url' => url('/auth/register'), 'icon' => 'user'],
            'cta_secondary' => ['text' => 'Doanh Nghiệp Đăng Tin', 'url' => url('/auth/register'), 'icon' => 'building'],
            'stat_badge' => '5.000+ Người lao động đã xác thực',
            // Dải màu 1: Ngọc Lục Bảo & Lam Ngọc Sáng (Teal & Turquoise Glow)
            'gradient' => 'linear-gradient(135deg, #042f2e 0%, #065f46 25%, #0d9488 65%, #2dd4bf 100%)',
            'glow_color_1' => 'rgba(45, 212, 191, 0.35)',
            'glow_color_2' => 'rgba(13, 148, 136, 0.4)',
            'accent_color' => '#2dd4bf',
            'theme_tag' => 'Xác Thực Pháp Lý',
        ],
        [
            'id' => 2,
            'badge' => '1.200+ Doanh Nghiệp & Đại Lý Cung Ứng Nhân Lực',
            'badge_icon' => 'handshake',
            'title' => 'Tuyển Dụng Nhân Lực Nhanh Chóng & Ủy Quyền Đại Lý',
            'subtitle' => 'Doanh nghiệp đăng tin việc làm ca linh hoạt, ký hợp đồng điện tử trực tiếp và phân quyền đại lý trung gian quản lý nguồn nhân lực chất lượng cao.',
            'cta_primary' => ['text' => 'Đăng Tin Tuyển Dụng Ngay', 'url' => url('/auth/register'), 'icon' => 'building'],
            'cta_secondary' => ['text' => 'Xem Danh Sách Đối Tác', 'url' => url('/employers'), 'icon' => 'briefcase'],
            'stat_badge' => '1.200+ Doanh nghiệp & Đại lý liên kết',
            // Dải màu 2: Biển Lam Ngọc & Xanh Đại Dương Sâu (Deep Ocean Cyan & Sapphire Spark)
            'gradient' => 'linear-gradient(135deg, #082f49 0%, #0e7490 35%, #0891b2 70%, #38bdf8 100%)',
            'glow_color_1' => 'rgba(56, 189, 248, 0.35)',
            'glow_color_2' => 'rgba(8, 145, 178, 0.45)',
            'accent_color' => '#38bdf8',
            'theme_tag' => 'Mạng Lưới Đối Tác',
        ],
        [
            'id' => 3,
            'badge' => 'Điểm Uy Tín Trust Score (0-100) & Đánh Giá 2 Chiều',
            'badge_icon' => 'scale',
            'title' => 'Minh Bạch Thu Nhập & Cam Kết Trách Nhiệm Ca Làm',
            'subtitle' => 'Người lao động ký cam kết điện tử có trách nhiệm, không bùng ca, duy trì Điểm Uy Tín Trust Score 100 điểm và nhận đánh giá công khai sau mỗi ca làm.',
            'cta_primary' => ['text' => 'Khám Phá Thang Điểm Uy Tín', 'url' => url('/auth/register'), 'icon' => 'star'],
            'cta_secondary' => ['text' => 'Tìm Việc Theo Ca', 'url' => url('/jobs?employment_type=part_time'), 'icon' => 'search'],
            'stat_badge' => '99.8% Tỷ lệ phỏng vấn & ca làm chuẩn giờ',
            // Dải màu 3: Ngọc Bích Hoàng Gia & Xanh Tươi Mới (Royal Jade & Vibrant Mint)
            'gradient' => 'linear-gradient(135deg, #022c22 0%, #064e3b 35%, #059669 70%, #34d399 100%)',
            'glow_color_1' => 'rgba(52, 211, 153, 0.35)',
            'glow_color_2' => 'rgba(5, 150, 105, 0.45)',
            'accent_color' => '#34d399',
            'theme_tag' => 'Cam Kết Uy Tín',
        ],
        [
            'id' => 4,
            'badge' => 'Khớp Lệnh Tuyển Dụng AI & Thông Báo Gmail 24/7',
            'badge_icon' => 'mail_check',
            'title' => 'Nhận Việc Liền Tay Với Thông Báo Tự Động 24/7',
            'subtitle' => 'Hệ thống AI tự động gợi ý công việc phù hợp năng lực & GPS, tự động gửi thông báo lịch hẹn phỏng vấn qua Gmail và phòng họp trực tuyến Google Meet.',
            'cta_primary' => ['text' => 'Trải Nghiệm Tìm Việc AI', 'url' => url('/jobs?ai_recommend=1'), 'icon' => 'bot'],
            'cta_secondary' => ['text' => 'Tìm Việc Gần Bạn (GPS)', 'url' => url('/jobs'), 'icon' => 'map_pin'],
            'stat_badge' => '100% Gửi thông báo Gmail tự động 24/7',
            // Dải màu 4: Lam Ngọc Huyền Ảo Pha Tím Hoàng Gia (Twilight Teal & Aurora Indigo)
            'gradient' => 'linear-gradient(135deg, #0f172a 0%, #134e4a 30%, #0f766e 65%, #4f46e5 100%)',
            'glow_color_1' => 'rgba(129, 140, 248, 0.35)',
            'glow_color_2' => 'rgba(15, 118, 110, 0.45)',
            'accent_color' => '#a5b4fc',
            'theme_tag' => 'Công Nghệ AI 24/7',
        ]
    ];

    // Quét các file ảnh có trong thư mục public/uploads/slides/
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    $imageFiles = [];
    if (is_dir($slidesDir)) {
        $files = scandir($slidesDir);
        foreach ($files as $f) {
            if ($f === '.' || $f === '..' || $f === 'README.txt') continue;
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExtensions)) {
                $imageFiles[] = $f;
            }
        }
        sort($imageFiles);
    }

    // Khởi tạo trạng thái ảnh cho từng slide
    $assignedFiles = [];
    $slotImages = array_fill(0, count($slides), null);

    // Bước 1: Khớp chính xác theo số thứ tự (slide1, slide-1, banner1, 1...)
    foreach ($slides as $idx => $slide) {
        $slotNum = $idx + 1;
        foreach ($imageFiles as $img) {
            if (in_array($img, $assignedFiles)) continue;
            $baseName = strtolower(pathinfo($img, PATHINFO_FILENAME));
            if (preg_match('/^(slide|banner)?[-_]?' . $slotNum . '$/i', $baseName)) {
                $slotImages[$idx] = $img;
                $assignedFiles[] = $img;
                break;
            }
        }
    }

    // Bước 2: Với các file còn lại không có số cố định, gán vào các slot còn trống theo thứ tự
    $unassignedFiles = array_values(array_diff($imageFiles, $assignedFiles));
    $unassignedIdx = 0;
    foreach ($slides as $idx => $slide) {
        if ($slotImages[$idx] === null && isset($unassignedFiles[$unassignedIdx])) {
            $slotImages[$idx] = $unassignedFiles[$unassignedIdx];
            $unassignedIdx++;
        }
    }

    // Gán kết quả vào mảng slides
    foreach ($slides as $idx => &$slide) {
        $matchedFile = $slotImages[$idx];
        if ($matchedFile) {
            $slide['image_url'] = asset('uploads/slides/' . $matchedFile);
            $slide['has_custom_image'] = true;
        } else {
            $slide['image_url'] = null;
            $slide['has_custom_image'] = false;
        }
    }
    unset($slide);

    return $slides;
}

/**
 * Lấy URL ảnh đại diện / Logo theo vai trò từ thư mục public/uploads/avatars/
 * 
 * @param string $avatarUrl Đường dẫn hoặc URL ảnh nếu có
 * @param string $roleCode Mã vai trò (JOB_SEEKER, EMPLOYER_ADMIN, SUPER_ADMIN, STAFF, INTERMEDIARY, SYSTEM)
 * @param string $gender Giới tính (Nam, Nữ) nếu có
 * @return string URL hoàn chỉnh của ảnh
 */
function getAvatarUrl($avatarUrl = '', $roleCode = '', $gender = '') {
    if (!empty($avatarUrl)) {
        if (strpos($avatarUrl, 'http://') === 0 || strpos($avatarUrl, 'https://') === 0) {
            return $avatarUrl;
        }
        return asset(ltrim($avatarUrl, '/'));
    }

    $cleanRole = strtoupper(trim($roleCode));
    switch ($cleanRole) {
        case 'SYSTEM':
        case 'LOGO':
            return asset('uploads/avatars/logo.png');

        case 'EMPLOYER_ADMIN':
        case 'EMPLOYER':
        case 'EMPLOYERS':
        case 'INTERMEDIARY':
        case 'INTERMEDIARIES':
            return asset('uploads/avatars/employers/employer_default.png');
            
        case 'JOB_SEEKER':
        case 'SEEKER':
        case 'SEEKERS':
            if (mb_strtolower(trim($gender)) === 'nữ') {
                return asset('uploads/avatars/seekers/seeker_female_default.png');
            }
            return asset('uploads/avatars/seekers/seeker_male_default.png');

        case 'SUPER_ADMIN':
        case 'ADMIN':
            return asset('uploads/avatars/admin/admin_default.png');

        case 'SUB_ADMIN':
        case 'CSKH_STAFF':
        case 'STAFF':
            return asset('uploads/avatars/admin/staff_default.png');

        default:
            return asset('uploads/avatars/logo.png');
    }
}

/**
 * Tải lên tệp ảnh đại diện / Logo người dùng một cách an toàn
 *
 * @param string $fileInputName Tên input trong $_FILES
 * @param string $subfolder Thư mục con ('seekers', 'employers', 'admin')
 * @param string|int $userId Mã người dùng để đặt tên file
 * @return string|null Đường dẫn tương đối lưu vào DB e.g. 'uploads/avatars/seekers/avatar_5_1699999.png', hoặc null nếu không upload
 * @throws Exception Nếu upload lỗi hoặc sai định dạng
 */
function uploadAvatarFile($fileInputName, $subfolder = 'seekers', $userId = '') {
    if (empty($_FILES[$fileInputName]) || empty($_FILES[$fileInputName]['tmp_name']) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fileInputName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Lỗi tải ảnh: mã lỗi ' . $file['error']];
    }

    // 1. Kiểm tra dung lượng (tối đa 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Kích thước tệp ảnh không được vượt quá 5MB!'];
    }

    // 2. Kiểm tra định dạng ảnh hợp lệ
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowedExts)) {
        return ['success' => false, 'message' => 'Định dạng ảnh không hợp lệ! Vui lòng chọn tệp JPG, JPEG, PNG hoặc WEBP.'];
    }

    // Kiểm tra MIME thực tế
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMimes)) {
            return ['success' => false, 'message' => 'Nội dung tệp không phải là hình ảnh hợp lệ!'];
        }
    }

    // 3. Chuẩn bị thư mục lưu trữ
    $safeFolder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $subfolder) ?: 'seekers';
    $targetDir = dirname(__DIR__) . '/public/uploads/avatars/' . $safeFolder;
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0755, true);
    }

    // 4. Tạo tên tệp độc nhất an toàn
    $prefix = $userId ? 'avatar_' . preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$userId) . '_' : 'avatar_';
    $fileName = $prefix . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetFilePath = $targetDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        return ['success' => false, 'message' => 'Không thể lưu tệp ảnh lên máy chủ, vui lòng kiểm tra quyền thư mục uploads!'];
    }

    $relativePath = 'uploads/avatars/' . $safeFolder . '/' . $fileName;
    return [
        'success' => true,
        'url' => $relativePath,
        'path' => $targetFilePath
    ];
}

/**
 * Lấy URL Logo chính của hệ thống JobLink (public/uploads/avatars/logo.png)
 */
function getSystemLogo() {
    return asset('uploads/avatars/logo.png');
}

/**
 * Lấy URL ảnh đại diện của Trợ Lý Joby AI (public/uploads/avatars/joby.png)
 */
function getJobyAvatar() {
    return asset('uploads/avatars/joby.png');
}

/**
 * Lấy dữ liệu thông báo, tin nhắn & AI insight dành riêng cho Header Bar
 * @param array|null $user
 * @return array
 */
function getHeaderNotificationsData($user = null) {
    if ($user === null && isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }

    $result = [
        'unread_messages_count' => 0,
        'recent_messages' => [],
        'unread_notifications_count' => 0,
        'recent_notifications' => [],
        'ai_alert' => '',
        'ai_badge' => 'Joby AI'
    ];

    if (empty($user)) {
        return $result;
    }

    $db = Database::getConnection();
    if (!$db) return $result;

    $accountId = $user['account_id'] ?? 0;
    $role = $user['role_code'] ?? ($user['role'] ?? 'GUEST');

    try {
        // 1. LẤY SỐ TIN NHẮN CHƯA ĐỌC & DANH SÁCH CUỘC TRÒ CHUYỆN (MESSENGER STYLE)
        if ($accountId > 0) {
            try {
                $stmtUnread = $db->prepare("SELECT COUNT(*) FROM messages WHERE receiver_account_id = :acc AND is_read = 0");
                $stmtUnread->execute([':acc' => $accountId]);
                $result['unread_messages_count'] = (int)$stmtUnread->fetchColumn();
            } catch (Exception $e) {
                $result['unread_messages_count'] = 0;
            }

            $conversations = [];
            try {
                if (class_exists('Message')) {
                    $conversations = Message::getConversations($accountId);
                }
            } catch (Exception $e) {
                error_log("[Header Message Load Error] " . $e->getMessage());
            }

            $result['conversations'] = $conversations;
            $result['recent_messages'] = $conversations;
        }

        // 2. LẤY THÔNG BÁO HOẠT ĐỘNG THEO ROLE
        $notis = [];
        if (in_array($role, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])) {
            $employer = class_exists('UserEmployer') ? UserEmployer::findByAccountId($accountId) : null;
            $eid = $employer['employer_id'] ?? 0;
            if ($eid > 0) {
                // Hồ sơ ứng tuyển mới
                $stmtApps = $db->prepare("
                    SELECT r.request_id, r.applied_at as created_at, s.full_name as title, p.job_title as subtitle,
                           'application' as type, '/employer/applications' as link
                    FROM job_requests r
                    JOIN job_posts p ON r.job_post_id = p.job_post_id
                    JOIN user_seekers s ON r.seeker_id = s.seeker_id
                    WHERE p.employer_id = :eid AND r.request_status = 'applied'
                    ORDER BY r.applied_at DESC LIMIT 5
                ");
                $stmtApps->execute([':eid' => $eid]);
                $notis = array_merge($notis, $stmtApps->fetchAll(PDO::FETCH_ASSOC));

                // Lịch phỏng vấn sắp tới
                $stmtMeet = $db->prepare("
                    SELECT m.meeting_id, CONCAT(m.meeting_date, ' ', m.start_time) as created_at, s.full_name as title, p.job_title as subtitle,
                           'meeting' as type, '/employer/meetings' as link
                    FROM candidate_meetings m
                    JOIN job_requests r ON m.request_id = r.request_id
                    JOIN job_posts p ON r.job_post_id = p.job_post_id
                    JOIN user_seekers s ON r.seeker_id = s.seeker_id
                    WHERE p.employer_id = :eid AND m.meeting_status = 'scheduled' AND m.meeting_date >= CURDATE()
                    ORDER BY m.meeting_date ASC, m.start_time ASC LIMIT 3
                ");
                $stmtMeet->execute([':eid' => $eid]);
                $notis = array_merge($notis, $stmtMeet->fetchAll(PDO::FETCH_ASSOC));
            }
        } elseif ($role === 'JOB_SEEKER') {
            $seeker = class_exists('UserSeeker') ? UserSeeker::findByAccountId($accountId) : null;
            $sid = $seeker['seeker_id'] ?? 0;
            if ($sid > 0) {
                // Cập nhật trạng thái ứng tuyển
                $stmtReqs = $db->prepare("
                    SELECT r.request_id, r.status_updated_at as created_at, e.employer_name as title, p.job_title as subtitle,
                           r.request_status as status, 'job_status' as type, '/seeker/applied-jobs' as link
                    FROM job_requests r
                    JOIN job_posts p ON r.job_post_id = p.job_post_id
                    JOIN user_employers e ON p.employer_id = e.employer_id
                    WHERE r.seeker_id = :sid AND r.status_updated_at IS NOT NULL
                    ORDER BY r.status_updated_at DESC LIMIT 5
                ");
                $stmtReqs->execute([':sid' => $sid]);
                $notis = array_merge($notis, $stmtReqs->fetchAll(PDO::FETCH_ASSOC));

                // Lịch phỏng vấn
                $stmtMeet = $db->prepare("
                    SELECT m.meeting_id, CONCAT(m.meeting_date, ' ', m.start_time) as created_at, e.employer_name as title, p.job_title as subtitle,
                           'meeting' as type, '/seeker/meetings' as link
                    FROM candidate_meetings m
                    JOIN job_requests r ON m.request_id = r.request_id
                    JOIN job_posts p ON r.job_post_id = p.job_post_id
                    JOIN user_employers e ON p.employer_id = e.employer_id
                    WHERE r.seeker_id = :sid AND m.meeting_status = 'scheduled' AND m.meeting_date >= CURDATE()
                    ORDER BY m.meeting_date ASC, m.start_time ASC LIMIT 3
                ");
                $stmtMeet->execute([':sid' => $sid]);
                $notis = array_merge($notis, $stmtMeet->fetchAll(PDO::FETCH_ASSOC));
            }
        } elseif (in_array($role, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
            // Tin tuyển dụng chờ duyệt
            $stmtJobs = $db->query("
                SELECT p.job_post_id, p.posted_at as created_at, p.job_title as title, e.employer_name as subtitle,
                       'pending_job' as type, '/admin/job-posts' as link
                FROM job_posts p
                JOIN user_employers e ON p.employer_id = e.employer_id
                WHERE p.post_status = 'pending'
                ORDER BY p.posted_at DESC LIMIT 5
            ");
            if ($stmtJobs) {
                $notis = array_merge($notis, $stmtJobs->fetchAll(PDO::FETCH_ASSOC));
            }

            // Doanh nghiệp chờ xác minh
            $stmtEmp = $db->query("
                SELECT employer_id, created_at, employer_name as title, business_license_number as subtitle,
                       'unverified_employer' as type, '/admin/employers' as link
                FROM user_employers
                WHERE verification_status = 'unverified' OR verification_status = 'pending'
                ORDER BY employer_id DESC LIMIT 3
            ");
            if ($stmtEmp) {
                $notis = array_merge($notis, $stmtEmp->fetchAll(PDO::FETCH_ASSOC));
            }
        }

        // Kiểm tra thời điểm đánh dấu đọc gần nhất của tài khoản
        $lastReadAt = $_SESSION['noti_last_read_at'] ?? null;
        if ($lastReadAt === null && $accountId > 0) {
            try {
                $stmtRead = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :k LIMIT 1");
                $stmtRead->execute([':k' => 'noti_read_at_' . $accountId]);
                $val = $stmtRead->fetchColumn();
                if ($val) {
                    $lastReadAt = $val;
                    $_SESSION['noti_last_read_at'] = $lastReadAt;
                }
            } catch (Exception $e) {}
        }

        $lastReadTime = !empty($lastReadAt) ? strtotime($lastReadAt) : 0;
        $unreadCount = 0;
        foreach ($notis as &$noti) {
            $createdTime = !empty($noti['created_at']) ? strtotime($noti['created_at']) : 0;
            // Nếu đã có mốc đọc và thông báo tạo trước hoặc đúng thời điểm mốc đọc -> đã đọc
            $isRead = ($lastReadTime > 0 && $createdTime > 0 && $createdTime <= $lastReadTime);
            $noti['is_read'] = $isRead;
            if (!$isRead) {
                $unreadCount++;
            }
        }
        unset($noti);

        $result['recent_notifications'] = $notis;
        $result['unread_notifications_count'] = $unreadCount;
    } catch (PDOException $e) {
        error_log("[getHeaderNotificationsData Error] " . $e->getMessage());
    }

    return $result;
}

/**
 * Chuẩn hóa tên vai trò hiển thị trên thẻ badge ở header thanh menu (Sidebar)
 * Đảm bảo ngắn gọn, không bị xuống hàng (wrap) gây biến dạng thẻ
 * @param string $roleCode
 * @param string $roleName
 * @return string
 */
function getSidebarRoleBadge($roleCode, $roleName = '') {
    $map = [
        'SUPER_ADMIN'    => 'Admin',
        'SUB_ADMIN'      => 'Sub Admin',
        'CSKH_STAFF'     => 'CSKH',
        'EMPLOYER_ADMIN' => 'Doanh Nghiệp',
        'INTERMEDIARY'   => 'Trung Gian',
        'JOB_SEEKER'     => 'Người Lao Động',
    ];

    $code = strtoupper(trim((string)$roleCode));
    if (isset($map[$code])) {
        return $map[$code];
    }

    if (!empty($roleName)) {
        $clean = trim((string)$roleName);
        if (mb_strlen($clean) <= 12) {
            return $clean;
        }
        $clean = str_ireplace('Người Tìm Việc', 'Người Lao Động', $clean);
        $clean = str_ireplace(['Nhà Tuyển Dụng', 'Doanh Nghiệp Tuyển Dụng'], 'Doanh Nghiệp', $clean);
        $clean = str_ireplace(['Người Trung Gian', 'Đại Diện Trung Gian'], 'Trung Gian', $clean);
        $clean = str_ireplace(['Quản Trị Viên Cấp Cao', 'Quản Trị Viên'], 'Admin', $clean);
        $clean = str_ireplace(['Chuyên Viên CSKH', 'Nhân Viên CSKH'], 'CSKH', $clean);
        if (mb_strlen($clean) <= 12) {
            return $clean;
        }
        return mb_substr($clean, 0, 10) . '..';
    }

    return 'Thành Viên';
}

/**
 * Lấy tóm tắt AI thông minh, cô đọng (ít chữ, làm nổi bật nội dung cốt lõi và số liệu đầu tiên)
 * Dùng cho thẻ Joby AI Tóm Tắt trong popup Thông Báo và trang Quản trị thông báo
 * @param array|null $user
 * @return string
 */
function getAiNotiSummary($user = null) {
    if ($user === null && isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }

    $db = Database::getConnection();
    $role = $user['role_code'] ?? ($user['role'] ?? 'GUEST');
    $accountId = $user['account_id'] ?? 0;

    // 1. Quản trị viên hệ thống (SUPER_ADMIN, SUB_ADMIN, CSKH_STAFF)
    if (in_array($role, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
        $pendingJobs = 0;
        $pendingEmployers = 0;
        if ($db) {
            try {
                $pendingJobs = (int)$db->query("SELECT COUNT(*) FROM job_posts WHERE post_status = 'pending'")->fetchColumn();
                $pendingEmployers = (int)$db->query("SELECT COUNT(*) FROM user_employers WHERE verification_status = 'unverified' OR verification_status = 'pending'")->fetchColumn();
            } catch (Exception $e) {}
        }

        $chips = [];
        if ($pendingEmployers > 0) {
            $chips[] = '<a href="' . url('/admin/employers') . '" class="ai-chip warning ai-chip-link workspace-nav-link" data-title="Xác Minh Doanh Nghiệp" title="Bấm để chuyển tới trang thẩm định doanh nghiệp">' . icon('alert_triangle', '', 13) . ' <strong>' . $pendingEmployers . '</strong> DN chờ duyệt GPKD</a>';
        }
        if ($pendingJobs > 0) {
            $chips[] = '<a href="' . url('/admin/job-posts') . '" class="ai-chip warning ai-chip-link workspace-nav-link" data-title="Duyệt Tin Tuyển Dụng" title="Bấm để chuyển tới trang duyệt tin tuyển dụng">' . icon('file_text', '', 13) . ' <strong>' . $pendingJobs . '</strong> Tin đăng chờ duyệt</a>';
        }

        if (empty($chips)) {
            return '<div class="ai-summary-chips"><span class="ai-chip success">' . icon('check_circle', '', 13) . ' <strong>Hệ thống ổn định</strong> • 0 việc tồn đọng</span></div>'
                 . '<div class="ai-summary-text">Tất cả tin đăng và doanh nghiệp đã được kiểm duyệt.</div>';
        }

        if ($pendingEmployers > 0 && $pendingJobs === 0) {
            $chips[] = '<a href="' . url('/admin/job-posts') . '" class="ai-chip neutral ai-chip-link workspace-nav-link" data-title="Duyệt Tin Tuyển Dụng">' . icon('check', '', 12) . ' <strong>0</strong> Tin chờ duyệt</a>';
        } elseif ($pendingJobs > 0 && $pendingEmployers === 0) {
            $chips[] = '<a href="' . url('/admin/employers') . '" class="ai-chip neutral ai-chip-link workspace-nav-link" data-title="Xác Minh Doanh Nghiệp">' . icon('check', '', 12) . ' <strong>0</strong> DN chờ GPKD</a>';
        }

        $desc = ($pendingEmployers > 0 && $pendingJobs > 0)
            ? 'Cần thẩm định GPKD và phê duyệt tin đăng mới.'
            : ($pendingEmployers > 0 ? 'Cần kiểm tra giấy phép kinh doanh để cấp xác thực.' : 'Cần kiểm duyệt nội dung tin tuyển dụng mới.');

        return '<div class="ai-summary-chips">' . implode('', $chips) . '</div>'
             . '<div class="ai-summary-text">' . $desc . '</div>';
    }

    // 2. Nhà tuyển dụng & Người trung gian (EMPLOYER_ADMIN, INTERMEDIARY)
    if (in_array($role, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])) {
        $employer = class_exists('UserEmployer') ? UserEmployer::findByAccountId($accountId) : null;
        $eid = $employer['employer_id'] ?? 0;
        $pendingApps = 0;
        $activeJobs = 0;
        $upcomingMeetings = 0;

        if ($db && $eid > 0) {
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM job_requests r JOIN job_posts p ON r.job_post_id = p.job_post_id WHERE p.employer_id = :eid AND r.request_status = 'applied'");
                $stmt->execute([':eid' => $eid]);
                $pendingApps = (int)$stmt->fetchColumn();

                $stmt2 = $db->prepare("SELECT COUNT(*) FROM job_posts WHERE employer_id = :eid AND post_status = 'approved'");
                $stmt2->execute([':eid' => $eid]);
                $activeJobs = (int)$stmt2->fetchColumn();

                $stmt3 = $db->prepare("SELECT COUNT(*) FROM candidate_meetings m JOIN job_requests r ON m.request_id = r.request_id JOIN job_posts p ON r.job_post_id = p.job_post_id WHERE p.employer_id = :eid AND m.meeting_status = 'scheduled' AND m.meeting_date >= CURDATE()");
                $stmt3->execute([':eid' => $eid]);
                $upcomingMeetings = (int)$stmt3->fetchColumn();
            } catch (Exception $e) {}
        }

        $chips = [];
        if ($pendingApps > 0) {
            $chips[] = '<a href="' . url('/employer/applications') . '" class="ai-chip warning ai-chip-link workspace-nav-link" data-title="Hồ Sơ Ứng Tuyển">' . icon('users', '', 13) . ' <strong>' . $pendingApps . '</strong> Người lao động mới</a>';
        }
        if ($upcomingMeetings > 0) {
            $chips[] = '<a href="' . url('/employer/meetings') . '" class="ai-chip primary ai-chip-link workspace-nav-link" data-title="Lịch Phỏng Vấn">' . icon('calendar', '', 13) . ' <strong>' . $upcomingMeetings . '</strong> Lịch phỏng vấn</a>';
        }

        if (!empty($chips)) {
            return '<div class="ai-summary-chips">' . implode('', $chips) . '</div>'
                 . '<div class="ai-summary-text">Có hồ sơ mới cần sàng lọc và phản hồi người lao động.</div>';
        }

        if ($activeJobs > 0) {
            return '<div class="ai-summary-chips"><a href="' . url('/employer/jobs') . '" class="ai-chip primary ai-chip-link workspace-nav-link" data-title="Danh Sách Tin Đăng">' . icon('briefcase', '', 13) . ' <strong>' . $activeJobs . '</strong> Tin đang tuyển</a></div>'
                 . '<div class="ai-summary-text">Hệ thống đang tự động gợi ý hồ sơ phù hợp.</div>';
        }

        return '<div class="ai-summary-chips"><span class="ai-chip neutral">' . icon('briefcase', '', 13) . ' <strong>Chưa có tin tuyển</strong></span></div>'
             . '<div class="ai-summary-text">Đăng tin để tiếp cận ngay người lao động có Ký Số.</div>';
    }

    // 3. Người tìm việc (JOB_SEEKER)
    if ($role === 'JOB_SEEKER') {
        $seeker = class_exists('UserSeeker') ? UserSeeker::findByAccountId($accountId) : null;
        $sid = $seeker['seeker_id'] ?? 0;
        $trustScore = (int)($seeker['trust_score'] ?? 100);

        $newJobsCount = 0;
        $activeApplications = 0;
        if ($db) {
            try {
                $newJobsCount = (int)$db->query("SELECT COUNT(*) FROM job_posts WHERE post_status = 'approved'")->fetchColumn();
                if ($sid > 0) {
                    $stmtApp = $db->prepare("SELECT COUNT(*) FROM job_requests WHERE seeker_id = :sid AND request_status IN ('applied', 'reviewing', 'interview_scheduled')");
                    $stmtApp->execute([':sid' => $sid]);
                    $activeApplications = (int)$stmtApp->fetchColumn();
                }
            } catch (Exception $e) {}
        }

        if ($activeApplications > 0) {
            return '<div class="ai-summary-chips">'
                 . '<a href="' . url('/seeker/applied-jobs') . '" class="ai-chip primary ai-chip-link workspace-nav-link" data-title="Hồ Sơ Đã Ứng Tuyển">' . icon('file_text', '', 13) . ' <strong>' . $activeApplications . '</strong> Hồ sơ đang xử lý</a>'
                 . '<span class="ai-chip success">' . icon('shield', '', 13) . ' Uy tín: <strong>' . $trustScore . '/100</strong></span>'
                 . '</div>'
                 . '<div class="ai-summary-text">Nhà tuyển dụng đang đánh giá hồ sơ ứng tuyển của bạn.</div>';
        }

        return '<div class="ai-summary-chips">'
             . '<a href="' . url('/jobs') . '" class="ai-chip primary ai-chip-link workspace-nav-link" data-title="Tìm Việc Làm">' . icon('briefcase', '', 13) . ' <strong>' . ($newJobsCount > 0 ? $newJobsCount . '+' : 'Nhiều') . '</strong> Việc làm mới</a>'
             . '<span class="ai-chip success">' . icon('shield', '', 13) . ' Uy tín: <strong>' . $trustScore . '/100</strong></span>'
             . '</div>'
             . '<div class="ai-summary-text">Nhiều cơ hội việc làm có Ký Số phù hợp với bạn.</div>';
    }

    // 4. Khách vãng lai (GUEST)
    $totalJobs = 0;
    if ($db) {
        try {
            $totalJobs = (int)$db->query("SELECT COUNT(*) FROM job_posts WHERE post_status = 'approved'")->fetchColumn();
        } catch (Exception $e) {}
    }

    return '<div class="ai-summary-chips">'
         . '<span class="ai-chip primary">' . icon('briefcase', '', 13) . ' <strong>' . ($totalJobs > 0 ? $totalJobs . '+' : 'Nhiều') . '</strong> Việc làm Ký Số</span>'
         . '<span class="ai-chip success">' . icon('shield', '', 13) . ' <strong>100%</strong> Định danh</span>'
         . '</div>'
         . '<div class="ai-summary-text">Sàn kết nối việc làm minh bạch và an toàn.</div>';
}

/**
 * Tạo câu chào ngắn gọn, thông minh và tích hợp số liệu thực tế theo vai trò cho Joby AI
 * @param array|null $user
 * @return string
 */
function getAiGreeting($user = null) {
    if ($user === null && isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }

    $db = Database::getConnection();

    if (empty($user)) {
        return "Chào bạn! JobLink có nhiều việc làm đã xác thực Ký Số. Bạn cần hỗ trợ tìm kiếm công việc gì hôm nay?";
    }

    $role = $user['role_code'] ?? ($user['role'] ?? 'GUEST');
    $accountId = $user['account_id'] ?? 0;

    // 1. Nhà tuyển dụng & Người trung gian (EMPLOYER_ADMIN / INTERMEDIARY)
    if (in_array($role, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])) {
        $employer = class_exists('UserEmployer') ? UserEmployer::findByAccountId($accountId) : null;
        $eid = $employer['employer_id'] ?? 0;
        $pendingApps = 0;

        if ($db && $eid > 0) {
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM job_requests r JOIN job_posts p ON r.job_post_id = p.job_post_id WHERE p.employer_id = :eid AND r.request_status = 'applied'");
                $stmt->execute([':eid' => $eid]);
                $pendingApps = (int)$stmt->fetchColumn();
            } catch (Exception $e) {}
        }

        if ($pendingApps > 0) {
            return "Doanh nghiệp có <strong>{$pendingApps} người lao động mới</strong> đang chờ duyệt hồ sơ.";
        }
        return "Chào bạn! Mình sẵn sàng hỗ trợ đăng tin tuyển dụng và tìm kiếm người lao động.";
    }

    // 2. Người lao động (JOB_SEEKER)
    if ($role === 'JOB_SEEKER') {
        $seeker = class_exists('UserSeeker') ? UserSeeker::findByAccountId($accountId) : null;
        $sid = $seeker['seeker_id'] ?? 0;
        $activeApplications = 0;

        if ($db && $sid > 0) {
            try {
                $stmtApp = $db->prepare("SELECT COUNT(*) FROM job_requests WHERE seeker_id = :sid AND request_status IN ('applied', 'reviewing', 'interview_scheduled')");
                $stmtApp->execute([':sid' => $sid]);
                $activeApplications = (int)$stmtApp->fetchColumn();
            } catch (Exception $e) {}
        }

        if ($activeApplications > 0) {
            return "Bạn đang có <strong>{$activeApplications} hồ sơ ứng tuyển</strong> đang được xử lý.";
        }
        return "Chào bạn! Mình có thể gợi ý những việc làm có Ký Số phù hợp nhất cho bạn.";
    }

    // 3. Nhân viên hệ thống (SUPER_ADMIN, SUB_ADMIN, CSKH_STAFF)
    if (in_array($role, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
        $pendingJobs = 0;
        $pendingEmployers = 0;
        if ($db) {
            try {
                $pendingJobs = (int)$db->query("SELECT COUNT(*) FROM job_posts WHERE post_status = 'pending'")->fetchColumn();
                $pendingEmployers = (int)$db->query("SELECT COUNT(*) FROM user_employers WHERE verification_status = 'unverified' OR verification_status = 'pending'")->fetchColumn();
            } catch (Exception $e) {}
        }

        if ($pendingEmployers > 0 || $pendingJobs > 0) {
            return "Hệ thống có <strong>{$pendingEmployers} DN chờ duyệt GPKD</strong> và <strong>{$pendingJobs} tin đăng chờ duyệt</strong>.";
        }
        return "Hệ thống ổn định, không có tác vụ kiểm duyệt nào đang chờ xử lý.";
    }

    return "Chào bạn! Mình là Joby, sẵn sàng hỗ trợ bạn hôm nay.";
}

/**
 * Lấy dữ liệu tóm lược thông minh hiển thị trực quan trên Bong bóng Joby AI (Badges / Mini KPI Cards)
 * @param array|null $user
 * @return array
 */
function getAiProactiveSummary($user = null) {
    if ($user === null && isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }

    $db = Database::getConnection();
    $role = $user['role_code'] ?? ($user['role'] ?? 'GUEST');
    $accountId = $user['account_id'] ?? 0;

    $tag = 'Cập nhật nhanh';
    $btnText = 'Xử lý ngay';
    $cards = [];

    // 1. Quản trị viên (SUPER_ADMIN, SUB_ADMIN, CSKH_STAFF)
    if (in_array($role, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
        $pendingJobs = 0;
        $pendingEmployers = 0;
        if ($db) {
            try {
                $pendingJobs = (int)$db->query("SELECT COUNT(*) FROM job_posts WHERE post_status = 'pending'")->fetchColumn();
                $pendingEmployers = (int)$db->query("SELECT COUNT(*) FROM user_employers WHERE verification_status = 'unverified' OR verification_status = 'pending'")->fetchColumn();
            } catch (Exception $e) {}
        }
        $tag = 'Tác vụ điều hành';
        $btnText = 'Kiểm tra ngay';

        $cards[] = [
            'type' => $pendingEmployers > 0 ? 'warning' : 'success',
            'icon' => $pendingEmployers > 0 ? icon('alert_triangle', '', 16) : icon('check_circle', '', 16),
            'num' => $pendingEmployers,
            'label' => 'DN chờ duyệt GPKD',
            'query' => 'Kiểm tra DN chờ xác minh GPKD'
        ];

        $cards[] = [
            'type' => $pendingJobs > 0 ? 'warning' : 'neutral',
            'icon' => $pendingJobs > 0 ? icon('file_text', '', 16) : icon('check_circle', '', 16),
            'num' => $pendingJobs,
            'label' => 'Tin đăng chờ duyệt',
            'query' => 'Duyệt tin tuyển dụng chờ xử lý'
        ];
    }
    // 2. Nhà tuyển dụng / Trung gian (EMPLOYER_ADMIN, INTERMEDIARY)
    elseif (in_array($role, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])) {
        $employer = class_exists('UserEmployer') ? UserEmployer::findByAccountId($accountId) : null;
        $eid = $employer['employer_id'] ?? 0;
        $pendingApps = 0;
        $activeJobs = 0;
        $upcomingMeetings = 0;

        if ($db && $eid > 0) {
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM job_requests r JOIN job_posts p ON r.job_post_id = p.job_post_id WHERE p.employer_id = :eid AND r.request_status = 'applied'");
                $stmt->execute([':eid' => $eid]);
                $pendingApps = (int)$stmt->fetchColumn();

                $stmt2 = $db->prepare("SELECT COUNT(*) FROM job_posts WHERE employer_id = :eid AND post_status = 'approved'");
                $stmt2->execute([':eid' => $eid]);
                $activeJobs = (int)$stmt2->fetchColumn();

                $stmt3 = $db->prepare("SELECT COUNT(*) FROM candidate_meetings m JOIN job_requests r ON m.request_id = r.request_id JOIN job_posts p ON r.job_post_id = p.job_post_id WHERE p.employer_id = :eid AND m.meeting_status = 'scheduled' AND m.meeting_date >= CURDATE()");
                $stmt3->execute([':eid' => $eid]);
                $upcomingMeetings = (int)$stmt3->fetchColumn();
            } catch (Exception $e) {}
        }
        $tag = 'Tuyển dụng';
        $btnText = 'Xem hồ sơ';

        $cards[] = [
            'type' => $pendingApps > 0 ? 'warning' : 'neutral',
            'icon' => $pendingApps > 0 ? icon('users', '', 16) : icon('check_circle', '', 16),
            'num' => $pendingApps,
            'label' => 'Người lao động mới',
            'query' => 'Sàng lọc hồ sơ người lao động'
        ];

        if ($upcomingMeetings > 0) {
            $cards[] = [
                'type' => 'neutral',
                'icon' => icon('calendar', '', 16),
                'num' => $upcomingMeetings,
                'label' => 'Lịch phỏng vấn',
                'query' => 'Lịch hẹn phỏng vấn'
            ];
        } else {
            $cards[] = [
                'type' => 'neutral',
                'icon' => icon('briefcase', '', 16),
                'num' => $activeJobs,
                'label' => 'Tin đang tuyển',
                'query' => 'Đăng tin tuyển dụng mới'
            ];
        }
    }
    // 3. Người tìm việc (JOB_SEEKER)
    elseif ($role === 'JOB_SEEKER') {
        $seeker = class_exists('UserSeeker') ? UserSeeker::findByAccountId($accountId) : null;
        $sid = $seeker['seeker_id'] ?? 0;
        $trustScore = (int)($seeker['trust_score'] ?? 100);
        $newJobsCount = 0;
        $activeApplications = 0;

        if ($db) {
            try {
                $newJobsCount = (int)$db->query("SELECT COUNT(*) FROM job_posts WHERE post_status = 'approved'")->fetchColumn();
                if ($sid > 0) {
                    $stmtApp = $db->prepare("SELECT COUNT(*) FROM job_requests WHERE seeker_id = :sid AND request_status IN ('applied', 'reviewing', 'interview_scheduled')");
                    $stmtApp->execute([':sid' => $sid]);
                    $activeApplications = (int)$stmtApp->fetchColumn();
                }
            } catch (Exception $e) {}
        }
        $tag = 'Cơ hội việc làm';
        $btnText = 'Xem việc ngay';

        if ($activeApplications > 0) {
            $cards[] = [
                'type' => 'neutral',
                'icon' => icon('file_text', '', 16),
                'num' => $activeApplications,
                'label' => 'Đơn đang ứng tuyển',
                'query' => 'Xem đơn ứng tuyển của tôi'
            ];
        } else {
            $cards[] = [
                'type' => 'neutral',
                'icon' => icon('briefcase', '', 16),
                'num' => $newJobsCount,
                'label' => 'Việc làm mới',
                'query' => 'Tìm việc làm mới nhất'
            ];
        }

        $cards[] = [
            'type' => $trustScore >= 80 ? 'success' : 'warning',
            'icon' => icon('star', '', 16),
            'num' => $trustScore . '/100',
            'label' => 'Điểm Uy Tín',
            'query' => 'Cách tăng Điểm Uy Tín'
        ];
    }
    // 4. Khách vãng lai (GUEST)
    else {
        $totalJobs = 0;
        if ($db) {
            try {
                $totalJobs = (int)$db->query("SELECT COUNT(*) FROM job_posts WHERE post_status = 'approved'")->fetchColumn();
            } catch (Exception $e) {}
        }
        $tag = 'Việc làm uy tín';
        $btnText = 'Khám phá ngay';

        $cards[] = [
            'type' => 'neutral',
            'icon' => icon('briefcase', '', 16),
            'num' => $totalJobs . '+',
            'label' => 'Việc làm có Ký Số',
            'query' => 'Tìm việc làm nhanh'
        ];

        $cards[] = [
            'type' => 'success',
            'icon' => icon('shield', '', 16),
            'num' => '100%',
            'label' => 'Xác thực định danh',
            'query' => 'Chữ ký số doanh nghiệp là gì?'
        ];
    }

    return [
        'tag' => $tag,
        'btn_text' => $btnText,
        'cards' => $cards
    ];
}

/**
 * Render cụm icon Trao đổi tin nhắn & Thông báo (đặt ở bên trái user-dropdown-container)
 * @param array|null $user
 * @return string
 */
function renderHeaderIcons($user = null) {
    if ($user === null && isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }
    if (empty($user)) return '';

    $data = getHeaderNotificationsData($user);
    $role = $user['role_code'] ?? ($user['role'] ?? 'GUEST');

    $msgUrl = url('/seeker/messages');
    $msgTabTitle = 'Tin Nhắn Trao Đổi';
    if (in_array($role, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])) {
        $msgUrl = url('/employer/messages');
        $msgTabTitle = 'Tin Nhắn Trao Đổi';
    } elseif (in_array($role, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
        $msgUrl = url('/admin/messages');
        $msgTabTitle = 'Tin Nhắn & Hỗ Trợ';
    }

    $notiUrl = url('/seeker/applied-jobs');
    $notiTabTitle = 'Tất Cả Thông Báo';
    if (in_array($role, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])) {
        $notiUrl = url('/employer/applications');
        $notiTabTitle = 'Hồ Sơ Ứng Tuyển';
    } elseif (in_array($role, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
        $notiUrl = url('/admin/notifications');
        $notiTabTitle = 'Tất Cả Thông Báo';
    }

    $unreadMsgs = $data['unread_messages_count'];
    $unreadNotis = $data['unread_notifications_count'];
    $conversations = $data['conversations'] ?? ($data['recent_messages'] ?? []);
    $recentNotis = $data['recent_notifications'];
    $aiNotiSummary = getAiNotiSummary($user);
    $accountId = $user['account_id'] ?? 0;

    ob_start();
    ?>
    <div class="nav-header-icons">
        <!-- 1. NOTIFICATION ICON & DROPDOWN -->
        <div class="header-icon-container nav-noti-container">
            <button type="button" class="header-icon-btn noti-trigger" title="Thông báo hệ thống & AI" aria-label="Thông báo">
                <?= icon('bell', '', 19) ?>
                <?php if ($unreadNotis > 0): ?>
                    <span class="header-badge"><?= $unreadNotis > 9 ? '9+' : $unreadNotis ?></span>
                <?php endif; ?>
            </button>

            <div class="header-dropdown-menu noti-dropdown-menu">
                <div class="header-dropdown-title">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span>Thông Báo</span>
                        <span class="badge badge-primary noti-unread-badge" style="font-size:0.75rem; padding: 2px 8px; <?= $unreadNotis > 0 ? '' : 'display:none;' ?>"><?= $unreadNotis ?> mới</span>
                    </div>
                    <?php if ($unreadNotis > 0): ?>
                        <button type="button" 
                                id="btnMarkAllNotiRead" 
                                class="btn-mark-all-read" 
                                title="Đánh dấu tất cả thông báo là đã đọc" 
                                onclick="window.markAllNotificationsRead && window.markAllNotificationsRead(event)">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Đánh dấu tất cả đã đọc</span>
                        </button>
                    <?php else: ?>
                        <button type="button" 
                                id="btnMarkAllNotiRead" 
                                class="btn-mark-all-read is-disabled" 
                                title="Tất cả thông báo đã được đọc" 
                                disabled 
                                style="opacity:0.7; color:#10b981; cursor:default;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Đã đọc tất cả</span>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- JOBY AI SMART SUMMARY -->
                <div class="header-ai-alert-card">
                    <div class="ai-alert-header">
                        <span class="ai-alert-badge"><?= icon('ai', '', 14) ?> Joby AI Tóm Tắt</span>
                    </div>
                    <div class="ai-alert-body">
                        <?= $aiNotiSummary ?>
                    </div>
                </div>

                <!-- NOTIFICATIONS LIST -->
                <div class="header-dropdown-list">
                    <?php if (empty($recentNotis)): ?>
                        <div class="header-dropdown-empty">
                            <?= icon('check_circle', '', 24) ?>
                            <p>Không có thông báo mới nào</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentNotis as $noti): ?>
                            <?php
                                $itemTabTitle = 'Chi Tiết';
                                if (($noti['type'] ?? '') === 'pending_job') $itemTabTitle = 'Duyệt Tin Tuyển Dụng';
                                elseif (($noti['type'] ?? '') === 'unverified_employer') $itemTabTitle = 'Xác Minh Doanh Nghiệp';
                                elseif (($noti['type'] ?? '') === 'application') $itemTabTitle = 'Hồ Sơ Ứng Tuyển';
                                elseif (($noti['type'] ?? '') === 'meeting') $itemTabTitle = 'Lịch Phỏng Vấn';
                                $isUnread = empty($noti['is_read']);
                            ?>
                            <a href="<?= url($noti['link'] ?? '/') ?>" class="header-dropdown-item noti-item <?= $isUnread ? 'unread' : 'is-read' ?> workspace-nav-link" data-title="<?= $itemTabTitle ?>">
                                <div class="item-icon noti-icon-box">
                                    <?php if (($noti['type'] ?? '') === 'application'): ?>
                                        <?= icon('user_check', '', 16) ?>
                                    <?php elseif (($noti['type'] ?? '') === 'meeting'): ?>
                                        <?= icon('calendar', '', 16) ?>
                                    <?php elseif (($noti['type'] ?? '') === 'pending_job'): ?>
                                        <?= icon('posts', '', 16) ?>
                                    <?php else: ?>
                                        <?= icon('bell', '', 16) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="item-content">
                                    <div class="item-title-row">
                                        <div class="item-title" style="<?= $isUnread ? 'font-weight:700;' : 'font-weight:600; color:#475569;' ?>"><?= htmlspecialchars($noti['title'] ?? 'Thông báo') ?></div>
                                        <?php if ($isUnread): ?>
                                            <span class="noti-unread-dot" title="Chưa đọc"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-desc"><?= htmlspecialchars($noti['subtitle'] ?? '') ?></div>
                                    <?php if (!empty($noti['created_at'])): ?>
                                        <div class="item-time"><?= date('H:i - d/m/Y', strtotime($noti['created_at'])) ?></div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="header-dropdown-footer">
                    <a href="<?= $notiUrl ?>" class="header-dropdown-link workspace-nav-link" data-title="<?= $notiTabTitle ?>">Xem tất cả hoạt động</a>
                </div>
            </div>
        </div>

        <!-- 2. MESSAGE ICON & DROPDOWN (MESSENGER POPUP STYLE) -->
        <div class="header-icon-container nav-msg-container">
            <button type="button" class="header-icon-btn msg-trigger" title="Đoạn chat & Hộp thư trao đổi" aria-label="Tin nhắn">
                <?= icon('chat', '', 19) ?>
                <?php if ($unreadMsgs > 0): ?>
                    <span class="header-badge"><?= $unreadMsgs > 9 ? '9+' : $unreadMsgs ?></span>
                <?php endif; ?>
            </button>

            <div class="header-dropdown-menu msg-dropdown-menu">
                <!-- HEADER: Đoạn chat + Icons hành động -->
                <div class="msg-popup-header">
                    <div class="msg-popup-title-left">
                        <h3 class="msg-popup-heading">Đoạn chat</h3>
                    </div>
                    <div class="msg-popup-actions">
                        <button type="button" class="msg-popup-action-btn" title="Tùy chọn đoạn chat" onclick="event.stopPropagation();">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="2"></circle><circle cx="12" cy="12" r="2"></circle><circle cx="19" cy="12" r="2"></circle></svg>
                        </button>
                        <a href="<?= $msgUrl ?>" class="msg-popup-action-btn workspace-nav-link" data-title="<?= $msgTabTitle ?>" title="Xem toàn màn hình trong Hộp Thư">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"></path><path d="M9 21H3v-6"></path><path d="M21 3l-7 7"></path><path d="M3 21l7-7"></path></svg>
                        </a>
                        <a href="<?= $msgUrl ?>" class="msg-popup-action-btn workspace-nav-link" data-title="<?= $msgTabTitle ?>" title="Soạn tin nhắn mới">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- TÌM KIẾM ĐOẠN CHAT (Messenger Style) -->
                <div class="msg-popup-search-box">
                    <div class="msg-popup-search-input-wrapper">
                        <svg class="msg-search-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="headerMsgSearchInput" class="msg-popup-search-input" placeholder="Tìm kiếm trên Messenger..." autocomplete="off">
                        <button type="button" id="headerMsgSearchClear" class="msg-search-clear-btn" style="display:none;" onclick="window.clearHeaderMsgSearch && window.clearHeaderMsgSearch(event)">&times;</button>
                    </div>
                </div>

                <!-- BỘ LỌC TABS: Tất cả / Chưa đọc -->
                <div class="msg-popup-filter-pills">
                    <button type="button" class="msg-filter-pill active" data-filter="all" onclick="window.filterHeaderMsgPills && window.filterHeaderMsgPills(this, 'all')">Tất cả</button>
                    <button type="button" class="msg-filter-pill" data-filter="unread" onclick="window.filterHeaderMsgPills && window.filterHeaderMsgPills(this, 'unread')">
                        Chưa đọc <?= $unreadMsgs > 0 ? '<span class="msg-pill-badge">' . $unreadMsgs . '</span>' : '' ?>
                    </button>
                    <button type="button" class="msg-filter-pill" data-filter="recent" onclick="window.filterHeaderMsgPills && window.filterHeaderMsgPills(this, 'recent')">Gần đây</button>
                </div>

                <!-- DANH SÁCH CUỘC TRÒ CHUYỆN (Scrollable) -->
                <div class="msg-popup-list" id="headerMsgList">
                    <!-- 1. JOBY AI ĐƯỢC GHIM ĐẦU -->
                    <a href="<?= $msgUrl ?>?partner_id=ai_joby" 
                       class="msg-popup-item msg-item-ai workspace-nav-link" 
                       data-title="<?= $msgTabTitle ?>" 
                       data-partner-id="ai_joby" 
                       data-name="trợ lý joby ai thông minh" 
                       data-snippet="trợ lý ảo tư vấn việc làm và hồ sơ 24/7" 
                       data-unread="0"
                       title="Trò chuyện với Trợ Lý Joby AI">
                        <div class="msg-popup-avatar-wrapper">
                            <img src="<?= getJobyAvatar() ?>" alt="Joby AI" class="msg-popup-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                            <span class="msg-ai-status-badge">AI</span>
                        </div>
                        <div class="msg-popup-info">
                            <div class="msg-popup-name-row">
                                <span class="msg-popup-name">Trợ Lý Joby AI</span>
                                <span class="msg-ai-sparkle-tag">✨ 24/7</span>
                            </div>
                            <div class="msg-popup-snippet-row">
                                <span class="msg-popup-snippet">Hỏi đáp việc làm, CV & phân tích thông minh</span>
                            </div>
                        </div>
                    </a>

                    <!-- 2. DANH SÁCH ĐỐI TÁC TRAO ĐỔI -->
                    <?php if (empty($conversations)): ?>
                        <div class="msg-popup-empty" id="headerMsgEmptyState">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.6"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            <p>Chưa có cuộc trò chuyện nào</p>
                            <span>Bắt đầu kết nối và trao đổi ngay</span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): 
                            $pId = (int)($conv['partner_id'] ?? 0);
                            $pName = $conv['partner_name'] ?? 'Thành viên';
                            $pAvatar = !empty($conv['partner_avatar']) ? asset($conv['partner_avatar']) : getSystemLogo();
                            $unreadCount = (int)($conv['unread_count'] ?? 0);
                            $isUnread = $unreadCount > 0;
                            $rawLastMsg = $conv['last_message'] ?? '';
                            $lastTime = !empty($conv['last_message_time']) ? formatRelativeTime($conv['last_message_time']) : '';
                            $lastSenderId = (int)($conv['last_sender_id'] ?? 0);
                            $isMe = ($lastSenderId > 0 && $lastSenderId === (int)$accountId);

                            $snippetText = '';
                            if (!empty($rawLastMsg)) {
                                if ($isMe) {
                                    $snippetText = 'Bạn: ' . $rawLastMsg;
                                } else {
                                    $snippetText = $rawLastMsg;
                                }
                            } else {
                                $snippetText = 'Bắt đầu cuộc trò chuyện mới';
                            }
                            $targetUrl = $msgUrl . '?partner_id=' . $pId;
                        ?>
                            <a href="<?= $targetUrl ?>" 
                               class="msg-popup-item <?= $isUnread ? 'is-unread' : '' ?> workspace-nav-link" 
                               data-title="<?= $msgTabTitle ?>" 
                               data-partner-id="<?= $pId ?>" 
                               data-name="<?= htmlspecialchars(mb_strtolower($pName), ENT_QUOTES) ?>" 
                               data-snippet="<?= htmlspecialchars(mb_strtolower($snippetText), ENT_QUOTES) ?>" 
                               data-unread="<?= $isUnread ? '1' : '0' ?>"
                               title="Mở trò chuyện với <?= htmlspecialchars($pName) ?>">
                                <div class="msg-popup-avatar-wrapper">
                                    <img src="<?= htmlspecialchars($pAvatar) ?>" alt="<?= htmlspecialchars($pName) ?>" class="msg-popup-avatar" onerror="this.src='<?= getSystemLogo() ?>'">
                                    <span class="msg-online-dot"></span>
                                </div>
                                <div class="msg-popup-info">
                                    <div class="msg-popup-name-row">
                                        <span class="msg-popup-name <?= $isUnread ? 'unread-name' : '' ?>"><?= htmlspecialchars($pName) ?></span>
                                    </div>
                                    <div class="msg-popup-snippet-row">
                                        <span class="msg-popup-snippet <?= $isUnread ? 'unread-snippet' : '' ?>"><?= htmlspecialchars(mb_substr($snippetText, 0, 36)) . (mb_strlen($snippetText) > 36 ? '...' : '') ?></span>
                                        <?php if (!empty($lastTime)): ?>
                                            <span class="msg-popup-dot-sep">·</span>
                                            <span class="msg-popup-time <?= $isUnread ? 'unread-time' : '' ?>"><?= $lastTime ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="msg-popup-meta">
                                    <?php if ($isUnread): ?>
                                        <span class="msg-popup-unread-dot" title="Chưa đọc"></span>
                                    <?php endif; ?>
                                    <button type="button" class="msg-popup-more-btn" title="Tùy chọn" onclick="event.preventDefault(); event.stopPropagation();">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="2"></circle><circle cx="12" cy="12" r="2"></circle><circle cx="19" cy="12" r="2"></circle></svg>
                                    </button>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="msg-popup-search-empty" id="headerMsgSearchEmpty" style="display:none;">
                        <p>Không tìm thấy kết quả phù hợp</p>
                    </div>
                </div>

                <!-- FOOTER: Xem tất cả trong Messenger -->
                <div class="msg-popup-footer">
                    <a href="<?= $msgUrl ?>" class="msg-popup-footer-link workspace-nav-link" data-title="<?= $msgTabTitle ?>">
                        Xem tất cả trong Messenger
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    if (!window.__headerMessengerInitialized) {
        window.__headerMessengerInitialized = true;

        window.filterHeaderMsgPills = function(btn, filterType) {
            document.querySelectorAll('.msg-filter-pill').forEach(function(p) { p.classList.remove('active'); });
            if (btn) btn.classList.add('active');

            var items = document.querySelectorAll('.msg-popup-item:not(.msg-item-ai)');
            var visibleCount = 0;
            items.forEach(function(item) {
                var isUnread = item.getAttribute('data-unread') === '1';
                if (filterType === 'all') {
                    item.style.display = 'flex';
                    visibleCount++;
                } else if (filterType === 'unread') {
                    if (isUnread) {
                        item.style.display = 'flex';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                } else if (filterType === 'recent') {
                    item.style.display = 'flex';
                    visibleCount++;
                }
            });

            var searchEmpty = document.getElementById('headerMsgSearchEmpty');
            if (searchEmpty) {
                searchEmpty.style.display = (visibleCount === 0 && items.length > 0) ? 'block' : 'none';
                if (visibleCount === 0 && items.length > 0) {
                    searchEmpty.innerHTML = '<p style="color:#64748b; font-size:0.85rem; padding:1.5rem; text-align:center;">Không có tin nhắn chưa đọc nào</p>';
                }
            }
        };

        window.clearHeaderMsgSearch = function(e) {
            if (e && e.preventDefault) e.preventDefault();
            var input = document.getElementById('headerMsgSearchInput');
            var clearBtn = document.getElementById('headerMsgSearchClear');
            if (input) {
                input.value = '';
                input.dispatchEvent(new Event('input'));
                input.focus();
            }
            if (clearBtn) clearBtn.style.display = 'none';
        };

        function setupHeaderMsgListeners() {
            var searchInput = document.getElementById('headerMsgSearchInput');
            var clearBtn = document.getElementById('headerMsgSearchClear');
            var searchEmpty = document.getElementById('headerMsgSearchEmpty');

            if (searchInput && !searchInput.__bound) {
                searchInput.__bound = true;
                searchInput.addEventListener('input', function() {
                    var q = (this.value || '').trim().toLowerCase();
                    if (clearBtn) {
                        clearBtn.style.display = q ? 'inline-flex' : 'none';
                    }

                    var items = document.querySelectorAll('.msg-popup-item');
                    var visibleCount = 0;
                    items.forEach(function(item) {
                        var name = item.getAttribute('data-name') || '';
                        var snippet = item.getAttribute('data-snippet') || '';
                        if (!q || name.indexOf(q) !== -1 || snippet.indexOf(q) !== -1) {
                            item.style.display = 'flex';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (searchEmpty) {
                        searchEmpty.style.display = (visibleCount === 0) ? 'block' : 'none';
                        if (visibleCount === 0) {
                            searchEmpty.innerHTML = '<p style="color:#64748b; font-size:0.85rem; padding:1.5rem; text-align:center;">Không tìm thấy kết quả phù hợp</p>';
                        }
                    }
                });
            }

            // Click delegation mở thẳng đối tác chat khi bấm vào item
            document.addEventListener('click', function(e) {
                var item = e.target.closest('.msg-popup-item');
                if (!item) return;

                var pId = item.getAttribute('data-partner-id');

                // Tự động đóng dropdown
                document.querySelectorAll('.header-icon-container.is-open').forEach(function(c) {
                    c.classList.remove('is-open');
                });

                // Nếu đang trực tiếp ở trang tin nhắn có switchConversation
                if (pId && typeof window.switchConversation === 'function') {
                    e.preventDefault();
                    window.switchConversation(pId);
                    return;
                }

                // Nếu đang trong MasterWorkspace và có iframe tab tin nhắn active
                var activeIframe = document.querySelector('.master-tab-panel.active iframe');
                if (activeIframe && activeIframe.contentWindow && typeof activeIframe.contentWindow.switchConversation === 'function') {
                    if (pId) {
                        e.preventDefault();
                        activeIframe.contentWindow.switchConversation(pId);
                        return;
                    }
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupHeaderMsgListeners);
        } else {
            setupHeaderMsgListeners();
        }
    }

        window.markAllNotificationsRead = function(e, fromPage) {
            if (e && e.preventDefault) e.preventDefault();
            var btn = document.getElementById('btnMarkAllNotiRead');
            var pageBtn = document.querySelector('.btn-mark-all-read-page');
            var targetBtn = fromPage ? (pageBtn || btn) : (btn || pageBtn);

            if (targetBtn && targetBtn.disabled) return;

            var origHtml = targetBtn ? targetBtn.innerHTML : '';
            if (targetBtn) {
                targetBtn.disabled = true;
                targetBtn.innerHTML = '<span style="font-size:0.75rem; color:#64748b;">Đang xử lý...</span>';
            }

            fetch('<?= url('/api/notifications/mark-all-read') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.success) {
                    // 1. Ẩn badge đỏ trên icon chuông
                    var badges = document.querySelectorAll('.nav-noti-container .header-badge');
                    badges.forEach(function(badge) {
                        badge.style.display = 'none';
                    });

                    // 2. Ẩn badge số lượng "X mới" ở tiêu đề dropdown
                    var unreadBadges = document.querySelectorAll('.noti-unread-badge');
                    unreadBadges.forEach(function(b) {
                        b.style.display = 'none';
                    });

                    // 3. Xóa chấm unread dot và cập nhật style các item thông báo
                    var notiItems = document.querySelectorAll('.header-dropdown-item.noti-item');
                    notiItems.forEach(function(item) {
                        item.classList.remove('unread');
                        item.classList.add('is-read');
                        var dot = item.querySelector('.noti-unread-dot');
                        if (dot) dot.remove();
                        var titleEl = item.querySelector('.item-title');
                        if (titleEl) {
                            titleEl.style.fontWeight = '600';
                            titleEl.style.color = '#475569';
                        }
                    });

                    // 4. Cập nhật nút bấm thành trạng thái "Đã đọc tất cả"
                    var checkIconSvg = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                    if (btn) {
                        btn.innerHTML = checkIconSvg + ' <span style="color:#10b981;">Đã đọc tất cả</span>';
                        btn.classList.add('is-disabled');
                        btn.disabled = true;
                        btn.style.opacity = '0.7';
                        btn.style.cursor = 'default';
                    }
                    if (pageBtn) {
                        pageBtn.innerHTML = checkIconSvg + ' <span style="color:#10b981;">Đã đọc tất cả</span>';
                        pageBtn.disabled = true;
                        pageBtn.style.opacity = '0.7';
                    }

                    // 5. Hiển thị Toast thông báo thành công
                    if (window.Toast && typeof window.Toast.success === 'function') {
                        window.Toast.success('Đã đánh dấu tất cả thông báo là đã đọc!');
                    }
                } else {
                    if (targetBtn) {
                        targetBtn.disabled = false;
                        targetBtn.innerHTML = origHtml;
                    }
                    if (window.Toast && typeof window.Toast.error === 'function') {
                        window.Toast.error((data && data.message) ? data.message : 'Không thể đánh dấu đã đọc!');
                    }
                }
            })
            .catch(function(err) {
                console.error(err);
                if (targetBtn) {
                    targetBtn.disabled = false;
                    targetBtn.innerHTML = origHtml;
                }
                if (window.Toast && typeof window.Toast.error === 'function') {
                    window.Toast.error('Lỗi kết nối khi đánh dấu thông báo!');
                }
            });
        };
    }
    </script>
    <?php
    return ob_get_clean();
}





