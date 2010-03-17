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
 * Allows execution of prepared statements and retrieval of query results.
 * @package DBALite
 * @todo Make Iteratable
 */
class DBALite_Statement
{
	/**
	 * Holds the PDO Statement object.
	 * @var PDOStatement
	 */
	protected $_stmt = null;

	/**
	 * The fetch mode to use for returning results.
	 * @var integer
	 */
	protected $_fetchMode = PDO::FETCH_ASSOC;

	/**
	 * Array map of fetch modes strings to PDO fetch mode constants.
	 * @var array
	 */
	protected static $fetchModes = array(
		'assoc'		=> PDO::FETCH_ASSOC,
		'both'		=> PDO::FETCH_BOTH,
		'default'	=> PDO::FETCH_ASSOC,
		'lazy'		=> PDO::FETCH_LAZY,
		'num'		=> PDO::FETCH_NUM,
		'obj'		=> PDO::FETCH_OBJ
		);

	/**
	 * If this object encapsulates a prepared statement does it use named
	 * or positional placeholders.
	 * @var int
	 */
	protected $_placeholder = DBALite::PARAM_NONE;

	/**
	 * SQL used to generate query, useful for debugging.
	 * @var array
	 */
	protected $_sql = '';

	/**
	 * Stores PDO Statement object and fetch mode in instantiation.
	 *
	 * @param PDOStatement $stmt        PDOStatement object.
	 * @param integer      $fetchMode   Current default fetch mode from 
	 *                                  DBALite_DriverAbstract instance.
	 * @param string       $sql         The SQL used to create this statement,
	 *                                  useful for debugging.
	 * @param int          $placeholder OPTIONAL: hint of which placeholder type
	 *                                  is used in query.
	 * @return DBALite_Statement
	 */
	public function __construct($stmt, $fetchMode, $sql, $placeholder = null)
	{
		if (! ($stmt instanceof PDOStatement)) {
			throw new DBALite_Exception("The object passed to DBALite_Statement was not a PDOStatement object.");
		}

		$this->_stmt = $stmt;
		$this->_fetchMode = $fetchMode;
		$this->_sql = $sql;
		$this->_placeholder = $placeholder;
	}

	/**
	 * Destroy the PDOStatement resource in a nice way.
	 */
	public function __destruct()
	{
		$this->_stmt = null;
	}

	/**
	 * Magic method to call PDOStatement methods that don't have a specific DBALite method.
	 *
	 * Note: This class provides reset() as an alias for PDOStatement::closeCursor().
	 *
	 * @method mixed getAttribute() proxy for PDOStatement::getAttribute()
	 * @method bool  setAttribute() proxy for PDOStatement::setAttribute()
	 * @method mixed errorCode()    proxy for PDOStatement::errorCode()
	 * @method array errorInfo()    proxy for PDOStatement::errorInfo()
	 * @method int   rowCount()     proxy for PDOStatement::rowCount()
	 * @method int   columnCount()  proxy for PDOStatement::columnCount()
	 * @method bool  nextRowset()   proxy for PDOStatement::nextRowset()
	 * @method bool  clodeCursor()  proxy for PDOStatement::closeCursor()
	 * 
	 * @param string $method
	 * @param array  $params
	 * @return mixed
	 * @throws DBALite_Exception If method doesn't exist.
	 */
	public function __call($method, $params)
	{
		switch ($method) {
			case 'getAttribute':
			case 'setAttribute':
			case 'errorCode':
			case 'errorInfo':
			case 'rowCount':
			case 'columnCount':
			case 'closeCursor':
			case 'nextRowset':
				return call_user_func_array(array($this->_stmt, $method), $params);
			default:
				throw new DBALite_Exception("Call to undefined method: $method. Not a valid PDO or DBALite method.");
		}
	}

	/**
	 * Sets the fetch mode for the result set.
	 *
	 * @param string $mode A valid fetch mode.
	 * @return void
	 */
	public function setFetchMode($mode)
	{
		if (array_key_exists($mode, self::fetchModes)) {
			$this->_fetchMode = self::$fetchModes[$mode];
		} else {
			throw new DBALite_Execption("Fetch mode '$mode' not supported or unknown.");
		}
	}

