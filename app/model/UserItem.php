<?php

(defined('APP_NAME')) or exit('Forbidden 403');

/**
 * User Item model class.
 */
class Model_UserItem extends Model_BaseModel {

    /**
     * Table Name.
     */
    const TABLE_NAME = 'user_items';

    /**
     * Table column definitions.
     */
    protected static $columnsOnDB = array(
        'id' => array(
            'type' => 'int',
            'json' => true,
        ),
        'user_id' => array(
            'type' => 'int',
            'json' => false,
        ),
        'item_name' => array(
            'type' => 'string',
            'json' => true,
        ),
        'created_at' => array(
            'type' => 'string',
            'json' => false,
        ),
        'updated_at' => array(
            'type' => 'string',
            'json' => false,
        ),
    );

    /**
     * Insert new unique item for session user.
     *
     * @param int    $userId   User ID
     * @param string $itemName Name of user item
     * @param obj    $pdo      DB connection Object PDO
     *
     * @throws System_Exception
     *
     * @return obj $userItemObj Model_UserItem object
     */
    public static function addUserItem($userId, $itemName, $pdo = null) {
        if (null === $pdo) {
            $pdo = Flight::pdo();
        }

        $userItemObj = self::findBy(array('user_id' => $userId, 'item_name' => $itemName), $pdo);

        if (empty($userItemObj)) {
            $userItemObj = new Model_UserItem();
            $userItemObj->user_id = $userId;
            $userItemObj->item_name = $itemName;

            $userItemObj->create($pdo);
        } else {
            throw new System_ApiException(ResultCode::DATABASE_ERROR, 'Item already exist!');
        }

        return $userItemObj;
    }

    /**
     * Get all item list available in database.
     *
     * @param string $itemName Item name to be searched
     * @param string $userId   User ID to be searched
     * @param obj    $pdo      DB connection Object PDO
     *
     * @return array $result Array of item list
     */
    public static function getAllItems($itemName = '', $userId = null, $pdo = null) {
        if (null === $pdo) {
            $pdo = Flight::pdo();
        }
        $sql = 'SELECT item_name, count(item_name) AS count FROM ' . self::TABLE_NAME;
        
        list($conditions, $values) = self::constructConditions(array_filter(array(
                    'item_name like' => $itemName,
                    'user_id' => $userId
                        ))
        );

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . join(' AND ', $conditions);
        }

        $sql .= ' GROUP BY item_name ORDER BY item_name ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

}
