<?php

namespace OCA\Admin_Audit\Hooks;

use \OC\Files\Filesystem;
use \OCP\Util;
use \OCP\User;
use \OCP\Group;
use OCA\MailNotify\Hooks;

class Handlers {
	const CLASSNAME = '\OCA\admin_audit\hooks\Handlers';

	static public function pre_login($params) {
		$user = $params['uid'];
		self::log('User '.$user.' attempted to log into ownCloud from IP address: '.$_SERVER['REMOTE_ADDR']);
	}
	static public function post_login($params) {
		$user = $params['uid'];
		self::log('User '.$user.' logged into ownCloud from IP address: '.$_SERVER['REMOTE_ADDR']);
	}
	static public function logout($params) {
		$user = User::getUser();
		self::log('User '.$user.' logged out of ownCloud');
	}
	static public function rename($params) {
		self::logOldNewPathAction('Rename', $params);
	}


	// torch added method
	static public function post_write($params) {
//		ob_start();
//		var_dump($params);
//		$result = ob_get_clean();
		self::log('post_write: file: '.$params['path'].' is post a write');

	}







	static public function create($params) {
		self::logSinglePathAction('Create', $params);
	}
	static public function copy($params) {
		self::logOldNewPathAction('Copy', $params);
	}
	static public function write($params) {
		self::logSinglePathAction('Write', $params);
	}
	static public function read($params) {

		if( !\OC\Files\Filesystem::file_exists( $params['path'] ) ){
			
		} else{
			self::logSinglePathAction('Read', $params);
		}

		// self::logSinglePathAction('Read', $params);
	}
	static public function delete($params) {
		self::logSinglePathAction('Delete', $params);
	}
	static public function pre_createUser($params){
	 	$user = $params['uid'];
	 	self::log('Admin attempted to create new user '.$user.'.');
	}
	static public function post_createUser($params){
		$user = $params['uid'];
		self::log('New User '.$user.' has been created.');
	}

	static public function pre_deleteUser($params){
		$user = $params['uid'];
		self::log('Admin attempted to delete '.$user.'.');
	}

    static public function post_deleteUser($params){
		$user = $params['uid'];
		self::log('User '.$user.' has been deleted.');
	}
	// static public function pre_createGroup($params){
	// 	$group = $params['gid'];
	// 	self::log('Admin attempted to create new group '.$group.'.');
	// }
	static public function post_setPassword($params){
		$user = $params['uid'];
		self::log('Password changed for user '.$user);
	}

	//Group hook handlers.  For some reason all
	//"Post" hooks aren't working
	static public function pre_createGroup($params){
		$group = $params['gid'];
		self::log('New group '.$group.' has been created.');
		// self::log('Admin attempted to create new group '.$group);
	}
	// static public function post_createGroup($params){
	// 	$group = $params['gid'];
	// 	self::log('New group '.$group.' has been created.');
	// }
	static public function pre_deleteGroup($params){
		$group = $params['gid'];
		self::log('Group '.$group.' has been deleted.');
	}
	// static public function post_deleteGroup($params){
	// 	$group = $params['gid'];
	// 	self::log('Group '.$group.' has been deleted.');
	// }
	static public function pre_addToGroup($params){
		$user = $params['uid'];
		$group = $params['gid'];
		self::log('User '.$user.' was added to group '.$group);
		// self::log('Admin tried to add user '.$user.' to group '.$group);
	}
	// static public function post_addToGroup($params){
	// 	$user = $params['uid'];
	// 	$group = $params['gid'];
	// 	self::log('User '.$user.' was added to group '.$group);
	// }
	static public function pre_removeFromGroup($params){
		$user = $params['uid'];
		$group = $params['gid'];
		self::log('User '.$user.' was removed from group '.$group);
		// self::log('Admin attempted to remove user '.$user.' from group '.$group);
	}
	// static public function post_removeFromGroup($params){
	// 	$user = $params['uid'];
	// 	$group = $params['gid'];
	// 	self::log('User '.$user.' was removed from group '.$group);
	// }
	static public function fileMovedToTrash($params){
		$path=$params['path'];
		self::log('File located at : '.$path.' was moved to trash.');
	}

