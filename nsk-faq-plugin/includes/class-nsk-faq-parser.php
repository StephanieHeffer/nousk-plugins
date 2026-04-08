<?php
defined('ABSPATH') || exit;

class NSK_FAQ_Parser {

	public function __construct() {
		add_filter('the_content', array($this, 'replace_faq_blocks'), 20);
		add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
	}

	public function replace_faq_blocks($content) {
		if (!is_singular()) {
			return $content;
		}

		global $post;

		if (!$post || empty($post->ID)) {
			return $content;
		}

		if (!NSK_FAQ_Helpers::is_faq_enabled($post->ID)) {
			return $content;
		}

		$start_marker = 'nsk-faq-inicio:';
		$end_marker   = 'nsk-faq-fim:';

		$pattern = '/' . preg_quote($start_marker, '/') . '(.*?)' . preg_quote($end_marker, '/') . '/s';

		$content = preg_replace_callback($pattern, array($this, 'render_faq_block'), $content);

		return $content;
	}

	private function render_faq_block($matches) {
		$block_content = $matches[1];
		$items = $this->parse_faq_items($block_content);

		if (empty($items)) {
			return '';
		}

		static $faq_instance = 0;
		$faq_instance++;

		$output = '<div class="nsk-faq">';

		foreach ($items as $index => $item) {
			$item_number = $index + 1;
			$answer_id = 'nsk-faq-answer-' . $faq_instance . '-' . $item_number;

			$output .= '<div class="nsk-faq-item">';
			$output .= '<button 
				class="nsk-faq-question" 
				type="button"
				aria-expanded="false"
				aria-controls="' . esc_attr($answer_id) . '">'
				. wp_kses_post($item['question']) .
			'</button>';

			$output .= '<div 
				id="' . esc_attr($answer_id) . '" 
				class="nsk-faq-answer" 
				hidden>'
				. wp_kses_post(wpautop($item['answer'])) .
			'</div>';

			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	private function parse_faq_items($block_content) {
		$question_marker = 'nsk-perg:';
		$response_marker = 'nsk-resp:';

		preg_match_all('/nsk\-(perg|resp)\:/', $block_content, $matches, PREG_OFFSET_CAPTURE);

		if (empty($matches[0])) {
			return array();
		}

		$markers = array();

		foreach ($matches[1] as $index => $match) {
			$markers[] = array(
				'type' => $match[0],
				'position' => $matches[0][$index][1],
				'full_marker' => $matches[0][$index][0],
			);
		}

		$items = array();
		$current_item = array(
			'question' => '',
			'answer'   => '',
		);

		foreach ($markers as $index => $marker) {
			$type = $marker['type'];
			$current_marker_length = strlen($marker['full_marker']);
			$content_start = $marker['position'] + $current_marker_length;

			if (isset($markers[$index + 1])) {
				$content_end = $markers[$index + 1]['position'];
			} else {
				$content_end = strlen($block_content);
			}

			$segment_content = substr($block_content, $content_start, $content_end - $content_start);
			$segment_content = trim($segment_content);

			if ($type === 'perg') {
				$current_item['question'] = $segment_content;
			} elseif ($type === 'resp') {
				$current_item['answer'] = $segment_content;
				$items[] = $current_item;
				$current_item = array(
					'question' => '',
					'answer'   => '',
				);
			}
		}

		return $items;
	}
	
	public function enqueue_assets() {
		if (!is_singular()) {
			return;
		}

		global $post;

		if (!$post || !NSK_FAQ_Helpers::is_faq_enabled($post->ID)) {
			return;
		}

		wp_enqueue_style(
			'nsk-faq-style',
			NSK_FAQ_PLUGIN_URL . 'assets/css/faq.css',
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
			'nsk-faq-script',
			NSK_FAQ_PLUGIN_URL . 'assets/js/faq.js',
			array(),
			'1.0.0',
			true
		);
	}
}
