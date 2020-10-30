<?php


class WPSL_shop_order
{

    public static function getWPSLMetaBox(array $options) {

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
    private static function getCustomFieldInputs(int $count) {

        $field = '';

        for ($i = 0; $i < $count; $i++) {

            $j = $i + 1;


            $field .= '<div>';
            $field .= '<label for="WPSL_customFieldTitle' . $j . '">Custom field ' . $j . ':</label>';
            $field .= "<input type='text' id='WPSL_customFieldTitle{$j}' name='WPSL_customFieldTitle{$j}' ";
            $field .= "value='' placeholder='Custom field {$j} title...' />";
            $field .= '<input type="text" id="WPSL_customFieldValue' . $j . '" name="WPSL_customFieldValue' . $j . '" value="" placeholder="Custom field ' . $j . ' data..." />';
            $field .= '</div>';

        }

        return $field;
    }

    private static function getSubmitButton() {
        return '<button type="submit" id="WPSL_submit" title="Print shipping label">Print shipping label</button>';
    }
}