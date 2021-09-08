<?php

namespace Tymy\Api\Module\News\Presenters;

use Tymy\Api\Module\Core\Presenters\SecuredPresenter;
use Tymy\Module\News\Manager\NewsManager;

/**
 * Description of DefaultPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 30. 11. 2020
 */
class DefaultPresenter extends SecuredPresenter
{

    public function injectManager(NewsManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId)
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        $news = $this->manager->getListUserAllowed($this->user->getId());
        $this->respondOk($this->arrayToJson($news));
    }

}
