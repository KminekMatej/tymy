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
    private $search;
    
    public function select() {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Discussion ID not set!');

        if(!isset($this->search) && (!isset($this->page) || $this->page <= 0)) // page is not set
            throw new \Tymy\Exception\APIException("Invalid page specified");
        
        if($this->search)
            $this->setUriParam("search", $this->search);
        
        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId . "/" . self::MODE . "/" . $this->page;
        return $this;
    }
    
    public function search($text){
        $this->search = $text;
        return $this;
    }
    
    public function insert($text) {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Discussion ID not set!');

        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME . "/" . $this->recId . "/post/";

        $this->urlEnd();

        $this->method = "POST";
        
        $this->addPost("post", $text);
        $this->result = $this->execute();
        return $this->result;
    }
    
    public function editPost($idPost, $text = NULL, $sticky = NULL){
        if (!isset($idPost))
            throw new \Tymy\Exception\APIException('Post ID not set!');
        
        if($text == NULL && $sticky == NULL)
            return null;
        
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME . "/" . $this->recId . "/editPost/$idPost";

        $this->urlEnd();
        
        $this->method = "PUT";

        if($text != NULL) $this->addPost("post", $text);
        if($sticky != NULL) $this->addPost("sticky", $sticky);
        
        $this->result = $this->execute();
        return $this->result;
    }
    
    protected function postProcess() {
        $data = $this->getData();
        $this->timeLoad($data->discussion->newInfo->lastVisit);
        $data->discussion->webName = \Nette\Utils\Strings::webalize($data->discussion->caption);
        foreach ($data->posts as $post) {
            $this->timeLoad($post->createdAt);
            if(property_exists($post, "updatedAt")){
                $this->timeLoad($post->updatedAt);
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
    
    public function reset() {
        $this->page = NULL;
        $this->search = NULL;
        return parent::reset();
    }

}
