<?php
/**
 * Plugin Name: Nousk WordPress Reset
 * Plugin URI:  https://nousk.com.br
 * Description: Reseta uma instalação WordPress para desenvolvimento, removendo conteúdos, plugins comuns e temas extras, enquanto mantém usuários e este plugin.
 * Version: 1.4.0
 * Author: Nousk
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adiciona o menu no admin.
 */
function nouskwpr_add_admin_menu() {
    add_menu_page(
        'Nousk WordPress Reset',
        'Nousk Reset',
        'manage_options',
        'nousk-wordpress-reset',
        'nouskwpr_render_admin_page',
        'dashicons-warning',
        99
    );
}
add_action('admin_menu', 'nouskwpr_add_admin_menu');

/**
 * Renderiza a página do plugin.
 */
function nouskwpr_render_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $reset_success = isset($_GET['reset']) && $_GET['reset'] === 'success';
    $theme_warning = isset($_GET['theme_warning']) && $_GET['theme_warning'] === '1';
    ?>
    <div class="wrap">
        <h1>Nousk WordPress Reset</h1>

        <?php if ($reset_success) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Reset concluído.</strong> O site foi limpo com sucesso.</p>
            </div>
        <?php endif; ?>

        <?php if ($theme_warning) : ?>
            <div class="notice notice-warning is-dismissible">
                <p><strong>Atenção:</strong> nenhum tema padrão do WordPress foi encontrado. O tema ativo atual foi mantido para evitar que o site fique sem tema utilizável.</p>
            </div>
        <?php endif; ?>

        <p>Esta ferramenta foi pensada para desenvolvedores que precisam limpar rapidamente uma instalação WordPress usada em testes, estudos, desenvolvimento ou staging.</p>

        <p><strong>Atenção:</strong> esta ação é destrutiva e não pode ser desfeita.</p>

        <p>Ao executar o reset, o plugin irá remover permanentemente:</p>
        <ul style="list-style: disc; margin-left: 20px;">
            <li>posts, páginas, anexos e custom post types, incluindo itens já enviados para a lixeira</li>
            <li>categorias, tags e outras taxonomias</li>
            <li>comentários e dados relacionados</li>
            <li>plugins instalados comuns, exceto este plugin</li>
            <li>temas extras</li>
        </ul>

        <p>Serão mantidos:</p>
        <ul style="list-style: disc; margin-left: 20px;">
            <li>os usuários</li>
            <li>este plugin</li>
            <li>um tema padrão do WordPress, se houver um instalado</li>
        </ul>

        <p><strong>Observação técnica:</strong> o plugin não apaga indiscriminadamente tudo do banco de dados. Isso é intencional, para evitar a remoção de opções internas do WordPress que podem quebrar a instalação. Dependendo do histórico do site, alguns registros técnicos antigos podem permanecer no banco e podem ser revisados manualmente depois.</p>

        <p><strong>Observação adicional:</strong> plugins do tipo must-use (<code>mu-plugins</code>) e arquivos avançados de integração ou cache, como drop-ins, podem permanecer no ambiente após o reset.</p>

        <p><strong>Recomendação:</strong> embora tenha sido pensado para desenvolvimento e staging, ele também pode ser usado em outros ambientes com total consciência de que o conteúdo será apagado permanentemente.</p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('nouskwpr_reset_action', 'nouskwpr_nonce'); ?>
            <input type="hidden" name="action" value="nouskwpr_run_reset">
            <button
                type="submit"
                class="button button-primary"
                onclick="return confirm('Tem certeza que deseja executar o reset do WordPress? Esta ação apagará o conteúdo permanentemente e não poderá ser desfeita.');"
            >
                Executar Reset
            </button>
        </form>
    </div>
    <?php
}

/**
 * Processa o reset.
 */
function nouskwpr_handle_reset() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Você não tem permissão para executar esta ação.', 'nousk-wordpress-reset'));
    }

    if (!isset($_POST['nouskwpr_nonce']) || !wp_verify_nonce($_POST['nouskwpr_nonce'], 'nouskwpr_reset_action')) {
        wp_die(__('Ação não permitida.', 'nousk-wordpress-reset'));
    }

    if (is_multisite()) {
        wp_die(__('Este plugin ainda não oferece suporte para instalações Multisite.', 'nousk-wordpress-reset'));
    }

    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    require_once ABSPATH . 'wp-admin/includes/theme.php';

    $theme_warning = nouskwpr_prepare_theme();

    nouskwpr_delete_all_content();
    nouskwpr_delete_all_terms();
    nouskwpr_delete_other_plugins();
    nouskwpr_delete_extra_themes();

    wp_cache_flush();
    delete_option('recently_activated');

    $redirect_url = add_query_arg(
        array(
            'page'          => 'nousk-wordpress-reset',
            'reset'         => 'success',
            'theme_warning' => $theme_warning ? '1' : '0',
        ),
        admin_url('admin.php')
    );

    wp_safe_redirect($redirect_url);
    exit;
}
add_action('admin_post_nouskwpr_run_reset', 'nouskwpr_handle_reset');

