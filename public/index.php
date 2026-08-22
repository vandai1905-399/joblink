<?php
// public/index.php - Front Controller tiếp nhận mọi request

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Helper.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../routes/web.php';

// Khởi chạy router
Router::dispatch();
