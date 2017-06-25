<?php

namespace App\Model;

use Nette;

/**
 * User privileges management
 *
 * @author kminekmatej
 */
class TapiAuthorizator implements Nette\Security\IAuthorizator {
    
    private $role;
    private $resource;
    private $user;
    
    public function isAllowed($role, $resource, $privilege) {
        $this->role = $role;
        $this->resource = $resource;
        switch ($this->resource) {
            case "users":
                return $this->usersPrivileges($privilege);
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



}
