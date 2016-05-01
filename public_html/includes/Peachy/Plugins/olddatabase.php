<?php

/*
This file is part of Peachy MediaWiki Bot API

Peachy is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class OldDatabase {
	/**
	 * MySQL object
	 * @var object
	 */
	private $mConn;
	
	/**
	 * Read-only mode
	 * @var bool
	 */
	private $mReadonly;
	
	/**
	 * Whether or not to use PostgreSQL
	 * @var bool
	 */
	private $mPG = false;
	
	/**
	 * Various parameters
	 */
	private $mHost;
	private $mPort;
	private $mUser;
	private $mPass;
	private $mDb;
	private $mPrefix;
	private $mysqli = true;
	
	/**
	 * Construct function, front-end for mysql_connect.
	 * @param string $host Server to connect to
	 * @param string $port Port
	 * @param string $user Username
	 * @param string $pass Password
	 * @param string $db Database
	 * @param string $prefix Prefix of the tables in the database. Default ''
	 * @param bool $readonly Read-only mode. Default false
	 * @return void
	 */
	public function __construct( $host, $port, $user, $pass, $db, $prefix, $readonly, $mysqli = true ) {
		if( $this->mPG ) {
			if( !function_exists( 'pg_connect' ) ) {
				throw new DependancyError( "PostgreSQL", "http://us2.php.net/manual/en/book.pgsql.php" );
			}
		}
		else {
			if( !function_exists( 'mysql_connect' ) && !class_exists( 'mysqli' ) ) {
				throw new DependancyError( "MySQL", "http://us2.php.net/manual/en/book.mysql.php" );
			}
		}
		
		$this->mHost = $host;
		$this->mPort = $port;
		$this->mUser = $user;
		$this->mPass = $pass;
		$this->mDb = $db;
		$this->mPrefix = $prefix;
		$this->mReadonly = $readonly;
		$this->mysqli = $mysqli;
	}

	/**
	 * Load function, initializes Database class
	 * @param Database &$newclass Where to store the class once it's initialized
	 * @param string $host Server to connect to
	 * @param string $port Port
	 * @param string $user Username
	 * @param string $pass Password
	 * @param string $db Database
	 * @param string $prefix Prefix of the tables in the database. Default ''
	 * @param bool $readonly Read-only mode. Default false
	 * @param bool $mysqli Whether or not to use mysqli. Default true
	 * @return void
	 */
	public static function load( &$newclass = null, $host, $port, $user, $pass, $db, $prefix = '', $readonly = false, $mysqli = true ) {
		
		Hooks::runHook( 'LoadDatabase', array( &$host, &$port, &$user, &$pass, &$db, &$prefix, &$readonly, &$mysqli ) );
		
		$newclass = new Database( $host, $port, $user, $pass, $db, $prefix, $readonly, $mysqli );
	}
	
	/**
	 * Tells Database class to use PostgreSQL functions instead of MySQL functions
	 *
	 * @return void
	 */
	public function setPostgre() {
		$this->mPG = true;
	}

	/**
	 * Connect to the my/pgsql server
	 *
	 * @return void
	 */
	private function connectToServer() {
		if( $this->mPG ) {
			$this->mConn = pg_connect("host={$this->mHost} port={$this->mPost} dbname={$this->mDb} user={$this->mUser} password={$this->mPass}");
		}
		else {
			if( !class_exists( 'mysqli' ) || $this->mysqli == false ) {
				$this->mConn = mysql_connect( $this->mHost.':'.$this->mPort, $this->mUser, $this->mPass, $force );
				mysql_select_db( $this->mDb, $this->mConn );
				$this->mysqli = false;
			}
			else {
				$this->mConn = new mysqli( $this->mHost, $this->mUser, $this->mPass, $this->mDb, $this->mPort );
			}
		}
	
	}
	
	/**
	 * Returns the table prefix
	 * @access public
	 * @return string Table prefix
	 */
	public function getPrefix() {
		return $this->mPrefix;
	}
	
	/**
	 * Destruct function. Closes the connection to the database.
	 * @return void
	 */	 
	public function __destruct() {
		if( $this->mPG ) {
			pg_close( $this->mConn );
		}
		else {
			if (!$this->mysqli) {
				mysql_close( $this->mConn );
			} else {
				$this->mConn->close();
			}
		}
	}
	
	/**
	 * Front-end for mysql_query. It's preferred to not use this function, 
	 * and rather the other Database::select, update, insert, and delete functions.
	 * @param string $sql Raw DB query
	 * @return array|bool Array of results, false if there's no result
	 */
	public function doQuery( $sql ) {
		if( is_null( $this->mConn ) ) $this->connectToServer();
		
		$sql = trim($sql);		
		
		if( $this->mPG ) {
			$result = pg_query( $this->mConn, $sql );
		}
		else {
			if (!$this->mysqli) {
				$result = mysql_query( $sql, $this->mConn );
			} else {
				$result = $this->mConn->query( $sql );
			}
			
			if ( $this->errorNo() == 2006 ) {
				$this->connectToServer();
				if (!$this->mysqli) {
					$result = mysql_query( $sql, $this->mConn );
				} else {
					$result = $this->mConn->query( $sql );
				}
			}
		}
		
		if( !$result ) return false;
		return $this->mysql2array( $result );
	}
	
	/**
	 * Sends a prepared statment using PHP's mysqli functions.
	 * @param string $query The database query.
	 * @param string $bind A string containing the datatypes to bind to the query (i=int,s=string,d=double,b=blob)
	 * @param array $values An array containg the values to bind to the query.
	 * @return object Returns a MySQLi_STMT object.
	 */
	public function preparedStmt( $query, $bind=null, $values=array() ) {
		if ($this->mysqli==false) throw new DBError( "Not in mysqli mode. PHP's mysqli functions are required." );
		
		$stmt = $this->mConn->prepare($query);
		$params = array_merge(array($bind),$values);
		
       		$tmp = array();
        	foreach($params as $key => $value) $tmp[$key] = &$params[$key];

		call_user_func_array(array($stmt, "bind_param"), $tmp);
		$stmt->execute();
		
		return $stmt;
	}
	
	/**
	 * Returns a string description of the last MySQL error
	 * @return string|bool MySQL error string, null if no error
	 */
	public function errorStr( ) {
		if( $this->mPG ) throw new DBError( "MySQLOnly", "Database::errorStr is only available for MySQL" );
		if (!$this->mysqli) {
			$result = mysql_error( $this->mConn );
		} else {
			$result = $this->mConn->error;
		}
		if( !$result ) return false;
		return $result;
	}
	
	/**
	 * Returns the error code of the last MySQL call
	 * @return int|bool MySQL error code, null if no error
	 */
	public function errorNo( ) {
		if( $this->mPG ) throw new DBError( "MySQLOnly", "Database::errorNo is only available for MySQL" );
		if (!$this->mysqli) {
			$result = mysql_errno( $this->mConn );
		} else {
			$result = $this->mConn->errno;
		}
		if( !$result ) return false;
		return $result;
	}
	
	/**
	 * Front-end for mysql_real_escape_string
	 * @param string $data Data to escape
	 * @return string Escaped data
	 */
	public function mysqlEscape( $data ) {
		if( $this->mPG ) {
			return $this->pgsqlEscape( $data );
		}
		
		if (!$this->mysqli) {
			return mysql_real_escape_string( $data, $this->mConn );
		} else {
			return $this->mConn->real_escape_string( $data );
		}
	}
	
	/**
	 * Front-end for pg_escape_string
	 * @param string $data Data to escape
	 * @return string Escaped data
	 */
	public function pgsqlEscape( $data ) {
		return pg_escape_string( $this->mConn, $data );
	}
	
	/**
	 * Shortcut for converting a MySQL result object to a plain array.
	 * Note: This is not compatible with results from preparedStmt()
	 * @param object $data MySQL result
	 * @return array Converted result
	 */
	public function mysql2array( $data ) {

		$return = array();
		
		if( $this->mPG ) {
			while( $row = pg_fetch_array( $this->mConn, $data ) ) {
				$return[] = $row;
			}
		}
		else {
			if (!$this->mysqli) {
				while( $row = mysql_fetch_assoc( $data ) ) {
					$return[] = $row;
				}
			} else {
				while( $row = $data->fetch_assoc( ) ) {
					$return[] = $row;
				}
			}
		}

		return $return;
	}
	
	public static function pgsql2array( $data ) {
		return self::mysql2array( $data );
	}
	
	/**
	 * SELECT frontend
	 * @param array|string $table Table(s) to select from. If it is an array, the tables will be JOINed.
	 * @param string|array $fields Columns to return
	 * @param string|array $where Conditions for the WHERE part of the query. Default null.
	 * @param array $options Options to add, can be GROUP BY, HAVING, and/or ORDER BY. Default an empty array.
	 * @param array $join_on If selecting from more than one table, this adds an ON statement to the query. Defualt an empty array.
	 * @return object MySQL object
	 */
	public function select ( $table, $fields, $where = null, $options = array(), $join_on = array() ) {
		if( is_array( $fields ) ) {
			$fields = implode( ',', $fields );
		}
		
		if( !is_array( $options ) ) {
			$options = array( $options );
		}
		
		if( is_array( $table ) ) {
			if( $this->mPrefix != '' ) {
				foreach( $table AS $id => $t ) {
					$table[$id] = $this->mPrefix . $t;
				}
			}
			
			if( count( $join_on ) == 0 ) {
				$from = 'FROM ' . implode( ',', $table );
				$on = null;
			}
			else {
				$tmp = array_shift( $table );
				$from = 'FROM ' . $tmp;
				$from .= ' JOIN ' . implode( ' JOIN ', $table );
				
				$tmp = array_keys( $join_on );
				$on = 'ON ' . $tmp[0] . ' = ' . $join_on[$tmp[0]];
			}
		}
		else {
			$from = 'FROM ' . $this->mPrefix . $table;
			$on = null;
		}
		
		$newoptions = null;
		if ( isset( $options['GROUP BY'] ) ) $newoptions .= "GROUP BY {$options['GROUP BY']}";
		if ( isset( $options['HAVING'] ) ) $newoptions .= "HAVING {$options['HAVING']}";
		if ( isset( $options['ORDER BY'] ) ) $newoptions .= "ORDER BY {$options['ORDER BY']}";
		
		if( !is_null( $where ) ) {
			if( is_array( $where ) ) {
				$where_tmp = array();
				foreach( $where as $wopt ) {
					$tmp = $this->mysqlEscape( $wopt[2] );
					if( $wopt[1] == 'LIKE' ) $tmp = $wopt[2];
					$where_tmp[] = '`' . $wopt[0] . '` ' . $wopt[1] . ' \'' . $tmp . '\'';					
				}
				$where = implode( ' AND ', $where_tmp );
			}
			$sql = "SELECT $fields $from $on WHERE $where $newoptions";
		}
		else {
			$sql = "SELECT $fields $from $on $newoptions";
		}
		
		if (isset($options['LIMIT'])) {
			$sql .= " LIMIT {$options['LIMIT']}";
		}
				
		if (isset($options['EXPLAIN'])) {
			$sql = 'EXPLAIN ' . $sql;
		}
		
		//echo $sql;
		return $this->doQuery( $sql );
	}
	
	/**
	 * INSERT frontend
	 * @param string $table Table to insert into.
	 * @param array $values Values to set.
	 * @param array $options Options
	 * @return object MySQL object
	 */
	public function insert( $table, $values, $options = array() ) {
		
		if( $this->mReadonly == true ) throw new DBError( "Write query called while under read-only mode" );
		if ( !count( $values ) ) {
			return true;
		}
		
		if ( !is_array( $options ) ) {
			$options = array( $options );
		}
		
		$cols = array();
		$vals = array();
		foreach( $values as $col => $value ) {
			$cols[] = "`$col`";
			$vals[] = "'" . $this->mysqlEscape( $value ) . "'";
		}
		
		$cols = implode( ',', $cols );
		$vals = implode( ',', $vals );
		
		$sql = "INSERT " . implode( ' ', $options ) . " INTO {$this->mPrefix}$table ($cols) VALUES ($vals)";

		return $this->doQuery( $sql );
	}
	
	/**
	 * Front-end for mysql_insert_id
	 * @return int The value of the AUTO_INCREMENT field that was updated by the previous query.
	 */
	public function insert_id () {
		if( $this->mReadonly == true ) throw new DBError( "Write function called while under read-only mode" );
		
		if (!$this->mysqli) {
			return mysql_insert_id( $this->mConn );
		} else {
			return $this->mConn->insert_id;
		}
	}
	
	/**
	 * UPDATE frontend
	 * @param string $table Table to update.
	 * @param array $values Values to set.
	 * @param array $conds Conditions to update. Default *, updates every entry.
	 * @return object MySQL object
	 */
	public function update( $table, $values, $conds = '*' ) { 
		if( $this->mReadonly == true ) throw new DBError( "Write query called while under read-only mode" );
		$vals = array();
		foreach( $values as $col => $val ) {
			$vals[] = "`$col`" . "= '" . $this->mysqlEscape( $val ) . "'";
		}
		$vals = implode( ', ', $vals );
		
		$sql = "UPDATE {$this->mPrefix}$table SET " . $vals;
		if ( $conds != '*' ) {
			$cnds = array();
			foreach( $conds as $col => $val ) {
				$cnds[] = "`$col`" . "= '" . $this->mysqlEscape( $val ) . "'";
			}
			$cnds = implode( ', ', $cnds );
			
			$sql .= " WHERE " . $cnds;
		}
		return $this->doQuery( $sql );
	}
	
	/**
	 * DELETE frontend
	 * @param string $table Table to delete from.
	 * @param array $conds Conditions to delete. Default *, deletes every entry.
	 * @return object MySQL object
	 */
	public function delete( $table, $conds ) {
		if( $this->mReadonly == true ) throw new DBError( "Write query called while under read-only mode" );
		$sql = "DELETE FROM {$this->mPrefix}$table";
		if ( $conds != '*' ) {
			$cnds = array();
			foreach( $conds as $col => $val ) {
				$cnds[] = "`$col`" . "= '" . $this->mysqlEscape( $val ) . "'";
			}
			$cnds = implode( ' AND ', $cnds );
			
			$sql .= " WHERE " . $cnds;
		}
		return $this->doQuery( $sql );
	}	
	
}
