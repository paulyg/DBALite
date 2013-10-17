<?php
/**
 * DBALite - a lightweight, PDO based Database Abstraction Layer
 *
 * @package DBALiteTest
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
class DBALiteTest extends PHPUnit_Framework_TestCase
{
    protected static $dbname;
    
    public static function setUpBeforeClass()
    {
        self::$dbname = DATA_DIR . 'SqliteTest.sqlite';
    }

    public function testFactoryNormal()
    {
        $driver = 'sqlite';
        $config = array('dbname' => self::$dbname);
        $instance = DBALite::factory($driver, $config);
        $this->assertInstanceOf('DBALite_Driver_Sqlite', $instance);
    }

    public function testFactoryDriverCase()
    {
        $driver = 'SQLite';
        $config = array('dbname' => self::$dbname);
        $instance = DBALite::factory($driver, $config);
        $this->assertInstanceOf('DBALite_Driver_Sqlite', $instance);
    }

    /**
     * @expectedException DBALite_Exception
     */
    public function testFactoryIncorrectDriver()
    {
        $driver = 'db2';
        $config = array('dbname' => 'HelloWorld');
        $instance = DBALite::factory($driver, $config);
    }

    /**
     * @expectedException DBALite_Exception
     */
    public function testFactoryWrongParamOrder()
    {
        $driver = 'sqlite';
        $config = array('dbname' => self::$dbname);
        $instance = DBALite::factory($config, $driver);
    }

    /**
     * @expectedException DBALite_Exception
     */
    public function testFactoryWrongConfigType()
    {
        $driver = 'sqlite';
        $config = new stdClass;
        $config->dbname = self::$dbname;
        $instance = DBALite::factory($driver, $config);
    }

    /**
     * @expectedException DBALite_Exception
     */
    public function testFactoryNoDbname()
    {
        $driver = 'sqlite';
        $config = array('database' => self::$dbname);
        $instance = DBALite::factory($driver, $config);
    }

    public function testGetDrivers()
    {
        $expected = array(
            'mysql' => extension_loaded('pdo_mysql'),
            'pgsql' => extension_loaded('pdo_pgsql'),
            'sqlite' => extension_loaded('pdo_sqlite'),
            //'sqlsrv' => extension_loaded('pdo_sqlsrv')            
        );
        $this->assertEquals($expected, DBALite::getDrivers());
    }
}
