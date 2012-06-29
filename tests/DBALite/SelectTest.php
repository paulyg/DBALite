<?php
/**
 * DBALite - the lightweight, PDO based Database Abstraction Layer
 *
 * @package DBALiteTest
 * @author Paul Garvin <paul@paulgarvin.net>
 * @copyright Copyright 2008-2012 Paul Garvin. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-3.0-standalone.html GNU General Public License
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
        $expected = 'SELECT DISTINCT "CustomerID" FROM "Orders"';
        $sel->from('Orders', array('CustomerID'))->distinct();
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testFromAll()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Suppliers"';
        $sel->from('Suppliers');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testFromMultipleAllNoAlias()
    {
        $sel = self::$select;
        $expected = 'SELECT "Products".*, "Suppliers".* FROM "Products", "Suppliers"';
        $sel->from('Products')->from('Suppliers');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testFromMultipleAllWithAlias()
    {
        $sel = self::$select;
        $expected = 'SELECT p.*, s.* FROM "Products" AS p, "Suppliers" AS s';
        $sel->from(array('p' => 'Products'), '*')->from(array('s' => 'Suppliers'), '*');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testFromNamed()
    {
        $sel = self::$select;
        $expected = 'SELECT "SupplierID", "CompanyName", "ContactName" FROM "Suppliers"';
        $sel->from('Suppliers', array('SupplierID', 'CompanyName', 'ContactName'));
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

/*  public function testFromNamedWithAlias()
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
        $expected = 'SELECT "Products"."ProductID", "Products"."ProductName", '
                  . '"Products"."UnitCost", "Suppliers"."SupplierName" FROM "Products" '
                  . 'INNER JOIN "Suppliers" ON "Products"."SupplierID" = "Suppliers"."SupplierID"';
        $prodcols = array('ProductID', 'ProductName', 'UnitCost');
        $supcols = array('SupplierName');
        $sel->from('Products', $prodcols)->join('inner', 'Suppliers', array('Products.SupplierID', 'Suppliers.SupplierID'), $supcols);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testJoinLeft()
    {
        $sel = self::$select;
        $expected = 'SELECT "Products"."ProductID", "Products"."ProductName", '
                  . '"Products"."UnitCost", "Suppliers"."SupplierName" FROM "Products" '
                  . 'LEFT JOIN "Suppliers" ON "Products"."SupplierID" = "Suppliers"."SupplierID"';
        $prodcols = array('ProductID', 'ProductName', 'UnitCost');
        $supcols = array('SupplierName');
        $sel->from('Products', $prodcols)->join('left', 'Suppliers', array('Products.SupplierID', 'Suppliers.SupplierID'), $supcols);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testJoinRight()
    {
        $sel = self::$select;
        $expected = 'SELECT "Products"."ProductID", "Products"."ProductName", '
                  . '"Products"."UnitCost", "Suppliers"."SupplierName" FROM "Products" '
                  . 'RIGHT JOIN "Suppliers" ON "Products"."SupplierID" = "Suppliers"."SupplierID"';
        $prodcols = array('ProductID', 'ProductName', 'UnitCost');
        $supcols = array('SupplierName');
        $sel->from('Products', $prodcols)->join('right', 'Suppliers', array('Products.SupplierID', 'Suppliers.SupplierID'), $supcols);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testJoinFull()
    {
        $sel = self::$select;
        $expected = 'SELECT "Order Details".*, "Products".* FROM "Order Details" '
                  . 'FULL JOIN "Products" ON "Order Details"."ProductID" = "Products"."ProductID"';
        $sel->from('Order Details')->join('full', 'Products', '"Order Details"."ProductID" = "Products"."ProductID"');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testJoinCross()
    {
        $sel = self::$select;
        $expected = 'SELECT "Orders"."ShipCountry", "Shippers"."CompanyName" FROM "Orders" '
                  . 'CROSS JOIN "Shippers"';
        $sel->from('Orders', 'ShipCountry')->join('cross', 'Shippers', null, 'CompanyName');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testJoinNatural()
    {
        $sel = self::$select;
        $expected = 'SELECT "Order Details".*, "Products".* FROM "Order Details" '
                  . 'NATURAL JOIN "Products"';
        $sel->from('Order Details')->join('natural', 'Products');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testJoinTwoInners()
    {
        $sel = self::$select;
        $expected = 'SELECT e."FirstName", e."LastName", e."Title", t."TerritoryDescription" '
                  . 'FROM "Employees" AS e '
                  . 'INNER JOIN "EmployeeTerritories" AS et ON "Employees"."EmployeeID" = "EmployeeTerritories"."EmployeeID"' . PHP_EOL
                  . 'INNER JOIN "Territories" AS t ON "EmployeeTerritories"."TerritoryID" = "Territories"."TerritoryID"';
        $employee_cols = array('FirstName', 'LastName', 'Title');
        $territory_cols = array('TerritoryDescription');
        $employeeterritories_cond = array('Employees.EmployeeID', 'EmployeeTerritories.EmployeeID');
        $territories_cond = array('EmployeeTerritories.TerritoryID', 'Territories.TerritoryID'); 
        $sel->from('Employees AS e', $employee_cols);
        $sel->join('inner', 'EmployeeTerritories AS et', $employeeterritories_cond, null);
        $sel->join('inner', 'Territories AS t', $territories_cond, $territory_cols);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testWhereEquals()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Products" WHERE "ProductID" = 17';
        $sel->from('Products')->where('ProductID', '=', 17);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testWhereNotEquals()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Products" WHERE "ProductID" != 25';
        $sel->from('Products')->where('ProductID', '!=', 25);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testWhereGreaterThan()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Products" WHERE "UnitsOnOrder" > 0';
        $sel->from('Products')->where('UnitsOnOrder', '>', 0);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testWhereLessThan()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Products" WHERE "UnitsInStock" < 10';
        $sel->from('Products')->where('UnitsInStock', '<', 10);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testWhereLessThanEqualTo()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Products" WHERE "UnitPrice" <= 9.99';
        $sel->from('Products')->where('UnitPrice', '<=', 9.99);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testWhereLessThanAndGreatherThan()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Products" WHERE "UnitPrice" > 9.99 AND "UnitPrice" < 30.01';
        $sel->from('Products')->where('UnitPrice', '>', 9.99)->where('UnitPrice', '<', 30.01, 'AND');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testWhereEqualToOrGreaterThanEqualTo()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Products" WHERE "UnitsInStock" = 0 OR "UnitsInStock" >= 100';
        $sel->from('Products')->where('UnitsInStock', '=', 0)->where('UnitsInStock', '>=', 100, 'OR');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testWhereLike()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Products" WHERE "ProductName" LIKE \'%Sir%\'';
        $sel->from('Products')->where('ProductName', 'LIKE', 'Sir');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testWhereNotLike()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Products" WHERE "QuantityPerUnit" NOT LIKE \'%pkgs%\'';
        $sel->from('Products')->where('QuantityPerUnit', 'not like', 'pkgs');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testGroupBy()
    {
        $sel = self::$select;
        $expected = 'SELECT "OrderID", SUM("Quantity") AS TotalUnits '
                  . 'FROM "Order Details" GROUP BY "OrderID"';
        $cols = array('OrderID', array('TotalUnits' => 'SUM("Quantity")'));
        $sel->from('Order Details', $cols)->groupBy('OrderID');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testHaving()
    {
        $sel = self::$select;
        $expected = 'SELECT "OrderID", SUM("Quantity") AS TotalUnits '
                  . 'FROM "Order Details" GROUP BY "OrderID" HAVING "TotalUnits" > 20';
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
        $expected = 'SELECT * FROM "Employees" ORDER BY "FirstName" ASC, "LastName" DESC';
        $sel->from('Employees')->orderBy('FirstName', 'ASC')->orderBy('LastName', 'DESC');
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testLimit()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Orders" LIMIT 25';
        $sel->from('Orders')->limit(25);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testLimitWithOffset()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Orders" LIMIT 25 OFFSET 75';
        $sel->from('Orders')->limit(25, 75);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }

    public function testLimitPage()
    {
        $sel = self::$select;
        $expected = 'SELECT * FROM "Orders" LIMIT 25 OFFSET 50';
        $sel->from('Orders')->limitPage(3, 25);
        $sql = $sel->build();
        $this->assertEquals($expected, $sql);
    }
}
