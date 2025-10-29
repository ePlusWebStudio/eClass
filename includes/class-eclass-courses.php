<?php
/**
 * Courses Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Courses {
    
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
        add_action('wp_ajax_eclass_save_course', array($this, 'ajax_save_course'));
        add_action('wp_ajax_eclass_delete_course', array($this, 'ajax_delete_course'));
        add_action('wp_ajax_eclass_get_course', array($this, 'ajax_get_course'));
        add_action('admin_init', array($this, 'handle_export_early'));
    }
    
    /**
     * Handle CSV export early (before any output)
     */
    public function handle_export_early() {
        if (isset($_GET['page']) && $_GET['page'] === 'eclass-courses' &&
            isset($_GET['action']) && $_GET['action'] === 'export_csv' &&
            isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'eclass_export_courses')) {
            $this->handle_csv_export();
            exit;
        }
    }
    
    /**
     * Render courses page
     */
    public function render() {
        global $wpdb;
        
        // Handle CSV import
        if (isset($_POST['eclass_import_courses']) && check_admin_referer('eclass_import_courses')) {
            $this->handle_csv_import();
        }
        
        // Get filters
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $type_filter = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
        $instructor_filter = isset($_GET['instructor']) ? intval($_GET['instructor']) : 0;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        // Build query
        $where = array('1=1');
        if ($status_filter) {
            $where[] = $wpdb->prepare("c.status = %s", $status_filter);
        }
        if ($type_filter) {
            $where[] = $wpdb->prepare("c.course_type = %s", $type_filter);
        }
        if ($instructor_filter) {
            $where[] = $wpdb->prepare("c.instructor_id = %d", $instructor_filter);
        }
        if ($search) {
            $where[] = $wpdb->prepare("(c.name LIKE %s OR c.description LIKE %s)", 
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Get courses
        $courses = $wpdb->get_results("
            SELECT c.*, i.name as instructor_name 
            FROM {$wpdb->prefix}eclass_courses c
            LEFT JOIN {$wpdb->prefix}eclass_instructors i ON c.instructor_id = i.id
            WHERE $where_clause
            ORDER BY c.created_at DESC
        ");
        
        // Get instructors for dropdown
        $instructors = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}eclass_instructors WHERE role = 'instructor' ORDER BY name");
        
        ?>
        <div class="eclass-wrap">
            <div class="eclass-header">
                <h1><?php _e('Courses Management', 'eclass'); ?></h1>
                <div class="eclass-header-actions">
                    <button class="eclass-btn eclass-btn-secondary" onclick="eclassShowImportModal()">
                        <span class="dashicons dashicons-upload"></span>
                        <?php _e('Import CSV', 'eclass'); ?>
                    </button>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=eclass-courses&action=export_csv'), 'eclass_export_courses', 'nonce'); ?>" class="eclass-btn eclass-btn-secondary">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export CSV', 'eclass'); ?>
                    </a>
                    <button class="eclass-btn eclass-btn-primary" onclick="eclassShowCourseModal()">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add Course', 'eclass'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="eclass-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="eclass-courses">
                    
                    <input type="text" name="s" placeholder="<?php _e('Search courses...', 'eclass'); ?>" value="<?php echo esc_attr($search); ?>" class="eclass-search">
                    
                    <select name="status" class="eclass-select">
                        <option value=""><?php _e('All Statuses', 'eclass'); ?></option>
                        <option value="upcoming" <?php selected($status_filter, 'upcoming'); ?>><?php _e('Upcoming', 'eclass'); ?></option>
                        <option value="ongoing" <?php selected($status_filter, 'ongoing'); ?>><?php _e('Ongoing', 'eclass'); ?></option>
                        <option value="completed" <?php selected($status_filter, 'completed'); ?>><?php _e('Completed', 'eclass'); ?></option>
                    </select>
                    
                    <select name="type" class="eclass-select">
                        <option value=""><?php _e('All Types', 'eclass'); ?></option>
                        <option value="offline" <?php selected($type_filter, 'offline'); ?>><?php _e('Offline', 'eclass'); ?></option>
                        <option value="online" <?php selected($type_filter, 'online'); ?>><?php _e('Online', 'eclass'); ?></option>
                    </select>
                    
                    <select name="instructor" class="eclass-select">
                        <option value=""><?php _e('All Instructors', 'eclass'); ?></option>
                        <?php foreach ($instructors as $instructor): ?>
                            <option value="<?php echo $instructor->id; ?>" <?php selected($instructor_filter, $instructor->id); ?>>
                                <?php echo esc_html($instructor->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="eclass-btn eclass-btn-secondary"><?php _e('Filter', 'eclass'); ?></button>
                    <?php if ($status_filter || $type_filter || $instructor_filter || $search): ?>
                        <a href="<?php echo admin_url('admin.php?page=eclass-courses'); ?>" class="eclass-btn eclass-btn-secondary">
                            <?php _e('Clear', 'eclass'); ?>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Courses Table -->
            <div class="eclass-card">
                <table class="eclass-table">
                    <thead>
                        <tr>
                            <th><?php _e('Course Name', 'eclass'); ?></th>
                            <th><?php _e('Instructor', 'eclass'); ?></th>
                            <th><?php _e('Type', 'eclass'); ?></th>
                            <th><?php _e('Schedule', 'eclass'); ?></th>
                            <th><?php _e('Capacity', 'eclass'); ?></th>
                            <th><?php _e('Status', 'eclass'); ?></th>
                            <th><?php _e('Price', 'eclass'); ?></th>
                            <th><?php _e('Actions', 'eclass'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($courses)): ?>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($course->name); ?></strong>
                                        <?php if ($course->location_or_link): ?>
                                            <br><small class="eclass-text-muted">
                                                <?php if ($course->course_type === 'online'): ?>
                                                    <span class="dashicons dashicons-video-alt3"></span>
                                                <?php else: ?>
                                                    <span class="dashicons dashicons-location"></span>
                                                <?php endif; ?>
                                                <?php echo esc_html($course->location_or_link); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($course->instructor_name ?: __('N/A', 'eclass')); ?></td>
                                    <td>
                                        <span class="eclass-badge eclass-badge-<?php echo esc_attr($course->course_type); ?>">
                                            <?php echo $course->course_type === 'online' ? __('Online', 'eclass') : __('Offline', 'eclass'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($course->schedule ?: __('N/A', 'eclass')); ?></td>
                                    <td><?php echo $course->enrolled_count; ?> / <?php echo $course->capacity; ?></td>
                                    <td>
                                        <span class="eclass-badge eclass-badge-<?php echo esc_attr($course->status); ?>">
                                            <?php echo esc_html(ucfirst($course->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo get_option('eclass_currency_symbol', '$'); ?><?php echo number_format($course->price, 2); ?></td>
                                    <td class="eclass-actions">
                                        <button class="eclass-btn-icon" onclick="eclassEditCourse(<?php echo $course->id; ?>)" title="<?php _e('Edit', 'eclass'); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>
                                        <button class="eclass-btn-icon eclass-btn-danger" onclick="eclassDeleteCourse(<?php echo $course->id; ?>)" title="<?php _e('Delete', 'eclass'); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="eclass-no-data"><?php _e('No courses found', 'eclass'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php $this->render_footer(); ?>
        </div>
        
        <!-- Course Modal -->
        <div id="eclass-course-modal" class="eclass-modal">
            <div class="eclass-modal-content eclass-modal-large">
                <div class="eclass-modal-header">
                    <h2 id="eclass-course-modal-title"><?php _e('Add Course', 'eclass'); ?></h2>
                    <button class="eclass-modal-close" onclick="eclassCloseCourseModal()">&times;</button>
                </div>
                <form id="eclass-course-form">
                    <input type="hidden" id="course-id" name="id" value="">
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Course Name', 'eclass'); ?> *</label>
                            <input type="text" id="course-name" name="name" required>
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('Instructor', 'eclass'); ?></label>
                            <select id="course-instructor" name="instructor_id">
                                <option value=""><?php _e('Select Instructor', 'eclass'); ?></option>
                                <?php foreach ($instructors as $instructor): ?>
                                    <option value="<?php echo $instructor->id; ?>"><?php echo esc_html($instructor->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Course Type', 'eclass'); ?> *</label>
                            <select id="course-type" name="course_type" onchange="eclassToggleCourseTypeField()" required>
                                <option value="offline"><?php _e('Offline (In-Person)', 'eclass'); ?></option>
                                <option value="online"><?php _e('Online', 'eclass'); ?></option>
                            </select>
                        </div>
                        <div class="eclass-form-group">
                            <label id="course-location-label"><?php _e('Location/Room', 'eclass'); ?></label>
                            <input type="text" id="course-location" name="location_or_link" placeholder="<?php _e('Enter room name or meeting link', 'eclass'); ?>">
                        </div>
                    </div>
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Schedule', 'eclass'); ?></label>
                            <input type="text" id="course-schedule" name="schedule" placeholder="<?php _e('e.g., Mon-Wed 10:00-12:00', 'eclass'); ?>">
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('Capacity', 'eclass'); ?></label>
                            <input type="number" id="course-capacity" name="capacity" min="0" value="0">
                        </div>
                    </div>
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Start Date', 'eclass'); ?></label>
                            <input type="date" id="course-start-date" name="start_date">
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('End Date', 'eclass'); ?></label>
                            <input type="date" id="course-end-date" name="end_date">
                        </div>
                    </div>
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Price', 'eclass'); ?></label>
                            <input type="number" id="course-price" name="price" min="0" step="0.01" value="0">
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('Status', 'eclass'); ?></label>
                            <select id="course-status" name="status">
                                <option value="upcoming"><?php _e('Upcoming', 'eclass'); ?></option>
                                <option value="ongoing"><?php _e('Ongoing', 'eclass'); ?></option>
                                <option value="completed"><?php _e('Completed', 'eclass'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="eclass-form-group">
                        <label><?php _e('Description', 'eclass'); ?></label>
                        <textarea id="course-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="eclass-modal-footer">
                        <button type="button" class="eclass-btn eclass-btn-secondary" onclick="eclassCloseCourseModal()">
                            <?php _e('Cancel', 'eclass'); ?>
                        </button>
                        <button type="submit" class="eclass-btn eclass-btn-primary">
                            <?php _e('Save Course', 'eclass'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Import CSV Modal -->
        <div id="eclass-import-modal" class="eclass-modal">
            <div class="eclass-modal-content">
                <div class="eclass-modal-header">
                    <h2><?php _e('Import Courses from CSV', 'eclass'); ?></h2>
                    <button class="eclass-modal-close" onclick="eclassCloseImportModal()">&times;</button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('eclass_import_courses'); ?>
                    <div class="eclass-form-group">
                        <label><?php _e('CSV File', 'eclass'); ?></label>
                        <input type="file" name="csv_file" accept=".csv" required>
                        <p class="description">
                            <?php _e('CSV format: name, instructor_id, schedule, capacity, status, course_type, location_or_link, price, description', 'eclass'); ?>
                        </p>
                    </div>
                    <div class="eclass-modal-footer">
                        <button type="button" class="eclass-btn eclass-btn-secondary" onclick="eclassCloseImportModal()">
                            <?php _e('Cancel', 'eclass'); ?>
                        </button>
                        <button type="submit" name="eclass_import_courses" class="eclass-btn eclass-btn-primary">
                            <?php _e('Import', 'eclass'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: Save course
     */
    public function ajax_save_course() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'instructor_id' => !empty($_POST['instructor_id']) ? intval($_POST['instructor_id']) : null,
            'schedule' => sanitize_text_field($_POST['schedule']),
            'capacity' => intval($_POST['capacity']),
            'status' => sanitize_text_field($_POST['status']),
            'course_type' => sanitize_text_field($_POST['course_type']),
            'location_or_link' => sanitize_text_field($_POST['location_or_link']),
            'price' => floatval($_POST['price']),
            'description' => sanitize_textarea_field($_POST['description']),
            'start_date' => !empty($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : null,
            'end_date' => !empty($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : null
        );
        
        if ($id) {
            // Update
            $wpdb->update(
                $wpdb->prefix . 'eclass_courses',
                $data,
                array('id' => $id)
            );
        } else {
            // Insert
            $wpdb->insert(
                $wpdb->prefix . 'eclass_courses',
                $data
            );
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: Delete course
     */
    public function ajax_delete_course() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        $id = intval($_POST['id']);
        
        $wpdb->delete(
            $wpdb->prefix . 'eclass_courses',
            array('id' => $id)
        );
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: Get course
     */
    public function ajax_get_course() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        $id = intval($_POST['id']);
        
        $course = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eclass_courses WHERE id = %d",
            $id
        ));
        
        wp_send_json_success($course);
    }
    
    /**
     * Handle CSV import
     */
    private function handle_csv_import() {
        if (!isset($_FILES['csv_file'])) {
            return;
        }
        
        $csv_handler = new EClass_CSV_Handler();
        $result = $csv_handler->import_courses($_FILES['csv_file']);
        
        if ($result['success']) {
            echo '<div class="notice notice-success"><p>' . sprintf(__('Successfully imported %d courses.', 'eclass'), $result['count']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    /**
     * Handle CSV export
     */
    private function handle_csv_export() {
        global $wpdb;
        
        $courses = $wpdb->get_results("
            SELECT c.*, i.name as instructor_name 
            FROM {$wpdb->prefix}eclass_courses c
            LEFT JOIN {$wpdb->prefix}eclass_instructors i ON c.instructor_id = i.id
            ORDER BY c.created_at DESC
        ", ARRAY_A);
        
        $csv_handler = new EClass_CSV_Handler();
        $csv_handler->export_courses($courses);
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
