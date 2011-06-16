<?php
/**
 * DBALite - the lightweight, PDO based Database Abstraction Layer
 *
 * DBALite requires PHP version 5.1.0 or greater.
 * Additionally the PDO extension, and the PDO driver for any database you wish
 * to connect to with DBALite must be installed and enabled in you php.ini file.
 *
 * @package DBALite
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
 *
 * DBALite incorporates work covered by the following copyright and
 * permission notice:
 *
 * Copyright (c) 2005-2008, Zend Technologies USA, Inc. (http://www.zend.com)
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice,
 *      this list of conditions and the following disclaimer.
 *
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *
 *    * Neither the name of Zend Technologies USA, Inc. nor the names of its
 *      contributors may be used to endorse or promote products derived from this
 *      software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
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
	const VERSION = '0.3.1-beta';
	const API_VERSION = 3010;
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
	protected static $supportedDrivers = array('mssql', 'mysql', 'pgsql', 'sqlite');

	/**
	 * Placeholder for optional singleton DBALite driver.
	 * @var DBALite_Abstract
	 */
	private static $singleton = null;

	/**
	 * Create an instance of a DBALite database adapter.
	 *
	 * Supported $driver values are: 'mssql', 'mysql', 'pgsql', or 'sqlite'.
	 *
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
	 * @param  string $driver The name of the RDBMS driver you wish to connect to.
	 * @param  bool   $enforce_singleton Force the factory to implement the singleton pattern.
	 * @return DBALite_DriverAbstract
	 * @throws DBALite_Exception
	 */
	public static function factory($driver, $config, $enforce_singleton = false)
	{
		if (!is_null(self::$singleton)) {
			throw new DBALite_Exception('A driver has been created with the "enforce singleton" option. Use DBALite::getSingleton() to return the singleton driver or remove the third parameter from your previous DBALite::factory() call to be able to instantiate more than one driver');
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

		if ($enforce_singleton) {
			self::$singleton = $instance;
		}

		return $instance;
	}

	/**
	 * Return a singleton driver instance.
	 *
	 * If a singleton instance was created with DBALite::factory() retreive a
	 * reference to that object with this method. If a singleton instance has
	 * not been created yet it will be created for you.
	 *
	 * @see    DBALite::factory()
	 * @param  string $driver Needed first call only.
	 * @param  array  $config Needed first call only.
	 * @return DBALite_DriverAbstract
	 */
	public static function getSingleton($driver = '', $config = null)
	{
		if (is_null(self::$singleton)) {
			return self::factory($driver, $config, true);
		} else {
			return self::$singleton;
		}
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
			if ($driver == 'mssql') {
				$dbalite_drivers[$driver] = in_array('odbc', $pdo_drivers);
			} else {
				$dbalite_drivers[$driver] = in_array($driver, $pdo_drivers);
			}
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

	/**
	 * Return the API version number.
	 * @return int
	 */
	public static function apiVersion()
	{
		return self::API_VERSION;
	}
}
# vim:ff=unix:ts=4:sw=4:
