<?php

namespace OCA\FileRetentionApp\applogic;

use \OCP\Util;
use \OCP\Config;

/*
	This class manages the maximum file retention business requirement.
	Every time a file is uploaded to owncloud, this class makes a soap
	call to our business rules server and retrieves the time at which
	the uploaded file must be deleted.  It uses this information to
	instantiate a new fileTimer, which kicks off a background batch
	file which tracks that file, and deletes it at the appointed time.
*/

class FileQueue {	
	

    //Handles the retention logic when a file has just been uploaded
    public static function write($params){

	//need to get rid of this, could set it in the config file and use getConfig to get it with a default value if not found in config
        $wsdl = Config::getSystemValue('ruleswsdl');
        $client = new \SoapClient($wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));
	$response = $client->deleteFileUploaded( array('arg0'=>time() ) );
	$deleteTime = floatval( $response->return );
		
	//Add new file path to the DB oc_files table
        $txt = 'INSERT INTO `*PREFIX*files` (`delete_time`, `path`, `actual_deleted_time`) VALUES(?, ?, NULL)';
        $path = self::formatPath($params['path']);
        $query = \OC_DB::prepare($txt);
        $time = json_encode($deleteTime);
        $filepath=json_encode($path);
        $params = array($time, $filepath);
        $query->execute($params);
    }


    public static function rename($params){
        $pathOld = self::formatPath($params['oldpath']);
        $pathNew = self::formatPath($params['newpath']);
       
	//Update path in the DB oc_files table with new file name
        $query = \OC_DB::prepare('UPDATE `*PREFIX*files` SET `path`= ? WHERE `path`= ? AND `actual_deleted_time` IS NULL');
        $filepath1=json_encode($pathNew);
        $filepath2=json_encode($pathOld);
        $params = array($filepath1, $filepath2);
        $query->execute($params);
    }

    
    public static function delete($params){
        $path = self::formatPath($params['path']);
        
	//Update deleteTime with time of deletion in the DB oc_files table.
        $query = \OC_DB::prepare('UPDATE `*PREFIX*files` SET `actual_deleted_time`= ? WHERE `path`= ? AND `actual_deleted_time` IS NULL');
        $deleteTime = json_encode(time());
        $filepath=json_encode($path);
        $params = array($deleteTime, $filepath);
        $query->execute($params);
    }


//do we need this read functionality to "update the oc_files table"


	//will handle the retention logic for a file that has just been downloaded
	/*
	This function helps to handle the following JIRA issue:
		- IRSIDES#49 : emits a hook upon successful download
		- IRSIDES#50 : emits a hook upon failed download
		- IRSIDES#28 : Starts a file deleter for each file downloaded (change this)
	*/
/*	public static function read($params){
		
		$path = $params['path'];

		//Check to see if the file exists
		if( !\OC\Files\Filesystem::file_exists( $path ) ){
			Util::writeLog("FileQueue","Download failed : ".$params['path'].". File does not exist",Util::INFO);
			Util::emitHook("FileQueue","Signal_Download_Failed",array('path'=>$params['path'])); //Emit a hook for failure
		} else{

			Util::emitHook("FileQueue","Signal_Download_Succeeded",array('path'=>$params['path'])); //Emit a hook for success

			$wsdl = Config::getSystemValue('ruleswsdl');
		
			$client = new \SoapClient($wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));

			$response = $client->deleteFileDownloaded( array('arg0'=>time() ) ); //SOAP call, returns the time at which to delete the downloaded file

			$deleteTime = floatval( $response->return );

			//Edit/update the entry in the database:
	 		\OCP\Util::writeLog('filequeue.php', "Path before insert into read: $path", \OCP\Util::FATAL);
			$query = \OC_DB::prepare('UPDATE `*PREFIX*files` SET `delete_time` = ? WHERE `path` = ?' );
			$time = json_encode($deleteTime);
			$filepath=json_encode($path);
			$params = array($time, $filepath);
			$query->execute($params);
		}

	}


*/
	public static function formatPath($path){	
		$result = stripslashes($path);
		$result = trim($result,'"');
		return \OC\Files\Filesystem::getView()->getAbsolutePath($path);		
	}		
}
