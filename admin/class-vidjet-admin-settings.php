<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Vidjet_Admin_Settings
 */
class Vidjet_Admin_Settings
{
    /**
     * The ID of this plugin.
     *
     * @var string $plugin_name The ID of this plugin.
     */
    private static $plugin_name = 'vidjet';

    /**
     *
     */
    public static function output()
    {
        include_once __DIR__ . '/partials/vidjet-admin-settings.php';
    }

    /**
     *
     */
    public static function settings_section_callback()
    {

    }

    /**
     * @param array $params
     */
    private static function render_text_field($params)
    { ?>
        <input
                type='text'
                name='vidjet_settings[<?php echo esc_html($params['name']) ?>]'
                value='<?php echo esc_html($params['value']) ?>'
                id="<?php echo esc_html($params['id']) ?>"
                placeholder="<?php echo esc_html($params['placeholder']) ?>"
                style="width: 400px;"
            <?php echo isset($params['required']) && $params['required'] ? 'required' : '' ?>
            <?php echo isset($params['disabled']) && $params['disabled'] ? 'disabled' : '' ?>
        >
        <?php
    }

    /**
     * @param array $params
     */
    public static function site_id_render($params)
    {
        self::render_text_field($params);
    }

    /**
     * @param array $params
     */
    public static function auth_token_render($params)
    {
        self::render_text_field($params);
    }
}
