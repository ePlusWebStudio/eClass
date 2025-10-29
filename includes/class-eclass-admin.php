<?php
/**
 * Admin Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_actions'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('eClass Academy', 'eclass'),
            __('eClass', 'eclass'),
            'manage_options',
            'eclass-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-welcome-learn-more',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'eclass-dashboard',
            __('Dashboard', 'eclass'),
            __('Dashboard', 'eclass'),
            'manage_options',
            'eclass-dashboard',
            array($this, 'render_dashboard')
        );
        
        // Students submenu
        add_submenu_page(
            'eclass-dashboard',
            __('Students', 'eclass'),
            __('Students', 'eclass'),
            'manage_options',
            'eclass-students',
            array($this, 'render_students')
        );
        
        // Courses submenu
        add_submenu_page(
            'eclass-dashboard',
            __('Courses', 'eclass'),
            __('Courses', 'eclass'),
            'manage_options',
            'eclass-courses',
            array($this, 'render_courses')
        );
        
        // Instructors submenu
        add_submenu_page(
            'eclass-dashboard',
            __('Instructors & Team', 'eclass'),
            __('Instructors & Team', 'eclass'),
            'manage_options',
            'eclass-instructors',
            array($this, 'render_instructors')
        );
        
        // Billing submenu
        add_submenu_page(
            'eclass-dashboard',
            __('Billing & Payments', 'eclass'),
            __('Billing & Payments', 'eclass'),
            'manage_options',
            'eclass-billing',
            array($this, 'render_billing')
        );
        
        // Settings submenu
        add_submenu_page(
            'eclass-dashboard',
            __('Settings', 'eclass'),
            __('Settings', 'eclass'),
            'manage_options',
            'eclass-settings',
            array($this, 'render_settings')
        );
    }
    
    /**
     * Handle actions
     */
    public function handle_actions() {
        // Handle various actions here
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        $dashboard = new EClass_Dashboard();
        $dashboard->render();
    }
    
    /**
     * Render students page
     */
    public function render_students() {
        EClass_Students::get_instance()->render();
    }
    
    /**
     * Render courses page
     */
    public function render_courses() {
        EClass_Courses::get_instance()->render();
    }
    
    /**
     * Render instructors page
     */
    public function render_instructors() {
        EClass_Instructors::get_instance()->render();
    }
    
    /**
     * Render billing page
     */
    public function render_billing() {
        EClass_Billing::get_instance()->render();
    }
    
    /**
     * Render settings page
     */
    public function render_settings() {
        EClass_Settings::get_instance()->render();
    }
}
