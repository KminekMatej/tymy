<?php

namespace Tymy\Module\PushNotification\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\PushNotification\Manager\PushNotificationManager;
use Tymy\Module\PushNotification\Model\Subscriber;

/**
 * Description of DefaultPresenter
 */
class FcmPresenter extends SecuredPresenter
{
    public function injectManager(PushNotificationManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId)
    {
        if ($this->getRequest()->getMethod() === 'POST') {
            $this->requestPost($resourceId);
        }

        $this->respondNotAllowed();
    }

    protected function requestPost($resourceId)
    {
        if (empty($this->requestData)) {
            $this->respondBadRequest("Missing request data");
        }

        if (!is_string($this->requestData)) {
            $this->respondBadRequest("Invalid device id");
        }

        $deviceId = $this->requestData;

        /* @var $subscriber Subscriber */
        $subscriber = $this->manager->getByUserAndSubscription($this->user->getId(), $deviceId);

        if ($subscriber) {
            $this->respondOk($subscriber->jsonSerialize());
        }

        $createdSubscription = $this->manager->create([
            "userId" => $this->user->getId(),
            "type" => Subscriber::TYPE_FCM,
            "subscription" => $deviceId,
        ]);

        if (!$createdSubscription) {
            $this->responder->E4011_CREATE_FAILED(Subscriber::MODULE);
        }

        $this->respondOkCreated($createdSubscription->jsonSerialize());
    }
}
