<?php

namespace Tymy\Api\Module\User\Presenters;

use Exception;
use Tymy\Api\Module\Core\Presenters\SecuredPresenter;
use Tymy\Module\User\Manager\AvatarManager;

/**
 * Description of AvatarPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 25. 10. 2020
 */
class AvatarPresenter extends SecuredPresenter
{
    /** @var @inject */
    public AvatarManager $avatarManager;

    public function actionDefault($resourceId)
    {
        if ($this->getRequest()->getMethod() !== "POST") {
            $this->respondNotAllowed();
        }

        if (empty($this->requestData) || !is_string($this->requestData)) {
            $this->respondBadRequest();
        }

        try {
            $this->avatarManager->uploadProfileImage($this->requestData, $resourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk();
    }
}
