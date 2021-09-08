<?php

namespace Tymy\Api\Module\Discussion\Presenters;

use Exception;
use Tymy\Api\Module\Core\Presenters\SecuredPresenter;
use Tymy\Module\Discussion\Manager\PostManager;

/**
 * Description of PostPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 16. 9. 2020
 */
class PostPresenter extends SecuredPresenter
{
    public function injectPostManager(PostManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId)
    {
        if (empty($subResourceId) && isset($this->requestData["id"]) && in_array($this->getRequest()->getMethod(), ["PUT", "DELETE"])) {
            $subResourceId = $this->requestData["id"];  //if subresourceid is not specified, take it from data
        }

        switch ($this->getRequest()->getMethod()) {
            case 'GET':
                $subResourceId ? $this->requestGet($resourceId, $subResourceId) : $this->requestGetList($resourceId, $this->getRequest()->getParameter("page") ?: 1);
                // no break
            case 'POST':
                $this->requestPost($resourceId);
                // no break
            case 'PUT':
                $this->requestPut($resourceId, $subResourceId);
                // no break
            case 'DELETE':
                $this->requestDelete($resourceId, $subResourceId);
        }
    }

    public function actionMode($resourceId, $subResourceId, $mode)
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        if (!in_array($mode, ["html", "bb"])) {
            $this->respondBadRequest();
        }

        $posts = $this->manager->mode($resourceId, $subResourceId, $mode, $this->getRequest()->getParameter("search"), $this->getRequest()->getParameter("suser"), $this->getRequest()->getParameter("jump2date"));

        $this->respondOk($posts->jsonSerialize());
    }

    private function requestGetList($resourceId, $page = 1)
    {
        try {
            $posts = $this->manager->mode($resourceId, $page, "html", $this->getRequest()->getParameter("search"), $this->getRequest()->getParameter("suser"), $this->getRequest()->getParameter("jump2date"));
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($posts->jsonSerialize());
    }
}
