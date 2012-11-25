<?php
/**
 * DBALite - a lightweight, PDO based Database Abstraction Layer
 *
 * DBALite requires PHP version 5.1.0 or greater.
 * Additionally the PDO extension, and the PDO driver for any database you wish
 * to connect to with DBALite must be installed and enabled in you php.ini file.
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
 * Include required files.
 */
require 'DBALite/Exception.php';
require 'DBALite/DriverAbstract.php';
require 'DBALite/Statement.php';
require 'DBALite/Select.php';

/**
 * This class provides a static factory method for creating new DABLite objects.
 * @package DBALite
 */
class DBALite
{
    /**
     * Class constants
     */
    const VERSION = '1.0.2';
    const ARRAY_MIXED = 0;
    const ARRAY_INDEXED = 1;
    const ARRAY_ASSOC = 2;
    const PARAM_NONE = 0;
    const PARAM_POSITIONAL = 1;
    const PARAM_NAMED = 2;

    /**
     * List of database drivers supported by DBALite.
     * @var array
     */
    protected static $supportedDrivers = array('mysql', 'pgsql', 'sqlite', /*'sqlsrv'*/);

    /**
     * Placeholder for optional singleton DBALite driver.
     * @var DBALite_Abstract
     */
    private static $singleton = null;

    /**
     * Private constructor since there are no instance methods on this class.
     */
    private function __construct() { }

    /**
     * Create an instance of a DBALite database adapter.
     *
     * @param string $driver The name of the RDBMS driver you wish to connect to.
     * Supported $driver values are: 'mysql', 'pgsql', or 'sqlite'.
     *
     * @param array $config Additional information needed to make the connection.
     * The following are valid keys for the $config array:
     * <pre>
     * 'dbname'   REQUIRED The name of your database (full path & name for SQLite)
     * 'username' OPTIONAL
     * 'password' OPTIONAL
     * 'options'  OPTIONAL An array of options for DBALite.
     *     Valid keys are 'fetchmode', 'casefolding', 'autoquoteidentifiers', 
     *     preferredplaceholder'. See the DBALite_DriverAbstract class for a complete
     *     description of these options.
     * 'driver_options' OPTIONAL An array of options to be passed directly to the PDO
     *     driver instance. See the documentation for the PDO driver of your choice
     *     on php.net for possible values.
     * See the driver object's documentation for complete explanation of all the
     * configuration keys accepted by that driver.
     * </pre>
     *
     * @return DBALite_DriverAbstract
     * @throws DBALite_Exception
     */
    public static function factory($driver, $config)
    {
        if (!is_null(self::$singleton)) {
            throw new DBALite_Exception('A singleton connection has been created with DBALite::getSingleton(). Call DBALite::getSingleton() again to return the singleton connection instance. If you wish to be able to instantiate more than one connection replace all previous DBALite::getSingleton() calls with DBALite::factory() calls.');
        }

        if (is_string($driver)) {
            $driver = strtolower($driver);
            if (!in_array($driver, self::$supportedDrivers)) {
                throw new DBALite_Exception("A valid database driver type was not specified. Please specify one of '" . implode("', '", self::$supportedDrivers) . "'.");
            }
        } else {
            throw new DBALite_Exception("Incorrect type supplied for driver. Driver must be a string");
        }

        if (!is_array($config)) {
            throw new DBALite_Exception("The DBALite configuration parameters must be in the form of an array. See the documentation for details.");
        }

        if (!array_key_exists('dbname', $config)) {
            throw new DBALite_Exception("A database name was not found in the connection parameters. Please specify a database name in the array key 'dbname'.");
        }

        $driver = ucfirst($driver);

        $driver_class = "DBALite_Driver_$driver";

        if (!class_exists($driver_class, false)) {

            $driver_file = "DBALite/Driver/$driver.php";

            require $driver_file;
        }

        $instance = new $driver_class($config);

        return $instance;
    }

    /**
     * Return a singleton connection instance.
     *
     * If a singleton instance has not been created yet it will be created for you.
     *
     * @see    DBALite::factory()
     * @param  string $driver Needed on first call only.
     * @param  array  $config Needed on first call only.
     * @return DBALite_DriverAbstract
     */
    public static function getSingleton($driver = '', $config = null)
    {
        if (is_null(self::$singleton)) {
            self::$singleton = self::factory($driver, $config);
        }
        return self::$singleton;
    }

    /**
     * Get list of available drivers, both for DBALite & PDO.
     *
     * An array is returned where the keys are the names of DBALite drivers
     * present and the values are booleans depending on if the corresponding
     * PDO driver is loaded.
     * 
     * @return array
     */
    public static function getDrivers()
    {
        $pdo_drivers = PDO::getAvailableDrivers();
        $dbalite_drivers = array();
        foreach (self::$supportedDrivers as $driver) {
            $dbalite_drivers[$driver] = in_array($driver, $pdo_drivers);
        }

        return $dbalite_drivers;
    }

    /**
     * Return the version number.
     * @return string
     */
    public static function version()
    {
        return self::VERSION;
    }
}
