<?php
/**
 * DBALite - the lightweight, PDO based Database Abstraction Layer
 *
 * DBALite requires PHP version 5.1.0 or greater
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
 * This file incorporates work covered by the following copyright and
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
 * DBALite driver for MySQL databases.
 * @package DBALite
 * @subpackage Drivers
 */
class DBALite_Driver_Mysql extends DBALite_DriverAbstract
{
	/**
	 * Name of driver (aka brand) of database in use.
	 * @var string
	 */
	protected $_driver = 'mysql';

	/**
	 * Character to use when quoting strings in queries.
	 *
	 * For info only. @see DBALite_DriverAbstract::quote
	 * @var string
	 */
	protected $_quoteChar = '\'';

	/**
	 * Character to use when quoting identifiers.
	 * @var string
	 */
	protected $_quoteIdentChar = '`';

	/**
	 * The native method of placeholding data for binding in prepared statements.
	 * @var int
	 */
	protected $_nativePlaceholder = DBALite::PARAM_POSITIONAL;

	/**
	 * Special options availible to this driver.
	 * @var array
	 */
	protected $_availibleOptions = array(
		PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,
		PDO::MYSQL_ATTR_LOCAL_INFILE,
		PDO::MYSQL_ATTR_INIT_COMMAND,
		PDO::MYSQL_ATTR_READ_DEFAULT_FILE,
		PDO::MYSQL_ATTR_READ_DEFAULT_GROUP,
		PDO::MYSQL_ATTR_MAX_BUFFER_SIZE,
		PDO::MYSQL_ATTR_DIRECT_QUERY,
	);

	/**
	 * Creates connection to database.
	 *
	 * Configuration array must contain a 'dbname', 'username' and 'password'.
	 * A 'host' key may be passed. If not present it will default to 'localhost'.
	 * The optional MySQL connection settings 'port' or 'unix_socket' may be
	 * passed and will be used if present.
	 *
	 * @param array $config
	 */
	protected function _connect(array $config)
	{
		$dsn = 'mysql:';
		if (isset($config['unix_socket'])) {
			$dsn .= "unix_socket={$config['unix_socket']};";
		} else {
			if (!isset($config['host'])) {
				$config['host'] = 'localhost';
			}
			$dsn .= "host={$config['host']};";
			if (isset($config['port'])) {
				$dsn .= "port={$config['port']};";
			}
		}
		$dsn .= 'dbname=' . $config['dbname'];

		$username = $config['username'];
		$password = $config['password'];
		
		try {
			if (empty($this->_driverOptions)) {
				$conn = new PDO($dsn, $username, $password);
			} else {
				$conn = new PDO($dsn, $username, $password, $this->_driverOptions);
			}
		} catch (PDOException $e) {
			throw new DBALite_Exception("Connection to MySQL database failed.", $e);
		}

		$this->_pdo = $conn;

		// Use buffered queries so we don't get the dreaded error:
		// 2014 Cannot execute queries while other unbuffered queries are active.
		$this->_pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		// For some unknown reason MySQL driver still emulates prepares by default.
		$this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		// Lot of issues with charset mismatches. Set this by default.
		$this->_pdo->exec("SET NAMES 'utf8'");
	}

	/**
	 * Adds the SQL needed to do a limit query.
	 *
	 * @param string $sql SQL statement.
	 * @param integer $limit Number of rows to return.
	 * @param integer $offset Offset number of rows.
	 * @return string
	 */
	public function limit($sql, $limit, $offset = 0)
	{
		$sql = $sql . " LIMIT $limit";
		if ($offset) {
			$sql = $sql . " OFFSET $offset";
		}
		return $sql;
	}

	/**
	 * Get the ID in the autoincrementing column for the last inserted row.
	 *
	 * @param string $seq Will be ignored, MySQL does not need this parameter.
	 */
	public function lastInsertId($seq = '')
	{
		return $this->_pdo->lastInsertId();
	}
}
# vim:ff=unix:ts=4:sw=4:
