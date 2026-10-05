<?php
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getConnection();
$stmt = $db->query("SELECT a.account_id, a.username, a.avatar_url as acc_avatar, s.seeker_id, s.full_name, s.gender, s.avatar_url as seeker_avatar FROM user_accounts a LEFT JOIN user_seekers s ON a.account_id = s.account_id WHERE s.full_name LIKE '%Hoài An%' OR a.username LIKE '%hoaian%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
