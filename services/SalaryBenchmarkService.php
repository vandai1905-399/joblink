<?php
// services/SalaryBenchmarkService.php - Dịch vụ tính toán mức lương phổ biến dựa trên CSDL và hệ số tiêu chí động

require_once __DIR__ . '/../config/Database.php';

class SalaryBenchmarkService {

    /**
     * Danh sách stop-words tiếng Việt thông dụng cần lọc khi so khớp chức danh
     */
    private static $stopWords = [
        'nhân', 'viên', 'công', 'ty', 'tnhh', 'cổ', 'phần', 'việc', 'làm', 'tại', 'cho', 
        'các', 'cần', 'tuyển', 'gấp', 'vị', 'trí', 'và', 'hoặc', 'với', 'trong', 'theo',
        'nam', 'nữ', 'người', 'tp', 'thành', 'phố', 'tỉnh', 'bình', 'dương', 'full', 'time',
        'part', 'ca', 'ngày', 'tháng', 'được', 'yêu', 'cầu', 'một'
    ];

    /**
     * Bảng mức lương chuẩn thị trường dự phòng theo nhóm ngành (khi CSDL chưa có mẫu tương đồng)
     */
    private static $baselineByCategory = [
        'it' => [
            'keywords' => ['lập trình', 'cntt', 'it', 'software', 'developer', 'web', 'frontend', 'backend', 'java', 'react', 'python', 'php', 'node', 'fullstack', 'mobile', 'ios', 'android'],
            'full_time' => 16000000, 'part_time' => 50000, 'temporary' => 500000
        ],
        'management' => [
            'keywords' => ['quản lý', 'trưởng phòng', 'giám đốc', 'leader', 'manager', 'chủ nhiệm', 'điều hành', 'phó phòng'],
            'full_time' => 18000000, 'part_time' => 60000, 'temporary' => 600000
        ],
        'office' => [
            'keywords' => ['kế toán', 'nhân sự', 'hành chính', 'văn phòng', 'lễ tân', 'thư ký', 'admin', 'chuyên viên', 'soạn thảo', 'thu ngân'],
            'full_time' => 11000000, 'part_time' => 35000, 'temporary' => 350000
        ],
        'sales' => [
            'keywords' => ['bán hàng', 'tư vấn', 'kinh doanh', 'sale', 'telesale', 'marketing', 'tiếp thị', 'bất động sản'],
            'full_time' => 10000000, 'part_time' => 30000, 'temporary' => 300000
        ],
        'customer_service' => [
            'keywords' => ['cskh', 'chăm sóc khách hàng', 'tư vấn viên', 'tổng đài', 'call center', 'hỗ trợ khách hàng'],
            'full_time' => 9500000, 'part_time' => 30000, 'temporary' => 300000
        ],
        'design' => [
            'keywords' => ['thiết kế', 'đồ họa', 'graphic', 'photoshop', 'illustrator', 'ui', 'ux', 'video', 'editor', 'quay phim'],
            'full_time' => 13000000, 'part_time' => 45000, 'temporary' => 450000
        ],
        'construction' => [
            'keywords' => ['xây dựng', 'kiến trúc', 'kỹ sư xây dựng', 'giám sát công trình', 'bản vẽ', 'autocad', 'dự toán'],
            'full_time' => 14000000, 'part_time' => 45000, 'temporary' => 450000
        ],
        'fnb' => [
            'keywords' => ['pha chế', 'barista', 'đầu bếp', 'phụ bếp', 'bếp', 'phục vụ', 'thu ngân nhà hàng', 'pha chế cà phê', 'bồi bàn', 'tạp vụ nhà hàng'],
            'full_time' => 8500000, 'part_time' => 28000, 'temporary' => 250000
        ],
        'technical' => [
            'keywords' => ['kỹ thuật', 'sản xuất', 'chế biến', 'gỗ', 'cơ khí', 'điện', 'bảo trì', 'vận hành', 'cnc', 'hàn', 'tiện'],
            'full_time' => 12000000, 'part_time' => 35000, 'temporary' => 350000
        ],
        'logistics' => [
            'keywords' => ['kho', 'thủ kho', 'kho vận', 'giao hàng', 'shipper', 'tài xế', 'lái xe', 'giao nhận', 'vận chuyển', 'bốc xếp'],
            'full_time' => 9500000, 'part_time' => 30000, 'temporary' => 320000
        ],
        'garment' => [
            'keywords' => ['may', 'may mặc', 'thợ may', 'cắt vải', 'ủi', 'kiểm hàng', 'qa', 'qc', 'dệt'],
            'full_time' => 8500000, 'part_time' => 28000, 'temporary' => 280000
        ],
        'healthcare' => [
            'keywords' => ['dược sĩ', 'điều dưỡng', 'y tá', 'bác sĩ', 'y tế', 'phòng khám', 'nha khoa'],
            'full_time' => 12000000, 'part_time' => 40000, 'temporary' => 400000
        ],
        'education' => [
            'keywords' => ['giáo viên', 'giảng viên', 'tiếng anh', 'đào tạo', 'dạy học', 'gia sư', 'trợ giảng', 'tiếng nhật', 'tiếng hoa', 'tiếng hàn'],
            'full_time' => 13000000, 'part_time' => 45000, 'temporary' => 400000
        ],
        'general' => [
            'keywords' => ['bảo vệ', 'công nhân', 'lao động phổ thông', 'đóng gói', 'tạp vụ', 'vệ sinh'],
            'full_time' => 8000000, 'part_time' => 25000, 'temporary' => 250000
        ]
    ];

