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
 * The test suite for DBALite uses the PHPUnit testing framework. The tests
 * were writen using PHPUnit 3.5. Compatability with older versions can not
 * be guarenteed. If you have an older version you are encouraged to upgrade.
 * To install PHPUnit simply run the following from the command line.
 *
 * pear config-set auto_discover 1
 * pear install pear.phpunit.de/PHPUnit
 * - You may have to run the commands as root or administrator (sudo or similar).
 *
 * This test suite will automatically add the tests for a database driver if
 * the PDO extension for that driver is loaded and it is able to connect to
 * the test database. SQLite tests are added as long as pdo_sqlite extension
 * is loaded. SQLite tests databases are provided with the test suite. For the
 * other databases you will need to setup a test database before you can run
 * tests on that driver. 
 *
 * Run the test suite with the following command:
 * 'phpunit --bootstrap TestHelper.php DBALiteTestSuite'
 *
 * MySQL test database setup:
 * 1) Log into the MySQL shell client with root access: 'mysql -u root -p'.
 * 2) "CREATE DATABASE DBALite_Test;"
 * 3) "CREATE USER 'dbalite'@'localhost' IDENTIFIED BY 'testme';"
 * 4) "GRANT ALL ON DBALite_Test.* TO 'dbalite'@'localhost';"
 * 5) Run the CREATE TABLE statements located in 'tests/Data/Mysql_Test_Db_Schema.sql'.
 *
 * PostgreSQL test database setup:
 * 1) Start the PostgreSQL command line client with superuser privlege: 'sudo -u postgres psql'.
 * 2) "CREATE DATABASE DBALiteTest;"
 * 3) "CREATE USER dbalite WITH PASSWORD 'testme'"
 * 4) 'GRANT ALL ON DATABASE "DBALiteTest" TO "dbalite";'
 * 5) Run the CREATE TABLE statements located in 'tests/Data/PostgreSql_Test_Db_Schema.sql'.
 *
 * The singleton functionality in class DBALite is not tested because it
 * prevents further tests from being run. To test this functionality run
 * the SingletonTest as follows:
 * 'phpunit --bootstrap TestHelper.php SingletonTest.php'
 *
 * @package DBALite_Test
 */
class DBALiteTestSuite
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();

        $suite->addTestSuite('DBALiteTest');
        $suite->addTestSuite('DBALite_DriverAbstractTest');
        $suite->addTestSuite('DBALite_SelectTest');

        if (extension_loaded('pdo_sqlite')) {
            require_once('DBALite/Driver/SqliteTest.php');
            require_once('DBALite/Statement/SqliteTest.php');
            $suite->addTestSuite('DBALite_Driver_SqliteTest');
            $suite->addTestSuite('DBALite_Statement_SqliteTest');
            echo 'Sqlite tests to be executed.' . PHP_EOL;
        }

        if (extension_loaded('pdo_mysql')) {
            require_once('DBALite/Driver/MysqlTest.php');
            require_once('DBALite/Statement/MysqlTest.php');
            try {
                $con = new Pdo('mysql:host=localhost;dbname=DBALite_Test', 'dbalite', 'testme');
                $suite->addTestSuite('DBALite_Driver_MysqlTest');
                $suite->addTestSuite('DBALite_Statement_MysqlTest');
                echo 'MySQL tests to be executed.' . PHP_EOL;
            } catch (PDOException $e) {
            }
        }

        if (extension_loaded('pdo_pgsql')) {
            require_once('DBALite/Driver/PgsqlTest.php');
            require_once('DBALite/Statement/PgsqlTest.php');
            try {
                $con = new PDO('pgsql:host=localhost dbname=DBALiteTest', 'dbalite', 'testme');
                $suite->addTestSuite('DBALite_Driver_PgsqlTest');
                $suite->addTestSuite('DBALite_Statement_PgsqlTest');
                echo 'PostgreSQL tests to be executed.' . PHP_EOL;
            } catch (PDOException $e) {
            }
        }

        /*
        if (extension_loaded('pdo_sqlsrv')) {
            require_once('DBALite/Driver/SqlsrvTest.php');
            require_once('DBALite/Statement/SqlsrvTest.php');
            $suite->addTestSuite('DBALite_Driver_SqlsrvTest');
            $suite->addTestSuite('DBALite_Statement_SqlsrvTest');
            echo 'SQL Server tests to be executed' . PHP_EOL;
        }
        */

        return $suite;
    }
}
?>
