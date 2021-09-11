<?php

namespace Tymy\Module\Multiaccount\Router;

use Nette\Application\Routers\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 07.02.2021
 */
class Router implements RouterInterface
{

    public function addRoutes(RouteList &$router): void
    {
        $router->withPath("api")->addRoute('multiaccount[s][/<resourceId>]', array(
            'module' => 'Multiaccount',
            'presenter' => 'Default',
            'action' => 'default',
        ));
    }
}