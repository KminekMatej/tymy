<?php

namespace Tapi;

/**
 * Description of UserDao
 *
 * @author kminekmatej
 */
class UserDao extends EditableDao {
    
    const TSID_REQUIRED = TRUE;
    
    public function getCreateUrl() {
        return "users/create";
    }

    public function getEditUrl($id) {
        if ($id == NULL)
            throw new \Tymy\Exception\APIException('User ID not set!');
        return "users/" .$this->recId . "/edit";
    }

    public function getFindUrl($id) {
        if ($id == NULL)
            throw new \Tymy\Exception\APIException('User ID not set!');
        return "user/" .$this->recId;
    }

    public function respond() {
        $mapper = new UserMapper();
        return $mapper->map($this->getData());
    }

}
