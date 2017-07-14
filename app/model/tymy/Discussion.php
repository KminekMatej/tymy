<?php

namespace Tymy;

use Nette;
use Nette\Utils\Json;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Discussion extends Tymy{
    
    const MODE = "html";
    const TAPI_NAME = "discussion";
    const TSID_REQUIRED = TRUE;
    
    private $page;
    
    public function select() {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Discussion ID not set!');

        if(!isset($this->page) || $this->page <= 0) // page is not set
            throw new \Tymy\Exception\APIException("Invalid page specified");
        
        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId . "/" . self::MODE . "/" . $this->page;
        return $this;
    }
    
    public function search($text){
        $this->setUriParam("search", $text);
        return $this;
    }
    
    public function insert($text) {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Discussion ID not set!');

        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME . "/" . $this->recId . "/post/";

        $this->urlEnd();

        $this->addPost("post", $text);
        $this->result = $this->execute();
        return $this->result;
    }
    
    protected function postProcess() {
        $data = $this->getData();
        $this->timezone($data->discussion->newInfo->lastVisit);
        $data->discussion->webName = \Nette\Utils\Strings::webalize($data->discussion->caption);
        foreach ($data->posts as $post) {
            $this->timezone($post->createdAt);
            if(property_exists($post, "updatedAt")){
                $this->timezone($post->updatedAt);
                $post->updatedBy = $this->users->data[$post->updatedById];
            }
        }
    }
    
    public function getPage(){
        return $this->page;
    }
    
    public function setPage($page) {
        $this->page = is_numeric($page) ? $page : 1 ;
        return $this;
    }
    
    protected function reset() {
        $this->setPage(NULL);
        parent::reset();
    }

}
