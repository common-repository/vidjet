<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 */

/**
 * Class Vidjet
 *
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 */
class Vidjet
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var Vidjet_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @var string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @var string $version The current version of the plugin.
     */
    protected $version;

    /**
     * @var string $plugin_base_file
     */
    protected $plugin_base_file;

    /**
     * Vidjet constructor.
     *
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     */
    public function __construct()
    {
        if (defined('VIDJET_VERSION')) {
            $this->version = VIDJET_VERSION;
        } else {
            $this->version = '1.1.2';
        }

        if (defined('VIDJET_PLUGIN_NAME')) {
            $this->plugin_name = VIDJET_PLUGIN_NAME;
        } else {
            $this->plugin_name = 'vidjet';
        }

        $this->plugin_base_file = VIDJET_PLUGIN_FILE;



        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Vidjet_Loader. Orchestrates the hooks of the plugin.
     * - Vidjet_i18n. Defines internationalization functionality.
     * - Vidjet_Admin. Defines all hooks for the admin area.
     * - Vidjet_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     */
    private function load_dependencies()
    {
        require_once plugin_dir_path(__DIR__) . 'includes/class-vidjet-autoloader.php';

        new Vidjet_Autoloader($this->get_plugin_name());

        $this->loader = new Vidjet_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Vidjet_i18n class in order to set the domain and to register the hook
     * with WordPress.
     */
    private function set_locale()
    {
        $plugin_i18n = new Vidjet_i18n($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Vidjet_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_menu', $plugin_admin, 'show_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'plugin_settings');

        if(Vidjet_Tools::is_woocommerce()) {
            $this->loader->add_action('woocommerce_init', $plugin_admin, 'woocommerce_init');
            $this->loader->add_action('save_post_shop_order', $plugin_admin, 'after_update_order');
            $this->loader->add_action('vidjet_daily_events', $plugin_admin, 'delete_old_from_cart_table');
        }

        $this->loader->add_filter(
            'plugin_action_links_' . plugin_basename($this->plugin_base_file),
            $plugin_admin,
            'action_links'
        );
   

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     */
    private function define_public_hooks()
    {
        $plugin_public = new Vidjet_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_footer', $plugin_public, 'add_main_script');

       // Wordpress Ajax php: Adding Products to cart
        add_action('wp_ajax_ql_woocommerce_ajax_add_to_cart', 'ql_woocommerce_ajax_add_to_cart'); 
        add_action('wp_ajax_nopriv_ql_woocommerce_ajax_add_to_cart', 'ql_woocommerce_ajax_add_to_cart');          
        function ql_woocommerce_ajax_add_to_cart() {  
            $productId = apply_filters('ql_woocommerce_add_to_cart_productId', absint($_POST['productId']));
            $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
            $variantId = absint($_POST['variantId']);
            $passed_validation = apply_filters('ql_woocommerce_add_to_cart_validation', true, $productId, $quantity);
            $product_status = get_post_status($productId); 
            if ($passed_validation && WC()->cart->add_to_cart($productId, $quantity, $variantId) && 'publish' === $product_status) { 
                do_action('ql_woocommerce_ajax_added_to_cart', $productId);
                    if ('yes' === get_option('ql_woocommerce_cart_redirect_after_add')) { 
                        wc_add_to_cart_message(array($productId => $quantity), true); 
                    } 
                    WC_AJAX :: get_refreshed_fragments(); 
            } else { 
                $data = array( 
                    'error' => true,
                    'product_url' => apply_filters('ql_woocommerce_cart_redirect_after_error', get_permalink($productId), $productId));
                echo wp_send_json($data);
            }
            wp_die();
        }
            
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return string The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return Vidjet_Loader Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return string The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
