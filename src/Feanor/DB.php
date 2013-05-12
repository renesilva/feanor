<?php
/**
 * Clase DB
 *
 * Implemente a PDO, básicamente porque no deseo recordar las funciones
 * y quisiera que el IDE haga su trabajo con ctrl+espacio.
 *
 * El gran problema fue con las funciones no estáticas de PDO y necesitaba
 * una clase como esta.
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor;

class DB {
	/**
	 *
	 * @var \PDO
	 */
	public static $connection;
	public static $connected_db = false;
	public static $dbprefix_str = '';
	
	public function __construct () {
		try {
			if(Config::get('connection_string') != ''){
				if(Config::get('connection_user') != ''){
					self::$connection = new \PDO(
						Config::get('connection_string'),
						Config::get('connection_user'),
						Config::get('connection_password'),
						array(\PDO::MYSQL_ATTR_FOUND_ROWS => true)
				  );
					self::$connection->exec('set names utf8');
					self::$dbprefix_str = Config::get('connection_dbprefix','');
				} else {
					self::$connection = new \PDO(Config::get('connection_string'));
				}
				//por defecto los errores como exceptions
				self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				//Por defecto en asociativo
				self::$connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
			}
		} catch (\PDOException $err) {
			echo 'Error: '.$err->getMessage();
			var_dump(debug_backtrace());
			exit();
		}
	}

	/**
	 * Coloca el prefix
	 *
	 * @param string $table
	 * @return string
	 */
	public static function dbprefix($table = ''){
		return self::$dbprefix_str.$table;
	}

	
	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Initiates a transaction
	 * @link http://php.net/manual/en/pdo.begintransaction.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public static function beginTransaction () {
		return self::$connection->beginTransaction();
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Commits a transaction
	 * @link http://php.net/manual/en/pdo.commit.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public static function commit () {
		return self::$connection->commit();
	}
	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Fetch the SQLSTATE associated with the last operation on the database handle
	 * @link http://php.net/manual/en/pdo.errorcode.php
	 * @return mixed an SQLSTATE, a five characters alphanumeric identifier defined in
	 * the ANSI SQL-92 standard. Briefly, an SQLSTATE consists of a
	 * two characters class value followed by a three characters subclass value. A
	 * class value of 01 indicates a warning and is accompanied by a return code
	 * of SQL_SUCCESS_WITH_INFO. Class values other than '01', except for the
	 * class 'IM', indicate an error. The class 'IM' is specific to warnings
	 * and errors that derive from the implementation of PDO (or perhaps ODBC,
	 * if you're using the ODBC driver) itself. The subclass value '000' in any
	 * class indicates that there is no subclass for that SQLSTATE.
	 * </p>
	 * <p>
	 * <b>PDO::errorCode</b> only retrieves error codes for operations
	 * performed directly on the database handle. If you create a PDOStatement
	 * object through <b>PDO::prepare</b> or
	 * <b>PDO::query</b> and invoke an error on the statement
	 * handle, <b>PDO::errorCode</b> will not reflect that error.
	 * You must call <b>PDOStatement::errorCode</b> to return the error
	 * code for an operation performed on a particular statement handle.
	 * </p>
	 * <p>
	 * Returns <b>NULL</b> if no operation has been run on the database handle.
	 */
	public static function errorCode () {
		return self::$connection->errorCode();
	}
	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Fetch extended error information associated with the last operation on the database handle
	 * @link http://php.net/manual/en/pdo.errorinfo.php
	 * @return array <b>PDO::errorInfo</b> returns an array of error information
	 * about the last operation performed by this database handle. The array
	 * consists of the following fields:
	 * <tr valign="top">
	 * <td>Element</td>
	 * <td>Information</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>0</td>
	 * <td>SQLSTATE error code (a five characters alphanumeric identifier defined
	 * in the ANSI SQL standard).</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>1</td>
	 * <td>Driver-specific error code.</td>
	 * </tr>
	 * <tr valign="top">
	 * <td>2</td>
	 * <td>Driver-specific error message.</td>
	 * </tr>
	 * </p>
	 * <p>
	 * If the SQLSTATE error code is not set or there is no driver-specific
	 * error, the elements following element 0 will be set to <b>NULL</b>.
	 * </p>
	 * <p>
	 * <b>PDO::errorInfo</b> only retrieves error information for
	 * operations performed directly on the database handle. If you create a
	 * PDOStatement object through <b>PDO::prepare</b> or
	 * <b>PDO::query</b> and invoke an error on the statement
	 * handle, <b>PDO::errorInfo</b> will not reflect the error
	 * from the statement handle. You must call
	 * <b>PDOStatement::errorInfo</b> to return the error
	 * information for an operation performed on a particular statement handle.
	 */
	public static function errorInfo () {
		return self::$connection->errorInfo();
	}
	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Execute an SQL statement and return the number of affected rows
	 * @link http://php.net/manual/en/pdo.exec.php
	 * @param string $statement <p>
	 * The SQL statement to prepare and execute.
	 * </p>
	 * <p>
	 * Data inside the query should be properly escaped.
	 * </p>
	 * @return int <b>PDO::exec</b> returns the number of rows that were modified
	 * or deleted by the SQL statement you issued. If no rows were affected,
	 * <b>PDO::exec</b> returns 0.
	 * </p>
	 * This function may
	 * return Boolean <b>FALSE</b>, but may also return a non-Boolean value which
	 * evaluates to <b>FALSE</b>. Please read the section on Booleans for more
	 * information. Use the ===
	 * operator for testing the return value of this
	 * function.
	 * <p>
	 * The following example incorrectly relies on the return value of
	 * <b>PDO::exec</b>, wherein a statement that affected 0 rows
	 * results in a call to <b>die</b>:
	 * <code>
	 * $db->exec() or die(print_r($db->errorInfo(), true));
	 * </code>
	 */
	public static function exec ($statement) {
		return self::$connection->exec($statement);
	}
	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.2.0)<br/>
	 * Retrieve a database connection attribute
	 * @link http://php.net/manual/en/pdo.getattribute.php
	 * @param int $attribute <p>
	 * One of the PDO::ATTR_* constants. The constants that
	 * apply to database connections are as follows:
	 * PDO::ATTR_AUTOCOMMIT
	 * PDO::ATTR_CASE
	 * PDO::ATTR_CLIENT_VERSION
	 * PDO::ATTR_CONNECTION_STATUS
	 * PDO::ATTR_DRIVER_NAME
	 * PDO::ATTR_ERRMODE
	 * PDO::ATTR_ORACLE_NULLS
	 * PDO::ATTR_PERSISTENT
	 * PDO::ATTR_PREFETCH
	 * PDO::ATTR_SERVER_INFO
	 * PDO::ATTR_SERVER_VERSION
	 * PDO::ATTR_TIMEOUT
	 * </p>
	 * @return mixed A successful call returns the value of the requested PDO attribute.
	 * An unsuccessful call returns null.
	 */
	public static function getAttribute ($attribute) {
		return self::$connection->getAttribute($attribute);
	}
	/**
	 * (PHP 5 &gt;= 5.1.3, PECL pdo &gt;= 1.0.3)<br/>
	 * Return an array of available PDO drivers
	 * @link http://php.net/manual/en/pdo.getavailabledrivers.php
	 * @return array <b>PDO::getAvailableDrivers</b> returns an array of PDO driver names. If
	 * no drivers are available, it returns an empty array.
	 */
	public static function getAvailableDrivers () {
		return self::$connection->getAvailableDrivers();
	}

	/**
	 * (PHP 5 &gt;= 5.3.3, Bundled pdo_pgsql)<br/>
	 * Checks if inside a transaction
	 * @link http://php.net/manual/en/pdo.intransaction.php
	 * @return bool <b>TRUE</b> if a transaction is currently active, and <b>FALSE</b> if not.
	 */
	public static function inTransaction () {
		return self::$connection->inTransaction();
	}
	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Returns the ID of the last inserted row or sequence value
	 * @link http://php.net/manual/en/pdo.lastinsertid.php
	 * @param string $name [optional] <p>
	 * Name of the sequence object from which the ID should be returned.
	 * </p>
	 * @return string If a sequence name was not specified for the <i>name</i>
	 * parameter, <b>PDO::lastInsertId</b> returns a
	 * string representing the row ID of the last row that was inserted into
	 * the database.
	 * </p>
	 * <p>
	 * If a sequence name was specified for the <i>name</i>
	 * parameter, <b>PDO::lastInsertId</b> returns a
	 * string representing the last value retrieved from the specified sequence
	 * object.
	 * </p>
	 * <p>
	 * If the PDO driver does not support this capability,
	 * <b>PDO::lastInsertId</b> triggers an
	 * IM001 SQLSTATE.
	 */
	public static function lastInsertId ($name = null) {
		return self::$connection->lastInsertId($name);
	}
	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Prepares a statement for execution and returns a statement object
	 * @link http://php.net/manual/en/pdo.prepare.php
	 * @param string $statement <p>
	 * This must be a valid SQL statement for the target database server.
	 * </p>
	 * @param array $driver_options [optional] <p>
	 * This array holds one or more key=&gt;value pairs to set
	 * attribute values for the PDOStatement object that this method
	 * returns. You would most commonly use this to set the
	 * PDO::ATTR_CURSOR value to
	 * PDO::CURSOR_SCROLL to request a scrollable cursor.
	 * Some drivers have driver specific options that may be set at
	 * prepare-time.
	 * </p>
	 * @return \PDOStatement If the database server successfully prepares the statement,
	 * <b>PDO::prepare</b> returns a
	 * <b>PDOStatement</b> object.
	 * If the database server cannot successfully prepare the statement,
	 * <b>PDO::prepare</b> returns <b>FALSE</b> or emits
	 * <b>PDOException</b> (depending on error handling).
	 * </p>
	 * <p>
	 * Emulated prepared statements does not communicate with the database server
	 * so <b>PDO::prepare</b> does not check the statement.
	 */
	public static function prepare ($statement, $driver_options = array()) {
		return self::$connection->prepare($statement, $driver_options);
	}
	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.2.0)<br/>
	 * Executes an SQL statement, returning a result set as a PDOStatement object
	 * @link http://php.net/manual/en/pdo.query.php
	 * @param string $statement <p>
	 * The SQL statement to prepare and execute.
	 * </p>
	 * <p>
	 * Data inside the query should be properly escaped.
	 * </p>
	 * @return \PDOStatement <b>PDO::query</b> returns a PDOStatement object, or <b>FALSE</b>
	 * on failure.
	 */
	public static function query ($statement) {
		try {
			return self::$connection->query($statement);
		} catch(\PDOException $err){
			echo $err->getMessage().'<br/>
				[Funci&oacute;n (::query)] Query: <strong>'.$statement.'</strong>';
			var_dump(debug_backtrace());
		}
	}
	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.2.1)<br/>
	 * Quotes a string for use in a query.
	 * @link http://php.net/manual/en/pdo.quote.php
	 * @param string $string <p>
	 * The string to be quoted.
	 * </p>
	 * @param int $parameter_type [optional] <p>
	 * Provides a data type hint for drivers that have alternate quoting styles.
	 * </p>
	 * @return string a quoted string that is theoretically safe to pass into an
	 * SQL statement. Returns <b>FALSE</b> if the driver does not support quoting in
	 * this way.
	 */
	public static function quote ($string, $parameter_type = 'PDO::PARAM_STR') {
		return self::$connection->quote($string, $parameter_type);
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Rolls back a transaction
	 * @link http://php.net/manual/en/pdo.rollback.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public static function rollBack () {
		return self::$connection->rollBack();
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Set an attribute
	 * @link http://php.net/manual/en/pdo.setattribute.php
	 * @param int $attribute
	 * @param mixed $value
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public static function setAttribute ($attribute, $value) {
		return self::$connection->setAttribute($attribute, $value);
	}
}


