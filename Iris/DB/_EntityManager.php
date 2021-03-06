<?php

namespace Iris\DB;

/*
 * This file is part of IRIS-PHP, distributed under the General Public License version 3.
 * A copy of the GNU General Public Version 3 is readable in /library/gpl-3.0.txt.
 * More details about the copyright may be found at
 * <http://irisphp.org/copyright> or <http://www.gnu.org/licenses/>
 *  
 * @copyright 2011-2015 Jacques THOORENS
 */

/**
 * IRIS_PARENT, IRIS_CHILDREN and IRIS_FILESEP are used to detect pseudo fields. They
 * can be changed in case of field naming convention problems with an existing
 * database. The change must be done as soon as possible (in index.php or
 * in Bootstrap class) and has a global scope in all the application.
 * For new databases, it is better to avoid field names containing these
 * patterns. 
 */
defined('IRIS_PARENT') or define('IRIS_PARENT', '_at_');
defined('IRIS_CHILDREN') or define('IRIS_CHILDREN', '_children_');
defined('IRIS_FIELDSEP') or define('IRIS_FIELDSEP', '__');


/**
 * This class creates objects based on DSN, UserName and 
 * Password. One of them is conserved as the default.
 * Each _EntityManager can be used to access data in
 * a database. Concrete entity managers are instancied
 * from classes in namespace dialects.
 * 
 * @author Jacques THOORENS (irisphp@thoorens.net)
 * @see http://irisphp.org
 * @license GPL version 3.0 (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: $ */
abstract class _EntityManager {

    const FK_TABLE = 0;
    const FK_FROM = 1;
    const FK_TO = 2;
    const PATH = '\Iris\DB\Dialects\\';

    /**
     * The recognized DBMS
     */
    const MYSQL = 1;
    const SQLITE = 2;
    const POSTGRESQL = 3;
    const ORACLE = 4;

    private static $_DBClasses = [
        self::MYSQL => 'Em_PDOmySQL',
        self::SQLITE => 'Em_PDOSQLite',
        self::POSTGRESQL => 'Em_PDOPostgresql',
        self::ORACLE => 'EmPDO_Oracle'
    ];
    private static $_DBNames = [
        self::MYSQL => 'mysql',
        self::SQLITE => 'sqlite',
        self::POSTGRESQL => 'postgresql',
        self::ORACLE => 'oracle'
    ];
    public static $LeftLimits = "SELECT min(%s) First, max(%s) Previous FROM %s WHERE %s < '%s'";
    public static $RightLimits = "SELECT max(%s) Last, min(%s) Next FROM %s WHERE %s > '%s'";
    private static $_Repository = array();

    /**
     * Mainly for test purpose (permits to place models in other places)
     * @var string 
     */
    protected static $_EntityPath = '\\models';

    /**
     * Instance privilégiée
     * @var _EntityManager
     */
    private static $_Instance = NULL;

    /**
     *
     * @var type 
     * @todo : deprecated ???
     */
    protected static $_Options = [];

    /**
     * An array repository with all entities 
     * 
     * @var _Entity[]
     */
    private $_entityRepository = [];

    
   
    /**
     * Converts a data base system number to its equivalent name
     * 
     * @param int $type
     * @return string
     */
    public static function DBName($type) {
        return self::$_DBNames[$type];
    }

    /**
     * Converts a data base system number to its equivalent class name
     * 
     * @param int $type
     * @return string
     */
    public static function DBClass($type) {
        return self::$_DBClasses[$type];
    }

    /**
     * Converts a database system name to its equivalent number
     * 
     * @param string $name
     * @return int
     */
    public static function DBNumber($name) {
        return array_search($name, self::$_DBNames);
    }

    /**
     * Only first instance is registred. Another instance
     * will be replaced
     * 
     * @param _Entity $entity 
     */
    public function registerEntity(&$entity) {
        $entityName = $entity->getEntityName();
        // if another instance exists, replace the new one
        if (isset($this->_entityRepository[$entityName])) {
            $entity = $this->_entityRepository[$entityName];
        }
        else {
            $this->_entityRepository[$entityName] = $entity;
            $entity->setEntityManager($this);
        }
    }

    /**
     * Tries to unregister an entity. Throws an exception if it exists and
     * contains objects. This methods should only be used with caution
     * and in try catch context
     * 
     * @param string $entityName
     * @throws \Iris\Exceptions\EntityException
     */
    public function unregisterEntity($entityName) {
        if (isset($this->_entityRepository[$entityName])) {
            $entity = $this->_entityRepository[$entityName];
            if ($entity->hasObjects()) {
                throw new \Iris\Exceptions\EntityException('You cannot unregister an entity when it has instanciated objects');
            }
            unset($this->_entityRepository[$entityName]);
        }
    }

    /**
     * Counts how many entities are registred
     * 
     * @return int
     */
    public function entityCount() {
        return count($this->_entityRepository);
    }

    public function extractEntity($entityName) {
        if (isset($this->_entityRepository[$entityName])) {
            $entity = $this->_entityRepository[$entityName];
        }
        else {
            $entity = \NULL;
        }
        return $entity;
    }

