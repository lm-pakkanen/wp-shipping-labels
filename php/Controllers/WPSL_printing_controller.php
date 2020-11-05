<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

require_once(__DIR__ . '/../Views/WPSL_printing.php');

require_once(__DIR__ . '/../Models/WPSL_ShippingLabel.php');

class WPSL_printing_controller
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'admin_init']);
    }

    public function admin_init() {
        $this->addPrintingWindow();
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