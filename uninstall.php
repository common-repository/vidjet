<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}
$plugin_name = 'vidjet';

require_once plugin_dir_path(__FILE__) . 'includes/class-vidjet-autoloader.php';

new Vidjet_Autoloader($plugin_name);

(new Vidjet_Api())->uninstall();

if (Vidjet_Tools::is_woocommerce()) {
    (new Vidjet_Cart())->uninstall();
}