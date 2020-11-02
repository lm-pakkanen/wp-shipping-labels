<?php

require_once(__DIR__ . '/../Views/WPSL_printing.php');
require_once(__DIR__ . '/../Models/WPSL_ShippingLabel.php');

/**
 * Main controller class of the WPSL
 */
class WPSL_Controller
{
    /**
     * Call WordPress actions
     */
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'addMetaBox']);
        add_action('admin_init', [$this, 'addPrintingWindow']);
    }

    /**
     * Add meta box to shop_order page
     * Main interface of the plugin
     */
    public function addMetaBox() {

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

        require_once(__DIR__ . '/../Views/WPSL_shop_order.php');

        WPSL_shop_order::getWPSLMetaBox($options);
    }

    /**
     * Creates window where PDF is shown on
     */
    public function addPrintingWindow() {

        if (!isset($_GET['printWPSL'])) {
            return;
        }

        if (!$this->isUserAllowed()) {
            WPSL_printing::showFatalError(new Exception('Current user is not allowed to perform this action.'));
            die();
        }

        if (!isset($_GET['orderID'])) {
            WPSL_printing::showFatalError(new Exception('Required parameter "orderID" is missing.'));
            die();
        }

        if (!preg_match('/^[0-9][0-9]*$/', $_GET['orderID'])) {
            WPSL_printing::showFatalError(new Exception('Required parameter "orderID" is invalid.'));
            die();
        }

        if (isset($_GET['isPriority'])) {
            if (strlen($_GET['isPriority']) !== 0) {
                WPSL_printing::showFatalError(new Exception('Parameter "isPriority" is invalid.'));
                die();
            }
        }

        if (isset($_GET['customFields'])) {

            $customFields = json_decode(stripslashes($_GET['customFields']), true);

            if (!is_array($customFields)) {
                WPSL_printing::showFatalError(new Exception('Parameter "customFields" is not an object.'));
                die();
            }

        }

        try {

            /**
             * Create new WC_Order object
             */
            $order = new WC_Order($_GET['orderID']);


            /**
             * Get shipping state from order
             * @param $order
             * @return string|null
             */
            function getState($order) {
                $states = WC()->countries->get_states( $order->get_shipping_country() );
                return $states[ $order->get_shipping_state() ] ?? null;
            }

            /**
             * Get shipping country from order
             * @param $order
             * @return string
             */
            function getCountry($order) {
                return WC()->countries->countries[ $order->get_shipping_country() ] ?? $order->get_shipping_country();
            }

            $company = $order->get_shipping_company();


            $to = [
                'firstName' => $order->get_shipping_first_name(),
                'lastName' => $order->get_shipping_last_name(),
                'company' => $company ?? null,
                'address' => $order->get_shipping_address_1(),
                'postCode' => $order->get_shipping_postcode(),
                'city' => $order->get_shipping_city(),
                'state' => getState($order),
                'country' => getCountry($order)
            ];

            $from = [
                'company' => 'Harriot Software',
                'address' => 'Yo-Kylä 11 B 14',
                'postCode' => '20540',
                'city' => 'Turku',
                'state' => null,
                'country' => 'Finland'
            ];

            $customFields = json_decode(stripslashes($_GET['customFields']), true) ?? null;

            $options = [
                'isPriority' => isset($_GET['isPriority']),
                'receiver' => $to,
                'sender' => $from,
                'customFields' => $customFields
            ];

            $settings = [

            ];

            try {

                $label = new WPSL_ShippingLabel($options, $settings);
                $label->generatePDF();

                die($label->getPDF());

            } catch (Exception $exception) {
                WPSL_printing::showFatalError($exception);
                die();
            }

        } catch (Exception $exception) {
            WPSL_printing::showFatalError($exception);
            die();
        }
    }

    private function isUserAllowed() {

        global $current_user;

        $authorized = [
            'administrator',
            'shop_manager'
        ];

        return array_intersect($authorized, $current_user->roles);
    }
}