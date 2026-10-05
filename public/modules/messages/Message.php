<?php
// models/Message.php - Quản lý tin nhắn & hội thoại trao đổi 2 chiều messages (100% CSDL)

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../services/MailService.php';

class Message {

    /**
     * Lấy danh sách tất cả các cuộc trò chuyện / đối tác chat của một tài khoản
     */
    public static function getConversations($myAccountId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT DISTINCT 
                    partner_acc.account_id as partner_id,
                    r.role_code as partner_role_code,
                    r.role_name as partner_role_name,
                    COALESCE(e.employer_name, s.full_name, st.full_name, partner_acc.username) as partner_name,
                    COALESCE(s.avatar_url, e.avatar_url, st.avatar_url, '') as partner_avatar,
                    s.seeker_id,
                    e.employer_id,
                    (
                        SELECT m.message_content 
                        FROM messages m 
                        WHERE (m.sender_account_id = :me1 AND m.receiver_account_id = partner_acc.account_id)
                           OR (m.sender_account_id = partner_acc.account_id AND m.receiver_account_id = :me2)
                        ORDER BY m.sent_at DESC 
                        LIMIT 1
                    ) as last_message,
                    (
                        SELECT m.sent_at 
                        FROM messages m 
                        WHERE (m.sender_account_id = :me3 AND m.receiver_account_id = partner_acc.account_id)
                           OR (m.sender_account_id = partner_acc.account_id AND m.receiver_account_id = :me4)
                        ORDER BY m.sent_at DESC 
                        LIMIT 1
                    ) as last_message_time,
                    (
                        SELECT m.sender_account_id 
                        FROM messages m 
                        WHERE (m.sender_account_id = :me11 AND m.receiver_account_id = partner_acc.account_id)
                           OR (m.sender_account_id = partner_acc.account_id AND m.receiver_account_id = :me12)
                        ORDER BY m.sent_at DESC 
                        LIMIT 1
                    ) as last_sender_id,
                    (
                        SELECT COUNT(*) 
                        FROM messages m 
                        WHERE m.sender_account_id = partner_acc.account_id 
                          AND m.receiver_account_id = :me5 
                          AND m.is_read = 0
                    ) as unread_count
                FROM (
                    SELECT sender_account_id as account_id FROM messages WHERE receiver_account_id = :me6
                    UNION
                    SELECT receiver_account_id as account_id FROM messages WHERE sender_account_id = :me7
                    UNION
                    SELECT s.account_id FROM job_requests r 
                    JOIN job_posts p ON r.job_post_id = p.job_post_id 
                    JOIN user_seekers s ON r.seeker_id = s.seeker_id 
                    JOIN user_employers emp ON p.employer_id = emp.employer_id 
                    WHERE emp.account_id = :me8
                    UNION
                    SELECT emp.account_id FROM job_requests r 
                    JOIN job_posts p ON r.job_post_id = p.job_post_id 
                    JOIN user_seekers s ON r.seeker_id = s.seeker_id 
                    JOIN user_employers emp ON p.employer_id = emp.employer_id 
                    WHERE s.account_id = :me9
                ) partners
                JOIN user_accounts partner_acc ON partners.account_id = partner_acc.account_id
                JOIN user_roles r ON partner_acc.user_role_id = r.user_role_id
                LEFT JOIN user_employers e ON partner_acc.account_id = e.account_id
                LEFT JOIN user_seekers s ON partner_acc.account_id = s.account_id
                LEFT JOIN user_staffs st ON partner_acc.account_id = st.account_id
                WHERE partner_acc.account_id != :me10
                ORDER BY (last_message_time IS NOT NULL) DESC, last_message_time DESC, partner_acc.account_id DESC
            ");

            $stmt->execute([
                ':me1' => $myAccountId,
                ':me2' => $myAccountId,
                ':me3' => $myAccountId,
                ':me4' => $myAccountId,
                ':me5' => $myAccountId,
                ':me6' => $myAccountId,
                ':me7' => $myAccountId,
                ':me8' => $myAccountId,
                ':me9' => $myAccountId,
                ':me10' => $myAccountId,
                ':me11' => $myAccountId,
                ':me12' => $myAccountId
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[Message::getConversations Error] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy toàn bộ lịch sử tin nhắn trao đổi 2 chiều giữa 2 người
     */
    public static function getThread($myAccountId, $partnerAccountId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            // Tự động đánh dấu đã đọc các tin nhắn gửi đến mình từ đối tác này
            self::markAsRead($myAccountId, $partnerAccountId);

            $stmt = $db->prepare("
                SELECT m.*,
                       COALESCE(e.employer_name, s.full_name, st.full_name, a.username) as sender_name,
                       COALESCE(s.avatar_url, e.avatar_url, st.avatar_url, '') as sender_avatar,
                       r.role_code as sender_role_code
                FROM messages m
                JOIN user_accounts a ON m.sender_account_id = a.account_id
                JOIN user_roles r ON a.user_role_id = r.user_role_id
                LEFT JOIN user_employers e ON a.account_id = e.account_id
                LEFT JOIN user_seekers s ON a.account_id = s.account_id
                LEFT JOIN user_staffs st ON a.account_id = st.account_id
                WHERE (m.sender_account_id = :me1 AND m.receiver_account_id = :p1)
                   OR (m.sender_account_id = :p2 AND m.receiver_account_id = :me2)
                ORDER BY m.sent_at ASC, m.message_id ASC
            ");

            $stmt->execute([
                ':me1' => $myAccountId,
                ':p1' => $partnerAccountId,
                ':p2' => $partnerAccountId,
                ':me2' => $myAccountId
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[Message::getThread Error] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thông tin chi tiết của đối tác chat
     */
    public static function getPartnerInfo($partnerAccountId) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT a.account_id, a.username, a.email, r.role_code, r.role_name,
                       COALESCE(e.employer_name, s.full_name, st.full_name, a.username) as display_name,
                       COALESCE(s.avatar_url, e.avatar_url, st.avatar_url, '') as avatar_url,
                       COALESCE(s.phone_number, e.phone_number, st.phone_number, '') as phone_number,
                       COALESCE(s.address, e.address, '') as address,
                       s.seeker_id, s.trust_score, s.desired_position,
                       e.employer_id, e.business_field, e.verification_status
                FROM user_accounts a
                JOIN user_roles r ON a.user_role_id = r.user_role_id
                LEFT JOIN user_seekers s ON a.account_id = s.account_id
                LEFT JOIN user_employers e ON a.account_id = e.account_id
                LEFT JOIN user_staffs st ON a.account_id = st.account_id
                WHERE a.account_id = :id
                LIMIT 1
            ");
            $stmt->execute([':id' => $partnerAccountId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("[Message::getPartnerInfo Error] " . $e->getMessage());
            return null;
        }
    }

    /**
     * Đánh dấu đã đọc các tin nhắn gửi đến từ một đối tác
     */
    public static function markAsRead($myAccountId, $partnerAccountId) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE messages 
                SET is_read = 1 
                WHERE sender_account_id = :partner AND receiver_account_id = :me AND is_read = 0
            ");
            return $stmt->execute([
                ':partner' => $partnerAccountId,
                ':me' => $myAccountId
            ]);
        } catch (PDOException $e) {
            error_log("[Message::markAsRead Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy hộp thư đến (Tương thích ngược)
     */
    public static function getInbox($accountId) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT m.*, 
                       COALESCE(e.employer_name, s.full_name, st.full_name, a.username) as sender_name,
                       COALESCE(s.avatar_url, e.avatar_url, st.avatar_url, '') as sender_avatar
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

    /**
     * Gửi tin nhắn trao đổi 1-1
     */
    public static function send($senderAccountId, $receiverAccountId, $content, $title = null, $type = 'normal') {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                INSERT INTO messages (sender_account_id, receiver_account_id, message_title, message_content, message_type, is_read, sent_at, created_at)
                VALUES (:s, :r, :t, :c, :type, 0, NOW(), NOW())
            ");
            $result = $stmt->execute([
                ':s' => $senderAccountId,
                ':r' => $receiverAccountId,
                ':t' => $title,
                ':c' => $content,
                ':type' => $type
            ]);

            if ($result) {
                $lastId = $db->lastInsertId();

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

                return [
                    'message_id' => $lastId,
                    'sender_account_id' => $senderAccountId,
                    'receiver_account_id' => $receiverAccountId,
                    'message_content' => $content,
                    'message_title' => $title,
                    'message_type' => $type,
                    'sent_at' => date('Y-m-d H:i:s'),
                    'time_formatted' => date('H:i')
                ];
            }

            return false;
        } catch (PDOException $e) {
            error_log("[Message::send Error] " . $e->getMessage());
            return false;
        }
    }
}

