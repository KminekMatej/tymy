<?php

namespace Tymy\Module\Multiaccount\Router;

use Tymy\Module\Core\Router\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 */
class Router implements RouterInterface
{
    public function addRoutes(RouteList &$router): void
    {
        $router->addApiRoute('multiaccount[s][/<resourceId>]', ['module' => 'Multiaccount', 'presenter' => 'Default', 'action' => 'default']);
    }
}
