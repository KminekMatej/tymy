<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of DiscussionNewsListResource
 *
 * @author kminekmatej created on 19.12.2017, 14:10:17
 */
class DiscussionNewsListResource extends DiscussionResource {
    
    public function init() {
        $this->setCacheable(FALSE);
    }
    
    public function preProcess() {
        $this->setUrl("discussions/newOnly");
        return $this;
    }

    protected function postProcess() {
        $this->options->warnings = 0;
        foreach ($this->data as $discussion) {
            if($discussion->newPosts > 0) $this->options->warnings++;
        }
    }
    

}
