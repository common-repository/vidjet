<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Vidjet_Public
 *
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 */
class Vidjet_Public
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
     * Vidjet_Public constructor.
     *
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
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
    public function add_main_script()
    {
        $options = get_option($this->plugin_name . '_settings');

        if (!empty($options['site_id'])) {

            ?>
            <!-- Vidjet Script - Start -->
            <script>
              (function (d, s, id) {
                if (d.getElementById(id)) return;
                var t = d.getElementsByTagName(s)[0];
                var vis = d.createElement(s);
                vis.id = id;
                vis.src = 'https://app-api.vidjet.io/generator.js?siteId=<?php echo esc_js($options['site_id'])?>';
                t.parentNode.insertBefore(vis, t);
              })(document, 'script', 'vidjet');


              //Vidjet Add to cart function         
              window.vidjetAddToCart = function (product) {

                  jQuery(window).ready(function($) {
                    
                      // The add to cart params are not present.
                      if ( 'undefined' === typeof wc_add_to_cart_params ) {
                          return false;
                      }     
                      
                      var data = {
                          action: 'ql_woocommerce_ajax_add_to_cart',
                          productId: product.productId,
                          quantity: product.qty || 1,
                          variantId: product.variantId || 0
                      };

                      const ajaxAvailable = $ || jQuery;

                      ajaxAvailable.ajax({
                          type: 'post',
                          url: wc_add_to_cart_params.ajax_url,
                          data: data,
                          success: function (response) { 
                              if (response.error) {
                                  return;
                              }

                              //Refresh the cart icon 
                              if (response.fragments) {
                                  ajaxAvailable.each(response.fragments, function(key, value) {
                                      ajaxAvailable(key).replaceWith(value);
                                  });
                              }
                          }, 
                      });   
                  }); 
              }
            <?php
                if (Vidjet_Tools::is_woocommerce()) {
                  $cartUrl = wc_get_cart_url();
                  $checkoutUrl = wc_get_checkout_url();
            ?>
                ///Vidjet get cart and checkout url function         
                window.vidjetCartUrl = "<?php echo $cartUrl ?>"
                window.vidjetcheckoutUrl = "<?php echo $checkoutUrl ?>" 
            <?php
            } ?>
            </script>
            <!-- Vidjet Script - End -->
            <?php
            
        }
 
    }
}
