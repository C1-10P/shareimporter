<?php
namespace OCA\ShareImporter\AppInfo;

use \OCP\AppFramework\App;

use \OCA\ShareImporter\Hooks\UserHooks;


class Application extends App {

    public function __construct(array $urlParams=array()){
        parent::__construct('shareimporter', $urlParams);

        $container = $this->getContainer();

        /**
         * Controllers
         */
        $container->registerService('UserHooks', function($c) {
            return new UserHooks(
                $c->query('AppName'),
                $c->query('ServerContainer')->getUserManager(),
                $c->query('ServerContainer')->getLogger(),
                $c->query('ServerContainer')->getUserStoragesService(),
                $c->query('ServerContainer')->getStoragesBackendService(),
                $c->query('ServerContainer')->getUserGlobalStoragesService()
            );
        });
    }
}
