<?php
namespace Tymy\Module\PushNotification\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\PushNotification\Manager\PushNotificationManager;
use Tymy\Module\PushNotification\Model\PushNotification;
use Tymy\Module\PushNotification\Model\Subscriber;

/**
 * Description of DefaultPresenter
 */
class DefaultPresenter extends SecuredPresenter
{

    private ?Subscriber $subscriber = null;

    public function injectManager(PushNotificationManager $manager): void
    {
        $this->manager = $manager;
    }
    
    public function actionDefault($resourceId, $subResourceId)
    {
        switch ($this->getRequest()->getMethod()) {
            /*case 'GET':
                $resourceId ? $this->requestGet($resourceId, $subResourceId) : $this->requestGetList();*/
                // no break
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

        if (!array_key_exists('userId', $this->requestData)) {
            $this->responder->E4013_MISSING_INPUT("userId");
        }

        if (!array_key_exists('subscription', $this->requestData)) {
            $this->responder->E4013_MISSING_INPUT("subscription");
        }

        $pushNotification = $this->pushNotificationManager->getByUserAndSubscription($this->requestData['userId'], $this->requestData['subscription']);

        if ($pushNotification !== false) {
            $this->respondOk($pushNotification->toJson());
        }

        $createdSubscription = $this->pushNotificationManager->create($this->requestData);

        if (!$createdSubscription) {
            $this->responder->E4011_CREATE_FAILED(PushNotification::MODULE);
        }

        $this->respondOkCreated($createdSubscription->toJson());
    }
}
