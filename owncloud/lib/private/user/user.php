<?php

/**
 * Copyright (c) 2013 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\User;

use OC\Hooks\Emitter;

class User {
	/**
	 * @var string $uid
	 */
	private $uid;

	/**
	 * @var string $displayName
	 */
	private $displayName;

	/**
	 * @var \OC_User_Backend $backend
	 */
	private $backend;

	/**
	 * @var bool $enabled
	 */
	private $enabled;

	/**
	 * @var Emitter | Manager $emitter
	 */
	private $emitter;

	/**
	 * @var string $home
	 */
	private $home;

	/**
	 * @var \OC\AllConfig $config
	 */
	private $config;



	private $error_count;
	private $locked;

	/**
	 * @param string $uid
	 * @param \OC_User_Backend $backend
	 * @param \OC\Hooks\Emitter $emitter
	 * @param \OC\AllConfig $config
	 */
	public function __construct($uid, $backend, $emitter = null, $config = null) {
		$this->uid = $uid;
		if ($backend and $backend->implementsActions(OC_USER_BACKEND_GET_DISPLAYNAME)) {
			$this->displayName = $backend->getDisplayName($uid);
			//MITRE CODE
			$this->error_count = $backend->getErrorCount($uid);
			$this->locked = $backend->getLocked($uid);
			//END MITRE CODE
		} else {	
			$this->displayName = $uid;
		}
		/*RYAN ADD
		if ($backend and $backend->implementsActions(OC_USER_BACKEND_GET_ERROR_COUNT)) {
			$this->error_count = $backend->getErrorCount($uid);
		} else {
			$this->error_count = null;
		}
		if ($backend and $backend->implementsActions(OC_USER_BACKEND_GET_LOCKED)) {
			$this->locked = $backend->getLocked($uid);
		} else {
			$this->locked = null;
		}
		RYAN ADD END*/
		$this->backend = $backend;
		$this->emitter = $emitter;
		$this->config = $config;
		if ($this->config) {
			$enabled = $this->config->getUserValue($uid, 'core', 'enabled', 'true');
			$this->enabled = ($enabled === 'true');
		} else {
			$this->enabled = true;
		}
	}

	/**
	 * get the user id
	 *
	 * @return string
	 */
	public function getUID() {
		return $this->uid;
	}

	/**
	 * get the displayname for the user, if no specific displayname is set it will fallback to the user id
	 *
	 * @return string
	 */
	public function getDisplayName() {
		return $this->displayName;
	}

	/**
	 * set the displayname for the user
	 *
	 * @param string $displayName
	 * @return bool
	 */
	public function setDisplayName($displayName) {
		if ($this->canChangeDisplayName()) {
			$this->displayName = $displayName;
			$result = $this->backend->setDisplayName($this->uid, $displayName);
			return $result !== false;
		} else {
			return false;
		}
	}

	//MITRE CODE
	//private $error_count;
	//private $locked;
	public function getErrorCount() {
		return $this->backend->getErrorCount($this->uid);
		//return $this->error_count;
	}
	public function getLocked() {
		return $this->backend->getLocked($this->uid);
		//return $this->locked;
	}
	public function setErrorCount($error_count) {
		$this->error_count = $error_count;
		$result = $this->backend->setErrorCount($this->uid, $error_count);
		return $result !== false;
	}
	public function setLocked($locked) {
		$this->locked = $locked;
		$result = $this->backend->setLocked($this->uid, $locked);
		return $result !== false;
	}
	//END MITRE CODE


	/**
	 * Delete the user
	 *
	 * @return bool
	 */
	public function delete() {
		if ($this->emitter) {
			$this->emitter->emit('\OC\User', 'preDelete', array($this));
		}
		$result = $this->backend->deleteUser($this->uid);
		if ($this->emitter) {
			$this->emitter->emit('\OC\User', 'postDelete', array($this));
		}
		return !($result === false);
	}

	/**
	 * Set the password of the user
	 *
	 * @param string $password
	 * @param string $recoveryPassword for the encryption app to reset encryption keys
	 * @return bool
	 */
	public function setPassword($password, $recoveryPassword) {
		if ($this->emitter) {
			$this->emitter->emit('\OC\User', 'preSetPassword', array($this, $password, $recoveryPassword));
		}
		if ($this->backend->implementsActions(\OC_USER_BACKEND_SET_PASSWORD)) {
			$result = $this->backend->setPassword($this->uid, $password);
			if ($this->emitter) {
				$this->emitter->emit('\OC\User', 'postSetPassword', array($this, $password, $recoveryPassword));
			}
			return !($result === false);
		} else {
			return false;
		}
	}

	/**
	 * get the users home folder to mount
	 *
	 * @return string
	 */
	public function getHome() {
		if (!$this->home) {
			if ($this->backend->implementsActions(\OC_USER_BACKEND_GET_HOME) and $home = $this->backend->getHome($this->uid)) {
				$this->home = $home;
			} elseif ($this->config) {
				$this->home = $this->config->getSystemValue('datadirectory') . '/' . $this->uid;
			} else {
				$this->home = \OC::$SERVERROOT . '/data/' . $this->uid;
			}
		}
		return $this->home;
	}

	/**
	 * check if the backend allows the user to change his avatar on Personal page
	 *
	 * @return bool
	 */
	public function canChangeAvatar() {
		if ($this->backend->implementsActions(\OC_USER_BACKEND_PROVIDE_AVATAR)) {
			return $this->backend->canChangeAvatar($this->uid);
		}
		return true;
	}

	/**
	 * check if the backend supports changing passwords
	 *
	 * @return bool
	 */
	public function canChangePassword() {
		return $this->backend->implementsActions(\OC_USER_BACKEND_SET_PASSWORD);
	}

	/**
	 * check if the backend supports changing display names
	 *
	 * @return bool
	 */
	public function canChangeDisplayName() {
		if ($this->config and $this->config->getSystemValue('allow_user_to_change_display_name') === false) {
			return false;
		} else {
			return $this->backend->implementsActions(\OC_USER_BACKEND_SET_DISPLAYNAME);
		}
	}

	/**
	 * check if the user is enabled
	 *
	 * @return bool
	 */
	public function isEnabled() {
		return $this->enabled;
	}

	/**
	 * set the enabled status for the user
	 *
	 * @param bool $enabled
	 */
	public function setEnabled($enabled) {
		$this->enabled = $enabled;
		if ($this->config) {
			$enabled = ($enabled) ? 'true' : 'false';
			$this->config->setUserValue($this->uid, 'core', 'enabled', $enabled);
		}
	}
}
