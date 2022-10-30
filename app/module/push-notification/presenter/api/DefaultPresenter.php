<?php

namespace Tymy\Module\PushNotification\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\PushNotification\Manager\NotificationGenerator;
use Tymy\Module\PushNotification\Manager\PushNotificationManager;
use Tymy\Module\PushNotification\Model\PushNotification;
use Tymy\Module\PushNotification\Model\Subscriber;

/**
 * Description of DefaultPresenter
 */
class DefaultPresenter extends SecuredPresenter
{
    public function injectManager(PushNotificationManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId): void
    {
        if ($this->getRequest()->getMethod() === 'POST') {
            $this->requestPost($resourceId);
        }

        $this->respondNotAllowed();
    }

    protected function requestPost($resourceId): void
    {
        if (empty($this->requestData)) {
            $this->respondBadRequest("Missing request data");
        }

        $subscription = \json_encode($this->requestData, JSON_THROW_ON_ERROR);
        assert($this->manager instanceof PushNotificationManager);
        $subscriber = $this->manager->getByUserAndSubscription($this->user->getId(), $subscription);
        assert($subscriber instanceof Subscriber);

        if ($subscriber) {
            $this->respondOk($subscriber->jsonSerialize());
        }

        $createdSubscription = $this->manager->create([
            "userId" => $this->user->getId(),
            "subscription" => $subscription,
        ]);

        if (!$createdSubscription) {
            $this->responder->E4009_CREATE_FAILED(Subscriber::MODULE);
        }

        $this->respondOkCreated($createdSubscription->jsonSerialize());
    }
}
