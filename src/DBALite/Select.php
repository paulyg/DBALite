<?php
/**
 * DBALite - a lightweight, PDO based Database Abstraction Layer
 *
 * DBALite requires PHP version 5.1.0 or greater
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
 * Allows you to programatically build a SELECT query from it's parts
 * @package DBALite
 */
class DBALite_Select
{
    /**
     * Holds a reference to the main DBALite object
     * @var DBALite_DriverAbstract
     */
    protected $_adapter = null;

    /**
     * Initial values for the $parts member
     * @var array
     */
    protected static $_partsInit = array(
        'distinct'  => FALSE,
        'columns'   => array(),
        'from'      => array(),
        'where'     => array(),
        'group'     => array(),
        'having'    => array(),
        'order'     => array(),
        'limit'     => array()
    );

    /**
     * List of supported join types
     * @var array
     */
    protected static $_joinTypes = array(
        'INNER',
        'LEFT',
        'LEFT OUTER',
        'RIGHT',
        'RIGHT OUTER',
        'CROSS',
        'NATURAL',
        'FULL'
    );

    /**
     * Is the query a DISTINCT query?
     * @var bool
     */
    protected $_distinct;

    /**#@+
     * Holds the parts of the query before building
     * @var array
     */
    protected $_columns;
    protected $_from;
    protected $_where;
    protected $_group;
    protected $_having;
    protected $_order;
    protected $_limit;
    /**#@-*/

    /**
     * Initalize properties storing query parts and store reference to the driver object.
     *
     * @param DBALite_DriverAbstract $adapter a DBALite driver
     * @return DBALite_Select
     */
    public function __construct(DBALite_DriverAbstract $adapter)
    {
        $this->_adapter = $adapter;
        $this->reset();
    }

    /**
     * Add a DISTINCT clause to the query.
     *
     * @param bool $flag      Whether or not the SELECT is DISTINCT (default true).
     * @return DBALite_Select This object.
     */
    public function distinct($flag = true)
    {
        $this->_distinct = (bool) $flag;
        return $this;
    }

    /**
     * Specify a table to include in the query with columns.
     * 
     * This method will only accept one table in the $table parameter. You may
     * specify an alias using 'table AS alias' or array('alias' => 'table').
     * You may call it multiple times to add more than one table to the query.
     * If you need to do a join other than an implicit inner join use the
     * join() method. If the $cols parameter is omitted all columns (SQL *)
     * will be specified.
     *
     * @param string|array $table The table name to select from,
     *                            may be as array('alias' => 'table').
     * @param string|array $cols  Column(s) to include in the query, defaults to '*'.
     * @return DBALite_Select     This object.
     */
    public function from($table, $columns = '*')
    {
        $this->join(null, $table, null, $columns);

        return $this;
    }

    /**
     * Join a table to the query.
     *
     * @param string       $type      A join type.
     * @param string|array $table     A table following the rules in from().
     * @param string       $condition A join condition, this will be evaluated
     *                                according the the same rules as
     *                                @see DBALite_DriverAbstract::where().
     * @param string|array $cols      Column(s) to include in the query,
     *                                defaults to '*'.
     * @return DBALite_Select         This object.
     */
    public function join($type, $table, $condition = null, $columns = '*')
    {
        if (!is_null($type) && !in_array(strtoupper($type), self::$_joinTypes)) {
            throw new DBALite_Exception("Invalid join type '$type'.");
        }

        if (is_array($table) && count($table) == 1) {
            $alias = key($table);
            $tableName = $table[$alias];
            if (!is_string($alias)) {
                throw new DBALite_Exception("The table alias must be a string.");
            }
        } else if (is_string($table) && !empty($table)) {
            if (preg_match('/^(.+)\s+AS\s+(.+)$/i', $table, $m)) {
                $tableName = $m[1];
                $alias = $m[2];
            } else {
                $tableName = $table;
                $alias = null;
            }
        } else {
            throw new DABALite_Exception("The \$table parameter was not a string or array, or the array had more than 1 elements.");
        }

        $correlation = (is_null($alias)) ? $tableName : $alias;

        if (!isset($this->_from[$correlation])) {

            if (!is_null($condition)) {
                $condition = $this->_parseJoinCondition($condition);
            }

            $this->_from[$correlation] = array(
                'tableName' => $this->_adapter->quoteIdentifier($tableName),
                'alias' => $alias,
                'joinType' => $type,
                'joinCondition' => $condition
            );
        }

        if (is_string($columns)) {
            $columns = array($columns);
        } elseif (is_null($columns)) {
            $columns = array();
        }

        if (is_null($alias)) {
            $correlation = $this->_adapter->quoteIdentifier($correlation);
        }

        foreach ($columns as $column) {
            $this->_addColumn($column, $correlation);
        }

        return $this;
    }

