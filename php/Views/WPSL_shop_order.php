<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

class WPSL_shop_order
{

    public static function getWPSLMetaBox(array $options) {

        if (!isset($options) || empty($options)) {
            throw new Exception('Required parameter "options" missing.');
        }

        echo self::getUrlInput($options['href']);
        echo self::getIsPriorityInput();
        echo self::getCustomFieldInputs($options['customFieldCount']);
        echo self::getSubmitButton();
    }

    /**
     * Add hidden input with redirect URL
     * @param string $href
     * @return string
     */
    private static function getUrlInput(string $href) {
        $href = str_replace('&amp;', '&', htmlspecialchars($href));
        return '<input type="hidden" id="WPSL_href" value="' . $href . '" />';
    }

    /**
     * Add isPriority checkbox
     * @return string
     */
    private static function getIsPriorityInput() {

        $field = '';

        $field .= '<div>';
        $field .= '<input type="checkbox" id="WPSL_isPriority" name="WPSL_isPriority">';
        $field .= '<label for="WPSL_isPriority">PRIORITY shipping</label>';
        $field .= '</div>';

        return $field;
    }

    /**
     * Add custom fields in a loop
     * @param int $count
     * @return string
     */
    private static function getCustomFieldInputs(int $count = 3) {

        if (!is_int($count)) {
            throw new Exception('Required parameter "Count" invalid.');
        }

        /**
         * Limit custom field count to 0-3
         */
        if (!($count > 0 && $count < 3)) { $count = 3; }

        $field = '';

        for ($i = 0; $i < $count; $i++) {

            $field .= '<div>';
            $field .= '<label>Custom field:</label>';
            $field .= '<input type="text" value="" class="WPSL_customFieldTitle" placeholder="Title (optional)..." />';
            $field .= '<input type="text" value="" class="WPSL_customFieldValue" placeholder="Value..." />';
            $field .= '</div>';

        }

        return $field;
    }

    private static function getSubmitButton() {
        return '<button type="submit" id="WPSL_submit" title="Print shipping label">Print shipping label</button>';
    }
}