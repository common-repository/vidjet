<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Vidjet_Cart_Table
 */
class Vidjet_Cart_Table
{
    protected $name = 'vidjet_cart';
    protected $wpdb;

    /**
     * Vidjet_Cart_Table constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->wpdb->prefix . $this->name;
    }

    /**
     * @return bool
     */
    public function is_exist()
    {
        return $this->wpdb->get_var("SHOW TABLES LIKE '" . $this->get_name() . "'") === $this->get_name();
    }

    /**
     *
     */
    public function install()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE `" . $this->get_name() . "` (
            `id` mediumint(9) NOT NULL AUTO_INCREMENT,
            `cart_id` VARCHAR(64) NOT NULL,
            `order_id` VARCHAR(64),
            `created` int(11) NOT NULL,
            `last_modified` int(11) NOT NULL,
            UNIQUE KEY `id` (id),
            PRIMARY KEY `cart_id` (cart_id)
            ) DEFAULT CHARACTER SET utf8mb4;";

        dbDelta($sql);
    }

    /**
     *
     */
    public function uninstall()
    {
        $this->wpdb->query("DROP TABLE `" . $this->get_name() . "`");
    }

    /**
     * @param string $cart_id
     * @param int $order_id
     *
     * @return string|null
     */
    public function insert($cart_id, $order_id = 0)
    {
        $current_time = current_time('timestamp');

        $fields = array(
            'cart_id' => $cart_id,
            'order_id' => $order_id,
            'created' => $current_time,
            'last_modified' => $current_time,
        );

        if ($this->wpdb->insert($this->get_name(), $fields) !== false) {
            return $cart_id;
        }

        return null;
    }

    /**
     * @param string $cart_id
     * @param int $order_id
     *
     * @return string|null
     */
    public function update($cart_id, $order_id)
    {
        $current_time = current_time('timestamp');

        if (
            $this->wpdb->update(
                $this->get_name(),
                array('order_id' => $order_id, 'last_modified' => $current_time),
                array('cart_id' => $cart_id)
            ) !== false
        ) {
            return $cart_id;
        }

        return null;
    }

    /**
     * @param int $order_id
     *
     * @return string|null
     */
    public function get_cart_id_by_order_id($order_id)
    {
        $sql = "SELECT `cart_id` FROM `" . $this->get_name() . "` WHERE `order_id`= %d;";

        $result = $this->wpdb->get_results($this->wpdb->prepare($sql, array($order_id)));
        if (!empty($result[0]->cart_id)) {
            return $result[0]->cart_id;
        }

        return null;
    }

    /**
     *
     */
    public function delete_old()
    {
        $old_records_time = current_time(strtotime('-3 months'));

        $sql = "DELETE FROM `" . $this->get_name() . "` WHERE `last_modified` < %d;";

        $this->wpdb->query($this->wpdb->prepare($sql, array($old_records_time)));
    }
}
