<?php
/**
 * Caching System Class
 * نظام التخزين المؤقت لتحسين الأداء
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Cache {

    private static $instance = null;
    private $cache_group = 'eclass';
    private $default_expiry = 300; // 5 minutes

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // تنظيف الـ cache عند تحديث البيانات
        add_action('eclass_student_saved', array($this, 'clear_students_cache'));
        add_action('eclass_course_saved', array($this, 'clear_courses_cache'));
        add_action('eclass_instructor_saved', array($this, 'clear_instructors_cache'));
        add_action('eclass_billing_saved', array($this, 'clear_billing_cache'));
    }

    /**
     * الحصول على قيمة من الـ cache
     */
    public function get($key, $default = false) {
        $cached = wp_cache_get($key, $this->cache_group);
        return $cached !== false ? $cached : $default;
    }

    /**
     * حفظ قيمة في الـ cache
     */
    public function set($key, $value, $expiry = null) {
        if ($expiry === null) {
            $expiry = $this->default_expiry;
        }
        return wp_cache_set($key, $value, $this->cache_group, $expiry);
    }

    /**
     * حذف قيمة من الـ cache
     */
    public function delete($key) {
        return wp_cache_delete($key, $this->cache_group);
    }

    /**
     * تنظيف كل الـ cache
     */
    public function flush() {
        wp_cache_flush();
    }

    /**
     * الحصول على إحصائيات الطلاب مع caching
     */
    public function get_students_count() {
        $cache_key = 'students_count';
        $count = $this->get($cache_key);

        if ($count === false) {
            global $wpdb;
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_students");
            $this->set($cache_key, $count, 600); // 10 minutes
        }

        return $count;
    }

    /**
     * الحصول على إحصائيات الدورات مع caching
     */
    public function get_courses_count() {
        $cache_key = 'courses_count';
        $count = $this->get($cache_key);

        if ($count === false) {
            global $wpdb;
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_courses");
            $this->set($cache_key, $count, 600);
        }

        return $count;
    }

    /**
     * الحصول على إحصائيات المدربين مع caching
     */
    public function get_instructors_count() {
        $cache_key = 'instructors_count';
        $count = $this->get($cache_key);

        if ($count === false) {
            global $wpdb;
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_instructors");
            $this->set($cache_key, $count, 600);
        }

        return $count;
    }

    /**
     * الحصول على إحصائيات الفواتير مع caching
     */
    public function get_billing_count() {
        $cache_key = 'billing_count';
        $count = $this->get($cache_key);

        if ($count === false) {
            global $wpdb;
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_billing");
            $this->set($cache_key, $count, 600);
        }

        return $count;
    }

    /**
     * الحصول على الإيرادات الإجمالية مع caching
     */
    public function get_total_revenue() {
        $cache_key = 'total_revenue';
        $revenue = $this->get($cache_key);

        if ($revenue === false) {
            global $wpdb;
            $revenue = $wpdb->get_var("SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}eclass_billing WHERE payment_status = 'paid'");
            $this->set($cache_key, $revenue, 1800); // 30 minutes
        }

        return $revenue;
    }

    /**
     * الحصول على الدورات النشطة مع caching
     */
    public function get_active_courses_count() {
        $cache_key = 'active_courses_count';
        $count = $this->get($cache_key);

        if ($count === false) {
            global $wpdb;
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_courses WHERE status = 'ongoing'");
            $this->set($cache_key, $count, 600);
        }

        return $count;
    }

    /**
     * الحصول على نمو الطلاب مع caching
     */
    public function get_students_growth() {
        $cache_key = 'students_growth';
        $growth = $this->get($cache_key);

        if ($growth === false) {
            global $wpdb;

            $this_month = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_students WHERE MONTH(enrollment_date) = MONTH(CURRENT_DATE()) AND YEAR(enrollment_date) = YEAR(CURRENT_DATE())");
            $last_month = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_students WHERE MONTH(enrollment_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(enrollment_date) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)");

            $growth = $last_month > 0 ? round((($this_month - $last_month) / $last_month) * 100) : 0;
            $this->set($cache_key, $growth, 3600); // 1 hour
        }

        return $growth;
    }

    /**
     * الحصول على نمو الإيرادات مع caching
     */
    public function get_revenue_growth() {
        $cache_key = 'revenue_growth';
        $growth = $this->get($cache_key);

        if ($growth === false) {
            global $wpdb;

            $this_month_revenue = $wpdb->get_var("SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}eclass_billing WHERE payment_status = 'paid' AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())");
            $last_month_revenue = $wpdb->get_var("SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}eclass_billing WHERE payment_status = 'paid' AND MONTH(payment_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(payment_date) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)");

            $growth = $last_month_revenue > 0 ? round((($this_month_revenue - $last_month_revenue) / $last_month_revenue) * 100) : 0;
            $this->set($cache_key, $growth, 3600);
        }

        return $growth;
    }

    /**
     * الحصول على الطلاب الأخيرين مع caching
     */
    public function get_recent_students($limit = 5) {
        $cache_key = 'recent_students_' . $limit;
        $students = $this->get($cache_key);

        if ($students === false) {
            global $wpdb;
            $students = $wpdb->get_results("
                SELECT s.*, c.name as course_name
                FROM {$wpdb->prefix}eclass_students s
                LEFT JOIN {$wpdb->prefix}eclass_courses c ON s.course_id = c.id
                ORDER BY s.enrollment_date DESC
                LIMIT {$limit}
            ");
            $this->set($cache_key, $students, 300); // 5 minutes
        }

        return $students;
    }

    /**
     * الحصول على المدفوعات الأخيرة مع caching
     */
    public function get_recent_payments($limit = 5) {
        $cache_key = 'recent_payments_' . $limit;
        $payments = $this->get($cache_key);

        if ($payments === false) {
            global $wpdb;
            $payments = $wpdb->get_results("
                SELECT b.*, s.name as student_name
                FROM {$wpdb->prefix}eclass_billing b
                LEFT JOIN {$wpdb->prefix}eclass_students s ON b.student_id = s.id
                ORDER BY b.created_at DESC
                LIMIT {$limit}
            ");
            $this->set($cache_key, $payments, 300);
        }

        return $payments;
    }

    /**
     * تنظيف cache الطلاب
     */
    public function clear_students_cache() {
        $this->delete('students_count');
        $this->delete('students_growth');
        $this->delete('recent_students_5');
        $this->delete('recent_students_10');
    }

    /**
     * تنظيف cache الدورات
     */
    public function clear_courses_cache() {
        $this->delete('courses_count');
        $this->delete('active_courses_count');
    }

    /**
     * تنظيف cache المدربين
     */
    public function clear_instructors_cache() {
        $this->delete('instructors_count');
    }

    /**
     * تنظيف cache الفواتير
     */
    public function clear_billing_cache() {
        $this->delete('billing_count');
        $this->delete('total_revenue');
        $this->delete('revenue_growth');
        $this->delete('recent_payments_5');
        $this->delete('recent_payments_10');
    }

    /**
     * الحصول على إحصائيات كاملة مع caching
     */
    public function get_dashboard_stats() {
        $cache_key = 'dashboard_stats';
        $stats = $this->get($cache_key);

        if ($stats === false) {
            $stats = array(
                'total_students' => $this->get_students_count(),
                'active_courses' => $this->get_active_courses_count(),
                'total_courses' => $this->get_courses_count(),
                'total_revenue' => $this->get_total_revenue(),
                'total_instructors' => $this->get_instructors_count(),
                'students_growth' => $this->get_students_growth(),
                'revenue_growth' => $this->get_revenue_growth(),
                'instructors_only' => $this->get_instructors_only_count()
            );
            $this->set($cache_key, $stats, 300); // 5 minutes
        }

        return $stats;
    }

    /**
     * الحصول على عدد المدربين فقط
     */
    private function get_instructors_only_count() {
        $cache_key = 'instructors_only_count';
        $count = $this->get($cache_key);

        if ($count === false) {
            global $wpdb;
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_instructors WHERE role = 'instructor'");
            $this->set($cache_key, $count, 600);
        }

        return $count;
    }
}