    /**
     * Build a where clause from parts.
     *
     * This method allows you to build a where expression programatically.
     * You must specify at least a column identifier and an  expression.
     * Only logical operators such as =, <, >, !=, <=, >=, <> and the keywords
     * EXISTS, IS NULL, LIKE, BETWEEN, IN  and any of the previous with NOT are
     * supported. It is assumed that the data argument will a scalar for
     * conditions that would only require one value. For BETWEEN  the data should
     * be an array and only the first 2 elements are used. IN expects an array also.
     * If the query contains more than one WHERE clause they will be concatenated
     * with AND. To force an OR clause pass 'OR' to the last parameter.
     *
     * @param string $col     A column name or alias.
     * @param string $expr    An operator or comparison keyword.
     * @param mixed  $data    Data to include in the expression.
     * @param string $andOr   OPTIONAL: Concatenate with 'AND' (default) or 'OR'.
     * @return DBALite_Select This object.
     */
    public function where($col, $expr, $data = '', $andOr = 'AND')
    {
        $col = $this->_adapter->quoteIdentifier($col);

        if (preg_match('/^[=!<>]{1,2}$/', $expr)
            && is_scalar($data)) {
            // we can expect one $data arg
            $data = $this->_adapter->quote($data);
            $where = "$col $expr $data";
        } else {
            $expr = strtoupper($expr);
            switch ($expr) {
                case 'EXISTS':
                case 'NOT EXISTS':
                case 'IS NULL':
                case 'IS NOT NULL':
                    // no $data args
                    $where = "$col $expr";
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                    // one $data arg
                    if (is_scalar($data)) {
                        if (strpos($data, '%') === false) { 
                            $data = $this->_adapter->quote("%$data%");
                        }
                        $where = "$col $expr $data";
                    }
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    // two $data args
                    if (is_array($data)) {
                        $where = "$col $expr" . $this->_adapter->quote($data[0]) .
                            ' AND ' . $this->_adapter->quote($data[1]);
                    }
                    break;
                case 'IN':
                case 'NOT IN':
                    // unknown number of $data args
                    if (is_array($data)) {
                        foreach ($data as $val) {
                            $val = $this->_adapter->quote($val);
                        }
                        $where = "$col $expr(" . implode(', ', $data) . ')';
                    }
                    break;
                default:
                    // If we ended up here there must be a problem
                    throw new DBALite_Exception("Invalid expression agrument '$expr'.");
            }
        }

        if (count($this->_where)) {
            switch (strtoupper(trim($andOr))) {
                case 'AND':
                    $where = "AND $where";
                    break;
                case 'OR':
                    $where = "OR $where";
                    break;
                default:
                    throw new DBALite_Exception("Invalid arguement $andOr. Must be 'AND' or 'OR'.");
            }
        }

        $this->_where[] = $where;

        return $this;
    }

    /**
     * Add grouping to the query.
     *
     * @param array|string $cols The column(s) to group by.
     * @return DBALite_Select    This object.
     */
    public function groupBy($cols)
    {
        if (!is_array($cols)) {
            settype($cols, 'array');
        }

        foreach ($cols as $col) {
            $col = $this->_adapter->quoteIdentifier($col);
            $this->_group[] = $col;
        }

        return $this;
    }

    /**
     * Add a HAVING clause to the query.
     *
     * The spec parameter expects a string or array following the same rules as
     * @see DBALite_DriverAbstract::where().
     *
     * @param string|array $expr  An expression.
     * @param string       $andOr OPTIONAL: 'AND' (defalt) or 'OR'.
     * @return DBALite_Select     This object.
     */
    public function having($expr, $andOr = 'AND')
    {
        $expr = $this->_adapter->where($expr);

        $having = '';
        if (count($this->_having)) {
            switch (strtoupper(trim($andOr))) {
                case 'AND':
                    $having = "AND $expr";
                    break;
                case 'OR':
                    $having = "OR $expr";
                    break;
                default:
                    throw new DBALite_Exception("Invalid arguement $andOr. Must be 'AND' or 'OR'.");
            }
        } else {
            $having = "$expr";
        }

        $this->_having[] = $having;

        return $this;
    }

    /**
     * Add an ordering clause to the query.
     *
     * @param string $cols    Column to order by.
     * @param string $dir     Optional direction: 'ASC' or 'DESC'.
     * @return DBALite_Select This object.
     */
    public function orderBy($col, $dir = null)
    {
        if (is_string($col)) {
            $col = $this->_adapter->quoteIdentifier($col);
        } else {
            throw new DBALite_Exception("Invalid parameter for DBALite_Select::orderby(). 1st parameter must be a string and a valid column name.");
        }
        
        if (!is_null($dir)) {
            $dir = strtoupper($dir);
            if (($dir != 'ASC') && ($dir != 'DESC')) {
                throw new DBALite_Exception("Invalid parameter for DBALite_Select::orderby(). 2nd parameter must be 'ASC' or 'DESC'.");
            }
        }

        $this->_order[] = $col . (is_null($dir) ? '' : ' ' . $dir);

        return $this;
    }

    /**
     * Add a limit count and optionally an offset to the query.
     *
     * @param int $limit      The number of rows to return.
     * @param int $offset     OPTIONAL: Skip this many rows before returning results.
     * @return DBALite_Select This object.
     */
    public function limit($limit, $offset = 0)
    {
        $this->_limit['limit'] = (int) $limit;
        $this->_limit['offset'] = (int) $offset;

        return $this;
    }

