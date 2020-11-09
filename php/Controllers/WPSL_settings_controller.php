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

    /**
     * Admin_init hook
     */
    public function admin_init() {
        $this->configSettingsPage();
    }

    /**
     * Admin_menu hook
     */
    public function admin_menu() {
        $this->addSettingsPage();
    }

    /**
     * Configures settings page
     */
    private function configSettingsPage() {

        $this->registerSettings();

        $page = 'WPSL_settings';

        $senderSection = 'WPSL_settings_sender';
        $pdfSection = 'WPSL_settings_pdf';

        add_settings_section(
            $senderSection,
            'Sender information',
            '',
            $page
        );

        add_settings_section(
            $pdfSection,
            'PDF settings',
            '',
            $page
        );

        $this->addSenderFields($page, $senderSection);
        $this->addPdfFields($page, $pdfSection);

    }

    /**
     * Gets input field for settings field
     * @param $args
     */
    public function getInput($args) {
        try {
            WPSL_settings::getInput($args);
        } catch (Exception $exception) { die($exception->getMessage());}
    }

    /**
     * Adds settings page to control panel menu
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

    private function registerSettings() {

        $optionGroup = 'WPSL_settings';

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

        register_setting($optionGroup, 'WPSL_pdf_spaceBeforeFrom');

        register_setting($optionGroup, 'WPSL_pdf_fontFamily');

        register_setting($optionGroup, 'WPSL_pdf_sender_title_fontSize');
        register_setting($optionGroup, 'WPSL_pdf_sender_content_fontSize');

        register_setting($optionGroup, 'WPSL_pdf_receiver_title_fontSize');
        register_setting($optionGroup, 'WPSL_pdf_receiver_content_fontSize');

    }

    private function addSenderFields(string $page, string $section) {

        add_settings_field(
            'WPSL_sender_company',
            'Company name:',
            [$this, 'getInput'],
            $page,
            $section,
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
            $section,
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
            $section,
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
            $section,
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
            $section,
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
            $section,
            [
                'type' => 'text',
                'name' => 'sender_country'
            ]
        );

    }

    private function addPdfFields(string $page, string $section) {

        add_settings_field(
            'WPSL_pdf_width',
            'Label width (mm):',
            [$this, 'getInput'],
            $page,
            $section,
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
            $section,
            [
                'type' => 'number',
                'name' => 'pdf_height',
                'min' => 50,
                'max' => 500
            ]
        );

        add_settings_field(
            'WPSL_pdf_spaceBeforeFrom',
            'Space height before "From" fields (mm):',
            [$this, 'getInput'],
            $page,
            $section,
            [
                'type' => 'number',
                'name' => 'pdf_spaceBeforeFrom',
                'min' => 0
            ]
        );

        add_settings_field(
            'WPSL_pdf_fontFamily',
            'Font family:',
            [$this, 'getInput'],
            $page,
            $section,
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
            'WPSL_pdf_sender_title_fontSize',
            '"From" section title fontsize:',
            [$this, 'getInput'],
            $page,
            $section,
            [
                'type' => 'number',
                'name' => 'pdf_sender_title_fontSize',
                'min' => 6,
                'max' => 35
            ]
        );

        add_settings_field(
            'WPSL_pdf_sender_content_fontSize',
            '"From" section content fontsize:',
            [$this, 'getInput'],
            $page,
            $section,
            [
                'type' => 'number',
                'name' => 'pdf_sender_content_fontSize',
                'min' => 6,
                'max' => 35
            ]
        );

        add_settings_field(
            'WPSL_pdf_receiver_title_fontSize',
            '"To" section title fontsize:',
            [$this, 'getInput'],
            $page,
            $section,
            [
                'type' => 'number',
                'name' => 'pdf_receiver_title_fontSize',
                'min' => 6,
                'max' => 35
            ]
        );

        add_settings_field(
            'WPSL_pdf_receiver_content_fontSize',
            '"To" section content fontsize:',
            [$this, 'getInput'],
            $page,
            $section,
            [
                'type' => 'number',
                'name' => 'pdf_receiver_content_fontSize',
                'min' => 6,
                'max' => 35
            ]
        );

    }
}