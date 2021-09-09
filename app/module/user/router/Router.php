<?php

namespace Tymy\Module\User\Router;

use Nette\Application\Routers\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 8. 2020
 */
class Router implements RouterInterface
{

    public function addRoutes(RouteList &$router): void
    {
        $router->withPath("api")
                ->addRoute('user[s]/status[/<status>]', array(
                    'module' => 'User',
                    'presenter' => 'Status',
                    'action' => 'default',
                ))
                ->addRoute('pwd<action reset|lost>[/<code>]', array(
                    'module' => 'User',
                    'presenter' => 'Pwd',
                    'action' => 'default',
                ))
                ->addRoute('live', array(
                    'module' => 'User',
                    'presenter' => 'Live',
                    'action' => 'default',
        ));
    }
}