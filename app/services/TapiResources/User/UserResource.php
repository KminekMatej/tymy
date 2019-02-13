<?php

namespace Tapi;

use Nette\Caching\Cache;
use Nette\Localization\ITranslator;
use Nette\Utils\Strings;

/**
 * Project: tymy_v2
 * Description of UserResource
 *
 * @author kminekmatej created on 22.12.2017, 21:04:36
 */
abstract class UserResource extends TapiObject{
    
    const FIELDS_PERSONAL = ["gender", "firstName", "lastName", "phone", "email", "birthDate", "nameDayMonth", "nameDayDay", "language"];
    const FIELDS_LOGIN = ["callName", "canEditCallName", "login", "password", "canLogin"];
    const FIELDS_TEAMINFO = ["status", "jerseyNumber"];
    const FIELDS_ADDRESS = ["street", "city", "zipCode"];

    protected function postProcessSimpleUser($user){
        if($user == null) TapiService::throwNotFound();
        $user->webName = (string)$user->id;
        if(property_exists($user, "fullName")) $user->webName .= "-" . Strings::webalize($user->displayName);
        if(!property_exists($user, "gender")) $user->gender = "UNKNOWN"; //set default value
        if(!property_exists($user, "login")) $user->login = ""; //set default value
        if(!property_exists($user, "callName")) $user->callName = ""; //set default value
    }
    
    protected function postProcessUser($user){
        $this->postProcessSimpleUser($user);
        if(!property_exists($user, "firstName")) $user->firstName = ""; //set default value
        if(!property_exists($user, "lastName")) $user->lastName = ""; //set default value
        if(!property_exists($user, "fullName")) $user->fullName = ""; //set default value
        if(!property_exists($user, "jerseyNumber")) $user->jerseyNumber = ""; //set default value
        if(!property_exists($user, "street")) $user->street = ""; //set default value
        if(!property_exists($user, "city")) $user->city = ""; //set default value
        if(!property_exists($user, "zipCode")) $user->zipCode = ""; //set default value
        if(!property_exists($user, "birthDate")) $user->birthDate = ""; //set default value
        if(!property_exists($user, "phone")) $user->phone = ""; //set default value
        if(!property_exists($user, "phone2")) $user->phone2 = ""; //set default value
        if(!property_exists($user, "email")) $user->email = ""; //set default value
        if(!property_exists($user, "language")) $user->language = "CZ"; //set default value
        if(!property_exists($user, "canEditCallName")) $user->canEditCallName = true; //set default value
        if(property_exists($user, "lastLogin")) $this->timeLoad($user->lastLogin);
        if(property_exists($user, "createdAt")) $this->timeLoad($user->createdAt);
        if(!property_exists($user, "roles")) $user->roles = [];
        $user->isNew = strtotime($user->createdAt) > strtotime("- 14 days");
        $this->postProcessUserWarnings($user);
    }
    
    protected function postProcessUsers($users){
        foreach ($users as $user) {
            $this->postProcessUser($user);
        }
    }
    
    protected function clearCache($id = NULL){
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:users"]);
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:users/status/PLAYER"]);
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:users/status/MEMBER"]);
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:users/status/SICK"]);
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:users/status/INIT"]);
        if($id != NULL){
            $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:user/$id"]);
        }
    }
    
    protected function postProcessUserWarnings($player) {
        $player->errCnt = 0;
        $player->errFls = [];
        
        foreach ($this->supplier->getRequiredFields() as $requiredField) {
            if (!isset($player->$requiredField) || empty($player->$requiredField)) {
                $player->errCnt++;
                $player->errFls[] = $requiredField;
                continue;
            }
            
            //email validation secondary check
            if($requiredField == "email" && filter_var($player->email, FILTER_VALIDATE_EMAIL) === FALSE){
                $player->errCnt++;
                $player->errFls[] = "email";
            }
        }
        
        if (isset($player->status) && $player->status == "INIT") {
            $player->errCnt++;
            $player->errFls[] = "status";
        }
    }
    
    public static function getAllFields(ITranslator $translator){
        $ret = [];
        $ret["PERSONAL"] = [];
        $ret["LOGIN"] = [];
        $ret["TEAMINFO"] = [];
        $ret["ADDRESS"] = [];
        $ret["ALL"] = [];
        foreach (self::FIELDS_PERSONAL as $field){
            $caption = $translator->translate("team.".$field);
            $ret["PERSONAL"][$field] = $caption;
            $ret["ALL"][$field] = $caption;
        } 
        foreach (self::FIELDS_LOGIN as $field){
            $caption = $translator->translate("team.".$field);
            $ret["LOGIN"][$field] = $translator->translate("team.".$field);
            $ret["ALL"][$field] = $caption;
        } 
        foreach (self::FIELDS_TEAMINFO as $field){
            $caption = $translator->translate("team.".$field);
            $ret["TEAMINFO"][$field] = $translator->translate("team.".$field);
            $ret["ALL"][$field] = $caption;
        } 
        foreach (self::FIELDS_ADDRESS as $field){
            $caption = $translator->translate("team.".$field);
            $ret["ADDRESS"][$field] = $translator->translate("team.".$field);
            $ret["ALL"][$field] = $caption;
        } 
        return $ret;
    }
}
