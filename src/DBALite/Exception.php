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
 * Handles errors.
 * @package DBALite
 */
class DBALite_Exception extends Exception
{
    /**
     * Caught PDOExceptions are passed to DBALite_Exception so we can give
     * better error messages.
     * @var Exception
     */
    protected $pdo_e;

    /**
     * Any SQL strings passed to a prepare(), query(), or execute() method.
     * @var string
     */
    protected $sql;

    /**
     * Object constructor.
     * @param string       $message
     * @param PDOException $pdo_exception (optional)
     * @param string       $sql (optional)
     * @return DBALite_Exception
     */
    public function __construct($message, $pdo_exception = null, $sql = null)
    {
        parent::__construct($message, 0);
        
        if (!is_null($pdo_exception))
            $this->pdo_e = $pdo_exception;

        if (!is_null($sql))
            $this->sql = $sql;
    }

    /**
     * Overload base PHP __toString() method.
     *
     * This is the only overloadable method allowed on exceptions in PHP.
     * Cast DBALite_Exception to a string to see any PDO error messages.
     * getMessage() will only return the DBALite generated error message.
     * <code>
     * try {
     *     //...
     * } catch (DBALite_Exception $e) {
     *    echo nl2br((string) $e);
     * }
     * </code>
     *
     * @return string
     */
    public function __toString()
    {
        $msg = $this->getMessage() . PHP_EOL;

        if (isset($this->pdo_e)) {
            // Let's try to make this message a bit prettier.
            $pdo_msg = $this->pdo_e->getMessage();
            $pdo_msg = ucfirst(trim(substr($pdo_msg, strrpos($pdo_msg, ']') + 1)));
            $msg .= 'The following PDO error was generated: ' . $pdo_msg . '.' . PHP_EOL;
            $msg .= 'SQLSTATE Code: ' . $this->pdo_e->getCode() . PHP_EOL;
            $msg .= 'In file: ' . $this->pdo_e->getFile() . ', line: ' . $this->pdo_e->getLine() . PHP_EOL;
        } else {
            $msg .= 'In file: ' . $this->getFile() . ', line: ' . $this->getLine() . PHP_EOL;
        }

        if (isset($this->sql)) {
            $msg .= 'SQL: ' . $this->sql . PHP_EOL;
        }

        return $msg;
    }

    /**
     * Return any passed SQL strings.
     * @return string|bool
     */
    public function getSql()
    {
        return (isset($this->sql)) ? $this->sql : false;
    }
}
