<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of PollListResource
 *
 * @author kminekmatej created on 5.1.2018, 9:51:45
 */

class PollListResource extends PollResource {
    
    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_LARGE);
        $this->setMenu(NULL);
        return $this;
    }

    protected function preProcess() {
        $this->setUrl($this->getMenu() ? "polls/menu" : "polls");
        return $this;
    }

    protected function postProcess() {
        $this->options->warnings = 0;
        if($this->data == null)
            return null;
        
        foreach ($this->data as $poll) {
            parent::postProcessPoll($poll);
        }
    }
    
    public function getMenu() {
        return $this->options->menu;
    }

    public function setMenu($menu) {
        $this->options->menu = $menu;
        return $this;
    }

}
