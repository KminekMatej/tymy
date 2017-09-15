<?php

namespace Tapi;

/**
 * Description of UserMapper
 *
 * @author kminekmatej
 */
class UserMapper implements MapperInterface {
    
    public function map(\stdClass $tapiResult) {
        $user = new User();
        foreach ($tapiResult as $property => $value) {
            call_user_func(array($user, "set" . ucfirst($property)), $value);
        }
    }

}
