<?php

namespace Tymy\Module\Event\Router;

use Tymy\Module\Core\Interfaces\RouterInterface;
use Tymy\Module\Core\Router\RouteList;

/**
 * Description of Router
 */
class Router implements RouterInterface
{
    public function addRoutes(RouteList &$router): void
    {
        $router->addApiRoute('event[s]/withMyAttendance', ['module' => 'Event', 'presenter' => 'Default', 'action' => 'default'])
                ->addApiRoute('eventTypes', ['module' => 'Event', 'presenter' => 'Types', 'action' => 'default']);
    }
}
