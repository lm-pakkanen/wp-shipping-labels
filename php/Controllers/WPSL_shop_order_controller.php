<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

require_once(__DIR__ . '/../Views/WPSL_shop_order.php');

class WPSL_shop_order_controller
{

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
    }

    /**
     * Add_meta_boxes hook
     */
    public function add_meta_boxes() {
        $this->addMetaBox();
    }

    /**
     * Adds meta box to shop_order page
     */
    private function addMetaBox() {

        $contents = [
            $this,
            'getMetaBoxContent'
        ];

        $screens = ['shop_order'];

        add_meta_box(
            'woocommerce-order-WPSL',
            __('WPSL Shipping Labels'),
            $contents,
            $screens,
            'side',
            'default'
        );
    }

    /**
     * Gets meta box content
     */
    public function getMetaBoxContent() {

        global $post;

        $order = new WC_Order($post->ID);

        /**
         * Label printing URL with order ID
         */
        $href =  wp_nonce_url(admin_url('?WPSL_printing&orderID=' . $order->get_id()), 'WPSL_printing');

        $options = [
            'href' => $href,
            'customFieldCount' => 1
        ];

        try {
            WPSL_shop_order::getWPSLMetaBox($options);
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

}