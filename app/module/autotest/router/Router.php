<?php

namespace Tymy\Module\Autotest\Router;

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
        $router->addRoute('autotest[/<resourceId>]', array(
            'module' => 'Autotest',
            'presenter' => 'Default',
            'action' => 'default',
        ));
    }

}