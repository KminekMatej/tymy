<?php

namespace Tymy\Module\Sign\Presenter\Front;

use Tymy\Module\Core\Presenter\Front\BasePresenter;

class OutPresenter extends BasePresenter
{

    public function actionDefault()
    {
        if (!is_null($this->getUser()->getIdentity())) {
            $this->getUser()->logout();
        }
        $this->flashMessage($this->translator->translate("common.alerts.logoutSuccesfull"));
        $this->redirect(':Sign:In:');
    }

}