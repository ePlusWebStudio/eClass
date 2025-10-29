<?php
/**
 * Instructors & Team Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Instructors {
    
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
        add_action('wp_ajax_eclass_save_instructor', array($this, 'ajax_save_instructor'));
        add_action('wp_ajax_eclass_delete_instructor', array($this, 'ajax_delete_instructor'));
        add_action('wp_ajax_eclass_get_instructor', array($this, 'ajax_get_instructor'));
        add_action('admin_init', array($this, 'handle_export_early'));
    }
    
    /**
     * Handle CSV export early (before any output)
     */
    public function handle_export_early() {
        if (isset($_GET['page']) && $_GET['page'] === 'eclass-instructors' &&
            isset($_GET['action']) && $_GET['action'] === 'export_csv' &&
            isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'eclass_export_instructors')) {
            $this->handle_csv_export();
            exit;
        }
    }
    
    /**
     * Render instructors page
     */
    public function render() {
        global $wpdb;
        
        // Handle CSV import
        if (isset($_POST['eclass_import_instructors']) && check_admin_referer('eclass_import_instructors')) {
            $this->handle_csv_import();
        }
        
        // Get filters
        $role_filter = isset($_GET['role']) ? sanitize_text_field($_GET['role']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        // Build query
        $where = array('1=1');
        if ($role_filter) {
            $where[] = $wpdb->prepare("role = %s", $role_filter);
        }
        if ($search) {
            $where[] = $wpdb->prepare("(name LIKE %s OR email LIKE %s OR phone LIKE %s)", 
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Get instructors
        $instructors = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}eclass_instructors
            WHERE $where_clause
            ORDER BY created_at DESC
        ");
        
        ?>
        <div class="eclass-wrap">
            <div class="eclass-header">
                <h1><?php _e('Instructors & Team Management', 'eclass'); ?></h1>
                <div class="eclass-header-actions">
                    <button class="eclass-btn eclass-btn-secondary" onclick="eclassShowImportModal()">
                        <span class="dashicons dashicons-upload"></span>
                        <?php _e('Import CSV', 'eclass'); ?>
                    </button>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=eclass-instructors&action=export_csv'), 'eclass_export_instructors', 'nonce'); ?>" class="eclass-btn eclass-btn-secondary">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export CSV', 'eclass'); ?>
                    </a>
                    <button class="eclass-btn eclass-btn-primary" onclick="eclassShowInstructorModal()">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add Team Member', 'eclass'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="eclass-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="eclass-instructors">
                    
                    <input type="text" name="s" placeholder="<?php _e('Search team members...', 'eclass'); ?>" value="<?php echo esc_attr($search); ?>" class="eclass-search">
                    
                    <select name="role" class="eclass-select">
                        <option value=""><?php _e('All Roles', 'eclass'); ?></option>
                        <option value="instructor" <?php selected($role_filter, 'instructor'); ?>><?php _e('Instructor', 'eclass'); ?></option>
                        <option value="admin" <?php selected($role_filter, 'admin'); ?>><?php _e('Admin', 'eclass'); ?></option>
                        <option value="support" <?php selected($role_filter, 'support'); ?>><?php _e('Support', 'eclass'); ?></option>
                    </select>
                    
                    <button type="submit" class="eclass-btn eclass-btn-secondary"><?php _e('Filter', 'eclass'); ?></button>
                    <?php if ($role_filter || $search): ?>
                        <a href="<?php echo admin_url('admin.php?page=eclass-instructors'); ?>" class="eclass-btn eclass-btn-secondary">
                            <?php _e('Clear', 'eclass'); ?>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Instructors Table -->
            <div class="eclass-card">
                <table class="eclass-table">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'eclass'); ?></th>
                            <th><?php _e('Email', 'eclass'); ?></th>
                            <th><?php _e('Phone', 'eclass'); ?></th>
                            <th><?php _e('Role', 'eclass'); ?></th>
                            <th><?php _e('Specialization', 'eclass'); ?></th>
                            <th><?php _e('Actions', 'eclass'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($instructors)): ?>
                            <?php foreach ($instructors as $instructor): ?>
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
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="eclass-no-data"><?php _e('No team members found', 'eclass'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php $this->render_footer(); ?>
        </div>
        
        <!-- Instructor Modal -->
        <div id="eclass-instructor-modal" class="eclass-modal">
            <div class="eclass-modal-content">
                <div class="eclass-modal-header">
                    <h2 id="eclass-instructor-modal-title"><?php _e('Add Team Member', 'eclass'); ?></h2>
                    <button class="eclass-modal-close" onclick="eclassCloseInstructorModal()">&times;</button>
                </div>
                <form id="eclass-instructor-form">
                    <input type="hidden" id="instructor-id" name="id" value="">
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Name', 'eclass'); ?> *</label>
                            <input type="text" id="instructor-name" name="name" required>
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('Email', 'eclass'); ?> *</label>
                            <input type="email" id="instructor-email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Phone', 'eclass'); ?></label>
                            <input type="text" id="instructor-phone" name="phone">
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('Role', 'eclass'); ?> *</label>
                            <select id="instructor-role" name="role" required>
                                <option value="instructor"><?php _e('Instructor', 'eclass'); ?></option>
                                <option value="admin"><?php _e('Admin', 'eclass'); ?></option>
                                <option value="support"><?php _e('Support', 'eclass'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="eclass-form-group">
                        <label><?php _e('Specialization', 'eclass'); ?></label>
                        <input type="text" id="instructor-specialization" name="specialization" placeholder="<?php _e('e.g., Web Development, Data Science', 'eclass'); ?>">
                    </div>
                    
                    <div class="eclass-form-group">
                        <label><?php _e('Bio', 'eclass'); ?></label>
                        <textarea id="instructor-bio" name="bio" rows="3"></textarea>
                    </div>
                    
                    <div class="eclass-modal-footer">
                        <button type="button" class="eclass-btn eclass-btn-secondary" onclick="eclassCloseInstructorModal()">
                            <?php _e('Cancel', 'eclass'); ?>
                        </button>
                        <button type="submit" class="eclass-btn eclass-btn-primary">
                            <?php _e('Save Team Member', 'eclass'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Import CSV Modal -->
        <div id="eclass-import-modal" class="eclass-modal">
            <div class="eclass-modal-content">
                <div class="eclass-modal-header">
                    <h2><?php _e('Import Team Members from CSV', 'eclass'); ?></h2>
                    <button class="eclass-modal-close" onclick="eclassCloseImportModal()">&times;</button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('eclass_import_instructors'); ?>
                    <div class="eclass-form-group">
                        <label><?php _e('CSV File', 'eclass'); ?></label>
                        <input type="file" name="csv_file" accept=".csv" required>
                        <p class="description">
                            <?php _e('CSV format: name, email, phone, role, specialization, bio', 'eclass'); ?>
                        </p>
                    </div>
                    <div class="eclass-modal-footer">
                        <button type="button" class="eclass-btn eclass-btn-secondary" onclick="eclassCloseImportModal()">
                            <?php _e('Cancel', 'eclass'); ?>
                        </button>
                        <button type="submit" name="eclass_import_instructors" class="eclass-btn eclass-btn-primary">
                            <?php _e('Import', 'eclass'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: Save instructor
     */
    public function ajax_save_instructor() {
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
            'role' => sanitize_text_field($_POST['role']),
            'specialization' => sanitize_text_field($_POST['specialization']),
            'bio' => sanitize_textarea_field($_POST['bio'])
        );
        
        if ($id) {
            // Update
            $wpdb->update(
                $wpdb->prefix . 'eclass_instructors',
                $data,
                array('id' => $id)
            );
        } else {
            // Insert
            $wpdb->insert(
                $wpdb->prefix . 'eclass_instructors',
                $data
            );
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: Delete instructor
     */
    public function ajax_delete_instructor() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        $id = intval($_POST['id']);
        
        $wpdb->delete(
            $wpdb->prefix . 'eclass_instructors',
            array('id' => $id)
        );
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: Get instructor
     */
    public function ajax_get_instructor() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        $id = intval($_POST['id']);
        
        $instructor = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eclass_instructors WHERE id = %d",
            $id
        ));
        
        wp_send_json_success($instructor);
    }
    
    /**
     * Handle CSV import
     */
    private function handle_csv_import() {
        if (!isset($_FILES['csv_file'])) {
            return;
        }
        
        $csv_handler = new EClass_CSV_Handler();
        $result = $csv_handler->import_instructors($_FILES['csv_file']);
        
        if ($result['success']) {
            echo '<div class="notice notice-success"><p>' . sprintf(__('Successfully imported %d team members.', 'eclass'), $result['count']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    /**
     * Handle CSV export
     */
    private function handle_csv_export() {
        global $wpdb;
        
        $instructors = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}eclass_instructors
            ORDER BY created_at DESC
        ", ARRAY_A);
        
        $csv_handler = new EClass_CSV_Handler();
        $csv_handler->export_instructors($instructors);
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
