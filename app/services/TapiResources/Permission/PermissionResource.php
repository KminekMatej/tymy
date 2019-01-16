<?php

namespace Tapi;

/**
 * Description of PermissionResource
 *
 * @author kminekmatej, 15.1.2019
 */
abstract class PermissionResource extends TapiObject{
    
    protected function postProcessPermission($permission){
        $this->timeLoad($permission->updatedAt);
    }
    
    protected function postProcessRight($right){
        //TODO
    }
    
    protected function clearCache($id = NULL, $name = NULL){
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:permissions"]);
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:permissionsType/USR"]);
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:permissionsType/SYS"]);
        
        if($id != NULL) $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:permissions/$id"]);
        if($name != NULL) $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:permissionName/$name"]);
    }
    
}
