<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionDetail
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionDetailResource extends DiscussionResource {
    
    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_LARGE);
        return $this;
    }
    
    public function preProcess() {
        if($this->getId() == null) throw new APIException('"Discussion ID is missing"', self::BAD_REQUEST);
        $this->setUrl("discussion/" . $this->getId());
        return $this;
    }

    protected function postProcess() {
        $this->postProcessDiscussion($this->data);
    }


}
