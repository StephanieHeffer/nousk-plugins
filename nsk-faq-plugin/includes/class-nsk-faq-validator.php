<?php
defined('ABSPATH') || exit;

class NSK_FAQ_Validator {

	const ERROR_QUERY_ARG = 'nsk_faq_error';
	const ERROR_TRANSIENT_PREFIX = 'nsk_faq_error_';

	public function __construct() {
		add_filter('wp_insert_post_data', array($this, 'validate_before_insert'), 10, 2);
		add_action('admin_notices', array($this, 'display_admin_notice'));
	}

	public function validate_before_insert($data, $postarr) {
		if (empty($postarr['ID'])) {
			return $data;
		}

		$post_id = (int) $postarr['ID'];

		if (!$this->should_validate($post_id, $data, $postarr)) {
			return $data;
		}

		$content = isset($data['post_content']) ? $data['post_content'] : '';

		$validation = $this->validate_content($content);

		if ($validation['valid']) {
			delete_transient($this->get_error_transient_key($post_id));
			return $data;
		}

		set_transient(
			$this->get_error_transient_key($post_id),
			$validation['message'],
			60
		);

		$data['post_status'] = 'draft';

		add_filter('redirect_post_location', function ($location) {
			return add_query_arg(
				array(
					self::ERROR_QUERY_ARG => 1,
				),
				$location
			);
		});

		return $data;
	}

	private function should_validate($post_id, $data, $postarr) {
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return false;
		}

		if (wp_is_post_revision($post_id)) {
			return false;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return false;
		}

		$post_type = isset($data['post_type']) ? $data['post_type'] : '';
		if (!in_array($post_type, array('post', 'page'), true)) {
			return false;
		}

		if (!isset($_POST['nsk_faq_metabox_nonce'])) {
			return false;
		}

		if (!wp_verify_nonce($_POST['nsk_faq_metabox_nonce'], 'nsk_faq_metabox_action')) {
			return false;
		}

		$is_enabled = isset($_POST['nsk_faq_enabled']) && $_POST['nsk_faq_enabled'] === '1';

		return $is_enabled;
	}

	private function validate_content($content) {
		$start_marker = 'nsk-faq-inicio:';
		$question_marker = 'nsk-perg:';
		$response_marker = 'nsk-resp:';
		$end_marker = 'nsk-faq-fim:';

		$start_count = substr_count($content, $start_marker);
		$end_count   = substr_count($content, $end_marker);

		if ($start_count === 0 && $end_count === 0) {
			return $this->error('FAQ inválido: não foi encontrado nenhum bloco FAQ no conteúdo.');
		}

		if ($start_count !== $end_count) {
			return $this->error('FAQ inválido: a quantidade de marcações nsk-faq-inicio: e nsk-faq-fim: não corresponde.');
		}

		$offset = 0;
		$found_any_block = false;

		while (($start_pos = strpos($content, $start_marker, $offset)) !== false) {
			$found_any_block = true;

			$block_start = $start_pos + strlen($start_marker);
			$end_pos = strpos($content, $end_marker, $block_start);

			if ($end_pos === false) {
				return $this->error('FAQ inválido: existe um bloco FAQ sem a marcação final nsk-faq-fim:.');
			}

			$block_content = substr($content, $block_start, $end_pos - $block_start);

			$nested_start = strpos($block_content, $start_marker);
			if ($nested_start !== false) {
				return $this->error('FAQ inválido: blocos FAQ não podem ser aninhados.');
			}

			$block_validation = $this->validate_single_block(
				$block_content,
				$question_marker,
				$response_marker
			);

			if (!$block_validation['valid']) {
				return $block_validation;
			}

			$offset = $end_pos + strlen($end_marker);
		}

		if (!$found_any_block) {
			return $this->error('FAQ inválido: não foi possível localizar um bloco FAQ válido.');
		}

		return array(
			'valid'   => true,
			'message' => '',
		);
	}

