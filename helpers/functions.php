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
        'external_link'  => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line>'
    ];


    $path = $icons[$name] ?? '<circle cx="12" cy="12" r="10"></circle>';
    $cls = 'icon-svg ' . $class;
    return '<svg class="' . trim($cls) . '" width="' . (int)$size . '" height="' . (int)$size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; display: inline-block;">' . $path . '</svg>';
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
            'badge' => 'Chữ Ký Số CA (SHA-256) & Hợp Đồng Pháp Lý',
            'badge_icon' => 'certificate',
            'title' => 'Cổng Kết Nối Việc Làm Linh Hoạt & An Toàn Pháp Lý',
            'subtitle' => 'Tra cứu việc làm theo ca, toàn thời gian và bán thời gian tại Bình Dương & Toàn quốc. 100% doanh nghiệp xác thực Chữ Ký Số CA, minh bạch pháp lý và điểm uy tín người lao động.',
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
 * Lấy URL Logo chính của hệ thống JobLink (public/uploads/avatars/logo.png)
 */
function getSystemLogo() {
    return asset('uploads/avatars/logo.png');
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
        // 1. LẤY TIN NHẮN GẦN NHẤT
        if ($accountId > 0) {
            $stmtMsg = $db->prepare("
                SELECT m.message_id, m.sender_account_id, m.receiver_account_id, m.message_title, m.message_content, m.message_type, m.sent_at,
                       COALESCE(e.employer_name, s.full_name, st.full_name, a.username) as sender_name,
                       COALESCE(s.avatar_url, st.avatar_url, '') as sender_avatar
                FROM messages m
                JOIN user_accounts a ON m.sender_account_id = a.account_id
                LEFT JOIN user_employers e ON a.account_id = e.account_id
                LEFT JOIN user_seekers s ON a.account_id = s.account_id
                LEFT JOIN user_staffs st ON a.account_id = st.account_id
                WHERE m.receiver_account_id = :acc
                ORDER BY m.sent_at DESC
                LIMIT 5
            ");
            $stmtMsg->execute([':acc' => $accountId]);
            $result['recent_messages'] = $stmtMsg->fetchAll(PDO::FETCH_ASSOC);
            $result['unread_messages_count'] = count($result['recent_messages']);
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

        $result['recent_notifications'] = $notis;
        $result['unread_notifications_count'] = count($notis);
    } catch (PDOException $e) {
        error_log("[getHeaderNotificationsData Error] " . $e->getMessage());
    }

    return $result;
}

/**
 * Tạo câu chào thông minh, cá nhân hóa và tích hợp số liệu thực tế theo vai trò cho Joby AI
 * @param array|null $user
 * @return string
 */
function getAiGreeting($user = null) {
    if ($user === null && isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }

    $db = Database::getConnection();

    if (empty($user)) {
        $totalJobs = 0;
        if ($db) {
            try {
                $totalJobs = (int)$db->query("SELECT COUNT(*) FROM job_posts WHERE post_status = 'approved'")->fetchColumn();
            } catch (Exception $e) {}
        }
        $jobText = $totalJobs > 0 ? "hơn {$totalJobs} việc làm đang tuyển dụng" : "nhiều cơ hội việc làm hấp dẫn";
        return "Chào bạn! Mình là Joby. Sàn JobLink hiện có {$jobText}. Mình có thể hỗ trợ gì cho bạn tìm việc hoặc tuyển dụng hôm nay?";
    }

    $role = $user['role_code'] ?? ($user['role'] ?? 'GUEST');
    $accountId = $user['account_id'] ?? 0;

    // 1. Nhà tuyển dụng & Người trung gian (EMPLOYER_ADMIN / INTERMEDIARY)
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

        $empName = !empty($employer['employer_name']) ? htmlspecialchars($employer['employer_name']) : '';
        $nameDisplay = !empty($empName) ? " ({$empName})" : '';

        if ($pendingApps > 0) {
            return "Chào bạn{$nameDisplay}! Hôm nay doanh nghiệp có <strong>{$pendingApps} ứng viên mới</strong> đang chờ duyệt hồ sơ và <strong>{$upcomingMeetings} lịch phỏng vấn</strong>. Bạn cần mình hỗ trợ sàng lọc ứng viên ngay không?";
        } elseif ($activeJobs > 0) {
            return "Chào bạn{$nameDisplay}! Doanh nghiệp đang có <strong>{$activeJobs} tin tuyển dụng</strong> đang hoạt động. Chúc bạn một ngày làm việc hiệu quả và kết nối ứng viên thành công nhé!";
        } else {
            return "Chào bạn{$nameDisplay}! Chúc bạn ngày mới tràn đầy năng lượng. Bạn có muốn mình hỗ trợ đăng tin tuyển dụng hoặc tìm ứng viên không?";
        }
    }

    // 2. Người lao động (JOB_SEEKER) -> Lấy danh xưng + tên & Báo cáo việc làm/hồ sơ
    if ($role === 'JOB_SEEKER') {
        $seeker = class_exists('UserSeeker') ? UserSeeker::findByAccountId($accountId) : null;
        $sid = $seeker['seeker_id'] ?? 0;
        $fullName = trim($seeker['full_name'] ?? ($user['display_name'] ?? ($user['username'] ?? '')));
        $trustScore = (int)($seeker['trust_score'] ?? 100);

        $nameParts = array_filter(explode(' ', $fullName));
        $shortName = !empty($nameParts) ? end($nameParts) : $fullName;
        if (empty($shortName)) $shortName = '';
        $displayName = !empty($shortName) ? " {$shortName}" : '';

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
            return "Chào bạn{$displayName}! Bạn đang có <strong>{$activeApplications} hồ sơ ứng tuyển</strong> đang được xử lý, điểm uy tín đạt <strong>{$trustScore}/100</strong>. Chúc bạn sớm nhận được kết quả tốt nhất nhé!";
        } else {
            return "Chào bạn{$displayName}! Sàn hôm nay có <strong>{$newJobsCount} việc làm mới</strong> đang mở tuyển, điểm uy tín của bạn là <strong>{$trustScore} điểm</strong>. Bạn có muốn mình gợi ý việc làm phù hợp nhất không?";
        }
    }

    // 3. Nhân viên hệ thống (SUPER_ADMIN, SUB_ADMIN, CSKH_STAFF) -> Báo cáo tác vụ kiểm duyệt
    if (in_array($role, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
        $pendingJobs = 0;
        $pendingEmployers = 0;
        if ($db) {
            try {
                $pendingJobs = (int)$db->query("SELECT COUNT(*) FROM job_posts WHERE post_status = 'pending'")->fetchColumn();
                $pendingEmployers = (int)$db->query("SELECT COUNT(*) FROM user_employers WHERE verification_status = 'unverified' OR verification_status = 'pending'")->fetchColumn();
            } catch (Exception $e) {}
        }

        $roleLabel = ($role === 'SUPER_ADMIN') ? 'Quản Trị Viên Cấp Cao' : (($role === 'CSKH_STAFF') ? 'Chuyên Viên CSKH' : 'Quản Trị Viên');

        return "Chào bạn ({$roleLabel})! Hệ thống hiện có <strong>{$pendingJobs} tin tuyển dụng chờ duyệt</strong> và <strong>{$pendingEmployers} doanh nghiệp cần xác minh GPKD</strong>. Mình sẵn sàng hỗ trợ các tác vụ quản trị cùng bạn hôm nay!";
    }

    return "Chào bạn! Mình là Joby, người bạn trợ lý của bạn. Hôm nay mình có thể hỗ trợ gì cho bạn nào?";
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
    $btnText = 'Xử lý ngay →';
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
        $btnText = 'Kiểm tra ngay →';

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
        $btnText = 'Xem hồ sơ →';

        $cards[] = [
            'type' => $pendingApps > 0 ? 'warning' : 'neutral',
            'icon' => $pendingApps > 0 ? icon('users', '', 16) : icon('check_circle', '', 16),
            'num' => $pendingApps,
            'label' => 'Ứng viên mới',
            'query' => 'Sàng lọc hồ sơ ứng viên'
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
        $btnText = 'Xem việc ngay →';

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
        $btnText = 'Khám phá ngay →';

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
    if (in_array($role, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])) {
        $msgUrl = url('/employer/messages');
    } elseif (in_array($role, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
        $msgUrl = url('/admin/messages');
    }

    $notiUrl = url('/seeker/applied-jobs');
    if (in_array($role, ['EMPLOYER_ADMIN', 'INTERMEDIARY'])) {
        $notiUrl = url('/employer/applications');
    } elseif (in_array($role, ['SUPER_ADMIN', 'SUB_ADMIN', 'CSKH_STAFF'])) {
        $notiUrl = url('/admin/job-posts');
    }

    $unreadMsgs = $data['unread_messages_count'];
    $unreadNotis = $data['unread_notifications_count'];
    $recentMsgs = $data['recent_messages'];
    $recentNotis = $data['recent_notifications'];
    $aiGreeting = getAiGreeting($user);

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
                    <span>Thông Báo</span>
                    <?php if ($unreadNotis > 0): ?>
                        <span class="badge badge-primary" style="font-size:0.75rem; padding: 2px 8px;"><?= $unreadNotis ?> mới</span>
                    <?php endif; ?>
                </div>

                <!-- JOBY AI SMART SUMMARY -->
                <div class="header-ai-alert-card">
                    <div class="ai-alert-header">
                        <span class="ai-alert-badge"><?= icon('ai', '', 14) ?> Joby AI Tóm Tắt</span>
                    </div>
                    <div class="ai-alert-body">
                        <?= $aiGreeting ?>
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
                            <a href="<?= url($noti['link'] ?? '/') ?>" class="header-dropdown-item noti-item">
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
                                    <div class="item-title"><?= htmlspecialchars($noti['title'] ?? 'Thông báo') ?></div>
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
                    <a href="<?= $notiUrl ?>" class="header-dropdown-link">Xem tất cả hoạt động &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 2. MESSAGE ICON & DROPDOWN -->
        <div class="header-icon-container nav-msg-container">
            <button type="button" class="header-icon-btn msg-trigger" title="Trao đổi tin nhắn" aria-label="Tin nhắn">
                <?= icon('chat', '', 19) ?>
                <?php if ($unreadMsgs > 0): ?>
                    <span class="header-badge"><?= $unreadMsgs > 9 ? '9+' : $unreadMsgs ?></span>
                <?php endif; ?>
            </button>

            <div class="header-dropdown-menu msg-dropdown-menu">
                <div class="header-dropdown-title">
                    <span>Tin Nhắn Trao Đổi</span>
                    <a href="<?= $msgUrl ?>" style="font-size:0.8rem; color:var(--primary); font-weight:700; text-decoration:none;">Vào hộp thư</a>
                </div>

                <div class="header-dropdown-list">
                    <?php if (empty($recentMsgs)): ?>
                        <div class="header-dropdown-empty">
                            <?= icon('chat', '', 24) ?>
                            <p>Chưa có tin nhắn trao đổi nào</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentMsgs as $msg): 
                            $senderAccId = (int)($msg['sender_account_id'] ?? 0);
                            $targetMsgUrl = $msgUrl . ($senderAccId > 0 ? '?partner_id=' . $senderAccId : '');
                        ?>
                            <a href="<?= $targetMsgUrl ?>" class="header-dropdown-item msg-item">
                                <div class="item-avatar-box">
                                    <img src="<?= htmlspecialchars(!empty($msg['sender_avatar']) ? asset($msg['sender_avatar']) : getSystemLogo()) ?>" 
                                         alt="avatar" 
                                         onerror="this.src='<?= getSystemLogo() ?>'">
                                </div>
                                <div class="item-content">
                                    <div class="item-title-row">
                                        <strong class="item-title"><?= htmlspecialchars($msg['sender_name'] ?? 'Thành viên') ?></strong>
                                        <span class="item-time"><?= date('d/m H:i', strtotime($msg['sent_at'])) ?></span>
                                    </div>
                                    <?php if (!empty($msg['message_title'])): ?>
                                        <div class="item-subtitle"><?= htmlspecialchars($msg['message_title']) ?></div>
                                    <?php endif; ?>
                                    <div class="item-snippet"><?= htmlspecialchars(mb_substr($msg['message_content'], 0, 55)) . (mb_strlen($msg['message_content']) > 55 ? '...' : '') ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="header-dropdown-footer">
                    <a href="<?= $msgUrl ?>" class="header-dropdown-link">Xem tất cả trong Hộp Thư &rarr;</a>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}





