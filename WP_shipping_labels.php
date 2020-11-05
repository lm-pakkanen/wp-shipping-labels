<?php
/**
 * Plugin Name: WordPress shipping labels | WPSL
 * Author: Harriot Software
 * Description: Print custom shipping labels automatically.
 * Version: 0.1
 * Requires at least: 5.5
 * Requires PHP: 7.4
 * Text Domain: WP_shipping_labels
 * Domain path: /languages
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

/**
 * Require controllers
 */
require_once(__DIR__ . '/php/Controllers/index.php');

/**
 * Main class
 */
class WP_shipping_labels {

    /**
     * Register hooks
     */
    public function __construct()
    {
        register_activation_hook(__FILE__, 'activate');
        register_deactivation_hook(__FILE__, 'deactivate');
        register_uninstall_hook(__FILE__, 'uninstall');

        add_action('init', [$this, 'init']);

        $this->startControllers();
    }

    /**
     * Initialize plugin
     */
    function init() {
        $this->addScripts();
        $this->addCss();
    }

    /**
     * Start controller
     */
    private function startControllers() {
        new WPSL_settings_controller();
        new WPSL_shop_order_controller();
        new WPSL_printing_controller();
    }

    /**
     * Activate plugin
     */
    public function activate() {
        // TODO: Add default values for document size
    }

    /**
     * Deactivate plugin
     */
    public function deactivate() {

    }

    /**
     * Uninstall plugin
     */
    public function uninstall() {
        // TODO: Remove DB values
    }

    /**
     * Enqueue JavaScript
     */
    function addScripts() {
        wp_register_script('WPSLScript', plugins_url( '/js/WPSL.js', __FILE__), ['jquery']);
        wp_enqueue_script('WPSLScript');
    }

    /**
     * Enqueue CSS
     */
    function addCss() {
        wp_register_style('WPSLCss', plugins_url( '/css/index.css', __FILE__));
        wp_enqueue_style('WPSLCss');
    }
}

new WP_shipping_labels();