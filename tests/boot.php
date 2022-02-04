<?php
/*
 * This file is part of the "Hierarchy" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

$testsDir = str_replace('\\', '/', __DIR__);
$libDir = dirname($testsDir);
$vendorDir = "{$libDir}/vendor";
$autoload = "{$vendorDir}/autoload.php";

if (!is_file($autoload)) {
    die('Please install via Composer before running tests.');
}

putenv('HIERARCHY_TESTS_BASEPATH=' . $testsDir);
putenv('HIERARCHY_LIBRARY_PATH=' . $libDir);
putenv('HIERARCHY_VENDOR_DIR=' . $vendorDir);

error_reporting(E_ALL);

require_once "{$vendorDir}/antecedent/patchwork/Patchwork.php";
require_once $autoload;

if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', $autoload);
    require_once $autoload;
}

require_once __DIR__.'/stubs/wp.php';
require_once __DIR__.'/stubs/branches.php';

unset($libDir, $testsDir, $vendorDir, $autoload);
