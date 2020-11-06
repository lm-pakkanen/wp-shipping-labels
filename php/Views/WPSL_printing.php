<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

/**
 * Shipping label printing page
 */
class WPSL_printing
{
    /**
     * Show fatal error on page
     * @param Exception $exception
     */
    public static function showFatalError(Exception $exception) {

        echo 'Error occurred while creating pdf.';
        echo '<br /><br />';
        echo 'Error message:<br />';
        echo $exception->getMessage();

    }
}