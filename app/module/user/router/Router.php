<?php

namespace Tymy\Module\User\Router;

use Tymy\Module\Core\Router\RouteList;
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
        $router->addApiRoute('user[s]/status[/<status>]', array(
                    'module' => 'User',
                    'presenter' => 'Status',
                    'action' => 'default',
                ))
                ->addApiRoute('pwd<action reset|lost>[/<code>]', array(
                    'module' => 'User',
                    'presenter' => 'Pwd',
                    'action' => 'default',
                ))
                ->addApiRoute('live', array(
                    'module' => 'User',
                    'presenter' => 'Live',
                    'action' => 'default',
                ));
    }
}
