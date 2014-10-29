<?php
OCP\User::checkLoggedIn();

$defaults = new \OCP\Defaults();

$tmpl = new OCP\Template( 'thirstrunwizard', 'wizard', '' );
$tmpl->printPage();

