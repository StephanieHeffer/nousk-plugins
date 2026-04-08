<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Nousk WP SafeCheck</h1>

    <p>
        Checklist de segurança para WordPress com feedback técnico acessível.
    </p>

    <hr>

    <h2>Resumo</h2>

    <div style="display:flex; gap:12px; flex-wrap:wrap; margin: 16px 0 24px;">
        <div style="background:#ecfdf5; border:1px solid #10b981; padding:12px 16px; border-radius:8px; min-width:120px;">
            <strong>OK</strong><br>
            <?php echo esc_html($summary['ok']); ?>
        </div>

        <div style="background:#fffbeb; border:1px solid #f59e0b; padding:12px 16px; border-radius:8px; min-width:120px;">
            <strong>Atenção</strong><br>
            <?php echo esc_html($summary['warning']); ?>
        </div>

        <div style="background:#fef2f2; border:1px solid #ef4444; padding:12px 16px; border-radius:8px; min-width:120px;">
            <strong>Risco</strong><br>
            <?php echo esc_html($summary['risk']); ?>
        </div>

        <div style="background:#eff6ff; border:1px solid #3b82f6; padding:12px 16px; border-radius:8px; min-width:120px;">
            <strong>Manual</strong><br>
            <?php echo esc_html($summary['manual']); ?>
        </div>
    </div>

    <?php foreach ($grouped_checks as $category => $checks_in_category) : ?>
        <h2><?php echo esc_html($category); ?></h2>

        <div style="display:grid; gap:16px; margin-bottom:32px;">
            <?php foreach ($checks_in_category as $check) : ?>
                <?php
                $status_labels = array(
                    'ok'      => 'OK',
                    'warning' => 'Atenção',
                    'risk'    => 'Risco',
                    'manual'  => 'Manual',
                );

                $status_styles = array(
                    'ok'      => 'background:#ecfdf5; color:#065f46; border:1px solid #10b981;',
                    'warning' => 'background:#fffbeb; color:#92400e; border:1px solid #f59e0b;',
                    'risk'    => 'background:#fef2f2; color:#991b1b; border:1px solid #ef4444;',
                    'manual'  => 'background:#eff6ff; color:#1d4ed8; border:1px solid #3b82f6;',
                );
                ?>

                <div style="background:#fff; border:1px solid #ddd; border-radius:10px; padding:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                        <h3 style="margin:0;">
                            <?php echo esc_html($check['title']); ?>
                        </h3>

                        <span style="padding:6px 10px; border-radius:999px; font-size:12px; font-weight:600; <?php echo esc_attr($status_styles[$check['status']]); ?>">
                            <?php echo esc_html($status_labels[$check['status']]); ?>
                        </span>
                    </div>

                    <p style="margin:12px 0 8px;">
                        <?php echo esc_html($check['message']); ?>
                    </p>

                    <p style="margin:0;">
                        <strong>Recomendação:</strong>
                        <?php echo esc_html($check['recommendation']); ?>
                    </p>

                    <?php if (!empty($check['details'])) : ?>
                        <p style="margin-top:10px; color:#555;">
                            <strong>Detalhes:</strong>
                            <?php echo esc_html($check['details']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
