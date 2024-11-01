<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Vidjet_Install
 *
 * Fired during plugin Installation.
 *
 * This class defines all code necessary to run during the plugin's installation.
 */
class Vidjet_Install
{
    /**
     * The unique identifier of this plugin.
     *
     * @var string $plugin_name The string used to uniquely identify this plugin.
     */
    protected static $plugin_name;

    /**
     *
     */
    public static function pre_install_vidjet()
    {
        self::$plugin_name = 'vidjet';

        self::load_dependencies();

        if(func_num_args()>0){
           
            $site_id=func_get_arg(0);
            $auth=func_get_arg(1);
            $response=(new Vidjet_Api())->install($site_id,$auth);           
        
        }else{
            $response=(new Vidjet_Api())->install();

        }

        if (Vidjet_Tools::is_woocommerce()) {
            (new Vidjet_Cart())->install();
        }

        wp_clear_scheduled_hook('vidjet_daily_events');
        wp_schedule_event(current_time(time()), 'daily', 'vidjet_daily_events');
        return $response;
    }

    /**
     *
     */
    private static function load_dependencies()
    {
        require_once plugin_dir_path(__DIR__) . 'includes/class-vidjet-autoloader.php';

        new Vidjet_Autoloader(self::$plugin_name);
    }

    /**
     * @param string $plugin
     */
    public static function activation_redirect($plugin)
    {
        if ($plugin === plugin_basename(plugin_dir_path(__DIR__)) . '/vidjet.php') {
     
                $url = admin_url('admin.php?page=vidjet-settings');
        
            exit(wp_redirect($url));
        }
    }
    
    public static function redirection(){
       // if ($plugin === plugin_basename(plugin_dir_path(__DIR__)) . '/vidjet.php') {
              $settings_key = 'vidjet_settings';
  
              $options = get_option($settings_key);
  
              $auth_token = esc_html($options['auth_token']);
  
             if (empty($auth_token)) {
                  $url = admin_url('admin.php?page=vidjet-settings');
              } 
              
              else {
                  $url = 'https://app.vidjet.io/create/campaign/step1?authToken=' . $auth_token;
              }
              
              exit(wp_redirect($url));
       //   }
    }
}
