<?php
/**
 * Settings Page Class
 * صفحة الإعدادات
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Settings {
    
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
        add_action('wp_ajax_eclass_insert_sample_data', array($this, 'ajax_insert_sample_data'));
        add_action('wp_ajax_eclass_delete_all_data', array($this, 'ajax_delete_all_data'));
    }
    
    /**
     * Render settings page
     */
    public function render() {
        global $wpdb;
        
        // Get current stats
        $stats = array(
            'students' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_students"),
            'courses' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_courses"),
            'instructors' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_instructors"),
            'billing' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_billing")
        );
        
        // Handle settings save
        if (isset($_POST['eclass_save_settings']) && check_admin_referer('eclass_save_settings')) {
            update_option('eclass_language', sanitize_text_field($_POST['language']));
            update_option('eclass_currency_symbol', sanitize_text_field($_POST['currency_symbol']));
            update_option('eclass_date_format', sanitize_text_field($_POST['date_format']));
            update_option('eclass_items_per_page', intval($_POST['items_per_page']));
            echo '<div class="notice notice-success"><p>' . eclass__('settings_saved', 'Settings saved successfully!') . '</p></div>';
            // Reload to apply language change
            echo '<script>setTimeout(function(){ location.reload(); }, 1000);</script>';
        }
        
        $i18n = EClass_i18n::get_instance();
        $current_language = get_option('eclass_language', 'ar');
        $currency_symbol = get_option('eclass_currency_symbol', '$');
        $date_format = get_option('eclass_date_format', 'Y-m-d');
        $items_per_page = get_option('eclass_items_per_page', 20);
        $text_direction = $i18n->get_text_direction();
        
        ?>
        <div class="eclass-wrap" dir="<?php echo esc_attr($text_direction); ?>">
            <div class="eclass-header">
                <h1><?php eclass_e('settings', 'Settings'); ?></h1>
            </div>
            
            <div style="padding: 32px;">
                <!-- Current Database Stats -->
                <div class="eclass-card" style="margin-bottom: 24px;">
                    <div class="eclass-card-header">
                        <h2><?php _e('Database Statistics', 'eclass'); ?></h2>
                    </div>
                    <div class="eclass-card-body" style="padding: 24px;">
                        <div class="eclass-kpi-grid">
                            <div class="eclass-kpi-card">
                                <div class="eclass-kpi-icon" style="background: #4CAF50;">
                                    <span class="dashicons dashicons-groups"></span>
                                </div>
                                <div class="eclass-kpi-content">
                                    <h3><?php echo number_format($stats['students']); ?></h3>
                                    <p><?php _e('Students', 'eclass'); ?></p>
                                </div>
                            </div>
                            
                            <div class="eclass-kpi-card">
                                <div class="eclass-kpi-icon" style="background: #2196F3;">
                                    <span class="dashicons dashicons-book"></span>
                                </div>
                                <div class="eclass-kpi-content">
                                    <h3><?php echo number_format($stats['courses']); ?></h3>
                                    <p><?php _e('Courses', 'eclass'); ?></p>
                                </div>
                            </div>
                            
                            <div class="eclass-kpi-card">
                                <div class="eclass-kpi-icon" style="background: #9C27B0;">
                                    <span class="dashicons dashicons-businessperson"></span>
                                </div>
                                <div class="eclass-kpi-content">
                                    <h3><?php echo number_format($stats['instructors']); ?></h3>
                                    <p><?php _e('Instructors', 'eclass'); ?></p>
                                </div>
                            </div>
                            
                            <div class="eclass-kpi-card">
                                <div class="eclass-kpi-icon" style="background: #FF9800;">
                                    <span class="dashicons dashicons-money-alt"></span>
                                </div>
                                <div class="eclass-kpi-content">
                                    <h3><?php echo number_format($stats['billing']); ?></h3>
                                    <p><?php _e('Invoices', 'eclass'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sample Data Section -->
                <div class="eclass-card" style="margin-bottom: 24px;">
                    <div class="eclass-card-header">
                        <h2><?php _e('Sample Data Management', 'eclass'); ?></h2>
                    </div>
                    <div class="eclass-card-body" style="padding: 24px;">
                        <p><?php _e('Use sample data to test the plugin functionality. This will insert demo instructors, courses, students, and billing records.', 'eclass'); ?></p>
                        
                        <div style="margin-top: 20px; display: flex; gap: 12px;">
                            <button type="button" class="eclass-btn eclass-btn-primary" onclick="eclassInsertSampleData()">
                                <span class="dashicons dashicons-database-add"></span>
                                <?php _e('Insert Sample Data', 'eclass'); ?>
                            </button>
                            
                            <button type="button" class="eclass-btn eclass-btn-danger" onclick="eclassDeleteAllData()">
                                <span class="dashicons dashicons-trash"></span>
                                <?php _e('Delete All Data', 'eclass'); ?>
                            </button>
                        </div>
                        
                        <div id="sample-data-message" style="margin-top: 16px;"></div>
                    </div>
                </div>
                
                <!-- General Settings -->
                <div class="eclass-card">
                    <div class="eclass-card-header">
                        <h2><?php _e('General Settings', 'eclass'); ?></h2>
                    </div>
                    <div class="eclass-card-body" style="padding: 24px;">
                        <form method="post" action="">
                            <?php wp_nonce_field('eclass_save_settings'); ?>
                            
                            <div class="eclass-form-group">
                                <label><?php eclass_e('language', 'Language'); ?></label>
                                <select name="language">
                                    <?php foreach ($i18n->get_available_languages() as $code => $name): ?>
                                        <option value="<?php echo esc_attr($code); ?>" <?php selected($current_language, $code); ?>>
                                            <?php echo esc_html($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php eclass_e('language', 'Select interface language'); ?> / اختر لغة الواجهة</p>
                            </div>
                            
                            <div class="eclass-form-group">
                                <label><?php eclass_e('currency_symbol', 'Currency Symbol'); ?></label>
                                <input type="text" name="currency_symbol" value="<?php echo esc_attr($currency_symbol); ?>" style="max-width: 100px;">
                                <p class="description"><?php eclass_e('currency_symbol', 'Symbol to display for currency'); ?> (e.g., $, €, ر.س)</p>
                            </div>
                            
                            <div class="eclass-form-group">
                                <label><?php _e('Date Format', 'eclass'); ?></label>
                                <select name="date_format">
                                    <option value="Y-m-d" <?php selected($date_format, 'Y-m-d'); ?>>YYYY-MM-DD (2024-10-29)</option>
                                    <option value="d/m/Y" <?php selected($date_format, 'd/m/Y'); ?>>DD/MM/YYYY (29/10/2024)</option>
                                    <option value="m/d/Y" <?php selected($date_format, 'm/d/Y'); ?>>MM/DD/YYYY (10/29/2024)</option>
                                    <option value="d-m-Y" <?php selected($date_format, 'd-m-Y'); ?>>DD-MM-YYYY (29-10-2024)</option>
                                </select>
                            </div>
                            
                            <div class="eclass-form-group">
                                <label><?php _e('Items Per Page', 'eclass'); ?></label>
                                <input type="number" name="items_per_page" value="<?php echo esc_attr($items_per_page); ?>" min="10" max="100" style="max-width: 100px;">
                                <p class="description"><?php _e('Number of items to display per page in tables', 'eclass'); ?></p>
                            </div>
                            
                            <button type="submit" name="eclass_save_settings" class="eclass-btn eclass-btn-primary">
                                <span class="dashicons dashicons-saved"></span>
                                <?php _e('Save Settings', 'eclass'); ?>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- System Information -->
                <div class="eclass-card" style="margin-top: 24px;">
                    <div class="eclass-card-header">
                        <h2><?php _e('System Information', 'eclass'); ?></h2>
                    </div>
                    <div class="eclass-card-body" style="padding: 24px;">
                        <table class="eclass-table">
                            <tbody>
                                <tr>
                                    <td><strong><?php _e('Plugin Version', 'eclass'); ?></strong></td>
                                    <td><?php echo ECLASS_VERSION; ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('WordPress Version', 'eclass'); ?></strong></td>
                                    <td><?php echo get_bloginfo('version'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('PHP Version', 'eclass'); ?></strong></td>
                                    <td><?php echo PHP_VERSION; ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('MySQL Version', 'eclass'); ?></strong></td>
                                    <td><?php echo $wpdb->db_version(); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('Database Tables', 'eclass'); ?></strong></td>
                                    <td>
                                        <?php echo $wpdb->prefix; ?>eclass_students,
                                        <?php echo $wpdb->prefix; ?>eclass_courses,
                                        <?php echo $wpdb->prefix; ?>eclass_instructors,
                                        <?php echo $wpdb->prefix; ?>eclass_billing
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php $this->render_footer(); ?>
        </div>
        <?php
    }
    
    /**
     * AJAX: Insert sample data
     */
    public function ajax_insert_sample_data() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-sample-data.php';
        $result = EClass_Sample_Data::insert_sample_data();
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX: Delete all data
     */
    public function ajax_delete_all_data() {
        check_ajax_referer('eclass_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-sample-data.php';
        $result = EClass_Sample_Data::delete_all_data();
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
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
