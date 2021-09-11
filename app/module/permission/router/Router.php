<?php

namespace Tymy\Module\Permission\Router;

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
        $router->addApiRoute('permission<action Name|Type>[/<name \D+>]', array(
            'module' => 'Permission',
            'presenter' => 'Default',
        ));
    }
}