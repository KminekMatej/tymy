<?php

namespace Tymy\Module\Autotest\Router;

use Tymy\Module\Core\Router\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 */
class Router implements RouterInterface
{
    public function addRoutes(RouteList &$router): void
    {
        $router->addRoute('autotest[/<resourceId>]', ['module' => 'Autotest', 'presenter' => 'Default', 'action' => 'default']);
    }
}
