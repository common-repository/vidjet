<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Vidjet_Api
 */
class Vidjet_Api
{
    private $api_url = 'https://app-api.vidjet.io';

    /**
     * Vidjet_Api constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return bool
     */
    public function install()
    {
       
        $result = false;

        $vidjet_settings = new Vidjet_Settings();

        $fields = $vidjet_settings->get_wordpress_fields();

        $url = $this->api_url . '/plugins/install';

        $body = array(
            'websiteUrl' => $fields['website_url'],
            'integration' => $fields['integration'],
            'email' => $fields['email'],
        );

        if(func_num_args()>0){
            $body['integrationKeys']['siteId'] = func_get_arg(0);
            $body['integrationKeys']['authToken'] = func_get_arg(1);
          
        }

        if (!empty($fields['companyName'])) {
            $body['companyName'] = $fields['companyName'];
        }

        if (!empty($fields['currency'])) {
            $body['currency'] = $fields['currency'];
        }

        if (!empty($fields['language'])) {
            $body['language'] = $fields['language'];
        }

        if (!empty($fields['firstname'])) {
            $body['firstname'] = $fields['firstname'];
        }

        if (!empty($fields['lastname'])) {
            $body['lastname'] = $fields['lastname'];
        }

        if (!empty($fields['country'])) {
            $body['address']['country'] = $fields['country'];
        }

        if (!empty($fields['address_line1'])) {
            $body['address']['line1'] = $fields['address_line1'];
        }

        if (!empty($fields['state'])) {
            $body['address']['state'] = $fields['state'];
        }

        if (!empty($fields['postal_code'])) {
            $body['address']['postal_code'] = $fields['postal_code'];
        }

        if (!empty($fields['city'])) {
            $body['address']['city'] = $fields['city'];
        }

        $args = array(
            'body' => $body,
        );

        $response = wp_remote_post($url, $args);
        
        
        if ($response['response']['code'] === 200) {
            $result = json_decode(trim($response['body']), true);
            if (!empty($result['siteId']) && !empty($result['authToken'])) {
                $vidjet_settings->set_settings($result['siteId'], $result['authToken']);
            }
        }else{
           $url = admin_url('admin.php?page=vidjet-settings');
         
            $redirect = add_query_arg( array('error'=> $response['response']['code'],'message'=> $response['body']), $url );

            wp_redirect( $redirect );
            exit;
         
        }
        
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        $result = false;

        $vidjet_settings = new Vidjet_Settings();

        $fields = $vidjet_settings->get_settings();

        $url = $this->api_url . '/plugins/uninstall';

        $args = array(
            'body' => array(
                'siteId' => $fields['site_id'],
            ),
        );

        $response = wp_remote_post($url, $args);

        $vidjet_settings->delete_settings();

        return $result;
    }

    /**
     * @param int $order_id
     *
     * @return bool
     */

     
    public function order($order_id)
    {
        $result = false;

        $order = wc_get_order($order_id);

        if (empty($order->get_cart_hash())) {
            return $result;
        }

        $vidjet_cart = new Vidjet_Cart();

        $cart_id = $vidjet_cart->get_cart_id_by_order_id($order_id);

        if (empty($cart_id)) {
            $cart_id = $vidjet_cart->get_cart_id_from_session();
            if (empty($cart_id)) {
                $cart_id = $vidjet_cart->get_cart_id_from_cookie();
            }

            if (!empty($cart_id) && $vidjet_cart->insert($cart_id, $order_id)) {
                $vidjet_cart->delete_cart_id();
            }
        }

        $fields = (new Vidjet_Settings())->get_settings();

        $total_ATI = (int)$order->get_total();
        $total_ET = $total_ATI - (int)$order->get_total_tax();

        $status = isset($_POST['order_status']) ? sanitize_text_field($_POST['order_status']) : '';
        $status = str_replace('wc-', '', $status);

        if (empty($status)) {
            $status = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
            $status = str_replace('mark_', '', $status);
        }
        if (empty($status)) {
            $status = $order->get_status();
        }

        $items =array();
        foreach ($order->get_items() as $item_id => $item ) {

            $one_product;

            $product  = $item->get_product();

            $one_product['productId'] = (string)$item->get_product_id();

            $one_product['name']   = $item->get_name();

            $one_product['qty']  = $item->get_quantity();

            $one_product['variantId'] = (string)$item->get_variation_id();

            $one_product['totalATI'] = (float)$item->get_total();

            $one_product['totalET'] = $one_product['totalATI'] - (float)$item->get_subtotal_tax();

            $items[]=$one_product;
        }
        $url = $this->api_url . '/orders';

        $args = array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => json_encode(
                array(
                    'siteId' => $fields['site_id'],
                    'orderId' => (string)$order_id,
                    'products'=>$items,
                    'cartId' => $cart_id,
                    'totalATI' => $total_ATI,
                    'totalET' => $total_ET,
                    'status' => $status,
                )
            ),
            'method' => 'POST',
            'data_format' => 'body',
        );

      //  $response = wp_remote_post($url, $args);

        return $result;
    }
}
