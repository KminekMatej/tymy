<?php

namespace Tymy\Module\Attendance\Router;

use Nette\Application\Routers\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 03. 11. 2020
 */
class Router implements RouterInterface
{

    public function addRoutes(RouteList &$router): void
    {
        $router->withPath("api")->addRoute('attendance<action Status|StatusSet>[/<resourceId>]', array(
            'module' => 'Attendance',
            'presenter' => 'Status',
        ));
    }
}