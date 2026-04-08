<?php
/**
 * Plugin Name: Nousk Ad
 * Plugin URI:  https://nousk.com.br
 * Description: Plugin para inserção de anúncios em posts e páginas nos modos sem anúncios, automático e manual.
 * Version: 1.0.0
 * Author: Nousk
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

if (!defined('NOUSK_AD_PLUGIN_FILE')) {
	define('NOUSK_AD_PLUGIN_FILE', __FILE__);
}

if (!defined('NOUSK_AD_PLUGIN_PATH')) {
	define('NOUSK_AD_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

if (!defined('NOUSK_AD_PLUGIN_URL')) {
	define('NOUSK_AD_PLUGIN_URL', plugin_dir_url(__FILE__));
}

require_once NOUSK_AD_PLUGIN_PATH . 'includes/class-nousk-ad-helpers.php';
require_once NOUSK_AD_PLUGIN_PATH . 'includes/class-nousk-ad-admin.php';
require_once NOUSK_AD_PLUGIN_PATH . 'includes/class-nousk-ad-metabox.php';
require_once NOUSK_AD_PLUGIN_PATH . 'includes/class-nousk-ad-shortcode.php';
require_once NOUSK_AD_PLUGIN_PATH . 'includes/class-nousk-ad-parser.php';
require_once NOUSK_AD_PLUGIN_PATH . 'includes/class-nousk-ad-validator.php';

final class Nousk_Ad_Plugin {

	public function __construct() {
		add_action('plugins_loaded', array($this, 'init'));
	}

	public function init() {
		new Nousk_Ad_Admin();
		new Nousk_Ad_Metabox();
		new Nousk_Ad_Shortcode();
		new Nousk_Ad_Parser();
		new Nousk_Ad_Validator();
	}
}

new Nousk_Ad_Plugin();
