<?php
/**
 * services/MailService.php
 * Dịch vụ Gửi Email Thông Báo Tự Động Qua Gmail SMTP (JobLink Mail Service)
 * Hỗ trợ Socket SMTP chuẩn RFC 5321 (STARTTLS Port 587 / SSL Port 465) & Template HTML Responsive
 */

require_once __DIR__ . '/../config/Database.php';

class MailService {

    /**
     * Lấy cấu hình SMTP từ database system_settings
     */
    public static function getConfig() {
        $db = Database::getConnection();
        $defaults = [
            'mail_driver'       => 'smtp',
            'mail_host'         => 'smtp.gmail.com',
            'mail_port'         => '587',
            'mail_username'     => 'joblink.system.notify@gmail.com',
            'mail_password'     => 'akxj wqey plza qbvc',
            'mail_encryption'   => 'tls',
            'mail_from_address' => 'joblink.system.notify@gmail.com',
            'mail_from_name'    => 'JobLink - Cổng Kết Nối Việc Làm',
            'mail_enabled'      => '1'
        ];

        if (!$db) return $defaults;

        try {
            $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_group = 'email'");
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            return array_merge($defaults, $rows);
        } catch (PDOException $e) {
            return $defaults;
        }
    }

    /**
     * Gửi email HTML qua SMTP Socket thuần (Không phụ thuộc thư viện ngoài)
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $htmlBody
     * @param array|null $customConfig
     * @return array [success => bool, message => string]
     */
    public static function send($toEmail, $toName, $subject, $htmlBody, $customConfig = null) {
        $config = $customConfig ?? self::getConfig();

        if (empty($config['mail_enabled']) || $config['mail_enabled'] === '0') {
            return ['success' => false, 'message' => 'Tính năng gửi email đang tạm tắt trong cài đặt hệ thống.'];
        }

        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Địa chỉ email người nhận không hợp lệ: ' . htmlspecialchars($toEmail)];
        }

        $host        = trim($config['mail_host'] ?? 'smtp.gmail.com');
        $port        = intval($config['mail_port'] ?? 587);
        $username    = trim($config['mail_username'] ?? '');
        $password    = str_replace(' ', '', trim($config['mail_password'] ?? ''));
        $encryption  = strtolower(trim($config['mail_encryption'] ?? 'tls'));
        $fromEmail   = trim($config['mail_from_address'] ?? $username);
        $fromName    = trim($config['mail_from_name'] ?? 'JobLink System');

        // Khởi tạo kết nối Socket tới SMTP Server
        $timeout = 10;
        $errno = 0;
        $errstr = '';

        $protocolPrefix = ($encryption === 'ssl' || $port === 465) ? 'ssl://' : 'tcp://';
        $socket = @fsockopen($protocolPrefix . $host, $port, $errno, $errstr, $timeout);

        if (!$socket) {
            error_log("[MailService Error] Không thể kết nối tới $host:$port ($errno: $errstr)");
            return ['success' => false, 'message' => "Không thể kết nối tới máy chủ SMTP Gmail ($host:$port): $errstr"];
        }

        stream_set_timeout($socket, $timeout);
        $response = fgets($socket, 515);

        // Hàm hỗ trợ gửi lệnh và nhận phản hồi
        $sendCommand = function($cmd, $expectedCode = 250) use ($socket, &$lastResponse) {
            fputs($socket, $cmd . "\r\n");
            $response = '';
            while ($line = fgets($socket, 515)) {
                $response .= $line;
                if (substr($line, 3, 1) === ' ') break;
            }
            $lastResponse = $response;
            $code = intval(substr($response, 0, 3));
            return ($code === $expectedCode || ($expectedCode === 250 && ($code === 250 || $code === 220 || $code === 235 || $code === 334)));
        };

        // EHLO
        if (!$sendCommand("EHLO " . gethostname())) {
            fclose($socket);
            return ['success' => false, 'message' => 'Lỗi bắt tay EHLO với máy chủ SMTP: ' . $lastResponse];
        }

        // STARTTLS nếu dùng cổng 587 hoặc TLS
        if ($encryption === 'tls' || $port === 587) {
            if ($sendCommand("STARTTLS", 220)) {
                $crypto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
                if (!$crypto) {
                    fclose($socket);
                    return ['success' => false, 'message' => 'Không thể thiết lập mã hóa bảo mật TLS với Gmail!'];
                }
                // Sau khi bật TLS, gửi lại EHLO
                $sendCommand("EHLO " . gethostname());
            }
        }

