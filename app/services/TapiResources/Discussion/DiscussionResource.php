<?php

namespace Tapi;
use Tapi\TapiObject;
use Nette\Utils\Strings;
use Nette\Caching\Cache;

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
        if(!property_exists($discussion, "canStick")) $discussion->canStick = FALSE;
        if(!property_exists($discussion, "canDelete")) $discussion->canDelete = FALSE;
        if(!property_exists($discussion, "canWrite")) $discussion->canWrite = FALSE;
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
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:discussions/accessible/withNew"]);
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:discussions"]);
        if($id != NULL){
            $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:discussions/$id"]);
        }
    }
}
