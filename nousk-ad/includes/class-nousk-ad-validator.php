<?php
defined('ABSPATH') || exit;

class Nousk_Ad_Validator {

	const ERROR_QUERY_ARG = 'nousk_ad_error';
	const WARNING_QUERY_ARG = 'nousk_ad_warning';

	const ERROR_TRANSIENT_PREFIX = 'nousk_ad_error_';
	const WARNING_TRANSIENT_PREFIX = 'nousk_ad_warning_';

	public function __construct() {
		add_filter('wp_insert_post_data', array($this, 'validate_before_insert'), 10, 2);
		add_action('admin_notices', array($this, 'display_admin_notices'));
	}

	public function validate_before_insert($data, $postarr) {
		if (empty($postarr['ID'])) {
			return $data;
		}

		$post_id = (int) $postarr['ID'];

		if (!$this->should_validate($post_id, $data)) {
			return $data;
		}

		$mode = isset($_POST['nousk_ad_mode']) ? sanitize_text_field($_POST['nousk_ad_mode']) : 'none';

		delete_transient($this->get_error_transient_key($post_id));
		delete_transient($this->get_warning_transient_key($post_id));

		if ($mode !== 'auto') {
			return $data;
		}

		$interval_raw = isset($_POST['nousk_ad_interval']) ? $_POST['nousk_ad_interval'] : '';
		$interval = intval($interval_raw);
		$content = isset($data['post_content']) ? $data['post_content'] : '';

		// Erro: intervalo vazio
		if ($interval_raw === '' || $interval_raw === null) {
			set_transient(
				$this->get_error_transient_key($post_id),
				'Nousk Ad inválido: informe o intervalo de parágrafos no modo automático.',
				60
			);

			$data['post_status'] = 'draft';
			$this->add_redirect_flag(self::ERROR_QUERY_ARG);

			return $data;
		}

		// Erro: intervalo menor que 1
		if ($interval < 1) {
			set_transient(
				$this->get_error_transient_key($post_id),
				'Nousk Ad inválido: o intervalo de parágrafos deve ser maior que zero no modo automático.',
				60
			);

			$data['post_status'] = 'draft';
			$this->add_redirect_flag(self::ERROR_QUERY_ARG);

			return $data;
		}

		$paragraph_count = $this->count_valid_paragraphs($content);

		// Aviso: intervalo maior que a quantidade de parágrafos válidos
		if ($paragraph_count > 0 && $interval > $paragraph_count) {
			set_transient(
				$this->get_warning_transient_key($post_id),
				sprintf(
					'Nousk Ad: o intervalo definido (%d) é maior que a quantidade de parágrafos válidos do conteúdo (%d). Nenhuma inserção intermediária será feita e o anúncio aparecerá apenas ao final.',
					$interval,
					$paragraph_count
				),
				60
			);

			$this->add_redirect_flag(self::WARNING_QUERY_ARG);
		}

		return $data;
	}

	private function should_validate($post_id, $data) {
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

		if (!isset($_POST['nousk_ad_metabox_nonce'])) {
			return false;
		}

		if (!wp_verify_nonce($_POST['nousk_ad_metabox_nonce'], 'nousk_ad_metabox_nonce')) {
			return false;
		}

		return true;
	}

	private function count_valid_paragraphs($content) {
		// Garante parágrafos mesmo se o conteúdo bruto ainda não tiver <p>
		$content = wpautop($content);

		$parts = explode('</p>', $content);
		$count = 0;

		foreach ($parts as $part) {
			if (trim($part) === '') {
				continue;
			}

			$paragraph = $part . '</p>';

			if ($this->has_meaningful_content($paragraph)) {
				$count++;
			}
		}

		return $count;
	}

	private function has_meaningful_content($content) {
		$text = wp_strip_all_tags($content);

		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$text = str_replace("\xc2\xa0", ' ', $text);
		$text = preg_replace('/\s+/u', '', $text);

		return $text !== '';
	}

	private function add_redirect_flag($query_arg) {
		add_filter('redirect_post_location', function ($location) use ($query_arg) {
			return add_query_arg(
				array(
					$query_arg => 1,
				),
				$location
			);
		});
	}

	private function get_error_transient_key($post_id) {
		return self::ERROR_TRANSIENT_PREFIX . $post_id;
	}

	private function get_warning_transient_key($post_id) {
		return self::WARNING_TRANSIENT_PREFIX . $post_id;
	}

	public function display_admin_notices() {
		$post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;

		if (!$post_id) {
			return;
		}

		if (isset($_GET[self::ERROR_QUERY_ARG])) {
			$message = get_transient($this->get_error_transient_key($post_id));

			if ($message) {
				delete_transient($this->get_error_transient_key($post_id));
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html($message); ?></p>
				</div>
				<?php
			}
		}

		if (isset($_GET[self::WARNING_QUERY_ARG])) {
			$message = get_transient($this->get_warning_transient_key($post_id));

			if ($message) {
				delete_transient($this->get_warning_transient_key($post_id));
				?>
				<div class="notice notice-warning is-dismissible">
					<p><?php echo esc_html($message); ?></p>
				</div>
				<?php
			}
		}
	}
}
