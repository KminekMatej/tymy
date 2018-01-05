<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of PollResource
 *
 * @author kminekmatej created on 22.12.2017, 21:04:23
 */
abstract class PollResource extends TapiObject{
    
    protected function postProcessPoll($poll){
        $poll->warnings = 0;
        $poll->webName = \Nette\Utils\Strings::webalize($poll->id . "-" . $poll->caption);
        if ($poll->status == "OPENED" && $poll->canVote && !$poll->voted)
            $poll->warnings++;
        $this->timeLoad($poll->createdAt);
        $this->timeLoad($poll->updatedAt);
        if(!property_exists($poll, "minItems")) $poll->minItems = NULL;
        if(!property_exists($poll, "maxItems")) $poll->maxItems = NULL;
        if(!property_exists($poll, "description")) $poll->description = NULL;
        
        $poll->radio = $poll->minItems == 1 && $poll->maxItems == 1;
        if ($poll->radio && property_exists($poll, "options")) {
            foreach ($poll->options as $opt) {
                if ($opt->type != "BOOLEAN") {
                    $poll->radio = FALSE;
                    break;
                }
            }
        }
        
        if (property_exists($poll, "votes")){
            $orderedVotes = [];
            foreach ($poll->votes as $vote) {
                if (!$poll->anonymousResults && $vote->userId == $this->user->getId()) {
                    $poll->myVotes[$vote->optionId] = $vote;
                }
                $orderedVotes[$vote->userId][$vote->optionId] = $vote;
                $this->timeLoad($vote->updatedAt);
            }
            $poll->orderedVotes = $orderedVotes;
        }
    }
    
    protected function clearCache($id = NULL){
        $this->cacheService->clear("Tapi\PollListResource");
        if($id != NULL){
            $this->cacheService->clear("Tapi\PollDetailResource:$id");
        }
    }
    
}
