DBALite is a lightweight Database Abstraction Library for PHP. It uses the PDO
extension to acheive much of this "lightness".

Rationale:
When writing a web application in PHP I found myself repeating code to
create SQL queries.  I knew it was time to look at libraries what would do that for
me. I was already using PDO to keep support for several different database types
and wanted whatever library I used to continue to use it. I did not want to use a
full ORM or Active Record solution. Looking around at what is available I found
Zend_Db (part of the Zend Framework) API to be much to my liking. However I felt 
the multiple levels of abstration Zend_Db uses to support both PDO drivers and
the vendor specific extensions was adding too much code that would be run by and
distributed with the application and wasn't really doing anything. And so I
decided to write my own library. The main functionality I wanted were methods
for creating and executing INSERT, UPDATE, DELETE, and SELECT queries.

Requirements:
DBALite requires PHP version 5.1.0 or greater. Additionally the PDO extension,
and the PDO driver for any database you wish to connect to with DBALite
(pdo_sqlite, pdo_mysql, pdo_pgsql) must be installed and enabled in you php.ini file.

Supported Databases: currently DBALite has been tested to work with Sqlite3, MySQL,
and PostgreSQL. I plan to support SQL Server via the ODBC driver when I get the
time to run the test suite on it.

Getting Started: 
1) To use DBLite simply copy the files in the src/ directory into your application
or somewhere under your include path.
2) Only one file, DBALite.php, must be included in your application.
3) Create an array with your connection settings. The array should have the following
keys:
'dbname' => 'Name of the database',
'host' => 'Hostname server is located on, usually localhost',
'username => 'database username',
'password' => 'database password'
NOTE: Only 'dbname' is required for Sqlite.
4) Call the DBALite::factory() method with the name of the driver as first
parameter and the configuration array as second parameter.
$my_connection = DBALite::factory('mysql', $db_config);

Things you should be aware of:
DBALite does weveral things for your automatically. It will throw exceptions
on all errors. The excpetion is of class DBALite_Exception. Use the
__toString() magic method to get the most informative error message.
echo (string) $exception;
The DBALite_Exception class encapulates PDOExceptions and will show
the PDO error message if the __toString() method is used.
DBALite assumes you want associative arrays when you fetch results.
You can change this default. See the options in DBALite_DriverAbstract.
On MySQL buffered queries are used to avoid the errors associated with
the default of unbuffered queries. Also the connection assumes UTF-8
encoding. (The command SET NAMES 'utf-8' is run after the connection
is opened. That is always how I program, with everything in UTF-8.

I plan to put more documentation up along with a bugtracker when I get time.
