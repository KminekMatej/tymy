<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of AvatarUploadResource
 *
 * @author kminekmatej created on 31.12.2017, 16:01:41
 */
class AvatarUploadResource extends UserResource{
    
    public function init() {
        parent::globalInit();
        $this->setMethod(RequestMethod::POST);
        $this->setCacheable(FALSE);
        $this->setAvatar(NULL);
        $this->setJsonEncoding(FALSE);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('User ID not set');
        
        if (!$this->getAvatar())
            throw new APIException('Avatar not set');
        
        $this->setUrl("users/" . $this->getId() . "/avatar");
        $this->setRequestData($this->getAvatar());
        return $this;
    }
    
    protected function postProcess() {
    }
    
    public function getAvatar() {
        return $this->options->avatar;
    }

    public function setAvatar($avatar) {
        $this->options->avatar = $avatar;
        return $this;
    }


}
