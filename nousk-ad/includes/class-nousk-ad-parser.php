<?php
defined('ABSPATH') || exit;

class Nousk_Ad_Parser {

	public function __construct() {
		add_filter('the_content', array($this, 'process_content'), 20);
	}

	public function process_content($content) {
		if (!is_singular()) {
			return $content;
		}

		global $post;

		if (!$post || empty($post->ID)) {
			return $content;
		}

		$mode = get_post_meta($post->ID, '_nousk_ad_mode', true);

		if ($mode !== 'auto') {
			return $content;
		}

		$interval = intval(get_post_meta($post->ID, '_nousk_ad_interval', true));

		if ($interval < 1) {
			return $content;
		}

		$ad_code = Nousk_Ad_Helpers::get_ad_code();

		if (empty($ad_code)) {
			return $content;
		}

		return $this->insert_ads($content, $interval, $ad_code);
	}

	private function insert_ads($content, $interval, $ad_code) {

		$parts = explode('</p>', $content);

		$result = '';
		$valid_paragraph_count = 0;
		$last_was_ad = false;

		foreach ($parts as $part) {

			if (trim($part) === '') {
				continue;
			}

			$paragraph = $part . '</p>';

			// verifica se tem conteúdo real
			if ($this->has_meaningful_content($paragraph)) {
				$valid_paragraph_count++;
			}

			$result .= $paragraph;

			if (
				$valid_paragraph_count > 0 &&
				$valid_paragraph_count % $interval === 0 &&
				!$last_was_ad
			) {
				$result .= $ad_code;
				$last_was_ad = true;
			} else {
				$last_was_ad = false;
			}
		}

		// garante anúncio no final
		if (!$last_was_ad) {
			$result .= $ad_code;
		}

		return $result;
	}

	private function has_meaningful_content($content) {
		$text = wp_strip_all_tags($content);

		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$text = str_replace("\xc2\xa0", ' ', $text);
		$text = preg_replace('/\s+/u', '', $text);

		return $text !== '';
	}
}
