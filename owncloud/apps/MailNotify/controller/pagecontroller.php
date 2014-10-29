<?php

namespace OCA\MailNotify\Controller;

\OC::$CLASSPATH['OCA\MailNotify\Db\ItemMapper'] = 'MailNotify/db/ItemMapper.php';
use \OCA\AppFramework\Controller\Controller;
use \OCA\MailNotify\Db\ItemMapper;
use OCP\Util;

class PageController extends Controller {


    public function __construct($api, $request){
        parent::__construct($api, $request);
    }


    /**
     * ATTENTION!!!
     * The following comments turn off security checks
     * Please look up their meaning in the documentation!
     *
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function index(){
        // retrieve notification entries from database.
        $curUser = $this->api->getUserId();
        $db = new ItemMapper($this->api);
        $notificationLog = $db->listItems($curUser);
       //$msg = serialize($notificationLog);
        Util::writeLog('MailNotify-Page', 'Rendering notifications page.', Util::DEBUG);

        return $this->render('main', array(
           'notificationLog' => $notificationLog,
        ));
    }


}
