<?php

namespace App\Presenters;

use Nette\Application\UI\NewPostControl;
use Nette\Utils\Strings;
use Tymy;
use Tracy\Debugger;

/**
 * Description of DiscussionPresenter
 *
 * @author matej
 */
class DiscussionPresenter extends SecuredPresenter {
    
    /** @var \Tymy\Discussion @inject */
    public $discussion;

    /** @var \Tymy\Discussions @inject */
    public $discussions;

    public function __construct() {
        parent::__construct();
    }
    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => "Diskuze", "link" => $this->link("Discussion:")]]);
    }

    public function renderDefault() {
        $this->template->discussions = $this->discussions->setWithNew(true)->getData();
    }
    
    public function actionNewPost($discussion){
        $post = $this->getHttpRequest()->getPost("post");
        if (trim($post) != "") {
            $this->discussion
                    ->recId($discussion)
                    ->insert($post);
        }
        $this->setView('discussion');
    }

    public function actionEditPost($discussion){
        $postId = $this->getHttpRequest()->getPost("postId");
        $text = $this->getHttpRequest()->getPost("post");
        $sticky = $this->getHttpRequest()->getPost("sticky");
        $this->discussion
                ->recId($discussion)
                ->editPost($postId, $text, $sticky);
        $this->setView('discussion');
    }
    
    public function actionStickPost($discussion){
        $postId = $this->getHttpRequest()->getPost("postId");
        $sticky = $this->getHttpRequest()->getPost("sticky");
        $this->discussion
                ->recId($discussion)
                ->editPost($postId, NULL, $sticky);
        $this->setView('discussion');
    }
    
    public function renderDiscussion($discussion, $page, $search) {
        $discussionId = NULL;
        if(!$discussionId = intval($discussion)){
            foreach ($this->discussions->getData() as $dis) {
                if ($dis->webName == $discussion) {
                    $discussionId = $dis->id;
                    break;
                }
            }
        }

        if (is_null($discussionId) || $discussionId < 1)
            $this->error("Tato diskuze neexistuje");
        
        $this->discussion
                ->reset()
                ->recId($discussionId)
                ->setPage($page);
        if($search) 
            $this->discussion->search($search);
        $data = $this->discussion->getData();

        $this->setLevelCaptions(["2" => ["caption" => $data->discussion->caption, "link" => $this->link("Discussion:discussion", [$data->discussion->webName]) ] ]);
        
        $this->template->userId = $this->getUser()->getId();
        $this->template->users = $this->users->getData();
        $this->template->discussion = $data;
        $this->template->nazevDiskuze = $data->discussion->webName;
        $this->template->currentPage = is_numeric($page) ? $page : 1 ;
        $currentPage = is_numeric($page) ? $page : 1;
        $lastPage = is_numeric($data->paging->numberOfPages) ? $data->paging->numberOfPages : 1 ;
        $this->template->lastPage = $lastPage;
        $this->template->pagination = $this->pagination($lastPage, 1, $currentPage, 5);
        if($this->isAjax())
            $this->redrawControl("discussion");
    }
    
    protected function createComponentNewPost($discussion) {
        $newpost = new NewPostControl($discussion);
        $newpost->redrawControl();
        return $newpost;
    }
    
    private function pagination($data, $limit = null, $current = null, $adjacents = null) {
        $result = array();

        if (isset($data, $limit) === true) {
            $result = range(1, ceil($data / $limit));

            if (isset($current, $adjacents) === true) {
                if (($adjacents = floor($adjacents / 2) * 2 + 1) >= 1) {
                    $result = array_slice($result, max(0, min(count($result) - $adjacents, intval($current) - ceil($adjacents / 2))), $adjacents);
                }
            }
        }

        return $result;
    }

}
