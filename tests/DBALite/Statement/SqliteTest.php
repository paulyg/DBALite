<?php
/**
 * DBALite - a lightweight PDO based Database Abstraction Layer.
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
class DBALite_Statement_SqliteTest extends DBALite_Statement_CommonTests
{

    public static function setUpBeforeClass()
    {
        $dbfile = DATA_DIR . 'SqliteTest.sqlite';
        $csvfile = DATA_DIR . 'TABLE_Products.csv';
        $pdoObj = new PDO("sqlite:$dbfile");
        self::$phpunitConn = new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($pdoObj);
        self::$dataset = new PHPUnit_Extensions_Database_Dataset_CsvDataSet();
        self::$dataset->addTable('Products', $csvfile);
        $setupOperation = PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT();
        $setupOperation->execute(self::$phpunitConn, self::$dataset);

        $driver = DBALite::factory('sqlite', array('dbname' => $dbfile));
        $sql = 'SELECT * FROM Products WHERE CategoryID = ?';
        self::$dbaliteStmt = $driver->prepare($sql);
        self::$dbaliteDriver = $driver;
    }

    public static function tearDownAfterClass()
    {
        $teardownOperation = PHPUnit_Extensions_Database_Operation_Factory::TRUNCATE();
        $teardownOperation->execute(self::$phpunitConn, self::$dataset);
    }
}
