<?php

namespace Tymy\Module\User\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\BasePresenter;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of PwdPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 21. 2. 2021
 */
class PwdPresenter extends BasePresenter
{
    public function injectManager(UserManager $userManager)
    {
        $this->manager = $userManager;
    }

    /*
     * 
    @RequestMapping(value = "/pwdlost", method = RequestMethod.POST)
     */
    public function actionLost()
    {
        if ($this->getRequest()->getMethod() !== "POST") {
            $this->respondNotAllowed();
        }

        foreach (["email", "callbackUri", "hostname"] as $inputName) {
            if (!isset($this->requestData[$inputName])) {
                $this->responder->E4013_MISSING_INPUT($inputName);
            }
        }

        $this->manager->pwdLost($this->requestData["email"], $this->requestData["hostname"], $this->requestData["callbackUri"]);

        $this->respondOk();
    }
    
    /**
     * 
    @RequestMapping(value = "/pwdreset/{resetCode}", method = RequestMethod.GET)
     * @param string $code
     */
    public function actionReset(?string $code)
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }
        
        if(empty($code)){
            $this->respondBadRequest($code);
        }
        
        $newPwd = $this->manager->pwdReset($code);
        
        $this->respondOk($newPwd);
    }
}
