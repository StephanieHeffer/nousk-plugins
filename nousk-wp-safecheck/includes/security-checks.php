<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cria estrutura padrão de um check.
 */
function nousksafe_make_check($id, $title, $category, $status, $message, $recommendation, $details = '') {
    return array(
        'id'             => $id,
        'title'          => $title,
        'category'       => $category,
        'status'         => $status,
        'message'        => $message,
        'recommendation' => $recommendation,
        'details'        => $details,
    );
}

/**
 * Retorna todos os checks da v1.
 */
function nousksafe_get_all_checks() {
    return array(
        nousksafe_check_https(),
        nousksafe_check_admin_username(),
        nousksafe_check_file_edit(),
        nousksafe_check_xmlrpc(),
        nousksafe_check_plugin_updates(),
        nousksafe_check_theme_updates(),
        nousksafe_check_wp_login(),
        nousksafe_check_git_access(),
        nousksafe_check_env_access(),
        nousksafe_check_security_headers(),
        nousksafe_check_wp_config_access(),
        nousksafe_check_sensitive_directories(),
        nousksafe_check_robots_txt(),
    );
}

/**
 * Retorna resumo por status.
 */
function nousksafe_get_checks_summary($checks) {
    $summary = array(
        'ok'      => 0,
        'warning' => 0,
        'risk'    => 0,
        'manual'  => 0,
    );

    foreach ($checks as $check) {
        if (isset($summary[$check['status']])) {
            $summary[$check['status']]++;
        }
    }

    return $summary;
}

/**
 * Agrupa checks por categoria.
 */
function nousksafe_group_checks_by_category($checks) {
    $grouped = array();

    foreach ($checks as $check) {
        $category = $check['category'];

        if (!isset($grouped[$category])) {
            $grouped[$category] = array();
        }

        $grouped[$category][] = $check;
    }

    return $grouped;
}

/**
 * Check: HTTPS ativo
 */
function nousksafe_check_https() {
    $is_https = is_ssl() || strpos(home_url(), 'https://') === 0;

    return nousksafe_make_check(
        'https_active',
        'HTTPS ativo',
        'Servidor e transporte',
        $is_https ? 'ok' : 'risk',
        $is_https
            ? 'O site está respondendo com HTTPS.'
            : 'O site não parece estar usando HTTPS.',
        $is_https
            ? 'Mantenha o certificado SSL/TLS válido e renovado.'
            : 'Ative HTTPS para proteger tráfego, autenticação e sessões.'
    );
}

/**
 * Check: Usuário com login admin
 */
function nousksafe_check_admin_username() {
    $user   = get_user_by('login', 'admin');
    $exists = $user instanceof WP_User;

    return nousksafe_make_check(
        'admin_username',
        'Usuário com login admin',
        'Configuração do WordPress',
        $exists ? 'warning' : 'ok',
        $exists
            ? 'Foi encontrado um usuário com login admin.'
            : 'Não foi encontrado usuário com login admin.',
        'Evite logins previsíveis em contas privilegiadas para reduzir tentativas automatizadas de acesso.'
    );
}

/**
 * Check: Edição de arquivos pelo admin
 */
function nousksafe_check_file_edit() {
    $disabled = defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT;

    return nousksafe_make_check(
        'file_edit_disabled',
        'Edição de arquivos pelo admin',
        'Configuração do WordPress',
        $disabled ? 'ok' : 'warning',
        $disabled
            ? 'A edição de arquivos pelo painel administrativo está desativada.'
            : 'A edição de arquivos pelo painel administrativo está ativada.',
        $disabled
            ? 'Mantenha essa configuração para reduzir risco em caso de comprometimento de uma conta administrativa.'
            : 'Desative esse recurso sempre que possível para reduzir risco em caso de comprometimento de uma conta administrativa.'
    );
}

/**
 * Check: XML-RPC acessível
 */
