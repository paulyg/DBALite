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
class DBALite_StatementTest extends PHPUnit_Framework_TestCase
{
	protected static $dbaliteDriver;

	protected static $dbaliteStmt;

	public static function setUpBeforeClass()
	{
		$testdb = realpath(DATA_DIR . 'Select_Test_Db.sqlite');
		$driver = DBALite::factory('sqlite', array('dbname' => $testdb));
		$sql = 'SELECT * FROM Products WHERE CategoryID = ?';
		self::$dbaliteStmt = $driver->prepare($sql);
		self::$dbaliteDriver = $driver;
	}


	public function testGetFetchMode()
	{
		$stmt = self::$dbaliteStmt;
		$this->assertEquals('assoc', $stmt->getFetchMode());
		return $stmt;
	}

	/**
	 * @depends testGetFetchMode
	 * @covers DBALite_Statement::execute
	 * @covers DBAlite_statement::fetchRow
	 */
	public function testSetFetchMode(DBALite_Statement $stmt)
	{
		$expected = array(1, 'Chai', 1, 1, '10 boxes x 20 bags', 18, 39, 0, 10, 0);
		$stmt->setFetchMode('num');
		$stmt->execute(array(1));
		$row = $stmt->fetchRow();
		$this->assertEquals($expected, $row);
		return $stmt;
	}

	/**
	 * @depends testSetFetchMode
	 */
	public function testSetFetchModeDirectPdo(DBALite_Statement $stmt)
	{
		$expected = new stdClass;
		$expected->ProductID = 2;
		$expected->ProductName = 'Chang';
		$expected->SupplierID = 1;
		$expected->CategoryID = 1;
		$expected->QuantityPerUnit = '24 - 12 oz bottles';
		$expected->UnitPrice = 19.0;
		$expected->UnitsInStock = 17;
		$expected->UnitsOnOrder = 40;
		$expected->ReorderLevel = 25;
		$expected->Discontinued = 0;
		$stmt->setFetchMode(PDO::FETCH_OBJ);
		$row = $stmt->fetchRow();
		$this->assertEquals($expected, $row);
		return $stmt;
	}

	/**
	 * @expectedException DBALite_Exception
	 */
	public function testSetBadFetchMode()
	{
		$stmt = self::$dbaliteStmt;
		$stmt->setFetchMode('magic');
	}

	/**
	 * Basic DBALite_Statement::fetchRow covered in DBALite_StatementTest::testSetFetchMode
	 * @depends testSetFetchModeDirectPdo
	 */
	public function testFetchRowWithMode(DBALite_Statement $stmt)
	{
		$expected = array(
			'ProductID' => 24,
			0 => 24,
			'ProductName' => 'Guaraná Fantástica',
			1 => 'Guaraná Fantástica',
			'SupplierID' => 10,
			2 => 10,
			'CategoryID' => 1,
			3 => 1,
			'QuantityPerUnit' => '12 - 355 ml cans',
			4 => '12 - 355 ml cans',
			'UnitPrice' => 4.5,
			5 => 4.5,
			'UnitsInStock' => 20,
			6 => 20,
			'UnitsOnOrder' => 0,
			7 => 0,
			'ReorderLevel' => 0,
			8 => 0,
			'Discontinued' => 1,
			9 => 1,			
		);
		$actual = $stmt->fetchRow('both');
		$this->assertEquals($expected, $actual);
		return $stmt;
	}

	/**
	 * @depends testFetchRowWithMode
	 */
	public function testFetchColumn(DBALite_Statement $stmt)
	{
		$expected = 'Sasquatch Ale';
		$actual = $stmt->fetchColumn(1);
		$this->assertEquals($expected, $actual);
		return $stmt;
	}

	/**
	 * @depends testFetchColumn
	 */
	public function testFetchColumnStringArg(DBALite_Statement $stmt)
	{
		$expected = 18;
		$actual = $stmt->fetchColumn('5');
		$this->assertEquals($expected, $actual);
		return $stmt;
	}

	/**
	 * @depends testFetchColumnStringArg
	 */
	public function testFetchObjectStdClass(DBALite_Statement $stmt)
	{
		$skip = $stmt->fetchRow();
		$expected = new stdClass;
		$expected->ProductID = 39;
		$expected->ProductName = 'Chartreuse verte';
		$expected->SupplierID = 18;
		$expected->CategoryID = 1;
		$expected->QuantityPerUnit = '750 cc per bottle';
		$expected->UnitPrice = 18.0;
		$expected->UnitsInStock = 69;
		$expected->UnitsOnOrder = 0;
		$expected->ReorderLevel = 5;
		$expected->Discontinued = 0;
		$actual = $stmt->fetchObject();
		$this->assertEquals($expected, $actual);
		return $stmt;
	}

