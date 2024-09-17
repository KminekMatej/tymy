<?php

namespace Tymy\Module\User\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\BasePresenter;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of PwdPresenter
 */
class PwdPresenter extends BasePresenter
{
    public function injectManager(UserManager $userManager): void
    {
        $this->manager = $userManager;
    }

    /*
     *
    @RequestMapping(value = "/pwdlost", method = RequestMethod.POST)
     */
    public function actionLost(): void
    {
        if ($this->getRequest()->getMethod() !== "POST") {
            $this->respondNotAllowed();
        }

        foreach (["email", "callbackUri", "hostname"] as $inputName) {
            if (!isset($this->requestData[$inputName])) {
                $this->responder->E4013_MISSING_INPUT($inputName);
            }
        }

        assert($this->manager instanceof UserManager);
        $this->manager->pwdLost($this->requestData["email"], $this->requestData["hostname"], $this->requestData["callbackUri"]);

        $this->respondOk();
    }

    /**
     * @RequestMapping(value = "/pwdreset/{resetCode}", method = RequestMethod.GET)
    */
    public function actionReset(?string $code): void
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        if (empty($code)) {
            $this->respondBadRequest($code);
        }

        assert($this->manager instanceof UserManager);
        $newPwd = $this->manager->pwdReset($code);

        $this->respondOk($newPwd);
    }
}
