<?php

namespace Tapi;
use Tapi\TapiAbstraction;
use Nette\Utils\Strings;

/**
 * Description of AttendanceResource
 *
 * @author kminekmatej
 */
abstract class DiscussionResource extends TapiAbstraction {
    
    public static function getIdFromWebname($webname, $discussionsList){
        if (intval($webname)) return (int)$webname;
        foreach ($discussionsList as $dis) {
            if ($dis->webName == $webname) {
                return (int)$dis->id;
            }
        }
        return null;
    }
    
    public static function mergeListWithNews($discussionList, $discussionNewsList) {
        $news = [];
        $discussionList->warnings = 0;
        foreach ($discussionNewsList->getData() as $n) {
            $news[$n->id] = $n->newPosts;
        }
        foreach ($discussionList->getData() as $discussion) {
            $discussion->newInfo->newsCount = $news[$discussion->id];
            if ($news[$discussion->id] > 0)
                $discussionList->warnings++;
        }
        return $discussionList;
    }

    protected function postProcessDiscussion($discussion) {
        $discussion->webName = Strings::webalize($discussion->caption);
        if(!empty($discussion->updatedAt)){
            $this->timeLoad($discussion->updatedAt);
        }
        if(!empty($discussion->newInfo->lastVisit)){
            $this->timeLoad($discussion->newInfo->lastVisit);
        }
    }
    
    protected function postProcessDiscussionPost($post) {
        if(!empty($post->createdAt)){
             $this->timeLoad($post->createdAt);
        }
        if(!empty($post->updatedAt)){
             $this->timeLoad($post->updatedAt);
        }
    }
    
    protected function clearCache($id = NULL){
        $this->cacheService->clear("Tapi\DiscussionListResource");
        $this->cacheService->clear("Tapi\DiscussionNewsListResource");
        $this->cacheService->clear("Tapi\DiscussionPageResource");
        if($id != NULL){
            $this->cacheService->clear("Tapi\DiscussionDetailResource:$id");
        }
    }
}