        // AUTH LOGIN
        if (!empty($username) && !empty($password)) {
            if (!$sendCommand("AUTH LOGIN", 334)) {
                fclose($socket);
                return ['success' => false, 'message' => 'Máy chủ SMTP từ chối xác thực AUTH LOGIN: ' . $lastResponse];
            }
            if (!$sendCommand(base64_encode($username), 334)) {
                fclose($socket);
                return ['success' => false, 'message' => 'Lỗi tài khoản Gmail (Username không đúng): ' . $lastResponse];
            }
            if (!$sendCommand(base64_encode($password), 235)) {
                fclose($socket);
                return ['success' => false, 'message' => 'Mật khẩu ứng dụng Gmail (App Password) không hợp lệ: ' . $lastResponse];
            }
        }

        // MAIL FROM
        if (!$sendCommand("MAIL FROM: <" . $fromEmail . ">")) {
            fclose($socket);
            return ['success' => false, 'message' => 'Lỗi địa chỉ người gửi (MAIL FROM): ' . $lastResponse];
        }

        // RCPT TO
        if (!$sendCommand("RCPT TO: <" . $toEmail . ">")) {
            fclose($socket);
            return ['success' => false, 'message' => 'Lỗi địa chỉ người nhận (RCPT TO): ' . $lastResponse];
        }

        // DATA
        if (!$sendCommand("DATA", 354)) {
            fclose($socket);
            return ['success' => false, 'message' => 'Lỗi khởi tạo nội dung thư (DATA): ' . $lastResponse];
        }

        // Xây dựng Header và Body MIME
        $boundary = "----=_JobLink_" . md5(uniqid(microtime(), true));
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
        $encodedToName   = '=?UTF-8?B?' . base64_encode($toName) . '?=';

        $headers = [];
        $headers[] = "From: $encodedFromName <$fromEmail>";
        $headers[] = "To: $encodedToName <$toEmail>";
        $headers[] = "Subject: $encodedSubject";
        $headers[] = "Date: " . date('r');
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: base64";
        $headers[] = "X-Mailer: JobLink-Mailer-PHP/2.0";

