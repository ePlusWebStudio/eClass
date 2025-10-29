<?php
/**
 * Database Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Database {
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Students table
        $students_table = $wpdb->prefix . 'eclass_students';
        $students_sql = "CREATE TABLE $students_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) DEFAULT NULL,
            course_id bigint(20) DEFAULT NULL,
            enrollment_status varchar(50) DEFAULT 'active',
            enrollment_date datetime DEFAULT CURRENT_TIMESTAMP,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY course_id (course_id),
            KEY enrollment_status (enrollment_status)
        ) $charset_collate;";
        
        // Courses table
        $courses_table = $wpdb->prefix . 'eclass_courses';
        $courses_sql = "CREATE TABLE $courses_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            instructor_id bigint(20) DEFAULT NULL,
            schedule varchar(255) DEFAULT NULL,
            capacity int(11) DEFAULT 0,
            enrolled_count int(11) DEFAULT 0,
            status varchar(50) DEFAULT 'upcoming',
            course_type varchar(50) DEFAULT 'offline',
            location_or_link text DEFAULT NULL,
            price decimal(10,2) DEFAULT 0.00,
            description text DEFAULT NULL,
            start_date date DEFAULT NULL,
            end_date date DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY instructor_id (instructor_id),
            KEY status (status),
            KEY course_type (course_type)
        ) $charset_collate;";
        
        // Instructors table
        $instructors_table = $wpdb->prefix . 'eclass_instructors';
        $instructors_sql = "CREATE TABLE $instructors_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) DEFAULT NULL,
            role varchar(50) DEFAULT 'instructor',
            specialization text DEFAULT NULL,
            bio text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY role (role)
        ) $charset_collate;";
        
        // Billing table
        $billing_table = $wpdb->prefix . 'eclass_billing';
        $billing_sql = "CREATE TABLE $billing_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            invoice_number varchar(100) NOT NULL,
            student_id bigint(20) NOT NULL,
            course_id bigint(20) DEFAULT NULL,
            amount decimal(10,2) NOT NULL,
            due_date date DEFAULT NULL,
            payment_status varchar(50) DEFAULT 'pending',
            payment_method varchar(50) DEFAULT NULL,
            transaction_code varchar(255) DEFAULT NULL,
            payment_date datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY invoice_number (invoice_number),
            KEY student_id (student_id),
            KEY course_id (course_id),
            KEY payment_status (payment_status)
        ) $charset_collate;";
        
        // Student-Course Enrollments table (Many-to-Many)
        $enrollments_table = $wpdb->prefix . 'eclass_student_courses';
        $enrollments_sql = "CREATE TABLE $enrollments_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            student_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            enrollment_status varchar(50) DEFAULT 'active',
            enrollment_date datetime DEFAULT CURRENT_TIMESTAMP,
            completion_date datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY student_course (student_id, course_id),
            KEY student_id (student_id),
            KEY course_id (course_id),
            KEY enrollment_status (enrollment_status)
        ) $charset_collate;";
        
        dbDelta($students_sql);
        dbDelta($courses_sql);
        dbDelta($instructors_sql);
        dbDelta($billing_sql);
        dbDelta($enrollments_sql);
    }
    
    /**
     * Drop tables (for uninstall)
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'eclass_student_courses',
            $wpdb->prefix . 'eclass_billing',
            $wpdb->prefix . 'eclass_students',
            $wpdb->prefix . 'eclass_courses',
            $wpdb->prefix . 'eclass_instructors'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
}
