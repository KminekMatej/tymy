<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of AvatarUploadResource
 *
 * @author kminekmatej created on 31.12.2017, 16:01:41
 */
class AvatarUploadResource extends UserResource{
    
    private $avatar;
    
    public function init() {
        $this->setCacheable(FALSE);
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('ID not set!');
        
        if ($this->getAvatar())
            throw new APIException('Avatar not set!');
        
        $this->setUrl("user/" . $this->getId() . "/avatar");
        $this->setRequestData($this->getAvatar());
        $this->setJsonEncoding(FALSE);
        return $this;
    }
    
    protected function postProcess() {
    }
    
    public function getAvatar() {
        return $this->avatar;
    }

    public function setAvatar($avatar) {
        $this->avatar = $avatar;
        return $this;
    }


}
