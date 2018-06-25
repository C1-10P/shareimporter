<?php
namespace OCA\ShareImporter\Hooks;
use OCP\IUserManager;
use OCP\ILogger;
use OCA\Files_external\Service\UserStoragesService;
use OCA\Files_External\Service\BackendService;
use \OCA\Files_External\Service\UserGlobalStoragesService;

class UserHooks {

    private $userManager;
    private $logger;
    private $appName;
    private $storageService;
    private $backendService;
    private $globalStorage;

    public function __construct($appName, IUserManager $userManager, ILogger $logger, UserStoragesService $storageService, BackendService $backendService, UserGlobalStoragesService  $globalStorage){
        $this->appName = $appName;
        $this->userManager = $userManager;
        $this->logger = $logger;
        $this->storageService = $storageService;
        $this->backendService = $backendService;
        $this->globalStorage =  $globalStorage;
    }

    public function register() {
        $callback = function($user) {
           // your code that executes after $user login
           $this->mountShares($user);

        };
        $this->userManager->listen('\OC\User', 'postLogin', $callback);
    }
   
   private function mountShares($user) {
           $this->logger->info(json_encode($user->getUID()), array('app' => $this->appName));
           $userShares = $this->getUserShares($user);
           $existingUserMounts = $this->getExistingUserMounts($user);
           $this->logger->info(json_encode($existingUserMounts), array('app' => $this->appName));
   }

  private function getExistingUserMounts($user){
     //$existingMounts = $this->storageService->getAllStorages();
     $existingMounts = $this->globalStorage->getAllStoragesForUser();
     $existingUserMounts = array();
     $this->logger->info(json_encode($existingMounts), array('app' => $this->appName));

     foreach ($existingMounts as $existingMount) {
           if ( $existingMount->getApplicableUsers() == [$user->getUID()])      {
               //$this->globalService->removeStorage($mountId);
               $existingUserMounts[] = $existingMount; 
           }
     }
    return $existingUserMounts;

 }


   private function getUserSharesJson($user) {
       $obj = new \stdClass;
       $obj->username = "testuser";
       $obj->shares = array();
       $obj->shares[0] = new \stdClass;
       $obj->shares[0]->uri = "";
       //'{"id":1,"mountPoint":"\/test","backend":"smb","authMechanism":"password::password","backendOptions":{"host":"host","share":"share","root":"","domain":"","user":"user","password":"pass"},"priority":100,"applicableUsers":["test"],"mountOptions":{"encrypt":true,"previews":true,"enable_sharing":false,"filesystem_check_changes":1,"encoding_compatibility":false},"userProvided":false,"type":"system"}'
       $json = json_encode($obj);
       $this->logger->info($json, array('app' => $this->appName));
       return $json;

   }


   private function getUserShares($user) {
       $json = $this->getUserSharesJson($user);
       $obj = json_decode($json);
       return $obj;
     
   }    

}
