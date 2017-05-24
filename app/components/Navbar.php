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
    private $polls;
    private $events;
    private $user;
    private $presenter;
    
    
    public function __construct(Nette\Application\UI\Presenter $presenter) {
        parent::__construct();
        $this->discussions = new \Tymy\Discussions($presenter);
        $this->players = new \Tymy\Users($presenter);
        $this->polls = new \Tymy\Polls($presenter);
        $this->events = new \Tymy\Events($presenter);
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
            "NEW"=>0, // TODO NEW PLAYERS
            "PLAYER"=>0,
            "MEMBER"=>0,
            "SICK"=>0,
            "DELETED"=>0,
            "INIT"=>0,
            ];
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
    
    private function polls(){
        $polls = $this->polls->fetch();
        $unvoteCount = 0;
        foreach ($polls as $p) {
            $p->webName = Strings::webalize($p->caption);
            if($p->status == "OPENED" && $p->canVote && !$p->voted)
                $unvoteCount++;
        }
        $this->template->unVotePolls = $unvoteCount;
        $this->template->polls = (object)$polls;
        
    }
    
    private function events(){
        $events = $this->events
                ->withMyAttendance(true)
                ->from(date("Ymd"))
                ->to(date("Ymd", strtotime(" + 14 days")))
                ->fetch();
        $unsetCount = 0;
        foreach ($events as $e) {
            $e->webName = Strings::webalize($e->caption);
            if(property_exists($e, "myAttendance")){
                switch ($e->myAttendance->preStatus) {
                    case "YES":
                        $e->preClass = "success";
                        break;
                    case "LAT":
                        $e->preClass = "warning";
                        break;
                    case "NO":
                        $e->preClass = "danger";
                        break;
                    case "DKY":
                        $e->preClass = "danger";
                        break;
                    case "UNKNOWN":
                        $e->preClass = "secondary";
                        break;

                    default:
                        break;
                }
            } else {
                $unsetCount++;
            }
        }
        $this->template->unSetEvents = $unsetCount;
        $this->template->events = (object)$events;
        
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
        //render events
        $this->events();
        //render polls
        $this->polls();

        $template->render();
    }
    
    public function handleRefresh() {
        if ($this->parent->isAjax()) {
            $this->redrawControl('nav');
        } else {
            $this->parent->redirect('this');
        }
    }
}
