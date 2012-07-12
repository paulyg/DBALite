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
 *
 * This file incorporates work covered by the following copyright and
 * permission notices:
 *
 * Copyright (c) 2005-2008, Zend Technologies USA, Inc. (http://www.zend.com) All rights reserved.
 * Copyright (c) 2005-2008, Paul M. Jones and other contributors.
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
 * Connects to database, performs basic queries, prepares sql queries & statements.
 * @package DBALite
 * @todo Implement Lazy Loading
 */
abstract class DBALite_DriverAbstract
{
    /**
     * Holds the PDO object.
     * @var PDO
     */  
    protected $_pdo = null;

    /**
     * Name of driver (aka brand) of database in use.
     * @var string
     * @abstract
     */
    protected $_driver;

    /**
     * Holds passed config array.
     * @var array
     */
    protected $_config = array();

    /**
     * Holds driver specific options.
     * @var array
     */
    protected $_driverOptions;

    /**
     * Character to use when quoting strings in queries.
     *
     * For info only. @see DBALite_DriverAbstract::quote
     * @var string
     * @abstract
     */
    protected $_quoteChar;

    /**
     * Character to use when quoting identifiers.
     * @var string
     * @abstract
     */
    protected $_quoteIdentChar;

    /**
     * The native method of placeholding data for binding in prepared statements.
     * @var int
     * @abstract
     */
    protected $_nativePlaceholder;

    /**
     * Special options availible to a particular driver.
     * @var array
     * @abstract
     */
    protected $_availibleOptions;

    /**
     * Fetch mode for results.
     * @var integer
     */
    protected $_fetchMode = PDO::FETCH_ASSOC;

    /**
     * Array map of fetch modes strings to PDO fetch mode constants.
     * @var array
     */
    protected static $fetchModes = array(
        'assoc'     => PDO::FETCH_ASSOC,
        'both'      => PDO::FETCH_BOTH,
        'default'   => PDO::FETCH_ASSOC,
        'lazy'      => PDO::FETCH_LAZY,
        'num'       => PDO::FETCH_NUM,
        'obj'       => PDO::FETCH_OBJ
    );

    /**
     * Case of column names returned in queries.
     * @var integer
     */
    protected $_caseFolding = PDO::CASE_NATURAL;

    /**
     * Array map of case folding mode strings to PDO case folding mode constants.
     * @var array
     */
    protected static $foldingModes = array(
        'lower'     => PDO::CASE_LOWER,
        'natural'   => PDO::CASE_NATURAL,
        'upper'     => PDO::CASE_UPPER
    );

    /**
     * Null and empty string handling mode.
     * @var integer
     */
    protected $nullMode = PDO::NULL_NATURAL;

    /**
     * Array map of null <=> empty string handling modes.
     * @var array
     */
    protected $nullModes = array(
        'natural'        => PDO::NULL_NATURAL,
        'string_to_null' => PDO::NULL_EMPTY_STRING,
        'null_to_string' => PDO::NULL_TO_STRING
    );

    /**
     * Automatic quoting of idenifiers on or off.
     * @var boolean
     */
    protected $_autoQuoteIdents = true;

    /**
     * Parses connection string/parameters, sets options, creates connection to database.
     *
     * @param array $config Database connection parameters and options, see documentation.
     * @return DBALite_DriverAbstract
     */
    public function __construct(array $config)
    {
        if (isset($config['options']) && is_array($config['options'])) {
            $options = $config['options'];
            unset($config['options']);
            foreach ($options as $key => $val) {
                $this->setOption($key, $val);
            }
        }

        if (isset($config['driver_options']) && is_array($config['driver_options'])) {
            $this->_driverOptions = $config['driver_options'];
            unset($config['driver_options']);
        }

        $this->_config = $config;

        $this->_connect($this->_config);

        $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_pdo->setAttribute(PDO::ATTR_CASE, $this->_caseFolding);
        $this->_pdo->setAttribute(PDO::ATTR_ORACLE_NULLS, $this->_nullMode);
    }

    /**
     * Safely closes the connection to the database.
     */
    public function __destruct() 
    {
        $this->_pdo = null;
    }

    /**
     * Magic method to call PDO methods that don't have a specific DBALite method.
     *
     * @method mixed getAttribute() proxy for PDO::getAttribute()
     * @method bool  setAttribute() proxy for PDO::setAttribute()
     * @method mixed errorCode()    proxy for PDO::errorCode()
     * @method array errorInfo()    proxy for PDO::errorInfo()
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
            case 'beginTransaction':
            case 'commit':
            case 'rollBack':
                return call_user_func_array(array($this->_pdo, $method), $params);
            default:
                throw new DBALite_Exception("Call to undefined method: $method. Not a valid PDO or DBALite method.");
        }
    }

    /**
     * Return the name of the driver in use.
     *
     * @return string
     */
    public function getDriverName()
    {
        return $this->_driver;
    }

    /**
     * Set a DBALite option.
     *
     * @param string $key Option name.
     * @param mixed  $val Option value.
     */
    public function setOption($key, $val)
    {
        $key = strtolower($key);
        $val = strtolower($val);
        switch ($key) {
            case 'fetchmode':
                if (isset(self::$fetchModes[$val])) {
                    $this->_fetchMode = self::$fetchModes[$val];
                } elseif (in_array($val, self::$fetchModes, true)) {
                    $this->_fetchMode = $val;
                } else {
                    throw new DBALite_Exception("Fetch mode '$val' not supported or unknown.");
                }
                break;
            case 'casefolding':
                if (isset(self::$foldingModes[$val])) {
                    $this->_caseFolding = self::$foldingModes[$val];
                } elseif (in_array($val, self::$foldingModes, true)) {
                    $this->_caseFolding = $val;
                } else {
                    throw new DBALite_Exception("Fetch mode '$val' not supported or unknown.");
                }
                if (isset($this->_pdo)) {
                    $this->_pdo->setAttribute(PDO::ATTR_CASE, $this->_caseFolding);
                }
                break;
            case 'nullhandling':
                if (isset(self::$nullModes[$val])) {
                    $this->_nullMode = self::$nullModes[$val];
                } elseif (in_array($val, self::$nullModes, true)) {
                    $this->_nullMode = $val;
                } else {
                    throw new DBALite_Exception("Null handling mode '$val' not supported or unknown.");
                }
                if (isset($this->_pdo)) {
                    $this->_pdo->setAttribute(PDO::ATTR_ORACLE_NULLS, $this->_nullMode);
                }
                break;
            case 'autoquoteidentifiers':
                $this->_autoQuoteIdents = (($val) ? true : false);
                break;
            default:
                throw new DBALite_Exception("Option '$key' not supported.");
        }
    }

    /**
     * Return the value of a DBALite option.
     *
     * @param $key Option name.
     * @return mixed
     */
    public function getOption($key)
    {
        $key = strtolower($key);
        switch ($key) {
            case 'fetchmode':
                return array_search($this->_fetchMode, self::$fetchModes);
                break;
            case 'casefolding':
                return array_search($this->_caseFolding, self::$foldingModes);
                break;
            case 'nullhandling':
                return array_search($this->_nullMode, self::$nullModes);
                break;
            case 'autoquoteidentifiers':
                return $this->_autoQuoteIdents;
                break;
            default:
                throw new DBALite_Exception("Option '$key' not supported.");
        }
    }

    /**
     * Build an INSERT statement from parts and execute it.
     *
     * @param string $table Name of table to insert into.
     * @param array  $data  Associative array of the form 'column_name' => 'data'.
     * @return int|bool     Number of affected rows or false on error.
     */
    public function insert($table, array $data)
    {
        if ($this->arrayType($data) != DBALite::ARRAY_ASSOC) {
            throw new DBALite_Exception("Ivalid array argument: you must pass an associative array in the form 'column_name' => 'data' when using insert().");
        }

        $columns = array_keys($data);
        $values = array_values($data);

        $stmt = $this->prepareInsert($table, $columns);

        $result = $stmt->execute($values);

        return ($result) ? $stmt->rowCount() : false;
    }

    /**
     * Build an INSERT statement and return a prepared query.
     *
     * @param string $table   Name of table to insert into.
     * @param array  $columns Indexed array of column names.
     * @return DBALite_Statement
     */
    public function prepareInsert($table, array $columns)
    {
        $cols = $vals = array();
        foreach ($columns as $col) {
            $cols[] = $this->quoteIdentifier($col);
            $vals[] = '?';
        }

        $sql = 'INSERT INTO '
             . $this->quoteIdentifier($table)
             . '(' . implode(', ', $cols) . ')'
             . ' VALUES (' . implode(', ', $vals) . ')';

        $stmt = $this->prepare($sql, DBALite::PARAM_POSITIONAL);

        return $stmt;
    }

    /**
     * Build an UPDATE statement from parts and execute it.
     *
     * @param string       $table   Name of table to update.
     * @param array        $columns Associative array of the form 'column_name' => 'data'.
     * @param string|array $where   A where clause, will be passed to where().
     * @return int|bool             Number of affected rows or false on error.
     */
    public function update($table, array $data, $where = '')
    {
        if ($this->arrayType($data) != DBALite::ARRAY_ASSOC) {
            throw new DBALite_Exception("Ivalid array argument: you must pass an associative array in the form 'column_name' => 'data' when using update().");
        }

        $columns = array_keys($data);
        $values = array_values($data);

        $stmt = $this->prepareUpdate($table, $columns, $where);

        $result = $stmt->execute($values);

        return ($result) ? $stmt->rowCount() : false;
    }

    /**
     * Build an UPDATE statement and return a prepared query.
     *
     * @param string       $table   Name of table to update.
     * @param array        $columns Indexed array of column names.
     * @param string|array $where   A where clause, will be passed to where().
     * @return DBALite_Statement
     */
    public function prepareUpdate($table, array $columns, $where = '')
    {
        $set = array();
        foreach ($columns as $col) {
            $set[] = $this->quoteIdentifier($col) . " = ?";
        }

        $where = $this->where($where);

        $sql = 'UPDATE '
             . $this->quoteIdentifier($table)
             . ' SET ' . implode(', ', $set)
             . (($where) ? " WHERE $where" : '');

        $stmt = $this->prepare($sql, DBALite::PARAM_POSITIONAL);

        return $stmt;
    }

    /**
     * Build a DELETE statement from parts and execute it.
     *
     * @param string       $table Name of table to delete from.
     * @param string|array $where A where clause, will be passed to where().
     * @return int|bool           Number of affected rows or false on error.
     */
    public function delete($table, $where = '')
    {
        $stmt = $this->prepareDelete($table, $where);

        $result = $stmt->execute();

        return ($result) ? $stmt->rowCount() : false;
    }

    /**
     * Build a DELETE statement and return a prepared query.
     *
     * @param string $table       Name of table to delete from.
     * @param string|array $where A where clause, will be passed to where().
     * @return DBALite_Statement
     */
    public function prepareDelete($table, $where = '')
    {
        $where = $this->where($where);

        $sql = 'DELETE FROM '
             . $this->quoteIdentifier($table)
             . (($where) ? " WHERE $where" : '');

        return $this->prepare($sql);
    }

    /**
     * Create a SELECT query using the DBALite_Select class.
     *
     * @return DBALite_Select
     */
    public function select()
    {
        return new DBALite_Select($this);
    }

    /**
     * Assist in building a where clause.
     *
     * This function accepts an array with three values:
     *     1) A column name or alias for the clause. The column name will be
     *        quoted via quoteIdentifier().
     *     2) An expression such as =, >, <, etc. This value will be put into the
     *        clause unquoted.
     *     3) A data value. The value will be quoted via quote().
     * If a string is passed it will be returned unaltered.
     * 
     * @param string|array $spec
     * @return string
     */
    public function where($spec)
    {
        if (!is_array($spec)) {
            return $spec;
        }

        list($col, $expr, $val) = $spec;
        if (strpos($col, '(') === false) {
            $col = $this->quoteIdentifier($col);
        }
        if (!is_null($val)) {
            $val = $this->quote($val);
        }
        $where = "$col $expr $val";

        return $where;
    }

    /**
     * Determine if array is associative (string keys) or indexed (numeric keys).
     * @param array $array
     * @return int DBALite::ARRAY_* constant.
     */
    public function arrayType(array $array)
    {
        $keys = array_keys($array);
        // Grab 1st key and determine type
        $first = array_shift($keys);
        if (is_int($first)) {
            foreach ($keys as $check) {
                if (!is_int($check)) {
                    return DBALite::ARRAY_MIXED;
                }
            }
            return DBALite::ARRAY_INDEXED;
        } elseif (is_string($first)) {
            foreach ($keys as $check) {
                if (!is_string($check)) {
                    return DBALite::ARRAY_MIXED;
                }
            }
            return DBALite::ARRAY_ASSOC;
        } else {
            return DBALite::ARRAY_MIXED;
        }
    }

    /**
     * Prepare a raw SQL query or statement.
     *
     * @param string $sql         SQL query or statement
     * @param int    $placeholder OPTIONAL: DBALite::ARRAY_* constant hint of which
     *                            placeholder type is used in query.
     * @return DBALite_Statement
     */
    public function prepare($sql, $placeholder = null)
    {
        try {
            $pdo_stmt = $this->_pdo->prepare($sql);
        } catch (PDOException $e) {
            throw new DBALite_Exception('Error executing PDO->prepare().', $e, $sql);
        }
        $dbalite_stmt = new DBALite_Statement($pdo_stmt, $sql, $this->_fetchMode, $placeholder);
        return $dbalite_stmt;
    }

    /**
     * Execute a raw SQL query and return the results.
     *
     * @param string $sql         SQL query to execute.
     * @param mixed  $data        OPTIONAL: Values to be quoted into the query.
     * @param int    $placeholder OPTIONAL: DBALite::ARRAY_* constant hint of which
     *                            placeholder type is used in query.
     * @return DBALite_Statement
     */
    public function query($sql, $data = null, $placeholder = null)
    {
        if (is_array($data)) {
            $dbalite_stmt = $this->prepare($sql, $placeholder);
            $dbalite_stmt->execute($data);
        } else {
            try {
                $pdo_stmt = $this->_pdo->query($sql);
            } catch (PDOException $e) {
                throw new DBALite_Exception('Error executing PDO->query().', $e, $sql);
            }
            $dbalite_stmt = new DBALite_Statement($pdo_stmt, $sql, $this->_fetchMode, $placeholder);
        }
        return $dbalite_stmt;
    }

    /**
     * Convenience method for query() + fetchAll().
     *
     * @param string $sql  SQL Select query.
     * @param mixed  $data OPTIONAL: Values to be quoted into query.
     * @param string $mode OPTIONAL: Override the default fetch mode.
     * @return array
     */
    public function queryAll($sql, $data = null, $mode = null)
    {
        $stmt = $this->query($sql, $data);
        return $stmt->fetchAll($mode);
    }

    /**
     * Convenience method for running a query and returning a single value.
     *
     * Returns value of first row and first column if query actually returns
     * more than one row and/or column.
     *
     * @param string $sql  SQL Select query.
     * @param mixed  $data OPTIONAL: Values to be quoted into query.
     * @return mixed
     */
    public function queryOne($sql, $data = null)
    {
        $stmt = $this->query($sql, $data);
        return $stmt->fetchColumn(0);
    }

    /**
     * Execute a raw SQL statement.
     *
     * @param string $sql SQL statement to execute.
     * @return bool|int   Number of rows affected or false if error.
     */
    public function execute($sql)
    {
        try {
            return $this->_pdo->exec($sql);
        } catch (PDOException $e) {
            throw new DBALite_Exception('Error running PDO->exec().', $e, $sql);
        }
    }

    /**
     * Properly escapes a string, places delimiters around it for use in a query.
     *
     * Prepared statement placeholders (? or :string) are not escaped and returned as-is.
     *
     * @param mixed $data
     * @return mixed
     */
    public function quote($data)
    {
        if (is_int($data) || is_float($data) || ($data == '?') || ($data[0] == ':')) {
            return $data;
        }

        $data = str_replace("\x00", '', $data);
        return $this->_pdo->quote($data);
    }

    /**
     * Place the proper delimiters around table and column names.
     *
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        if (! $this->_autoQuoteIdents) {
            return $identifier;
        }

        $q = $this->_quoteIdentChar;

        $idents = explode('.', $identifier);

        foreach ($idents as $key => $ident) {
            $idents[$key] = $q . str_replace("$q", "$q$q", $ident) . $q;
        }

        $quoted = implode('.', $idents);

        return $quoted;
    }

    /**
     * Begin an SQL transaction.
     *
     * @return void
     */
    public function begin()
    {
        return $this->_pdo->beginTransaction();
    }

    /**
     * Commit the current SQL transaction.
     *
     * @return void
     */
    public function commitTransaction()
    {
        return $this->_pdo->commit();
    }

    /**
     * Cancels the current SQL transaction.
     *
     * @return void
     */
    public function rollbackTransaction()
    {
        return $this->_pdo->rollBack();
    }

    /**
     * Returns the PDO object for direct manipulation.
     *
     * @return PDO
     */
    public function getPdo()
    {
        return $this->_pdo;
    }

    /**
     * Creates the driver specific PDO connection.
     *
     * @param array $config Connection parameters.
     * @return void
     */
    abstract protected function _connect(array $config);

    /**
     * Retrieve the ID of the last record inserted into an auto-incrementing column.
     *
     * Some RDBMS' will return the value of the auto-increment column, on others
     * you need to pass a sequence name for it to work.
     *
     * @return int
     */
    abstract public function lastInsertId($seq = '');

    /**
     * Adds the SQL needed to do a limit query.
     *
     * @param string  $sql    SQL statement.
     * @param integer $limit  Number of rows to return.
     * @param integer $offset Offset number of rows.
     * @return string
     */
    abstract public function limit($sql, $limit, $offset = 0);
}
