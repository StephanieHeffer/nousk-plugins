<?php
/**
 * Plugin Name: Nousk CPT Manager
 * Plugin URI:  https://nousk.com.br
 * Description: Gerencie Custom Post Types pelo painel administrativo.
 * Version: 1.1.0
 * Author: Nousk
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adiciona menu no admin.
 */
function nouskcpt_add_admin_menu() {
    add_menu_page(
        'Nousk CPT Manager',
        'CPT Manager',
        'manage_options',
        'nousk-cpt-manager',
        'nouskcpt_render_admin_page',
        'dashicons-admin-generic',
        20
    );
}
add_action('admin_menu', 'nouskcpt_add_admin_menu');

/**
 * Obtém os CPTs salvos.
 */
function nouskcpt_get_cpts() {
    $cpts = get_option('nouskcpt_cpts', array());
    return is_array($cpts) ? $cpts : array();
}

/**
 * Retorna uma lista de dashicons.
 */
function nouskcpt_get_dashicons() {
    return array(
        'dashicons-admin-post',
        'dashicons-admin-page',
        'dashicons-admin-media',
        'dashicons-admin-comments',
        'dashicons-admin-appearance',
        'dashicons-admin-users',
        'dashicons-admin-tools',
        'dashicons-admin-settings',
        'dashicons-portfolio',
        'dashicons-format-aside',
        'dashicons-format-image',
        'dashicons-format-video',
        'dashicons-format-audio',
        'dashicons-book',
        'dashicons-category',
        'dashicons-tag',
        'dashicons-archive',
        'dashicons-welcome-write-blog',
        'dashicons-feedback',
        'dashicons-products',
    );
}

/**
 * Renderiza a página do plugin.
 */