	/**
	 * @depends testFetchObjectStdClass
	 */
	public function testFetchObjectCustom(DBALite_Statement $stmt)
	{
		$arg1 = 'bar';
		$arg2 = 'baz';
		$expected = new Foo($arg1, $arg2);
		$expected->ProductID = 43;
		$expected->ProductName = 'Ipoh Coffee';
		$expected->SupplierID = 20;
		$expected->CategoryID = 1;
		$expected->QuantityPerUnit = '16 - 500 g tins';
		$expected->UnitPrice = 46;
		$expected->UnitsInStock = 17;
		$expected->UnitsOnOrder = 10;
		$expected->ReorderLevel = 25;
		$expected->Discontinued = 0;
		$foo_args = array($arg1, $arg2);
		$actual = $stmt->fetchObject('Foo', $foo_args);
		$this->assertEquals($expected, $actual);
		return $stmt;
	}

	/**
	 * @depends testFetchObjectCustom
	 */
	public function testBindColumn(DBALite_Statement $stmt)
	{
		$name = '';
		$supplier = null;
		$stmt->bindColumn(3, $supplier);
		$stmt->bindColumn('ProductName', $name);
		$stmt->fetchRow(PDO::FETCH_BOUND);
		$this->assertEquals(16, $supplier);
		$this->assertEquals('Laughing Lumberjack Lager', $name);
		return $stmt;

	}

	/**
	 * @depends testBindColumn
	 */
	public function testBindParam(DBALite_Statement $stmt)
	{
		$stmt->reset();
		$stmt->setFetchMode('num');
		$category = 1;
		$stmt->bindParam(1, $category);
		// Change $category after binding
		$category = 2;
		$stmt->execute();
		$expected = array(3, 'Aniseed Syrup', 1, 2, '12 - 550 ml bottles',10, 13, 70, 25, 0);
		$actual = $stmt->fetchRow();
		$this->assertEquals($expected, $actual);
		return $stmt;
	}

	/**
	 * @depends testBindParam
	 */
	public function testBindValue(DBALite_Statement $stmt)
	{
		$stmt->reset();
		$stmt->bindValue(1, 3);
		$stmt->execute();
		$expected = array(16, 'Pavlova', 7, 3, '32 - 500 g boxes', 17.45, 29, 0, 10, 0);
		$actual = $stmt->fetchRow();
		$this->assertEquals($expected, $actual);
		return $stmt;
	}

	/**
	 * Basic DBALite_Statement::fetchAll covered in DBALite_Driver_Common_Tests::queryAll
	 * @depends testBindValue
	 */
	public function testFetchAllWithMode(DBALite_Statement $stmt)
	{
		$stmt->setFetchMode('assoc');
		$stmt->reset();
		$stmt->bindValue(1, 7);
		$stmt->execute();
		$expected = array(
			array(7, 'Uncle Bob\'s Organic Dried Pears', 3, 7, '12 - 1 lb pkgs.', 30.0, 15, 0, 10, 0),
			array(14, 'Tofu', 6, 7, '40 - 100 g pkgs.', 23.25, 35, 0, 0, 0),
			array(28, 'Rössle Sauerkraut', 12, 7, '25 - 825 g cans', 45.6, 26, 0, 0, 1),
			array(51, 'Manjimup Dried Apples', 24, 7, '50 - 300 g pkgs.', 53.0, 20, 0, 10, 0),
			array(74, 'Longlife Tofu', 4, 7, '5 kg pkg.', 10.0, 4, 20, 5, 0),
		);
		$actual = $stmt->fetchAll('num');
		$this->assertEquals($expected, $actual);
		return $stmt;
	}

	public function testGetSqlDefault()
	{
		$stmt = self::$dbaliteStmt;
		$expected = 'SELECT * FROM Products WHERE CategoryID = ?';
		$this->assertEquals($expected, $stmt->getSql());
	}

	public function testGetSqlPdo()
	{
		$stmt = self::$dbaliteStmt;
		$expected = 'SELECT * FROM Products WHERE CategoryID = ?';
		$this->assertEquals($expected, $stmt->getSql('pdo'));
	}

	public function testGetSqlDbalite()
	{
		$stmt = self::$dbaliteStmt;
		$expected = 'SELECT * FROM Products WHERE CategoryID = ?';
		$this->assertEquals($expected, $stmt->getSql('dbalite'));
	}
}


class Foo
{
	protected $foo1;
	protected $foo2;

	public $ProductID;
	public $ProductName;
	public $SupplierID;
	public $CategoryID;
	public $QuantityPerUnit;
	public $UnitPrice;
	public $UnitsInStock;
	public $UnitsOnOrder;
	public $ReorderLevel;
	public $Discontinued;

	public function __construct($arg1, $arg2)
	{
		$this->foo1 = $arg1;
		$this->foo2 = $arg2;
	}
}
