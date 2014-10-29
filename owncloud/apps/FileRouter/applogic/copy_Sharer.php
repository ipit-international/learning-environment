<?php
/**
 * Created by PhpStorm.
 * User: AJROSS
 * Date: 11/15/13
 * Time: 12:46 PM
 */

namespace OCA\FileRouter\applogic;

use OCA\FileRetentionApp\applogic\FileQueue;
use OCA\FileRouter\Db\Transmission;
use OCA\FileRouter\Db\TransmissionMapper;
use OCA\FileRouter\IGATypes;
use OCA\FileRouter\TransmissionStates;
use OCP\MC_User;
use \OCP\Util;
use \OC\Files\Filesystem;
use OCP\Share;
use OCP\Config;
use \OCP\MC_Group;



/**
 * TODO: rethink the kickoff mechanism for this app - can hooks work? cases for manual vs auto upload?
 *
 * Class Sharer
 * @package OCA\FileRouter\applogic
 */
class Sharer{

    private $shareFilePath;
    private $shareFileName;
    private $shareUsers;
    private $shareGroups;
    private $fileId;
    private $hcta; //M1O2 intermediary
    private $dbMapper;

    public function __construct($hookPath){


	$this->dbMapper = new TransmissionMapper($this->makeAppAPI());

        if($this->counterpartExists($hookPath)){
//            Util::writeLog("sharer","counterpart exists!",Util::INFO);

            //Add a new transmission into the db here.

            foreach($this->shareUsers as $user){
                if(!($user === "UNDEFINED") && !($this->shareFilePath === null)){
                    $this->shareFileWithUser(trim($user,"\r"));
                }
            }

            foreach($this->shareGroups as $group){
                if(!($group === "UNDEFINED") && !($this->shareFilePath === null)){
                    $group=trim($group,"\r");

//                    $this->shareFileWithGroup(trim($group,"\r"));

                    $this->handleModelAndOption($hookPath,$group);
                }
            }



            $this->insertTransmission();

            $params = array('path'=>$hookPath);
            FileQueue::auto_write($params);

            //Delete the sidecar
            $fragments = explode(".",$hookPath);
//            Util::writeLog("Sharer","Should delete sidecar now",Util::INFO);
            Filesystem::unlink($fragments[0].".fsc");

        }

        return;
    }

    private function counterpartExists($hookPath){
        //Case 1: we detect sidecar
        if($this->endsWith($hookPath,".fsc")){

            $this->sidecarPath=$this->convertToFullPath($hookPath);
            $this->parseSidecar($this->sidecarPath);

            if(file_exists($this->shareFilePath)){
                return true;
            }

        //Case 2: we detect fatca file
        }elseif(!($this->endsWith($hookPath,".fsc"))){

            //infer the sidecar path
            $fragments = explode(".",$hookPath);
            $this->sidecarPath = $this->convertToFullPath($fragments[0].'.fsc');

            if(file_exists($this->sidecarPath)){
                $this->parseSidecar($this->sidecarPath);
                return true;
            }
        }else{
            return false;
        }
    }

    private function parseSidecar($fullPathToSidecar){

        $contents = file_get_contents($fullPathToSidecar);
        $lines = explode("\n",$contents);

        foreach($lines as $line){
            if(self::startsWith($line,"Path")){
                $vals = explode("\t",$line);
                $this->shareFileName=trim($vals[1],"\r");
                $this->shareFilePath=$this->convertToFullPath('/'.trim($vals[1],"\r"));
            }elseif(self::startsWith($line,"ShareUser")){
                $users = explode("\t",$line);
                array_shift($users);
            }elseif(self::startsWith($line,"ShareGroup")){
		\OCP\Util::writeLog("Sharer","Exploding groups",4);
                $groups = explode("\t",$line);
		//\OCP\Util::writeLog("sharer","group is ".var_export($groups),4);
                array_shift($groups);
            }
        }

        $this->shareUsers = $users;
	\OCP\Util::writeLog("Sharer","Share users = ".$users[0],4);
        $this->shareGroups= $groups;
	\OCP\Util::writeLog("Sharer","Share groups = ".$groups[0],4);

    }

    private function convertToFullPath($hookPath){
        $dataDir = Config::getSystemValue('datadirectory');
        return $dataDir.Filesystem::getRoot().$hookPath;
    }

    private function endsWith($haystack, $needle){
        return $needle === "" || substr($haystack,-strlen($needle)) === $needle;
    }

    private function startsWith($haystack, $needle){
        return $needle === "" || strpos($haystack,$needle) === 0;
    }


    /**
     * I believe Item source arg must be obtained via a lookup in the oc_filecache table
     *
     * @param $recipient
     */
    private function shareFileWithUser($recipient){

        $localPath = Filesystem::getLocalPath($this->shareFilePath);
        Util::writeLog("File Router Sharer","local path = $localPath",Util::INFO);

//        $fileID = $this->dbMapper->;

        Share::shareItem("file",$fileID,Share::SHARE_TYPE_USER,$recipient,\OCP\PERMISSION_ALL);
    }


