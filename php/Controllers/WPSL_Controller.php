<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

require_once(__DIR__ . '/../Views/WPSL_settings.php');
require_once(__DIR__ . '/../Views/WPSL_shop_order.php');
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

        add_action('admin_init', [$this, 'initAdmin']);
        add_action('admin_menu', [$this, 'addSettingsPage']);
    }

    /**
     * Initialize Admin related assets
     */
    public function initAdmin() {
        $this->configSettingsPage();
        $this->addPrintingWindow();
    }

    private function configSettingsPage() {

        $optionGroup = 'WPSL_settings';

        $page = 'WPSL_settings';

        $sender_section = 'WPSL_settings_sender';

        register_setting($optionGroup, 'WPSL_sender_company');
        register_setting($optionGroup, 'WPSL_sender_firstName');
        register_setting($optionGroup, 'WPSL_sender_lastName');
        register_setting($optionGroup, 'WPSL_sender_address');
        register_setting($optionGroup, 'WPSL_sender_postCode');
        register_setting($optionGroup, 'WPSL_sender_city');
        register_setting($optionGroup, 'WPSL_sender_state');
        register_setting($optionGroup, 'WPSL_sender_country');

        add_settings_section(
            $sender_section,
            'Sender information',
            '',
            $page
        );


        add_settings_field(
            'WPSL_settings_sender_company',
            'Company name:',
            'WPSL_settings::getSenderInput',
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'company'
            ]
        );

        add_settings_field(
            'WPSL_settings_sender_firstName',
            'First name (optional):',
            'WPSL_settings::getSenderInput',
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'firstName'
            ]
        );

        add_settings_field(
            'WPSL_settings_sender_lastName',
            'Last name (optional):',
            'WPSL_settings::getSenderInput',
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'lastName'
            ]
        );

        add_settings_field(
            'WPSL_settings_sender_address',
            'Address:',
            'WPSL_settings::getSenderInput',
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'address'
            ]
        );

        add_settings_field(
            'WPSL_settings_sender_postCode',
            'Post code:',
            'WPSL_settings::getSenderInput',
            $page,
            $sender_section,
            [
                'type' => 'number',
                'name' => 'postCode'
            ]
        );

        add_settings_field(
            'WPSL_settings_sender_city',
            'City:',
            'WPSL_settings::getSenderInput',
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'city'
            ]
        );

        add_settings_field(
            'WPSL_settings_sender_state',
            'State (optional):',
            'WPSL_settings::getSenderInput',
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'state'
            ]
        );

        add_settings_field(
            'WPSL_settings_sender_country',
            'Country',
            'WPSL_settings::getSenderInput',
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'country'
            ]
        );


    }

    public function addSettingsPage() {

        $options = [
            'title' => 'WPSL settings',
            'menu_title' => 'WPSL settings',
            'capability' => 'manage_options',
            'menu_slug' => 'WPSL_settings',
            'callback' => 'WPSL_settings::getSettingsPage'
        ];

        add_options_page(
            $options['title'],
            $options['menu_title'],
            $options['capability'],
            $options['menu_slug'],
            $options['callback']
        );
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

        WPSL_shop_order::getWPSLMetaBox($options);
    }

    /**
     * Creates window where PDF is shown on
     */
    private function addPrintingWindow() {

        if (!isset($_GET['printWPSL'])) {
            return;
        }

        if (!check_admin_referer('printWPSL')) {
            WPSL_printing::showFatalError(new Exception('Nonce could not be verified.'));
            die();
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
                'company' => get_option('WPSL_sender_company') ?? '',
                'address' => get_option('WPSL_sender_address') ?? '',
                'postCode' => get_option('WPSL_sender_postCode') ?? '',
                'city' => get_option('WPSL_sender_city') ?? '',
                'state' => get_option('WPSL_sender_state') ?? '',
                'country' => get_option('WPSL_sender_country') ?? ''
            ];

            if (!($from['company'] && $from['address'] && $from['postCode'] && $from['city'] && $from['country']))
            {
                WPSL_printing::showFatalError(
                    new Exception(
                        'Sender information missing in settings. Please fill in the required fields.'
                    )
                );
                die();
            }

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