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

/**
 * @package DBALiteTest
 */
class DBALite_Driver_SqliteTest extends DBALite_Driver_CommonTests
{
	public static function setUpBeforeClass()
	{
		$config = array('dbname' => DATA_DIR . 'SqliteTest.sqlite');
		self::$dbaliteConn = DBALite::factory('sqlite', $config);
	}

	public function getConnection()
	{
		if (!isset($this->pdoConn)) {
			$file = DATA_DIR . 'SqliteTest.sqlite';
			$pdoObj = new PDO("sqlite:$file");
			$this->pdoConn = $this->createDefaultDBConnection($pdoObj, 'sqlite');
		}

		return $this->pdoConn;
	}

	public function testQuoteString()
	{
		$expected = "'testme'";
		$dbh = self::$dbaliteConn;
		$this->assertEquals($expected, $dbh->quote('testme'));
	}

	public function testQuoteIdentifier()
	{
		$expected = '"firstname"';
		$dbh = self::$dbaliteConn;
		$this->assertEquals($expected, $dbh->quoteIdentifier('firstname'));
	}

	public function testLimit()
	{
		$this->markTestIncomplete();
	}

	public function testLastInsertId()
	{
		$this->markTestIncomplete();
	}
}
