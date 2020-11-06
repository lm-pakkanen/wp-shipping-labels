<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

require_once(__DIR__ . '/../Views/WPSL_settings.php');

class WPSL_settings_controller
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'admin_init']);
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    public function admin_init() {
        $this->configSettingsPage();
    }

    public function admin_menu() {
        $this->addSettingsPage();
    }

    /**
     * Configure settings page
     */
    private function configSettingsPage() {

        $optionGroup = 'WPSL_settings';

        $page = 'WPSL_settings';

        $sender_section = 'WPSL_settings_sender';
        $pdf_section = 'WPSL_settings_pdf';

        register_setting($optionGroup, 'WPSL_sender_company');
        register_setting($optionGroup, 'WPSL_sender_firstName');
        register_setting($optionGroup, 'WPSL_sender_lastName');
        register_setting($optionGroup, 'WPSL_sender_address');
        register_setting($optionGroup, 'WPSL_sender_postCode');
        register_setting($optionGroup, 'WPSL_sender_city');
        register_setting($optionGroup, 'WPSL_sender_state');
        register_setting($optionGroup, 'WPSL_sender_country');

        register_setting($optionGroup, 'WPSL_pdf_width');
        register_setting($optionGroup, 'WPSL_pdf_height');

        register_setting($optionGroup, 'WPSL_pdf_fontFamily');
        register_setting($optionGroup, 'WPSL_pdf_fontStyle');
        register_setting($optionGroup, 'WPSL_pdf_fontSize');

        register_setting($optionGroup, 'WPSL_pdf_fontFamily_title');
        register_setting($optionGroup, 'WPSL_pdf_fontStyle_title');
        register_setting($optionGroup, 'WPSL_pdf_fontSize_title');

        add_settings_section(
            $sender_section,
            'Sender information',
            '',
            $page
        );

        add_settings_section(
            $pdf_section,
            'PDF settings',
            '',
            $page
        );


        /**
         * Sender fields start
         */
        add_settings_field(
            'WPSL_sender_company',
            'Company name:',
            [$this, 'getInput'],
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'sender_company'
            ]
        );

        add_settings_field(
            'WPSL_sender_address',
            'Address:',
            [$this, 'getInput'],
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'sender_address'
            ]
        );

        add_settings_field(
            'WPSL_sender_postCode',
            'Post code:',
            [$this, 'getInput'],
            $page,
            $sender_section,
            [
                'type' => 'number',
                'name' => 'sender_postCode'
            ]
        );

        add_settings_field(
            'WPSL_sender_city',
            'City:',
            [$this, 'getInput'],
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'sender_city'
            ]
        );

        add_settings_field(
            'WPSL_sender_state',
            'State (optional):',
            [$this, 'getInput'],
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'sender_state'
            ]
        );

        add_settings_field(
            'WPSL_sender_country',
            'Country:',
            [$this, 'getInput'],
            $page,
            $sender_section,
            [
                'type' => 'text',
                'name' => 'sender_country'
            ]
        );

        /**
         * PDF fields start
         */
        add_settings_field(
            'WPSL_pdf_width',
            'Label width (mm):',
            [$this, 'getInput'],
            $page,
            $pdf_section,
            [
                'type' => 'number',
                'name' => 'pdf_width',
                'min' => 50,
                'max' => 500
            ]
        );

        add_settings_field(
            'WPSL_pdf_height',
            'Label height (mm):',
            [$this, 'getInput'],
            $page,
            $pdf_section,
            [
                'type' => 'number',
                'name' => 'pdf_height',
                'min' => 50,
                'max' => 500
            ]
        );

        add_settings_field(
            'WPSL_pdf_fontFamily',
            'Font family',
            [$this, 'getInput'],
            $page,
            $pdf_section,
            [
                'type' => 'select',
                'name' => 'pdf_fontFamily',
                'options' => [
                    'Times',
                    'Helvetica',
                    'Courier'
                ]
            ]
        );

        add_settings_field(
            'WPSL_pdf_fontSize_title',
            'Title fontsize',
            [$this, 'getInput'],
            $page,
            $pdf_section,
            [
                'type' => 'number',
                'name' => 'pdf_fontSize_title',
                'min' => 6,
                'max' => 35
            ]
        );

        add_settings_field(
            'WPSL_pdf_fontSize',
            'Content fontsize',
            [$this, 'getInput'],
            $page,
            $pdf_section,
            [
                'type' => 'number',
                'name' => 'pdf_fontSize',
                'min' => 6,
                'max' => 35
            ]
        );

    }

    public function getInput($args) {
        try {
            WPSL_settings::getInput($args);
        } catch (Exception $exception) { die($exception->getMessage());}
    }

    /**
     * Add settings page to control panel menu
     */
    private function addSettingsPage() {

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
}