<?php

namespace OCA_ThirstRunWizard;

class Config {

	/**
	* @brief Disable the FirstRunWizard
	*/
	public static function enable() {
		\OCP\Config::setUserValue( \OCP\User::getUser(), 'thirstrunwizard', 'show', 1 );
	}
	
	/**
	* @brief Enable the FirstRunWizard
	*/
	public static function disable() {
		\OCP\Config::setUserValue( \OCP\User::getUser(), 'thirstrunwizard', 'show', 0 );
	}

	/**
	* @brief Check if the FirstRunWizard is enabled or not
	* @return bool
	*/
	public static function isenabled() {
		$conf=\OCP\CONFIG::getUserValue( \OCP\User::getUser() , 'thirstrunwizard' , 'show' , 1 );
		if($conf==1) {
			return(true);
		}else{
			return(false);
		}
	}



}
