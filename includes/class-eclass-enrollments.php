<?php
/**
 * Student Enrollments Management Class
 * إدارة تسجيل الطلاب في الدورات المتعددة
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Enrollments {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_eclass_enroll_student', array($this, 'ajax_enroll_student'));
        add_action('wp_ajax_eclass_unenroll_student', array($this, 'ajax_unenroll_student'));
        add_action('wp_ajax_eclass_get_student_courses', array($this, 'ajax_get_student_courses'));
        add_action('wp_ajax_eclass_update_enrollment_status', array($this, 'ajax_update_enrollment_status'));
    }
    
    /**
     * Enroll student in a course
     */
    public static function enroll_student($student_id, $course_id, $status = 'active', $notes = '') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'eclass_student_courses';
        
        // Check if already enrolled
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE student_id = %d AND course_id = %d",
            $student_id,
            $course_id
        ));
        
        if ($existing) {
            // Update existing enrollment
            return $wpdb->update(
                $table,
                array(
                    'enrollment_status' => $status,
                    'notes' => $notes,
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'student_id' => $student_id,
                    'course_id' => $course_id
                )
            );
        } else {
            // Insert new enrollment
            return $wpdb->insert(
                $table,
                array(
                    'student_id' => $student_id,
                    'course_id' => $course_id,
                    'enrollment_status' => $status,
                    'enrollment_date' => current_time('mysql'),
                    'notes' => $notes
                )
            );
        }
    }
    
    /**
     * Unenroll student from a course
     */
    public static function unenroll_student($student_id, $course_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'eclass_student_courses';
        
        return $wpdb->delete(
            $table,
            array(
                'student_id' => $student_id,
                'course_id' => $course_id
            )
        );
    }
    
    /**
     * Get all courses for a student
     */
    public static function get_student_courses($student_id) {
        global $wpdb;
        
        $enrollments_table = $wpdb->prefix . 'eclass_student_courses';
        $courses_table = $wpdb->prefix . 'eclass_courses';
        
        $sql = "SELECT e.*, c.name as course_name, c.instructor_id, c.schedule, c.status as course_status
                FROM $enrollments_table e
                LEFT JOIN $courses_table c ON e.course_id = c.id
                WHERE e.student_id = %d
                ORDER BY e.enrollment_date DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $student_id));
    }
    
    /**
     * Get all students for a course
     */
    public static function get_course_students($course_id) {
        global $wpdb;
        
        $enrollments_table = $wpdb->prefix . 'eclass_student_courses';
        $students_table = $wpdb->prefix . 'eclass_students';
        
        $sql = "SELECT e.*, s.name as student_name, s.email, s.phone
                FROM $enrollments_table e
                LEFT JOIN $students_table s ON e.student_id = s.id
                WHERE e.course_id = %d
                ORDER BY e.enrollment_date DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $course_id));
    }
    
    /**
     * AJAX: Enroll student in course
     */
    public function ajax_enroll_student() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $student_id = intval($_POST['student_id']);
        $course_id = intval($_POST['course_id']);
        $status = sanitize_text_field($_POST['status']);
        $notes = sanitize_textarea_field($_POST['notes']);
        
        $result = self::enroll_student($student_id, $course_id, $status, $notes);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => eclass__('enrollment_added', 'Student enrolled successfully!')
            ));
        } else {
            wp_send_json_error(eclass__('error_occurred', 'An error occurred'));
        }
    }
    
    /**
     * AJAX: Unenroll student from course
     */
    public function ajax_unenroll_student() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $student_id = intval($_POST['student_id']);
        $course_id = intval($_POST['course_id']);
        
        $result = self::unenroll_student($student_id, $course_id);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => eclass__('enrollment_removed', 'Student unenrolled successfully!')
            ));
        } else {
            wp_send_json_error(eclass__('error_occurred', 'An error occurred'));
        }
    }
    
    /**
     * AJAX: Get student courses
     */
    public function ajax_get_student_courses() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $student_id = intval($_POST['student_id']);
        $courses = self::get_student_courses($student_id);
        
        wp_send_json_success($courses);
    }
    
    /**
     * AJAX: Update enrollment status
     */
    public function ajax_update_enrollment_status() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        
        $student_id = intval($_POST['student_id']);
        $course_id = intval($_POST['course_id']);
        $status = sanitize_text_field($_POST['status']);
        
        $table = $wpdb->prefix . 'eclass_student_courses';
        
        $result = $wpdb->update(
            $table,
            array('enrollment_status' => $status),
            array(
                'student_id' => $student_id,
                'course_id' => $course_id
            )
        );
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => eclass__('status_updated', 'Status updated successfully!')
            ));
        } else {
            wp_send_json_error(eclass__('error_occurred', 'An error occurred'));
        }
    }
}
