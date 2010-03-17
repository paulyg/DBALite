<?php
/**
 * DBALite - the lightweight, PDO based Database Abstraction Layer
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

require_once 'DBALiteTest.php';
require_once 'DBALite/DriverAbstractTest.php';
require_once 'DBALite/StatementTest.php';
require_once 'DBALite/SelectTest.php';
require_once 'DBALite/Driver/SqliteTest.php';

/**
 * Test all of the non driver specific code in DBALite + SQLite tests.
 *
 * This test suite allows tests to be run even if you do not have a database
 * server running. The serverless database SQLite is used to run all of the
 * tests. You must have the PDO_SQLITE extension enabled. Tests for the follwing
 * classes are run: DBALite, DBALiteDriverAbstract, DBALite_Statement,
 * DBALite_Select, and DBALite_Driver_Sqlite. The singleton functionality in
 * DBALite is not tested because it prevents further tests from being run.
 *
 * To test a specific driver invoke the driver test directly by issuing a
 * command like the following from the 'tests' directory:
 * 'phpunit --bootstrap TestHelper.php DBALite_Driver_Mysql'
 *
 * To test the singleton functionality run the SingletonTest:
 * 'phpunit --bootstrap TestHelper.php SingletonTest.php'
 *
 * @package DBALite_Test
 */
class TestCommon
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite();

		$suite->addTestSuite('DBALiteTest');
		$suite->addTestSuite('DBALite_DriverAbstractTest');
		$suite->addTestSuite('DBALite_StatementTest');
		$suite->addTestSuite('DBALite_SelectTest');
		$suite->addTestSuite('DBALite_Driver_SqliteTest');

		return $suite;
	}
}
?>
