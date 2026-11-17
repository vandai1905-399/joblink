<?php
/**
 * services/GeoService.php
 * Hệ thống Định vị địa lý, Tính toán khoảng cách & Quét bán kính việc làm (JobLink Geo engine)
 */

class GeoService {

    /**
     * Bảng ánh xạ tọa độ GPS chuẩn hóa của các địa danh, phường/xã, KCN tại Bình Dương & TP.HCM
     */
    private static $locationCoordinates = [
        // ==========================================
        // 1. THÀNH PHỐ / THỊ XÃ / HUYỆN BÌNH DƯƠNG
        // ==========================================
        'thủ dầu một'        => ['lat' => 10.9804, 'lng' => 106.6519, 'name' => 'TP. Thủ Dầu Một, Bình Dương'],
        'thuận an'           => ['lat' => 10.9234, 'lng' => 106.6975, 'name' => 'TP. Thuận An, Bình Dương'],
        'dĩ an'              => ['lat' => 10.9069, 'lng' => 106.7725, 'name' => 'TP. Dĩ An, Bình Dương'],
        'bến cát'            => ['lat' => 11.1350, 'lng' => 106.6022, 'name' => 'TP. Bến Cát, Bình Dương'],
        'tân uyên'           => ['lat' => 11.0827, 'lng' => 106.7719, 'name' => 'TP. Tân Uyên, Bình Dương'],
        'bàu bàng'           => ['lat' => 11.2727, 'lng' => 106.5936, 'name' => 'Huyện Bàu Bàng, Bình Dương'],
        'bắc tân uyên'       => ['lat' => 11.1667, 'lng' => 106.8667, 'name' => 'Huyện bắc Tân Uyên, Bình Dương'],
        'phú giáo'           => ['lat' => 11.3167, 'lng' => 106.8000, 'name' => 'Huyện phú giáo, Bình Dương'],
        'dầu tiếng'          => ['lat' => 11.3333, 'lng' => 106.4000, 'name' => 'Huyện dầu tiếng, Bình Dương'],
        'bình dương'         => ['lat' => 10.9804, 'lng' => 106.6519, 'name' => 'Bình Dương'],

        // ==========================================
        // 2. CÁC PHƯỜNG / TRUNG TÂM TRỌNG ĐIỂM
        // ==========================================
        'chánh nghĩa'        => ['lat' => 10.9747, 'lng' => 106.6610, 'name' => 'Phường chánh nghĩa, Thủ Dầu Một'],
        'phú hòa'            => ['lat' => 10.9720, 'lng' => 106.6780, 'name' => 'Phường phú hòa, Thủ Dầu Một'],
        'hiệp thành'         => ['lat' => 10.9910, 'lng' => 106.6580, 'name' => 'Phường hiệp thành, Thủ Dầu Một'],
        'phú lợi'            => ['lat' => 10.9850, 'lng' => 106.6850, 'name' => 'Phường phú lợi, Thủ Dầu Một'],
        'định hòa'           => ['lat' => 11.0350, 'lng' => 106.6620, 'name' => 'Phường định hòa, Thủ Dầu Một'],
        'hòa phú'            => ['lat' => 11.0580, 'lng' => 106.6750, 'name' => 'Phường hòa phú, TP. Mới Bình Dương'],
        'thành phố mới'      => ['lat' => 11.0550, 'lng' => 106.6740, 'name' => 'Thành phố mới Bình Dương'],
        'lái thiêu'          => ['lat' => 10.9100, 'lng' => 106.7000, 'name' => 'Phường lái thiêu, Thuận An'],
        'an thạnh'           => ['lat' => 10.9350, 'lng' => 106.6850, 'name' => 'Phường an thạnh, Thuận An'],
        'thuận giao'         => ['lat' => 10.9450, 'lng' => 106.7150, 'name' => 'Phường thuận giao, Thuận An'],
        'an phú'             => ['lat' => 10.9380, 'lng' => 106.7450, 'name' => 'Phường an phú, Thuận An'],
        'đông hòa'           => ['lat' => 10.8950, 'lng' => 106.7850, 'name' => 'Phường đông hòa, Dĩ An'],
        'tân đông hiệp'      => ['lat' => 10.9250, 'lng' => 106.7650, 'name' => 'Phường tân đông hiệp, Dĩ An'],
        'an bình'            => ['lat' => 10.8800, 'lng' => 106.7600, 'name' => 'Phường an bình, Dĩ An'],
        'mỹ phước'           => ['lat' => 11.1250, 'lng' => 106.6120, 'name' => 'Phường mỹ phước, Bến Cát'],
        'tân định'           => ['lat' => 11.0850, 'lng' => 106.6250, 'name' => 'Phường tân định, Bến Cát'],
        'tân phước khánh'    => ['lat' => 11.0350, 'lng' => 106.7350, 'name' => 'Phường tân phước khánh, Tân Uyên'],
        'thái hòa'           => ['lat' => 10.9950, 'lng' => 106.7750, 'name' => 'Phường thái hòa, Tân Uyên'],
        'uyên hưng'          => ['lat' => 11.0750, 'lng' => 106.8250, 'name' => 'Phường uyên hưng, Tân Uyên'],

        // ==========================================
        // 3. KHU CÔNG NGHIỆP (KCN) TRỌNG ĐIỂM
        // ==========================================
        'vsip 1'             => ['lat' => 10.9290, 'lng' => 106.7020, 'name' => 'KCN VSIP 1, Thuận An'],
        'vsip i'             => ['lat' => 10.9290, 'lng' => 106.7020, 'name' => 'KCN VSIP 1, Thuận An'],
        'vsip 2'             => ['lat' => 11.0667, 'lng' => 106.6833, 'name' => 'KCN VSIP 2 / VSIP 2A, Tân Uyên/TP Mới'],
        'vsip ii'            => ['lat' => 11.0667, 'lng' => 106.6833, 'name' => 'KCN VSIP 2, Bình Dương'],
        'sóng thần 1'        => ['lat' => 10.8980, 'lng' => 106.7450, 'name' => 'KCN Sóng thần 1, Dĩ An'],
        'sóng thần 2'        => ['lat' => 10.9080, 'lng' => 106.7420, 'name' => 'KCN Sóng thần 2, Dĩ An'],
        'sóng thần'          => ['lat' => 10.9020, 'lng' => 106.7430, 'name' => 'KCN Sóng thần, Dĩ An'],
        'mỹ phước 1'         => ['lat' => 11.1150, 'lng' => 106.6150, 'name' => 'KCN Mỹ phước 1, Bến Cát'],
        'mỹ phước 2'         => ['lat' => 11.1350, 'lng' => 106.6100, 'name' => 'KCN Mỹ phước 2, Bến Cát'],
        'mỹ phước 3'         => ['lat' => 11.1550, 'lng' => 106.6250, 'name' => 'KCN Mỹ phước 3, Bến Cát'],
        'đồng an'            => ['lat' => 10.9050, 'lng' => 106.7150, 'name' => 'KCN Đồng an, Thuận An'],
        'đồng an 2'          => ['lat' => 11.0450, 'lng' => 106.6650, 'name' => 'KCN Đồng an 2, Thủ Dầu Một'],
        'nam tân uyên'       => ['lat' => 11.0350, 'lng' => 106.7850, 'name' => 'KCN Nam Tân Uyên, Tân Uyên'],
        'đại đăng'           => ['lat' => 11.0150, 'lng' => 106.6950, 'name' => 'KCN Đại đăng, Thủ Dầu Một'],
        'kim huy'            => ['lat' => 11.0280, 'lng' => 106.7050, 'name' => 'KCN Kim huy, Thủ Dầu Một'],
        'việt hương'         => ['lat' => 10.9350, 'lng' => 106.7050, 'name' => 'KCN Việt hương 1, Thuận An'],
        'việt hương 2'       => ['lat' => 11.1450, 'lng' => 106.5650, 'name' => 'KCN Việt hương 2, Bến Cát'],
        'rạch bắp'           => ['lat' => 11.1750, 'lng' => 106.5550, 'name' => 'KCN Rạch bắp, Bến Cát'],
        'mapletree'          => ['lat' => 11.0650, 'lng' => 106.6750, 'name' => 'Khu công nghệ cao mapletree, TP. Mới'],
        'tân đông hiệp a'    => ['lat' => 10.9150, 'lng' => 106.7550, 'name' => 'KCN Tân đông hiệp A, Dĩ An'],
        'tân đông hiệp b'    => ['lat' => 10.9250, 'lng' => 106.7650, 'name' => 'KCN Tân đông hiệp B, Dĩ An'],

        // ==========================================
        // 4. TP. HỒ CHÍ MINH & CÁC QUẬN LÂN CẬN
        // ==========================================
        'thành phố hồ chí minh' => ['lat' => 10.7769, 'lng' => 106.7009, 'name' => 'TP. Hồ Chí Minh'],
        'hồ chí minh'        => ['lat' => 10.7769, 'lng' => 106.7009, 'name' => 'TP. Hồ Chí Minh'],
        'tp hcm'             => ['lat' => 10.7769, 'lng' => 106.7009, 'name' => 'TP. Hồ Chí Minh'],
        'sài gòn'            => ['lat' => 10.7769, 'lng' => 106.7009, 'name' => 'TP. Hồ Chí Minh'],
        'thủ đức'            => ['lat' => 10.8494, 'lng' => 106.7537, 'name' => 'TP. Thủ Đức, TP.HCM'],
        'quận 12'            => ['lat' => 10.8671, 'lng' => 106.6413, 'name' => 'Quận 12, TP.HCM'],
        'gò vấp'             => ['lat' => 10.8387, 'lng' => 106.6653, 'name' => 'Quận gò vấp, TP.HCM'],
        'bình thạnh'         => ['lat' => 10.8105, 'lng' => 106.6978, 'name' => 'Quận bình thạnh, TP.HCM'],
        'tân bình'           => ['lat' => 10.7992, 'lng' => 106.6543, 'name' => 'Quận tân bình, TP.HCM'],
        'củ chi'             => ['lat' => 10.9733, 'lng' => 106.4936, 'name' => 'Huyện củ chi, TP.HCM'],
        'hóc môn'            => ['lat' => 10.8860, 'lng' => 106.5920, 'name' => 'Huyện hóc môn, TP.HCM'],
        'quận 1'             => ['lat' => 10.7756, 'lng' => 106.7004, 'name' => 'Quận 1, TP.HCM'],

        // ==========================================
        // 5. ĐỒNG NAI
        // ==========================================
        'biên hòa'           => ['lat' => 10.9574, 'lng' => 106.8427, 'name' => 'TP. Biên Hòa, Đồng Nai'],
        'đồng nai'           => ['lat' => 10.9574, 'lng' => 106.8427, 'name' => 'Đồng Nai']
    ];