    private function shareFileWithGroup($recipient){
        $localPath = Filesystem::getLocalPath($this->shareFilePath);
        Util::writeLog("File Router Sharer","local path = $localPath",Util::INFO);
        $this->fileId = SharingManager::lookupFileId($localPath);
        Share::shareItem("file",$this->fileId,Share::SHARE_TYPE_GROUP,$recipient,\OCP\PERMISSION_ALL);
    }

    /**
     *
     *
     * @param $hookPath
     */
    private function handleModelAndOption($hookPath, $recipientGroup){

        $senderInfo = MC_User::getUserInfo();
        $senderCountry =$senderInfo['country'];
//        Util::writeLog("FileRouter Sharer","sender country = $senderCountry",Util::INFO);
        $receiverInfo = MC_Group::getGroupInfo($recipientGroup);
        $receiverCountry = $receiverInfo['country'];
//        Util::writeLog("FileRouter Sharer","receiver country = $receiverCountry",Util::INFO);

        //If the transmission is model 1 option 2
        if(RulesManager::isModelOneOptionTwo($senderCountry,$receiverCountry)){

            Util::writeLog("FileRouter Sharer","isModelOneOptionTwo",4);

            $this->hcta = RulesManager::determineAuthorizingHCTA($senderCountry);
            $this->shareFileWithGroup($this->hcta);

	    \OCP\Util::writeLog("Sharer","Hcta = $this->hcta",4);

        //else, the file is m1o1
        } else{
//            Util::writeLog("FileRouter Sharer","NOT isModelOneOptionTwo",Util::INFO);
            $this->shareFileWithGroup($recipientGroup);
        }

    }

    private function insertTransmission(){
        \OCP\Util::writeLog("File Router Sharer","InsertingTransmission",4);
        if(\OC_User::isLoggedIn()){
            $userInfo=MC_User::getUserInfo();
            if(in_array('TA',$userInfo['types'])){
                Util::writeLog("File Router Sharer","Trying to do M1O1 transmission",4);
                Util::writeLog("File Router Sharer","fileId = ".$this->fileId,4);
                Util::writeLog("File Router Sharer","share group = ".$this->shareGroups[0],4);
                $tx = new Transmission();
                $tx->setFileid($this->fileId);
                $tx->setIgatype(IGATypes::ModelOneOptionOne);
                $tx->setIntermediate("Not Applicable");
                $tx->setSender($userInfo['userId']);
                $tx->setRecipient($this->shareGroups[0]);
                $tx->setState(TransmissionStates::AutoReleased);
                $tx->setFullpath($this->shareFilePath);

                $db = new TransmissionMapper($this->makeAppAPI());
                $db->insert($tx);
		\OCP\Util::writeLog("FileRouter Sharer", "table = " . $db->getTableName(), 4);
            }else{
                Util::writeLog("File Router Sharer","Trying to do M1O2 transmission",Util::INFO);
                Util::writeLog("File Router Sharer","fileId = ".$this->fileId,Util::INFO);
                Util::writeLog("File Router Sharer","share group = ".$this->shareGroups[0],Util::INFO);
                Util::writeLog("File Router Sharer","hcta = ".$this->hcta,Util::INFO);
                $tx = new Transmission();
                $tx->setFileid($this->fileId);
                $tx->setIgatype(IGATypes::ModelOneOptionTwo);
                $tx->setIntermediate($this->hcta);
                $tx->setSender($userInfo['userId']);
                $tx->setRecipient($this->shareGroups[0]);
                $tx->setState(TransmissionStates::PendingReview);
                $tx->setFullpath($this->shareFilePath);

                $db = new TransmissionMapper($this->makeAppAPI());
                $db->insert($tx);
            }
        }
    }

    /**
     * Makes another instance of the API declared in FileRouter/appinfo/app.php
     * @return \OCA\AppFramework\Core\API
     */
    private function makeAppAPI(){
        $api = new \OCA\AppFramework\Core\API('FileRouter');

        $api->addNavigationEntry(array(
            // the string under which your app will be referenced in owncloud
            'id' => $api->getAppName(),
            // sorting weight for the navigation. The higher the number, the higher
            // will it be listed in the navigation
            'order' => 10,
            // the route that will be shown on startup
            'href' => $api->linkToRoute('tx_index'),
            // the icon that will be shown in the navigation
            // this file needs to exist in img/example.png
            'icon' => $api->imagePath('env.png'),
            // the title of your application. This will be used in the
            // navigation or on the settings page of your app
            'name' => $api->getTrans()->t('Transmissions')
        ));

        //    $api->addScript('public');
        $api->addStyle('public');
        return $api;
    }


}
