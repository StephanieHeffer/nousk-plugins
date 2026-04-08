<?php
defined('ABSPATH') || exit;

class Nousk_Ad_Helpers {

	public static function get_ad_code() {
		$ad_code = get_option('nousk_ad_code', '');

		return is_string($ad_code) ? $ad_code : '';
	}
}
