<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Vidjet_Tools
 */
class Vidjet_Tools
{
    /**
     * Vidjet_Tools constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $message
     * @param string $title
     * @param string $fileName
     */
    public static function add_log_message($message, $title = '', $fileName = 'logs')
    {
        $content = PHP_EOL;
        $content .= $title . ' - ' . print_r($message, true) . PHP_EOL;

        $upload_dir = wp_get_upload_dir();

        $path = $upload_dir['basedir'] . '/vidjet-logs/';

        if (wp_mkdir_p($path)) {
            file_put_contents($path . $fileName . '.log', $content, FILE_APPEND);
        }
    }

    /**
     * @return bool
     */
    public static function is_woocommerce()
    {
        return function_exists('WC') && is_object(WC()) && WC() instanceof \WooCommerce;
    }

    /**
     * @return string
     */
    public static function generate_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
