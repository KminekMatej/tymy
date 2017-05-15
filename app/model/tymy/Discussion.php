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
    
    
    public function __construct(Nette\Application\UI\Presenter $presenter = NULL, $html = FALSE, $page) {
        parent::__construct($presenter);
        $this->mode = $html ? "html" : "bb";
        $this->page = is_numeric($page) ? $page : 1 ;
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
        \Tracy\Debugger::barDump("Searching for $text");
        $this->setUriParam("search", $text);
        return $this;
    }
    
    public function insert($text) {
        if (!isset($this->recId))
            throw new APIException('Discussion ID not set!');

        $this->urlStart();

        $this->fullUrl .= "discussion/" . $this->recId . "/post/";

        $this->urlEnd();

        $this->addPost("post", $text);

        return $this->execute();
    }
    
    protected function tzFields($jsonObj){
        $this->timezone($jsonObj->discussion->newInfo->lastVisit);
        foreach ($jsonObj->posts as $post) {
            $this->timezone($post->createdAt);
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

}