    /**
     * Bóc tách từ khóa quan trọng từ câu chữ
     */
    public static function extractKeywords($text) {
        if (!$text) return [];
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $words = array_filter(explode(' ', $text), function($w) {
            $w = trim($w);
            return mb_strlen($w, 'UTF-8') >= 2 && !in_array($w, self::$stopWords);
        });
        return array_values(array_unique($words));
    }

    /**
     * Phân tích các tiêu chí và tính toán hệ số tăng giảm lương
     */
    public static function parseCriteria($title, $req, $shift = '') {
        $factors = [];
        $multiplier = 1.0;

        $titleLower = mb_strtolower($title, 'UTF-8');
        $reqLower = mb_strtolower($req, 'UTF-8');
        $shiftLower = mb_strtolower($shift, 'UTF-8');

        // 1. Cấp bậc / Chức danh (Seniority / Hierarchy)
        if (preg_match('/(thực tập|intern|học việc|phụ việc|học nghề)/u', $titleLower)) {
            $factors['seniority'] = [
                'type' => 'seniority',
                'label' => 'Thực tập sinh / Học việc',
                'percent' => -25,
                'tag' => '-25%'
            ];
            $multiplier *= 0.75;
        } elseif (preg_match('/(giám đốc|director|head of|chuyên gia|expert|kien truc su|kiến trúc sư)/u', $titleLower)) {
            $factors['seniority'] = [
                'type' => 'seniority',
                'label' => 'Cấp Giám đốc / Chuyên gia cao cấp',
                'percent' => +70,
                'tag' => '+70%'
            ];
            $multiplier *= 1.70;
        } elseif (preg_match('/(trưởng phòng|quản lý|manager|chủ nhiệm|phó phòng)/u', $titleLower)) {
            $factors['seniority'] = [
                'type' => 'seniority',
                'label' => 'Cấp Quản lý / Trưởng phòng',
                'percent' => +45,
                'tag' => '+45%'
            ];
            $multiplier *= 1.45;
        } elseif (preg_match('/(trưởng nhóm|leader|lead|giám sát|supervisor)/u', $titleLower)) {
            $factors['seniority'] = [
                'type' => 'seniority',
                'label' => 'Cấp Trưởng nhóm / Giám sát',
                'percent' => +25,
                'tag' => '+25%'
            ];
            $multiplier *= 1.25;
        }

        // 2. Tiêu chí Kinh nghiệm (Experience)
        if (preg_match('/(không yêu cầu kinh nghiệm|chưa có kinh nghiệm|không cần kinh nghiệm|chấp nhận mới ra trường|fresher|chưa kinh nghiệm)/u', $reqLower)) {
            $factors['experience'] = [
                'type' => 'experience',
                'label' => 'Chưa có kinh nghiệm (Fresher)',
                'percent' => -12,
                'tag' => '-12%'
            ];
            $multiplier *= 0.88;
        } elseif (preg_match('/(?:từ\s*)?(\d+)\s*(?:[-–]\s*(\d+))?\s*(?:năm|year)\s*(?:kinh nghiệm|kn|exp)?/u', $reqLower, $m) || 
                  preg_match('/(?:kinh nghiệm|kn|exp)\s*(?:từ\s*)?(\d+)\s*(?:năm|year)?/u', $reqLower, $m)) {
            $years = intval($m[1] ?? 1);
            if ($years >= 5) {
                $factors['experience'] = [
                    'type' => 'experience',
                    'label' => "Kinh nghiệm từ {$years} năm trở lên",
                    'percent' => +50,
                    'tag' => '+50%'
                ];
                $multiplier *= 1.50;
            } elseif ($years >= 3) {
                $factors['experience'] = [
                    'type' => 'experience',
                    'label' => "Kinh nghiệm {$years} năm",
                    'percent' => +30,
                    'tag' => '+30%'
                ];
                $multiplier *= 1.30;
            } elseif ($years >= 2) {
                $factors['experience'] = [
                    'type' => 'experience',
                    'label' => "Kinh nghiệm {$years} năm",
                    'percent' => +15,
                    'tag' => '+15%'
                ];
                $multiplier *= 1.15;
            } elseif ($years >= 1) {
                $factors['experience'] = [
                    'type' => 'experience',
                    'label' => "Kinh nghiệm 1 năm (Mức chuẩn)",
                    'percent' => 0,
                    'tag' => 'Chuẩn'
                ];
            }
        }

        // 3. Tiêu chí Trình độ & Chứng chỉ (Education & Certifications)
        if (preg_match('/(thạc sĩ|tiến sĩ|master|phd)/u', $reqLower)) {
            $factors['education'] = [
                'type' => 'education',
                'label' => 'Trình độ Thạc sĩ / Sau Đại học',
                'percent' => +25,
                'tag' => '+25%'
            ];
            $multiplier *= 1.25;
        } elseif (preg_match('/(đại học|cử nhân|kỹ sư|bachelor|university|ielts\s*[7-9]|toeic\s*[8-9]\d{2}|jlpt\s*n[12])/u', $reqLower)) {
            $factors['education'] = [
                'type' => 'education',
                'label' => 'Tốt nghiệp Đại học / Ngoại ngữ cao cấp',
                'percent' => +12,
                'tag' => '+12%'
            ];
            $multiplier *= 1.12;
        } elseif (preg_match('/(cao đẳng|college|ielts\s*6|toeic\s*[6-7]\d{2}|jlpt\s*n3)/u', $reqLower)) {
            $factors['education'] = [
                'type' => 'education',
                'label' => 'Tốt nghiệp Cao đẳng / Chứng chỉ khá',
                'percent' => +5,
                'tag' => '+5%'
            ];
            $multiplier *= 1.05;
        } elseif (preg_match('/(lao động phổ thông|không yêu cầu bằng cấp|tốt nghiệp thpt|cấp 3|cấp 2)/u', $reqLower)) {
            $factors['education'] = [
                'type' => 'education',
                'label' => 'Lao động phổ thông / Không yêu cầu bằng cấp',
                'percent' => -8,
                'tag' => '-8%'
            ];
            $multiplier *= 0.92;
        }

        // 4. Ca làm việc & Môi trường (Work Shift & Conditions)
        $shiftContent = $shiftLower . ' ' . $reqLower;
        if (preg_match('/(ca đêm|trực đêm|làm đêm|ban đêm|night)/u', $shiftContent)) {
            $factors['shift'] = [
                'type' => 'shift',
                'label' => 'Phụ cấp làm ca đêm',
                'percent' => +15,
                'tag' => '+15%'
            ];
            $multiplier *= 1.15;
        } elseif (preg_match('/(tăng ca|ngoài giờ|overtime|ot|xoay ca liên tục)/u', $shiftContent)) {
            $factors['shift'] = [
                'type' => 'shift',
                'label' => 'Làm thêm ngoài giờ / Tăng ca',
                'percent' => +10,
                'tag' => '+10%'
            ];
            $multiplier *= 1.10;
        }

        // Giới hạn hệ số an toàn trong khoảng 0.65 - 2.50
        $multiplier = max(0.65, min(2.50, $multiplier));

        return [
            'factors' => $factors,
            'multiplier' => round($multiplier, 2)
        ];
    }

