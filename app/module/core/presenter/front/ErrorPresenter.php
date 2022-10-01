<?php

namespace Tymy\Module\Core\Presenter\Front;

use Nette\Application\BadRequestException;
use Nette\Application\Helpers;
use Nette\Application\IPresenter;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\Responses\ForwardResponse;
use Nette\SmartObject;
use Tracy\ILogger;

class ErrorPresenter implements IPresenter
{
    use SmartObject;


    public function __construct(private ILogger $logger)
    {
    }


    /**
     * @return IResponse
     */
    public function run(Request $request): Response
    {
        $e = $request->getParameter('exception');

        if ($e instanceof BadRequestException) {
            // $this->logger->log("HTTP code {$e->getCode()}: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", 'access');
            [$module, , $sep] = Helpers::splitName($request->getPresenterName());
            return new ForwardResponse($request->setPresenterName($module . $sep . 'Error4xx'));
        }

        $this->logger->log($e, ILogger::EXCEPTION);
        return new CallbackResponse(function () {
            require __DIR__ . '/templates/Error/500.html';
        });
    }
}
