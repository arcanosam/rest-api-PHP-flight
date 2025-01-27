<?php

/**
 * A RESTful API template in PHP based on flight micro-framework.
 *
 * ANYONE IN THE DEVELOPER COMMUNITY MAY USE THIS PROJECT FREELY
 * FOR THEIR OWN DEVELOPMENT SELF-LEARNING OR DEVELOPMENT or LIVE PROJECT
 *
 * @author      Sabbir Hossain Rupom <sabbir.hossain.rupom@hotmail.com>
 * @license	http://www.opensource.org/licenses/mit-license.php ( MIT License )
 *
 * @since       Version 1.0.0
 */
(defined('APP_NAME')) or exit('Forbidden 403');

/**
 * Abstract Base Model Class.
 */
abstract class Model_BaseModel {

    // Table name. Be overridden by the implementation class.
    const TABLE_NAME = '';
    // Created_at whether the column exists. Be overridden by the implementation class, if necessary.
    const HAS_CREATED_AT = true;
    // Updated_at whether the column exists. Be overridden by the implementation class, if necessary.
    const HAS_UPDATED_AT = true;
    // Memcached Validity period
    const MEMCACHED_EXPIRE = 1800; // 30 minutes

    // Cache the column name list on the db

    private static $columnsOnDB = null;

    /**
     * Retrieve records by table ID from the database.
     *
     * @param mixed $id        Table row ID
     * @param PDO   $pdo       Database connection object
     * @param bool  $forUpdate Whether to update the query result
     *
     * @return object Search result as an object of called class
     */
    public static function find($id = null, $pdo = null, $forUpdate = false) {
        if (null == $pdo) {
            $pdo = Flight::pdo();
        }

        if (!isset($id)) {
            throw new System_ApiException(ResultCode::DATABASE_ERROR, '$id must be passed as argument');
        }

        $sql = 'SELECT * FROM ' . static::TABLE_NAME . ' WHERE id = ?';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $stmt = $pdo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $obj = $stmt->fetch(PDO::FETCH_CLASS);

        return $obj;
    }

    /**
     * Based on the specified conditions, return to get only one record from the database.
     *
     * @param array $params    column name the key, associative array whose value is the value to use for the search
     * @param PDO   $pdo       Database connection object
     * @param bool  $forUpdate Whether to update the query result
     *
     * @return object Search result as an object of called class
     */
    public static function findBy($params = array(), $pdo = null, $forUpdate = false) {
        if (null == $pdo) {
            $pdo = Flight::pdo();
        }
        list($conditionSql, $values) = self::constructQuery($params, null, null, $forUpdate);
        $sql = 'SELECT * FROM ' . static::TABLE_NAME . $conditionSql;

        $stmt = $pdo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute($values);

        $obj = $stmt->fetch(PDO::FETCH_CLASS);

        return $obj;
    }

    /**
     * Based on specified criteria, returns all items of the records from the database.
     *
     * @param array  $params    column name the key, associative array whose value is the value to use for the search
     * @param string $order     SQL ORDER BY column, associative array whose value is Direction and key is Column
     * @param array  $limitArgs SQL LIMIT value
     * @param PDO    $pdo       Database connection object
     * @param bool   $forUpdate Whether to update the query result
     *
     * @return PDO PDO fetch class object
     */
    public static function findAllBy($params = array(), $order = null, $limitArgs = null, $pdo = null, $forUpdate = false) {
        if (null == $pdo) {
            $pdo = Flight::pdo();
        }
        list($conditionSql, $values) = self::constructQuery($params, $order, $limitArgs, $forUpdate);
        $sql = 'SELECT * FROM ' . static::TABLE_NAME . $conditionSql;

        $stmt = $pdo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute($values);
        $objs = $stmt->fetchAll();

        return $objs;
    }

