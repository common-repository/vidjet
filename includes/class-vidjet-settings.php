<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Vidjet_Settings
 */
class Vidjet_Settings
{
    private $settings_key = 'vidjet_settings';

    /**
     * Vidjet_Settings constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return false|mixed|void
     */
    public function get_settings()
    {
        return get_option($this->get_key());
    }

    /**
     *
     */
    public function set_settings($site_id, $auth_token)
    {
        $vidjet_settings = $this->get_settings();

        $fields = array(
            'site_id' => $site_id,
            'auth_token' => $auth_token,
        );

        if (empty($vidjet_settings)) {
            add_option($this->get_key(), $fields);
        } else {
            update_option($this->get_key(), $fields, false);
        }
    }

    /**
     *
     */
    public function delete_settings()
    {
        delete_option($this->get_key());
    }

    /**
     * @return array
     */
    public function get_wordpress_fields()
    {
        $is_woocommerce = Vidjet_Tools::is_woocommerce();
        $woocommerce_data = $this->get_woocommerce_data($is_woocommerce);
        $user_data = $this->get_current_user_data();

        return array(
            'website_url' => get_bloginfo('url'),
            'company_name' => get_bloginfo('name'),
            'integration' => $is_woocommerce ? 'woocommerce' : 'wordpress',
            'country' => $woocommerce_data['country'],
            'state' => '',
            'city' => $woocommerce_data['city'],
            'address_line1' => $woocommerce_data['address'],
            'postal_code' => $woocommerce_data['postal_code'],
            'currency' => $woocommerce_data['currency'],
            'language' => $this->get_language(),
            'email' => $user_data['email'],
            'firstname' => $user_data['firstname'],
            'lastname' => $user_data['lastname'],
        );
    }

    /**
     * @return string
     */
    public function get_key()
    {
        return $this->settings_key;
    }

    /**
     * @return array
     */
    private function get_current_user_data()
    {
        $data = array(
            'email' => '',
            'firstname' => '',
            'lastname' => '',
        );

        try {
            wp_cookie_constants();
            require ABSPATH . WPINC . '/pluggable.php';

            $user = wp_get_current_user();

            if (!is_null($user)) {
                $data = array(
                    'email' => $user->user_email,
                    'firstname' => $user->user_firstname,
                    'lastname' => $user->user_lastname,
                );
            }
        } catch (Exception $e) {
            return $data;
        }

        return $data;
    }

    /**
     * @return string
     */
    private function get_language()
    {
        $language_parts = explode('-', get_bloginfo('language'));

        return empty($language_parts[0]) ? 'en' : $language_parts[0];
    }

    /**
     * @param $is_woocommerce
     *
     * @return array
     */
    private function get_woocommerce_data($is_woocommerce)
    {
        $data = array(
            'country' => '',
            'city' => '',
            'address' => '',
            'postal_code' => '',
            'currency' => '',
        );

        if (!$is_woocommerce) {
            return $data;
        }

        return array(
            'country' => get_option('woocommerce_default_country'),
            'city' => get_option('woocommerce_store_city'),
            'address' => get_option('woocommerce_store_address'),
            'postal_code' => get_option('woocommerce_store_postcode'),
            'currency' => get_option('woocommerce_currency'),
        );
    }
}
