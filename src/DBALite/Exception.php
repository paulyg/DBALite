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
	 *	  echo nl2br((string) $e);
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
# vim:ff=unix:ts=4:sw=4:
