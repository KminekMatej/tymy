<?php

namespace Tymy\Module\PushNotification\Presenter;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\PushNotification\Manager\PushNotificationManager;
use Tymy\Module\PushNotification\Model\Subscriber;

/**
 * Description of DefaultPresenter
 */
class DefaultPresenter extends SecuredPresenter
{
    
    private Subscriber $subscriber = null;

    /** @inject */
    public PushNotificationManager $pushNotificationManager;

    public function actionDefault(int $resourceId)
    {
        $this->subscriber = $this->loadResource($resourceId, $this->pushNotificationManager);

        $this->allowRequest();

        switch ($this->getRequest()->getMethod()) {
            case 'GET':
                $resourceId ? $this->requestGet() : $this->requestGetList();
                break;
            default:
                $this->respondNotAllowed();
                break;
        }
    }

    protected function allowRead(?int $recordId = null): void
    {
        if ($recordId) {
            if (!$this->canRead($this->subscriber, $this->user->getId())) {
                $this->responder->E4001_VIEW_NOT_PERMITTED(Subscriber::MODULE, $recordId);
            }
        }
    }

    /**
     * @todo Disabled - to be enabled if this would be needed at all
     */
    private function requestGet()
    {
        $this->respondBadRequest();
        $this->respondOk($this->subscriber->toJson());
    }
    
    /**
     * @todo Disabled - to be enabled if this would be needed at all
     */
    private function requestGetList()
    {
        $this->respondBadRequest();
    }
}