function nouskcpt_render_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $cpts        = nouskcpt_get_cpts();
    $editing_cpt = null;
    $edit_index  = isset($_GET['edit']) ? absint($_GET['edit']) : null;
    $message     = isset($_GET['message']) ? sanitize_text_field(wp_unslash($_GET['message'])) : '';

    if ($edit_index !== null && isset($cpts[$edit_index])) {
        $editing_cpt = $cpts[$edit_index];
    }

    ?>
    <div class="wrap">
        <h1>Nousk CPT Manager</h1>

        <?php if ($message === 'saved') : ?>
            <div class="notice notice-success is-dismissible">
                <p>CPT salvo com sucesso.</p>
            </div>
        <?php elseif ($message === 'deleted') : ?>
            <div class="notice notice-success is-dismissible">
                <p>CPT removido com sucesso.</p>
            </div>
        <?php elseif ($message === 'duplicate_slug') : ?>
            <div class="notice notice-error is-dismissible">
                <p>Já existe um CPT cadastrado com esse slug.</p>
            </div>
        <?php elseif ($message === 'invalid') : ?>
            <div class="notice notice-error is-dismissible">
                <p>Preencha corretamente os campos obrigatórios.</p>
            </div>
        <?php endif; ?>

        <h2>Custom Post Types cadastrados</h2>

        <?php if (!empty($cpts)) : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th>Ícone</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cpts as $index => $cpt) : ?>
                        <tr>
                            <td><?php echo esc_html($cpt['name']); ?></td>
                            <td><?php echo esc_html($cpt['slug']); ?></td>
                            <td><span class="dashicons <?php echo esc_attr($cpt['icon']); ?>"></span></td>
                            <td>
                                <?php
                                $edit_url = add_query_arg(
                                    array(
                                        'page' => 'nousk-cpt-manager',
                                        'edit' => $index,
                                    ),
                                    admin_url('admin.php')
                                );

                                $delete_url = wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => 'nouskcpt_delete_cpt',
                                            'index'  => $index,
                                        ),
                                        admin_url('admin-post.php')
                                    ),
                                    'nouskcpt_delete_cpt_' . $index
                                );
                                ?>
                                <a href="<?php echo esc_url($edit_url); ?>">Editar</a> |
                                <a href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('Tem certeza que deseja excluir este CPT?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>Nenhum CPT cadastrado ainda.</p>
        <?php endif; ?>

        <hr>

        <h2><?php echo $editing_cpt ? 'Editar CPT' : 'Adicionar novo CPT'; ?></h2>

        <?php
        $new_url = add_query_arg(
            array(
                'page' => 'nousk-cpt-manager',
            ),
            admin_url('admin.php')
        );
        ?>
        <p><a href="<?php echo esc_url($new_url); ?>" class="button">Adicionar novo CPT</a></p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('nouskcpt_save_cpt', 'nouskcpt_nonce'); ?>
            <input type="hidden" name="action" value="nouskcpt_save_cpt">
            <input type="hidden" name="cpt_index" value="<?php echo esc_attr($edit_index !== null ? $edit_index : ''); ?>">

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="cpt_name">Nome do Custom Post Type</label></th>
                    <td>
                        <input type="text" id="cpt_name" name="cpt_name" value="<?php echo esc_attr($editing_cpt['name'] ?? ''); ?>" class="regular-text" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="cpt_slug">Slug</label></th>
                    <td>
                        <input type="text" id="cpt_slug" name="cpt_slug" value="<?php echo esc_attr($editing_cpt['slug'] ?? ''); ?>" class="regular-text" required>
                        <p class="description">Use apenas letras minúsculas, números e hífens.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="cpt_icon">Ícone</label></th>
                    <td>
                        <input type="text" id="cpt_icon" name="cpt_icon" value="<?php echo esc_attr($editing_cpt['icon'] ?? 'dashicons-admin-post'); ?>" class="regular-text" readonly>
                        <div id="icon-picker" class="nouskcpt-icon-grid">
                            <?php foreach (nouskcpt_get_dashicons() as $icon) : ?>
                                <span class="dashicons <?php echo esc_attr($icon); ?> nouskcpt-icon-option" data-icon="<?php echo esc_attr($icon); ?>"></span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Elementos</th>
                    <td>
                        <label><input type="checkbox" checked disabled> Título</label><br>
                        <label><input type="checkbox" name="cpt_supports[]" value="editor" <?php checked(isset($editing_cpt['supports']) && in_array('editor', $editing_cpt['supports'], true)); ?>> Conteúdo</label><br>
                        <label><input type="checkbox" name="cpt_supports[]" value="thumbnail" <?php checked(isset($editing_cpt['supports']) && in_array('thumbnail', $editing_cpt['supports'], true)); ?>> Imagem destacada</label><br>
                        <label><input type="checkbox" name="cpt_supports[]" value="excerpt" <?php checked(isset($editing_cpt['supports']) && in_array('excerpt', $editing_cpt['supports'], true)); ?>> Resumo</label>
                    </td>
                </tr>
            </table>

            <?php submit_button($editing_cpt ? 'Salvar alterações' : 'Criar CPT'); ?>
        </form>
    </div>

    <style>
        .nouskcpt-icon-grid {
            display: grid;
            grid-template-columns: repeat(6, 50px);
            gap: 10px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            background: #f9f9f9;
            margin-top: 10px;
        }

        .nouskcpt-icon-option {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            font-size: 24px;
            cursor: pointer;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #fff;
        }

        .nouskcpt-icon-option:hover {
            background-color: #ddd;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const iconInput = document.getElementById('cpt_icon');
            const nameInput = document.getElementById('cpt_name');
            const slugInput = document.getElementById('cpt_slug');

            document.querySelectorAll('.nouskcpt-icon-option').forEach(function(icon) {
                icon.addEventListener('click', function() {
                    iconInput.value = this.dataset.icon;
                });
            });

            nameInput.addEventListener('input', function() {
                let slug = nameInput.value
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-');

                slugInput.value = slug;
            });
        });
    </script>
    <?php
}

/**
 * Salva CPT.
 */
