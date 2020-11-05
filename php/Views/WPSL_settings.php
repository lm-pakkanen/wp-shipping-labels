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

    public static function getInput(array $args){

        $type = $args['type'] ?? 'text';
        $name = $args['name'] ?? null;
        $min = $args['min'] ?? null;
        $max = $args['max'] ?? null;
        $options = $args['options'] ?? null;

        if (!isset($name) || !$name) {
            throw new Exception('Required parameter "Name" missing.');
        }

        $type = htmlspecialchars($type);
        $name = 'WPSL_' . htmlspecialchars($name);
        $value = htmlspecialchars(get_option($name));

        switch ($type) {

            case 'number':
                $min = htmlspecialchars($min);
                $max = htmlspecialchars($max);

                echo '<input type="' . $type . '" name="' . $name . '" value="' . $value . '" min="' . $min . '" max="' . $max .'">';
                break;

            case 'select':

                if (!isset($options) || !is_array($options)) { return; }

                echo '<select name="' . $name . '" value="test">';

                forEach($options as $option) {

                    $option = htmlspecialchars($option);

                    echo '<option value="' . $option . '">' . $option . '</option>';
                }

                echo '</select>';

                break;

            default:
                echo '<input type="' . $type . '" name="' . $name . '" value="' . $value . '">';
                break;
        }

    }

    public static function getPdfInput(array $args) {



    }
}