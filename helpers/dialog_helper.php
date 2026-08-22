<?php
/**
 * helpers/dialog_helper.php - PHP Helper hỗ trợ Dialog & Toast Notification
 * Dùng để kích hoạt Toast, Confirm, Alert hoặc truyền thông báo từ Controller sang View
 */

class DialogHelper {

    /**
     * Đặt thông báo thành công (Success Toast)
     */
    public static function flashSuccess($message) {
        $_SESSION['flash_msg'] = $message;
        $_SESSION['flash_type'] = 'success';
    }

    /**
     * Đặt thông báo lỗi (Error Toast)
     */
    public static function flashError($message) {
        $_SESSION['flash_msg'] = $message;
        $_SESSION['flash_type'] = 'error';
    }

    /**
     * Đặt thông báo cảnh báo (Warning Toast)
     */
    public static function flashWarning($message) {
        $_SESSION['flash_msg'] = $message;
        $_SESSION['flash_type'] = 'warning';
    }

    /**
     * Đặt thông báo thông tin (Info Toast)
     */
    public static function flashInfo($message) {
        $_SESSION['flash_msg'] = $message;
        $_SESSION['flash_type'] = 'info';
    }

    /**
     * Tự động sinh mã JS kích hoạt Toast khi có Flash Message trong Session
     */
    public static function renderFlashScript() {
        $msg = null;
        $type = 'success';

        if (!empty($_SESSION['flash_msg'])) {
            $msg = $_SESSION['flash_msg'];
            $type = $_SESSION['flash_type'] ?? 'success';
        } elseif (!empty($_SESSION['flash_error'])) {
            $msg = $_SESSION['flash_error'];
            $type = 'error';
        } elseif (!empty($_SESSION['flash_success'])) {
            $msg = $_SESSION['flash_success'];
            $type = 'success';
        } elseif (!empty($_SESSION['flash_warning'])) {
            $msg = $_SESSION['flash_warning'];
            $type = 'warning';
        }

        if (!$msg) {
            return '';
        }

        $safeMsg = addslashes($msg);

        // Xóa sạch session flash
        unset($_SESSION['flash_msg'], $_SESSION['flash_type'], $_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['flash_warning']);

        return "<script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.Toast && typeof window.Toast.{$type} === 'function') {
                    window.Toast.{$type}('{$safeMsg}');
                } else if (window.Toast && typeof window.Toast.show === 'function') {
                    window.Toast.show('{$safeMsg}', '{$type}');
                }
            });
        </script>";
    }
}
