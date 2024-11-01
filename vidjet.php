<?php

/**
 * Plugin Name: Vidjet
 * Plugin URI: https://www.vidjet.io/integrations
 * Description: Engage and convert your visitors using smart videos.
 * Version: 1.1.2
 * Requires at least: 4.6
 * Requires PHP: 5.6
 * Author: Vidjet Technologies
 * Author URI: https://www.vidjet.io/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: vidjet
 * Domain Path: /languages
 * Network:
 * Update URI:
 */

/**
 * If this file is called directly, abort.
 */
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
const VIDJET_VERSION = '1.1.2';

/**
 * The unique identifier of this plugin.
 */
const VIDJET_PLUGIN_NAME = 'vidjet';

const VIDJET_PLUGIN_FILE = __FILE__;

/**
 * The code that runs during plugin installation.
 * This action is documented in includes/class-vidjet-install.php
 */
function pre_install_vidjet()
{   
    require_once plugin_dir_path(__FILE__) . 'includes/class-vidjet-install.php';
    ///integration of an account
    if(func_num_args()>0){
        $site_id=func_get_arg(0);
        $auth=func_get_arg(1);
        $response=Vidjet_Install::pre_install_vidjet($site_id,$auth);
        
    }else{
        //creation of a new account
        $response=Vidjet_Install::pre_install_vidjet();

    }
    return $response;
}


/**
* The code that runs during plugin activation.
 * @param string $plugin
 */

function activation_redirect_vidjet($plugin)
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-vidjet-install.php';
    Vidjet_Install::activation_redirect($plugin);
}
add_action('activated_plugin', 'activation_redirect_vidjet');


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-vidjet-deactivator.php
 */
function deactivate_vidjet()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-vidjet-deactivator.php';
    Vidjet_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_vidjet');


/**
 * The function is called in creation of new account or integration of an account .
*/
function CreateWoocommerceKeys(){
    $settings_key = 'vidjet_settings';
  
    $options = get_option($settings_key);
    $auth_token = esc_html($options['auth_token']);

    $site_id = esc_html($options['site_id']);

    $store_url = get_site_url();
    $endpoint  = '/wc-auth/v1/authorize';
    $params    = array(
	    'app_name'     => 'Integrate with vidjet api ',
	    'scope'        => 'read_write',
	    'user_id'      => $site_id,
	    'return_url'   => 'https://app.vidjet.io/create/campaign/step1?authToken=' . $auth_token,
	    'callback_url' => 'https://app-api.vidjet.io/woocommerce/auth-callback'
    );
    exit(wp_redirect($store_url . $endpoint . '?' . http_build_query( $params )));

}

/**
 * The code that runs during Creation of a new account .
*/
function vidjet_create_account() {
    pre_install_vidjet() ;   
    if (Vidjet_Tools::is_woocommerce()) {

        //automatically enable ajax add to cart button 
        $ajaxAddToCart = get_option("woocommerce_enable_ajax_add_to_cart");
        if ($ajaxAddToCart == "no") {
            update_option("woocommerce_enable_ajax_add_to_cart", "yes", false);
        } 
            
        CreateWoocommerceKeys(); 

    } else {
        require_once plugin_dir_path(__FILE__) . 'includes/class-vidjet-install.php';
        Vidjet_Install::redirection();        
    }
      

}
add_action( 'admin_post_create_account', 'vidjet_create_account' );


/**
 * The code that runs during Integration of an account .
*/
function vidjet_integrate_account() {
    
   if ( !empty( $_POST['vidjet_settings']['site_id'] )
        && !empty( $_POST['vidjet_settings']['auth_token'] ))
    {
       $response=pre_install_vidjet($_POST['vidjet_settings']['site_id'],$_POST['vidjet_settings']['auth_token']);  
       if (Vidjet_Tools::is_woocommerce()) {
   
        //automatically enable ajax add to cart button 
        $ajaxAddToCart = get_option("woocommerce_enable_ajax_add_to_cart");
        if ($ajaxAddToCart == "no") {
            update_option("woocommerce_enable_ajax_add_to_cart", "yes", false);
        } 
            
        CreateWoocommerceKeys(); 
   
       } else {
           require_once plugin_dir_path(__FILE__) . 'includes/class-vidjet-install.php';
           Vidjet_Install::redirection();        
       }

    }
}
add_action( 'admin_post_integrate_account', 'vidjet_integrate_account' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path(__FILE__) . 'includes/class-vidjet.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_vidjet()
{
    $plugin = new Vidjet();
    $plugin->run();
}

add_action('plugins_loaded', 'run_vidjet');