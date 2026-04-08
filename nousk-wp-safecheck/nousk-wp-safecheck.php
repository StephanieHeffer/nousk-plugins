<?php
/**
 * Plugin Name: Nousk WP SafeCheck
 * Plugin URI:  https://nousk.com.br
 * Description: Checklist de segurança para WordPress com feedback técnico acessível.
 * Version: 1.0.0
 * Author: Nousk
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

define('NOUSKSAFE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('NOUSKSAFE_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once NOUSKSAFE_PLUGIN_PATH . 'includes/security-checks.php';

/**
 * Adiciona menu no admin.
 */
function nousksafe_add_admin_menu() {
    add_menu_page(
        'Nousk WP SafeCheck',
        'Nousk SafeCheck',
        'manage_options',
        'nousk-wp-safecheck',
        'nousksafe_render_admin_page',
        'dashicons-shield',
        81
    );
}
add_action('admin_menu', 'nousksafe_add_admin_menu');

/**
 * Renderiza a página do plugin.
 */
function nousksafe_render_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $checks         = nousksafe_get_all_checks();
    $summary        = nousksafe_get_checks_summary($checks);
    $grouped_checks = nousksafe_group_checks_by_category($checks);

    require NOUSKSAFE_PLUGIN_PATH . 'templates/admin-page.php';
}
