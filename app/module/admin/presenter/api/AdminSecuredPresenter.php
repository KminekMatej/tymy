<?php

namespace Tymy\Module\Admin\Presenter\Api;

use Tymy\Module\Admin\Manager\AdminManager;
use Tymy\Module\Core\Presenter\Api\BasePresenter;

/**
 * Description of AdminSecuredPresenter
 *
 * @author kminekmatej, 25. 10. 2021
 */
class AdminSecuredPresenter extends BasePresenter
{
    /** @inject */
    public AdminManager $adminManager;

    protected function startup()
    {
        parent::startup();

        $token = $this->getTokenFromHeader('Authorization') ?: $this->getTokenFromHeader('X-Authorization') ?: $this->getTokenFromUrl('token');

        if (!$token || !$this->adminManager->allowToken($token)) {
            $this->responder->E401_UNAUTHORIZED();
        }
    }

    /**
     * Attempts to load Admin token from specified header
     * If there is something sent inside specified header, always logout current user and work just with this header
     *
     * @param string $header Name of header to try to parse from
     * @return string|null If bearer is not found or is invalid formatted
     */
    private function getTokenFromHeader($header): ?string
    {
        $headerContent = $this->getHttpRequest()->getHeader($header);

        if (empty($headerContent)) {
            return null;
        }

        if ($this->user->isLoggedIn()) {
            $this->user->logout(true); //
        }

        return is_string($headerContent) ? $headerContent : null;
    }

    /**
     * Attempts to load Bearer token from specified url param
     * If there is something sent in this param, always logout current user and work just with this header
     *
     * @param string $param Name of url param to try to parse from
     * @return string|null If bearer is not found or is invalid formatted
     */
    private function getTokenFromUrl(string $param): ?string
    {
        $paramContent = $this->getHttpRequest()->getUrl()->getQueryParameter($param);

        if (empty($paramContent)) {
            return null;
        }

        if ($this->user->isLoggedIn()) {
            $this->user->logout(true); //
        }

        return is_string($paramContent) ? $paramContent : null;
    }

    /**
     * Authorize user using token
     *
     * @param string $token Contents
     */
    private function allowToken(?string $token): void
    {
    }
}