    protected static function _GetNew($manager, $id, $dsn, $username, $passwd, &$options = []) {
        if (!isset(self::$_Repository[$id])) {
            //$entityManager = new $manager($dsn, $username, $passwd, $options);
            self::$_Repository[$id] = new $manager($dsn, $username, $passwd, $options);
        }
        else {
            
        }
        return self::$_Repository[$id];
    }

    /**
     * The constructor mustn't be used except in a factory
     * 
     * @param String $dsn : Data Source Name
     * @param String $username : user login name
     * @param String $passwd : user password
     * @param boolean $default : if TRUE store this EM as default
     * @param string[] $options additional options
     */
    protected abstract function __construct($dsn, $username, $passwd, &$options = []);
//    {
//        foreach (static::$_Options as $key => $value) {
//            $options[$key] = $value;
//        }
//    }

    /**
     * Return the default instance (creating it if necessary)
     * 
     * @return _EntityManager 
     */
    public static function GetInstance() {
        if (is_null(self::$_Instance)) {
            self::$_Instance = self::_AutoInstance();
        }
        return self::$_Instance;
    }

    /**
     * Sets a default instance, by bypassing the global parameters.
     * Use with caution!
     * 
     * @param _EntityManager $instance
     */
    public static function SetInstance($instance) {
        self::$_Instance = $instance;
    }

    /**
     * Creates the default entity manager as defined in Memory (by means
     * of a parameter file)
     * 
     * @return _EntityManager 
     */
    protected static function _AutoInstance() {
        $memory = \Iris\Engine\Memory::GetInstance();
        $siteMode = \Iris\Engine\Mode::GetSiteMode();
        $params = $memory->Get('param_database', \NULL);
        if (is_null($params)) {
            throw new \Iris\Exceptions\DBException('No database parameters found');
        }
        /* @var $param \Iris\SysConfig\Config */
        $param = $params[$siteMode];
        $dsn = self::_DsnFormater($param);
        $username = $param->database_username;
        $passwd = $param->database_password;
        return self::EMFactory($dsn, $username, $passwd);
    }

    /**
     *
     * @param \Iris\SysConfig\Config $param
     * @return type 
     */
    private static function _DsnFormater($param) {
        $ManagerClass = '\\Iris\\DB\\Dialects\\' . self::_GetDBType($param->database_adapter);
        return $ManagerClass::_GetDsn($param);
    }

    /**
     *
     * @param \Iris\SysConfig\Config $param
     * @return string 
     */
    protected static function _GetDsn($param) {
        return sprintf("%s:host=%s;dbname=%s;", $param->database_adapter, $param->database_host, $param->database_dbname);
    }

    public static function GetEntityPath() {
        return self::$_EntityPath;
    }

    public static function SetEntityPath($entityPath) {
        self::$_EntityPath = $entityPath;
    }

    /**
     *
     * @param string $dsn
     * @param string $username
     * @param string $passwd
     * @param boolean $default
     * @param mixed $options
     * @return _EntityManager
     * @deprecated since january 2016 
     */
    public static function EMFactory($dsn, $username = \NULL, $passwd = \NULL, $options = []) {
        $type = strtok($dsn, ':');
        $typeNumber = self::DBNumber($type);
        return self::_EMFactory($typeNumber, -1, $dsn, $username, $passwd, $options);
    }

    /**
     *
     * @param string $dsn
     * @param string $username
     * @param string $passwd
     * @param boolean $default
     * @param mixed $options
     * @return _EntityManager 
     */
    protected static function _EMFactory($type, $id, $dsn, $username = \NULL, $passwd = \NULL, $options = []) {
        if(isset(self::$_Repository[$id])){
            return self::$_Repository[$id];
        }
        if (!is_string($dsn)) {
            throw new \Iris\Exceptions\NotSupportedException('No analyse written of config');
            //@todo extraire le dsn, username et password
        }
        if (!empty($dsn)) {
            switch ($type) {
                case self::MYSQL:
                    $class = self::DBClass(self::MYSQL);
                    break;
                case self::SQLITE :
                    $class = self::DBClass(self::SQLITE);
                    break;
                case self::POSTGRESQL:
                    $class = self::DBClass(self::POSTGRESQL);
                    break;
                case self::MYSQL:
                    $class = self::DBClass(self::ORACLE);
                    break;
                default:
                    throw new \Iris\Exceptions\NotSupportedException('Invalid database type');
            }
        }
        $manager = self::PATH . $class;
        try {
            if ($id == -1)
                $id = $dsn;
            $entityManager = self::_GetNew($manager, $id, $dsn, $username, $passwd, $options);
        }
        catch (Exception $exc) {
            $message = $exc->getMessage();
            $code = $exc->getCode();
            throw new \Iris\Exceptions\DBException('Error opening the database. Check parameters');
        }
        return $entityManager;
    }

