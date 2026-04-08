<?php
defined('ABSPATH') || exit;

class NSK_FAQ_Metabox {

	const META_KEY = '_nsk_faq_enabled';

	public function __construct() {
		add_action('add_meta_boxes', array($this, 'register_metabox'));
		add_action('save_post', array($this, 'save_metabox'));
	}

	public function register_metabox() {
		$post_types = array('post', 'page');

		foreach ($post_types as $post_type) {
			add_meta_box(
				'nsk-faq-metabox',
				'NSK FAQ',
				array($this, 'render_metabox'),
				$post_type,
				'side',
				'high'
			);
		}
	}

	public function render_metabox($post) {
		wp_nonce_field('nsk_faq_metabox_action', 'nsk_faq_metabox_nonce');

		$enabled = get_post_meta($post->ID, self::META_KEY, true);
		?>
		<p>
			<label for="nsk_faq_enabled">
				<input
					type="checkbox"
					name="nsk_faq_enabled"
					id="nsk_faq_enabled"
					value="1"
					<?php checked($enabled, '1'); ?>
				/>
				Ativar FAQ neste conteúdo
			</label>
		</p>

		<div style="margin-top: 12px; padding: 10px; background: #f6f7f7; border: 1px solid #dcdcde;">
			<p style="margin-top: 0;"><strong>Marcações obrigatórias:</strong></p>
			<pre style="white-space: pre-wrap; margin: 0;">nsk-faq-inicio:
nsk-perg:
nsk-resp:
nsk-faq-fim:</pre>
		</div>

		<p style="margin-top: 12px; font-size: 12px; color: #50575e;">
			Use essas marcações no conteúdo somente quando este post/page for um FAQ.
		</p>
		<?php
	}

	public function save_metabox($post_id) {
		if (!isset($_POST['nsk_faq_metabox_nonce'])) {
			return;
		}

		if (!wp_verify_nonce($_POST['nsk_faq_metabox_nonce'], 'nsk_faq_metabox_action')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$value = isset($_POST['nsk_faq_enabled']) ? '1' : '0';
		update_post_meta($post_id, self::META_KEY, $value);
	}
}
