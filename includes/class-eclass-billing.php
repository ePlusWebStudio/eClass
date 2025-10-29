<?php
/**
 * Billing & Payments Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Billing {
    
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
        add_action('wp_ajax_eclass_save_billing', array($this, 'ajax_save_billing'));
        add_action('wp_ajax_eclass_delete_billing', array($this, 'ajax_delete_billing'));
        add_action('wp_ajax_eclass_get_billing', array($this, 'ajax_get_billing'));
        add_action('admin_init', array($this, 'handle_export_early'));
    }
    
    /**
     * Handle CSV export early (before any output)
     */
    public function handle_export_early() {
        if (isset($_GET['page']) && $_GET['page'] === 'eclass-billing' &&
            isset($_GET['action']) && $_GET['action'] === 'export_csv' &&
            isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'eclass_export_billing')) {
            $this->handle_csv_export();
            exit;
        }
    }
    
    /**
     * Render billing page
     */
    public function render() {
        global $wpdb;
        
        // Handle CSV import
        if (isset($_POST['eclass_import_billing']) && check_admin_referer('eclass_import_billing')) {
            $this->handle_csv_import();
        }
        
        // Get filters
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $method_filter = isset($_GET['method']) ? sanitize_text_field($_GET['method']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        // Build query
        $where = array('1=1');
        if ($status_filter) {
            $where[] = $wpdb->prepare("b.payment_status = %s", $status_filter);
        }
        if ($method_filter) {
            $where[] = $wpdb->prepare("b.payment_method = %s", $method_filter);
        }
        if ($search) {
            $where[] = $wpdb->prepare("(b.invoice_number LIKE %s OR s.name LIKE %s OR b.transaction_code LIKE %s)", 
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Get billing records
        $billings = $wpdb->get_results("
            SELECT b.*, s.name as student_name, c.name as course_name 
            FROM {$wpdb->prefix}eclass_billing b
            LEFT JOIN {$wpdb->prefix}eclass_students s ON b.student_id = s.id
            LEFT JOIN {$wpdb->prefix}eclass_courses c ON b.course_id = c.id
            WHERE $where_clause
            ORDER BY b.created_at DESC
        ");
        
        // Get students and courses for dropdowns
        $students = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}eclass_students ORDER BY name");
        $courses = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}eclass_courses ORDER BY name");
        
        ?>
        <div class="eclass-wrap">
            <div class="eclass-header">
                <h1><?php _e('Billing & Payments', 'eclass'); ?></h1>
                <div class="eclass-header-actions">
                    <button class="eclass-btn eclass-btn-secondary" onclick="eclassShowImportModal()">
                        <span class="dashicons dashicons-upload"></span>
                        <?php _e('Import CSV', 'eclass'); ?>
                    </button>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=eclass-billing&action=export_csv'), 'eclass_export_billing', 'nonce'); ?>" class="eclass-btn eclass-btn-secondary">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export CSV', 'eclass'); ?>
                    </a>
                    <button class="eclass-btn eclass-btn-primary" onclick="eclassShowBillingModal()">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add Invoice', 'eclass'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="eclass-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="eclass-billing">
                    
                    <input type="text" name="s" placeholder="<?php _e('Search invoices...', 'eclass'); ?>" value="<?php echo esc_attr($search); ?>" class="eclass-search">
                    
                    <select name="status" class="eclass-select">
                        <option value=""><?php _e('All Statuses', 'eclass'); ?></option>
                        <option value="paid" <?php selected($status_filter, 'paid'); ?>><?php _e('Paid', 'eclass'); ?></option>
                        <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php _e('Pending', 'eclass'); ?></option>
                        <option value="overdue" <?php selected($status_filter, 'overdue'); ?>><?php _e('Overdue', 'eclass'); ?></option>
                    </select>
                    
                    <select name="method" class="eclass-select">
                        <option value=""><?php _e('All Payment Methods', 'eclass'); ?></option>
                        <option value="credit_card" <?php selected($method_filter, 'credit_card'); ?>><?php _e('Credit Card', 'eclass'); ?></option>
                        <option value="bank_transfer" <?php selected($method_filter, 'bank_transfer'); ?>><?php _e('Bank Transfer', 'eclass'); ?></option>
                        <option value="cash" <?php selected($method_filter, 'cash'); ?>><?php _e('Cash', 'eclass'); ?></option>
                        <option value="other" <?php selected($method_filter, 'other'); ?>><?php _e('Other', 'eclass'); ?></option>
                    </select>
                    
                    <button type="submit" class="eclass-btn eclass-btn-secondary"><?php _e('Filter', 'eclass'); ?></button>
                    <?php if ($status_filter || $method_filter || $search): ?>
                        <a href="<?php echo admin_url('admin.php?page=eclass-billing'); ?>" class="eclass-btn eclass-btn-secondary">
                            <?php _e('Clear', 'eclass'); ?>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Billing Table -->
            <div class="eclass-card">
                <table class="eclass-table">
                    <thead>
                        <tr>
                            <th><?php _e('Invoice #', 'eclass'); ?></th>
                            <th><?php _e('Student', 'eclass'); ?></th>
                            <th><?php _e('Course', 'eclass'); ?></th>
                            <th><?php _e('Amount', 'eclass'); ?></th>
                            <th><?php _e('Due Date', 'eclass'); ?></th>
                            <th><?php _e('Status', 'eclass'); ?></th>
                            <th><?php _e('Payment Method', 'eclass'); ?></th>
                            <th><?php _e('Actions', 'eclass'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($billings)): ?>
                            <?php foreach ($billings as $billing): ?>
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
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="eclass-no-data"><?php _e('No billing records found', 'eclass'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php $this->render_footer(); ?>
        </div>
        
        <!-- Billing Modal -->
        <div id="eclass-billing-modal" class="eclass-modal">
            <div class="eclass-modal-content eclass-modal-large">
                <div class="eclass-modal-header">
                    <h2 id="eclass-billing-modal-title"><?php _e('Add Invoice', 'eclass'); ?></h2>
                    <button class="eclass-modal-close" onclick="eclassCloseBillingModal()">&times;</button>
                </div>
                <form id="eclass-billing-form">
                    <input type="hidden" id="billing-id" name="id" value="">
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Invoice Number', 'eclass'); ?> *</label>
                            <input type="text" id="billing-invoice-number" name="invoice_number" placeholder="<?php _e('e.g., INV-001', 'eclass'); ?>" required>
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('Student', 'eclass'); ?> *</label>
                            <select id="billing-student" name="student_id" required>
                                <option value=""><?php _e('Select Student', 'eclass'); ?></option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student->id; ?>"><?php echo esc_html($student->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Course', 'eclass'); ?></label>
                            <select id="billing-course" name="course_id">
                                <option value=""><?php _e('Select Course', 'eclass'); ?></option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course->id; ?>"><?php echo esc_html($course->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('Amount', 'eclass'); ?> *</label>
                            <input type="number" id="billing-amount" name="amount" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="eclass-form-row">
                        <div class="eclass-form-group">
                            <label><?php _e('Due Date', 'eclass'); ?></label>
                            <input type="date" id="billing-due-date" name="due_date">
                        </div>
                        <div class="eclass-form-group">
                            <label><?php _e('Payment Status', 'eclass'); ?> *</label>
                            <select id="billing-status" name="payment_status" onchange="eclassTogglePaymentFields()" required>
                                <option value="pending"><?php _e('Pending', 'eclass'); ?></option>
                                <option value="paid"><?php _e('Paid', 'eclass'); ?></option>
                                <option value="overdue"><?php _e('Overdue', 'eclass'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="payment-details" style="display: none;">
                        <div class="eclass-form-row">
                            <div class="eclass-form-group">
                                <label><?php _e('Payment Method', 'eclass'); ?></label>
                                <select id="billing-method" name="payment_method">
                                    <option value=""><?php _e('Select Method', 'eclass'); ?></option>
                                    <option value="credit_card"><?php _e('Credit Card', 'eclass'); ?></option>
                                    <option value="bank_transfer"><?php _e('Bank Transfer', 'eclass'); ?></option>
                                    <option value="cash"><?php _e('Cash', 'eclass'); ?></option>
                                    <option value="other"><?php _e('Other', 'eclass'); ?></option>
                                </select>
                            </div>
                            <div class="eclass-form-group">
                                <label><?php _e('Transaction Code', 'eclass'); ?></label>
                                <input type="text" id="billing-transaction-code" name="transaction_code">
                            </div>
                        </div>
                        
                        <div class="eclass-form-group">
                            <label><?php _e('Payment Date', 'eclass'); ?></label>
                            <input type="datetime-local" id="billing-payment-date" name="payment_date" step="1">
                        </div>
                    </div>
                    
                    <div class="eclass-form-group">
                        <label><?php _e('Notes', 'eclass'); ?></label>
                        <textarea id="billing-notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="eclass-modal-footer">
                        <button type="button" class="eclass-btn eclass-btn-secondary" onclick="eclassCloseBillingModal()">
                            <?php _e('Cancel', 'eclass'); ?>
                        </button>
                        <button type="submit" class="eclass-btn eclass-btn-primary">
                            <?php _e('Save Invoice', 'eclass'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Import CSV Modal -->
        <div id="eclass-import-modal" class="eclass-modal">
            <div class="eclass-modal-content">
                <div class="eclass-modal-header">
                    <h2><?php _e('Import Billing Records from CSV', 'eclass'); ?></h2>
                    <button class="eclass-modal-close" onclick="eclassCloseImportModal()">&times;</button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('eclass_import_billing'); ?>
                    <div class="eclass-form-group">
                        <label><?php _e('CSV File', 'eclass'); ?></label>
                        <input type="file" name="csv_file" accept=".csv" required>
                        <p class="description">
                            <?php _e('CSV format: invoice_number, student_id, course_id, amount, due_date, payment_status, payment_method, transaction_code, notes', 'eclass'); ?>
                        </p>
                    </div>
                    <div class="eclass-modal-footer">
                        <button type="button" class="eclass-btn eclass-btn-secondary" onclick="eclassCloseImportModal()">
                            <?php _e('Cancel', 'eclass'); ?>
                        </button>
                        <button type="submit" name="eclass_import_billing" class="eclass-btn eclass-btn-primary">
                            <?php _e('Import', 'eclass'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: Save billing
     */
    public function ajax_save_billing() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $data = array(
            'invoice_number' => sanitize_text_field($_POST['invoice_number']),
            'student_id' => intval($_POST['student_id']),
            'course_id' => !empty($_POST['course_id']) ? intval($_POST['course_id']) : null,
            'amount' => floatval($_POST['amount']),
            'due_date' => !empty($_POST['due_date']) ? sanitize_text_field($_POST['due_date']) : null,
            'payment_status' => sanitize_text_field($_POST['payment_status']),
            'payment_method' => sanitize_text_field($_POST['payment_method']),
            'transaction_code' => sanitize_text_field($_POST['transaction_code']),
            'payment_date' => !empty($_POST['payment_date']) ? sanitize_text_field($_POST['payment_date']) : null,
            'notes' => sanitize_textarea_field($_POST['notes'])
        );
        
        if ($id) {
            // Update
            $wpdb->update(
                $wpdb->prefix . 'eclass_billing',
                $data,
                array('id' => $id)
            );
        } else {
            // Insert
            $wpdb->insert(
                $wpdb->prefix . 'eclass_billing',
                $data
            );
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: Delete billing
     */
    public function ajax_delete_billing() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        $id = intval($_POST['id']);
        
        $wpdb->delete(
            $wpdb->prefix . 'eclass_billing',
            array('id' => $id)
        );
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: Get billing
     */
    public function ajax_get_billing() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        $id = intval($_POST['id']);
        
        $billing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eclass_billing WHERE id = %d",
            $id
        ));
        
        wp_send_json_success($billing);
    }
    
    /**
     * Handle CSV import
     */
    private function handle_csv_import() {
        if (!isset($_FILES['csv_file'])) {
            return;
        }
        
        $csv_handler = new EClass_CSV_Handler();
        $result = $csv_handler->import_billing($_FILES['csv_file']);
        
        if ($result['success']) {
            echo '<div class="notice notice-success"><p>' . sprintf(__('Successfully imported %d billing records.', 'eclass'), $result['count']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    /**
     * Handle CSV export
     */
    private function handle_csv_export() {
        global $wpdb;
        
        $billings = $wpdb->get_results("
            SELECT b.*, s.name as student_name, c.name as course_name 
            FROM {$wpdb->prefix}eclass_billing b
            LEFT JOIN {$wpdb->prefix}eclass_students s ON b.student_id = s.id
            LEFT JOIN {$wpdb->prefix}eclass_courses c ON b.course_id = c.id
            ORDER BY b.created_at DESC
        ", ARRAY_A);
        
        $csv_handler = new EClass_CSV_Handler();
        $csv_handler->export_billing($billings);
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
