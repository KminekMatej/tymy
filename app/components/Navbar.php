<?php

namespace Nette\Application\UI;

use Nette;
use Nette\Utils\Strings;

/**
 * Description of Navbar
 *
 * @author matej
 */
class NavbarControl extends Control {
    
    private $discussions;
    private $players;
    private $user;
    private $presenter;
    
    
    public function __construct(Nette\Application\UI\Presenter $presenter) {
        parent::__construct();
        $this->discussions = new \Tymy\Discussions($presenter);
        $this->players = new \Tymy\Users($presenter);
        $this->user = $presenter->getUser();
        $this->presenter = $presenter;
    }
    
    private function discussions(){
        $this->discussions->setWithNew(true);
        $discussions = $this->discussions->fetch();
        
        $data = [];
        $unreadSum = 0;
        foreach ($discussions as $dis) {
            $unreadSum += $dis->newInfo->newsCount;
            $data[] = [
                "caption" => $dis->caption,
                "captionLink" => Strings::webalize($dis->caption),
                "unreadCount" => $dis->newInfo->newsCount,
                
            ];
                    
        }
        $this->template->unreadSum = $unreadSum;
        $this->template->discussions = $data;
        
    }
    
    private function players(){
        $players = $this->players->fetch();
        $playerErrors = 0;
        $counts = [
            "ALL"=>0,
            "PLAYER"=>0,
            "MEMBER"=>0,
            "SICK"=>0,
            "DELETED"=>0,];
        foreach ($players as $p) {
            $counts["ALL"]++;
            $counts[$p->status]++;
            if($p->id == $this->user->getId()){
                $playerErrors = $p->errCnt;
                $this->template->me = (object)$p;
            }
        }
        
        $this->template->counts = $counts;
        $this->template->playerErrors = $playerErrors;
        $this->template->players = (object)$players;
        
    }
    
    public function render(){
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/navbar.latte');
        $this->template->levels = $this->presenter->getLevelCaptions();
        $this->template->presenterName = $this->presenter->getName();
        $this->template->action = $this->presenter->getAction();
        $this->template->tym = $this->presenter->getSession()->getSection("tymy")->tym;
        $this->template->userId = $this->user->getId();
        
        //render menus
        $this->discussions();
        //render players
        $this->players();

        $template->render();
    }
    
    public function handleRefresh() {
        if ($this->parent->isAjax()) {
            $this->redrawControl('nav');
        } else {
            // redirect může jen presenter, nikoliv komponenta
            $this->parent->redirect('this');
        }
    }
}