private function validate_single_block($block_content, $question_marker, $response_marker) {
	$trimmed_block = trim(wp_strip_all_tags($block_content));

	if ($trimmed_block === '') {
		return $this->error('FAQ inválido: existe um bloco FAQ vazio.');
	}

	$first_question_pos = strpos($block_content, $question_marker);
	$first_response_pos = strpos($block_content, $response_marker);

	if ($first_question_pos === false) {
		return $this->error('FAQ inválido: o bloco FAQ não possui nenhuma marcação nsk-perg:.');
	}

	if ($first_response_pos === false) {
		return $this->error('FAQ inválido: o bloco FAQ não possui nenhuma marcação nsk-resp:.');
	}

	if ($first_response_pos < $first_question_pos) {
		return $this->error('FAQ inválido: a primeira marcação válida dentro do bloco FAQ deve ser nsk-perg:.');
	}

	$before_first_question = substr($block_content, 0, $first_question_pos);
	if ($this->has_meaningful_content($before_first_question)) {
		return $this->error('FAQ inválido: existe conteúdo solto dentro do bloco FAQ antes da primeira marcação nsk-perg:.');
	}

	preg_match_all('/nsk\-(perg|resp)\:/', $block_content, $matches, PREG_OFFSET_CAPTURE);

	if (empty($matches[0])) {
		return $this->error('FAQ inválido: nenhuma marcação de pergunta ou resposta foi encontrada dentro do bloco.');
	}

	$markers = array();

	foreach ($matches[1] as $index => $match) {
		$markers[] = array(
			'type' => $match[0],
			'position' => $matches[0][$index][1],
			'full_marker' => $matches[0][$index][0],
		);
	}

	$expected = 'perg';
	$question_count = 0;
	$response_count = 0;

	foreach ($markers as $index => $marker) {
		$type = $marker['type'];

		if ($type !== $expected) {
			if ($type === 'resp') {
				return $this->error('FAQ inválido: existe uma resposta sem pergunta correspondente ou fora de ordem.');
			}

			return $this->error('FAQ inválido: existe uma pergunta sem resposta anterior.');
		}

		$current_marker_length = strlen($marker['full_marker']);
		$content_start = $marker['position'] + $current_marker_length;

		if (isset($markers[$index + 1])) {
			$content_end = $markers[$index + 1]['position'];
		} else {
			$content_end = strlen($block_content);
		}

		$segment_content = substr($block_content, $content_start, $content_end - $content_start);

		if (!$this->has_meaningful_content($segment_content)) {
			if ($type === 'perg') {
				return $this->error('FAQ inválido: existe uma pergunta vazia dentro do bloco FAQ.');
			}

			return $this->error('FAQ inválido: existe uma resposta vazia dentro do bloco FAQ.');
		}

		if ($type === 'perg') {
			$question_count++;
			$expected = 'resp';
		} else {
			$response_count++;
			$expected = 'perg';
		}
	}

	if ($question_count === 0 || $response_count === 0) {
		return $this->error('FAQ inválido: o bloco precisa ter pelo menos uma pergunta e uma resposta.');
	}

	if ($question_count !== $response_count) {
		return $this->error('FAQ inválido: cada pergunta deve ter uma resposta correspondente.');
	}

	if ($expected === 'resp') {
		return $this->error('FAQ inválido: existe uma pergunta sem resposta ao final do bloco.');
	}

	return array(
		'valid'   => true,
		'message' => '',
	);
}
	private function has_meaningful_content($content) {
	$text = wp_strip_all_tags($content);

	$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$text = str_replace("\xc2\xa0", ' ', $text);
	$text = preg_replace('/\s+/u', '', $text);

	return $text !== '';
}

	private function error($message) {
		return array(
			'valid'   => false,
			'message' => $message,
		);
	}

	private function get_error_transient_key($post_id) {
		return self::ERROR_TRANSIENT_PREFIX . $post_id;
	}

	public function display_admin_notice() {
		if (!isset($_GET[self::ERROR_QUERY_ARG])) {
			return;
		}

		$post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
		if (!$post_id) {
			return;
		}

		$message = get_transient($this->get_error_transient_key($post_id));

		if (!$message) {
			return;
		}

		delete_transient($this->get_error_transient_key($post_id));
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo esc_html($message); ?></p>
		</div>
		<?php
	}
}
