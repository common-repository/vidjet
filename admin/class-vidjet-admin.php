<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Vidjet_Admin
 *
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Vidjet_Admin
{
    /**
     * The ID of this plugin.
     *
     * @var string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @var string $version The current version of this plugin.
     */
    private $version;

    /**
     * Vidjet_Admin constructor.
     *
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     *
     */
    public function show_admin_menu()
    {
        add_menu_page(
            esc_html__('Vidjet', $this->plugin_name),
            esc_html__('Vidjet', $this->plugin_name),
            'manage_options',
            $this->plugin_name . '-settings',
            array($this, 'settings_page'),
            'dashicons-video-alt3',
            26
        );

        add_submenu_page(
            $this->plugin_name . '-settings',
            esc_html__('Settings', $this->plugin_name),
            esc_html__('Settings', $this->plugin_name),
            'manage_options',
            $this->plugin_name . '-settings',
            array($this, 'settings_page'),
            3
        );
    }

    
    public function plugin_settings()
    {
        $options = get_option($this->plugin_name . '_settings');

        $site_id = esc_html($options['site_id']);
        $auth_token = esc_html($options['auth_token']);
       
        register_setting($this->plugin_name . '_settings', $this->plugin_name . '_settings');

        add_settings_section(
            $this->plugin_name . '_settings_section',
            esc_html__('Settings', $this->plugin_name),
            array('Vidjet_Admin_Settings', 'settings_section_callback'),
            $this->plugin_name . '_settings'
        );
        add_settings_field(
            'site_id',
            esc_html__('Site Id', $this->plugin_name),
            array('Vidjet_Admin_Settings', 'site_id_render'),
            $this->plugin_name . '_settings',
            $this->plugin_name . '_settings_section',
            array(
                'id' => 'site_id',
                'name' => 'site_id',  
                'value' => empty($site_id) ? '' : $site_id,
                'placeholder' => esc_html__('Site Id', $this->plugin_name),
                'label_for' => 'site_id',
                'disabled' => false,
                'required' => true,

            )
        );

        add_settings_field(
            'auth_token',
            esc_html__('Auth Token', $this->plugin_name),
            array('Vidjet_Admin_Settings', 'auth_token_render'),
            $this->plugin_name . '_settings',
            $this->plugin_name . '_settings_section',
            array(
                'id' => 'auth_token',
                'name' => 'auth_token',  
                'value' => empty($auth_token) ? '' : $auth_token,
                'placeholder' => esc_html__('Auth Token', $this->plugin_name),
                'label_for' => 'auth_token',
                'disabled' => false,
                'required' => true,

            )

        );
       
    }

    /**
     *
     */
    public function woocommerce_init()
    {
        (new Vidjet_Cart())->set_cart_id();
    }

    /**
     * @param $order_id
     */
    public function after_update_order($order_id)
    {
        $vidjet_api = new Vidjet_Api();
        $vidjet_api->order((int)$order_id);
    }

    /**
     *
     */
    public function delete_old_from_cart_table()
    {
        (new Vidjet_Cart())->delete_old();
    }

    /**
     * @param array $actions
     *
     * @return array
     */
    public function action_links($actions)
    {
        $settings_link = '<a href="' . get_site_url() . '/wp-admin/admin.php?page=' . $this->plugin_name . '-settings">';
        $settings_link .= esc_html__('Settings', $this->plugin_name);
        $settings_link .= '</a>';

        array_unshift($actions, $settings_link);

        return $actions;
    }

    /**
     *
     */
    public function settings_page()
    {
        Vidjet_Admin_Settings::output();
    }
}
