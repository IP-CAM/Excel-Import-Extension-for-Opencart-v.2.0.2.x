<?php
require_once('Authentication.php');
Authentication::authenticate() or die('Access Forbidden');

/**
 * Class Database handles the connection with the database and the queries.
 * Also fetches data from it as object, array of objects or simple arrays.
 * The class uses the Singleton Design Pattern. As a result, only one instance
 * of the class is constructed. This one instance can be retrieved statically
 * when calling the getInstance function.
 *
 * @author Sotiris Poulias
 * @since 15/05/2015
 * @version 0.1
 */
class Database {
  
    private $host;                // Hostname
    private $user;                // Username
    private $password;            // Password
    private $db;                  // Database
    private $charset;             // Charset
    private $result;              // Result of query
    private $query;               // Query
    private $error;               // Error
    private $prefix;              // Database prefix

    protected static $_instance;  // Instance of Singleton Database Object
    protected $_mysqli;           // Mysqli Connection Object

    /**
    * Constructor of database object
    */
    private function __construct() {
        if (is_file('../../config.php')) {
            require_once('../../config.php');
        } else {
            return false;
        }

        // Startup
        require_once(DIR_SYSTEM . 'startup.php');

        // Registry
        $registry = new Registry();

        // Database details
        $this->db = DB_DATABASE;
        $this->user = DB_USERNAME;
        $this->password = DB_PASSWORD;
        $this->host = DB_HOSTNAME;
        $this->charset = "utf8";
        $this->connect();
        $this->prefix = DB_PREFIX;
    }

    /**
     * Function to get the latest inserted id
     *
     * @return mixed
     */
    public function getInsertedId() {
        return $this->_mysqli->insert_id;
    }

    /**
    * The function that returns the signleton object.
    * If there is already an object constructed, then it just returns the instance
    */
    public static function getInstance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new Database();
        }
        return self::$_instance;
    }

    /**
    * The function that creates the connection with the database.
    * The connection is stored in _mysqli field of the object
    * If an error occurred, it is stored in error field of the object
    */
    private function connect() {

        if (empty ($this->host)) {
            $this->error = 'Mysql host is not set';
        }

        $this->_mysqli = new mysqli($this->host, $this->user, $this->password, $this->db);

        if(mysqli_connect_errno()) {
            $this->error = mysqli_connect_error();
        }

        $this->_mysqli->set_charset($this->charset);

    }

    /**
    * Closes the result and the connection
    */
    public function close() {
        try {
            $this->result->close();
            $this->_mysqli->close();
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    /**
    * Creates the query in the database and gets the result.
    * Also, checks if there is any errors. If there are any,
    * sets them in the error field of the object.
    *
    * @param String $query
    */
    public function query($query = NULL) {
        if(!is_null($query)) {
            $this->query = $query;
        }
        $this->result = $this->_mysqli->query($this->query);
        if(!$this->result) {
            $this->error = $this->_mysqli->error;
        }
    }

    /**
    * Function to load the rows from the result in an array.
    * Checks if the result is empty.
    *
    * @return mixed $array
    */
    public function loadResultArray() {
        $array = array();
        $this->query();
        if(!$this->result || is_null($this->result)) {
            return NULL;
        }

        while($row = $this->result->fetch_row()) {
            $array[] = $row;
        }

        return $array;
    }

    /**
    * Function to load the rows return from result as an array
    * of object. Before this, checks if the result is empty.
    *
    * @return mixed $array
    */
    public function loadObjectList() {
        $array = array();
        $this->query();

        if(!$this->result || is_null($this->result)) {
            return NULL;
        }

        while($object = $this->result->fetch_object()) {
            $array[] = $object;
        }
        return $array;
    }

    /**
    * Function to return the object from the result
    * of the query. Checks if the result is not empty
    * and then calls fetch_object.
    *
    * @return mixed $object;
    */
    public function loadObject() {
        $this->query();

        if(!$this->result || is_null($this->result)) {
            return NULL;
        }

        $object = $this->result->fetch_object();
        return $object;
    }

    /**
    * Sets the query field of the object
    *
    * @param $query
    */
    public function setQuery($query) {
        $this->query = $query;
    }

    /**
    * Gets the query field of the object
    *
    * @return String $query
    */
    public function getQuery() {
        return $this->query;
    }

    /**
    * Gets the host field of the object
    *
    * @return String $host
    */
    public function getHost() {
        return $this->host;
    }

    /**
    * Gets the user field of the object
    *
    * @return String $user
    */
    public function getUser() {
        return $this->user;
    }

    /**
    * Gets the password field of the object
    *
    * @return String $password
    */
    public function getPassword() {
        return $this->password;
    }

    /**
    * Gets the db field of the object
    *
    * @return String $db
    */
    public function getDb() {
        return $this->db;
    }

    /**
    * Gets the charset field of the object
    *
    * @return String $charset
    */
    public function getCharset() {
        return $this->charset;
    }

    /**
    * Gets the error field of the object
    *
    * @return String $error
    */
    public function getError() {
        return $this->error;
    }

    public function escape($string) {
        return $this->_mysqli->real_escape_string($string);
    }
    
    public function getPrefix() {
        return $this->prefix;
    }
}
?>