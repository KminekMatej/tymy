<?php

namespace App\Presenters;
use Tapi\UserCreateResource;
use Tapi\UserEditResource;
use Tapi\UserDeleteResource;
use Tapi\AvatarUploadResource;
use Tapi\Exception\APIException;

class TeamPresenter extends SecuredPresenter {
    
    private $userType;
    
    /** @var UserCreateResource @inject */
    public $userCreator;
    
    /** @var UserEditResource @inject */
    public $userEditor;
    
    /** @var UserDeleteResource @inject */
    public $userDeleter;
    
    /** @var AvatarUploadResource @inject */
    public $avatarUploader;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => "Tým", "link" => $this->link("Team:") ] ]);
    }
    
    public function actionPlayers() {
        $this->setLevelCaptions(["2" => ["caption" => "Hráči", "link" => $this->link("Team:players") ] ]);
        $this->userType = "PLAYER";
        $this->setView('default');
    }
    
    public function actionMembers() {
        $this->setLevelCaptions(["2" => ["caption" => "Členové", "link" => $this->link("Team:members") ] ]);
        $this->userType = "MEMBER";
        $this->setView('default');
    }
    
    public function actionSicks() {
        $this->setLevelCaptions(["2" => ["caption" => "Marodi", "link" => $this->link("Team:sicks") ] ]);
        $this->userType = "SICK";
        $this->setView('default');
    }
    
    public function actionInits() {
        $this->setLevelCaptions(["2" => ["caption" => "Registrovaní", "link" => $this->link("Team:inits") ] ]);
        $this->userType = "INIT";
        $this->setView('default');
    }
    
    public function renderDefault() {
        try {
            $users = $this->userList->setUserType($this->userType)->getData();
            $allMails = [];
            foreach ($users as $u) {
                if(property_exists($u, "email")){
                    $allMails[] = $u->email;
                }
            }
            $this->template->users = $users;
            $this->template->allMails = join(",", $allMails);
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        $this->cacheService->dumpCache();
    }

    public function renderPlayer($player) {
        try {
            $user = $this->userDetail
                    ->setId($this->parseIdFromWebname($player))
                    ->getData();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }


        $this->setLevelCaptions(["2" => ["caption" => $user->displayName, "link" => $this->link("Team:player", $user->webName)]]);

        //set default values to avoid latte exceptions
        if (!isset($user->firstName))//TODO handle in latte or postProcess
            $user->firstName = "";
        if (!isset($user->lastName))
            $user->lastName = "";
        if (!isset($user->login))
            $user->login = "";
        if (!isset($user->callName))
            $user->callName = "";
        if (!isset($user->jerseyNumber))
            $user->jerseyNumber = "";
        if (!isset($user->street))
            $user->street = "";
        if (!isset($user->city))
            $user->city = "";
        if (!isset($user->zipCode))
            $user->zipCode = "";
        if (!isset($user->birthDate))
            $user->birthDate = "";
        if (!isset($user->phone))
            $user->phone = "";
        if (!isset($user->phone2))
            $user->phone2 = "";
        if (!isset($user->email))
            $user->email = "";

        $this->template->player = $user;
        $allRoles = [];
        $allRoles[] = (object) ["code" => "SUPER", "caption" => "Administrátor", "class" => $this->supplier->getRoleClass("SUPER")];
        $allRoles[] = (object) ["code" => "USR", "caption" => "Správce uživatelů", "class" => $this->supplier->getRoleClass("USR")];
        $allRoles[] = (object) ["code" => "ATT", "caption" => "Správce docházky", "class" => $this->supplier->getRoleClass("ATT")];

        $this->template->allRoles = $allRoles;
    }

    public function handleEdit(){
        $bind = $this->getRequest()->getPost();
        if(array_key_exists("roles", $bind["changes"]) && $bind["changes"]["roles"] === ""){
            $bind["changes"]["roles"] = [];
        }
        try {
            $this->userEditor
                ->setId($bind["id"])
                ->setUserData($bind["changes"])
                ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, "this");
        }
    }
    
    public function handleDelete() {
        if (!$this->getUser()->isAllowed("users", "canDelete"))
            return;
        $bind = $this->getRequest()->getPost();
        try {
            $this->userDeleter
                    ->setId($bind["id"])
                    ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        $this->flashMessage("Uživatel byl úspešně smazán", "success");
        $this->redirect('Team:');
    }

    public function handleUpload() {
        $bind = $this->getRequest()->getPost();
        $files = $this->getRequest()->getFiles();
        $file = $files["files"][0];
        if ($file->isImage() && $file->isOk()) {
            $avatarB64 = 'data:' . mime_content_type($file->getTemporaryFile()) . ';base64,' . base64_encode(file_get_contents($file->getTemporaryFile()));
            try {
                $this->avatarUploader
                        ->setId($bind["id"])
                        ->setAvatar($avatarB64);
            } catch (APIException $ex) {
                $this->handleTapiException($ex, "this");
            }
        } else {
            $response = $this->getHttpResponse();
            $response->setCode(400);
        }
    }

}
