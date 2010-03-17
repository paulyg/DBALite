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
class DBALite_SelectTest extends PHPUnit_Framework_TestCase
{
	protected static $driver;
	protected static $select;

	public static function setUpBeforeClass()
	{
		$testdb = realpath(DATA_DIR . 'Select_Test_Db.sqlite');
		self::$driver = DBALite::factory('sqlite', array('dbname' => $testdb));
		self::$select = new DBALite_Select(self::$driver);
	}

	protected function setUp()
	{
		self::$select->reset();
	}

	public function testDistinct()
	{
		$sel = self::$select;
		$expected = "SELECT DISTINCT \"CustomerID\"\nFROM \"Orders\"";
		$sel->from('Orders', array('CustomerID'))->distinct();
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testFromAll()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Suppliers\"";
		$sel->from('Suppliers');
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testFromNamed()
	{
		$sel = self::$select;
		$expected = 'SELECT "SupplierID", "CompanyName", "ContactName"' .
					"\nFROM \"Suppliers\"";
		$sel->from('Suppliers', array('SupplierID', 'CompanyName', 'ContactName'));
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

/*	public function testFromNamedWithAlias()
	{
		$sel = self::$select;
		$expected = 'SELECT s."SupplierID", s."CompanyName", s."ContactName"' .
					"\nFROM \"Suppliers\" AS s";
		$sel->from(array('s' => 'Suppliers'), array('SupplierID', 'CompanyName', 'ContactName'));
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	} */

	public function testJoinInner()
	{
		$sel = self::$select;
		$expected = 'SELECT "Categories"."CategoryName", "Products".*' .
					"\nFROM \"Categories\"" .
					"\nINNER JOIN \"Products\" ON \"Categories\".\"CategoryID\" = \"Products\".\"CategoryID\"";
		$sel->from('Categories', 'CategoryName')->join('inner', 'Products', 'Categories.CategoryID = Products.CategoryID');
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testJoinLeft()
	{
		$sel = self::$select;
		$expected = 'SELECT "Products".*, "Suppliers"."CompanyName"' .
		            "\nFROM \"Products\"" .
					"\nLEFT JOIN \"Suppliers\" ON \"Products\".\"SuppliersID\" = \"Suppliers\".\"SupplierID\"";
		$sel->from('Products')->join('left', 'Suppliers', 'Products.SupplierID = Suppliers.SupplierID', array('CompanyName'));
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testJoinRight()
	{
		$sel = self::$select;
		$expected = 'SELECT "Products".*, "Suppliers.SupplierID"' .
		            "\nFROM \"Suppliers\"" .
					"\nRIGHT JOIN \"Products\" ON \"Suppliers\".\"SupplierID\" = \"Products\".\"SupplierID\"";;
		$sel->from('Suppliers', array('SupplierID'))->join('right', 'Products', 'Suppliers.SupplierID = Products.SupplierID', '*');
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testJoinFull()
	{
		$sel = self::$select;
		$expected = 'SELECT "Order Details".*, "Products.*"' .
		            "\nFROM \"Order Details\"" .
					"\nFULL JOIN \"Products\" ON \"Order Details\".\"ProductID\" = \"Products\".\"ProductID\"";
		$sel->from('Order Details')->join('full', 'Products', 'Order Details.ProductID = Products.ProductID');
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testJoinCross()
	{
		$this->markTestIncomplete();
		$sel = self::$select;
		$expected = '';
		$sel->from()->join();
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testJoinNatural()
	{
		$this->markTestIncomplete();
		$sel = self::$select;
		$expected = '';
		$sel->from()->join();
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testJoinTwoInners()
	{
		$this->markTestIncomplete();
		$sel = self::$select;
		$expected = '';
		$sel->from()->join();
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testJoinLeftAndNatural()
	{
		$this->markTestIncomplete();
		$sel = self::$select;
		$expected = '';
		$sel->from()->join();
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testJoinTripple()
	{
		$sel = self::$select;
		$expected = 'SELECT "Employees"."FirstName", "Employees"."LastName", "Employees"."Title", ' .
		            '"Territories"."TerritoryDescription"' .
					"\nFROM \"Employees\"" .
					"\nINNER JOIN \"EmployeeTerritories\" ON \"Employees\".\"EmployeeID\" = \"EmployeeTerritories\".\"EmployeeID\"" .
					"\nINNER JOIN \"Territories\" ON \"EmployeeTerritories\".\"TerritoryID\" = \"Territories\".\"TerritoryID\"";
		$employee_cols = array('FirstName', 'LastName', 'Title');
		$territory_cols = array('TerritoryDescription');
		$sel->from('Employees', $employee_cols)->join('inner', 'EmployeeTerritories', $territory_cols);
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testWhereEquals()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Products\"\nWHERE \"ProductID\" = 17";
		$sel->from('Products')->where('ProductID', '=', 17);
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testWhereNotEquals()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Products\"\nWHERE \"ProductID\" != 25";
		$sel->from('Products')->where('ProductID', '!=', 25);
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testWhereGreaterThan()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Products\"\nWHERE \"UnitsOnOrder\" > 0";
		$sel->from('Products')->where('UnitsOnOrder', '>', 0);
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testWhereLessThan()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Products\"\nWHERE \"UnitsInStock\" < 10";
		$sel->from('Products')->where('UnitsInStock', '<', 10);
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testWhereLessThanEqualTo()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Products\"\nWHERE \"UnitPrice\" <= 9.99";
		$sel->from('Products')->where('UnitPrice', '<=', 9.99);
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testWhereLessThanAndGreatherThan()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Products\"\nWHERE \"UnitPrice\" > 9.99 AND \"UnitPrice\" < 30.01";
		$sel->from('Products')->where('UnitPrice', '>', 9.99)->where('UnitPrice', '<', 30.01, 'AND');
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testWhereEqualToOrGreaterThanEqualTo()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Products\"\nWHERE \"UnitsInStock\" = 0 OR \"UnitsInStock\" >= 100";
		$sel->from('Products')->where('UnitsInStock', '=', 0)->where('UnitsInStock', '>=', 100, 'OR');
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testWhereLike()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Products\"\nWHERE \"ProductName\" LIKE '%Sir%'";
		$sel->from('Products')->where('ProductName', 'LIKE', 'Sir');
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testWhereNotLike()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Products\"\nWHERE \"QuantityPerUnit\" NOT LIKE '%pkgs%'";
		$sel->from('Products')->where('QuantityPerUnit', 'not like', 'pkgs');
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testGroupBy()
	{
		$sel = self::$select;
		$expected = "SELECT \"OrderID\", SUM(\"Quantity\") AS TotalUnits" .
		            "\nFROM \"Order Details\"\nGROUP BY \"OrderID\"";
		$cols = array('OrderID', array('TotalUnits' => 'SUM("Quantity")'));
		$sel->from('Order Details', $cols)->groupBy('OrderID');
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testHaving()
	{
		$sel = self::$select;
		$expected = "SELECT \"OrderID\", SUM(\"Quantity\") AS TotalUnits" .
		            "\nFROM \"Order Details\"\nGROUP BY \"OrderID\"\nHAVING \"TotalUnits\" > 20";
		$cols = array('OrderID', array('TotalUnits' => 'SUM("Quantity")'));
		$sel->from('Order Details', $cols)->groupBy('OrderID');
		$sel->having(array('TotalUnits', '>', 20));
		$sql = $sel->build();
		$res = self::$driver->query($sql);
		$this->assertEquals($expected, $sql);
	}

	public function testOrderBy()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Employees\"\nORDER BY \"FirstName\" ASC, \"LastName\" DESC";
		$sel->from('Employees')->orderBy('FirstName', 'ASC')->orderBy('LastName', 'DESC');
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testLimit()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Orders\"\nLIMIT 25";
		$sel->from('Orders')->limit(25);
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testLimitWithOffset()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Orders\"\nLIMIT 25 OFFSET 75";
		$sel->from('Orders')->limit(25, 75);
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}

	public function testLimitPage()
	{
		$sel = self::$select;
		$expected = "SELECT *\nFROM \"Orders\"\nLIMIT 25 OFFSET 50";
		$sel->from('Orders')->limitPage(3, 25);
		$sql = $sel->build();
		$this->assertEquals($expected, $sql);
	}
}