    /**
     * Based on specified criteria, returns specific items of the records from the database.
     *
     * @param array  $columns   Column names are values which will be returned in result only
     * @param array  $params    column name the key, associative array whose value is the value to use for the search
     * @param string $order     SQL ORDER BY column, associative array whose value is Direction and key is Column
     * @param array  $limitArgs SQL LIMIT value
     * @param PDO    $pdo       Database connection object
     * @param bool   $forUpdate Whether to update the query result
     *
     * @return PDO PDO fetch class object
     */
    public static function findColumnSpecificData($columns, $params, $order = null, $limitArgs = null, $pdo = null) {
        if (null == $pdo) {
            $pdo = Flight::pdo();
        }

        list($conditionSql, $values) = self::constructQuery($params, $order, $limitArgs, $forUpdate);

        $sql = 'SELECT ' . implode(',', $columns) . ' FROM ' . static::TABLE_NAME . $conditionSql;

        $stmt = $pdo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute($values);
        $objs = $stmt->fetchAll(PDO::FETCH_CLASS, get_called_class());

        return $objs;
    }

    /**
     * Returns the number of records matching the specified condition.
     *
     * @param array $params              associative array with column name as key, value as search value
     * @param PDO   $pdo                 when executing within a transaction, specify the PDO object
     * @param bool  $highPerformanceFlag to count the number of records in table
     *
     * @return int Number of records
     */
    public static function countBy($params = array(), $pdo = null, $highPerformanceFlag = false) {
        if (null == $pdo) {
            $pdo = Flight::pdo();
        }
        $conditionSql = '';
        $values = array();
        if (!empty($params)) {
            list($conditionSql, $values) = self::constructQuery($params);
        }
        $countSql = ' * ';
        if (true === $highPerformanceFlag) {
            $countSql = 'id';
        }

        $sql = 'SELECT count(' . (true === $highPerformanceFlag ? ' id ' : ' * ') . ') as count FROM ' . static::TABLE_NAME . (true === $highPerformanceFlag ? '' : $conditionSql);

        $stmt = $pdo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute($values);
        $record = $stmt->fetch();
        
        return (int) $record->count;
    }

    /**
     * Insert new record to database.
     *
     * @param PDO $pdo
     *
     * @return PDO object
     */
    public function create($pdo = null) {
        if (is_null($pdo)) {
            $pdo = Flight::pdo();
        }
        // Prepare SQL
        list($columns, $values) = $this->getValues();

        $now = Common_DateUtil::getToday();
        $sql = 'INSERT INTO ' . static::TABLE_NAME . ' (' . join(',', $columns);
        $sql .= (true === static::HAS_CREATED_AT ? ',created_at' : '');
        $sql .= (true === static::HAS_UPDATED_AT ? ',updated_at' : '');
        $sql .= ') VALUES (' . str_repeat('?,', count($columns) - 1) . '?';
        $sql .= (true === static::HAS_CREATED_AT ? ",'" . $now . "'" : '');
        $sql .= (true === static::HAS_UPDATED_AT ? ",'" . $now . "'" : '');
        $sql .= ')';
        // INSERT data
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($values);
        $this->id = $pdo->lastInsertId();

        return $result;
    }

