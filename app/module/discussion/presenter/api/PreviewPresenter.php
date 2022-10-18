<?php

namespace Tymy\Module\Discussion\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Core\Service\BbService;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Discussion\Model\Discussion;

/**
 * Description of PreviewPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 14. 9. 2020
 */
class PreviewPresenter extends SecuredPresenter
{
    public function actionDefault($resourceId): void
    {
        if ($this->getRequest()->getMethod() != "POST") {
            $this->respondNotAllowed();
        }

        $this->requestPost($resourceId);
    }

    protected function requestPost($resourceId): void
    {
        if (!array_key_exists("post", $this->requestData)) {
            $this->responder->E4013_MISSING_INPUT("post");
        }

        $this->respondOk(BbService::bb2Html($this->requestData["post"]));
    }
}