function nouskcpt_handle_save() {
    if (!current_user_can('manage_options')) {
        wp_die('Você não tem permissão para executar esta ação.');
    }

    if (!isset($_POST['nouskcpt_nonce']) || !wp_verify_nonce($_POST['nouskcpt_nonce'], 'nouskcpt_save_cpt')) {
        wp_die('Ação não permitida.');
    }

    $name = isset($_POST['cpt_name']) ? sanitize_text_field(wp_unslash($_POST['cpt_name'])) : '';
    $slug = isset($_POST['cpt_slug']) ? sanitize_title(wp_unslash($_POST['cpt_slug'])) : '';
    $icon = isset($_POST['cpt_icon']) ? sanitize_text_field(wp_unslash($_POST['cpt_icon'])) : 'dashicons-admin-post';

    $supports = isset($_POST['cpt_supports']) ? array_map('sanitize_text_field', wp_unslash($_POST['cpt_supports'])) : array();

    if (empty($name) || empty($slug)) {
        wp_safe_redirect(admin_url('admin.php?page=nousk-cpt-manager&message=invalid'));
        exit;
    }

    if (!in_array('title', $supports, true)) {
        $supports[] = 'title';
    }

    $cpts       = nouskcpt_get_cpts();
    $edit_index = isset($_POST['cpt_index']) && $_POST['cpt_index'] !== '' ? absint($_POST['cpt_index']) : null;

    foreach ($cpts as $index => $cpt) {
        if ($cpt['slug'] === $slug && $index !== $edit_index) {
            wp_safe_redirect(admin_url('admin.php?page=nousk-cpt-manager&message=duplicate_slug'));
            exit;
        }
    }

    $cpt_data = array(
        'name'     => $name,
        'slug'     => $slug,
        'icon'     => $icon,
        'supports' => $supports,
    );

    if ($edit_index !== null && isset($cpts[$edit_index])) {
        $cpts[$edit_index] = $cpt_data;
    } else {
        $cpts[] = $cpt_data;
    }

    update_option('nouskcpt_cpts', $cpts);

    wp_safe_redirect(admin_url('admin.php?page=nousk-cpt-manager&message=saved'));
    exit;
}
add_action('admin_post_nouskcpt_save_cpt', 'nouskcpt_handle_save');

/**
 * Exclui CPT salvo.
 */
function nouskcpt_handle_delete() {
    if (!current_user_can('manage_options')) {
        wp_die('Você não tem permissão para executar esta ação.');
    }

    $index = isset($_GET['index']) ? absint($_GET['index']) : null;

    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'nouskcpt_delete_cpt_' . $index)) {
        wp_die('Ação não permitida.');
    }

    $cpts = nouskcpt_get_cpts();

    if ($index !== null && isset($cpts[$index])) {
        unset($cpts[$index]);
        $cpts = array_values($cpts);
        update_option('nouskcpt_cpts', $cpts);
    }

    wp_safe_redirect(admin_url('admin.php?page=nousk-cpt-manager&message=deleted'));
    exit;
}
add_action('admin_post_nouskcpt_delete_cpt', 'nouskcpt_handle_delete');

/**
 * Registra os CPTs salvos.
 */
function nouskcpt_register_custom_cpts() {
    $cpts = nouskcpt_get_cpts();

    foreach ($cpts as $cpt) {
        $labels = array(
            'name'          => $cpt['name'],
            'singular_name' => $cpt['name'],
            'add_new_item'  => 'Adicionar novo ' . $cpt['name'],
            'edit_item'     => 'Editar ' . $cpt['name'],
            'new_item'      => 'Novo ' . $cpt['name'],
            'view_item'     => 'Ver ' . $cpt['name'],
            'search_items'  => 'Buscar ' . $cpt['name'],
            'not_found'     => 'Nenhum item encontrado',
        );

        register_post_type(
            $cpt['slug'],
            array(
                'labels'       => $labels,
                'public'       => true,
                'show_in_menu' => true,
                'menu_icon'    => $cpt['icon'],
                'supports'     => $cpt['supports'],
                'has_archive'  => true,
                'show_in_rest' => true,
            )
        );
    }
}
add_action('init', 'nouskcpt_register_custom_cpts');