        $fullPayload = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($htmlBody)) . "\r\n.";

        fputs($socket, $fullPayload . "\r\n");
        $finalResponse = fgets($socket, 515);

        $sendCommand("QUIT", 221);
        fclose($socket);

        if (strpos($finalResponse, '250') !== false) {
            return ['success' => true, 'message' => 'Email đã được gửi thành công tới hộp thư Gmail: ' . $toEmail];
        } else {
            return ['success' => false, 'message' => 'Máy chủ SMTP hoàn tất với phản hồi: ' . $finalResponse];
        }
    }

    /**
     * Gửi thông báo khi có tin nhắn mới giữa các bên
     */
    public static function notifyNewMessage($receiverAccountId, $senderName, $messageTitle, $messageContent) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("SELECT email, username FROM user_accounts WHERE account_id = :id");
            $stmt->execute([':id' => $receiverAccountId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || empty($user['email'])) return false;

            $subject = "💬 Tin nhắn mới từ $senderName - JobLink";
            $cleanTitle = !empty($messageTitle) ? $messageTitle : 'Trao đổi công việc';
            $preview = nl2br(htmlspecialchars(mb_substr($messageContent, 0, 300)));

            $bodyHtml = self::buildEmailTemplate([
                'title'       => 'Bạn Có Tin Nhắn Mới Trên JobLink',
                'greeting'    => 'Xin chào ' . htmlspecialchars($user['username']) . ',',
                'badge'       => 'Tin Nhắn Trực Tiếp',
                'badge_color' => '#0d9488',
                'content_html'=> "
                    <p style='margin-top:0; font-size:15px; color:#334155;'>
                        Bạn vừa nhận được một tin nhắn mới từ <strong>" . htmlspecialchars($senderName) . "</strong> với tiêu đề: <em>\"" . htmlspecialchars($cleanTitle) . "\"</em>.
                    </p>
                    <div style='background:#f8fafc; border-left:4px solid #0d9488; padding:15px; border-radius:6px; margin:20px 0;'>
                        <p style='margin:0; font-size:14px; color:#1e293b; line-height:1.6;'>
                            $preview ...
                        </p>
                    </div>
                    <p style='font-size:13px; color:#64748b;'>
                        Để bảo mật thông tin và trao đổi trực tiếp, vui lòng bấm vào nút bên dưới để truy cập hòm thư trên hệ thống JobLink.
                    </p>
                ",
                'btn_text'    => 'Xem Tin Nhắn & Trả Lời Ngay',
                'btn_url'     => 'http://localhost/messages'
            ]);

            return self::send($user['email'], $user['username'], $subject, $bodyHtml);
        } catch (Throwable $e) {
            error_log("[MailService::notifyNewMessage Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi thông báo khi có đơn ứng tuyển mới tới nhà tuyển dụng
     */
    public static function notifyNewApplication($employerId, $seekerName, $jobTitle) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("SELECT e.employer_name, e.contact_email, a.email as acc_email FROM user_employers e JOIN user_accounts a ON e.account_id = a.account_id WHERE e.employer_id = :id");
            $stmt->execute([':id' => $employerId]);
            $emp = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$emp) return false;
            $toEmail = !empty($emp['contact_email']) ? $emp['contact_email'] : $emp['acc_email'];
            if (empty($toEmail)) return false;

            $subject = "🎯 Hồ sơ ứng tuyển mới cho vị trí: $jobTitle - JobLink";
            $bodyHtml = self::buildEmailTemplate([
                'title'       => 'Hồ Sơ Ứng Tuyển Mới',
                'greeting'    => 'Kính gửi ' . htmlspecialchars($emp['employer_name']) . ',',
                'badge'       => 'Ứng Tuyển Mới',
                'badge_color' => '#2563eb',
                'content_html'=> "
                    <p style='margin-top:0; font-size:15px; color:#334155;'>
                        Người lao động <strong>" . htmlspecialchars($seekerName) . "</strong> vừa nộp hồ sơ ứng tuyển vào vị trí: <strong>\"" . htmlspecialchars($jobTitle) . "\"</strong> của quý doanh nghiệp.
                    </p>
                    <div style='background:#eff6ff; border-left:4px solid #2563eb; padding:15px; border-radius:6px; margin:20px 0;'>
                        <p style='margin:0; font-size:14px; color:#1e40af;'>
                            💡 <strong>Mẹo tuyển dụng:</strong> Doanh nghiệp phản hồi hồ sơ trong vòng 24 giờ có tỷ lệ tuyển thành công cao gấp 3 lần.
                        </p>
                    </div>
                ",
                'btn_text'    => 'Duyệt Hồ Sơ Người Lao Động',
                'btn_url'     => 'http://localhost/employer/applications'
            ]);

            return self::send($toEmail, $emp['employer_name'], $subject, $bodyHtml);
        } catch (Throwable $e) {
            error_log("[MailService::notifyNewApplication Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi thông báo lịch phỏng vấn tới người lao động
     */
    public static function notifyMeetingScheduled($seekerId, $employerName, $meetingTime, $location) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("SELECT s.full_name, s.email, a.email as acc_email FROM user_seekers s JOIN user_accounts a ON s.account_id = a.account_id WHERE s.seeker_id = :id");
            $stmt->execute([':id' => $seekerId]);
            $seeker = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$seeker) return false;
            $toEmail = !empty($seeker['email']) ? $seeker['email'] : $seeker['acc_email'];
            if (empty($toEmail)) return false;

            $subject = "📅 Lịch hẹn phỏng vấn từ $employerName - JobLink";
            $formattedTime = date('H:i - d/m/Y', strtotime($meetingTime));

            $bodyHtml = self::buildEmailTemplate([
                'title'       => 'Thư Mời Phỏng Vấn Tuyển Dụng',
                'greeting'    => 'Xin chào ' . htmlspecialchars($seeker['full_name']) . ',',
                'badge'       => 'Lịch Phỏng Vấn',
                'badge_color' => '#16a34a',
                'content_html'=> "
                    <p style='margin-top:0; font-size:15px; color:#334155;'>
                        Nhà tuyển dụng <strong>" . htmlspecialchars($employerName) . "</strong> đã xếp lịch phỏng vấn trao đổi công việc với bạn.
                    </p>
                    <div style='background:#f0fdf4; border:1px solid #bbf7d0; padding:15px; border-radius:8px; margin:20px 0;'>
                        <p style='margin:0 0 8px 0; font-size:14px; color:#166534;'><strong>⏰ Thời gian:</strong> $formattedTime</p>
                        <p style='margin:0; font-size:14px; color:#166534;'><strong>📍 Địa điểm / Hình thức:</strong> " . htmlspecialchars($location) . "</p>
                    </div>
                    <p style='font-size:13px; color:#dc2626;'>
                        ⚠️ <strong>Lưu ý cam kết:</strong> Vui lòng có mặt đúng giờ hoặc phản hồi sớm trước 12h nếu cần đổi lịch để bảo toàn Điểm Uy Tín (Trust Score) trên tài khoản của bạn.
                    </p>
                ",
                'btn_text'    => 'Xác Nhận & Xem Lịch Hẹn',
                'btn_url'     => 'http://localhost/seeker/meetings'
            ]);

            return self::send($toEmail, $seeker['full_name'], $subject, $bodyHtml);
        } catch (Throwable $e) {
            error_log("[MailService::notifyMeetingScheduled Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dựng template HTML Email chuẩn Brand JobLink
     */
    private static function buildEmailTemplate($data) {
        $title       = htmlspecialchars($data['title'] ?? 'Thông Báo Từ JobLink');
        $greeting    = $data['greeting'] ?? 'Kính gửi Quý Khách,';
        $badge       = htmlspecialchars($data['badge'] ?? 'Hệ Thống');
        $badgeColor  = $data['badge_color'] ?? '#0d9488';
        $contentHtml = $data['content_html'] ?? '';
        $btnText     = htmlspecialchars($data['btn_text'] ?? 'Truy Cập JobLink');
        $btnUrl      = $data['btn_url'] ?? 'http://localhost';

        return "
        <!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>$title</title>
        </head>
        <body style='margin:0; padding:0; background-color:#f1f5f9; font-family:-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing:antialiased;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f1f5f9; padding:30px 10px;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.06); border:1px solid #e2e8f0; max-width:600px;'>
                            <!-- Header -->
                            <tr>
                                <td style='background:linear-gradient(135deg, #064e3b 0%, #047857 100%); padding:25px 30px; text-align:left;'>
                                    <table width='100%' cellpadding='0' cellspacing='0'>
                                        <tr>
                                            <td>
                                                <h1 style='margin:0; font-size:24px; font-weight:800; color:#ffffff; letter-spacing:-0.5px;'>JobLink</h1>
                                                <p style='margin:4px 0 0 0; font-size:13px; color:#d1fae5;'>Cổng Kết Nối Việc Làm & Xác Thực Số</p>
                                            </td>
                                            <td align='right'>
                                                <span style='background:$badgeColor; color:#ffffff; font-size:11px; font-weight:700; padding:5px 12px; border-radius:9999px; text-transform:uppercase;'>$badge</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!-- Body Content -->
                            <tr>
                                <td style='padding:30px; text-align:left;'>
                                    <h2 style='margin-top:0; font-size:18px; font-weight:700; color:#0f172a;'>$greeting</h2>
                                    $contentHtml
                                    <div style='text-align:center; margin:30px 0 20px 0;'>
                                        <a href='$btnUrl' style='display:inline-block; background:#0d9488; color:#ffffff; font-weight:700; font-size:14px; text-decoration:none; padding:12px 28px; border-radius:8px; box-shadow:0 3px 10px rgba(13,148,136,0.3);'>$btnText &rarr;</a>
                                    </div>
                                </td>
                            </tr>
                            <!-- Footer -->
                            <tr>
                                <td style='background-color:#f8fafc; padding:20px 30px; border-top:1px solid #e2e8f0; text-align:center; font-size:12px; color:#64748b; line-height:1.6;'>
                                    <p style='margin:0 0 6px 0;'>Đây là thông báo tự động từ Hệ thống Cổng Thông Tin Tuyển Dụng JobLink.</p>
                                    <p style='margin:0;'>Bình Dương, Việt Nam &bull; Hỗ trợ: support@joblink.vn &bull; Hotline: 1900 8888</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }
}
