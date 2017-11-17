<?php

namespace App\Presenters;

class TeamPresenter extends SecuredPresenter {
    
    private $userType;
    
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
            $this->template->users = $this->users->reset()->setUserType($this->userType)->getData();
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }

    public function renderPlayer($player) {
        try {
            $user = $this->user
                    ->reset()
                    ->recId($this->parseIdFromWebname($player))
                    ->getData();
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }


        $this->setLevelCaptions(["2" => ["caption" => $user->displayName, "link" => $this->link("Team:player", $user->webName)]]);

        //set default values to avoid latte exceptions
        if (!isset($user->firstName))
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
            $this->user
                ->recId($bind["id"])
                ->edit($bind["changes"]);
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
    }
    
    public function handleDelete() {
        if (!$this->getUser()->isAllowed("users", "canDelete"))
            return;
        $bind = $this->getRequest()->getPost();
        try {
            $this->user
                    ->recId($bind["id"])
                    ->edit(["status" => "DELETED"]);
        } catch (\Tymy\Exception\APIException $ex) {
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
                $this->user
                        ->recId($bind["id"])
                        ->setAvatar($avatarB64);
            } catch (\Tymy\Exception\APIException $ex) {
                $this->handleTapiException($ex);
            }
        } else {
            $response = $this->getHttpResponse();
            $response->setCode(400);
        }
    }

}