function nousksafe_check_xmlrpc() {
    $xmlrpc_url = site_url('/xmlrpc.php');

    $response = wp_remote_get(
        $xmlrpc_url,
        array(
            'timeout'     => 10,
            'redirection' => 3,
        )
    );

    if (is_wp_error($response)) {
        return nousksafe_make_check(
            'xmlrpc_access',
            'XML-RPC acessível',
            'Acesso público',
            'ok',
            'O endpoint xmlrpc.php não respondeu como endpoint público acessível.',
            'Se o XML-RPC não for necessário no projeto, mantenha-o bloqueado.',
            $response->get_error_message()
        );
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body        = wp_remote_retrieve_body($response);

    $is_accessible = ($status_code === 200 && stripos($body, 'XML-RPC server accepts POST requests only') !== false);

    return nousksafe_make_check(
        'xmlrpc_access',
        'XML-RPC acessível',
        'Acesso público',
        $is_accessible ? 'warning' : 'ok',
        $is_accessible
            ? 'O endpoint xmlrpc.php está acessível publicamente.'
            : 'O endpoint xmlrpc.php não está acessível publicamente.',
        'Se o XML-RPC não for necessário no projeto, considere bloqueá-lo para reduzir a superfície de ataque.',
        'URL verificada: ' . $xmlrpc_url
    );
}

/**
 * Check: Plugins com atualização pendente
 */
function nousksafe_check_plugin_updates() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $update_plugins = get_site_transient('update_plugins');
    $plugins_with_updates = array();

    if (!empty($update_plugins->response) && is_array($update_plugins->response)) {
        $all_plugins = get_plugins();

        foreach ($update_plugins->response as $plugin_file => $plugin_data) {
            if (isset($all_plugins[$plugin_file]['Name'])) {
                $plugins_with_updates[] = $all_plugins[$plugin_file]['Name'];
            }
        }
    }

    $has_updates = !empty($plugins_with_updates);

    return nousksafe_make_check(
        'plugins_updates',
        'Plugins com atualização pendente',
        'Configuração do WordPress',
        $has_updates ? 'warning' : 'ok',
        $has_updates
            ? 'Existem plugins com atualização pendente.'
            : 'Não foram encontradas atualizações pendentes para plugins.',
        'Mantenha os plugins atualizados para reduzir exposição a vulnerabilidades conhecidas.',
        $has_updates ? implode(', ', $plugins_with_updates) : ''
    );
}

/**
 * Check: Temas com atualização pendente
 */
function nousksafe_check_theme_updates() {
    $update_themes = get_site_transient('update_themes');
    $themes_with_updates = array();

    if (!empty($update_themes->response) && is_array($update_themes->response)) {
        $installed_themes = wp_get_themes();

        foreach ($update_themes->response as $theme_slug => $theme_data) {
            if (isset($installed_themes[$theme_slug])) {
                $themes_with_updates[] = $installed_themes[$theme_slug]->get('Name');
            } else {
                $themes_with_updates[] = $theme_slug;
            }
        }
    }

    $has_updates = !empty($themes_with_updates);

    return nousksafe_make_check(
        'themes_updates',
        'Temas com atualização pendente',
        'Configuração do WordPress',
        $has_updates ? 'warning' : 'ok',
        $has_updates
            ? 'Existem temas com atualização pendente.'
            : 'Não foram encontradas atualizações pendentes para temas.',
        'Mantenha os temas atualizados e remova, quando possível, temas não utilizados.',
        $has_updates ? implode(', ', $themes_with_updates) : ''
    );
}

/**
 * Check: wp-login.php acessível
 */
function nousksafe_check_wp_login() {
    $login_url = site_url('/wp-login.php');

    $response = wp_remote_get(
        $login_url,
        array(
            'timeout' => 10,
        )
    );

    if (is_wp_error($response)) {
        return nousksafe_make_check(
            'wp_login_access',
            'Acesso ao wp-login.php',
            'Acesso público',
            'ok',
            'A página padrão de login não respondeu como endpoint público.',
            'Isso reduz exposição automatizada, mas não substitui outras medidas de segurança.',
            $response->get_error_message()
        );
    }

    $status_code = wp_remote_retrieve_response_code($response);

    $is_accessible = ($status_code === 200);

    return nousksafe_make_check(
        'wp_login_access',
        'Acesso ao wp-login.php',
        'Acesso público',
        $is_accessible ? 'warning' : 'ok',
        $is_accessible
            ? 'A página padrão de login do WordPress está acessível publicamente.'
            : 'A página padrão de login não está acessível publicamente.',
        'Considere proteção adicional como 2FA, limite de tentativas ou restrição de acesso.',
        'URL verificada: ' . $login_url
    );
}

