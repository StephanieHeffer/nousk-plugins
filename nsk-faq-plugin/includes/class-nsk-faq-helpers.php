<?php
defined('ABSPATH') || exit;

class NSK_FAQ_Helpers {

	public static function is_faq_enabled($post_id) {
		return get_post_meta($post_id, '_nsk_faq_enabled', true) === '1';
	}
}
