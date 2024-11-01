<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Vidjet_Autoloader
 */
class Vidjet_Autoloader
{
    /**
     * The ID of this plugin.
     *
     * @var string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * @var string $path
     */
    private $path;

    /**
     * Vidjet_Autoloader constructor.
     *
     * @param string $plugin_name The name of this plugin.
     */
    public function __construct($plugin_name)
    {
        $this->plugin_name = $plugin_name;
        $this->path = plugin_dir_path(__DIR__);

        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Auto-load classes on demand to reduce memory consumption.
     *
     * @param string $class Class name.
     */
    public function autoload($class)
    {
        $class = strtolower($class);

        if (0 !== strpos($class, $this->plugin_name)) {
            return;
        }

        $file = $this->get_file_name_from_class($class);
        $classParts = explode('_', $class);

        if ($classParts[1] === 'admin') {
            $path = $this->path . 'admin/';
        } elseif ($classParts[1] === 'public') {
            $path = $this->path . 'public/';
        } else {
            $path = $this->path . 'includes/';
        }
       
        $this->load_file($path . $file);
    }

    /**
     * Take a class name and turn it into a file name.
     *
     * @param string $class Class name.
     *
     * @return string
     */
    private function get_file_name_from_class($class)
    {
        return 'class-' . str_replace('_', '-', $class) . '.php';
    }

    /**
     * Include a class file.
     *
     * @param string $path File path.
     */
    private function load_file($path)
    {
        if ($path && is_readable($path)) {
           
            require_once $path;
        }    
    
}
}
