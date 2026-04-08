<?php
defined('ABSPATH') || exit;

class Nousk_Ad_Admin {

	const OPTION_KEY = 'nousk_ad_code';

	public function __construct() {
		add_action('admin_menu', array($this, 'register_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
	}

	public function register_admin_menu() {
		add_menu_page(
			'Nousk Ad',
			'Nousk Ad',
			'manage_options',
			'nousk-ad',
			array($this, 'render_settings_page'),
			'dashicons-megaphone',
			59
		);

		add_submenu_page(
			'nousk-ad',
			'Configuração',
			'Configuração',
			'manage_options',
			'nousk-ad',
			array($this, 'render_settings_page')
		);

		add_submenu_page(
			'nousk-ad',
			'Como usar',
			'Como usar',
			'manage_options',
			'nousk-ad-how-to-use',
			array($this, 'render_help_page')
		);
	}

	public function register_settings() {
		register_setting(
			'nousk_ad_settings_group',
			self::OPTION_KEY,
			array($this, 'sanitize_ad_code')
		);
	}

	public function sanitize_ad_code($value) {
		return is_string($value) ? $value : '';
	}

	public function render_settings_page() {
		$ad_code = get_option(self::OPTION_KEY, '');
		?>
		<div class="wrap">
			<h1>Nousk Ad</h1>

			<p>
				Esta é a área de configuração global do plugin.
				O código salvo aqui será usado nos modos manual e automático.
			</p>

			<p>
				Você pode usar HTML, CSS e scripts. Para testes, também pode colar um bloco visual simples.
			</p>

			<form method="post" action="options.php">
				<?php settings_fields('nousk_ad_settings_group'); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="nousk_ad_code">Código do anúncio</label>
						</th>
						<td>
							<textarea
								name="<?php echo esc_attr(self::OPTION_KEY); ?>"
								id="nousk_ad_code"
								rows="12"
								cols="80"
								class="large-text code"
							><?php echo esc_textarea($ad_code); ?></textarea>

							<p class="description">
								Este código será replicado exatamente como salvo quando o anúncio for inserido no conteúdo.
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button('Salvar configuração'); ?>
			</form>

			<hr>

			<h2>Exemplo visual para testes</h2>
			<p>Se você ainda não tiver um script real, pode usar este exemplo:</p>

			<pre><code>&lt;div style="padding:20px; background:#f5f5f5; border:1px solid #dcdcdc; text-align:center; font-weight:bold;"&gt;
	Anúncio de teste
&lt;/div&gt;</code></pre>
		</div>
		<?php
	}

	public function render_help_page() {
		?>
		<div class="wrap">
			<h1>Como usar o Nousk Ad</h1>

			<p>
				O plugin permite inserir anúncios em posts e páginas com três modos de funcionamento:
				sem anúncios, automático e manual.
			</p>

			<h2>Modos disponíveis</h2>
			<ul>
				<li><strong>Sem anúncios:</strong> o conteúdo é exibido sem alterações.</li>
				<li><strong>Automático:</strong> o plugin insere anúncios a cada X parágrafos com conteúdo.</li>
				<li><strong>Manual:</strong> o editor escolhe onde o anúncio será exibido usando o shortcode <code>[nousk_ads]</code>.</li>
			</ul>

			<h2>Modo manual</h2>
			<p>
				No modo manual, use o shortcode abaixo no conteúdo:
			</p>
			<pre>[nousk_ads]</pre>

			<h2>Modo automático</h2>
			<ul>
				<li>Conta apenas parágrafos <code>&lt;p&gt;</code> com conteúdo real.</li>
				<li>Parágrafos vazios não entram na contagem.</li>
				<li>O anúncio é inserido a cada intervalo definido.</li>
				<li>O plugin sempre garante um anúncio ao final do conteúdo.</li>
			</ul>

			<h2>Observações importantes</h2>
			<ul>
				<li>O código do anúncio é configurado globalmente na página Configuração.</li>
				<li>Na v1, o plugin usa apenas um único código de anúncio.</li>
				<li>O shortcode <code>[nousk_ads]</code> é fixo e não é customizável.</li>
			</ul>
		</div>
		<?php
	}
}