/**
 * Check: acesso à pasta .git
 */
function nousksafe_check_git_access() {
    $git_url = site_url('/.git/HEAD');

    $response = wp_remote_get(
        $git_url,
        array(
            'timeout' => 10,
        )
    );

    if (is_wp_error($response)) {
        return nousksafe_make_check(
            'git_access',
            'Acesso à pasta .git',
            'Acesso público',
            'ok',
            'Não foi possível acessar arquivos da pasta .git.',
            'Mantenha essa proteção para evitar exposição de código.',
            $response->get_error_message()
        );
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body        = wp_remote_retrieve_body($response);

    $is_exposed = ($status_code === 200 && stripos($body, 'ref:') !== false);

    return nousksafe_make_check(
        'git_access',
        'Acesso à pasta .git',
        'Acesso público',
        $is_exposed ? 'risk' : 'ok',
        $is_exposed
            ? 'Foi detectado acesso público à pasta .git.'
            : 'Não foi detectado acesso público à pasta .git.',
        'Bloqueie imediatamente o acesso à pasta .git, pois ela pode expor informações sensíveis do projeto.',
        'URL verificada: ' . $git_url
    );
}

/**
 * Check: acesso ao arquivo .env
 */
function nousksafe_check_env_access() {
    $env_url = site_url('/.env');

    $response = wp_remote_get(
        $env_url,
        array(
            'timeout' => 10,
        )
    );

    if (is_wp_error($response)) {
        return nousksafe_make_check(
            'env_access',
            'Acesso ao arquivo .env',
            'Acesso público',
            'ok',
            'Não foi possível acessar o arquivo .env.',
            'Mantenha arquivos sensíveis fora da raiz pública.',
            $response->get_error_message()
        );
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body        = wp_remote_retrieve_body($response);

    // heurística simples
    $is_exposed = ($status_code === 200 && strlen($body) > 0);

    return nousksafe_make_check(
        'env_access',
        'Acesso ao arquivo .env',
        'Acesso público',
        $is_exposed ? 'risk' : 'ok',
        $is_exposed
            ? 'O arquivo .env parece estar acessível publicamente.'
            : 'O arquivo .env não está acessível publicamente.',
        'Garanta que arquivos de ambiente não fiquem expostos no servidor.',
        'URL verificada: ' . $env_url
    );
}

/**
 * Check: Headers de segurança
 */
function nousksafe_check_security_headers() {
    $url = home_url();

    $response = wp_remote_get(
        $url,
        array(
            'timeout' => 10,
        )
    );

    if (is_wp_error($response)) {
        return nousksafe_make_check(
            'security_headers',
            'Headers de segurança',
            'Servidor e transporte',
            'manual',
            'Não foi possível verificar os headers de segurança.',
            'Verifique manualmente os headers configurados no servidor.',
            $response->get_error_message()
        );
    }

    $headers = wp_remote_retrieve_headers($response);

    $required_headers = array(
        'strict-transport-security' => 'HSTS',
        'x-frame-options'           => 'X-Frame-Options',
        'x-content-type-options'    => 'X-Content-Type-Options',
        'content-security-policy'   => 'Content-Security-Policy',
        'referrer-policy'           => 'Referrer-Policy',
    );

    $missing = array();

    foreach ($required_headers as $key => $label) {
        if (!isset($headers[$key])) {
            $missing[] = $label;
        }
    }

    if (empty($missing)) {
        return nousksafe_make_check(
            'security_headers',
            'Headers de segurança',
            'Servidor e transporte',
            'ok',
            'Headers de segurança importantes foram detectados.',
            'Mantenha essa configuração para melhorar a segurança no navegador.'
        );
    }

    return nousksafe_make_check(
        'security_headers',
        'Headers de segurança',
        'Servidor e transporte',
        'warning',
        'Alguns headers de segurança não foram detectados.',
        'Configure headers como HSTS, CSP e X-Frame-Options para aumentar a proteção.',
        'Ausentes: ' . implode(', ', $missing)
    );
}

/**
 * Check: acesso ao wp-config.php
 */
function nousksafe_check_wp_config_access() {
    $config_url = site_url('/wp-config.php');

    $response = wp_remote_get(
        $config_url,
        array(
            'timeout' => 10,
        )
    );

    if (is_wp_error($response)) {
        return nousksafe_make_check(
            'wp_config_access',
            'Acesso ao wp-config.php',
            'Acesso público',
            'ok',
            'Não foi possível acessar o arquivo wp-config.php.',
            'Mantenha essa proteção para evitar exposição de dados sensíveis.',
            $response->get_error_message()
        );
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body        = wp_remote_retrieve_body($response);

    // heurística simples
    $is_exposed = ($status_code === 200 && stripos($body, 'DB_NAME') !== false);

    return nousksafe_make_check(
        'wp_config_access',
        'Acesso ao wp-config.php',
        'Acesso público',
        $is_exposed ? 'risk' : 'ok',
        $is_exposed
            ? 'O arquivo wp-config.php parece estar acessível publicamente.'
            : 'O arquivo wp-config.php não está acessível publicamente.',
        'O wp-config.php contém dados sensíveis e nunca deve estar acessível via navegador.',
        'URL verificada: ' . $config_url
    );
}

/**
 * Check: diretórios sensíveis acessíveis
 */
function nousksafe_check_sensitive_directories() {
    $paths = array(
        '/wp-content/uploads/',
        '/wp-includes/',
        '/wp-content/plugins/',
        '/wp-content/themes/',
    );

    $exposed = array();

    foreach ($paths as $path) {
        $url = site_url($path);

        $response = wp_remote_get(
            $url,
            array(
                'timeout' => 10,
            )
        );

        if (is_wp_error($response)) {
            continue;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = wp_remote_retrieve_body($response);

        // detecta index of (listagem)
        if ($status_code === 200 && stripos($body, 'Index of') !== false) {
            $exposed[] = $path;
        }
    }

    $has_exposure = !empty($exposed);

    return nousksafe_make_check(
        'sensitive_directories',
        'Diretórios sensíveis expostos',
        'Acesso público',
        $has_exposure ? 'risk' : 'ok',
        $has_exposure
            ? 'Foi detectada listagem pública em diretórios sensíveis.'
            : 'Não foi detectada listagem pública em diretórios sensíveis.',
        'Desative a listagem de diretórios no servidor (Options -Indexes no Apache ou configuração equivalente).',
        $has_exposure ? implode(', ', $exposed) : ''
    );
}

/**
 * Check: revisão do robots.txt
 */
function nousksafe_check_robots_txt() {
    $robots_url = site_url('/robots.txt');

    $response = wp_remote_get(
        $robots_url,
        array(
            'timeout' => 10,
        )
    );

    if (is_wp_error($response)) {
        return nousksafe_make_check(
            'robots_review',
            'Revisão do robots.txt',
            'Revisão manual',
            'manual',
            'Não foi possível verificar automaticamente o arquivo robots.txt.',
            'Revise manualmente se o robots.txt expõe caminhos ou informações desnecessárias.',
            $response->get_error_message()
        );
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body        = trim(wp_remote_retrieve_body($response));

    if ($status_code === 200 && $body !== '') {
        return nousksafe_make_check(
            'robots_review',
            'Revisão do robots.txt',
            'Revisão manual',
            'manual',
            'O arquivo robots.txt foi encontrado e deve ser revisado manualmente.',
            'Verifique se o robots.txt não está expondo caminhos sensíveis ou regras desnecessárias.',
            'URL verificada: ' . $robots_url
        );
    }

    return nousksafe_make_check(
        'robots_review',
        'Revisão do robots.txt',
        'Revisão manual',
        'manual',
        'Nenhum robots.txt explícito foi encontrado ou a resposta exige revisão manual.',
        'Confirme se a ausência ou geração automática do robots.txt está adequada ao projeto.',
        'URL verificada: ' . $robots_url
    );
}
