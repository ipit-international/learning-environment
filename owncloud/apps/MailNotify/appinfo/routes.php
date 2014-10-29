<?php

namespace OCA\MailNotify;

use \OCA\AppFramework\App;
use \OCA\MailNotify\DependencyInjection\DIContainer;

\OC::$CLASSPATH['OCA\MailNotify\DependencyInjection\DIContainer'] = 'MailNotify/dependencyinjection/dicontainer.php';
//require 'apps/MailNotify/dependencyinjection/dicontainer.php';

$this->create('mn_index', '/')->action(
    function($params){
        // call the index method on the class PageController
        App::main('PageController', 'index', $params, new DIContainer());
    }
);
