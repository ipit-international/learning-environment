<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SHARRISON
 * Date: 10/16/13
 * Time: 8:56 AM
 * To change this template use File | Settings | File Templates.
 */

namespace OCA\MailNotify\Db;

use \OCA\AppFramework\Db\Entity;
use OCA\MailNotify\NotificationTypes;


class Item extends Entity
{
    public $nid;
    public $origin;
    public $recipients;
    public $subject;
    public $message;
    public $ncode;
    public $timestamp;

    public $notificationtype;
    public $transmissionid;
    /**
     * @var int - Flag denoting the sent state of the notice.
     *     -1 => sent (emailed)
     *      0 => not sent
     *      1 => pending send operation (don't send again)
     */
    public $sentflag;

    public function getTimeString()
    {
        return date('o-m-d H:i e', $this->timestamp);
    }

    public function getTypeLabel()
    {
        return NotificationTypes::getLabel($this->notificationtype);
    }

    public function getTrimmedSubject()
    {
        if (empty($this->timestamp)) {
            return '';
        }
        $trimmed = str_replace('[IPIT]', '', $this->subject);
        return trim(preg_replace('(IPIT.*)', '', $trimmed));
    }

    public function getRecipientString()
    {
        if (!is_array($this->recipients)) {
            return $this->recipients;
        }
        if (empty($this->recipients) || count($this->recipients) < 1) {
            return '';
        }
        return implode(',', $this->recipients);
    }

    public function getSentLabel()
    {
        if ($this->sentflag == -1) {
            return 'Sent';
        } else if ($this->sentflag == 1) {
            return 'Sending';
        }
        return 'Queued';
    }
}