    /**
     * Phân tích và đề xuất mức lương thông minh từ CSDL và Tiêu chí
     */
    public static function analyze($jobTitle = '', $employmentType = 'full_time', $jobRequirements = '', $workShift = '', $workplaceAddress = '') {
        $employmentType = in_array($employmentType, ['full_time', 'part_time', 'temporary']) ? $employmentType : 'full_time';
        $db = Database::getConnection();

        $titleKeywords = self::extractKeywords($jobTitle);
        $reqKeywords = self::extractKeywords($jobRequirements);

        $matchedJobs = [];

        if ($db && (!empty($titleKeywords) || !empty($reqKeywords))) {
            try {
                $stmt = $db->query("
                    SELECT p.job_post_id, p.job_title, p.employment_type, p.salary_amount, p.job_requirements,
                           p.work_shift, p.education_level, p.experience_required, p.workplace_address, e.employer_name
                    FROM job_posts p
                    LEFT JOIN user_employers e ON p.employer_id = e.employer_id
                    WHERE p.salary_amount > 0 AND p.post_status != 'rejected'
                    ORDER BY p.job_post_id DESC
                ");
                $allJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($allJobs as $job) {
                    if ($job['employment_type'] !== $employmentType) {
                        continue;
                    }
                    $jTitle = mb_strtolower($job['job_title'], 'UTF-8');
                    $jReq = mb_strtolower($job['job_requirements'] ?? '', 'UTF-8');
                    $score = 0;

                    // So khớp từ khóa chức danh (Trọng số lớn: 15đ mỗi từ khớp)
                    foreach ($titleKeywords as $kw) {
                        if (mb_strpos($jTitle, $kw) !== false) {
                            $score += 15;
                        } elseif (mb_strpos($jReq, $kw) !== false) {
                            $score += 4;
                        }
                    }

                    // So khớp từ khóa yêu cầu công việc
                    foreach ($reqKeywords as $kw) {
                        if (mb_strpos($jReq, $kw) !== false) {
                            $score += 4;
                        }
                    }

                    // Cùng hình thức làm việc được cộng điểm ưu tiên
                    if ($job['employment_type'] === $employmentType) {
                        $score += 8;
                    }

                    // Ca làm tương tự giúp loại bỏ các mức lương khác bản chất công việc
                    $jobShift = mb_strtolower($job['work_shift'] ?? '', 'UTF-8');
                    foreach (self::extractKeywords($workShift) as $kw) {
                        if ($kw !== '' && mb_strpos($jobShift, $kw) !== false) {
                            $score += 3;
                        }
                    }

                    if ($score >= 15) {
                        $matchedJobs[] = [
                            'score' => $score,
                            'job_post_id' => $job['job_post_id'],
                            'job_title' => $job['job_title'],
                            'employer_name' => $job['employer_name'] ?? 'Doanh nghiệp',
                            'employment_type' => $job['employment_type'],
                            'salary_amount' => floatval($job['salary_amount']),
                            'job_requirements' => $job['job_requirements'],
                            'work_shift' => $job['work_shift'] ?? '',
                            'education_level' => $job['education_level'] ?? '',
                            'experience_required' => $job['experience_required'] ?? '',
                            'workplace_address' => $job['workplace_address']
                        ];
                    }
                }

                // Sắp xếp theo độ tương đồng cao nhất
                usort($matchedJobs, function($a, $b) {
                    return $b['score'] <=> $a['score'];
                });

                // Lọc thông minh: Chỉ giữ lại các bài đăng có độ khớp sát với bài cao nhất (tránh bài lạc đề)
                if (!empty($matchedJobs)) {
                    $topScore = $matchedJobs[0]['score'];
                    $threshold = max(20, $topScore * 0.55);
                    $matchedJobs = array_values(array_filter($matchedJobs, function($item) use ($threshold) {
                        return $item['score'] >= $threshold;
                    }));
                }
            } catch (PDOException $e) {
                error_log("[SalaryBenchmarkService Error] " . $e->getMessage());
            }
        }

        // Tính mức lương cơ sở thị trường từ các bài đăng tương tự trong CSDL
        $matchedCount = count($matchedJobs);
        $baseSalaries = [];

        foreach ($matchedJobs as $m) {
            $normalized = self::normalizeSalaryToUnit($m['salary_amount'], $m['employment_type'], $employmentType);
            if ($normalized > 0) {
                $baseSalaries[] = $normalized;
            }
        }

        $sourceType = 'database'; // 'database' hoặc 'market_baseline'

        if (!empty($baseSalaries)) {
            sort($baseSalaries);
            $baseAvg = array_sum($baseSalaries) / count($baseSalaries);
            $baseMin = $baseSalaries[0];
            $baseMax = end($baseSalaries);

        } else {
            // Trường hợp vị trí mới chưa có mẫu tương đồng trong CSDL -> Dùng chuẩn ngành nghề
            $sourceType = 'market_baseline';
            $baseMin = 8000000;
            $baseAvg = 12000000;
            $baseMax = 16000000;

            if ($employmentType === 'part_time') {
                $baseMin = 25000; $baseAvg = 32000; $baseMax = 45000;
            } elseif ($employmentType === 'temporary') {
                $baseMin = 220000; $baseAvg = 300000; $baseMax = 420000;
            }

            // Kiểm tra khớp ngành nghề theo từ khóa
            $fullTitleLower = mb_strtolower($jobTitle, 'UTF-8');
            foreach (self::$baselineByCategory as $cat) {
                foreach ($cat['keywords'] as $k) {
                    if (mb_strpos($fullTitleLower, $k) !== false) {
                        $std = $cat[$employmentType] ?? $baseAvg;
                        $baseAvg = $std;
                        $baseMin = round($std * 0.8);
                        $baseMax = round($std * 1.25);
                        break 2;
                    }
                }
            }
        }

        // Phân tích tiêu chí và tính hệ số tăng/giảm
        $parsed = self::parseCriteria($jobTitle, $jobRequirements, $workShift);
        $multiplier = $parsed['multiplier'];
        $factors = $parsed['factors'];

        // Áp dụng hệ số vào mức lương cơ sở
        $adjMin = self::roundSalary($baseMin * $multiplier, $employmentType);
        $adjAvg = self::roundSalary($baseAvg * $multiplier, $employmentType);
        $adjMax = self::roundSalary($baseMax * $multiplier, $employmentType);

        if ($adjMax <= $adjMin) {
            $adjMax = self::roundSalary($adjMin * 1.35, $employmentType);
        }
        if ($adjAvg <= $adjMin || $adjAvg >= $adjMax) {
            $adjAvg = self::roundSalary(($adjMin + $adjMax) / 2, $employmentType);
        }

        $similarJobs = [];
        foreach (array_slice($matchedJobs, 0, 10) as $similarJob) {
            $similarJob['normalized_salary'] = self::normalizeSalaryToUnit(
                $similarJob['salary_amount'],
                $similarJob['employment_type'],
                $employmentType
            );
            $similarJobs[] = $similarJob;
        }

        // Sinh danh sách các nút gợi ý bấm nhanh (Presets)
        $presets = self::buildPresets($adjMin, $adjAvg, $adjMax, $employmentType);

        $unitLabel = ($employmentType === 'full_time') ? 'tháng' : (($employmentType === 'part_time') ? 'giờ' : 'ca');

        return [
            'success' => true,
            'job_title' => $jobTitle,
            'employment_type' => $employmentType,
            'unit' => $unitLabel,
            'source_type' => $sourceType,
            'matched_count' => $matchedCount,
            'matched_sample' => $similarJobs,
            'base_stats' => [
                'min' => round($baseMin),
                'avg' => round($baseAvg),
                'max' => round($baseMax)
            ],
            'factors' => $factors,
            'multiplier' => $multiplier,
            'multiplier_percent' => round(($multiplier - 1) * 100),
            'suggested' => [
                'min' => $adjMin,
                'avg' => $adjAvg,
                'max' => $adjMax,
                'formatted_min' => number_format($adjMin, 0, ',', '.'),
                'formatted_avg' => number_format($adjAvg, 0, ',', '.'),
                'formatted_max' => number_format($adjMax, 0, ',', '.')
            ],
            'presets' => $presets
        ];
    }

    /**
     * Làm tròn mức lương theo từng hình thức cho đẹp mắt và thực tế
     */
    private static function roundSalary($val, $type) {
        if ($type === 'full_time') {
            // Toàn thời gian: Làm tròn đến 500.000 VNĐ gần nhất
            return max(3500000, round($val / 500000) * 500000);
        } elseif ($type === 'part_time') {
            // Bán thời gian: Làm tròn đến 1.000 VNĐ gần nhất
            return max(20000, round($val / 1000) * 1000);
        } else {
            // Thời vụ: Làm tròn đến 10.000 VNĐ gần nhất
            return max(150000, round($val / 10000) * 10000);
        }
    }

    /**
     * Tạo các nút đề xuất thông minh kèm nhãn trực quan
     */
    private static function buildPresets($min, $avg, $max, $type) {
        $unit = ($type === 'full_time') ? 'đ' : (($type === 'part_time') ? 'đ/h' : 'đ/ca');
        $presets = [];

        // Nấc 1: Mức thấp nhất trong nhóm tin tương đồng
        $presets[] = [
            'type' => 'min',
            'badge' => 'Thấp nhất',
            'label' => self::formatShortMoney($min) . ' ' . $unit,
            'raw_label' => number_format($min, 0, ',', '.') . ' ' . $unit,
            'value' => $min,
            'description' => 'Mức thấp nhất trong các tin có tiêu chí tương đồng'
        ];

        // Nấc 2: Mức trung bình của nhóm tin tương đồng
        $presets[] = [
            'type' => 'recommended',
            'badge' => 'Trung bình',
            'label' => self::formatShortMoney($avg) . ' ' . $unit,
            'raw_label' => number_format($avg, 0, ',', '.') . ' ' . $unit,
            'value' => $avg,
            'is_recommended' => true,
            'description' => 'Mức trung bình của các tin có tiêu chí tương đồng'
        ];

        // Nấc 3: Mức cao nhất trong nhóm tin tương đồng
        $presets[] = [
            'type' => 'max',
            'badge' => 'Cao nhất',
            'label' => self::formatShortMoney($max) . ' ' . $unit,
            'raw_label' => number_format($max, 0, ',', '.') . ' ' . $unit,
            'value' => $max,
            'description' => 'Mức cao nhất trong các tin có tiêu chí tương đồng'
        ];

        return $presets;
    }

    /**
     * Rút gọn tiền tệ hiển thị ngắn gọn (12.000.000 -> 12 triệu)
     */
    public static function formatShortMoney($val) {
        if ($val >= 1000000) {
            $m = $val / 1000000;
            return (floor($m) == $m ? $m : number_format($m, 1, ',', '.')) . ' triệu';
        }
        if ($val >= 1000) {
            return round($val / 1000) . 'k';
        }
        return number_format($val, 0, ',', '.');
    }

    /**
     * Tự động chuẩn hóa mức lương trong CSDL về đơn vị mục tiêu (tháng, giờ, ca)
     */
    public static function normalizeSalaryToUnit($amount, $jobType, $targetType) {
        $amount = floatval($amount);
        if ($amount <= 0) return 0;

        // 1. Chuẩn hóa về mức lương Tháng
        $monthly = $amount;
        if ($jobType === 'part_time') {
            if ($amount <= 300000) {
                // Lương giờ thực tế -> quy đổi ra tháng (26 ngày * 8 giờ = 208 giờ)
                $monthly = $amount * 26 * 8;
            } else {
                // Người nhập đã điền sẵn lương tháng (vd 6.500.000)
                $monthly = $amount;
            }
        } elseif ($jobType === 'temporary') {
            if ($amount <= 1500000) {
                // Lương ca thực tế -> quy đổi ra tháng (26 ca)
                $monthly = $amount * 26;
            } else {
                $monthly = $amount;
            }
        } else {
            // full_time
            if ($amount <= 300000) {
                $monthly = $amount * 26 * 8;
            } elseif ($amount <= 1500000) {
                $monthly = $amount * 26;
            }
        }

        // 2. Chuyển từ Lương Tháng sang Target Type
        if ($targetType === 'part_time') {
            return round($monthly / (26 * 8));
        } elseif ($targetType === 'temporary') {
            return round($monthly / 26);
        } else {
            return round($monthly);
        }
    }
}
