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