    public static function EMByNumber($entityNumber = 0, &$options = []) {
        // the number $InternalDatabaseNumber permits to access the internal database for admin toolbar
        if ($entityNumber == \Iris\SysConfig\Settings::$InternalDatabaseNumber) {
            $fileName = IRIS_INTERNAL;
            return self::_EMFactory(self::SQLITE, $entityNumber, "sqlite:$fileName");
        }
        // the number $AdDatabaseNumber permits to access the internal database for adds in demo site
        elseif ($entityNumber == \Iris\SysConfig\Settings::$AdDatabaseNumber) {
            $fileName = IRIS_AD;
            return self::_EMFactory(self::SQLITE, $entityNumber, "sqlite:$fileName");
        }
        $varName = 'param_database';
        // database.ini has no appended number
        if ($entityNumber != 0) {
            iris_debug($entityNumber);
            $varName .= $entityNumber;
        }
        $params = \Iris\Engine\Memory::Get($varName);
        $mode = \Iris\Engine\Mode::GetSiteMode();
        $param = $params[$mode];
        $adapterName = $param->database_adapter;
        $adapterNumber = self::DBNumber($adapterName);
        $host = $param->database_host;
        $dbname = $param->database_dbname;
        if($adapterNumber == self::SQLITE){
            //die('sqlite');
            $dsn = "$adapterName:$param->database_file";
        }
        else{//die('not sqlite');
            $dsn = "$adapterName:host=$host;dbname=$dbname";
        }
        $username = $param->database_username;
        $password = $param->database_password;
        return self::_EMFactory($adapterNumber, $entityNumber, $dsn, $username, $password, $options);

        /*
          database_adapter=mysql
          database_dbname=u3a
          database_host=localhost
          database_username=u3a
          database_password=codd

          ; * in normal cases, these two settings are all right
          database_charset=utf8
          database_collate=utf8_general_ci */
        ;
    }

    /**
     * Each adapter must provide a way to obtain a connexion
     * 
     * @return PDO (not always)
     */
    abstract public function getConnexion();

    /**
     * Returns the adapter class name by analysing the dsn
     * 
     * @param string $dsn
     * @return string 
     * CAUTION: this method must be modified each time a new dialect is added
     * to the framework (see todo below)
     */
    private static function _GetDBType($dsn) {
        //@todo It should be possible to scan Dialects folder to found all
        // adapters
        $prefix = strtok($dsn, ':');
        switch ($prefix) {
            case 'mysql':
                $type = 'Em_PDOmySQL';
                break;
            case 'sqlite':
                $type = 'Em_PDOSQLite';
                break;
            default:
                throw new \Iris\Exceptions\NotSupportedException('DB not supported or unknown.');
        }
        return $type;
    }

    /**
     * Executes a direct SQL query on the connexion
     * 
     * @param type $sql
     * @return \PDOStatement
     */
    public abstract function directSQLQuery($sql);

    /**
     * Executes a direct SQL query on the connexion
     * 
     * @param type $sql
     * @return \PDOStatement
     */
    public abstract function directSQLExec($sql);

    /**
     * Execute a select query on the current database, returning an array of
     * Objects (found in the repository or freshly created)
     * 
     * @param _Entity $entity
     * @param string $sql
     * @param type $fieldsPH
     * @return array(Object) 
     */
    public function fetchAll(_Entity $entity, $sql, $fieldsPH = []) {
        $results = $this->getResults($sql, $fieldsPH);
        $objects = [];
        $objectType = $entity->getRowType();
        foreach ($results as $result) {
            $identifier = $this->_getIdentifier($entity, $result);
            $object = $entity->retrieveObject($identifier);
            if (is_null($object)) {
                $object = new $objectType($entity, $identifier, $result);
            }
            $objects[] = $object;
        }
        return $objects;
    }

    private function _getIdentifier($entity, $result) {
        $identifier = [];
        foreach ($entity->getIdNames() as $id) {
            if (isset($result[$id])) {
                $identifier[$id] = $result[$id];
            }
        }
        if (count($identifier) == 0) {
            foreach ($result as $field => $value) {
                $identifier[$field] = $value;
            }
        }
        return $identifier;
    }

    /**
     * @return array 
     */
    abstract public function getResults($sql, $fieldsPH = []);

    abstract public function exec($sql, $value);

    /**
     * @param string $tableName The table name corresponding to the entity
     * @return \Iris\DB\Metadata The metadata corresponding to the table
     */
    public abstract function readFields($tableName);

    /**
     * @return array(ForeignKey)
     */
    public abstract function getForeignKeys($tableName);

    /**
     * Returns the table list of the database
     * 
     * @parameter boolean $views if false does not list views
     * @return array
     */
    public abstract function listTables($views = \TRUE);

    public abstract function lastInsertedId($entity);

    /**
     * Returns a format string to manage bitwise AND operations
     *
     * @return sting
     */
    public function bitAnd() {
        return "%s & %s";
    }

    /**
     * Returns a format string to manage bitwise OR operations
     * 
     * @return string
     */
    public function bitOr() {
        return "%s | %s";
    }

    /**
     * Returns a format string to manage bitwise XOR operations
     * 
     * @return string
     */
    public abstract function bitXor();

    /**
     * By default, LIMIT is not supported. May be overriden in some EM.
     * 
     * @return string
     */
    public function getLimitClause() {
        return \NULL;
    }

}
