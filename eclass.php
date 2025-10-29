<?php
/**
 * Plugin Name: eClass - Training Academy CRM
 * Plugin URI: https://eplusweb.com
 * Description: A professional Training Academy CRM application for managing training academies with comprehensive student enrollment, course scheduling, instructor management, and integrated billing.
 * Version: 1.2.4
 * Author: ePlusWeb
 * Author URI: https://eplusweb.com
 * Text Domain: eclass
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ECLASS_VERSION', '1.2.4');
define('ECLASS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ECLASS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ECLASS_PLUGIN_FILE', __FILE__);

/**
 * Main eClass Plugin Class
 */
class EClass_Plugin {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
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
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-i18n.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-database.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-admin.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-students.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-courses.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-instructors.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-billing.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-dashboard.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-csv-handler.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-settings.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-enrollments.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-cache.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-pagination.php';
        require_once ECLASS_PLUGIN_DIR . 'includes/class-eclass-lazy-loader.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Initialize i18n
        EClass_i18n::get_instance();
        
        // Initialize admin
        if (is_admin()) {
            EClass_Admin::get_instance();
            
            // Initialize all classes to register AJAX handlers
            EClass_Settings::get_instance();
            EClass_Students::get_instance();
            EClass_Courses::get_instance();
            EClass_Instructors::get_instance();
            EClass_Billing::get_instance();
            EClass_Enrollments::get_instance();
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        EClass_Database::create_tables();
        
        // Set default options
        if (!get_option('eclass_version')) {
            update_option('eclass_version', ECLASS_VERSION);
        }
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('eclass', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'eclass') === false) {
            return;
        }
        
        $i18n = EClass_i18n::get_instance();
        
        // Enqueue CSS
        wp_enqueue_style('eclass-admin', ECLASS_PLUGIN_URL . 'assets/css/admin.css', array(), ECLASS_VERSION);
        
        // Add RTL support
        if ($i18n->is_rtl()) {
            $rtl_css = '
                .eclass-wrap { direction: rtl; }
                .eclass-wrap * { direction: rtl; }
                .eclass-table { text-align: right; }
                .eclass-form-group label { text-align: right; }
                .eclass-modal-content { text-align: right; }
                .eclass-btn .dashicons { margin-left: 5px; margin-right: 0; }
            ';
            wp_add_inline_style('eclass-admin', $rtl_css);
        }
        
        // Enqueue JavaScript
        wp_enqueue_script('eclass-admin', ECLASS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), ECLASS_VERSION, true);

        // Ensure message helper is always defined before script runs
        $inline_message_helper = <<<'JS'
window.eclassShowMessage = window.eclassShowMessage || function(message, type) {
    type = type === 'error' ? 'error' : 'success';
    if (window.console && console.warn) {
        console.warn('Fallback eclassShowMessage invoked', message, type);
    }
    alert(message);
};
JS;
        wp_add_inline_script('eclass-admin', $inline_message_helper, 'before');
        
        // Localize script
        wp_localize_script('eclass-admin', 'eclassData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eclass_nonce'),
            'confirmDelete' => eclass__('confirm_delete', 'Are you sure you want to delete this item?'),
            'error' => eclass__('error_occurred', 'An error occurred. Please try again.'),
            'language' => $i18n->get_current_language(),
            'isRTL' => $i18n->is_rtl(),
        ));
    }
}

/**
 * Initialize the plugin
 */
function eclass_init() {
    return EClass_Plugin::get_instance();
}

// Start the plugin
eclass_init();
