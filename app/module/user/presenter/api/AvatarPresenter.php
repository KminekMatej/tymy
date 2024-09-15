<?php

namespace Tymy\Module\User\Presenter\Api;

use Exception;
use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\User\Manager\AvatarManager;

/**
 * Description of AvatarPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 25. 10. 2020
 */
class AvatarPresenter extends SecuredPresenter
{
    #[\Nette\DI\Attributes\Inject]
    public AvatarManager $avatarManager;

    public function actionDefault(int $resourceId): void
    {
        if ($this->getRequest()->getMethod() !== "POST") {
            $this->respondNotAllowed();
        }

        if (empty($this->requestData) || !is_string($this->requestData)) {
            $this->respondBadRequest();
        }

        try {
            $this->avatarManager->uploadAvatarBase64($this->requestData, $resourceId);
        } catch (Exception $exc) {
            $this->respondByException($exc);
        }

        $this->respondOk();
    }
}
