<?php
/**
 * Students Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Students {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('wp_ajax_eclass_save_student', array($this, 'ajax_save_student'));
        add_action('wp_ajax_eclass_delete_student', array($this, 'ajax_delete_student'));
        add_action('wp_ajax_eclass_get_student', array($this, 'ajax_get_student'));
        add_action('admin_init', array($this, 'handle_export_early'));
    }
    
    /**
     * Handle CSV export early (before any output)
     */
    public function handle_export_early() {
        if (isset($_GET['page']) && $_GET['page'] === 'eclass-students' &&
            isset($_GET['action']) && $_GET['action'] === 'export_csv' &&
            isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'eclass_export_students')) {
            $this->handle_csv_export();
            exit;
        }
    }
    
    /**
     * Render students page with pagination
     */
    public function render() {
        global $wpdb;

        // Handle CSV import
        if (isset($_POST['eclass_import_students']) && check_admin_referer('eclass_import_students')) {
            $this->handle_csv_import();
        }

        // Get filters
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $course_filter = isset($_GET['course']) ? intval($_GET['course']) : 0;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $per_page = get_option('eclass_items_per_page', 20);

        // Build query
        $where = array('1=1');
        if ($status_filter) {
            $where[] = $wpdb->prepare("s.enrollment_status = %s", $status_filter);
        }
        if ($course_filter) {
            $where[] = $wpdb->prepare("s.course_id = %d", $course_filter);
        }
        if ($search) {
            $where[] = $wpdb->prepare("(s.name LIKE %s OR s.email LIKE %s OR s.phone LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $where_clause = implode(' AND ', $where);

        // Get total count for pagination
        $total_students = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->prefix}eclass_students s
            LEFT JOIN {$wpdb->prefix}eclass_courses c ON s.course_id = c.id
            WHERE $where_clause
        ");

        // Initialize pagination
        $pagination = new EClass_Pagination($per_page, $current_page);
        $pagination->set_total_items($total_students);
        $pagination->set_base_url(admin_url('admin.php?page=eclass-students' .
            ($status_filter ? '&status=' . urlencode($status_filter) : '') .
            ($course_filter ? '&course=' . $course_filter : '') .
            ($search ? '&s=' . urlencode($search) : '')
        ));

        // Get students with pagination
        $students = $wpdb->get_results($wpdb->prepare("
            SELECT s.*, c.name as course_name
            FROM {$wpdb->prefix}eclass_students s
            LEFT JOIN {$wpdb->prefix}eclass_courses c ON s.course_id = c.id
            WHERE $where_clause
            ORDER BY s.created_at DESC
            LIMIT %d OFFSET %d
        ", $pagination->get_limit(), $pagination->get_offset()));
        
        // Get courses for dropdown
        $courses = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}eclass_courses ORDER BY name");
        
        ?>
        <div class="eclass-wrap">
            <div class="eclass-header">
                <h1><?php _e('Students Management', 'eclass'); ?></h1>
                <div class="eclass-header-actions">
                    <button class="eclass-btn eclass-btn-secondary" onclick="eclassShowImportModal()">
                        <span class="dashicons dashicons-upload"></span>
                        <?php _e('Import CSV', 'eclass'); ?>
                    </button>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=eclass-students&action=export_csv'), 'eclass_export_students', 'nonce'); ?>" class="eclass-btn eclass-btn-secondary">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export CSV', 'eclass'); ?>
                    </a>
                    <button class="eclass-btn eclass-btn-primary" onclick="eclassShowStudentModal()">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add Student', 'eclass'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="eclass-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="eclass-students">
                    
                    <input type="text" name="s" placeholder="<?php _e('Search students...', 'eclass'); ?>" value="<?php echo esc_attr($search); ?>" class="eclass-search">
                    
                    <select name="status" class="eclass-select">
                        <option value=""><?php _e('All Statuses', 'eclass'); ?></option>
                        <option value="active" <?php selected($status_filter, 'active'); ?>><?php _e('Active', 'eclass'); ?></option>
                        <option value="inactive" <?php selected($status_filter, 'inactive'); ?>><?php _e('Inactive', 'eclass'); ?></option>
                        <option value="completed" <?php selected($status_filter, 'completed'); ?>><?php _e('Completed', 'eclass'); ?></option>
                    </select>
                    
                    <select name="course" class="eclass-select">
                        <option value=""><?php _e('All Courses', 'eclass'); ?></option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course->id; ?>" <?php selected($course_filter, $course->id); ?>>
                                <?php echo esc_html($course->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="eclass-btn eclass-btn-secondary"><?php _e('Filter', 'eclass'); ?></button>
                    <?php if ($status_filter || $course_filter || $search): ?>
                        <a href="<?php echo admin_url('admin.php?page=eclass-students'); ?>" class="eclass-btn eclass-btn-secondary">
                            <?php _e('Clear', 'eclass'); ?>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Students Table -->
            <div class="eclass-card">
                <table class="eclass-table">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'eclass'); ?></th>
                            <th><?php _e('Email', 'eclass'); ?></th>
                            <th><?php _e('Phone', 'eclass'); ?></th>
                            <th><?php eclass_e('enrolled_courses', 'Enrolled Courses'); ?></th>
                            <th><?php _e('Actions', 'eclass'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $student): ?>
                                <?php 
                                // جلب دورات الطالب من الجدول الجديد
                                $student_courses = EClass_Enrollments::get_student_courses($student->id);
                                $courses_count = count($student_courses);
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html($student->name); ?></strong></td>
                                    <td><?php echo esc_html($student->email); ?></td>
                                    <td><?php echo esc_html($student->phone); ?></td>
                                    <td>
                                        <?php if ($courses_count > 0): ?>
                                            <span class="eclass-badge eclass-badge-info">
                                                <?php echo sprintf(_n('%d دورة', '%d دورات', $courses_count, 'eclass'), $courses_count); ?>
                                            </span>
                                            <button class="eclass-btn-text" onclick="eclassManageEnrollments(<?php echo $student->id; ?>, '<?php echo esc_js($student->name); ?>')">
                                                <?php eclass_e('manage_enrollments', 'Manage'); ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="eclass-text-muted"><?php eclass_e('no_enrollments', 'No enrollments'); ?></span>
                                            <button class="eclass-btn-text" onclick="eclassManageEnrollments(<?php echo $student->id; ?>, '<?php echo esc_js($student->name); ?>')">
                                                <?php eclass_e('enroll_in_course', 'Enroll'); ?>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="eclass-actions">
                                        <button class="eclass-btn-icon" onclick="eclassEditStudent(<?php echo $student->id; ?>)" title="<?php _e('Edit', 'eclass'); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>
                                        <button class="eclass-btn-icon eclass-btn-danger" onclick="eclassDeleteStudent(<?php echo $student->id; ?>)" title="<?php _e('Delete', 'eclass'); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="eclass-no-data"><?php _e('No students found', 'eclass'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php echo $pagination->render_pagination(); ?>

            <?php $this->render_footer(); ?>
        </div>
        
        <!-- Student Modal -->
        <div id="eclass-student-modal" class="eclass-modal">
            <div class="eclass-modal-content">
                <div class="eclass-modal-header">
                    <h2 id="eclass-student-modal-title"><?php _e('Add Student', 'eclass'); ?></h2>
                    <button class="eclass-modal-close" onclick="eclassCloseStudentModal()">&times;</button>
                </div>
                <form id="eclass-student-form">
                    <input type="hidden" id="student-id" name="id" value="">
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Name', 'eclass'); ?> *</label>
                            <input type="text" id="student-name" name="name" required>
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('Email', 'eclass'); ?> *</label>
                            <input type="email" id="student-email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Phone', 'eclass'); ?></label>
                            <input type="text" id="student-phone" name="phone">
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('Course', 'eclass'); ?></label>
                            <select id="student-course" name="course_id">
                                <option value=""><?php _e('Select Course', 'eclass'); ?></option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course->id; ?>"><?php echo esc_html($course->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Status', 'eclass'); ?></label>
                            <select id="student-status" name="enrollment_status">
                                <option value="active"><?php _e('Active', 'eclass'); ?></option>
                                <option value="inactive"><?php _e('Inactive', 'eclass'); ?></option>
                                <option value="completed"><?php _e('Completed', 'eclass'); ?></option>
                            </select>
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('Enrollment Date', 'eclass'); ?></label>
                            <input type="date" id="student-enrollment-date" name="enrollment_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="eclass-form-group">
                        <label><?php _e('Notes', 'eclass'); ?></label>
                        <textarea id="student-notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="eclass-modal-footer">
                        <button type="button" class="eclass-btn eclass-btn-secondary" onclick="eclassCloseStudentModal()">
                            <?php _e('Cancel', 'eclass'); ?>
                        </button>
                        <button type="submit" class="eclass-btn eclass-btn-primary">
                            <?php _e('Save Student', 'eclass'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Import CSV Modal -->
        <div id="eclass-import-modal" class="eclass-modal">
            <div class="eclass-modal-content">
                <div class="eclass-modal-header">
                    <h2><?php _e('Import Students from CSV', 'eclass'); ?></h2>
                    <button class="eclass-modal-close" onclick="eclassCloseImportModal()">&times;</button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('eclass_import_students'); ?>
                    <div class="eclass-form-group">
                        <label><?php _e('CSV File', 'eclass'); ?></label>
                        <input type="file" name="csv_file" accept=".csv" required>
                        <p class="description">
                            <?php _e('CSV format: name, email, phone, course_id, enrollment_status, notes', 'eclass'); ?>
                        </p>
                    </div>
                    <div class="eclass-modal-footer">
                        <button type="button" class="eclass-btn eclass-btn-secondary" onclick="eclassCloseImportModal()">
                            <?php _e('Cancel', 'eclass'); ?>
                        </button>
                        <button type="submit" name="eclass_import_students" class="eclass-btn eclass-btn-primary">
                            <?php _e('Import', 'eclass'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Enrollments Management Modal -->
        <div id="eclass-enrollments-modal" class="eclass-modal">
            <div class="eclass-modal-content" style="max-width: 800px;">
                <div class="eclass-modal-header">
                    <h2 id="eclass-enrollments-modal-title"><?php eclass_e('manage_enrollments', 'Manage Enrollments'); ?></h2>
                    <button class="eclass-modal-close" onclick="eclassCloseEnrollmentsModal()">&times;</button>
                </div>
                <div class="eclass-modal-body">
                    <input type="hidden" id="enrollment-student-id" value="">
                    
                    <!-- Add New Enrollment -->
                    <div class="eclass-card" style="margin-bottom: 20px;">
                        <div class="eclass-card-header">
                            <h3><?php eclass_e('enroll_in_course', 'Enroll in Course'); ?></h3>
                        </div>
                        <div class="eclass-card-body">
                            <form id="eclass-add-enrollment-form" style="display: flex; gap: 10px; align-items: end;">
                                <div class="eclass-form-group" style="flex: 1; margin: 0;">
                                    <label><?php eclass_e('course', 'Course'); ?></label>
                                    <select id="new-enrollment-course" required>
                                        <option value=""><?php eclass_e('select_course', 'Select Course'); ?></option>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?php echo $course->id; ?>"><?php echo esc_html($course->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="eclass-form-group" style="flex: 1; margin: 0;">
                                    <label><?php eclass_e('status', 'Status'); ?></label>
                                    <select id="new-enrollment-status">
                                        <option value="active"><?php eclass_e('active', 'Active'); ?></option>
                                        <option value="inactive"><?php eclass_e('inactive', 'Inactive'); ?></option>
                                        <option value="completed"><?php eclass_e('completed', 'Completed'); ?></option>
                                    </select>
                                </div>
                                <button type="submit" class="eclass-btn eclass-btn-primary">
                                    <span class="dashicons dashicons-plus"></span>
                                    <?php eclass_e('add', 'Add'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Current Enrollments -->
                    <div class="eclass-card">
                        <div class="eclass-card-header">
                            <h3><?php eclass_e('enrolled_courses', 'Enrolled Courses'); ?></h3>
                        </div>
                        <div class="eclass-card-body">
                            <div id="eclass-enrollments-list">
                                <p class="eclass-text-muted"><?php eclass_e('loading', 'Loading...'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: Save student
     */
    public function ajax_save_student() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'course_id' => !empty($_POST['course_id']) ? intval($_POST['course_id']) : null,
            'enrollment_status' => sanitize_text_field($_POST['enrollment_status']),
            'enrollment_date' => sanitize_text_field($_POST['enrollment_date']),
            'notes' => sanitize_textarea_field($_POST['notes'])
        );
        
        if ($id) {
            // Update
            $wpdb->update(
                $wpdb->prefix . 'eclass_students',
                $data,
                array('id' => $id)
            );
        } else {
            // Insert
            $wpdb->insert(
                $wpdb->prefix . 'eclass_students',
                $data
            );
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: Delete student
     */
    public function ajax_delete_student() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        $id = intval($_POST['id']);
        
        $wpdb->delete(
            $wpdb->prefix . 'eclass_students',
            array('id' => $id)
        );
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: Get student
     */
    public function ajax_get_student() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        $id = intval($_POST['id']);
        
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eclass_students WHERE id = %d",
            $id
        ));
        
        wp_send_json_success($student);
    }
    
    /**
     * Handle CSV import
     */
    private function handle_csv_import() {
        if (!isset($_FILES['csv_file'])) {
            return;
        }
        
        $csv_handler = new EClass_CSV_Handler();
        $result = $csv_handler->import_students($_FILES['csv_file']);
        
        if ($result['success']) {
            echo '<div class="notice notice-success"><p>' . sprintf(__('Successfully imported %d students.', 'eclass'), $result['count']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    /**
     * Handle CSV export
     */
    private function handle_csv_export() {
        global $wpdb;
        
        $students = $wpdb->get_results("
            SELECT s.*, c.name as course_name 
            FROM {$wpdb->prefix}eclass_students s
            LEFT JOIN {$wpdb->prefix}eclass_courses c ON s.course_id = c.id
            ORDER BY s.created_at DESC
        ", ARRAY_A);
        
        $csv_handler = new EClass_CSV_Handler();
        $csv_handler->export_students($students);
        exit;
    }
    
    /**
     * Render footer
     */
    private function render_footer() {
        ?>
        <div class="eclass-footer">
            <p>
                &copy; <?php echo date('Y'); ?> eClass - <?php _e('Training Academy CRM', 'eclass'); ?> | 
                <?php _e('Developed by', 'eclass'); ?> <a href="https://eplusweb.com" target="_blank">ePlusWeb.com</a>
            </p>
        </div>
        <?php
    }
}
