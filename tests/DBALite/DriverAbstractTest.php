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
class DBALite_DriverAbstractTest extends PHPUnit_Framework_TestCase
{
	protected static $database;

	public static function setUpBeforeClass()
	{
		$driver = 'sqlite';
		$config = array(
			'dbname' => DATA_DIR . 'SqliteTest.sqlite',
			'options' => array(
				'fetchmode' => 'obj',
				'casefolding' => 'lower',
				'autoquoteidentifiers' => false,
			)
		);
		self::$database = DBALite::factory($driver, $config);
	}

	/**
	 * @covers DBALite_DriverAbstract::setOption
	 * @covers DBALite_DriverAbstract::getOption
	 */
	public function testOptions()
	{
		$dbh = self::$database;
		
		// We change options from that set in setUpBeforeClass().
		// Also set up options for later tests.
		$dbh->setOption('fetchmode', 'assoc');
		$dbh->setOption('casefolding', 'natural');
		$dbh->setOption('autoquoteidentifiers', true);

		$this->assertEquals('assoc', $dbh->getOption('fetchmode'));
		$this->assertEquals('natural', $dbh->getOption('casefolding'));
		$this->assertEquals(true, $dbh->getOption('autoquoteidentifiers'));
	}

	/**
	 * @expectedException DBALite_Exception
	 */
	public function testSetOptionBadParam()
	{
		$dbh = self::$database;
		$dbh->setOption('badparam', 'foobar');
	}

	/**
	 * @expectedException DBALite_Exception
	 */
	public function testGetOptionBadParam()
	{
		$dbh = self::$database;
		$dbh->getOption('badparam');
	}

	public function testCall()
	{
		$dbh = self::$database;
		$ret = $dbh->getAttribute(PDO::ATTR_SERVER_VERSION);
		$this->assertEquals(true, $ret);
	}

	/**
	 * @expectedException DBALite_Exception
	 */
	public function testBadCall()
	{
		$dbh = self::$database;
		$ret = $dbh->munge('someparam');
	}

	public function testSelect()
	{
		$dbh = self::$database;
		$sel = $dbh->select();
		$this->assertInstanceOf('DBALite_Select', $sel);
	}

	public function testWhereString()
	{
		$dbh = self::$database;
		$where = 'testcol = 1';
		$this->assertEquals($where, $dbh->where($where));
	}

	public function testWhereArrayIntValue()
	{
		$dbh = self::$database;
		$where = array('testcol', '=', 1);
		$this->assertEquals('"testcol" = 1', $dbh->where($where));
	}

	public function testWhereArrayStringValue()
	{
		$dbh = self::$database;
		$where = array('testcol', '>', 'one');
		$this->assertEquals('"testcol" > \'one\'', $dbh->where($where));
	}

	public function testWhereArrayStringOperator()
	{
		$dbh = self::$database;
		$where = array('testcol', 'LIKE', '%searchstring%');
		$this->assertEquals('"testcol" LIKE \'%searchstring%\'', $dbh->where($where));
	}

	public function testArrayTypeIndexed()
	{
		$dbh = self::$database;
		$array = array('one', 'two', 'three');
		$this->assertEquals(DBALite::ARRAY_INDEXED, $dbh->arrayType($array));
	}

	public function testArrayTypeAssoc()
	{
		$dbh = self::$database;
		$array = array('one' => 'one1!', 'two' => 'two2@', 'three' => 'three3#');
		$this->assertEquals(DBALite::ARRAY_ASSOC, $dbh->arrayType($array));
	}

	public function testArrayTypeMixed()
	{
		$dbh = self::$database;
		$array = array('one', 'seven' => 'two', 'three');
		$this->assertEquals(DBALite::ARRAY_MIXED, $dbh->arrayType($array));
	}
}
