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

require_once(__DIR__ . '/php/Controllers/WPSL_Controller.php');

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

        add_action('init', [$this, 'initAssets']);

        $this->startController();
    }

    /**
     * Initialize assets
     */
    function initAssets() {
        $this->addScripts();
        $this->addCss();
    }

    /**
     * Start controller
     */
    private function startController() {
        new WPSL_Controller();
    }

    /**
     * Activate plugin
     */
    private function activate() {

    }

    /**
     * Deactivate plugin
     */
    private function deactivate() {

    }

    /**
     * Uninstall plugin
     */
    private function uninstall() {

    }

    /**
     * Enqueue JavaScript
     */
    function addScripts() {
        wp_register_script('WPSLScript', plugins_url( '/js/WPSL.js', __FILE__));
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