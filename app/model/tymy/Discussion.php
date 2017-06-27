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
    
    private $mode;
    private $page;
    private $search;
    private $users;
    
    
    public function __construct(\App\Model\TapiAuthenticator $tapiAuthenticator = NULL, Nette\Application\UI\Presenter $presenter = NULL, $html = FALSE, $page) {
        parent::__construct($tapiAuthenticator, $presenter);
        $this->mode = $html ? "html" : "bb";
        $this->page = is_numeric($page) ? $page : 1 ;
        $this->users = $presenter ? $presenter->getUsers() : NULL;
    }
    
    public function select() {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Discussion ID not set!');

        if($this->page < 1)
            throw new \Tymy\Exception\APIException("Page do not exist");
        
        $this->fullUrl .= "discussion/" .$this->recId . "/" . $this->mode . "/" . $this->page;
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

        $this->fullUrl .= "discussion/" . $this->recId . "/post/";

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
    
    public function getMode(){
        return $this->mode;
    }

    public function getPage(){
        return $this->page;
    }

    public function getSearch(){
        return $this->search;
    }
    
    public function setUsers($users) {
        $this->users = $users;
        return $this;
    }


}
