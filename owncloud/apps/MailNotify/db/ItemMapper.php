<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SHARRISON
 * Date: 10/16/13
 * Time: 9:00 AM
 * To change this template use File | Settings | File Templates.
 */

namespace OCA\MailNotify\Db;

use OCA\AppFramework\Core\API;
use \OCA\AppFramework\Db\Mapper;

/**
 * Class ItemMapper
 * @package OCA\MailNotify\Db
 */
class ItemMapper extends Mapper
{

    /**
     * @param API $api
     */
    public function __construct(API $api)
    {
        parent::__construct($api, 'mn_items');
    }

    /**
     * @brief List notification items for a user. returns items in reverse chronological order.
     * @param $userid - the user ID to list notifications for
     * @return array - the DB entities that were retrieved.
     */
    public function listItems($userid)
    {
        $sql = 'SELECT * FROM `' . $this->getTableName() . '` ' .
            'WHERE `origin` LIKE ? OR `recipients` LIKE ? ' .
            'ORDER BY `id` DESC';

        $rows = $this->findEntities($sql, array($userid, '%' . $userid . '%'));
        return $rows;
    }

    public function listUnsentNotifications()
    {
        // get notices where the sent flag is false (sentflag == 0)
        $sql = 'SELECT * FROM `' . $this->getTableName() . '` ' .
            'WHERE `sentflag` = 0 ' .
            'ORDER BY `id` ASC';

        $rows = $this->findEntities($sql);

        // set state of rows to 'pending'
        foreach ($rows as $row) {
            if (is_null($row)) {
                continue;
            }
            $row->setSentflag(1);
            $this->update($row);
        }

        return $rows;
    }

    /**
     * @brief convert a database row to an Item entity
     * @param $row - the database row to convert
     * @return null|Item - the Item represented by the database row, or null
     */
    function toItem($row)
    {
        if (is_null($row)) {
            return NULL;
        }
        $entity = new Item();
        $entity->fromRow($row);
        return $entity;
    }

    /**
     * @brief Retrieve a Notification Item by id
     * @param $itemId string - the ID of the notification entry to retrieve
     * @return Item the item that matches the ID, or null if no item is found.
     */
    public function getItem($itemId)
    {
        $sql = 'SELECT * FROM `' . $this->getTableName() . '` ' .
            'WHERE `id` = ?';
        $row = $this->findOneQuery($sql, array($itemId));
        return $this->toItem($row);
    }
}