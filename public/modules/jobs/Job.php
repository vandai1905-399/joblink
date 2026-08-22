<?php
/**
 * models/Job.php
 * Model: Truy vấn, lọc, sắp xếp và quản lý dữ liệu việc làm.
 */

require_once __DIR__ . '/../../../config/Database.php';

class Job {

    /** Lấy toàn bộ danh sách công việc */
    public static function all() {
        return Database::getJobs();
    }

    /** Tìm 1 công việc theo ID */
    public static function find($id) {
        foreach (Database::getJobs() as $job) {
            if ($job['id'] === $id) return $job;
        }
        return null;
    }

    /** Thêm công việc mới */
    public static function create($data) {
        Database::addJob($data);
    }

    /** Toggle bookmark */
    public static function toggleBookmark($id) {
        return Database::toggleBookmark($id);
    }

    /** Lấy danh sách bookmark */
    public static function bookmarks() {
        return Database::getBookmarks();
    }

    /** Kiểm tra 1 job có được bookmark không */
    public static function isBookmarked($id) {
        return in_array($id, Database::getBookmarks());
    }

    /** Đếm số lượng bookmark */
    public static function bookmarkCount() {
        return count(Database::getBookmarks());
    }

    /**
     * Lọc & sắp xếp danh sách công việc theo bộ tham số.
     * 
     * @param array $filters [q, location, category, job_types[], exp_levels[], min_salary, sort, tab]
     * @return array Danh sách job đã lọc
     */
    public static function filter($filters) {
        $jobs      = self::all();
        $bookmarks = self::bookmarks();

        $q         = strtolower(trim($filters['q'] ?? ''));
        $location  = trim($filters['location'] ?? '');
        $category  = trim($filters['category'] ?? '');
        $jobTypes  = $filters['job_types'] ?? [];
        $expLevels = $filters['exp_levels'] ?? [];
        $minSalary = intval($filters['min_salary'] ?? 0);
        $tab       = $filters['tab'] ?? 'all';
        $sort      = $filters['sort'] ?? 'newest';

        // Lọc
        $result = array_filter($jobs, function($job) use ($q, $location, $category, $jobTypes, $expLevels, $minSalary, $tab, $bookmarks) {

            // Tab bookmark
            if ($tab === 'bookmarks' && !in_array($job['id'], $bookmarks)) {
                return false;
            }

            // Từ khóa tìm kiếm
            if ($q !== '') {
                $haystack = strtolower($job['title'] . ' ' . $job['company'] . ' ' . $job['desc'] . ' ' . implode(' ', $job['tags']));
                if (strpos($haystack, $q) === false) return false;
            }

            // Địa điểm
            if ($location !== '' && $job['location'] !== $location) return false;

            // Ngành nghề
            if ($category !== '' && $job['category'] !== $category) return false;

            // Hình thức
            if (!empty($jobTypes) && !in_array($job['job_type'], $jobTypes)) return false;

            // Cấp bậc
            if (!empty($expLevels) && !in_array($job['exp_level'], $expLevels)) return false;

            // Mức lương
            if ($minSalary > 0 && ($job['salary_num'] ?? 0) < $minSalary) return false;

            return true;
        });

        // Sắp xếp
        usort($result, function($a, $b) use ($sort) {
            if ($sort === 'salary')  return ($b['salary_num'] ?? 0) - ($a['salary_num'] ?? 0);
            if ($sort === 'popular') return ($b['views'] ?? 0) - ($a['views'] ?? 0);
            return 0;
        });

        return array_values($result);
    }
}
