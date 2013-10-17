<?php
/**
 * DBALite - a lightweight, PDO based Database Abstraction Layer
 *
 * @package DBALite
 * @author Paul Garvin <paul@paulgarvin.net>
 * @copyright Copyright 2008-2012 Paul Garvin.
 * @license LGPL-3.0+
 *
 * DBALite is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * DBALite is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with DBALite. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @package DBALiteTest
 */
class DBALite_Driver_MysqlTest extends DBALite_Driver_CommonTests
{
    public static function setUpBeforeClass()
    {
        $config = array(
            'dbname' => 'DBALite_Test',
            'username' => 'dbalite',
            'password' => 'testme',
            'host' => '127.0.0.1'
        );
        self::$dbaliteConn = DBALite::factory('mysql', $config);
    }

    public function getConnection()
    {
        if (!isset($this->pdoConn)) {
            $pdoObj = new PDO('mysql:host=127.0.0.1;dbname=DBALite_Test;charset=utf8', 'dbalite', 'testme');
            //$pdoObj->exec("SET NAMES 'utf8'");
            $this->pdoConn = $this->createDefaultDBConnection($pdoObj);
        }
        
        return $this->pdoConn;
    }

    public function testGetDriverName()
    {
        $dbh = self::$dbaliteConn;
        $this->assertEquals('mysql', $dbh->getDriverName());
    }

    public function testExecute()
    {
        $expected_file = DATA_DIR . 'DataSet-AfterExecute.xml';
        $dbh = self::$dbaliteConn;
        $sql = 'INSERT INTO Products (ProductID, ProductName, SupplierID, CategoryID, '
            . 'QuantityPerUnit, UnitPrice, UnitsInStock, ReorderLevel) VALUES (7, '
            . '\'Tiramisu\', 11, 3, \'1 - 0.5 lb box\', 6.99, 11, 5)';
        $dbh->execute($sql);

        $this->assertDataSetsEqual(
            $this->createXMLDataSet($expected_file),
            $this->getConnection()->createDataSet(array('Products'))
        );
    }

    public function testQuoteString()
    {
        $expected = "'testme'";
        $dbh = self::$dbaliteConn;
        $this->assertEquals($expected, $dbh->quote('testme'));
    }

    public function testQuoteIdentifier()
    {
        $expected = '`firstname`';
        $dbh = self::$dbaliteConn;
        $this->assertEquals($expected, $dbh->quoteIdentifier('firstname'));
    }

    public function testLimit()
    {
        $dbh = self::$dbaliteConn;
        $expected = "SELECT * FROM Products LIMIT 10 OFFSET 5";
        $sql = 'SELECT * FROM Products';
        $sql = $dbh->limit($sql, 10, 5);
        $this->assertEquals($expected, $sql);
    }

    public function testLastInsertId()
    {
        $dbh = self::$dbaliteConn;

        $dbh->execute('TRUNCATE TABLE Cars');
        $data = array(
            'make' => 'Honda',
            'model' => 'Prelude',
            'trim' => 'Type SH',
            'numcyls' => 4,
            'enginesize' => 2.2
        );
        $dbh->insert('Cars', $data);
        $this->assertEquals(1, $dbh->lastInsertId());
    }
}
