<?php

namespace Tymy\Module\Authentication\Router;

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
        $router->addApiRoute('log<action \D+>[/<username>][/<password>]', ['module' => 'Authentication', 'presenter' => 'Default', 'action' => 'default'])
                ->addApiRoute('is', ['module' => 'Authentication', 'presenter' => 'Is', 'action' => 'default']);
    }
}
