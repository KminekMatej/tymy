<?php

namespace Tymy\Module\News\Router;

use Nette\Application\Routers\RouteList;
use Tymy\Module\Core\Interfaces\RouterInterface;

/**
 * Description of Router
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 21. 02. 2021
 */
class Router implements RouterInterface
{

    public function addRoutes(RouteList &$router): void
    {
        $router->withPath("api")->addRoute('news[/<resourceId \d+>][/<presenter>][s][/<subResourceId \d+>][/<action>]',
                [
                    'module' => 'News',
                    'presenter' => 'Default',
                    'action' => 'default',
                ]
        );
    }
}
