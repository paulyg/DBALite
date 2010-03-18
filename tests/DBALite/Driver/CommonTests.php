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
abstract class DBALite_Driver_CommonTests extends PHPUnit_Extensions_Database_TestCase
{
	protected static $dbaliteConn;

	protected $pdoConn;

	protected $dataset;

	public function getDataSet()
	{
		if (!isset($this->dataset)) {
			require_once 'PHPUnit/Extensions/Database/DataSet/XmlDataSet.php';
			$file = DATA_DIR . 'DataSet-Initial.xml';
			$this->dataset = new PHPUnit_Extensions_Database_DataSet_XmlDataSet($file);
		}

        return $this->dataset;
	}

	public function testPrepare()
	{
		$this->markTestIncomplete();
	}

	public function testQuery()
	{
		$this->markTestIncomplete();
	}

	public function testInsert()
	{
		$expected_file = DATA_DIR . 'DataSet-AfterInsert.xml';
		$dbh = self::$dbaliteConn;
		$data = array(
			'ProductID' => 7,
			'ProductName' => 'Teatime Chocolate Biscuits',
			'SupplierID' => 8,
			'CategoryID' => 3,
			'QuantityPerUnit' => '10 boxes x 12 pcs',
			'UnitPrice' => 9.25
		);
		$dbh->insert('Products', $data);
		$this->assertDataSetsEqual(
			$this->createXMLDataSet($expected_file),
			$this->getConnection()->createDataSet(array('Products'))
		);
	}

	public function testPrepareInsert()
	{
		$expected_file = DATA_DIR . 'DataSet-AfterInsertMultiple.xml';
		$dbh = self::$dbaliteConn;
		$cols = array(
			'ProductID',
			'ProductName',
			'SupplierID',
			'CategoryId',
			'QuantityPerUnit',
			'UnitPrice'
		);
		$stmt = $dbh->prepareInsert('Products', $cols);
		$this->assertType('DBALite_Statement', $stmt);

		$data1 = array(7, 'Ipoh Coffee', 20, 1, '16 - 500 g tins', 46.0);
		$stmt->execute($data1);

		$data2 = array(8, 'Filo Mix', 24, 5, '16 - 2 kg boxes', 7.99);
		$stmt->execute($data2);

		$this->assertDataSetsEqual(
			$this->createXMLDataSet($expected_file),
			$this->getConnection()->createDataSet(array('Products'))
		);

		return $stmt;
	}

	/**
	 * @depends testPrepareInsert
	 * @expectedException DBALite_Exception
	 */
	public function testPrepareInsertBadParamType(DBALite_Statement $stmt)
	{
		$bad_data = array(
			'ProductID' => 8,
			'ProductName' => 'Filo Mix',
			'SupplierID' => 24,
			'CategoryId' => 5,
			'QuantityPerUnit' => '16 - 2 kg boxes',
			'UnitPrice' => 7.99
		);
		$stmt->execute($bad_data);
	}

	public function testUpdate()
	{
		$expected_file = DATA_DIR . 'DataSet-AfterUpdate.xml';
		$dbh = self::$dbaliteConn;
		$data = array('UnitPrice' => 43.75);
		$dbh->update('Products', $data, 'ProductID = 3');
		$this->assertDataSetsEqual(
			$this->createXMLDataSet($expected_file),
			$this->getConnection()->createDataSet(array('Products'))
		);
	}

	public function testPrepareUpdate()
	{
		$expected_file = DATA_DIR . 'DataSet-AfterUpdateMultiple.xml';
		$dbh = self::$dbaliteConn;
		$cols = array(
			'QuantityPerUnit',
			'UnitPrice'
		);
		$stmt = $dbh->prepareUpdate('Products', $cols, 'ProductID = ?');
		$this->assertType('DBALite_Statement', $stmt);

		$data1 = array('48 - 5.5 oz jars', 23.5, 2);
		$stmt->execute($data1);

		$data2 = array('15 - 825 g cans', 32.25, 4);
		$stmt->execute($data2);

		$this->assertDataSetsEqual(
			$this->createXMLDataSet($expected_file),
			$this->getConnection()->createDataSet(array('Products'))
		);

		return $stmt;
	}

	/**
	 * @depends testPrepareUpdate
	 * @expectedException DBALite_Exception
	 */
	public function testPrepareUpdateBadParams(DBALite_Statement $stmt)
	{
		$bad_data = array(
			'QuantityPerUnit' => '15 - 825 g cans',
			'UnitPrice' => 32.25,
			'ProductID' => 4
		);
		$stmt->execute($bad_data);
	}

	public function testDelete()
	{
		$expected_file = DATA_DIR . 'DataSet-AfterDelete.xml';
		$dbh = self::$dbaliteConn;
		$dbh->delete('Products', 'ProductID = 6');
		$this->assertDataSetsEqual(
			$this->createXMLDataSet($expected_file),
			$this->getConnection()->createDataSet(array('Products'))
		);
	}

	public function testPrepareDelete()
	{
		$expected_file = DATA_DIR . 'DataSet-AfterDeleteMultiple.xml';
		$dbh = self::$dbaliteConn;
		$stmt = $dbh->prepareDelete('Products', 'ProductID = ?');
		$this->assertType('DBALite_Statement', $stmt);

		$stmt->execute(5);
		$stmt->execute(array(6));

		$this->assertDataSetsEqual(
			$this->createXMLDataSet($expected_file),
			$this->getConnection()->createDataSet(array('Products'))
		);
	}

	public function testQueryAll()
	{
		$expected = array(
			array(
				'ProductID' => 1,
				'ProductName' => 'Chai',
				'SupplierID' => 1,
				'CategoryID' => 1,
				'QuantityPerUnit' => '10 boxes x 20 bags',
				'UnitPrice' => 18.0
			),
			array(
				'ProductID' => 5,
				'ProductName' => 'Côte de Blaye',
				'SupplierID' => 18,
				'CategoryID' => 1,
				'QuantityPerUnit' => '12 - 75 cl bottles',
				'UnitPrice' => 263.5
			),
		);

		$dbh = self::$dbaliteConn;
		$sql = 'SELECT * FROM Products WHERE CategoryID = 1';
		$result = $dbh->queryAll($sql);

		$this->assertEquals($expected, $result);
	}

	public function testQueryOne()
	{
		$dbh = self::$dbaliteConn;
		$sql = 'SELECT COUNT(*) FROM Products';
		$result = $dbh->queryOne($sql);

		$this->assertEquals('6', $result);
	}

	public function testExecute()
	{
		$this->markTestIncomplete();
	}

	public function testQuoteInt()
	{
		$expected = 654;
		$dbh = self::$dbaliteConn;
		$this->assertEquals($expected, $dbh->quote(654));
	}

	public function testQuoteFloat()
	{
		$expected = 220.221;
		$dbh = self::$dbaliteConn;
		$this->assertEquals($expected, $dbh->quote(220.221));
	}

	public function testQuoteQmarkPlaceholder()
	{
		$expected = '?';
		$dbh = self::$dbaliteConn;
		$this->assertEquals($expected, $dbh->quote('?'));
	}

	public function testQuoteNamedPlaceholder()
	{
		$expected = ':firstname';
		$dbh = self::$dbaliteConn;
		$this->assertEquals($expected, $dbh->quote(':firstname'));
	}
}