	/**
	 * Returns the current fetch mode.
	 *
	 * @return string
	 */
	public function getFetchMode()
	{
		return array_search($this->_fetchMode, self::$fetchModes);
	}

	/**
	 * Internal function for checking a passed fetch mode and returing the
	 * correct PDO::FETCH_* constant.
	 *
	 * @return int
	 */
	protected function _checkFetchMode($modeName)
	{
		if ($modeName === null) {
			return $this->_fetchMode;
		} else {
			if (array_key_exists($modeName, self::$fetchModes)) {
			return self::$fetchModes[$mode];
			} else {
				throw new DBALite_Execption("Fetch mode '$mode' not supported or unknown.");
			}
		}
	}

	/**
	 * Execute a prepared statement and return resutls.
	 *
	 * @param array $bind OPTIONAL: Values to bind into the query.
	 * @return bool       Success or failure.
	 */
	public function execute($bind = null)
	{
        if (is_array($bind)) {
			foreach ($bind as $name => $value) {
				$newName =  $this->_checkParam($name);
				if ($newName != $name) {
					unset($bind[$name]);
					$bind[$newName] = $value;
				}
			}
		}

		try {
			if (is_array($bind)) {
				return $this->_stmt->execute($bind);
			} else {
				return $this->_stmt->execute();
			}
		} catch (PDOException $e) {
			throw new DBALite_Exception('Error on PDOStatement->execute()', $e);
		}
	}

    /**
     * Bind a column of the statement result set to a PHP variable.
	 * Note: This method exists because pass-by-reference won't work with
	 * call_user_func_array();
     *
     * @param string $column Name the column in the result set, either by position
     *                       or by name. Note: if by name must match case.
     * @param mixed  $param  Reference to the PHP variable containing the value.
     * @param mixed  $type   OPTIONAL: PDO::PARAM_* Type hint.
     * @return bool          TRUE on success, FALSE on failure
     */
    public function bindColumn($column, &$param, $type = null)
    {
		$parameter = $this->_checkParam($parameter);

        try {
            if (is_null($type)) {
                return $this->_stmt->bindColumn($column, $param);
            } else {
                return $this->_stmt->bindColumn($column, $param, $type);
            }
        } catch (PDOException $e) {
            throw new DBALite_Exception("Error binding column '$column' to PHP variable", $e);
        }
    }

   /**
     * Binds a parameter to the specified variable name.
     *
	 * Note: This method exists because pass-by-reference won't work with
	 * call_user_func_array();
	 *
     * @param mixed $parameter Name the parameter, either integer or string.
     * @param mixed $variable  Reference to PHP variable containing the value.
     * @param mixed $type      OPTIONAL: PDO::PARAM_* Type hint.
     * @param mixed $length    OPTIONAL: Length of SQL parameter.
     * @param mixed $options   OPTIONAL: Other options.
     * @return bool            TRUE on success, FALSE on failure
	 */
    public function bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
		$parameter = $this->_checkParam($parameter);

