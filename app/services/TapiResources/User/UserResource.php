<?php

namespace Tapi;
use Nette\Utils\Strings;
/**
 * Project: tymy_v2
 * Description of UserResource
 *
 * @author kminekmatej created on 22.12.2017, 21:04:36
 */
abstract class UserResource extends TapiObject{
    
    protected function postProcessSimpleUser($user){
        $user->webName = (string)$user->id;
        if(property_exists($user, "fullName")) $user->webName .= "-" . Strings::webalize($user->displayName);
        if(!property_exists($user, "gender")) $user->gender = "UNKNOWN"; //set default value
    }
    
    protected function postProcessUser($user){
        $this->postProcessSimpleUser($user);
        if(!property_exists($user, "language")) $user->language = "CZ"; //set default value
        if(!property_exists($user, "canEditCallName")) $user->canEditCallName = true; //set default value
        if(property_exists($user, "lastLogin")) $this->timeLoad($user->lastLogin);
        if(property_exists($user, "createdAt")) $this->timeLoad($user->createdAt);
        if(!property_exists($user, "roles")) $user->roles = [];
        $this->postProcessUserWarnings($user);
    }
    
    protected function postProcessUsers($users){
        foreach ($users as $user) {
            $this->postProcessUser($user);
        }
    }
    
    protected function clearCache($id = NULL){
        $this->cache->remove($this->getCacheKey("GET:users"));
        if($id != NULL){
            $this->cache->remove($this->getCacheKey("GET:user/$id"));
        }
    }
    
    protected function postProcessUserWarnings($player) {
        $player->errCnt = 0;
        $player->errFls = [];
        if (!isset($player->firstName) || empty($player->firstName)) {
            $player->errCnt++;
            $player->errFls[] = "firstName";
        }
        if (!isset($player->lastName) || empty($player->lastName)) {
            $player->errCnt++;
            $player->errFls[] = "lastName";
        }
        if (!isset($player->gender) || empty($player->gender)) {
            $player->errCnt++;
            $player->errFls[] = "gender";
        }
        if (!isset($player->phone) || empty($player->phone)) {
            $player->errCnt++;
            $player->errFls[] = "phone";
        }
        if (!isset($player->email) || empty($player->email) || filter_var($player->email, FILTER_VALIDATE_EMAIL) === FALSE) {
            $player->errCnt++;
            $player->errFls[] = "email";
        }
        if (!isset($player->birthDate) || empty($player->birthDate)) {
            $player->errCnt++;
            $player->errFls[] = "birthDate";
        }
        if (!isset($player->jerseyNumber) || empty($player->jerseyNumber)) {
            $player->errCnt++;
            $player->errFls[] = "jerseyNumber";
        }
        if ($player->status == "INIT") {
            $player->errCnt++;
            $player->errFls[] = "status";
        }
    }
}