/**
 * Ativa um tema padrão se existir.
 * Se não existir, mantém o tema atual e retorna true para exibir aviso.
 *
 * @return bool
 */
function nouskwpr_prepare_theme() {
    $default_themes = array(
        'twentytwentysix',
        'twentytwentyfive',
        'twentytwentyfour',
        'twentytwentythree',
        'twentytwentytwo',
        'twentytwentyone',
        'twentytwenty',
        'twentynineteen',
        'twentyseventeen',
        'twentysixteen',
    );

    $current_theme = wp_get_theme();
    $current_slug  = $current_theme->get_stylesheet();

    foreach ($default_themes as $theme_slug) {
        $theme = wp_get_theme($theme_slug);

        if ($theme->exists()) {
            if ($current_slug !== $theme_slug) {
                switch_theme($theme_slug);
            }
            return false;
        }
    }

    return true;
}

/**
 * Apaga posts, páginas, CPTs, anexos e comentários permanentemente.
 */
function nouskwpr_delete_all_content() {
    global $wpdb;

    $post_ids = $wpdb->get_col(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type != 'revision'"
    );

    foreach ($post_ids as $post_id) {
        $post_type = get_post_type($post_id);

        if ($post_type === 'attachment') {
            wp_delete_attachment($post_id, true);
        } else {
            wp_delete_post($post_id, true);
        }
    }

    $comment_ids = $wpdb->get_col(
        "SELECT comment_ID FROM {$wpdb->comments}"
    );

    foreach ($comment_ids as $comment_id) {
        wp_delete_comment($comment_id, true);
    }
}

/**
 * Apaga termos de taxonomias.
 */
function nouskwpr_delete_all_terms() {
    $excluded_taxonomies = array(
        'nav_menu',
        'link_category',
        'wp_theme',
        'wp_template_part_area',
        'wp_pattern_category',
    );

    $taxonomies = get_taxonomies(array(), 'names');

    foreach ($taxonomies as $taxonomy) {
        if (in_array($taxonomy, $excluded_taxonomies, true)) {
            continue;
        }

        $terms = get_terms(
            array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            )
        );

        if (is_wp_error($terms) || empty($terms)) {
            continue;
        }

        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $taxonomy);
        }
    }
}

/**
 * Desativa, desinstala e apaga plugins, exceto este plugin.
 */
function nouskwpr_delete_other_plugins() {
    $current_plugin = plugin_basename(__FILE__);
    $all_plugins    = array_keys(get_plugins());

    foreach ($all_plugins as $plugin_file) {
        if ($plugin_file === $current_plugin) {
            continue;
        }

        if (is_plugin_active($plugin_file)) {
            deactivate_plugins($plugin_file, true);
        }

        uninstall_plugin($plugin_file);
        delete_plugins(array($plugin_file));
    }
}

/**
 * Apaga temas extras e mantém apenas o ativo e, se existir, um tema padrão.
 */
function nouskwpr_delete_extra_themes() {
    $active_theme = wp_get_theme();
    $active_slug  = $active_theme->get_stylesheet();

    $default_themes = array(
        'twentytwentysix',
        'twentytwentyfive',
        'twentytwentyfour',
        'twentytwentythree',
        'twentytwentytwo',
        'twentytwentyone',
        'twentytwenty',
        'twentynineteen',
        'twentyseventeen',
        'twentysixteen',
    );

    $keep_themes = array($active_slug);

    foreach ($default_themes as $theme_slug) {
        $theme = wp_get_theme($theme_slug);

        if ($theme->exists()) {
            $keep_themes[] = $theme_slug;
            break;
        }
    }

    $installed_themes = wp_get_themes();

    foreach ($installed_themes as $theme_slug => $theme_obj) {
        if (in_array($theme_slug, $keep_themes, true)) {
            continue;
        }

        delete_theme($theme_slug);
    }
}
