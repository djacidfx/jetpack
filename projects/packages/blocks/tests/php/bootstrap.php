<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package automattic/jetpack-blocks
 */

/**
 * Load the composer autoloader.
 */
require_once __DIR__ . '/../../vendor/autoload.php';

// Initialize WordPress test environment
\Automattic\Jetpack\Test_Environment::init();
