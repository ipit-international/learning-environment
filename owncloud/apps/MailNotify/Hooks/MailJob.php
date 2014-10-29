<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SHARRISON
 * Date: 11/19/13
 * Time: 9:49 AM
 * To change this template use File | Settings | File Templates.
 */

namespace OCA\MailNotify\Hooks;

require_once 'lib/base.php';

use OC\BackgroundJob\TimedJob;
use \OCP\Util;

class MailJob extends TimedJob
{
    const CLASSNAME = 'OCA\MailNotify\Hooks\MailJob';

    protected function run($argument)
    {
        // expecting argument to be null.
        try {
            Util::writeLog(self::CLASSNAME, 'Executing recurring mail job', Util::DEBUG);
            HookHandler::sendNotifications();
        } catch (\Exception $ex) {
            $msg = sprintf("Error while executing recurring mail job: %s\n%s", $argument, $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog(self::CLASSNAME, $msg, Util::ERROR);
        }
    }
}
