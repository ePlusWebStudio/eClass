<?php
/**
 * Pagination System Class
 * نظام التصفح للجداول الكبيرة
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Pagination {

    private $per_page;
    private $current_page;
    private $total_items;
    private $total_pages;
    private $base_url;

    public function __construct($per_page = 20, $current_page = 1) {
        $this->per_page = intval($per_page);
        $this->current_page = max(1, intval($current_page));
        $this->total_items = 0;
        $this->total_pages = 0;
        $this->base_url = '';
    }

    /**
     * تعيين إجمالي العناصر
     */
    public function set_total_items($total) {
        $this->total_items = intval($total);
        $this->total_pages = ceil($this->total_items / $this->per_page);
        $this->current_page = min($this->current_page, $this->total_pages);
    }

    /**
     * تعيين الرابط الأساسي
     */
    public function set_base_url($url) {
        $this->base_url = $url;
    }

    /**
     * الحصول على offset للاستعلام
     */
    public function get_offset() {
        return ($this->current_page - 1) * $this->per_page;
    }

    /**
     * الحصول على limit للاستعلام
     */
    public function get_limit() {
        return $this->per_page;
    }

    /**
     * الحصول على الصفحة الحالية
     */
    public function get_current_page() {
        return $this->current_page;
    }

    /**
     * الحصول على إجمالي الصفحات
     */
    public function get_total_pages() {
        return $this->total_pages;
    }

    /**
     * الحصول على إجمالي العناصر
     */
    public function get_total_items() {
        return $this->total_items;
    }

    /**
     * التحقق من وجود صفحات سابقة
     */
    public function has_previous() {
        return $this->current_page > 1;
    }

    /**
     * التحقق من وجود صفحات تالية
     */
    public function has_next() {
        return $this->current_page < $this->total_pages;
    }

    /**
     * الحصول على رابط الصفحة السابقة
     */
    public function get_previous_url() {
        if (!$this->has_previous()) {
            return false;
        }
        return add_query_arg('paged', $this->current_page - 1, $this->base_url);
    }

    /**
     * الحصول على رابط الصفحة التالية
     */
    public function get_next_url() {
        if (!$this->has_next()) {
            return false;
        }
        return add_query_arg('paged', $this->current_page + 1, $this->base_url);
    }

    /**
     * الحصول على رابط صفحة محددة
     */
    public function get_page_url($page) {
        $page = intval($page);
        if ($page < 1 || $page > $this->total_pages) {
            return false;
        }
        return add_query_arg('paged', $page, $this->base_url);
    }

    /**
     * عرض عناصر التحكم في التصفح مع تصميم محسن
     */
    public function render_pagination() {
        if ($this->total_pages <= 1) {
            return '';
        }

        $output = '<div class="eclass-pagination" role="navigation" aria-label="' . __('Pagination Navigation', 'eclass') . '">';
        $output .= '<div class="eclass-pagination-info">';
        $output .= sprintf(
            __('Showing %d-%d of %d items', 'eclass'),
            $this->get_offset() + 1,
            min($this->get_offset() + $this->per_page, $this->total_items),
            $this->total_items
        );
        $output .= '</div>';

        $output .= '<div class="eclass-pagination-controls">';

        // Previous button
        if ($this->has_previous()) {
            $output .= '<a href="' . esc_url($this->get_previous_url()) . '" class="eclass-btn eclass-btn-secondary eclass-btn-sm" aria-label="' . __('Go to previous page', 'eclass') . '" title="' . __('Previous Page', 'eclass') . '">';
            $output .= '<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>';
            $output .= '<span class="eclass-pagination-label">' . __('Previous', 'eclass') . '</span>';
            $output .= '</a>';
        } else {
            $output .= '<span class="eclass-btn eclass-btn-secondary eclass-btn-sm eclass-btn-disabled" aria-disabled="true" title="' . __('No previous page available', 'eclass') . '">';
            $output .= '<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>';
            $output .= '<span class="eclass-pagination-label">' . __('Previous', 'eclass') . '</span>';
            $output .= '</span>';
        }

        // Page numbers
        $start_page = max(1, $this->current_page - 2);
        $end_page = min($this->total_pages, $this->current_page + 2);

        // First page
        if ($start_page > 1) {
            $output .= '<a href="' . esc_url($this->get_page_url(1)) . '" class="eclass-btn eclass-btn-link eclass-btn-sm" aria-label="' . sprintf(__('Go to page %d', 'eclass'), 1) . '" title="' . __('First Page', 'eclass') . '">1</a>';
            if ($start_page > 2) {
                $output .= '<span class="eclass-pagination-dots" aria-hidden="true">…</span>';
            }
        }

        // Page range
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $this->current_page) {
                $output .= '<span class="eclass-btn eclass-btn-primary eclass-btn-sm eclass-btn-active" aria-current="page" title="' . sprintf(__('Current page, page %d', 'eclass'), $i) . '">' . $i . '</span>';
            } else {
                $output .= '<a href="' . esc_url($this->get_page_url($i)) . '" class="eclass-btn eclass-btn-link eclass-btn-sm" aria-label="' . sprintf(__('Go to page %d', 'eclass'), $i) . '" title="' . sprintf(__('Page %d', 'eclass'), $i) . '">' . $i . '</a>';
            }
        }

        // Last page
        if ($end_page < $this->total_pages) {
            if ($end_page < $this->total_pages - 1) {
                $output .= '<span class="eclass-pagination-dots" aria-hidden="true">…</span>';
            }
            $output .= '<a href="' . esc_url($this->get_page_url($this->total_pages)) . '" class="eclass-btn eclass-btn-link eclass-btn-sm" aria-label="' . sprintf(__('Go to page %d', 'eclass'), $this->total_pages) . '" title="' . __('Last Page', 'eclass') . '">' . $this->total_pages . '</a>';
        }

        // Next button
        if ($this->has_next()) {
            $output .= '<a href="' . esc_url($this->get_next_url()) . '" class="eclass-btn eclass-btn-secondary eclass-btn-sm" aria-label="' . __('Go to next page', 'eclass') . '" title="' . __('Next Page', 'eclass') . '">';
            $output .= '<span class="eclass-pagination-label">' . __('Next', 'eclass') . '</span>';
            $output .= '<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>';
            $output .= '</a>';
        } else {
            $output .= '<span class="eclass-btn eclass-btn-secondary eclass-btn-sm eclass-btn-disabled" aria-disabled="true" title="' . __('No next page available', 'eclass') . '">';
            $output .= '<span class="eclass-pagination-label">' . __('Next', 'eclass') . '</span>';
            $output .= '<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>';
            $output .= '</span>';
        }

        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * إنشاء استعلام SQL مع pagination
     */
    public function get_sql_limit() {
        return $GLOBALS['wpdb']->prepare("LIMIT %d OFFSET %d", $this->per_page, $this->get_offset());
    }

    /**
     * الحصول على معلومات التصفح كمصفوفة
     */
    public function get_pagination_info() {
        return array(
            'current_page' => $this->current_page,
            'total_pages' => $this->total_pages,
            'total_items' => $this->total_items,
            'per_page' => $this->per_page,
            'has_previous' => $this->has_previous(),
            'has_next' => $this->has_next(),
            'previous_url' => $this->get_previous_url(),
            'next_url' => $this->get_next_url(),
            'showing_start' => $this->get_offset() + 1,
            'showing_end' => min($this->get_offset() + $this->per_page, $this->total_items)
        );
    }
}