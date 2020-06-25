<?php

namespace Tapi;

use App\Model\Supplier;
use Kdyby\Translation\Translator;
use Nette\Caching\Storages\NewMemcachedStorage;
use Nette\Security\User;

/**
 * Project: tymy_v2
 * Description of UserListResource
 *
 * @author kminekmatej created on 29.12.2017, 19:57:30
 */
class UserListResource extends UserResource {
    
    /** @var Translator */
    private $translator;
    
    public function __construct(Supplier $supplier, User $user, TapiService $tapiService, NewMemcachedStorage $cacheStorage, Translator $translator) {
        parent::__construct($supplier, $user, $tapiService, $cacheStorage);
        $this->translator = $translator;
    }

    
    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_LARGE);
        $this->setUserType(NULL);
        $this->options->byId = null;
        $this->options->byTypeAndId = null;
        $this->options->me = null;
        $this->options->counts = null;
        return $this;
    }

    protected function preProcess() {
        $this->setUrl(is_null($this->getUserType()) ? "users" : "users/status/" . $this->getUserType());
        return $this;
    }
    
    protected function postProcess() {
        $this->options->warnings = 0;
        $this->options->byId = [];
        $this->options->byTypeAndId = [];
        $myId = $this->user->getId();

        $this->options->counts = [
            "ALL" => 0,
            "NEW" => 0,
            "PLAYER" => 0,
            "NEW:PLAYER" => 0,
            "MEMBER" => 0,
            "SICK" => 0,
            "DELETED" => 0,
            "INIT" => 0,
        ];
        if ($this->data) {
            foreach ($this->data as $user) {
                parent::postProcessUser($user);
                $this->options->counts["ALL"] ++;
                $this->options->counts[$user->status] ++;
                if ($user->id == $myId) {
                    $this->options->warnings = $user->errCnt;
                    $this->options->me = (object) $user;
                }
                if ($user->isNew) {
                    $this->options->counts["NEW"] ++;
                    if ($user->status == "PLAYER")
                        $this->options->counts["NEW:PLAYER"] ++;
                }
                $this->options->byId[$user->id] = $user;
                $this->options->byTypeAndId[$user->status][$user->id] = $user;
            }
        }
    }
    
    private function mockTeamUser(){
        return (object)[
            "id" => 0,
            "displayName" => "*** TEAM ***",
        ];
    }
    
    public function getByIdWithTeam(){
        
        return [$this->mockTeamUser()] + $this->options->byId;
    }
    
    public function getMeWithTeam(){
        
        return [$this->mockTeamUser(), $this->getMe()];
    }

    public function getUserType() {
        return $this->options->userType;
    }

    public function setUserType($userType) {
        $this->options->userType = $userType;
        return $this;
    }
    
    public function getCounts() {
        return $this->options->counts;
    }

    public function getById() {
        return $this->options->byId;
    }
    
    public function getByTypesAndId() {
        return $this->options->byTypeAndId;
    }

    public function getMe() {
        return $this->options->me;
    }

}
