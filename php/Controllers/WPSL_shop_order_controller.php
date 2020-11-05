<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

require_once(__DIR__ . '/../Views/WPSL_shop_order.php');

/**
 * "Shop_order" page controller
 */
class WPSL_shop_order_controller
{
    /**
     * Call WordPress actions
     */
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
    }

    public function add_meta_boxes() {
        $this->addMetaBox();
    }

    /**
     * Add meta box to shop_order page
     * Main interface of the plugin
     */
    private function addMetaBox() {

        /**
         * Meta box contents
         */
        $contents = [
            $this,
            'getPrintingMetaBoxContent'
        ];

        $screens = ['shop_order'];

        /**
         * Create box
         */
        add_meta_box(
            'woocommerce-order-WPSL',
            __('Print shipping label'),
            $contents,
            $screens,
            'side',
            'default'
        );
    }

    /**
     * Gets meta box content
     */
    public function getPrintingMetaBoxContent() {

        global $post;

        /**
         * Create order object by post ID
         */
        $order = new WC_Order($post->ID);

        /**
         * Label printing URL with order ID
         */
        $href =  wp_nonce_url(admin_url('?printWPSL&orderID=' . $order->get_id()), 'printWPSL');

        /**
         * Number of custom fields to present
         * In the meta box
         */
        $customFieldCount = 3;

        $options = [
            'href' => $href,
            'customFieldCount' => $customFieldCount
        ];

        WPSL_shop_order::getWPSLMetaBox($options);
    }

}