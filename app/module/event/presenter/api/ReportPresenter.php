<?php

namespace Tymy\Module\Event\Presenter\Api;

use Tymy\Module\Core\Presenter\Front\SecuredPresenter;

/**
 * Description of ReportPresenter
 *
 * @author kminekmatej, 5. 1. 2023, 21:27:25
 */
class ReportPresenter extends SecuredPresenter
{
    public function renderExport(string $year, string $page)
    {
        if ($this->getRequest()->getMethod() != 'GET') {
            $this->respondNotAllowed();
        }
        throw new \Exception("mk test");
    }
}
