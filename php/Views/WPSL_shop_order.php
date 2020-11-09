<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

class WPSL_shop_order
{

    /**
     * Get MetaBox for WPSL
     * @param array $options
     * @throws Exception
     */
    public static function getWPSLMetaBox(array $options) {

        if (!isset($options) || empty($options)) {
            throw new Exception('Required parameter "options" missing.');
        }

        echo self::getUrlInput($options['href']);
        echo self::getIsPriorityInput();
        echo self::getLabelInputs();
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
     * Gets 'to' & 'from' fields' label input
     */
    private static function getLabelInputs() {

        $field = '';

        $field .= '<div>';
        $field .= '<label for="WPSL_to">"To" field label:</label>';
        $field .= '<input type="text" id="WPSL_to" name="WPSL_to" value="To:"/>';

        $field .= '<label for="WPSL_from">"From" field label:</label>';
        $field .= '<input type="text" id="WPSL_from" name="WPSL_from" value="From:" />';
        $field .= '</div>';

        return $field;

    }

    /**
     * Add custom fields in a loop
     * @param int $count
     * @return string
     * @throws Exception
     */
    private static function getCustomFieldInputs(int $count) {

        if (!is_int($count) || $count < 0) {
            throw new Exception('Required parameter "Count" invalid.');
        }

        /**
         * Limit custom field count to 0-3
         */
        if ($count > 3) { $count = 3; }

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

    /**
     * Get submit button for MetaBox
     * @return string
     */
    private static function getSubmitButton() {
        return '<button type="submit" id="WPSL_submit" title="Print shipping label">Print WPSL shipping label</button>';
    }
}