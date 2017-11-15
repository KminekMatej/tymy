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
    
    public function edit($fields){
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Discussion ID not set!');
        if (!$fields)
            throw new \Tymy\Exception\APIException('Fields to edit not set!');
        if (!$this->user->isAllowed("SYS","DSSETUP"))
            throw new \Tymy\Exception\APIException('Permission denied!');
        
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME;

        $this->urlEnd();
        
        $this->method = "PUT";
        
        $fields["id"] = $this->recId;
        
        $this->setPostData((object)$fields);
        
        $this->result = $this->execute();
        return $this;
    }
    
    public function delete() {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Discussion ID not set!');
        if (!$this->user->isAllowed("SYS", "DSSETUP"))
            throw new \Tymy\Exception\APIException('Permission denied!');

        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME;

        $this->urlEnd();

        $this->method = "DELETE";
        
        $this->setPostData((object)["id" => $this->recId]);

        $this->result = $this->execute();
        return $this;
    }

    public function create($discussion){
        if (!array_key_exists("caption", $discussion))
            throw new \Tymy\Exception\APIException('Caption not set!');
        
        if (!$this->user->isAllowed("SYS", "DSSETUP"))
            throw new \Tymy\Exception\APIException('Permission denied!');
        
        $this->urlStart();

        $this->fullUrl .= "discussions";
        
        $this->method = "POST";
        
        $this->setPostData($discussion);
        
        $this->result = $this->execute();

        return $this;
    }
    
    protected function postProcess() {
        $this->getResult()->menuWarningCount = 0;
        if (($data = $this->getData()) == null)
            return;
        
        foreach ($data as $discussion) {
            $discussion->webName = \Nette\Utils\Strings::webalize($discussion->caption);
            if(!property_exists($discussion, "description")) $discussion->description = ""; //set default value
            if ($this->withNew){
                if(!property_exists($discussion, "newPosts")) $discussion->newPosts = 0; //set default value
                $this->getResult()->menuWarningCount += $discussion->newPosts;
                if(property_exists($discussion, "newInfo"))
                    $this->timeLoad($discussion->newInfo->lastVisit);
            }
                
        }
    }

}