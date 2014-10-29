<?php

namespace OCA\FileRouter\DependencyInjection;

use \OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;

use \OCA\FileRouter\Controller\PageController;

class DIContainer extends BaseContainer {

    public function __construct(){
        parent::__construct('FileRouter');

        // use this to specify the template directory
        //$this['TwigTemplateDirectory'] = __DIR__ . '/../templates';

        $this['PageController'] = function($c){
            return new PageController($c['API'], $c['Request']);
        };
    }

}
