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

            $subject = "[JobLink] Tin nhắn mới từ $senderName";
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

            $subject = "[JobLink] Hồ sơ ứng tuyển mới cho vị trí: $jobTitle";
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
                            <strong>Mẹo tuyển dụng:</strong> Doanh nghiệp phản hồi hồ sơ trong vòng 24 giờ có tỷ lệ tuyển thành công cao gấp 3 lần.
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
     * Gửi thông báo lịch phỏng vấn tới người lao động (kèm khoảng ngày và cẩm nang chuẩn bị)
     */
    public static function notifyMeetingScheduled($seekerId, $employerName, $meetingTime, $location, $prepGuide = '', $interviewRange = '') {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("SELECT s.full_name, s.email, a.email as acc_email FROM user_seekers s JOIN user_accounts a ON s.account_id = a.account_id WHERE s.seeker_id = :id");
            $stmt->execute([':id' => $seekerId]);
            $seeker = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$seeker) return false;
            $toEmail = !empty($seeker['email']) ? $seeker['email'] : $seeker['acc_email'];
            if (empty($toEmail)) return false;

            $subject = "[JobLink] Lịch hẹn phỏng vấn từ $employerName";
            $formattedTime = date('H:i - d/m/Y', strtotime($meetingTime));

            $prepExtraHtml = '';
            if (!empty($interviewRange)) {
                $prepExtraHtml .= "<p style='margin:0 0 8px 0; font-size:14px; color:#166534;'><strong>Khoảng ngày dự kiến:</strong> " . htmlspecialchars($interviewRange) . "</p>";
            }
            if (!empty($prepGuide)) {
                $prepExtraHtml .= "<div style='margin-top:10px; padding-top:8px; border-top:1px dashed #bbf7d0; font-size:13px; color:#14532d;'><strong>Hướng dẫn chuẩn bị:</strong><br>" . nl2br(htmlspecialchars($prepGuide)) . "</div>";
            }

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
                        $prepExtraHtml
                        <p style='margin:0 0 8px 0; font-size:14px; color:#166534;'><strong>Thời gian phỏng vấn chính thức:</strong> $formattedTime</p>
                        <p style='margin:0 0 8px 0; font-size:14px; color:#166534;'><strong>Địa điểm / Hình thức:</strong> " . htmlspecialchars($location) . "</p>
                    </div>
                    <p style='font-size:13px; color:#dc2626;'>
                        <strong>Lưu ý cam kết:</strong> Vui lòng có mặt đúng giờ hoặc phản hồi sớm trước 12h nếu cần đổi lịch để bảo toàn Điểm Uy Tín (Trust Score) trên tài khoản của bạn.
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
     * Gửi thông báo chúc mừng trúng tuyển kèm checklist ngày đầu đi làm
     */
    public static function notifyApplicationAccepted($seekerId, $employerName, $jobTitle, $onboardingData = []) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("SELECT s.full_name, s.email, a.email as acc_email FROM user_seekers s JOIN user_accounts a ON s.account_id = a.account_id WHERE s.seeker_id = :id");
            $stmt->execute([':id' => $seekerId]);
            $seeker = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$seeker) return false;

            $toEmail = !empty($seeker['email']) ? $seeker['email'] : $seeker['acc_email'];
            if (empty($toEmail)) return false;

            $startDate = !empty($onboardingData['start_date']) ? date('d/m/Y', strtotime($onboardingData['start_date'])) : 'Theo thông báo từ công ty';
            $startTime = !empty($onboardingData['start_time']) ? $onboardingData['start_time'] : '08:00';
            $location  = !empty($onboardingData['workplace']) ? $onboardingData['workplace'] : 'Văn phòng / Chi nhánh công ty';
            $contactPerson = !empty($onboardingData['contact_person']) ? $onboardingData['contact_person'] : $employerName;
            $items = $onboardingData['checklist'] ?? [];

            $checklistHtml = '';
            if (!empty($items)) {
                $checklistHtml .= "<div style='margin-top:12px;'><strong style='color:#065f46;'>Những thứ cần chuẩn bị cho buổi đầu đi làm:</strong><ul style='margin:8px 0 0 20px; padding:0; color:#334155; font-size:13px;'>";
                foreach ($items as $item) {
                    $checklistHtml .= "<li style='margin-bottom:4px;'>• " . htmlspecialchars($item) . "</li>";
                }
                $checklistHtml .= "</ul></div>";
            }

            $subject = "[JobLink] Chúc Mừng Trúng Tuyển: Vị trí $jobTitle tại $employerName";
            $bodyHtml = self::buildEmailTemplate([
                'title'       => 'Chúc Mừng Trúng Tuyển & Nhận Việc',
                'greeting'    => 'Xin chúc mừng ' . htmlspecialchars($seeker['full_name']) . '!',
                'badge'       => 'Đã Trúng Tuyển',
                'badge_color' => '#059669',
                'content_html'=> "
                    <p style='margin-top:0; font-size:15px; color:#334155;'>
                        Doanh nghiệp <strong>" . htmlspecialchars($employerName) . "</strong> trân trọng thông báo bạn đã xuất sắc vượt qua vòng phỏng vấn và chính thức trúng tuyển vị trí <strong>" . htmlspecialchars($jobTitle) . "</strong>!
                    </p>
                    <div style='background:#ecfdf5; border:1px solid #a7f3d0; padding:15px; border-radius:8px; margin:20px 0;'>
                        <p style='margin:0 0 6px 0; font-size:14px; color:#065f46;'><strong>Ngày bắt đầu nhận việc:</strong> $startDate (lúc $startTime)</p>
                        <p style='margin:0 0 6px 0; font-size:14px; color:#065f46;'><strong>Địa điểm làm việc:</strong> " . htmlspecialchars($location) . "</p>
                        <p style='margin:0; font-size:14px; color:#065f46;'><strong>Người đón tiếp / Liên hệ:</strong> " . htmlspecialchars($contactPerson) . "</p>
                        $checklistHtml
                    </div>
                ",
                'btn_text'    => 'Xem Chi Tiết Hành Trang Ngày Đầu Đi Làm',
                'btn_url'     => 'http://localhost/seeker/applied-jobs'
            ]);

            return self::send($toEmail, $seeker['full_name'], $subject, $bodyHtml);
        } catch (Throwable $e) {
            error_log("[MailService::notifyApplicationAccepted Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi thông báo từ chối lịch sự và hẹn dịp sau
     */
    public static function notifyApplicationRejected($seekerId, $employerName, $jobTitle, $reason = '') {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("SELECT s.full_name, s.email, a.email as acc_email FROM user_seekers s JOIN user_accounts a ON s.account_id = a.account_id WHERE s.seeker_id = :id");
            $stmt->execute([':id' => $seekerId]);
            $seeker = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$seeker) return false;

            $toEmail = !empty($seeker['email']) ? $seeker['email'] : $seeker['acc_email'];
            if (empty($toEmail)) return false;

            $subject = "Thư Cảm Ơn & Phản Hồi Từ $employerName - Vị trí $jobTitle";
            $bodyHtml = self::buildEmailTemplate([
                'title'       => 'Thư Cảm Ơn & Hẹn Dịp Phỏng Vấn Sau',
                'greeting'    => 'Kính gửi ' . htmlspecialchars($seeker['full_name']) . ',',
                'badge'       => 'Phản Hồi Tuyển Dụng',
                'badge_color' => '#64748b',
                'content_html'=> "
                    <p style='margin-top:0; font-size:15px; color:#334155;'>
                        Doanh nghiệp <strong>" . htmlspecialchars($employerName) . "</strong> xin gửi lời cảm ơn chân thành tới bạn vì đã dành thời gian quan tâm và tham gia ứng tuyển/phỏng vấn vị trí <strong>" . htmlspecialchars($jobTitle) . "</strong>.
                    </p>
                    <p style='font-size:14px; color:#475569; line-height:1.6;'>
                        Dù ở thời điểm này hồ sơ chưa thực sự phù hợp với tiêu chí hiện tại của dự án, doanh nghiệp đánh giá rất cao năng lực và tác phong của bạn.
                    </p>
                    <div style='background:#f8fafc; border:1px solid #e2e8f0; padding:15px; border-radius:8px; margin:20px 0;'>
                        <p style='margin:0; font-size:13px; color:#475569;'>
                            <strong>Hẹn gặp lại bạn ở cơ hội tiếp theo:</strong> Thông tin hồ sơ năng lực của bạn đã được lưu trữ trong danh sách nhân tài tiềm năng của công ty trên nền tảng JobLink. Chúng tôi sẽ ưu tiên liên hệ lại với bạn khi có các vị trí phù hợp hơn!
                        </p>
                    </div>
                ",
                'btn_text'    => 'Khám Phá Các Việc Làm Phù Hợp Khác',
                'btn_url'     => 'http://localhost/jobs'
            ]);

            return self::send($toEmail, $seeker['full_name'], $subject, $bodyHtml);
        } catch (Throwable $e) {
            error_log("[MailService::notifyApplicationRejected Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi email nhắc nhở nhà tuyển dụng phản hồi kết quả phỏng vấn do đã quá hạn
     */
    public static function notifyEmployerResponseReminder($employerId, $seekerName, $jobTitle) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("SELECT e.employer_name, e.email, a.email as acc_email FROM user_employers e JOIN user_accounts a ON e.account_id = a.account_id WHERE e.employer_id = :id");
            $stmt->execute([':id' => $employerId]);
            $emp = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$emp) return false;

            $toEmail = !empty($emp['email']) ? $emp['email'] : $emp['acc_email'];
            if (empty($toEmail)) return false;

            $subject = "[JobLink][Nhắc Nhở] Đã Quá Hạn Phản Hồi Kết Quả PV: Người lao động $seekerName";
            $bodyHtml = self::buildEmailTemplate([
                'title'       => 'Nhắc Nhở Cập Nhật Kết Quả Phỏng Vấn',
                'greeting'    => 'Kính gửi Ban Tuyển Dụng ' . htmlspecialchars($emp['employer_name']) . ',',
                'badge'       => 'Quá Hạn Phản Hồi',
                'badge_color' => '#ea580c',
                'content_html'=> "
                    <p style='margin-top:0; font-size:15px; color:#334155;'>
                        Người lao động <strong>" . htmlspecialchars($seekerName) . "</strong> đã hoàn thành buổi phỏng vấn vị trí <strong>" . htmlspecialchars($jobTitle) . "</strong> và hiện đã qua thời gian cam kết phản hồi kết quả (3 ngày làm việc).
                    </p>
                    <p style='font-size:14px; color:#475569;'>
                        Vui lòng truy cập hệ thống JobLink để cập nhật kết quả phỏng vấn (Duyệt nhận việc hoặc Thư cảm ơn hẹn dịp sau) nhằm giữ gìn trải nghiệm người lao động và duy trì Điểm Đánh Giá Doanh Nghiệp uy tín trên sàn.
                    </p>
                ",
                'btn_text'    => 'Cập Nhật Kết Quả Phỏng Vấn Ngay',
                'btn_url'     => 'http://localhost/employer/applications'
            ]);

            return self::send($toEmail, $emp['employer_name'], $subject, $bodyHtml);
        } catch (Throwable $e) {
            error_log("[MailService::notifyEmployerResponseReminder Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi email mời phỏng vấn lại cho người lao động trong Talent Pool (cơ hội việc làm mới phù hợp)
     */
    public static function notifyReInviteMeeting($seekerId, $employerName, $jobTitle, $meetingTime, $location, $prepGuide = null, $interviewRange = null, $note = null) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("SELECT s.full_name, s.email, a.email as acc_email FROM user_seekers s JOIN user_accounts a ON s.account_id = a.account_id WHERE s.seeker_id = :id");
            $stmt->execute([':id' => $seekerId]);
            $seeker = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$seeker) return false;

            $toEmail = !empty($seeker['email']) ? $seeker['email'] : $seeker['acc_email'];
            if (empty($toEmail)) return false;

            $rangeHtml = '';
            if (!empty($interviewRange)) {
                $rangeHtml = "
                    <div style='background:#f0fdfa; border-left:4px solid #0d9488; padding:12px 15px; border-radius:6px; margin:15px 0;'>
                        <strong style='color:#0f766e;'>Khoảng thời gian dự kiến:</strong> <span style='color:#134e4a;'>$interviewRange</span>
                        <br><small style='color:#047857;'>Bạn có thể chủ động sắp xếp thời gian hoặc đề xuất dời lịch nếu cần.</small>
                    </div>
                ";
            }

            $prepGuideHtml = '';
            if (!empty($prepGuide)) {
                $prepGuideHtml = "
                    <div style='background:#f8fafc; border:1px solid #e2e8f0; padding:12px 15px; border-radius:6px; margin:15px 0;'>
                        <strong style='color:#0f172a;'>Cẩm nang hướng dẫn chuẩn bị:</strong>
                        <p style='margin:5px 0 0 0; color:#475569; font-size:13px; line-height:1.5;'>" . nl2br(htmlspecialchars($prepGuide)) . "</p>
                    </div>
                ";
            }

            $noteHtml = '';
            if (!empty($note)) {
                $noteHtml = "
                    <div style='background:#fffbeb; border-left:4px solid #f59e0b; padding:12px 15px; border-radius:6px; margin:15px 0;'>
                        <strong style='color:#b45309;'>Lời nhắn từ Nhà tuyển dụng:</strong>
                        <p style='margin:5px 0 0 0; color:#92400e; font-size:13px; font-style:italic;'>" . nl2br(htmlspecialchars($note)) . "</p>
                    </div>
                ";
            }

            $subject = "[JobLink][Cơ Hội Mới] Lời Mời Phỏng Vấn Vị Trí: $jobTitle - $employerName";
            $bodyHtml = self::buildEmailTemplate([
                'title'       => 'Lời Mời Phỏng Vấn Cơ Hội Mới',
                'greeting'    => 'Xin chào ' . htmlspecialchars($seeker['full_name']) . ',',
                'badge'       => 'Cơ Hội Mới',
                'badge_color' => '#059669',
                'content_html'=> "
                    <p style='margin-top:0; font-size:15px; color:#334155;'>
                        Trước đây hồ sơ của bạn đã được lưu trữ trong danh sách nhân tài tiềm năng của <strong>" . htmlspecialchars($employerName) . "</strong>.
                    </p>
                    <p style='font-size:14px; color:#334155;'>
                        Hiện tại, chúng tôi có vị trí mới <strong>\"" . htmlspecialchars($jobTitle) . "\"</strong> rất tương thích với năng lực và kinh nghiệm của bạn, trân trọng mời bạn tham gia buổi phỏng vấn trao đổi cơ hội hợp tác!
                    </p>
                    <div style='background:#f8fafc; border:1px solid #cbd5e1; padding:15px; border-radius:8px; margin:20px 0;'>
                        <p style='margin:0 0 8px 0; font-size:14px; color:#0f172a;'>
                            <strong>Thời gian hẹn:</strong> <span style='color:#0d9488; font-weight:700;'>$meetingTime</span>
                        </p>
                        <p style='margin:0; font-size:14px; color:#0f172a;'>
                            <strong>Địa điểm / Link phòng họp:</strong> <span style='color:#2563eb;'>$location</span>
                        </p>
                    </div>
                    $rangeHtml
                    $prepGuideHtml
                    $noteHtml
                    <p style='font-size:13px; color:#64748b;'>
                        Vui lòng đăng nhập hệ thống JobLink để xác nhận lịch hẹn hoặc đề xuất khung giờ thuận tiện nhất nhé.
                    </p>
                ",
                'btn_text'    => 'Xem Lịch Phỏng Vấn & Phản Hồi',
                'btn_url'     => 'http://localhost/seeker/meetings'
            ]);

            return self::send($toEmail, $seeker['full_name'], $subject, $bodyHtml);
        } catch (Throwable $e) {
            error_log("[MailService::notifyReInviteMeeting Error] " . $e->getMessage());
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