	//Notification hook handlers
	// static public function post_Notification($params){
	// 	self::log($params['message']);
	// }

	/**
	 * modifies the passed $params array:
	 * - replaces paths with their absolutePath
	 * - adds original owner of 'path' or 'oldpath'
	 * - adds user or IP accessing the file
	 * @param array $params
	 */
	static protected function resolveDetails(&$params) {
		if (isset($params[Filesystem::signal_param_path])) {
			$path = $params[Filesystem::signal_param_path];
			$params[Filesystem::signal_param_path] = Filesystem::getView()->getAbsolutePath($path);
			$params['owner'] = Filesystem::getOwner($path);
		} else {
			$oldpath = $params[Filesystem::signal_param_oldpath];
			$params[Filesystem::signal_param_oldpath] = Filesystem::getView()->getAbsolutePath($oldpath);
			$params['owner'] = Filesystem::getOwner($oldpath);
			$newpath = $params[Filesystem::signal_param_newpath];
			$params[Filesystem::signal_param_newpath] = Filesystem::getView()->getAbsolutePath($newpath);
		}
		$params['userOrIP'] = self::getUserOrIP();
	}
	
	/**
	 * returns the currently logged in user or the requests REMOTE_ADDR
	 * prepends result with 'user ' or 'IP '
	 * @return string
	 */
	static protected function getUserOrIP() {
		$user = User::getUser();
		if ($user) {
			$result = 'user ' . $user;
		} else {
			$result = 'IP ' . $_SERVER['REMOTE_ADDR'];
		}
		return $result;
	}

	/**
	 * logs an action that takes two path parameters (rename, copy) in the form of:
	 * '$action "$oldpath" to "$newpath" by $user'.
	 * If original owner and user differ will append the original owner:
	 * '$action "$oldpath" to "$newpath" by $user, owner: $owner'.
	 * @param string $action
	 * @param array $params
	 */
	static protected function logOldNewPathAction($action, $params) {
		self::resolveDetails($params);
		$msg = $action.' "'.$params[Filesystem::signal_param_oldpath].'" to "'
						   .$params[Filesystem::signal_param_newpath].'" by '.$params['userOrIP'];
		if ($params['userOrIP'] !== $params['owner']) {
			$msg .= ', owner: '.$params['owner'];
		}
		self::log($msg);
	}

	/**
	 * logs an action that takes a single path parameter
	 * '$action "$oldpath" by $user'.
	 * If original owner and user differ will append the original owner:
	 * '$action "$oldpath" by $user, owner: $owner'.
	 * @param string $action
	 * @param array $params
	 */
	static protected function logSinglePathAction($action, $params) {
		self::resolveDetails($params);
		$msg = $action.' "'.$params[Filesystem::signal_param_path].'" by '.$params['userOrIP'];
		if ($params['userOrIP'] !== $params['owner']) {
			$msg .= ', owner: '.$params['owner'];
		}
		self::log($msg);
	}

	/**
	 * logs a message for 'admin_audit' at INFO level
	 * @param string $msg
	 */
	static protected function log($msg) {
		Util::writeLog('admin_audit', $msg, 4);
		//Util::writeLog('admin_audit', $msg, Util::INFO);
		// $myFile = "C:\inetpub\wwwroot\owncloud\apps\admin_audit\hooks\audit_log.txt";
		// file_put_contets($myFile,$stringData);
		// $myfile = "C:\inetpub\wwwroot\owncloud\apps\admin_audit\hooks\audit_log.txt";
		// $fileHandle = fopen($myfile,'w') or die("Cannot open file");
		// fwrite($fileHandle,$msg);
		// fclose($fileHandle);
	}
}

