<?php
/**
 * Plugin Name: WordPress shipping labels | WPSL
 * Author: Harriot Software
 * Description: Print custom shipping labels automatically.
 * Version: 0.1
 */

require_once(__DIR__ . '/php/ShippingLabel.php');

/**
 * Register hooks
 */
register_activation_hook(__FILE__, 'activate');
register_deactivation_hook(__FILE__, 'deactivate');
register_uninstall_hook(__FILE__, 'uninstall');

/**
 * Activate plugin
 */
function activate() {

}

/**
 * Deactivate plugin
 */
function deactivate() {

}

/**
 * Uninstall plugin
 */
function uninstall() {

}

/**
 * Main class
 */
class WP_shipping_labels {

    /**
     * Call WordPress actions
     */
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'addPrintingMetaBox']);
        add_action('admin_init', [$this, 'addPrintingWindow']);
        add_action('init', [$this, 'init']);
    }

    /**
     * Initialize other assets
     */
    function init() {
        $this->addScripts();
        $this->addCss();
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


    /**
     * Add meta box to shop_order page
     * Main control of the plugin
     */
    function addPrintingMetaBox() {

        /**
         * Meta box contents
         */
        $contents = [
            $this,
            'getPrintingMetaBoxContent'
        ];

        /**
         * Screens to add box to
         */
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
    function getPrintingMetaBoxContent() {

        /**
         * Post object
         */
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


        /**
         * Add hidden input with redirect URL
         */
        echo '<input type="hidden" id="WPSL_href" value="' . $href . '" />';

        /**
         * Add isPriority checkbox
         */
        echo '<div>';
        echo '<input type="checkbox" id="WPSL_isPriority" name="WPSL_isPriority">';
        echo '<label for="WPSL_isPriority">PRIORITY shipping</label>';
        echo '</div>';


        /**
         * Add custom fields in a loop
         */
        for ($i = 0; $i < $customFieldCount; $i++) {

            $j = $i + 1;

            echo '<div>';
            echo '<label for="WPSL_customFieldTitle' . $j . '">Custom field ' . $j . ':</label>';
            echo "<input type='text' id='WPSL_customFieldTitle{$j}' name='WPSL_customFieldTitle{$j}' ";
            echo "value='' placeholder='Custom field {$j} title...' />";
            echo '<input type="text" id="WPSL_customFieldValue' . $j . '" name="WPSL_customFieldValue' . $j . '" value="" placeholder="Custom field ' . $j . ' data..." />';
            echo '</div>';

        }

        /**
         * Add submit button
         */
        echo '<button type="submit" id="WPSL_submit" title="Print shipping label">Print shipping label</button>';
    }

    /**
     * Creates window where PDF is shown on
     */
    function addPrintingWindow() {

        if (!isset($_GET['printWPSL'])) {
            return;
        }

        if (!isset($_GET['orderID'])) {
            die('Required parameter "orderID" is missing.');
        }

        if (!preg_match('/[0-9]+/', $_GET['orderID'])) {
            die('Required parameter "orderID" is missing.');
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
                'company' => isset($company) ? $company : null,
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

            $optionalFields = [
                [
                    'title' => isset($_GET['customFieldTitle1']) ? $_GET['customFieldTitle1'] : null,
                    'value' => isset($_GET['customFieldValue1']) ? $_GET['customFieldValue1'] : null
                ],
                [
                    'title' => isset($_GET['customFieldTitle2']) ? $_GET['customFieldTitle2'] : null,
                    'value' => isset($_GET['customFieldValue2']) ? $_GET['customFieldValue2'] : null
                ],
                [
                    'title' => isset($_GET['customFieldTitle3']) ? $_GET['customFieldTitle3'] : null,
                    'value' => isset($_GET['customFieldValue3']) ? $_GET['customFieldValue3'] : null
                ]
            ];

            $options = [
                'receiver' => $to,
                'sender' => $from,
                'optionalFields' => $optionalFields
            ];

            $settings = [

            ];

            try {

                $label = new ShippingLabel($options, $settings);
                $label->generatePDF();

            } catch (Exception $exception) {
                die($exception->getMessage());
            }

            die($label->getPDF());

        } catch (Exception $exception) {
            echo 'Error occurred while creating pdf.';
            echo '<br /><br />';
            die($exception->getMessage());
        }
    }

}

new WP_shipping_labels();