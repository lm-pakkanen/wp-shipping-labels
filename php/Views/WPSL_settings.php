<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

class WPSL_settings
{
    public static function getSettingsPage() {

        echo '<div class="WPSL_settings">';

        echo '<h1>WPSL plugin settings</h1>';

        echo '<div class="spacer"/>';

        echo '<form method="POST" action="options.php">';

        settings_fields('WPSL_settings');

        do_settings_sections('WPSL_settings');

        submit_button();

        echo '</form>';

        echo '</div>';

    }

    public static function getSenderInput(array $args){

        $type = $args['type'] ?? 'text';
        $name = $args['name'] ?? null;

        if (!isset($name) || !$name) { return; }

        $name = 'WPSL_sender_' . $name;

        echo '<input type="' . $type . '" name="' . $name . '" value="' . get_option($name) . '">';
    }
}