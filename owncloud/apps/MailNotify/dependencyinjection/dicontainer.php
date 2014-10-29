<?php

namespace OCA\MailNotify\DependencyInjection;

\OC::$CLASSPATH['OCA\MailNotify\Controller\PageController'] = 'MailNotify/controller/pagecontroller.php';
use \OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;
use \OCA\MailNotify\Controller\PageController;

//require 'apps/MailNotify/dependencyinjection/dicontainer.php';
//require 'apps/MailNotify/controller/pagecontroller.php';



class DIContainer extends BaseContainer {

    public function __construct(){
        parent::__construct('MailNotify');

        // use this to specify the template directory
        //$this['TwigTemplateDirectory'] = __DIR__ . '/../templates';

        $this['PageController'] = function($c){
            return new PageController($c['API'], $c['Request']);
        };
    }

}
