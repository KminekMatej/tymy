<?php
namespace Tymy\Module\PushNotification\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\PushNotification\Manager\PushNotificationManager;
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

    public function actionDefault($resourceId, $subResourceId)
    {
        switch ($this->getRequest()->getMethod()) {
            case 'POST':
                $this->requestPost($resourceId);
            // no break
        }

        $this->respondNotAllowed();
    }

    protected function requestPost($resourceId)
    {
        if (empty($this->requestData)) {
            $this->respondBadRequest("Missing request data");
        }

        $subscription = \json_encode($this->requestData);
        /* @var $subscriber Subscriber */
        $subscriber = $this->manager->getByUserAndSubscription($this->user->getId(), $subscription);

        if ($subscriber) {
            $this->respondOk($subscriber->jsonSerialize());
        }

        $createdSubscription = $this->manager->create([
            "userId" => $this->user->getId(),
            "subscription" => $subscription,
        ]);

        if (!$createdSubscription) {
            $this->responder->E4011_CREATE_FAILED(Subscriber::MODULE);
        }

        $this->respondOkCreated($createdSubscription->jsonSerialize());
    }
}
