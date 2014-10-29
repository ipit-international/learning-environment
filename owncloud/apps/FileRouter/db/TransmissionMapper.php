<?php
/**
 * Created by PhpStorm.
 * User: SHARRISON
 * Date: 12/10/13
 * Time: 3:28 PM
 */

namespace OCA\FileRouter\Db;


use OCA\AppFramework\Core\API;
use OCA\AppFramework\Db\Mapper;
use OCA\FileRouter\TransmissionStates;

class TransmissionMapper extends Mapper
{
    /**
     * Constructor for the TransmissionMapper, providing database access for our transmission tracking table.
     * @param API $api - the AppFramework API reference
     */
    public function __construct($api) {
        parent::__construct($api, 'transmission_tracker'); // tablename is news_feeds
    }

    /**
     * List the transmissions that a user has sent.
     * @param $senderUid - username of sender to look for
     * @return array - Transmissions that were sent by the specified user
     */
    public function listMyTransmissions($senderUid)
    {
        $sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `sender` LIKE ? ORDER BY `id` ASC';
        $rows = $this->findEntities($sql, array($senderUid));
        return $rows;
    }

    /**
     * List the transmissions that a TA group can view to release/reject
     * @param $taxAuthorityGroup - group name of Tax Authority
     * @return array - Transmissions that the specified TA can review
     */
    public function listTransmissionsForIntermediate($taxAuthorityGroup)
    {
        $sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `intermediate` LIKE ? ORDER BY `id` ASC';
        $rows = $this->findEntities($sql, array($taxAuthorityGroup));
        return $rows;
    }

    /**
     * List transmissions viewable to a recipient. Restricts list to items that have been released to the end recipient.
     * @param $recipientGroup - group that is the end recipient for a transmission
     * @return array - all transmissions that have been released or autoreleased to the recipient.
     */
    public function listTransmissionsForRecipient($recipientGroup)
    {
        $sql = 'SELECT * FROM `' . $this->getTableName() .
            '` WHERE `recipient` LIKE ? AND (`state` = ? OR `state` = ? OR `state` = ?)' .
            ' ORDER BY `id` ASC';
        $rows = $this->findEntities($sql, array(
            $recipientGroup,
            TransmissionStates::Released,
            TransmissionStates::AutoReleased,
            TransmissionStates::Deleted
        ));
        return $rows;
    }

    /**
     * Get a specific transmission from the database
     * @param $transmissionId - ID of the transmission to retrieve
     * @return null|Transmission
     */
    public function getTransmission($transmissionId)
    {
        if (is_null($transmissionId)) {
            return null;
        }
        $sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `id` = ?';
        $row = $this->findOneQuery($sql, array($transmissionId));
        if (is_null($row)) {
            return null;
        }
        $entity = new Transmission();
        $entity->fromRow($row);
        return $entity;
    }

    public function getTransmissionForFile($fileId)
    {
	$table = $this->getTableName();	
	\OCP\Util::writelog('File router transmission mapper', "fileid = $fileId "." table = $table", 4);
        $sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `fileid` = ?';
	\OCP\Util::writelog('File router transmission mapper', "sql statement = $sql", 4);	
        $row = $this->findOneQuery($sql, array($fileId));
        if (is_null($row)) {
            return null;
        }
        $entity = new Transmission();
        $entity->fromRow($row);
        return $entity;
    }
} 
