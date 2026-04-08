<?php
defined('ABSPATH') || exit;

class Nousk_Ad_Metabox {

	const META_MODE = '_nousk_ad_mode';
	const META_INTERVAL = '_nousk_ad_interval';

	public function __construct() {
		add_action('add_meta_boxes', array($this, 'register_metabox'));
		add_action('save_post', array($this, 'save_metabox'));
	}

	public function register_metabox() {
		add_meta_box(
			'nousk_ad_metabox',
			'Nousk Ad',
			array($this, 'render_metabox'),
			array('post', 'page'),
			'side',
			'default'
		);
	}

	public function render_metabox($post) {
		wp_nonce_field('nousk_ad_metabox_nonce', 'nousk_ad_metabox_nonce');

		$mode = get_post_meta($post->ID, self::META_MODE, true);
		$interval = get_post_meta($post->ID, self::META_INTERVAL, true);

		$mode = $mode ? $mode : 'none';
		?>

		<p>
			<label for="nousk_ad_mode"><strong>Modo</strong></label>
		</p>

		<select name="nousk_ad_mode" id="nousk_ad_mode" style="width:100%;">
			<option value="none" <?php selected($mode, 'none'); ?>>Sem anúncios</option>
			<option value="auto" <?php selected($mode, 'auto'); ?>>Automático</option>
			<option value="manual" <?php selected($mode, 'manual'); ?>>Manual</option>
		</select>

		<div id="nousk_ad_interval_wrapper" style="margin-top:10px;">
			<p>
				<label for="nousk_ad_interval"><strong>Intervalo de parágrafos</strong></label>
			</p>
			<input 
				type="number" 
				name="nousk_ad_interval" 
				id="nousk_ad_interval" 
				value="<?php echo esc_attr($interval); ?>" 
				min="1"
				style="width:100%;"
			>
		</div>

		<div id="nousk_ad_manual_message" style="margin-top:10px;">
			<p style="margin:0;">
				Use o shortcode <code>[nousk_ads]</code> no conteúdo para inserir o anúncio.
			</p>
		</div>

		<script>
			document.addEventListener('DOMContentLoaded', function () {
				const modeSelect = document.getElementById('nousk_ad_mode');
				const intervalWrapper = document.getElementById('nousk_ad_interval_wrapper');
				const manualMessage = document.getElementById('nousk_ad_manual_message');

				function toggleFields() {
					const mode = modeSelect.value;

					if (mode === 'auto') {
						intervalWrapper.style.display = 'block';
						manualMessage.style.display = 'none';
					} else if (mode === 'manual') {
						intervalWrapper.style.display = 'none';
						manualMessage.style.display = 'block';
					} else {
						intervalWrapper.style.display = 'none';
						manualMessage.style.display = 'none';
					}
				}

				modeSelect.addEventListener('change', toggleFields);

				toggleFields();
			});
		</script>

		<?php
	}

	public function save_metabox($post_id) {
		if (!isset($_POST['nousk_ad_metabox_nonce'])) return;

		if (!wp_verify_nonce($_POST['nousk_ad_metabox_nonce'], 'nousk_ad_metabox_nonce')) return;

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

		if (!current_user_can('edit_post', $post_id)) return;

		$mode = isset($_POST['nousk_ad_mode']) ? sanitize_text_field($_POST['nousk_ad_mode']) : 'none';
		$interval = isset($_POST['nousk_ad_interval']) ? intval($_POST['nousk_ad_interval']) : '';

		update_post_meta($post_id, self::META_MODE, $mode);

		if ($mode === 'auto') {
			update_post_meta($post_id, self::META_INTERVAL, $interval);
		} else {
			delete_post_meta($post_id, self::META_INTERVAL);
		}
	}
}
