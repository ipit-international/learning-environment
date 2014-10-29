<?php

namespace OCA\FileRetentionApp\applogic;

require_once 'lib/base.php';

 use \OCP\Util;
 use \OC\Files\View;
 use \OC\Files\Filesystem;
 use \OC;

// $RUNTIME_APPTYPES=array('filesystem');
// \OC_App::loadApps($RUNTIME_APPTYPES);


 //To get this class to work correctly, it is necessary to modify joblist.php (changes noted in that file)
 //

class AutoDeleter extends \OC\BackgroundJob\TimedJob {

	public function __construct(){
		$this->setInterval(1); //This should probably be set some other way.
	}

	//$argument = null
	public function run($argument){
		Util::writeLog("AutoDeleter","run", Util::DEBUG);
		//Get the path to all files with delete time less than the current time
		$query = \OC_DB::prepare('SELECT `path`,`id` FROM `*PREFIX*files` WHERE `delete_time` < ? AND `actual_deleted_time` IS NULL');
		$time = array(json_encode(time()));
		$result = $query->execute($time);

		$rows = array();

		//Collect all the arrays returned by the query
		while($row = $query->fetchRow()){
			$rows[] = $row;
		}

		Util::writeLog("Autodeleter","RPG: sql result size = ".count($rows),Util::DEBUG);

		//Now try to delete the files.
		foreach($rows as $r){
			
			
			$query = \OC_DB::prepare('UPDATE `*PREFIX*files` SET `actual_deleted_time`= ? WHERE `id`= ?');
			$id = $r['id'];
			

			//$path = substr( $r['path'],3, -1 );
			
			$path = stripslashes($r['path']);
			$path = trim($path,'"');
			//$path = stripslashes($r['path']);
			//$path = realpath(dirname($r['path']));
			//$path = $r['path'];
			//list($storage, $internalPath) = \OC\Files\Filesystem::resolvePath($path);			
			//\OC\Files\Filesystem::isDeletable($path);
			//if(\OC\Files\Filesystem::isDeletable($path)) {
			//	if(!$storage->unlink($internalPath)) {
			//		Util::writeLog("Autodeleter", "RPG: failed?",Util::INFO);
			//	}
			//}
			//$root = getenv('CONFIG_DATADIRECTORY_ROOT');
			//$root = \OCP\Config::getSystemValue('datadirectory');
			//$path = "/testUSA/files/images.jpg";
			//$root = getenv('SERVER_NAME');
			//Util::writeLog("Autodeleter","RPG: root= $root",Util::FATAL);
			Util::writeLog("Autodeleter","RPG: path= $path",Util::DEBUG);
			$view = new View();
			//$view = new View($root);

			Util::writeLog("Autodeleter","RPG: valid path= ".Filesystem::isValidPath($path),Util::DEBUG);
			Util::writeLog("Autodeleter","RPG: relative path= ".$view->getRelativePath($path),Util::DEBUG);

			$view->isDeletable($path);
			if($view->file_exists($path)) {
				$stuff = $view->unlink($path);
				if(!$view->file_exists($path)){
					$deleteTime = json_encode(time());
					$query->execute(array($deleteTime,$id));
				}else{
					Util::writeLog("Autodeleter","RPG: unlink fail= $stuff on file= $path",Util::ERROR);
				}
			}else{
				Util::writeLog("Autodeleter","RPG: path does not exist= $path",Util::ERROR);
			}
/*			$path = "/images.jpg";
			Util::writeLog("Autodeleter","RPG: $path",Util::INFO);
			\OC\Files\Filesystem::init('testUSA', '/testUSA/files');
			\OC\Files\Filesystem::isDeletable($path);

			if(!\OC\Files\Filesystem::unlink($path)) {	
				Util::writeLog("Autodeleter", "RPG: failed?",Util::INFO);
			}
*/		}

		//TODO: insert the time of deletion into the table, not delete the row
		//$deleteTime = array(jason_encode(time()));
		
		//$q = \OC_DB::prepare('UPDATE `*PREFIX*files` SET `actual_deleted_time`= ? WHERE `id`= ?');
		//$q = \OC_DB::prepare('DELETE FROM `*PREFIX*files` WHERE `delete_time` < ?');
		//$q->execute($deleteTime);

	}

}



?>
