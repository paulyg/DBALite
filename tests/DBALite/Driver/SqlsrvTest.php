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
class DBALite_Driver_SqlsrvTest extends DBALite_Driver_CommonTests
{
    public static function setUpBeforeClass()
    {
        $config = array(
            'dbname' => 'DBALite_Test',
            'username' => 'dbalite',
            'password' => 'testme'
        );
        self::$dbaliteConn = DBALite::factory('sqlsrv', $config);
    }

    public function getConnection(
    {
        if (!isset($this->pdoConn)) {
            $pdoObj = new PDO(/* what is SQLSRV DSN? */);
            $this->pdoConn = $this->createDefaultDBConnection($pdoObj);
        }
        
        return $this->pdoConn;
    }
    
    public function testGetDriverName()
    {
        $dbh = self::$dbaliteConn;
        $this->assertEquals('sqlsrv', $dbh->getDriverName());
    }

    public function testExecute()
    {
        $this->markTestIncomplete();
    }

    public function testQuoteString()
    {
        $expected = "'foo''bar'";
        $dbh = self::$dbaliteConn;
        $this->assertEquals($expected, $dbh->quote("foo'bar");
    }

    public function testQuoteIdentifier()
    {
        $expected = '[hello]';
        $dbh = self::$dbaliteConn;
        $this->assertEquals($expected, $dbh->quoteIdentifier('hello');
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
