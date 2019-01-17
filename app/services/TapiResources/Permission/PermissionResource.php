<?php

namespace Tapi;

use Nette\Caching\Cache;
use Nette\Utils\Strings;

/**
 * Description of PermissionResource
 *
 * @author kminekmatej, 15.1.2019
 */
abstract class PermissionResource extends TapiObject{
    
    protected function postProcessPermission($permission){
        $this->timeLoad($permission->updatedAt);
        $permission->meAllowed = self::isUserAllowed($this->user->getId(), $this->user->getIdentity()->status, $this->user->getRoles(), $permission);
        $permission->webName = Strings::webalize($permission->name);
    }
    
    protected function postProcessRight($right){
        //nothing to post proccess
    }
    
    protected function clearCache($id = NULL, $name = NULL){
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:permissions"]);
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:permissionsType/USR"]);
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:permissionsType/SYS"]);
        
        if($id != NULL) $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:permissions/$id"]);
        if($name != NULL) $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:permissionName/$name"]);
    }
    
    public static function isUserAllowed($id, $status, $roles, $permission){
        if(self::revokedById($id, $permission) || self::revokedByRole($roles, $permission) || self::revokedByStatus($status, $permission)) return FALSE;
        return self::allowedById($id, $permission) || self::allowedByRole($roles, $permission) || self::allowedByStatus($status, $permission);
    }
    
    private static function allowedByRole($userRoles, $permission){
        if($permission->allowedRoles == null) return FALSE;
        return count(array_intersect($userRoles, $permission->allowedRoles)) > 0;
    }
    
    private static function revokedByRole($userRoles, $permission){
        return $permission->revokedRoles != null && count(array_intersect($userRoles, $permission->revokedRoles)) > 0;
    }
    
    private static function allowedByStatus($status, $permission){
        if($permission->allowedStatuses == null) return FALSE;
        return in_array($status, $permission->allowedStatuses);
    }
    
    private static function revokedByStatus($status, $permission){
        return $permission->revokedStatuses != null && in_array($status, $permission->revokedStatuses);
    }
    
    private static function allowedById($id, $permission){
        if($permission->allowedUsers == null) return FALSE;
        return in_array($id, $permission->allowedUsers);
    }
    
    private static function revokedById($id, $permission){
        return $permission->revokedUsers != null && in_array($id, $permission->revokedUsers);
    }
    
    
}
