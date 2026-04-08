<?php
/**
 * Plugin Name: Nousk FAQ
 * Plugin URI:  https://nousk.com.br
 * Description: Plugin para transformar blocos marcados no conteúdo em FAQs expansíveis.
 * Version: 1.0.0
 * Author: Nousk
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

if (!defined('NSK_FAQ_PLUGIN_FILE')) {
	define('NSK_FAQ_PLUGIN_FILE', __FILE__);
}

if (!defined('NSK_FAQ_PLUGIN_PATH')) {
	define('NSK_FAQ_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

if (!defined('NSK_FAQ_PLUGIN_URL')) {
	define('NSK_FAQ_PLUGIN_URL', plugin_dir_url(__FILE__));
}

require_once NSK_FAQ_PLUGIN_PATH . 'includes/class-nsk-faq-admin.php';
require_once NSK_FAQ_PLUGIN_PATH . 'includes/class-nsk-faq-metabox.php';
require_once NSK_FAQ_PLUGIN_PATH . 'includes/class-nsk-faq-helpers.php';
require_once NSK_FAQ_PLUGIN_PATH . 'includes/class-nsk-faq-validator.php';
require_once NSK_FAQ_PLUGIN_PATH . 'includes/class-nsk-faq-parser.php';

final class NSK_FAQ_Plugin {

	public function __construct() {
		add_action('plugins_loaded', array($this, 'init'));
	}

	public function init() {
		new NSK_FAQ_Admin();
		new NSK_FAQ_Metabox();
		new NSK_FAQ_Validator();
		new NSK_FAQ_Parser();
	}
}

new NSK_FAQ_Plugin();
