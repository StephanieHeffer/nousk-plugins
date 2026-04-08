<?php
/**
 * Plugin Name: Nousk Post Duplicator
 * Plugin URI:  https://nousk.com.br
 * Description: Adiciona um botão para duplicar posts, páginas e custom post types no WordPress.
 * Version: 1.0.0
 * Author: Nousk
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adiciona o link "Duplicar" na lista de posts, páginas e CPTs.
 */
function nouskpd_add_duplicate_link($actions, $post) {
    if (!current_user_can('edit_post', $post->ID)) {
        return $actions;
    }

    $url = add_query_arg(
        array(
            'action'  => 'nouskpd_duplicate_post',
            'post_id' => $post->ID,
        ),
        admin_url('admin-post.php')
    );

    $url = wp_nonce_url($url, 'nouskpd_duplicate_post_' . $post->ID);

    $actions['duplicate'] = '<a href="' . esc_url($url) . '" title="' . esc_attr__('Duplicar este post', 'nousk-post-duplicator') . '" style="color: green;">' . esc_html__('Duplicar', 'nousk-post-duplicator') . '</a>';

    return $actions;
}
add_filter('post_row_actions', 'nouskpd_add_duplicate_link', 10, 2);
add_filter('page_row_actions', 'nouskpd_add_duplicate_link', 10, 2);

/**
 * Gera um título único para a cópia.
 */
function nouskpd_generate_unique_title($title, $post_type) {
    $count = 0;
    $new_title = 'Cópia - ' . $title;

    while (post_exists($new_title, '', '', $post_type)) {
        $count++;
        $new_title = 'Cópia (' . $count . ') - ' . $title;
    }

    return $new_title;
}

/**
 * Duplica o post.
 */
function nouskpd_duplicate_post() {
    if (!isset($_GET['post_id'], $_GET['_wpnonce'])) {
        wp_die('Requisição inválida.');
    }

    $post_id = absint($_GET['post_id']);

    if (!wp_verify_nonce($_GET['_wpnonce'], 'nouskpd_duplicate_post_' . $post_id)) {
        wp_die('Falha na verificação de segurança.');
    }

    if (!current_user_can('edit_post', $post_id)) {
        wp_die('Acesso negado.');
    }

    $post = get_post($post_id);

    if (!$post) {
        wp_die('Post não encontrado.');
    }

    $new_title = nouskpd_generate_unique_title($post->post_title, $post->post_type);

    $new_post = array(
        'post_title'            => $new_title,
        'post_content'          => $post->post_content,
        'post_excerpt'          => $post->post_excerpt,
        'post_status'           => 'draft',
        'post_type'             => $post->post_type,
        'post_author'           => get_current_user_id(),
        'post_parent'           => $post->post_parent,
        'menu_order'            => $post->menu_order,
        'post_content_filtered' => $post->post_content_filtered,
    );

    $new_post_id = wp_insert_post($new_post);

    if (is_wp_error($new_post_id) || !$new_post_id) {
        wp_die('Erro ao duplicar o post.');
    }

    $taxonomies = get_object_taxonomies($post->post_type);
    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'ids'));
        wp_set_object_terms($new_post_id, $terms, $taxonomy);
    }

    $meta_keys_to_skip = array(
        '_edit_lock',
        '_edit_last',
    );

    $meta_data = get_post_meta($post_id);
    foreach ($meta_data as $key => $values) {
        if (in_array($key, $meta_keys_to_skip, true)) {
            continue;
        }

        foreach ($values as $value) {
            add_post_meta($new_post_id, $key, maybe_unserialize($value));
        }
    }

    wp_redirect(admin_url('edit.php?post_type=' . $post->post_type));
    exit;
}
add_action('admin_post_nouskpd_duplicate_post', 'nouskpd_duplicate_post');
