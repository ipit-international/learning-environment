<?php

namespace OCA\FileRouter\Controller;

\OC::$CLASSPATH['OCA\FileRouter\Db\TransmissionMapper'] = 'FileRouter/db/TransmissionMapper.php';


use OC\Files\Filesystem;
use \OCA\AppFramework\Controller\Controller;
use OCA\FileRouter\Db\TransmissionMapper;
use OCP\MC_User;
use OCP\Share;
use OCP\Util;
use OCA\FileRouter\TransmissionStates;



class PageController extends Controller
{
    const FFI = 'FFI';
    const TA = 'TA';

    public $api;

    public function __construct($api, $request)
    {
        $this->api = $api;
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
    public function index()
    {
        $renderMessage = null;

        if (array_key_exists('action', $_GET)) {
            $action = $_GET['action'];
            $xmissionMapper = new TransmissionMapper($this->api);
            $transmissionState = null;
        Util::writeLog('Transmission table',"action chosen = $action" , Util::INFO);

            if ($action !== null) {
                if ($_GET['tx'] !== null) {
                    $txid = $_GET['tx'];
                    $xmission = $xmissionMapper->getTransmission($txid);
                    $recipient = $xmission->getRecipient();
                    $fileid = $xmission->getFileid();

                    if ($action === "approve") {
                        $shareSuccess = Share::shareItem(
                            "file",
                            $fileid,
                            Share::SHARE_TYPE_GROUP,
                            $recipient,
                            \OCP\PERMISSION_READ
                        );
                        $xmission->setState(TransmissionStates::Released);
                        $transmissionState = "approved";
                    }
                    if ($action === "reject") {
                        $filepath = $xmission->getFullpath();
                        Filesystem::unlink($filepath);
                        $xmission->setState(TransmissionStates::Rejected);
                        $transmissionState = "rejected";
                    }

                    $xmissionMapper->update($xmission);
                    $params = array('transmissionid' => $txid, 'fileid' => $fileid);
                    Util::emitHook('SharingManager', 'transmission_' . $transmissionState, $params);
                    $renderMessage = "Transmission #" . $txid . " has been $transmissionState.";
                }
            }
        }
        return $this->fillPage($renderMessage);
    }

    public function fillPage($actionMessage)
    {
        $errorMessage = null;
        $transmissionLog = null;
        $transmissionsToReview = null;
        $transmissionsToReceive = null;

        // retrieve notification entries from database.
        $db = new TransmissionMapper($this->api);
        $userInfo = MC_User::getUserInfo();

        // Users *should* only belong to 1 group and it will be either FFI or TA, it's a requirement
        // and enforced by the UI (type is a radio button)
        $groupType = $userInfo['type'];

        if($groupType===self::TA){
            $transmissionLog = $db->listMyTransmissions($userInfo['userId']);
            $transmissionsToReview = $db->listTransmissionsForIntermediate($userInfo['group']);
            $transmissionsToReceive = $db->listTransmissionsForRecipient($userInfo['group']);
        }elseif($groupType===self::FFI){
            $transmissionLog = $db->listMyTransmissions($userInfo['userId']);
        }else{
            $errorMessage = "User is not properly registered: User must belong to either a TA or an FFI";
        }

        Util::writeLog('FileRouter', serialize($userInfo['groups']), Util::DEBUG);

        return $this->render(
            'main',
            array(
                'error' => $errorMessage,
                'actionmsg' => $actionMessage,
                'txLog' => $transmissionLog,
                'txReview' => $transmissionsToReview,
                'txReceive' => $transmissionsToReceive,
            )
        );
    }

    /**
     * Makes another instance of the API declared in FileRouter/appinfo/app.php
     *
     * @return \OCA\AppFramework\Core\API
     */

    /*
    private function makeAppAPI()
    {
        $api = new \OCA\AppFramework\Core\API('FileRouter');


        //    $api->addScript('public');
        $api->addStyle('public');
        return $api;
    }
     */
}
