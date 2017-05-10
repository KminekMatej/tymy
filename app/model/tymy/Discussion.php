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
    
    
    public function __construct(Nette\Application\UI\Presenter $presenter, $html = FALSE, $page) {
        parent::__construct($presenter);
        $this->mode = $html ? "html" : "bb";
        $this->page = is_numeric($page) ? $page : 1 ;
    }
    
    public function select() {
        if (!isset($this->recId))
            throw new TymyException('Discussion ID not set!');

        if($this->page < 1)
            throw new TymyException("Page do not exist");
        
        $this->fullUrl .= "discussion/" .$this->recId . "/" . $this->mode . "/" . $this->page;
        return $this;
    }
    
    public function search($text){
        \Tracy\Debugger::barDump("Searching for $text");
        $this->setUriParam("search", $text);
        return $this;
    }
    
    public function insert($text) {
        //TODO change date to UTC when performing insert
        if (!isset($this->recId))
            throw new TymyException('Discussion ID not set!');

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

}
