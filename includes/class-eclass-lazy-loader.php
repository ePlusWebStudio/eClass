<?php
/**
 * Lazy Loading System Class
 * نظام التحميل الكسول للجداول والموارد
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Lazy_Loader {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_eclass_lazy_load_students', array($this, 'ajax_lazy_load_students'));
        add_action('wp_ajax_eclass_lazy_load_courses', array($this, 'ajax_lazy_load_courses'));
        add_action('wp_ajax_eclass_lazy_load_instructors', array($this, 'ajax_lazy_load_instructors'));
        add_action('wp_ajax_eclass_lazy_load_billing', array($this, 'ajax_lazy_load_billing'));
    }

    /**
     * AJAX: Lazy load students
     */
    public function ajax_lazy_load_students() {
        check_ajax_referer('eclass_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $page = intval($_POST['page']);
        $per_page = intval($_POST['per_page']);
        $filters = $_POST['filters'] ?? array();

        $offset = ($page - 1) * $per_page;

        global $wpdb;

        // Build where clause
        $where = array('1=1');
        if (!empty($filters['status'])) {
            $where[] = $wpdb->prepare("s.enrollment_status = %s", sanitize_text_field($filters['status']));
        }
        if (!empty($filters['course'])) {
            $where[] = $wpdb->prepare("s.course_id = %d", intval($filters['course']));
        }
        if (!empty($filters['search'])) {
            $search = sanitize_text_field($filters['search']);
            $where[] = $wpdb->prepare("(s.name LIKE %s OR s.email LIKE %s OR s.phone LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $where_clause = implode(' AND ', $where);

        // Get students
        $students = $wpdb->get_results($wpdb->prepare("
            SELECT s.*, c.name as course_name
            FROM {$wpdb->prefix}eclass_students s
            LEFT JOIN {$wpdb->prefix}eclass_courses c ON s.course_id = c.id
            WHERE $where_clause
            ORDER BY s.created_at DESC
            LIMIT %d OFFSET %d
        ", $per_page, $offset));

        // Generate HTML
        ob_start();
        if (!empty($students)) {
            foreach ($students as $student) {
                $this->render_student_row($student);
            }
        }
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'has_more' => count($students) === $per_page
        ));
    }

    /**
     * Render student row HTML
     */
    private function render_student_row($student) {
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
        <?php
    }

    /**
     * AJAX: Lazy load courses
     */
    public function ajax_lazy_load_courses() {
        check_ajax_referer('eclass_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $page = intval($_POST['page']);
        $per_page = intval($_POST['per_page']);
        $filters = $_POST['filters'] ?? array();

        $offset = ($page - 1) * $per_page;

        global $wpdb;

        // Build where clause
        $where = array('1=1');
        if (!empty($filters['status'])) {
            $where[] = $wpdb->prepare("c.status = %s", sanitize_text_field($filters['status']));
        }
        if (!empty($filters['type'])) {
            $where[] = $wpdb->prepare("c.course_type = %s", sanitize_text_field($filters['type']));
        }
        if (!empty($filters['instructor'])) {
            $where[] = $wpdb->prepare("c.instructor_id = %d", intval($filters['instructor']));
        }
        if (!empty($filters['search'])) {
            $search = sanitize_text_field($filters['search']);
            $where[] = $wpdb->prepare("(c.name LIKE %s OR c.description LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $where_clause = implode(' AND ', $where);

        // Get courses
        $courses = $wpdb->get_results($wpdb->prepare("
            SELECT c.*, i.name as instructor_name
            FROM {$wpdb->prefix}eclass_courses c
            LEFT JOIN {$wpdb->prefix}eclass_instructors i ON c.instructor_id = i.id
            WHERE $where_clause
            ORDER BY c.created_at DESC
            LIMIT %d OFFSET %d
        ", $per_page, $offset));

        // Generate HTML
        ob_start();
        if (!empty($courses)) {
            foreach ($courses as $course) {
                $this->render_course_row($course);
            }
        }
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'has_more' => count($courses) === $per_page
        ));
    }

    /**
     * Render course row HTML
     */
    private function render_course_row($course) {
        ?>
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
        <?php
    }

    /**
     * AJAX: Lazy load instructors
     */
    public function ajax_lazy_load_instructors() {
        check_ajax_referer('eclass_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $page = intval($_POST['page']);
        $per_page = intval($_POST['per_page']);
        $filters = $_POST['filters'] ?? array();

        $offset = ($page - 1) * $per_page;

        global $wpdb;

        // Build where clause
        $where = array('1=1');
        if (!empty($filters['role'])) {
            $where[] = $wpdb->prepare("role = %s", sanitize_text_field($filters['role']));
        }
        if (!empty($filters['search'])) {
            $search = sanitize_text_field($filters['search']);
            $where[] = $wpdb->prepare("(name LIKE %s OR email LIKE %s OR phone LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $where_clause = implode(' AND ', $where);

        // Get instructors
        $instructors = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}eclass_instructors
            WHERE $where_clause
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d
        ", $per_page, $offset));

        // Generate HTML
        ob_start();
        if (!empty($instructors)) {
            foreach ($instructors as $instructor) {
                $this->render_instructor_row($instructor);
            }
        }
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'has_more' => count($instructors) === $per_page
        ));
    }

    /**
     * Render instructor row HTML
     */
    private function render_instructor_row($instructor) {
        ?>
        <tr>
            <td><strong><?php echo esc_html($instructor->name); ?></strong></td>
            <td><?php echo esc_html($instructor->email); ?></td>
            <td><?php echo esc_html($instructor->phone); ?></td>
            <td>
                <span class="eclass-badge eclass-badge-<?php echo esc_attr($instructor->role); ?>">
                    <?php echo esc_html(ucfirst($instructor->role)); ?>
                </span>
            </td>
            <td><?php echo esc_html($instructor->specialization ?: __('N/A', 'eclass')); ?></td>
            <td class="eclass-actions">
                <button class="eclass-btn-icon" onclick="eclassEditInstructor(<?php echo $instructor->id; ?>)" title="<?php _e('Edit', 'eclass'); ?>">
                    <span class="dashicons dashicons-edit"></span>
                </button>
                <button class="eclass-btn-icon eclass-btn-danger" onclick="eclassDeleteInstructor(<?php echo $instructor->id; ?>)" title="<?php _e('Delete', 'eclass'); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </td>
        </tr>
        <?php
    }

    /**
     * AJAX: Lazy load billing
     */
    public function ajax_lazy_load_billing() {
        check_ajax_referer('eclass_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $page = intval($_POST['page']);
        $per_page = intval($_POST['per_page']);
        $filters = $_POST['filters'] ?? array();

        $offset = ($page - 1) * $per_page;

        global $wpdb;

        // Build where clause
        $where = array('1=1');
        if (!empty($filters['status'])) {
            $where[] = $wpdb->prepare("b.payment_status = %s", sanitize_text_field($filters['status']));
        }
        if (!empty($filters['method'])) {
            $where[] = $wpdb->prepare("b.payment_method = %s", sanitize_text_field($filters['method']));
        }
        if (!empty($filters['search'])) {
            $search = sanitize_text_field($filters['search']);
            $where[] = $wpdb->prepare("(b.invoice_number LIKE %s OR s.name LIKE %s OR b.transaction_code LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $where_clause = implode(' AND ', $where);

        // Get billing records
        $billings = $wpdb->get_results($wpdb->prepare("
            SELECT b.*, s.name as student_name, c.name as course_name
            FROM {$wpdb->prefix}eclass_billing b
            LEFT JOIN {$wpdb->prefix}eclass_students s ON b.student_id = s.id
            LEFT JOIN {$wpdb->prefix}eclass_courses c ON b.course_id = c.id
            WHERE $where_clause
            ORDER BY b.created_at DESC
            LIMIT %d OFFSET %d
        ", $per_page, $offset));

        // Generate HTML
        ob_start();
        if (!empty($billings)) {
            foreach ($billings as $billing) {
                $this->render_billing_row($billing);
            }
        }
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'has_more' => count($billings) === $per_page
        ));
    }

    /**
     * Render billing row HTML
     */
    private function render_billing_row($billing) {
        ?>
        <tr>
            <td><strong><?php echo esc_html($billing->invoice_number); ?></strong></td>
            <td><?php echo esc_html($billing->student_name); ?></td>
            <td><?php echo esc_html($billing->course_name ?: __('N/A', 'eclass')); ?></td>
            <td><strong><?php echo get_option('eclass_currency_symbol', '$'); ?><?php echo number_format($billing->amount, 2); ?></strong></td>
            <td><?php echo $billing->due_date ? date_i18n(get_option('date_format'), strtotime($billing->due_date)) : __('N/A', 'eclass'); ?></td>
            <td>
                <span class="eclass-badge eclass-badge-<?php echo esc_attr($billing->payment_status); ?>">
                    <?php echo esc_html(ucfirst($billing->payment_status)); ?>
                </span>
            </td>
            <td>
                <?php if ($billing->payment_method): ?>
                    <?php echo esc_html(ucwords(str_replace('_', ' ', $billing->payment_method))); ?>
                    <?php if ($billing->transaction_code): ?>
                        <br><small class="eclass-text-muted"><?php echo esc_html($billing->transaction_code); ?></small>
                    <?php endif; ?>
                <?php else: ?>
                    <?php _e('N/A', 'eclass'); ?>
                <?php endif; ?>
            </td>
            <td class="eclass-actions">
                <button class="eclass-btn-icon" onclick="eclassEditBilling(<?php echo $billing->id; ?>)" title="<?php _e('Edit', 'eclass'); ?>">
                    <span class="dashicons dashicons-edit"></span>
                </button>
                <button class="eclass-btn-icon eclass-btn-danger" onclick="eclassDeleteBilling(<?php echo $billing->id; ?>)" title="<?php _e('Delete', 'eclass'); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </td>
        </tr>
        <?php
    }
}