    /**
     * Sets limit and offset by a count per page and page number.
     *
     * @param int $pageNum    The page number.
     * @param int $perPage    The number of items per page.
     * @return DBALite_Select This object.
     */
    public function limitPage($pageNum, $perPage)
    {
        $this->_limit['limit'] = (int) $perPage;
        $this->_limit['offset'] = (int) $perPage * ($pageNum - 1);

        return $this;
    }

    /**
     * Builds the query and returns it as an SQL string.
     *
     * @return string An SQL select query.
     */
    public function build()
    {
        $sql = 'SELECT ';

        // Add DISTINCT
        if ($this->_distinct) {
            $sql .= 'DISTINCT ';
        }

        // Add columns
        $columns = array();
        if ((count($this->_columns) > 1) && (count($this->_from) > 1)) {
            foreach ($this->_columns as $colspec) {
                $columns[] = $colspec['correlation'] . "." . $colspec['columnName'];
            }
        } else {
            foreach ($this->_columns as $colspec) {
                $columns[] = $colspec['columnName'];
            }
        }
        $sql .= implode(', ', $columns);

        // Add FROM and JOINs
        $from = array();
        $joins = array();
        foreach ($this->_from as $tabledata) {
            $table = $tabledata['tableName'];
            if (!is_null($tabledata['alias'])) {
                $table .= ' AS ' . $tabledata['alias'];
            }
            if (!is_null($tabledata['joinType'])) {
                $join = strtoupper($tabledata['joinType']) . ' JOIN ' . $table;
                $join .= (!is_null($tabledata['joinCondition'])) ? ' ' . $tabledata['joinCondition'] : '';
                $joins[] = $join;
            } else {
                $from[] = $table;
            }
        }
        $sql .= ' FROM ' . implode(', ', $from);
        if (count($joins)) {
            $sql .= ' ' . implode(PHP_EOL, $joins);
        }

        // Add WHERE clauses
        if (count($this->_where)) {
            $sql .= ' WHERE ' . implode(' ', $this->_where);
        }

        // Add GROUP BYs
        if (count($this->_group)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->_group);
        }

        // Add HAVING
        if (count($this->_having)) {
            $sql .= ' HAVING ' . implode(' ', $this->_having);
        }

        // Add ORDER BY
        if (count($this->_order)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->_order);
        }

        // Add LIMIT
        if (count($this->_limit)) {
            $sql = $this->_adapter->limit($sql, $this->_limit['limit'], $this->_limit['offset']);
        }

        // And we're done!
        return $sql;
    }

    /**
     * Magic method and proxy for @see build().
     *
     * @return string An SQL select query.
     */
    public function __toString()
    {
        return $this->build();
    }

    /**
     * Resets all parts of the query back to a blank state.
     *
     * @return void
     */
    public function reset()
    {
        foreach (self::$_partsInit as $k => $v) {
            $m = "_$k";
            $this->$m = $v;
        }
    }

    /**
     * Internal method for adding a column to the query.
     * 
     * The first parameter may be a string containing a column name or expression,
     * optionally with an alias, or an array with the form 'alias => column'.
     * If a value is passed for the second parameter it will be prepended to the
     * column name.
     *
     * @param string|array $column      A column specification.
     * @param string       $correlation Table name or table alias.
     * @return void
     */
    protected function _addColumn($column, $correlation)
    {
        if (is_array($column) && count($column) == 1) {
            $alias = key($column);
            $columnName = $column[$alias];
        } else if (is_string($column) && !empty($column)) {
            if (preg_match('/^(.+)\s+AS\s+(.+)$/i', $column, $m)) {
                $columnName = $m[1];
                $alias = $m[2];
            } else {
                $columnName = $column;
                $alias = null;
            }
        } else {
            throw new DABALite_Exception("The \$column parameter was not a string or array, or the array had more than 1 elements.");
        }

        if (($columnName != '*') && (false === strpos($columnName, '('))) {
            $columnName = $this->_adapter->quoteIdentifier($columnName);
        }

        if ($alias) {
            $columnName .= " AS $alias";
        }

        $this->_columns[] = array(
            'columnName' => $columnName,
            'correlation' => $correlation
        );
    }

    /**
     * Internal function for building join conditions.
     *
     * @param mixed $condition
     * @return string
     */
    protected function _parseJoinCondition($condition)
    {
        if (is_array($condition)) {
            if ($condition[0] == 'USING') {
                array_shift($condition);
                $conditionCols = '';
                foreach ($condition as $col) {
                    $conditionCols .= $this->_adapter->quoteIdentifier($col);
                }
                $condition = 'USING (' . explode(', ', $conditionCols) . ')';
            } elseif (count($condition) == 2) {
                $condition = 'ON ' . $this->_adapter->quoteIdentifier($condition[0])
                    . ' = ' . $this->_adapter->quoteIdentifier($condition[1]);
            } else {
                throw new DBALite_Exception("The array format for \$condition did not match any of the valid formats.");
            }
        } else {
            $condition = 'ON ' . $condition;
        }

        return $condition;
    }
}
