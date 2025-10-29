<?php
/**
 * Internationalization Class
 * كلاس إدارة اللغات
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_i18n {
    
    private static $instance = null;
    private $translations = array();
    private $current_language = 'ar';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->current_language = get_option('eclass_language', 'ar');
        $this->load_translations();
    }
    
    /**
     * Load translation file with caching
     */
    private function load_translations() {
        $cache_key = 'translations_' . $this->current_language;
        $cache = EClass_Cache::get_instance();

        $this->translations = $cache->get($cache_key);

        if ($this->translations === false) {
            $lang_file = ECLASS_PLUGIN_DIR . 'languages/eclass-' . $this->current_language . '.php';

            if (file_exists($lang_file)) {
                $this->translations = include $lang_file;
            } else {
                // Fallback to Arabic
                $fallback_file = ECLASS_PLUGIN_DIR . 'languages/eclass-ar.php';
                if (file_exists($fallback_file)) {
                    $this->translations = include $fallback_file;
                } else {
                    $this->translations = array();
                }
            }

            // Cache translations for 1 hour
            $cache->set($cache_key, $this->translations, 3600);
        }
    }
    
    /**
     * Get translation
     */
    public function __($key, $default = '') {
        if (isset($this->translations[$key])) {
            return $this->translations[$key];
        }
        return $default ? $default : $key;
    }
    
    /**
     * Echo translation
     */
    public function _e($key, $default = '') {
        echo $this->__($key, $default);
    }
    
    /**
     * Get current language
     */
    public function get_current_language() {
        return $this->current_language;
    }
    
    /**
     * Get available languages
     */
    public function get_available_languages() {
        return array(
            'ar' => 'العربية (Arabic)',
            'en_US' => 'English'
        );
    }
    
    /**
     * Is RTL language
     */
    public function is_rtl() {
        return in_array($this->current_language, array('ar', 'he', 'fa', 'ur'));
    }
    
    /**
     * Get text direction
     */
    public function get_text_direction() {
        return $this->is_rtl() ? 'rtl' : 'ltr';
    }
}

/**
 * Helper functions
 */
function eclass__($key, $default = '') {
    return EClass_i18n::get_instance()->__($key, $default);
}

function eclass_e($key, $default = '') {
    EClass_i18n::get_instance()->_e($key, $default);
}

/**
 * Override WordPress translation functions for eclass text domain
 */
add_filter('gettext_eclass', 'eclass_translate_text', 10, 3);
add_filter('gettext_with_context_eclass', 'eclass_translate_text', 10, 3);

function eclass_translate_text($translated, $text, $domain) {
    if ($domain !== 'eclass') {
        return $translated;
    }
    
    $i18n = EClass_i18n::get_instance();
    
    // Map common WordPress translations to our keys
    $key_map = array(
        // Main menu
        'Settings' => 'settings',
        'Dashboard' => 'dashboard',
        'Students' => 'students',
        'Courses' => 'courses',
        'Instructors & Team' => 'instructors_team',
        'Billing & Payments' => 'billing_payments',
        
        // Actions
        'Add Student' => 'add_student',
        'Edit Student' => 'edit_student',
        'Add Course' => 'add_course',
        'Edit Course' => 'edit_course',
        'Add Team Member' => 'add_team_member',
        'Edit Team Member' => 'edit_team_member',
        'Add Invoice' => 'add_invoice',
        'Edit Invoice' => 'edit_invoice',
        'Save' => 'save',
        'Cancel' => 'cancel',
        'Delete' => 'delete',
        'Edit' => 'edit',
        'Search' => 'search',
        'Filter' => 'filter',
        'Clear' => 'clear',
        'Export CSV' => 'export_csv',
        'Import CSV' => 'import_csv',
        'Actions' => 'actions',
        'Close' => 'close',
        
        // Fields
        'Name' => 'name',
        'Email' => 'email',
        'Phone' => 'phone',
        'Status' => 'status',
        'Date' => 'date',
        'Notes' => 'notes',
        'Description' => 'description',
        'Course' => 'course',
        'Student' => 'student',
        'Instructor' => 'instructor',
        'Amount' => 'amount',
        'Price' => 'price',
        'Role' => 'role',
        
        // Settings
        'Database Statistics' => 'database_statistics',
        'General Settings' => 'general_settings',
        'System Information' => 'system_information',
        'Sample Data Management' => 'sample_data_management',
        'Insert Sample Data' => 'insert_sample_data',
        'Delete All Data' => 'delete_all_data',
        'Language' => 'language',
        'Currency Symbol' => 'currency_symbol',
        'Date Format' => 'date_format',
        'Items Per Page' => 'items_per_page',
        'Save Settings' => 'save_settings',
        'Plugin Version' => 'plugin_version',
        'WordPress Version' => 'wordpress_version',
        'PHP Version' => 'php_version',
        'MySQL Version' => 'mysql_version',
        'Database Tables' => 'database_tables',
        
        // Dashboard
        'Total Students' => 'total_students',
        'Active Courses' => 'active_courses',
        'Total Revenue' => 'total_revenue',
        'Team Members' => 'team_members',
        'Recent Enrollments' => 'recent_enrollments',
        'Recent Payments' => 'recent_payments',
        'View All' => 'view_all',
        
        // Status
        'Active' => 'active',
        'Inactive' => 'inactive',
        'Completed' => 'completed',
        'Pending' => 'pending',
        'Paid' => 'paid',
        'Overdue' => 'overdue',
        'Online' => 'online',
        'Offline' => 'offline',
        'Upcoming' => 'upcoming',
        'Ongoing' => 'ongoing',
        
        // More fields
        'Enrollment Status' => 'enrollment_status',
        'Enrollment Date' => 'enrollment_date',
        'Student Name' => 'student_name',
        'Course Name' => 'course_name',
        'Course Type' => 'course_type',
        'Meeting Link' => 'meeting_link',
        'Location/Room' => 'location_room',
        'Schedule' => 'schedule',
        'Capacity' => 'capacity',
        'Enrolled' => 'enrolled',
        'Start Date' => 'start_date',
        'End Date' => 'end_date',
        'Specialization' => 'specialization',
        'Bio' => 'bio',
        'Biography' => 'bio',
        'Invoice Number' => 'invoice_number',
        'Due Date' => 'due_date',
        'Payment Status' => 'payment_status',
        'Payment Method' => 'payment_method',
        'Transaction Code' => 'transaction_code',
        'Payment Date' => 'payment_date',
        'Credit Card' => 'credit_card',
        'Bank Transfer' => 'bank_transfer',
        'Cash' => 'cash',
        'Other' => 'other',
        
        // More actions and labels
        'All Statuses' => 'all_statuses',
        'All Courses' => 'all_courses',
        'All Types' => 'all_types',
        'All Instructors' => 'all_instructors',
        'All Roles' => 'all_roles',
        'All Payment Statuses' => 'all_payment_statuses',
        'All Payment Methods' => 'all_payment_methods',
        'Enrollment' => 'enrollment',
        'Type' => 'type',
        'Location' => 'location',
        'Room' => 'room',
        'Link' => 'link',
        'Invoices' => 'invoices',
        'Total' => 'total',
        'Growth' => 'growth',
        'This Month' => 'this_month',
        
        // Messages
        'Settings saved successfully!' => 'settings_saved',
        'Are you sure you want to delete this item?' => 'confirm_delete',
        'An error occurred. Please try again.' => 'error_occurred',
        'No data found' => 'no_data_found',
    );
    
    if (isset($key_map[$text])) {
        return $i18n->__($key_map[$text], $text);
    }
    
    return $translated;
}