    /**
     * Tính khoảng cách đường chim bay giữa 2 tọa độ GPS (Công thức Haversine)
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Khoảng cách tính bằng kilomet (km), làm tròn 1 chữ số thập phân
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $lat1 = floatval($lat1);
        $lon1 = floatval($lon1);
        $lat2 = floatval($lat2);
        $lon2 = floatval($lon2);

        if ($lat1 == 0 || $lon1 == 0 || $lat2 == 0 || $lon2 == 0) {
            return 999.0;
        }

        // Bán kính trái đất (km)
        $earthRadius = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 1);
    }

    /**
     * Bóc tách tọa độ GPS từ URL Google Maps (nếu có)
     * @param string $mapUrl
     * @return array|null ['lat' => ..., 'lng' => ...]
     */
    public static function extractCoordinatesFromUrl($mapUrl) {
        if (empty($mapUrl)) return null;

        // Định dạng: @10.9804,106.6519
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $mapUrl, $matches)) {
            return ['lat' => floatval($matches[1]), 'lng' => floatval($matches[2])];
        }

        // Định dạng: q=10.9804,106.6519 hoặc query=10.9804,106.6519 hoặc ll=10.9804,106.6519
        if (preg_match('/[?&](?:q|query|ll|loc)=(-?\d+\.\d+),(-?\d+\.\d+)/', $mapUrl, $matches)) {
            return ['lat' => floatval($matches[1]), 'lng' => floatval($matches[2])];
        }

        // Định dạng: !3d10.9804!4d106.6519
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $mapUrl, $matches)) {
            return ['lat' => floatval($matches[1]), 'lng' => floatval($matches[2])];
        }

        return null;
    }

    /**
     * Tìm tọa độ từ tên địa danh hoặc chuỗi địa chỉ
     * @param string $addressText
     * @return array ['lat' => float, 'lng' => float, 'name' => string]
     */
    public static function resolveCoordinates($addressText) {
        $clean = mb_strtolower(trim($addressText));
        if (empty($clean)) {
            return ['lat' => 10.9804, 'lng' => 106.6519, 'name' => 'Bình Dương']; // Mặc định Thủ Dầu Một
        }

        // 1. Kiểm tra khớp chính xác hoặc chứa từ khóa trong bảng từ điển
        foreach (self::$locationCoordinates as $keyword => $coord) {
            if (strpos($clean, $keyword) !== false) {
                return $coord;
            }
        }

        // 2. Fallback về trung tâm Bình Dương
        return ['lat' => 10.9804, 'lng' => 106.6519, 'name' => $addressText];
    }

    /**
     * Lấy tọa độ GPS của một bài đăng tuyển dụng (Ưu tiên Map URL -> Địa chỉ làm việc -> Địa chỉ công ty)
     * @param array $job
     * @return array ['lat' => float, 'lng' => float]
     */
    public static function getJobCoordinates($job) {
        // 1. Thử bóc tách từ Google map URL
        $mapUrl = $job['workplace_map_url'] ?? ($job['map_url'] ?? '');
        $coords = self::extractCoordinatesFromUrl($mapUrl);
        if ($coords) return $coords;

        // 2. Tra cứu từ chuỗi địa chỉ làm việc
        $address = $job['workplace_address'] ?? ($job['employer_address'] ?? ($job['address'] ?? ''));
        return self::resolveCoordinates($address);
    }

    /**
     * Định dạng chuỗi hiển thị khoảng cách thân thiện với người dùng
     * @param float $km
     * @return string (ví dụ: "800 m", "2.4 km")
     */
    public static function formatDistance($km) {
        if ($km < 1.0) {
            return round($km * 1000) . ' m';
        }
        return number_format($km, 1) . ' km';
    }

    /**
     * Danh sách gợi ý các địa danh trọng điểm cho Autocomplete tìm kiếm
     * @return array
     */
    public static function getPopularLocations() {
        return [
            ['name' => 'TP. Thủ Dầu Một', 'lat' => 10.9804, 'lng' => 106.6519, 'group' => 'Đô thị Bình Dương'],
            ['name' => 'TP. Thuận An', 'lat' => 10.9234, 'lng' => 106.6975, 'group' => 'Đô thị Bình Dương'],
            ['name' => 'TP. Dĩ An', 'lat' => 10.9069, 'lng' => 106.7725, 'group' => 'Đô thị Bình Dương'],
            ['name' => 'TP. Bến Cát', 'lat' => 11.1350, 'lng' => 106.6022, 'group' => 'Đô thị Bình Dương'],
            ['name' => 'TP. Tân Uyên', 'lat' => 11.0827, 'lng' => 106.7719, 'group' => 'Đô thị Bình Dương'],
            ['name' => 'Huyện Bàu Bàng', 'lat' => 11.2727, 'lng' => 106.5936, 'group' => 'Đô thị Bình Dương'],
            ['name' => 'KCN VSIP 1 (Thuận An)', 'lat' => 10.9290, 'lng' => 106.7020, 'group' => 'Khu công nghiệp'],
            ['name' => 'KCN VSIP 2 (TP Mới / Tân Uyên)', 'lat' => 11.0667, 'lng' => 106.6833, 'group' => 'Khu công nghiệp'],
            ['name' => 'KCN Sóng thần 1 & 2 (Dĩ An)', 'lat' => 10.9020, 'lng' => 106.7430, 'group' => 'Khu công nghiệp'],
            ['name' => 'KCN Mỹ phước 1, 2, 3 (Bến Cát)', 'lat' => 11.1350, 'lng' => 106.6100, 'group' => 'Khu công nghiệp'],
            ['name' => 'KCN Đồng an (Thuận An)', 'lat' => 10.9050, 'lng' => 106.7150, 'group' => 'Khu công nghiệp'],
            ['name' => 'KCN Nam Tân Uyên', 'lat' => 11.0350, 'lng' => 106.7850, 'group' => 'Khu công nghiệp'],
            ['name' => 'KCN Đại đăng (Thủ Dầu Một)', 'lat' => 11.0150, 'lng' => 106.6950, 'group' => 'Khu công nghiệp'],
            ['name' => 'TP. Hồ Chí Minh', 'lat' => 10.7769, 'lng' => 106.7009, 'group' => 'TP. Hồ Chí Minh & lân cận'],
            ['name' => 'TP. Thủ Đức (TP.HCM)', 'lat' => 10.8494, 'lng' => 106.7537, 'group' => 'TP. Hồ Chí Minh & lân cận'],
            ['name' => 'Quận 12 (TP.HCM)', 'lat' => 10.8671, 'lng' => 106.6413, 'group' => 'TP. Hồ Chí Minh & lân cận'],
            ['name' => 'Huyện củ chi (TP.HCM)', 'lat' => 10.9733, 'lng' => 106.4936, 'group' => 'TP. Hồ Chí Minh & lân cận'],
            ['name' => 'TP. Biên Hòa (Đồng Nai)', 'lat' => 10.9574, 'lng' => 106.8427, 'group' => 'Đồng Nai']
        ];
    }
}
