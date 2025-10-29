<?php
/**
 * CSV Import/Export Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_CSV_Handler {
    
    /**
     * Import students from CSV
     */
    public function import_students($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array('success' => false, 'message' => __('File upload error', 'eclass'));
        }
        
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            return array('success' => false, 'message' => __('Could not open file', 'eclass'));
        }
        
        global $wpdb;
        $count = 0;
        $header = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== false) {
            $wpdb->insert(
                $wpdb->prefix . 'eclass_students',
                array(
                    'name' => sanitize_text_field($data[0]),
                    'email' => sanitize_email($data[1]),
                    'phone' => sanitize_text_field($data[2] ?? ''),
                    'course_id' => !empty($data[3]) ? intval($data[3]) : null,
                    'enrollment_status' => sanitize_text_field($data[4] ?? 'active'),
                    'notes' => sanitize_textarea_field($data[5] ?? '')
                )
            );
            $count++;
        }
        
        fclose($handle);
        return array('success' => true, 'count' => $count);
    }
    
    /**
     * Export students to CSV
     */
    public function export_students($students) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=eclass-students-' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Name', 'Email', 'Phone', 'Course', 'Status', 'Enrollment Date', 'Notes'));
        
        foreach ($students as $student) {
            fputcsv($output, array(
                $student['name'],
                $student['email'],
                $student['phone'],
                $student['course_name'] ?? '',
                $student['enrollment_status'],
                $student['enrollment_date'],
                $student['notes']
            ));
        }
        
        fclose($output);
    }
    
    /**
     * Import courses from CSV
     */
    public function import_courses($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array('success' => false, 'message' => __('File upload error', 'eclass'));
        }
        
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            return array('success' => false, 'message' => __('Could not open file', 'eclass'));
        }
        
        global $wpdb;
        $count = 0;
        $header = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== false) {
            $wpdb->insert(
                $wpdb->prefix . 'eclass_courses',
                array(
                    'name' => sanitize_text_field($data[0]),
                    'instructor_id' => !empty($data[1]) ? intval($data[1]) : null,
                    'schedule' => sanitize_text_field($data[2] ?? ''),
                    'capacity' => intval($data[3] ?? 0),
                    'status' => sanitize_text_field($data[4] ?? 'upcoming'),
                    'course_type' => sanitize_text_field($data[5] ?? 'offline'),
                    'location_or_link' => sanitize_text_field($data[6] ?? ''),
                    'price' => floatval($data[7] ?? 0),
                    'description' => sanitize_textarea_field($data[8] ?? '')
                )
            );
            $count++;
        }
        
        fclose($handle);
        return array('success' => true, 'count' => $count);
    }
    
    /**
     * Export courses to CSV
     */
    public function export_courses($courses) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=eclass-courses-' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Name', 'Instructor', 'Schedule', 'Capacity', 'Enrolled', 'Status', 'Type', 'Location/Link', 'Price', 'Description'));
        
        foreach ($courses as $course) {
            fputcsv($output, array(
                $course['name'],
                $course['instructor_name'] ?? '',
                $course['schedule'],
                $course['capacity'],
                $course['enrolled_count'],
                $course['status'],
                $course['course_type'],
                $course['location_or_link'],
                $course['price'],
                $course['description']
            ));
        }
        
        fclose($output);
    }
    
    /**
     * Import instructors from CSV
     */
    public function import_instructors($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array('success' => false, 'message' => __('File upload error', 'eclass'));
        }
        
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            return array('success' => false, 'message' => __('Could not open file', 'eclass'));
        }
        
        global $wpdb;
        $count = 0;
        $header = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== false) {
            $wpdb->insert(
                $wpdb->prefix . 'eclass_instructors',
                array(
                    'name' => sanitize_text_field($data[0]),
                    'email' => sanitize_email($data[1]),
                    'phone' => sanitize_text_field($data[2] ?? ''),
                    'role' => sanitize_text_field($data[3] ?? 'instructor'),
                    'specialization' => sanitize_text_field($data[4] ?? ''),
                    'bio' => sanitize_textarea_field($data[5] ?? '')
                )
            );
            $count++;
        }
        
        fclose($handle);
        return array('success' => true, 'count' => $count);
    }
    
    /**
     * Export instructors to CSV
     */
    public function export_instructors($instructors) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=eclass-instructors-' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Name', 'Email', 'Phone', 'Role', 'Specialization', 'Bio'));
        
        foreach ($instructors as $instructor) {
            fputcsv($output, array(
                $instructor['name'],
                $instructor['email'],
                $instructor['phone'],
                $instructor['role'],
                $instructor['specialization'],
                $instructor['bio']
            ));
        }
        
        fclose($output);
    }
    
    /**
     * Import billing from CSV
     */
    public function import_billing($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array('success' => false, 'message' => __('File upload error', 'eclass'));
        }
        
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            return array('success' => false, 'message' => __('Could not open file', 'eclass'));
        }
        
        global $wpdb;
        $count = 0;
        $header = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== false) {
            $wpdb->insert(
                $wpdb->prefix . 'eclass_billing',
                array(
                    'invoice_number' => sanitize_text_field($data[0]),
                    'student_id' => intval($data[1]),
                    'course_id' => !empty($data[2]) ? intval($data[2]) : null,
                    'amount' => floatval($data[3]),
                    'due_date' => !empty($data[4]) ? sanitize_text_field($data[4]) : null,
                    'payment_status' => sanitize_text_field($data[5] ?? 'pending'),
                    'payment_method' => sanitize_text_field($data[6] ?? ''),
                    'transaction_code' => sanitize_text_field($data[7] ?? ''),
                    'notes' => sanitize_textarea_field($data[8] ?? '')
                )
            );
            $count++;
        }
        
        fclose($handle);
        return array('success' => true, 'count' => $count);
    }
    
    /**
     * Export billing to CSV
     */
    public function export_billing($billings) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=eclass-billing-' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Invoice #', 'Student', 'Course', 'Amount', 'Due Date', 'Status', 'Payment Method', 'Transaction Code', 'Payment Date', 'Notes'));
        
        foreach ($billings as $billing) {
            fputcsv($output, array(
                $billing['invoice_number'],
                $billing['student_name'],
                $billing['course_name'] ?? '',
                $billing['amount'],
                $billing['due_date'],
                $billing['payment_status'],
                $billing['payment_method'],
                $billing['transaction_code'],
                $billing['payment_date'],
                $billing['notes']
            ));
        }
        
        fclose($output);
    }
}