    /**
     * To update the object.
     *
     * @param PDO $pdo DB connection Object PDO
     */
    public function update($pdo = null) {
        if (!isset($this->id)) {
            throw new Exception('The ' . get_called_class() . ' is not saved yet.');
        }
        if (is_null($pdo)) {
            $pdo = Flight::pdo();
        }
        // Preparing SQL
        list($columns, $values) = $this->getValues();
        $sql = 'UPDATE ' . static::TABLE_NAME . ' SET ';
        $setStmts = array();
        foreach ($columns as $column) {
            $setStmts[] = $column . '=?';
        }
        $sql .= join(',', $setStmts);
        if (true === static::HAS_UPDATED_AT) {
            $sql .= (empty($setStmts) ? '' : ',') . "updated_at='" . Common_DateUtil::getToday() . "'";
        }
        $sql .= ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($values, array($this->id)));
        // Return updated record count
        return $stmt->rowCount();
    }

    /**
     * Delete the object of specific row ID from table.
     *
     * @param PDO $pdo DB connection Object PDO
     *
     * @return obj PDO query execution result
     */
    public function delete($pdo = null) {
        if (!isset($this->id)) {
            throw new Exception('The ' . get_called_class() . ' is not initiated properly.');
        }
        if (is_null($pdo)) {
            $pdo = Flight::pdo();
        }

        $stmt = $pdo->prepare('DELETE FROM ' . static::TABLE_NAME . ' WHERE id = ?');
        $stmt->bindParam(1, $this->id);
        $result = $stmt->execute();

        return $result;
    }

    /**
     * To check whether there is a column name both in database and model class definition.
     *
     * @param mixed $name
     * */
    public static function hasColumn($name) {
        return in_array($name, static::getColumnsOnDB()) &&
                in_array($name, static::getColumns());
    }

    /**
     * To check whether there is a column in model class column definition.
     *
     * @param mixed $name
     * */
    public static function hasColumnDefined($name) {
        return in_array($name, static::getColumns());
    }

    /**
     * Return to the specified column whether or not to include in JSON.
     *
     * @param string $ column target column name
     * @param mixed $column
     */
    public static function isColumnIncludedInJson($column) {
        $columnDef = static::$columnsOnDB[$column];
        if (isset($columnDef['json'])) {
            return $columnDef['json'];
        }
        // The default is TRUE.
        return true;
    }

    /**
     * Return an associative array for JSON.
     */
    public function toJsonHash() {
        foreach (static::getColumns() as $column) {
            if (static::isColumnIncludedInJson($column)) {
                if ('int' === static::getColumnType($column) && isset($this->{$column})) {
                    $hash[$column] = (int) $this->{$column};
                } elseif ('float' === static::getColumnType($column) && isset($this->{$column})) {
                    $hash[$column] = floatval($this->{$column});
                } elseif ('bool' === static::getColumnType($column) && isset($this->{$column}) && !is_null($this->{$column})) {
                    $hash[$column] = (bool) $this->{$column};
                } else {
                    $hash[$column] = (!isset($this->{$column}) || is_null($this->{$column})) ? '' : $this->{$column};
                }
            }
        }

        return $hash;
    }

    /**
     * Get the cache data.
     *
     * @param unknown_type $key
     * @param mixed        $cacheKey
     */
    public static function getCache($cacheKey) {
        if (Config_Config::getInstance()->isServerCacheEnable()) {
            $memcache = Config_Config::getMemcachedClient();

            return $memcache->get($cacheKey);
        }
        return FALSE;
    }

    /**
     * Save the cache data.
     *
     * @param unknown_type $key
     * @param unknown_type $value
     * @param mixed        $cacheKey
     */
    public static function setCache($cacheKey, $value) {
        if (Config_Config::getInstance()->isServerCacheEnable()) {
            $memcache = Config_Config::getMemcachedClient();
            $call_class = get_called_class();

            $memcache->set($cacheKey, $value, MEMCACHE_COMPRESSED, $call_class::MEMCACHED_EXPIRE);
            return true;
        }
        return false;
    }

    /**
     * Delete the cache.
     *
     * @param unknown_type $key
     * @param mixed        $cacheKey
     */
    public static function deleteCache($cacheKey) {
        if (Config_Config::getInstance()->isServerCacheEnable()) {
            $memcache = Config_Config::getMemcachedClient();
            $memcache->delete($cacheKey);
            return true;
        }
        return false;
    }

    /**
     * To get all the data from Memcache.
     * If it's not registered to Memcache, it is set to Memcache to retrieve from the database.
     *
     * @return mixed array of model objects or null
     */
    public static function getAll() {
        if (Config_Config::getInstance()->isServerCacheEnable()) {
            $key = static::getAllKey();
            // To connect to Memcached, to get the value.
            $memcache = Config_Config::getMemcachedClient();
            $value = $memcache->get($key);
            if (false === $value) {
                // If the value has been set to Memcached, it is set to Memcached to retrieve from the database.
                $value = self::findAllBy(array());
                if ($value) {
                    $memcache->set($key, $value, 0, static::MEMCACHED_EXPIRE);
                }
            }

            return $value;
        }
        return null;
    }

    /**
     * To build a conditional clause and bind the value array of SQL.
     *
     * @param array  $params    column name the key, associative array whose value is the value to use for the search
     * @param string $order     SQL ORDER BY column, associative array whose value is Direction and key is Column
     * @param array  $limitArgs SQL LIMIT value
     * @param bool   $forUpdate whether to update the query
     *
     * @return array Constructed Query
     */
    protected static function constructQuery($params = array(), $order = array(), $limitArgs = null, $forUpdate = false) {
        list($conditions, $values) = self::constructConditions($params);

//        foreach ($params as $k => $v) {
//            if (is_array($v)) {
//                $conditions[] = $k . ' IN (' . implode(',', array_fill(0, count($v), '?')) . ')';
//                $values = array_merge($values, $v);
//            } else {
//                $conditions[] = $k . '=?';
//                $values[] = $v;
//            }
//        }
        $sql = '';
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . join(' AND ', $conditions);
        }
        if (isset($order) && is_array($order) && !empty($order)) {
            $sqo = '';
            foreach ($order as $key => $val) {
                $sqo .= '' == $sqo ? "{$key} {$val}" : ", {$key} {$val}";
            }
            $sql .= ' ORDER BY ' . $sqo;
        }
        if (isset($limitArgs) && array_key_exists('limit', $limitArgs)) {
            if (array_key_exists('offset', $limitArgs)) {
                $sql .= ' LIMIT ' . $limitArgs['offset'] . ', ' . $limitArgs['limit'];
            } else {
                $sql .= ' LIMIT ' . $limitArgs['limit'];
            }
        }
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }

        return array($sql, $values);
    }

    /**
     * To construct query conditions.
     *
     * @param array $params $params[][0] for column-name, $params[][1] for value, $params[][1] for condition
     *
     * @return array Constructed Query condition
     */
    protected static function constructConditions($params = array()) {
        $conditions = $values = array();
        foreach ($params as $k => $v) {
            if (is_array($v)) {
                $conditions[] = $k . ' IN (' . implode(',', array_fill(0, count($v), '?')) . ')';
                $values = array_merge($values, $v);
            } else {
                $field = explode(' ', trim($k));

                if (count($field) > 1) {
                    $operator = $field[1];
                } else {
                    $operator = '=';
                }
                switch ($operator) {
                    case '=':
                    case '<>':
                    case '!=':
                    case '>=':
                    case '<=':
                    case '>':
                    case '<':
                        $conditions[] = $field[0] . " ${operator} ?";

                        break;
                    case '!':
                        $conditions[] .= $field[0] . " ${operator}= ?";

                        break;
                    case 'like':
                        $conditions[] .= $field[0] . " ${operator} ?";
                        $v = ('%' . $v . '%');

                        break;
                }
                $values[] = $v;
            }
        }

        return array($conditions, $values);
    }

    /**
     * $columns Return an array of values corresponding to
     * Do not include attributes that are not set in the instance
     * [ id model class does not include attributes in the DB column definition, they will not be executed, only default values will be ].
     *
     * @return array An array consisting of an array of columns, an array of values
     */
    protected function getValues() {
        $values = array();
        $columns = array();
        foreach (static::getColumns() as $column) {
            if (isset($this->{$column})) {
                $columns[] = $column;
                $values[] = $this->{$column};
            }
        }

        return array($columns, $values);
    }

    /**
     * Return the column name list.
     */
    protected static function getColumns() {
        if (isset(static::$columnsOnDB)) {
            return array_keys(static::$columnsOnDB);
        }

        return array();
    }

    /**
     * To get the column list of the database table.
     *
     * @param null|mixed $pdo
     */
    protected static function getColumnsOnDB($pdo = null) {
        if (null == self::$columnsOnDB) {
            if (null == $pdo) {
                $pdo = Flight::pdo();
            }

            $stmt = $pdo->prepare('SELECT * from ' . static::TABLE_NAME . ' order by id limit 1 ');
            $stmt->execute();
            self::$columnsOnDB = array_keys($stmt->fetch(PDO::FETCH_ASSOC));
        }

        return self::$columnsOnDB;
    }

    /**
     * Returns the type of column.
     *
     * @param string $ column target column name defined in model class
     * @param mixed $column
     */
    protected static function getColumnType($column) {
        return static::$columnsOnDB[$column]['type'];
    }

    /**
     * Returns the key for setting all records in memcache.
     */
    protected static function getAllKey() {
        return Config_Config::getInstance()->getMemcachePrefix() . get_called_class() . '_all';
    }

}