        try {
            if ($type === null) {
                if (is_bool($variable)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($variable)) {
                    $type = PDO::PARAM_NULL;
                } elseif (is_integer($variable)) {
                    $type = PDO::PARAM_INT;
                } else {
                    $type = PDO::PARAM_STR;
                }
            }
            return $this->_stmt->bindParam($parameter, $variable, $type, $length, $options);
        } catch (PDOException $e) {
            throw new DBALite_Exception("Error binding parameter", $e);
        }
    }

    /**
     * Binds a value to a named or question-mark placeholder in the SQL query.
     *
	 * Note: This method exists because pass-by-reference won't work with
	 * call_user_func_array();
	 *
     * @param mixed $parameter Name the parameter if named placeholders are used,
	 *						   or the 1-indexed position of the ? in the query.
     * @param mixed $value     Scalar value to bind to the parameter.
     * @param mixed $type      OPTIONAL: PDO::PARAM_* Type hint.
     * @return bool            TRUE on success, FALSE on failure.
     */
    public function bindValue($parameter, $value, $type = null)
    {
		$parameter = $this->_checkParam($parameter);

        try {
            if (is_null($type)) {
                return $this->_stmt->bindValue($parameter, $value);
            } else {
                return $this->_stmt->bindValue($parameter, $value, $type);
            }
        } catch (PDOException $e) {
            throw new DBALite_Exception("Error binding value to query.", $e);
        }
    }

	/**
	 * Internal function to check the passed parameter to see if it is correct type.
	 *
	 * @param  int|string $param
	 * @return int|string
	 * @throws DBALite_Exception
	 */
	protected function _checkParam($param)
	{
		if (is_int($param) && ($this->_placeholder != DBALite::PARAM_POSITIONAL)) {
			throw new DBALite_Exception("The prepared query used positional (?) parameters and you passed a named parameter");
		} elseif (is_string($param)) {
			if ($this->_placeholder != DBALite::PARAM_NAMED) {
				throw new DBALite_Exception("The prepared query used named parameters and you passed a positional parameter");
			}
			if  ($param[0] != ':') {
            	$param = ":$param";
			}
        }
		return $param;
	}

	/**
	 * Return all of the results in an array.
	 *
	 * @param string $mode Override the default return type.
	 * @return array
	 */
	public function fetchAll($mode = null)
	{
		$mode = $this->_checkFetchMode($mode);

		try {
			return $this->_stmt->fetchAll($mode);
		} catch (PDOException $e) {
			throw new DBALite_Exception("Error fetching rows from result set", $e);
		}
	}

	/**
	 * Return a single row from the result set.
	 *
	 * @param string $mode Override the default return type.
	 * @return array|mixed
	 */
	public function fetchRow($mode = null)
	{
		$mode = $this->_checkFetchMode($mode);

		try {
			return $this->_stmt->fetch($mode);
		} catch (PDOException $e) {
			throw new DBALite_Exception("Error fetching row from result set", $e);
		}
	}

	/**
	 * Return the value from a single column of the current result row.
	 *
	 * @param int $col 0-indexed number of the column to return.
	 * @return mixed
	 */
	public function fetchColumn($col = 0)
	{
		if (! is_int($col)) {
			settype($col, 'int');
		}

		try {
			return $this->_stmt->fetchColumn($col);
		} catch (PDOException $e) {
			throw new DBALite_Exception("Error fetching column $col from current row", $e);
		}
	}

	/**
	 * Return the current rowset as an object with columns set as members of the object.
	 *
	 * @param string $className OPTIONAL: The class of the object returned,
	 *							defaults to 'stdClass'
	 * @param array $args       OPTIONAL: Arguments to pass the the class constructor.
	 * @return object
	 */
	public function fetchObject($className = 'stdClass', $args = array())
	{
		try {
			return $this->_stmt->fetchObject($className, $args);
		} catch (PDOException $e) {
			throw new DBALite_Exception("Error fetching row from result set", $e);
		}
	}

	/**
	 * Alias of closeCursor().
	 *
	 * Closes the cursor on the result set and resets the statement to be executed again.
	 *
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function reset()
	{
		return $this->_stmt->closeCursor();
	}

	/**
	 * Returns the PDOStatement object for direct manipulation.
	 *
	 * @return PDOStatement
	 */
	 public function getPdoStatement()
	 { 
		 return $this->_stmt;
	 }

	 /**
	  * Returns the SQL used to generate the statement, or the SQL as transformed by PDO.
	  *
	  * By default, or with an argument of 'dbalite' the original SQL string is returned.
	  * By passing 'pdo' as the argument it will return the SQL string in PDO which may
	  * have been rewritten to deal with binding.
	  *
	  * @param string $spec
	  * @return string
	  */
	 public function sql($spec)
	 {
		 switch ($spec) {
			 case 'pdo':
			 	return $this->_stmt->queryString;
				break;
			case 'dbalite':
			default:
				return $this->_sql;
		}
	 }
}
# vim:ff=unix:ts=4:sw=4:
