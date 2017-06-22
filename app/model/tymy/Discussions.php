<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Discussions extends Tymy{
    
    private $withNew = FALSE;
    
    public function setWithNew($withNew){
        $this->withNew = $withNew;
        return $this;
    }
    
    public function select() {
        $url = "discussions";
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
                $this->getResult()->menuWarningCount += $discussion->newPosts;
                if(property_exists($discussion, "newInfo"))
                    $this->timezone($discussion->newInfo->lastVisit);
            }
                
        }
    }

}