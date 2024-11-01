<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Vidjet_Deactivator
 *
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class Vidjet_Deactivator
{
    /**
     *
     */
    public static function deactivate()
    {
        wp_clear_scheduled_hook('vidjet_daily_events');
    }
}
