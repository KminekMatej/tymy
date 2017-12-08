<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionDetail
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionDetail extends DiscussionResource {
    
    public function init() {
        //everything inited in constructor properly
    }
    
    public function composeUrl() {
        if($this->getId() == null) throw new APIException ("Discussion ID is missing");
        $this->setUrl("discussion/" . $this->getId());
        return $this;
    }

    

}
