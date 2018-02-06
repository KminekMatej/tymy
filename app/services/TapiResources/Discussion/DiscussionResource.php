<?php

namespace Tapi;
use Tapi\TapiObject;
use Nette\Utils\Strings;

/**
 * Description of AttendanceResource
 *
 * @author kminekmatej
 */
abstract class DiscussionResource extends TapiObject {
    
    public static function getIdFromWebname($webname, $discussionsList){
        if (intval($webname)) return (int)$webname;
        foreach ($discussionsList as $dis) {
            if ($dis->webName == $webname) {
                return (int)$dis->id;
            }
        }
        return null;
    }

    protected function postProcessDiscussion($discussion) {
        if($discussion == null) TapiService::throwNotFound();
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
        $this->cache->remove($this->getCacheKey("GET:discussions/accessible/withNew"));
        $this->cache->remove($this->getCacheKey("GET:discussions"));
        if($id != NULL){
            $this->cache->remove("GET:discussions/$id");
        }
    }
}
