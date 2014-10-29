<?php

OC::$CLASSPATH['OCA_ThirstRunWizard\Config'] = 'thirstrunwizard/lib/thirstrunwizard.php';

OCP\Util::addStyle( 'thirstrunwizard', 'colorbox');
OCP\Util::addScript( 'thirstrunwizard', 'jquery.colorbox');
OCP\Util::addScript( 'thirstrunwizard', 'thirstrunwizard');

OCP\Util::addStyle('thirstrunwizard', 'thirstrunwizard');

if(\OCP\User::isLoggedIn() and \OCA_ThirstRunWizard\Config::isenabled()){
	OCP\Util::addScript( 'thirstrunwizard', 'activate');
}
