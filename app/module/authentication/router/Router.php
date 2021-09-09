<?php

namespace Tymy\Module\Authentication\Router;

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
        $router->withPath("api")->addRoute('log<action \D+>[/<username>][/<password>]', array(
                    'module' => 'Authentication',
                    'presenter' => 'Default',
                    'action' => 'default',
                ))
                ->addRoute('is', array(
                    'module' => 'Authentication',
                    'presenter' => 'Is',
                    'action' => 'default',
        ));
    }

}