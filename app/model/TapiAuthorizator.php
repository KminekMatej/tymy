<?php

namespace App\Model;

use Nette;
use Tapi\AuthDetailResource;

/**
 * User privileges management
 *
 * @author kminekmatej
 */
class TapiAuthorizator implements Nette\Security\IAuthorizator {
    
    private $role;
    private $resource;
    private $user;
    
    /** @var AuthDetailResource */
    private $apiRights;
    
    public function isAllowed($role, $resource, $privilege) {
        $rights = $this->apiRights->getData();
        $rs = $resource . "Rights";
        if(property_exists($rights, $rs) && property_exists($rights->$rs, $privilege) ){
            return $rights->$rs->$privilege;
        } else {
            $this->role = $role;
            $this->resource = $resource;
            switch ($this->resource) {
                case "permissions":
                    return $this->isAdmin();
                /*case "users": // MAYBE UNCOMMENT IN FUTURE
                    return $this->usersPrivileges($privilege);
                case "settings":
                    return $this->settingsPrivileges($privilege);
                case "SYS":
                    return $this->sysPrivileges($privilege);*/
                default:
                    return TapiAuthorizator::DENY;
            }
        }
        
        /* RIGHTS EXAMPLE:
         * {
    "notesRights" : {
      "manageSharedNotes" : true
    },
    "discussionRights" : {
      "setup" : true
    },
    "eventRights" : {
      "canCreate" : true,
      "canDelete" : true,
      "canUpdate" : true,
      "canResult" : true,
      "canPlanOthers" : true
    },
    "pollRights" : {
      "canCreatePoll" : true,
      "canUpdatePoll" : true,
      "canDeletePoll" : true,
      "canResetVotes" : true
    },
    "reportsRights" : {
      "canSetup" : true
    },
    "teamRights" : {
      "canSetup" : true
    },
    "userRights" : {
      "canCreate" : true,
      "canUpdate" : true,
      "canDelete" : true
    }
}
         */
        
        
    }
    
    public function getApiRights() {
        return $this->apiRights;
    }

    public function setApiRights($apiRights) {
        $this->apiRights = $apiRights;
        return $this;
    }

        
    private function sysPrivileges($privilege){
        switch ($privilege) {
            case "EVE_UPDATE":
                return in_array($this->role, ["ATT"]) ? TapiAuthorizator::ALLOW : TapiAuthorizator::DENY;
            case "EVE_DELETE":
                return in_array($this->role, ["ATT"]) ? TapiAuthorizator::ALLOW : TapiAuthorizator::DENY;
            case "EVE_CREATE":
                return in_array($this->role, ["ATT"]) ? TapiAuthorizator::ALLOW : TapiAuthorizator::DENY;
            case "DSSETUP":
            case "ASK.VOTE_UPDATE":
            case "ASK.VOTE_DELETE":
            case "ASK.VOTE_CREATE":
                return $this->isAdmin();
            default:
                return TapiAuthorizator::DENY;
        }
    }
    
    private function usersPrivileges($privilege){
        switch ($privilege) {
            case "canSeeRegisteredUsers":
                return in_array($this->role, ["SUPER","USR"]) ? TapiAuthorizator::ALLOW : TapiAuthorizator::DENY;
            case "canLogin":
                return $this->getUser()->canLogin ? TapiAuthorizator::ALLOW : TapiAuthorizator::DENY;
            case "canDelete":
                return in_array($this->role, ["SUPER"]) ? TapiAuthorizator::ALLOW : TapiAuthorizator::DENY;
            default:
                return TapiAuthorizator::DENY;
        }
    }
    
    private function settingsPrivileges($privilege){
        switch ($privilege) {
            case "discussions"://TODO check for right SYS::DSSETUP (or possibly check whether api returns this possibility)
                return TapiAuthorizator::ALLOW;
            case "events"://TODO check for role SUPER and right PAGE::EVENT_TYPES (or possibly check whether api returns this possibility)
                return in_array($this->role, ["SUPER"]) ? TapiAuthorizator::ALLOW : TapiAuthorizator::DENY;
            case "team"://TODO check for role SUPER and right PAGE::TEAM_SETUP (or possibly check whether api returns this possibility)
                return in_array($this->role, ["SUPER"]) ? TapiAuthorizator::ALLOW : TapiAuthorizator::DENY;
            case "polls"://TODO check for right PAGE::VOTE and check for right SYS::ASK.VOTE_CREATE or SYS::ASK.VOTE_UPDATE or SYS::ASK.VOTE_DELETE
                return TapiAuthorizator::ALLOW;
            case "notes":
                return TapiAuthorizator::ALLOW;
            case "reports"://TODO check for right PAGE::REPORTS and right SYS::REP_SETUP (or possibly check whether api returns this possibility)
                return TapiAuthorizator::ALLOW;
            case "permissions"://TODO check for role SUPER and right PAGE::RIGHTS (or possibly check whether api returns this possibility)
                return in_array($this->role, ["SUPER"]) ? TapiAuthorizator::ALLOW : TapiAuthorizator::DENY;
            case "app"://TODO check for right PAGE::SETTINGS (or possibly check whether api returns this possibility)
                return TapiAuthorizator::ALLOW;
            default:
                return TapiAuthorizator::DENY;
        }
    }
    
    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }
    
    private function isAdmin(){
        return in_array($this->role, ["SUPER"]) ? TapiAuthorizator::ALLOW : TapiAuthorizator::DENY;
    }



}
