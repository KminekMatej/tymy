<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Tymy;

/**
 * UserInterface - interface for adding special user based function on User and Users api
 *
 * @author kminekmatej
 */
abstract class UserInterface extends Tymy{
    
    protected function userPermissions(&$player){
        if(!property_exists($player, "permissions")){
            $player->permissions = [];
        }
        $player->permissions["canSeeRegisteredUsers"] = property_exists($player, "roles") && array_intersect(["SUPER","USR"], $player->roles) ? TRUE : FALSE;
        $player->permissions["canLogin"] = $player->canLogin;
    }
    
    protected function userWarnings(&$player) {
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
        if (!isset($player->callName) || empty($player->callName)) {
            $player->errCnt++;
            $player->errFls[] = "callName";
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
