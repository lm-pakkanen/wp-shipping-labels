<?php

/**
 * Shipping label printing page
 */
class WPSL_printing
{
    public static function showFatalError(Exception $exception) {
        echo 'Error occurred while creating pdf.';
        echo '<br /><br />';
        echo 'Error message:<br />';
        echo $exception->getMessage();
    }
}