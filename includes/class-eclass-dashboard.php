<?php
/**
 * Dashboard Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Dashboard {
    
    /**
     * Render dashboard
     */
    public function render() {
        global $wpdb;
        
        // Get statistics
        $stats = $this->get_statistics();
        $recent_students = $this->get_recent_students();
        $recent_payments = $this->get_recent_payments();
        
        ?>
        <div class="eclass-wrap">
            <div class="eclass-header">
                <h1><?php _e('Dashboard', 'eclass'); ?></h1>
            </div>
            
            <div class="eclass-dashboard">
                <!-- KPI Cards -->
                <div class="eclass-kpi-grid">
                    <div class="eclass-kpi-card">
                        <div class="eclass-kpi-icon" style="background: #4CAF50;">
                            <span class="dashicons dashicons-groups"></span>
                        </div>
                        <div class="eclass-kpi-content">
                            <h3><?php echo number_format($stats['total_students']); ?></h3>
                            <p><?php _e('Total Students', 'eclass'); ?></p>
                            <?php if ($stats['students_growth'] != 0): ?>
                                <span class="eclass-kpi-growth <?php echo $stats['students_growth'] > 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo $stats['students_growth'] > 0 ? '+' : ''; ?><?php echo $stats['students_growth']; ?>% <?php _e('this month', 'eclass'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="eclass-kpi-card">
                        <div class="eclass-kpi-icon" style="background: #2196F3;">
                            <span class="dashicons dashicons-book"></span>
                        </div>
                        <div class="eclass-kpi-content">
                            <h3><?php echo number_format($stats['active_courses']); ?></h3>
                            <p><?php _e('Active Courses', 'eclass'); ?></p>
                            <span class="eclass-kpi-detail">
                                <?php echo number_format($stats['total_courses']); ?> <?php _e('total courses', 'eclass'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="eclass-kpi-card">
                        <div class="eclass-kpi-icon" style="background: #FF9800;">
                            <span class="dashicons dashicons-money-alt"></span>
                        </div>
                        <div class="eclass-kpi-content">
                            <h3><?php echo get_option('eclass_currency_symbol', '$'); ?><?php echo number_format($stats['total_revenue'], 2); ?></h3>
                            <p><?php _e('Total Revenue', 'eclass'); ?></p>
                            <?php if ($stats['revenue_growth'] != 0): ?>
                                <span class="eclass-kpi-growth <?php echo $stats['revenue_growth'] > 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo $stats['revenue_growth'] > 0 ? '+' : ''; ?><?php echo $stats['revenue_growth']; ?>% <?php _e('this month', 'eclass'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="eclass-kpi-card">
                        <div class="eclass-kpi-icon" style="background: #9C27B0;">
                            <span class="dashicons dashicons-businessperson"></span>
                        </div>
                        <div class="eclass-kpi-content">
                            <h3><?php echo number_format($stats['total_instructors']); ?></h3>
                            <p><?php _e('Team Members', 'eclass'); ?></p>
                            <span class="eclass-kpi-detail">
                                <?php echo number_format($stats['instructors_only']); ?> <?php _e('instructors', 'eclass'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="eclass-dashboard-grid">
                    <!-- Recent Students -->
                    <div class="eclass-dashboard-card">
                        <div class="eclass-card-header">
                            <h2><?php _e('Recent Enrollments', 'eclass'); ?></h2>
                            <a href="<?php echo admin_url('admin.php?page=eclass-students'); ?>" class="eclass-btn-link">
                                <?php _e('View All', 'eclass'); ?>
                            </a>
                        </div>
                        <div class="eclass-card-body">
                            <?php if (!empty($recent_students)): ?>
                                <table class="eclass-table">
                                    <thead>
                                        <tr>
                                            <th><?php _e('Student', 'eclass'); ?></th>
                                            <th><?php _e('Course', 'eclass'); ?></th>
                                            <th><?php _e('Date', 'eclass'); ?></th>
                                            <th><?php _e('Status', 'eclass'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_students as $student): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo esc_html($student->name); ?></strong><br>
                                                    <small><?php echo esc_html($student->email); ?></small>
                                                </td>
                                                <td><?php echo esc_html($student->course_name ?: __('N/A', 'eclass')); ?></td>
                                                <td><?php echo date_i18n(get_option('date_format'), strtotime($student->enrollment_date)); ?></td>
                                                <td>
                                                    <span class="eclass-badge eclass-badge-<?php echo esc_attr($student->enrollment_status); ?>">
                                                        <?php echo esc_html(ucfirst($student->enrollment_status)); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="eclass-no-data"><?php _e('No recent enrollments', 'eclass'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Recent Payments -->
                    <div class="eclass-dashboard-card">
                        <div class="eclass-card-header">
                            <h2><?php _e('Recent Payments', 'eclass'); ?></h2>
                            <a href="<?php echo admin_url('admin.php?page=eclass-billing'); ?>" class="eclass-btn-link">
                                <?php _e('View All', 'eclass'); ?>
                            </a>
                        </div>
                        <div class="eclass-card-body">
                            <?php if (!empty($recent_payments)): ?>
                                <table class="eclass-table">
                                    <thead>
                                        <tr>
                                            <th><?php _e('Invoice', 'eclass'); ?></th>
                                            <th><?php _e('Student', 'eclass'); ?></th>
                                            <th><?php _e('Amount', 'eclass'); ?></th>
                                            <th><?php _e('Status', 'eclass'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_payments as $payment): ?>
                                            <tr>
                                                <td><strong><?php echo esc_html($payment->invoice_number); ?></strong></td>
                                                <td><?php echo esc_html($payment->student_name); ?></td>
                                                <td><?php echo get_option('eclass_currency_symbol', '$'); ?><?php echo number_format($payment->amount, 2); ?></td>
                                                <td>
                                                    <span class="eclass-badge eclass-badge-<?php echo esc_attr($payment->payment_status); ?>">
                                                        <?php echo esc_html(ucfirst($payment->payment_status)); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="eclass-no-data"><?php _e('No recent payments', 'eclass'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php $this->render_footer(); ?>
        </div>
        <?php
    }
    
    /**
     * Get statistics with caching
     */
    private function get_statistics() {
        $cache = EClass_Cache::get_instance();
        return $cache->get_dashboard_stats();
    }
    
    /**
     * Get recent students with caching
     */
    private function get_recent_students() {
        $cache = EClass_Cache::get_instance();
        return $cache->get_recent_students(5);
    }

    /**
     * Get recent payments with caching
     */
    private function get_recent_payments() {
        $cache = EClass_Cache::get_instance();
        return $cache->get_recent_payments(5);
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
