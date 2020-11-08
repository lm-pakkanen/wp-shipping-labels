<?php
/**
 * Plugin Name: WPSL | WordPress Shipping Labels
 * Author: Harriot Software
 * Description: WooCommerce extension for printing custom shipping labels.
 * Version: 1.0
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

require_once(__DIR__ . '/php/Controllers/index.php');

class WP_shipping_labels {

    public function __construct()
    {
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);
        register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall']);

        add_action('init', [$this, 'init']);

        $this->startControllers();
    }

    /**
     * Init hook
     */
    function init() {
        $this->addScripts();
        $this->addCss();
    }

    /**
     * Starts controllers
     */
    private function startControllers() {
        new WPSL_settings_controller();
        new WPSL_shop_order_controller();
        new WPSL_printing_controller();
    }

    /**
     * Activate plugin
     */
    public static function activate() {

        if (empty(get_option('WPSL_pdf_width')) || empty(get_option('WPSL_pdf_height'))) {

            update_option('WPSL_pdf_width', 107);
            update_option('WPSL_pdf_height', 225);

        }

        if (empty(get_option('WPSL_pdf_fontFamily'))) {
            update_option('WPSL_pdf_fontFamily', 'Times');
        }

        if (empty(get_option('WPSL_pdf_fontSize_title'))) {
            update_option('WPSL_pdf_fontSize_title', 14);
        }

        if (empty(get_option('WPSL_pdf_fontSize'))) {
            update_option('WPSL_pdf_fontSize', 12);
        }

    }

    /**
     * Deactivates plugin
     */
    public static function deactivate() {

    }

    /**
     * Uninstalls plugin
     */
    public static function uninstall() {

    }

    /**
     * Enqueues JavaScript
     */
    function addScripts() {
        wp_register_script('WPSLScript', plugins_url( '/js/WPSL.js', __FILE__), ['jquery']);
        wp_enqueue_script('WPSLScript');
    }

    /**
     * Enqueues CSS
     */
    function addCss() {
        wp_register_style('WPSLCss', plugins_url( '/css/index.css', __FILE__));
        wp_enqueue_style('WPSLCss');
    }
}

new WP_shipping_labels();