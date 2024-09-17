<?php

namespace Tymy\Module\File\Router;

use Tymy\Module\Core\Router\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 */
class Router implements RouterInterface
{
    public function addRoutes(RouteList &$router): void
    {
        $router->addRoute('file/download/<filename>', ['module' => 'File', 'presenter' => 'Download', 'action' => 'default']);
    }
}
