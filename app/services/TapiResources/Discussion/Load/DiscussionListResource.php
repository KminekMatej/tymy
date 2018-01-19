<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of DiscussionListResource
 *
 * @author kminekmatej created on 19.12.2017, 14:01:17
 */
class DiscussionListResource extends DiscussionResource {
    
    public function init() {
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_LARGE);
        return $this;
    }
    
    public function preProcess() {
        $this->setUrl("discussions/accessible/withNew");
        return $this;
    }

    protected function postProcess() {
        $this->options->warnings = 0;
        foreach ($this->data as $discussion) {
            parent::postProcessDiscussion($discussion);
            if($discussion->newInfo->newsCount > 0) $this->options->warnings++;
        }
        
    }


}
