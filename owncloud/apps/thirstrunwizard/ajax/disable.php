<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('thirstrunwizard');
OCP\JSON::callCheck();
\OCA_ThirstRunWizard\Config::disable();
