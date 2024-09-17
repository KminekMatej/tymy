<?php

namespace Tymy\Module\Attendance\Router;

use Tymy\Module\Core\Router\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 */
class Router implements RouterInterface
{
    public function addRoutes(RouteList &$router): void
    {
        $router->addApiRoute('attendance<action Status|StatusSet>[/<resourceId>]', ['module' => 'Attendance', 'presenter' => 'Status']);
    }
}
