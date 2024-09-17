<?php

namespace Tymy\Module\Permission\Router;

use Tymy\Module\Core\Router\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 */
class Router implements RouterInterface
{
    public function addRoutes(RouteList &$router): void
    {
        $router->addApiRoute('permission<action Name|Type>[/<name \D+>]', ['module' => 'Permission', 'presenter' => 'Default']);
    }
}
