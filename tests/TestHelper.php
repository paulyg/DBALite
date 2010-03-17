<?php
/**
 * DBALite - the lightweight, PDO based Database Abstraction Layer
 *
 * This file contains code bootstrap code needed by the DBALite test scripts.
 * - The DBALite source and test directories are added to the include_path.
 * - The DBALite library is loaded.
 * - The PHPUnit_Framework and PHPUnit_Extensions_Database libraries are loaded.
 * - The DBALite_Driver_CommonTests class is loaded.
 * - A constant is set up for the test data directory.
 *
 * @package DBALiteTest
 * @author Paul Garvin <paul@paulgarvin.net>
 * @copyright Copyright 2008, 2009, 2010 Paul Garvin. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-3.0-standalone.html GNU General Public License
 * @link
 *
 * DBALite is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DBALite is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DBALite. If not, see <http://www.gnu.org/licenses/>.
 */

$dbalite_base = (defined(__DIR__)) ? dirname(__DIR__) : dirname(dirname(__FILE__));
$source_dir = $dbalite_base . DIRECTORY_SEPARATOR . 'src';
$test_dir = $dbalite_base . DIRECTORY_SEPARATOR . 'tests';

set_include_path(get_include_path()	. PATH_SEPARATOR . $test_dir . PATH_SEPARATOR . $source_dir);

define('DATA_DIR', $test_dir . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR);

require_once 'DBALite.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'DBALite/Driver/CommonTests.php';
