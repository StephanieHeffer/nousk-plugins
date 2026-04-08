<?php
defined('ABSPATH') || exit;

class Nousk_Ad_Shortcode {

	public function __construct() {
		add_shortcode('nousk_ads', array($this, 'render_ad'));
	}

	public function render_ad() {
		if (!is_singular()) {
			return '';
		}

		global $post;

		if (!$post || empty($post->ID)) {
			return '';
		}

		$mode = get_post_meta($post->ID, '_nousk_ad_mode', true);

		// Só funciona no modo manual
		if ($mode !== 'manual') {
			return '';
		}

		$ad_code = Nousk_Ad_Helpers::get_ad_code();

		if (empty($ad_code)) {
			return '';
		}

		return $ad_code;
	}
}
