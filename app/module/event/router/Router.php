<?php

namespace Tymy\Module\Event\Router;

use Nette\Application\Routers\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 13. 9. 2020
 */
class Router implements RouterInterface
{

    public function addRoutes(RouteList &$router): void
    {
        $router->withPath("api")
                ->addRoute('event[s]/withMyAttendance', array(
                    'module' => 'Event',
                    'presenter' => 'Default',
                    'action' => 'default',
                ))
                ->addRoute('eventTypes', array(
                    'module' => 'Event',
                    'presenter' => 'Types',
                    'action' => 'default',
        ));
    }
}