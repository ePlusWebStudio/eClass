<?php
/**
 * Uninstall eClass Plugin
 * 
 * This file runs when the plugin is uninstalled (deleted).
 * It removes all plugin data from the database.
 */

// Exit if accessed directly or not uninstalling
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load database class
require_once plugin_dir_path(__FILE__) . 'includes/class-eclass-database.php';

// Drop all tables
EClass_Database::drop_tables();

// Delete plugin options
delete_option('eclass_version');
delete_option('eclass_currency_symbol');

// Clear any cached data
wp_cache_flush();
