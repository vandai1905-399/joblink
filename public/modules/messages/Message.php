<?php
// models/Message.php - Quản lý tin nhắn messages (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../services/MailService.php';

class Message {

    public static function getInbox($accountId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT m.*, 
                       COALESCE(e.employer_name, s.full_name, st.full_name, a.username) as sender_name
                FROM messages m
                JOIN user_accounts a ON m.sender_account_id = a.account_id
                LEFT JOIN user_employers e ON a.account_id = e.account_id
                LEFT JOIN user_seekers s ON a.account_id = s.account_id
                LEFT JOIN user_staffs st ON a.account_id = st.account_id
                WHERE m.receiver_account_id = :acc
                ORDER BY m.sent_at DESC
            ");
            $stmt->execute([':acc' => $accountId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[Message::getInbox Error] " . $e->getMessage());
            return [];
        }
    }

    public static function send($senderAccountId, $receiverAccountId, $content, $title = null, $type = 'normal') {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                INSERT INTO messages (sender_account_id, receiver_account_id, message_title, message_content, message_type, sent_at)
                VALUES (:s, :r, :t, :c, :type, NOW())
            ");
            $result = $stmt->execute([
                ':s' => $senderAccountId,
                ':r' => $receiverAccountId,
                ':t' => $title,
                ':c' => $content,
                ':type' => $type
            ]);

            if ($result) {
                // Lấy tên người gửi để gửi thông báo Gmail
                $stmtSender = $db->prepare("
                    SELECT COALESCE(e.employer_name, s.full_name, st.full_name, a.username) as sender_name
                    FROM user_accounts a
                    LEFT JOIN user_employers e ON a.account_id = e.account_id
                    LEFT JOIN user_seekers s ON a.account_id = s.account_id
                    LEFT JOIN user_staffs st ON a.account_id = st.account_id
                    WHERE a.account_id = :s
                ");
                $stmtSender->execute([':s' => $senderAccountId]);
                $senderName = $stmtSender->fetchColumn() ?: 'Thành viên JobLink';

                // Tự động kích hoạt gửi thông báo qua Gmail (Chạy an toàn không chặn luồng)
                try {
                    MailService::notifyNewMessage($receiverAccountId, $senderName, $title, $content);
                } catch (Throwable $mailEx) {
                    error_log("[Message Auto Mail Warning] " . $mailEx->getMessage());
                }
            }

            return $result;
        } catch (PDOException $e) {
            error_log("[Message::send Error] " . $e->getMessage());
            return false;
        }
    }
}
