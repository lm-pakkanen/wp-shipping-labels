<?php

if (!defined( 'ABSPATH' )) {
    exit("Direct access denied.");
}

/**
 * Require all controllers in the same file
 */
require_once(__DIR__ . '/WPSL_Controller.php');
require_once(__DIR__ . '/WPSL_settings_controller.php');