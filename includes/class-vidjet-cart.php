<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Vidjet_Cart
 */
class Vidjet_Cart extends Vidjet_Cart_Table
{
    private $cart_id = '';

    /**
     * Vidjet_Cart constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (is_null(WC()->session)) {
            WC()->session = new WC_Session_Handler();
            WC()->session->init();
        }
    }

    /**
     *
     */
    public function set_cart_id()
    {
        $cart_id = $this->get_cart_id_from_cookie();

        if (empty($cart_id)) {
            $this->cart_id = Vidjet_Tools::generate_uuid();

            WC()->session->set('vidjet_woo_cart_id', $this->cart_id);

            setcookie('vidjet_woo_cart_id', $this->cart_id, array('expires' => 0, 'path' => '/'));
        } else {
            $this->cart_id = $cart_id;
        }

        if ($this->get_cart_id_from_session() !== $this->cart_id) {
            WC()->session->set('vidjet_woo_cart_id', $this->cart_id);
        }
    }

    /**
     * @return string
     */
    public function get_cart_id()
    {
        return $this->cart_id;
    }

    /**
     *
     */
    public function delete_cart_id()
    {
        $this->cart_id = '';

        WC()->session->set('vidjet_woo_cart_id', '');

        if (isset($_COOKIE['vidjet_woo_cart_id'])) {
            unset($_COOKIE['vidjet_woo_cart_id']);
            setcookie('vidjet_woo_cart_id', null, -1, '/');
        }
    }

    /**
     * @return string
     */
    public function get_cart_id_from_session()
    {
        return empty(WC()->session->get('vidjet_woo_cart_id')) ? '' : WC()->session->get('vidjet_woo_cart_id');
    }

    /**
     * @return string
     */
    public function get_cart_id_from_cookie()
    {
        return empty($_COOKIE['vidjet_woo_cart_id']) ? '' : sanitize_text_field($_COOKIE['vidjet_woo_cart_id']);
    }
}