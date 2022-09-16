<?php

namespace Tymy\Module\User\Router;

use Tymy\Module\Core\Interfaces\RouterInterface;
use Tymy\Module\Core\Router\RouteList;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 8. 2020
 */
class Router implements RouterInterface
{

    public function addRoutes(RouteList &$router): void
    {
        //add route to be able to server ical calendars on the same url like in previous version
        $router->addRoute("index.php ? page=calexp & user_id=<resource> & hash=<hash>", [
                'module' => 'User',
                'presenter' => 'Detail',
                'action' => 'calendar'
                ], $router::ONE_WAY)
            ->addApiRoute('user[s]/status[/<status>]', [
                'module' => 'User',
                'presenter' => 'Status',
                'action' => 'default',
            ])
            ->addApiRoute('pwd<action reset|lost>[/<code>]', [
                'module' => 'User',
                'presenter' => 'Pwd',
                'action' => 'default',
            ])
            ->addApiRoute('live', [
                'module' => 'User',
                'presenter' => 'Live',
                'action' => 'default',
        ]);
    }
}
