<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Discussions extends Tymy{
    
    const TAPI_NAME = "discussions";
    const TSID_REQUIRED = TRUE;
    private $withNew = FALSE;
        
    public function getWithNew() {
        return $this->withNew;
    }

    public function setWithNew($withNew){
        $this->withNew = $withNew;
        return $this;
    }
    
    public function select() {
        $url = self::TAPI_NAME;
        if($this->withNew)
            $url .= "/withNew";
        $this->fullUrl .= $url;
        return $this;
    }
    
    protected function postProcess() {
        $data = $this->getData();
        $this->getResult()->menuWarningCount = 0;
        foreach ($data as $discussion) {
            $discussion->webName = \Nette\Utils\Strings::webalize($discussion->caption);
            if ($this->withNew){
                if(!property_exists($discussion, "newPosts")) $discussion->newPosts = 0; //set default value
                $this->getResult()->menuWarningCount += $discussion->newPosts;
                if(property_exists($discussion, "newInfo"))
                    $this->timeLoad($discussion->newInfo->lastVisit);
            }
                
        }
    }

}