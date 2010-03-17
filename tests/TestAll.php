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

require_once 'TestCommon.php';
require_once 'DBALite/Driver/MysqlTest.php';
require_once 'DBALite/Driver/PgsqlTest.php';
require_once 'DBALite/Driver/MssqlTest.php';
require_once 'SingletonTest.php';

/**
 * @package DBALite_Test
 */
class TestAll
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite();

		$suite->addTestSuite(TestCommon::suite());
		$suite->addTestSuite('DBALite_Driver_MysqlTest');
		$suite->addTestSuite('DBALite_Driver_PgsqlTest');
		$suite->addTestSuite('DBALite_Driver_MssqlTest');
		$suite->addTestSuite('SingletonTest');

		return $suite;
	}
}
?>
