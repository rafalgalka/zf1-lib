<?php

/**
 * ModernWeb
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.modernweb.pl/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@modernweb.pl so we can send you a copy immediately.
 *
 * @category    Modern
 * @package     Modern
 * @subpackage  UnitTests
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2012 ModernWeb (http://www.modernweb.pl)
 * @license     http://www.modernweb.pl/license/new-bsd     New BSD License
 */

/**
 * Set error reporting to the level to which Zend Framework code must comply.
 */
error_reporting(E_ALL | E_STRICT);

$phpUnitVersion = PHPUnit_Runner_Version::id();
if ('@package_version@' !== $phpUnitVersion && version_compare($phpUnitVersion, '3.5.0', '<')) {
    echo 'This version of PHPUnit (' . PHPUnit_Runner_Version::id() . ') is not supported in Zend Framework unit tests.' . PHP_EOL;
    exit(1);
}
unset($phpUnitVersion);

/**
 * Determine the root, library, and tests directories of the framework.
 */
$modernRoot = realpath(dirname(__DIR__));
$modernCoreLibrary = "$modernRoot/library";
$modernCoreTests = "$modernRoot/tests";

/**
 * Load the user-defined test configuration file, if it exists; otherwise, load
 * the default configuration.
 */
if (is_readable($modernCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php')) {
    require_once $modernCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php';
} else {
    require_once $modernCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php.dist';
}

/**
 * Prepend the Modern library/ and tests/ directories to the
 * include_path. This allows the tests to run out of the box and helps prevent
 * loading other copies of the framework code and tests that would supersede
 * this copy.
 */
$path = array(
    $modernCoreLibrary,
    $modernCoreTests,
);
if (defined('TESTS_OB_ENABLED') && null != ZEND_INCLUDE_PATH) {
    $path[] = ZEND_INCLUDE_PATH;
}
$path[] = get_include_path();

set_include_path(implode(PATH_SEPARATOR, $path));

/**
 * Setup autoloading
 */
include __DIR__ . '/_autoload.php';

// check Zend Framework include path
@include_once 'Zend/Version.php';
if (!class_exists('Zend_Version')) {
    die("Can't find Zend Framework libraries. Configure ZEND_INCLUDE_PATH in TestConfiguration.php");
}

if (Zend_Version::compareVersion('1.11.10') > 0) {
    die("Modern Library works with Zend Framework version >= 1.11.10. Your version is: " . Zend_Version::VERSION);
}

/**
 * Start output buffering, if enabled
 */
if (defined('TESTS_OB_ENABLED') && constant('TESTS_OB_ENABLED')) {
    ob_start();
}

/**
 * Unset global variables that are no longer needed.
 */
unset($modernRoot, $modernCoreLibrary, $modernCoreTests, $path);
