<?php
defined('ABSPATH') || exit;

class NSK_FAQ_Admin {

	public function __construct() {
		add_action('admin_menu', array($this, 'register_admin_menu'));
	}

	public function register_admin_menu() {
		add_menu_page(
			'NSK FAQ',
			'NSK FAQ',
			'manage_options',
			'nsk-faq',
			array($this, 'render_admin_page'),
			'dashicons-editor-help',
			58
		);
	}

	public function render_admin_page() {
		?>
		<div class="wrap">
			<h1>NSK FAQ</h1>

			<p>Esta é a central oficial de regras do plugin.</p>

			<h2>Como funciona</h2>
			<p>
				Quando o FAQ estiver ativado no post ou page, o plugin irá procurar
				blocos marcados no conteúdo e transformar esses blocos em FAQ no front-end.
			</p>

			<h2>Marcações obrigatórias</h2>
			<pre>nsk-faq-inicio:
nsk-perg:
nsk-resp:
nsk-faq-fim:</pre>

			<h2>Regras</h2>
			<ul>
				<li>O FAQ só acontece entre <code>nsk-faq-inicio:</code> e <code>nsk-faq-fim:</code>.</li>
				<li>Dentro do bloco FAQ, a primeira marcação válida deve ser <code>nsk-perg:</code>.</li>
				<li>Tudo entre <code>nsk-perg:</code> e <code>nsk-resp:</code> é pergunta.</li>
				<li>Tudo entre <code>nsk-resp:</code> e a próxima <code>nsk-perg:</code> ou <code>nsk-faq-fim:</code> é resposta.</li>
				<li>Parágrafos vazios são permitidos.</li>
				<li>Texto solto dentro do bloco FAQ invalida a estrutura.</li>
				<li>É permitido ter mais de um bloco FAQ no mesmo conteúdo.</li>
				<li>Blocos FAQ não podem ser aninhados.</li>
			</ul>

			<h2>Exemplo válido</h2>
			<pre>Texto normal antes.

nsk-faq-inicio:
nsk-perg:
O que este plugin faz?

nsk-resp:
Ele transforma conteúdo marcado em FAQ expansível.

nsk-perg:
Posso usar mais de um parágrafo?

nsk-resp:
Sim.

Pode usar mais de um parágrafo sem problema.
nsk-faq-fim:

Texto normal depois.</pre>

			<h2>Validação</h2>
			<p>
				Quando FAQ estiver ativado, o conteúdo será validado antes de publicar ou atualizar.
				Se a estrutura estiver inválida, o conteúdo não deverá ser salvo/publicado normalmente.
			</p>
		</div>
		<?php
	}
}
