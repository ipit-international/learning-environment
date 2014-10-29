<?php

namespace OCA\FileRouter;

use \OCA\AppFramework\App;
use \OCA\FileRouter\DependencyInjection\DIContainer;

$this->create('tx_index', '/')->action(
    function($params){
        // call the index method on the class PageController
        App::main('PageController', 'index', $params, new DIContainer());
    }
);