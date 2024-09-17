<?php

namespace Tymy\Module\Discussion\Presenter\Api;

use Exception;
use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Discussion\Manager\PostManager;

/**
 * Description of PostPresenter
 */
class PostPresenter extends SecuredPresenter
{
    public function injectPostManager(PostManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId): void
    {
        if (empty($subResourceId) && isset($this->requestData["id"]) && in_array($this->getRequest()->getMethod(), ["PUT", "DELETE"])) {
            $subResourceId = $this->requestData["id"];  //if subresourceid is not specified, take it from data
        }

        switch ($this->getRequest()->getMethod()) {
            case 'GET':
                $subResourceId ? $this->requestGet($resourceId, $subResourceId) : $this->requestGetList($resourceId, $this->getRequest()->getParameter("page") ?: 1);
            // no break
            case 'POST':
                if (empty($this->requestData)) {    //todo: move to some allow function or extend manager to allow sending null
                    $this->respondBadRequest("Missing input data");
                }
                $this->requestPost($resourceId);
                // no break
            case 'PUT':
                if (empty($this->requestData)) {    //todo: move to some allow function or extend manager to allow sending null
                    $this->respondBadRequest("Missing input data");
                }
                $this->requestPut($resourceId, $subResourceId);
                // no break
            case 'DELETE':
                $this->requestDelete($resourceId, $subResourceId);
        }
    }

    public function actionReact($resourceId, $subResourceId): void
    {
        if (!in_array($this->getRequest()->getMethod(), ["POST", "DELETE"])) {
            $this->respondNotAllowed();
        }

        $remove = $this->getRequest()->getMethod() == "DELETE";

        if (!is_string($this->requestData) && !is_null($this->requestData)) {
            $this->respondBadRequest("Reaction must be string or empty");
        }

        try {
            assert($this->manager instanceof PostManager);
            $this->manager->react($resourceId, $subResourceId, $this->user->getId(), $this->requestData, $remove);
        } catch (Exception $exc) {
            $this->respondByException($exc);
        }

        $this->respondOk();
    }

    public function actionMode($resourceId, $subResourceId, $mode): void
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        if (!in_array($mode, ["html", "bb"])) {
            $this->respondBadRequest();
        }

        assert($this->manager instanceof PostManager);
        $posts = $this->manager->mode($resourceId, $subResourceId ?: 1, $mode, $this->getRequest()->getParameter("search"), $this->getRequest()->getParameter("suser"), $this->getRequest()->getParameter("jump2date"));

        $this->respondOk($posts->jsonSerialize());
    }

    private function requestGetList($resourceId, $page = 1): never
    {
        $posts = null;
        try {
            assert($this->manager instanceof PostManager);
            $posts = $this->manager->mode($resourceId, $page, "html", $this->getRequest()->getParameter("search"), $this->getRequest()->getParameter("suser"), $this->getRequest()->getParameter("jump2date"));
        } catch (Exception $exc) {
            $this->respondByException($exc);
        }

        $this->respondOk($posts->jsonSerialize());
    }
}
